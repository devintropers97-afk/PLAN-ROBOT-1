<?php
$page_title = 'Trading Strategies';
require_once 'includes/header.php';

$strategies = getAllStrategies();
?>

<section class="section" style="padding-top: calc(var(--navbar-height) + 3rem);">
    <div class="container">
        <div class="section-header fade-in">
            <span class="section-badge">ZYN Quant Logic</span>
            <h1 class="section-title">10 Powerful Trading Strategies</h1>
            <p class="section-desc">
                Setiap strategi dirancang algoritmik untuk kondisi market tertentu.
                Pilih yang sesuai dengan gaya trading kamu!
            </p>
            <!-- Frequency Legend -->
            <div class="frequency-legend mt-3">
                <span class="badge bg-danger me-2">🔥 SERING = 15-30 signal/hari</span>
                <span class="badge bg-primary me-2">⚡ SEDANG = 5-15 signal/hari</span>
                <span class="badge bg-info">💎 JARANG = 1-5 signal/hari (MOMENTUM TERBAIK!)</span>
            </div>
        </div>

        <div class="row g-4">
            <?php foreach ($strategies as $strategy):
                $riskClass = 'risk-medium';
                if (strpos(strtolower($strategy['risk']), 'low') !== false) $riskClass = 'risk-low';
                elseif (strpos(strtolower($strategy['risk']), 'high') !== false) $riskClass = 'risk-high';
                if (strpos(strtolower($strategy['risk']), 'very') !== false) $riskClass = 'risk-very-high';

                $tierColor = 'secondary';
                $tierIcon = '';
                if ($strategy['tier'] === 'VIP') { $tierColor = 'warning'; $tierIcon = '👑'; }
                elseif ($strategy['tier'] === 'ELITE') { $tierColor = 'info'; $tierIcon = '💎'; }
                elseif ($strategy['tier'] === 'PRO') { $tierColor = 'primary'; $tierIcon = '💎'; }
                else { $tierColor = 'success'; $tierIcon = '🆓'; }

                $frequencyClass = getFrequencyClass($strategy['frequency'] ?? 'medium');
            ?>
            <div class="col-lg-6 fade-in">
                <div class="card strategy-card h-100 <?php echo $frequencyClass; ?>">
                    <?php if (!empty($strategy['badge'])): ?>
                    <div class="strategy-badge-ribbon bg-<?php echo $strategy['badge_color']; ?>">
                        <?php echo $strategy['badge']; ?>
                    </div>
                    <?php endif; ?>

                    <div class="card-body">
                        <div class="strategy-header">
                            <div>
                                <span class="badge bg-<?php echo $tierColor; ?> mb-2"><?php echo $tierIcon; ?> <?php echo $strategy['tier']; ?></span>
                                <h3 class="strategy-name"><?php echo $strategy['name']; ?></h3>
                                <small class="text-muted"><?php echo $strategy['subtitle'] ?? ''; ?></small>
                            </div>
                            <div class="strategy-winrate">
                                <i class="fas fa-chart-line"></i>
                                <?php echo $strategy['win_rate']; ?>
                            </div>
                        </div>

                        <!-- Rating & Users -->
                        <div class="strategy-social mt-2">
                            <span class="rating">
                                <?php echo renderStarRating($strategy['rating'] ?? 4.5); ?>
                                <span class="rating-value"><?php echo number_format($strategy['rating'] ?? 4.5, 1); ?></span>
                            </span>
                            <span class="users text-muted ms-2">
                                <i class="fas fa-users"></i> <?php echo $strategy['users'] ?? 100; ?> users
                            </span>
                        </div>

                        <!-- Frequency Label -->
                        <div class="frequency-badge mt-2">
                            <span class="badge <?php
                                echo $strategy['frequency'] === 'rare' ? 'bg-info' :
                                    ($strategy['frequency'] === 'frequent' ? 'bg-danger' : 'bg-primary');
                            ?>">
                                <?php echo $strategy['frequency_label'] ?? '⚡ SEDANG'; ?>
                            </span>
                            <small class="text-muted ms-1"><?php echo $strategy['frequency_desc'] ?? '5-15 signal/hari'; ?></small>
                        </div>

                        <p class="text-muted mt-3"><?php echo $strategy['description']; ?></p>

                        <div class="mt-3">
                            <div class="d-flex flex-wrap gap-2">
                                <span class="strategy-risk <?php echo $riskClass; ?>"><?php echo $strategy['risk']; ?> Risk</span>
                                <?php foreach ($strategy['indicators'] as $indicator): ?>
                                <span class="badge bg-dark"><?php echo $indicator; ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top" style="border-color: var(--border-color) !important;">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="text-muted small">Timeframe</div>
                                    <div class="fw-bold"><?php echo $strategy['best_timeframe']; ?></div>
                                </div>
                                <div class="col-4">
                                    <div class="text-muted small">Assets</div>
                                    <div class="fw-bold">EUR/USD, GBP/USD</div>
                                </div>
                                <div class="col-4">
                                    <div class="text-muted small">Signals/Day</div>
                                    <div class="fw-bold"><?php echo $strategy['signals_per_day'] ?? '5-15'; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Strategy Comparison -->
        <div class="mt-5 fade-in">
            <h2 class="section-title text-center mb-4">📊 Strategy Comparison</h2>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Strategy</th>
                            <th>Win Rate</th>
                            <th>Frequency</th>
                            <th>Risk</th>
                            <th>Rating</th>
                            <th>Package</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($strategies as $strategy):
                            $riskClass = 'risk-medium';
                            if (strpos(strtolower($strategy['risk']), 'low') !== false) $riskClass = 'risk-low';
                            elseif (strpos(strtolower($strategy['risk']), 'high') !== false) $riskClass = 'risk-high';

                            $tierColor = 'secondary';
                            if ($strategy['tier'] === 'VIP') $tierColor = 'warning';
                            elseif ($strategy['tier'] === 'ELITE') $tierColor = 'info';
                            elseif ($strategy['tier'] === 'PRO') $tierColor = 'primary';
                            else $tierColor = 'success';
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo $strategy['name']; ?></strong>
                                <?php if (!empty($strategy['badge'])): ?>
                                <span class="badge bg-<?php echo $strategy['badge_color']; ?> ms-1"><?php echo $strategy['badge']; ?></span>
                                <?php endif; ?>
                                <br><small class="text-muted"><?php echo $strategy['subtitle']; ?></small>
                            </td>
                            <td class="text-success fw-bold"><?php echo $strategy['win_rate']; ?></td>
                            <td>
                                <span class="badge <?php
                                    echo $strategy['frequency'] === 'rare' ? 'bg-info' :
                                        ($strategy['frequency'] === 'frequent' ? 'bg-danger' : 'bg-primary');
                                ?>"><?php echo $strategy['frequency_label']; ?></span>
                            </td>
                            <td><span class="badge <?php echo $riskClass; ?>"><?php echo $strategy['risk']; ?></span></td>
                            <td>
                                <?php echo renderStarRating($strategy['rating']); ?>
                                <small>(<?php echo $strategy['users']; ?>)</small>
                            </td>
                            <td><span class="badge bg-<?php echo $tierColor; ?>"><?php echo $strategy['tier']; ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- CTA -->
        <div class="text-center mt-5 fade-in">
            <h3 class="mb-3">🚀 Siap Trading Lebih Cerdas?</h3>
            <p class="text-muted mb-4">
                "Kenapa capek trading manual? Biarkan robot yang kerja, kamu tinggal cek profit" 💰
            </p>
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="register.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-rocket"></i> Daftar GRATIS + Dapat Robot! 🎁
                </a>
                <a href="pricing.php" class="btn btn-secondary btn-lg">
                    Lihat Paket Premium
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Risk Note -->
<section class="py-4 bg-darker">
    <div class="container">
        <div class="alert alert-warning mb-0">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Disclaimer:</strong> Performa masa lalu tidak menjamin hasil di masa depan. Win rate berdasarkan backtest historis dan dapat bervariasi dalam kondisi live trading.
        </div>
    </div>
</section>

<style>
.strategy-badge-ribbon {
    position: absolute;
    top: 15px;
    right: -5px;
    padding: 5px 15px 5px 10px;
    font-size: 0.75rem;
    font-weight: 700;
    color: #fff;
    border-radius: 3px 0 0 3px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    z-index: 10;
}

.strategy-badge-ribbon::after {
    content: '';
    position: absolute;
    right: 0;
    bottom: -5px;
    border-left: 5px solid transparent;
    border-top: 5px solid rgba(0,0,0,0.3);
}

.strategy-card {
    position: relative;
    overflow: visible;
}

.strategy-social .rating {
    display: inline-flex;
    align-items: center;
    gap: 3px;
}

.strategy-social .rating-value {
    font-weight: 600;
    margin-left: 5px;
    color: var(--warning);
}

.strategy-social .users {
    font-size: 0.85rem;
}

.frequency-rare {
    border-left: 4px solid var(--info) !important;
}

.frequency-medium {
    border-left: 4px solid var(--primary) !important;
}

.frequency-frequent {
    border-left: 4px solid var(--danger) !important;
}

.frequency-legend {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}

@media (max-width: 768px) {
    .frequency-legend {
        flex-direction: column;
        align-items: center;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>
