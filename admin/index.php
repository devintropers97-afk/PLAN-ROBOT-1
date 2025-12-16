<?php
$page_title = 'Dashboard';
require_once 'includes/admin-header.php';

// Get statistics
$db = getDBConnection();

// Total users
$stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$totalUsers = $stmt->fetch()['total'];

// Pending verifications
$stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE status = 'pending'");
$pendingVerifications = $stmt->fetch()['total'];

// Active users
$stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE status = 'active'");
$activeUsers = $stmt->fetch()['total'];

// Today's trades
$stmt = $db->query("SELECT COUNT(*) as total FROM trades WHERE DATE(created_at) = CURDATE()");
$todayTrades = $stmt->fetch()['total'];

// Today's wins
$stmt = $db->query("SELECT COUNT(*) as total FROM trades WHERE DATE(created_at) = CURDATE() AND result = 'win'");
$todayWins = $stmt->fetch()['total'];

// Win rate today
$winRate = $todayTrades > 0 ? round(($todayWins / $todayTrades) * 100, 1) : 0;

// Subscription revenue this month
$stmt = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM subscriptions WHERE status = 'active' AND MONTH(created_at) = MONTH(CURDATE())");
$monthlyRevenue = $stmt->fetch()['total'];

// Package distribution
$stmt = $db->query("SELECT package, COUNT(*) as count FROM users WHERE role = 'user' AND status = 'active' GROUP BY package");
$packageStats = [];
while ($row = $stmt->fetch()) {
    $packageStats[$row['package']] = $row['count'];
}

// Recent users
$stmt = $db->query("SELECT * FROM users WHERE role = 'user' ORDER BY created_at DESC LIMIT 8");
$recentUsers = $stmt->fetchAll();

// Recent trades
$stmt = $db->query("
    SELECT t.*, u.fullname
    FROM trades t
    JOIN users u ON t.user_id = u.id
    ORDER BY t.created_at DESC
    LIMIT 5
");
$recentTrades = $stmt->fetchAll();
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
        <p class="page-subtitle">Welcome back! Here's your system overview</p>
    </div>
    <div class="d-flex gap-2">
        <span class="admin-clock badge badge-primary">
            <i class="fas fa-clock me-1"></i>
            <span><?php echo date('H:i'); ?></span>
        </span>
    </div>
</div>

<!-- Stats Grid -->
<div class="stat-grid">
    <div class="stat-card primary fade-in">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-value" data-count="<?php echo $totalUsers; ?>"><?php echo $totalUsers; ?></div>
        <div class="stat-label">Total Users</div>
    </div>

    <div class="stat-card warning fade-in">
        <div class="stat-icon">
            <i class="fas fa-user-clock"></i>
        </div>
        <div class="stat-value" data-count="<?php echo $pendingVerifications; ?>"><?php echo $pendingVerifications; ?></div>
        <div class="stat-label">Pending Verification</div>
    </div>

    <div class="stat-card success fade-in">
        <div class="stat-icon">
            <i class="fas fa-user-check"></i>
        </div>
        <div class="stat-value" data-count="<?php echo $activeUsers; ?>"><?php echo $activeUsers; ?></div>
        <div class="stat-label">Active Users</div>
    </div>

    <div class="stat-card info fade-in">
        <div class="stat-icon">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-value" data-count="<?php echo $todayTrades; ?>"><?php echo $todayTrades; ?></div>
        <div class="stat-label">Today's Trades</div>
    </div>

    <div class="stat-card <?php echo $winRate >= 70 ? 'success' : ($winRate >= 50 ? 'warning' : 'danger'); ?> fade-in">
        <div class="stat-icon">
            <i class="fas fa-percentage"></i>
        </div>
        <div class="stat-value"><?php echo $winRate; ?>%</div>
        <div class="stat-label">Today's Win Rate</div>
    </div>

    <div class="stat-card primary fade-in">
        <div class="stat-icon">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-value">$<?php echo number_format($monthlyRevenue); ?></div>
        <div class="stat-label">Monthly Revenue</div>
    </div>
</div>

<!-- Alerts -->
<?php if ($pendingVerifications > 0): ?>
<div class="alert alert-warning fade-in">
    <i class="fas fa-exclamation-triangle"></i>
    <div>
        <strong><?php echo $pendingVerifications; ?> users</strong> pending verification.
        <a href="verify-users.php" class="ms-2">Review now <i class="fas fa-arrow-right"></i></a>
    </div>
</div>
<?php endif; ?>

<div class="row g-4">
    <!-- Package Distribution -->
    <div class="col-lg-4">
        <div class="admin-card fade-in">
            <div class="admin-card-header">
                <h5 class="admin-card-title"><i class="fas fa-pie-chart"></i> Package Distribution</h5>
            </div>
            <div class="admin-card-body">
                <div class="package-stats">
                    <?php
                    $packages = [
                        'free' => ['label' => 'FREE', 'color' => 'secondary', 'icon' => 'fas fa-user'],
                        'pro' => ['label' => 'PRO', 'color' => 'info', 'icon' => 'fas fa-star'],
                        'elite' => ['label' => 'ELITE', 'color' => 'warning', 'icon' => 'fas fa-gem'],
                        'vip' => ['label' => 'VIP', 'color' => 'primary', 'icon' => 'fas fa-crown']
                    ];
                    foreach ($packages as $key => $pkg):
                        $count = $packageStats[$key] ?? 0;
                        $percentage = $activeUsers > 0 ? round(($count / $activeUsers) * 100) : 0;
                    ?>
                    <div class="package-stat-item">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge badge-<?php echo $pkg['color']; ?>">
                                <i class="<?php echo $pkg['icon']; ?> me-1"></i><?php echo $pkg['label']; ?>
                            </span>
                            <span class="text-muted small"><?php echo $count; ?> users</span>
                        </div>
                        <div class="progress" style="height: 6px; background: rgba(255,255,255,0.1); border-radius: 3px;">
                            <div class="progress-bar bg-<?php echo $pkg['color']; ?>" style="width: <?php echo $percentage; ?>%; border-radius: 3px;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Trades -->
    <div class="col-lg-8">
        <div class="admin-card fade-in">
            <div class="admin-card-header">
                <h5 class="admin-card-title"><i class="fas fa-history"></i> Recent Trades</h5>
                <a href="trades.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="admin-card-body" style="padding: 0;">
                <?php if (empty($recentTrades)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="fas fa-chart-line"></i></div>
                    <p class="empty-state-desc">No trades recorded yet</p>
                </div>
                <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Strategy</th>
                            <th>Direction</th>
                            <th>Result</th>
                            <th>P/L</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentTrades as $trade): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($trade['fullname']); ?></strong></td>
                            <td><span class="badge badge-secondary"><?php echo htmlspecialchars($trade['strategy_id']); ?></span></td>
                            <td>
                                <span class="badge badge-<?php echo $trade['direction'] === 'call' ? 'success' : 'danger'; ?>">
                                    <i class="fas fa-arrow-<?php echo $trade['direction'] === 'call' ? 'up' : 'down'; ?> me-1"></i>
                                    <?php echo strtoupper($trade['direction']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($trade['result']): ?>
                                <span class="badge badge-<?php echo $trade['result'] === 'win' ? 'success' : 'danger'; ?>">
                                    <?php echo strtoupper($trade['result']); ?>
                                </span>
                                <?php else: ?>
                                <span class="badge badge-warning">PENDING</span>
                                <?php endif; ?>
                            </td>
                            <td class="<?php echo ($trade['profit_loss'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?>">
                                <?php echo ($trade['profit_loss'] ?? 0) >= 0 ? '+' : ''; ?>$<?php echo number_format($trade['profit_loss'] ?? 0, 2); ?>
                            </td>
                            <td class="text-muted small"><?php echo date('H:i', strtotime($trade['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Users -->
<div class="admin-card mt-4 fade-in">
    <div class="admin-card-header">
        <h5 class="admin-card-title"><i class="fas fa-user-plus"></i> Recent Registrations</h5>
        <a href="users.php" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="admin-card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Country</th>
                        <th>OlympTrade ID</th>
                        <th>Package</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentUsers as $user): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($user['fullname']); ?></strong></td>
                        <td class="text-muted"><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['country']); ?></td>
                        <td><code><?php echo htmlspecialchars($user['olymptrade_id']); ?></code></td>
                        <td>
                            <?php
                            $pkgColors = ['free' => 'secondary', 'pro' => 'info', 'elite' => 'warning', 'vip' => 'primary'];
                            ?>
                            <span class="badge badge-<?php echo $pkgColors[$user['package']] ?? 'secondary'; ?>">
                                <?php echo strtoupper($user['package']); ?>
                            </span>
                        </td>
                        <td>
                            <?php
                            $statusColors = ['pending' => 'warning', 'active' => 'success', 'rejected' => 'danger', 'suspended' => 'secondary'];
                            ?>
                            <span class="badge badge-<?php echo $statusColors[$user['status']] ?? 'secondary'; ?>">
                                <?php echo ucfirst($user['status']); ?>
                            </span>
                        </td>
                        <td class="text-muted small"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <?php if ($user['status'] === 'pending'): ?>
                            <div class="action-btns">
                                <a href="verify-users.php?action=verify&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-success btn-icon" title="Approve" data-confirm="Approve this user?">
                                    <i class="fas fa-check"></i>
                                </a>
                                <a href="verify-users.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger btn-icon" title="Reject">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                            <?php else: ?>
                            <a href="users.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-primary btn-icon" title="View Details">
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

<style>
.package-stat-item {
    margin-bottom: 1.25rem;
}
.package-stat-item:last-child {
    margin-bottom: 0;
}
.progress-bar {
    transition: width 0.6s ease;
}
</style>

<?php require_once 'includes/admin-footer.php'; ?>
