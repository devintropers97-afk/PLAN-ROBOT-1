<?php
/**
 * ZYN Trade System - Referral Reward System
 * FASE 3: Referral Reward System (FINAL ZYN - Inovasi #12)
 */
$page_title = 'Referral Program';
require_once 'dashboard/includes/dashboard-header.php';

// Get user referral data
$user = getUserById($_SESSION['user_id']);
$referralCode = $user['referral_code'] ?? strtoupper(substr(md5($user['email']), 0, 8));
$referralLink = SITE_URL . '/register.php?ref=' . $referralCode;

// Mock referral stats
$referralStats = [
    'total_referrals' => 5,
    'active_referrals' => 3,
    'pending_referrals' => 2,
    'total_reward_days' => 21,
    'remaining_slots' => 3 - 3, // Max 3 referrals
];

// Mock referred users
$referredUsers = [
    ['name' => 'User A***', 'date' => '2024-12-15', 'status' => 'active', 'reward' => '+7 days'],
    ['name' => 'User B***', 'date' => '2024-12-12', 'status' => 'active', 'reward' => '+7 days'],
    ['name' => 'User C***', 'date' => '2024-12-10', 'status' => 'active', 'reward' => '+7 days'],
    ['name' => 'User D***', 'date' => '2024-12-08', 'status' => 'pending', 'reward' => 'Pending'],
    ['name' => 'User E***', 'date' => '2024-12-05', 'status' => 'pending', 'reward' => 'Pending'],
];

// Reward history
$rewardHistory = [
    ['date' => '2024-12-15', 'description' => 'Referral reward from User A***', 'reward' => '+7 days', 'status' => 'credited'],
    ['date' => '2024-12-12', 'description' => 'Referral reward from User B***', 'reward' => '+7 days', 'status' => 'credited'],
    ['date' => '2024-12-10', 'description' => 'Referral reward from User C***', 'reward' => '+7 days', 'status' => 'credited'],
];

// Handle form actions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';

    if ($action === 'generate_new_code') {
        // In production: generate new referral code
        $message = 'New referral code generated!';
        $messageType = 'success';
    }
}
?>

<!-- Page Header -->
<div class="db-page-header">
    <div>
        <h1 class="db-page-title"><i class="fas fa-users"></i> Referral Program</h1>
        <p class="db-page-subtitle">Invite friends and earn free subscription days</p>
    </div>
</div>

<?php if ($message): ?>
<div class="db-alert <?php echo $messageType; ?> db-fade-in">
    <i class="fas fa-check-circle"></i>
    <span><?php echo $message; ?></span>
    <button type="button" class="btn-close btn-close-white ms-auto" onclick="this.parentElement.remove()"></button>
</div>
<?php endif; ?>

<!-- Referral Stats -->
<div class="row g-4 mb-4">
    <div class="col-6 col-lg-3">
        <div class="db-stat-card db-fade-in">
            <div class="db-stat-icon"><i class="fas fa-user-plus"></i></div>
            <div class="db-stat-value"><?php echo $referralStats['total_referrals']; ?></div>
            <div class="db-stat-label">Total Referrals</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="db-stat-card success db-fade-in">
            <div class="db-stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="db-stat-value"><?php echo $referralStats['active_referrals']; ?></div>
            <div class="db-stat-label">Active Referrals</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="db-stat-card warning db-fade-in">
            <div class="db-stat-icon"><i class="fas fa-clock"></i></div>
            <div class="db-stat-value"><?php echo $referralStats['pending_referrals']; ?></div>
            <div class="db-stat-label">Pending Verification</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="db-stat-card db-fade-in" style="background: linear-gradient(135deg, rgba(var(--db-primary-rgb), 0.15), rgba(124, 58, 237, 0.15));">
            <div class="db-stat-icon"><i class="fas fa-gift"></i></div>
            <div class="db-stat-value">+<?php echo $referralStats['total_reward_days']; ?> days</div>
            <div class="db-stat-label">Total Rewards Earned</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column -->
    <div class="col-lg-8">
        <!-- Referral Link Card -->
        <div class="db-card mb-4 db-fade-in referral-link-card">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="fas fa-link"></i> Your Referral Link</h5>
            </div>
            <div class="db-card-body">
                <div class="referral-link-wrapper">
                    <div class="referral-code-display">
                        <span class="code-label">Your Code:</span>
                        <span class="code-value"><?php echo $referralCode; ?></span>
                    </div>

                    <div class="referral-link-input">
                        <input type="text" id="referralLink" value="<?php echo $referralLink; ?>" readonly class="db-form-control">
                        <button class="db-btn db-btn-primary" onclick="copyReferralLink()">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>

                    <div class="share-buttons">
                        <span class="share-label">Share via:</span>
                        <a href="https://wa.me/?text=Daftar%20ZYN%20Trade%20System%20dan%20dapatkan%20robot%20trading%20gratis!%20<?php echo urlencode($referralLink); ?>" target="_blank" class="share-btn whatsapp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="https://t.me/share/url?url=<?php echo urlencode($referralLink); ?>&text=Daftar%20ZYN%20Trade%20System%20dan%20dapatkan%20robot%20trading%20gratis!" target="_blank" class="share-btn telegram">
                            <i class="fab fa-telegram"></i>
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($referralLink); ?>" target="_blank" class="share-btn facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($referralLink); ?>&text=Robot%20trading%20otomatis%20terbaik!" target="_blank" class="share-btn twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Referred Users -->
        <div class="db-card mb-4 db-fade-in">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="fas fa-user-friends"></i> Referred Users</h5>
            </div>
            <div class="db-card-body" style="padding: 0;">
                <?php if (empty($referredUsers)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3" style="opacity: 0.3;"></i>
                    <p class="text-muted">No referrals yet. Share your link to start earning!</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="db-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Date Joined</th>
                                <th>Status</th>
                                <th>Reward</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($referredUsers as $ref): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="user-avatar-sm">
                                            <?php echo substr($ref['name'], 5, 1); ?>
                                        </div>
                                        <?php echo htmlspecialchars($ref['name']); ?>
                                    </div>
                                </td>
                                <td><?php echo date('d M Y', strtotime($ref['date'])); ?></td>
                                <td>
                                    <span class="db-badge <?php echo $ref['status'] === 'active' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($ref['status']); ?>
                                    </span>
                                </td>
                                <td class="<?php echo $ref['status'] === 'active' ? 'text-success' : 'text-muted'; ?> fw-bold">
                                    <?php echo $ref['reward']; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Reward History -->
        <div class="db-card db-fade-in">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="fas fa-history"></i> Reward History</h5>
            </div>
            <div class="db-card-body" style="padding: 0;">
                <?php if (empty($rewardHistory)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-gift fa-3x text-muted mb-3" style="opacity: 0.3;"></i>
                    <p class="text-muted">No rewards yet. Invite friends to earn rewards!</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="db-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Reward</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rewardHistory as $reward): ?>
                            <tr>
                                <td><?php echo date('d M Y', strtotime($reward['date'])); ?></td>
                                <td><?php echo htmlspecialchars($reward['description']); ?></td>
                                <td class="text-success fw-bold"><?php echo $reward['reward']; ?></td>
                                <td>
                                    <span class="db-badge success">
                                        <i class="fas fa-check me-1"></i><?php echo ucfirst($reward['status']); ?>
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
    </div>

    <!-- Right Column -->
    <div class="col-lg-4">
        <!-- How It Works -->
        <div class="db-card mb-4 db-fade-in">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="fas fa-info-circle"></i> How It Works</h5>
            </div>
            <div class="db-card-body">
                <div class="how-it-works">
                    <div class="step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h6>Share Your Link</h6>
                            <p>Bagikan link referral kamu ke teman-teman</p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h6>Friend Registers</h6>
                            <p>Teman daftar dan verifikasi akun OlympTrade</p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h6>Get Rewarded</h6>
                            <p>Kamu dapat <strong>+7 hari subscription gratis!</strong></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reward Details -->
        <div class="db-card mb-4 db-fade-in">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="fas fa-gift"></i> Reward Details</h5>
            </div>
            <div class="db-card-body">
                <div class="reward-info">
                    <div class="reward-item">
                        <div class="reward-icon success">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        <div class="reward-text">
                            <strong>+7 Days FREE</strong>
                            <span>Per successful referral</span>
                        </div>
                    </div>

                    <div class="reward-item">
                        <div class="reward-icon warning">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="reward-text">
                            <strong>Max 3 Referrals</strong>
                            <span>Per user (21 days total)</span>
                        </div>
                    </div>

                    <div class="reward-item">
                        <div class="reward-icon info">
                            <i class="fas fa-check-double"></i>
                        </div>
                        <div class="reward-text">
                            <strong>Verification Required</strong>
                            <span>Referral must be verified</span>
                        </div>
                    </div>
                </div>

                <div class="progress-section mt-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Referral Slots Used</span>
                        <strong><?php echo $referralStats['active_referrals']; ?>/3</strong>
                    </div>
                    <div class="db-progress-bar">
                        <div class="db-progress-fill primary" style="width: <?php echo ($referralStats['active_referrals'] / 3) * 100; ?>%;"></div>
                    </div>
                    <?php if ($referralStats['remaining_slots'] <= 0): ?>
                    <small class="text-warning d-block mt-2">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Maximum referrals reached
                    </small>
                    <?php else: ?>
                    <small class="text-muted d-block mt-2">
                        <?php echo $referralStats['remaining_slots']; ?> slots remaining
                    </small>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Terms & Conditions -->
        <div class="db-card db-fade-in">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="fas fa-file-alt"></i> Terms</h5>
            </div>
            <div class="db-card-body">
                <ul class="terms-list">
                    <li>Referral harus mendaftar via link kamu</li>
                    <li>Referral harus memverifikasi akun OlympTrade</li>
                    <li>Reward dikreditkan setelah verifikasi</li>
                    <li>Maksimal 3 referral per user</li>
                    <li>Self-referral tidak diperbolehkan</li>
                    <li>ZYN berhak membatalkan reward jika ada kecurangan</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
/* Referral Link Card */
.referral-link-card {
    background: linear-gradient(135deg, rgba(var(--db-primary-rgb), 0.1), rgba(124, 58, 237, 0.1));
    border: 1px solid rgba(var(--db-primary-rgb), 0.3);
}

.referral-code-display {
    text-align: center;
    margin-bottom: 1.5rem;
}

.code-label {
    display: block;
    color: var(--db-text-muted);
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.code-value {
    font-family: 'Orbitron', monospace;
    font-size: 2rem;
    font-weight: 700;
    background: linear-gradient(135deg, var(--db-primary), #7c3aed);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    letter-spacing: 4px;
}

.referral-link-input {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}

.referral-link-input input {
    flex: 1;
    font-size: 0.85rem;
}

/* Share Buttons */
.share-buttons {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
}

.share-label {
    color: var(--db-text-muted);
    font-size: 0.9rem;
}

.share-btn {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    color: white;
    text-decoration: none;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.share-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    color: white;
}

.share-btn.whatsapp { background: #25D366; }
.share-btn.telegram { background: #0088cc; }
.share-btn.facebook { background: #1877F2; }
.share-btn.twitter { background: #1DA1F2; }

/* User Avatar Small */
.user-avatar-sm {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: linear-gradient(135deg, var(--db-primary), #7c3aed);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    color: white;
    font-size: 0.8rem;
}

/* How It Works */
.how-it-works {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.step {
    display: flex;
    gap: 1rem;
}

.step-number {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--db-primary), #7c3aed);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: white;
    flex-shrink: 0;
}

.step-content h6 {
    margin-bottom: 0.25rem;
}

.step-content p {
    margin: 0;
    font-size: 0.85rem;
    color: var(--db-text-muted);
}

/* Reward Info */
.reward-info {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.reward-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem;
    background: var(--db-surface-light);
    border-radius: 10px;
}

.reward-icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}

.reward-icon.success { background: rgba(var(--db-success-rgb), 0.15); color: var(--db-success); }
.reward-icon.warning { background: rgba(var(--db-warning-rgb), 0.15); color: var(--db-warning); }
.reward-icon.info { background: rgba(var(--db-info-rgb), 0.15); color: var(--db-info); }

.reward-text {
    display: flex;
    flex-direction: column;
}

.reward-text strong {
    font-size: 0.95rem;
}

.reward-text span {
    font-size: 0.8rem;
    color: var(--db-text-muted);
}

/* Terms List */
.terms-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.terms-list li {
    padding: 0.5rem 0;
    padding-left: 1.5rem;
    position: relative;
    font-size: 0.85rem;
    color: var(--db-text-muted);
    border-bottom: 1px solid var(--db-border);
}

.terms-list li:last-child {
    border-bottom: none;
}

.terms-list li::before {
    content: '•';
    position: absolute;
    left: 0;
    color: var(--db-primary);
}
</style>

<script>
function copyReferralLink() {
    const input = document.getElementById('referralLink');
    input.select();
    document.execCommand('copy');
    showToast('Referral link copied!', 'success');
}
</script>

<?php require_once 'dashboard/includes/dashboard-footer.php'; ?>
