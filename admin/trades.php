<?php
$page_title = 'Trade History';
require_once '../includes/header.php';

// Admin only
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

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

<section class="section" style="padding-top: calc(var(--navbar-height) + 2rem);">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-chart-line me-2"></i>Trade History</h2>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>

        <!-- Stats Row -->
        <div class="row g-3 mb-4">
            <div class="col-md-2">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center py-2">
                        <h4 class="mb-0"><?php echo number_format($stats['total_trades'] ?? 0); ?></h4>
                        <small>Total Trades</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-success text-white">
                    <div class="card-body text-center py-2">
                        <h4 class="mb-0"><?php echo number_format($stats['wins'] ?? 0); ?></h4>
                        <small>Wins</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center py-2">
                        <h4 class="mb-0"><?php echo number_format($stats['losses'] ?? 0); ?></h4>
                        <small>Losses</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-<?php echo ($stats['total_pnl'] ?? 0) >= 0 ? 'success' : 'danger'; ?> text-white">
                    <div class="card-body text-center py-2">
                        <h4 class="mb-0">$<?php echo number_format($stats['total_pnl'] ?? 0, 2); ?></h4>
                        <small>Total P/L</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center py-2">
                        <h4 class="mb-0"><?php echo number_format($stats['win_rate'] ?? 0, 1); ?>%</h4>
                        <small>Win Rate</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body py-2">
                <form method="GET" class="row g-2 align-items-center">
                    <div class="col-md-2">
                        <label class="form-label small mb-0">From</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="<?php echo $date_from; ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-0">To</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="<?php echo $date_to; ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-0">User</label>
                        <select name="user" class="form-select form-select-sm">
                            <option value="">All Users</option>
                            <?php foreach ($allUsers as $u): ?>
                            <option value="<?php echo $u['id']; ?>" <?php echo $user_filter == $u['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($u['fullname']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-0">Strategy</label>
                        <select name="strategy" class="form-select form-select-sm">
                            <option value="">All Strategies</option>
                            <?php foreach ($strategies as $s): ?>
                            <option value="<?php echo $s['strategy_id']; ?>" <?php echo $strategy_filter == $s['strategy_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($s['strategy']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-0">Result</label>
                        <select name="result" class="form-select form-select-sm">
                            <option value="">All Results</option>
                            <option value="win" <?php echo $result_filter === 'win' ? 'selected' : ''; ?>>Win</option>
                            <option value="loss" <?php echo $result_filter === 'loss' ? 'selected' : ''; ?>>Loss</option>
                            <option value="tie" <?php echo $result_filter === 'tie' ? 'selected' : ''; ?>>Tie</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-0">&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Trades Table -->
        <div class="card">
            <div class="card-body">
                <?php if (empty($trades)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No trades found for the selected period</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
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
                                <td>#<?php echo $trade['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($trade['fullname']); ?></strong>
                                    <br><small class="text-muted">OT: <?php echo htmlspecialchars($trade['olymptrade_id']); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($trade['strategy_id']); ?></span>
                                    <br><small><?php echo htmlspecialchars($trade['strategy']); ?></small>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($trade['asset']); ?>
                                    <br><small class="text-muted"><?php echo $trade['timeframe']; ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $trade['direction'] === 'call' ? 'success' : 'danger'; ?>">
                                        <?php echo strtoupper($trade['direction']); ?>
                                        <i class="fas fa-arrow-<?php echo $trade['direction'] === 'call' ? 'up' : 'down'; ?>"></i>
                                    </span>
                                </td>
                                <td>$<?php echo number_format($trade['amount'], 0); ?></td>
                                <td>
                                    <?php if ($trade['result']): ?>
                                    <span class="badge bg-<?php echo $trade['result'] === 'win' ? 'success' : ($trade['result'] === 'loss' ? 'danger' : 'secondary'); ?>">
                                        <?php echo strtoupper($trade['result']); ?>
                                    </span>
                                    <?php else: ?>
                                    <span class="badge bg-warning">PENDING</span>
                                    <?php endif; ?>
                                </td>
                                <td class="<?php echo ($trade['profit_loss'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo ($trade['profit_loss'] ?? 0) >= 0 ? '+' : ''; ?>$<?php echo number_format($trade['profit_loss'] ?? 0, 2); ?>
                                </td>
                                <td>
                                    <?php echo date('M d', strtotime($trade['created_at'])); ?>
                                    <br><small class="text-muted"><?php echo date('H:i:s', strtotime($trade['created_at'])); ?></small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-muted small mt-2">
                    Showing <?php echo count($trades); ?> trades (max 500)
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
