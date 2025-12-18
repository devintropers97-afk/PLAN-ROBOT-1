<?php
/**
 * ZYN Trade System - Strategy Performance Comparison
 * FASE 3: Strategy Performance Comparison (FINAL ZYN - Inovasi #11)
 */
$page_title = __('strat_compare_title');
require_once 'dashboard/includes/dashboard-header.php';

// Strategy data with historical performance
$strategies = [
    [
        'id' => 1,
        'name' => 'ORACLE-PRIME',
        'tier' => 'VIP',
        'description' => 'Triple RSI Confirmation - Signal jarang tapi SUPER AKURAT',
        'win_rate' => ['30d' => 91.2, '60d' => 90.5, '90d' => 90.8],
        'signals_per_day' => 3,
        'avg_profit' => ['30d' => 28.5, '60d' => 27.2, '90d' => 27.8],
        'max_drawdown' => 8.5,
        'sharpe_ratio' => 2.8,
        'best_timeframe' => '15M',
        'best_market' => 'EUR/USD',
        'risk_level' => 'Low',
        'total_trades' => ['30d' => 87, '60d' => 165, '90d' => 248],
        'color' => '#00d4ff'
    ],
    [
        'id' => 2,
        'name' => 'NEXUS-WAVE',
        'tier' => 'VIP',
        'description' => 'MACD + RSI Divergence - Balance antara frequency dan accuracy',
        'win_rate' => ['30d' => 87.4, '60d' => 86.8, '90d' => 87.1],
        'signals_per_day' => 5,
        'avg_profit' => ['30d' => 24.2, '60d' => 23.8, '90d' => 24.0],
        'max_drawdown' => 12.3,
        'sharpe_ratio' => 2.4,
        'best_timeframe' => '5M',
        'best_market' => 'GBP/USD',
        'risk_level' => 'Low',
        'total_trades' => ['30d' => 142, '60d' => 278, '90d' => 420],
        'color' => '#7c3aed'
    ],
    [
        'id' => 3,
        'name' => 'STEALTH-MODE',
        'tier' => 'ELITE',
        'description' => 'Connors RSI(2) - Extreme mean reversion strategy',
        'win_rate' => ['30d' => 81.5, '60d' => 80.2, '90d' => 80.8],
        'signals_per_day' => 7,
        'avg_profit' => ['30d' => 18.5, '60d' => 17.8, '90d' => 18.1],
        'max_drawdown' => 15.2,
        'sharpe_ratio' => 1.9,
        'best_timeframe' => '5M',
        'best_market' => 'EUR/USD',
        'risk_level' => 'Medium',
        'total_trades' => ['30d' => 198, '60d' => 385, '90d' => 580],
        'color' => '#10b981'
    ],
    [
        'id' => 4,
        'name' => 'PHOENIX-X1',
        'tier' => 'ELITE',
        'description' => 'Stochastic RSI + MACD - Recovery specialist',
        'win_rate' => ['30d' => 78.8, '60d' => 79.5, '90d' => 79.2],
        'signals_per_day' => 8,
        'avg_profit' => ['30d' => 15.2, '60d' => 16.1, '90d' => 15.8],
        'max_drawdown' => 18.5,
        'sharpe_ratio' => 1.7,
        'best_timeframe' => '15M',
        'best_market' => 'EUR/USD',
        'risk_level' => 'Medium',
        'total_trades' => ['30d' => 225, '60d' => 445, '90d' => 668],
        'color' => '#f59e0b'
    ],
    [
        'id' => 5,
        'name' => 'VORTEX-PRO',
        'tier' => 'ELITE',
        'description' => 'MACD Bollinger Fusion - Strong momentum catcher',
        'win_rate' => ['30d' => 78.2, '60d' => 77.5, '90d' => 77.8],
        'signals_per_day' => 6,
        'avg_profit' => ['30d' => 14.8, '60d' => 14.2, '90d' => 14.5],
        'max_drawdown' => 16.8,
        'sharpe_ratio' => 1.6,
        'best_timeframe' => '30M',
        'best_market' => 'GBP/USD',
        'risk_level' => 'Medium',
        'total_trades' => ['30d' => 168, '60d' => 330, '90d' => 495],
        'color' => '#ef4444'
    ],
    [
        'id' => 6,
        'name' => 'TITAN-PULSE',
        'tier' => 'PRO',
        'description' => 'Williams %R Strategy - Oversold/overbought hunter',
        'win_rate' => ['30d' => 73.5, '60d' => 72.8, '90d' => 73.1],
        'signals_per_day' => 10,
        'avg_profit' => ['30d' => 12.5, '60d' => 11.8, '90d' => 12.1],
        'max_drawdown' => 20.5,
        'sharpe_ratio' => 1.4,
        'best_timeframe' => '5M',
        'best_market' => 'EUR/USD',
        'risk_level' => 'Medium',
        'total_trades' => ['30d' => 285, '60d' => 565, '90d' => 848],
        'color' => '#8b5cf6'
    ],
    [
        'id' => 7,
        'name' => 'SHADOW-EDGE',
        'tier' => 'PRO',
        'description' => 'RSI Divergence Detector - Trend reversal expert',
        'win_rate' => ['30d' => 73.2, '60d' => 72.5, '90d' => 72.8],
        'signals_per_day' => 8,
        'avg_profit' => ['30d' => 11.8, '60d' => 11.2, '90d' => 11.5],
        'max_drawdown' => 22.1,
        'sharpe_ratio' => 1.3,
        'best_timeframe' => '15M',
        'best_market' => 'GBP/USD',
        'risk_level' => 'High',
        'total_trades' => ['30d' => 228, '60d' => 452, '90d' => 678],
        'color' => '#ec4899'
    ],
    [
        'id' => 8,
        'name' => 'BLITZ-SIGNAL',
        'tier' => 'FREE',
        'description' => 'BB + RSI Standard - High frequency trading',
        'win_rate' => ['30d' => 68.5, '60d' => 67.8, '90d' => 68.1],
        'signals_per_day' => 15,
        'avg_profit' => ['30d' => 8.5, '60d' => 8.2, '90d' => 8.3],
        'max_drawdown' => 25.8,
        'sharpe_ratio' => 1.1,
        'best_timeframe' => '1M',
        'best_market' => 'EUR/USD',
        'risk_level' => 'High',
        'total_trades' => ['30d' => 428, '60d' => 848, '90d' => 1275],
        'color' => '#06b6d4'
    ],
    [
        'id' => 9,
        'name' => 'APEX-HUNTER',
        'tier' => 'FREE',
        'description' => 'Multi-Indicator Combo - Versatile all-rounder',
        'win_rate' => ['30d' => 65.2, '60d' => 66.8, '90d' => 66.1],
        'signals_per_day' => 12,
        'avg_profit' => ['30d' => 6.8, '60d' => 7.5, '90d' => 7.2],
        'max_drawdown' => 28.5,
        'sharpe_ratio' => 0.95,
        'best_timeframe' => '5M',
        'best_market' => 'EUR/USD',
        'risk_level' => 'High',
        'total_trades' => ['30d' => 345, '60d' => 685, '90d' => 1028],
        'color' => '#84cc16'
    ],
    [
        'id' => 10,
        'name' => 'QUANTUM-FLOW',
        'tier' => 'VIP',
        'description' => 'Adaptive AI Algorithm - Self-optimizing strategy',
        'win_rate' => ['30d' => 85.5, '60d' => 86.2, '90d' => 85.8],
        'signals_per_day' => 4,
        'avg_profit' => ['30d' => 22.5, '60d' => 23.2, '90d' => 22.8],
        'max_drawdown' => 10.2,
        'sharpe_ratio' => 2.5,
        'best_timeframe' => '15M',
        'best_market' => 'EUR/USD',
        'risk_level' => 'Low',
        'total_trades' => ['30d' => 115, '60d' => 228, '90d' => 342],
        'color' => '#f472b6'
    ],
];

$selectedPeriod = $_GET['period'] ?? '30d';
$userPackage = $currentUser['package'] ?? 'free';
?>

<!-- Page Header -->
<div class="db-page-header">
    <div>
        <h1 class="db-page-title"><i class="fas fa-balance-scale"></i> Strategy Comparison</h1>
        <p class="db-page-subtitle">Compare performance across all strategies</p>
    </div>
    <div class="d-flex gap-2">
        <select class="db-form-control" id="periodSelect" style="width: auto;" onchange="changePeriod(this.value)">
            <option value="30d" <?php echo $selectedPeriod === '30d' ? 'selected' : ''; ?>>Last 30 Days</option>
            <option value="60d" <?php echo $selectedPeriod === '60d' ? 'selected' : ''; ?>>Last 60 Days</option>
            <option value="90d" <?php echo $selectedPeriod === '90d' ? 'selected' : ''; ?>>Last 90 Days</option>
        </select>
    </div>
</div>

<!-- Quick Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="db-stat-card db-fade-in">
            <div class="db-stat-icon"><i class="fas fa-trophy"></i></div>
            <div class="db-stat-value">ORACLE-PRIME</div>
            <div class="db-stat-label">Highest Win Rate</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="db-stat-card success db-fade-in">
            <div class="db-stat-icon"><i class="fas fa-dollar-sign"></i></div>
            <div class="db-stat-value">28.5%</div>
            <div class="db-stat-label">Best Monthly Profit</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="db-stat-card db-fade-in">
            <div class="db-stat-icon"><i class="fas fa-shield-alt"></i></div>
            <div class="db-stat-value">8.5%</div>
            <div class="db-stat-label">Lowest Drawdown</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="db-stat-card warning db-fade-in">
            <div class="db-stat-icon"><i class="fas fa-lightbulb"></i></div>
            <div class="db-stat-value">QUANTUM</div>
            <div class="db-stat-label">Recommended</div>
        </div>
    </div>
</div>

<!-- Comparison Chart -->
<div class="db-card mb-4 db-fade-in">
    <div class="db-card-header">
        <h5 class="db-card-title"><i class="fas fa-chart-bar"></i> Win Rate Comparison</h5>
    </div>
    <div class="db-card-body">
        <div class="chart-container">
            <?php foreach ($strategies as $strategy):
                $winRate = $strategy['win_rate'][$selectedPeriod];
            ?>
            <div class="chart-row">
                <div class="chart-label">
                    <span class="strategy-name"><?php echo $strategy['name']; ?></span>
                    <span class="db-badge <?php echo strtolower($strategy['tier']); ?> ms-1"><?php echo $strategy['tier']; ?></span>
                </div>
                <div class="chart-bar-wrapper">
                    <div class="chart-bar" style="width: <?php echo $winRate; ?>%; background: <?php echo $strategy['color']; ?>;">
                        <span class="chart-value"><?php echo number_format($winRate, 1); ?>%</span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Detailed Comparison Table -->
<div class="db-card mb-4 db-fade-in">
    <div class="db-card-header">
        <h5 class="db-card-title"><i class="fas fa-table"></i> Detailed Comparison</h5>
        <div class="d-flex gap-2">
            <button class="db-btn db-btn-sm db-btn-outline" onclick="sortTable('win_rate')">
                <i class="fas fa-sort"></i> Win Rate
            </button>
            <button class="db-btn db-btn-sm db-btn-outline" onclick="sortTable('profit')">
                <i class="fas fa-sort"></i> Profit
            </button>
            <button class="db-btn db-btn-sm db-btn-outline" onclick="sortTable('risk')">
                <i class="fas fa-sort"></i> Risk
            </button>
        </div>
    </div>
    <div class="db-card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="db-table" id="comparisonTable">
                <thead>
                    <tr>
                        <th>Strategy</th>
                        <th>Tier</th>
                        <th>Win Rate</th>
                        <th>Avg Profit</th>
                        <th>Signals/Day</th>
                        <th>Max DD</th>
                        <th>Sharpe</th>
                        <th>Risk</th>
                        <th>Best TF</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($strategies as $strategy):
                        $winRate = $strategy['win_rate'][$selectedPeriod];
                        $avgProfit = $strategy['avg_profit'][$selectedPeriod];
                        $isAccessible = $strategy['tier'] === 'FREE' ||
                                        ($strategy['tier'] === 'PRO' && in_array($userPackage, ['pro', 'elite', 'vip'])) ||
                                        ($strategy['tier'] === 'ELITE' && in_array($userPackage, ['elite', 'vip'])) ||
                                        ($strategy['tier'] === 'VIP' && $userPackage === 'vip');
                    ?>
                    <tr data-win-rate="<?php echo $winRate; ?>" data-profit="<?php echo $avgProfit; ?>" data-risk="<?php echo $strategy['max_drawdown']; ?>">
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="strategy-dot" style="background: <?php echo $strategy['color']; ?>;"></div>
                                <strong><?php echo $strategy['name']; ?></strong>
                            </div>
                        </td>
                        <td><span class="db-badge <?php echo strtolower($strategy['tier']); ?>"><?php echo $strategy['tier']; ?></span></td>
                        <td class="text-success fw-bold"><?php echo number_format($winRate, 1); ?>%</td>
                        <td class="text-primary">+<?php echo number_format($avgProfit, 1); ?>%</td>
                        <td><?php echo $strategy['signals_per_day']; ?></td>
                        <td class="text-danger"><?php echo $strategy['max_drawdown']; ?>%</td>
                        <td><?php echo $strategy['sharpe_ratio']; ?></td>
                        <td>
                            <span class="risk-badge <?php echo strtolower($strategy['risk_level']); ?>">
                                <?php echo $strategy['risk_level']; ?>
                            </span>
                        </td>
                        <td><?php echo $strategy['best_timeframe']; ?></td>
                        <td>
                            <?php if ($isAccessible): ?>
                            <a href="dashboard.php?strategy=<?php echo $strategy['id']; ?>" class="db-btn db-btn-primary db-btn-sm">
                                <i class="fas fa-play"></i>
                            </a>
                            <?php else: ?>
                            <a href="pricing.php" class="db-btn db-btn-warning db-btn-sm">
                                <i class="fas fa-lock"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Strategy Recommendation -->
<div class="db-card db-fade-in">
    <div class="db-card-header">
        <h5 class="db-card-title"><i class="fas fa-lightbulb"></i> Personalized Recommendation</h5>
    </div>
    <div class="db-card-body">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="recommendation-card conservative">
                    <div class="rec-icon"><i class="fas fa-shield-alt"></i></div>
                    <h6>Conservative Trader</h6>
                    <p class="text-muted small">Prioritas: Keamanan modal, win rate tinggi</p>
                    <div class="rec-strategy">
                        <strong>ORACLE-PRIME</strong>
                        <span class="db-badge success ms-2">91.2% WR</span>
                    </div>
                    <ul class="rec-points">
                        <li>Win rate tertinggi 91.2%</li>
                        <li>Max drawdown hanya 8.5%</li>
                        <li>Sharpe ratio 2.8</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-4">
                <div class="recommendation-card balanced">
                    <div class="rec-icon"><i class="fas fa-balance-scale"></i></div>
                    <h6>Balanced Trader</h6>
                    <p class="text-muted small">Prioritas: Balance risk & reward</p>
                    <div class="rec-strategy">
                        <strong>QUANTUM-FLOW</strong>
                        <span class="db-badge primary ms-2">85.5% WR</span>
                    </div>
                    <ul class="rec-points">
                        <li>Win rate stabil 85.5%</li>
                        <li>Self-optimizing algorithm</li>
                        <li>Profit/risk ratio optimal</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-4">
                <div class="recommendation-card aggressive">
                    <div class="rec-icon"><i class="fas fa-rocket"></i></div>
                    <h6>Aggressive Trader</h6>
                    <p class="text-muted small">Prioritas: Volume trading tinggi</p>
                    <div class="rec-strategy">
                        <strong>BLITZ-SIGNAL</strong>
                        <span class="db-badge warning ms-2">15 sig/day</span>
                    </div>
                    <ul class="rec-points">
                        <li>15+ signals per day</li>
                        <li>High frequency trading</li>
                        <li>FREE tier available</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Chart Container */
.chart-container {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.chart-row {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.chart-label {
    width: 180px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
}

.strategy-name {
    font-weight: 500;
    font-size: 0.85rem;
}

.chart-bar-wrapper {
    flex: 1;
    background: var(--db-surface-light);
    border-radius: 8px;
    height: 28px;
    overflow: hidden;
}

.chart-bar {
    height: 100%;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding-right: 0.75rem;
    transition: width 0.5s ease;
    min-width: 60px;
}

.chart-value {
    color: white;
    font-weight: 600;
    font-size: 0.8rem;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3);
}

/* Strategy Dot */
.strategy-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    flex-shrink: 0;
}

/* Risk Badge */
.risk-badge {
    font-size: 0.7rem;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-weight: 500;
}

.risk-badge.low {
    background: rgba(var(--db-success-rgb), 0.15);
    color: var(--db-success);
}

.risk-badge.medium {
    background: rgba(var(--db-warning-rgb), 0.15);
    color: var(--db-warning);
}

.risk-badge.high {
    background: rgba(var(--db-danger-rgb), 0.15);
    color: var(--db-danger);
}

/* Recommendation Cards */
.recommendation-card {
    padding: 1.5rem;
    border-radius: 12px;
    background: var(--db-surface);
    border: 1px solid var(--db-border);
    height: 100%;
}

.recommendation-card.conservative {
    border-color: var(--db-success);
    background: rgba(var(--db-success-rgb), 0.05);
}

.recommendation-card.balanced {
    border-color: var(--db-primary);
    background: rgba(var(--db-primary-rgb), 0.05);
}

.recommendation-card.aggressive {
    border-color: var(--db-warning);
    background: rgba(var(--db-warning-rgb), 0.05);
}

.rec-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    margin-bottom: 1rem;
}

.conservative .rec-icon {
    background: rgba(var(--db-success-rgb), 0.15);
    color: var(--db-success);
}

.balanced .rec-icon {
    background: rgba(var(--db-primary-rgb), 0.15);
    color: var(--db-primary);
}

.aggressive .rec-icon {
    background: rgba(var(--db-warning-rgb), 0.15);
    color: var(--db-warning);
}

.rec-strategy {
    padding: 0.75rem;
    background: var(--db-surface-light);
    border-radius: 8px;
    margin: 1rem 0;
}

.rec-points {
    list-style: none;
    padding: 0;
    margin: 0;
    font-size: 0.85rem;
}

.rec-points li {
    padding: 0.25rem 0;
    padding-left: 1.25rem;
    position: relative;
}

.rec-points li::before {
    content: '✓';
    position: absolute;
    left: 0;
    color: var(--db-success);
}
</style>

<script>
function changePeriod(period) {
    window.location.href = 'strategy-comparison.php?period=' + period;
}

function sortTable(criteria) {
    const table = document.getElementById('comparisonTable');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));

    rows.sort((a, b) => {
        let aVal, bVal;
        switch(criteria) {
            case 'win_rate':
                aVal = parseFloat(a.dataset.winRate);
                bVal = parseFloat(b.dataset.winRate);
                break;
            case 'profit':
                aVal = parseFloat(a.dataset.profit);
                bVal = parseFloat(b.dataset.profit);
                break;
            case 'risk':
                aVal = parseFloat(a.dataset.risk);
                bVal = parseFloat(b.dataset.risk);
                return aVal - bVal; // Lower is better for risk
        }
        return bVal - aVal;
    });

    rows.forEach(row => tbody.appendChild(row));
    showToast('Sorted by ' + criteria.replace('_', ' '), 'info');
}
</script>

<?php require_once 'dashboard/includes/dashboard-footer.php'; ?>
