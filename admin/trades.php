<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/language.php';

$page_title = __('admin_trades_title');
require_once 'includes/admin-header.php';

$db = getDBConnection();

// Get filters
$user_filter = $_GET['user'] ?? '';
$strategy_filter = $_GET['strategy'] ?? '';
$result_filter = $_GET['result'] ?? '';
$date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-7 days'));
$date_to = $_GET['date_to'] ?? date('Y-m-d');

// Build query
$sql = "
    SELECT t.*, u.fullname, u.email, u.olymptrade_id
    FROM trades t
    JOIN users u ON t.user_id = u.id
    WHERE DATE(t.created_at) BETWEEN ? AND ?
";
$params = [$date_from, $date_to];

if ($user_filter) {
    $sql .= " AND t.user_id = ?";
    $params[] = $user_filter;
}

if ($strategy_filter) {
    $sql .= " AND t.strategy_id = ?";
    $params[] = $strategy_filter;
}

if ($result_filter) {
    $sql .= " AND t.result = ?";
    $params[] = $result_filter;
}

$sql .= " ORDER BY t.created_at DESC LIMIT 500";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$trades = $stmt->fetchAll();

// Get stats for the period
$statsSql = "
    SELECT
        COUNT(*) as total_trades,
        SUM(CASE WHEN result = 'win' THEN 1 ELSE 0 END) as wins,
        SUM(CASE WHEN result = 'loss' THEN 1 ELSE 0 END) as losses,
        SUM(profit_loss) as total_pnl,
        ROUND(AVG(CASE WHEN result = 'win' THEN 1 ELSE 0 END) * 100, 2) as win_rate
    FROM trades
    WHERE DATE(created_at) BETWEEN ? AND ?
";
$stmt = $db->prepare($statsSql);
$stmt->execute([$date_from, $date_to]);
$stats = $stmt->fetch();

// Get users for filter dropdown
$stmt = $db->query("SELECT id, fullname FROM users WHERE role = 'user' ORDER BY fullname");
$allUsers = $stmt->fetchAll();

// Get unique strategies
$stmt = $db->query("SELECT DISTINCT strategy_id, strategy FROM trades ORDER BY strategy");
$strategies = $stmt->fetchAll();
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-chart-line"></i> <?php _e('admin_trades_title'); ?></h1>
        <p class="page-subtitle"><?php _e('admin_viewing_trades'); ?> <?php echo date('M d', strtotime($date_from)); ?> <?php _e('admin_to'); ?> <?php echo date('M d, Y', strtotime($date_to)); ?></p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i><?php _e('admin_back_dashboard'); ?>
    </a>
</div>

<!-- Stats Row -->
<div class="stat-grid">
    <div class="stat-card primary fade-in">
        <div class="stat-icon"><i class="fas fa-chart-bar"></i></div>
        <div class="stat-value" data-count="<?php echo $stats['total_trades'] ?? 0; ?>"><?php echo number_format($stats['total_trades'] ?? 0); ?></div>
        <div class="stat-label"><?php _e('admin_total_trades'); ?></div>
    </div>
    <div class="stat-card success fade-in">
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        <div class="stat-value" data-count="<?php echo $stats['wins'] ?? 0; ?>"><?php echo number_format($stats['wins'] ?? 0); ?></div>
        <div class="stat-label"><?php _e('admin_wins'); ?></div>
    </div>
    <div class="stat-card danger fade-in">
        <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
        <div class="stat-value" data-count="<?php echo $stats['losses'] ?? 0; ?>"><?php echo number_format($stats['losses'] ?? 0); ?></div>
        <div class="stat-label"><?php _e('admin_losses'); ?></div>
    </div>
    <div class="stat-card <?php echo ($stats['total_pnl'] ?? 0) >= 0 ? 'success' : 'danger'; ?> fade-in">
        <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
        <div class="stat-value"><?php echo ($stats['total_pnl'] ?? 0) >= 0 ? '+' : ''; ?>$<?php echo number_format($stats['total_pnl'] ?? 0, 2); ?></div>
        <div class="stat-label"><?php _e('admin_total_pnl'); ?></div>
    </div>
    <div class="stat-card <?php echo ($stats['win_rate'] ?? 0) >= 60 ? 'success' : (($stats['win_rate'] ?? 0) >= 50 ? 'warning' : 'danger'); ?> fade-in">
        <div class="stat-icon"><i class="fas fa-percentage"></i></div>
        <div class="stat-value"><?php echo number_format($stats['win_rate'] ?? 0, 1); ?>%</div>
        <div class="stat-label"><?php _e('admin_win_rate'); ?></div>
    </div>
</div>

<!-- Filters -->
<div class="admin-card mb-4 fade-in">
    <div class="admin-card-header">
        <h5 class="admin-card-title"><i class="fas fa-filter"></i> <?php _e('admin_filters'); ?></h5>
    </div>
    <div class="admin-card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label small"><?php _e('admin_from_date'); ?></label>
                <input type="date" name="date_from" class="form-control" value="<?php echo $date_from; ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small"><?php _e('admin_to_date'); ?></label>
                <input type="date" name="date_to" class="form-control" value="<?php echo $date_to; ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small"><?php _e('admin_user'); ?></label>
                <select name="user" class="form-select">
                    <option value=""><?php _e('admin_all_users'); ?></option>
                    <?php foreach ($allUsers as $u): ?>
                    <option value="<?php echo $u['id']; ?>" <?php echo $user_filter == $u['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($u['fullname']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small"><?php _e('admin_strategy'); ?></label>
                <select name="strategy" class="form-select">
                    <option value=""><?php _e('admin_all_strategies'); ?></option>
                    <?php foreach ($strategies as $s): ?>
                    <option value="<?php echo $s['strategy_id']; ?>" <?php echo $strategy_filter == $s['strategy_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($s['strategy']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small"><?php _e('admin_result'); ?></label>
                <select name="result" class="form-select">
                    <option value=""><?php _e('admin_all_results'); ?></option>
                    <option value="win" <?php echo $result_filter === 'win' ? 'selected' : ''; ?>><?php _e('admin_wins'); ?></option>
                    <option value="loss" <?php echo $result_filter === 'loss' ? 'selected' : ''; ?>><?php _e('admin_losses'); ?></option>
                    <option value="tie" <?php echo $result_filter === 'tie' ? 'selected' : ''; ?>>Tie</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-2"></i>Apply
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Trades Table -->
<div class="admin-card fade-in">
    <div class="admin-card-header">
        <h5 class="admin-card-title"><i class="fas fa-history"></i> Trade Records</h5>
        <span class="badge badge-primary"><?php echo count($trades); ?> trades (max 500)</span>
    </div>
    <div class="admin-card-body" style="padding: 0;">
        <?php if (empty($trades)): ?>
        <div class="empty-state">
            <div class="empty-state-icon"><i class="fas fa-chart-line"></i></div>
            <h4 class="empty-state-title">No Trades Found</h4>
            <p class="empty-state-desc">No trades found for the selected period and filters.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Strategy</th>
                        <th>Asset</th>
                        <th>Direction</th>
                        <th>Amount</th>
                        <th>Result</th>
                        <th>P/L</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($trades as $trade): ?>
                    <tr>
                        <td><span class="badge badge-secondary">#<?php echo $trade['id']; ?></span></td>
                        <td>
                            <div class="user-cell">
                                <span class="user-name"><?php echo htmlspecialchars($trade['fullname']); ?></span>
                                <span class="user-email"><i class="fas fa-id-card me-1"></i><?php echo htmlspecialchars($trade['olymptrade_id']); ?></span>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-secondary"><?php echo htmlspecialchars($trade['strategy_id']); ?></span>
                            <br><small class="text-muted"><?php echo htmlspecialchars($trade['strategy']); ?></small>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($trade['asset']); ?></strong>
                            <br><small class="text-muted"><?php echo $trade['timeframe']; ?></small>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $trade['direction'] === 'call' ? 'success' : 'danger'; ?>" style="min-width: 70px;">
                                <i class="fas fa-arrow-<?php echo $trade['direction'] === 'call' ? 'up' : 'down'; ?> me-1"></i>
                                <?php echo strtoupper($trade['direction']); ?>
                            </span>
                        </td>
                        <td><strong>$<?php echo number_format($trade['amount'], 0); ?></strong></td>
                        <td>
                            <?php if ($trade['result']): ?>
                            <span class="badge badge-<?php echo $trade['result'] === 'win' ? 'success' : ($trade['result'] === 'loss' ? 'danger' : 'secondary'); ?>">
                                <?php echo strtoupper($trade['result']); ?>
                            </span>
                            <?php else: ?>
                            <span class="badge badge-warning">PENDING</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong class="<?php echo ($trade['profit_loss'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?>">
                                <?php echo ($trade['profit_loss'] ?? 0) >= 0 ? '+' : ''; ?>$<?php echo number_format($trade['profit_loss'] ?? 0, 2); ?>
                            </strong>
                        </td>
                        <td>
                            <span class="d-block"><?php echo date('M d', strtotime($trade['created_at'])); ?></span>
                            <small class="text-muted"><?php echo date('H:i:s', strtotime($trade['created_at'])); ?></small>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Performance Summary -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="admin-card fade-in">
            <div class="admin-card-header">
                <h5 class="admin-card-title"><i class="fas fa-chart-pie"></i> Result Distribution</h5>
            </div>
            <div class="admin-card-body">
                <?php
                $total = ($stats['wins'] ?? 0) + ($stats['losses'] ?? 0);
                $winPercent = $total > 0 ? round(($stats['wins'] / $total) * 100) : 0;
                $lossPercent = $total > 0 ? round(($stats['losses'] / $total) * 100) : 0;
                ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-success"><i class="fas fa-check-circle me-1"></i>Wins</span>
                        <span class="text-success"><?php echo $stats['wins'] ?? 0; ?> (<?php echo $winPercent; ?>%)</span>
                    </div>
                    <div class="progress" style="height: 8px; background: rgba(255,255,255,0.1); border-radius: 4px;">
                        <div class="progress-bar bg-success" style="width: <?php echo $winPercent; ?>%; border-radius: 4px;"></div>
                    </div>
                </div>
                <div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-danger"><i class="fas fa-times-circle me-1"></i>Losses</span>
                        <span class="text-danger"><?php echo $stats['losses'] ?? 0; ?> (<?php echo $lossPercent; ?>%)</span>
                    </div>
                    <div class="progress" style="height: 8px; background: rgba(255,255,255,0.1); border-radius: 4px;">
                        <div class="progress-bar bg-danger" style="width: <?php echo $lossPercent; ?>%; border-radius: 4px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="admin-card fade-in">
            <div class="admin-card-header">
                <h5 class="admin-card-title"><i class="fas fa-info-circle"></i> Quick Stats</h5>
            </div>
            <div class="admin-card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="text-center p-3" style="background: rgba(var(--primary-rgb), 0.05); border-radius: 8px;">
                            <div class="h4 mb-1 text-primary"><?php echo count($trades); ?></div>
                            <small class="text-muted">Displayed Trades</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-3" style="background: rgba(var(--primary-rgb), 0.05); border-radius: 8px;">
                            <?php
                            $avgPL = count($trades) > 0 ? ($stats['total_pnl'] ?? 0) / count($trades) : 0;
                            ?>
                            <div class="h4 mb-1 <?php echo $avgPL >= 0 ? 'text-success' : 'text-danger'; ?>">
                                $<?php echo number_format($avgPL, 2); ?>
                            </div>
                            <small class="text-muted">Avg P/L per Trade</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
