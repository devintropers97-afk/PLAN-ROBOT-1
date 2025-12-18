<?php
/**
 * ZYN Trade System - Badges Page
 * Halaman untuk melihat semua badges dan achievements
 */

$page_title = 'Badges & Achievements';
require_once 'includes/header.php';
require_once 'includes/badges.php';

// Get filter category
$category = $_GET['category'] ?? 'all';
$validCategories = ['all', 'milestone', 'profit', 'skill', 'streak', 'special', 'social', 'achievement', 'dedication'];
if (!in_array($category, $validCategories)) {
    $category = 'all';
}

// Get all badges
$allBadges = BadgeSystem::getAllBadges();

// Filter by category
if ($category !== 'all') {
    $filteredBadges = array_filter($allBadges, function($badge) use ($category) {
        return $badge['category'] === $category;
    });
} else {
    $filteredBadges = $allBadges;
}

// Get user's badges if logged in
$userBadges = [];
$userBadgeIds = [];
$userBadgePoints = 0;
$userBadgeRank = null;
$badgeProgress = [];

if (isLoggedIn()) {
    // Check and award any new badges
    BadgeSystem::checkAndAward($_SESSION['user_id']);

    $userBadges = BadgeSystem::getUserBadges($_SESSION['user_id']);
    $userBadgeIds = array_column($userBadges, 'id');
    $userBadgePoints = BadgeSystem::getTotalPoints($_SESSION['user_id']);
    $userBadgeRank = BadgeSystem::getBadgeRank($userBadgePoints);
    $badgeProgress = BadgeSystem::getProgress($_SESSION['user_id']);
}

// Category labels
$categoryLabels = [
    'all' => __('badges_cat_all'),
    'milestone' => __('badges_cat_milestone'),
    'profit' => __('badges_cat_profit'),
    'skill' => __('badges_cat_skill'),
    'streak' => __('badges_cat_streak'),
    'special' => __('badges_cat_special'),
    'social' => __('badges_cat_social'),
    'achievement' => __('badges_cat_achievement'),
    'dedication' => __('badges_cat_dedication')
];

$lang = $_SESSION['lang'] ?? 'id';
?>

<?php echo render_badge_styles(); ?>

<section class="badges-page">
    <div class="container py-4">
        <!-- Page Header -->
        <div class="page-header text-center mb-4">
            <h1 class="page-title">
                <i class="fas fa-award text-warning"></i> <?php _e('badges_title'); ?>
            </h1>
            <p class="text-muted"><?php _e('badges_subtitle'); ?></p>
        </div>

        <?php if (isLoggedIn()): ?>
        <!-- User Badge Stats -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="badge-rank-large" style="background: <?php echo $userBadgeRank['color']; ?>;">
                                    <i class="fas <?php echo $userBadgeRank['icon']; ?> fa-2x"></i>
                                </div>
                            </div>
                            <div class="col">
                                <h4 class="mb-0"><?php echo $userBadgeRank['rank']; ?> <?php _e('badges_rank'); ?></h4>
                                <p class="text-muted mb-0"><?php echo $userBadgePoints; ?> <?php _e('leaderboard_badge_points'); ?></p>
                            </div>
                            <div class="col-auto text-center px-4 border-start">
                                <h3 class="mb-0 text-primary"><?php echo count($userBadges); ?></h3>
                                <small class="text-muted"><?php _e('badges_earned'); ?></small>
                            </div>
                            <div class="col-auto text-center px-4 border-start">
                                <h3 class="mb-0"><?php echo count($allBadges); ?></h3>
                                <small class="text-muted"><?php _e('badges_total'); ?></small>
                            </div>
                            <div class="col-auto text-center px-4 border-start">
                                <h3 class="mb-0 text-success"><?php echo round((count($userBadges) / count($allBadges)) * 100); ?>%</h3>
                                <small class="text-muted"><?php _e('badges_completion'); ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-chart-line"></i> <?php _e('badges_next'); ?></h6>
                    </div>
                    <div class="card-body p-2">
                        <?php if (!empty($badgeProgress)): ?>
                            <?php foreach ($badgeProgress as $prog): ?>
                            <div class="badge-progress mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <?php echo BadgeSystem::renderBadge($prog['badge_id'], 'sm', false); ?>
                                    <div class="flex-grow-1">
                                        <div class="small fw-bold"><?php echo $lang === 'id' ? $prog['badge']['name_id'] : $prog['badge']['name']; ?></div>
                                        <div class="badge-progress-bar">
                                            <div class="badge-progress-fill" style="width: <?php echo $prog['percent']; ?>%"></div>
                                        </div>
                                    </div>
                                    <div class="small text-muted">
                                        <?php echo number_format($prog['current']); ?>/<?php echo number_format($prog['target']); ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center mb-0"><?php _e('badges_all_earned'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Category Filter -->
        <div class="card mb-4">
            <div class="card-body py-2">
                <div class="d-flex flex-wrap gap-2 justify-content-center">
                    <?php foreach ($categoryLabels as $cat => $label): ?>
                    <a href="?category=<?php echo $cat; ?>"
                       class="btn btn-sm <?php echo $category === $cat ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                        <?php echo $label; ?>
                        <?php if ($cat !== 'all'): ?>
                        <span class="badge bg-light text-dark ms-1">
                            <?php echo count(array_filter($allBadges, function($b) use ($cat) { return $b['category'] === $cat; })); ?>
                        </span>
                        <?php endif; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Badges Grid -->
        <div class="row g-3">
            <?php foreach ($filteredBadges as $badge_id => $badge): ?>
            <?php
                $isEarned = in_array($badge_id, $userBadgeIds);
                $earnedAt = null;
                if ($isEarned) {
                    foreach ($userBadges as $ub) {
                        if ($ub['id'] === $badge_id) {
                            $earnedAt = $ub['earned_at'];
                            break;
                        }
                    }
                }
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="badge-full-card <?php echo $isEarned ? 'earned' : 'locked'; ?> rarity-<?php echo $badge['rarity']; ?>">
                    <div class="badge-full-icon" style="--badge-color: <?php echo $badge['color']; ?>">
                        <i class="fas <?php echo $badge['icon']; ?>"></i>
                        <?php if (!$isEarned): ?>
                        <div class="lock-overlay">
                            <i class="fas fa-lock"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="badge-full-content">
                        <h5 class="badge-full-name">
                            <?php echo $lang === 'id' ? $badge['name_id'] : $badge['name']; ?>
                        </h5>
                        <p class="badge-full-desc">
                            <?php echo $lang === 'id' ? $badge['description_id'] : $badge['description']; ?>
                        </p>
                        <div class="badge-full-meta">
                            <span class="badge-rarity-tag rarity-<?php echo $badge['rarity']; ?>">
                                <?php echo ucfirst($badge['rarity']); ?>
                            </span>
                            <span class="badge-points-tag">
                                <i class="fas fa-star"></i> <?php echo $badge['points']; ?> pts
                            </span>
                            <?php if ($isEarned && $earnedAt): ?>
                            <span class="badge-earned-tag">
                                <i class="fas fa-check-circle text-success"></i>
                                <?php echo date('d M Y', strtotime($earnedAt)); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (!isLoggedIn()): ?>
        <!-- CTA for non-logged in -->
        <div class="card mt-4 bg-gradient-primary text-white">
            <div class="card-body text-center py-4">
                <h4><?php _e('badges_cta_title'); ?></h4>
                <p class="mb-3"><?php _e('badges_cta_desc'); ?></p>
                <a href="register.php" class="btn btn-light btn-lg">
                    <i class="fas fa-rocket"></i> <?php _e('leaderboard_start_free'); ?>
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Rarity Legend -->
        <div class="card mt-4">
            <div class="card-body">
                <h6 class="mb-3"><i class="fas fa-info-circle"></i> <?php _e('badges_rarity'); ?></h6>
                <div class="d-flex flex-wrap gap-3 justify-content-center">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge-rarity-tag rarity-common">Common</span>
                        <span class="text-muted small">10-25 pts</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge-rarity-tag rarity-uncommon">Uncommon</span>
                        <span class="text-muted small">40-75 pts</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge-rarity-tag rarity-rare">Rare</span>
                        <span class="text-muted small">75-150 pts</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge-rarity-tag rarity-epic">Epic</span>
                        <span class="text-muted small">200-300 pts</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge-rarity-tag rarity-legendary">Legendary</span>
                        <span class="text-muted small">400-500 pts</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Badge Full Card */
.badge-full-card {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 16px;
    background: var(--card-bg);
    border-radius: 12px;
    border: 1px solid var(--border-color);
    transition: all 0.3s;
    height: 100%;
}
.badge-full-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}
.badge-full-card.locked {
    opacity: 0.6;
}
.badge-full-card.locked:hover {
    opacity: 0.8;
}
.badge-full-card.earned {
    border-color: var(--badge-color, var(--primary-color));
    background: linear-gradient(135deg, var(--card-bg), color-mix(in srgb, var(--badge-color, var(--primary-color)) 5%, var(--card-bg)));
}

.badge-full-icon {
    position: relative;
    width: 64px;
    height: 64px;
    min-width: 64px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--badge-color), color-mix(in srgb, var(--badge-color) 70%, black));
    color: white;
    font-size: 1.5rem;
    box-shadow: 0 4px 15px color-mix(in srgb, var(--badge-color) 40%, transparent);
}
.lock-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.5);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.badge-full-content {
    flex: 1;
}
.badge-full-name {
    margin: 0 0 4px 0;
    font-size: 1rem;
}
.badge-full-desc {
    margin: 0 0 8px 0;
    font-size: 0.85rem;
    color: var(--text-muted);
    line-height: 1.4;
}
.badge-full-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    font-size: 0.75rem;
}

/* Rarity tags */
.badge-rarity-tag {
    padding: 2px 8px;
    border-radius: 4px;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.7rem;
}
.badge-rarity-tag.rarity-common { background: #6c757d; color: white; }
.badge-rarity-tag.rarity-uncommon { background: #17a2b8; color: white; }
.badge-rarity-tag.rarity-rare { background: #6f42c1; color: white; }
.badge-rarity-tag.rarity-epic { background: #fd7e14; color: white; }
.badge-rarity-tag.rarity-legendary {
    background: linear-gradient(135deg, #e83e8c, #fd7e14);
    color: white;
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    0%, 100% { filter: brightness(1); }
    50% { filter: brightness(1.2); }
}

.badge-points-tag {
    color: var(--primary-color);
    font-weight: 600;
}
.badge-earned-tag {
    color: var(--text-muted);
}

/* Badge Rank Large */
.badge-rank-large {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #000;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

/* Rarity card borders */
.badge-full-card.rarity-legendary.earned {
    border-color: #e83e8c;
    animation: legendary-glow 3s infinite;
}
.badge-full-card.rarity-epic.earned {
    border-color: #fd7e14;
}
.badge-full-card.rarity-rare.earned {
    border-color: #6f42c1;
}

@keyframes legendary-glow {
    0%, 100% { box-shadow: 0 0 5px #e83e8c; }
    50% { box-shadow: 0 0 20px #e83e8c, 0 0 30px #fd7e14; }
}

.bg-gradient-primary {
    background: linear-gradient(135deg, var(--primary-color), #0066cc);
}
</style>

<?php require_once 'includes/footer.php'; ?>
