<?php
$page_title = __('admin_myrobot_title') ?: 'My Robot';
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

            $message = $newStatus ? __('admin_robot_enabled') : __('admin_robot_disabled');
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
            $resumeBehavior = $_POST['resume_behavior'] ?? 'next_session';

            // Handle schedule-specific data
            $customStart = $_POST['custom_start'] ?? '08:00';
            $customEnd = $_POST['custom_end'] ?? '22:00';
            $scheduleSessions = $_POST['schedule_sessions'] ?? '[]';

            // Build schedule_per_day from form data
            $schedulePerDay = [];
            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
            foreach ($days as $day) {
                $schedulePerDay[$day] = [
                    'enabled' => isset($_POST['day_enabled'][$day]),
                    'start' => $_POST['day_start'][$day] ?? '08:00',
                    'end' => $_POST['day_end'][$day] ?? '22:00'
                ];
            }

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
                    resume_behavior = ?,
                    custom_start = ?,
                    custom_end = ?,
                    schedule_sessions = ?,
                    schedule_per_day = ?,
                    updated_at = NOW()
                WHERE user_id = ?
            ");
            $stmt->execute([
                $market, $timeframe, $riskLevel, $tradeAmount, $dailyLimit,
                $tpTarget, $mlLimit, $scheduleMode, json_encode($activeStrategies),
                $resumeBehavior, $customStart, $customEnd, $scheduleSessions,
                json_encode($schedulePerDay), $adminId
            ]);

            $message = __('admin_settings_updated');
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

            $message = str_replace(':type', strtoupper($accountType), __('admin_switched_account'));
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
        <h1 class="page-title"><i class="fas fa-robot"></i> <?php _e('admin_myrobot_title'); ?></h1>
        <p class="page-subtitle"><?php _e('admin_myrobot_subtitle'); ?></p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <!-- Account Type Switch -->
        <form method="POST" class="d-inline">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="switch_account_type">
            <input type="hidden" name="account_type" value="<?php echo $adminUser['olymptrade_account_type'] === 'demo' ? 'real' : 'demo'; ?>">
            <button type="submit" class="btn btn-<?php echo $adminUser['olymptrade_account_type'] === 'demo' ? 'warning' : 'success'; ?>">
                <i class="fas fa-<?php echo $adminUser['olymptrade_account_type'] === 'demo' ? 'flask' : 'dollar-sign'; ?> me-2"></i>
                <?php echo $adminUser['olymptrade_account_type'] === 'demo' ? __('admin_demo_mode') : __('admin_real_mode'); ?>
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
                        <h3 class="mb-1"><?php _e('admin_robot_status'); ?></h3>
                        <p class="mb-0">
                            <span class="badge badge-<?php echo $robotSettings['robot_enabled'] ? 'success' : 'secondary'; ?> badge-lg">
                                <i class="fas fa-<?php echo $robotSettings['robot_enabled'] ? 'play' : 'stop'; ?> me-1"></i>
                                <?php echo $robotSettings['robot_enabled'] ? __('admin_running') : __('admin_stopped'); ?>
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
                            <?php _e('admin_trading_on'); ?> <strong><?php echo strtoupper($adminUser['olymptrade_account_type'] ?? 'demo'); ?></strong>
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
                        <?php echo $robotSettings['robot_enabled'] ? __('admin_btn_stop_robot') : __('admin_btn_start_robot'); ?>
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
        <div class="stat-label"><?php _e('admin_stat_today_trades'); ?></div>
    </div>
    <div class="stat-card success fade-in">
        <div class="stat-icon"><i class="fas fa-check"></i></div>
        <div class="stat-value"><?php echo $todayStats['wins'] ?? 0; ?></div>
        <div class="stat-label"><?php _e('admin_stat_wins'); ?></div>
    </div>
    <div class="stat-card danger fade-in">
        <div class="stat-icon"><i class="fas fa-times"></i></div>
        <div class="stat-value"><?php echo $todayStats['losses'] ?? 0; ?></div>
        <div class="stat-label"><?php _e('admin_stat_losses'); ?></div>
    </div>
    <div class="stat-card <?php echo ($todayStats['pnl'] ?? 0) >= 0 ? 'success' : 'danger'; ?> fade-in">
        <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
        <div class="stat-value"><?php echo ($todayStats['pnl'] ?? 0) >= 0 ? '+' : ''; ?>$<?php echo number_format($todayStats['pnl'] ?? 0, 2); ?></div>
        <div class="stat-label"><?php _e('admin_stat_today_pnl'); ?></div>
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
                    <h5 class="admin-card-title"><i class="fas fa-sliders-h text-primary"></i> <?php _e('admin_trading_settings'); ?></h5>
                </div>
                <div class="admin-card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?php _e('admin_label_market'); ?></label>
                            <select name="market" class="form-select">
                                <option value="EUR/USD" <?php echo $robotSettings['market'] === 'EUR/USD' ? 'selected' : ''; ?>>EUR/USD</option>
                                <option value="GBP/USD" <?php echo $robotSettings['market'] === 'GBP/USD' ? 'selected' : ''; ?>>GBP/USD</option>
                                <option value="USD/JPY" <?php echo $robotSettings['market'] === 'USD/JPY' ? 'selected' : ''; ?>>USD/JPY</option>
                                <option value="AUD/USD" <?php echo $robotSettings['market'] === 'AUD/USD' ? 'selected' : ''; ?>>AUD/USD</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?php _e('admin_label_timeframe'); ?></label>
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
                            <label class="form-label"><?php _e('admin_label_risk_level'); ?></label>
                            <select name="risk_level" class="form-select">
                                <option value="low" <?php echo $robotSettings['risk_level'] === 'low' ? 'selected' : ''; ?>><?php _e('admin_risk_low'); ?></option>
                                <option value="medium" <?php echo $robotSettings['risk_level'] === 'medium' ? 'selected' : ''; ?>><?php _e('admin_risk_medium'); ?></option>
                                <option value="high" <?php echo $robotSettings['risk_level'] === 'high' ? 'selected' : ''; ?>><?php _e('admin_risk_high'); ?></option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?php _e('admin_label_schedule_mode'); ?></label>
                            <select name="schedule_mode" id="scheduleMode" class="form-select" onchange="toggleScheduleUI()">
                                <option value="auto_24h" <?php echo ($robotSettings['schedule_mode'] ?? 'auto_24h') === 'auto_24h' ? 'selected' : ''; ?>>
                                    <?php _e('admin_schedule_24h'); ?>
                                </option>
                                <option value="best_hours" <?php echo ($robotSettings['schedule_mode'] ?? '') === 'best_hours' ? 'selected' : ''; ?>>
                                    <?php _e('admin_schedule_best'); ?>
                                </option>
                                <option value="custom_single" <?php echo ($robotSettings['schedule_mode'] ?? '') === 'custom_single' ? 'selected' : ''; ?>>
                                    <?php _e('admin_schedule_custom'); ?>
                                </option>
                                <option value="multi_session" <?php echo ($robotSettings['schedule_mode'] ?? '') === 'multi_session' ? 'selected' : ''; ?>>
                                    <?php _e('admin_schedule_multi'); ?>
                                </option>
                                <option value="per_day" <?php echo ($robotSettings['schedule_mode'] ?? '') === 'per_day' ? 'selected' : ''; ?>>
                                    <?php _e('admin_schedule_per_day'); ?>
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Custom Single Session UI -->
                    <div id="customSingleUI" class="schedule-ui-panel" style="display: none;">
                        <div class="row mt-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><i class="fas fa-play text-success"></i> <?php _e('admin_label_start_time'); ?></label>
                                <input type="time" name="custom_start" id="customStart" class="form-control"
                                       value="<?php echo $robotSettings['custom_start'] ?? '08:00'; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><i class="fas fa-stop text-danger"></i> <?php _e('admin_label_end_time'); ?></label>
                                <input type="time" name="custom_end" id="customEnd" class="form-control"
                                       value="<?php echo $robotSettings['custom_end'] ?? '22:00'; ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Multi-Session UI -->
                    <div id="multiSessionUI" class="schedule-ui-panel" style="display: none;">
                        <div class="mt-3">
                            <label class="form-label"><i class="fas fa-layer-group"></i> <?php _e('admin_multiple_sessions'); ?></label>
                            <div id="sessionsContainer">
                                <!-- Sessions will be rendered here -->
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="addSession()">
                                <i class="fas fa-plus"></i> <?php _e('admin_btn_add_session'); ?>
                            </button>
                            <input type="hidden" name="schedule_sessions" id="scheduleSessionsInput" value="">
                        </div>
                    </div>

                    <!-- Per Day UI -->
                    <div id="perDayUI" class="schedule-ui-panel" style="display: none;">
                        <div class="mt-3">
                            <label class="form-label"><i class="fas fa-calendar-week"></i> <?php _e('admin_schedule_per_day_label'); ?></label>
                            <?php
                            $days = ['monday' => __('admin_day_monday'), 'tuesday' => __('admin_day_tuesday'), 'wednesday' => __('admin_day_wednesday'), 'thursday' => __('admin_day_thursday'), 'friday' => __('admin_day_friday')];
                            $schedulePerDay = json_decode($robotSettings['schedule_per_day'] ?? '{}', true) ?: [];
                            ?>
                            <?php foreach ($days as $dayKey => $dayName): ?>
                            <div class="day-schedule-row mb-2">
                                <div class="row align-items-center">
                                    <div class="col-3">
                                        <label class="form-check">
                                            <input type="checkbox" class="form-check-input" name="day_enabled[<?php echo $dayKey; ?>]"
                                                   <?php echo isset($schedulePerDay[$dayKey]) && $schedulePerDay[$dayKey]['enabled'] ? 'checked' : ''; ?>>
                                            <span class="form-check-label"><?php echo $dayName; ?></span>
                                        </label>
                                    </div>
                                    <div class="col-4">
                                        <input type="time" class="form-control form-control-sm" name="day_start[<?php echo $dayKey; ?>]"
                                               value="<?php echo $schedulePerDay[$dayKey]['start'] ?? '08:00'; ?>">
                                    </div>
                                    <div class="col-1 text-center">-</div>
                                    <div class="col-4">
                                        <input type="time" class="form-control form-control-sm" name="day_end[<?php echo $dayKey; ?>]"
                                               value="<?php echo $schedulePerDay[$dayKey]['end'] ?? '22:00'; ?>">
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <button type="button" class="btn btn-outline-secondary btn-sm mt-2" onclick="copyToAllDays()">
                                <i class="fas fa-copy"></i> <?php _e('admin_copy_to_all'); ?>
                            </button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?php _e('admin_label_trade_amount'); ?></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="trade_amount" class="form-control"
                                       value="<?php echo $robotSettings['trade_amount']; ?>" min="10000" step="1000">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?php _e('admin_label_daily_limit'); ?></label>
                            <div class="input-group">
                                <input type="number" name="daily_limit" class="form-control"
                                       value="<?php echo $robotSettings['daily_limit']; ?>" min="1" max="100">
                                <span class="input-group-text"><?php _e('admin_trades_suffix'); ?></span>
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
                    <h5 class="admin-card-title"><i class="fas fa-shield-alt text-warning"></i> <?php _e('admin_risk_management'); ?></h5>
                </div>
                <div class="admin-card-body">
                    <div class="mb-4">
                        <label class="form-label">
                            <i class="fas fa-arrow-up text-success me-1"></i> <?php _e('admin_label_tp_target'); ?>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="take_profit_target" class="form-control"
                                   value="<?php echo $robotSettings['take_profit_target']; ?>" min="10" step="5">
                        </div>
                        <small class="text-muted"><?php _e('admin_tp_hint'); ?></small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">
                            <i class="fas fa-arrow-down text-danger me-1"></i> <?php _e('admin_label_ml_limit'); ?>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="max_loss_limit" class="form-control"
                                   value="<?php echo $robotSettings['max_loss_limit']; ?>" min="10" step="5">
                        </div>
                        <small class="text-muted"><?php _e('admin_ml_hint'); ?></small>
                    </div>

                    <!-- Resume Behavior -->
                    <div class="mb-4">
                        <label class="form-label">
                            <i class="fas fa-redo text-info me-1"></i> <?php _e('admin_label_resume_behavior'); ?>
                        </label>
                        <select name="resume_behavior" class="form-select">
                            <option value="next_session" <?php echo ($robotSettings['resume_behavior'] ?? 'next_session') === 'next_session' ? 'selected' : ''; ?>>
                                <?php _e('admin_resume_next_session'); ?>
                            </option>
                            <option value="next_day" <?php echo ($robotSettings['resume_behavior'] ?? '') === 'next_day' ? 'selected' : ''; ?>>
                                <?php _e('admin_resume_next_day'); ?>
                            </option>
                            <option value="manual_only" <?php echo ($robotSettings['resume_behavior'] ?? '') === 'manual_only' ? 'selected' : ''; ?>>
                                <?php _e('admin_resume_manual'); ?>
                            </option>
                        </select>
                        <small class="text-muted"><?php _e('admin_resume_hint'); ?></small>
                    </div>

                    <div class="alert alert-info py-2 mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong><?php _e('admin_current_daily_pnl'); ?></strong>
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
            <h5 class="admin-card-title"><i class="fas fa-brain text-info"></i> <?php _e('admin_active_strategies'); ?></h5>
            <span class="badge badge-primary"><?php echo str_replace([':active', ':total'], [count($activeStrategies), count($strategies)], __('admin_strategies_count')); ?></span>
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
                                ~<?php echo $strategy['winrate']; ?>% <?php _e('admin_winrate_label'); ?>
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
                    <h5 class="mb-1"><i class="fas fa-save me-2 text-primary"></i><?php _e('admin_save_settings'); ?></h5>
                    <p class="text-muted mb-0 small"><?php _e('admin_settings_apply_immediately'); ?></p>
                </div>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save me-2"></i><?php _e('admin_save_all_settings'); ?>
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

/* Schedule UI Panels */
.schedule-ui-panel {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    padding: 1rem;
    margin-top: 1rem;
}

.day-schedule-row {
    background: rgba(255, 255, 255, 0.02);
    padding: 0.5rem;
    border-radius: 6px;
}

.session-row {
    background: rgba(255, 255, 255, 0.02);
    padding: 0.75rem;
    border-radius: 6px;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.session-row .btn-remove {
    color: var(--danger);
    cursor: pointer;
}

.session-row .btn-remove:hover {
    color: #ff4757;
}
</style>

<script>
// Schedule sessions data
let sessions = <?php echo json_encode(json_decode($robotSettings['schedule_sessions'] ?? '[]', true) ?: [['start' => '08:00', 'end' => '12:00'], ['start' => '14:00', 'end' => '22:00']]); ?>;

// Toggle Schedule UI based on selected mode
function toggleScheduleUI() {
    const mode = document.getElementById('scheduleMode').value;

    // Hide all panels first
    document.querySelectorAll('.schedule-ui-panel').forEach(panel => {
        panel.style.display = 'none';
    });

    // Show relevant panel
    if (mode === 'custom_single') {
        document.getElementById('customSingleUI').style.display = 'block';
    } else if (mode === 'multi_session') {
        document.getElementById('multiSessionUI').style.display = 'block';
        renderSessions();
    } else if (mode === 'per_day') {
        document.getElementById('perDayUI').style.display = 'block';
    }
}

// Render multi-session rows
function renderSessions() {
    const container = document.getElementById('sessionsContainer');
    container.innerHTML = '';

    sessions.forEach((session, index) => {
        const row = document.createElement('div');
        row.className = 'session-row';
        row.innerHTML = `
            <span class="badge badge-primary">Sesi ${index + 1}</span>
            <input type="time" class="form-control form-control-sm" style="width: 120px;"
                   value="${session.start}" onchange="updateSession(${index}, 'start', this.value)">
            <span>-</span>
            <input type="time" class="form-control form-control-sm" style="width: 120px;"
                   value="${session.end}" onchange="updateSession(${index}, 'end', this.value)">
            ${index > 0 ? `<span class="btn-remove" onclick="removeSession(${index})"><i class="fas fa-trash"></i></span>` : ''}
        `;
        container.appendChild(row);
    });

    // Update hidden input
    document.getElementById('scheduleSessionsInput').value = JSON.stringify(sessions);
}

// Add new session
function addSession() {
    if (sessions.length < 5) {
        sessions.push({ start: '08:00', end: '12:00' });
        renderSessions();
    } else {
        alert('<?php echo addslashes(__('admin_max_sessions')); ?>');
    }
}

// Remove session
function removeSession(index) {
    if (sessions.length > 1) {
        sessions.splice(index, 1);
        renderSessions();
    }
}

// Update session time
function updateSession(index, field, value) {
    sessions[index][field] = value;
    document.getElementById('scheduleSessionsInput').value = JSON.stringify(sessions);
}

// Copy Monday schedule to all days
function copyToAllDays() {
    const mondayStart = document.querySelector('input[name="day_start[monday]"]').value;
    const mondayEnd = document.querySelector('input[name="day_end[monday]"]').value;
    const mondayEnabled = document.querySelector('input[name="day_enabled[monday]"]').checked;

    ['tuesday', 'wednesday', 'thursday', 'friday'].forEach(day => {
        document.querySelector(`input[name="day_start[${day}]"]`).value = mondayStart;
        document.querySelector(`input[name="day_end[${day}]"]`).value = mondayEnd;
        document.querySelector(`input[name="day_enabled[${day}]"]`).checked = mondayEnabled;
    });

    alert('<?php echo addslashes(__('admin_copy_success')); ?>');
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleScheduleUI();
});
</script>

<?php require_once 'includes/admin-footer.php'; ?>
