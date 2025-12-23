-- =============================================
-- ZYN TRADE ROBOT - DATABASE SCHEMA
-- =============================================
-- Optimized for 10,000+ traders
-- MySQL 8.0+
-- =============================================

-- Drop tables if exist (for fresh install)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS trade_history;
DROP TABLE IF EXISTS trade_signals;
DROP TABLE IF EXISTS trader_sessions;
DROP TABLE IF EXISTS trader_settings;
DROP TABLE IF EXISTS traders;
DROP TABLE IF EXISTS subscription_tiers;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS affiliate_links;
DROP TABLE IF EXISTS news_signals;
DROP TABLE IF EXISTS strategies;
DROP TABLE IF EXISTS settings;
SET FOREIGN_KEY_CHECKS = 1;

-- =============================================
-- AFFILIATE LINKS (Per-country affiliate links)
-- =============================================
CREATE TABLE affiliate_links (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    country_code VARCHAR(5) NOT NULL UNIQUE,
    affiliate_link TEXT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    click_count INT UNSIGNED DEFAULT 0,
    conversion_count INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_country (country_code),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default affiliate links
INSERT INTO affiliate_links (country_code, affiliate_link, is_active) VALUES
('id', 'https://olymptrade.com/id/', 1),
('en', 'https://olymptrade.com/en/', 1);

-- =============================================
-- STRATEGIES (Trading strategies configuration)
-- =============================================
CREATE TABLE strategies (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    tier_required ENUM('FREE', 'PRO', 'ELITE', 'VIP') DEFAULT 'FREE',

    -- Indicators configuration
    indicators JSON NOT NULL,
    timeframes JSON DEFAULT '["5m", "15m"]',
    assets JSON DEFAULT '["EUR/USD"]',

    -- Performance
    win_rate_backtest DECIMAL(5,2) DEFAULT 0.00,
    signals_per_day INT UNSIGNED DEFAULT 10,
    risk_level ENUM('low', 'medium', 'high') DEFAULT 'medium',

    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_tier (tier_required),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default strategies
INSERT INTO strategies (code, name, description, tier_required, indicators, win_rate_backtest, signals_per_day, risk_level) VALUES
('BLITZ-SIGNAL', 'Blitz Signal', 'Fast momentum-based signals using RSI and Bollinger Bands', 'FREE', '{"rsi": {"period": 14, "oversold": 30, "overbought": 70}, "bb": {"period": 20, "stddev": 2}}', 69.00, 20, 'medium'),
('APEX-HUNTER', 'Apex Hunter', 'Divergence detection for reversal trades', 'FREE', '{"rsi": {"period": 14}, "macd": {"fast": 12, "slow": 26, "signal": 9}}', 55.00, 15, 'high'),
('TITAN-PULSE', 'Titan Pulse', 'Multi-indicator confirmation for reliable signals', 'PRO', '{"rsi": {"period": 14}, "stoch": {"k": 14, "d": 3}, "ema": [9, 21]}', 75.00, 10, 'low'),
('SHADOW-EDGE', 'Shadow Edge', 'Price action with indicator confirmation', 'PRO', '{"atr": {"period": 14}, "rsi": {"period": 7}, "ema": [5, 13]}', 73.00, 12, 'medium'),
('STEALTH-MODE', 'Stealth Mode', 'Hidden divergence detection with volume confirmation', 'ELITE', '{"rsi": {"period": 14}, "obv": true, "macd": {"fast": 12, "slow": 26}}', 83.00, 8, 'low'),
('PHOENIX-X1', 'Phoenix X1', 'Extreme oversold/overbought detection', 'ELITE', '{"rsi": {"period": 2}, "bb": {"period": 20}, "stoch": {"k": 5}}', 81.00, 10, 'medium'),
('VORTEX-PRO', 'Vortex Pro', 'Support/resistance with momentum confirmation', 'ELITE', '{"pivot": true, "rsi": {"period": 14}, "atr": {"period": 14}}', 78.00, 6, 'low'),
('NEXUS-WAVE', 'Nexus Wave', 'Multi-timeframe trend confirmation', 'VIP', '{"ema": [9, 21, 55], "rsi": {"period": 14}, "macd": true}', 87.00, 5, 'low'),
('QUANTUM-FLOW', 'Quantum Flow', 'Advanced pattern recognition with multiple confirmations', 'VIP', '{"patterns": true, "indicators": ["rsi", "macd", "stoch", "bb"]}', 85.00, 4, 'low'),
('ORACLE-PRIME', 'Oracle Prime', 'Ultimate multi-layer analysis for highest accuracy', 'VIP', '{"multi_tf": true, "sentiment": true, "all_indicators": true}', 91.00, 3, 'low');

-- =============================================
-- NEWS SIGNALS (Economic calendar news signals)
-- =============================================
CREATE TABLE news_signals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id VARCHAR(50),
    event_name VARCHAR(255) NOT NULL,
    currency VARCHAR(10) NOT NULL,
    impact ENUM('low', 'medium', 'high') DEFAULT 'medium',

    -- Event time
    event_time TIMESTAMP NOT NULL,

    -- Forecast and actual values
    previous_value VARCHAR(50),
    forecast_value VARCHAR(50),
    actual_value VARCHAR(50),

    -- Trading signal
    direction ENUM('CALL', 'PUT', 'NEUTRAL'),
    confidence INT UNSIGNED DEFAULT 50,
    recommended_asset VARCHAR(20),

    -- Status
    status ENUM('upcoming', 'released', 'traded', 'skipped') DEFAULT 'upcoming',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_event_time (event_time),
    INDEX idx_currency (currency),
    INDEX idx_impact (impact),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- SETTINGS (System-wide settings)
-- =============================================
CREATE TABLE settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) NOT NULL UNIQUE,
    `value` TEXT,
    `type` ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT INTO settings (`key`, `value`, `type`, description) VALUES
('site_name', 'ZYN Trade System', 'string', 'Website name'),
('site_tagline', 'Presisi Diatas Emosi', 'string', 'Website tagline'),
('default_language', 'id', 'string', 'Default language code'),
('maintenance_mode', '0', 'boolean', 'Enable maintenance mode'),
('robot_api_url', 'http://localhost:3001', 'string', 'Robot API URL'),
('robot_api_key', 'zyn-robot-secret-key', 'string', 'Robot API Key'),
('news_api_key', '', 'string', 'Economic news API key'),
('news_api_enabled', '0', 'boolean', 'Enable news-based signals'),
('telegram_bot_token', '', 'string', 'Telegram bot token'),
('telegram_channel', '', 'string', 'Telegram channel for signals'),
('min_deposit', '10', 'number', 'Minimum deposit in USD'),
('price_pro', '99000', 'number', 'PRO tier price (IDR)'),
('price_elite', '199000', 'number', 'ELITE tier price (IDR)'),
('price_vip', '499000', 'number', 'VIP tier price (IDR)');

-- =============================================
-- SUBSCRIPTION TIERS
-- =============================================
CREATE TABLE subscription_tiers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    priority INT UNSIGNED DEFAULT 25,
    max_trades_per_hour INT UNSIGNED DEFAULT 10,
    max_trades_per_day INT UNSIGNED DEFAULT 100,
    allowed_strategies JSON,
    price_monthly DECIMAL(10,2) DEFAULT 0,
    price_yearly DECIMAL(10,2) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default tiers
INSERT INTO subscription_tiers (name, priority, max_trades_per_hour, max_trades_per_day, allowed_strategies, price_monthly) VALUES
('FREE', 25, 10, 50, '["BLITZ-SIGNAL", "APEX-HUNTER"]', 0),
('PRO', 50, 20, 150, '["BLITZ-SIGNAL", "APEX-HUNTER", "TITAN-PULSE", "SHADOW-EDGE"]', 99000),
('ELITE', 75, 30, 250, '["BLITZ-SIGNAL", "APEX-HUNTER", "TITAN-PULSE", "SHADOW-EDGE", "STEALTH-MODE", "PHOENIX-X1", "VORTEX-PRO"]', 199000),
('VIP', 100, 50, 500, '["ALL"]', 499000);

-- =============================================
-- USERS (Website accounts)
-- =============================================
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100),
    phone VARCHAR(20),
    tier_id INT UNSIGNED DEFAULT 1,
    is_admin TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    email_verified_at TIMESTAMP NULL,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (tier_id) REFERENCES subscription_tiers(id),
    INDEX idx_email (email),
    INDEX idx_tier (tier_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TRADERS (OlympTrade accounts linked to users)
-- =============================================
CREATE TABLE traders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    olymptrade_email VARCHAR(255) NOT NULL,
    olymptrade_password_encrypted TEXT NOT NULL,
    account_type ENUM('demo', 'real') DEFAULT 'demo',
    is_active TINYINT(1) DEFAULT 1,
    auto_trade TINYINT(1) DEFAULT 0,

    -- Trading settings
    default_amount DECIMAL(10,2) DEFAULT 1.00,
    max_amount DECIMAL(10,2) DEFAULT 100.00,
    default_asset VARCHAR(20) DEFAULT 'EUR/USD',
    default_duration INT UNSIGNED DEFAULT 1,
    strategy VARCHAR(50) DEFAULT 'BLITZ-SIGNAL',

    -- Limits
    daily_trade_limit INT UNSIGNED DEFAULT 50,
    daily_loss_limit DECIMAL(10,2) DEFAULT 100.00,
    daily_profit_target DECIMAL(10,2) DEFAULT 200.00,

    -- Stats
    total_trades INT UNSIGNED DEFAULT 0,
    total_wins INT UNSIGNED DEFAULT 0,
    total_losses INT UNSIGNED DEFAULT 0,
    total_profit DECIMAL(12,2) DEFAULT 0.00,
    current_balance DECIMAL(12,2) DEFAULT 0.00,
    last_trade_at TIMESTAMP NULL,

    -- Session info
    session_valid TINYINT(1) DEFAULT 0,
    session_last_check TIMESTAMP NULL,
    login_attempts INT UNSIGNED DEFAULT 0,
    last_login_attempt TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_olymptrade (olymptrade_email),
    INDEX idx_user (user_id),
    INDEX idx_auto_trade (auto_trade),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TRADER SETTINGS (Additional settings per trader)
-- =============================================
CREATE TABLE trader_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    trader_id INT UNSIGNED NOT NULL UNIQUE,

    -- Schedule settings
    schedule_enabled TINYINT(1) DEFAULT 0,
    schedule_mode ENUM('24h', 'best_hours', 'custom', 'per_day') DEFAULT 'best_hours',
    schedule_custom JSON,

    -- Money management
    money_management ENUM('flat', 'martingale', 'percentage') DEFAULT 'flat',
    martingale_multiplier DECIMAL(3,2) DEFAULT 2.00,
    martingale_max_steps INT UNSIGNED DEFAULT 3,
    percentage_of_balance DECIMAL(5,2) DEFAULT 2.00,

    -- Risk management
    stop_on_loss_streak INT UNSIGNED DEFAULT 3,
    pause_after_win_streak INT UNSIGNED DEFAULT 5,

    -- Notifications
    notify_email TINYINT(1) DEFAULT 1,
    notify_telegram TINYINT(1) DEFAULT 0,
    telegram_chat_id VARCHAR(50),

    -- Advanced
    min_signal_confidence INT UNSIGNED DEFAULT 70,
    allowed_assets JSON DEFAULT '["EUR/USD"]',
    blocked_hours JSON,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (trader_id) REFERENCES traders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TRADE SIGNALS (Generated signals)
-- =============================================
CREATE TABLE trade_signals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    strategy VARCHAR(50) NOT NULL,
    asset VARCHAR(20) NOT NULL,
    timeframe VARCHAR(10) NOT NULL,
    direction ENUM('CALL', 'PUT') NOT NULL,
    confidence INT UNSIGNED NOT NULL,
    entry_price DECIMAL(20,8),
    indicators JSON,
    is_active TINYINT(1) DEFAULT 1,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_strategy (strategy),
    INDEX idx_asset (asset),
    INDEX idx_active (is_active),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TRADE HISTORY (All executed trades)
-- =============================================
CREATE TABLE trade_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    trader_id INT UNSIGNED NOT NULL,
    signal_id BIGINT UNSIGNED,
    job_id VARCHAR(50),

    -- Trade details
    direction ENUM('CALL', 'PUT') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    asset VARCHAR(20) NOT NULL,
    duration INT UNSIGNED DEFAULT 1,

    -- Execution
    status ENUM('pending', 'executed', 'won', 'lost', 'draw', 'cancelled', 'error') DEFAULT 'pending',
    entry_price DECIMAL(20,8),
    exit_price DECIMAL(20,8),
    profit DECIMAL(12,2) DEFAULT 0.00,
    payout_percentage DECIMAL(5,2),

    -- Metadata
    strategy VARCHAR(50),
    signal_confidence INT UNSIGNED,
    account_type ENUM('demo', 'real') DEFAULT 'demo',
    execution_time_ms INT UNSIGNED,
    error_message TEXT,

    executed_at TIMESTAMP NULL,
    settled_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (trader_id) REFERENCES traders(id) ON DELETE CASCADE,
    FOREIGN KEY (signal_id) REFERENCES trade_signals(id) ON DELETE SET NULL,
    INDEX idx_trader (trader_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at),
    INDEX idx_job (job_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TRADER SESSIONS (Browser session tracking)
-- =============================================
CREATE TABLE trader_sessions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    trader_id INT UNSIGNED NOT NULL UNIQUE,
    session_id VARCHAR(50),
    is_valid TINYINT(1) DEFAULT 0,
    last_activity TIMESTAMP NULL,
    login_count INT UNSIGNED DEFAULT 0,
    last_login_success TINYINT(1) DEFAULT 0,
    last_login_error TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (trader_id) REFERENCES traders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- DAILY STATISTICS (Aggregated daily stats)
-- =============================================
CREATE TABLE daily_stats (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    trader_id INT UNSIGNED NOT NULL,
    date DATE NOT NULL,

    total_trades INT UNSIGNED DEFAULT 0,
    wins INT UNSIGNED DEFAULT 0,
    losses INT UNSIGNED DEFAULT 0,
    draws INT UNSIGNED DEFAULT 0,

    total_amount DECIMAL(12,2) DEFAULT 0.00,
    total_profit DECIMAL(12,2) DEFAULT 0.00,
    win_rate DECIMAL(5,2) DEFAULT 0.00,

    best_streak INT DEFAULT 0,
    worst_streak INT DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (trader_id) REFERENCES traders(id) ON DELETE CASCADE,
    UNIQUE KEY unique_trader_date (trader_id, date),
    INDEX idx_date (date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- VIEWS FOR COMMON QUERIES
-- =============================================

-- Active traders with their tier info
CREATE VIEW v_active_traders AS
SELECT
    t.id AS trader_id,
    t.user_id,
    t.olymptrade_email,
    t.account_type,
    t.auto_trade,
    t.default_amount,
    t.strategy,
    t.total_trades,
    t.total_wins,
    t.total_profit,
    t.current_balance,
    u.email AS user_email,
    u.name AS user_name,
    st.name AS tier_name,
    st.priority,
    st.max_trades_per_hour,
    st.max_trades_per_day
FROM traders t
JOIN users u ON t.user_id = u.id
JOIN subscription_tiers st ON u.tier_id = st.id
WHERE t.is_active = 1 AND u.is_active = 1;

-- Today's trade summary per trader
CREATE VIEW v_today_summary AS
SELECT
    t.id AS trader_id,
    t.olymptrade_email,
    COUNT(th.id) AS trades_today,
    SUM(CASE WHEN th.status = 'won' THEN 1 ELSE 0 END) AS wins_today,
    SUM(CASE WHEN th.status = 'lost' THEN 1 ELSE 0 END) AS losses_today,
    COALESCE(SUM(th.profit), 0) AS profit_today,
    ROUND(SUM(CASE WHEN th.status = 'won' THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(th.id), 0), 2) AS win_rate_today
FROM traders t
LEFT JOIN trade_history th ON t.id = th.trader_id
    AND DATE(th.created_at) = CURDATE()
    AND th.status IN ('won', 'lost', 'draw')
GROUP BY t.id, t.olymptrade_email;

-- =============================================
-- STORED PROCEDURES
-- =============================================

DELIMITER //

-- Update trader stats after trade settlement
CREATE PROCEDURE sp_update_trader_stats(
    IN p_trader_id INT UNSIGNED,
    IN p_trade_id BIGINT UNSIGNED,
    IN p_status VARCHAR(20),
    IN p_profit DECIMAL(12,2)
)
BEGIN
    -- Update trader totals
    UPDATE traders
    SET
        total_trades = total_trades + 1,
        total_wins = total_wins + IF(p_status = 'won', 1, 0),
        total_losses = total_losses + IF(p_status = 'lost', 1, 0),
        total_profit = total_profit + p_profit,
        last_trade_at = NOW()
    WHERE id = p_trader_id;

    -- Update or insert daily stats
    INSERT INTO daily_stats (trader_id, date, total_trades, wins, losses, total_profit, win_rate)
    VALUES (
        p_trader_id,
        CURDATE(),
        1,
        IF(p_status = 'won', 1, 0),
        IF(p_status = 'lost', 1, 0),
        p_profit,
        IF(p_status = 'won', 100, 0)
    )
    ON DUPLICATE KEY UPDATE
        total_trades = total_trades + 1,
        wins = wins + IF(p_status = 'won', 1, 0),
        losses = losses + IF(p_status = 'lost', 1, 0),
        total_profit = total_profit + p_profit,
        win_rate = ROUND(wins * 100.0 / total_trades, 2),
        updated_at = NOW();
END //

-- Get traders ready for auto-trading
CREATE PROCEDURE sp_get_ready_traders()
BEGIN
    SELECT
        t.*,
        u.email AS user_email,
        st.name AS tier_name,
        st.priority,
        st.max_trades_per_hour,
        st.max_trades_per_day,
        COALESCE(ds.total_trades, 0) AS trades_today
    FROM traders t
    JOIN users u ON t.user_id = u.id
    JOIN subscription_tiers st ON u.tier_id = st.id
    LEFT JOIN daily_stats ds ON t.id = ds.trader_id AND ds.date = CURDATE()
    WHERE
        t.is_active = 1
        AND t.auto_trade = 1
        AND u.is_active = 1
        AND t.session_valid = 1
        AND (ds.total_trades IS NULL OR ds.total_trades < t.daily_trade_limit)
        AND (ds.total_profit IS NULL OR ds.total_profit > -t.daily_loss_limit)
    ORDER BY st.priority DESC, t.last_trade_at ASC;
END //

DELIMITER ;

-- =============================================
-- INDEXES FOR PERFORMANCE
-- =============================================

-- Additional indexes for common queries
CREATE INDEX idx_trade_history_date ON trade_history(created_at);
CREATE INDEX idx_trade_history_trader_status ON trade_history(trader_id, status);
CREATE INDEX idx_daily_stats_trader ON daily_stats(trader_id, date);

-- =============================================
-- INITIAL ADMIN USER
-- =============================================

-- Password: admin123 (hashed with bcrypt)
INSERT INTO users (email, password, name, tier_id, is_admin) VALUES
('admin@zyntrade.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 4, 1);

-- =============================================
-- SAMPLE DATA (Optional - for testing)
-- =============================================

-- Uncomment below to add sample traders
/*
INSERT INTO users (email, password, name, tier_id) VALUES
('trader1@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Trader 1', 1),
('trader2@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Trader 2', 2),
('trader3@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Trader 3', 3);
*/
