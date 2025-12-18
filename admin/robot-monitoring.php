<?php
$page_title = __('admin_robot_monitoring');
require_once 'includes/admin-header.php';

$db = getDBConnection();

// Get all users with robot settings
$stmt = $db->query("
    SELECT
        u.id,
        u.fullname,
        u.email,
        u.olymptrade_id,
        u.package,
        u.olymptrade_account_type,
        rs.robot_enabled,
        rs.market,
        rs.timeframe,
        rs.trade_amount,
        rs.daily_limit,
        rs.take_profit_target,
        rs.max_loss_limit,
        rst.status as robot_status,
        rst.connection_status,
        rst.balance,
        rst.session_trades,
        rst.session_profit,
        rst.last_active,
        rst.last_trade,
        rst.error_message,
        rst.heartbeat_count
    FROM users u
    LEFT JOIN robot_settings rs ON u.id = rs.user_id
    LEFT JOIN robot_status rst ON u.id = rst.user_id
    WHERE u.status = 'active'
    ORDER BY rs.robot_enabled DESC, rst.last_active DESC
");
$users = $stmt->fetchAll();

// Count active robots
$activeRobots = 0;
$connectedRobots = 0;
$totalTodayTrades = 0;
$totalTodayPnl = 0;

foreach ($users as $user) {
    if ($user['robot_enabled']) $activeRobots++;
    if ($user['connection_status'] === 'connected') $connectedRobots++;
}

// Get today's global stats
$stmt = $db->query("
    SELECT
        COUNT(*) as total_trades,
        SUM(CASE WHEN result = 'win' THEN 1 ELSE 0 END) as wins,
        COALESCE(SUM(profit_loss), 0) as pnl
    FROM trades
    WHERE DATE(created_at) = CURDATE()
");
$todayGlobalStats = $stmt->fetch();

// Get VPS status (simulated - in real world, this would connect to VPS)
$vpsStatus = [
    'status' => 'online',
    'uptime' => '15 days 4 hours',
    'cpu' => rand(15, 45),
    'memory' => rand(30, 60),
    'last_check' => date('Y-m-d H:i:s')
];
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-satellite-dish"></i> Robot Monitoring</h1>
        <p class="page-subtitle">Real-time monitoring of all trading robots</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary" onclick="location.reload()">
            <i class="fas fa-sync-alt me-2"></i>Refresh
        </button>
    </div>
</div>

<!-- VPS Status -->
<div class="admin-card mb-4 fade-in" style="border-color: var(--success);">
    <div class="admin-card-body">
        <div class="row align-items-center">
            <div class="col-md-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="vps-status-icon online">
                        <i class="fas fa-server"></i>
                    </div>
                    <div>
                        <h4 class="mb-0">VPS Status</h4>
                        <span class="badge badge-success">
                            <i class="fas fa-circle me-1" style="font-size: 8px;"></i>
                            ONLINE
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="row text-center">
                    <div class="col">
                        <div class="vps-stat">
                            <span class="vps-stat-value"><?php echo $vpsStatus['uptime']; ?></span>
                            <span class="vps-stat-label">Uptime</span>
                        </div>
                    </div>
                    <div class="col">
                        <div class="vps-stat">
                            <span class="vps-stat-value"><?php echo $vpsStatus['cpu']; ?>%</span>
                            <span class="vps-stat-label">CPU</span>
                        </div>
                    </div>
                    <div class="col">
                        <div class="vps-stat">
                            <span class="vps-stat-value"><?php echo $vpsStatus['memory']; ?>%</span>
                            <span class="vps-stat-label">Memory</span>
                        </div>
                    </div>
                    <div class="col">
                        <div class="vps-stat">
                            <span class="vps-stat-value text-success"><?php echo $connectedRobots; ?></span>
                            <span class="vps-stat-label">Connected</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="stat-grid">
    <div class="stat-card primary fade-in">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-value"><?php echo count($users); ?></div>
        <div class="stat-label">Total Users</div>
    </div>
    <div class="stat-card success fade-in">
        <div class="stat-icon"><i class="fas fa-robot"></i></div>
        <div class="stat-value"><?php echo $activeRobots; ?></div>
        <div class="stat-label">Robots ON</div>
    </div>
    <div class="stat-card info fade-in">
        <div class="stat-icon"><i class="fas fa-wifi"></i></div>
        <div class="stat-value"><?php echo $connectedRobots; ?></div>
        <div class="stat-label">Connected</div>
    </div>
    <div class="stat-card warning fade-in">
        <div class="stat-icon"><i class="fas fa-chart-bar"></i></div>
        <div class="stat-value"><?php echo number_format($todayGlobalStats['total_trades'] ?? 0); ?></div>
        <div class="stat-label">Today's Trades</div>
    </div>
    <div class="stat-card <?php echo ($todayGlobalStats['pnl'] ?? 0) >= 0 ? 'success' : 'danger'; ?> fade-in">
        <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
        <div class="stat-value"><?php echo ($todayGlobalStats['pnl'] ?? 0) >= 0 ? '+' : ''; ?>$<?php echo number_format($todayGlobalStats['pnl'] ?? 0, 2); ?></div>
        <div class="stat-label">Today's P/L</div>
    </div>
</div>

<!-- Active Robots -->
<div class="admin-card mb-4 fade-in">
    <div class="admin-card-header">
        <h5 class="admin-card-title"><i class="fas fa-robot text-success"></i> Active Robots</h5>
        <span class="badge badge-success"><?php echo $activeRobots; ?> Active</span>
    </div>
    <div class="admin-card-body" style="padding: 0;">
        <?php
        $activeUsers = array_filter($users, function($u) { return $u['robot_enabled']; });
        if (empty($activeUsers)):
        ?>
        <div class="empty-state py-4">
            <div class="empty-state-icon"><i class="fas fa-robot"></i></div>
            <h4 class="empty-state-title">No Active Robots</h4>
            <p class="empty-state-desc">No users have their robot enabled currently.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Status</th>
                        <th>Market</th>
                        <th>Account</th>
                        <th>Balance</th>
                        <th>Session</th>
                        <th>Last Active</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activeUsers as $user): ?>
                    <tr>
                        <td>
                            <div class="user-cell">
                                <span class="user-name"><?php echo htmlspecialchars($user['fullname']); ?></span>
                                <span class="user-email">
                                    <i class="fas fa-id-card me-1"></i><?php echo htmlspecialchars($user['olymptrade_id']); ?>
                                </span>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-column gap-1">
                                <span class="badge badge-<?php echo $user['connection_status'] === 'connected' ? 'success' : 'warning'; ?>">
                                    <i class="fas fa-<?php echo $user['connection_status'] === 'connected' ? 'wifi' : 'wifi'; ?> me-1"></i>
                                    <?php echo ucfirst($user['connection_status'] ?? 'unknown'); ?>
                                </span>
                                <?php if ($user['error_message']): ?>
                                <small class="text-danger" title="<?php echo htmlspecialchars($user['error_message']); ?>">
                                    <i class="fas fa-exclamation-triangle"></i> Error
                                </small>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <strong><?php echo $user['market'] ?? 'EUR/USD'; ?></strong>
                            <br><small class="text-muted"><?php echo $user['timeframe'] ?? '15M'; ?></small>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $user['olymptrade_account_type'] === 'demo' ? 'warning' : 'success'; ?>">
                                <?php echo strtoupper($user['olymptrade_account_type'] ?? 'demo'); ?>
                            </span>
                            <br>
                            <span class="badge badge-<?php
                                echo $user['package'] === 'vip' ? 'primary' :
                                    ($user['package'] === 'elite' ? 'warning' :
                                    ($user['package'] === 'pro' ? 'info' : 'secondary'));
                            ?>">
                                <?php echo strtoupper($user['package']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($user['balance']): ?>
                            <strong>$<?php echo number_format($user['balance'], 2); ?></strong>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="d-block"><?php echo $user['session_trades'] ?? 0; ?> trades</span>
                            <span class="<?php echo ($user['session_profit'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?>">
                                <?php echo ($user['session_profit'] ?? 0) >= 0 ? '+' : ''; ?>$<?php echo number_format($user['session_profit'] ?? 0, 2); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($user['last_active']): ?>
                            <span class="d-block"><?php echo date('H:i:s', strtotime($user['last_active'])); ?></span>
                            <small class="text-muted"><?php echo date('M d', strtotime($user['last_active'])); ?></small>
                            <?php else: ?>
                            <span class="text-muted">Never</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="users.php?search=<?php echo urlencode($user['email']); ?>" class="btn btn-sm btn-outline-primary" title="View User">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- All Users Robot Status -->
<div class="admin-card fade-in">
    <div class="admin-card-header">
        <h5 class="admin-card-title"><i class="fas fa-users text-info"></i> All Users</h5>
        <span class="badge badge-primary"><?php echo count($users); ?> users</span>
    </div>
    <div class="admin-card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Package</th>
                        <th>Account</th>
                        <th>Robot</th>
                        <th>Settings</th>
                        <th>Limits</th>
                        <th>Heartbeats</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr class="<?php echo $user['robot_enabled'] ? '' : 'opacity-50'; ?>">
                        <td>
                            <div class="user-cell">
                                <span class="user-name"><?php echo htmlspecialchars($user['fullname']); ?></span>
                                <span class="user-email"><?php echo htmlspecialchars($user['email']); ?></span>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-<?php
                                echo $user['package'] === 'vip' ? 'primary' :
                                    ($user['package'] === 'elite' ? 'warning' :
                                    ($user['package'] === 'pro' ? 'info' : 'secondary'));
                            ?>">
                                <?php echo strtoupper($user['package'] ?? 'free'); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo ($user['olymptrade_account_type'] ?? 'demo') === 'demo' ? 'warning' : 'success'; ?>">
                                <?php echo strtoupper($user['olymptrade_account_type'] ?? 'demo'); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $user['robot_enabled'] ? 'success' : 'secondary'; ?>">
                                <?php echo $user['robot_enabled'] ? 'ON' : 'OFF'; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($user['market']): ?>
                            <span class="d-block"><?php echo $user['market']; ?> / <?php echo $user['timeframe']; ?></span>
                            <small class="text-muted">$<?php echo number_format($user['trade_amount'] ?? 0); ?>/trade</small>
                            <?php else: ?>
                            <span class="text-muted">Not configured</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($user['daily_limit']): ?>
                            <span class="d-block"><?php echo $user['daily_limit']; ?> trades/day</span>
                            <small class="text-muted">
                                TP: $<?php echo $user['take_profit_target'] ?? 0; ?> /
                                ML: $<?php echo $user['max_loss_limit'] ?? 0; ?>
                            </small>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge badge-secondary">
                                <i class="fas fa-heartbeat me-1"></i>
                                <?php echo number_format($user['heartbeat_count'] ?? 0); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.vps-status-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.vps-status-icon.online {
    background: rgba(16, 185, 129, 0.15);
    color: var(--success);
}

.vps-status-icon.offline {
    background: rgba(239, 68, 68, 0.15);
    color: var(--danger);
}

.vps-stat {
    display: flex;
    flex-direction: column;
}

.vps-stat-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-primary);
}

.vps-stat-label {
    font-size: 0.75rem;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.opacity-50 {
    opacity: 0.5;
}

.sidebar-section-title {
    font-size: 0.65rem;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 1rem 1.25rem 0.5rem;
    margin-top: 0.5rem;
}
</style>

<script>
// Auto-refresh every 30 seconds
setTimeout(function() {
    location.reload();
}, 30000);
</script>

<?php require_once 'includes/admin-footer.php'; ?>
