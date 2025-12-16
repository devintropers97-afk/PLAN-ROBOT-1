<?php
$page_title = 'Dashboard';
require_once 'includes/header.php';

// Require login
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get user data
$user = getUserById($_SESSION['user_id']);
$robotSettings = getRobotSettings($_SESSION['user_id']);
$stats = getUserStats($_SESSION['user_id'], 30);
$recentTrades = getRecentTrades($_SESSION['user_id'], 10);
$allStrategies = getAllStrategies();
$userStrategies = getStrategiesByTier(strtoupper($user['package'] ?? 'free'));
$packageInfo = getPackageDetails($user['package'] ?? 'free');

// Calculate days remaining for paid packages
$daysRemaining = 0;
if ($user['package'] !== 'free' && $user['package_expiry']) {
    $daysRemaining = daysRemaining($user['package_expiry']);
}

// Calculate TP/ML progress
$tpTarget = $robotSettings['take_profit_target'] ?? 50;
$mlLimit = $robotSettings['max_loss_limit'] ?? 25;
$currentPnl = $robotSettings['current_daily_pnl'] ?? 0;
$tpProgress = $currentPnl >= 0 ? min(100, ($currentPnl / $tpTarget) * 100) : 0;
$mlProgress = $currentPnl < 0 ? min(100, (abs($currentPnl) / $mlLimit) * 100) : 0;

// Determine signal status
$signalStatus = 'inactive';
$autoPaused = $robotSettings['auto_pause_triggered'] ?? false;
$isWeekendNow = isWeekend();

if ($isWeekendNow && WEEKEND_AUTO_OFF) {
    $signalStatus = 'weekend';
} elseif ($autoPaused) {
    $signalStatus = 'paused';
} elseif ($robotSettings['robot_enabled']) {
    $signalStatus = 'active';
} elseif ($user['status'] === 'active') {
    $signalStatus = 'standby';
}

// Money management settings
$moneyManagementType = $robotSettings['money_management_type'] ?? 'flat';
$martingaleStep = $robotSettings['martingale_step'] ?? 0;
$martingaleBase = $robotSettings['martingale_base_amount'] ?? 10000;

// Get timeframe amounts
$timeframeAmounts = json_decode($robotSettings['timeframe_amounts'] ?? '{}', true) ?: [];
if (empty($timeframeAmounts)) {
    $timeframeAmounts = [
        '5M' => TIMEFRAME_AMOUNTS['5M']['default'],
        '15M' => TIMEFRAME_AMOUNTS['15M']['default'],
        '30M' => TIMEFRAME_AMOUNTS['30M']['default'],
        '1H' => TIMEFRAME_AMOUNTS['1H']['default']
    ];
}

// Weekend message
$weekendMessage = getWeekendMessage();

// Setup progress for onboarding
$setupProgress = getSetupProgress($_SESSION['user_id']);

// Daily target progress
$dailyTarget = getDailyTargetProgress($_SESSION['user_id']);

// Performance score
$performanceScore = calculatePerformanceScore($_SESSION['user_id'], 30);
?>

<section class="dashboard-page">
    <div class="container py-4">
        <!-- Weekend Alert Banner -->
        <?php if ($weekendMessage): ?>
        <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-calendar-times fa-2x me-3"></i>
                <div>
                    <strong>Weekend Mode</strong>
                    <p class="mb-0 small"><?php echo $weekendMessage; ?></p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Real Account Warning -->
        <?php if (REAL_ACCOUNT_ONLY): ?>
        <div class="alert alert-info alert-sm mb-3">
            <i class="fas fa-info-circle me-2"></i>
            <small>ZYN Trade System hanya mendukung <strong>Real Account</strong> OlympTrade untuk menjaga akurasi statistik.</small>
        </div>
        <?php endif; ?>

        <!-- Setup Checklist (Show if not 100% complete) -->
        <?php if ($setupProgress['percentage'] < 100): ?>
        <div class="card mb-4 border-primary">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-rocket"></i> ZYN Setup Checklist</h5>
                <span class="badge bg-light text-primary"><?php echo $setupProgress['percentage']; ?>%</span>
            </div>
            <div class="card-body">
                <div class="progress mb-3" style="height: 8px;">
                    <div class="progress-bar bg-success" style="width: <?php echo $setupProgress['percentage']; ?>%"></div>
                </div>
                <div class="row">
                    <?php foreach ($setupProgress['items'] as $item): ?>
                    <div class="col-md-4 col-6 mb-2">
                        <div class="d-flex align-items-center <?php echo $item['completed'] ? 'text-success' : 'text-muted'; ?>">
                            <i class="fas <?php echo $item['completed'] ? 'fa-check-circle' : 'fa-circle'; ?> me-2"></i>
                            <small><?php echo $item['title']; ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if ($setupProgress['percentage'] < 100): ?>
                <div class="mt-3 text-center">
                    <a href="settings.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-cog"></i> Lanjutkan Setup
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Daily Target Progress -->
        <?php if ($dailyTarget && $dailyTarget['target'] > 0): ?>
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">
                        <i class="fas fa-bullseye text-primary"></i> Target Hari Ini: $<?php echo number_format($dailyTarget['target'], 0); ?>
                    </h6>
                    <span class="<?php echo $dailyTarget['achieved'] ? 'text-success' : ''; ?>">
                        <?php if ($dailyTarget['achieved']): ?>
                            <i class="fas fa-trophy text-warning"></i> Target Tercapai!
                        <?php else: ?>
                            $<?php echo number_format($dailyTarget['current'], 2); ?> / $<?php echo number_format($dailyTarget['target'], 0); ?>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="progress" style="height: 20px;">
                    <div class="progress-bar <?php echo $dailyTarget['achieved'] ? 'bg-success' : 'bg-primary'; ?>"
                         style="width: <?php echo min(100, $dailyTarget['progress']); ?>%">
                        <?php echo round($dailyTarget['progress']); ?>%
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-2">
                    <small class="text-muted">Trades: <?php echo $dailyTarget['trades']; ?> | W: <?php echo $dailyTarget['wins']; ?> | L: <?php echo $dailyTarget['losses']; ?></small>
                    <?php if ($dailyTarget['auto_stop']): ?>
                    <small class="text-success"><i class="fas fa-pause-circle"></i> Auto-stop ON</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div>
                <h1 class="dashboard-title">Selamat datang, <?php echo htmlspecialchars($user['fullname']); ?></h1>
                <p class="dashboard-subtitle">
                    <span class="badge bg-primary me-2"><?php echo strtoupper($user['package']); ?></span>
                    <code class="text-muted"><?php echo $user['license_key'] ?? '-'; ?></code>
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="settings.php" class="btn btn-secondary">
                    <i class="fas fa-cog"></i> Settings
                </a>
                <a href="profile.php" class="btn btn-outline-light">
                    <i class="fas fa-user"></i> Profile
                </a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-crown fa-2x text-warning mb-2"></i>
                        <h5 class="mb-1 text-primary"><?php echo strtoupper($packageInfo['name']); ?></h5>
                        <small class="text-muted"><?php echo $packageInfo['strategies']; ?> Strategi</small>
                        <?php if ($user['package'] !== 'free' && $daysRemaining <= 7): ?>
                        <span class="badge bg-warning d-block mt-2">Exp: <?php echo $daysRemaining; ?> hari</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-bar fa-2x text-info mb-2"></i>
                        <h5 class="mb-1"><?php echo $stats['total_trades'] ?? 0; ?></h5>
                        <small class="text-muted">Total Trades (30d)</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-percentage fa-2x text-success mb-2"></i>
                        <h5 class="mb-1 <?php echo ($stats['win_rate'] ?? 0) >= 70 ? 'text-success' : ''; ?>">
                            <?php echo number_format($stats['win_rate'] ?? 0, 1); ?>%
                        </h5>
                        <small class="text-muted">Win Rate</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-dollar-sign fa-2x <?php echo ($stats['total_pnl'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?> mb-2"></i>
                        <h5 class="mb-1 <?php echo ($stats['total_pnl'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?>">
                            <?php echo ($stats['total_pnl'] ?? 0) >= 0 ? '+' : ''; ?>$<?php echo number_format(abs($stats['total_pnl'] ?? 0), 2); ?>
                        </h5>
                        <small class="text-muted">Profit/Loss</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Robot Control Panel -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <!-- Robot Status Header -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="d-flex align-items-center">
                                <div class="robot-status-indicator <?php echo $signalStatus; ?> me-3">
                                    <i class="fas fa-robot fa-2x"></i>
                                </div>
                                <div>
                                    <h4 class="mb-0">
                                        Robot <?php
                                        switch($signalStatus) {
                                            case 'weekend': echo 'WEEKEND MODE'; break;
                                            case 'paused': echo 'AUTO-PAUSED'; break;
                                            default: echo strtoupper($signalStatus);
                                        }
                                        ?>
                                    </h4>
                                    <small class="text-muted">
                                        <?php
                                        switch($signalStatus) {
                                            case 'active': echo 'Trading otomatis berjalan'; break;
                                            case 'weekend': echo 'Market tutup. Lanjut Senin.'; break;
                                            case 'paused':
                                                $reason = $robotSettings['auto_pause_reason'] ?? '';
                                                echo in_array($reason, ['tp_reached', 'take_profit']) ? 'Target Profit tercapai!' : 'Max Loss tercapai!';
                                                break;
                                            case 'standby': echo 'Siap trading. Nyalakan untuk mulai.'; break;
                                            default: echo 'Konfigurasi pengaturan untuk mulai';
                                        }
                                        ?>
                                    </small>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="d-block small text-muted mb-1">Master Switch</span>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="masterToggle" <?php echo $robotSettings['robot_enabled'] ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>

                        <!-- Auto-Pause Progress (TP/ML) -->
                        <div class="auto-pause-section mb-4">
                            <h6 class="mb-3"><i class="fas fa-shield-alt"></i> Auto-Pause System</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="progress-card bg-success-soft">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="small"><i class="fas fa-arrow-up text-success"></i> Take Profit</span>
                                            <span class="small fw-bold">$<?php echo number_format($currentPnl >= 0 ? $currentPnl : 0, 2); ?> / $<?php echo number_format($tpTarget, 2); ?></span>
                                        </div>
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar bg-success" style="width: <?php echo $tpProgress; ?>%"></div>
                                        </div>
                                        <?php if ($robotSettings['auto_pause_reason'] === 'tp_reached'): ?>
                                        <span class="badge bg-success mt-2"><i class="fas fa-check"></i> TARGET TERCAPAI!</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="progress-card bg-danger-soft">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="small"><i class="fas fa-arrow-down text-danger"></i> Max Loss</span>
                                            <span class="small fw-bold">$<?php echo number_format($currentPnl < 0 ? abs($currentPnl) : 0, 2); ?> / $<?php echo number_format($mlLimit, 2); ?></span>
                                        </div>
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar bg-danger" style="width: <?php echo $mlProgress; ?>%"></div>
                                        </div>
                                        <?php if ($robotSettings['auto_pause_reason'] === 'ml_reached'): ?>
                                        <span class="badge bg-danger mt-2"><i class="fas fa-exclamation"></i> LIMIT TERCAPAI!</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-3 mt-2">
                                <div class="col-md-6">
                                    <label class="form-label small">Target Take Profit ($)</label>
                                    <input type="number" class="form-control form-control-sm" id="tpTarget" value="<?php echo $tpTarget; ?>" min="10" max="1000">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Batas Max Loss ($)</label>
                                    <input type="number" class="form-control form-control-sm" id="mlLimit" value="<?php echo $mlLimit; ?>" min="5" max="500">
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Schedule System -->
                        <div class="schedule-section mb-4">
                            <h6 class="mb-3"><i class="fas fa-clock"></i> Jadwal Trading</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small">Mode Jadwal</label>
                                    <select class="form-select form-select-sm" id="scheduleMode">
                                        <option value="auto_24h" <?php echo ($robotSettings['schedule_mode'] ?? 'auto_24h') === 'auto_24h' ? 'selected' : ''; ?>>
                                            24 Jam Otomatis
                                        </option>
                                        <option value="best_hours" <?php echo ($robotSettings['schedule_mode'] ?? '') === 'best_hours' ? 'selected' : ''; ?>>
                                            Best Hours (09:00-21:00)
                                        </option>
                                        <option value="custom_single" <?php echo ($robotSettings['schedule_mode'] ?? '') === 'custom_single' ? 'selected' : ''; ?>>
                                            Custom Single Session
                                        </option>
                                        <option value="multi_session" <?php echo ($robotSettings['schedule_mode'] ?? '') === 'multi_session' ? 'selected' : ''; ?>>
                                            Multi-Session
                                        </option>
                                        <option value="per_day" <?php echo ($robotSettings['schedule_mode'] ?? '') === 'per_day' ? 'selected' : ''; ?>>
                                            Per Hari Berbeda
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-4" id="customTimeStart" style="<?php echo in_array($robotSettings['schedule_mode'] ?? '', ['custom_single']) ? '' : 'display:none;'; ?>">
                                    <label class="form-label small">Mulai</label>
                                    <input type="time" class="form-control form-control-sm" id="scheduleStart" value="<?php echo $robotSettings['schedule_start_time'] ?? '09:00'; ?>">
                                </div>
                                <div class="col-md-4" id="customTimeEnd" style="<?php echo in_array($robotSettings['schedule_mode'] ?? '', ['custom_single']) ? '' : 'display:none;'; ?>">
                                    <label class="form-label small">Selesai</label>
                                    <input type="time" class="form-control form-control-sm" id="scheduleEnd" value="<?php echo $robotSettings['schedule_end_time'] ?? '21:00'; ?>">
                                </div>
                            </div>

                            <!-- Multi-Session Container -->
                            <div id="multiSessionContainer" class="mt-3" style="<?php echo ($robotSettings['schedule_mode'] ?? '') === 'multi_session' ? '' : 'display:none;'; ?>">
                                <div class="card bg-dark border mb-3">
                                    <div class="card-header py-2">
                                        <small class="fw-bold"><i class="fas fa-list"></i> Sesi Trading (Sama Setiap Hari)</small>
                                    </div>
                                    <div class="card-body py-2">
                                        <div id="sessionsList">
                                            <?php
                                            $sessions = json_decode($robotSettings['schedule_sessions'] ?? '[]', true) ?: [
                                                ['start' => '09:00', 'end' => '12:00'],
                                                ['start' => '14:00', 'end' => '17:00'],
                                                ['start' => '20:00', 'end' => '22:00']
                                            ];
                                            foreach ($sessions as $idx => $session): ?>
                                            <div class="session-row d-flex align-items-center gap-2 mb-2" data-session="<?php echo $idx; ?>">
                                                <span class="badge bg-primary">Sesi <?php echo $idx + 1; ?></span>
                                                <input type="time" class="form-control form-control-sm session-start" value="<?php echo $session['start']; ?>" style="width:100px;">
                                                <span>-</span>
                                                <input type="time" class="form-control form-control-sm session-end" value="<?php echo $session['end']; ?>" style="width:100px;">
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-session" <?php echo count($sessions) <= 1 ? 'disabled' : ''; ?>>
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-success mt-2" id="addSessionBtn">
                                            <i class="fas fa-plus"></i> Tambah Sesi
                                        </button>
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle"></i> Sesi tidak boleh overlap. Min 30 menit per sesi.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Per-Day Schedule Container -->
                            <div id="perDayContainer" class="mt-3" style="<?php echo ($robotSettings['schedule_mode'] ?? '') === 'per_day' ? '' : 'display:none;'; ?>">
                                <div class="card bg-dark border mb-3">
                                    <div class="card-header py-2 d-flex justify-content-between align-items-center">
                                        <small class="fw-bold"><i class="fas fa-calendar-alt"></i> Jadwal Per Hari</small>
                                        <button type="button" class="btn btn-sm btn-outline-info" id="copyMondayToAll">
                                            <i class="fas fa-copy"></i> Copy Senin ke Semua
                                        </button>
                                    </div>
                                    <div class="card-body py-2">
                                        <?php
                                        $perDay = json_decode($robotSettings['schedule_per_day'] ?? '{}', true) ?: [
                                            '1' => [['start' => '09:00', 'end' => '17:00']],
                                            '2' => [['start' => '09:00', 'end' => '17:00']],
                                            '3' => [['start' => '09:00', 'end' => '17:00']],
                                            '4' => [['start' => '09:00', 'end' => '17:00']],
                                            '5' => [['start' => '09:00', 'end' => '12:00']]
                                        ];
                                        $dayNames = ['1' => 'Senin', '2' => 'Selasa', '3' => 'Rabu', '4' => 'Kamis', '5' => 'Jumat'];
                                        foreach ($dayNames as $dayNum => $dayName):
                                            $daySessions = $perDay[$dayNum] ?? [['start' => '09:00', 'end' => '17:00']];
                                        ?>
                                        <div class="day-schedule mb-3" data-day="<?php echo $dayNum; ?>">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span class="badge bg-<?php echo $dayNum == 5 ? 'warning text-dark' : 'primary'; ?>">
                                                    <?php echo $dayName; ?>
                                                </span>
                                                <button type="button" class="btn btn-sm btn-outline-success add-day-session">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                            <div class="day-sessions">
                                                <?php foreach ($daySessions as $idx => $session): ?>
                                                <div class="d-flex align-items-center gap-2 mb-1 day-session-row">
                                                    <input type="time" class="form-control form-control-sm day-session-start" value="<?php echo $session['start']; ?>" style="width:90px;">
                                                    <span>-</span>
                                                    <input type="time" class="form-control form-control-sm day-session-end" value="<?php echo $session['end']; ?>" style="width:90px;">
                                                    <button type="button" class="btn btn-sm btn-outline-danger remove-day-session" <?php echo count($daySessions) <= 1 ? 'disabled' : ''; ?>>
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                        <div class="text-center py-2 border-top mt-2">
                                            <small class="text-muted">
                                                <i class="fas fa-ban"></i> Sabtu & Minggu = LIBUR (Market tutup)
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Money Management Section -->
                        <div class="money-management-section mb-4">
                            <h6 class="mb-3"><i class="fas fa-coins"></i> Money Management</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small">Tipe Management</label>
                                    <select class="form-select form-select-sm" id="moneyManagementType">
                                        <option value="flat" <?php echo $moneyManagementType === 'flat' ? 'selected' : ''; ?>>
                                            Flat Amount (Tetap)
                                        </option>
                                        <option value="martingale" <?php echo $moneyManagementType === 'martingale' ? 'selected' : ''; ?>>
                                            Martingale (x2 setelah loss)
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-8" id="martingaleInfo" style="<?php echo $moneyManagementType === 'martingale' ? '' : 'display:none;'; ?>">
                                    <div class="alert alert-warning alert-sm mb-0">
                                        <small>
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            <strong>Martingale:</strong> Base Rp<?php echo number_format($martingaleBase, 0, ',', '.'); ?>
                                            → Step 1: Rp<?php echo number_format($martingaleBase * 2, 0, ',', '.'); ?>
                                            → Step 2: Rp<?php echo number_format($martingaleBase * 4, 0, ',', '.'); ?>
                                            → Step 3: Rp<?php echo number_format($martingaleBase * 8, 0, ',', '.'); ?> (Max)
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Multi-Timeframe Amount Settings -->
                        <div class="timeframe-amount-section mb-4">
                            <h6 class="mb-3">
                                <i class="fas fa-layer-group"></i> Amount per Timeframe
                                <span class="badge bg-info ms-2">Multi-TF</span>
                            </h6>
                            <p class="small text-muted mb-3">Atur jumlah trade berbeda untuk setiap timeframe</p>
                            <div class="row g-3">
                                <?php foreach (ALLOWED_TIMEFRAMES as $tf):
                                    $tfSettings = TIMEFRAME_AMOUNTS[$tf];
                                    $currentAmount = $timeframeAmounts[$tf] ?? $tfSettings['default'];
                                ?>
                                <div class="col-md-3 col-6">
                                    <label class="form-label small"><?php echo $tf; ?></label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control timeframe-amount"
                                               data-tf="<?php echo $tf; ?>"
                                               min="<?php echo $tfSettings['min']; ?>"
                                               max="<?php echo $tfSettings['max']; ?>"
                                               step="1000"
                                               value="<?php echo $currentAmount; ?>">
                                    </div>
                                    <small class="text-muted">Min: Rp<?php echo number_format($tfSettings['min'], 0, ',', '.'); ?></small>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <hr>

                        <!-- Market & Settings -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label small">Market</label>
                                <select class="form-select form-select-sm" id="market">
                                    <option value="EUR/USD" <?php echo ($robotSettings['market'] ?? 'EUR/USD') === 'EUR/USD' ? 'selected' : ''; ?>>EUR/USD</option>
                                    <option value="GBP/USD" <?php echo ($robotSettings['market'] ?? '') === 'GBP/USD' ? 'selected' : ''; ?>>GBP/USD</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Timeframe Utama</label>
                                <select class="form-select form-select-sm" id="timeframe">
                                    <option value="5M" <?php echo ($robotSettings['timeframe'] ?? '15M') === '5M' ? 'selected' : ''; ?>>5 Menit</option>
                                    <option value="15M" <?php echo ($robotSettings['timeframe'] ?? '15M') === '15M' ? 'selected' : ''; ?>>15 Menit</option>
                                    <option value="30M" <?php echo ($robotSettings['timeframe'] ?? '') === '30M' ? 'selected' : ''; ?>>30 Menit</option>
                                    <option value="1H" <?php echo ($robotSettings['timeframe'] ?? '') === '1H' ? 'selected' : ''; ?>>1 Jam</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Base Amount (Rp)</label>
                                <input type="number" class="form-control form-control-sm" id="tradeAmount" min="10000" max="1000000" step="1000" value="<?php echo $robotSettings['trade_amount'] ?? 10000; ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Daily Limit</label>
                                <input type="number" class="form-control form-control-sm" id="dailyLimit" min="1" max="100" value="<?php echo $robotSettings['daily_limit'] ?? 10; ?>">
                            </div>
                        </div>

                        <hr>

                        <!-- Strategy Selection -->
                        <div class="strategy-section">
                            <h6 class="mb-3">
                                <i class="fas fa-chess"></i> Pilih Strategi
                                <span class="badge bg-primary ms-2"><?php echo count($userStrategies); ?> tersedia</span>
                            </h6>
                            <?php
                            $selectedStrategies = json_decode($robotSettings['strategies'] ?? '[]', true) ?: [];
                            ?>
                            <div class="row g-2">
                                <?php foreach ($allStrategies as $strategy):
                                    $canAccess = canAccessStrategy($user['package'], $strategy['id']);
                                    $isSelected = in_array($strategy['id'], $selectedStrategies);
                                    $tierBadge = [
                                        'VIP' => 'bg-warning text-dark',
                                        'ELITE' => 'bg-info',
                                        'PRO' => 'bg-primary',
                                        'FREE' => 'bg-secondary'
                                    ][$strategy['tier']] ?? 'bg-dark';
                                ?>
                                <div class="col-md-6 col-lg-4">
                                    <?php $signalFreq = getSignalFrequency($strategy['id']); ?>
                                    <div class="strategy-card <?php echo !$canAccess ? 'locked' : ''; ?> <?php echo $isSelected ? 'selected' : ''; ?>">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <span class="badge <?php echo $tierBadge; ?> mb-1"><?php echo $strategy['tier']; ?></span>
                                                <h6 class="mb-0">#<?php echo $strategy['id']; ?> <?php echo $strategy['name']; ?></h6>
                                            </div>
                                            <?php if ($canAccess): ?>
                                            <input type="checkbox" class="strategy-checkbox" value="<?php echo $strategy['id']; ?>" <?php echo $isSelected ? 'checked' : ''; ?>>
                                            <?php else: ?>
                                            <i class="fas fa-lock text-muted"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small class="text-muted"><?php echo $strategy['risk']; ?> Risk</small>
                                            <span class="badge bg-success"><?php echo $strategy['win_rate']; ?></span>
                                        </div>
                                        <div class="signal-frequency">
                                            <small class="text-info">
                                                <i class="fas fa-signal me-1"></i>
                                                <?php echo $signalFreq['min']; ?>-<?php echo $signalFreq['max']; ?> signal/jam
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if ($user['package'] !== 'vip'): ?>
                            <div class="text-center mt-3">
                                <a href="pricing.php" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-unlock"></i> Upgrade untuk unlock strategi lainnya
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>

                        <hr>

                        <button class="btn btn-primary w-100" id="saveSettings">
                            <i class="fas fa-save"></i> Simpan Pengaturan
                        </button>
                    </div>
                </div>

                <!-- Trade History -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-history"></i> Riwayat Trade</h5>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-light active" data-period="7">7H</button>
                            <button class="btn btn-outline-light" data-period="30">30H</button>
                            <button class="btn btn-outline-light" data-period="90">90H</button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($recentTrades)): ?>
                        <div class="text-center p-4 text-muted">
                            <i class="fas fa-chart-line fa-3x mb-3"></i>
                            <p>Belum ada trade. Nyalakan robot untuk mulai!</p>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-dark table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Waktu</th>
                                        <th>Strategi</th>
                                        <th>Pair/TF</th>
                                        <th>Direction</th>
                                        <th>Amount</th>
                                        <th>Result</th>
                                        <th>P&L</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentTrades as $trade):
                                        // Get strategy details
                                        $strategyInfo = getStrategyById($trade['strategy_id'] ?? '');
                                        $tierBadge = [
                                            'VIP' => 'bg-warning text-dark',
                                            'ELITE' => 'bg-info',
                                            'PRO' => 'bg-primary',
                                            'FREE' => 'bg-secondary'
                                        ][$strategyInfo['tier'] ?? 'FREE'] ?? 'bg-dark';
                                    ?>
                                    <tr>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo date('d/m H:i', strtotime($trade['created_at'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge <?php echo $tierBadge; ?> badge-sm">
                                                    #<?php echo $trade['strategy_id'] ?? '-'; ?>
                                                </span>
                                                <span class="small"><?php echo htmlspecialchars($strategyInfo['name'] ?? $trade['strategy']); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span><?php echo htmlspecialchars($trade['asset']); ?></span>
                                            <small class="text-muted d-block"><?php echo $trade['timeframe'] ?? '15M'; ?></small>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $trade['direction'] === 'call' ? 'bg-success' : 'bg-danger'; ?>">
                                                <i class="fas fa-arrow-<?php echo $trade['direction'] === 'call' ? 'up' : 'down'; ?> me-1"></i>
                                                <?php echo strtoupper($trade['direction']); ?>
                                            </span>
                                        </td>
                                        <td>Rp<?php echo number_format($trade['amount'], 0, ',', '.'); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $trade['result'] === 'win' ? 'success' : ($trade['result'] === 'loss' ? 'danger' : 'secondary'); ?>">
                                                <?php echo strtoupper($trade['result'] ?? 'pending'); ?>
                                            </span>
                                        </td>
                                        <td class="<?php echo ($trade['profit_loss'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo ($trade['profit_loss'] ?? 0) >= 0 ? '+' : ''; ?>Rp<?php echo number_format($trade['profit_loss'] ?? 0, 0, ',', '.'); ?>
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

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Current Status -->
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <div class="robot-status-indicator <?php echo $signalStatus; ?> large mb-3 mx-auto">
                            <i class="fas fa-robot fa-3x"></i>
                        </div>
                        <h4 class="mb-2"><?php echo $signalStatus === 'weekend' ? 'WEEKEND' : strtoupper($signalStatus); ?></h4>
                        <p class="text-muted small mb-0">
                            <?php
                            switch($signalStatus) {
                                case 'active': echo 'Robot berjalan & monitoring market'; break;
                                case 'weekend': echo 'Market tutup. Lanjut Senin.'; break;
                                case 'paused': echo 'Auto-pause aktif. Reset untuk lanjut.'; break;
                                case 'standby': echo 'Siap trading. Nyalakan master switch.'; break;
                                default: echo 'Cek balance & pengaturan';
                            }
                            ?>
                        </p>
                        <?php if ($autoPaused): ?>
                        <button class="btn btn-warning btn-sm mt-3" id="resetAutoPause">
                            <i class="fas fa-redo"></i> Reset Auto-Pause
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Performance Score Quick View -->
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0"><i class="fas fa-star text-warning"></i> Performance</h6>
                            <a href="statistics.php" class="text-primary small">Detail</a>
                        </div>
                        <div class="performance-mini-score">
                            <span class="score-value"><?php echo $performanceScore['score']; ?></span>
                            <span class="score-max">/100</span>
                        </div>
                        <span class="badge bg-<?php echo getPerformanceLevelColor($performanceScore['level']); ?>">
                            <?php echo $performanceScore['level']; ?>
                        </span>
                    </div>
                </div>

                <!-- Live Trade Log with Motivational Messages -->
                <div class="card mb-4">
                    <div class="card-header py-2">
                        <h6 class="mb-0"><i class="fas fa-stream text-info"></i> Live Log</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="live-log-container" id="liveLog" style="max-height: 200px; overflow-y: auto;">
                            <?php
                            $todayTrades = getRecentTrades($_SESSION['user_id'], 1);
                            $winMessages = [
                                "Cha-ching! 💰 Uang masuk lagi",
                                "Profit lagi! Robot bekerja untuk kamu 💪",
                                "Robot on fire 🔥",
                                "Mantap! Profit konsisten 🎯",
                                "YES! Strategi bekerja sempurna ✨",
                            ];
                            $lossMessages = [
                                "Tenang, ini bagian dari trading 📊",
                                "Stay calm, strategi tetap on track 💎",
                                "Koreksi minor, fokus jangka panjang 📈",
                            ];
                            $recoveryMessages = [
                                "Bangkit! Recovery sukses ✅",
                                "Back on track! 🚀",
                            ];

                            if (empty($todayTrades)):
                            ?>
                            <div class="text-center py-3 text-muted small">
                                <i class="fas fa-clock"></i> Belum ada trade hari ini
                            </div>
                            <?php else:
                            $winStreak = 0;
                            $previousWasLoss = false;
                            foreach (array_slice($todayTrades, 0, 10) as $idx => $trade):
                                $isWin = $trade['result'] === 'win';
                                if ($isWin) {
                                    $winStreak++;
                                    if ($previousWasLoss) {
                                        $message = $recoveryMessages[array_rand($recoveryMessages)];
                                        $previousWasLoss = false;
                                    } elseif ($winStreak >= 3) {
                                        $message = "$winStreak WIN berturut-turut! Robot on fire 🔥";
                                    } else {
                                        $message = $winMessages[array_rand($winMessages)];
                                    }
                                } else {
                                    $winStreak = 0;
                                    $previousWasLoss = true;
                                    $message = $lossMessages[array_rand($lossMessages)];
                                }
                            ?>
                            <div class="log-item <?php echo $trade['result']; ?>">
                                <span class="log-time"><?php echo date('H:i', strtotime($trade['created_at'])); ?></span>
                                <span class="log-icon">
                                    <?php echo $trade['result'] === 'win' ? '🎉' : ($trade['result'] === 'loss' ? '❌' : '⏳'); ?>
                                </span>
                                <span class="log-text">
                                    <?php echo strtoupper($trade['result'] ?? 'pending'); ?>
                                    <?php echo ($trade['profit_loss'] ?? 0) >= 0 ? '+' : ''; ?>$<?php echo number_format(abs($trade['profit_loss'] ?? 0), 2); ?>
                                    <span class="log-message">"<?php echo $message; ?>"</span>
                                </span>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="mb-3"><i class="fas fa-chart-pie"></i> Statistik (30 Hari)</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Win Rate</span>
                            <span class="fw-bold text-success"><?php echo number_format($stats['win_rate'] ?? 0, 1); ?>%</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Menang</span>
                            <span class="fw-bold text-success"><?php echo $stats['wins'] ?? 0; ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Kalah</span>
                            <span class="fw-bold text-danger"><?php echo $stats['losses'] ?? 0; ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Total P&L</span>
                            <span class="fw-bold <?php echo ($stats['total_pnl'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?>">
                                <?php echo ($stats['total_pnl'] ?? 0) >= 0 ? '+' : ''; ?>$<?php echo number_format($stats['total_pnl'] ?? 0, 2); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Daily Tips (with upgrade prompts for FREE users) -->
                <div class="card mb-4 border-info">
                    <div class="card-body">
                        <h6 class="mb-2"><i class="fas fa-lightbulb text-warning"></i> Tips Hari Ini</h6>
                        <?php
                        $userPackage = strtolower($user['package'] ?? 'free');

                        // Tips for FREE users with upgrade prompts
                        $freeTips = [
                            ["tip" => "Kamu sudah profit hari ini dengan strategi FREE. Bayangkan kalau pakai VIP dengan win rate 90%! 🤔", "cta" => true],
                            ["tip" => "Robot sudah trading untuk kamu hari ini. Kalau kamu trading manual, butuh 5 jam! ⏰", "cta" => false],
                            ["tip" => "User VIP rata-rata profit 3x lebih banyak dari FREE. Win rate 90% vs 60%! 💎", "cta" => true],
                            ["tip" => "Strategi Triple RSI punya win rate 90-91%. Signal jarang tapi SUPER AKURAT! 🎯", "cta" => true],
                            ["tip" => "Konsistensi lebih penting dari profit besar sesekali. Biarkan robot bekerja!", "cta" => false],
                            ["tip" => "London & NY session (14:00-22:00 WIB) adalah waktu terbaik trading.", "cta" => false],
                            ["tip" => "Strategi premium menambah 3-7 strategi baru dengan win rate hingga 91%!", "cta" => true],
                            ["tip" => "Set Take Profit realistis. $30-50/hari sudah sangat baik! Jangan serakah.", "cta" => false],
                        ];

                        // Tips for paid users
                        $paidTips = [
                            "Jangan trading dengan emosi. Biarkan robot bekerja sesuai sistem.",
                            "Konsistensi lebih penting dari profit besar sesekali.",
                            "Set Take Profit realistis. $30-50/hari sudah sangat baik!",
                            "Win rate 70%+ dengan disiplin = profit jangka panjang.",
                            "Jangan ubah strategi terlalu sering. Beri waktu sistem bekerja.",
                            "London & NY session (14:00-22:00 WIB) adalah waktu terbaik trading.",
                            "Review statistik mingguan untuk evaluasi performa.",
                            "Jangan over-trading. Daily limit membantu kontrol risiko.",
                            "Precision Over Emotion - Biarkan sistem yang bekerja!",
                            "Terima kasih sudah berlangganan! Kami terus meningkatkan akurasi strategi. 🙏"
                        ];

                        if ($userPackage === 'free') {
                            $todayTipData = $freeTips[date('z') % count($freeTips)];
                            echo '<p class="text-muted small mb-0">' . $todayTipData['tip'] . '</p>';
                            if ($todayTipData['cta']) {
                                echo '<a href="pricing.php" class="btn btn-primary btn-sm mt-2"><i class="fas fa-arrow-up"></i> Lihat Premium</a>';
                            }
                        } else {
                            $todayTip = $paidTips[date('z') % count($paidTips)];
                            echo '<p class="text-muted small mb-0">' . $todayTip . '</p>';
                        }
                        ?>
                    </div>
                </div>

                <!-- Package Info -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="mb-3"><i class="fas fa-crown"></i> Paket Anda</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tier</span>
                            <span class="badge bg-primary"><?php echo strtoupper($packageInfo['name']); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Strategi</span>
                            <span><?php echo $packageInfo['strategies']; ?> dari 10</span>
                        </div>
                        <?php if ($user['package'] !== 'free'): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Berakhir</span>
                            <span><?php echo $user['package_expiry'] ? formatDate($user['package_expiry']) : '-'; ?></span>
                        </div>
                        <?php endif; ?>
                        <hr>
                        <a href="pricing.php" class="btn btn-outline-primary w-100 btn-sm">
                            <i class="fas fa-arrow-up"></i> Upgrade Paket
                        </a>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="mb-3"><i class="fas fa-bolt"></i> Aksi Cepat</h6>
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-danger btn-sm text-start" id="stopAllTrades">
                                <i class="fas fa-stop-circle"></i> Stop Semua Trade
                            </button>
                            <a href="statistics.php" class="btn btn-outline-light btn-sm text-start">
                                <i class="fas fa-chart-bar text-info"></i> Lihat Statistik
                            </a>
                            <a href="leaderboard.php" class="btn btn-outline-light btn-sm text-start">
                                <i class="fas fa-trophy text-warning"></i> Leaderboard
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Support Card -->
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fab fa-telegram fa-2x text-primary mb-2"></i>
                        <h6>Butuh Bantuan?</h6>
                        <p class="text-muted small mb-2">Join channel & hubungi support</p>
                        <div class="d-grid gap-2">
                            <a href="<?php echo TELEGRAM_CHANNEL; ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="fab fa-telegram"></i> Join Channel
                            </a>
                            <a href="https://t.me/<?php echo str_replace('@', '', TELEGRAM_USERNAME); ?>" target="_blank" class="btn btn-primary btn-sm">
                                <i class="fas fa-headset"></i> Chat Support
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.robot-status-indicator {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
}
/* Performance Mini Score */
.performance-mini-score {
    margin: 10px 0;
}
.performance-mini-score .score-value {
    font-size: 2.5rem;
    font-weight: bold;
    color: var(--primary-color);
}
.performance-mini-score .score-max {
    font-size: 1rem;
    color: var(--text-muted);
}

/* Live Log */
.log-item {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    border-bottom: 1px solid var(--border-color);
    font-size: 0.8rem;
}
.log-item:last-child {
    border-bottom: none;
}
.log-item.win {
    background: rgba(34, 197, 94, 0.05);
}
.log-item.loss {
    background: rgba(239, 68, 68, 0.05);
}
.log-time {
    color: var(--text-muted);
    min-width: 40px;
    margin-right: 8px;
}
.log-icon {
    margin-right: 8px;
}
.log-text {
    flex: 1;
}
.log-message {
    display: block;
    font-size: 0.7rem;
    color: var(--text-muted);
    font-style: italic;
}

.robot-status-indicator.large {
    width: 100px;
    height: 100px;
}
.robot-status-indicator.active {
    background: rgba(34, 197, 94, 0.2);
    color: #22c55e;
    animation: pulse 2s infinite;
}
.robot-status-indicator.standby {
    background: rgba(251, 191, 36, 0.2);
    color: #fbbf24;
}
.robot-status-indicator.inactive {
    background: rgba(107, 114, 128, 0.2);
    color: #6b7280;
}
.robot-status-indicator.paused {
    background: rgba(239, 68, 68, 0.2);
    color: #ef4444;
}
.robot-status-indicator.weekend {
    background: rgba(147, 51, 234, 0.2);
    color: #a855f7;
}

@keyframes pulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4); }
    50% { box-shadow: 0 0 0 15px rgba(34, 197, 94, 0); }
}

.progress-card {
    padding: 1rem;
    border-radius: 0.5rem;
}
.bg-success-soft { background: rgba(34, 197, 94, 0.1); }
.bg-danger-soft { background: rgba(239, 68, 68, 0.1); }

.strategy-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    padding: 1rem;
    transition: all 0.2s;
    cursor: pointer;
}
.strategy-card:hover:not(.locked) {
    border-color: var(--primary-color);
}
.strategy-card.selected {
    border-color: var(--primary-color);
    background: rgba(0, 212, 255, 0.1);
}
.strategy-card.locked {
    opacity: 0.5;
    cursor: not-allowed;
}
.strategy-checkbox {
    width: 20px;
    height: 20px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Schedule mode toggle
    document.getElementById('scheduleMode').addEventListener('change', function() {
        const customStart = document.getElementById('customTimeStart');
        const customEnd = document.getElementById('customTimeEnd');
        if (this.value === 'custom_single') {
            customStart.style.display = 'block';
            customEnd.style.display = 'block';
        } else {
            customStart.style.display = 'none';
            customEnd.style.display = 'none';
        }
    });

    // Money management type toggle
    document.getElementById('moneyManagementType').addEventListener('change', function() {
        const martingaleInfo = document.getElementById('martingaleInfo');
        if (this.value === 'martingale') {
            martingaleInfo.style.display = 'block';
        } else {
            martingaleInfo.style.display = 'none';
        }
    });

    // Strategy card click
    document.querySelectorAll('.strategy-card:not(.locked)').forEach(card => {
        card.addEventListener('click', function() {
            const checkbox = this.querySelector('.strategy-checkbox');
            if (checkbox) {
                checkbox.checked = !checkbox.checked;
                this.classList.toggle('selected', checkbox.checked);
            }
        });
    });

    // Estimated signals calculation
    function updateEstimatedSignals() {
        let totalMin = 0, totalMax = 0;
        document.querySelectorAll('.strategy-checkbox:checked').forEach(cb => {
            const signalText = cb.closest('.strategy-card').querySelector('.signal-frequency small').textContent;
            const match = signalText.match(/(\d+)-(\d+)/);
            if (match) {
                totalMin += parseInt(match[1]);
                totalMax += parseInt(match[2]);
            }
        });
        // Could display this somewhere if needed
    }

    document.querySelectorAll('.strategy-checkbox').forEach(cb => {
        cb.addEventListener('change', updateEstimatedSignals);
    });
});
</script>

<?php
// Check if subscription expired for premium users
$subscriptionExpired = false;
$justSubscribed = isset($_SESSION['just_subscribed']) && $_SESSION['just_subscribed'];
if (isset($_SESSION['just_subscribed'])) unset($_SESSION['just_subscribed']);

if ($user['package'] !== 'free' && $user['package_expiry']) {
    $expiry = strtotime($user['package_expiry']);
    if ($expiry < time()) {
        $subscriptionExpired = true;
    }
}
?>

<!-- Promo Banner for FREE Users -->
<?php if ($user['package'] === 'free'): ?>
<div class="promo-banner" id="promoBanner">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5 class="mb-0">🔥 PROMO TERBATAS!</h5>
                <p class="mb-0 small">Upgrade ke VIP sekarang, dapat BONUS 1 Minggu GRATIS + Group VIP Telegram!</p>
            </div>
            <div class="col-md-4 text-md-end mt-2 mt-md-0">
                <span class="countdown-text me-2">⏰ Berakhir dalam: <span id="promoCountdown">2d 14h 30m</span></span>
                <a href="pricing.php" class="btn btn-dark btn-sm">UPGRADE SEKARANG</a>
                <button class="btn btn-sm btn-outline-dark ms-1" onclick="document.getElementById('promoBanner').style.display='none'">×</button>
            </div>
        </div>
    </div>
</div>
<style>
.promo-banner {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(135deg, #ff6b35, #f7c42d);
    color: #000;
    padding: 0.75rem;
    z-index: 1000;
    box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.3);
}
.countdown-text {
    font-family: 'Orbitron', monospace;
    font-weight: 600;
}
</style>
<script>
// Promo countdown timer (random between 1-3 days)
const promoEndTime = Date.now() + (Math.floor(Math.random() * 2) + 1) * 24 * 60 * 60 * 1000 + Math.floor(Math.random() * 12) * 60 * 60 * 1000;
function updatePromoCountdown() {
    const now = Date.now();
    const diff = promoEndTime - now;
    if (diff <= 0) {
        document.getElementById('promoCountdown').textContent = 'Expired!';
        return;
    }
    const days = Math.floor(diff / (24 * 60 * 60 * 1000));
    const hours = Math.floor((diff % (24 * 60 * 60 * 1000)) / (60 * 60 * 1000));
    const mins = Math.floor((diff % (60 * 60 * 1000)) / (60 * 1000));
    document.getElementById('promoCountdown').textContent = `${days}d ${hours}h ${mins}m`;
}
setInterval(updatePromoCountdown, 60000);
updatePromoCountdown();
</script>
<?php endif; ?>

<!-- Subscription Expired Modal -->
<?php if ($subscriptionExpired): ?>
<div class="modal fade" id="subscriptionExpiredModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white">
            <div class="modal-body text-center p-4">
                <i class="fas fa-exclamation-triangle fa-4x text-warning mb-3"></i>
                <h4>⚠️ SUBSCRIPTION EXPIRED</h4>
                <p class="text-muted">
                    Akses premium kamu sudah berakhir.<br>
                    Kamu sekarang hanya bisa pakai strategi FREE.
                </p>
                <div class="alert alert-dark border">
                    <p class="mb-0">😢 "Sayang banget kalau berhenti sekarang...<br>
                    Kemarin aja profit kamu bagus pakai strategi premium"</p>
                </div>
                <p>Perpanjang sekarang untuk lanjut profit! 💰</p>
                <a href="pricing.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-rocket"></i> PERPANJANG SEKARANG
                </a>
                <button type="button" class="btn btn-outline-secondary ms-2" data-bs-dismiss="modal">
                    Nanti Saja
                </button>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    new bootstrap.Modal(document.getElementById('subscriptionExpiredModal')).show();
});
</script>
<?php endif; ?>

<!-- Thank You Modal for Just Subscribed Users -->
<?php if ($justSubscribed && $user['package'] !== 'free'): ?>
<div class="modal fade" id="thankYouModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white">
            <div class="modal-body text-center p-4">
                <i class="fas fa-heart fa-4x text-danger mb-3"></i>
                <h4>🙏 TERIMA KASIH SUDAH BERLANGGANAN!</h4>
                <p class="text-muted">
                    Support kamu sangat berarti untuk kami.<br>
                    Dengan subscription kamu, kami bisa terus:
                </p>
                <ul class="list-unstyled text-start px-4">
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Memperbaiki sistem agar lebih stabil</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Meningkatkan akurasi strategi</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Menambah fitur-fitur baru</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Memberikan support terbaik</li>
                </ul>
                <p class="text-primary fw-bold">Selamat trading & semoga profit selalu! 🚀</p>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                    <i class="fas fa-rocket"></i> Mulai Trading
                </button>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    new bootstrap.Modal(document.getElementById('thankYouModal')).show();
});
</script>
<?php endif; ?>

<!-- Upgrade Popup Modal -->
<div class="modal fade" id="upgradeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header border-bottom border-secondary">
                <h5 class="modal-title"><i class="fas fa-crown text-warning"></i> 💎 UPGRADE KE PREMIUM</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <p class="lead">"Strategi FREE sudah bagus...<br>
                    Tapi kenapa puas dengan 60% win rate kalau bisa dapat <strong class="text-success">90%</strong>?"</p>
                </div>

                <div class="card bg-secondary mb-4">
                    <div class="card-body">
                        <h6><i class="fas fa-calculator"></i> 📊 Perhitungan Sederhana:</h6>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="p-3 bg-dark rounded">
                                    <h6 class="text-muted">FREE (60% win rate):</h6>
                                    <p class="mb-0">100 trade x $1 = <strong>~$12 profit</strong></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-success bg-opacity-25 rounded border border-success">
                                    <h6 class="text-success">VIP (90% win rate):</h6>
                                    <p class="mb-0">100 trade x $1 = <strong class="text-success">~$70 profit</strong> ⬆️ 5x LIPAT!</p>
                                </div>
                            </div>
                        </div>
                        <div class="text-center mt-3">
                            <p class="mb-0">💰 Subscription $29/bulan = <strong>BALIK MODAL dalam 1-2 hari trading!</strong></p>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="card bg-secondary h-100 text-center">
                            <div class="card-body">
                                <h6 class="text-primary">💎 PRO</h6>
                                <h4>$29<small>/bulan</small></h4>
                                <p class="small text-muted">4 Strategi<br>Win rate 73%+</p>
                                <a href="pricing.php" class="btn btn-outline-primary btn-sm w-100">Pilih</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-secondary h-100 text-center border-primary">
                            <div class="card-body">
                                <span class="badge bg-primary mb-2">🔥 POPULAR</span>
                                <h6 class="text-info">💎 ELITE</h6>
                                <h4>$79<small>/bulan</small></h4>
                                <p class="small text-muted">7 Strategi<br>Win rate 83%+</p>
                                <a href="pricing.php" class="btn btn-primary btn-sm w-100">Pilih</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-secondary h-100 text-center border-warning">
                            <div class="card-body">
                                <span class="badge bg-warning text-dark mb-2">🏆 BEST VALUE</span>
                                <h6 class="text-warning">👑 VIP</h6>
                                <h4>$149<small>/bulan</small></h4>
                                <p class="small text-muted">10 Strategi<br>Win rate 91%+</p>
                                <a href="pricing.php" class="btn btn-warning btn-sm w-100 text-dark">Pilih</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <p class="small text-muted">💬 Hubungi admin untuk pembayaran: <a href="https://t.me/<?php echo str_replace('@', '', TELEGRAM_USERNAME); ?>" class="text-primary"><?php echo TELEGRAM_USERNAME; ?></a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
