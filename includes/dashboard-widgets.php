<?php
/**
 * ZYN Trade System - Dashboard Analytics Widgets
 *
 * CARA PAKAI:
 * 1. Include di dashboard.php
 * 2. Panggil widget: DashboardWidgets::renderProfitChart($user_id);
 *
 * Widgets tersedia:
 * - Profit Chart (7 hari terakhir)
 * - Win Rate Gauge
 * - Strategy Performance
 * - Trading Activity Heatmap
 * - Recent Signals
 * - Performance Comparison
 */

class DashboardWidgets {

    /**
     * Get user's trading data
     */
    private static function getUserStats($user_id, $days = 30) {
        global $pdo;

        if (!isset($pdo)) {
            try {
                $pdo = new PDO(
                    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                    DB_USER, DB_PASS
                );
            } catch (PDOException $e) {
                return null;
            }
        }

        $stmt = $pdo->prepare("
            SELECT
                DATE(created_at) as date,
                COUNT(*) as total_trades,
                SUM(CASE WHEN result = 'win' THEN 1 ELSE 0 END) as wins,
                SUM(CASE WHEN result = 'loss' THEN 1 ELSE 0 END) as losses,
                SUM(profit) as profit
            FROM trades
            WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ");
        $stmt->execute([$user_id, $days]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Render Profit Chart Widget (7-day line chart)
     */
    public static function renderProfitChart($user_id) {
        $data = self::getUserStats($user_id, 7);

        // Prepare chart data
        $labels = [];
        $profits = [];
        $cumulative = 0;

        // Fill in missing days
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[] = date('d M', strtotime($date));

            $dayProfit = 0;
            foreach ($data as $d) {
                if ($d['date'] === $date) {
                    $dayProfit = (float) $d['profit'];
                    break;
                }
            }

            $cumulative += $dayProfit;
            $profits[] = $cumulative;
        }

        $labelsJson = json_encode($labels);
        $profitsJson = json_encode($profits);
        $profitColor = end($profits) >= 0 ? '#10b981' : '#ef4444';

        return <<<HTML
<div class="widget widget-chart">
    <div class="widget-header">
        <h5><i class="fas fa-chart-line me-2"></i>Profit 7 Hari Terakhir</h5>
        <span class="widget-value" style="color: {$profitColor}">
            \${$cumulative}
        </span>
    </div>
    <canvas id="profitChart" height="200"></canvas>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('profitChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {$labelsJson},
                datasets: [{
                    label: 'Cumulative Profit',
                    data: {$profitsJson},
                    borderColor: '{$profitColor}',
                    backgroundColor: '{$profitColor}20',
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
                    legend: { display: false }
                },
                scales: {
                    y: {
                        grid: { color: 'rgba(255,255,255,0.05)' },
                        ticks: { color: '#94a3b8' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#94a3b8' }
                    }
                }
            }
        });
    }
});
</script>
HTML;
    }

    /**
     * Render Win Rate Gauge Widget
     */
    public static function renderWinRateGauge($user_id) {
        global $pdo;

        $stmt = $pdo->prepare("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN result = 'win' THEN 1 ELSE 0 END) as wins
            FROM trades
            WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $winRate = $result['total'] > 0
            ? round(($result['wins'] / $result['total']) * 100, 1)
            : 0;

        $gaugeColor = $winRate >= 70 ? '#10b981' : ($winRate >= 50 ? '#f59e0b' : '#ef4444');
        $statusText = $winRate >= 70 ? 'Excellent!' : ($winRate >= 50 ? 'Good' : 'Needs Improvement');

        return <<<HTML
<div class="widget widget-gauge">
    <div class="widget-header">
        <h5><i class="fas fa-bullseye me-2"></i>Win Rate (30 Hari)</h5>
    </div>
    <div class="gauge-container">
        <div class="gauge-circle" style="--percentage: {$winRate}; --color: {$gaugeColor}">
            <div class="gauge-value">{$winRate}%</div>
        </div>
        <div class="gauge-status" style="color: {$gaugeColor}">{$statusText}</div>
        <div class="gauge-info">
            <span>{$result['wins']} wins</span>
            <span>/</span>
            <span>{$result['total']} trades</span>
        </div>
    </div>
</div>

<style>
.gauge-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px;
}
.gauge-circle {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    background: conic-gradient(
        var(--color) calc(var(--percentage) * 1%),
        rgba(255,255,255,0.1) calc(var(--percentage) * 1%)
    );
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}
.gauge-circle::before {
    content: '';
    position: absolute;
    width: 120px;
    height: 120px;
    background: var(--bg-card);
    border-radius: 50%;
}
.gauge-value {
    position: relative;
    font-size: 2rem;
    font-weight: 700;
    color: var(--color);
}
.gauge-status {
    font-size: 1.1rem;
    font-weight: 600;
    margin-top: 1rem;
}
.gauge-info {
    display: flex;
    gap: 0.5rem;
    color: var(--text-muted);
    font-size: 0.9rem;
    margin-top: 0.5rem;
}
</style>
HTML;
    }

    /**
     * Render Strategy Performance Widget
     */
    public static function renderStrategyPerformance($user_id) {
        global $pdo;

        $stmt = $pdo->prepare("
            SELECT
                strategy,
                COUNT(*) as total,
                SUM(CASE WHEN result = 'win' THEN 1 ELSE 0 END) as wins,
                SUM(profit) as profit
            FROM trades
            WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY strategy
            ORDER BY profit DESC
            LIMIT 5
        ");
        $stmt->execute([$user_id]);
        $strategies = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $html = <<<HTML
<div class="widget widget-strategies">
    <div class="widget-header">
        <h5><i class="fas fa-chess me-2"></i>Top Strategi (30 Hari)</h5>
    </div>
    <div class="strategy-list">
HTML;

        foreach ($strategies as $s) {
            $winRate = $s['total'] > 0 ? round(($s['wins'] / $s['total']) * 100, 1) : 0;
            $profitClass = $s['profit'] >= 0 ? 'profit-positive' : 'profit-negative';
            $profitSign = $s['profit'] >= 0 ? '+' : '';

            $html .= <<<HTML
        <div class="strategy-item">
            <div class="strategy-name">
                <strong>{$s['strategy']}</strong>
                <span class="strategy-trades">{$s['total']} trades</span>
            </div>
            <div class="strategy-stats">
                <span class="strategy-winrate">{$winRate}%</span>
                <span class="strategy-profit {$profitClass}">{$profitSign}\${$s['profit']}</span>
            </div>
            <div class="strategy-bar">
                <div class="strategy-bar-fill" style="width: {$winRate}%"></div>
            </div>
        </div>
HTML;
        }

        $html .= '</div></div>';

        $html .= <<<HTML
<style>
.strategy-list { padding: 15px; }
.strategy-item {
    padding: 12px 0;
    border-bottom: 1px solid var(--border-color);
}
.strategy-item:last-child { border-bottom: none; }
.strategy-name {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}
.strategy-trades { color: var(--text-muted); font-size: 0.85rem; }
.strategy-stats {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}
.strategy-winrate { color: var(--primary); font-weight: 600; }
.profit-positive { color: #10b981; font-weight: 600; }
.profit-negative { color: #ef4444; font-weight: 600; }
.strategy-bar {
    height: 4px;
    background: rgba(255,255,255,0.1);
    border-radius: 2px;
    overflow: hidden;
}
.strategy-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--primary), var(--secondary));
    border-radius: 2px;
    transition: width 0.5s ease;
}
</style>
HTML;

        return $html;
    }

    /**
     * Render Recent Signals Widget
     */
    public static function renderRecentSignals($user_id) {
        global $pdo;

        $stmt = $pdo->prepare("
            SELECT * FROM trades
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT 5
        ");
        $stmt->execute([$user_id]);
        $trades = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $html = <<<HTML
<div class="widget widget-signals">
    <div class="widget-header">
        <h5><i class="fas fa-signal me-2"></i>Signal Terbaru</h5>
        <a href="statistics.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
    </div>
    <div class="signals-list">
HTML;

        foreach ($trades as $trade) {
            $time = date('H:i', strtotime($trade['created_at']));
            $date = date('d M', strtotime($trade['created_at']));
            $resultIcon = $trade['result'] === 'win' ? '✅' : '❌';
            $resultClass = $trade['result'] === 'win' ? 'signal-win' : 'signal-loss';
            $directionIcon = $trade['direction'] === 'UP' ? '🟢' : '🔴';

            $html .= <<<HTML
        <div class="signal-item {$resultClass}">
            <div class="signal-time">
                <span class="signal-hour">{$time}</span>
                <span class="signal-date">{$date}</span>
            </div>
            <div class="signal-info">
                <span class="signal-pair">{$trade['pair']}</span>
                <span class="signal-strategy">{$trade['strategy']}</span>
            </div>
            <div class="signal-direction">{$directionIcon} {$trade['direction']}</div>
            <div class="signal-result">{$resultIcon}</div>
        </div>
HTML;
        }

        $html .= '</div></div>';

        $html .= <<<HTML
<style>
.signals-list { padding: 10px; }
.signal-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 8px;
    background: rgba(255,255,255,0.02);
    transition: all 0.2s;
}
.signal-item:hover { background: rgba(255,255,255,0.05); }
.signal-win { border-left: 3px solid #10b981; }
.signal-loss { border-left: 3px solid #ef4444; }
.signal-time {
    text-align: center;
    min-width: 50px;
}
.signal-hour { display: block; font-weight: 600; }
.signal-date { display: block; font-size: 0.75rem; color: var(--text-muted); }
.signal-info { flex: 1; }
.signal-pair { display: block; font-weight: 600; }
.signal-strategy { display: block; font-size: 0.8rem; color: var(--text-muted); }
.signal-direction { font-weight: 600; }
.signal-result { font-size: 1.2rem; }
</style>
HTML;

        return $html;
    }

    /**
     * Render Trading Activity Heatmap (Mini version)
     */
    public static function renderActivityHeatmap($user_id) {
        global $pdo;

        // Get trade counts per hour for last 7 days
        $stmt = $pdo->prepare("
            SELECT
                DAYOFWEEK(created_at) as day,
                HOUR(created_at) as hour,
                COUNT(*) as count
            FROM trades
            WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY DAYOFWEEK(created_at), HOUR(created_at)
        ");
        $stmt->execute([$user_id]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Create heatmap data structure
        $heatmap = [];
        for ($d = 1; $d <= 7; $d++) {
            for ($h = 0; $h < 24; $h++) {
                $heatmap[$d][$h] = 0;
            }
        }

        $maxCount = 1;
        foreach ($data as $d) {
            $heatmap[$d['day']][$d['hour']] = $d['count'];
            if ($d['count'] > $maxCount) $maxCount = $d['count'];
        }

        $days = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

        $html = <<<HTML
<div class="widget widget-heatmap">
    <div class="widget-header">
        <h5><i class="fas fa-fire me-2"></i>Aktivitas Trading</h5>
    </div>
    <div class="heatmap-container">
        <div class="heatmap-hours">
HTML;

        // Hours header
        for ($h = 0; $h < 24; $h += 4) {
            $html .= "<span>{$h}</span>";
        }

        $html .= '</div><div class="heatmap-grid">';

        // Grid cells
        for ($d = 2; $d <= 7; $d++) { // Monday to Saturday
            $dayName = $days[$d - 1];
            $html .= "<div class='heatmap-row'><span class='heatmap-day'>{$dayName}</span>";

            for ($h = 0; $h < 24; $h++) {
                $count = $heatmap[$d][$h];
                $intensity = $maxCount > 0 ? ($count / $maxCount) : 0;
                $opacity = 0.1 + ($intensity * 0.9);
                $html .= "<div class='heatmap-cell' style='--opacity: {$opacity}' title='{$count} trades'></div>";
            }

            $html .= '</div>';
        }

        $html .= '</div></div></div>';

        $html .= <<<HTML
<style>
.heatmap-container { padding: 15px; overflow-x: auto; }
.heatmap-hours {
    display: flex;
    justify-content: space-between;
    padding-left: 40px;
    margin-bottom: 5px;
    color: var(--text-muted);
    font-size: 0.75rem;
}
.heatmap-grid { display: flex; flex-direction: column; gap: 3px; }
.heatmap-row { display: flex; align-items: center; gap: 3px; }
.heatmap-day {
    width: 35px;
    font-size: 0.75rem;
    color: var(--text-muted);
}
.heatmap-cell {
    width: 12px;
    height: 12px;
    border-radius: 2px;
    background: var(--primary);
    opacity: var(--opacity);
}
</style>
HTML;

        return $html;
    }

    /**
     * Render Quick Stats Row
     */
    public static function renderQuickStats($user_id) {
        global $pdo;

        $stmt = $pdo->prepare("
            SELECT
                COUNT(*) as total_trades,
                SUM(CASE WHEN result = 'win' THEN 1 ELSE 0 END) as wins,
                SUM(profit) as total_profit,
                MAX(profit) as best_trade,
                MIN(profit) as worst_trade
            FROM trades
            WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $stmt->execute([$user_id]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        $winRate = $stats['total_trades'] > 0
            ? round(($stats['wins'] / $stats['total_trades']) * 100, 1)
            : 0;

        $profitSign = $stats['total_profit'] >= 0 ? '+' : '';
        $profitClass = $stats['total_profit'] >= 0 ? 'stat-positive' : 'stat-negative';

        return <<<HTML
<div class="quick-stats-row">
    <div class="quick-stat">
        <div class="quick-stat-icon"><i class="fas fa-exchange-alt"></i></div>
        <div class="quick-stat-info">
            <span class="quick-stat-value">{$stats['total_trades']}</span>
            <span class="quick-stat-label">Total Trades</span>
        </div>
    </div>
    <div class="quick-stat">
        <div class="quick-stat-icon text-success"><i class="fas fa-check-circle"></i></div>
        <div class="quick-stat-info">
            <span class="quick-stat-value">{$stats['wins']}</span>
            <span class="quick-stat-label">Wins</span>
        </div>
    </div>
    <div class="quick-stat">
        <div class="quick-stat-icon text-primary"><i class="fas fa-percentage"></i></div>
        <div class="quick-stat-info">
            <span class="quick-stat-value">{$winRate}%</span>
            <span class="quick-stat-label">Win Rate</span>
        </div>
    </div>
    <div class="quick-stat">
        <div class="quick-stat-icon {$profitClass}"><i class="fas fa-dollar-sign"></i></div>
        <div class="quick-stat-info">
            <span class="quick-stat-value {$profitClass}">{$profitSign}\${$stats['total_profit']}</span>
            <span class="quick-stat-label">Profit</span>
        </div>
    </div>
</div>

<style>
.quick-stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}
.quick-stat {
    display: flex;
    align-items: center;
    gap: 15px;
    background: rgba(18, 18, 26, 0.6);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 15px 20px;
}
.quick-stat-icon {
    font-size: 1.5rem;
    color: var(--primary);
}
.quick-stat-value {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
}
.quick-stat-label {
    display: block;
    font-size: 0.8rem;
    color: var(--text-muted);
}
.stat-positive { color: #10b981; }
.stat-negative { color: #ef4444; }
</style>
HTML;
    }
}
