/**
 * ZYN Trade Robot - Database Module
 * Handles all MySQL database operations
 */

const mysql = require('mysql2/promise');
const logger = require('../utils/logger');
const { decryptPassword } = require('../utils/helpers');

class Database {
    constructor() {
        this.pool = null;
    }

    /**
     * Connect to database
     */
    async connect() {
        try {
            this.pool = mysql.createPool({
                host: process.env.DB_HOST || 'localhost',
                database: process.env.DB_NAME,
                user: process.env.DB_USER,
                password: process.env.DB_PASS,
                waitForConnections: true,
                connectionLimit: 10,
                queueLimit: 0,
                charset: 'utf8mb4'
            });

            // Test connection
            const connection = await this.pool.getConnection();
            connection.release();
            logger.info('Database pool created');
            return true;
        } catch (error) {
            logger.error('Database connection failed:', error);
            throw error;
        }
    }

    /**
     * Close database connection
     */
    async close() {
        if (this.pool) {
            await this.pool.end();
            logger.info('Database connection closed');
        }
    }

    /**
     * Get all active users with robot enabled AND OlympTrade credentials setup
     */
    async getActiveUsers() {
        try {
            const [rows] = await this.pool.execute(`
                SELECT u.*, rs.robot_enabled
                FROM users u
                INNER JOIN robot_settings rs ON u.id = rs.user_id
                WHERE u.status = 'active'
                AND rs.robot_enabled = 1
                AND u.olymptrade_setup_completed = 1
                AND u.olymptrade_email IS NOT NULL
                AND u.olymptrade_password IS NOT NULL
            `);
            return rows;
        } catch (error) {
            logger.error('Error getting active users:', error);
            return [];
        }
    }

    /**
     * Get OlympTrade credentials for a user (decrypts password)
     */
    async getOlympTradeCredentials(userId) {
        try {
            const [rows] = await this.pool.execute(`
                SELECT
                    olymptrade_email,
                    olymptrade_password,
                    olymptrade_account_type,
                    olymptrade_setup_completed
                FROM users
                WHERE id = ? AND olymptrade_setup_completed = 1
            `, [userId]);

            if (rows.length === 0) {
                logger.warn(`No OlympTrade credentials found for user ${userId}`);
                return null;
            }

            const creds = rows[0];

            // Decrypt the password
            const decryptedPassword = decryptPassword(creds.olymptrade_password);

            if (!decryptedPassword) {
                logger.error(`Failed to decrypt password for user ${userId}`);
                return null;
            }

            return {
                email: creds.olymptrade_email,
                password: decryptedPassword,
                accountType: creds.olymptrade_account_type || 'demo',
                isDemo: creds.olymptrade_account_type === 'demo'
            };
        } catch (error) {
            logger.error(`Error getting OlympTrade credentials for user ${userId}:`, error);
            return null;
        }
    }

    /**
     * Update robot connection status
     */
    async updateRobotStatus(userId, status, errorMessage = null) {
        try {
            await this.pool.execute(`
                INSERT INTO robot_status (user_id, status, last_active, error_message, connection_status)
                VALUES (?, ?, NOW(), ?, 'connected')
                ON DUPLICATE KEY UPDATE
                    status = VALUES(status),
                    last_active = NOW(),
                    error_message = VALUES(error_message),
                    connection_status = 'connected',
                    heartbeat_count = heartbeat_count + 1
            `, [userId, status, errorMessage]);
            return true;
        } catch (error) {
            logger.error(`Error updating robot status for user ${userId}:`, error);
            return false;
        }
    }

    /**
     * Update user balance from OlympTrade
     */
    async updateUserBalance(userId, balance) {
        try {
            await this.pool.execute(`
                UPDATE robot_status SET balance = ?, updated_at = NOW() WHERE user_id = ?
            `, [balance, userId]);
            return true;
        } catch (error) {
            logger.error(`Error updating balance for user ${userId}:`, error);
            return false;
        }
    }

    /**
     * Get user robot settings
     */
    async getUserRobotSettings(userId) {
        try {
            const [rows] = await this.pool.execute(
                'SELECT * FROM robot_settings WHERE user_id = ?',
                [userId]
            );
            return rows[0] || null;
        } catch (error) {
            logger.error(`Error getting robot settings for user ${userId}:`, error);
            return null;
        }
    }

    /**
     * Get today's trade count for user
     */
    async getTodayTradesCount(userId) {
        try {
            const [rows] = await this.pool.execute(`
                SELECT COUNT(*) as count
                FROM trades
                WHERE user_id = ?
                AND DATE(created_at) = CURDATE()
            `, [userId]);
            return rows[0]?.count || 0;
        } catch (error) {
            logger.error(`Error getting today's trades for user ${userId}:`, error);
            return 0;
        }
    }

    /**
     * Get user's daily P&L
     */
    async getDailyPnL(userId) {
        try {
            const [rows] = await this.pool.execute(`
                SELECT COALESCE(SUM(profit_loss), 0) as pnl
                FROM trades
                WHERE user_id = ?
                AND DATE(created_at) = CURDATE()
            `, [userId]);
            return parseFloat(rows[0]?.pnl) || 0;
        } catch (error) {
            logger.error(`Error getting daily PnL for user ${userId}:`, error);
            return 0;
        }
    }

    /**
     * Trigger auto-pause for user
     */
    async triggerAutoPause(userId, reason) {
        try {
            await this.pool.execute(`
                UPDATE robot_settings SET
                    robot_enabled = 0,
                    auto_pause_triggered = 1,
                    auto_pause_reason = ?,
                    auto_pause_time = NOW()
                WHERE user_id = ?
            `, [reason, userId]);

            // Create notification
            await this.createNotification(userId, 'auto_pause',
                reason === 'take_profit'
                    ? 'Robot di-pause: Target profit tercapai!'
                    : 'Robot di-pause: Batas maksimum loss tercapai'
            );

            logger.info(`Auto-pause triggered for user ${userId}: ${reason}`);
            return true;
        } catch (error) {
            logger.error(`Error triggering auto-pause for user ${userId}:`, error);
            return false;
        }
    }

    /**
     * Record a trade
     */
    async recordTrade(tradeData) {
        try {
            const [result] = await this.pool.execute(`
                INSERT INTO trades
                (user_id, strategy_id, strategy, asset, timeframe, amount, direction, result, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
            `, [
                tradeData.userId,
                tradeData.strategyId,
                tradeData.strategy,
                tradeData.asset,
                tradeData.timeframe,
                tradeData.amount,
                tradeData.direction
            ]);

            logger.info(`Trade recorded: ID ${result.insertId}`);
            return result.insertId;
        } catch (error) {
            logger.error('Error recording trade:', error);
            return null;
        }
    }

    /**
     * Update trade result
     */
    async updateTradeResult(tradeId, result, profitLoss) {
        try {
            await this.pool.execute(`
                UPDATE trades SET
                    result = ?,
                    profit_loss = ?,
                    updated_at = NOW()
                WHERE id = ?
            `, [result, profitLoss, tradeId]);

            logger.info(`Trade ${tradeId} updated: ${result}, P&L: ${profitLoss}`);
            return true;
        } catch (error) {
            logger.error(`Error updating trade ${tradeId}:`, error);
            return false;
        }
    }

    /**
     * Update daily stats for user after trade result
     */
    async updateDailyStats(userId, tradeResult) {
        try {
            const { result, profitLoss } = tradeResult;
            const today = new Date().toISOString().split('T')[0];

            // Use INSERT ... ON DUPLICATE KEY UPDATE for upsert
            await this.pool.execute(`
                INSERT INTO daily_stats (user_id, date, total_trades, trades, wins, losses, profit_loss, profit, updated_at)
                VALUES (?, ?, 1, 1,
                    ${result === 'win' ? 1 : 0},
                    ${result === 'loss' ? 1 : 0},
                    ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                    total_trades = total_trades + 1,
                    trades = trades + 1,
                    wins = wins + ${result === 'win' ? 1 : 0},
                    losses = losses + ${result === 'loss' ? 1 : 0},
                    profit_loss = profit_loss + ?,
                    profit = profit + ?,
                    win_rate = CASE WHEN (wins + ${result === 'win' ? 1 : 0}) > 0
                        THEN ROUND(((wins + ${result === 'win' ? 1 : 0}) / (total_trades + 1)) * 100, 2)
                        ELSE 0 END,
                    updated_at = NOW()
            `, [userId, today, profitLoss, profitLoss, profitLoss, profitLoss]);

            // Also update user's total stats
            await this.pool.execute(`
                UPDATE users SET
                    total_trades = total_trades + 1,
                    wins = wins + ${result === 'win' ? 1 : 0},
                    losses = losses + ${result === 'loss' ? 1 : 0},
                    total_profit = total_profit + ?,
                    last_trade_at = NOW()
                WHERE id = ?
            `, [profitLoss, userId]);

            // Update robot_settings current_daily_pnl
            await this.pool.execute(`
                UPDATE robot_settings SET current_daily_pnl = current_daily_pnl + ? WHERE user_id = ?
            `, [profitLoss, userId]);

            return true;
        } catch (error) {
            logger.error(`Error updating daily stats for user ${userId}:`, error);
            return false;
        }
    }

    /**
     * Update martingale step
     */
    async updateMartingaleStep(userId, step) {
        try {
            await this.pool.execute(`
                UPDATE robot_settings SET martingale_step = ? WHERE user_id = ?
            `, [step, userId]);
            return true;
        } catch (error) {
            logger.error(`Error updating martingale step for user ${userId}:`, error);
            return false;
        }
    }

    /**
     * Create notification for user
     */
    async createNotification(userId, type, message) {
        try {
            await this.pool.execute(`
                INSERT INTO notifications (user_id, type, message, created_at)
                VALUES (?, ?, ?, NOW())
            `, [userId, type, message]);
            return true;
        } catch (error) {
            logger.error(`Error creating notification for user ${userId}:`, error);
            return false;
        }
    }

    /**
     * Reset daily stats (called at midnight)
     */
    async resetDailyStats() {
        try {
            await this.pool.execute(`
                UPDATE robot_settings SET
                    current_daily_pnl = 0,
                    martingale_step = 0
            `);
            logger.info('Daily stats reset completed');
            return true;
        } catch (error) {
            logger.error('Error resetting daily stats:', error);
            return false;
        }
    }

    /**
     * Log activity
     */
    async logActivity(userId, action, description) {
        try {
            await this.pool.execute(`
                INSERT INTO activity_log (user_id, action, description, created_at)
                VALUES (?, ?, ?, NOW())
            `, [userId, action, description]);
            return true;
        } catch (error) {
            logger.error('Error logging activity:', error);
            return false;
        }
    }
}

module.exports = Database;
