<?php
$page_title = 'My Robot';
require_once 'includes/admin-header.php';

$db = getDBConnection();
$message = '';
$messageType = '';
$adminId = $_SESSION['user_id'];

// Get admin user data
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$adminId]);
$adminUser = $stmt->fetch();

// Get or create robot settings for admin
$stmt = $db->prepare("SELECT * FROM robot_settings WHERE user_id = ?");
$stmt->execute([$adminId]);
$robotSettings = $stmt->fetch();

if (!$robotSettings) {
    // Create default robot settings for admin
    $stmt = $db->prepare("
        INSERT INTO robot_settings (user_id, robot_enabled, market, timeframe, risk_level, trade_amount, daily_limit)
        VALUES (?, 0, 'EUR/USD', '15M', 'medium', 10000, 20)
    ");
    $stmt->execute([$adminId]);

    $stmt = $db->prepare("SELECT * FROM robot_settings WHERE user_id = ?");
    $stmt->execute([$adminId]);
    $robotSettings = $stmt->fetch();
}

// Get robot status
$stmt = $db->prepare("SELECT * FROM robot_status WHERE user_id = ?");
$stmt->execute([$adminId]);
$robotStatus = $stmt->fetch();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'toggle_robot':
            $newStatus = $robotSettings['robot_enabled'] ? 0 : 1;
            $stmt = $db->prepare("UPDATE robot_settings SET robot_enabled = ?, updated_at = NOW() WHERE user_id = ?");
            $stmt->execute([$newStatus, $adminId]);

            // Update robot status
            $stmt = $db->prepare("
                INSERT INTO robot_status (user_id, status, connection_status, updated_at)
                VALUES (?, ?, 'connecting', NOW())
                ON DUPLICATE KEY UPDATE status = VALUES(status), connection_status = VALUES(connection_status), updated_at = NOW()
            ");
            $stmt->execute([$adminId, $newStatus ? 'running' : 'stopped']);

            $message = $newStatus ? 'Robot ENABLED! Waiting for connection...' : 'Robot DISABLED';
            $messageType = $newStatus ? 'success' : 'warning';

            // Refresh data
            $stmt = $db->prepare("SELECT * FROM robot_settings WHERE user_id = ?");
            $stmt->execute([$adminId]);
            $robotSettings = $stmt->fetch();
            break;

        case 'update_settings':
            $market = $_POST['market'] ?? 'EUR/USD';
            $timeframe = $_POST['timeframe'] ?? '15M';
            $riskLevel = $_POST['risk_level'] ?? 'medium';
            $tradeAmount = floatval($_POST['trade_amount'] ?? 10000);
            $dailyLimit = intval($_POST['daily_limit'] ?? 10);
            $tpTarget = floatval($_POST['take_profit_target'] ?? 50);
            $mlLimit = floatval($_POST['max_loss_limit'] ?? 25);
            $scheduleMode = $_POST['schedule_mode'] ?? 'auto_24h';
            $activeStrategies = $_POST['strategies'] ?? [];

            $stmt = $db->prepare("
                UPDATE robot_settings SET
                    market = ?,
                    timeframe = ?,
                    risk_level = ?,
                    trade_amount = ?,
                    daily_limit = ?,
                    take_profit_target = ?,
                    max_loss_limit = ?,
                    schedule_mode = ?,
                    active_strategies = ?,
                    updated_at = NOW()
                WHERE user_id = ?
            ");
            $stmt->execute([
                $market, $timeframe, $riskLevel, $tradeAmount, $dailyLimit,
                $tpTarget, $mlLimit, $scheduleMode, json_encode($activeStrategies), $adminId
            ]);

            $message = 'Robot settings updated!';
            $messageType = 'success';

            // Refresh data
            $stmt = $db->prepare("SELECT * FROM robot_settings WHERE user_id = ?");
            $stmt->execute([$adminId]);
            $robotSettings = $stmt->fetch();
            break;

        case 'switch_account_type':
            $accountType = $_POST['account_type'] ?? 'demo';
            $stmt = $db->prepare("UPDATE users SET olymptrade_account_type = ? WHERE id = ?");
            $stmt->execute([$accountType, $adminId]);

            $message = 'Switched to ' . strtoupper($accountType) . ' account mode';
            $messageType = 'info';

            // Refresh admin data
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$adminId]);
            $adminUser = $stmt->fetch();
            break;
    }
}

// Available strategies
$strategies = [
    'RSI_MASTER' => ['name' => 'RSI Master', 'desc' => 'RSI overbought/oversold signals', 'winrate' => 68],
    'BB_SQUEEZE' => ['name' => 'Bollinger Squeeze', 'desc' => 'Bollinger Band breakout signals', 'winrate' => 65],
    'MACD_DIVERGENCE' => ['name' => 'MACD Divergence', 'desc' => 'MACD divergence detection', 'winrate' => 62],
    'EMA_CROSS' => ['name' => 'EMA Crossover', 'desc' => 'EMA 9/21 crossover signals', 'winrate' => 64],
    'STOCH_RSI' => ['name' => 'Stochastic RSI', 'desc' => 'StochRSI momentum signals', 'winrate' => 66],
    'ADX_TREND' => ['name' => 'ADX Trend', 'desc' => 'ADX trend strength filter', 'winrate' => 61],
    'VOLUME_BREAKOUT' => ['name' => 'Volume Breakout', 'desc' => 'Volume spike detection', 'winrate' => 59],
    'SUPPORT_RESISTANCE' => ['name' => 'S/R Levels', 'desc' => 'Support/Resistance bounces', 'winrate' => 63],
    'CANDLE_PATTERN' => ['name' => 'Candle Patterns', 'desc' => 'Japanese candlestick patterns', 'winrate' => 60],
    'MULTI_TF' => ['name' => 'Multi Timeframe', 'desc' => 'Multi-timeframe confirmation', 'winrate' => 70]
];

$activeStrategies = json_decode($robotSettings['active_strategies'] ?? '[]', true) ?: [];

// Get today's stats
$stmt = $db->prepare("
    SELECT
        COUNT(*) as total_trades,
        SUM(CASE WHEN result = 'win' THEN 1 ELSE 0 END) as wins,
        SUM(CASE WHEN result = 'loss' THEN 1 ELSE 0 END) as losses,
        COALESCE(SUM(profit_loss), 0) as pnl
    FROM trades
    WHERE user_id = ? AND DATE(created_at) = CURDATE()
");
$stmt->execute([$adminId]);
$todayStats = $stmt->fetch();
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-robot"></i> My Robot</h1>
        <p class="page-subtitle">Configure and control your trading robot</p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <!-- Account Type Switch -->
        <form method="POST" class="d-inline">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="switch_account_type">
            <input type="hidden" name="account_type" value="<?php echo $adminUser['olymptrade_account_type'] === 'demo' ? 'real' : 'demo'; ?>">
            <button type="submit" class="btn btn-<?php echo $adminUser['olymptrade_account_type'] === 'demo' ? 'warning' : 'success'; ?>">
                <i class="fas fa-<?php echo $adminUser['olymptrade_account_type'] === 'demo' ? 'flask' : 'dollar-sign'; ?> me-2"></i>
                <?php echo strtoupper($adminUser['olymptrade_account_type'] ?? 'demo'); ?> MODE
            </button>
        </form>
    </div>
</div>

<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType; ?> fade-in">
    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : ($messageType === 'warning' ? 'exclamation-triangle' : 'info-circle'); ?>"></i>
    <span><?php echo $message; ?></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Robot Status Card -->
<div class="admin-card mb-4 fade-in" style="border: 2px solid <?php echo $robotSettings['robot_enabled'] ? 'var(--success)' : 'var(--text-muted)'; ?>;">
    <div class="admin-card-body">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-4">
                    <div class="robot-status-icon <?php echo $robotSettings['robot_enabled'] ? 'active' : ''; ?>">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div>
                        <h3 class="mb-1">Robot Status</h3>
                        <p class="mb-0">
                            <span class="badge badge-<?php echo $robotSettings['robot_enabled'] ? 'success' : 'secondary'; ?> badge-lg">
                                <i class="fas fa-<?php echo $robotSettings['robot_enabled'] ? 'play' : 'stop'; ?> me-1"></i>
                                <?php echo $robotSettings['robot_enabled'] ? 'RUNNING' : 'STOPPED'; ?>
                            </span>
                            <?php if ($robotStatus): ?>
                            <span class="badge badge-<?php echo $robotStatus['connection_status'] === 'connected' ? 'success' : 'warning'; ?> ms-2">
                                <i class="fas fa-wifi me-1"></i>
                                <?php echo ucfirst($robotStatus['connection_status'] ?? 'unknown'); ?>
                            </span>
                            <?php endif; ?>
                        </p>
                        <p class="text-muted small mt-2 mb-0">
                            <i class="fas fa-<?php echo $adminUser['olymptrade_account_type'] === 'demo' ? 'flask' : 'dollar-sign'; ?> me-1"></i>
                            Trading on <strong><?php echo strtoupper($adminUser['olymptrade_account_type'] ?? 'demo'); ?></strong> account
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 text-md-end">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="toggle_robot">
                    <button type="submit" class="btn btn-lg btn-<?php echo $robotSettings['robot_enabled'] ? 'danger' : 'success'; ?>">
                        <i class="fas fa-power-off me-2"></i>
                        <?php echo $robotSettings['robot_enabled'] ? 'STOP ROBOT' : 'START ROBOT'; ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Today's Stats -->
<div class="stat-grid mb-4">
    <div class="stat-card primary fade-in">
        <div class="stat-icon"><i class="fas fa-chart-bar"></i></div>
        <div class="stat-value"><?php echo $todayStats['total_trades'] ?? 0; ?></div>
        <div class="stat-label">Today's Trades</div>
    </div>
    <div class="stat-card success fade-in">
        <div class="stat-icon"><i class="fas fa-check"></i></div>
        <div class="stat-value"><?php echo $todayStats['wins'] ?? 0; ?></div>
        <div class="stat-label">Wins</div>
    </div>
    <div class="stat-card danger fade-in">
        <div class="stat-icon"><i class="fas fa-times"></i></div>
        <div class="stat-value"><?php echo $todayStats['losses'] ?? 0; ?></div>
        <div class="stat-label">Losses</div>
    </div>
    <div class="stat-card <?php echo ($todayStats['pnl'] ?? 0) >= 0 ? 'success' : 'danger'; ?> fade-in">
        <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
        <div class="stat-value"><?php echo ($todayStats['pnl'] ?? 0) >= 0 ? '+' : ''; ?>$<?php echo number_format($todayStats['pnl'] ?? 0, 2); ?></div>
        <div class="stat-label">Today's P/L</div>
    </div>
</div>

<form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    <input type="hidden" name="action" value="update_settings">

    <div class="row">
        <!-- Trading Settings -->
        <div class="col-lg-6 mb-4">
            <div class="admin-card h-100 fade-in">
                <div class="admin-card-header">
                    <h5 class="admin-card-title"><i class="fas fa-sliders-h text-primary"></i> Trading Settings</h5>
                </div>
                <div class="admin-card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Market</label>
                            <select name="market" class="form-select">
                                <option value="EUR/USD" <?php echo $robotSettings['market'] === 'EUR/USD' ? 'selected' : ''; ?>>EUR/USD</option>
                                <option value="GBP/USD" <?php echo $robotSettings['market'] === 'GBP/USD' ? 'selected' : ''; ?>>GBP/USD</option>
                                <option value="USD/JPY" <?php echo $robotSettings['market'] === 'USD/JPY' ? 'selected' : ''; ?>>USD/JPY</option>
                                <option value="AUD/USD" <?php echo $robotSettings['market'] === 'AUD/USD' ? 'selected' : ''; ?>>AUD/USD</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Timeframe</label>
                            <select name="timeframe" class="form-select">
                                <option value="5M" <?php echo $robotSettings['timeframe'] === '5M' ? 'selected' : ''; ?>>5 Minutes</option>
                                <option value="15M" <?php echo $robotSettings['timeframe'] === '15M' ? 'selected' : ''; ?>>15 Minutes</option>
                                <option value="30M" <?php echo $robotSettings['timeframe'] === '30M' ? 'selected' : ''; ?>>30 Minutes</option>
                                <option value="1H" <?php echo $robotSettings['timeframe'] === '1H' ? 'selected' : ''; ?>>1 Hour</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Risk Level</label>
                            <select name="risk_level" class="form-select">
                                <option value="low" <?php echo $robotSettings['risk_level'] === 'low' ? 'selected' : ''; ?>>Low (Conservative)</option>
                                <option value="medium" <?php echo $robotSettings['risk_level'] === 'medium' ? 'selected' : ''; ?>>Medium (Balanced)</option>
                                <option value="high" <?php echo $robotSettings['risk_level'] === 'high' ? 'selected' : ''; ?>>High (Aggressive)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Schedule</label>
                            <select name="schedule_mode" class="form-select">
                                <option value="auto_24h" <?php echo $robotSettings['schedule_mode'] === 'auto_24h' ? 'selected' : ''; ?>>24/7 Auto</option>
                                <option value="best_hours" <?php echo $robotSettings['schedule_mode'] === 'best_hours' ? 'selected' : ''; ?>>Best Hours Only</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Trade Amount (IDR)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="trade_amount" class="form-control"
                                       value="<?php echo $robotSettings['trade_amount']; ?>" min="10000" step="1000">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Daily Limit</label>
                            <div class="input-group">
                                <input type="number" name="daily_limit" class="form-control"
                                       value="<?php echo $robotSettings['daily_limit']; ?>" min="1" max="100">
                                <span class="input-group-text">trades</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Risk Management -->
        <div class="col-lg-6 mb-4">
            <div class="admin-card h-100 fade-in">
                <div class="admin-card-header">
                    <h5 class="admin-card-title"><i class="fas fa-shield-alt text-warning"></i> Risk Management</h5>
                </div>
                <div class="admin-card-body">
                    <div class="mb-4">
                        <label class="form-label">
                            <i class="fas fa-arrow-up text-success me-1"></i> Take Profit Target (Auto-Stop)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="take_profit_target" class="form-control"
                                   value="<?php echo $robotSettings['take_profit_target']; ?>" min="10" step="5">
                        </div>
                        <small class="text-muted">Robot will auto-stop when daily profit reaches this target</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">
                            <i class="fas fa-arrow-down text-danger me-1"></i> Max Loss Limit (Auto-Stop)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="max_loss_limit" class="form-control"
                                   value="<?php echo $robotSettings['max_loss_limit']; ?>" min="10" step="5">
                        </div>
                        <small class="text-muted">Robot will auto-stop when daily loss reaches this limit</small>
                    </div>

                    <div class="alert alert-info py-2 mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Current Daily P/L:</strong>
                        <span class="<?php echo ($todayStats['pnl'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?>">
                            <?php echo ($todayStats['pnl'] ?? 0) >= 0 ? '+' : ''; ?>$<?php echo number_format($todayStats['pnl'] ?? 0, 2); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Strategies -->
    <div class="admin-card mb-4 fade-in">
        <div class="admin-card-header">
            <h5 class="admin-card-title"><i class="fas fa-brain text-info"></i> Active Strategies</h5>
            <span class="badge badge-primary"><?php echo count($activeStrategies); ?> / <?php echo count($strategies); ?> Active</span>
        </div>
        <div class="admin-card-body">
            <div class="row">
                <?php foreach ($strategies as $id => $strategy): ?>
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="strategy-card <?php echo in_array($id, $activeStrategies) ? 'active' : ''; ?>">
                        <label class="strategy-checkbox">
                            <input type="checkbox" name="strategies[]" value="<?php echo $id; ?>"
                                   <?php echo in_array($id, $activeStrategies) ? 'checked' : ''; ?>>
                            <span class="checkmark"></span>
                        </label>
                        <div class="strategy-info">
                            <h6 class="strategy-name"><?php echo $strategy['name']; ?></h6>
                            <p class="strategy-desc"><?php echo $strategy['desc']; ?></p>
                            <span class="strategy-winrate">
                                <i class="fas fa-chart-line me-1"></i>
                                ~<?php echo $strategy['winrate']; ?>% Win Rate
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Save Button -->
    <div class="admin-card fade-in">
        <div class="admin-card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1"><i class="fas fa-save me-2 text-primary"></i>Save Settings</h5>
                    <p class="text-muted mb-0 small">Settings will be applied immediately</p>
                </div>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save me-2"></i>Save All Settings
                </button>
            </div>
        </div>
    </div>
</form>

<style>
.robot-status-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.05);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    color: var(--text-muted);
    transition: all 0.3s ease;
}

.robot-status-icon.active {
    background: rgba(16, 185, 129, 0.15);
    color: var(--success);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
    50% { box-shadow: 0 0 0 15px rgba(16, 185, 129, 0); }
}

.badge-lg {
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
}

.strategy-card {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 12px;
    padding: 1rem;
    display: flex;
    gap: 1rem;
    transition: all 0.3s ease;
    cursor: pointer;
}

.strategy-card:hover {
    background: rgba(255, 255, 255, 0.06);
    border-color: rgba(255, 255, 255, 0.15);
}

.strategy-card.active {
    background: rgba(var(--primary-rgb), 0.1);
    border-color: var(--primary);
}

.strategy-checkbox {
    position: relative;
    cursor: pointer;
}

.strategy-checkbox input {
    opacity: 0;
    position: absolute;
}

.strategy-checkbox .checkmark {
    width: 24px;
    height: 24px;
    border: 2px solid rgba(255, 255, 255, 0.2);
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.strategy-checkbox input:checked + .checkmark {
    background: var(--primary);
    border-color: var(--primary);
}

.strategy-checkbox input:checked + .checkmark::after {
    content: '\f00c';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    color: white;
    font-size: 12px;
}

.strategy-info {
    flex: 1;
}

.strategy-name {
    margin-bottom: 0.25rem;
    font-weight: 600;
}

.strategy-desc {
    font-size: 0.8rem;
    color: var(--text-secondary);
    margin-bottom: 0.5rem;
}

.strategy-winrate {
    font-size: 0.75rem;
    color: var(--success);
    font-weight: 500;
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
