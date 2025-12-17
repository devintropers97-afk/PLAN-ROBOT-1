-- ===========================================
-- ZYN Trade System - Badges Schema
-- ===========================================
-- CARA PAKAI: Import file ini ke database MySQL
-- phpMyAdmin: Import > Choose File > badges_schema.sql
-- ===========================================

-- Table untuk menyimpan badges yang sudah didapat user
CREATE TABLE IF NOT EXISTS `user_badges` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `badge_id` VARCHAR(50) NOT NULL,
    `earned_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_user_badge` (`user_id`, `badge_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_badge_id` (`badge_id`),
    KEY `idx_earned_at` (`earned_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tambahkan kolom badge_points ke tabel users jika belum ada
ALTER TABLE `users`
ADD COLUMN IF NOT EXISTS `badge_points` INT DEFAULT 0 AFTER `status`;

-- Index untuk badge_points (untuk leaderboard)
CREATE INDEX IF NOT EXISTS `idx_badge_points` ON `users` (`badge_points` DESC);

-- ===========================================
-- DAFTAR BADGE IDS:
-- ===========================================
-- MILESTONE: first_trade, trader_10, trader_50, trader_100, trader_500, trader_1000
-- PROFIT: profit_100, profit_500, profit_1000, profit_5000, profit_10000
-- SKILL: winrate_60, winrate_70, winrate_80, winrate_90
-- STREAK: streak_5, streak_10, streak_20
-- SPECIAL: early_bird, vip_member, night_owl, comeback_king
-- SOCIAL: referral_5, referral_20
-- ACHIEVEMENT: top_3_monthly, top_1_monthly
-- DEDICATION: consistency_30
-- ===========================================

-- Sample data (opsional - untuk testing)
-- INSERT INTO user_badges (user_id, badge_id) VALUES (1, 'first_trade');
-- INSERT INTO user_badges (user_id, badge_id) VALUES (1, 'trader_10');

-- ===========================================
-- VIEW untuk statistik badges
-- ===========================================
CREATE OR REPLACE VIEW `badge_statistics` AS
SELECT
    badge_id,
    COUNT(*) as total_earned,
    MIN(earned_at) as first_earned,
    MAX(earned_at) as last_earned
FROM user_badges
GROUP BY badge_id
ORDER BY total_earned DESC;

-- View untuk leaderboard badges
CREATE OR REPLACE VIEW `badge_leaderboard` AS
SELECT
    u.id as user_id,
    u.username,
    u.name,
    u.country,
    u.package,
    COALESCE(u.badge_points, 0) as badge_points,
    COUNT(ub.id) as badge_count
FROM users u
LEFT JOIN user_badges ub ON u.id = ub.user_id
WHERE u.status = 'active'
GROUP BY u.id
ORDER BY badge_points DESC, badge_count DESC;
