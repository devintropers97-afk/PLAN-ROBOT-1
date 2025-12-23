<?php
/**
 * =========================================================
 * ZYN TRADE SYSTEM - Robot Service
 * =========================================================
 *
 * Service class untuk komunikasi dengan Robot API
 * Digunakan oleh website PHP untuk execute trades, get balance, dll
 *
 * Usage:
 *   $robot = new RobotService();
 *   $result = $robot->executeTrade($userId, 'CALL', 10, true);
 */

require_once __DIR__ . '/config.php';

class RobotService
{
    private $apiUrl;
    private $apiKey;
    private $timeout;
    private $lastError;

    public function __construct()
    {
        $this->apiUrl = rtrim(ROBOT_API_URL, '/');
        $this->apiKey = ROBOT_API_KEY;
        $this->timeout = ROBOT_TIMEOUT;
        $this->lastError = null;
    }

    /**
     * Check if robot service is enabled
     */
    public function isEnabled(): bool
    {
        return defined('ROBOT_ENABLED') && ROBOT_ENABLED === true;
    }

    /**
     * Get last error message
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Health check - check if robot API is running
     */
    public function health(): array
    {
        try {
            $response = $this->request('GET', '/health');
            return [
                'success' => true,
                'status' => $response['status'] ?? 'unknown',
                'queue' => $response['queue'] ?? [],
                'uptime' => $response['uptime'] ?? 0
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Execute trade untuk trader
     *
     * @param int $userId User ID from database
     * @param string $direction 'CALL' or 'PUT'
     * @param float $amount Trade amount
     * @param bool $isDemo Demo or Real account
     * @param array $options Additional options (asset, duration, tier)
     * @return array Result dengan jobId
     */
    public function executeTrade(int $userId, string $direction, float $amount, bool $isDemo = true, array $options = []): array
    {
        // Get trader credentials from database
        $trader = $this->getTraderCredentials($userId);

        if (!$trader) {
            return [
                'success' => false,
                'error' => 'Trader credentials not found'
            ];
        }

        // Decrypt password
        $password = $this->decryptPassword($trader['olymptrade_password_encrypted']);

        if (!$password) {
            return [
                'success' => false,
                'error' => 'Failed to decrypt trader credentials'
            ];
        }

        // Get user tier for priority
        $tier = $options['tier'] ?? $this->getUserTier($userId);

        try {
            $response = $this->request('POST', '/api/trade/execute', [
                'email' => $trader['olymptrade_email'],
                'password' => $password,
                'direction' => strtoupper($direction),
                'amount' => $amount,
                'asset' => $options['asset'] ?? 'EUR/USD',
                'duration' => $options['duration'] ?? 1,
                'isDemo' => $isDemo,
                'tier' => $tier
            ]);

            // Record trade in database
            if ($response['success'] ?? false) {
                $this->recordTradeRequest($userId, $response['jobId'], $direction, $amount, $isDemo);
            }

            return $response;
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get trade status by job ID
     */
    public function getTradeStatus(string $jobId): array
    {
        try {
            return $this->request('GET', "/api/trade/status/{$jobId}");
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Wait for trade to complete
     */
    public function waitForTrade(string $jobId, int $maxWait = 120, int $pollInterval = 2): array
    {
        $startTime = time();

        while (time() - $startTime < $maxWait) {
            $status = $this->getTradeStatus($jobId);

            if (($status['success'] ?? false) && isset($status['job'])) {
                $jobStatus = $status['job']['status'] ?? '';

                if ($jobStatus === 'completed' || $jobStatus === 'failed') {
                    return $status['job'];
                }
            }

            sleep($pollInterval);
        }

        return [
            'success' => false,
            'error' => "Trade timeout after {$maxWait} seconds"
        ];
    }

    /**
     * Test login for trader
     */
    public function testLogin(int $userId): array
    {
        $trader = $this->getTraderCredentials($userId);

        if (!$trader) {
            return [
                'success' => false,
                'error' => 'Trader credentials not found'
            ];
        }

        $password = $this->decryptPassword($trader['olymptrade_password_encrypted']);

        try {
            return $this->request('POST', '/api/trader/login', [
                'email' => $trader['olymptrade_email'],
                'password' => $password,
                'isDemo' => true,
                'tier' => $this->getUserTier($userId)
            ]);
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get balance for trader
     */
    public function getBalance(int $userId, bool $isDemo = true): array
    {
        $trader = $this->getTraderCredentials($userId);

        if (!$trader) {
            return [
                'success' => false,
                'error' => 'Trader credentials not found'
            ];
        }

        $password = $this->decryptPassword($trader['olymptrade_password_encrypted']);

        try {
            return $this->request('POST', '/api/trader/balance', [
                'email' => $trader['olymptrade_email'],
                'password' => $password,
                'isDemo' => $isDemo,
                'tier' => $this->getUserTier($userId)
            ]);
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get trader session status
     */
    public function getSession(int $userId): array
    {
        $trader = $this->getTraderCredentials($userId);

        if (!$trader) {
            return ['hasSession' => false];
        }

        try {
            return $this->request('GET', '/api/trader/session/' . urlencode($trader['olymptrade_email']));
        } catch (Exception $e) {
            return ['hasSession' => false];
        }
    }

    /**
     * Clear trader session
     */
    public function clearSession(int $userId): array
    {
        $trader = $this->getTraderCredentials($userId);

        if (!$trader) {
            return ['success' => false, 'error' => 'Trader not found'];
        }

        try {
            return $this->request('DELETE', '/api/trader/session/' . urlencode($trader['olymptrade_email']));
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get queue statistics
     */
    public function getQueueStats(): array
    {
        try {
            return $this->request('GET', '/api/queue/stats');
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Cancel a pending trade
     */
    public function cancelTrade(string $jobId): array
    {
        try {
            return $this->request('DELETE', "/api/trade/{$jobId}");
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    // =========================================================
    // PRIVATE HELPER METHODS
    // =========================================================

    /**
     * Make HTTP request to Robot API
     */
    private function request(string $method, string $endpoint, array $data = null): array
    {
        $url = $this->apiUrl . $endpoint;

        $ch = curl_init();

        $headers = [
            'Content-Type: application/json',
            'X-API-Key: ' . $this->apiKey
        ];

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => $method
        ]);

        if ($data && $method === 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            $this->lastError = "cURL Error: {$error}";
            throw new Exception($this->lastError);
        }

        $decoded = json_decode($response, true);

        if ($httpCode >= 400) {
            $errorMsg = $decoded['error'] ?? 'Unknown error';
            $this->lastError = "API Error ({$httpCode}): {$errorMsg}";
            throw new Exception($this->lastError);
        }

        return $decoded ?? [];
    }

    /**
     * Get trader credentials from database
     */
    private function getTraderCredentials(int $userId): ?array
    {
        $pdo = getDBConnection();
        if (!$pdo) return null;

        $stmt = $pdo->prepare("
            SELECT olymptrade_email, olymptrade_password_encrypted
            FROM traders
            WHERE user_id = :user_id AND is_active = 1
            LIMIT 1
        ");
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get user tier/package
     */
    private function getUserTier(int $userId): string
    {
        $pdo = getDBConnection();
        if (!$pdo) return 'FREE';

        $stmt = $pdo->prepare("
            SELECT st.name as tier_name
            FROM users u
            JOIN subscription_tiers st ON u.tier_id = st.id
            WHERE u.id = :user_id
            LIMIT 1
        ");
        $stmt->execute(['user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['tier_name'] ?? 'FREE';
    }

    /**
     * Decrypt OlympTrade password
     */
    private function decryptPassword(string $encrypted): ?string
    {
        try {
            $key = ENCRYPTION_KEY;
            $data = base64_decode($encrypted);
            $iv = substr($data, 0, 16);
            $encryptedData = substr($data, 16);

            return openssl_decrypt($encryptedData, 'AES-256-CBC', $key, 0, $iv);
        } catch (Exception $e) {
            error_log("Decrypt error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Encrypt password for storage
     */
    public static function encryptPassword(string $password): string
    {
        $key = ENCRYPTION_KEY;
        $iv = openssl_random_pseudo_bytes(16);
        $encrypted = openssl_encrypt($password, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    /**
     * Record trade request in database
     */
    private function recordTradeRequest(int $userId, string $jobId, string $direction, float $amount, bool $isDemo): void
    {
        $pdo = getDBConnection();
        if (!$pdo) return;

        try {
            // Get trader ID
            $stmt = $pdo->prepare("SELECT id FROM traders WHERE user_id = :user_id LIMIT 1");
            $stmt->execute(['user_id' => $userId]);
            $trader = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$trader) return;

            // Insert trade record
            $stmt = $pdo->prepare("
                INSERT INTO trade_history (
                    trader_id, job_id, direction, amount, asset, status, account_type, created_at
                ) VALUES (
                    :trader_id, :job_id, :direction, :amount, 'EUR/USD', 'pending', :account_type, NOW()
                )
            ");
            $stmt->execute([
                'trader_id' => $trader['id'],
                'job_id' => $jobId,
                'direction' => $direction,
                'amount' => $amount,
                'account_type' => $isDemo ? 'demo' : 'real'
            ]);
        } catch (Exception $e) {
            error_log("Record trade error: " . $e->getMessage());
        }
    }
}

// =========================================================
// HELPER FUNCTIONS
// =========================================================

/**
 * Get RobotService instance (singleton)
 */
function getRobotService(): RobotService
{
    static $instance = null;
    if ($instance === null) {
        $instance = new RobotService();
    }
    return $instance;
}

/**
 * Quick helper to execute trade
 */
function executeTrade(int $userId, string $direction, float $amount, bool $isDemo = true): array
{
    return getRobotService()->executeTrade($userId, $direction, $amount, $isDemo);
}

/**
 * Quick helper to check robot health
 */
function checkRobotHealth(): array
{
    return getRobotService()->health();
}
