<?php
/**
 * ZYN Trade System - Error Logging System
 *
 * CARA PAKAI:
 * 1. Include file ini: require_once 'includes/logger.php';
 * 2. Log error: Logger::error('Pesan error', ['data' => 'tambahan']);
 * 3. Log info: Logger::info('User login sukses', ['user_id' => 123]);
 *
 * Log files disimpan di folder /logs/
 */

class Logger {
    private static $log_dir = null;
    private static $max_file_size = 5242880; // 5MB
    private static $max_files = 10;

    /**
     * Initialize logger
     */
    private static function init() {
        if (self::$log_dir === null) {
            self::$log_dir = dirname(__DIR__) . '/logs';

            // Create logs directory if not exists
            if (!is_dir(self::$log_dir)) {
                mkdir(self::$log_dir, 0755, true);

                // Create .htaccess to protect logs
                file_put_contents(self::$log_dir . '/.htaccess', 'Deny from all');
            }
        }
    }

    /**
     * Write log entry
     *
     * @param string $level - error, warning, info, debug
     * @param string $message - Log message
     * @param array $context - Additional data
     * @param string $channel - Log channel/file name
     */
    public static function log($level, $message, $context = [], $channel = 'app') {
        self::init();

        $log_file = self::$log_dir . '/' . $channel . '.log';

        // Rotate log if too large
        self::rotateLog($log_file);

        // Build log entry
        $entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => strtoupper($level),
            'message' => $message,
            'context' => $context,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'cli',
            'url' => $_SERVER['REQUEST_URI'] ?? 'cli',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'cli',
            'user_id' => $_SESSION['user_id'] ?? null,
        ];

        // Format log line
        $log_line = sprintf(
            "[%s] %s: %s %s\n",
            $entry['timestamp'],
            $entry['level'],
            $entry['message'],
            !empty($context) ? json_encode($context) : ''
        );

        // Write to file
        file_put_contents($log_file, $log_line, FILE_APPEND | LOCK_EX);

        // Also write to PHP error log for critical errors
        if (in_array($level, ['error', 'critical', 'emergency'])) {
            error_log("[ZYN] {$entry['level']}: {$message}");
        }

        return true;
    }

    /**
     * Rotate log files when too large
     */
    private static function rotateLog($log_file) {
        if (!file_exists($log_file)) return;

        if (filesize($log_file) < self::$max_file_size) return;

        // Rotate existing files
        for ($i = self::$max_files - 1; $i >= 1; $i--) {
            $old_file = $log_file . '.' . $i;
            $new_file = $log_file . '.' . ($i + 1);

            if (file_exists($old_file)) {
                if ($i + 1 >= self::$max_files) {
                    unlink($old_file);
                } else {
                    rename($old_file, $new_file);
                }
            }
        }

        // Rename current log
        rename($log_file, $log_file . '.1');
    }

    // Shorthand methods
    public static function emergency($message, $context = []) {
        return self::log('emergency', $message, $context);
    }

    public static function critical($message, $context = []) {
        return self::log('critical', $message, $context);
    }

    public static function error($message, $context = []) {
        return self::log('error', $message, $context);
    }

    public static function warning($message, $context = []) {
        return self::log('warning', $message, $context);
    }

    public static function info($message, $context = []) {
        return self::log('info', $message, $context);
    }

    public static function debug($message, $context = []) {
        return self::log('debug', $message, $context);
    }

    // Specialized log channels
    public static function security($message, $context = []) {
        return self::log('warning', $message, $context, 'security');
    }

    public static function trade($message, $context = []) {
        return self::log('info', $message, $context, 'trades');
    }

    public static function api($message, $context = []) {
        return self::log('info', $message, $context, 'api');
    }

    public static function payment($message, $context = []) {
        return self::log('info', $message, $context, 'payments');
    }

    /**
     * Log exception
     */
    public static function exception($exception, $context = []) {
        $context['exception'] = [
            'class' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ];

        return self::log('error', 'Exception: ' . $exception->getMessage(), $context);
    }

    /**
     * Get recent logs
     */
    public static function getRecent($channel = 'app', $lines = 100) {
        self::init();

        $log_file = self::$log_dir . '/' . $channel . '.log';

        if (!file_exists($log_file)) {
            return [];
        }

        $logs = [];
        $file = new SplFileObject($log_file, 'r');
        $file->seek(PHP_INT_MAX);
        $total_lines = $file->key();

        $start = max(0, $total_lines - $lines);
        $file->seek($start);

        while (!$file->eof()) {
            $line = $file->fgets();
            if (!empty(trim($line))) {
                $logs[] = $line;
            }
        }

        return $logs;
    }

    /**
     * Clear old logs
     */
    public static function cleanup($days = 30) {
        self::init();

        $files = glob(self::$log_dir . '/*.log*');
        $threshold = time() - ($days * 24 * 60 * 60);
        $deleted = 0;

        foreach ($files as $file) {
            if (filemtime($file) < $threshold) {
                unlink($file);
                $deleted++;
            }
        }

        self::info("Log cleanup completed", ['deleted_files' => $deleted]);

        return $deleted;
    }
}

/**
 * Global error handler
 */
function zyn_error_handler($errno, $errstr, $errfile, $errline) {
    $error_types = [
        E_ERROR => 'Error',
        E_WARNING => 'Warning',
        E_PARSE => 'Parse Error',
        E_NOTICE => 'Notice',
        E_CORE_ERROR => 'Core Error',
        E_CORE_WARNING => 'Core Warning',
        E_COMPILE_ERROR => 'Compile Error',
        E_COMPILE_WARNING => 'Compile Warning',
        E_USER_ERROR => 'User Error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice',
        E_STRICT => 'Strict',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_DEPRECATED => 'Deprecated',
        E_USER_DEPRECATED => 'User Deprecated',
    ];

    $type = $error_types[$errno] ?? 'Unknown';

    Logger::error("PHP {$type}: {$errstr}", [
        'file' => $errfile,
        'line' => $errline,
        'type' => $errno
    ]);

    // Don't execute PHP internal error handler
    return false;
}

/**
 * Global exception handler
 */
function zyn_exception_handler($exception) {
    Logger::exception($exception);

    // Show error page in production
    if (!defined('DEBUG_MODE') || !DEBUG_MODE) {
        http_response_code(500);
        include dirname(__DIR__) . '/500.php';
        exit;
    }
}

/**
 * Shutdown handler for fatal errors
 */
function zyn_shutdown_handler() {
    $error = error_get_last();

    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        Logger::critical("Fatal Error: {$error['message']}", [
            'file' => $error['file'],
            'line' => $error['line'],
            'type' => $error['type']
        ]);
    }
}

// Register handlers
set_error_handler('zyn_error_handler');
set_exception_handler('zyn_exception_handler');
register_shutdown_function('zyn_shutdown_handler');
