<?php
/**
 * ZYN Trade System - Helper Functions
 */

require_once 'config.php';

/**
 * User Authentication Functions
 */

// Register new user
function registerUser($email, $password, $fullname, $country, $olymptrade_id, $phone = '') {
    $db = getDBConnection();
    if (!$db) return ['success' => false, 'message' => 'Database connection failed'];

    // Check if email exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email already registered'];
    }

    // Check if OlympTrade ID exists
    $stmt = $db->prepare("SELECT id FROM users WHERE olymptrade_id = ?");
    $stmt->execute([$olymptrade_id]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'OlympTrade ID already registered'];
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => HASH_COST]);

    // Insert user
    $stmt = $db->prepare("
        INSERT INTO users (email, password, fullname, country, olymptrade_id, phone, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");

    try {
        $stmt->execute([$email, $hashedPassword, $fullname, $country, $olymptrade_id, $phone]);
        return ['success' => true, 'message' => 'Registration successful! Please wait for admin verification.', 'user_id' => $db->lastInsertId()];
    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }
}

// Login user (legacy email/password - kept for admin)
function loginUser($email, $password) {
    $db = getDBConnection();
    if (!$db) return ['success' => false, 'message' => 'Database connection failed'];

    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        return ['success' => false, 'message' => 'Invalid email or password'];
    }

    if (!password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Invalid email or password'];
    }

    if ($user['status'] === 'pending') {
        return ['success' => false, 'message' => 'Your account is pending verification. Please wait for admin approval.'];
    }

    if ($user['status'] === 'rejected') {
        return ['success' => false, 'message' => 'Your account has been rejected. Reason: ' . ($user['rejection_reason'] ?? 'Contact support')];
    }

    if ($user['status'] === 'suspended') {
        return ['success' => false, 'message' => 'Your account has been suspended. Please contact support.'];
    }

    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['fullname'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_package'] = $user['package'];
    $_SESSION['license_key'] = $user['license_key'];

    // Update last login
    $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$user['id']]);

    return ['success' => true, 'message' => 'Login successful', 'user' => $user];
}

/**
 * License Key Authentication System
 */

// Login with License Key
function loginWithLicenseKey($license_key) {
    $db = getDBConnection();
    if (!$db) return ['success' => false, 'message' => 'Koneksi database gagal'];

    // Find user by license key
    $stmt = $db->prepare("SELECT * FROM users WHERE license_key = ?");
    $stmt->execute([$license_key]);
    $user = $stmt->fetch();

    if (!$user) {
        return ['success' => false, 'message' => 'License Key tidak ditemukan. Pastikan Anda memasukkan key dengan benar.'];
    }

    if ($user['status'] === 'pending') {
        return ['success' => false, 'message' => 'Akun Anda sedang menunggu verifikasi admin. Silakan tunggu maksimal 24 jam.'];
    }

    if ($user['status'] === 'rejected') {
        $reason = $user['rejection_reason'] ?? 'Hubungi support untuk info lebih lanjut';
        return ['success' => false, 'message' => 'Akun Anda ditolak. Alasan: ' . $reason];
    }

    if ($user['status'] === 'suspended') {
        return ['success' => false, 'message' => 'Akun Anda telah disuspend. Hubungi support.'];
    }

    // Check package expiry for paid packages
    if ($user['package'] !== 'free' && $user['package_expiry']) {
        if (strtotime($user['package_expiry']) < time()) {
            // Downgrade to free
            $stmt = $db->prepare("UPDATE users SET package = 'free', package_expiry = NULL WHERE id = ?");
            $stmt->execute([$user['id']]);
            $user['package'] = 'free';
        }
    }

    // SECURITY: Regenerate session ID to prevent session fixation attacks
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }

    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['fullname'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_package'] = $user['package'];
    $_SESSION['license_key'] = $user['license_key'];
    $_SESSION['login_time'] = time();

    // Update last login
    $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$user['id']]);

    // Log activity
    logActivity($user['id'], 'login', 'Login dengan License Key');

    return ['success' => true, 'message' => 'Login berhasil', 'user' => $user];
}

// Generate License Key
function generateLicenseKey($package = 'free') {
    $prefix = 'ZYN-';
    $tierCode = [
        'free' => 'F',
        'pro' => 'P',
        'elite' => 'E',
        'vip' => 'V'
    ];

    $code = $tierCode[$package] ?? 'X';
    $part1 = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
    $part2 = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));

    return $prefix . $code . '-' . $part1 . '-' . $part2;
}

// Validate License Key format
function validateLicenseKeyFormat($key) {
    return preg_match('/^ZYN-[A-Z]-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $key);
}

// Assign License Key to User
function assignLicenseKeyToUser($user_id, $package = 'free') {
    $db = getDBConnection();
    if (!$db) return false;

    $license_key = generateLicenseKey($package);

    // Check if key already exists (rare but possible)
    $stmt = $db->prepare("SELECT id FROM users WHERE license_key = ?");
    $stmt->execute([$license_key]);
    if ($stmt->fetch()) {
        // Generate new key if exists
        $license_key = generateLicenseKey($package);
    }

    $stmt = $db->prepare("UPDATE users SET license_key = ? WHERE id = ?");
    $success = $stmt->execute([$license_key, $user_id]);

    return $success ? $license_key : false;
}

// Log activity
function logActivity($user_id, $action, $description = '') {
    $db = getDBConnection();
    if (!$db) return false;

    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

    $stmt = $db->prepare("INSERT INTO activity_log (user_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$user_id, $action, $description, $ip, $user_agent]);
}

// Get user by ID
function getUserById($id) {
    $db = getDBConnection();
    if (!$db) return null;

    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Update user profile
function updateUserProfile($user_id, $data) {
    $db = getDBConnection();
    if (!$db) return false;

    $allowedFields = ['fullname', 'phone', 'country'];
    $updates = [];
    $values = [];

    foreach ($data as $key => $value) {
        if (in_array($key, $allowedFields)) {
            $updates[] = "$key = ?";
            $values[] = $value;
        }
    }

    if (empty($updates)) return false;

    $values[] = $user_id;
    $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";

    $stmt = $db->prepare($sql);
    return $stmt->execute($values);
}

/**
 * Trading Statistics Functions
 */

// Get user trading stats
function getUserStats($user_id, $period = '30') {
    $db = getDBConnection();
    if (!$db) return null;

    $date_limit = date('Y-m-d', strtotime("-$period days"));

    $stmt = $db->prepare("
        SELECT
            COUNT(*) as total_trades,
            SUM(CASE WHEN result = 'win' THEN 1 ELSE 0 END) as wins,
            SUM(CASE WHEN result = 'loss' THEN 1 ELSE 0 END) as losses,
            SUM(profit_loss) as total_pnl,
            AVG(CASE WHEN result = 'win' THEN 1 ELSE 0 END) * 100 as win_rate
        FROM trades
        WHERE user_id = ? AND created_at >= ?
    ");
    $stmt->execute([$user_id, $date_limit]);
    return $stmt->fetch();
}

// Get recent trades
function getRecentTrades($user_id, $limit = 10) {
    $db = getDBConnection();
    if (!$db) return [];

    $stmt = $db->prepare("
        SELECT * FROM trades
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$user_id, $limit]);
    return $stmt->fetchAll();
}

// Record a trade
function recordTrade($user_id, $data) {
    $db = getDBConnection();
    if (!$db) return false;

    $stmt = $db->prepare("
        INSERT INTO trades (user_id, strategy, asset, amount, direction, result, profit_loss, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    return $stmt->execute([
        $user_id,
        $data['strategy'],
        $data['asset'],
        $data['amount'],
        $data['direction'],
        $data['result'],
        $data['profit_loss']
    ]);
}

/**
 * Robot Settings Functions
 */

// Get user robot settings
function getRobotSettings($user_id) {
    $db = getDBConnection();
    if (!$db) return null;

    $stmt = $db->prepare("SELECT * FROM robot_settings WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $settings = $stmt->fetch();

    if (!$settings) {
        // Create default settings
        $stmt = $db->prepare("
            INSERT INTO robot_settings (user_id, robot_enabled, strategies, risk_level, trade_amount, daily_limit, stop_loss, take_profit)
            VALUES (?, 0, '[]', 'medium', 1, 10, 5, 10)
        ");
        $stmt->execute([$user_id]);

        return [
            'user_id' => $user_id,
            'robot_enabled' => 0,
            'strategies' => '[]',
            'risk_level' => 'medium',
            'trade_amount' => 1,
            'daily_limit' => 10,
            'stop_loss' => 5,
            'take_profit' => 10
        ];
    }

    return $settings;
}

// Update robot settings
function updateRobotSettings($user_id, $data) {
    $db = getDBConnection();
    if (!$db) return false;

    $stmt = $db->prepare("
        UPDATE robot_settings SET
            robot_enabled = ?,
            strategies = ?,
            risk_level = ?,
            trade_amount = ?,
            daily_limit = ?,
            stop_loss = ?,
            take_profit = ?,
            updated_at = NOW()
        WHERE user_id = ?
    ");

    return $stmt->execute([
        $data['robot_enabled'] ?? 0,
        $data['strategies'] ?? '[]',
        $data['risk_level'] ?? 'medium',
        $data['trade_amount'] ?? 1,
        $data['daily_limit'] ?? 10,
        $data['stop_loss'] ?? 5,
        $data['take_profit'] ?? 10,
        $user_id
    ]);
}

/**
 * Admin Functions
 */

// Get all pending users
function getPendingUsers() {
    $db = getDBConnection();
    if (!$db) return [];

    $stmt = $db->prepare("SELECT * FROM users WHERE status = 'pending' ORDER BY created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll();
}

// Get all users with filters
function getAllUsers($status = null, $page = 1, $limit = ITEMS_PER_PAGE) {
    $db = getDBConnection();
    if (!$db) return [];

    $offset = ($page - 1) * $limit;

    if ($status) {
        $stmt = $db->prepare("SELECT * FROM users WHERE status = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute([$status, $limit, $offset]);
    } else {
        $stmt = $db->prepare("SELECT * FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
    }

    return $stmt->fetchAll();
}

// Verify user (approve) - Updated for License Key system
function verifyUser($user_id, $admin_id) {
    $db = getDBConnection();
    if (!$db) return false;

    // Generate license key for user
    $license_key = generateLicenseKey('free');

    // Check if key already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE license_key = ?");
    $stmt->execute([$license_key]);
    if ($stmt->fetch()) {
        $license_key = generateLicenseKey('free');
    }

    // FREE tier has no expiry
    $stmt = $db->prepare("
        UPDATE users SET
            status = 'active',
            package = 'free',
            package_expiry = NULL,
            license_key = ?,
            verified_by = ?,
            verified_at = NOW()
        WHERE id = ?
    ");

    $success = $stmt->execute([$license_key, $admin_id, $user_id]);

    if ($success) {
        // Get user email for notification
        $user = getUserById($user_id);
        if ($user) {
            // Create notification
            createNotification($user_id, 'verification', 'Akun Anda telah diverifikasi! License Key: ' . $license_key);
            logActivity($admin_id, 'verify_user', 'Verified user ID: ' . $user_id . ' with key: ' . $license_key);
        }
    }

    return $success ? $license_key : false;
}

// Reject user
function rejectUser($user_id, $reason_code, $custom_reason = '') {
    $db = getDBConnection();
    if (!$db) return false;

    $reasons = [
        'R01' => 'ID tidak ditemukan / ID not found',
        'R02' => 'Tidak terdaftar via link afiliasi resmi / Not registered via official affiliate',
        'R03' => 'Deposit di bawah $10 / Deposit below $10',
        'R04' => 'ID sudah digunakan akun lain / ID already used by another account',
        'R05' => 'Data tidak lengkap / Incomplete data',
        'R06' => 'Akun OlympTrade tidak aktif / OlympTrade account inactive',
        'R07' => 'Negara tidak sesuai / Country mismatch',
        'R08' => 'Screenshot tidak valid / Invalid screenshot',
        'R09' => 'Duplikat akun terdeteksi / Duplicate account detected',
        'R10' => $custom_reason
    ];

    $reason = $reasons[$reason_code] ?? $custom_reason;

    $stmt = $db->prepare("UPDATE users SET status = 'rejected', rejection_reason = ? WHERE id = ?");
    return $stmt->execute([$reason, $user_id]);
}

/**
 * Subscription Functions
 */

// Get package details - Updated for v3.0
function getPackageDetails($package) {
    $packages = [
        'free' => [
            'name' => 'FREE',
            'price' => 0,
            'price_display' => 'GRATIS',
            'strategies' => 2,
            'strategy_ids' => ['8', '9'],
            'history_days' => 30,
            'features' => [
                '2 Strategi Dasar (#8, #9)',
                'Win rate 55-78%',
                'Statistik dasar',
                'History 30 hari',
                'Telegram support'
            ]
        ],
        'pro' => [
            'name' => 'PRO',
            'price' => PRICE_PRO,
            'price_display' => 'Rp299.000/bulan',
            'strategies' => 4,
            'strategy_ids' => ['6', '7', '8', '9'],
            'history_days' => 90,
            'features' => [
                '4 Strategi (#6, #7, #8, #9)',
                'Win rate hingga 78%',
                'Statistik lengkap',
                'History 90 hari',
                'Priority support',
                'Export CSV'
            ]
        ],
        'elite' => [
            'name' => 'ELITE',
            'price' => PRICE_ELITE,
            'price_display' => 'Rp599.000/bulan',
            'strategies' => 7,
            'strategy_ids' => ['3', '4', '5', '6', '7', '8', '9'],
            'history_days' => 180,
            'features' => [
                '7 Strategi (#3-#9)',
                'Win rate hingga 83%',
                'Statistik premium',
                'History 180 hari',
                'VIP support',
                'Export semua format',
                'Auto-pause system'
            ]
        ],
        'vip' => [
            'name' => 'VIP',
            'price' => PRICE_VIP,
            'price_display' => 'Rp999.000/bulan',
            'strategies' => 10,
            'strategy_ids' => ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'],
            'history_days' => 365,
            'features' => [
                'Semua 10 Strategi',
                'Win rate hingga 91%',
                'Triple RSI (90-91%)',
                'History 1 tahun',
                'Direct owner support',
                'Custom strategies',
                'Beta features access',
                'Priority signal queue'
            ]
        ]
    ];

    return $packages[$package] ?? $packages['free'];
}

/**
 * Leaderboard Functions
 */

// Get leaderboard
function getLeaderboard($period = 'monthly', $country = null, $limit = 50) {
    $db = getDBConnection();
    if (!$db) return [];

    $date_map = [
        'daily' => 1,
        'weekly' => 7,
        'monthly' => 30
    ];

    $days = $date_map[$period] ?? 30;
    $date_limit = date('Y-m-d', strtotime("-$days days"));

    $sql = "
        SELECT
            u.id,
            u.fullname,
            u.country,
            COUNT(t.id) as total_trades,
            SUM(CASE WHEN t.result = 'win' THEN 1 ELSE 0 END) as wins,
            SUM(t.profit_loss) as total_profit,
            AVG(CASE WHEN t.result = 'win' THEN 1 ELSE 0 END) * 100 as win_rate
        FROM users u
        LEFT JOIN trades t ON u.id = t.user_id AND t.created_at >= ?
        WHERE u.status = 'active'
    ";

    $params = [$date_limit];

    if ($country) {
        $sql .= " AND u.country = ?";
        $params[] = $country;
    }

    $sql .= " GROUP BY u.id ORDER BY total_profit DESC LIMIT ?";
    $params[] = $limit;

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Strategy Functions - 10 Strategies dengan Tier Access
 * Berdasarkan materi ZYN Trade System v3.0
 */

// Get all strategies with enhanced metadata
function getAllStrategies() {
    return [
        [
            'id' => '1',
            'name' => 'ORACLE-PRIME',
            'subtitle' => 'Triple RSI Filter',
            'tier' => 'VIP',
            'risk' => 'Low',
            'win_rate' => '90-91%',
            'description' => 'RSI 7 + RSI 14 + RSI 21 kombinasi untuk akurasi TERTINGGI. Signal jarang tapi SUPER AKURAT!',
            'icon' => 'crown',
            'indicators' => ['RSI 7', 'RSI 14', 'RSI 21'],
            'best_timeframe' => '15M-1H',
            'frequency' => 'rare',
            'frequency_label' => '💎 JARANG',
            'frequency_desc' => '1-3 signal/hari',
            'signals_per_day' => '1-3',
            'rating' => 5.0,
            'users' => 89,
            'badge' => '🏆 HIGHEST WIN RATE',
            'badge_color' => 'warning'
        ],
        [
            'id' => '2',
            'name' => 'NEXUS-WAVE',
            'subtitle' => 'Structured MACD',
            'tier' => 'VIP',
            'risk' => 'Low-Medium',
            'win_rate' => '87%',
            'description' => 'MACD dengan struktur entry yang ketat. Cross di zona berlawanan = momentum reversal kuat!',
            'icon' => 'layer-group',
            'indicators' => ['MACD', 'Signal Line', 'EMA 200'],
            'best_timeframe' => '15M-30M',
            'frequency' => 'rare',
            'frequency_label' => '💎 JARANG',
            'frequency_desc' => '2-5 signal/hari',
            'signals_per_day' => '2-5',
            'rating' => 4.9,
            'users' => 76,
            'badge' => '',
            'badge_color' => ''
        ],
        [
            'id' => '3',
            'name' => 'STEALTH-MODE',
            'subtitle' => 'Williams %R Reversal',
            'tier' => 'ELITE',
            'risk' => 'Medium',
            'win_rate' => '81%',
            'description' => 'Williams %R period 2 untuk deteksi extreme oversold/overbought. Perform LEBIH BAIK dari RSI!',
            'icon' => 'exchange-alt',
            'indicators' => ['Williams %R'],
            'best_timeframe' => '15M-30M',
            'frequency' => 'medium',
            'frequency_label' => '⚡ SEDANG',
            'frequency_desc' => '5-10 signal/hari',
            'signals_per_day' => '5-10',
            'rating' => 4.8,
            'users' => 203,
            'badge' => '🔥 BEST SELLER',
            'badge_color' => 'danger'
        ],
        [
            'id' => '4',
            'name' => 'PHOENIX-X1',
            'subtitle' => 'Connors RSI-2',
            'tier' => 'ELITE',
            'risk' => 'Medium',
            'win_rate' => '75-83%',
            'description' => 'RSI periode 2 ultra-short untuk deteksi extreme CEPAT. Level 5 & 95 sangat akurat!',
            'icon' => 'bolt',
            'indicators' => ['RSI 2', 'SMA 200', 'SMA 5'],
            'best_timeframe' => '5M-15M',
            'frequency' => 'medium',
            'frequency_label' => '⚡ SEDANG',
            'frequency_desc' => '5-10 signal/hari',
            'signals_per_day' => '5-10',
            'rating' => 4.8,
            'users' => 178,
            'badge' => '',
            'badge_color' => ''
        ],
        [
            'id' => '5',
            'name' => 'VORTEX-PRO',
            'subtitle' => 'MACD + Bollinger Bands',
            'tier' => 'ELITE',
            'risk' => 'Medium',
            'win_rate' => '78%',
            'description' => 'BB identifikasi support/resistance dinamis + MACD konfirmasi momentum reversal.',
            'icon' => 'chart-area',
            'indicators' => ['MACD', 'Bollinger Bands'],
            'best_timeframe' => '15M-30M',
            'frequency' => 'medium',
            'frequency_label' => '⚡ SEDANG',
            'frequency_desc' => '5-12 signal/hari',
            'signals_per_day' => '5-12',
            'rating' => 4.7,
            'users' => 167,
            'badge' => '',
            'badge_color' => ''
        ],
        [
            'id' => '6',
            'name' => 'TITAN-PULSE',
            'subtitle' => 'MACD + RSI Combo',
            'tier' => 'PRO',
            'risk' => 'Medium',
            'win_rate' => '73%',
            'description' => 'MACD crossover dengan RSI filter di atas/bawah 50 untuk konfirmasi momentum.',
            'icon' => 'sync-alt',
            'indicators' => ['MACD', 'RSI 14'],
            'best_timeframe' => '15M-30M',
            'frequency' => 'medium',
            'frequency_label' => '⚡ SEDANG',
            'frequency_desc' => '8-15 signal/hari',
            'signals_per_day' => '8-15',
            'rating' => 4.5,
            'users' => 156,
            'badge' => '',
            'badge_color' => ''
        ],
        [
            'id' => '7',
            'name' => 'SHADOW-EDGE',
            'subtitle' => 'Stochastic RSI + MACD',
            'tier' => 'PRO',
            'risk' => 'Medium-High',
            'win_rate' => '73%',
            'description' => 'Stochastic RSI dengan konfirmasi MACD untuk timing entry yang presisi.',
            'icon' => 'random',
            'indicators' => ['Stochastic RSI', 'MACD'],
            'best_timeframe' => '15M-30M',
            'frequency' => 'medium',
            'frequency_label' => '⚡ SEDANG',
            'frequency_desc' => '8-15 signal/hari',
            'signals_per_day' => '8-15',
            'rating' => 4.4,
            'users' => 142,
            'badge' => '',
            'badge_color' => ''
        ],
        [
            'id' => '8',
            'name' => 'BLITZ-SIGNAL',
            'subtitle' => 'BB + RSI Standard',
            'tier' => 'FREE',
            'risk' => 'Medium',
            'win_rate' => '60-78%',
            'description' => 'Bollinger Bands bounce dengan RSI konfirmasi. Cocok untuk trading aktif!',
            'icon' => 'chart-line',
            'indicators' => ['Bollinger Bands', 'RSI 14'],
            'best_timeframe' => '15M-1H',
            'frequency' => 'frequent',
            'frequency_label' => '🔥 SERING',
            'frequency_desc' => '15-30 signal/hari',
            'signals_per_day' => '15-30',
            'rating' => 4.2,
            'users' => 512,
            'badge' => '🆓 GRATIS',
            'badge_color' => 'success'
        ],
        [
            'id' => '9',
            'name' => 'APEX-HUNTER',
            'subtitle' => 'RSI Divergence',
            'tier' => 'FREE',
            'risk' => 'Medium-High',
            'win_rate' => '55-86%',
            'description' => 'Deteksi divergence RSI untuk reversal. Signal bervariasi tapi bisa sangat akurat!',
            'icon' => 'code-branch',
            'indicators' => ['RSI 14', 'Price Action'],
            'best_timeframe' => '30M-1H',
            'frequency' => 'frequent',
            'frequency_label' => '🔥 SERING',
            'frequency_desc' => '15-25 signal/hari',
            'signals_per_day' => '15-25',
            'rating' => 4.1,
            'users' => 489,
            'badge' => '🆓 GRATIS',
            'badge_color' => 'success'
        ],
        [
            'id' => '10',
            'name' => 'QUANTUM-FLOW',
            'subtitle' => 'Multi-Indicator Fusion',
            'tier' => 'VIP',
            'risk' => 'Low',
            'win_rate' => '80-90%',
            'description' => 'Kombinasi 5+ indikator untuk konfirmasi maksimal. Signal jarang tapi SUPER AKURAT!',
            'icon' => 'sitemap',
            'indicators' => ['RSI', 'MACD', 'BB', 'Stochastic', 'Williams %R'],
            'best_timeframe' => '15M-30M',
            'frequency' => 'rare',
            'frequency_label' => '💎 JARANG',
            'frequency_desc' => '1-5 signal/hari',
            'signals_per_day' => '1-5',
            'rating' => 4.9,
            'users' => 54,
            'badge' => '🎯 SUPER AKURAT',
            'badge_color' => 'info'
        ]
    ];
}

// Helper function to render star rating
function renderStarRating($rating) {
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5;
    $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);

    $html = '';
    for ($i = 0; $i < $fullStars; $i++) {
        $html .= '<i class="fas fa-star text-warning"></i>';
    }
    if ($halfStar) {
        $html .= '<i class="fas fa-star-half-alt text-warning"></i>';
    }
    for ($i = 0; $i < $emptyStars; $i++) {
        $html .= '<i class="far fa-star text-warning"></i>';
    }
    return $html;
}

// Get frequency class for styling
function getFrequencyClass($frequency) {
    switch ($frequency) {
        case 'rare': return 'frequency-rare';
        case 'medium': return 'frequency-medium';
        case 'frequent': return 'frequency-frequent';
        default: return 'frequency-medium';
    }
}

// Get strategies by tier
function getStrategiesByTier($tier) {
    $strategies = getAllStrategies();
    $accessible = [];

    $tierAccess = [
        'FREE' => ['8', '9'],
        'PRO' => ['6', '7', '8', '9'],
        'ELITE' => ['3', '4', '5', '6', '7', '8', '9'],
        'VIP' => ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10']
    ];

    $allowedIds = $tierAccess[$tier] ?? $tierAccess['FREE'];

    foreach ($strategies as $strategy) {
        if (in_array($strategy['id'], $allowedIds)) {
            $accessible[] = $strategy;
        }
    }

    return $accessible;
}

// Check if user can access strategy
function canAccessStrategy($user_package, $strategy_id) {
    $tierAccess = [
        'free' => ['8', '9'],
        'pro' => ['6', '7', '8', '9'],
        'elite' => ['3', '4', '5', '6', '7', '8', '9'],
        'vip' => ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10']
    ];

    $package = strtolower($user_package);
    $allowedIds = $tierAccess[$package] ?? $tierAccess['free'];

    return in_array($strategy_id, $allowedIds);
}

// Get strategy by ID
function getStrategyById($id) {
    $strategies = getAllStrategies();
    foreach ($strategies as $strategy) {
        if ($strategy['id'] === $id) {
            return $strategy;
        }
    }
    return null;
}

/**
 * Notification Functions
 */

// Create notification
function createNotification($user_id, $type, $message) {
    $db = getDBConnection();
    if (!$db) return false;

    $stmt = $db->prepare("INSERT INTO notifications (user_id, type, message, created_at) VALUES (?, ?, ?, NOW())");
    return $stmt->execute([$user_id, $type, $message]);
}

// Get user notifications
function getUserNotifications($user_id, $unread_only = false, $limit = 20) {
    $db = getDBConnection();
    if (!$db) return [];

    $sql = "SELECT * FROM notifications WHERE user_id = ?";
    if ($unread_only) {
        $sql .= " AND is_read = 0";
    }
    $sql .= " ORDER BY created_at DESC LIMIT ?";

    $stmt = $db->prepare($sql);
    $stmt->execute([$user_id, $limit]);
    return $stmt->fetchAll();
}

// Mark notification as read
function markNotificationRead($notification_id, $user_id) {
    $db = getDBConnection();
    if (!$db) return false;

    $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    return $stmt->execute([$notification_id, $user_id]);
}

/**
 * Affiliate Link Functions
 */

// Get affiliate link by language code
function getAffiliateLink($lang_code = 'en') {
    $links = AFFILIATE_LINKS;
    return $links[$lang_code] ?? $links['en'];
}

// Detect user language and get appropriate affiliate link
function getLocalizedAffiliateLink() {
    $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'en', 0, 2);
    $lang = strtolower($lang);
    return getAffiliateLink($lang);
}

/**
 * Money Management Functions
 */

// Get money management types
function getMoneyManagementTypes() {
    return MONEY_MANAGEMENT_TYPES;
}

// Calculate martingale amount
function calculateMartingaleAmount($base_amount, $current_step) {
    $max_steps = MONEY_MANAGEMENT_TYPES['martingale']['max_steps'];
    $multiplier = MONEY_MANAGEMENT_TYPES['martingale']['multiplier'];

    if ($current_step > $max_steps) {
        $current_step = $max_steps;
    }

    return $base_amount * pow($multiplier, $current_step);
}

// Get martingale sequence
function getMartingaleSequence($base_amount) {
    $sequence = [$base_amount];
    $max_steps = MONEY_MANAGEMENT_TYPES['martingale']['max_steps'];
    $multiplier = MONEY_MANAGEMENT_TYPES['martingale']['multiplier'];

    for ($i = 1; $i <= $max_steps; $i++) {
        $sequence[] = $base_amount * pow($multiplier, $i);
    }

    return $sequence;
}

/**
 * Multi-Timeframe Amount Functions
 */

// Get timeframe amount settings
function getTimeframeAmountSettings($timeframe = null) {
    $settings = TIMEFRAME_AMOUNTS;

    if ($timeframe) {
        return $settings[$timeframe] ?? $settings['5M'];
    }

    return $settings;
}

// Validate amount for timeframe
function validateAmountForTimeframe($amount, $timeframe) {
    $settings = getTimeframeAmountSettings($timeframe);

    if ($amount < $settings['min']) {
        return ['valid' => false, 'message' => "Minimum untuk {$timeframe}: Rp" . number_format($settings['min'], 0, ',', '.')];
    }

    if ($amount > $settings['max']) {
        return ['valid' => false, 'message' => "Maximum untuk {$timeframe}: Rp" . number_format($settings['max'], 0, ',', '.')];
    }

    return ['valid' => true, 'message' => 'OK'];
}

/**
 * Weekend Auto-Off Functions
 */

// Check if today is weekend
function isWeekend() {
    $dayOfWeek = (int)date('w'); // 0 = Sunday, 6 = Saturday
    return in_array($dayOfWeek, WEEKEND_DAYS);
}

// Check if trading is allowed (not weekend when auto-off is enabled)
function isTradingAllowed() {
    if (!WEEKEND_AUTO_OFF) {
        return true;
    }
    return !isWeekend();
}

// Get next trading day
function getNextTradingDay() {
    $date = new DateTime();

    while (true) {
        $date->modify('+1 day');
        $dayOfWeek = (int)$date->format('w');

        if (!in_array($dayOfWeek, WEEKEND_DAYS)) {
            return $date->format('l, d M Y'); // e.g., "Monday, 18 Dec 2025"
        }
    }
}

// Get weekend message
function getWeekendMessage() {
    if (!isWeekend()) {
        return null;
    }

    $nextDay = getNextTradingDay();
    return "Robot dinonaktifkan otomatis karena weekend. Trading dilanjutkan pada: {$nextDay}";
}

/**
 * Signal Frequency Functions
 */

// Get signal frequency for strategy
function getSignalFrequency($strategy_id) {
    $frequencies = SIGNAL_FREQUENCY;
    return $frequencies[$strategy_id] ?? ['min' => 0, 'max' => 0, 'avg' => 0];
}

// Get estimated signals per hour text
function getSignalFrequencyText($strategy_id) {
    $freq = getSignalFrequency($strategy_id);
    return "{$freq['min']}-{$freq['max']} signal/jam (avg: {$freq['avg']})";
}

// Get total estimated signals for active strategies
function getTotalEstimatedSignals($strategy_ids) {
    $total_min = 0;
    $total_max = 0;
    $total_avg = 0;

    foreach ($strategy_ids as $id) {
        $freq = getSignalFrequency($id);
        $total_min += $freq['min'];
        $total_max += $freq['max'];
        $total_avg += $freq['avg'];
    }

    return [
        'min' => $total_min,
        'max' => $total_max,
        'avg' => $total_avg
    ];
}

/**
 * Schedule Mode Functions
 */

// Get schedule modes
function getScheduleModes() {
    return SCHEDULE_MODES;
}

// Check if current time is within schedule
function isWithinSchedule($schedule_mode, $settings = []) {
    $now = new DateTime();
    $currentTime = $now->format('H:i');

    switch ($schedule_mode) {
        case 'auto_24h':
            return true;

        case 'best_hours':
            $start = SCHEDULE_MODES['best_hours']['start'];
            $end = SCHEDULE_MODES['best_hours']['end'];
            return $currentTime >= $start && $currentTime <= $end;

        case 'custom_single':
            if (isset($settings['start']) && isset($settings['end'])) {
                return $currentTime >= $settings['start'] && $currentTime <= $settings['end'];
            }
            return false;

        case 'multi_session':
            if (isset($settings['sessions']) && is_array($settings['sessions'])) {
                foreach ($settings['sessions'] as $session) {
                    if ($currentTime >= $session['start'] && $currentTime <= $session['end']) {
                        return true;
                    }
                }
            }
            return false;

        case 'per_day':
            $dayOfWeek = strtolower($now->format('l')); // monday, tuesday, etc.
            if (isset($settings[$dayOfWeek])) {
                $daySettings = $settings[$dayOfWeek];
                if ($daySettings['enabled']) {
                    return $currentTime >= $daySettings['start'] && $currentTime <= $daySettings['end'];
                }
            }
            return false;

        default:
            return false;
    }
}

// Get schedule status message
function getScheduleStatusMessage($schedule_mode, $settings = []) {
    if (isWithinSchedule($schedule_mode, $settings)) {
        return ['status' => 'active', 'message' => 'Dalam jadwal aktif'];
    }

    // Calculate next active time based on mode
    switch ($schedule_mode) {
        case 'best_hours':
            $start = SCHEDULE_MODES['best_hours']['start'];
            return ['status' => 'waiting', 'message' => "Menunggu jam trading ({$start} WIB)"];

        case 'custom_single':
            $start = $settings['start'] ?? '00:00';
            return ['status' => 'waiting', 'message' => "Menunggu jadwal ({$start} WIB)"];

        default:
            return ['status' => 'inactive', 'message' => 'Di luar jadwal'];
    }
}

/**
 * Real Account Validation Functions
 */

// Check if account is real (not demo)
function isRealAccount($olymptrade_id) {
    // Real OlympTrade IDs typically have specific patterns
    // This is a basic validation - actual validation requires API check

    if (empty($olymptrade_id)) {
        return false;
    }

    // Must be numeric
    if (!is_numeric($olymptrade_id)) {
        return false;
    }

    // Must be reasonable length (6-12 digits)
    $length = strlen($olymptrade_id);
    if ($length < 6 || $length > 12) {
        return false;
    }

    return true;
}

// Get real account warning message
function getRealAccountWarning() {
    if (!REAL_ACCOUNT_ONLY) {
        return null;
    }

    return "ZYN Trade System hanya mendukung akun REAL. Akun demo tidak didukung untuk menjaga integritas statistik dan performa.";
}

/**
 * Auto-Pause System Functions
 */

// Check auto-pause conditions
function checkAutoPause($user_id, $current_profit, $current_loss) {
    $settings = getRobotSettings($user_id);

    if (!$settings) {
        return ['should_pause' => false];
    }

    $take_profit = $settings['take_profit_target'] ?? 0;
    $max_loss = $settings['max_loss_limit'] ?? 0;

    // Check take profit
    if ($take_profit > 0 && $current_profit >= $take_profit) {
        return [
            'should_pause' => true,
            'reason' => 'take_profit',
            'message' => "Target profit tercapai! (Rp" . number_format($current_profit, 0, ',', '.') . ")",
            'recommendation' => 'Selamat! Pertimbangkan untuk withdraw sebagian profit.'
        ];
    }

    // Check max loss
    if ($max_loss > 0 && $current_loss >= $max_loss) {
        return [
            'should_pause' => true,
            'reason' => 'max_loss',
            'message' => "Batas loss tercapai! (Rp" . number_format($current_loss, 0, ',', '.') . ")",
            'recommendation' => 'Robot dipause untuk melindungi modal. Evaluasi strategi sebelum melanjutkan.'
        ];
    }

    return ['should_pause' => false];
}

// Trigger auto-pause
function triggerAutoPause($user_id, $reason) {
    $db = getDBConnection();
    if (!$db) return false;

    $stmt = $db->prepare("
        UPDATE robot_settings SET
            robot_enabled = 0,
            auto_pause_triggered = 1,
            auto_pause_reason = ?,
            auto_pause_time = NOW()
        WHERE user_id = ?
    ");

    $success = $stmt->execute([$reason, $user_id]);

    if ($success) {
        // Create notification
        $message = $reason === 'take_profit'
            ? 'Robot di-pause: Target profit tercapai!'
            : 'Robot di-pause: Batas maksimum loss tercapai';
        createNotification($user_id, 'auto_pause', $message);
        logActivity($user_id, 'auto_pause', "Auto-pause triggered: {$reason}");
    }

    return $success;
}

// Reset auto-pause
function resetAutoPause($user_id) {
    $db = getDBConnection();
    if (!$db) return false;

    $stmt = $db->prepare("
        UPDATE robot_settings SET
            auto_pause_triggered = 0,
            auto_pause_reason = NULL,
            auto_pause_time = NULL
        WHERE user_id = ?
    ");

    return $stmt->execute([$user_id]);
}

/**
 * Country List
 */
function getCountryList() {
    return [
        'ID' => 'Indonesia',
        'MY' => 'Malaysia',
        'PH' => 'Philippines',
        'TH' => 'Thailand',
        'VN' => 'Vietnam',
        'IN' => 'India',
        'PK' => 'Pakistan',
        'BD' => 'Bangladesh',
        'BR' => 'Brazil',
        'MX' => 'Mexico',
        'CO' => 'Colombia',
        'AR' => 'Argentina',
        'PE' => 'Peru',
        'CL' => 'Chile',
        'NG' => 'Nigeria',
        'KE' => 'Kenya',
        'ZA' => 'South Africa',
        'EG' => 'Egypt',
        'TR' => 'Turkey',
        'RU' => 'Russia',
        'UA' => 'Ukraine',
        'PL' => 'Poland',
        'ES' => 'Spain',
        'PT' => 'Portugal',
        'IT' => 'Italy',
        'DE' => 'Germany',
        'FR' => 'France',
        'GB' => 'United Kingdom',
        'US' => 'United States',
        'CA' => 'Canada',
        'AU' => 'Australia',
        'OTHER' => 'Other'
    ];
}

/**
 * Leaderboard Helper Functions
 */

// Get user's rank
function getUserRank($user_id, $period = 'monthly') {
    $db = getDBConnection();
    if (!$db) return null;

    $date_map = [
        'daily' => 1,
        'weekly' => 7,
        'monthly' => 30
    ];

    $days = $date_map[$period] ?? 30;
    $date_limit = date('Y-m-d', strtotime("-$days days"));

    // Get all users ranked by profit
    $sql = "
        SELECT
            u.id as user_id,
            u.fullname as username,
            u.country,
            u.package,
            COUNT(t.id) as total_trades,
            SUM(CASE WHEN t.result = 'win' THEN 1 ELSE 0 END) as wins,
            COALESCE(SUM(t.profit_loss), 0) as total_profit,
            COALESCE(AVG(CASE WHEN t.result IN ('win', 'loss') THEN CASE WHEN t.result = 'win' THEN 1 ELSE 0 END END) * 100, 0) as win_rate
        FROM users u
        LEFT JOIN trades t ON u.id = t.user_id AND t.created_at >= ?
        WHERE u.status = 'active'
        GROUP BY u.id
        HAVING total_trades > 0
        ORDER BY total_profit DESC
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute([$date_limit]);
    $rankings = $stmt->fetchAll();

    // Find user's rank
    foreach ($rankings as $index => $user) {
        if ($user['user_id'] == $user_id) {
            return [
                'rank' => $index + 1,
                'total_profit' => $user['total_profit'],
                'win_rate' => $user['win_rate'],
                'total_trades' => $user['total_trades']
            ];
        }
    }

    return null;
}

// Get active countries (countries with active users)
function getActiveCountries() {
    $db = getDBConnection();
    if (!$db) return [];

    $stmt = $db->prepare("
        SELECT DISTINCT country, COUNT(*) as user_count
        FROM users
        WHERE status = 'active' AND country IS NOT NULL AND country != ''
        GROUP BY country
        ORDER BY user_count DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

// Get country flag emoji
function getCountryFlag($country) {
    $flags = [
        'Indonesia' => '🇮🇩',
        'Malaysia' => '🇲🇾',
        'Philippines' => '🇵🇭',
        'Thailand' => '🇹🇭',
        'Vietnam' => '🇻🇳',
        'India' => '🇮🇳',
        'Pakistan' => '🇵🇰',
        'Bangladesh' => '🇧🇩',
        'Brazil' => '🇧🇷',
        'Mexico' => '🇲🇽',
        'Colombia' => '🇨🇴',
        'Argentina' => '🇦🇷',
        'Peru' => '🇵🇪',
        'Chile' => '🇨🇱',
        'Nigeria' => '🇳🇬',
        'Kenya' => '🇰🇪',
        'South Africa' => '🇿🇦',
        'Egypt' => '🇪🇬',
        'Turkey' => '🇹🇷',
        'Russia' => '🇷🇺',
        'Ukraine' => '🇺🇦',
        'Poland' => '🇵🇱',
        'Spain' => '🇪🇸',
        'Portugal' => '🇵🇹',
        'Italy' => '🇮🇹',
        'Germany' => '🇩🇪',
        'France' => '🇫🇷',
        'United Kingdom' => '🇬🇧',
        'United States' => '🇺🇸',
        'Canada' => '🇨🇦',
        'Australia' => '🇦🇺'
    ];

    return $flags[$country] ?? '🌍';
}

// Get rank class for styling
function getRankClass($rank) {
    if ($rank == 1) return 'gold';
    if ($rank == 2) return 'silver';
    if ($rank == 3) return 'bronze';
    return '';
}

// Mask username for privacy
function maskUsername($username) {
    if (strlen($username) <= 3) {
        return $username[0] . '***';
    }
    return substr($username, 0, 3) . '***' . substr($username, -1);
}

// Get win rate badge class
function getWinRateBadgeClass($winRate) {
    if ($winRate >= 80) return 'bg-success';
    if ($winRate >= 70) return 'bg-info';
    if ($winRate >= 60) return 'bg-warning text-dark';
    return 'bg-secondary';
}

// Get package badge color
function getPackageBadgeColor($package) {
    $colors = [
        'free' => 'secondary',
        'pro' => 'info',
        'elite' => 'warning',
        'vip' => 'primary'
    ];
    return $colors[$package] ?? 'secondary';
}

/**
 * Statistics Functions
 */

// Get detailed user statistics
function getDetailedStats($user_id, $days = 30) {
    $db = getDBConnection();
    if (!$db) return [];

    $date_limit = date('Y-m-d', strtotime("-$days days"));

    // Overall stats
    $stmt = $db->prepare("
        SELECT
            COUNT(*) as total_trades,
            SUM(CASE WHEN result = 'win' THEN 1 ELSE 0 END) as wins,
            SUM(CASE WHEN result = 'loss' THEN 1 ELSE 0 END) as losses,
            COALESCE(SUM(profit_loss), 0) as total_pnl,
            COALESCE(SUM(CASE WHEN profit_loss > 0 THEN profit_loss ELSE 0 END), 0) as total_profit,
            COALESCE(SUM(CASE WHEN profit_loss < 0 THEN ABS(profit_loss) ELSE 0 END), 0) as total_loss,
            COALESCE(AVG(CASE WHEN result IN ('win', 'loss') THEN CASE WHEN result = 'win' THEN 1 ELSE 0 END END) * 100, 0) as win_rate,
            COALESCE(AVG(profit_loss), 0) as avg_trade,
            COALESCE(MAX(profit_loss), 0) as best_trade,
            COALESCE(MIN(profit_loss), 0) as worst_trade
        FROM trades
        WHERE user_id = ? AND created_at >= ?
    ");
    $stmt->execute([$user_id, $date_limit]);
    $overall = $stmt->fetch();

    // Daily breakdown
    $stmt = $db->prepare("
        SELECT
            DATE(created_at) as trade_date,
            COUNT(*) as trades,
            SUM(CASE WHEN result = 'win' THEN 1 ELSE 0 END) as wins,
            SUM(profit_loss) as pnl
        FROM trades
        WHERE user_id = ? AND created_at >= ?
        GROUP BY DATE(created_at)
        ORDER BY trade_date DESC
    ");
    $stmt->execute([$user_id, $date_limit]);
    $daily = $stmt->fetchAll();

    // Strategy breakdown
    $stmt = $db->prepare("
        SELECT
            strategy,
            strategy_id,
            COUNT(*) as trades,
            SUM(CASE WHEN result = 'win' THEN 1 ELSE 0 END) as wins,
            SUM(profit_loss) as pnl,
            AVG(CASE WHEN result IN ('win', 'loss') THEN CASE WHEN result = 'win' THEN 1 ELSE 0 END END) * 100 as win_rate
        FROM trades
        WHERE user_id = ? AND created_at >= ?
        GROUP BY strategy, strategy_id
        ORDER BY pnl DESC
    ");
    $stmt->execute([$user_id, $date_limit]);
    $byStrategy = $stmt->fetchAll();

    // Market breakdown
    $stmt = $db->prepare("
        SELECT
            market,
            COUNT(*) as trades,
            SUM(CASE WHEN result = 'win' THEN 1 ELSE 0 END) as wins,
            SUM(profit_loss) as pnl,
            AVG(CASE WHEN result IN ('win', 'loss') THEN CASE WHEN result = 'win' THEN 1 ELSE 0 END END) * 100 as win_rate
        FROM trades
        WHERE user_id = ? AND created_at >= ?
        GROUP BY market
        ORDER BY pnl DESC
    ");
    $stmt->execute([$user_id, $date_limit]);
    $byMarket = $stmt->fetchAll();

    // Timeframe breakdown
    $stmt = $db->prepare("
        SELECT
            timeframe,
            COUNT(*) as trades,
            SUM(CASE WHEN result = 'win' THEN 1 ELSE 0 END) as wins,
            SUM(profit_loss) as pnl,
            AVG(CASE WHEN result IN ('win', 'loss') THEN CASE WHEN result = 'win' THEN 1 ELSE 0 END END) * 100 as win_rate
        FROM trades
        WHERE user_id = ? AND created_at >= ?
        GROUP BY timeframe
        ORDER BY pnl DESC
    ");
    $stmt->execute([$user_id, $date_limit]);
    $byTimeframe = $stmt->fetchAll();

    // Streak calculation
    $stmt = $db->prepare("
        SELECT result FROM trades
        WHERE user_id = ? AND created_at >= ?
        ORDER BY created_at DESC
    ");
    $stmt->execute([$user_id, $date_limit]);
    $results = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $currentStreak = 0;
    $maxWinStreak = 0;
    $maxLossStreak = 0;
    $tempStreak = 0;
    $lastResult = null;

    foreach ($results as $result) {
        if ($result === $lastResult) {
            $tempStreak++;
        } else {
            if ($lastResult === 'win' && $tempStreak > $maxWinStreak) {
                $maxWinStreak = $tempStreak;
            }
            if ($lastResult === 'loss' && $tempStreak > $maxLossStreak) {
                $maxLossStreak = $tempStreak;
            }
            $tempStreak = 1;
            $lastResult = $result;
        }
    }

    // Check final streak
    if ($lastResult === 'win' && $tempStreak > $maxWinStreak) {
        $maxWinStreak = $tempStreak;
    }
    if ($lastResult === 'loss' && $tempStreak > $maxLossStreak) {
        $maxLossStreak = $tempStreak;
    }

    // Current streak
    if (!empty($results)) {
        $currentResult = $results[0];
        $currentStreak = 0;
        foreach ($results as $result) {
            if ($result === $currentResult) {
                $currentStreak++;
            } else {
                break;
            }
        }
        if ($currentResult === 'loss') {
            $currentStreak = -$currentStreak;
        }
    }

    return [
        'overall' => $overall,
        'daily' => $daily,
        'by_strategy' => $byStrategy,
        'by_market' => $byMarket,
        'by_timeframe' => $byTimeframe,
        'streaks' => [
            'current' => $currentStreak,
            'max_win' => $maxWinStreak,
            'max_loss' => $maxLossStreak
        ]
    ];
}

// Get trading calendar data
function getTradingCalendar($user_id, $month = null, $year = null) {
    $db = getDBConnection();
    if (!$db) return [];

    $month = $month ?? date('n');
    $year = $year ?? date('Y');

    $startDate = "$year-$month-01";
    $endDate = date('Y-m-t', strtotime($startDate));

    $stmt = $db->prepare("
        SELECT
            DATE(created_at) as trade_date,
            COUNT(*) as trades,
            SUM(CASE WHEN result = 'win' THEN 1 ELSE 0 END) as wins,
            SUM(CASE WHEN result = 'loss' THEN 1 ELSE 0 END) as losses,
            SUM(profit_loss) as pnl
        FROM trades
        WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ?
        GROUP BY DATE(created_at)
    ");
    $stmt->execute([$user_id, $startDate, $endDate]);
    $trades = $stmt->fetchAll();

    $calendar = [];
    foreach ($trades as $day) {
        $calendar[$day['trade_date']] = [
            'trades' => $day['trades'],
            'wins' => $day['wins'],
            'losses' => $day['losses'],
            'pnl' => $day['pnl'],
            'status' => $day['pnl'] >= 0 ? 'profit' : 'loss'
        ];
    }

    return $calendar;
}

/**
 * Performance Score Functions
 */

// Calculate performance score (0-100)
function calculatePerformanceScore($user_id, $days = 30) {
    $stats = getDetailedStats($user_id, $days);

    if (empty($stats['overall']) || $stats['overall']['total_trades'] == 0) {
        return [
            'score' => 0,
            'level' => 'Unranked',
            'breakdown' => []
        ];
    }

    $overall = $stats['overall'];

    // Win Rate Score (max 35 points)
    $winRateScore = min(35, ($overall['win_rate'] / 100) * 40);

    // Consistency Score - based on daily trading (max 25 points)
    $tradingDays = count($stats['daily']);
    $maxDays = min($days, 22); // Assuming 22 trading days per month
    $consistencyScore = min(25, ($tradingDays / $maxDays) * 30);

    // Profit Factor Score (max 20 points)
    $profitFactor = $overall['total_loss'] > 0 ? $overall['total_profit'] / $overall['total_loss'] : 2;
    $profitFactorScore = min(20, $profitFactor * 8);

    // Discipline Score - based on average trade size variance (max 10 points)
    $disciplineScore = 8; // Default good discipline

    // Streak Bonus (max 10 points)
    $streakBonus = min(10, $stats['streaks']['max_win'] * 1.5);

    // Total Score
    $totalScore = round($winRateScore + $consistencyScore + $profitFactorScore + $disciplineScore + $streakBonus);
    $totalScore = min(100, $totalScore);

    // Determine Level
    $levels = [
        90 => 'Diamond',
        80 => 'Platinum',
        70 => 'Gold',
        60 => 'Silver',
        50 => 'Bronze',
        0 => 'Beginner'
    ];

    $level = 'Beginner';
    foreach ($levels as $threshold => $name) {
        if ($totalScore >= $threshold) {
            $level = $name;
            break;
        }
    }

    return [
        'score' => $totalScore,
        'level' => $level,
        'breakdown' => [
            'win_rate' => round($winRateScore),
            'consistency' => round($consistencyScore),
            'profit_factor' => round($profitFactorScore),
            'discipline' => round($disciplineScore),
            'streak_bonus' => round($streakBonus)
        ]
    ];
}

/**
 * Achievement System Functions
 */

// Get all achievements
function getAchievements() {
    return [
        'first_blood' => [
            'id' => 'first_blood',
            'name' => 'First Blood',
            'description' => 'Selesaikan trade pertama',
            'icon' => 'fa-play',
            'color' => 'success'
        ],
        'win_10' => [
            'id' => 'win_10',
            'name' => '10 Wins',
            'description' => 'Menangkan 10 trade',
            'icon' => 'fa-trophy',
            'color' => 'warning'
        ],
        'win_50' => [
            'id' => 'win_50',
            'name' => '50 Wins',
            'description' => 'Menangkan 50 trade',
            'icon' => 'fa-medal',
            'color' => 'info'
        ],
        'win_100' => [
            'id' => 'win_100',
            'name' => 'Century',
            'description' => 'Menangkan 100 trade',
            'icon' => 'fa-crown',
            'color' => 'primary'
        ],
        'streak_5' => [
            'id' => 'streak_5',
            'name' => 'Hot Streak',
            'description' => '5 kemenangan berturut-turut',
            'icon' => 'fa-fire',
            'color' => 'danger'
        ],
        'streak_10' => [
            'id' => 'streak_10',
            'name' => 'On Fire',
            'description' => '10 kemenangan berturut-turut',
            'icon' => 'fa-fire-flame-curved',
            'color' => 'danger'
        ],
        'profit_100' => [
            'id' => 'profit_100',
            'name' => 'Profit Hunter',
            'description' => 'Total profit $100+',
            'icon' => 'fa-dollar-sign',
            'color' => 'success'
        ],
        'profit_500' => [
            'id' => 'profit_500',
            'name' => 'Money Maker',
            'description' => 'Total profit $500+',
            'icon' => 'fa-money-bill-wave',
            'color' => 'success'
        ],
        'profit_1000' => [
            'id' => 'profit_1000',
            'name' => 'Profit Master',
            'description' => 'Total profit $1000+',
            'icon' => 'fa-sack-dollar',
            'color' => 'warning'
        ],
        'daily_target_7' => [
            'id' => 'daily_target_7',
            'name' => 'Consistent',
            'description' => 'Capai target harian 7 hari berturut',
            'icon' => 'fa-calendar-check',
            'color' => 'info'
        ],
        'win_rate_80' => [
            'id' => 'win_rate_80',
            'name' => 'Precision',
            'description' => 'Win rate 80%+ (min 20 trades)',
            'icon' => 'fa-bullseye',
            'color' => 'primary'
        ],
        'early_bird' => [
            'id' => 'early_bird',
            'name' => 'Early Bird',
            'description' => 'Trade sebelum jam 7 pagi',
            'icon' => 'fa-sun',
            'color' => 'warning'
        ],
        'night_owl' => [
            'id' => 'night_owl',
            'name' => 'Night Owl',
            'description' => 'Trade setelah jam 10 malam',
            'icon' => 'fa-moon',
            'color' => 'secondary'
        ]
    ];
}

// Get user achievements
function getUserAchievements($user_id) {
    $db = getDBConnection();
    if (!$db) return [];

    $achievements = getAchievements();
    $earned = [];

    // Get user stats
    $stmt = $db->prepare("
        SELECT
            COUNT(*) as total_trades,
            SUM(CASE WHEN result = 'win' THEN 1 ELSE 0 END) as total_wins,
            COALESCE(SUM(CASE WHEN profit_loss > 0 THEN profit_loss ELSE 0 END), 0) as total_profit,
            MIN(TIME(created_at)) as earliest_trade,
            MAX(TIME(created_at)) as latest_trade
        FROM trades WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $stats = $stmt->fetch();

    // Check first blood
    if ($stats['total_trades'] > 0) {
        $earned['first_blood'] = $achievements['first_blood'];
    }

    // Check win milestones
    if ($stats['total_wins'] >= 10) $earned['win_10'] = $achievements['win_10'];
    if ($stats['total_wins'] >= 50) $earned['win_50'] = $achievements['win_50'];
    if ($stats['total_wins'] >= 100) $earned['win_100'] = $achievements['win_100'];

    // Check profit milestones
    if ($stats['total_profit'] >= 100) $earned['profit_100'] = $achievements['profit_100'];
    if ($stats['total_profit'] >= 500) $earned['profit_500'] = $achievements['profit_500'];
    if ($stats['total_profit'] >= 1000) $earned['profit_1000'] = $achievements['profit_1000'];

    // Check time-based achievements
    if ($stats['earliest_trade'] && $stats['earliest_trade'] < '07:00:00') {
        $earned['early_bird'] = $achievements['early_bird'];
    }
    if ($stats['latest_trade'] && $stats['latest_trade'] > '22:00:00') {
        $earned['night_owl'] = $achievements['night_owl'];
    }

    // Check win rate
    if ($stats['total_trades'] >= 20) {
        $winRate = ($stats['total_wins'] / $stats['total_trades']) * 100;
        if ($winRate >= 80) {
            $earned['win_rate_80'] = $achievements['win_rate_80'];
        }
    }

    // Check streak (simplified - would need more complex query for exact streak)
    $detailedStats = getDetailedStats($user_id, 30);
    if ($detailedStats['streaks']['max_win'] >= 5) {
        $earned['streak_5'] = $achievements['streak_5'];
    }
    if ($detailedStats['streaks']['max_win'] >= 10) {
        $earned['streak_10'] = $achievements['streak_10'];
    }

    return $earned;
}

/**
 * Daily Target Functions
 */

// Get user's daily target settings
function getDailyTargetSettings($user_id) {
    $db = getDBConnection();
    if (!$db) return null;

    $stmt = $db->prepare("
        SELECT daily_target_amount, daily_target_auto_stop
        FROM robot_settings WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $settings = $stmt->fetch();

    return [
        'target' => $settings['daily_target_amount'] ?? 50,
        'auto_stop' => $settings['daily_target_auto_stop'] ?? false
    ];
}

// Get today's progress towards daily target
function getDailyTargetProgress($user_id) {
    $db = getDBConnection();
    if (!$db) return null;

    $settings = getDailyTargetSettings($user_id);
    $target = $settings['target'];

    $stmt = $db->prepare("
        SELECT
            COALESCE(SUM(profit_loss), 0) as today_pnl,
            COUNT(*) as today_trades,
            SUM(CASE WHEN result = 'win' THEN 1 ELSE 0 END) as today_wins,
            SUM(CASE WHEN result = 'loss' THEN 1 ELSE 0 END) as today_losses
        FROM trades
        WHERE user_id = ? AND DATE(created_at) = CURDATE()
    ");
    $stmt->execute([$user_id]);
    $today = $stmt->fetch();

    $progress = $target > 0 ? min(100, ($today['today_pnl'] / $target) * 100) : 0;
    $achieved = $today['today_pnl'] >= $target;

    return [
        'target' => $target,
        'current' => $today['today_pnl'],
        'progress' => max(0, $progress),
        'achieved' => $achieved,
        'trades' => $today['today_trades'],
        'wins' => $today['today_wins'],
        'losses' => $today['today_losses'],
        'auto_stop' => $settings['auto_stop']
    ];
}

// Update daily target
function updateDailyTarget($user_id, $amount, $auto_stop = false) {
    $db = getDBConnection();
    if (!$db) return false;

    $stmt = $db->prepare("
        UPDATE robot_settings SET
            daily_target_amount = ?,
            daily_target_auto_stop = ?
        WHERE user_id = ?
    ");

    return $stmt->execute([$amount, $auto_stop ? 1 : 0, $user_id]);
}

/**
 * Performance Level Helper
 */

// Get performance level color for badges
function getPerformanceLevelColor($level) {
    $colors = [
        'Diamond' => 'info',
        'Platinum' => 'light',
        'Gold' => 'warning',
        'Silver' => 'secondary',
        'Bronze' => 'warning',
        'Beginner' => 'dark',
        'Unranked' => 'secondary'
    ];
    return $colors[$level] ?? 'secondary';
}

/**
 * Setup Checklist Functions
 */

// Get user's setup progress
function getSetupProgress($user_id) {
    $db = getDBConnection();
    if (!$db) return [];

    $user = getUserById($user_id);
    $settings = getRobotSettings($user_id);
    $trades = getRecentTrades($user_id, 1);

    $checklist = [
        [
            'id' => 'register',
            'title' => 'Registrasi Akun',
            'description' => 'Buat akun ZYN Trade System',
            'completed' => true, // Always true if they're logged in
            'icon' => 'fa-user-plus'
        ],
        [
            'id' => 'verify',
            'title' => 'Verifikasi Admin',
            'description' => 'Tunggu verifikasi dari admin',
            'completed' => $user['status'] === 'active',
            'icon' => 'fa-check-circle'
        ],
        [
            'id' => 'olymptrade',
            'title' => 'Hubungkan OlympTrade',
            'description' => 'Masukkan ID OlympTrade Anda',
            'completed' => !empty($user['olymptrade_id']),
            'icon' => 'fa-link'
        ],
        [
            'id' => 'strategy',
            'title' => 'Pilih Strategi',
            'description' => 'Pilih strategi trading yang diinginkan',
            'completed' => !empty($settings['active_strategies']),
            'icon' => 'fa-chess'
        ],
        [
            'id' => 'amount',
            'title' => 'Atur Jumlah Trade',
            'description' => 'Set jumlah per trade',
            'completed' => ($settings['trade_amount'] ?? 0) > 0,
            'icon' => 'fa-dollar-sign'
        ],
        [
            'id' => 'robot',
            'title' => 'Aktifkan Robot',
            'description' => 'Nyalakan robot untuk mulai trading',
            'completed' => $settings['robot_enabled'] ?? false,
            'icon' => 'fa-robot'
        ],
        [
            'id' => 'first_trade',
            'title' => 'Trade Pertama',
            'description' => 'Selesaikan trade pertama Anda',
            'completed' => !empty($trades),
            'icon' => 'fa-chart-line'
        ]
    ];

    $completed = 0;
    foreach ($checklist as $item) {
        if ($item['completed']) $completed++;
    }

    return [
        'items' => $checklist,
        'completed' => $completed,
        'total' => count($checklist),
        'percentage' => round(($completed / count($checklist)) * 100)
    ];
}
?>
