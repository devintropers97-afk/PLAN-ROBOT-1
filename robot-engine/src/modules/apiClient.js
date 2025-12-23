/**
 * API Client Module
 * Komunikasi dengan PHP Backend Website
 */

const axios = require('axios');
const logger = require('../utils/logger');

class APIClient {
    constructor(baseUrl, apiKey = null) {
        this.baseUrl = baseUrl || process.env.API_BASE_URL;
        this.apiKey = apiKey || process.env.API_KEY;

        this.client = axios.create({
            baseURL: this.baseUrl,
            timeout: 30000,
            headers: {
                'Content-Type': 'application/json',
                'X-API-Key': this.apiKey,
                'User-Agent': 'ZYN-Trade-Robot/1.0'
            }
        });

        // Request interceptor for logging
        this.client.interceptors.request.use(
            (config) => {
                logger.debug(`API Request: ${config.method?.toUpperCase()} ${config.url}`);
                return config;
            },
            (error) => {
                logger.error('API Request Error:', error);
                return Promise.reject(error);
            }
        );

        // Response interceptor for logging
        this.client.interceptors.response.use(
            (response) => {
                logger.debug(`API Response: ${response.status} ${response.config.url}`);
                return response;
            },
            (error) => {
                logger.error('API Response Error:', error.response?.status, error.message);
                return Promise.reject(error);
            }
        );
    }

    /**
     * Validate license key
     */
    async validateLicense(licenseKey) {
        try {
            const response = await this.client.post('/api/validate-license.php', {
                license_key: licenseKey
            });

            return {
                valid: response.data.valid || false,
                plan: response.data.plan || 'FREE',
                expiry: response.data.expiry || null,
                strategies: response.data.strategies || [],
                user: response.data.user || null
            };
        } catch (error) {
            logger.error('License validation failed:', error.message);
            return { valid: false, error: error.message };
        }
    }

    /**
     * Get user robot settings
     */
    async getUserSettings(userId) {
        try {
            const response = await this.client.get('/api/user-settings.php', {
                params: { user_id: userId }
            });

            return response.data;
        } catch (error) {
            logger.error('Failed to get user settings:', error.message);
            return null;
        }
    }

    /**
     * Record a trade
     */
    async recordTrade(tradeData) {
        try {
            const response = await this.client.post('/api/record-trade.php', {
                user_id: tradeData.userId,
                asset: tradeData.asset,
                direction: tradeData.direction,
                amount: tradeData.amount,
                strategy_id: tradeData.strategyId,
                strategy_name: tradeData.strategyName,
                confidence: tradeData.confidence,
                indicators: tradeData.indicators,
                result: tradeData.result || 'pending',
                profit: tradeData.profit || 0,
                timeframe: tradeData.timeframe,
                entry_price: tradeData.entryPrice,
                exit_price: tradeData.exitPrice,
                expiry: tradeData.expiry
            });

            return {
                success: response.data.success || false,
                trade_id: response.data.trade_id
            };
        } catch (error) {
            logger.error('Failed to record trade:', error.message);
            return { success: false, error: error.message };
        }
    }

    /**
     * Update trade result
     */
    async updateTradeResult(tradeId, result, profit, exitPrice) {
        try {
            const response = await this.client.post('/api/update-trade.php', {
                trade_id: tradeId,
                result: result, // 'win', 'loss', 'draw'
                profit: profit,
                exit_price: exitPrice
            });

            return response.data.success || false;
        } catch (error) {
            logger.error('Failed to update trade result:', error.message);
            return false;
        }
    }

    /**
     * Get user statistics
     */
    async getUserStats(userId) {
        try {
            const response = await this.client.get('/api/user-stats.php', {
                params: { user_id: userId }
            });

            return response.data;
        } catch (error) {
            logger.error('Failed to get user stats:', error.message);
            return null;
        }
    }

    /**
     * Report robot status
     */
    async reportStatus(userId, status) {
        try {
            const response = await this.client.post('/api/robot-status.php', {
                user_id: userId,
                status: status, // 'running', 'paused', 'stopped', 'error'
                timestamp: new Date().toISOString(),
                version: '1.0.0'
            });

            return response.data.success || false;
        } catch (error) {
            logger.error('Failed to report status:', error.message);
            return false;
        }
    }

    /**
     * Get available assets/pairs
     */
    async getAvailableAssets() {
        try {
            const response = await this.client.get('/api/assets.php');
            return response.data.assets || [];
        } catch (error) {
            logger.error('Failed to get assets:', error.message);
            return [];
        }
    }

    /**
     * Get trading schedule for user
     */
    async getTradingSchedule(userId) {
        try {
            const response = await this.client.get('/api/schedule.php', {
                params: { user_id: userId }
            });

            return response.data;
        } catch (error) {
            logger.error('Failed to get trading schedule:', error.message);
            return null;
        }
    }

    /**
     * Check if user should auto-pause (TP/SL reached)
     */
    async checkAutoPause(userId) {
        try {
            const response = await this.client.get('/api/check-autopause.php', {
                params: { user_id: userId }
            });

            return {
                shouldPause: response.data.should_pause || false,
                reason: response.data.reason || null
            };
        } catch (error) {
            logger.error('Failed to check auto-pause:', error.message);
            return { shouldPause: false };
        }
    }

    /**
     * Trigger auto-pause
     */
    async triggerAutoPause(userId, reason) {
        try {
            const response = await this.client.post('/api/trigger-autopause.php', {
                user_id: userId,
                reason: reason,
                timestamp: new Date().toISOString()
            });

            return response.data.success || false;
        } catch (error) {
            logger.error('Failed to trigger auto-pause:', error.message);
            return false;
        }
    }

    /**
     * Log robot activity
     */
    async logActivity(userId, activity, details = {}) {
        try {
            const response = await this.client.post('/api/log-activity.php', {
                user_id: userId,
                activity: activity,
                details: JSON.stringify(details),
                timestamp: new Date().toISOString()
            });

            return response.data.success || false;
        } catch (error) {
            // Silent fail for logging
            return false;
        }
    }

    /**
     * Get Martingale settings
     */
    async getMartingaleSettings(userId) {
        try {
            const response = await this.client.get('/api/martingale-settings.php', {
                params: { user_id: userId }
            });

            return {
                enabled: response.data.enabled || false,
                multiplier: response.data.multiplier || 2,
                maxSteps: response.data.max_steps || 3,
                currentStep: response.data.current_step || 0
            };
        } catch (error) {
            logger.error('Failed to get Martingale settings:', error.message);
            return { enabled: false };
        }
    }

    /**
     * Update Martingale step
     */
    async updateMartingaleStep(userId, step, amount) {
        try {
            const response = await this.client.post('/api/update-martingale.php', {
                user_id: userId,
                step: step,
                amount: amount
            });

            return response.data.success || false;
        } catch (error) {
            logger.error('Failed to update Martingale step:', error.message);
            return false;
        }
    }

    /**
     * Health check
     */
    async healthCheck() {
        try {
            const response = await this.client.get('/api/health.php');
            return response.data.status === 'ok';
        } catch (error) {
            return false;
        }
    }
}

module.exports = APIClient;
