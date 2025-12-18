<?php
/**
 * ZYN Trade System - Academy / Education Center
 * FASE 3: ZYN Academy / Edukasi (FINAL ZYN - Inovasi #9)
 */
$page_title = __('academy_title');
require_once 'dashboard/includes/dashboard-header.php';

// Academy Modules
$modules = [
    [
        'id' => 1,
        'category' => 'basic',
        'title' => 'Pengenalan Trading Binary Options',
        'description' => 'Pelajari dasar-dasar trading fixed time dan cara kerjanya',
        'duration' => '15 min',
        'level' => 'Beginner',
        'icon' => 'fa-graduation-cap',
        'lessons' => 5,
        'free' => true,
        'content' => [
            ['title' => 'Apa itu Binary Options?', 'type' => 'video', 'duration' => '3 min'],
            ['title' => 'Call vs Put - Kapan Memilih?', 'type' => 'article', 'duration' => '5 min'],
            ['title' => 'Memahami Expiry Time', 'type' => 'video', 'duration' => '3 min'],
            ['title' => 'Payout dan Profit Calculation', 'type' => 'article', 'duration' => '2 min'],
            ['title' => 'Quiz: Basic Trading', 'type' => 'quiz', 'duration' => '2 min'],
        ]
    ],
    [
        'id' => 2,
        'category' => 'basic',
        'title' => 'Platform OlympTrade 101',
        'description' => 'Panduan lengkap menggunakan platform OlympTrade',
        'duration' => '20 min',
        'level' => 'Beginner',
        'icon' => 'fa-desktop',
        'lessons' => 6,
        'free' => true,
        'content' => [
            ['title' => 'Navigasi Dashboard OlympTrade', 'type' => 'video', 'duration' => '4 min'],
            ['title' => 'Deposit & Withdrawal', 'type' => 'article', 'duration' => '3 min'],
            ['title' => 'Setting Chart & Indicators', 'type' => 'video', 'duration' => '5 min'],
            ['title' => 'Account Types: Demo vs Real', 'type' => 'article', 'duration' => '3 min'],
            ['title' => 'Mobile App vs Desktop', 'type' => 'article', 'duration' => '2 min'],
            ['title' => 'Quiz: Platform Mastery', 'type' => 'quiz', 'duration' => '3 min'],
        ]
    ],
    [
        'id' => 3,
        'category' => 'money',
        'title' => 'Money Management Essentials',
        'description' => 'Rahasia survive dan profit konsisten dengan money management',
        'duration' => '25 min',
        'level' => 'Intermediate',
        'icon' => 'fa-coins',
        'lessons' => 7,
        'free' => true,
        'content' => [
            ['title' => 'Rule 1-3%: Jangan Over-Trade!', 'type' => 'video', 'duration' => '4 min'],
            ['title' => 'Fixed Amount vs Percentage', 'type' => 'article', 'duration' => '3 min'],
            ['title' => 'Martingale: Pedang Bermata Dua', 'type' => 'video', 'duration' => '5 min'],
            ['title' => 'Take Profit & Stop Loss', 'type' => 'article', 'duration' => '4 min'],
            ['title' => 'Recovery Plan Setelah Loss', 'type' => 'video', 'duration' => '4 min'],
            ['title' => 'Compound vs Withdraw', 'type' => 'article', 'duration' => '3 min'],
            ['title' => 'Quiz: Money Management', 'type' => 'quiz', 'duration' => '2 min'],
        ]
    ],
    [
        'id' => 4,
        'category' => 'psychology',
        'title' => 'Trading Psychology Mastery',
        'description' => 'Kontrol emosi dan mental untuk hasil trading optimal',
        'duration' => '30 min',
        'level' => 'Intermediate',
        'icon' => 'fa-brain',
        'lessons' => 8,
        'free' => false,
        'content' => [
            ['title' => 'Fear & Greed: Musuh Terbesar', 'type' => 'video', 'duration' => '5 min'],
            ['title' => 'FOMO: Fear of Missing Out', 'type' => 'article', 'duration' => '3 min'],
            ['title' => 'Revenge Trading Prevention', 'type' => 'video', 'duration' => '4 min'],
            ['title' => 'Building Trading Discipline', 'type' => 'article', 'duration' => '4 min'],
            ['title' => 'Mindset: Trader vs Gambler', 'type' => 'video', 'duration' => '4 min'],
            ['title' => 'Daily Routine untuk Trader', 'type' => 'article', 'duration' => '3 min'],
            ['title' => 'Dealing with Losses', 'type' => 'video', 'duration' => '4 min'],
            ['title' => 'Quiz: Psychology', 'type' => 'quiz', 'duration' => '3 min'],
        ]
    ],
    [
        'id' => 5,
        'category' => 'strategy',
        'title' => 'Technical Analysis Deep Dive',
        'description' => 'Kuasai analisa teknikal untuk prediksi market yang lebih akurat',
        'duration' => '45 min',
        'level' => 'Advanced',
        'icon' => 'fa-chart-line',
        'lessons' => 10,
        'free' => false,
        'content' => [
            ['title' => 'Support & Resistance', 'type' => 'video', 'duration' => '5 min'],
            ['title' => 'Trend Lines & Channels', 'type' => 'video', 'duration' => '5 min'],
            ['title' => 'Candlestick Patterns', 'type' => 'article', 'duration' => '5 min'],
            ['title' => 'Moving Averages Strategy', 'type' => 'video', 'duration' => '4 min'],
            ['title' => 'RSI: Overbought & Oversold', 'type' => 'video', 'duration' => '4 min'],
            ['title' => 'MACD Signal Interpretation', 'type' => 'article', 'duration' => '4 min'],
            ['title' => 'Bollinger Bands Breakout', 'type' => 'video', 'duration' => '5 min'],
            ['title' => 'Stochastic Oscillator', 'type' => 'article', 'duration' => '4 min'],
            ['title' => 'Multi-Timeframe Analysis', 'type' => 'video', 'duration' => '5 min'],
            ['title' => 'Quiz: Technical Analysis', 'type' => 'quiz', 'duration' => '4 min'],
        ]
    ],
    [
        'id' => 6,
        'category' => 'strategy',
        'title' => 'ZYN Strategies Explained',
        'description' => 'Pelajari cara kerja 10 strategi ZYN secara mendalam',
        'duration' => '60 min',
        'level' => 'Advanced',
        'icon' => 'fa-chess',
        'lessons' => 12,
        'free' => false,
        'content' => [
            ['title' => 'Overview: 10 ZYN Strategies', 'type' => 'video', 'duration' => '5 min'],
            ['title' => 'ORACLE-PRIME Deep Dive', 'type' => 'video', 'duration' => '5 min'],
            ['title' => 'NEXUS-WAVE Explained', 'type' => 'article', 'duration' => '5 min'],
            ['title' => 'STEALTH-MODE Strategy', 'type' => 'video', 'duration' => '5 min'],
            ['title' => 'PHOENIX-X1 Recovery Logic', 'type' => 'article', 'duration' => '5 min'],
            ['title' => 'VORTEX-PRO Momentum', 'type' => 'video', 'duration' => '5 min'],
            ['title' => 'TITAN-PULSE Breakdown', 'type' => 'article', 'duration' => '5 min'],
            ['title' => 'SHADOW-EDGE Scalping', 'type' => 'video', 'duration' => '5 min'],
            ['title' => 'BLITZ-SIGNAL Speed', 'type' => 'article', 'duration' => '5 min'],
            ['title' => 'APEX-HUNTER Precision', 'type' => 'video', 'duration' => '5 min'],
            ['title' => 'QUANTUM-FLOW Algorithm', 'type' => 'article', 'duration' => '5 min'],
            ['title' => 'Choosing the Right Strategy', 'type' => 'video', 'duration' => '5 min'],
        ]
    ],
    [
        'id' => 7,
        'category' => 'advanced',
        'title' => 'Robot Optimization Secrets',
        'description' => 'Tips dan trik untuk memaksimalkan performa robot trading',
        'duration' => '35 min',
        'level' => 'Expert',
        'icon' => 'fa-robot',
        'lessons' => 8,
        'free' => false,
        'content' => [
            ['title' => 'Best Trading Hours Analysis', 'type' => 'video', 'duration' => '5 min'],
            ['title' => 'Market Condition Detection', 'type' => 'article', 'duration' => '4 min'],
            ['title' => 'Optimal Amount per Trade', 'type' => 'video', 'duration' => '5 min'],
            ['title' => 'Multi-Strategy Rotation', 'type' => 'article', 'duration' => '4 min'],
            ['title' => 'News Event Navigation', 'type' => 'video', 'duration' => '5 min'],
            ['title' => 'Performance Tracking Tips', 'type' => 'article', 'duration' => '4 min'],
            ['title' => 'When to Stop & Rest', 'type' => 'video', 'duration' => '4 min'],
            ['title' => 'Quiz: Robot Mastery', 'type' => 'quiz', 'duration' => '4 min'],
        ]
    ]
];

$categories = [
    'basic' => ['name' => 'Trading Basics', 'icon' => 'fa-book', 'color' => 'info'],
    'money' => ['name' => 'Money Management', 'icon' => 'fa-dollar-sign', 'color' => 'success'],
    'psychology' => ['name' => 'Psychology', 'icon' => 'fa-brain', 'color' => 'warning'],
    'strategy' => ['name' => 'Strategy', 'icon' => 'fa-chess', 'color' => 'primary'],
    'advanced' => ['name' => 'Advanced', 'icon' => 'fa-rocket', 'color' => 'danger']
];

// Calculate user progress (mock data)
$userProgress = [
    1 => ['completed' => 5, 'total' => 5, 'score' => 85],
    2 => ['completed' => 3, 'total' => 6, 'score' => 0],
    3 => ['completed' => 0, 'total' => 7, 'score' => 0],
    4 => ['completed' => 0, 'total' => 8, 'score' => 0],
    5 => ['completed' => 0, 'total' => 10, 'score' => 0],
    6 => ['completed' => 0, 'total' => 12, 'score' => 0],
    7 => ['completed' => 0, 'total' => 8, 'score' => 0],
];

$totalLessons = array_sum(array_column($modules, 'lessons'));
$completedLessons = array_sum(array_map(fn($p) => $p['completed'], $userProgress));
$overallProgress = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;

$userPackage = $currentUser['package'] ?? 'free';
$isPremium = in_array($userPackage, ['pro', 'elite', 'vip']);
?>

<!-- Page Header -->
<div class="db-page-header">
    <div>
        <h1 class="db-page-title"><i class="fas fa-graduation-cap"></i> ZYN Academy</h1>
        <p class="db-page-subtitle">Master trading dengan materi edukasi lengkap</p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <div class="db-badge success">
            <i class="fas fa-star me-1"></i> Progress: <?php echo $overallProgress; ?>%
        </div>
    </div>
</div>

<!-- Progress Overview -->
<div class="db-card mb-4 db-fade-in">
    <div class="db-card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5 class="mb-2"><i class="fas fa-chart-pie me-2"></i>Your Learning Progress</h5>
                <div class="db-progress-bar" style="height: 12px; margin-bottom: 0.5rem;">
                    <div class="db-progress-fill success" style="width: <?php echo $overallProgress; ?>%;"></div>
                </div>
                <div class="d-flex justify-content-between text-muted small">
                    <span><?php echo $completedLessons; ?> / <?php echo $totalLessons; ?> lessons completed</span>
                    <span><?php echo $overallProgress; ?>% complete</span>
                </div>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <div class="d-flex gap-3 justify-content-md-end">
                    <div class="text-center">
                        <div class="h4 mb-0 text-primary"><?php echo count(array_filter($userProgress, fn($p) => $p['completed'] == $p['total'])); ?></div>
                        <small class="text-muted">Completed</small>
                    </div>
                    <div class="text-center">
                        <div class="h4 mb-0 text-warning"><?php echo count(array_filter($userProgress, fn($p) => $p['completed'] > 0 && $p['completed'] < $p['total'])); ?></div>
                        <small class="text-muted">In Progress</small>
                    </div>
                    <div class="text-center">
                        <div class="h4 mb-0 text-muted"><?php echo count(array_filter($userProgress, fn($p) => $p['completed'] == 0)); ?></div>
                        <small class="text-muted">Not Started</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Category Filter -->
<div class="db-card mb-4 db-fade-in">
    <div class="db-card-body py-3">
        <div class="d-flex flex-wrap gap-2 justify-content-center">
            <button class="db-btn db-btn-primary db-btn-sm active" data-category="all">
                <i class="fas fa-th-large me-1"></i> All Courses
            </button>
            <?php foreach ($categories as $key => $cat): ?>
            <button class="db-btn db-btn-outline db-btn-sm" data-category="<?php echo $key; ?>">
                <i class="fas <?php echo $cat['icon']; ?> me-1"></i> <?php echo $cat['name']; ?>
            </button>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Course Grid -->
<div class="row g-4" id="courseGrid">
    <?php foreach ($modules as $module):
        $progress = $userProgress[$module['id']] ?? ['completed' => 0, 'total' => $module['lessons'], 'score' => 0];
        $progressPercent = $progress['total'] > 0 ? round(($progress['completed'] / $progress['total']) * 100) : 0;
        $isLocked = !$module['free'] && !$isPremium;
        $isCompleted = $progress['completed'] == $progress['total'];
        $isStarted = $progress['completed'] > 0;
    ?>
    <div class="col-md-6 col-lg-4 course-item" data-category="<?php echo $module['category']; ?>">
        <div class="db-card h-100 <?php echo $isCompleted ? 'border-success' : ''; ?> <?php echo $isLocked ? 'locked' : ''; ?>">
            <div class="db-card-body">
                <!-- Module Header -->
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="module-icon <?php echo $categories[$module['category']]['color']; ?>">
                        <i class="fas <?php echo $module['icon']; ?>"></i>
                    </div>
                    <div class="d-flex gap-1">
                        <span class="db-badge <?php echo $categories[$module['category']]['color']; ?>">
                            <?php echo $categories[$module['category']]['name']; ?>
                        </span>
                        <?php if ($module['free']): ?>
                        <span class="db-badge success">FREE</span>
                        <?php else: ?>
                        <span class="db-badge warning"><i class="fas fa-crown"></i> PRO</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Module Title & Description -->
                <h5 class="module-title"><?php echo htmlspecialchars($module['title']); ?></h5>
                <p class="module-description text-muted small"><?php echo htmlspecialchars($module['description']); ?></p>

                <!-- Module Stats -->
                <div class="d-flex gap-3 mb-3 text-muted small">
                    <span><i class="fas fa-clock me-1"></i> <?php echo $module['duration']; ?></span>
                    <span><i class="fas fa-book me-1"></i> <?php echo $module['lessons']; ?> lessons</span>
                    <span><i class="fas fa-signal me-1"></i> <?php echo $module['level']; ?></span>
                </div>

                <!-- Progress Bar -->
                <div class="db-progress-bar mb-2" style="height: 6px;">
                    <div class="db-progress-fill <?php echo $isCompleted ? 'success' : 'primary'; ?>" style="width: <?php echo $progressPercent; ?>%;"></div>
                </div>
                <div class="d-flex justify-content-between small mb-3">
                    <span class="text-muted"><?php echo $progress['completed']; ?>/<?php echo $progress['total']; ?> completed</span>
                    <?php if ($isCompleted && $progress['score'] > 0): ?>
                    <span class="text-success"><i class="fas fa-trophy me-1"></i>Score: <?php echo $progress['score']; ?>%</span>
                    <?php endif; ?>
                </div>

                <!-- Action Button -->
                <?php if ($isLocked): ?>
                <a href="pricing.php" class="db-btn db-btn-warning w-100">
                    <i class="fas fa-lock me-1"></i> Upgrade to Unlock
                </a>
                <?php elseif ($isCompleted): ?>
                <button class="db-btn db-btn-success w-100" onclick="openModule(<?php echo $module['id']; ?>)">
                    <i class="fas fa-redo me-1"></i> Review Module
                </button>
                <?php elseif ($isStarted): ?>
                <button class="db-btn db-btn-primary w-100" onclick="openModule(<?php echo $module['id']; ?>)">
                    <i class="fas fa-play me-1"></i> Continue Learning
                </button>
                <?php else: ?>
                <button class="db-btn db-btn-outline w-100" onclick="openModule(<?php echo $module['id']; ?>)">
                    <i class="fas fa-play me-1"></i> Start Module
                </button>
                <?php endif; ?>
            </div>

            <?php if ($isCompleted): ?>
            <div class="position-absolute top-0 end-0 m-2">
                <span class="badge bg-success rounded-pill"><i class="fas fa-check"></i></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Certification Section -->
<div class="db-card mt-4 db-fade-in">
    <div class="db-card-header">
        <h5 class="db-card-title"><i class="fas fa-certificate"></i> ZYN Trading Certificate</h5>
    </div>
    <div class="db-card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5>Dapatkan Sertifikat ZYN Trader!</h5>
                <p class="text-muted">Selesaikan semua module dan quiz dengan score minimal 80% untuk mendapatkan sertifikat digital yang bisa kamu share di social media.</p>
                <div class="d-flex gap-3">
                    <div>
                        <span class="text-muted">Progress:</span>
                        <strong><?php echo $overallProgress; ?>%</strong>
                    </div>
                    <div>
                        <span class="text-muted">Min Score:</span>
                        <strong>80%</strong>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <?php if ($overallProgress >= 100): ?>
                <button class="db-btn db-btn-success">
                    <i class="fas fa-download me-1"></i> Download Certificate
                </button>
                <?php else: ?>
                <button class="db-btn db-btn-outline" disabled>
                    <i class="fas fa-lock me-1"></i> Complete All Modules
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.module-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.module-icon.info { background: rgba(var(--db-info-rgb), 0.15); color: var(--db-info); }
.module-icon.success { background: rgba(var(--db-success-rgb), 0.15); color: var(--db-success); }
.module-icon.warning { background: rgba(var(--db-warning-rgb), 0.15); color: var(--db-warning); }
.module-icon.primary { background: rgba(var(--db-primary-rgb), 0.15); color: var(--db-primary); }
.module-icon.danger { background: rgba(var(--db-danger-rgb), 0.15); color: var(--db-danger); }

.module-title {
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.module-description {
    line-height: 1.5;
}

.db-card.locked {
    opacity: 0.7;
}

.db-card.border-success {
    border-color: var(--db-success) !important;
}

.course-item {
    transition: all 0.3s ease;
}

.course-item.hidden {
    display: none;
}
</style>

<script>
// Category filter
document.querySelectorAll('[data-category]').forEach(btn => {
    btn.addEventListener('click', function() {
        const category = this.dataset.category;

        // Update active state
        document.querySelectorAll('[data-category]').forEach(b => {
            b.classList.remove('active', 'db-btn-primary');
            b.classList.add('db-btn-outline');
        });
        this.classList.add('active', 'db-btn-primary');
        this.classList.remove('db-btn-outline');

        // Filter courses
        document.querySelectorAll('.course-item').forEach(item => {
            if (category === 'all' || item.dataset.category === category) {
                item.classList.remove('hidden');
            } else {
                item.classList.add('hidden');
            }
        });
    });
});

function openModule(moduleId) {
    // In production, this would open a modal or navigate to module page
    showToast('Module loading... Coming soon!', 'info');
}
</script>

<?php require_once 'dashboard/includes/dashboard-footer.php'; ?>
