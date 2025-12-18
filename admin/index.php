<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/language.php';

$page_title = __('admin_dashboard');
require_once 'includes/admin-header.php';

// Initialize default values
$totalUsers = 0;
$pendingVerifications = 0;
$activeUsers = 0;
$todayTrades = 0;
$todayWins = 0;
$winRate = 0;
$monthlyRevenue = 0;
$packageStats = [];
$recentUsers = [];
$recentTrades = [];
$dbError = false;

// Get statistics
$db = getDBConnection();

if ($db) {
    try {
        // Total users
        $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
        if ($stmt) $totalUsers = $stmt->fetch()['total'] ?? 0;

        // Pending verifications
        $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE status = 'pending'");
        if ($stmt) $pendingVerifications = $stmt->fetch()['total'] ?? 0;

        // Active users
        $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE status = 'active'");
        if ($stmt) $activeUsers = $stmt->fetch()['total'] ?? 0;

        // Today's trades
        $stmt = $db->query("SELECT COUNT(*) as total FROM trades WHERE DATE(created_at) = CURDATE()");
        if ($stmt) $todayTrades = $stmt->fetch()['total'] ?? 0;

        // Today's wins
        $stmt = $db->query("SELECT COUNT(*) as total FROM trades WHERE DATE(created_at) = CURDATE() AND result = 'win'");
        if ($stmt) $todayWins = $stmt->fetch()['total'] ?? 0;

        // Win rate today
        $winRate = $todayTrades > 0 ? round(($todayWins / $todayTrades) * 100, 1) : 0;

        // Subscription revenue this month
        $stmt = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM subscriptions WHERE status = 'active' AND MONTH(created_at) = MONTH(CURDATE())");
        if ($stmt) $monthlyRevenue = $stmt->fetch()['total'] ?? 0;

        // Package distribution
        $stmt = $db->query("SELECT package, COUNT(*) as count FROM users WHERE role = 'user' AND status = 'active' GROUP BY package");
        if ($stmt) {
            while ($row = $stmt->fetch()) {
                $packageStats[$row['package']] = $row['count'];
            }
        }

        // Recent users
        $stmt = $db->query("SELECT * FROM users WHERE role = 'user' ORDER BY created_at DESC LIMIT 8");
        if ($stmt) $recentUsers = $stmt->fetchAll() ?: [];

        // Recent trades
        $stmt = $db->query("
            SELECT t.*, u.fullname
            FROM trades t
            JOIN users u ON t.user_id = u.id
            ORDER BY t.created_at DESC
            LIMIT 5
        ");
        if ($stmt) $recentTrades = $stmt->fetchAll() ?: [];
    } catch (Exception $e) {
        error_log("Admin dashboard error: " . $e->getMessage());
        $dbError = true;
    }
} else {
    $dbError = true;
}
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-tachometer-alt"></i> <?php _e('admin_dashboard'); ?></h1>
        <p class="page-subtitle"><?php _e('admin_welcome'); ?></p>
    </div>
    <div class="d-flex gap-2">
        <span class="admin-clock badge badge-primary">
            <i class="fas fa-clock me-1"></i>
            <span><?php echo date('H:i'); ?></span>
        </span>
    </div>
</div>

<?php if ($dbError): ?>
<div class="alert alert-danger fade-in">
    <i class="fas fa-database"></i>
    <div>
        <strong><?php _e('admin_db_error'); ?></strong> <?php _e('admin_db_error_desc'); ?>
    </div>
</div>
<?php endif; ?>

<!-- Stats Grid -->
<div class="stat-grid">
    <div class="stat-card primary fade-in">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-value" data-count="<?php echo $totalUsers; ?>"><?php echo $totalUsers; ?></div>
        <div class="stat-label"><?php _e('admin_total_users'); ?></div>
    </div>

    <div class="stat-card warning fade-in">
        <div class="stat-icon">
            <i class="fas fa-user-clock"></i>
        </div>
        <div class="stat-value" data-count="<?php echo $pendingVerifications; ?>"><?php echo $pendingVerifications; ?></div>
        <div class="stat-label"><?php _e('admin_pending_verification'); ?></div>
    </div>

    <div class="stat-card success fade-in">
        <div class="stat-icon">
            <i class="fas fa-user-check"></i>
        </div>
        <div class="stat-value" data-count="<?php echo $activeUsers; ?>"><?php echo $activeUsers; ?></div>
        <div class="stat-label"><?php _e('admin_active_users'); ?></div>
    </div>

    <div class="stat-card info fade-in">
        <div class="stat-icon">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-value" data-count="<?php echo $todayTrades; ?>"><?php echo $todayTrades; ?></div>
        <div class="stat-label"><?php _e('admin_today_trades'); ?></div>
    </div>

    <div class="stat-card <?php echo $winRate >= 70 ? 'success' : ($winRate >= 50 ? 'warning' : 'danger'); ?> fade-in">
        <div class="stat-icon">
            <i class="fas fa-percentage"></i>
        </div>
        <div class="stat-value"><?php echo $winRate; ?>%</div>
        <div class="stat-label"><?php _e('admin_today_winrate'); ?></div>
    </div>

    <div class="stat-card primary fade-in">
        <div class="stat-icon">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-value">$<?php echo number_format($monthlyRevenue); ?></div>
        <div class="stat-label"><?php _e('admin_monthly_revenue'); ?></div>
    </div>
</div>

<!-- Alerts -->
<?php if ($pendingVerifications > 0): ?>
<div class="alert alert-warning fade-in">
    <i class="fas fa-exclamation-triangle"></i>
    <div>
        <strong><?php echo $pendingVerifications; ?></strong> <?php _e('admin_pending_alert'); ?>
        <a href="verify-users.php" class="ms-2"><?php _e('admin_review_now'); ?> <i class="fas fa-arrow-right"></i></a>
    </div>
</div>
<?php endif; ?>

<div class="row g-4">
    <!-- Package Distribution -->
    <div class="col-lg-4">
        <div class="admin-card fade-in">
            <div class="admin-card-header">
                <h5 class="admin-card-title"><i class="fas fa-pie-chart"></i> <?php _e('admin_package_dist'); ?></h5>
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
                <h5 class="admin-card-title"><i class="fas fa-history"></i> <?php _e('admin_recent_trades'); ?></h5>
                <a href="trades.php" class="btn btn-sm btn-outline-primary"><?php _e('admin_view_all'); ?></a>
            </div>
            <div class="admin-card-body" style="padding: 0;">
                <?php if (empty($recentTrades)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="fas fa-chart-line"></i></div>
                    <p class="empty-state-desc"><?php _e('admin_no_trades'); ?></p>
                </div>
                <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th><?php _e('admin_user'); ?></th>
                            <th><?php _e('admin_strategy'); ?></th>
                            <th><?php _e('admin_direction'); ?></th>
                            <th><?php _e('admin_result'); ?></th>
                            <th><?php _e('admin_pnl'); ?></th>
                            <th><?php _e('admin_time'); ?></th>
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
        <h5 class="admin-card-title"><i class="fas fa-user-plus"></i> <?php _e('admin_recent_reg'); ?></h5>
        <a href="users.php" class="btn btn-sm btn-outline-primary"><?php _e('admin_view_all'); ?></a>
    </div>
    <div class="admin-card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th><?php _e('admin_user'); ?></th>
                        <th><?php _e('admin_email'); ?></th>
                        <th><?php _e('admin_country'); ?></th>
                        <th><?php _e('admin_ot_id'); ?></th>
                        <th><?php _e('admin_package'); ?></th>
                        <th><?php _e('admin_status'); ?></th>
                        <th><?php _e('admin_registered'); ?></th>
                        <th><?php _e('admin_actions'); ?></th>
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
                                <a href="verify-users.php" class="btn btn-sm btn-success btn-icon" title="<?php _e('admin_review_user'); ?>">
                                    <i class="fas fa-user-check"></i>
                                </a>
                            </div>
                            <?php else: ?>
                            <a href="users.php?search=<?php echo urlencode($user['email']); ?>" class="btn btn-sm btn-outline-primary btn-icon" title="<?php _e('admin_view_details'); ?>">
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
