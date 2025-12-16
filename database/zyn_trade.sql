-- =============================================
-- ZYN TRADE SYSTEM - DATABASE SCHEMA
-- Version: 3.0 (Updated with License Key System)
-- "Precision Over Emotion"
-- =============================================

-- Create database (uncomment if needed)
-- CREATE DATABASE IF NOT EXISTS zyn_trade CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE zyn_trade;

-- =============================================
-- LICENSE KEYS TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS `license_keys` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `license_key` VARCHAR(50) NOT NULL UNIQUE,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `package` ENUM('free', 'pro', 'elite', 'vip') NOT NULL DEFAULT 'free',
    `status` ENUM('available', 'active', 'expired', 'revoked') DEFAULT 'available',
    `activated_at` DATETIME DEFAULT NULL,
    `expires_at` DATETIME DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_license_key` (`license_key`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_package` (`package`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- USERS TABLE (Updated with License Key)
-- =============================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `license_key` VARCHAR(50) DEFAULT NULL UNIQUE,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) DEFAULT NULL,
    `fullname` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(50) DEFAULT NULL,
    `country` VARCHAR(10) NOT NULL,
    `olymptrade_id` VARCHAR(50) NOT NULL UNIQUE,
    `olymptrade_account_type` ENUM('demo', 'real') DEFAULT 'real',
    `role` ENUM('user', 'admin') DEFAULT 'user',
    `status` ENUM('pending', 'active', 'rejected', 'suspended') DEFAULT 'pending',
    `rejection_reason` TEXT DEFAULT NULL,
    `rejection_code` VARCHAR(10) DEFAULT NULL,
    `package` ENUM('free', 'pro', 'elite', 'vip') DEFAULT 'free',
    `package_expiry` DATETIME DEFAULT NULL,
    `verified_by` INT UNSIGNED DEFAULT NULL,
    `verified_at` DATETIME DEFAULT NULL,
    `last_login` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_status` (`status`),
    INDEX `idx_country` (`country`),
    INDEX `idx_package` (`package`),
    INDEX `idx_olymptrade_id` (`olymptrade_id`),
    INDEX `idx_license_key` (`license_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- ROBOT SETTINGS TABLE (Updated with Schedule & Auto-Pause)
-- =============================================
CREATE TABLE IF NOT EXISTS `robot_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL UNIQUE,
    `robot_enabled` TINYINT(1) DEFAULT 0,
    `strategies` JSON DEFAULT NULL,
    `market` ENUM('EUR/USD', 'GBP/USD') DEFAULT 'EUR/USD',
    `timeframe` ENUM('5M', '15M', '30M', '1H') DEFAULT '15M',
    `risk_level` ENUM('low', 'medium', 'high') DEFAULT 'medium',
    `trade_amount` DECIMAL(10,2) DEFAULT 10000.00,
    `daily_limit` INT DEFAULT 10,

    -- Money Management System
    `money_management_type` ENUM('flat', 'martingale') DEFAULT 'flat',
    `martingale_step` INT DEFAULT 0 COMMENT 'Current martingale step (0-3)',
    `martingale_base_amount` DECIMAL(10,2) DEFAULT 10000.00,

    -- Multi-Timeframe Amounts (JSON)
    `timeframe_amounts` JSON DEFAULT NULL COMMENT 'Different amounts per timeframe',

    -- Take Profit & Max Loss (Auto-Pause System)
    `take_profit_target` DECIMAL(10,2) DEFAULT 50.00,
    `max_loss_limit` DECIMAL(10,2) DEFAULT 25.00,
    `current_daily_pnl` DECIMAL(10,2) DEFAULT 0.00,
    `auto_pause_triggered` TINYINT(1) DEFAULT 0,
    `auto_pause_reason` ENUM('tp_reached', 'ml_reached', 'manual', 'take_profit', 'max_loss') DEFAULT NULL,
    `auto_pause_time` DATETIME DEFAULT NULL,

    -- Schedule System (5 Modes)
    `schedule_mode` ENUM('auto_24h', 'best_hours', 'custom_single', 'multi_session', 'per_day') DEFAULT 'auto_24h',
    `schedule_start_time` TIME DEFAULT NULL,
    `schedule_end_time` TIME DEFAULT NULL,
    `schedule_sessions` JSON DEFAULT NULL,
    `schedule_per_day` JSON DEFAULT NULL,

    -- Daily Target System
    `daily_target_amount` DECIMAL(10,2) DEFAULT 50.00 COMMENT 'Daily profit target in USD',
    `daily_target_auto_stop` TINYINT(1) DEFAULT 0 COMMENT 'Auto-stop when target reached',

    -- Active Strategies (JSON array of strategy IDs)
    `active_strategies` JSON DEFAULT NULL COMMENT 'Array of active strategy IDs',

    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TRADES TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS `trades` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `strategy` VARCHAR(50) NOT NULL,
    `strategy_id` VARCHAR(10) NOT NULL,
    `asset` VARCHAR(50) NOT NULL,
    `timeframe` VARCHAR(10) DEFAULT '15M',
    `amount` DECIMAL(10,2) NOT NULL,
    `direction` ENUM('call', 'put') NOT NULL,
    `result` ENUM('win', 'loss', 'tie') DEFAULT NULL,
    `profit_loss` DECIMAL(10,2) DEFAULT 0.00,
    `entry_price` DECIMAL(15,5) DEFAULT NULL,
    `exit_price` DECIMAL(15,5) DEFAULT NULL,
    `duration` INT DEFAULT NULL COMMENT 'Duration in seconds',
    `indicators_snapshot` JSON DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_strategy` (`strategy`),
    INDEX `idx_strategy_id` (`strategy_id`),
    INDEX `idx_result` (`result`),
    INDEX `idx_created_at` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- SUBSCRIPTIONS TABLE (USD for Global Market)
-- =============================================
CREATE TABLE IF NOT EXISTS `subscriptions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `plan` ENUM('pro', 'elite', 'vip') NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `currency` VARCHAR(3) DEFAULT 'USD',
    `payment_method` VARCHAR(50) NOT NULL,
    `payment_id` VARCHAR(255) DEFAULT NULL,
    `payment_proof` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('pending', 'active', 'expired', 'cancelled', 'refunded', 'rejected') DEFAULT 'pending',
    `rejection_reason` TEXT DEFAULT NULL,
    `starts_at` DATETIME DEFAULT NULL,
    `expires_at` DATETIME DEFAULT NULL,
    `auto_renew` TINYINT(1) DEFAULT 0,
    `verified_by` INT UNSIGNED DEFAULT NULL,
    `verified_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_plan` (`plan`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- NOTIFICATIONS TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `type` VARCHAR(50) NOT NULL,
    `message` TEXT NOT NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_is_read` (`is_read`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- DAILY STATS TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS `daily_stats` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `date` DATE NOT NULL,
    `total_trades` INT DEFAULT 0,
    `wins` INT DEFAULT 0,
    `losses` INT DEFAULT 0,
    `ties` INT DEFAULT 0,
    `profit_loss` DECIMAL(10,2) DEFAULT 0.00,
    `win_rate` DECIMAL(5,2) DEFAULT 0.00,
    `tp_reached` TINYINT(1) DEFAULT 0,
    `ml_reached` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_user_date` (`user_id`, `date`),
    INDEX `idx_date` (`date`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- SIGNALS TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS `signals` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `strategy_id` VARCHAR(10) NOT NULL,
    `strategy_name` VARCHAR(100) NOT NULL,
    `asset` VARCHAR(50) NOT NULL,
    `timeframe` VARCHAR(10) DEFAULT '15M',
    `direction` ENUM('call', 'put') NOT NULL,
    `confidence` INT DEFAULT 75 COMMENT 'Confidence percentage',
    `indicators_data` JSON DEFAULT NULL,
    `valid_until` DATETIME DEFAULT NULL,
    `result` ENUM('win', 'loss', 'tie', 'pending') DEFAULT 'pending',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_strategy_id` (`strategy_id`),
    INDEX `idx_asset` (`asset`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- ROBOT STATUS TABLE (For Robot Engine)
-- =============================================
CREATE TABLE IF NOT EXISTS `robot_status` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL UNIQUE,
    `status` ENUM('running', 'paused', 'stopped', 'error', 'unknown') DEFAULT 'unknown',
    `last_active` DATETIME DEFAULT NULL,
    `version` VARCHAR(20) DEFAULT '1.0.0',
    `total_signals_today` INT DEFAULT 0,
    `total_trades_today` INT DEFAULT 0,
    `connection_status` ENUM('connected', 'disconnected', 'connecting') DEFAULT 'disconnected',
    `last_error` TEXT DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_status` (`status`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- SESSION TABLE (For PHP Sessions)
-- =============================================
CREATE TABLE IF NOT EXISTS `sessions` (
    `id` VARCHAR(128) PRIMARY KEY,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(255) DEFAULT NULL,
    `payload` TEXT NOT NULL,
    `last_activity` INT NOT NULL,
    INDEX `idx_last_activity` (`last_activity`),
    INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- SETTINGS TABLE (System Settings)
-- =============================================
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) NOT NULL UNIQUE,
    `value` TEXT DEFAULT NULL,
    `description` VARCHAR(255) DEFAULT NULL,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- ACTIVITY LOG TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS `activity_log` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `action` VARCHAR(100) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- USER ACHIEVEMENTS TABLE (FINAL ZYN Feature)
-- =============================================
CREATE TABLE IF NOT EXISTS `user_achievements` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `achievement_id` VARCHAR(50) NOT NULL COMMENT 'Achievement identifier',
    `achievement_name` VARCHAR(100) NOT NULL,
    `achievement_icon` VARCHAR(50) DEFAULT 'fa-trophy',
    `unlocked_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `progress` INT DEFAULT 100 COMMENT 'Progress percentage (100 = unlocked)',
    UNIQUE KEY `unique_user_achievement` (`user_id`, `achievement_id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_achievement_id` (`achievement_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- USER NOTIFICATION SETTINGS TABLE (Smart Notifications)
-- =============================================
CREATE TABLE IF NOT EXISTS `user_notification_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL UNIQUE,
    `signal_alert` TINYINT(1) DEFAULT 1 COMMENT 'Signal Alert ON/OFF',
    `trade_result` TINYINT(1) DEFAULT 1 COMMENT 'Trade Result ON/OFF',
    `win_streak_alert` TINYINT(1) DEFAULT 1 COMMENT 'Win Streak Alert',
    `loss_warning` TINYINT(1) DEFAULT 1 COMMENT 'Loss Warning',
    `daily_summary` TINYINT(1) DEFAULT 1 COMMENT 'Daily Summary',
    `quiet_hours_enabled` TINYINT(1) DEFAULT 0 COMMENT 'Quiet Hours ON/OFF',
    `quiet_hours_start` TIME DEFAULT '22:00:00' COMMENT 'Quiet hours start time',
    `quiet_hours_end` TIME DEFAULT '07:00:00' COMMENT 'Quiet hours end time',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- INSERT DEFAULT ADMIN USER
-- Password: admin123 (CHANGE THIS!)
-- =============================================
INSERT INTO `users` (`license_key`, `email`, `password`, `fullname`, `country`, `olymptrade_id`, `olymptrade_account_type`, `role`, `status`, `package`, `package_expiry`) VALUES
('ZYN-ADMIN-001', 'admin@zyntrade.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/X.VvK.MqYv1H4LDHO', 'System Admin', 'ID', 'ADMIN001', 'real', 'admin', 'active', 'vip', DATE_ADD(NOW(), INTERVAL 10 YEAR));

-- =============================================
-- INSERT DEFAULT SETTINGS (Updated for v3.0)
-- =============================================
INSERT INTO `settings` (`key`, `value`, `description`) VALUES
('site_name', 'ZYN Trade System', 'Website name'),
('site_tagline', 'Precision Over Emotion', 'Website tagline'),
('affiliate_id', '660784', 'OlympTrade affiliate ID'),
('olymptrade_affiliate_link', 'https://olymptrade.com/?affiliate_id=660784&subid1=ZYNtradeSystem', 'OlympTrade affiliate link'),
('olymptrade_affiliate_link_id', 'https://olymptrade-vid.com/id-id/?affiliate_id=660784&subid1=ZYNtradeSystem', 'OlympTrade affiliate link Indonesia'),
('telegram_channel', 'https://t.me/OlymptradeCopytrade', 'Telegram channel URL'),
('telegram_channel_id', '@OlymptradeCopytrade', 'Telegram channel ID'),
('telegram_support', '@aheenkgans', 'Telegram support username'),
('min_deposit', '10', 'Minimum deposit requirement (USD)'),
('allowed_markets', 'EUR/USD,GBP/USD', 'Allowed markets'),
('allowed_timeframes', '5M,15M,30M,1H', 'Allowed timeframes'),
('default_tp_target', '50', 'Default Take Profit target'),
('default_ml_limit', '25', 'Default Max Loss limit'),
('real_account_only', '1', 'Only allow real accounts'),
('weekend_auto_off', '1', 'Auto-off on weekends'),
('maintenance_mode', '0', 'Enable maintenance mode'),
('currency', 'USD', 'Default currency'),
('price_pro', '29', 'PRO package price in USD'),
('price_elite', '79', 'ELITE package price in USD'),
('price_vip', '149', 'VIP package price in USD'),
('paypal_email', 'payment@zyntrade.com', 'PayPal payment email'),
('wise_email', 'payment@zyntrade.com', 'Wise payment email'),
('crypto_usdt_trc20', '', 'USDT TRC20 wallet address'),
('crypto_btc', '', 'BTC wallet address'),
('bank_name', 'BCA', 'Bank name for transfer'),
('bank_account', '', 'Bank account number'),
('bank_holder', 'ZYN TRADE SYSTEM', 'Bank account holder name');

-- =============================================
-- INSERT SAMPLE LICENSE KEYS
-- =============================================
INSERT INTO `license_keys` (`license_key`, `package`, `status`) VALUES
('ZYN-FREE-DEMO-001', 'free', 'available'),
('ZYN-FREE-DEMO-002', 'free', 'available'),
('ZYN-PRO-DEMO-001', 'pro', 'available'),
('ZYN-ELITE-DEMO-001', 'elite', 'available'),
('ZYN-VIP-DEMO-001', 'vip', 'available');

-- =============================================
-- VIEWS FOR STATISTICS
-- =============================================

-- User Statistics View
CREATE OR REPLACE VIEW `view_user_stats` AS
SELECT
    u.id AS user_id,
    u.fullname,
    u.email,
    u.license_key,
    u.package,
    COUNT(t.id) AS total_trades,
    SUM(CASE WHEN t.result = 'win' THEN 1 ELSE 0 END) AS wins,
    SUM(CASE WHEN t.result = 'loss' THEN 1 ELSE 0 END) AS losses,
    SUM(t.profit_loss) AS total_pnl,
    ROUND(AVG(CASE WHEN t.result = 'win' THEN 1 ELSE 0 END) * 100, 2) AS win_rate
FROM users u
LEFT JOIN trades t ON u.id = t.user_id
WHERE u.role = 'user'
GROUP BY u.id;

-- Leaderboard View (Monthly)
CREATE OR REPLACE VIEW `view_leaderboard_monthly` AS
SELECT
    u.id AS user_id,
    u.fullname,
    u.country,
    COUNT(t.id) AS total_trades,
    SUM(CASE WHEN t.result = 'win' THEN 1 ELSE 0 END) AS wins,
    SUM(t.profit_loss) AS total_profit,
    ROUND(AVG(CASE WHEN t.result = 'win' THEN 1 ELSE 0 END) * 100, 2) AS win_rate
FROM users u
LEFT JOIN trades t ON u.id = t.user_id AND t.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
WHERE u.role = 'user' AND u.status = 'active'
GROUP BY u.id
ORDER BY total_profit DESC;

-- =============================================
-- STORED PROCEDURES
-- =============================================

DELIMITER //

-- Update daily stats procedure
CREATE PROCEDURE IF NOT EXISTS `update_daily_stats`(IN p_user_id INT, IN p_date DATE)
BEGIN
    INSERT INTO daily_stats (user_id, date, total_trades, wins, losses, ties, profit_loss, win_rate)
    SELECT
        p_user_id,
        p_date,
        COUNT(*),
        SUM(CASE WHEN result = 'win' THEN 1 ELSE 0 END),
        SUM(CASE WHEN result = 'loss' THEN 1 ELSE 0 END),
        SUM(CASE WHEN result = 'tie' THEN 1 ELSE 0 END),
        SUM(profit_loss),
        ROUND(AVG(CASE WHEN result = 'win' THEN 1 ELSE 0 END) * 100, 2)
    FROM trades
    WHERE user_id = p_user_id AND DATE(created_at) = p_date
    ON DUPLICATE KEY UPDATE
        total_trades = VALUES(total_trades),
        wins = VALUES(wins),
        losses = VALUES(losses),
        ties = VALUES(ties),
        profit_loss = VALUES(profit_loss),
        win_rate = VALUES(win_rate),
        updated_at = NOW();
END //

-- Generate license key procedure
CREATE PROCEDURE IF NOT EXISTS `generate_license_key`(IN p_package VARCHAR(10), IN p_created_by INT)
BEGIN
    DECLARE new_key VARCHAR(50);
    DECLARE prefix VARCHAR(10);

    SET prefix = CASE p_package
        WHEN 'free' THEN 'ZYN-F'
        WHEN 'pro' THEN 'ZYN-P'
        WHEN 'elite' THEN 'ZYN-E'
        WHEN 'vip' THEN 'ZYN-V'
        ELSE 'ZYN-X'
    END;

    SET new_key = CONCAT(prefix, '-', UPPER(SUBSTRING(MD5(RAND()), 1, 4)), '-', UPPER(SUBSTRING(MD5(RAND()), 1, 4)));

    INSERT INTO license_keys (license_key, package, status, created_by)
    VALUES (new_key, p_package, 'available', p_created_by);

    SELECT new_key AS generated_key;
END //

DELIMITER ;

-- =============================================
-- END OF SCHEMA
-- =============================================
