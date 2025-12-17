<?php
/**
 * ZYN Trade System - Badges & Achievements
 *
 * CARA KERJA:
 * 1. System otomatis check achievements setiap kali user trading
 * 2. Badge diberikan berdasarkan milestone tertentu
 * 3. Badge bisa ditampilkan di profile dan leaderboard
 *
 * CARA PAKAI:
 * - Check badge: BadgeSystem::checkAndAward($user_id)
 * - Get badges: BadgeSystem::getUserBadges($user_id)
 * - Render badge: BadgeSystem::renderBadge('badge_id')
 */

class BadgeSystem {
    private static $db = null;

    // Definisi semua badges
    private static $badges = [
        // === TRADING MILESTONES ===
        'first_trade' => [
            'name' => 'First Trade',
            'name_id' => 'Trade Pertama',
            'description' => 'Complete your first trade',
            'description_id' => 'Selesaikan trade pertama',
            'icon' => 'fa-flag-checkered',
            'color' => '#28a745',
            'category' => 'milestone',
            'rarity' => 'common',
            'points' => 10
        ],
        'trader_10' => [
            'name' => '10 Trades',
            'name_id' => '10 Trades',
            'description' => 'Complete 10 trades',
            'description_id' => 'Selesaikan 10 trades',
            'icon' => 'fa-chart-line',
            'color' => '#17a2b8',
            'category' => 'milestone',
            'rarity' => 'common',
            'points' => 25
        ],
        'trader_50' => [
            'name' => '50 Trades',
            'name_id' => '50 Trades',
            'description' => 'Complete 50 trades',
            'description_id' => 'Selesaikan 50 trades',
            'icon' => 'fa-chart-bar',
            'color' => '#6f42c1',
            'category' => 'milestone',
            'rarity' => 'uncommon',
            'points' => 50
        ],
        'trader_100' => [
            'name' => 'Century Trader',
            'name_id' => 'Trader 100',
            'description' => 'Complete 100 trades',
            'description_id' => 'Selesaikan 100 trades',
            'icon' => 'fa-award',
            'color' => '#fd7e14',
            'category' => 'milestone',
            'rarity' => 'rare',
            'points' => 100
        ],
        'trader_500' => [
            'name' => 'Trading Master',
            'name_id' => 'Master Trading',
            'description' => 'Complete 500 trades',
            'description_id' => 'Selesaikan 500 trades',
            'icon' => 'fa-crown',
            'color' => '#ffc107',
            'category' => 'milestone',
            'rarity' => 'epic',
            'points' => 250
        ],
        'trader_1000' => [
            'name' => 'Trading Legend',
            'name_id' => 'Legenda Trading',
            'description' => 'Complete 1000 trades',
            'description_id' => 'Selesaikan 1000 trades',
            'icon' => 'fa-gem',
            'color' => '#e83e8c',
            'category' => 'milestone',
            'rarity' => 'legendary',
            'points' => 500
        ],

        // === PROFIT MILESTONES ===
        'profit_100' => [
            'name' => '$100 Profit',
            'name_id' => 'Profit $100',
            'description' => 'Earn $100 total profit',
            'description_id' => 'Raih total profit $100',
            'icon' => 'fa-dollar-sign',
            'color' => '#28a745',
            'category' => 'profit',
            'rarity' => 'common',
            'points' => 25
        ],
        'profit_500' => [
            'name' => '$500 Profit',
            'name_id' => 'Profit $500',
            'description' => 'Earn $500 total profit',
            'description_id' => 'Raih total profit $500',
            'icon' => 'fa-money-bill-wave',
            'color' => '#20c997',
            'category' => 'profit',
            'rarity' => 'uncommon',
            'points' => 75
        ],
        'profit_1000' => [
            'name' => '$1K Profit Club',
            'name_id' => 'Klub $1K',
            'description' => 'Earn $1,000 total profit',
            'description_id' => 'Raih total profit $1,000',
            'icon' => 'fa-sack-dollar',
            'color' => '#6f42c1',
            'category' => 'profit',
            'rarity' => 'rare',
            'points' => 150
        ],
        'profit_5000' => [
            'name' => '$5K Elite',
            'name_id' => 'Elite $5K',
            'description' => 'Earn $5,000 total profit',
            'description_id' => 'Raih total profit $5,000',
            'icon' => 'fa-coins',
            'color' => '#fd7e14',
            'category' => 'profit',
            'rarity' => 'epic',
            'points' => 300
        ],
        'profit_10000' => [
            'name' => '$10K Legend',
            'name_id' => 'Legenda $10K',
            'description' => 'Earn $10,000 total profit',
            'description_id' => 'Raih total profit $10,000',
            'icon' => 'fa-piggy-bank',
            'color' => '#ffc107',
            'category' => 'profit',
            'rarity' => 'legendary',
            'points' => 500
        ],

        // === WIN RATE BADGES ===
        'winrate_60' => [
            'name' => 'Good Trader',
            'name_id' => 'Trader Baik',
            'description' => 'Maintain 60%+ win rate (min 20 trades)',
            'description_id' => 'Pertahankan win rate 60%+ (min 20 trades)',
            'icon' => 'fa-thumbs-up',
            'color' => '#17a2b8',
            'category' => 'skill',
            'rarity' => 'uncommon',
            'points' => 50
        ],
        'winrate_70' => [
            'name' => 'Pro Trader',
            'name_id' => 'Trader Pro',
            'description' => 'Maintain 70%+ win rate (min 50 trades)',
            'description_id' => 'Pertahankan win rate 70%+ (min 50 trades)',
            'icon' => 'fa-star',
            'color' => '#6f42c1',
            'category' => 'skill',
            'rarity' => 'rare',
            'points' => 100
        ],
        'winrate_80' => [
            'name' => 'Expert Trader',
            'name_id' => 'Trader Expert',
            'description' => 'Maintain 80%+ win rate (min 100 trades)',
            'description_id' => 'Pertahankan win rate 80%+ (min 100 trades)',
            'icon' => 'fa-trophy',
            'color' => '#fd7e14',
            'category' => 'skill',
            'rarity' => 'epic',
            'points' => 200
        ],
        'winrate_90' => [
            'name' => 'Master Trader',
            'name_id' => 'Trader Master',
            'description' => 'Maintain 90%+ win rate (min 200 trades)',
            'description_id' => 'Pertahankan win rate 90%+ (min 200 trades)',
            'icon' => 'fa-fire',
            'color' => '#dc3545',
            'category' => 'skill',
            'rarity' => 'legendary',
            'points' => 400
        ],

        // === STREAK BADGES ===
        'streak_5' => [
            'name' => 'Hot Streak',
            'name_id' => 'Streak Panas',
            'description' => '5 winning trades in a row',
            'description_id' => '5 trade win berturut-turut',
            'icon' => 'fa-fire-alt',
            'color' => '#fd7e14',
            'category' => 'streak',
            'rarity' => 'uncommon',
            'points' => 40
        ],
        'streak_10' => [
            'name' => 'On Fire',
            'name_id' => 'Membara',
            'description' => '10 winning trades in a row',
            'description_id' => '10 trade win berturut-turut',
            'icon' => 'fa-burn',
            'color' => '#dc3545',
            'category' => 'streak',
            'rarity' => 'rare',
            'points' => 100
        ],
        'streak_20' => [
            'name' => 'Unstoppable',
            'name_id' => 'Tak Terhentikan',
            'description' => '20 winning trades in a row',
            'description_id' => '20 trade win berturut-turut',
            'icon' => 'fa-meteor',
            'color' => '#e83e8c',
            'category' => 'streak',
            'rarity' => 'legendary',
            'points' => 300
        ],

        // === SPECIAL BADGES ===
        'early_bird' => [
            'name' => 'Early Bird',
            'name_id' => 'Pendaftar Awal',
            'description' => 'Joined during beta period',
            'description_id' => 'Bergabung saat periode beta',
            'icon' => 'fa-egg',
            'color' => '#20c997',
            'category' => 'special',
            'rarity' => 'rare',
            'points' => 100
        ],
        'vip_member' => [
            'name' => 'VIP Member',
            'name_id' => 'Member VIP',
            'description' => 'Upgraded to VIP package',
            'description_id' => 'Upgrade ke paket VIP',
            'icon' => 'fa-crown',
            'color' => '#ffc107',
            'category' => 'special',
            'rarity' => 'rare',
            'points' => 75
        ],
        'referral_5' => [
            'name' => 'Influencer',
            'name_id' => 'Influencer',
            'description' => 'Refer 5 active users',
            'description_id' => 'Referensikan 5 user aktif',
            'icon' => 'fa-users',
            'color' => '#6f42c1',
            'category' => 'social',
            'rarity' => 'uncommon',
            'points' => 100
        ],
        'referral_20' => [
            'name' => 'Ambassador',
            'name_id' => 'Ambassador',
            'description' => 'Refer 20 active users',
            'description_id' => 'Referensikan 20 user aktif',
            'icon' => 'fa-user-tie',
            'color' => '#fd7e14',
            'category' => 'social',
            'rarity' => 'epic',
            'points' => 250
        ],
        'top_3_monthly' => [
            'name' => 'Monthly Champion',
            'name_id' => 'Juara Bulanan',
            'description' => 'Finish in top 3 monthly leaderboard',
            'description_id' => 'Masuk top 3 leaderboard bulanan',
            'icon' => 'fa-medal',
            'color' => '#ffc107',
            'category' => 'achievement',
            'rarity' => 'epic',
            'points' => 200
        ],
        'top_1_monthly' => [
            'name' => '#1 Champion',
            'name_id' => 'Juara 1',
            'description' => 'Finish #1 in monthly leaderboard',
            'description_id' => 'Finish #1 di leaderboard bulanan',
            'icon' => 'fa-chess-king',
            'color' => '#e83e8c',
            'category' => 'achievement',
            'rarity' => 'legendary',
            'points' => 500
        ],
        'consistency_30' => [
            'name' => 'Consistent Trader',
            'name_id' => 'Trader Konsisten',
            'description' => 'Trade for 30 consecutive days',
            'description_id' => 'Trading 30 hari berturut-turut',
            'icon' => 'fa-calendar-check',
            'color' => '#17a2b8',
            'category' => 'dedication',
            'rarity' => 'rare',
            'points' => 150
        ],
        'night_owl' => [
            'name' => 'Night Owl',
            'name_id' => 'Burung Hantu',
            'description' => 'Complete 50 trades between midnight and 5 AM',
            'description_id' => 'Selesaikan 50 trade antara jam 00:00-05:00',
            'icon' => 'fa-moon',
            'color' => '#6c757d',
            'category' => 'special',
            'rarity' => 'uncommon',
            'points' => 50
        ],
        'comeback_king' => [
            'name' => 'Comeback King',
            'name_id' => 'Raja Comeback',
            'description' => 'Recover from 3+ losing streak to profit',
            'description_id' => 'Bangkit dari 3+ losing streak ke profit',
            'icon' => 'fa-phoenix',
            'color' => '#dc3545',
            'category' => 'special',
            'rarity' => 'rare',
            'points' => 75
        ]
    ];

    /**
     * Initialize database connection
     */
    private static function getDB() {
        if (self::$db === null) {
            try {
                self::$db = new PDO(
                    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                    DB_USER,
                    DB_PASS,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
            } catch (PDOException $e) {
                error_log("Badge DB Error: " . $e->getMessage());
                return null;
            }
        }
        return self::$db;
    }

    /**
     * Get all badge definitions
     */
    public static function getAllBadges() {
        return self::$badges;
    }

    /**
     * Get badge by ID
     */
    public static function getBadge($badge_id) {
        return self::$badges[$badge_id] ?? null;
    }

    /**
     * Get user's earned badges
     */
    public static function getUserBadges($user_id) {
        $db = self::getDB();
        if (!$db) return [];

        $stmt = $db->prepare("
            SELECT badge_id, earned_at
            FROM user_badges
            WHERE user_id = ?
            ORDER BY earned_at DESC
        ");
        $stmt->execute([$user_id]);
        $earned = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $badges = [];
        foreach ($earned as $row) {
            if (isset(self::$badges[$row['badge_id']])) {
                $badge = self::$badges[$row['badge_id']];
                $badge['id'] = $row['badge_id'];
                $badge['earned_at'] = $row['earned_at'];
                $badges[] = $badge;
            }
        }

        return $badges;
    }

    /**
     * Check if user has badge
     */
    public static function hasBadge($user_id, $badge_id) {
        $db = self::getDB();
        if (!$db) return false;

        $stmt = $db->prepare("SELECT 1 FROM user_badges WHERE user_id = ? AND badge_id = ?");
        $stmt->execute([$user_id, $badge_id]);
        return $stmt->fetch() !== false;
    }

    /**
     * Award badge to user
     */
    public static function awardBadge($user_id, $badge_id) {
        if (self::hasBadge($user_id, $badge_id)) {
            return false; // Already has badge
        }

        $db = self::getDB();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("
                INSERT INTO user_badges (user_id, badge_id, earned_at)
                VALUES (?, ?, NOW())
            ");
            $stmt->execute([$user_id, $badge_id]);

            // Update user's total badge points
            $badge = self::getBadge($badge_id);
            if ($badge) {
                $stmt = $db->prepare("
                    UPDATE users
                    SET badge_points = COALESCE(badge_points, 0) + ?
                    WHERE id = ?
                ");
                $stmt->execute([$badge['points'], $user_id]);
            }

            return true;
        } catch (PDOException $e) {
            error_log("Award Badge Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check and award badges based on user stats
     */
    public static function checkAndAward($user_id) {
        $db = self::getDB();
        if (!$db) return [];

        $awarded = [];

        // Get user stats
        $stmt = $db->prepare("
            SELECT
                u.*,
                COALESCE((SELECT COUNT(*) FROM trades WHERE user_id = u.id), 0) as total_trades,
                COALESCE((SELECT SUM(profit) FROM trades WHERE user_id = u.id AND profit > 0), 0) as total_profit,
                COALESCE((SELECT COUNT(*) FROM trades WHERE user_id = u.id AND profit > 0), 0) as winning_trades,
                COALESCE((SELECT COUNT(*) FROM referrals WHERE referrer_id = u.id AND status = 'converted'), 0) as referral_count
            FROM users u
            WHERE u.id = ?
        ");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) return [];

        $totalTrades = (int) $user['total_trades'];
        $totalProfit = (float) $user['total_profit'];
        $winRate = $totalTrades > 0 ? ($user['winning_trades'] / $totalTrades) * 100 : 0;
        $referralCount = (int) $user['referral_count'];

        // Check trading milestones
        if ($totalTrades >= 1) $awarded[] = self::tryAward($user_id, 'first_trade');
        if ($totalTrades >= 10) $awarded[] = self::tryAward($user_id, 'trader_10');
        if ($totalTrades >= 50) $awarded[] = self::tryAward($user_id, 'trader_50');
        if ($totalTrades >= 100) $awarded[] = self::tryAward($user_id, 'trader_100');
        if ($totalTrades >= 500) $awarded[] = self::tryAward($user_id, 'trader_500');
        if ($totalTrades >= 1000) $awarded[] = self::tryAward($user_id, 'trader_1000');

        // Check profit milestones
        if ($totalProfit >= 100) $awarded[] = self::tryAward($user_id, 'profit_100');
        if ($totalProfit >= 500) $awarded[] = self::tryAward($user_id, 'profit_500');
        if ($totalProfit >= 1000) $awarded[] = self::tryAward($user_id, 'profit_1000');
        if ($totalProfit >= 5000) $awarded[] = self::tryAward($user_id, 'profit_5000');
        if ($totalProfit >= 10000) $awarded[] = self::tryAward($user_id, 'profit_10000');

        // Check win rate badges
        if ($winRate >= 60 && $totalTrades >= 20) $awarded[] = self::tryAward($user_id, 'winrate_60');
        if ($winRate >= 70 && $totalTrades >= 50) $awarded[] = self::tryAward($user_id, 'winrate_70');
        if ($winRate >= 80 && $totalTrades >= 100) $awarded[] = self::tryAward($user_id, 'winrate_80');
        if ($winRate >= 90 && $totalTrades >= 200) $awarded[] = self::tryAward($user_id, 'winrate_90');

        // Check referral badges
        if ($referralCount >= 5) $awarded[] = self::tryAward($user_id, 'referral_5');
        if ($referralCount >= 20) $awarded[] = self::tryAward($user_id, 'referral_20');

        // Check VIP status
        if (isset($user['package']) && $user['package'] === 'vip') {
            $awarded[] = self::tryAward($user_id, 'vip_member');
        }

        // Check winning streak
        $streak = self::getWinningStreak($user_id);
        if ($streak >= 5) $awarded[] = self::tryAward($user_id, 'streak_5');
        if ($streak >= 10) $awarded[] = self::tryAward($user_id, 'streak_10');
        if ($streak >= 20) $awarded[] = self::tryAward($user_id, 'streak_20');

        return array_filter($awarded);
    }

    /**
     * Try to award badge, return badge_id if successful
     */
    private static function tryAward($user_id, $badge_id) {
        if (self::awardBadge($user_id, $badge_id)) {
            return $badge_id;
        }
        return null;
    }

    /**
     * Get current winning streak
     */
    private static function getWinningStreak($user_id) {
        $db = self::getDB();
        if (!$db) return 0;

        $stmt = $db->prepare("
            SELECT profit
            FROM trades
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT 50
        ");
        $stmt->execute([$user_id]);
        $trades = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $streak = 0;
        foreach ($trades as $profit) {
            if ($profit > 0) {
                $streak++;
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * Get user's total badge points
     */
    public static function getTotalPoints($user_id) {
        $badges = self::getUserBadges($user_id);
        $total = 0;
        foreach ($badges as $badge) {
            $total += $badge['points'] ?? 0;
        }
        return $total;
    }

    /**
     * Get badge rank based on points
     */
    public static function getBadgeRank($points) {
        if ($points >= 2000) return ['rank' => 'Diamond', 'color' => '#b9f2ff', 'icon' => 'fa-gem'];
        if ($points >= 1000) return ['rank' => 'Platinum', 'color' => '#e5e4e2', 'icon' => 'fa-crown'];
        if ($points >= 500) return ['rank' => 'Gold', 'color' => '#ffd700', 'icon' => 'fa-star'];
        if ($points >= 200) return ['rank' => 'Silver', 'color' => '#c0c0c0', 'icon' => 'fa-medal'];
        if ($points >= 50) return ['rank' => 'Bronze', 'color' => '#cd7f32', 'icon' => 'fa-award'];
        return ['rank' => 'Rookie', 'color' => '#6c757d', 'icon' => 'fa-user'];
    }

    /**
     * Render single badge HTML
     */
    public static function renderBadge($badge_id, $size = 'md', $showTooltip = true) {
        $badge = self::getBadge($badge_id);
        if (!$badge) return '';

        $lang = $_SESSION['lang'] ?? 'id';
        $name = $lang === 'id' ? $badge['name_id'] : $badge['name'];
        $desc = $lang === 'id' ? $badge['description_id'] : $badge['description'];

        $sizeClass = [
            'sm' => 'badge-icon-sm',
            'md' => 'badge-icon-md',
            'lg' => 'badge-icon-lg'
        ][$size] ?? 'badge-icon-md';

        $rarityClass = 'rarity-' . $badge['rarity'];

        $tooltip = $showTooltip ? "data-bs-toggle=\"tooltip\" title=\"{$name}: {$desc}\"" : '';

        return <<<HTML
<div class="badge-icon {$sizeClass} {$rarityClass}" style="--badge-color: {$badge['color']}" {$tooltip}>
    <i class="fas {$badge['icon']}"></i>
</div>
HTML;
    }

    /**
     * Render badge with details
     */
    public static function renderBadgeCard($badge_id, $earned_at = null) {
        $badge = self::getBadge($badge_id);
        if (!$badge) return '';

        $lang = $_SESSION['lang'] ?? 'id';
        $name = $lang === 'id' ? $badge['name_id'] : $badge['name'];
        $desc = $lang === 'id' ? $badge['description_id'] : $badge['description'];

        $rarityLabel = ucfirst($badge['rarity']);
        $earnedText = $earned_at ? date('d M Y', strtotime($earned_at)) : '';

        return <<<HTML
<div class="badge-card rarity-{$badge['rarity']}">
    <div class="badge-card-icon" style="--badge-color: {$badge['color']}">
        <i class="fas {$badge['icon']}"></i>
    </div>
    <div class="badge-card-info">
        <h6 class="badge-card-name">{$name}</h6>
        <p class="badge-card-desc">{$desc}</p>
        <div class="badge-card-meta">
            <span class="badge-rarity">{$rarityLabel}</span>
            <span class="badge-points">+{$badge['points']} pts</span>
            <?php if ($earnedText): ?>
            <span class="badge-earned">{$earnedText}</span>
            <?php endif; ?>
        </div>
    </div>
</div>
HTML;
    }

    /**
     * Render user's badge showcase
     */
    public static function renderShowcase($user_id, $limit = 5) {
        $badges = self::getUserBadges($user_id);
        if (empty($badges)) {
            return '<span class="text-muted small">No badges yet</span>';
        }

        // Sort by rarity (legendary first)
        $rarityOrder = ['legendary' => 0, 'epic' => 1, 'rare' => 2, 'uncommon' => 3, 'common' => 4];
        usort($badges, function($a, $b) use ($rarityOrder) {
            return ($rarityOrder[$a['rarity']] ?? 5) - ($rarityOrder[$b['rarity']] ?? 5);
        });

        $html = '<div class="badge-showcase">';
        $count = 0;
        foreach ($badges as $badge) {
            if ($count >= $limit) break;
            $html .= self::renderBadge($badge['id'], 'sm', true);
            $count++;
        }

        if (count($badges) > $limit) {
            $more = count($badges) - $limit;
            $html .= "<span class=\"badge-more\">+{$more}</span>";
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Get leaderboard by badge points
     */
    public static function getBadgeLeaderboard($limit = 20) {
        $db = self::getDB();
        if (!$db) return [];

        $stmt = $db->prepare("
            SELECT
                u.id,
                u.username,
                u.name,
                u.country,
                u.package,
                COALESCE(u.badge_points, 0) as badge_points,
                (SELECT COUNT(*) FROM user_badges WHERE user_id = u.id) as badge_count
            FROM users u
            WHERE u.status = 'active'
            ORDER BY badge_points DESC, badge_count DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get badges by category
     */
    public static function getBadgesByCategory($category) {
        return array_filter(self::$badges, function($badge) use ($category) {
            return $badge['category'] === $category;
        });
    }

    /**
     * Get user's progress towards next badges
     */
    public static function getProgress($user_id) {
        $db = self::getDB();
        if (!$db) return [];

        // Get user stats
        $stmt = $db->prepare("
            SELECT
                COALESCE((SELECT COUNT(*) FROM trades WHERE user_id = ?), 0) as total_trades,
                COALESCE((SELECT SUM(profit) FROM trades WHERE user_id = ? AND profit > 0), 0) as total_profit
        ");
        $stmt->execute([$user_id, $user_id]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        $progress = [];

        // Trade progress
        $tradeTargets = [10, 50, 100, 500, 1000];
        foreach ($tradeTargets as $target) {
            $badge_id = $target == 10 ? 'trader_10' : ($target == 50 ? 'trader_50' : ($target == 100 ? 'trader_100' : ($target == 500 ? 'trader_500' : 'trader_1000')));
            if (!self::hasBadge($user_id, $badge_id)) {
                $progress[] = [
                    'badge_id' => $badge_id,
                    'badge' => self::getBadge($badge_id),
                    'current' => $stats['total_trades'],
                    'target' => $target,
                    'percent' => min(100, ($stats['total_trades'] / $target) * 100)
                ];
                break;
            }
        }

        // Profit progress
        $profitTargets = [100, 500, 1000, 5000, 10000];
        foreach ($profitTargets as $target) {
            $badge_id = 'profit_' . $target;
            if (!self::hasBadge($user_id, $badge_id)) {
                $progress[] = [
                    'badge_id' => $badge_id,
                    'badge' => self::getBadge($badge_id),
                    'current' => $stats['total_profit'],
                    'target' => $target,
                    'percent' => min(100, ($stats['total_profit'] / $target) * 100)
                ];
                break;
            }
        }

        return $progress;
    }
}

/**
 * Render badge CSS styles
 */
function render_badge_styles() {
    return <<<CSS
<style>
/* Badge Icons */
.badge-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--badge-color), color-mix(in srgb, var(--badge-color) 70%, black));
    color: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}
.badge-icon-sm { width: 28px; height: 28px; font-size: 0.75rem; }
.badge-icon-md { width: 40px; height: 40px; font-size: 1rem; }
.badge-icon-lg { width: 56px; height: 56px; font-size: 1.5rem; }

/* Rarity effects */
.rarity-legendary { animation: pulse-glow 2s infinite; }
.rarity-epic { box-shadow: 0 0 15px var(--badge-color); }
.rarity-rare { border: 2px solid var(--badge-color); }

@keyframes pulse-glow {
    0%, 100% { box-shadow: 0 0 5px var(--badge-color); }
    50% { box-shadow: 0 0 20px var(--badge-color), 0 0 30px var(--badge-color); }
}

/* Badge Showcase */
.badge-showcase {
    display: flex;
    gap: 4px;
    align-items: center;
}
.badge-more {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-left: 4px;
}

/* Badge Card */
.badge-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: var(--card-bg);
    border-radius: 8px;
    border: 1px solid var(--border-color);
    transition: transform 0.2s;
}
.badge-card:hover {
    transform: translateY(-2px);
}
.badge-card-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--badge-color), color-mix(in srgb, var(--badge-color) 70%, black));
    color: white;
    font-size: 1.25rem;
}
.badge-card-info {
    flex: 1;
}
.badge-card-name {
    margin: 0 0 4px 0;
    font-weight: 600;
}
.badge-card-desc {
    margin: 0;
    font-size: 0.85rem;
    color: var(--text-muted);
}
.badge-card-meta {
    margin-top: 6px;
    display: flex;
    gap: 8px;
    font-size: 0.75rem;
}
.badge-rarity {
    padding: 2px 6px;
    border-radius: 4px;
    background: var(--bg-light);
    text-transform: uppercase;
}
.badge-points {
    color: var(--primary-color);
    font-weight: 600;
}
.badge-earned {
    color: var(--text-muted);
}

/* Rarity colors */
.badge-card.rarity-legendary .badge-rarity { background: linear-gradient(135deg, #e83e8c, #fd7e14); color: white; }
.badge-card.rarity-epic .badge-rarity { background: #fd7e14; color: white; }
.badge-card.rarity-rare .badge-rarity { background: #6f42c1; color: white; }
.badge-card.rarity-uncommon .badge-rarity { background: #17a2b8; color: white; }
.badge-card.rarity-common .badge-rarity { background: #6c757d; color: white; }

/* Badge Progress */
.badge-progress {
    padding: 12px;
    background: var(--card-bg);
    border-radius: 8px;
    border: 1px solid var(--border-color);
}
.badge-progress-bar {
    height: 8px;
    background: var(--bg-light);
    border-radius: 4px;
    overflow: hidden;
    margin-top: 8px;
}
.badge-progress-fill {
    height: 100%;
    background: var(--primary-color);
    border-radius: 4px;
    transition: width 0.3s;
}

/* Badge Rank */
.badge-rank {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.85rem;
}
</style>
CSS;
}
