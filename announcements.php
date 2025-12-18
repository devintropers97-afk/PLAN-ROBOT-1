<?php
/**
 * ZYN Trade System - Announcements & News Feed
 * FASE 3: In-App Announcements Feed
 */
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/language.php';

$page_title = __('announcements_title');
require_once 'dashboard/includes/dashboard-header.php';

// Mock announcements data
$announcements = [
    [
        'id' => 1,
        'type' => 'update',
        'title' => 'New Strategy Released: QUANTUM-FLOW v2.0',
        'content' => 'We\'re excited to announce the release of QUANTUM-FLOW v2.0 with improved AI algorithms and better accuracy. This update includes enhanced market condition detection and adaptive signal timing.',
        'date' => '2024-12-17',
        'priority' => 'high',
        'read' => false,
        'image' => null,
        'link' => 'strategies.php',
        'link_text' => 'View Strategy'
    ],
    [
        'id' => 2,
        'type' => 'promo',
        'title' => 'Holiday Special: 50% OFF All Plans!',
        'content' => 'Celebrate the holiday season with us! Get 50% off on all subscription plans from December 20-31. Use code: HOLIDAY50 at checkout. Limited time offer - don\'t miss out!',
        'date' => '2024-12-16',
        'priority' => 'high',
        'read' => false,
        'image' => null,
        'link' => 'pricing.php',
        'link_text' => 'Upgrade Now'
    ],
    [
        'id' => 3,
        'type' => 'maintenance',
        'title' => 'Scheduled Maintenance: December 20, 2024',
        'content' => 'We will be performing scheduled maintenance on December 20, 2024 from 02:00 - 04:00 WIB. During this time, robot trading will be temporarily paused. All pending trades will be handled safely.',
        'date' => '2024-12-15',
        'priority' => 'medium',
        'read' => true,
        'image' => null,
        'link' => null,
        'link_text' => null
    ],
    [
        'id' => 4,
        'type' => 'news',
        'title' => 'Market Update: High Volatility Expected',
        'content' => 'Due to upcoming FOMC meeting and end-of-year market conditions, we expect higher than usual volatility. We recommend using conservative strategies and lower trade amounts during this period.',
        'date' => '2024-12-14',
        'priority' => 'medium',
        'read' => true,
        'image' => null,
        'link' => 'market-sentiment.php',
        'link_text' => 'View Market Sentiment'
    ],
    [
        'id' => 5,
        'type' => 'feature',
        'title' => 'New Feature: ZYN Academy Launched!',
        'content' => 'Learn trading the right way with our brand new ZYN Academy. Access comprehensive courses on trading basics, money management, psychology, and advanced strategies.',
        'date' => '2024-12-12',
        'priority' => 'low',
        'read' => true,
        'image' => null,
        'link' => 'academy.php',
        'link_text' => 'Start Learning'
    ],
    [
        'id' => 6,
        'type' => 'tip',
        'title' => 'Pro Tip: Best Trading Hours',
        'content' => 'Did you know? The best trading hours for EUR/USD are during London-New York overlap (19:00-23:00 WIB). Use the Best Hours schedule mode to maximize your win rate!',
        'date' => '2024-12-10',
        'priority' => 'low',
        'read' => true,
        'image' => null,
        'link' => 'settings.php',
        'link_text' => 'Adjust Settings'
    ],
    [
        'id' => 7,
        'type' => 'milestone',
        'title' => 'ZYN Trade System Reaches 5,000 Users!',
        'content' => 'We\'re thrilled to announce that ZYN Trade System has reached 5,000 active users! Thank you for being part of our community. Stay tuned for special rewards and giveaways.',
        'date' => '2024-12-08',
        'priority' => 'low',
        'read' => true,
        'image' => null,
        'link' => null,
        'link_text' => null
    ],
];

$typeIcons = [
    'update' => 'fa-rocket',
    'promo' => 'fa-tag',
    'maintenance' => 'fa-wrench',
    'news' => 'fa-newspaper',
    'feature' => 'fa-star',
    'tip' => 'fa-lightbulb',
    'milestone' => 'fa-trophy',
];

$typeColors = [
    'update' => 'primary',
    'promo' => 'warning',
    'maintenance' => 'danger',
    'news' => 'info',
    'feature' => 'success',
    'tip' => 'info',
    'milestone' => 'success',
];

$selectedType = $_GET['type'] ?? 'all';
$unreadCount = count(array_filter($announcements, fn($a) => !$a['read']));
?>

<!-- Page Header -->
<div class="db-page-header">
    <div>
        <h1 class="db-page-title"><i class="fas fa-bullhorn"></i> Announcements</h1>
        <p class="db-page-subtitle">Stay updated with the latest news and updates</p>
    </div>
    <?php if ($unreadCount > 0): ?>
    <span class="db-badge primary">
        <?php echo $unreadCount; ?> Unread
    </span>
    <?php endif; ?>
</div>

<!-- Filter Tabs -->
<div class="db-card mb-4 db-fade-in">
    <div class="db-card-body py-3">
        <div class="d-flex flex-wrap gap-2 justify-content-center">
            <a href="?type=all" class="db-btn db-btn-sm <?php echo $selectedType === 'all' ? 'db-btn-primary' : 'db-btn-outline'; ?>">
                <i class="fas fa-th-large me-1"></i> All
            </a>
            <a href="?type=update" class="db-btn db-btn-sm <?php echo $selectedType === 'update' ? 'db-btn-primary' : 'db-btn-outline'; ?>">
                <i class="fas fa-rocket me-1"></i> Updates
            </a>
            <a href="?type=promo" class="db-btn db-btn-sm <?php echo $selectedType === 'promo' ? 'db-btn-primary' : 'db-btn-outline'; ?>">
                <i class="fas fa-tag me-1"></i> Promos
            </a>
            <a href="?type=news" class="db-btn db-btn-sm <?php echo $selectedType === 'news' ? 'db-btn-primary' : 'db-btn-outline'; ?>">
                <i class="fas fa-newspaper me-1"></i> News
            </a>
            <a href="?type=maintenance" class="db-btn db-btn-sm <?php echo $selectedType === 'maintenance' ? 'db-btn-primary' : 'db-btn-outline'; ?>">
                <i class="fas fa-wrench me-1"></i> Maintenance
            </a>
        </div>
    </div>
</div>

<!-- Pinned Announcement (High Priority Unread) -->
<?php
$pinnedAnnouncement = array_filter($announcements, fn($a) => $a['priority'] === 'high' && !$a['read']);
$pinnedAnnouncement = reset($pinnedAnnouncement);
if ($pinnedAnnouncement):
?>
<div class="pinned-announcement db-fade-in mb-4">
    <div class="pinned-badge">
        <i class="fas fa-thumbtack me-1"></i> PINNED
    </div>
    <div class="pinned-content">
        <div class="pinned-icon <?php echo $typeColors[$pinnedAnnouncement['type']]; ?>">
            <i class="fas <?php echo $typeIcons[$pinnedAnnouncement['type']]; ?>"></i>
        </div>
        <div class="pinned-text">
            <h4><?php echo htmlspecialchars($pinnedAnnouncement['title']); ?></h4>
            <p><?php echo htmlspecialchars($pinnedAnnouncement['content']); ?></p>
            <?php if ($pinnedAnnouncement['link']): ?>
            <a href="<?php echo $pinnedAnnouncement['link']; ?>" class="db-btn db-btn-primary db-btn-sm">
                <?php echo $pinnedAnnouncement['link_text']; ?> <i class="fas fa-arrow-right ms-1"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Announcements List -->
<div class="announcements-list">
    <?php
    $filteredAnnouncements = $selectedType === 'all'
        ? $announcements
        : array_filter($announcements, fn($a) => $a['type'] === $selectedType);

    if (empty($filteredAnnouncements)):
    ?>
    <div class="db-card db-fade-in">
        <div class="db-card-body text-center py-5">
            <i class="fas fa-inbox fa-3x text-muted mb-3" style="opacity: 0.3;"></i>
            <p class="text-muted">No announcements in this category</p>
        </div>
    </div>
    <?php else: ?>
    <?php foreach ($filteredAnnouncements as $announcement): ?>
    <div class="db-card mb-3 db-fade-in announcement-card <?php echo !$announcement['read'] ? 'unread' : ''; ?>" data-type="<?php echo $announcement['type']; ?>">
        <div class="db-card-body">
            <div class="announcement-header">
                <div class="announcement-icon <?php echo $typeColors[$announcement['type']]; ?>">
                    <i class="fas <?php echo $typeIcons[$announcement['type']]; ?>"></i>
                </div>
                <div class="announcement-meta">
                    <span class="db-badge <?php echo $typeColors[$announcement['type']]; ?>">
                        <?php echo ucfirst($announcement['type']); ?>
                    </span>
                    <span class="announcement-date">
                        <i class="fas fa-clock me-1"></i>
                        <?php echo date('d M Y', strtotime($announcement['date'])); ?>
                    </span>
                    <?php if (!$announcement['read']): ?>
                    <span class="unread-dot"></span>
                    <?php endif; ?>
                </div>
            </div>

            <h5 class="announcement-title"><?php echo htmlspecialchars($announcement['title']); ?></h5>
            <p class="announcement-content"><?php echo htmlspecialchars($announcement['content']); ?></p>

            <?php if ($announcement['link']): ?>
            <div class="announcement-footer">
                <a href="<?php echo $announcement['link']; ?>" class="db-btn db-btn-outline db-btn-sm">
                    <?php echo $announcement['link_text']; ?> <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
            <?php endif; ?>

            <?php if ($announcement['priority'] === 'high'): ?>
            <div class="priority-indicator high">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Load More Button -->
<div class="text-center mt-4 db-fade-in">
    <button class="db-btn db-btn-outline" onclick="loadMore()">
        <i class="fas fa-chevron-down me-1"></i> Load More
    </button>
</div>

<style>
/* Pinned Announcement */
.pinned-announcement {
    background: linear-gradient(135deg, rgba(var(--db-primary-rgb), 0.15), rgba(124, 58, 237, 0.15));
    border: 2px solid rgba(var(--db-primary-rgb), 0.3);
    border-radius: 16px;
    padding: 1.5rem;
    position: relative;
    overflow: hidden;
}

.pinned-badge {
    position: absolute;
    top: 0;
    right: 0;
    background: linear-gradient(135deg, var(--db-primary), #7c3aed);
    color: white;
    padding: 0.5rem 1rem;
    font-size: 0.75rem;
    font-weight: 600;
    border-bottom-left-radius: 12px;
}

.pinned-content {
    display: flex;
    gap: 1.5rem;
    align-items: flex-start;
}

.pinned-icon {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.pinned-icon.primary { background: rgba(var(--db-primary-rgb), 0.2); color: var(--db-primary); }
.pinned-icon.warning { background: rgba(var(--db-warning-rgb), 0.2); color: var(--db-warning); }
.pinned-icon.success { background: rgba(var(--db-success-rgb), 0.2); color: var(--db-success); }
.pinned-icon.danger { background: rgba(var(--db-danger-rgb), 0.2); color: var(--db-danger); }
.pinned-icon.info { background: rgba(var(--db-info-rgb), 0.2); color: var(--db-info); }

.pinned-text h4 {
    margin-bottom: 0.75rem;
    font-weight: 600;
}

.pinned-text p {
    color: var(--db-text-muted);
    margin-bottom: 1rem;
    line-height: 1.6;
}

/* Announcement Card */
.announcement-card {
    position: relative;
    transition: all 0.3s ease;
}

.announcement-card:hover {
    transform: translateX(5px);
}

.announcement-card.unread {
    border-left: 3px solid var(--db-primary);
}

.announcement-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.announcement-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}

.announcement-icon.primary { background: rgba(var(--db-primary-rgb), 0.15); color: var(--db-primary); }
.announcement-icon.warning { background: rgba(var(--db-warning-rgb), 0.15); color: var(--db-warning); }
.announcement-icon.success { background: rgba(var(--db-success-rgb), 0.15); color: var(--db-success); }
.announcement-icon.danger { background: rgba(var(--db-danger-rgb), 0.15); color: var(--db-danger); }
.announcement-icon.info { background: rgba(var(--db-info-rgb), 0.15); color: var(--db-info); }

.announcement-meta {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex: 1;
}

.announcement-date {
    color: var(--db-text-muted);
    font-size: 0.85rem;
}

.unread-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--db-primary);
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.2); }
}

.announcement-title {
    font-weight: 600;
    margin-bottom: 0.75rem;
}

.announcement-content {
    color: var(--db-text-muted);
    line-height: 1.6;
    margin-bottom: 1rem;
}

.announcement-footer {
    padding-top: 0.75rem;
    border-top: 1px solid var(--db-border);
}

.priority-indicator {
    position: absolute;
    top: 1rem;
    right: 1rem;
    font-size: 1.25rem;
}

.priority-indicator.high {
    color: var(--db-danger);
}

/* Responsive */
@media (max-width: 768px) {
    .pinned-content {
        flex-direction: column;
    }

    .pinned-icon {
        width: 48px;
        height: 48px;
    }
}
</style>

<script>
function loadMore() {
    showToast('Loading more announcements...', 'info');
    // In production: fetch more announcements via AJAX
}

// Mark as read when scrolled into view
document.querySelectorAll('.announcement-card.unread').forEach(card => {
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.classList.remove('unread');
                    entry.target.querySelector('.unread-dot')?.remove();
                }, 2000);
                observer.unobserve(entry.target);
            }
        });
    });
    observer.observe(card);
});
</script>

<?php require_once 'dashboard/includes/dashboard-footer.php'; ?>
