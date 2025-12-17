<?php
$page_title = 'Robot Control';
require_once 'dashboard/includes/dashboard-header.php';

$db = getDBConnection();

// Get user settings and data
$user = getUserById($_SESSION['user_id']);
$package = $user['package'] ?? 'free';
$robotSettings = getRobotSettings($_SESSION['user_id']);
$dailyTarget = getDailyTargetSettings($_SESSION['user_id']);
$availableStrategies = getAvailableStrategies($package);
$allStrategies = getAllStrategies();
$packageInfo = getPackageDetails($package);

// Check if OlympTrade credentials are setup
$otSetupCompleted = !empty($user['olymptrade_setup_completed']);

// Get statistics
$todayStats = getDailyStats($_SESSION['user_id'], date('Y-m-d'));
$weekStats = getUserStats($_SESSION['user_id'], 7);
$monthStats = getUserStats($_SESSION['user_id'], 30);

// Get recent trades
$recentTrades = getRecentTrades($_SESSION['user_id'], 10);

// Get live logs
$liveLogs = getLiveLogs($_SESSION['user_id'], 15);

// Get settings for markets and timeframes
$allowedMarkets = explode(',', getSetting('allowed_markets', 'EUR/USD,GBP/USD,USD/JPY'));
$allowedTimeframes = explode(',', getSetting('allowed_timeframes', '1m,5m,15m'));

// Weekend check
$isWeekendNow = isWeekend();

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'toggle_robot':
            $newStatus = $_POST['robot_status'] === '1' ? 'active' : 'paused';

            // Block robot activation if OlympTrade credentials not setup
            if ($newStatus === 'active' && !$otSetupCompleted) {
                $message = 'Setup OlympTrade credentials terlebih dahulu sebelum mengaktifkan robot!';
                $messageType = 'danger';
                break;
            }

            updateRobotStatus($_SESSION['user_id'], $newStatus);
            $robotSettings['robot_enabled'] = $newStatus === 'active';
            $message = $newStatus === 'active' ? 'Robot activated!' : 'Robot paused.';
            $messageType = $newStatus === 'active' ? 'success' : 'warning';
            break;

        case 'save_settings':
            $settings = [
                'strategy_id' => $_POST['strategy_id'] ?? $robotSettings['strategy_id'],
                'markets' => implode(',', $_POST['markets'] ?? []),
                'timeframes' => implode(',', $_POST['timeframes'] ?? []),
                'trade_amount' => floatval($_POST['trade_amount'] ?? 5),
                'max_trades_per_day' => intval($_POST['max_trades'] ?? 20)
            ];
            updateRobotSettings($_SESSION['user_id'], $settings);
            $robotSettings = getRobotSettings($_SESSION['user_id']);
            $message = 'Settings saved successfully!';
            $messageType = 'success';
            break;

        case 'save_targets':
            $targets = [
                'take_profit_target' => floatval($_POST['daily_tp'] ?? 20),
                'max_loss_limit' => floatval($_POST['daily_ml'] ?? 10),
                'auto_pause_on_tp' => isset($_POST['auto_pause_tp']) ? 1 : 0,
                'auto_pause_on_ml' => isset($_POST['auto_pause_ml']) ? 1 : 0
            ];
            updateRobotSettings($_SESSION['user_id'], $targets);
            $robotSettings = getRobotSettings($_SESSION['user_id']);
            $message = 'Daily targets updated!';
            $messageType = 'success';
            break;
    }
}

// Determine robot status
$robotEnabled = $robotSettings['robot_enabled'] ?? false;
$autoPaused = $robotSettings['auto_pause_triggered'] ?? false;

if ($isWeekendNow) {
    $robotStatus = 'weekend';
} elseif ($autoPaused) {
    $robotStatus = 'paused';
} elseif ($robotEnabled) {
    $robotStatus = 'active';
} elseif ($user['status'] === 'active') {
    $robotStatus = 'standby';
} else {
    $robotStatus = 'inactive';
}

$statusLabels = [
    'active' => ['label' => 'RUNNING', 'desc' => 'Robot is actively trading', 'icon' => 'fa-play-circle'],
    'paused' => ['label' => 'PAUSED', 'desc' => 'Trading temporarily paused', 'icon' => 'fa-pause-circle'],
    'standby' => ['label' => 'STANDBY', 'desc' => 'Waiting for signal', 'icon' => 'fa-clock'],
    'weekend' => ['label' => 'WEEKEND', 'desc' => 'Markets are closed', 'icon' => 'fa-moon'],
    'inactive' => ['label' => 'OFFLINE', 'desc' => 'Robot not configured', 'icon' => 'fa-power-off']
];

$currentStatusInfo = $statusLabels[$robotStatus] ?? $statusLabels['inactive'];

// Calculate target progress
$todayPnl = $todayStats['total_pnl'] ?? $robotSettings['current_daily_pnl'] ?? 0;
$tpTarget = $robotSettings['take_profit_target'] ?? 20;
$mlLimit = $robotSettings['max_loss_limit'] ?? 10;
$tpProgress = $tpTarget > 0 ? min(100, max(0, ($todayPnl / $tpTarget) * 100)) : 0;
$mlProgress = $mlLimit > 0 && $todayPnl < 0 ? min(100, (abs($todayPnl) / $mlLimit) * 100) : 0;
?>

<!-- Page Header -->
<div class="db-page-header">
    <div>
        <h1 class="db-page-title"><i class="fas fa-robot"></i> Robot Control</h1>
        <p class="db-page-subtitle">Manage your trading bot settings and monitor performance</p>
    </div>
    <div class="d-flex gap-2">
        <a href="statistics.php" class="db-btn db-btn-outline">
            <i class="fas fa-chart-bar"></i> Full Stats
        </a>
    </div>
</div>

<?php if ($message): ?>
<div class="db-alert <?php echo $messageType; ?> db-fade-in">
    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
    <span><?php echo $message; ?></span>
    <button type="button" class="btn-close btn-close-white ms-auto" onclick="this.parentElement.remove()"></button>
</div>
<?php endif; ?>

<?php if (!$otSetupCompleted): ?>
<div class="db-alert warning db-fade-in">
    <i class="fas fa-exclamation-triangle"></i>
    <div class="flex-grow-1">
        <strong>OlympTrade Setup Required!</strong>
        <p class="mb-0 small" style="opacity: 0.9;">
            Anda harus menghubungkan akun OlympTrade sebelum mengaktifkan robot trading.
            Robot membutuhkan credentials untuk login dan melakukan trading otomatis.
        </p>
    </div>
    <a href="olymptrade-setup.php" class="db-btn db-btn-warning db-btn-sm ms-3">
        <i class="fas fa-link"></i> Setup Sekarang
    </a>
</div>
<?php endif; ?>

<?php if ($isWeekendNow): ?>
<div class="db-alert warning db-fade-in">
    <i class="fas fa-calendar-times"></i>
    <div>
        <strong>Weekend Mode</strong>
        <p class="mb-0 small" style="opacity: 0.9;">Markets are closed. Robot will resume on Monday.</p>
    </div>
</div>
<?php endif; ?>

<!-- Robot Status Panel -->
<div class="robot-status-panel db-fade-in">
    <div class="robot-status-header">
        <div class="robot-status-info">
            <div class="robot-indicator <?php echo $robotStatus; ?>">
                <i class="fas <?php echo $currentStatusInfo['icon']; ?>"></i>
            </div>
            <div class="robot-status-text">
                <h4><?php echo $currentStatusInfo['label']; ?></h4>
                <p><?php echo $currentStatusInfo['desc']; ?></p>
            </div>
        </div>

        <?php if (!$isWeekendNow): ?>
        <form method="POST" class="master-toggle">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="toggle_robot">
            <label>Master Switch</label>
            <?php if (!$otSetupCompleted): ?>
            <a href="olymptrade-setup.php" class="db-btn db-btn-warning db-btn-sm" title="Setup OlympTrade dulu">
                <i class="fas fa-link"></i> Setup
            </a>
            <?php else: ?>
            <label class="toggle-switch">
                <input type="checkbox" name="robot_status" value="1"
                       <?php echo $robotEnabled ? 'checked' : ''; ?>
                       onchange="this.form.submit()">
                <span class="toggle-slider"></span>
            </label>
            <?php endif; ?>
        </form>
        <?php endif; ?>
    </div>

    <!-- Daily Target Progress -->
    <div class="row g-3 mt-2">
        <div class="col-md-6">
            <div class="db-progress-card success">
                <div class="db-progress-header">
                    <span><i class="fas fa-bullseye me-1"></i> Take Profit Target</span>
                    <span class="<?php echo $todayPnl >= $tpTarget && $todayPnl > 0 ? 'text-success fw-bold' : ''; ?>">
                        $<?php echo number_format(max(0, $todayPnl), 2); ?> / $<?php echo number_format($tpTarget, 0); ?>
                        <?php if ($todayPnl >= $tpTarget && $todayPnl > 0): ?> <i class="fas fa-check-circle"></i><?php endif; ?>
                    </span>
                </div>
                <div class="db-progress-bar">
                    <div class="db-progress-fill success" style="width: <?php echo max(0, $tpProgress); ?>%;"></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="db-progress-card danger">
                <div class="db-progress-header">
                    <span><i class="fas fa-shield-alt me-1"></i> Max Loss Limit</span>
                    <span class="<?php echo $todayPnl < 0 && abs($todayPnl) >= $mlLimit ? 'text-danger fw-bold' : ''; ?>">
                        <?php if ($todayPnl < 0): ?>
                            $<?php echo number_format(abs($todayPnl), 2); ?> / $<?php echo number_format($mlLimit, 0); ?>
                        <?php else: ?>
                            <i class="fas fa-check text-success me-1"></i> Safe
                        <?php endif; ?>
                    </span>
                </div>
                <div class="db-progress-bar">
                    <div class="db-progress-fill danger" style="width: <?php echo $mlProgress; ?>%;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="db-stat-grid db-fade-in">
    <div class="db-stat-card">
        <div class="db-stat-icon"><i class="fas fa-chart-bar"></i></div>
        <div class="db-stat-value" data-count="<?php echo $todayStats['total_trades'] ?? 0; ?>">0</div>
        <div class="db-stat-label">Today's Trades</div>
    </div>
    <div class="db-stat-card success">
        <div class="db-stat-icon"><i class="fas fa-check-circle"></i></div>
        <div class="db-stat-value" data-count="<?php echo $todayStats['wins'] ?? 0; ?>">0</div>
        <div class="db-stat-label">Wins Today</div>
    </div>
    <div class="db-stat-card <?php echo ($todayPnl >= 0) ? 'success' : 'danger'; ?>">
        <div class="db-stat-icon"><i class="fas fa-dollar-sign"></i></div>
        <div class="db-stat-value"><?php echo $todayPnl >= 0 ? '+' : ''; ?>$<?php echo number_format($todayPnl, 2); ?></div>
        <div class="db-stat-label">Today's P/L</div>
    </div>
    <div class="db-stat-card">
        <div class="db-stat-icon"><i class="fas fa-percentage"></i></div>
        <div class="db-stat-value"><?php echo number_format($weekStats['win_rate'] ?? 0, 1); ?>%</div>
        <div class="db-stat-label">Win Rate (7d)</div>
    </div>
    <div class="db-stat-card <?php echo ($monthStats['total_pnl'] ?? 0) >= 0 ? 'success' : 'danger'; ?>">
        <div class="db-stat-icon"><i class="fas fa-calendar-alt"></i></div>
        <div class="db-stat-value"><?php echo ($monthStats['total_pnl'] ?? 0) >= 0 ? '+' : ''; ?>$<?php echo number_format($monthStats['total_pnl'] ?? 0, 2); ?></div>
        <div class="db-stat-label">This Month</div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column - Settings -->
    <div class="col-lg-8">
        <!-- Strategy Selection -->
        <div class="db-card db-fade-in mb-4">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="fas fa-chess"></i> Strategy Selection</h5>
                <span class="db-badge <?php echo strtolower($package); ?>"><?php echo strtoupper($package); ?> Access</span>
            </div>
            <div class="db-card-body">
                <form method="POST" id="settingsForm">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="save_settings">

                    <div class="db-strategy-grid mb-4">
                        <?php foreach ($allStrategies as $strategy):
                            $isAvailable = in_array($strategy['id'], array_column($availableStrategies, 'id'));
                            $isSelected = ($robotSettings['strategy_id'] ?? '') == $strategy['id'];
                        ?>
                        <div class="db-strategy-card <?php echo $isSelected ? 'selected' : ''; ?> <?php echo !$isAvailable ? 'locked' : ''; ?>"
                             data-strategy="<?php echo $strategy['id']; ?>"
                             <?php echo $isAvailable ? 'onclick="selectStrategy(this)"' : ''; ?>>
                            <div class="db-strategy-header">
                                <div>
                                    <span class="db-badge <?php echo strtolower($strategy['tier']); ?>"><?php echo $strategy['tier']; ?></span>
                                    <h6 class="db-strategy-title"><?php echo htmlspecialchars($strategy['name']); ?></h6>
                                </div>
                                <div class="db-strategy-winrate"><?php echo $strategy['win_rate']; ?></div>
                            </div>
                            <div class="db-strategy-meta">
                                <span><i class="fas fa-clock me-1"></i><?php echo $strategy['best_timeframe']; ?></span>
                                <span><i class="fas fa-signal me-1"></i><?php echo $strategy['signals_per_day'] ?? '5-15'; ?>/day</span>
                            </div>
                            <?php if (!$isAvailable): ?>
                            <div class="position-absolute top-50 start-50 translate-middle">
                                <i class="fas fa-lock fa-2x text-muted"></i>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="strategy_id" id="strategyInput" value="<?php echo $robotSettings['strategy_id'] ?? ''; ?>">

                    <!-- Trading Settings -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="db-form-group">
                                <label class="db-form-label">Markets</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php
                                    $selectedMarkets = explode(',', $robotSettings['markets'] ?? 'EUR/USD');
                                    foreach ($allowedMarkets as $market):
                                        $market = trim($market);
                                        $checked = in_array($market, $selectedMarkets);
                                    ?>
                                    <label class="db-checkbox-label">
                                        <input type="checkbox" name="markets[]" value="<?php echo $market; ?>" <?php echo $checked ? 'checked' : ''; ?>>
                                        <span class="db-checkbox-box"><?php echo $market; ?></span>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="db-form-group">
                                <label class="db-form-label">Timeframes</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php
                                    $selectedTimeframes = explode(',', $robotSettings['timeframes'] ?? '5m');
                                    foreach ($allowedTimeframes as $tf):
                                        $tf = trim($tf);
                                        $checked = in_array($tf, $selectedTimeframes);
                                    ?>
                                    <label class="db-checkbox-label">
                                        <input type="checkbox" name="timeframes[]" value="<?php echo $tf; ?>" <?php echo $checked ? 'checked' : ''; ?>>
                                        <span class="db-checkbox-box"><?php echo strtoupper($tf); ?></span>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="db-form-group">
                                <label class="db-form-label">Trade Amount ($)</label>
                                <input type="number" name="trade_amount" class="db-form-control"
                                       value="<?php echo $robotSettings['trade_amount'] ?? 5; ?>"
                                       min="1" max="100" step="1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="db-form-group">
                                <label class="db-form-label">Max Trades/Day</label>
                                <input type="number" name="max_trades" class="db-form-control"
                                       value="<?php echo $robotSettings['max_trades_per_day'] ?? 20; ?>"
                                       min="1" max="100">
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="db-btn db-btn-primary">
                            <i class="fas fa-save"></i> Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Recent Trades -->
        <div class="db-card db-fade-in">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="fas fa-history"></i> Recent Trades</h5>
                <a href="statistics.php" class="db-btn db-btn-sm db-btn-outline">View All</a>
            </div>
            <div class="db-card-body" style="padding: 0;">
                <?php if (empty($recentTrades)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-chart-line fa-3x text-muted mb-3" style="opacity: 0.3;"></i>
                    <p class="text-muted">No trades yet. Start the robot to begin trading!</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="db-table">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Asset</th>
                                <th>Direction</th>
                                <th>Amount</th>
                                <th>Result</th>
                                <th>P/L</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentTrades as $trade): ?>
                            <tr>
                                <td>
                                    <span class="d-block"><?php echo date('H:i', strtotime($trade['created_at'])); ?></span>
                                    <small class="text-muted"><?php echo date('M d', strtotime($trade['created_at'])); ?></small>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($trade['asset']); ?></strong>
                                    <br><small class="text-muted"><?php echo $trade['timeframe']; ?></small>
                                </td>
                                <td>
                                    <span class="db-badge <?php echo $trade['direction'] === 'call' ? 'success' : 'danger'; ?>">
                                        <i class="fas fa-arrow-<?php echo $trade['direction'] === 'call' ? 'up' : 'down'; ?>"></i>
                                        <?php echo strtoupper($trade['direction']); ?>
                                    </span>
                                </td>
                                <td>$<?php echo number_format($trade['amount'], 0); ?></td>
                                <td>
                                    <?php if ($trade['result']): ?>
                                    <span class="db-badge <?php echo $trade['result'] === 'win' ? 'success' : 'danger'; ?>">
                                        <?php echo strtoupper($trade['result']); ?>
                                    </span>
                                    <?php else: ?>
                                    <span class="db-badge warning">PENDING</span>
                                    <?php endif; ?>
                                </td>
                                <td class="<?php echo ($trade['profit_loss'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?> fw-bold">
                                    <?php echo ($trade['profit_loss'] ?? 0) >= 0 ? '+' : ''; ?>$<?php echo number_format($trade['profit_loss'] ?? 0, 2); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="col-lg-4">
        <!-- Daily Target Settings -->
        <div class="db-card db-fade-in mb-4">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="fas fa-bullseye"></i> Daily Targets</h5>
            </div>
            <div class="db-card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="save_targets">

                    <div class="db-form-group">
                        <label class="db-form-label">Take Profit ($)</label>
                        <input type="number" name="daily_tp" class="db-form-control"
                               value="<?php echo $robotSettings['take_profit_target'] ?? 20; ?>" min="5" step="5">
                        <small class="text-muted">Auto-pause when reached</small>
                    </div>

                    <div class="db-form-group">
                        <label class="db-form-label">Max Loss ($)</label>
                        <input type="number" name="daily_ml" class="db-form-control"
                               value="<?php echo $robotSettings['max_loss_limit'] ?? 10; ?>" min="5" step="5">
                        <small class="text-muted">Stop trading to protect capital</small>
                    </div>

                    <div class="d-flex flex-column gap-2 mb-3">
                        <label class="d-flex align-items-center gap-2">
                            <input type="checkbox" name="auto_pause_tp" class="form-check-input m-0"
                                   <?php echo ($robotSettings['auto_pause_on_tp'] ?? 1) ? 'checked' : ''; ?>>
                            <span class="small">Auto-pause on TP</span>
                        </label>
                        <label class="d-flex align-items-center gap-2">
                            <input type="checkbox" name="auto_pause_ml" class="form-check-input m-0"
                                   <?php echo ($robotSettings['auto_pause_on_ml'] ?? 1) ? 'checked' : ''; ?>>
                            <span class="small">Auto-pause on ML</span>
                        </label>
                    </div>

                    <button type="submit" class="db-btn db-btn-primary w-100">
                        <i class="fas fa-save"></i> Update Targets
                    </button>
                </form>
            </div>
        </div>

        <!-- Live Log -->
        <div class="db-card db-fade-in mb-4">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="fas fa-stream"></i> Live Activity</h5>
                <span class="db-badge info" id="liveIndicator">
                    <i class="fas fa-circle fa-xs"></i> LIVE
                </span>
            </div>
            <div class="db-card-body" style="padding: 0;">
                <div class="db-live-log" id="liveLog">
                    <?php if (empty($liveLogs)): ?>
                    <div class="text-center py-4">
                        <p class="text-muted small mb-0">No activity yet</p>
                    </div>
                    <?php else: ?>
                    <?php foreach ($liveLogs as $log): ?>
                    <div class="db-log-item <?php echo $log['type'] ?? ''; ?>">
                        <span class="db-log-time"><?php echo date('H:i', strtotime($log['created_at'])); ?></span>
                        <span class="db-log-icon">
                            <?php
                            $icons = [
                                'win' => '<i class="fas fa-check-circle text-success"></i>',
                                'loss' => '<i class="fas fa-times-circle text-danger"></i>',
                                'signal' => '<i class="fas fa-bolt text-warning"></i>',
                                'info' => '<i class="fas fa-info-circle text-info"></i>',
                                'system' => '<i class="fas fa-cog text-muted"></i>'
                            ];
                            echo $icons[$log['type']] ?? $icons['info'];
                            ?>
                        </span>
                        <span class="db-log-text">
                            <?php echo htmlspecialchars($log['action'] ?? ''); ?>
                            <?php if (!empty($log['message'])): ?>
                            <span class="db-log-message"><?php echo htmlspecialchars($log['message']); ?></span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Account Info -->
        <div class="db-card db-fade-in">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="fas fa-user-circle"></i> Account</h5>
            </div>
            <div class="db-card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Package</span>
                    <span class="db-badge <?php echo strtolower($package); ?>"><?php echo strtoupper($package); ?></span>
                </div>
                <?php if ($user['package_expiry']): ?>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Expires</span>
                    <span><?php echo date('M d, Y', strtotime($user['package_expiry'])); ?></span>
                </div>
                <?php endif; ?>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Broker ID</span>
                    <code><?php echo htmlspecialchars($user['olymptrade_id']); ?></code>
                </div>
                <?php if (!empty($user['license_key'])): ?>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">License</span>
                    <code class="small"><?php echo htmlspecialchars(substr($user['license_key'], 0, 12)); ?>...</code>
                </div>
                <?php endif; ?>

                <?php if ($package === 'free'): ?>
                <a href="pricing.php" class="db-btn db-btn-warning w-100">
                    <i class="fas fa-crown"></i> Upgrade Now
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
/* Strategy checkbox styling */
.db-checkbox-label {
    cursor: pointer;
}

.db-checkbox-label input {
    display: none;
}

.db-checkbox-box {
    display: inline-block;
    padding: 0.4rem 0.75rem;
    background: var(--db-surface-light);
    border: 1px solid var(--db-border);
    border-radius: 8px;
    font-size: 0.85rem;
    transition: var(--db-transition);
}

.db-checkbox-label input:checked + .db-checkbox-box {
    background: rgba(var(--db-primary-rgb), 0.15);
    border-color: var(--db-primary);
    color: var(--db-primary);
}

.db-checkbox-label:hover .db-checkbox-box {
    border-color: rgba(var(--db-primary-rgb), 0.5);
}

/* Live indicator pulse */
#liveIndicator i {
    animation: livePulse 1.5s infinite;
}

@keyframes livePulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; }
}
</style>

<script>
function selectStrategy(element) {
    // Remove selected from all
    document.querySelectorAll('.db-strategy-card').forEach(card => {
        card.classList.remove('selected');
    });

    // Add selected to clicked
    element.classList.add('selected');

    // Update hidden input
    document.getElementById('strategyInput').value = element.dataset.strategy;
}

// Auto refresh live log every 30 seconds
setInterval(function() {
    // AJAX refresh could be added here
}, 30000);
</script>

<?php require_once 'dashboard/includes/dashboard-footer.php'; ?>
