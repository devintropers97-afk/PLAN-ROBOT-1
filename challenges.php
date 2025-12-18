<?php
/**
 * ZYN Trade System - Trading Challenges / Gamification
 * FASE 3/4: Gamification - Trading Challenges (FINAL ZYN - Inovasi #16)
 */
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/language.php';

$page_title = __('challenges_title');
require_once 'dashboard/includes/dashboard-header.php';

// Get user data
$user = getUserById($_SESSION['user_id']);

// Active challenges
$activeChallenges = [
    [
        'id' => 1,
        'name' => '7 Day Streak',
        'description' => 'Trade setiap hari selama 7 hari berturut-turut',
        'icon' => 'fa-fire',
        'color' => 'warning',
        'reward' => '+3 Days FREE',
        'progress' => 5,
        'target' => 7,
        'deadline' => date('Y-m-d', strtotime('+2 days')),
        'status' => 'active',
        'difficulty' => 'Easy'
    ],
    [
        'id' => 2,
        'name' => '50 Win Challenge',
        'description' => 'Menangkan 50 trade dalam seminggu',
        'icon' => 'fa-trophy',
        'color' => 'success',
        'reward' => '+7 Days FREE',
        'progress' => 32,
        'target' => 50,
        'deadline' => date('Y-m-d', strtotime('+5 days')),
        'status' => 'active',
        'difficulty' => 'Medium'
    ],
    [
        'id' => 3,
        'name' => 'Perfect Day',
        'description' => 'Menangkan semua trade dalam 1 hari (min 5 trades)',
        'icon' => 'fa-star',
        'color' => 'primary',
        'reward' => '+1 Day FREE',
        'progress' => 0,
        'target' => 1,
        'deadline' => date('Y-m-d', strtotime('+1 day')),
        'status' => 'active',
        'difficulty' => 'Hard'
    ],
];

// Upcoming challenges
$upcomingChallenges = [
    [
        'id' => 4,
        'name' => 'Century Club',
        'description' => 'Raih 100 trade win total',
        'icon' => 'fa-medal',
        'color' => 'danger',
        'reward' => '+14 Days FREE',
        'start_date' => date('Y-m-d', strtotime('+3 days')),
        'difficulty' => 'Hard',
        'duration' => '30 days'
    ],
    [
        'id' => 5,
        'name' => 'Profit Hunter',
        'description' => 'Profit total $500 dalam sebulan',
        'icon' => 'fa-dollar-sign',
        'color' => 'success',
        'reward' => 'VIP Badge',
        'start_date' => date('Y-m-d', strtotime('+7 days')),
        'difficulty' => 'Expert',
        'duration' => '30 days'
    ],
];

// Completed challenges
$completedChallenges = [
    [
        'id' => 100,
        'name' => 'First Blood',
        'description' => 'Selesaikan trade pertama kamu',
        'icon' => 'fa-check-circle',
        'color' => 'success',
        'reward' => 'First Blood Badge',
        'completed_at' => '2024-12-10',
    ],
    [
        'id' => 101,
        'name' => '10 Win Milestone',
        'description' => 'Menangkan 10 trade',
        'icon' => 'fa-award',
        'color' => 'info',
        'reward' => '+1 Day FREE',
        'completed_at' => '2024-12-12',
    ],
    [
        'id' => 102,
        'name' => 'Early Bird',
        'description' => 'Trade sebelum jam 7 pagi',
        'icon' => 'fa-sun',
        'color' => 'warning',
        'reward' => 'Early Bird Badge',
        'completed_at' => '2024-12-14',
    ],
];

// Leaderboard for current challenge
$challengeLeaderboard = [
    ['rank' => 1, 'name' => 'Trader_Pro', 'score' => 48, 'badge' => 'fa-crown text-warning'],
    ['rank' => 2, 'name' => 'ZYN_Master', 'score' => 45, 'badge' => 'fa-medal text-secondary'],
    ['rank' => 3, 'name' => 'Robot_King', 'score' => 42, 'badge' => 'fa-medal' style='color: #cd7f32'],
    ['rank' => 4, 'name' => htmlspecialchars($currentUser['fullname']), 'score' => 32, 'badge' => null, 'is_me' => true],
    ['rank' => 5, 'name' => 'Winner99', 'score' => 28, 'badge' => null],
];

// User stats
$userChallengeStats = [
    'total_completed' => count($completedChallenges),
    'total_rewards' => '+2 Days + 3 Badges',
    'current_streak' => 5,
    'rank' => 4,
];
?>

<!-- Page Header -->
<div class="db-page-header">
    <div>
        <h1 class="db-page-title"><i class="fas fa-gamepad"></i> Trading Challenges</h1>
        <p class="db-page-subtitle">Complete challenges to earn rewards and climb the leaderboard!</p>
    </div>
</div>

<!-- Challenge Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="db-stat-card db-fade-in">
            <div class="db-stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="db-stat-value"><?php echo $userChallengeStats['total_completed']; ?></div>
            <div class="db-stat-label">Completed</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="db-stat-card success db-fade-in">
            <div class="db-stat-icon"><i class="fas fa-gift"></i></div>
            <div class="db-stat-value"><?php echo $userChallengeStats['total_rewards']; ?></div>
            <div class="db-stat-label">Total Rewards</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="db-stat-card warning db-fade-in">
            <div class="db-stat-icon"><i class="fas fa-fire"></i></div>
            <div class="db-stat-value"><?php echo $userChallengeStats['current_streak']; ?> days</div>
            <div class="db-stat-label">Current Streak</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="db-stat-card db-fade-in">
            <div class="db-stat-icon"><i class="fas fa-trophy"></i></div>
            <div class="db-stat-value">#<?php echo $userChallengeStats['rank']; ?></div>
            <div class="db-stat-label">Current Rank</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column - Challenges -->
    <div class="col-lg-8">
        <!-- Active Challenges -->
        <div class="db-card mb-4 db-fade-in">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="fas fa-bolt"></i> Active Challenges</h5>
                <span class="db-badge primary"><?php echo count($activeChallenges); ?> Active</span>
            </div>
            <div class="db-card-body">
                <div class="challenges-grid">
                    <?php foreach ($activeChallenges as $challenge):
                        $progressPercent = ($challenge['progress'] / $challenge['target']) * 100;
                        $daysLeft = (strtotime($challenge['deadline']) - time()) / 86400;
                    ?>
                    <div class="challenge-card <?php echo $challenge['color']; ?>">
                        <div class="challenge-header">
                            <div class="challenge-icon">
                                <i class="fas <?php echo $challenge['icon']; ?>"></i>
                            </div>
                            <div class="challenge-info">
                                <h6 class="challenge-name"><?php echo htmlspecialchars($challenge['name']); ?></h6>
                                <span class="db-badge <?php echo $challenge['difficulty'] === 'Easy' ? 'success' : ($challenge['difficulty'] === 'Medium' ? 'warning' : 'danger'); ?> challenge-difficulty">
                                    <?php echo $challenge['difficulty']; ?>
                                </span>
                            </div>
                            <div class="challenge-reward">
                                <span class="reward-badge"><?php echo $challenge['reward']; ?></span>
                            </div>
                        </div>

                        <p class="challenge-description"><?php echo htmlspecialchars($challenge['description']); ?></p>

                        <div class="challenge-progress">
                            <div class="progress-header">
                                <span>Progress</span>
                                <span><?php echo $challenge['progress']; ?>/<?php echo $challenge['target']; ?></span>
                            </div>
                            <div class="db-progress-bar">
                                <div class="db-progress-fill <?php echo $challenge['color']; ?>" style="width: <?php echo $progressPercent; ?>%;"></div>
                            </div>
                        </div>

                        <div class="challenge-footer">
                            <span class="deadline <?php echo $daysLeft < 1 ? 'text-danger' : ''; ?>">
                                <i class="fas fa-clock me-1"></i>
                                <?php echo $daysLeft < 1 ? 'Ends today!' : round($daysLeft) . ' days left'; ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Upcoming Challenges -->
        <div class="db-card mb-4 db-fade-in">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="fas fa-calendar-alt"></i> Upcoming Challenges</h5>
            </div>
            <div class="db-card-body">
                <div class="upcoming-challenges">
                    <?php foreach ($upcomingChallenges as $challenge): ?>
                    <div class="upcoming-card">
                        <div class="upcoming-icon <?php echo $challenge['color']; ?>">
                            <i class="fas <?php echo $challenge['icon']; ?>"></i>
                        </div>
                        <div class="upcoming-info">
                            <h6><?php echo htmlspecialchars($challenge['name']); ?></h6>
                            <p><?php echo htmlspecialchars($challenge['description']); ?></p>
                            <div class="upcoming-meta">
                                <span><i class="fas fa-calendar me-1"></i>Starts: <?php echo date('d M', strtotime($challenge['start_date'])); ?></span>
                                <span><i class="fas fa-hourglass-half me-1"></i><?php echo $challenge['duration']; ?></span>
                                <span class="db-badge <?php echo $challenge['difficulty'] === 'Expert' ? 'danger' : 'warning'; ?>">
                                    <?php echo $challenge['difficulty']; ?>
                                </span>
                            </div>
                        </div>
                        <div class="upcoming-reward">
                            <span class="reward-badge"><?php echo $challenge['reward']; ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Completed Challenges -->
        <div class="db-card db-fade-in">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="fas fa-check-double"></i> Completed Challenges</h5>
            </div>
            <div class="db-card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="db-table">
                        <thead>
                            <tr>
                                <th>Challenge</th>
                                <th>Completed</th>
                                <th>Reward</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($completedChallenges as $challenge): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="completed-icon <?php echo $challenge['color']; ?>">
                                            <i class="fas <?php echo $challenge['icon']; ?>"></i>
                                        </div>
                                        <div>
                                            <strong><?php echo htmlspecialchars($challenge['name']); ?></strong>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($challenge['description']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo date('d M Y', strtotime($challenge['completed_at'])); ?></td>
                                <td>
                                    <span class="db-badge success">
                                        <i class="fas fa-gift me-1"></i><?php echo $challenge['reward']; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column - Leaderboard & Info -->
    <div class="col-lg-4">
        <!-- Challenge Leaderboard -->
        <div class="db-card mb-4 db-fade-in">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="fas fa-crown"></i> Challenge Leaderboard</h5>
                <span class="text-muted small">50 Win Challenge</span>
            </div>
            <div class="db-card-body">
                <div class="leaderboard-list">
                    <?php foreach ($challengeLeaderboard as $entry): ?>
                    <div class="leaderboard-item <?php echo isset($entry['is_me']) ? 'is-me' : ''; ?>">
                        <div class="rank-badge <?php echo $entry['rank'] <= 3 ? 'top-3' : ''; ?>">
                            <?php if ($entry['badge']): ?>
                            <i class="fas <?php echo $entry['badge']; ?>"></i>
                            <?php else: ?>
                            #<?php echo $entry['rank']; ?>
                            <?php endif; ?>
                        </div>
                        <div class="player-name">
                            <?php echo htmlspecialchars($entry['name']); ?>
                            <?php if (isset($entry['is_me'])): ?>
                            <span class="db-badge primary ms-1">You</span>
                            <?php endif; ?>
                        </div>
                        <div class="player-score">
                            <?php echo $entry['score']; ?> wins
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <a href="leaderboard.php" class="db-btn db-btn-outline w-100 mt-3">
                    View Full Leaderboard
                </a>
            </div>
        </div>

        <!-- How to Earn Rewards -->
        <div class="db-card mb-4 db-fade-in">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="fas fa-gift"></i> Reward Types</h5>
            </div>
            <div class="db-card-body">
                <div class="reward-types">
                    <div class="reward-type-item">
                        <div class="rt-icon success">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        <div class="rt-info">
                            <strong>Free Subscription Days</strong>
                            <span>+1 to +14 days</span>
                        </div>
                    </div>
                    <div class="reward-type-item">
                        <div class="rt-icon warning">
                            <i class="fas fa-medal"></i>
                        </div>
                        <div class="rt-info">
                            <strong>Exclusive Badges</strong>
                            <span>Show off on profile</span>
                        </div>
                    </div>
                    <div class="reward-type-item">
                        <div class="rt-icon primary">
                            <i class="fas fa-unlock"></i>
                        </div>
                        <div class="rt-info">
                            <strong>Strategy Access</strong>
                            <span>Unlock premium strategies</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tips -->
        <div class="db-card db-fade-in">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="fas fa-lightbulb"></i> Pro Tips</h5>
            </div>
            <div class="db-card-body">
                <div class="tips-list">
                    <div class="tip-item">
                        <i class="fas fa-check-circle text-success"></i>
                        <span>Focus on one challenge at a time</span>
                    </div>
                    <div class="tip-item">
                        <i class="fas fa-check-circle text-success"></i>
                        <span>Keep daily streaks going!</span>
                    </div>
                    <div class="tip-item">
                        <i class="fas fa-check-circle text-success"></i>
                        <span>Easy challenges are quick wins</span>
                    </div>
                    <div class="tip-item">
                        <i class="fas fa-check-circle text-success"></i>
                        <span>Check back for new challenges</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Challenge Card */
.challenges-grid {
    display: grid;
    gap: 1rem;
}

.challenge-card {
    background: var(--db-surface-light);
    border-radius: 16px;
    padding: 1.5rem;
    border-left: 4px solid transparent;
    transition: all 0.3s ease;
}

.challenge-card:hover {
    transform: translateX(5px);
}

.challenge-card.warning { border-left-color: var(--db-warning); }
.challenge-card.success { border-left-color: var(--db-success); }
.challenge-card.primary { border-left-color: var(--db-primary); }
.challenge-card.danger { border-left-color: var(--db-danger); }
.challenge-card.info { border-left-color: var(--db-info); }

.challenge-header {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1rem;
}

.challenge-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.challenge-card.warning .challenge-icon { background: rgba(var(--db-warning-rgb), 0.15); color: var(--db-warning); }
.challenge-card.success .challenge-icon { background: rgba(var(--db-success-rgb), 0.15); color: var(--db-success); }
.challenge-card.primary .challenge-icon { background: rgba(var(--db-primary-rgb), 0.15); color: var(--db-primary); }
.challenge-card.danger .challenge-icon { background: rgba(var(--db-danger-rgb), 0.15); color: var(--db-danger); }

.challenge-info {
    flex: 1;
}

.challenge-name {
    margin-bottom: 0.25rem;
    font-weight: 600;
}

.challenge-difficulty {
    font-size: 0.7rem;
}

.challenge-reward {
    text-align: right;
}

.reward-badge {
    display: inline-block;
    padding: 0.5rem 0.75rem;
    background: linear-gradient(135deg, rgba(var(--db-success-rgb), 0.15), rgba(var(--db-success-rgb), 0.05));
    border: 1px solid rgba(var(--db-success-rgb), 0.3);
    border-radius: 8px;
    color: var(--db-success);
    font-weight: 600;
    font-size: 0.85rem;
}

.challenge-description {
    color: var(--db-text-muted);
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.challenge-progress {
    margin-bottom: 1rem;
}

.progress-header {
    display: flex;
    justify-content: space-between;
    font-size: 0.85rem;
    margin-bottom: 0.5rem;
}

.challenge-footer {
    font-size: 0.85rem;
    color: var(--db-text-muted);
}

/* Upcoming Challenges */
.upcoming-challenges {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.upcoming-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: var(--db-surface-light);
    border-radius: 12px;
}

.upcoming-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.upcoming-icon.danger { background: rgba(var(--db-danger-rgb), 0.15); color: var(--db-danger); }
.upcoming-icon.success { background: rgba(var(--db-success-rgb), 0.15); color: var(--db-success); }

.upcoming-info {
    flex: 1;
}

.upcoming-info h6 {
    margin-bottom: 0.25rem;
}

.upcoming-info p {
    font-size: 0.85rem;
    color: var(--db-text-muted);
    margin-bottom: 0.5rem;
}

.upcoming-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    font-size: 0.8rem;
    color: var(--db-text-muted);
}

/* Completed Icon */
.completed-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.completed-icon.success { background: rgba(var(--db-success-rgb), 0.15); color: var(--db-success); }
.completed-icon.info { background: rgba(var(--db-info-rgb), 0.15); color: var(--db-info); }
.completed-icon.warning { background: rgba(var(--db-warning-rgb), 0.15); color: var(--db-warning); }

/* Leaderboard */
.leaderboard-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.leaderboard-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    background: var(--db-surface-light);
    border-radius: 10px;
}

.leaderboard-item.is-me {
    background: rgba(var(--db-primary-rgb), 0.15);
    border: 1px solid rgba(var(--db-primary-rgb), 0.3);
}

.rank-badge {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.85rem;
    background: var(--db-surface);
}

.rank-badge.top-3 {
    font-size: 1rem;
}

.player-name {
    flex: 1;
    font-weight: 500;
}

.player-score {
    font-size: 0.85rem;
    color: var(--db-text-muted);
}

/* Reward Types */
.reward-types {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.reward-type-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.rt-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.rt-icon.success { background: rgba(var(--db-success-rgb), 0.15); color: var(--db-success); }
.rt-icon.warning { background: rgba(var(--db-warning-rgb), 0.15); color: var(--db-warning); }
.rt-icon.primary { background: rgba(var(--db-primary-rgb), 0.15); color: var(--db-primary); }

.rt-info {
    display: flex;
    flex-direction: column;
}

.rt-info strong {
    font-size: 0.9rem;
}

.rt-info span {
    font-size: 0.8rem;
    color: var(--db-text-muted);
}

/* Tips */
.tips-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.tips-list .tip-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.9rem;
}
</style>

<?php require_once 'dashboard/includes/dashboard-footer.php'; ?>
