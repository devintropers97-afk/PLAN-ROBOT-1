/**
 * =============================================
 * ZYN TRADE SYSTEM - ROBOT ENGINE
 * Version: 3.1.0 - Full Auto Trading
 * "Precision Over Emotion"
 * =============================================
 *
 * Main entry point for the trading robot.
 * This file orchestrates all modules:
 * - Multi-user session management
 * - Price Data Feed (TradingView/Yahoo)
 * - Technical Analysis (10 Strategies)
 * - Signal Generator
 * - Trade Executor (OlympTrade via Puppeteer)
 * - Database Sync (MySQL to cPanel)
 */

require('dotenv').config();
const cron = require('node-cron');
const express = require('express');
const cors = require('cors');
const logger = require('./utils/logger');
const Database = require('./modules/database');
const PriceDataFeed = require('./modules/priceData');
const SignalGenerator = require('./modules/signalGenerator');
const TradeExecutor = require('./modules/tradeExecutor');
const { isWeekend, isWithinSchedule, sleep } = require('./utils/helpers');

class ZYNTradeRobot {
    constructor() {
        this.isRunning = false;
        this.db = null;
        this.priceData = null;
        this.signalGenerator = null;
        this.tradeExecutor = null;
        this.activeUsers = [];
        this.stats = {
            startTime: null,
            totalCycles: 0,
            totalTrades: 0,
            totalSignals: 0,
            errors: 0
        };
        this.app = express();
    }

    /**
     * Initialize all modules
     */
    async initialize() {
        console.log('\n');
        logger.info('========================================');
        logger.info('   ZYN TRADE SYSTEM - ROBOT ENGINE');
        logger.info('   Version 3.1.0 - Full Auto Trading');
        logger.info('   "Precision Over Emotion"');
        logger.info('========================================');
        logger.info('');
        logger.info('Initializing robot...');

        try {
            // 1. Initialize Database Connection
            logger.info('[1/5] Connecting to database...');
            this.db = new Database();
            await this.db.connect();
            logger.info('✓ Database connected successfully');

            // 2. Initialize Price Data Feed
            logger.info('[2/5] Starting price data feed...');
            this.priceData = new PriceDataFeed();
            await this.priceData.initialize();
            logger.info('✓ Price data feed ready');

            // 3. Initialize Signal Generator
            logger.info('[3/5] Loading strategies...');
            this.signalGenerator = new SignalGenerator(this.priceData);
            logger.info('✓ Signal generator ready (10 strategies loaded)');

            // 4. Initialize Trade Executor (Multi-user)
            logger.info('[4/5] Initializing trade executor...');
            this.tradeExecutor = new TradeExecutor(this.db);
            logger.info('✓ Trade executor ready (multi-user mode)');

            // 5. Initialize API Server
            logger.info('[5/5] Starting API server...');
            this.setupAPIServer();
            logger.info('✓ API server ready');

            logger.info('');
            logger.info('========================================');
            logger.info('✓ Robot initialized successfully!');
            logger.info('========================================');
            logger.info('');

            this.stats.startTime = new Date();
            return true;
        } catch (error) {
            logger.error('Failed to initialize robot:', error);
            return false;
        }
    }

    /**
     * Setup Express API server for status monitoring
     */
    setupAPIServer() {
        this.app.use(cors());
        this.app.use(express.json());

        // Health check endpoint
        this.app.get('/api/health', (req, res) => {
            res.json({
                status: 'running',
                uptime: this.getUptime(),
                activeUsers: this.activeUsers.length,
                activeSessions: this.tradeExecutor.getActiveSessionCount(),
                stats: this.stats
            });
        });

        // Get active users
        this.app.get('/api/users/active', async (req, res) => {
            res.json({
                count: this.activeUsers.length,
                users: this.activeUsers.map(u => ({
                    id: u.id,
                    email: u.email,
                    package: u.package
                }))
            });
        });

        // Get session status for specific user
        this.app.get('/api/user/:userId/status', (req, res) => {
            const userId = parseInt(req.params.userId);
            const status = this.tradeExecutor.getSessionStatus(userId);
            res.json(status);
        });

        // Force refresh user list
        this.app.post('/api/refresh', async (req, res) => {
            this.activeUsers = await this.db.getActiveUsers();
            res.json({
                success: true,
                activeUsers: this.activeUsers.length
            });
        });

        const port = process.env.API_PORT || 3001;
        this.app.listen(port, () => {
            logger.info(`API server listening on port ${port}`);
        });
    }

    /**
     * Get uptime string
     */
    getUptime() {
        if (!this.stats.startTime) return '0s';
        const seconds = Math.floor((Date.now() - this.stats.startTime) / 1000);
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;
        return `${hours}h ${minutes}m ${secs}s`;
    }

    /**
     * Main trading loop
     */
    async runTradingCycle() {
        if (!this.isRunning) return;

        this.stats.totalCycles++;

        try {
            // Check if weekend
            if (isWeekend()) {
                if (this.stats.totalCycles % 60 === 0) { // Log every 30 min
                    logger.info('Weekend - markets closed');
                }
                return;
            }

            // Get active users with robot enabled AND OlympTrade credentials
            this.activeUsers = await this.db.getActiveUsers();

            if (this.activeUsers.length === 0) {
                if (this.stats.totalCycles % 20 === 0) { // Log every 10 min
                    logger.info('No active users with robot enabled');
                }
                return;
            }

            logger.info(`Processing ${this.activeUsers.length} active users...`);

            // Process each user
            for (const user of this.activeUsers) {
                await this.processUserTrading(user);
                // Small delay between users to avoid overload
                await sleep(500);
            }

        } catch (error) {
            this.stats.errors++;
            logger.error('Error in trading cycle:', error);
        }
    }

    /**
     * Process trading for a single user
     */
    async processUserTrading(user) {
        const userId = user.id;

        try {
            const settings = await this.db.getUserRobotSettings(userId);

            // Check if robot is enabled
            if (!settings || !settings.robot_enabled) {
                return;
            }

            // Check if auto-paused
            if (settings.auto_pause_triggered) {
                logger.debug(`[User ${userId}] Auto-paused, skipping`);
                return;
            }

            // Check schedule
            if (!isWithinSchedule(settings.schedule_mode, settings)) {
                logger.debug(`[User ${userId}] Outside schedule`);
                return;
            }

            // Check daily limit
            const todayTrades = await this.db.getTodayTradesCount(userId);
            if (todayTrades >= (settings.daily_limit || 10)) {
                logger.debug(`[User ${userId}] Daily limit reached (${todayTrades})`);
                return;
            }

            // Check daily P&L for auto-pause
            const dailyPnL = await this.db.getDailyPnL(userId);

            // Check take profit
            if (settings.take_profit_target && dailyPnL >= settings.take_profit_target) {
                await this.db.triggerAutoPause(userId, 'take_profit');
                logger.info(`[User ${userId}] Take profit reached ($${dailyPnL})! Auto-pausing...`);
                await this.tradeExecutor.closeSession(userId);
                return;
            }

            // Check max loss
            if (settings.max_loss_limit && dailyPnL < 0 && Math.abs(dailyPnL) >= settings.max_loss_limit) {
                await this.db.triggerAutoPause(userId, 'max_loss');
                logger.info(`[User ${userId}] Max loss reached ($${dailyPnL})! Auto-pausing...`);
                await this.tradeExecutor.closeSession(userId);
                return;
            }

            // Get user's selected strategies
            let strategies = [];
            try {
                strategies = JSON.parse(settings.active_strategies || settings.strategies || '[]');
            } catch {
                strategies = [];
            }

            if (strategies.length === 0) {
                // Use default strategy based on package
                strategies = this.getDefaultStrategies(user.package);
            }

            // Get market from settings
            const market = settings.market || 'EUR/USD';
            const timeframe = settings.timeframe || '15M';

            // Get current price data
            const priceData = await this.priceData.getCandles(market, timeframe, 100);

            if (!priceData || priceData.length === 0) {
                logger.warn(`[User ${userId}] No price data for ${market}`);
                return;
            }

            // Generate signals for each strategy
            for (const strategyId of strategies) {
                const signal = await this.signalGenerator.generateSignal(strategyId, priceData);

                if (signal && signal.execute) {
                    this.stats.totalSignals++;

                    logger.info(`[User ${userId}] Signal: ${signal.direction} via Strategy #${strategyId} (${signal.confidence}% confidence)`);

                    // Calculate trade amount
                    const amount = this.calculateTradeAmount(settings);

                    // Execute trade
                    const tradeResult = await this.tradeExecutor.executeTrade({
                        userId: userId,
                        direction: signal.direction,
                        amount: amount,
                        pair: market,
                        timeframe: timeframe,
                        strategyId: strategyId,
                        strategyName: signal.strategyName,
                        confidence: signal.confidence
                    });

                    if (tradeResult.success) {
                        this.stats.totalTrades++;

                        logger.info(`[User ${userId}] ✓ Trade executed: ${signal.direction} $${amount} on ${market}`);

                        // Record trade in database
                        await this.db.recordTrade({
                            userId: userId,
                            strategyId: strategyId,
                            strategy: signal.strategyName || `Strategy #${strategyId}`,
                            asset: market,
                            timeframe: timeframe,
                            amount: amount,
                            direction: signal.direction
                        });

                        // Update robot status
                        await this.db.updateRobotStatus(userId, 'running');

                        // Log activity
                        await this.db.logActivity(userId, 'trade',
                            `Executed ${signal.direction} trade: $${amount} on ${market}`);
                    } else {
                        logger.warn(`[User ${userId}] Trade failed: ${tradeResult.error}`);
                        await this.db.updateRobotStatus(userId, 'error', tradeResult.error);
                    }

                    // Only one trade per cycle per user
                    break;
                }
            }

        } catch (error) {
            this.stats.errors++;
            logger.error(`[User ${userId}] Processing error:`, error.message);
        }
    }

    /**
     * Get default strategies based on package
     */
    getDefaultStrategies(package_name) {
        const defaults = {
            'free': ['8', '9'],
            'starter': ['7', '8', '9'],
            'pro': ['6', '7', '8', '9'],
            'elite': ['3', '4', '5', '6', '7', '8', '9'],
            'vip': ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10']
        };
        return defaults[package_name] || defaults['free'];
    }

    /**
     * Calculate trade amount based on money management settings
     */
    calculateTradeAmount(settings) {
        const baseAmount = parseFloat(settings.trade_amount) || 10000;

        if (settings.money_management_type === 'martingale') {
            const step = parseInt(settings.martingale_step) || 0;
            const multiplier = parseFloat(settings.martingale_multiplier) || 2;
            const maxSteps = parseInt(settings.martingale_max_steps) || 3;
            const actualStep = Math.min(step, maxSteps);
            return Math.round(baseAmount * Math.pow(multiplier, actualStep));
        }

        // Flat amount (default)
        return baseAmount;
    }

    /**
     * Start the robot
     */
    async start() {
        const initialized = await this.initialize();
        if (!initialized) {
            logger.error('Failed to start robot - initialization failed');
            process.exit(1);
        }

        this.isRunning = true;

        // Run trading cycle every 30 seconds
        logger.info('Starting trading cycle (every 30 seconds)...');
        cron.schedule('*/30 * * * * *', async () => {
            await this.runTradingCycle();
        });

        // Health check every 5 minutes
        cron.schedule('*/5 * * * *', async () => {
            const sessions = this.tradeExecutor.getActiveSessionCount();
            logger.info(`[Health] Uptime: ${this.getUptime()} | Users: ${this.activeUsers.length} | Sessions: ${sessions} | Trades: ${this.stats.totalTrades}`);
        });

        // Reset daily stats at midnight (WIB = UTC+7)
        cron.schedule('0 17 * * *', async () => { // 00:00 WIB = 17:00 UTC
            logger.info('Midnight reset - resetting daily statistics...');
            await this.db.resetDailyStats();
            this.stats.totalSignals = 0;
        });

        // Refresh user list every minute
        cron.schedule('* * * * *', async () => {
            this.activeUsers = await this.db.getActiveUsers();
        });

        logger.info('');
        logger.info('🤖 Robot is now running!');
        logger.info('   Trading cycle: every 30 seconds');
        logger.info('   API endpoint: http://localhost:' + (process.env.API_PORT || 3001));
        logger.info('');
        logger.info('Press Ctrl+C to stop');
        logger.info('');
    }

    /**
     * Stop the robot gracefully
     */
    async stop() {
        logger.info('');
        logger.info('Stopping robot...');
        this.isRunning = false;

        if (this.tradeExecutor) {
            await this.tradeExecutor.closeAll();
        }

        if (this.db) {
            await this.db.close();
        }

        logger.info('Robot stopped');
        logger.info(`Session stats: ${this.stats.totalCycles} cycles, ${this.stats.totalTrades} trades, ${this.stats.errors} errors`);
        process.exit(0);
    }
}

// Create and start robot
const robot = new ZYNTradeRobot();

// Handle graceful shutdown
process.on('SIGINT', () => robot.stop());
process.on('SIGTERM', () => robot.stop());
process.on('uncaughtException', (error) => {
    logger.error('Uncaught exception:', error);
});
process.on('unhandledRejection', (reason, promise) => {
    logger.error('Unhandled rejection at:', promise, 'reason:', reason);
});

// Start the robot
robot.start().catch(error => {
    logger.error('Fatal error:', error);
    process.exit(1);
});
