<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="ZYN Trade System - Precision Over Emotion. Automated trading robot for OlympTrade with 10 powerful strategies and up to 91% win rate.">
    <meta name="keywords" content="trading robot, automated trading, olymptrade, binary options, algorithmic trading, ZYN">
    <meta name="author" content="ZYN Trade System">
    <meta name="theme-color" content="#00d4ff">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><defs><linearGradient id='g' x1='0%25' y1='0%25' x2='100%25' y2='100%25'><stop offset='0%25' stop-color='%2300d4ff'/><stop offset='100%25' stop-color='%237c3aed'/></linearGradient></defs><rect fill='%230a0a0f' rx='20' width='100' height='100'/><text x='50' y='68' font-family='Arial Black' font-size='48' fill='url(%23g)' text-anchor='middle' font-weight='900'>Z</text></svg>">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Orbitron:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
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
</head>
<body class="<?php echo $current_page; ?>-page">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <!-- SVG Logo -->
                <div class="brand-logo-icon">
                    <svg viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="logoGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#00d4ff"/>
                                <stop offset="50%" style="stop-color:#7c3aed"/>
                                <stop offset="100%" style="stop-color:#00d4ff"/>
                            </linearGradient>
                            <filter id="glow">
                                <feGaussianBlur stdDeviation="2" result="coloredBlur"/>
                                <feMerge>
                                    <feMergeNode in="coloredBlur"/>
                                    <feMergeNode in="SourceGraphic"/>
                                </feMerge>
                            </filter>
                        </defs>
                        <!-- Outer hexagon -->
                        <polygon points="25,2 45,14 45,36 25,48 5,36 5,14" fill="none" stroke="url(#logoGradient)" stroke-width="2" filter="url(#glow)"/>
                        <!-- Inner circuit lines -->
                        <path d="M15,20 L25,15 L35,20 M15,30 L25,35 L35,30" fill="none" stroke="url(#logoGradient)" stroke-width="1.5" opacity="0.6"/>
                        <!-- Center Z -->
                        <text x="25" y="32" font-family="Orbitron, sans-serif" font-size="18" fill="url(#logoGradient)" text-anchor="middle" font-weight="900" filter="url(#glow)">Z</text>
                        <!-- Corner dots -->
                        <circle cx="25" cy="5" r="2" fill="#00d4ff" class="animate-pulse"/>
                        <circle cx="43" cy="15" r="1.5" fill="#7c3aed" opacity="0.8"/>
                        <circle cx="43" cy="35" r="1.5" fill="#7c3aed" opacity="0.8"/>
                        <circle cx="25" cy="45" r="2" fill="#00d4ff" class="animate-pulse"/>
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
                            <i class="fas fa-home d-lg-none me-2"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'strategies' ? 'active' : ''; ?>" href="strategies.php">
                            <i class="fas fa-chess d-lg-none me-2"></i>Strategies
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'pricing' ? 'active' : ''; ?>" href="pricing.php">
                            <i class="fas fa-tags d-lg-none me-2"></i>Pricing
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'faq' ? 'active' : ''; ?>" href="faq.php">
                            <i class="fas fa-question-circle d-lg-none me-2"></i>FAQ
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'calculator' ? 'active' : ''; ?>" href="calculator.php">
                            <i class="fas fa-calculator"></i><span class="ms-1 d-lg-none d-xl-inline"> Calculator</span>
                        </a>
                    </li>

                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>" href="dashboard.php">
                                <i class="fas fa-chart-line"></i><span class="ms-1"> Dashboard</span>
                            </a>
                        </li>
                        <?php if (isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/index.php">
                                <i class="fas fa-cog"></i><span class="ms-1 d-lg-none d-xl-inline"> Admin</span>
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
                                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                                <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                                <li><a class="dropdown-item" href="statistics.php"><i class="fas fa-chart-bar me-2"></i>Statistics</a></li>
                                <li><a class="dropdown-item" href="leaderboard.php"><i class="fas fa-trophy me-2"></i>Leaderboard</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'login' ? 'active' : ''; ?>" href="login.php">
                                <i class="fas fa-sign-in-alt d-lg-none me-2"></i>Login
                            </a>
                        </li>
                        <li class="nav-item ms-lg-2">
                            <a class="btn btn-primary btn-nav" href="register.php">
                                <i class="fas fa-rocket me-1"></i>Get Started
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content Wrapper -->
    <main class="main-content">
