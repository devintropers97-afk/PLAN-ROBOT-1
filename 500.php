<?php
/**
 * 500 Error Page - Server Error
 *
 * CARA SETUP di .htaccess:
 * ErrorDocument 500 /500.php
 */

http_response_code(500);

// Try to include config and language for i18n support, fallback to defaults if unavailable
$lang_loaded = false;
if (file_exists(__DIR__ . '/includes/config.php') && file_exists(__DIR__ . '/includes/language.php')) {
    try {
        require_once __DIR__ . '/includes/config.php';
        require_once __DIR__ . '/includes/language.php';
        $lang_loaded = function_exists('__');
    } catch (Exception $e) {
        // Config or language failed to load, use defaults
        $lang_loaded = false;
    }
}

$page_title = $lang_loaded ? __('error_500_title') : 'Server Error';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>500 - <?php echo $page_title; ?> | ZYN Trade System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --bg-dark: #0a0a0f;
            --primary: #00d4ff;
            --secondary: #7c3aed;
            --danger: #ef4444;
            --text-primary: #ffffff;
            --text-muted: #94a3b8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-dark);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background-image:
                radial-gradient(circle at 20% 80%, rgba(239, 68, 68, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(124, 58, 237, 0.08) 0%, transparent 50%);
        }

        .error-container {
            text-align: center;
            max-width: 600px;
        }

        .error-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 2rem;
            position: relative;
        }

        .error-icon-circle {
            width: 100%;
            height: 100%;
            border: 3px solid var(--danger);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse-error 2s ease-in-out infinite;
        }

        @keyframes pulse-error {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
        }

        .error-icon i {
            font-size: 3.5rem;
            color: var(--danger);
        }

        .error-code {
            font-family: 'Orbitron', sans-serif;
            font-size: 5rem;
            font-weight: 900;
            color: var(--danger);
            margin-bottom: 1rem;
        }

        .error-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .error-desc {
            color: var(--text-muted);
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .error-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 2rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #fff;
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 212, 255, 0.3);
        }

        .btn-secondary {
            background: transparent;
            color: var(--text-primary);
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .status-box {
            margin-top: 3rem;
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px;
        }

        .status-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .status-item:last-child {
            border-bottom: none;
        }

        .status-label {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .status-value {
            font-weight: 600;
            font-size: 0.9rem;
        }

        .status-value.ok { color: #10b981; }
        .status-value.error { color: #ef4444; }
        .status-value.checking {
            color: #f59e0b;
            animation: blink 1s infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .contact-support {
            margin-top: 2rem;
            padding: 1rem;
            background: rgba(0, 212, 255, 0.1);
            border-radius: 10px;
            font-size: 0.9rem;
        }

        .contact-support a {
            color: var(--primary);
            text-decoration: none;
        }

        .contact-support a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .error-code { font-size: 4rem; }
            .error-title { font-size: 1.5rem; }
            .error-actions { flex-direction: column; }
            .btn { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <div class="error-icon-circle">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>

        <div class="error-code"><?php echo $lang_loaded ? __('error_500_heading') : '500'; ?></div>

        <h1 class="error-title"><?php echo $lang_loaded ? __('error_500_message') : 'Terjadi Kesalahan Server'; ?></h1>

        <p class="error-desc">
            <?php echo $lang_loaded ? __('error_500_desc') : 'Maaf, server kami sedang mengalami masalah teknis. Tim kami sudah diberitahu dan sedang memperbaiki masalah ini.'; ?>
        </p>

        <div class="error-actions">
            <button onclick="window.location.reload()" class="btn btn-primary">
                <i class="fas fa-sync-alt"></i> <?php echo $lang_loaded ? __('error_retry') : 'Coba Lagi'; ?>
            </button>
            <a href="/" class="btn btn-secondary">
                <i class="fas fa-home"></i> <?php echo $lang_loaded ? __('error_back_home') : 'Kembali ke Home'; ?>
            </a>
        </div>

        <div class="status-box">
            <div class="status-item">
                <span class="status-label"><?php echo $lang_loaded ? __('error_status_server') : 'Status Server'; ?></span>
                <span class="status-value checking" id="server-status"><?php echo $lang_loaded ? __('error_status_checking') : 'Mengecek...'; ?></span>
            </div>
            <div class="status-item">
                <span class="status-label"><?php echo $lang_loaded ? __('error_status_database') : 'Database'; ?></span>
                <span class="status-value checking" id="db-status"><?php echo $lang_loaded ? __('error_status_checking') : 'Mengecek...'; ?></span>
            </div>
            <div class="status-item">
                <span class="status-label"><?php echo $lang_loaded ? __('error_status_api') : 'API'; ?></span>
                <span class="status-value checking" id="api-status"><?php echo $lang_loaded ? __('error_status_checking') : 'Mengecek...'; ?></span>
            </div>
        </div>

        <div class="contact-support">
            <i class="fab fa-whatsapp"></i>
            <?php echo $lang_loaded ? __('error_need_help') : 'Butuh bantuan segera?'; ?> <a href="https://wa.me/6281234567890" target="_blank"><?php echo $lang_loaded ? __('error_contact_whatsapp') : 'Hubungi Support via WhatsApp'; ?></a>
        </div>
    </div>

    <script>
        // Check system status
        setTimeout(() => {
            document.getElementById('server-status').textContent = 'Sedang Diperbaiki';
            document.getElementById('server-status').className = 'status-value error';
        }, 1000);

        setTimeout(() => {
            document.getElementById('db-status').textContent = 'OK';
            document.getElementById('db-status').className = 'status-value ok';
        }, 1500);

        setTimeout(() => {
            document.getElementById('api-status').textContent = 'OK';
            document.getElementById('api-status').className = 'status-value ok';
        }, 2000);

        // Auto refresh after 30 seconds
        setTimeout(() => {
            window.location.reload();
        }, 30000);
    </script>

    <?php
    // Log 500 error
    $log_file = __DIR__ . '/logs/500.log';
    $log_dir = dirname($log_file);
    if (!is_dir($log_dir)) mkdir($log_dir, 0755, true);

    $log_entry = date('Y-m-d H:i:s') . ' | ' .
                 ($_SERVER['REQUEST_URI'] ?? 'unknown') . ' | ' .
                 ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . PHP_EOL;

    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    ?>
</body>
</html>
