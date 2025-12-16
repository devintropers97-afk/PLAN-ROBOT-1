/**
 * =============================================
 * ZYN TRADE SYSTEM - ROBOT ENGINE
 * Version: 1.0.0
 * "Precision Over Emotion"
 * =============================================
 *
 * Main entry point for the trading robot.
 * This file orchestrates all modules:
 * - Price Data Feed
 * - Technical Analysis (10 Strategies)
 * - Signal Generator
 * - Trade Executor (OlympTrade via Puppeteer)
 * - Database Sync (MySQL)
 */

require('dotenv').config();
const cron = require('node-cron');
const logger = require('./utils/logger');
const Database = require('./modules/database');
const PriceDataFeed = require('./modules/priceData');
const SignalGenerator = require('./modules/signalGenerator');
const TradeExecutor = require('./modules/tradeExecutor');
const { isWeekend, isWithinSchedule } = require('./utils/helpers');

class ZYNTradeRobot {
    constructor() {
        this.isRunning = false;
        this.db = null;
        this.priceData = null;
        this.signalGenerator = null;
        this.tradeExecutor = null;
        this.activeUsers = [];
    }

    /**
     * Initialize all modules
     */
    async initialize() {
        logger.info('========================================');
        logger.info('   ZYN TRADE SYSTEM - ROBOT ENGINE');
        logger.info('   Precision Over Emotion');
        logger.info('========================================');
        logger.info('Initializing robot...');

        try {
            // 1. Initialize Database Connection
            logger.info('[1/4] Connecting to database...');
            this.db = new Database();
            await this.db.connect();
            logger.info('Database connected successfully');

            // 2. Initialize Price Data Feed
            logger.info('[2/4] Starting price data feed...');
            this.priceData = new PriceDataFeed();
            await this.priceData.initialize();
            logger.info('Price data feed ready');

            // 3. Initialize Signal Generator
            logger.info('[3/4] Loading strategies...');
            this.signalGenerator = new SignalGenerator(this.priceData);
            logger.info('Signal generator ready with 10 strategies');

            // 4. Initialize Trade Executor
            logger.info('[4/4] Initializing trade executor...');
            this.tradeExecutor = new TradeExecutor(this.db);
            logger.info('Trade executor ready');

            logger.info('========================================');
            logger.info('Robot initialized successfully!');
            logger.info('========================================');

            return true;
        } catch (error) {
            logger.error('Failed to initialize robot:', error);
            return false;
        }
    }

    /**
     * Main trading loop
     */
    async runTradingCycle() {
        if (!this.isRunning) return;

        try {
            // Check if weekend
            if (isWeekend()) {
                logger.info('Weekend detected - robot paused');
                return;
            }

            // Get active users with robot enabled
            this.activeUsers = await this.db.getActiveUsers();
            logger.info(`Active users with robot enabled: ${this.activeUsers.length}`);

            // Process each user
            for (const user of this.activeUsers) {
                await this.processUserTrading(user);
            }

        } catch (error) {
            logger.error('Error in trading cycle:', error);
        }
    }

    /**
     * Process trading for a single user
     */
    async processUserTrading(user) {
        try {
            const settings = await this.db.getUserRobotSettings(user.id);

            // Check if robot is enabled
            if (!settings.robot_enabled) return;

            // Check if auto-paused
            if (settings.auto_pause_triggered) {
                logger.debug(`User ${user.id}: Auto-paused, skipping`);
                return;
            }

            // Check schedule
            if (!isWithinSchedule(settings.schedule_mode, settings)) {
                logger.debug(`User ${user.id}: Outside schedule, skipping`);
                return;
            }

            // Check daily limit
            const todayTrades = await this.db.getTodayTradesCount(user.id);
            if (todayTrades >= settings.daily_limit) {
                logger.debug(`User ${user.id}: Daily limit reached (${todayTrades}/${settings.daily_limit})`);
                return;
            }

            // Get user's selected strategies
            const strategies = JSON.parse(settings.strategies || '[]');
            if (strategies.length === 0) {
                logger.debug(`User ${user.id}: No strategies selected`);
                return;
            }

            // Get current price data
            const priceData = await this.priceData.getCandles(settings.market, settings.timeframe, 100);

            // Generate signals for each strategy
            for (const strategyId of strategies) {
                const signal = await this.signalGenerator.generateSignal(strategyId, priceData);

                if (signal.execute) {
                    logger.info(`User ${user.id}: Signal generated - ${signal.direction} via Strategy #${strategyId}`);

                    // Check auto-pause conditions
                    const dailyPnL = await this.db.getDailyPnL(user.id);
                    if (dailyPnL >= settings.take_profit_target) {
                        await this.db.triggerAutoPause(user.id, 'take_profit');
                        logger.info(`User ${user.id}: Take profit reached! Auto-pausing...`);
                        return;
                    }
                    if (Math.abs(dailyPnL) >= settings.max_loss_limit && dailyPnL < 0) {
                        await this.db.triggerAutoPause(user.id, 'max_loss');
                        logger.info(`User ${user.id}: Max loss reached! Auto-pausing...`);
                        return;
                    }

                    // Calculate amount based on money management
                    const amount = this.calculateTradeAmount(settings);

                    // Execute trade
                    const tradeResult = await this.tradeExecutor.executeTrade({
                        userId: user.id,
                        direction: signal.direction,
                        amount: amount,
                        pair: settings.market,
                        timeframe: settings.timeframe,
                        strategyId: strategyId,
                        strategyName: signal.strategyName,
                        confidence: signal.confidence
                    });

                    if (tradeResult.success) {
                        logger.info(`User ${user.id}: Trade executed successfully - ${tradeResult.tradeId}`);

                        // Record trade in database
                        await this.db.recordTrade({
                            userId: user.id,
                            strategyId: strategyId,
                            strategy: signal.strategyName,
                            asset: settings.market,
                            timeframe: settings.timeframe,
                            amount: amount,
                            direction: signal.direction,
                            olymptradeTradeId: tradeResult.tradeId
                        });
                    }

                    // Only one trade per cycle per user
                    break;
                }
            }

        } catch (error) {
            logger.error(`Error processing user ${user.id}:`, error);
        }
    }

    /**
     * Calculate trade amount based on money management settings
     * Martingale: x1.5 after each loss, max 10 steps
     */
    calculateTradeAmount(settings) {
        const baseAmount = parseFloat(settings.trade_amount) || 10000;

        if (settings.money_management_type === 'martingale') {
            const step = parseInt(settings.martingale_step) || 0;
            const multiplier = 1.5; // Updated: 1.5x (naik 50% per step)
            const maxSteps = 10;
            const actualStep = Math.min(step, maxSteps);

            // Calculate amount: baseAmount * 1.5^step
            // Step 0: 1x, Step 1: 1.5x, Step 2: 2.25x, ... Step 10: 57.67x
            const amount = Math.round(baseAmount * Math.pow(multiplier, actualStep));

            logger.debug(`Martingale calculation: Step ${actualStep}, Base: ${baseAmount}, Amount: ${amount}`);

            return amount;
        }

        // Flat amount
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
        cron.schedule('*/30 * * * * *', async () => {
            await this.runTradingCycle();
        });

        // Health check every 5 minutes
        cron.schedule('*/5 * * * *', async () => {
            logger.info('Health check: Robot is running');
            logger.info(`Active users: ${this.activeUsers.length}`);
        });

        // Reset daily stats at midnight
        cron.schedule('0 0 * * *', async () => {
            logger.info('Resetting daily statistics...');
            await this.db.resetDailyStats();
        });

        logger.info('Robot started - trading cycle running every 30 seconds');
        logger.info('Press Ctrl+C to stop');
    }

    /**
     * Stop the robot gracefully
     */
    async stop() {
        logger.info('Stopping robot...');
        this.isRunning = false;

        if (this.tradeExecutor) {
            await this.tradeExecutor.close();
        }

        if (this.db) {
            await this.db.close();
        }

        logger.info('Robot stopped');
        process.exit(0);
    }
}

// Create and start robot
const robot = new ZYNTradeRobot();

// Handle graceful shutdown
process.on('SIGINT', () => robot.stop());
process.on('SIGTERM', () => robot.stop());

// Start the robot
robot.start().catch(error => {
    logger.error('Fatal error:', error);
    process.exit(1);
});
