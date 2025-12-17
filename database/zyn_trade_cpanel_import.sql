-- =============================================
-- ZYN TRADE SYSTEM - CPANEL READY SQL IMPORT
-- Version: 3.1 Premium Edition
-- =============================================
--
-- CARA IMPORT DI CPANEL:
-- 1. Login ke cPanel
-- 2. Buka phpMyAdmin
-- 3. Pilih database: nrrskfvk_ZYNtradesystem
-- 4. Klik tab "Import"
-- 5. Pilih file ini (zyn_trade_cpanel_import.sql)
-- 6. Klik "Go" / "Import"
--
-- ADMIN LOGIN SETELAH IMPORT:
-- License Key: ZYN-A-ADMN-2024
-- Password: password
--
-- URL Login: https://tester.situneo.my.id/login.php
-- URL Admin: https://tester.situneo.my.id/admin/
-- =============================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =============================================
-- LICENSE KEYS TABLE
-- =============================================
DROP TABLE IF EXISTS `license_keys`;
CREATE TABLE `license_keys` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `license_key` VARCHAR(64) NOT NULL UNIQUE,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `package` ENUM('free', 'starter', 'pro', 'elite', 'vip') NOT NULL DEFAULT 'free',
    `status` ENUM('available', 'active', 'expired', 'revoked', 'suspended') DEFAULT 'available',
    `activated_at` DATETIME DEFAULT NULL,
    `expires_at` DATETIME DEFAULT NULL,
    `max_devices` INT DEFAULT 1 COMMENT 'Maximum devices allowed',
    `notes` TEXT DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_license_key` (`license_key`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_package` (`package`),
    INDEX `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- USERS TABLE
-- =============================================
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `license_key` VARCHAR(64) DEFAULT NULL UNIQUE,
    `username` VARCHAR(100) DEFAULT NULL UNIQUE,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) DEFAULT NULL,
    `fullname` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(50) DEFAULT NULL,
    `country` VARCHAR(10) NOT NULL,
    `timezone` VARCHAR(50) DEFAULT 'Asia/Jakarta',
    `olymptrade_id` VARCHAR(50) NOT NULL UNIQUE,
    `olymptrade_email` VARCHAR(255) DEFAULT NULL COMMENT 'OlympTrade login email',
    `olymptrade_password` TEXT DEFAULT NULL COMMENT 'Encrypted OlympTrade password',
    `olymptrade_account_type` ENUM('demo', 'real') DEFAULT 'demo',
    `olymptrade_setup_completed` TINYINT(1) DEFAULT 0 COMMENT 'Has user setup OT credentials',
    `role` ENUM('user', 'admin', 'moderator') DEFAULT 'user',
    `status` ENUM('pending', 'active', 'rejected', 'suspended', 'banned') DEFAULT 'pending',
    `rejection_reason` TEXT DEFAULT NULL,
    `rejection_code` VARCHAR(20) DEFAULT NULL,
    `package` ENUM('free', 'starter', 'pro', 'elite', 'vip') DEFAULT 'free',
    `package_expiry` DATETIME DEFAULT NULL,
    `verified_by` INT UNSIGNED DEFAULT NULL,
    `verified_at` DATETIME DEFAULT NULL,
    `last_login` DATETIME DEFAULT NULL,
    `login_count` INT UNSIGNED DEFAULT 0,
    `last_ip` VARCHAR(45) DEFAULT NULL,
    `avatar` VARCHAR(255) DEFAULT NULL,
    `bio` TEXT DEFAULT NULL,
    `referral_code` VARCHAR(20) DEFAULT NULL UNIQUE,
    `referred_by` INT UNSIGNED DEFAULT NULL,
    `total_trades` INT UNSIGNED DEFAULT 0,
    `total_profit` DECIMAL(15,2) DEFAULT 0.00,
    `wins` INT UNSIGNED DEFAULT 0,
    `losses` INT UNSIGNED DEFAULT 0,
    `last_trade_at` DATETIME DEFAULT NULL,
    `robot_last_active` DATETIME DEFAULT NULL,
    `email_verified_at` DATETIME DEFAULT NULL,
    `two_factor_enabled` TINYINT(1) DEFAULT 0,
    `two_factor_secret` VARCHAR(255) DEFAULT NULL,
    `remember_token` VARCHAR(100) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_status` (`status`),
    INDEX `idx_country` (`country`),
    INDEX `idx_package` (`package`),
    INDEX `idx_olymptrade_id` (`olymptrade_id`),
    INDEX `idx_license_key` (`license_key`),
    INDEX `idx_referral_code` (`referral_code`),
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_package_expiry` (`package_expiry`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- ROBOT SETTINGS TABLE
-- =============================================
DROP TABLE IF EXISTS `robot_settings`;
CREATE TABLE `robot_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL UNIQUE,
    `robot_enabled` TINYINT(1) DEFAULT 0,
    `strategies` JSON DEFAULT NULL,
    `market` ENUM('EUR/USD', 'GBP/USD', 'USD/JPY', 'AUD/USD') DEFAULT 'EUR/USD',
    `timeframe` ENUM('5M', '15M', '30M', '1H') DEFAULT '15M',
    `risk_level` ENUM('low', 'medium', 'high', 'aggressive') DEFAULT 'medium',
    `trade_amount` DECIMAL(15,2) DEFAULT 10000.00,
    `daily_limit` INT DEFAULT 10,
    `money_management_type` ENUM('flat', 'martingale', 'anti_martingale', 'compound') DEFAULT 'flat',
    `martingale_step` INT DEFAULT 0,
    `martingale_max_steps` INT DEFAULT 3,
    `martingale_multiplier` DECIMAL(5,2) DEFAULT 2.00,
    `martingale_base_amount` DECIMAL(15,2) DEFAULT 10000.00,
    `timeframe_amounts` JSON DEFAULT NULL,
    `take_profit_target` DECIMAL(15,2) DEFAULT 50.00,
    `max_loss_limit` DECIMAL(15,2) DEFAULT 25.00,
    `current_daily_pnl` DECIMAL(15,2) DEFAULT 0.00,
    `auto_pause_triggered` TINYINT(1) DEFAULT 0,
    `auto_pause_reason` ENUM('tp_reached', 'ml_reached', 'manual', 'take_profit', 'max_loss', 'error', 'maintenance') DEFAULT NULL,
    `auto_pause_time` DATETIME DEFAULT NULL,
    `schedule_mode` ENUM('auto_24h', 'best_hours', 'custom_single', 'multi_session', 'per_day') DEFAULT 'auto_24h',
    `schedule_start_time` TIME DEFAULT NULL,
    `schedule_end_time` TIME DEFAULT NULL,
    `schedule_sessions` JSON DEFAULT NULL,
    `schedule_per_day` JSON DEFAULT NULL,
    `weekend_auto_off` TINYINT(1) DEFAULT 1,
    `daily_target_amount` DECIMAL(15,2) DEFAULT 50.00,
    `daily_target_auto_stop` TINYINT(1) DEFAULT 0,
    `active_strategies` JSON DEFAULT NULL,
    `notify_on_trade` TINYINT(1) DEFAULT 1,
    `notify_on_error` TINYINT(1) DEFAULT 1,
    `notify_on_pause` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TRADES TABLE
-- =============================================
DROP TABLE IF EXISTS `trades`;
CREATE TABLE `trades` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `strategy` VARCHAR(100) NOT NULL,
    `strategy_id` VARCHAR(20) NOT NULL,
    `asset` VARCHAR(50) NOT NULL,
    `timeframe` VARCHAR(10) DEFAULT '15M',
    `amount` DECIMAL(15,2) NOT NULL,
    `direction` ENUM('call', 'put') NOT NULL,
    `result` ENUM('win', 'loss', 'tie', 'pending', 'cancelled') DEFAULT 'pending',
    `profit` DECIMAL(15,2) DEFAULT 0.00,
    `profit_loss` DECIMAL(15,2) DEFAULT 0.00,
    `confidence` DECIMAL(5,2) DEFAULT NULL,
    `entry_price` DECIMAL(20,8) DEFAULT NULL,
    `exit_price` DECIMAL(20,8) DEFAULT NULL,
    `expiry` DATETIME DEFAULT NULL,
    `duration` INT DEFAULT NULL,
    `indicators` JSON DEFAULT NULL,
    `indicators_snapshot` JSON DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_strategy` (`strategy`),
    INDEX `idx_strategy_id` (`strategy_id`),
    INDEX `idx_result` (`result`),
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_asset` (`asset`),
    INDEX `idx_user_result` (`user_id`, `result`),
    INDEX `idx_user_created` (`user_id`, `created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- SUBSCRIPTIONS TABLE
-- =============================================
DROP TABLE IF EXISTS `subscriptions`;
CREATE TABLE `subscriptions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `plan` ENUM('starter', 'pro', 'elite', 'vip') NOT NULL,
    `amount` DECIMAL(15,2) NOT NULL,
    `currency` VARCHAR(3) DEFAULT 'USD',
    `payment_method` VARCHAR(50) NOT NULL,
    `payment_gateway` VARCHAR(50) DEFAULT NULL,
    `payment_id` VARCHAR(255) DEFAULT NULL,
    `payment_proof` VARCHAR(255) DEFAULT NULL,
    `invoice_number` VARCHAR(50) DEFAULT NULL UNIQUE,
    `status` ENUM('pending', 'active', 'expired', 'cancelled', 'refunded', 'rejected', 'processing') DEFAULT 'pending',
    `rejection_reason` TEXT DEFAULT NULL,
    `starts_at` DATETIME DEFAULT NULL,
    `expires_at` DATETIME DEFAULT NULL,
    `auto_renew` TINYINT(1) DEFAULT 0,
    `renewal_reminder_sent` TINYINT(1) DEFAULT 0,
    `verified_by` INT UNSIGNED DEFAULT NULL,
    `verified_at` DATETIME DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `metadata` JSON DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_plan` (`plan`),
    INDEX `idx_expires_at` (`expires_at`),
    INDEX `idx_invoice` (`invoice_number`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- NOTIFICATIONS TABLE
-- =============================================
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `type` VARCHAR(50) NOT NULL,
    `title` VARCHAR(255) DEFAULT NULL,
    `message` TEXT NOT NULL,
    `action_url` VARCHAR(255) DEFAULT NULL,
    `icon` VARCHAR(50) DEFAULT 'fa-bell',
    `priority` ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    `is_read` TINYINT(1) DEFAULT 0,
    `read_at` DATETIME DEFAULT NULL,
    `expires_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_is_read` (`is_read`),
    INDEX `idx_type` (`type`),
    INDEX `idx_created_at` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- DAILY STATS TABLE
-- =============================================
DROP TABLE IF EXISTS `daily_stats`;
CREATE TABLE `daily_stats` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `date` DATE NOT NULL,
    `total_trades` INT DEFAULT 0,
    `trades` INT DEFAULT 0,
    `wins` INT DEFAULT 0,
    `losses` INT DEFAULT 0,
    `ties` INT DEFAULT 0,
    `profit_loss` DECIMAL(15,2) DEFAULT 0.00,
    `profit` DECIMAL(15,2) DEFAULT 0.00,
    `win_rate` DECIMAL(5,2) DEFAULT 0.00,
    `best_strategy` VARCHAR(100) DEFAULT NULL,
    `worst_strategy` VARCHAR(100) DEFAULT NULL,
    `tp_reached` TINYINT(1) DEFAULT 0,
    `ml_reached` TINYINT(1) DEFAULT 0,
    `robot_active_hours` DECIMAL(5,2) DEFAULT 0.00,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_user_date` (`user_id`, `date`),
    INDEX `idx_date` (`date`),
    INDEX `idx_user_date` (`user_id`, `date`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- SIGNALS TABLE
-- =============================================
DROP TABLE IF EXISTS `signals`;
CREATE TABLE `signals` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `strategy_id` VARCHAR(20) NOT NULL,
    `strategy_name` VARCHAR(100) NOT NULL,
    `asset` VARCHAR(50) NOT NULL,
    `timeframe` VARCHAR(10) DEFAULT '15M',
    `direction` ENUM('call', 'put') NOT NULL,
    `confidence` INT DEFAULT 75,
    `entry_price` DECIMAL(20,8) DEFAULT NULL,
    `indicators_data` JSON DEFAULT NULL,
    `valid_until` DATETIME DEFAULT NULL,
    `result` ENUM('win', 'loss', 'tie', 'pending', 'expired') DEFAULT 'pending',
    `subscribers_count` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_strategy_id` (`strategy_id`),
    INDEX `idx_asset` (`asset`),
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_result` (`result`),
    INDEX `idx_valid_until` (`valid_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- ROBOT STATUS TABLE
-- =============================================
DROP TABLE IF EXISTS `robot_status`;
CREATE TABLE `robot_status` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL UNIQUE,
    `status` ENUM('running', 'paused', 'stopped', 'error', 'idle', 'maintenance', 'unknown') DEFAULT 'unknown',
    `version` VARCHAR(20) DEFAULT '1.0.0',
    `balance` DECIMAL(15,2) DEFAULT NULL,
    `session_trades` INT DEFAULT 0,
    `session_profit` DECIMAL(15,2) DEFAULT 0.00,
    `total_signals_today` INT DEFAULT 0,
    `total_trades_today` INT DEFAULT 0,
    `connection_status` ENUM('connected', 'disconnected', 'connecting', 'error') DEFAULT 'disconnected',
    `last_active` DATETIME DEFAULT NULL,
    `last_trade` DATETIME DEFAULT NULL,
    `last_signal` DATETIME DEFAULT NULL,
    `last_error` TEXT DEFAULT NULL,
    `error_message` TEXT DEFAULT NULL,
    `error_count` INT DEFAULT 0,
    `heartbeat_count` INT UNSIGNED DEFAULT 0,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(255) DEFAULT NULL,
    `platform` VARCHAR(50) DEFAULT NULL,
    `metadata` JSON DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_status` (`status`),
    INDEX `idx_last_active` (`last_active`),
    INDEX `idx_connection` (`connection_status`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- SESSIONS TABLE
-- =============================================
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
    `id` VARCHAR(128) PRIMARY KEY,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(500) DEFAULT NULL,
    `payload` MEDIUMTEXT NOT NULL,
    `last_activity` INT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_last_activity` (`last_activity`),
    INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- SETTINGS TABLE
-- =============================================
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) NOT NULL UNIQUE,
    `value` TEXT DEFAULT NULL,
    `type` ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    `group` VARCHAR(50) DEFAULT 'general',
    `description` VARCHAR(255) DEFAULT NULL,
    `is_public` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_group` (`group`),
    INDEX `idx_public` (`is_public`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- ACTIVITY LOG TABLE
-- =============================================
DROP TABLE IF EXISTS `activity_log`;
CREATE TABLE `activity_log` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `action` VARCHAR(100) NOT NULL,
    `entity_type` VARCHAR(50) DEFAULT NULL,
    `entity_id` INT UNSIGNED DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `old_values` JSON DEFAULT NULL,
    `new_values` JSON DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(500) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_entity` (`entity_type`, `entity_id`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- USER ACHIEVEMENTS TABLE
-- =============================================
DROP TABLE IF EXISTS `user_achievements`;
CREATE TABLE `user_achievements` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `achievement_id` VARCHAR(50) NOT NULL,
    `achievement_name` VARCHAR(100) NOT NULL,
    `achievement_description` VARCHAR(255) DEFAULT NULL,
    `achievement_icon` VARCHAR(50) DEFAULT 'fa-trophy',
    `achievement_color` VARCHAR(20) DEFAULT '#fbbf24',
    `unlocked_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `progress` INT DEFAULT 100,
    `points` INT DEFAULT 0,
    UNIQUE KEY `unique_user_achievement` (`user_id`, `achievement_id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_achievement_id` (`achievement_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- USER NOTIFICATION SETTINGS TABLE
-- =============================================
DROP TABLE IF EXISTS `user_notification_settings`;
CREATE TABLE `user_notification_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL UNIQUE,
    `signal_alert` TINYINT(1) DEFAULT 1,
    `trade_result` TINYINT(1) DEFAULT 1,
    `win_streak_alert` TINYINT(1) DEFAULT 1,
    `loss_warning` TINYINT(1) DEFAULT 1,
    `daily_summary` TINYINT(1) DEFAULT 1,
    `weekly_report` TINYINT(1) DEFAULT 1,
    `marketing_emails` TINYINT(1) DEFAULT 0,
    `telegram_notifications` TINYINT(1) DEFAULT 1,
    `email_notifications` TINYINT(1) DEFAULT 1,
    `push_notifications` TINYINT(1) DEFAULT 1,
    `quiet_hours_enabled` TINYINT(1) DEFAULT 0,
    `quiet_hours_start` TIME DEFAULT '22:00:00',
    `quiet_hours_end` TIME DEFAULT '07:00:00',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- API LOGS TABLE
-- =============================================
DROP TABLE IF EXISTS `api_logs`;
CREATE TABLE `api_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `endpoint` VARCHAR(255) NOT NULL,
    `method` VARCHAR(10) NOT NULL,
    `request_body` JSON DEFAULT NULL,
    `response_code` INT DEFAULT NULL,
    `response_time_ms` INT DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(500) DEFAULT NULL,
    `error_message` TEXT DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_endpoint` (`endpoint`),
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_response_code` (`response_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- PAYMENT TRANSACTIONS TABLE
-- =============================================
DROP TABLE IF EXISTS `payment_transactions`;
CREATE TABLE `payment_transactions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `subscription_id` INT UNSIGNED DEFAULT NULL,
    `transaction_id` VARCHAR(100) UNIQUE,
    `gateway` VARCHAR(50) NOT NULL,
    `gateway_transaction_id` VARCHAR(255) DEFAULT NULL,
    `type` ENUM('payment', 'refund', 'chargeback') DEFAULT 'payment',
    `amount` DECIMAL(15,2) NOT NULL,
    `currency` VARCHAR(3) DEFAULT 'USD',
    `fee` DECIMAL(15,2) DEFAULT 0.00,
    `net_amount` DECIMAL(15,2) DEFAULT 0.00,
    `status` ENUM('pending', 'processing', 'completed', 'failed', 'refunded', 'cancelled') DEFAULT 'pending',
    `payment_method` VARCHAR(50) DEFAULT NULL,
    `card_last_four` VARCHAR(4) DEFAULT NULL,
    `card_brand` VARCHAR(20) DEFAULT NULL,
    `receipt_url` VARCHAR(500) DEFAULT NULL,
    `failure_reason` TEXT DEFAULT NULL,
    `metadata` JSON DEFAULT NULL,
    `completed_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_subscription_id` (`subscription_id`),
    INDEX `idx_transaction_id` (`transaction_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_gateway` (`gateway`),
    INDEX `idx_created_at` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================
-- INSERT ADMIN USER
-- License Key: ZYN-A-ADMN-2024
-- Password: password (bcrypt hash)
-- =============================================
INSERT INTO `users` (
    `license_key`,
    `username`,
    `email`,
    `password`,
    `fullname`,
    `phone`,
    `country`,
    `timezone`,
    `olymptrade_id`,
    `olymptrade_account_type`,
    `role`,
    `status`,
    `package`,
    `package_expiry`,
    `referral_code`,
    `created_at`
) VALUES (
    'ZYN-A-ADMN-2024',
    'admin',
    'admin@zyntrade.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Admin ZYN Trade',
    '6281234567890',
    'ID',
    'Asia/Jakarta',
    '999999999',
    'real',
    'admin',
    'active',
    'vip',
    DATE_ADD(NOW(), INTERVAL 10 YEAR),
    'ZYNADMIN',
    NOW()
) ON DUPLICATE KEY UPDATE
    `password` = VALUES(`password`),
    `status` = 'active',
    `role` = 'admin',
    `updated_at` = NOW();

-- =============================================
-- INSERT ADMIN ROBOT SETTINGS
-- Admin bisa trading dengan semua fitur VIP
-- =============================================
INSERT INTO `robot_settings` (
    `user_id`,
    `robot_enabled`,
    `strategies`,
    `market`,
    `timeframe`,
    `risk_level`,
    `trade_amount`,
    `daily_limit`,
    `money_management_type`,
    `martingale_max_steps`,
    `martingale_multiplier`,
    `take_profit_target`,
    `max_loss_limit`,
    `schedule_mode`,
    `weekend_auto_off`,
    `daily_target_amount`,
    `active_strategies`,
    `notify_on_trade`,
    `notify_on_error`,
    `notify_on_pause`
) SELECT
    id,
    0,  -- robot_enabled: OFF by default, admin turn ON when ready
    '["RSI_MASTER","BB_SQUEEZE","MACD_DIVERGENCE","EMA_CROSS","STOCH_RSI","ADX_TREND","VOLUME_BREAKOUT","SUPPORT_RESISTANCE","CANDLE_PATTERN","MULTI_TF"]',
    'EUR/USD',
    '15M',
    'medium',
    10000.00,
    20,  -- VIP daily limit
    'flat',
    3,
    2.00,
    100.00,  -- VIP take profit target
    50.00,   -- VIP max loss limit
    'auto_24h',
    1,
    100.00,
    '["RSI_MASTER","BB_SQUEEZE","MACD_DIVERGENCE","EMA_CROSS","STOCH_RSI"]',
    1,
    1,
    1
FROM users WHERE license_key = 'ZYN-A-ADMN-2024'
ON DUPLICATE KEY UPDATE `updated_at` = NOW();

-- =============================================
-- INSERT ADMIN ROBOT STATUS
-- =============================================
INSERT INTO `robot_status` (
    `user_id`,
    `status`,
    `version`,
    `connection_status`
) SELECT
    id,
    'stopped',
    '3.1.0',
    'disconnected'
FROM users WHERE license_key = 'ZYN-A-ADMN-2024'
ON DUPLICATE KEY UPDATE `updated_at` = NOW();

-- =============================================
-- INSERT DEFAULT SETTINGS
-- =============================================
INSERT INTO `settings` (`key`, `value`, `type`, `group`, `description`, `is_public`) VALUES
('site_name', 'ZYN Trade System', 'string', 'general', 'Website name', 1),
('site_tagline', 'Precision Over Emotion', 'string', 'general', 'Website tagline', 1),
('site_version', '3.1.0', 'string', 'general', 'Current version', 1),
('affiliate_id', '660784', 'string', 'affiliate', 'OlympTrade affiliate ID', 0),
('olymptrade_affiliate_link', 'https://olymptrade.com/?affiliate_id=660784&subid1=ZYNtradeSystem', 'string', 'affiliate', 'OlympTrade affiliate link', 1),
('olymptrade_affiliate_link_id', 'https://olymptrade-vid.com/id-id/?affiliate_id=660784&subid1=ZYNtradeSystem', 'string', 'affiliate', 'OlympTrade affiliate link Indonesia', 1),
('telegram_channel', 'https://t.me/OlymptradeCopytrade', 'string', 'social', 'Telegram channel URL', 1),
('telegram_channel_id', '@OlymptradeCopytrade', 'string', 'social', 'Telegram channel ID', 1),
('telegram_support', '@aheenkgans', 'string', 'social', 'Telegram support username', 1),
('min_deposit', '10', 'number', 'trading', 'Minimum deposit requirement (USD)', 1),
('allowed_markets', 'EUR/USD,GBP/USD', 'string', 'trading', 'Allowed markets', 1),
('allowed_timeframes', '5M,15M,30M,1H', 'string', 'trading', 'Allowed timeframes', 1),
('default_tp_target', '50', 'number', 'trading', 'Default Take Profit target', 0),
('default_ml_limit', '25', 'number', 'trading', 'Default Max Loss limit', 0),
('real_account_only', '1', 'boolean', 'trading', 'Only allow real accounts', 0),
('weekend_auto_off', '1', 'boolean', 'trading', 'Auto-off on weekends', 0),
('maintenance_mode', '0', 'boolean', 'system', 'Enable maintenance mode', 0),
('currency', 'USD', 'string', 'payment', 'Default currency', 1),
('price_starter', '19', 'number', 'payment', 'STARTER package price in USD', 1),
('price_pro', '29', 'number', 'payment', 'PRO package price in USD', 1),
('price_elite', '79', 'number', 'payment', 'ELITE package price in USD', 1),
('price_vip', '149', 'number', 'payment', 'VIP package price in USD', 1),
('paypal_email', 'payment@zyntrade.com', 'string', 'payment', 'PayPal payment email', 0),
('wise_email', 'payment@zyntrade.com', 'string', 'payment', 'Wise payment email', 0),
('crypto_usdt_trc20', '', 'string', 'payment', 'USDT TRC20 wallet address', 0),
('crypto_btc', '', 'string', 'payment', 'BTC wallet address', 0),
('bank_name', 'BCA', 'string', 'payment', 'Bank name for transfer', 0),
('bank_account', '', 'string', 'payment', 'Bank account number', 0),
('bank_holder', 'ZYN TRADE SYSTEM', 'string', 'payment', 'Bank account holder name', 0)
ON DUPLICATE KEY UPDATE `updated_at` = NOW();

-- =============================================
-- INSERT SAMPLE LICENSE KEYS
-- =============================================
INSERT INTO `license_keys` (`license_key`, `package`, `status`) VALUES
('ZYN-FREE-DEMO-001', 'free', 'available'),
('ZYN-FREE-DEMO-002', 'free', 'available'),
('ZYN-FREE-DEMO-003', 'free', 'available'),
('ZYN-STARTER-DEMO-001', 'starter', 'available'),
('ZYN-PRO-DEMO-001', 'pro', 'available'),
('ZYN-ELITE-DEMO-001', 'elite', 'available'),
('ZYN-VIP-DEMO-001', 'vip', 'available')
ON DUPLICATE KEY UPDATE `updated_at` = NOW();

-- =============================================
-- CREATE VIEWS
-- =============================================

-- User Statistics View
CREATE OR REPLACE VIEW `view_user_stats` AS
SELECT
    u.id AS user_id,
    u.fullname,
    u.username,
    u.email,
    u.license_key,
    u.package,
    u.status,
    u.country,
    COALESCE(u.total_trades, 0) AS total_trades,
    COALESCE(u.wins, 0) AS wins,
    COALESCE(u.losses, 0) AS losses,
    COALESCE(u.total_profit, 0) AS total_pnl,
    CASE
        WHEN COALESCE(u.total_trades, 0) > 0
        THEN ROUND((COALESCE(u.wins, 0) / COALESCE(u.total_trades, 1)) * 100, 2)
        ELSE 0
    END AS win_rate,
    u.created_at AS member_since,
    u.last_login
FROM users u
WHERE u.role = 'user';

-- Leaderboard View (Monthly)
CREATE OR REPLACE VIEW `view_leaderboard_monthly` AS
SELECT
    u.id AS user_id,
    u.fullname,
    u.username,
    u.country,
    u.package,
    COUNT(t.id) AS total_trades,
    SUM(CASE WHEN t.result = 'win' THEN 1 ELSE 0 END) AS wins,
    SUM(COALESCE(t.profit_loss, 0)) AS total_profit,
    CASE
        WHEN COUNT(t.id) > 0
        THEN ROUND((SUM(CASE WHEN t.result = 'win' THEN 1 ELSE 0 END) / COUNT(t.id)) * 100, 2)
        ELSE 0
    END AS win_rate
FROM users u
LEFT JOIN trades t ON u.id = t.user_id AND t.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
WHERE u.role = 'user' AND u.status = 'active'
GROUP BY u.id
HAVING total_trades > 0
ORDER BY total_profit DESC;

-- Active Robots View
CREATE OR REPLACE VIEW `view_active_robots` AS
SELECT
    rs.user_id,
    u.fullname,
    u.username,
    u.package,
    rs.status,
    rs.version,
    rs.balance,
    rs.session_trades,
    rs.session_profit,
    rs.connection_status,
    rs.last_active,
    rs.heartbeat_count,
    TIMESTAMPDIFF(SECOND, rs.last_active, NOW()) AS seconds_since_active
FROM robot_status rs
JOIN users u ON u.id = rs.user_id
WHERE rs.status IN ('running', 'paused')
ORDER BY rs.last_active DESC;

-- =============================================
-- STORED PROCEDURES
-- =============================================

DELIMITER //

-- Update daily stats procedure
DROP PROCEDURE IF EXISTS `update_daily_stats`//
CREATE PROCEDURE `update_daily_stats`(IN p_user_id INT, IN p_date DATE)
BEGIN
    INSERT INTO daily_stats (user_id, date, total_trades, trades, wins, losses, ties, profit_loss, profit, win_rate)
    SELECT
        p_user_id,
        p_date,
        COUNT(*),
        COUNT(*),
        SUM(CASE WHEN result = 'win' THEN 1 ELSE 0 END),
        SUM(CASE WHEN result = 'loss' THEN 1 ELSE 0 END),
        SUM(CASE WHEN result = 'tie' THEN 1 ELSE 0 END),
        SUM(COALESCE(profit_loss, 0)),
        SUM(COALESCE(profit_loss, 0)),
        CASE
            WHEN COUNT(*) > 0
            THEN ROUND((SUM(CASE WHEN result = 'win' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2)
            ELSE 0
        END
    FROM trades
    WHERE user_id = p_user_id AND DATE(created_at) = p_date
    ON DUPLICATE KEY UPDATE
        total_trades = VALUES(total_trades),
        trades = VALUES(trades),
        wins = VALUES(wins),
        losses = VALUES(losses),
        ties = VALUES(ties),
        profit_loss = VALUES(profit_loss),
        profit = VALUES(profit),
        win_rate = VALUES(win_rate),
        updated_at = NOW();
END //

-- Generate license key procedure
DROP PROCEDURE IF EXISTS `generate_license_key`//
CREATE PROCEDURE `generate_license_key`(IN p_package VARCHAR(10), IN p_created_by INT)
BEGIN
    DECLARE new_key VARCHAR(64);
    DECLARE prefix VARCHAR(10);
    DECLARE key_exists INT DEFAULT 1;

    SET prefix = CASE p_package
        WHEN 'free' THEN 'ZYN-F'
        WHEN 'starter' THEN 'ZYN-S'
        WHEN 'pro' THEN 'ZYN-P'
        WHEN 'elite' THEN 'ZYN-E'
        WHEN 'vip' THEN 'ZYN-V'
        ELSE 'ZYN-X'
    END;

    WHILE key_exists > 0 DO
        SET new_key = CONCAT(
            prefix, '-',
            UPPER(SUBSTRING(MD5(RAND()), 1, 4)), '-',
            UPPER(SUBSTRING(MD5(RAND()), 1, 4)), '-',
            UPPER(SUBSTRING(MD5(RAND()), 1, 4))
        );
        SELECT COUNT(*) INTO key_exists FROM license_keys WHERE license_key = new_key;
    END WHILE;

    INSERT INTO license_keys (license_key, package, status, created_by)
    VALUES (new_key, p_package, 'available', p_created_by);

    SELECT new_key AS generated_key, p_package AS package;
END //

-- Cleanup expired sessions procedure
DROP PROCEDURE IF EXISTS `cleanup_expired_sessions`//
CREATE PROCEDURE `cleanup_expired_sessions`()
BEGIN
    DELETE FROM sessions WHERE last_activity < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 7 DAY));
END //

-- Update user stats from trades procedure
DROP PROCEDURE IF EXISTS `sync_user_stats`//
CREATE PROCEDURE `sync_user_stats`(IN p_user_id INT)
BEGIN
    UPDATE users u
    SET
        total_trades = (SELECT COUNT(*) FROM trades WHERE user_id = p_user_id),
        wins = (SELECT COUNT(*) FROM trades WHERE user_id = p_user_id AND result = 'win'),
        losses = (SELECT COUNT(*) FROM trades WHERE user_id = p_user_id AND result = 'loss'),
        total_profit = (SELECT COALESCE(SUM(profit_loss), 0) FROM trades WHERE user_id = p_user_id),
        updated_at = NOW()
    WHERE u.id = p_user_id;
END //

DELIMITER ;

-- =============================================
-- TRIGGERS
-- =============================================

DELIMITER //

-- Trigger to sync user stats after trade insert
DROP TRIGGER IF EXISTS `after_trade_insert`//
CREATE TRIGGER `after_trade_insert` AFTER INSERT ON `trades`
FOR EACH ROW
BEGIN
    UPDATE users
    SET
        total_trades = total_trades + 1,
        wins = wins + IF(NEW.result = 'win', 1, 0),
        losses = losses + IF(NEW.result = 'loss', 1, 0),
        total_profit = total_profit + COALESCE(NEW.profit_loss, 0),
        last_trade_at = NEW.created_at
    WHERE id = NEW.user_id;
END //

-- Trigger to update user stats after trade update
DROP TRIGGER IF EXISTS `after_trade_update`//
CREATE TRIGGER `after_trade_update` AFTER UPDATE ON `trades`
FOR EACH ROW
BEGIN
    IF OLD.result != NEW.result OR OLD.profit_loss != NEW.profit_loss THEN
        UPDATE users
        SET
            wins = wins - IF(OLD.result = 'win', 1, 0) + IF(NEW.result = 'win', 1, 0),
            losses = losses - IF(OLD.result = 'loss', 1, 0) + IF(NEW.result = 'loss', 1, 0),
            total_profit = total_profit - COALESCE(OLD.profit_loss, 0) + COALESCE(NEW.profit_loss, 0)
        WHERE id = NEW.user_id;
    END IF;
END //

DELIMITER ;

-- =============================================
-- PERFORMANCE INDEXES
-- =============================================
CREATE INDEX IF NOT EXISTS `idx_trades_user_date` ON trades(`user_id`, `created_at`);
CREATE INDEX IF NOT EXISTS `idx_trades_user_result_date` ON trades(`user_id`, `result`, `created_at`);
CREATE INDEX IF NOT EXISTS `idx_subs_user_status` ON subscriptions(`user_id`, `status`);

-- =============================================
-- IMPORT SELESAI!
--
-- LANGKAH SELANJUTNYA:
-- 1. Login ke: https://tester.situneo.my.id/login.php
-- 2. Masukkan License Key: ZYN-A-ADMN-2024
-- 3. Masukkan Password: password
-- 4. Akses Admin Panel: https://tester.situneo.my.id/admin/
-- 5. Verifikasi user yang mendaftar dari Admin Panel
-- =============================================
