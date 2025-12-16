<?php
$page_title = 'Profit Calculator';
require_once 'includes/header.php';
?>

<section class="calculator-page">
    <div class="container py-4">
        <!-- Page Header -->
        <div class="text-center mb-4">
            <h1 class="page-title"><i class="fas fa-calculator text-primary"></i> Profit Calculator</h1>
            <p class="text-muted">Simulasikan potensi profit berdasarkan modal dan win rate</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Calculator Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-sliders-h"></i> Parameter Kalkulasi</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Modal Awal ($)</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="initialCapital" value="100" min="10" step="10">
                                </div>
                                <small class="text-muted">Minimum $10</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Trade Amount ($)</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="tradeAmount" value="5" min="1" step="1">
                                </div>
                                <small class="text-muted">Jumlah per trade</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Win Rate (%)</label>
                                <input type="range" class="form-range" id="winRate" min="50" max="95" value="75" step="1">
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">50%</small>
                                    <span class="badge bg-primary" id="winRateDisplay">75%</span>
                                    <small class="text-muted">95%</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Payout Rate (%)</label>
                                <input type="range" class="form-range" id="payoutRate" min="70" max="92" value="82" step="1">
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">70%</small>
                                    <span class="badge bg-info" id="payoutDisplay">82%</span>
                                    <small class="text-muted">92%</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Trades per Hari</label>
                                <input type="number" class="form-control" id="tradesPerDay" value="10" min="1" max="50">
                                <small class="text-muted">Rata-rata jumlah trade harian</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Hari Trading per Bulan</label>
                                <input type="number" class="form-control" id="tradingDays" value="22" min="1" max="30">
                                <small class="text-muted">Senin-Jumat = 22 hari</small>
                            </div>
                        </div>

                        <hr>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="compoundMode">
                            <label class="form-check-label" for="compoundMode">
                                <strong>Compound Mode</strong> - Reinvest profit ke trade berikutnya
                            </label>
                        </div>

                        <button class="btn btn-primary w-100" onclick="calculateProfit()">
                            <i class="fas fa-calculator"></i> Hitung Estimasi Profit
                        </button>
                    </div>
                </div>

                <!-- Results Card -->
                <div class="card mb-4" id="resultsCard" style="display: none;">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-line"></i> Hasil Kalkulasi</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <!-- Daily Results -->
                            <div class="col-md-4">
                                <div class="result-card text-center p-3 rounded bg-dark">
                                    <i class="fas fa-sun fa-2x text-warning mb-2"></i>
                                    <h6 class="text-muted">Per Hari</h6>
                                    <h3 class="text-success mb-0" id="dailyProfit">$0</h3>
                                    <small class="text-muted" id="dailyTrades">0 trades</small>
                                </div>
                            </div>
                            <!-- Weekly Results -->
                            <div class="col-md-4">
                                <div class="result-card text-center p-3 rounded bg-dark">
                                    <i class="fas fa-calendar-week fa-2x text-info mb-2"></i>
                                    <h6 class="text-muted">Per Minggu</h6>
                                    <h3 class="text-success mb-0" id="weeklyProfit">$0</h3>
                                    <small class="text-muted" id="weeklyROI">0% ROI</small>
                                </div>
                            </div>
                            <!-- Monthly Results -->
                            <div class="col-md-4">
                                <div class="result-card text-center p-3 rounded bg-dark">
                                    <i class="fas fa-calendar-alt fa-2x text-primary mb-2"></i>
                                    <h6 class="text-muted">Per Bulan</h6>
                                    <h3 class="text-success mb-0" id="monthlyProfit">$0</h3>
                                    <small class="text-muted" id="monthlyROI">0% ROI</small>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Detailed Breakdown -->
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-info-circle"></i> Detail Kalkulasi</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td class="text-muted">Total Trades/Bulan</td>
                                        <td class="text-end" id="totalTrades">0</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Estimasi Win</td>
                                        <td class="text-end text-success" id="totalWins">0</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Estimasi Loss</td>
                                        <td class="text-end text-danger" id="totalLosses">0</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Gross Profit</td>
                                        <td class="text-end text-success" id="grossProfit">$0</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Total Loss</td>
                                        <td class="text-end text-danger" id="totalLoss">$0</td>
                                    </tr>
                                    <tr class="table-success">
                                        <td><strong>Net Profit</strong></td>
                                        <td class="text-end"><strong id="netProfit">$0</strong></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-chart-pie"></i> Proyeksi Saldo</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td class="text-muted">Modal Awal</td>
                                        <td class="text-end" id="startBalance">$0</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Setelah 1 Bulan</td>
                                        <td class="text-end" id="balance1m">$0</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Setelah 3 Bulan</td>
                                        <td class="text-end" id="balance3m">$0</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Setelah 6 Bulan</td>
                                        <td class="text-end" id="balance6m">$0</td>
                                    </tr>
                                    <tr class="table-primary">
                                        <td><strong>Setelah 1 Tahun</strong></td>
                                        <td class="text-end"><strong id="balance1y">$0</strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Strategy Recommendations -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-lightbulb"></i> Rekomendasi Strategi</h5>
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
                                        <td class="text-success strategy-profit" data-winrate="90.5">-</td>
                                    </tr>
                                    <tr>
                                        <td><strong>NEXUS-WAVE</strong></td>
                                        <td><span class="badge bg-success">87%</span></td>
                                        <td><span class="badge bg-primary">VIP</span></td>
                                        <td class="text-success strategy-profit" data-winrate="87">-</td>
                                    </tr>
                                    <tr>
                                        <td><strong>STEALTH-MODE</strong></td>
                                        <td><span class="badge bg-info">81%</span></td>
                                        <td><span class="badge bg-warning text-dark">ELITE</span></td>
                                        <td class="text-success strategy-profit" data-winrate="81">-</td>
                                    </tr>
                                    <tr>
                                        <td><strong>QUANTUM-FLOW</strong></td>
                                        <td><span class="badge bg-success">80-90%</span></td>
                                        <td><span class="badge bg-primary">VIP</span></td>
                                        <td class="text-success strategy-profit" data-winrate="85">-</td>
                                    </tr>
                                    <tr>
                                        <td><strong>TITAN-PULSE</strong></td>
                                        <td><span class="badge bg-warning text-dark">73%</span></td>
                                        <td><span class="badge bg-info">PRO</span></td>
                                        <td class="text-success strategy-profit" data-winrate="73">-</td>
                                    </tr>
                                    <tr>
                                        <td><strong>BLITZ-SIGNAL</strong></td>
                                        <td><span class="badge bg-secondary">60-78%</span></td>
                                        <td><span class="badge bg-secondary">FREE</span></td>
                                        <td class="text-success strategy-profit" data-winrate="69">-</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <small class="text-muted">*Estimasi berdasarkan parameter yang Anda masukkan</small>
                    </div>
                </div>

                <!-- Disclaimer -->
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle"></i> Disclaimer Penting</h6>
                    <ul class="mb-0 small">
                        <li>Kalkulator ini hanya untuk <strong>simulasi dan edukasi</strong>.</li>
                        <li>Hasil aktual dapat berbeda dari estimasi karena kondisi market yang berubah.</li>
                        <li>Win rate historis tidak menjamin hasil di masa depan.</li>
                        <li>Trading mengandung risiko. Hanya gunakan dana yang Anda siap untuk kehilangan.</li>
                        <li>Compound mode mengasumsikan reinvestasi penuh, yang mungkin tidak realistis.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Update display values
document.getElementById('winRate').addEventListener('input', function() {
    document.getElementById('winRateDisplay').textContent = this.value + '%';
});

document.getElementById('payoutRate').addEventListener('input', function() {
    document.getElementById('payoutDisplay').textContent = this.value + '%';
});

function calculateProfit() {
    // Get values
    const capital = parseFloat(document.getElementById('initialCapital').value) || 100;
    const tradeAmount = parseFloat(document.getElementById('tradeAmount').value) || 5;
    const winRate = parseFloat(document.getElementById('winRate').value) / 100 || 0.75;
    const payoutRate = parseFloat(document.getElementById('payoutRate').value) / 100 || 0.82;
    const tradesPerDay = parseInt(document.getElementById('tradesPerDay').value) || 10;
    const tradingDays = parseInt(document.getElementById('tradingDays').value) || 22;
    const compoundMode = document.getElementById('compoundMode').checked;

    // Calculate per trade
    const winProfit = tradeAmount * payoutRate;
    const lossAmount = tradeAmount;

    // Calculate monthly
    const totalTrades = tradesPerDay * tradingDays;
    const wins = Math.round(totalTrades * winRate);
    const losses = totalTrades - wins;

    const grossProfit = wins * winProfit;
    const totalLoss = losses * lossAmount;
    const netProfit = grossProfit - totalLoss;

    // Daily/Weekly
    const dailyProfit = netProfit / tradingDays;
    const weeklyProfit = dailyProfit * 5;

    // ROI
    const monthlyROI = (netProfit / capital) * 100;
    const weeklyROI = monthlyROI / 4;

    // Compound projection
    let balance1m, balance3m, balance6m, balance1y;

    if (compoundMode) {
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

    document.getElementById('dailyProfit').textContent = formatCurrency(dailyProfit);
    document.getElementById('dailyTrades').textContent = tradesPerDay + ' trades';

    document.getElementById('weeklyProfit').textContent = formatCurrency(weeklyProfit);
    document.getElementById('weeklyROI').textContent = weeklyROI.toFixed(1) + '% ROI';

    document.getElementById('monthlyProfit').textContent = formatCurrency(netProfit);
    document.getElementById('monthlyROI').textContent = monthlyROI.toFixed(1) + '% ROI';

    document.getElementById('totalTrades').textContent = totalTrades;
    document.getElementById('totalWins').textContent = wins;
    document.getElementById('totalLosses').textContent = losses;
    document.getElementById('grossProfit').textContent = formatCurrency(grossProfit);
    document.getElementById('totalLoss').textContent = '-' + formatCurrency(totalLoss);
    document.getElementById('netProfit').textContent = formatCurrency(netProfit);

    document.getElementById('startBalance').textContent = formatCurrency(capital);
    document.getElementById('balance1m').textContent = formatCurrency(balance1m);
    document.getElementById('balance3m').textContent = formatCurrency(balance3m);
    document.getElementById('balance6m').textContent = formatCurrency(balance6m);
    document.getElementById('balance1y').textContent = formatCurrency(balance1y);

    // Update strategy recommendations
    updateStrategyProfits(tradeAmount, tradesPerDay, tradingDays, payoutRate);

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
    });
}

function formatCurrency(amount) {
    return '$' + amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Auto-calculate on page load
calculateProfit();
</script>

<?php require_once 'includes/footer.php'; ?>
