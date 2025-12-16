<?php
$page_title = 'Statistics';
require_once 'includes/header.php';

// Require login
if (!isLoggedIn()) {
    redirect('login.php');
}

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
$startDay = date('N', $firstDayOfMonth); // 1 = Monday
$monthName = date('F', $firstDayOfMonth);
?>

<section class="statistics-page">
    <div class="container py-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title"><i class="fas fa-chart-bar text-info"></i> Statistics</h1>
                <p class="text-muted mb-0">Analisis detail performa trading Anda</p>
            </div>
            <div>
                <a href="dashboard.php" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left"></i> Dashboard
                </a>
            </div>
        </div>

        <!-- Period Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="text-muted">Filter Periode:</span>
                    <div class="btn-group">
                        <?php foreach ($validPeriods as $p): ?>
                            <?php
                            $disabled = $p > ($maxPeriod[$package] ?? 30);
                            $label = $p == 7 ? '7 Hari' : ($p == 30 ? '30 Hari' : ($p == 90 ? '3 Bulan' : ($p == 180 ? '6 Bulan' : '1 Tahun')));
                            ?>
                            <a href="?period=<?php echo $p; ?>"
                               class="btn btn-sm <?php echo $period == $p ? 'btn-primary' : 'btn-outline-primary'; ?> <?php echo $disabled ? 'disabled' : ''; ?>"
                               <?php echo $disabled ? 'title="Upgrade untuk akses"' : ''; ?>>
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

        <div class="row">
            <!-- Left Column - Main Stats -->
            <div class="col-lg-8">
                <!-- Overview Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3 col-6">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-chart-bar fa-2x text-info mb-2"></i>
                                <h4 class="mb-0"><?php echo $stats['overall']['total_trades'] ?? 0; ?></h4>
                                <small class="text-muted">Total Trades</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                <h4 class="mb-0 text-success"><?php echo $stats['overall']['wins'] ?? 0; ?></h4>
                                <small class="text-muted">Wins</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                                <h4 class="mb-0 text-danger"><?php echo $stats['overall']['losses'] ?? 0; ?></h4>
                                <small class="text-muted">Losses</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-percentage fa-2x text-primary mb-2"></i>
                                <h4 class="mb-0 <?php echo ($stats['overall']['win_rate'] ?? 0) >= 70 ? 'text-success' : ''; ?>">
                                    <?php echo number_format($stats['overall']['win_rate'] ?? 0, 1); ?>%
                                </h4>
                                <small class="text-muted">Win Rate</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- P&L Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-dollar-sign"></i> Profit & Loss</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 text-center border-end">
                                <h6 class="text-muted">Total P&L</h6>
                                <h3 class="<?php echo ($stats['overall']['total_pnl'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo ($stats['overall']['total_pnl'] ?? 0) >= 0 ? '+' : ''; ?>$<?php echo number_format(abs($stats['overall']['total_pnl'] ?? 0), 2); ?>
                                </h3>
                                <?php
                                // Fun message based on profit amount
                                $totalPnl = $stats['overall']['total_pnl'] ?? 0;
                                $rupiahValue = $totalPnl * 15500; // Approx USD to IDR
                                if ($totalPnl > 0):
                                    $funMessage = '';
                                    if ($rupiahValue >= 500000) {
                                        $funMessage = "Wow! Bisa bayar tagihan bulanan! 🎉";
                                    } elseif ($rupiahValue >= 150000) {
                                        $funMessage = "Lumayan buat makan enak! 🍜";
                                    } elseif ($rupiahValue >= 50000) {
                                        $funMessage = "Lumayan buat ngopi! ☕";
                                    } elseif ($rupiahValue >= 20000) {
                                        $funMessage = "Lumayan buat jajan! 🍿";
                                    } else {
                                        $funMessage = "Profit tetap profit! 💪";
                                    }
                                ?>
                                <p class="small text-muted mb-0 mt-1">
                                    💡 = Rp <?php echo number_format($rupiahValue, 0, ',', '.'); ?>+<br>
                                    <span class="text-success"><?php echo $funMessage; ?></span>
                                </p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4 text-center border-end">
                                <h6 class="text-muted">Best Trade</h6>
                                <h3 class="text-success">+$<?php echo number_format($stats['overall']['best_trade'] ?? 0, 2); ?></h3>
                            </div>
                            <div class="col-md-4 text-center">
                                <h6 class="text-muted">Worst Trade</h6>
                                <h3 class="text-danger">$<?php echo number_format($stats['overall']['worst_trade'] ?? 0, 2); ?></h3>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <small class="text-muted">Total Profit</small>
                                <p class="text-success mb-0">+$<?php echo number_format($stats['overall']['total_profit'] ?? 0, 2); ?></p>
                            </div>
                            <div class="col-md-4 text-center">
                                <small class="text-muted">Total Loss</small>
                                <p class="text-danger mb-0">-$<?php echo number_format($stats['overall']['total_loss'] ?? 0, 2); ?></p>
                            </div>
                            <div class="col-md-4 text-center">
                                <small class="text-muted">Avg per Trade</small>
                                <p class="mb-0 <?php echo ($stats['overall']['avg_trade'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?>">
                                    $<?php echo number_format($stats['overall']['avg_trade'] ?? 0, 2); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Daily Target Progress -->
                <?php
                $dailyTarget = getDailyTargetSettings($_SESSION['user_id']);
                $todayPnl = $stats['overall']['today_pnl'] ?? $stats['overall']['total_pnl'] ?? 0;
                $takeProfit = $dailyTarget['amount'] ?? 20;
                $maxLoss = $dailyTarget['max_loss'] ?? 10;
                $tpProgress = $takeProfit > 0 ? min(100, ($todayPnl / $takeProfit) * 100) : 0;
                $mlProgress = $maxLoss > 0 && $todayPnl < 0 ? min(100, (abs($todayPnl) / $maxLoss) * 100) : 0;
                ?>
                <div class="card mb-4 border-info">
                    <div class="card-header bg-info bg-opacity-10">
                        <h5 class="mb-0"><i class="fas fa-bullseye"></i> 🎯 Progress Target Hari Ini</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Take Profit ($<?php echo number_format($takeProfit, 0); ?>)</span>
                                <span class="<?php echo $todayPnl >= $takeProfit ? 'text-success fw-bold' : ''; ?>">
                                    $<?php echo number_format(max(0, $todayPnl), 2); ?> / $<?php echo number_format($takeProfit, 0); ?>
                                    <?php if ($todayPnl >= $takeProfit): ?> 🎉 TERCAPAI!<?php endif; ?>
                                </span>
                            </div>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-success progress-bar-striped <?php echo $todayPnl > 0 ? 'progress-bar-animated' : ''; ?>"
                                     role="progressbar"
                                     style="width: <?php echo max(0, $tpProgress); ?>%"
                                     aria-valuenow="<?php echo $tpProgress; ?>"
                                     aria-valuemin="0"
                                     aria-valuemax="100">
                                    <?php echo number_format(max(0, $tpProgress), 0); ?>%
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Max Loss ($<?php echo number_format($maxLoss, 0); ?>)</span>
                                <span class="<?php echo abs($todayPnl) >= $maxLoss && $todayPnl < 0 ? 'text-danger fw-bold' : ''; ?>">
                                    <?php if ($todayPnl < 0): ?>
                                        $<?php echo number_format(abs($todayPnl), 2); ?> / $<?php echo number_format($maxLoss, 0); ?>
                                        <?php if (abs($todayPnl) >= $maxLoss): ?> ⚠️ LIMIT!<?php endif; ?>
                                    <?php else: ?>
                                        ✅ AMAN
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-danger <?php echo $todayPnl < 0 ? 'progress-bar-striped' : ''; ?>"
                                     role="progressbar"
                                     style="width: <?php echo $mlProgress; ?>%"
                                     aria-valuenow="<?php echo $mlProgress; ?>"
                                     aria-valuemin="0"
                                     aria-valuemax="100">
                                    <?php echo $todayPnl < 0 ? number_format($mlProgress, 0) . '%' : ''; ?>
                                </div>
                            </div>
                        </div>

                        <?php if ($todayPnl > 0 && $todayPnl < $takeProfit): ?>
                        <div class="alert alert-success mt-3 mb-0 py-2">
                            <i class="fas fa-chart-line"></i>
                            Kamu sudah profit <strong>$<?php echo number_format($todayPnl, 2); ?></strong> hari ini!
                            Tinggal <strong>$<?php echo number_format($takeProfit - $todayPnl, 2); ?></strong> lagi untuk mencapai target! 💪
                        </div>
                        <?php elseif ($todayPnl >= $takeProfit): ?>
                        <div class="alert alert-success mt-3 mb-0 py-2">
                            <i class="fas fa-trophy"></i>
                            🎉 <strong>SELAMAT!</strong> Target Take Profit tercapai! Robot akan auto-pause untuk mengamankan profit.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Strategy Breakdown -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chess"></i> Performa per Strategi</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($stats['by_strategy'])): ?>
                        <div class="text-center py-4">
                            <p class="text-muted mb-0">Belum ada data trading</p>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Strategi</th>
                                        <th class="text-center">Trades</th>
                                        <th class="text-center">Win Rate</th>
                                        <th class="text-end">P&L</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stats['by_strategy'] as $strategy): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($strategy['strategy'] ?? 'Unknown'); ?></strong>
                                        </td>
                                        <td class="text-center"><?php echo $strategy['trades']; ?></td>
                                        <td class="text-center">
                                            <span class="badge <?php echo getWinRateBadgeClass($strategy['win_rate'] ?? 0); ?>">
                                                <?php echo number_format($strategy['win_rate'] ?? 0, 1); ?>%
                                            </span>
                                        </td>
                                        <td class="text-end <?php echo ($strategy['pnl'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?>">
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
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Trading Calendar</h5>
                        <div class="btn-group btn-group-sm">
                            <?php
                            $prevMonth = $month - 1;
                            $prevYear = $year;
                            if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
                            $nextMonth = $month + 1;
                            $nextYear = $year;
                            if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }
                            ?>
                            <a href="?period=<?php echo $period; ?>&month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>" class="btn btn-outline-light">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                            <span class="btn btn-outline-light disabled"><?php echo $monthName . ' ' . $year; ?></span>
                            <a href="?period=<?php echo $period; ?>&month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>" class="btn btn-outline-light">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="trading-calendar">
                            <div class="calendar-header">
                                <div>Sen</div>
                                <div>Sel</div>
                                <div>Rab</div>
                                <div>Kam</div>
                                <div>Jum</div>
                                <div>Sab</div>
                                <div>Min</div>
                            </div>
                            <div class="calendar-grid">
                                <?php
                                // Empty cells before first day
                                for ($i = 1; $i < $startDay; $i++) {
                                    echo '<div class="calendar-day empty"></div>';
                                }

                                // Days of month
                                for ($day = 1; $day <= $daysInMonth; $day++) {
                                    $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                    $dayData = $calendar[$date] ?? null;
                                    $dayClass = 'no-trade';
                                    $pnlText = '';

                                    if ($dayData) {
                                        $dayClass = $dayData['status'];
                                        $pnlText = ($dayData['pnl'] >= 0 ? '+' : '') . '$' . number_format($dayData['pnl'], 0);
                                    }

                                    // Weekend check
                                    $dayOfWeek = date('N', strtotime($date));
                                    if ($dayOfWeek >= 6) {
                                        $dayClass = 'weekend';
                                    }
                                ?>
                                <div class="calendar-day <?php echo $dayClass; ?>" data-date="<?php echo $date; ?>"
                                     title="<?php echo $dayData ? "Trades: {$dayData['trades']}, W: {$dayData['wins']}, L: {$dayData['losses']}" : 'No trades'; ?>">
                                    <span class="day-number"><?php echo $day; ?></span>
                                    <?php if ($dayData): ?>
                                    <span class="day-pnl"><?php echo $pnlText; ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">
                                <span class="badge bg-success me-2">Profit</span>
                                <span class="badge bg-danger me-2">Loss</span>
                                <span class="badge bg-secondary me-2">No Trade</span>
                                <span class="badge bg-dark">Weekend</span>
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Market & Timeframe Breakdown -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-globe"></i> Per Market</h6>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($stats['by_market'])): ?>
                                <div class="text-center py-3">
                                    <small class="text-muted">No data</small>
                                </div>
                                <?php else: ?>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($stats['by_market'] as $market): ?>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span><?php echo htmlspecialchars($market['market']); ?></span>
                                        <span class="<?php echo ($market['pnl'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo ($market['pnl'] ?? 0) >= 0 ? '+' : ''; ?>$<?php echo number_format($market['pnl'] ?? 0, 2); ?>
                                        </span>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-clock"></i> Per Timeframe</h6>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($stats['by_timeframe'])): ?>
                                <div class="text-center py-3">
                                    <small class="text-muted">No data</small>
                                </div>
                                <?php else: ?>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($stats['by_timeframe'] as $tf): ?>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span><?php echo htmlspecialchars($tf['timeframe']); ?></span>
                                        <span class="<?php echo ($tf['pnl'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo ($tf['pnl'] ?? 0) >= 0 ? '+' : ''; ?>$<?php echo number_format($tf['pnl'] ?? 0, 2); ?>
                                        </span>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Performance & Achievements -->
            <div class="col-lg-4">
                <!-- Performance Score -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-star"></i> Performance Score</h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="performance-score-circle mb-3">
                            <svg viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="45" fill="none" stroke="#333" stroke-width="8"/>
                                <circle cx="50" cy="50" r="45" fill="none" stroke="var(--primary-color)" stroke-width="8"
                                        stroke-dasharray="<?php echo ($performanceScore['score'] / 100) * 283; ?> 283"
                                        stroke-linecap="round" transform="rotate(-90 50 50)"/>
                            </svg>
                            <div class="score-text">
                                <span class="score-number"><?php echo $performanceScore['score']; ?></span>
                                <span class="score-label">/100</span>
                            </div>
                        </div>
                        <h4 class="mb-3">
                            <span class="badge bg-<?php echo getPerformanceLevelColor($performanceScore['level']); ?>">
                                <?php echo $performanceScore['level']; ?>
                            </span>
                        </h4>

                        <?php if (!empty($performanceScore['breakdown'])): ?>
                        <div class="score-breakdown text-start">
                            <div class="d-flex justify-content-between mb-2">
                                <small>Win Rate</small>
                                <small><?php echo $performanceScore['breakdown']['win_rate']; ?>/35</small>
                            </div>
                            <div class="progress mb-3" style="height: 6px;">
                                <div class="progress-bar bg-success" style="width: <?php echo ($performanceScore['breakdown']['win_rate'] / 35) * 100; ?>%"></div>
                            </div>

                            <div class="d-flex justify-content-between mb-2">
                                <small>Konsistensi</small>
                                <small><?php echo $performanceScore['breakdown']['consistency']; ?>/25</small>
                            </div>
                            <div class="progress mb-3" style="height: 6px;">
                                <div class="progress-bar bg-info" style="width: <?php echo ($performanceScore['breakdown']['consistency'] / 25) * 100; ?>%"></div>
                            </div>

                            <div class="d-flex justify-content-between mb-2">
                                <small>Profit Factor</small>
                                <small><?php echo $performanceScore['breakdown']['profit_factor']; ?>/20</small>
                            </div>
                            <div class="progress mb-3" style="height: 6px;">
                                <div class="progress-bar bg-warning" style="width: <?php echo ($performanceScore['breakdown']['profit_factor'] / 20) * 100; ?>%"></div>
                            </div>

                            <div class="d-flex justify-content-between mb-2">
                                <small>Disiplin</small>
                                <small><?php echo $performanceScore['breakdown']['discipline']; ?>/10</small>
                            </div>
                            <div class="progress mb-3" style="height: 6px;">
                                <div class="progress-bar bg-primary" style="width: <?php echo ($performanceScore['breakdown']['discipline'] / 10) * 100; ?>%"></div>
                            </div>

                            <div class="d-flex justify-content-between mb-2">
                                <small>Streak Bonus</small>
                                <small><?php echo $performanceScore['breakdown']['streak_bonus']; ?>/10</small>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-danger" style="width: <?php echo ($performanceScore['breakdown']['streak_bonus'] / 10) * 100; ?>%"></div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Streaks -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-fire"></i> Streaks</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="streak-item">
                                    <span class="streak-value <?php echo ($stats['streaks']['current'] ?? 0) > 0 ? 'text-success' : (($stats['streaks']['current'] ?? 0) < 0 ? 'text-danger' : ''); ?>">
                                        <?php echo abs($stats['streaks']['current'] ?? 0); ?>
                                    </span>
                                    <span class="streak-label">Current</span>
                                    <small class="text-muted"><?php echo ($stats['streaks']['current'] ?? 0) > 0 ? 'Win' : (($stats['streaks']['current'] ?? 0) < 0 ? 'Loss' : '-'); ?></small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="streak-item">
                                    <span class="streak-value text-success"><?php echo $stats['streaks']['max_win'] ?? 0; ?></span>
                                    <span class="streak-label">Best Win</span>
                                    <small class="text-muted">Streak</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="streak-item">
                                    <span class="streak-value text-danger"><?php echo $stats['streaks']['max_loss'] ?? 0; ?></span>
                                    <span class="streak-label">Worst Loss</span>
                                    <small class="text-muted">Streak</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Achievements -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-trophy"></i> Achievements</h5>
                    </div>
                    <div class="card-body">
                        <div class="achievements-grid">
                            <?php foreach ($allAchievements as $id => $achievement): ?>
                                <?php $earned = isset($achievements[$id]); ?>
                                <div class="achievement-item <?php echo $earned ? 'earned' : 'locked'; ?>"
                                     title="<?php echo $achievement['name']; ?>: <?php echo $achievement['description']; ?>">
                                    <div class="achievement-icon bg-<?php echo $earned ? $achievement['color'] : 'secondary'; ?>">
                                        <i class="fas <?php echo $achievement['icon']; ?>"></i>
                                    </div>
                                    <small><?php echo $achievement['name']; ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-3 text-center">
                            <small class="text-muted">
                                <?php echo count($achievements); ?>/<?php echo count($allAchievements); ?> unlocked
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.trading-calendar {
    background: var(--card-bg);
    border-radius: 8px;
}
.calendar-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    text-align: center;
    padding: 10px 0;
    font-weight: bold;
    color: var(--text-muted);
    font-size: 0.85rem;
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
    cursor: default;
    transition: all 0.2s;
}
.calendar-day:not(.empty):hover {
    transform: scale(1.1);
    z-index: 1;
}
.calendar-day.profit {
    background: rgba(34, 197, 94, 0.2);
    border: 1px solid rgba(34, 197, 94, 0.5);
}
.calendar-day.loss {
    background: rgba(239, 68, 68, 0.2);
    border: 1px solid rgba(239, 68, 68, 0.5);
}
.calendar-day.no-trade {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
}
.calendar-day.weekend {
    background: rgba(107, 114, 128, 0.1);
    border: 1px solid var(--border-color);
    opacity: 0.6;
}
.calendar-day.empty {
    background: transparent;
    border: none;
}
.day-number {
    font-weight: bold;
    font-size: 0.9rem;
}
.day-pnl {
    font-size: 0.65rem;
    white-space: nowrap;
}
.calendar-day.profit .day-pnl { color: #22c55e; }
.calendar-day.loss .day-pnl { color: #ef4444; }

/* Performance Score Circle */
.performance-score-circle {
    position: relative;
    width: 150px;
    height: 150px;
    margin: 0 auto;
}
.performance-score-circle svg {
    width: 100%;
    height: 100%;
}
.score-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
}
.score-number {
    font-size: 2.5rem;
    font-weight: bold;
    color: var(--primary-color);
}
.score-label {
    font-size: 1rem;
    color: var(--text-muted);
}

/* Streaks */
.streak-item {
    display: flex;
    flex-direction: column;
}
.streak-value {
    font-size: 1.8rem;
    font-weight: bold;
}
.streak-label {
    font-size: 0.8rem;
    color: var(--text-muted);
}

/* Achievements Grid */
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
    transition: all 0.2s;
}
.achievement-item.locked {
    opacity: 0.4;
}
.achievement-item.earned {
    background: rgba(var(--primary-rgb), 0.1);
}
.achievement-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 4px;
}
.achievement-icon i {
    color: white;
}
.achievement-item small {
    font-size: 0.65rem;
    line-height: 1.2;
}
</style>

<?php
function getPerformanceLevelColor($level) {
    $colors = [
        'Diamond' => 'info',
        'Platinum' => 'light',
        'Gold' => 'warning',
        'Silver' => 'secondary',
        'Bronze' => 'warning',
        'Beginner' => 'dark',
        'Unranked' => 'secondary'
    ];
    return $colors[$level] ?? 'secondary';
}
?>

<?php require_once 'includes/footer.php'; ?>
