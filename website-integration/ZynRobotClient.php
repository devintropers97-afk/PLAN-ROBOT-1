<?php
/**
 * ZYN Trade Robot - PHP Client
 *
 * Class untuk integrasi website PHP dengan Robot API
 *
 * Contoh penggunaan:
 *
 * $robot = new ZynRobotClient('http://localhost:3001', 'your-api-key');
 *
 * // Execute trade
 * $result = $robot->executeTrade([
 *     'email' => 'trader@email.com',
 *     'password' => 'password123',
 *     'direction' => 'CALL',
 *     'amount' => 10,
 *     'isDemo' => true
 * ]);
 *
 * // Check status
 * $status = $robot->getTradeStatus($result['jobId']);
 */

class ZynRobotClient
{
    private $baseUrl;
    private $apiKey;
    private $timeout;

    /**
     * Constructor
     *
     * @param string $baseUrl Robot API base URL (e.g., 'http://localhost:3001')
     * @param string $apiKey API key untuk autentikasi
     * @param int $timeout Request timeout in seconds
     */
    public function __construct($baseUrl, $apiKey, $timeout = 30)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
        $this->timeout = $timeout;
    }

    /**
     * Execute trade untuk trader
     *
     * @param array $params [
     *     'email' => string (required),
     *     'password' => string (required),
     *     'direction' => 'CALL' | 'PUT' (required),
     *     'amount' => int (required, minimum 1),
     *     'asset' => string (optional, default 'EUR/USD'),
     *     'duration' => int (optional, default 1 minute),
     *     'isDemo' => bool (optional, default true)
     * ]
     * @return array Response dengan jobId
     */
    public function executeTrade($params)
    {
        return $this->post('/api/trade/execute', $params);
    }

    /**
     * Get status of a trade job
     *
     * @param string $jobId Job ID dari executeTrade
     * @return array Job status
     */
    public function getTradeStatus($jobId)
    {
        return $this->get("/api/trade/status/{$jobId}");
    }

    /**
     * Wait for trade to complete
     *
     * @param string $jobId Job ID
     * @param int $maxWait Maximum wait time in seconds
     * @param int $pollInterval Polling interval in seconds
     * @return array Final job result
     */
    public function waitForTrade($jobId, $maxWait = 120, $pollInterval = 2)
    {
        $startTime = time();

        while (time() - $startTime < $maxWait) {
            $status = $this->getTradeStatus($jobId);

            if ($status['success'] && isset($status['job'])) {
                $jobStatus = $status['job']['status'];

                if ($jobStatus === 'completed' || $jobStatus === 'failed') {
                    return $status['job'];
                }
            }

            sleep($pollInterval);
        }

        throw new Exception("Trade timeout after {$maxWait} seconds");
    }

    /**
     * Test login untuk trader
     *
     * @param string $email
     * @param string $password
     * @param bool $isDemo
     * @return array
     */
    public function testLogin($email, $password, $isDemo = true)
    {
        return $this->post('/api/trader/login', [
            'email' => $email,
            'password' => $password,
            'isDemo' => $isDemo
        ]);
    }

    /**
     * Get balance untuk trader
     *
     * @param string $email
     * @param string $password
     * @param bool $isDemo
     * @return array
     */
    public function getBalance($email, $password, $isDemo = true)
    {
        return $this->post('/api/trader/balance', [
            'email' => $email,
            'password' => $password,
            'isDemo' => $isDemo
        ]);
    }

    /**
     * Get session info untuk trader
     *
     * @param string $email
     * @return array
     */
    public function getSession($email)
    {
        return $this->get("/api/trader/session/{$email}");
    }

    /**
     * Clear session untuk trader
     *
     * @param string $email
     * @return array
     */
    public function clearSession($email)
    {
        return $this->delete("/api/trader/session/{$email}");
    }

    /**
     * Get queue status
     *
     * @return array
     */
    public function getQueueStatus()
    {
        return $this->get('/api/queue/status');
    }

    /**
     * Get all jobs in queue
     *
     * @param string|null $status Filter by status (pending, processing, completed, failed)
     * @param int $limit Maximum jobs to return
     * @return array
     */
    public function getQueueJobs($status = null, $limit = 50)
    {
        $query = ['limit' => $limit];
        if ($status) {
            $query['status'] = $status;
        }
        return $this->get('/api/queue/jobs?' . http_build_query($query));
    }

    /**
     * Cancel a pending job
     *
     * @param string $jobId
     * @return array
     */
    public function cancelJob($jobId)
    {
        return $this->delete("/api/queue/job/{$jobId}");
    }

    /**
     * Health check
     *
     * @return array
     */
    public function health()
    {
        return $this->get('/health');
    }

    /**
     * Get all active sessions
     *
     * @return array
     */
    public function getAllSessions()
    {
        return $this->get('/api/sessions');
    }

    /**
     * Cleanup expired sessions
     *
     * @return array
     */
    public function cleanupSessions()
    {
        return $this->post('/api/sessions/cleanup', []);
    }

    // ========================================
    // HTTP Methods
    // ========================================

    private function get($endpoint)
    {
        return $this->request('GET', $endpoint);
    }

    private function post($endpoint, $data)
    {
        return $this->request('POST', $endpoint, $data);
    }

    private function delete($endpoint)
    {
        return $this->request('DELETE', $endpoint);
    }

    private function request($method, $endpoint, $data = null)
    {
        $url = $this->baseUrl . $endpoint;

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
            throw new Exception("cURL Error: {$error}");
        }

        $decoded = json_decode($response, true);

        if ($httpCode >= 400) {
            $errorMsg = $decoded['error'] ?? 'Unknown error';
            throw new Exception("API Error ({$httpCode}): {$errorMsg}");
        }

        return $decoded;
    }
}

// ========================================
// Helper Functions
// ========================================

/**
 * Enkripsi password sebelum simpan ke database
 */
function encryptPassword($password, $key = null)
{
    $key = $key ?? getenv('ENCRYPTION_KEY') ?? 'default-key-change-this';
    $iv = openssl_random_pseudo_bytes(16);
    $encrypted = openssl_encrypt($password, 'AES-256-CBC', $key, 0, $iv);
    return base64_encode($iv . $encrypted);
}

/**
 * Dekripsi password dari database
 */
function decryptPassword($encrypted, $key = null)
{
    $key = $key ?? getenv('ENCRYPTION_KEY') ?? 'default-key-change-this';
    $data = base64_decode($encrypted);
    $iv = substr($data, 0, 16);
    $encrypted = substr($data, 16);
    return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
}
