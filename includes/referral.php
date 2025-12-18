<?php
/**
 * ZYN Trade System - Referral/Affiliate Program
 *
 * CARA KERJA:
 * 1. Setiap user dapat referral code unik
 * 2. User share link: zyntrading.com/?ref=ABC123
 * 3. Ketika ada yang daftar via link tersebut:
 *    - Referrer dapat komisi 20% dari pembayaran
 *    - New user dapat diskon 10%
 *
 * CARA PAKAI:
 * - Generate code: Referral::generateCode($user_id)
 * - Track referral: Referral::trackVisit($_GET['ref'])
 * - Apply referral: Referral::applyOnRegistration($new_user_id)
 * - Calculate commission: Referral::calculateCommission($payment_amount)
 */

class Referral {
    private static $db = null;
    private static $commission_rate = 0.20; // 20% komisi
    private static $discount_rate = 0.10;   // 10% diskon untuk new user

    /**
     * Initialize database connection
     */
    private static function getDB() {
        if (self::$db === null) {
            try {
                self::$db = new PDO(
                    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                    DB_USER,
                    DB_PASS,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
            } catch (PDOException $e) {
                error_log("Referral DB Error: " . $e->getMessage());
                return null;
            }
        }
        return self::$db;
    }

    /**
     * Generate unique referral code for user
     */
    public static function generateCode($user_id) {
        // Generate unique code
        $code = strtoupper(substr(md5($user_id . time() . rand()), 0, 8));

        // Check if code exists
        $db = self::getDB();
        if (!$db) return null;

        // Update user's referral code
        $stmt = $db->prepare("UPDATE users SET referral_code = ? WHERE id = ?");
        $stmt->execute([$code, $user_id]);

        return $code;
    }

    /**
     * Get user's referral code
     */
    public static function getCode($user_id) {
        $db = self::getDB();
        if (!$db) return null;

        $stmt = $db->prepare("SELECT referral_code FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && $result['referral_code']) {
            return $result['referral_code'];
        }

        // Generate if not exists
        return self::generateCode($user_id);
    }

    /**
     * Get user ID by referral code
     */
    public static function getUserByCode($code) {
        $db = self::getDB();
        if (!$db) return null;

        $stmt = $db->prepare("SELECT id, name, email FROM users WHERE referral_code = ?");
        $stmt->execute([strtoupper($code)]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Track referral visit (save to session/cookie)
     */
    public static function trackVisit($code) {
        if (empty($code)) return false;

        $referrer = self::getUserByCode($code);
        if (!$referrer) return false;

        // Save to session
        $_SESSION['referral_code'] = strtoupper($code);
        $_SESSION['referrer_id'] = $referrer['id'];

        // Save to cookie (30 days)
        setcookie('ref', strtoupper($code), time() + (30 * 24 * 60 * 60), '/');

        // Log visit
        self::logReferralVisit($referrer['id'], $code);

        return true;
    }

    /**
     * Get tracked referral from session/cookie
     */
    public static function getTrackedReferral() {
        // Check session first
        if (!empty($_SESSION['referral_code'])) {
            return [
                'code' => $_SESSION['referral_code'],
                'referrer_id' => $_SESSION['referrer_id'] ?? null
            ];
        }

        // Check cookie
        if (!empty($_COOKIE['ref'])) {
            $referrer = self::getUserByCode($_COOKIE['ref']);
            if ($referrer) {
                return [
                    'code' => $_COOKIE['ref'],
                    'referrer_id' => $referrer['id']
                ];
            }
        }

        return null;
    }

    /**
     * Apply referral on user registration
     */
    public static function applyOnRegistration($new_user_id) {
        $referral = self::getTrackedReferral();
        if (!$referral) return false;

        $db = self::getDB();
        if (!$db) return false;

        try {
            // Update new user's referred_by
            $stmt = $db->prepare("UPDATE users SET referred_by = ? WHERE id = ?");
            $stmt->execute([$referral['referrer_id'], $new_user_id]);

            // Create referral record
            $stmt = $db->prepare("
                INSERT INTO referrals (referrer_id, referred_id, code, status, created_at)
                VALUES (?, ?, ?, 'pending', NOW())
            ");
            $stmt->execute([
                $referral['referrer_id'],
                $new_user_id,
                $referral['code']
            ]);

            // Update referrer's stats
            $stmt = $db->prepare("
                UPDATE users
                SET referral_count = referral_count + 1
                WHERE id = ?
            ");
            $stmt->execute([$referral['referrer_id']]);

            // Clear session/cookie
            unset($_SESSION['referral_code']);
            unset($_SESSION['referrer_id']);
            setcookie('ref', '', time() - 3600, '/');

            return true;

        } catch (PDOException $e) {
            error_log("Referral Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Calculate commission for payment
     */
    public static function calculateCommission($amount) {
        return round($amount * self::$commission_rate, 2);
    }

    /**
     * Calculate discount for referred user
     */
    public static function calculateDiscount($amount) {
        return round($amount * self::$discount_rate, 2);
    }

    /**
     * Process commission on successful payment
     */
    public static function processCommission($user_id, $payment_amount, $payment_id) {
        $db = self::getDB();
        if (!$db) return false;

        // Get user's referrer
        $stmt = $db->prepare("SELECT referred_by FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !$user['referred_by']) {
            return false; // No referrer
        }

        $referrer_id = $user['referred_by'];
        $commission = self::calculateCommission($payment_amount);

        try {
            // Create commission record
            $stmt = $db->prepare("
                INSERT INTO commissions
                (referrer_id, referred_id, payment_id, amount, commission, status, created_at)
                VALUES (?, ?, ?, ?, ?, 'pending', NOW())
            ");
            $stmt->execute([
                $referrer_id,
                $user_id,
                $payment_id,
                $payment_amount,
                $commission
            ]);

            // Update referrer's total earnings
            $stmt = $db->prepare("
                UPDATE users
                SET referral_earnings = referral_earnings + ?
                WHERE id = ?
            ");
            $stmt->execute([$commission, $referrer_id]);

            // Update referral status
            $stmt = $db->prepare("
                UPDATE referrals
                SET status = 'converted', converted_at = NOW()
                WHERE referred_id = ? AND status = 'pending'
            ");
            $stmt->execute([$user_id]);

            return $commission;

        } catch (PDOException $e) {
            error_log("Commission Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user's referral statistics
     */
    public static function getStats($user_id) {
        $db = self::getDB();
        if (!$db) return null;

        $stats = [
            'code' => self::getCode($user_id),
            'total_referrals' => 0,
            'pending_referrals' => 0,
            'converted_referrals' => 0,
            'total_earnings' => 0,
            'pending_earnings' => 0,
            'withdrawn_earnings' => 0,
            'referral_link' => ''
        ];

        // Get referral counts
        $stmt = $db->prepare("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) as converted
            FROM referrals
            WHERE referrer_id = ?
        ");
        $stmt->execute([$user_id]);
        $counts = $stmt->fetch(PDO::FETCH_ASSOC);

        $stats['total_referrals'] = (int) $counts['total'];
        $stats['pending_referrals'] = (int) $counts['pending'];
        $stats['converted_referrals'] = (int) $counts['converted'];

        // Get earnings
        $stmt = $db->prepare("
            SELECT
                COALESCE(SUM(commission), 0) as total,
                COALESCE(SUM(CASE WHEN status = 'pending' THEN commission ELSE 0 END), 0) as pending,
                COALESCE(SUM(CASE WHEN status = 'paid' THEN commission ELSE 0 END), 0) as withdrawn
            FROM commissions
            WHERE referrer_id = ?
        ");
        $stmt->execute([$user_id]);
        $earnings = $stmt->fetch(PDO::FETCH_ASSOC);

        $stats['total_earnings'] = (float) $earnings['total'];
        $stats['pending_earnings'] = (float) $earnings['pending'];
        $stats['withdrawn_earnings'] = (float) $earnings['withdrawn'];

        // Generate referral link
        $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
        $base_url .= '://' . ($_SERVER['HTTP_HOST'] ?? 'zyntrading.com');
        $stats['referral_link'] = $base_url . '/register.php?ref=' . $stats['code'];

        return $stats;
    }

    /**
     * Get user's referral list
     */
    public static function getReferrals($user_id, $limit = 50) {
        $db = self::getDB();
        if (!$db) return [];

        $stmt = $db->prepare("
            SELECT
                r.*,
                u.name as referred_name,
                u.email as referred_email,
                u.created_at as referred_date,
                COALESCE((
                    SELECT SUM(commission)
                    FROM commissions
                    WHERE referred_id = r.referred_id AND referrer_id = r.referrer_id
                ), 0) as total_commission
            FROM referrals r
            JOIN users u ON r.referred_id = u.id
            WHERE r.referrer_id = ?
            ORDER BY r.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$user_id, $limit]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Request withdrawal
     */
    public static function requestWithdrawal($user_id, $amount, $method, $details) {
        $db = self::getDB();
        if (!$db) return ['success' => false, 'error' => 'Database error'];

        // Check minimum withdrawal
        $min_withdrawal = 10; // $10 minimum
        if ($amount < $min_withdrawal) {
            return ['success' => false, 'error' => "Minimum withdrawal adalah \${$min_withdrawal}"];
        }

        // Check available balance
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(commission), 0) as available
            FROM commissions
            WHERE referrer_id = ? AND status = 'pending'
        ");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['available'] < $amount) {
            return ['success' => false, 'error' => 'Saldo tidak mencukupi'];
        }

        try {
            // Create withdrawal request
            $stmt = $db->prepare("
                INSERT INTO withdrawals
                (user_id, amount, method, details, status, created_at)
                VALUES (?, ?, ?, ?, 'pending', NOW())
            ");
            $stmt->execute([$user_id, $amount, $method, json_encode($details)]);

            return ['success' => true, 'message' => 'Permintaan withdrawal berhasil diajukan'];

        } catch (PDOException $e) {
            error_log("Withdrawal Error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Gagal mengajukan withdrawal'];
        }
    }

    /**
     * Log referral visit
     */
    private static function logReferralVisit($referrer_id, $code) {
        $db = self::getDB();
        if (!$db) return;

        try {
            $stmt = $db->prepare("
                INSERT INTO referral_visits
                (referrer_id, code, ip_address, user_agent, visited_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $referrer_id,
                $code,
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
        } catch (PDOException $e) {
            // Silent fail for visits logging
        }
    }

    /**
     * Check if user has referral discount
     */
    public static function hasDiscount($user_id) {
        $db = self::getDB();
        if (!$db) return false;

        $stmt = $db->prepare("SELECT referred_by FROM users WHERE id = ? AND referred_by IS NOT NULL");
        $stmt->execute([$user_id]);

        return $stmt->fetch() !== false;
    }
}

// Auto-track referral visits
if (isset($_GET['ref']) && !empty($_GET['ref'])) {
    Referral::trackVisit($_GET['ref']);
}
