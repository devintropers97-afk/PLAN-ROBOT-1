<?php
/**
 * ZYN Trade System - Market Sentiment Indicator
 * FASE 3: Market Sentiment Indicator (FINAL ZYN - Inovasi #10)
 */
$page_title = __('sentiment_title');
require_once 'dashboard/includes/dashboard-header.php';

// Mock market sentiment data
$sentimentData = [
    'EUR/USD' => [
        'overall' => 65,
        'trend' => 'bullish',
        'strength' => 'strong',
        'recommendation' => 'CALL',
        'timeframes' => [
            '1M' => ['sentiment' => 58, 'trend' => 'neutral'],
            '5M' => ['sentiment' => 65, 'trend' => 'bullish'],
            '15M' => ['sentiment' => 70, 'trend' => 'bullish'],
            '1H' => ['sentiment' => 62, 'trend' => 'bullish'],
        ],
        'indicators' => [
            'RSI' => ['value' => 58, 'signal' => 'neutral'],
            'MACD' => ['value' => 0.0012, 'signal' => 'bullish'],
            'BB' => ['value' => 'middle', 'signal' => 'neutral'],
            'SMA' => ['value' => 'above', 'signal' => 'bullish'],
        ],
        'news_impact' => 'medium',
        'volatility' => 'normal',
    ],
    'GBP/USD' => [
        'overall' => 42,
        'trend' => 'bearish',
        'strength' => 'moderate',
        'recommendation' => 'PUT',
        'timeframes' => [
            '1M' => ['sentiment' => 45, 'trend' => 'neutral'],
            '5M' => ['sentiment' => 40, 'trend' => 'bearish'],
            '15M' => ['sentiment' => 38, 'trend' => 'bearish'],
            '1H' => ['sentiment' => 48, 'trend' => 'neutral'],
        ],
        'indicators' => [
            'RSI' => ['value' => 35, 'signal' => 'oversold'],
            'MACD' => ['value' => -0.0008, 'signal' => 'bearish'],
            'BB' => ['value' => 'lower', 'signal' => 'bearish'],
            'SMA' => ['value' => 'below', 'signal' => 'bearish'],
        ],
        'news_impact' => 'high',
        'volatility' => 'high',
    ],
    'USD/JPY' => [
        'overall' => 52,
        'trend' => 'neutral',
        'strength' => 'weak',
        'recommendation' => 'WAIT',
        'timeframes' => [
            '1M' => ['sentiment' => 50, 'trend' => 'neutral'],
            '5M' => ['sentiment' => 55, 'trend' => 'neutral'],
            '15M' => ['sentiment' => 48, 'trend' => 'neutral'],
            '1H' => ['sentiment' => 53, 'trend' => 'neutral'],
        ],
        'indicators' => [
            'RSI' => ['value' => 52, 'signal' => 'neutral'],
            'MACD' => ['value' => 0.0002, 'signal' => 'neutral'],
            'BB' => ['value' => 'middle', 'signal' => 'neutral'],
            'SMA' => ['value' => 'at', 'signal' => 'neutral'],
        ],
        'news_impact' => 'low',
        'volatility' => 'low',
    ],
];

// Upcoming news events
$upcomingNews = [
    ['time' => '14:30', 'currency' => 'USD', 'event' => 'Initial Jobless Claims', 'impact' => 'high', 'forecast' => '215K'],
    ['time' => '16:00', 'currency' => 'EUR', 'event' => 'ECB President Speech', 'impact' => 'high', 'forecast' => '-'],
    ['time' => '18:30', 'currency' => 'GBP', 'event' => 'BOE Interest Rate', 'impact' => 'high', 'forecast' => '5.25%'],
    ['time' => '20:00', 'currency' => 'USD', 'event' => 'Fed Chair Powell Speaks', 'impact' => 'medium', 'forecast' => '-'],
];

// Market hours
$marketHours = [
    'Sydney' => ['open' => '04:00', 'close' => '13:00', 'status' => 'open'],
    'Tokyo' => ['open' => '06:00', 'close' => '15:00', 'status' => 'open'],
    'London' => ['open' => '14:00', 'close' => '23:00', 'status' => 'closed'],
    'New York' => ['open' => '19:00', 'close' => '04:00', 'status' => 'closed'],
];

$currentHour = (int)date('H');
foreach ($marketHours as $market => &$data) {
    $openHour = (int)explode(':', $data['open'])[0];
    $closeHour = (int)explode(':', $data['close'])[0];
    if ($closeHour < $openHour) { // Overnight session
        $data['status'] = ($currentHour >= $openHour || $currentHour < $closeHour) ? 'open' : 'closed';
    } else {
        $data['status'] = ($currentHour >= $openHour && $currentHour < $closeHour) ? 'open' : 'closed';
    }
}
unset($data);
?>

<!-- Page Header -->
<div class="db-page-header">
    <div>
        <h1 class="db-page-title"><i class="fas fa-compass"></i> <?php _e('sentiment_heading'); ?></h1>
        <p class="db-page-subtitle"><?php _e('sentiment_subtitle'); ?></p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <span class="db-badge info">
            <i class="fas fa-sync-alt fa-spin me-1"></i> <?php _e('sentiment_live_updates'); ?>
        </span>
        <span class="text-muted small"><?php _e('sentiment_last_update'); ?>: <?php echo date('H:i:s'); ?> WIB</span>
    </div>
</div>

<!-- Market Sessions -->
<div class="db-card mb-4 db-fade-in">
    <div class="db-card-header">
        <h5 class="db-card-title"><i class="fas fa-globe"></i> <?php _e('sentiment_market_sessions'); ?></h5>
    </div>
    <div class="db-card-body">
        <div class="row g-3">
            <?php foreach ($marketHours as $market => $data): ?>
            <div class="col-6 col-md-3">
                <div class="market-session <?php echo $data['status']; ?>">
                    <div class="session-status">
                        <span class="status-dot"></span>
                        <?php echo ucfirst($data['status']); ?>
                    </div>
                    <div class="session-name"><?php echo $market; ?></div>
                    <div class="session-time"><?php echo $data['open']; ?> - <?php echo $data['close']; ?> WIB</div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Sentiment Cards -->
<div class="row g-4 mb-4">
    <?php foreach ($sentimentData as $pair => $data): ?>
    <div class="col-md-4">
        <div class="db-card h-100 db-fade-in sentiment-card <?php echo $data['trend']; ?>">
            <div class="db-card-header">
                <h5 class="db-card-title"><?php echo $pair; ?></h5>
                <span class="db-badge <?php echo $data['trend'] === 'bullish' ? 'success' : ($data['trend'] === 'bearish' ? 'danger' : 'warning'); ?>">
                    <i class="fas fa-arrow-<?php echo $data['trend'] === 'bullish' ? 'up' : ($data['trend'] === 'bearish' ? 'down' : 'right'); ?>"></i>
                    <?php echo strtoupper($data['trend']); ?>
                </span>
            </div>
            <div class="db-card-body">
                <!-- Sentiment Gauge -->
                <div class="sentiment-gauge-wrapper">
                    <div class="sentiment-gauge">
                        <div class="gauge-value <?php echo $data['trend']; ?>"><?php echo $data['overall']; ?></div>
                        <div class="gauge-bar">
                            <div class="gauge-fill <?php echo $data['trend']; ?>" style="width: <?php echo $data['overall']; ?>%;"></div>
                        </div>
                        <div class="gauge-labels">
                            <span class="text-danger">PUT</span>
                            <span class="text-muted">50</span>
                            <span class="text-success">CALL</span>
                        </div>
                    </div>
                </div>

                <!-- Recommendation -->
                <div class="text-center my-3">
                    <div class="recommendation-badge <?php echo strtolower($data['recommendation']); ?>">
                        <?php echo $data['recommendation']; ?>
                    </div>
                    <small class="text-muted d-block mt-1"><?php _e('sentiment_signal_strength'); ?>: <strong><?php echo ucfirst($data['strength']); ?></strong></small>
                </div>

                <!-- Timeframe Breakdown -->
                <div class="timeframe-breakdown">
                    <div class="d-flex justify-content-between text-muted small mb-2">
                        <span><?php _e('sentiment_timeframe_analysis'); ?></span>
                    </div>
                    <?php foreach ($data['timeframes'] as $tf => $tfData): ?>
                    <div class="tf-row">
                        <span class="tf-name"><?php echo $tf; ?></span>
                        <div class="tf-bar-wrapper">
                            <div class="tf-bar">
                                <div class="tf-fill <?php echo $tfData['trend']; ?>" style="width: <?php echo $tfData['sentiment']; ?>%;"></div>
                            </div>
                        </div>
                        <span class="tf-value"><?php echo $tfData['sentiment']; ?>%</span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Indicators -->
                <div class="indicators-grid mt-3">
                    <?php foreach ($data['indicators'] as $ind => $indData): ?>
                    <div class="indicator-item">
                        <span class="ind-name"><?php echo $ind; ?></span>
                        <span class="ind-signal <?php echo $indData['signal']; ?>">
                            <?php echo ucfirst($indData['signal']); ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Market Conditions -->
                <div class="market-conditions mt-3 pt-3" style="border-top: 1px solid var(--db-border);">
                    <div class="d-flex justify-content-between small">
                        <span class="text-muted"><?php _e('sentiment_news_impact'); ?>:</span>
                        <span class="badge bg-<?php echo $data['news_impact'] === 'high' ? 'danger' : ($data['news_impact'] === 'medium' ? 'warning' : 'success'); ?>">
                            <?php echo ucfirst($data['news_impact']); ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between small mt-1">
                        <span class="text-muted"><?php _e('sentiment_volatility'); ?>:</span>
                        <span class="badge bg-<?php echo $data['volatility'] === 'high' ? 'danger' : ($data['volatility'] === 'normal' ? 'warning' : 'success'); ?>">
                            <?php echo ucfirst($data['volatility']); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Upcoming News Events -->
<div class="db-card db-fade-in">
    <div class="db-card-header">
        <h5 class="db-card-title"><i class="fas fa-newspaper"></i> <?php _e('sentiment_upcoming_news'); ?></h5>
        <span class="text-muted small"><?php _e('sentiment_today'); ?>, <?php echo date('d M Y'); ?></span>
    </div>
    <div class="db-card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="db-table">
                <thead>
                    <tr>
                        <th><?php _e('sentiment_time'); ?></th>
                        <th><?php _e('sentiment_currency'); ?></th>
                        <th><?php _e('sentiment_event'); ?></th>
                        <th><?php _e('sentiment_impact'); ?></th>
                        <th><?php _e('sentiment_forecast'); ?></th>
                        <th><?php _e('sentiment_action'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($upcomingNews as $news): ?>
                    <tr>
                        <td>
                            <strong><?php echo $news['time']; ?></strong>
                        </td>
                        <td>
                            <span class="currency-badge"><?php echo $news['currency']; ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($news['event']); ?></td>
                        <td>
                            <span class="impact-badge <?php echo $news['impact']; ?>">
                                <?php for ($i = 0; $i < ($news['impact'] === 'high' ? 3 : ($news['impact'] === 'medium' ? 2 : 1)); $i++): ?>
                                <i class="fas fa-fire"></i>
                                <?php endfor; ?>
                            </span>
                        </td>
                        <td><?php echo $news['forecast']; ?></td>
                        <td>
                            <button class="db-btn db-btn-outline db-btn-sm" onclick="setNewsAlert('<?php echo $news['event']; ?>')">
                                <i class="fas fa-bell"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Disclaimer -->
<div class="alert alert-warning mt-4 db-fade-in" style="background: rgba(var(--db-warning-rgb), 0.1); border: 1px solid rgba(var(--db-warning-rgb), 0.3);">
    <div class="d-flex gap-3">
        <i class="fas fa-exclamation-triangle fa-lg mt-1"></i>
        <div>
            <strong>Disclaimer:</strong> <?php _e('sentiment_disclaimer'); ?>
        </div>
    </div>
</div>

<style>
/* Market Sessions */
.market-session {
    padding: 1rem;
    border-radius: 12px;
    background: var(--db-surface);
    border: 1px solid var(--db-border);
    text-align: center;
}

.market-session.open {
    border-color: var(--db-success);
    background: rgba(var(--db-success-rgb), 0.05);
}

.session-status {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
}

.status-dot {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--db-danger);
    margin-right: 4px;
}

.market-session.open .status-dot {
    background: var(--db-success);
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
}

.session-name {
    font-weight: 600;
    font-size: 1rem;
}

.session-time {
    font-size: 0.75rem;
    color: var(--db-text-muted);
}

/* Sentiment Card */
.sentiment-card.bullish { border-left: 3px solid var(--db-success); }
.sentiment-card.bearish { border-left: 3px solid var(--db-danger); }
.sentiment-card.neutral { border-left: 3px solid var(--db-warning); }

/* Sentiment Gauge */
.sentiment-gauge-wrapper {
    padding: 1rem 0;
}

.gauge-value {
    font-size: 2.5rem;
    font-weight: 700;
    text-align: center;
    font-family: 'Orbitron', sans-serif;
}

.gauge-value.bullish { color: var(--db-success); }
.gauge-value.bearish { color: var(--db-danger); }
.gauge-value.neutral { color: var(--db-warning); }

.gauge-bar {
    height: 8px;
    background: linear-gradient(90deg, var(--db-danger) 0%, var(--db-warning) 50%, var(--db-success) 100%);
    border-radius: 4px;
    margin: 0.5rem 0;
    position: relative;
}

.gauge-fill {
    position: absolute;
    left: 0;
    top: -4px;
    width: 4px;
    height: 16px;
    background: white;
    border-radius: 2px;
    box-shadow: 0 0 4px rgba(0,0,0,0.5);
    transition: left 0.5s ease;
}

.gauge-labels {
    display: flex;
    justify-content: space-between;
    font-size: 0.7rem;
}

/* Recommendation Badge */
.recommendation-badge {
    display: inline-block;
    padding: 0.5rem 1.5rem;
    border-radius: 20px;
    font-weight: 700;
    font-size: 1.1rem;
    letter-spacing: 1px;
}

.recommendation-badge.call {
    background: rgba(var(--db-success-rgb), 0.2);
    color: var(--db-success);
}

.recommendation-badge.put {
    background: rgba(var(--db-danger-rgb), 0.2);
    color: var(--db-danger);
}

.recommendation-badge.wait {
    background: rgba(var(--db-warning-rgb), 0.2);
    color: var(--db-warning);
}

/* Timeframe Breakdown */
.tf-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.tf-name {
    width: 35px;
    font-size: 0.75rem;
    color: var(--db-text-muted);
}

.tf-bar-wrapper {
    flex: 1;
}

.tf-bar {
    height: 6px;
    background: var(--db-surface-light);
    border-radius: 3px;
    overflow: hidden;
}

.tf-fill {
    height: 100%;
    transition: width 0.5s ease;
}

.tf-fill.bullish { background: var(--db-success); }
.tf-fill.bearish { background: var(--db-danger); }
.tf-fill.neutral { background: var(--db-warning); }

.tf-value {
    width: 35px;
    text-align: right;
    font-size: 0.75rem;
    font-weight: 500;
}

/* Indicators Grid */
.indicators-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.5rem;
}

.indicator-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.35rem 0.5rem;
    background: var(--db-surface-light);
    border-radius: 6px;
    font-size: 0.75rem;
}

.ind-name {
    font-weight: 500;
}

.ind-signal {
    font-size: 0.7rem;
    padding: 0.15rem 0.4rem;
    border-radius: 4px;
}

.ind-signal.bullish { background: rgba(var(--db-success-rgb), 0.2); color: var(--db-success); }
.ind-signal.bearish { background: rgba(var(--db-danger-rgb), 0.2); color: var(--db-danger); }
.ind-signal.neutral { background: rgba(var(--db-warning-rgb), 0.2); color: var(--db-warning); }
.ind-signal.oversold { background: rgba(var(--db-danger-rgb), 0.2); color: var(--db-danger); }
.ind-signal.overbought { background: rgba(var(--db-success-rgb), 0.2); color: var(--db-success); }

/* News Table */
.currency-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    background: rgba(var(--db-primary-rgb), 0.15);
    color: var(--db-primary);
    border-radius: 4px;
    font-weight: 600;
    font-size: 0.75rem;
}

.impact-badge {
    display: inline-flex;
    gap: 2px;
}

.impact-badge.high { color: var(--db-danger); }
.impact-badge.medium { color: var(--db-warning); }
.impact-badge.low { color: var(--db-success); }

.impact-badge i {
    font-size: 0.7rem;
}
</style>

<script>
function setNewsAlert(event) {
    showToast('Alert set for: ' + event, 'success');
}

// Auto refresh every 30 seconds
setInterval(() => {
    // In production, this would fetch new data via AJAX
    console.log('Refreshing sentiment data...');
}, 30000);
</script>

<?php require_once 'dashboard/includes/dashboard-footer.php'; ?>
