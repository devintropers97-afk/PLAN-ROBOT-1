<?php
/**
 * 404 Error Page - Halaman Tidak Ditemukan
 *
 * CARA SETUP di .htaccess:
 * ErrorDocument 404 /404.php
 */

http_response_code(404);

// Try to include config for language support, fallback to defaults if unavailable
$lang_loaded = false;
if (file_exists(__DIR__ . '/includes/config.php')) {
    try {
        require_once __DIR__ . '/includes/config.php';
        $lang_loaded = true;
    } catch (Exception $e) {
        // Config failed to load, use defaults
    }
}

$page_title = $lang_loaded ? __('error_404_title') : 'Halaman Tidak Ditemukan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>404 - <?php echo $page_title; ?> | ZYN Trade System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --bg-dark: #0a0a0f;
            --primary: #00d4ff;
            --secondary: #7c3aed;
            --text-primary: #ffffff;
            --text-muted: #94a3b8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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
                radial-gradient(circle at 20% 80%, rgba(0, 212, 255, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(124, 58, 237, 0.08) 0%, transparent 50%);
        }

        .error-container {
            text-align: center;
            max-width: 600px;
        }

        .error-code {
            font-family: 'Orbitron', sans-serif;
            font-size: 10rem;
            font-weight: 900;
            line-height: 1;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
            text-shadow: 0 0 100px rgba(0, 212, 255, 0.3);
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
            border-color: var(--primary);
        }

        .suggestions {
            margin-top: 3rem;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 16px;
        }

        .suggestions h4 {
            font-size: 1rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .suggestions-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            justify-content: center;
            list-style: none;
        }

        .suggestions-list a {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: rgba(0, 212, 255, 0.1);
            color: var(--primary);
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .suggestions-list a:hover {
            background: rgba(0, 212, 255, 0.2);
        }

        .robot-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        @media (max-width: 768px) {
            .error-code {
                font-size: 6rem;
            }

            .error-title {
                font-size: 1.5rem;
            }

            .error-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="robot-icon">
            <i class="fas fa-robot"></i>
        </div>

        <div class="error-code"><?php echo $lang_loaded ? __('error_404_heading') : '404'; ?></div>

        <h1 class="error-title"><?php echo $lang_loaded ? __('error_404_message') : 'Halaman Tidak Ditemukan'; ?></h1>

        <p class="error-desc">
            <?php echo $lang_loaded ? __('error_404_desc') : 'Maaf, halaman yang Anda cari tidak ada atau sudah dipindahkan. Robot kami tidak bisa menemukan halaman ini.'; ?>
        </p>

        <div class="error-actions">
            <a href="/" class="btn btn-primary">
                <i class="fas fa-home"></i> <?php echo $lang_loaded ? __('error_back_home') : 'Kembali ke Home'; ?>
            </a>
            <a href="javascript:history.back()" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> <?php echo $lang_loaded ? __('common_back') : 'Halaman Sebelumnya'; ?>
            </a>
        </div>

        <div class="suggestions">
            <h4><i class="fas fa-lightbulb"></i> Mungkin Anda mencari:</h4>
            <ul class="suggestions-list">
                <li><a href="/dashboard.php">Dashboard</a></li>
                <li><a href="/strategies.php">Strategi</a></li>
                <li><a href="/pricing.php">Pricing</a></li>
                <li><a href="/calculator.php">Calculator</a></li>
                <li><a href="/faq.php">FAQ</a></li>
                <li><a href="/login.php">Login</a></li>
            </ul>
        </div>
    </div>

    <?php
    // Log 404 error
    $log_file = __DIR__ . '/logs/404.log';
    $log_dir = dirname($log_file);
    if (!is_dir($log_dir)) mkdir($log_dir, 0755, true);

    $log_entry = date('Y-m-d H:i:s') . ' | ' .
                 ($_SERVER['REQUEST_URI'] ?? 'unknown') . ' | ' .
                 ($_SERVER['HTTP_REFERER'] ?? 'direct') . ' | ' .
                 ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . PHP_EOL;

    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    ?>
</body>
</html>
