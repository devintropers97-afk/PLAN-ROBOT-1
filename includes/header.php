<?php
/**
 * ZYN Trade System - Header Template
 * Version: 3.0 Premium Edition
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/language.php';

$current_page = basename($_SERVER['PHP_SELF'], '.php');
$flash = getFlash();
$currentLang = getCurrentLanguage();
$textDir = getTextDirection();
?>
<!DOCTYPE html>
<html lang="<?php echo getHtmlLang(); ?>" dir="<?php echo $textDir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- Primary SEO Meta Tags -->
    <meta name="title" content="<?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME . ' - Robot Trading Otomatis Terbaik Indonesia'; ?>">
    <meta name="description" content="ZYN Trade System - Robot Trading Otomatis 24/7 untuk OlympTrade. 10 strategi powerful dengan win rate hingga 91%. Tidur nyenyak, bangun profit! Gratis untuk pemula.">
    <meta name="keywords" content="robot trading, trading otomatis, robot olymptrade, trading robot indonesia, automated trading, binary options robot, robot trading gratis, cara trading autopilot, robot trading terbaik, ZYN trade system, signal trading, copy trading">
    <meta name="author" content="ZYN Trade System">
    <meta name="theme-color" content="#00d4ff">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="googlebot" content="index, follow">
    <meta name="bingbot" content="index, follow">

    <!-- Geo Tags for Indonesia -->
    <meta name="geo.region" content="ID">
    <meta name="geo.placename" content="Indonesia">
    <meta name="language" content="<?php echo getHtmlLang(); ?>">
    <meta name="content-language" content="<?php echo getHtmlLang(); ?>">

    <!-- Mobile Web App -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="ZYN Trade">
    <meta name="application-name" content="ZYN Trade System">
    <meta name="msapplication-TileColor" content="#00d4ff">
    <meta name="msapplication-config" content="browserconfig.xml">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:title" content="<?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?>">
    <meta property="og:description" content="Robot Trading Otomatis 24/7 untuk OlympTrade. 10 strategi powerful dengan win rate hingga 91%. Tidur nyenyak, bangun profit!">
    <meta property="og:image" content="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/assets/images/og-image.jpg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="<?php echo SITE_NAME; ?>">
    <meta property="og:locale" content="id_ID">
    <meta property="og:locale:alternate" content="en_US">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta name="twitter:title" content="<?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?>">
    <meta name="twitter:description" content="Robot Trading Otomatis 24/7 untuk OlympTrade. Win rate hingga 91%. Tidur nyenyak, bangun profit!">
    <meta name="twitter:image" content="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/assets/images/og-image.jpg">

    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . strtok($_SERVER['REQUEST_URI'], '?'); ?>">

    <!-- Alternate Language Links -->
    <link rel="alternate" hreflang="id" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . strtok($_SERVER['REQUEST_URI'], '?'); ?>?lang=id">
    <link rel="alternate" hreflang="en" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . strtok($_SERVER['REQUEST_URI'], '?'); ?>?lang=en">
    <link rel="alternate" hreflang="x-default" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . strtok($_SERVER['REQUEST_URI'], '?'); ?>">

    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME . ' - Robot Trading Otomatis Terbaik Indonesia'; ?></title>

    <!-- JSON-LD Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "ZYN Trade System",
        "description": "Robot Trading Otomatis 24/7 untuk OlympTrade dengan 10 strategi powerful dan win rate hingga 91%",
        "applicationCategory": "FinanceApplication",
        "operatingSystem": "Web Browser, Android",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Gratis selamanya untuk tier FREE"
        },
        "aggregateRating": {
            "@type": "AggregateRating",
            "ratingValue": "4.8",
            "ratingCount": "2500",
            "bestRating": "5",
            "worstRating": "1"
        },
        "author": {
            "@type": "Organization",
            "name": "ZYN Trade System",
            "url": "<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>"
        }
    }
    </script>

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "ZYN Trade System",
        "url": "<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>",
        "logo": "<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/assets/images/logo.png",
        "sameAs": [],
        "contactPoint": {
            "@type": "ContactPoint",
            "contactType": "customer support",
            "availableLanguage": ["Indonesian", "English"]
        }
    }
    </script>

    <!-- Favicon SVG -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><defs><linearGradient id='g' x1='0%25' y1='0%25' x2='100%25' y2='100%25'><stop offset='0%25' stop-color='%2300d4ff'/><stop offset='100%25' stop-color='%237c3aed'/></linearGradient></defs><rect fill='%230a0a0f' rx='20' width='100' height='100'/><text x='50' y='68' font-family='Arial Black' font-size='48' fill='url(%23g)' text-anchor='middle' font-weight='900'>Z</text></svg>">

    <!-- Preconnect for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Orbitron:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Premium Edition CSS -->
    <link rel="stylesheet" href="assets/css/premium.css">
    <?php
    $dashboard_pages = ['dashboard', 'admin', 'statistics', 'leaderboard', 'settings', 'profile', 'calculator', 'subscribe'];
    $needs_dashboard_css = false;
    foreach ($dashboard_pages as $page) {
        if (strpos($current_page, $page) !== false) {
            $needs_dashboard_css = true;
            break;
        }
    }
    if ($needs_dashboard_css):
    ?>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <?php endif; ?>

    <!-- Page specific styles -->
    <?php if (isset($page_styles)): ?>
    <?php echo $page_styles; ?>
    <?php endif; ?>
</head>
<body class="<?php echo $current_page; ?>-page">
    <!-- Premium Preloader -->
    <div class="premium-preloader">
        <div class="preloader-content">
            <div class="preloader-logo-premium">
                <div class="preloader-ring"></div>
                <div class="preloader-ring"></div>
                <div class="preloader-ring"></div>
                <span class="preloader-logo-text">Z</span>
            </div>
            <div class="preloader-progress">
                <div class="preloader-progress-bar"></div>
            </div>
            <div class="preloader-text">INITIALIZING SYSTEM</div>
            <div class="preloader-counter">0%</div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <!-- Premium SVG Logo -->
                <div class="brand-logo-icon">
                    <svg viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg" class="logo-svg">
                        <defs>
                            <linearGradient id="logoGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#00d4ff"/>
                                <stop offset="50%" style="stop-color:#7c3aed"/>
                                <stop offset="100%" style="stop-color:#00d4ff"/>
                            </linearGradient>
                            <linearGradient id="logoGradientAnim" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#00d4ff">
                                    <animate attributeName="stop-color" values="#00d4ff;#7c3aed;#00d4ff" dur="3s" repeatCount="indefinite"/>
                                </stop>
                                <stop offset="100%" style="stop-color:#7c3aed">
                                    <animate attributeName="stop-color" values="#7c3aed;#00d4ff;#7c3aed" dur="3s" repeatCount="indefinite"/>
                                </stop>
                            </linearGradient>
                            <filter id="logoGlow" x="-50%" y="-50%" width="200%" height="200%">
                                <feGaussianBlur stdDeviation="2" result="coloredBlur"/>
                                <feMerge>
                                    <feMergeNode in="coloredBlur"/>
                                    <feMergeNode in="SourceGraphic"/>
                                </feMerge>
                            </filter>
                        </defs>
                        <!-- Outer hexagon with animation -->
                        <polygon points="25,2 45,14 45,36 25,48 5,36 5,14" fill="none" stroke="url(#logoGradientAnim)" stroke-width="2" filter="url(#logoGlow)" class="logo-hexagon">
                            <animate attributeName="stroke-dasharray" values="0,200;200,200" dur="2s" fill="freeze"/>
                        </polygon>
                        <!-- Inner circuit lines -->
                        <path d="M15,20 L25,15 L35,20 M15,30 L25,35 L35,30" fill="none" stroke="url(#logoGradient)" stroke-width="1.5" opacity="0.6" class="logo-circuit"/>
                        <!-- Center Z -->
                        <text x="25" y="32" font-family="Orbitron, sans-serif" font-size="18" fill="url(#logoGradientAnim)" text-anchor="middle" font-weight="900" filter="url(#logoGlow)" class="logo-text">Z</text>
                        <!-- Corner dots with pulse animation -->
                        <circle cx="25" cy="5" r="2" fill="#00d4ff" class="logo-dot">
                            <animate attributeName="opacity" values="1;0.3;1" dur="1.5s" repeatCount="indefinite"/>
                            <animate attributeName="r" values="2;3;2" dur="1.5s" repeatCount="indefinite"/>
                        </circle>
                        <circle cx="43" cy="15" r="1.5" fill="#7c3aed" opacity="0.8"/>
                        <circle cx="43" cy="35" r="1.5" fill="#7c3aed" opacity="0.8"/>
                        <circle cx="25" cy="45" r="2" fill="#00d4ff" class="logo-dot">
                            <animate attributeName="opacity" values="0.3;1;0.3" dur="1.5s" repeatCount="indefinite"/>
                            <animate attributeName="r" values="2;3;2" dur="1.5s" repeatCount="indefinite" begin="0.75s"/>
                        </circle>
                        <circle cx="7" cy="35" r="1.5" fill="#7c3aed" opacity="0.8"/>
                        <circle cx="7" cy="15" r="1.5" fill="#7c3aed" opacity="0.8"/>
                    </svg>
                </div>
                <div class="brand-text-wrapper">
                    <span class="brand-logo">ZYN</span>
                    <span class="brand-text">Trade System</span>
                </div>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'index' ? 'active' : ''; ?>" href="index.php">
                            <i class="fas fa-home d-lg-none me-2"></i><?php _e('nav_home'); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'strategies' ? 'active' : ''; ?>" href="strategies.php">
                            <i class="fas fa-chess d-lg-none me-2"></i><?php _e('nav_strategies'); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'pricing' ? 'active' : ''; ?>" href="pricing.php">
                            <i class="fas fa-tags d-lg-none me-2"></i><?php _e('nav_pricing'); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'faq' ? 'active' : ''; ?>" href="faq.php">
                            <i class="fas fa-question-circle d-lg-none me-2"></i><?php _e('nav_faq'); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'calculator' ? 'active' : ''; ?>" href="calculator.php">
                            <i class="fas fa-calculator"></i><span class="ms-1 d-lg-none d-xl-inline"> <?php _e('nav_calculator'); ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'mobile' ? 'active' : ''; ?>" href="mobile.php" title="Download Aplikasi Mobile">
                            <i class="fas fa-mobile-alt"></i><span class="ms-1 d-lg-none d-xl-inline"> Mobile App</span>
                        </a>
                    </li>

                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>" href="dashboard.php">
                                <i class="fas fa-chart-line"></i><span class="ms-1"> <?php _e('nav_dashboard'); ?></span>
                            </a>
                        </li>
                        <?php if (isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/index.php">
                                <i class="fas fa-cog"></i><span class="ms-1 d-lg-none d-xl-inline"> <?php _e('nav_admin'); ?></span>
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="user-avatar">
                                    <i class="fas fa-user-circle"></i>
                                </span>
                                <span class="ms-1 d-none d-lg-inline"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li>
                                    <div class="dropdown-header">
                                        <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>
                                        <small class="d-block text-muted"><?php echo isset($_SESSION['user_package']) ? ucfirst($_SESSION['user_package']) : 'Free'; ?> Member</small>
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i><?php _e('nav_profile'); ?></a></li>
                                <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i><?php _e('nav_settings'); ?></a></li>
                                <li><a class="dropdown-item" href="statistics.php"><i class="fas fa-chart-bar me-2"></i><?php _e('nav_statistics'); ?></a></li>
                                <li><a class="dropdown-item" href="leaderboard.php"><i class="fas fa-trophy me-2"></i><?php _e('nav_leaderboard'); ?></a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i><?php _e('nav_logout'); ?></a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'login' ? 'active' : ''; ?>" href="login.php">
                                <i class="fas fa-sign-in-alt d-lg-none me-2"></i><?php _e('nav_login'); ?>
                            </a>
                        </li>
                        <li class="nav-item ms-lg-2">
                            <a class="btn btn-primary btn-nav" href="register.php">
                                <i class="fas fa-rocket me-1"></i><?php _e('nav_register'); ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <!-- Language Selector -->
                    <li class="nav-item ms-lg-2">
                        <?php echo renderLanguageSelector(); ?>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Message Alert -->
    <?php if ($flash): ?>
    <div class="flash-message-container">
        <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show flash-message" role="alert">
            <div class="d-flex align-items-center">
                <?php if ($flash['type'] === 'success'): ?>
                    <i class="fas fa-check-circle me-2"></i>
                <?php elseif ($flash['type'] === 'danger'): ?>
                    <i class="fas fa-exclamation-circle me-2"></i>
                <?php elseif ($flash['type'] === 'warning'): ?>
                    <i class="fas fa-exclamation-triangle me-2"></i>
                <?php else: ?>
                    <i class="fas fa-info-circle me-2"></i>
                <?php endif; ?>
                <span><?php echo $flash['message']; ?></span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main Content Wrapper -->
    <main class="main-content">
