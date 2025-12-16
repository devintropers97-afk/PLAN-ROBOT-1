<?php
$page_title = 'Leaderboard';
require_once 'includes/header.php';

// Get period from query string
$period = $_GET['period'] ?? 'monthly';
$country = $_GET['country'] ?? null;

// Valid periods
$validPeriods = ['daily', 'weekly', 'monthly'];
if (!in_array($period, $validPeriods)) {
    $period = 'monthly';
}

// Get leaderboard data
$leaderboardData = getLeaderboard($period, $country, 50);

// Get current user's rank if logged in
$userRank = null;
if (isLoggedIn()) {
    $userRank = getUserRank($_SESSION['user_id'], $period);
}

// Get list of countries for filter
$countries = getActiveCountries();
?>

<section class="leaderboard-page">
    <div class="container py-4">
        <!-- Page Header -->
        <div class="page-header text-center mb-4">
            <h1 class="page-title">
                <i class="fas fa-trophy text-warning"></i> Leaderboard
            </h1>
            <p class="text-muted">Top performers dari ZYN Trade System</p>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="btn-group w-100" role="group">
                            <a href="?period=daily<?php echo $country ? '&country=' . $country : ''; ?>"
                               class="btn <?php echo $period === 'daily' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                <i class="fas fa-calendar-day"></i> Hari Ini
                            </a>
                            <a href="?period=weekly<?php echo $country ? '&country=' . $country : ''; ?>"
                               class="btn <?php echo $period === 'weekly' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                <i class="fas fa-calendar-week"></i> Minggu Ini
                            </a>
                            <a href="?period=monthly<?php echo $country ? '&country=' . $country : ''; ?>"
                               class="btn <?php echo $period === 'monthly' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                <i class="fas fa-calendar-alt"></i> Bulan Ini
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <select class="form-select" id="countryFilter" onchange="filterByCountry(this.value)">
                            <option value="">Semua Negara</option>
                            <?php foreach ($countries as $c): ?>
                            <option value="<?php echo htmlspecialchars($c['country']); ?>"
                                    <?php echo $country === $c['country'] ? 'selected' : ''; ?>>
                                <?php echo getCountryFlag($c['country']); ?> <?php echo htmlspecialchars($c['country']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Your Rank Card (if logged in) -->
        <?php if (isLoggedIn() && $userRank): ?>
        <div class="card mb-4 border-primary">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div class="rank-badge large <?php echo getRankClass($userRank['rank']); ?>">
                            #<?php echo $userRank['rank']; ?>
                        </div>
                    </div>
                    <div class="col">
                        <h5 class="mb-0">Peringkat Anda</h5>
                        <small class="text-muted"><?php echo ucfirst($period); ?> ranking</small>
                    </div>
                    <div class="col-auto text-end">
                        <div class="stat-item">
                            <span class="stat-value text-success">+$<?php echo number_format($userRank['total_profit'], 2); ?></span>
                            <span class="stat-label">Profit</span>
                        </div>
                    </div>
                    <div class="col-auto text-end">
                        <div class="stat-item">
                            <span class="stat-value"><?php echo number_format($userRank['win_rate'], 1); ?>%</span>
                            <span class="stat-label">Win Rate</span>
                        </div>
                    </div>
                    <div class="col-auto text-end">
                        <div class="stat-item">
                            <span class="stat-value"><?php echo $userRank['total_trades']; ?></span>
                            <span class="stat-label">Trades</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Leaderboard Table -->
        <div class="card">
            <div class="card-body p-0">
                <?php if (empty($leaderboardData)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-trophy fa-3x text-muted mb-3"></i>
                    <h5>Belum Ada Data</h5>
                    <p class="text-muted">Belum ada trading activity untuk periode ini.</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center" width="80">Rank</th>
                                <th>Trader</th>
                                <th class="text-center">Negara</th>
                                <th class="text-end">Profit</th>
                                <th class="text-center">Win Rate</th>
                                <th class="text-center">Trades</th>
                                <th class="text-center">Package</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leaderboardData as $index => $trader): ?>
                            <tr class="<?php echo ($userRank && $trader['user_id'] == $_SESSION['user_id']) ? 'table-primary' : ''; ?>">
                                <td class="text-center">
                                    <span class="rank-badge <?php echo getRankClass($index + 1); ?>">
                                        <?php if ($index < 3): ?>
                                            <?php echo ['🥇', '🥈', '🥉'][$index]; ?>
                                        <?php else: ?>
                                            #<?php echo $index + 1; ?>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-2">
                                            <span class="avatar-text"><?php echo strtoupper(substr($trader['username'], 0, 2)); ?></span>
                                        </div>
                                        <div>
                                            <strong><?php echo maskUsername($trader['username']); ?></strong>
                                            <?php if ($trader['package'] === 'vip'): ?>
                                            <span class="badge bg-warning text-dark ms-1"><i class="fas fa-crown"></i></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span title="<?php echo htmlspecialchars($trader['country']); ?>">
                                        <?php echo getCountryFlag($trader['country']); ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="<?php echo $trader['total_profit'] >= 0 ? 'text-success' : 'text-danger'; ?> fw-bold">
                                        <?php echo $trader['total_profit'] >= 0 ? '+' : ''; ?>$<?php echo number_format($trader['total_profit'], 2); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?php echo getWinRateBadgeClass($trader['win_rate']); ?>">
                                        <?php echo number_format($trader['win_rate'], 1); ?>%
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php echo $trader['total_trades']; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-<?php echo getPackageBadgeColor($trader['package']); ?>">
                                        <?php echo strtoupper($trader['package']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Legend -->
        <div class="card mt-4">
            <div class="card-body">
                <h6 class="mb-3"><i class="fas fa-info-circle"></i> Keterangan</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center mb-2">
                            <span class="rank-badge gold me-2">🥇</span>
                            <span>Top 1 - Champion</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="rank-badge silver me-2">🥈</span>
                            <span>Top 2 - Runner Up</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="rank-badge bronze me-2">🥉</span>
                            <span>Top 3 - Third Place</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <p class="small text-muted mb-1"><i class="fas fa-shield-alt"></i> Username disamarkan untuk privasi</p>
                        <p class="small text-muted mb-1"><i class="fas fa-sync"></i> Update setiap jam</p>
                        <p class="small text-muted mb-0"><i class="fas fa-filter"></i> Filter by period & country</p>
                    </div>
                    <div class="col-md-4">
                        <p class="small text-muted mb-1"><strong>Win Rate Badge:</strong></p>
                        <span class="badge bg-success me-1">80%+</span>
                        <span class="badge bg-info me-1">70-79%</span>
                        <span class="badge bg-warning text-dark me-1">60-69%</span>
                        <span class="badge bg-secondary">&lt;60%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA for non-logged in -->
        <?php if (!isLoggedIn()): ?>
        <div class="card mt-4 bg-gradient-primary text-white">
            <div class="card-body text-center py-4">
                <h4>Ingin masuk Leaderboard?</h4>
                <p class="mb-3">Daftar sekarang dan mulai trading dengan ZYN Trade System!</p>
                <a href="register.php" class="btn btn-light btn-lg">
                    <i class="fas fa-rocket"></i> Mulai Gratis
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<style>
.rank-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    font-weight: bold;
    font-size: 0.9rem;
    background: var(--card-bg);
    border: 2px solid var(--border-color);
}
.rank-badge.large {
    width: 60px;
    height: 60px;
    font-size: 1.2rem;
}
.rank-badge.gold {
    background: linear-gradient(135deg, #ffd700, #ffed4a);
    border-color: #ffd700;
    color: #000;
}
.rank-badge.silver {
    background: linear-gradient(135deg, #c0c0c0, #e8e8e8);
    border-color: #c0c0c0;
    color: #000;
}
.rank-badge.bronze {
    background: linear-gradient(135deg, #cd7f32, #daa06d);
    border-color: #cd7f32;
    color: #fff;
}
.avatar-sm {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
}
.avatar-text {
    color: white;
    font-weight: bold;
    font-size: 0.8rem;
}
.stat-item {
    display: flex;
    flex-direction: column;
}
.stat-value {
    font-size: 1.1rem;
    font-weight: bold;
}
.stat-label {
    font-size: 0.75rem;
    color: var(--text-muted);
}
.bg-gradient-primary {
    background: linear-gradient(135deg, var(--primary-color), #0066cc);
}
</style>

<script>
function filterByCountry(country) {
    const period = '<?php echo $period; ?>';
    let url = '?period=' + period;
    if (country) {
        url += '&country=' + encodeURIComponent(country);
    }
    window.location.href = url;
}
</script>

<?php require_once 'includes/footer.php'; ?>
