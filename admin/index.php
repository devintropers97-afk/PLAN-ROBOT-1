<?php
$page_title = 'Admin Dashboard';
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Require admin login
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Get statistics
$db = getDBConnection();

// Total users
$stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$totalUsers = $stmt->fetch()['total'];

// Pending verifications
$stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE status = 'pending'");
$pendingUsers = $stmt->fetch()['total'];

// Active users
$stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE status = 'active'");
$activeUsers = $stmt->fetch()['total'];

// Today's trades
$stmt = $db->query("SELECT COUNT(*) as total FROM trades WHERE DATE(created_at) = CURDATE()");
$todayTrades = $stmt->fetch()['total'];

// Recent users
$stmt = $db->query("SELECT * FROM users WHERE role = 'user' ORDER BY created_at DESC LIMIT 10");
$recentUsers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="dashboard-page">
    <!-- Admin Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <span class="brand-logo">ZYN</span>
                <span class="brand-text">Admin Panel</span>
            </a>

            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../dashboard.php">
                    <i class="fas fa-home"></i> User Dashboard
                </a>
                <a class="nav-link" href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4" style="margin-top: 80px;">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-2">
                <div class="card mb-4">
                    <div class="card-body p-2">
                        <nav class="nav flex-column">
                            <a class="nav-link active" href="index.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                            <a class="nav-link" href="verify-users.php">
                                <i class="fas fa-user-check"></i> Verify Users
                                <?php if ($pendingUsers > 0): ?>
                                <span class="badge bg-danger"><?php echo $pendingUsers; ?></span>
                                <?php endif; ?>
                            </a>
                            <a class="nav-link" href="users.php">
                                <i class="fas fa-users"></i> All Users
                            </a>
                            <a class="nav-link" href="trades.php">
                                <i class="fas fa-chart-line"></i> Trades
                            </a>
                            <a class="nav-link" href="subscriptions.php">
                                <i class="fas fa-credit-card"></i> Subscriptions
                            </a>
                            <a class="nav-link" href="settings.php">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-10">
                <div class="dashboard-header">
                    <div>
                        <h1 class="dashboard-title">Admin Dashboard</h1>
                        <p class="dashboard-subtitle">Manage ZYN Trade System</p>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="admin-stats">
                    <div class="admin-stat-card">
                        <div class="admin-stat-value text-primary"><?php echo $totalUsers; ?></div>
                        <div class="admin-stat-label">Total Users</div>
                    </div>
                    <div class="admin-stat-card">
                        <div class="admin-stat-value text-warning"><?php echo $pendingUsers; ?></div>
                        <div class="admin-stat-label">Pending Verification</div>
                    </div>
                    <div class="admin-stat-card">
                        <div class="admin-stat-value text-success"><?php echo $activeUsers; ?></div>
                        <div class="admin-stat-label">Active Users</div>
                    </div>
                    <div class="admin-stat-card">
                        <div class="admin-stat-value"><?php echo $todayTrades; ?></div>
                        <div class="admin-stat-label">Today's Trades</div>
                    </div>
                </div>

                <!-- Pending Verifications Alert -->
                <?php if ($pendingUsers > 0): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong><?php echo $pendingUsers; ?> users</strong> pending verification.
                    <a href="verify-users.php" class="alert-link">Review now</a>
                </div>
                <?php endif; ?>

                <!-- Recent Users -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-4"><i class="fas fa-users"></i> Recent Registrations</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th>Country</th>
                                        <th>OlympTrade ID</th>
                                        <th>Status</th>
                                        <th>Registered</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentUsers as $user): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($user['fullname']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['country']); ?></td>
                                        <td><code><?php echo htmlspecialchars($user['olymptrade_id']); ?></code></td>
                                        <td>
                                            <?php
                                            $statusClass = [
                                                'pending' => 'warning',
                                                'active' => 'success',
                                                'rejected' => 'danger',
                                                'suspended' => 'secondary'
                                            ];
                                            ?>
                                            <span class="badge bg-<?php echo $statusClass[$user['status']] ?? 'secondary'; ?>">
                                                <?php echo ucfirst($user['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDate($user['created_at']); ?></td>
                                        <td>
                                            <?php if ($user['status'] === 'pending'): ?>
                                            <a href="verify-users.php?action=verify&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-success">
                                                <i class="fas fa-check"></i>
                                            </a>
                                            <a href="verify-users.php?action=reject&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger">
                                                <i class="fas fa-times"></i>
                                            </a>
                                            <?php else: ?>
                                            <a href="users.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-light">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
