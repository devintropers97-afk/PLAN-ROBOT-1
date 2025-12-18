<?php
/**
 * ZYN Trade System - Security Helper
 * CSRF Protection, Input Sanitization, Rate Limiting
 *
 * CARA PAKAI:
 * 1. Include file ini di setiap halaman yang butuh keamanan
 * 2. Panggil csrf_field() di dalam form
 * 3. Panggil verify_csrf() di awal proses form
 */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate CSRF Token
 * Token ini mencegah serangan Cross-Site Request Forgery
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

/**
 * Get CSRF Token (untuk AJAX requests)
 */
function get_csrf_token() {
    return generate_csrf_token();
}

/**
 * Render hidden CSRF input field
 * CARA PAKAI: Taruh <?php csrf_field(); ?> di dalam <form>
 */
function csrf_field() {
    $token = generate_csrf_token();
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Render CSRF meta tag (untuk AJAX)
 * CARA PAKAI: Taruh di <head> untuk AJAX requests
 */
function csrf_meta() {
    $token = generate_csrf_token();
    echo '<meta name="csrf-token" content="' . htmlspecialchars($token) . '">';
}

/**
 * Verify CSRF Token
 * CARA PAKAI: Panggil di awal processing form
 *
 * @param string $token Token dari form submission
 * @param int $max_age Maximum age in seconds (default 1 hour)
 * @return bool True if valid
 */
function verify_csrf($token = null, $max_age = 3600) {
    // Get token from parameter or POST
    if ($token === null) {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    }

    // Check if token exists in session
    if (empty($_SESSION['csrf_token'])) {
        return false;
    }

    // Check if token matches
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }

    // Check if token is not expired
    if (time() - ($_SESSION['csrf_token_time'] ?? 0) > $max_age) {
        // Regenerate token
        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_token_time']);
        return false;
    }

    return true;
}

/**
 * Regenerate CSRF Token (setelah login/logout)
 */
function regenerate_csrf() {
    unset($_SESSION['csrf_token']);
    unset($_SESSION['csrf_token_time']);
    return generate_csrf_token();
}

/**
 * Rate Limiting - Mencegah brute force attack
 *
 * CARA PAKAI:
 * if (!check_rate_limit('login', 5, 300)) {
 *     die('Terlalu banyak percobaan. Coba lagi dalam 5 menit.');
 * }
 *
 * @param string $action Nama action (login, register, etc)
 * @param int $max_attempts Maximum attempts allowed
 * @param int $window Time window in seconds
 * @return bool True if allowed, False if rate limited
 */
function check_rate_limit($action, $max_attempts = 5, $window = 300) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = "rate_limit_{$action}_{$ip}";

    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = [
            'attempts' => 0,
            'first_attempt' => time()
        ];
    }

    $data = &$_SESSION[$key];

    // Reset if window expired
    if (time() - $data['first_attempt'] > $window) {
        $data['attempts'] = 0;
        $data['first_attempt'] = time();
    }

    // Check if exceeded
    if ($data['attempts'] >= $max_attempts) {
        return false;
    }

    // Increment attempts
    $data['attempts']++;

    return true;
}

/**
 * Get remaining attempts
 */
function get_remaining_attempts($action, $max_attempts = 5) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = "rate_limit_{$action}_{$ip}";

    if (!isset($_SESSION[$key])) {
        return $max_attempts;
    }

    return max(0, $max_attempts - $_SESSION[$key]['attempts']);
}

/**
 * Reset rate limit (after successful action)
 */
function reset_rate_limit($action) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = "rate_limit_{$action}_{$ip}";
    unset($_SESSION[$key]);
}

/**
 * Sanitize input
 * CARA PAKAI: $clean = sanitize_input($_POST['name']);
 */
function sanitize_input($input, $type = 'string') {
    if (is_array($input)) {
        return array_map(function($item) use ($type) {
            return sanitize_input($item, $type);
        }, $input);
    }

    $input = trim($input);

    switch ($type) {
        case 'email':
            return filter_var($input, FILTER_SANITIZE_EMAIL);
        case 'int':
            return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
        case 'float':
            return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        case 'url':
            return filter_var($input, FILTER_SANITIZE_URL);
        case 'html':
            return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        case 'string':
        default:
            return htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Validate input
 */
function validate_input($input, $type, $options = []) {
    switch ($type) {
        case 'email':
            return filter_var($input, FILTER_VALIDATE_EMAIL) !== false;
        case 'phone':
            // Indonesian phone format
            return preg_match('/^(\+62|62|0)[0-9]{9,13}$/', preg_replace('/[^0-9+]/', '', $input));
        case 'license_key':
            return preg_match('/^ZYN-[A-Z0-9]{4,}-[A-Z0-9]{3,}$/', $input);
        case 'length':
            $min = $options['min'] ?? 0;
            $max = $options['max'] ?? PHP_INT_MAX;
            $len = strlen($input);
            return $len >= $min && $len <= $max;
        default:
            return !empty($input);
    }
}

/**
 * Log security event
 */
function log_security_event($event, $details = []) {
    $log_file = __DIR__ . '/../logs/security.log';
    $log_dir = dirname($log_file);

    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }

    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event' => $event,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'user_id' => $_SESSION['user_id'] ?? null,
        'details' => $details
    ];

    file_put_contents($log_file, json_encode($log_entry) . PHP_EOL, FILE_APPEND | LOCK_EX);
}

/**
 * Check if request is AJAX
 */
function is_ajax_request() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Send JSON response (for AJAX)
 */
function json_response($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * CSRF error response
 */
function csrf_error() {
    if (is_ajax_request()) {
        json_response(['error' => 'Invalid security token. Please refresh the page.'], 403);
    } else {
        // Use __() function if available, otherwise use fallback message
        $message = function_exists('__') ? __('session_expired') : 'Session expired. Please refresh the page.';
        $_SESSION['flash'] = [
            'type' => 'error',
            'message' => $message
        ];
        $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/';
        header('Location: ' . $redirect);
        exit;
    }
}
?>
