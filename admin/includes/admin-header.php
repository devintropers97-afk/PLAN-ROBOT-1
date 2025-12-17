<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';

// CRITICAL: Prevent browser caching of admin pages (fixes back button bypass)
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

// Require admin login
if (!isLoggedIn() || !isAdmin()) {
    // Clear any cached session data
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
    redirect('../login.php');
    exit;
}

// Get pending counts for notifications
$db = getDBConnection();
$pendingUsers = 0;
$pendingSubscriptions = 0;

if ($db) {
    try {
        $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE status = 'pending'");
        if ($stmt) {
            $result = $stmt->fetch();
            $pendingUsers = $result['total'] ?? 0;
        }

        $stmt = $db->query("SELECT COUNT(*) as total FROM subscriptions WHERE status = 'pending'");
        if ($stmt) {
            $result = $stmt->fetch();
            $pendingSubscriptions = $result['total'] ?? 0;
        }
    } catch (Exception $e) {
        error_log("Admin header DB error: " . $e->getMessage());
    }
}

$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo $page_title ?? 'Admin'; ?> - <?php echo SITE_NAME; ?> Admin</title>

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

    <!-- Admin CSS -->
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body class="admin-body">
    <!-- Admin Navbar -->
    <nav class="admin-navbar">
        <div class="admin-navbar-brand">
            <a href="index.php" class="brand-link">
                <!-- SVG Logo -->
                <div class="admin-logo">
                    <svg viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="adminLogoGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#00d4ff"/>
                                <stop offset="50%" style="stop-color:#7c3aed"/>
                                <stop offset="100%" style="stop-color:#00d4ff"/>
                            </linearGradient>
                            <filter id="adminGlow">
                                <feGaussianBlur stdDeviation="1.5" result="coloredBlur"/>
                                <feMerge>
                                    <feMergeNode in="coloredBlur"/>
                                    <feMergeNode in="SourceGraphic"/>
                                </feMerge>
                            </filter>
                        </defs>
                        <polygon points="25,2 45,14 45,36 25,48 5,36 5,14" fill="none" stroke="url(#adminLogoGradient)" stroke-width="2" filter="url(#adminGlow)"/>
                        <text x="25" y="32" font-family="Orbitron, sans-serif" font-size="18" fill="url(#adminLogoGradient)" text-anchor="middle" font-weight="900" filter="url(#adminGlow)">Z</text>
                        <circle cx="25" cy="5" r="2" fill="#00d4ff" class="pulse-dot"/>
                        <circle cx="25" cy="45" r="2" fill="#00d4ff" class="pulse-dot"/>
                    </svg>
                </div>
                <div class="brand-text-box">
                    <span class="brand-name">ZYN</span>
                    <span class="brand-label">Admin Panel</span>
                </div>
            </a>
        </div>

        <div class="admin-navbar-right">
            <!-- Notifications -->
            <?php if ($pendingUsers > 0 || $pendingSubscriptions > 0): ?>
            <div class="admin-notifications">
                <button class="notification-btn" data-bs-toggle="dropdown">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge"><?php echo $pendingUsers + $pendingSubscriptions; ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end notification-dropdown">
                    <li class="dropdown-header">Notifications</li>
                    <?php if ($pendingUsers > 0): ?>
                    <li>
                        <a class="dropdown-item" href="verify-users.php">
                            <i class="fas fa-user-clock text-warning me-2"></i>
                            <span><?php echo $pendingUsers; ?> pending verifications</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if ($pendingSubscriptions > 0): ?>
                    <li>
                        <a class="dropdown-item" href="subscriptions.php">
                            <i class="fas fa-credit-card text-info me-2"></i>
                            <span><?php echo $pendingSubscriptions; ?> pending payments</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Quick Links -->
            <a href="../dashboard.php" class="admin-nav-link" title="User Dashboard">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="../logout.php" class="admin-nav-link logout" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </nav>

    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <nav class="sidebar-nav">
                <a href="index.php" class="sidebar-link <?php echo $current_page === 'index' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="verify-users.php" class="sidebar-link <?php echo $current_page === 'verify-users' ? 'active' : ''; ?>">
                    <i class="fas fa-user-check"></i>
                    <span>Verify Users</span>
                    <?php if ($pendingUsers > 0): ?>
                    <span class="sidebar-badge warning"><?php echo $pendingUsers; ?></span>
                    <?php endif; ?>
                </a>
                <a href="users.php" class="sidebar-link <?php echo $current_page === 'users' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span>All Users</span>
                </a>
                <a href="trades.php" class="sidebar-link <?php echo $current_page === 'trades' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line"></i>
                    <span>Trades</span>
                </a>
                <a href="subscriptions.php" class="sidebar-link <?php echo $current_page === 'subscriptions' ? 'active' : ''; ?>">
                    <i class="fas fa-credit-card"></i>
                    <span>Subscriptions</span>
                    <?php if ($pendingSubscriptions > 0): ?>
                    <span class="sidebar-badge info"><?php echo $pendingSubscriptions; ?></span>
                    <?php endif; ?>
                </a>

                <div class="sidebar-divider"></div>

                <a href="settings.php" class="sidebar-link <?php echo $current_page === 'settings' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="admin-user">
                    <div class="admin-avatar">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="admin-info">
                        <span class="admin-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        <span class="admin-role">Administrator</span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-content">
