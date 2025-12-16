<?php
require_once 'includes/language.php';
$page_title = __('calc_title');
require_once 'includes/header.php';
?>

<section class="calculator-page">
    <div class="container py-4" style="padding-top: calc(var(--navbar-height) + 2rem) !important;">
        <!-- Page Header -->
        <div class="text-center mb-4">
            <h1 class="page-title"><i class="fas fa-calculator text-primary"></i> <?php _e('calc_title'); ?></h1>
            <p class="text-muted"><?php _e('calc_desc'); ?></p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Calculator Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-sliders-h"></i> <?php _e('calc_params'); ?></h5>
                    </div>
                    <div class="card-body">
                        <!-- Calculator Mode Selector -->
                        <div class="mb-4">
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="calcMode" id="modeStrategy" value="strategy" checked>
                                <label class="btn btn-outline-primary" for="modeStrategy">
                                    <i class="fas fa-chess me-2"></i>Berdasarkan Strategi
                                </label>
                                <input type="radio" class="btn-check" name="calcMode" id="modeManual" value="manual">
                                <label class="btn btn-outline-primary" for="modeManual">
                                    <i class="fas fa-edit me-2"></i>Target Manual
                                </label>
                            </div>
                        </div>

                        <!-- Strategy Mode -->
                        <div id="strategyMode">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label"><?php _e('calc_initial_capital'); ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="initialCapital" value="100" min="10" step="10">
                                    </div>
                                    <small class="text-muted">Minimum $10</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><?php _e('calc_trade_amount'); ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="tradeAmount" value="5" min="1" step="1">
                                    </div>
                                    <small class="text-muted">Jumlah per trade (disarankan 5% dari modal)</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><?php _e('calc_win_rate'); ?></label>
                                    <select class="form-select" id="strategySelect">
                                        <option value="91">ORACLE-PRIME (VIP) - 91%</option>
                                        <option value="87">NEXUS-WAVE (VIP) - 87%</option>
                                        <option value="85">QUANTUM-FLOW (VIP) - 85%</option>
                                        <option value="83">STEALTH-MODE (ELITE) - 83%</option>
                                        <option value="81">PHOENIX-SURGE (ELITE) - 81%</option>
                                        <option value="78">VORTEX-EDGE (ELITE) - 78%</option>
                                        <option value="75" selected>TITAN-PULSE (PRO) - 75%</option>
                                        <option value="73">SHADOW-STRIKE (PRO) - 73%</option>
                                        <option value="69">BLITZ-SIGNAL (FREE) - 69%</option>
                                        <option value="55">APEX-HUNTER (FREE) - 55%</option>
                                    </select>
                                    <div class="mt-2">
                                        <span class="badge bg-primary" id="winRateDisplay">Win Rate: 75%</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><?php _e('calc_payout'); ?></label>
                                    <input type="range" class="form-range" id="payoutRate" min="70" max="92" value="82" step="1">
                                    <div class="d-flex justify-content-between">
                                        <small class="text-muted">70%</small>
                                        <span class="badge bg-info" id="payoutDisplay">82%</span>
                                        <small class="text-muted">92%</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><?php _e('calc_trades_per_day'); ?></label>
                                    <input type="number" class="form-control" id="tradesPerDay" value="10" min="1" max="50">
                                    <small class="text-muted">Rata-rata jumlah trade harian</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><?php _e('calc_trading_days'); ?></label>
                                    <input type="number" class="form-control" id="tradingDays" value="22" min="1" max="30">
                                    <small class="text-muted">Senin-Jumat = 22 hari</small>
                                </div>
                            </div>
                        </div>

                        <!-- Manual Mode -->
                        <div id="manualMode" style="display: none;">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label"><?php _e('calc_initial_capital'); ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="manualCapital" value="100" min="10" step="10">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Target Profit Harian (%)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="dailyTarget" value="5" min="0.5" max="20" step="0.5">
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <small class="text-muted">Realistis: 3-10% per hari</small>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Hari Trading per Bulan</label>
                                    <input type="number" class="form-control" id="manualTradingDays" value="22" min="1" max="30">
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="compoundMode">
                            <label class="form-check-label" for="compoundMode">
                                <strong><?php _e('calc_compound_mode'); ?></strong> - <?php _e('calc_compound_desc'); ?>
                            </label>
                        </div>

                        <button class="btn btn-primary w-100 btn-lg" onclick="calculateProfit()">
                            <i class="fas fa-calculator"></i> <?php _e('calc_btn_calculate'); ?>
                        </button>
                    </div>
                </div>

                <!-- Results Card -->
                <div class="card mb-4" id="resultsCard" style="display: none;">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-line"></i> <?php _e('calc_results'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <!-- Daily Results -->
                            <div class="col-md-4">
                                <div class="result-card text-center p-3 rounded" style="background: rgba(var(--primary-rgb), 0.1); border: 1px solid var(--primary);">
                                    <i class="fas fa-sun fa-2x text-warning mb-2"></i>
                                    <h6 style="color: #c8c8d8;"><?php _e('calc_per_day'); ?></h6>
                                    <h3 class="mb-0" id="dailyProfit">$0</h3>
                                    <small style="color: #b8b8c8;" id="dailyTrades">0 trades</small>
                                </div>
                            </div>
                            <!-- Weekly Results -->
                            <div class="col-md-4">
                                <div class="result-card text-center p-3 rounded" style="background: rgba(var(--primary-rgb), 0.1); border: 1px solid var(--primary);">
                                    <i class="fas fa-calendar-week fa-2x text-info mb-2"></i>
                                    <h6 style="color: #c8c8d8;"><?php _e('calc_per_week'); ?></h6>
                                    <h3 class="mb-0" id="weeklyProfit">$0</h3>
                                    <small style="color: #b8b8c8;" id="weeklyROI">0% ROI</small>
                                </div>
                            </div>
                            <!-- Monthly Results -->
                            <div class="col-md-4">
                                <div class="result-card text-center p-3 rounded" style="background: rgba(var(--primary-rgb), 0.1); border: 1px solid var(--primary);">
                                    <i class="fas fa-calendar-alt fa-2x text-primary mb-2"></i>
                                    <h6 style="color: #c8c8d8;"><?php _e('calc_per_month'); ?></h6>
                                    <h3 class="mb-0" id="monthlyProfit">$0</h3>
                                    <small style="color: #b8b8c8;" id="monthlyROI">0% ROI</small>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Detailed Breakdown -->
                        <div class="row">
                            <div class="col-md-6">
                                <h6 style="color: #ffffff;"><i class="fas fa-info-circle"></i> <?php _e('calc_detail'); ?></h6>
                                <table class="table table-sm" style="color: #c8c8d8;">
                                    <tr>
                                        <td><?php _e('calc_total_trades'); ?></td>
                                        <td class="text-end" id="totalTrades">0</td>
                                    </tr>
                                    <tr>
                                        <td><?php _e('calc_estimate_win'); ?></td>
                                        <td class="text-end text-success" id="totalWins">0</td>
                                    </tr>
                                    <tr>
                                        <td><?php _e('calc_estimate_loss'); ?></td>
                                        <td class="text-end text-danger" id="totalLosses">0</td>
                                    </tr>
                                    <tr>
                                        <td><?php _e('calc_gross_profit'); ?></td>
                                        <td class="text-end text-success" id="grossProfit">$0</td>
                                    </tr>
                                    <tr>
                                        <td><?php _e('calc_total_loss'); ?></td>
                                        <td class="text-end text-danger" id="totalLoss">$0</td>
                                    </tr>
                                    <tr style="background: rgba(var(--primary-rgb), 0.1);">
                                        <td><strong style="color: #ffffff;"><?php _e('calc_net_profit'); ?></strong></td>
                                        <td class="text-end"><strong id="netProfit" class="net-profit-value">$0</strong></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 style="color: #ffffff;"><i class="fas fa-chart-pie"></i> <?php _e('calc_projection'); ?></h6>
                                <table class="table table-sm" style="color: #c8c8d8;">
                                    <tr>
                                        <td><?php _e('calc_initial'); ?></td>
                                        <td class="text-end" id="startBalance">$0</td>
                                    </tr>
                                    <tr>
                                        <td><?php _e('calc_after_1m'); ?></td>
                                        <td class="text-end" id="balance1m">$0</td>
                                    </tr>
                                    <tr>
                                        <td><?php _e('calc_after_3m'); ?></td>
                                        <td class="text-end" id="balance3m">$0</td>
                                    </tr>
                                    <tr>
                                        <td><?php _e('calc_after_6m'); ?></td>
                                        <td class="text-end" id="balance6m">$0</td>
                                    </tr>
                                    <tr style="background: rgba(var(--primary-rgb), 0.1);">
                                        <td><strong style="color: #ffffff;"><?php _e('calc_after_1y'); ?></strong></td>
                                        <td class="text-end"><strong id="balance1y" class="projection-value">$0</strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Strategy Recommendations -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-lightbulb"></i> <?php _e('calc_recommendations'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Strategi</th>
                                        <th>Win Rate</th>
                                        <th>Tier</th>
                                        <th>Estimasi Profit/Bulan*</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>ORACLE-PRIME</strong></td>
                                        <td><span class="badge bg-success">90-91%</span></td>
                                        <td><span class="badge bg-primary">VIP</span></td>
                                        <td class="strategy-profit" data-winrate="90.5">-</td>
                                    </tr>
                                    <tr>
                                        <td><strong>NEXUS-WAVE</strong></td>
                                        <td><span class="badge bg-success">87%</span></td>
                                        <td><span class="badge bg-primary">VIP</span></td>
                                        <td class="strategy-profit" data-winrate="87">-</td>
                                    </tr>
                                    <tr>
                                        <td><strong>STEALTH-MODE</strong></td>
                                        <td><span class="badge bg-info">81-83%</span></td>
                                        <td><span class="badge bg-warning text-dark">ELITE</span></td>
                                        <td class="strategy-profit" data-winrate="82">-</td>
                                    </tr>
                                    <tr>
                                        <td><strong>QUANTUM-FLOW</strong></td>
                                        <td><span class="badge bg-success">80-90%</span></td>
                                        <td><span class="badge bg-primary">VIP</span></td>
                                        <td class="strategy-profit" data-winrate="85">-</td>
                                    </tr>
                                    <tr>
                                        <td><strong>TITAN-PULSE</strong></td>
                                        <td><span class="badge bg-warning text-dark">73-75%</span></td>
                                        <td><span class="badge bg-info">PRO</span></td>
                                        <td class="strategy-profit" data-winrate="74">-</td>
                                    </tr>
                                    <tr>
                                        <td><strong>BLITZ-SIGNAL</strong></td>
                                        <td><span class="badge bg-secondary">60-78%</span></td>
                                        <td><span class="badge bg-secondary">FREE</span></td>
                                        <td class="strategy-profit" data-winrate="69">-</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <small class="text-muted">*Estimasi berdasarkan parameter yang Anda masukkan</small>
                    </div>
                </div>

                <!-- Disclaimer -->
                <div class="alert" style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); color: #c8c8d8;">
                    <h6 style="color: #f59e0b;"><i class="fas fa-exclamation-triangle"></i> <?php _e('calc_disclaimer_title'); ?></h6>
                    <ul class="mb-0 small">
                        <?php
                        $disclaimerItems = __('calc_disclaimer_items');
                        if (is_array($disclaimerItems)) {
                            foreach ($disclaimerItems as $item): ?>
                        <li><?php echo $item; ?></li>
                        <?php endforeach;
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.calculator-page .card {
    border-radius: 16px;
}

.calculator-page .result-card {
    transition: all 0.3s ease;
}

.calculator-page .result-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 20px rgba(0, 212, 255, 0.2);
}

/* Net Profit & Projection Colors */
.net-profit-value.positive {
    color: #10b981 !important;
}

.net-profit-value.negative {
    color: #ef4444 !important;
}

.projection-value.positive {
    color: #10b981 !important;
}

.projection-value.negative {
    color: #ef4444 !important;
}

/* Result Card Colors */
#dailyProfit.positive, #weeklyProfit.positive, #monthlyProfit.positive {
    color: #10b981;
}

#dailyProfit.negative, #weeklyProfit.negative, #monthlyProfit.negative {
    color: #ef4444;
}

/* Strategy Profit Colors */
.strategy-profit.positive {
    color: #10b981;
}

.strategy-profit.negative {
    color: #ef4444;
}
</style>

<script>
// Calculator Mode Switch
document.querySelectorAll('input[name="calcMode"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        if (this.value === 'manual') {
            document.getElementById('strategyMode').style.display = 'none';
            document.getElementById('manualMode').style.display = 'block';
        } else {
            document.getElementById('strategyMode').style.display = 'block';
            document.getElementById('manualMode').style.display = 'none';
        }
    });
});

// Update display values
document.getElementById('strategySelect').addEventListener('change', function() {
    document.getElementById('winRateDisplay').textContent = 'Win Rate: ' + this.value + '%';
});

document.getElementById('payoutRate').addEventListener('input', function() {
    document.getElementById('payoutDisplay').textContent = this.value + '%';
});

function calculateProfit() {
    const isManualMode = document.getElementById('modeManual').checked;
    let capital, netProfit, tradingDays;

    if (isManualMode) {
        // Manual mode calculation
        capital = parseFloat(document.getElementById('manualCapital').value) || 100;
        const dailyTarget = parseFloat(document.getElementById('dailyTarget').value) / 100 || 0.05;
        tradingDays = parseInt(document.getElementById('manualTradingDays').value) || 22;

        const dailyProfit = capital * dailyTarget;
        netProfit = dailyProfit * tradingDays;

        // Update UI for manual mode
        document.getElementById('dailyTrades').textContent = 'Target: ' + (dailyTarget * 100).toFixed(1) + '%/hari';
        document.getElementById('totalTrades').textContent = tradingDays + ' hari trading';
        document.getElementById('totalWins').textContent = '-';
        document.getElementById('totalLosses').textContent = '-';
        document.getElementById('grossProfit').textContent = '-';
        document.getElementById('totalLoss').textContent = '-';

    } else {
        // Strategy mode calculation
        capital = parseFloat(document.getElementById('initialCapital').value) || 100;
        const tradeAmount = parseFloat(document.getElementById('tradeAmount').value) || 5;
        const winRate = parseFloat(document.getElementById('strategySelect').value) / 100 || 0.75;
        const payoutRate = parseFloat(document.getElementById('payoutRate').value) / 100 || 0.82;
        const tradesPerDay = parseInt(document.getElementById('tradesPerDay').value) || 10;
        tradingDays = parseInt(document.getElementById('tradingDays').value) || 22;

        // Calculate per trade
        const winProfit = tradeAmount * payoutRate;
        const lossAmount = tradeAmount;

        // Calculate monthly
        const totalTrades = tradesPerDay * tradingDays;
        const wins = Math.round(totalTrades * winRate);
        const losses = totalTrades - wins;

        const grossProfit = wins * winProfit;
        const totalLoss = losses * lossAmount;
        netProfit = grossProfit - totalLoss;

        // Update detail UI
        document.getElementById('dailyTrades').textContent = tradesPerDay + ' trades';
        document.getElementById('totalTrades').textContent = totalTrades;
        document.getElementById('totalWins').textContent = wins;
        document.getElementById('totalLosses').textContent = losses;
        document.getElementById('grossProfit').textContent = formatCurrency(grossProfit);
        document.getElementById('totalLoss').textContent = '-' + formatCurrency(totalLoss);

        // Update strategy recommendations
        updateStrategyProfits(tradeAmount, tradesPerDay, tradingDays, payoutRate);
    }

    const compoundMode = document.getElementById('compoundMode').checked;

    // Daily/Weekly
    const dailyProfit = netProfit / tradingDays;
    const weeklyProfit = dailyProfit * 5;

    // ROI
    const monthlyROI = (netProfit / capital) * 100;
    const weeklyROI = monthlyROI / 4;

    // Compound projection
    let balance1m, balance3m, balance6m, balance1y;

    if (compoundMode && netProfit > 0) {
        // Compound calculation (simplified monthly compounding)
        const monthlyReturn = 1 + (netProfit / capital);
        balance1m = capital * monthlyReturn;
        balance3m = capital * Math.pow(monthlyReturn, 3);
        balance6m = capital * Math.pow(monthlyReturn, 6);
        balance1y = capital * Math.pow(monthlyReturn, 12);
    } else {
        // Flat calculation
        balance1m = capital + netProfit;
        balance3m = capital + (netProfit * 3);
        balance6m = capital + (netProfit * 6);
        balance1y = capital + (netProfit * 12);
    }

    // Update UI
    document.getElementById('resultsCard').style.display = 'block';

    // Daily Profit
    const dailyEl = document.getElementById('dailyProfit');
    dailyEl.textContent = formatCurrency(dailyProfit);
    dailyEl.className = dailyProfit >= 0 ? 'mb-0 positive' : 'mb-0 negative';

    // Weekly Profit
    const weeklyEl = document.getElementById('weeklyProfit');
    weeklyEl.textContent = formatCurrency(weeklyProfit);
    weeklyEl.className = weeklyProfit >= 0 ? 'mb-0 positive' : 'mb-0 negative';
    document.getElementById('weeklyROI').textContent = weeklyROI.toFixed(1) + '% ROI';

    // Monthly Profit
    const monthlyEl = document.getElementById('monthlyProfit');
    monthlyEl.textContent = formatCurrency(netProfit);
    monthlyEl.className = netProfit >= 0 ? 'mb-0 positive' : 'mb-0 negative';
    document.getElementById('monthlyROI').textContent = monthlyROI.toFixed(1) + '% ROI';

    // Net Profit with color
    const netProfitEl = document.getElementById('netProfit');
    netProfitEl.textContent = formatCurrency(netProfit);
    netProfitEl.className = netProfit >= 0 ? 'net-profit-value positive' : 'net-profit-value negative';

    // Balance projections with color
    document.getElementById('startBalance').textContent = formatCurrency(capital);

    const bal1mEl = document.getElementById('balance1m');
    bal1mEl.textContent = formatCurrency(balance1m);
    bal1mEl.style.color = balance1m >= capital ? '#10b981' : '#ef4444';

    const bal3mEl = document.getElementById('balance3m');
    bal3mEl.textContent = formatCurrency(balance3m);
    bal3mEl.style.color = balance3m >= capital ? '#10b981' : '#ef4444';

    const bal6mEl = document.getElementById('balance6m');
    bal6mEl.textContent = formatCurrency(balance6m);
    bal6mEl.style.color = balance6m >= capital ? '#10b981' : '#ef4444';

    const bal1yEl = document.getElementById('balance1y');
    bal1yEl.textContent = formatCurrency(balance1y);
    bal1yEl.className = balance1y >= capital ? 'projection-value positive' : 'projection-value negative';

    // Scroll to results
    document.getElementById('resultsCard').scrollIntoView({ behavior: 'smooth' });
}

function updateStrategyProfits(tradeAmount, tradesPerDay, tradingDays, payoutRate) {
    const strategyProfits = document.querySelectorAll('.strategy-profit');

    strategyProfits.forEach(function(cell) {
        const winRate = parseFloat(cell.dataset.winrate) / 100;
        const totalTrades = tradesPerDay * tradingDays;
        const wins = Math.round(totalTrades * winRate);
        const losses = totalTrades - wins;
        const profit = (wins * tradeAmount * payoutRate) - (losses * tradeAmount);
        cell.textContent = formatCurrency(profit);
        cell.className = profit >= 0 ? 'strategy-profit positive' : 'strategy-profit negative';
    });
}

function formatCurrency(amount) {
    const prefix = amount >= 0 ? '$' : '-$';
    return prefix + Math.abs(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Auto-calculate on page load
calculateProfit();
</script>

<?php require_once 'includes/footer.php'; ?>
