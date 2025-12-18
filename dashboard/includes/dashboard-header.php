<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';

// Require login
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get user data
$currentUser = getUserById($_SESSION['user_id']);
$packageInfo = getPackageDetails($currentUser['package'] ?? 'free');
$notifications = getUnreadNotifications($_SESSION['user_id']);
$notificationCount = count($notifications);

// Get current page
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>ZYN Trade Dashboard</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Orbitron:wght@500;700&display=swap" rel="stylesheet">

    <!-- Icons & Libraries -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <!-- Dashboard CSS -->
    <link rel="stylesheet" href="/dashboard/assets/dashboard.css">
</head>
<body class="dashboard-body">

<!-- Sidebar Overlay (Mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<div class="dashboard-wrapper">
    <!-- Sidebar -->
    <aside class="dashboard-sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-logo">
                <svg viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="dashLogoGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#00d4ff"/>
                            <stop offset="100%" style="stop-color:#7c3aed"/>
                        </linearGradient>
                        <filter id="dashGlow">
                            <feGaussianBlur stdDeviation="2" result="coloredBlur"/>
                            <feMerge>
                                <feMergeNode in="coloredBlur"/>
                                <feMergeNode in="SourceGraphic"/>
                            </feMerge>
                        </filter>
                    </defs>
                    <polygon points="25,2 47,14 47,36 25,48 3,36 3,14" fill="none" stroke="url(#dashLogoGrad)" stroke-width="2" filter="url(#dashGlow)"/>
                    <text x="25" y="30" text-anchor="middle" fill="url(#dashLogoGrad)" font-family="Orbitron" font-size="14" font-weight="700">ZYN</text>
                </svg>
            </div>
            <span class="brand-text">Trade</span>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section">
                <div class="nav-section-title">Main Menu</div>
                <div class="nav-item">
                    <a href="/dashboard.php" class="nav-link <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                        <i class="fas fa-robot"></i>
                        <span>Robot Control</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/statistics.php" class="nav-link <?php echo $currentPage === 'statistics' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-line"></i>
                        <span>Statistics</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/leaderboard.php" class="nav-link <?php echo $currentPage === 'leaderboard' ? 'active' : ''; ?>">
                        <i class="fas fa-trophy"></i>
                        <span>Leaderboard</span>
                    </a>
                </div>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Tools</div>
                <div class="nav-item">
                    <a href="/calculator.php" class="nav-link <?php echo $currentPage === 'calculator' ? 'active' : ''; ?>">
                        <i class="fas fa-calculator"></i>
                        <span>Profit Calculator</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/strategies.php" class="nav-link <?php echo $currentPage === 'strategies' ? 'active' : ''; ?>">
                        <i class="fas fa-chess"></i>
                        <span>Strategies</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/strategy-comparison.php" class="nav-link <?php echo $currentPage === 'strategy-comparison' ? 'active' : ''; ?>">
                        <i class="fas fa-balance-scale"></i>
                        <span>Compare Strategies</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/market-sentiment.php" class="nav-link <?php echo $currentPage === 'market-sentiment' ? 'active' : ''; ?>">
                        <i class="fas fa-compass"></i>
                        <span>Market Sentiment</span>
                    </a>
                </div>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Learning</div>
                <div class="nav-item">
                    <a href="/academy.php" class="nav-link <?php echo $currentPage === 'academy' ? 'active' : ''; ?>">
                        <i class="fas fa-graduation-cap"></i>
                        <span>ZYN Academy</span>
                        <span class="nav-badge bg-success">NEW</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/challenges.php" class="nav-link <?php echo $currentPage === 'challenges' ? 'active' : ''; ?>">
                        <i class="fas fa-gamepad"></i>
                        <span>Challenges</span>
                    </a>
                </div>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Account</div>
                <div class="nav-item">
                    <a href="/olymptrade-setup.php" class="nav-link <?php echo $currentPage === 'olymptrade-setup' ? 'active' : ''; ?>">
                        <i class="fas fa-link"></i>
                        <span>OlympTrade Setup</span>
                        <?php if (empty($currentUser['olymptrade_setup_completed'])): ?>
                        <span class="nav-badge bg-danger">!</span>
                        <?php endif; ?>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/profile.php" class="nav-link <?php echo $currentPage === 'profile' ? 'active' : ''; ?>">
                        <i class="fas fa-user"></i>
                        <span>Profile</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/settings.php" class="nav-link <?php echo $currentPage === 'settings' ? 'active' : ''; ?>">
                        <i class="fas fa-cog"></i>
                        <span>Robot Settings</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/security.php" class="nav-link <?php echo $currentPage === 'security' ? 'active' : ''; ?>">
                        <i class="fas fa-shield-alt"></i>
                        <span>Security</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/referral.php" class="nav-link <?php echo $currentPage === 'referral' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i>
                        <span>Referral</span>
                        <span class="nav-badge bg-success">+7 Days</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/announcements.php" class="nav-link <?php echo $currentPage === 'announcements' ? 'active' : ''; ?>">
                        <i class="fas fa-bullhorn"></i>
                        <span>Announcements</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/pricing.php" class="nav-link <?php echo $currentPage === 'pricing' ? 'active' : ''; ?>">
                        <i class="fas fa-crown"></i>
                        <span>Upgrade</span>
                        <?php if (($currentUser['package'] ?? 'free') === 'free'): ?>
                        <span class="nav-badge bg-warning text-dark">PRO</span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Support</div>
                <div class="nav-item">
                    <a href="<?php echo getSetting('telegram_channel', '#'); ?>" target="_blank" class="nav-link">
                        <i class="fab fa-telegram"></i>
                        <span>Telegram Group</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/faq.php" class="nav-link">
                        <i class="fas fa-question-circle"></i>
                        <span>FAQ & Help</span>
                    </a>
                </div>
            </div>
        </nav>

        <div class="sidebar-user">
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($currentUser['fullname'], 0, 2)); ?>
                </div>
                <div class="user-details">
                    <div class="user-name"><?php echo htmlspecialchars($currentUser['fullname']); ?></div>
                    <div class="user-package">
                        <span class="db-badge <?php echo strtolower($currentUser['package'] ?? 'free'); ?>">
                            <?php echo strtoupper($currentUser['package'] ?? 'FREE'); ?>
                        </span>
                    </div>
                </div>
                <a href="/logout.php" class="topbar-btn" title="Logout" style="width: 32px; height: 32px;">
                    <i class="fas fa-sign-out-alt" style="font-size: 0.85rem;"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="dashboard-main">
        <!-- Topbar -->
        <header class="dashboard-topbar">
            <div class="topbar-left">
                <button class="mobile-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="page-breadcrumb">
                    <i class="fas fa-home me-1"></i> Dashboard / <span><?php echo $page_title ?? 'Home'; ?></span>
                </div>
            </div>
            <div class="topbar-right">
                <a href="/notifications.php" class="topbar-btn" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <?php if ($notificationCount > 0): ?>
                    <span class="badge"><?php echo $notificationCount > 9 ? '9+' : $notificationCount; ?></span>
                    <?php endif; ?>
                </a>
                <a href="/profile.php" class="topbar-btn" title="Settings">
                    <i class="fas fa-cog"></i>
                </a>
                <a href="/" class="topbar-btn" title="Back to Home">
                    <i class="fas fa-home"></i>
                </a>
            </div>
        </header>

        <!-- Page Content -->
        <div class="dashboard-content">
