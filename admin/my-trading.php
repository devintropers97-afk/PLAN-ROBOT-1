<?php
$page_title = 'My Trading';
require_once 'includes/admin-header.php';

$db = getDBConnection();
$adminId = $_SESSION['user_id'];

// Get admin user data
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$adminId]);
$adminUser = $stmt->fetch();

// Get robot settings
$stmt = $db->prepare("SELECT * FROM robot_settings WHERE user_id = ?");
$stmt->execute([$adminId]);
$robotSettings = $stmt->fetch();

// Get date filters
$dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('-7 days'));
$dateTo = $_GET['date_to'] ?? date('Y-m-d');

// Get admin's trades
$stmt = $db->prepare("
    SELECT *
    FROM trades
    WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ?
    ORDER BY created_at DESC
    LIMIT 100
");
$stmt->execute([$adminId, $dateFrom, $dateTo]);
$trades = $stmt->fetchAll();

// Get stats for the period
$stmt = $db->prepare("
    SELECT
        COUNT(*) as total_trades,
        SUM(CASE WHEN result = 'win' THEN 1 ELSE 0 END) as wins,
        SUM(CASE WHEN result = 'loss' THEN 1 ELSE 0 END) as losses,
        COALESCE(SUM(profit_loss), 0) as total_pnl,
        COALESCE(AVG(CASE WHEN result IN ('win', 'loss') THEN profit_loss END), 0) as avg_trade,
        MAX(profit_loss) as best_trade,
        MIN(profit_loss) as worst_trade
    FROM trades
    WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ?
");
$stmt->execute([$adminId, $dateFrom, $dateTo]);
$stats = $stmt->fetch();

// Calculate win rate
$totalCompleted = ($stats['wins'] ?? 0) + ($stats['losses'] ?? 0);
$winRate = $totalCompleted > 0 ? round(($stats['wins'] / $totalCompleted) * 100, 1) : 0;

// Get today's stats
$stmt = $db->prepare("
    SELECT
        COUNT(*) as total,
        SUM(CASE WHEN result = 'win' THEN 1 ELSE 0 END) as wins,
        COALESCE(SUM(profit_loss), 0) as pnl
    FROM trades
    WHERE user_id = ? AND DATE(created_at) = CURDATE()
");
$stmt->execute([$adminId]);
$todayStats = $stmt->fetch();

// Get daily stats for chart
$stmt = $db->prepare("
    SELECT
        DATE(created_at) as date,
        COUNT(*) as trades,
        SUM(CASE WHEN result = 'win' THEN 1 ELSE 0 END) as wins,
        SUM(profit_loss) as pnl
    FROM trades
    WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ?
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");
$stmt->execute([$adminId, $dateFrom, $dateTo]);
$dailyStats = $stmt->fetchAll();

// Get strategy performance
$stmt = $db->prepare("
    SELECT
        strategy_id,
        strategy,
        COUNT(*) as total,
        SUM(CASE WHEN result = 'win' THEN 1 ELSE 0 END) as wins,
        SUM(CASE WHEN result = 'loss' THEN 1 ELSE 0 END) as losses,
        SUM(profit_loss) as pnl
    FROM trades
    WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ?
    GROUP BY strategy_id, strategy
    ORDER BY pnl DESC
");
$stmt->execute([$adminId, $dateFrom, $dateTo]);
$strategyPerf = $stmt->fetchAll();

// Get cumulative PnL for chart
$cumulativePnl = [];
$runningTotal = 0;
foreach ($dailyStats as $day) {
    $runningTotal += $day['pnl'];
    $cumulativePnl[] = ['date' => $day['date'], 'pnl' => $runningTotal];
}
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-chart-area"></i> My Trading</h1>
        <p class="page-subtitle">Your personal trading performance</p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <span class="badge badge-<?php echo $adminUser['olymptrade_account_type'] === 'demo' ? 'warning' : 'success'; ?> badge-lg">
            <i class="fas fa-<?php echo $adminUser['olymptrade_account_type'] === 'demo' ? 'flask' : 'dollar-sign'; ?> me-1"></i>
            <?php echo strtoupper($adminUser['olymptrade_account_type'] ?? 'demo'); ?> Account
        </span>
        <a href="my-robot.php" class="btn btn-primary">
            <i class="fas fa-robot me-2"></i>Robot Settings
        </a>
    </div>
</div>

<!-- Stats Grid -->
<div class="stat-grid">
    <div class="stat-card primary fade-in">
        <div class="stat-icon"><i class="fas fa-chart-bar"></i></div>
        <div class="stat-value"><?php echo number_format($stats['total_trades'] ?? 0); ?></div>
        <div class="stat-label">Total Trades</div>
    </div>
    <div class="stat-card success fade-in">
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        <div class="stat-value"><?php echo number_format($stats['wins'] ?? 0); ?></div>
        <div class="stat-label">Wins</div>
    </div>
    <div class="stat-card danger fade-in">
        <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
        <div class="stat-value"><?php echo number_format($stats['losses'] ?? 0); ?></div>
        <div class="stat-label">Losses</div>
    </div>
    <div class="stat-card <?php echo $winRate >= 60 ? 'success' : ($winRate >= 50 ? 'warning' : 'danger'); ?> fade-in">
        <div class="stat-icon"><i class="fas fa-percentage"></i></div>
        <div class="stat-value"><?php echo $winRate; ?>%</div>
        <div class="stat-label">Win Rate</div>
    </div>
    <div class="stat-card <?php echo ($stats['total_pnl'] ?? 0) >= 0 ? 'success' : 'danger'; ?> fade-in">
        <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
        <div class="stat-value"><?php echo ($stats['total_pnl'] ?? 0) >= 0 ? '+' : ''; ?>$<?php echo number_format($stats['total_pnl'] ?? 0, 2); ?></div>
        <div class="stat-label">Total P/L</div>
    </div>
</div>

<!-- Date Filter -->
<div class="admin-card mb-4 fade-in">
    <div class="admin-card-body py-3">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">From Date</label>
                <input type="date" name="date_from" class="form-control" value="<?php echo $dateFrom; ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small">To Date</label>
                <input type="date" name="date_to" class="form-control" value="<?php echo $dateTo; ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-2"></i>Filter
                </button>
            </div>
            <div class="col-md-4 text-end">
                <div class="btn-group">
                    <a href="?date_from=<?php echo date('Y-m-d'); ?>&date_to=<?php echo date('Y-m-d'); ?>" class="btn btn-outline-secondary">Today</a>
                    <a href="?date_from=<?php echo date('Y-m-d', strtotime('-7 days')); ?>&date_to=<?php echo date('Y-m-d'); ?>" class="btn btn-outline-secondary">7 Days</a>
                    <a href="?date_from=<?php echo date('Y-m-d', strtotime('-30 days')); ?>&date_to=<?php echo date('Y-m-d'); ?>" class="btn btn-outline-secondary">30 Days</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <!-- P/L Chart -->
    <div class="col-lg-8 mb-4">
        <div class="admin-card h-100 fade-in">
            <div class="admin-card-header">
                <h5 class="admin-card-title"><i class="fas fa-chart-line text-primary"></i> Cumulative P/L</h5>
            </div>
            <div class="admin-card-body">
                <canvas id="pnlChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Today's Summary -->
    <div class="col-lg-4 mb-4">
        <div class="admin-card h-100 fade-in">
            <div class="admin-card-header">
                <h5 class="admin-card-title"><i class="fas fa-calendar-day text-info"></i> Today's Summary</h5>
            </div>
            <div class="admin-card-body">
                <div class="today-summary">
                    <div class="summary-item">
                        <span class="summary-label">Trades</span>
                        <span class="summary-value"><?php echo $todayStats['total'] ?? 0; ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Wins</span>
                        <span class="summary-value text-success"><?php echo $todayStats['wins'] ?? 0; ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Win Rate</span>
                        <span class="summary-value">
                            <?php
                            $todayWinRate = ($todayStats['total'] ?? 0) > 0
                                ? round(($todayStats['wins'] / $todayStats['total']) * 100, 1)
                                : 0;
                            echo $todayWinRate . '%';
                            ?>
                        </span>
                    </div>
                    <div class="summary-item highlight">
                        <span class="summary-label">P/L</span>
                        <span class="summary-value <?php echo ($todayStats['pnl'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?>">
                            <?php echo ($todayStats['pnl'] ?? 0) >= 0 ? '+' : ''; ?>$<?php echo number_format($todayStats['pnl'] ?? 0, 2); ?>
                        </span>
                    </div>
                </div>

                <div class="mt-4">
                    <h6 class="mb-3">Quick Stats</h6>
                    <div class="quick-stat">
                        <span>Best Trade</span>
                        <span class="text-success">+$<?php echo number_format($stats['best_trade'] ?? 0, 2); ?></span>
                    </div>
                    <div class="quick-stat">
                        <span>Worst Trade</span>
                        <span class="text-danger">$<?php echo number_format($stats['worst_trade'] ?? 0, 2); ?></span>
                    </div>
                    <div class="quick-stat">
                        <span>Avg Trade</span>
                        <span class="<?php echo ($stats['avg_trade'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?>">
                            $<?php echo number_format($stats['avg_trade'] ?? 0, 2); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Strategy Performance -->
<div class="admin-card mb-4 fade-in">
    <div class="admin-card-header">
        <h5 class="admin-card-title"><i class="fas fa-brain text-warning"></i> Strategy Performance</h5>
    </div>
    <div class="admin-card-body" style="padding: 0;">
        <?php if (empty($strategyPerf)): ?>
        <div class="empty-state py-4">
            <div class="empty-state-icon"><i class="fas fa-chart-pie"></i></div>
            <p class="empty-state-desc">No trades recorded yet</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Strategy</th>
                        <th>Trades</th>
                        <th>Wins</th>
                        <th>Losses</th>
                        <th>Win Rate</th>
                        <th>P/L</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($strategyPerf as $perf):
                        $stratWinRate = $perf['total'] > 0 ? round(($perf['wins'] / $perf['total']) * 100, 1) : 0;
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($perf['strategy']); ?></strong>
                            <br><small class="text-muted"><?php echo $perf['strategy_id']; ?></small>
                        </td>
                        <td><?php echo $perf['total']; ?></td>
                        <td class="text-success"><?php echo $perf['wins']; ?></td>
                        <td class="text-danger"><?php echo $perf['losses']; ?></td>
                        <td>
                            <span class="badge badge-<?php echo $stratWinRate >= 60 ? 'success' : ($stratWinRate >= 50 ? 'warning' : 'danger'); ?>">
                                <?php echo $stratWinRate; ?>%
                            </span>
                        </td>
                        <td class="<?php echo $perf['pnl'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                            <strong><?php echo $perf['pnl'] >= 0 ? '+' : ''; ?>$<?php echo number_format($perf['pnl'], 2); ?></strong>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Recent Trades -->
<div class="admin-card fade-in">
    <div class="admin-card-header">
        <h5 class="admin-card-title"><i class="fas fa-history text-info"></i> Recent Trades</h5>
        <span class="badge badge-primary"><?php echo count($trades); ?> trades</span>
    </div>
    <div class="admin-card-body" style="padding: 0;">
        <?php if (empty($trades)): ?>
        <div class="empty-state py-4">
            <div class="empty-state-icon"><i class="fas fa-chart-line"></i></div>
            <h4 class="empty-state-title">No Trades Yet</h4>
            <p class="empty-state-desc">Start your robot to begin trading!</p>
            <a href="my-robot.php" class="btn btn-primary mt-2">
                <i class="fas fa-robot me-2"></i>Go to Robot Settings
            </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Strategy</th>
                        <th>Asset</th>
                        <th>Direction</th>
                        <th>Amount</th>
                        <th>Result</th>
                        <th>P/L</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($trades as $trade): ?>
                    <tr>
                        <td>
                            <span class="d-block"><?php echo date('M d', strtotime($trade['created_at'])); ?></span>
                            <small class="text-muted"><?php echo date('H:i:s', strtotime($trade['created_at'])); ?></small>
                        </td>
                        <td>
                            <span class="badge badge-secondary"><?php echo $trade['strategy_id']; ?></span>
                        </td>
                        <td>
                            <strong><?php echo $trade['asset']; ?></strong>
                            <br><small class="text-muted"><?php echo $trade['timeframe']; ?></small>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $trade['direction'] === 'call' ? 'success' : 'danger'; ?>">
                                <i class="fas fa-arrow-<?php echo $trade['direction'] === 'call' ? 'up' : 'down'; ?> me-1"></i>
                                <?php echo strtoupper($trade['direction']); ?>
                            </span>
                        </td>
                        <td>$<?php echo number_format($trade['amount'], 0); ?></td>
                        <td>
                            <?php if ($trade['result']): ?>
                            <span class="badge badge-<?php echo $trade['result'] === 'win' ? 'success' : ($trade['result'] === 'loss' ? 'danger' : 'secondary'); ?>">
                                <?php echo strtoupper($trade['result']); ?>
                            </span>
                            <?php else: ?>
                            <span class="badge badge-warning">PENDING</span>
                            <?php endif; ?>
                        </td>
                        <td class="<?php echo ($trade['profit_loss'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?>">
                            <strong><?php echo ($trade['profit_loss'] ?? 0) >= 0 ? '+' : ''; ?>$<?php echo number_format($trade['profit_loss'] ?? 0, 2); ?></strong>
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
.badge-lg {
    font-size: 0.85rem;
    padding: 0.5rem 1rem;
}

.today-summary {
    display: grid;
    gap: 1rem;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background: rgba(255, 255, 255, 0.03);
    border-radius: 8px;
}

.summary-item.highlight {
    background: rgba(var(--primary-rgb), 0.1);
    border: 1px solid rgba(var(--primary-rgb), 0.2);
}

.summary-label {
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.summary-value {
    font-weight: 700;
    font-size: 1.1rem;
}

.quick-stat {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.quick-stat:last-child {
    border-bottom: none;
}
</style>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// P/L Chart
const pnlData = <?php echo json_encode($cumulativePnl); ?>;
const ctx = document.getElementById('pnlChart').getContext('2d');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: pnlData.map(d => d.date),
        datasets: [{
            label: 'Cumulative P/L',
            data: pnlData.map(d => d.pnl),
            borderColor: pnlData.length > 0 && pnlData[pnlData.length - 1].pnl >= 0 ? '#10b981' : '#ef4444',
            backgroundColor: pnlData.length > 0 && pnlData[pnlData.length - 1].pnl >= 0
                ? 'rgba(16, 185, 129, 0.1)'
                : 'rgba(239, 68, 68, 0.1)',
            fill: true,
            tension: 0.4,
            pointRadius: 4,
            pointHoverRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: 'rgba(15, 15, 25, 0.9)',
                titleColor: '#fff',
                bodyColor: '#fff',
                borderColor: 'rgba(255, 255, 255, 0.1)',
                borderWidth: 1,
                callbacks: {
                    label: function(context) {
                        return '$' + context.parsed.y.toFixed(2);
                    }
                }
            }
        },
        scales: {
            x: {
                grid: {
                    color: 'rgba(255, 255, 255, 0.05)'
                },
                ticks: {
                    color: 'rgba(255, 255, 255, 0.5)'
                }
            },
            y: {
                grid: {
                    color: 'rgba(255, 255, 255, 0.05)'
                },
                ticks: {
                    color: 'rgba(255, 255, 255, 0.5)',
                    callback: function(value) {
                        return '$' + value;
                    }
                }
            }
        }
    }
});
</script>

<?php require_once 'includes/admin-footer.php'; ?>
