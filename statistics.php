<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/language.php';

$page_title = __('stats_title');
require_once 'dashboard/includes/dashboard-header.php';

// Get period from query
$period = isset($_GET['period']) ? (int)$_GET['period'] : 30;
$validPeriods = [7, 30, 90, 180, 365];
if (!in_array($period, $validPeriods)) {
    $period = 30;
}

// Check access based on package
$user = getUserById($_SESSION['user_id']);
$package = $user['package'] ?? 'free';

// Limit period access by package
$maxPeriod = [
    'free' => 30,
    'pro' => 90,
    'elite' => 180,
    'vip' => 365
];

if ($period > ($maxPeriod[$package] ?? 30)) {
    $period = $maxPeriod[$package] ?? 30;
}

// Get detailed statistics
$stats = getDetailedStats($_SESSION['user_id'], $period);
$performanceScore = calculatePerformanceScore($_SESSION['user_id'], $period);
$achievements = getUserAchievements($_SESSION['user_id']);
$allAchievements = getAchievements();

// Get calendar data
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$calendar = getTradingCalendar($_SESSION['user_id'], $month, $year);

// Calculate calendar month details
$firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = date('t', $firstDayOfMonth);
$startDay = date('N', $firstDayOfMonth);
$monthName = date('F', $firstDayOfMonth);

// Daily target progress (using correct keys from getRobotSettings)
$robotSettings = getRobotSettings($_SESSION['user_id']);
$todayPnl = $stats['overall']['total_pnl'] ?? 0;
$takeProfit = $robotSettings['take_profit_target'] ?? 20;
$maxLoss = $robotSettings['max_loss_limit'] ?? 10;

function getPerformanceLevelColor($level) {
    $colors = [
        'Diamond' => 'info',
        'Platinum' => 'secondary',
        'Gold' => 'warning',
        'Silver' => 'secondary',
        'Bronze' => 'warning',
        'Beginner' => 'secondary',
        'Unranked' => 'secondary'
    ];
    return $colors[$level] ?? 'secondary';
}
?>

<!-- Page Header -->
<div class="db-page-header">
    <div>
        <h1 class="db-page-title"><i class="fas fa-chart-bar"></i> <?php _e('stats_title'); ?></h1>
        <p class="db-page-subtitle"><?php _e('stats_subtitle'); ?></p>
    </div>
    <a href="dashboard.php" class="db-btn db-btn-outline">
        <i class="fas fa-arrow-left"></i> <?php _e('nav_dashboard'); ?>
    </a>
</div>

<!-- Period Filter -->
<div class="db-card mb-4 db-fade-in">
    <div class="db-card-body py-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="text-muted"><?php _e('stats_filter_period'); ?></span>
            <div class="d-flex gap-2 flex-wrap">
                <?php foreach ($validPeriods as $p): ?>
                    <?php
                    $disabled = $p > ($maxPeriod[$package] ?? 30);
                    $label = $p == 7 ? __('stats_7_days') : ($p == 30 ? __('stats_30_days') : ($p == 90 ? __('stats_3_months') : ($p == 180 ? __('stats_6_months') : __('stats_1_year'))));
                    ?>
                    <a href="?period=<?php echo $p; ?>"
                       class="db-btn db-btn-sm <?php echo $period == $p ? 'db-btn-primary' : 'db-btn-outline'; ?> <?php echo $disabled ? 'disabled' : ''; ?>"
                       <?php echo $disabled ? 'onclick="return false;" style="opacity: 0.5;"' : ''; ?>>
                        <?php echo $label; ?>
                        <?php if ($disabled): ?>
                            <i class="fas fa-lock ms-1"></i>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column - Main Stats -->
    <div class="col-lg-8">
        <!-- Overview Cards -->
        <div class="db-stat-grid mb-4 db-fade-in">
            <div class="db-stat-card">
                <div class="db-stat-icon"><i class="fas fa-chart-bar"></i></div>
                <div class="db-stat-value"><?php echo $stats['overall']['total_trades'] ?? 0; ?></div>
                <div class="db-stat-label"><?php _e('stats_total_trades'); ?></div>
            </div>
            <div class="db-stat-card success">
                <div class="db-stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="db-stat-value"><?php echo $stats['overall']['wins'] ?? 0; ?></div>
                <div class="db-stat-label"><?php _e('stats_wins'); ?></div>
            </div>
            <div class="db-stat-card danger">
                <div class="db-stat-icon"><i class="fas fa-times-circle"></i></div>
                <div class="db-stat-value"><?php echo $stats['overall']['losses'] ?? 0; ?></div>
                <div class="db-stat-label"><?php _e('stats_losses'); ?></div>
            </div>
            <div class="db-stat-card">
                <div class="db-stat-icon"><i class="fas fa-percentage"></i></div>
                <div class="db-stat-value <?php echo ($stats['overall']['win_rate'] ?? 0) >= 70 ? 'text-success' : ''; ?>">
                    <?php echo number_format($stats['overall']['win_rate'] ?? 0, 1); ?>%
                </div>
                <div class="db-stat-label"><?php _e('stats_win_rate'); ?></div>
            </div>
        </div>

        <!-- P&L Card -->
        <div class="db-card mb-4 db-fade-in">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="fas fa-dollar-sign"></i> <?php _e('stats_pnl'); ?></h5>
            </div>
            <div class="db-card-body">
                <div class="row text-center">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="p-3 rounded" style="background: rgba(var(--db-primary-rgb), 0.05);">
                            <small class="text-muted d-block mb-1"><?php _e('stats_total_pnl'); ?></small>
                            <h3 class="mb-0 <?php echo ($stats['overall']['total_pnl'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?>">
                                <?php echo ($stats['overall']['total_pnl'] ?? 0) >= 0 ? '+' : ''; ?>$<?php echo number_format(abs($stats['overall']['total_pnl'] ?? 0), 2); ?>
                            </h3>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="p-3 rounded" style="background: rgba(var(--db-success-rgb), 0.05);">
                            <small class="text-muted d-block mb-1"><?php _e('stats_best_trade'); ?></small>
                            <h3 class="mb-0 text-success">+$<?php echo number_format($stats['overall']['best_trade'] ?? 0, 2); ?></h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background: rgba(var(--db-danger-rgb), 0.05);">
                            <small class="text-muted d-block mb-1"><?php _e('stats_worst_trade'); ?></small>
                            <h3 class="mb-0 text-danger">$<?php echo number_format($stats['overall']['worst_trade'] ?? 0, 2); ?></h3>
                        </div>
                    </div>
                </div>
                <hr style="border-color: var(--db-border);">
                <div class="row text-center">
                    <div class="col-4">
                        <small class="text-muted d-block"><?php _e('stats_total_profit'); ?></small>
                        <span class="text-success fw-bold">+$<?php echo number_format($stats['overall']['total_profit'] ?? 0, 2); ?></span>
                    </div>
                    <div class="col-4">
                        <small class="text-muted d-block"><?php _e('stats_total_loss'); ?></small>
                        <span class="text-danger fw-bold">-$<?php echo number_format($stats['overall']['total_loss'] ?? 0, 2); ?></span>
                    </div>
                    <div class="col-4">
                        <small class="text-muted d-block"><?php _e('stats_avg_trade'); ?></small>
                        <span class="fw-bold <?php echo ($stats['overall']['avg_trade'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?>">
                            $<?php echo number_format($stats['overall']['avg_trade'] ?? 0, 2); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Strategy Breakdown -->
        <div class="db-card mb-4 db-fade-in">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="fas fa-chess"></i> <?php _e('stats_per_strategy'); ?></h5>
            </div>
            <div class="db-card-body" style="padding: 0;">
                <?php if (empty($stats['by_strategy'])): ?>
                <div class="text-center py-5">
                    <i class="fas fa-chart-pie fa-3x text-muted mb-3" style="opacity: 0.3;"></i>
                    <p class="text-muted"><?php _e('stats_no_data'); ?></p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="db-table">
                        <thead>
                            <tr>
                                <th><?php _e('stats_strategy'); ?></th>
                                <th class="text-center"><?php _e('stats_trades'); ?></th>
                                <th class="text-center"><?php _e('stats_win_rate'); ?></th>
                                <th class="text-end">P&L</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['by_strategy'] as $strategy): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($strategy['strategy'] ?? 'Unknown'); ?></strong></td>
                                <td class="text-center"><?php echo $strategy['trades']; ?></td>
                                <td class="text-center">
                                    <span class="db-badge <?php echo ($strategy['win_rate'] ?? 0) >= 70 ? 'success' : (($strategy['win_rate'] ?? 0) >= 55 ? 'warning' : 'danger'); ?>">
                                        <?php echo number_format($strategy['win_rate'] ?? 0, 1); ?>%
                                    </span>
                                </td>
                                <td class="text-end <?php echo ($strategy['pnl'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?> fw-bold">
                                    <?php echo ($strategy['pnl'] ?? 0) >= 0 ? '+' : ''; ?>$<?php echo number_format($strategy['pnl'] ?? 0, 2); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Trading Calendar -->
        <div class="db-card mb-4 db-fade-in">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="fas fa-calendar-alt"></i> <?php _e('stats_calendar'); ?></h5>
                <div class="d-flex gap-1">
                    <?php
                    $prevMonth = $month - 1;
                    $prevYear = $year;
                    if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
                    $nextMonth = $month + 1;
                    $nextYear = $year;
                    if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }
                    ?>
                    <a href="?period=<?php echo $period; ?>&month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>" class="db-btn db-btn-sm db-btn-outline">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <span class="db-btn db-btn-sm" style="pointer-events: none;"><?php echo $monthName . ' ' . $year; ?></span>
                    <a href="?period=<?php echo $period; ?>&month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>" class="db-btn db-btn-sm db-btn-outline">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
            </div>
            <div class="db-card-body">
                <div class="trading-calendar">
                    <div class="calendar-header">
                        <div><?php _e('stats_day_mon'); ?></div><div><?php _e('stats_day_tue'); ?></div><div><?php _e('stats_day_wed'); ?></div><div><?php _e('stats_day_thu'); ?></div><div><?php _e('stats_day_fri'); ?></div><div><?php _e('stats_day_sat'); ?></div><div><?php _e('stats_day_sun'); ?></div>
                    </div>
                    <div class="calendar-grid">
                        <?php
                        for ($i = 1; $i < $startDay; $i++) {
                            echo '<div class="calendar-day empty"></div>';
                        }

                        for ($day = 1; $day <= $daysInMonth; $day++) {
                            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                            $dayData = $calendar[$date] ?? null;
                            $dayClass = 'no-trade';
                            $pnlText = '';

                            if ($dayData) {
                                $dayClass = $dayData['status'];
                                $pnlText = ($dayData['pnl'] >= 0 ? '+' : '') . '$' . number_format($dayData['pnl'], 0);
                            }

                            $dayOfWeek = date('N', strtotime($date));
                            if ($dayOfWeek >= 6) {
                                $dayClass = 'weekend';
                            }
                        ?>
                        <div class="calendar-day <?php echo $dayClass; ?>"
                             title="<?php echo $dayData ? "Trades: {$dayData['trades']}, W: {$dayData['wins']}, L: {$dayData['losses']}" : 'No trades'; ?>">
                            <span class="day-number"><?php echo $day; ?></span>
                            <?php if ($dayData): ?>
                            <span class="day-pnl"><?php echo $pnlText; ?></span>
                            <?php endif; ?>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-3 flex-wrap">
                    <span class="d-flex align-items-center gap-2"><span class="calendar-legend profit"></span> <?php _e('stats_legend_profit'); ?></span>
                    <span class="d-flex align-items-center gap-2"><span class="calendar-legend loss"></span> <?php _e('stats_legend_loss'); ?></span>
                    <span class="d-flex align-items-center gap-2"><span class="calendar-legend no-trade"></span> <?php _e('stats_legend_no_trade'); ?></span>
                    <span class="d-flex align-items-center gap-2"><span class="calendar-legend weekend"></span> <?php _e('stats_legend_weekend'); ?></span>
                </div>
            </div>
        </div>

        <!-- Market & Timeframe Breakdown -->
        <div class="row g-4">
            <div class="col-md-6">
                <div class="db-card db-fade-in">
                    <div class="db-card-header">
                        <h5 class="db-card-title"><i class="fas fa-globe"></i> <?php _e('stats_per_market'); ?></h5>
                    </div>
                    <div class="db-card-body" style="padding: 0;">
                        <?php if (empty($stats['by_market'])): ?>
                        <div class="text-center py-4"><small class="text-muted"><?php _e('stats_no_data'); ?></small></div>
                        <?php else: ?>
                        <?php foreach ($stats['by_market'] as $market): ?>
                        <div class="d-flex justify-content-between p-3 border-bottom" style="border-color: var(--db-border) !important;">
                            <span><?php echo htmlspecialchars($market['market']); ?></span>
                            <span class="fw-bold <?php echo ($market['pnl'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?>">
                                <?php echo ($market['pnl'] ?? 0) >= 0 ? '+' : ''; ?>$<?php echo number_format($market['pnl'] ?? 0, 2); ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="db-card db-fade-in">
                    <div class="db-card-header">
                        <h5 class="db-card-title"><i class="fas fa-clock"></i> <?php _e('stats_per_timeframe'); ?></h5>
                    </div>
                    <div class="db-card-body" style="padding: 0;">
                        <?php if (empty($stats['by_timeframe'])): ?>
                        <div class="text-center py-4"><small class="text-muted"><?php _e('stats_no_data'); ?></small></div>
                        <?php else: ?>
                        <?php foreach ($stats['by_timeframe'] as $tf): ?>
                        <div class="d-flex justify-content-between p-3 border-bottom" style="border-color: var(--db-border) !important;">
                            <span><?php echo htmlspecialchars($tf['timeframe']); ?></span>
                            <span class="fw-bold <?php echo ($tf['pnl'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?>">
                                <?php echo ($tf['pnl'] ?? 0) >= 0 ? '+' : ''; ?>$<?php echo number_format($tf['pnl'] ?? 0, 2); ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column - Performance & Achievements -->
    <div class="col-lg-4">
        <!-- Performance Score -->
        <div class="db-card mb-4 db-fade-in">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="fas fa-star"></i> <?php _e('stats_perf_score'); ?></h5>
            </div>
            <div class="db-card-body text-center">
                <div class="db-perf-circle mb-3">
                    <svg viewBox="0 0 140 140">
                        <circle class="bg" cx="70" cy="70" r="60"/>
                        <circle class="progress" cx="70" cy="70" r="60"
                                stroke-dasharray="<?php echo ($performanceScore['score'] / 100) * 377; ?> 377"/>
                    </svg>
                    <div class="db-perf-text">
                        <span class="db-perf-score"><?php echo $performanceScore['score']; ?></span>
                        <span class="db-perf-max">/100</span>
                    </div>
                </div>
                <span class="db-badge <?php echo getPerformanceLevelColor($performanceScore['level']); ?>" style="font-size: 0.9rem; padding: 0.5rem 1rem;">
                    <?php echo $performanceScore['level']; ?>
                </span>

                <?php if (!empty($performanceScore['breakdown'])): ?>
                <div class="mt-4 text-start">
                    <?php
                    $breakdownItems = [
                        'win_rate' => ['label' => __('stats_win_rate'), 'max' => 35, 'color' => 'success'],
                        'consistency' => ['label' => __('stats_consistency'), 'max' => 25, 'color' => 'info'],
                        'profit_factor' => ['label' => __('stats_profit_factor'), 'max' => 20, 'color' => 'warning'],
                        'discipline' => ['label' => __('stats_discipline'), 'max' => 10, 'color' => 'primary'],
                        'streak_bonus' => ['label' => __('stats_streak_bonus'), 'max' => 10, 'color' => 'danger']
                    ];
                    foreach ($breakdownItems as $key => $item):
                        $value = $performanceScore['breakdown'][$key] ?? 0;
                        $percent = ($value / $item['max']) * 100;
                    ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small><?php echo $item['label']; ?></small>
                            <small><?php echo $value; ?>/<?php echo $item['max']; ?></small>
                        </div>
                        <div class="db-progress-bar" style="height: 6px;">
                            <div class="db-progress-fill <?php echo $item['color']; ?>" style="width: <?php echo $percent; ?>%;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Streaks -->
        <div class="db-card mb-4 db-fade-in">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="fas fa-fire"></i> <?php _e('stats_streaks'); ?></h5>
            </div>
            <div class="db-card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="streak-value <?php echo ($stats['streaks']['current'] ?? 0) > 0 ? 'text-success' : (($stats['streaks']['current'] ?? 0) < 0 ? 'text-danger' : ''); ?>">
                            <?php echo abs($stats['streaks']['current'] ?? 0); ?>
                        </div>
                        <small class="text-muted"><?php _e('stats_current'); ?></small>
                        <div class="small text-muted"><?php echo ($stats['streaks']['current'] ?? 0) > 0 ? 'Win' : (($stats['streaks']['current'] ?? 0) < 0 ? 'Loss' : '-'); ?></div>
                    </div>
                    <div class="col-4">
                        <div class="streak-value text-success"><?php echo $stats['streaks']['max_win'] ?? 0; ?></div>
                        <small class="text-muted"><?php _e('stats_best_win'); ?></small>
                        <div class="small text-muted">Streak</div>
                    </div>
                    <div class="col-4">
                        <div class="streak-value text-danger"><?php echo $stats['streaks']['max_loss'] ?? 0; ?></div>
                        <small class="text-muted"><?php _e('stats_worst_loss'); ?></small>
                        <div class="small text-muted">Streak</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Achievements -->
        <div class="db-card db-fade-in">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="fas fa-trophy"></i> <?php _e('stats_achievements'); ?></h5>
            </div>
            <div class="db-card-body">
                <div class="achievements-grid">
                    <?php foreach ($allAchievements as $id => $achievement): ?>
                    <?php $earned = isset($achievements[$id]); ?>
                    <div class="achievement-item <?php echo $earned ? 'earned' : 'locked'; ?>"
                         title="<?php echo $achievement['name']; ?>: <?php echo $achievement['description']; ?>">
                        <div class="achievement-icon" style="background: <?php echo $earned ? 'var(--db-' . $achievement['color'] . ')' : 'var(--db-surface-light)'; ?>;">
                            <i class="fas <?php echo $achievement['icon']; ?>"></i>
                        </div>
                        <small><?php echo $achievement['name']; ?></small>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-3 text-center">
                    <small class="text-muted"><?php echo count($achievements); ?>/<?php echo count($allAchievements); ?> <?php _e('stats_unlocked'); ?></small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Trading Calendar */
.trading-calendar {
    background: var(--db-surface-light);
    border-radius: 12px;
    padding: 1rem;
}
.calendar-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    text-align: center;
    padding-bottom: 0.75rem;
    font-weight: 600;
    color: var(--db-text-muted);
    font-size: 0.8rem;
}
.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 4px;
}
.calendar-day {
    aspect-ratio: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    padding: 4px;
    font-size: 0.85rem;
    transition: var(--db-transition);
}
.calendar-day:not(.empty):hover {
    transform: scale(1.1);
    z-index: 1;
}
.calendar-day.profit {
    background: rgba(var(--db-success-rgb), 0.2);
    border: 1px solid rgba(var(--db-success-rgb), 0.4);
}
.calendar-day.loss {
    background: rgba(var(--db-danger-rgb), 0.2);
    border: 1px solid rgba(var(--db-danger-rgb), 0.4);
}
.calendar-day.no-trade {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--db-border);
}
.calendar-day.weekend {
    background: rgba(148, 163, 184, 0.1);
    opacity: 0.5;
}
.calendar-day.empty {
    background: transparent;
    border: none;
}
.day-number {
    font-weight: 600;
}
.day-pnl {
    font-size: 0.6rem;
    margin-top: 2px;
}
.calendar-day.profit .day-pnl { color: var(--db-success); }
.calendar-day.loss .day-pnl { color: var(--db-danger); }

.calendar-legend {
    width: 16px;
    height: 16px;
    border-radius: 4px;
}
.calendar-legend.profit { background: rgba(var(--db-success-rgb), 0.3); }
.calendar-legend.loss { background: rgba(var(--db-danger-rgb), 0.3); }
.calendar-legend.no-trade { background: rgba(255, 255, 255, 0.05); }
.calendar-legend.weekend { background: rgba(148, 163, 184, 0.2); }

/* Streaks */
.streak-value {
    font-size: 1.75rem;
    font-weight: 700;
}

/* Achievements */
.achievements-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
}
.achievement-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 8px;
    border-radius: 8px;
    transition: var(--db-transition);
}
.achievement-item.locked {
    opacity: 0.35;
}
.achievement-item.earned {
    background: rgba(var(--db-primary-rgb), 0.1);
}
.achievement-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 6px;
}
.achievement-icon i {
    color: white;
    font-size: 0.9rem;
}
.achievement-item small {
    font-size: 0.65rem;
    line-height: 1.2;
    color: var(--db-text-muted);
}

/* Progress bar colors */
.db-progress-fill.info { background: var(--db-info); }
.db-progress-fill.primary { background: var(--db-primary); }
</style>

<?php require_once 'dashboard/includes/dashboard-footer.php'; ?>
