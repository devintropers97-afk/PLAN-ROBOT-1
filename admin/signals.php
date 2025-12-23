<?php
$page_title = __('admin_signals_title') ?: 'Signal History';
require_once 'includes/admin-header.php';

$db = getDBConnection();

// Get filters
$dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('-7 days'));
$dateTo = $_GET['date_to'] ?? date('Y-m-d');
$strategyFilter = $_GET['strategy'] ?? '';
$resultFilter = $_GET['result'] ?? '';

// Build query
$sql = "SELECT * FROM signals WHERE DATE(created_at) BETWEEN ? AND ?";
$params = [$dateFrom, $dateTo];

if ($strategyFilter) {
    $sql .= " AND strategy_id = ?";
    $params[] = $strategyFilter;
}

if ($resultFilter) {
    $sql .= " AND result = ?";
    $params[] = $resultFilter;
}

$sql .= " ORDER BY created_at DESC LIMIT 500";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$signals = $stmt->fetchAll();

// Get stats
$stmt = $db->prepare("
    SELECT
        COUNT(*) as total,
        SUM(CASE WHEN result = 'win' THEN 1 ELSE 0 END) as wins,
        SUM(CASE WHEN result = 'loss' THEN 1 ELSE 0 END) as losses,
        SUM(CASE WHEN result = 'pending' THEN 1 ELSE 0 END) as pending,
        AVG(confidence) as avg_confidence
    FROM signals
    WHERE DATE(created_at) BETWEEN ? AND ?
");
$stmt->execute([$dateFrom, $dateTo]);
$stats = $stmt->fetch();

// Calculate win rate
$totalCompleted = ($stats['wins'] ?? 0) + ($stats['losses'] ?? 0);
$winRate = $totalCompleted > 0 ? round(($stats['wins'] / $totalCompleted) * 100, 1) : 0;

// Get strategy performance
$stmt = $db->prepare("
    SELECT
        strategy_id,
        strategy_name,
        COUNT(*) as total,
        SUM(CASE WHEN result = 'win' THEN 1 ELSE 0 END) as wins,
        SUM(CASE WHEN result = 'loss' THEN 1 ELSE 0 END) as losses,
        AVG(confidence) as avg_confidence
    FROM signals
    WHERE DATE(created_at) BETWEEN ? AND ?
    GROUP BY strategy_id, strategy_name
    ORDER BY wins DESC
");
$stmt->execute([$dateFrom, $dateTo]);
$strategyPerf = $stmt->fetchAll();

// Get unique strategies for filter
$stmt = $db->query("SELECT DISTINCT strategy_id, strategy_name FROM signals ORDER BY strategy_name");
$strategies = $stmt->fetchAll();

// Get today's signals
$stmt = $db->query("
    SELECT
        COUNT(*) as total,
        SUM(CASE WHEN result = 'win' THEN 1 ELSE 0 END) as wins,
        SUM(CASE WHEN result = 'loss' THEN 1 ELSE 0 END) as losses
    FROM signals
    WHERE DATE(created_at) = CURDATE()
");
$todayStats = $stmt->fetch();
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-broadcast-tower"></i> <?php _e('admin_signals_title'); ?></h1>
        <p class="page-subtitle"><?php _e('admin_signals_subtitle'); ?></p>
    </div>
</div>

<!-- Stats Grid -->
<div class="stat-grid">
    <div class="stat-card primary fade-in">
        <div class="stat-icon"><i class="fas fa-broadcast-tower"></i></div>
        <div class="stat-value"><?php echo number_format($stats['total'] ?? 0); ?></div>
        <div class="stat-label"><?php _e('admin_total_signals'); ?></div>
    </div>
    <div class="stat-card success fade-in">
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        <div class="stat-value"><?php echo number_format($stats['wins'] ?? 0); ?></div>
        <div class="stat-label"><?php _e('admin_winning_signals'); ?></div>
    </div>
    <div class="stat-card danger fade-in">
        <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
        <div class="stat-value"><?php echo number_format($stats['losses'] ?? 0); ?></div>
        <div class="stat-label"><?php _e('admin_losing_signals'); ?></div>
    </div>
    <div class="stat-card <?php echo $winRate >= 60 ? 'success' : ($winRate >= 50 ? 'warning' : 'danger'); ?> fade-in">
        <div class="stat-icon"><i class="fas fa-percentage"></i></div>
        <div class="stat-value"><?php echo $winRate; ?>%</div>
        <div class="stat-label"><?php _e('admin_winrate_label'); ?></div>
    </div>
    <div class="stat-card info fade-in">
        <div class="stat-icon"><i class="fas fa-brain"></i></div>
        <div class="stat-value"><?php echo number_format($stats['avg_confidence'] ?? 0, 0); ?>%</div>
        <div class="stat-label"><?php _e('admin_avg_confidence'); ?></div>
    </div>
</div>

<!-- Filters -->
<div class="admin-card mb-4 fade-in">
    <div class="admin-card-header">
        <h5 class="admin-card-title"><i class="fas fa-filter"></i> <?php _e('admin_filters'); ?></h5>
    </div>
    <div class="admin-card-body py-3">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label small"><?php _e('admin_from_date'); ?></label>
                <input type="date" name="date_from" class="form-control" value="<?php echo $dateFrom; ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small"><?php _e('admin_to_date'); ?></label>
                <input type="date" name="date_to" class="form-control" value="<?php echo $dateTo; ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small"><?php _e('admin_th_strategy'); ?></label>
                <select name="strategy" class="form-select">
                    <option value=""><?php _e('admin_all_strategies'); ?></option>
                    <?php foreach ($strategies as $s): ?>
                    <option value="<?php echo $s['strategy_id']; ?>" <?php echo $strategyFilter === $s['strategy_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($s['strategy_name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small"><?php _e('admin_th_result'); ?></label>
                <select name="result" class="form-select">
                    <option value=""><?php _e('admin_all_results'); ?></option>
                    <option value="win" <?php echo $resultFilter === 'win' ? 'selected' : ''; ?>><?php _e('admin_result_win'); ?></option>
                    <option value="loss" <?php echo $resultFilter === 'loss' ? 'selected' : ''; ?>><?php _e('admin_result_loss'); ?></option>
                    <option value="pending" <?php echo $resultFilter === 'pending' ? 'selected' : ''; ?>><?php _e('admin_status_pending'); ?></option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-2"></i><?php _e('admin_filter'); ?>
                </button>
            </div>
            <div class="col-md-2">
                <div class="btn-group w-100">
                    <a href="?date_from=<?php echo date('Y-m-d'); ?>&date_to=<?php echo date('Y-m-d'); ?>" class="btn btn-outline-secondary"><?php _e('admin_today'); ?></a>
                    <a href="?date_from=<?php echo date('Y-m-d', strtotime('-7 days')); ?>&date_to=<?php echo date('Y-m-d'); ?>" class="btn btn-outline-secondary">7D</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <!-- Strategy Performance -->
    <div class="col-lg-4 mb-4">
        <div class="admin-card h-100 fade-in">
            <div class="admin-card-header">
                <h5 class="admin-card-title"><i class="fas fa-chart-pie text-warning"></i> <?php _e('admin_strategy_performance'); ?></h5>
            </div>
            <div class="admin-card-body">
                <?php if (empty($strategyPerf)): ?>
                <div class="text-center text-muted py-4">
                    <i class="fas fa-chart-pie fa-2x mb-2"></i>
                    <p><?php _e('admin_no_data_available'); ?></p>
                </div>
                <?php else: ?>
                <div class="strategy-list">
                    <?php foreach ($strategyPerf as $perf):
                        $stratWinRate = ($perf['wins'] + $perf['losses']) > 0
                            ? round(($perf['wins'] / ($perf['wins'] + $perf['losses'])) * 100, 1)
                            : 0;
                    ?>
                    <div class="strategy-item">
                        <div class="strategy-header">
                            <span class="strategy-name"><?php echo htmlspecialchars($perf['strategy_name']); ?></span>
                            <span class="badge badge-<?php echo $stratWinRate >= 60 ? 'success' : ($stratWinRate >= 50 ? 'warning' : 'danger'); ?>">
                                <?php echo $stratWinRate; ?>%
                            </span>
                        </div>
                        <div class="strategy-stats">
                            <span class="text-muted"><?php echo $perf['total']; ?> <?php _e('admin_signals_label'); ?></span>
                            <span class="text-success"><?php echo $perf['wins']; ?> W</span>
                            <span class="text-danger"><?php echo $perf['losses']; ?> L</span>
                        </div>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-success" style="width: <?php echo $stratWinRate; ?>%;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Today's Summary -->
    <div class="col-lg-8 mb-4">
        <div class="admin-card h-100 fade-in">
            <div class="admin-card-header">
                <h5 class="admin-card-title"><i class="fas fa-calendar-day text-info"></i> <?php _e('admin_todays_signals'); ?></h5>
            </div>
            <div class="admin-card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="today-stat">
                            <div class="today-stat-value"><?php echo $todayStats['total'] ?? 0; ?></div>
                            <div class="today-stat-label"><?php _e('admin_total_signals'); ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="today-stat">
                            <div class="today-stat-value text-success"><?php echo $todayStats['wins'] ?? 0; ?></div>
                            <div class="today-stat-label"><?php _e('admin_wins'); ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="today-stat">
                            <div class="today-stat-value text-danger"><?php echo $todayStats['losses'] ?? 0; ?></div>
                            <div class="today-stat-label"><?php _e('admin_losses'); ?></div>
                        </div>
                    </div>
                </div>

                <?php
                $todayWinRate = (($todayStats['wins'] ?? 0) + ($todayStats['losses'] ?? 0)) > 0
                    ? round(($todayStats['wins'] / (($todayStats['wins'] ?? 0) + ($todayStats['losses'] ?? 0))) * 100, 1)
                    : 0;
                ?>
                <div class="mt-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span><?php _e('admin_today_winrate'); ?></span>
                        <strong class="<?php echo $todayWinRate >= 60 ? 'text-success' : ($todayWinRate >= 50 ? 'text-warning' : 'text-danger'); ?>">
                            <?php echo $todayWinRate; ?>%
                        </strong>
                    </div>
                    <div class="progress" style="height: 10px; border-radius: 5px;">
                        <div class="progress-bar bg-success" style="width: <?php echo $todayWinRate; ?>%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Signals Table -->
<div class="admin-card fade-in">
    <div class="admin-card-header">
        <h5 class="admin-card-title"><i class="fas fa-list"></i> <?php _e('admin_signal_records'); ?></h5>
        <span class="badge badge-primary"><?php echo count($signals); ?> <?php _e('admin_signals_label'); ?></span>
    </div>
    <div class="admin-card-body" style="padding: 0;">
        <?php if (empty($signals)): ?>
        <div class="empty-state py-4">
            <div class="empty-state-icon"><i class="fas fa-broadcast-tower"></i></div>
            <h4 class="empty-state-title"><?php _e('admin_no_signals_found'); ?></h4>
            <p class="empty-state-desc"><?php _e('admin_no_signals_match'); ?></p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th><?php _e('admin_th_time'); ?></th>
                        <th><?php _e('admin_th_strategy'); ?></th>
                        <th><?php _e('admin_th_asset'); ?></th>
                        <th><?php _e('admin_th_direction'); ?></th>
                        <th><?php _e('admin_th_confidence'); ?></th>
                        <th><?php _e('admin_th_entry_price'); ?></th>
                        <th><?php _e('admin_th_result'); ?></th>
                        <th><?php _e('admin_th_subscribers'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($signals as $signal): ?>
                    <tr>
                        <td>
                            <span class="d-block"><?php echo date('M d', strtotime($signal['created_at'])); ?></span>
                            <small class="text-muted"><?php echo date('H:i:s', strtotime($signal['created_at'])); ?></small>
                        </td>
                        <td>
                            <span class="badge badge-secondary"><?php echo $signal['strategy_id']; ?></span>
                            <br><small class="text-muted"><?php echo htmlspecialchars($signal['strategy_name']); ?></small>
                        </td>
                        <td>
                            <strong><?php echo $signal['asset']; ?></strong>
                            <br><small class="text-muted"><?php echo $signal['timeframe']; ?></small>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $signal['direction'] === 'call' ? 'success' : 'danger'; ?>" style="min-width: 70px;">
                                <i class="fas fa-arrow-<?php echo $signal['direction'] === 'call' ? 'up' : 'down'; ?> me-1"></i>
                                <?php echo strtoupper($signal['direction']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="confidence-meter">
                                <div class="confidence-bar" style="width: <?php echo $signal['confidence']; ?>%;
                                    background: <?php echo $signal['confidence'] >= 80 ? 'var(--success)' : ($signal['confidence'] >= 60 ? 'var(--warning)' : 'var(--danger)'); ?>;">
                                </div>
                            </div>
                            <small class="text-muted"><?php echo $signal['confidence']; ?>%</small>
                        </td>
                        <td>
                            <?php if ($signal['entry_price']): ?>
                            <code><?php echo number_format($signal['entry_price'], 5); ?></code>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($signal['result'] && $signal['result'] !== 'pending'): ?>
                            <span class="badge badge-<?php echo $signal['result'] === 'win' ? 'success' : ($signal['result'] === 'loss' ? 'danger' : 'secondary'); ?>">
                                <?php echo strtoupper($signal['result']); ?>
                            </span>
                            <?php else: ?>
                            <span class="badge badge-warning">PENDING</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge badge-secondary">
                                <i class="fas fa-users me-1"></i>
                                <?php echo $signal['subscribers_count'] ?? 0; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.strategy-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.strategy-item {
    padding: 1rem;
    background: rgba(255, 255, 255, 0.03);
    border-radius: 8px;
    border: 1px solid rgba(255, 255, 255, 0.05);
}

.strategy-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.strategy-name {
    font-weight: 600;
}

.strategy-stats {
    display: flex;
    gap: 1rem;
    font-size: 0.85rem;
}

.today-stat {
    padding: 1.5rem;
    background: rgba(255, 255, 255, 0.03);
    border-radius: 12px;
}

.today-stat-value {
    font-size: 2.5rem;
    font-weight: 700;
    line-height: 1;
}

.today-stat-label {
    font-size: 0.85rem;
    color: var(--text-secondary);
    margin-top: 0.5rem;
}

.confidence-meter {
    width: 60px;
    height: 6px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 3px;
    overflow: hidden;
}

.confidence-bar {
    height: 100%;
    border-radius: 3px;
    transition: width 0.3s ease;
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

<?php require_once 'includes/admin-footer.php'; ?>
