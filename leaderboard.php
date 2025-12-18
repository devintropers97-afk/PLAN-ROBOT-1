<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/language.php';

$page_title = __('leaderboard_title');
require_once 'includes/header.php';
require_once 'includes/badges.php';

// Get view mode (ranking or badges)
$view = $_GET['view'] ?? 'ranking';

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

// Get badge leaderboard if in badges view
$badgeLeaderboard = [];
if ($view === 'badges') {
    $badgeLeaderboard = BadgeSystem::getBadgeLeaderboard(50);
}

// Get current user's badges if logged in
$userBadges = [];
$userBadgePoints = 0;
$userBadgeRank = null;
if (isLoggedIn()) {
    $userBadges = BadgeSystem::getUserBadges($_SESSION['user_id']);
    $userBadgePoints = BadgeSystem::getTotalPoints($_SESSION['user_id']);
    $userBadgeRank = BadgeSystem::getBadgeRank($userBadgePoints);

    // Check for new badges
    BadgeSystem::checkAndAward($_SESSION['user_id']);
}
?>
<?php echo render_badge_styles(); ?>

<section class="leaderboard-page">
    <div class="container py-4">
        <!-- Page Header -->
        <div class="page-header text-center mb-4">
            <h1 class="page-title">
                <i class="fas fa-trophy text-warning"></i> <?php _e('leaderboard_title'); ?>
            </h1>
            <p class="text-muted"><?php _e('leaderboard_subtitle'); ?></p>
        </div>

        <!-- View Toggle -->
        <div class="view-toggle mb-4">
            <div class="btn-group w-100" role="group" style="max-width: 400px; margin: 0 auto; display: flex;">
                <a href="?view=ranking&period=<?php echo $period; ?><?php echo $country ? '&country=' . $country : ''; ?>"
                   class="btn <?php echo $view === 'ranking' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                    <i class="fas fa-chart-line"></i> <?php _e('leaderboard_profit_ranking'); ?>
                </a>
                <a href="?view=badges"
                   class="btn <?php echo $view === 'badges' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                    <i class="fas fa-award"></i> <?php _e('leaderboard_badge_ranking'); ?>
                </a>
            </div>
        </div>

        <?php if ($view === 'ranking'): ?>
        <!-- PROFIT RANKING VIEW -->

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="btn-group w-100" role="group">
                            <a href="?period=daily<?php echo $country ? '&country=' . $country : ''; ?>"
                               class="btn <?php echo $period === 'daily' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                <i class="fas fa-calendar-day"></i> <?php _e('leaderboard_today'); ?>
                            </a>
                            <a href="?period=weekly<?php echo $country ? '&country=' . $country : ''; ?>"
                               class="btn <?php echo $period === 'weekly' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                <i class="fas fa-calendar-week"></i> <?php _e('leaderboard_this_week'); ?>
                            </a>
                            <a href="?period=monthly<?php echo $country ? '&country=' . $country : ''; ?>"
                               class="btn <?php echo $period === 'monthly' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                <i class="fas fa-calendar-alt"></i> <?php _e('leaderboard_this_month'); ?>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <select class="form-select" id="countryFilter" onchange="filterByCountry(this.value)">
                            <option value=""><?php _e('leaderboard_all_countries'); ?></option>
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
                        <h5 class="mb-0"><?php _e('leaderboard_your_rank'); ?></h5>
                        <small class="text-muted"><?php echo ucfirst($period); ?> <?php _e('leaderboard_ranking'); ?></small>
                    </div>
                    <div class="col-auto text-end">
                        <div class="stat-item">
                            <span class="stat-value text-success">+$<?php echo number_format($userRank['total_profit'], 2); ?></span>
                            <span class="stat-label"><?php _e('leaderboard_profit'); ?></span>
                        </div>
                    </div>
                    <div class="col-auto text-end">
                        <div class="stat-item">
                            <span class="stat-value"><?php echo number_format($userRank['win_rate'], 1); ?>%</span>
                            <span class="stat-label"><?php _e('stats_win_rate'); ?></span>
                        </div>
                    </div>
                    <div class="col-auto text-end">
                        <div class="stat-item">
                            <span class="stat-value"><?php echo $userRank['total_trades']; ?></span>
                            <span class="stat-label"><?php _e('leaderboard_trades'); ?></span>
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
                    <h5><?php _e('leaderboard_no_data'); ?></h5>
                    <p class="text-muted"><?php _e('leaderboard_no_activity'); ?></p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center" width="80"><?php _e('leaderboard_rank'); ?></th>
                                <th><?php _e('leaderboard_trader'); ?></th>
                                <th class="text-center"><?php _e('leaderboard_country'); ?></th>
                                <th class="text-end"><?php _e('leaderboard_profit'); ?></th>
                                <th class="text-center"><?php _e('stats_win_rate'); ?></th>
                                <th class="text-center"><?php _e('leaderboard_trades'); ?></th>
                                <th class="text-center"><?php _e('leaderboard_package'); ?></th>
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
                                            <div>
                                                <strong><?php echo maskUsername($trader['username']); ?></strong>
                                                <?php if ($trader['package'] === 'vip'): ?>
                                                <span class="badge bg-warning text-dark ms-1"><i class="fas fa-crown"></i></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="mt-1">
                                                <?php echo BadgeSystem::renderShowcase($trader['user_id'], 3); ?>
                                            </div>
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
                <h6 class="mb-3"><i class="fas fa-info-circle"></i> <?php _e('leaderboard_legend'); ?></h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center mb-2">
                            <span class="rank-badge gold me-2">🥇</span>
                            <span>Top 1 - <?php _e('leaderboard_champion'); ?></span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="rank-badge silver me-2">🥈</span>
                            <span>Top 2 - <?php _e('leaderboard_runner_up'); ?></span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="rank-badge bronze me-2">🥉</span>
                            <span>Top 3 - <?php _e('leaderboard_third'); ?></span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <p class="small text-muted mb-1"><i class="fas fa-shield-alt"></i> <?php _e('leaderboard_privacy_note'); ?></p>
                        <p class="small text-muted mb-1"><i class="fas fa-sync"></i> <?php _e('leaderboard_update_note'); ?></p>
                        <p class="small text-muted mb-0"><i class="fas fa-filter"></i> <?php _e('leaderboard_filter_note'); ?></p>
                    </div>
                    <div class="col-md-4">
                        <p class="small text-muted mb-1"><strong><?php _e('leaderboard_winrate_badge'); ?></strong></p>
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
                <h4><?php _e('leaderboard_want_join'); ?></h4>
                <p class="mb-3"><?php _e('leaderboard_join_now'); ?></p>
                <a href="register.php" class="btn btn-light btn-lg">
                    <i class="fas fa-rocket"></i> <?php _e('leaderboard_start_free'); ?>
                </a>
            </div>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <!-- BADGE RANKING VIEW -->

        <!-- Your Badge Stats (if logged in) -->
        <?php if (isLoggedIn()): ?>
        <div class="card mb-4 border-primary">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div class="badge-rank" style="background: <?php echo $userBadgeRank['color']; ?>; color: #000;">
                            <i class="fas <?php echo $userBadgeRank['icon']; ?>"></i>
                            <?php echo $userBadgeRank['rank']; ?>
                        </div>
                    </div>
                    <div class="col">
                        <h5 class="mb-0"><?php _e('leaderboard_badge_stats'); ?></h5>
                        <small class="text-muted"><?php echo $userBadgePoints; ?> <?php _e('leaderboard_badge_points'); ?></small>
                    </div>
                    <div class="col-auto text-end">
                        <div class="stat-item">
                            <span class="stat-value text-primary"><?php echo count($userBadges); ?></span>
                            <span class="stat-label"><?php _e('leaderboard_total_badges'); ?></span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <a href="badges.php" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-eye"></i> <?php _e('leaderboard_view_all'); ?>
                        </a>
                    </div>
                </div>
                <?php if (!empty($userBadges)): ?>
                <div class="mt-3 pt-3 border-top">
                    <small class="text-muted d-block mb-2"><?php _e('leaderboard_recent_badges'); ?></small>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach (array_slice($userBadges, 0, 6) as $badge): ?>
                        <?php echo BadgeSystem::renderBadge($badge['id'], 'md', true); ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Badge Leaderboard -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-award"></i> Badge Point Rankings</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($badgeLeaderboard)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-award fa-3x text-muted mb-3"></i>
                    <h5><?php _e('leaderboard_no_data'); ?></h5>
                    <p class="text-muted"><?php _e('leaderboard_no_badges'); ?></p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center" width="80"><?php _e('leaderboard_rank'); ?></th>
                                <th><?php _e('leaderboard_trader'); ?></th>
                                <th class="text-center"><?php _e('leaderboard_badges'); ?></th>
                                <th class="text-center"><?php _e('leaderboard_points'); ?></th>
                                <th class="text-center"><?php _e('badges_rank'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($badgeLeaderboard as $index => $trader): ?>
                            <?php
                                $traderBadgeRank = BadgeSystem::getBadgeRank($trader['badge_points']);
                                $isCurrentUser = isLoggedIn() && $trader['id'] == $_SESSION['user_id'];
                            ?>
                            <tr class="<?php echo $isCurrentUser ? 'table-primary' : ''; ?>">
                                <td class="text-center">
                                    <span class="rank-badge <?php echo $index < 3 ? ['gold', 'silver', 'bronze'][$index] : ''; ?>">
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
                                            <div>
                                                <strong><?php echo maskUsername($trader['username']); ?></strong>
                                                <?php if ($trader['package'] === 'vip'): ?>
                                                <span class="badge bg-warning text-dark ms-1"><i class="fas fa-crown"></i></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="mt-1">
                                                <?php echo BadgeSystem::renderShowcase($trader['id'], 5); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary"><?php echo $trader['badge_count']; ?> badges</span>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold text-primary"><?php echo number_format($trader['badge_points']); ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge-rank" style="background: <?php echo $traderBadgeRank['color']; ?>; color: #000; font-size: 0.75rem;">
                                        <i class="fas <?php echo $traderBadgeRank['icon']; ?>"></i>
                                        <?php echo $traderBadgeRank['rank']; ?>
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

        <!-- Badge Tiers Legend -->
        <div class="card mt-4">
            <div class="card-body">
                <h6 class="mb-3"><i class="fas fa-info-circle"></i> <?php _e('leaderboard_badge_tiers'); ?></h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge-rank me-2" style="background: #b9f2ff; color: #000;">
                                <i class="fas fa-gem"></i> Diamond
                            </span>
                            <span class="text-muted">2,000+ points</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge-rank me-2" style="background: #e5e4e2; color: #000;">
                                <i class="fas fa-crown"></i> Platinum
                            </span>
                            <span class="text-muted">1,000+ points</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge-rank me-2" style="background: #ffd700; color: #000;">
                                <i class="fas fa-star"></i> Gold
                            </span>
                            <span class="text-muted">500+ points</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge-rank me-2" style="background: #c0c0c0; color: #000;">
                                <i class="fas fa-medal"></i> Silver
                            </span>
                            <span class="text-muted">200+ points</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge-rank me-2" style="background: #cd7f32; color: #000;">
                                <i class="fas fa-award"></i> Bronze
                            </span>
                            <span class="text-muted">50+ points</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge-rank me-2" style="background: #6c757d; color: #fff;">
                                <i class="fas fa-user"></i> Rookie
                            </span>
                            <span class="text-muted">0-49 points</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA for non-logged in -->
        <?php if (!isLoggedIn()): ?>
        <div class="card mt-4 bg-gradient-primary text-white">
            <div class="card-body text-center py-4">
                <h4><?php _e('leaderboard_collect_badges'); ?></h4>
                <p class="mb-3"><?php _e('leaderboard_collect_desc'); ?></p>
                <a href="register.php" class="btn btn-light btn-lg">
                    <i class="fas fa-rocket"></i> <?php _e('leaderboard_start_free'); ?>
                </a>
            </div>
        </div>
        <?php endif; ?>

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
