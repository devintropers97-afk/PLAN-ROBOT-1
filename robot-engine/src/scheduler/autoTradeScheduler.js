/**
 * =============================================
 * ZYN Trade Robot - Auto Trade Scheduler
 * =============================================
 *
 * Automated trading based on signals
 * - Reads signals from database
 * - Matches traders based on strategy subscription
 * - Queues trades with proper rate limiting
 * - Respects trader schedules and settings
 *
 * Run: node src/scheduler/autoTradeScheduler.js
 * PM2: Runs automatically via ecosystem.config.js
 */

require('dotenv').config();
const mysql = require('mysql2/promise');
const axios = require('axios');

const LOG_PREFIX = '[AutoTrader]';

// Configuration
const CONFIG = {
    // Database
    db: {
        host: process.env.DB_HOST || 'localhost',
        user: process.env.DB_USER || 'root',
        password: process.env.DB_PASSWORD || '',
        database: process.env.DB_NAME || 'zyn_trade',
        waitForConnections: true,
        connectionLimit: 10
    },
    // Robot API
    robotApi: {
        url: process.env.ROBOT_API_URL || 'http://localhost:3001',
        key: process.env.ROBOT_API_KEY || 'zyn-robot-secret-key'
    },
    // Scheduler
    pollInterval: parseInt(process.env.SCHEDULER_POLL_INTERVAL) || 5000, // 5 seconds
    batchSize: parseInt(process.env.SCHEDULER_BATCH_SIZE) || 50,
    maxConcurrent: parseInt(process.env.SCHEDULER_MAX_CONCURRENT) || 20
};

// Database pool
let pool = null;

// Stats
const stats = {
    signalsProcessed: 0,
    tradesQueued: 0,
    errors: 0,
    lastRun: null
};

/**
 * Initialize database connection
 */
async function initDatabase() {
    try {
        pool = mysql.createPool(CONFIG.db);
        // Test connection
        const conn = await pool.getConnection();
        log('Database connected successfully');
        conn.release();
        return true;
    } catch (error) {
        log(`Database connection failed: ${error.message}`);
        return false;
    }
}

/**
 * Logger
 */
function log(message, level = 'info') {
    const timestamp = new Date().toISOString();
    const prefix = level === 'error' ? '❌' : level === 'warn' ? '⚠️' : '✓';
    console.log(`${LOG_PREFIX} ${timestamp} ${prefix} ${message}`);
}

/**
 * Get pending signals that need to be processed
 */
async function getPendingSignals() {
    const query = `
        SELECT
            ts.*,
            s.name as strategy_name,
            s.tier_required
        FROM trade_signals ts
        JOIN strategies s ON ts.strategy_id = s.id
        WHERE ts.status = 'pending'
        AND ts.execute_at <= NOW()
        AND ts.expires_at > NOW()
        ORDER BY ts.priority DESC, ts.created_at ASC
        LIMIT ?
    `;

    const [signals] = await pool.execute(query, [CONFIG.batchSize]);
    return signals;
}

/**
 * Get eligible traders for a signal
 */
async function getEligibleTraders(signal) {
    const query = `
        SELECT
            t.id as trader_id,
            t.user_id,
            t.olymptrade_email,
            t.olymptrade_password_encrypted,
            ts.strategy_ids,
            ts.amount_per_trade,
            ts.money_management_type,
            ts.is_active as settings_active,
            ts.schedule_mode,
            ts.schedule_data,
            ts.use_demo_account,
            u.tier_id,
            st.name as tier_name,
            st.priority as tier_priority
        FROM traders t
        JOIN users u ON t.user_id = u.id
        JOIN subscription_tiers st ON u.tier_id = st.id
        LEFT JOIN trader_settings ts ON t.id = ts.trader_id
        WHERE t.is_active = 1
        AND t.login_status != 'failed'
        AND (ts.is_active = 1 OR ts.is_active IS NULL)
        AND st.priority >= ?
        AND (
            ts.strategy_ids IS NULL
            OR JSON_CONTAINS(ts.strategy_ids, ?)
        )
        ORDER BY st.priority DESC, t.last_trade_at ASC
        LIMIT ?
    `;

    // Get minimum tier required for this strategy
    const minTierPriority = getTierPriority(signal.tier_required);
    const strategyIdJson = JSON.stringify(signal.strategy_id.toString());

    const [traders] = await pool.execute(query, [minTierPriority, strategyIdJson, 500]);
    return traders;
}

/**
 * Get tier priority number
 */
function getTierPriority(tierName) {
    const priorities = {
        'FREE': 25,
        'STARTER': 50,
        'PRO': 75,
        'ELITE': 85,
        'VIP': 100
    };
    return priorities[tierName] || 25;
}

/**
 * Check if trader is within schedule
 */
function isWithinSchedule(trader) {
    if (!trader.schedule_mode || trader.schedule_mode === 'auto_24h') {
        return true;
    }

    const now = new Date();
    const currentHour = now.getHours();
    const currentMinute = now.getMinutes();
    const currentDay = now.getDay();

    // Skip weekends if configured
    if ([0, 6].includes(currentDay)) {
        return false; // Saturday and Sunday
    }

    try {
        const scheduleData = typeof trader.schedule_data === 'string'
            ? JSON.parse(trader.schedule_data)
            : trader.schedule_data;

        if (!scheduleData) return true;

        switch (trader.schedule_mode) {
            case 'best_hours':
                // 14:00 - 22:00 WIB (07:00 - 15:00 UTC)
                return currentHour >= 7 && currentHour < 15;

            case 'custom_single':
                const start = parseTime(scheduleData.start);
                const end = parseTime(scheduleData.end);
                const current = currentHour * 60 + currentMinute;
                return current >= start && current < end;

            case 'multi_session':
                if (!Array.isArray(scheduleData.sessions)) return true;
                const currentTime = currentHour * 60 + currentMinute;
                return scheduleData.sessions.some(session => {
                    const sessionStart = parseTime(session.start);
                    const sessionEnd = parseTime(session.end);
                    return currentTime >= sessionStart && currentTime < sessionEnd;
                });

            case 'per_day':
                const dayNames = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
                const todaySchedule = scheduleData[dayNames[currentDay]];
                if (!todaySchedule || !todaySchedule.active) return false;
                const dayStart = parseTime(todaySchedule.start);
                const dayEnd = parseTime(todaySchedule.end);
                const dayCurrentTime = currentHour * 60 + currentMinute;
                return dayCurrentTime >= dayStart && dayCurrentTime < dayEnd;

            default:
                return true;
        }
    } catch (error) {
        log(`Schedule parse error for trader ${trader.trader_id}: ${error.message}`, 'warn');
        return true; // Allow trading if schedule can't be parsed
    }
}

/**
 * Parse time string to minutes
 */
function parseTime(timeStr) {
    if (!timeStr) return 0;
    const [hours, minutes] = timeStr.split(':').map(Number);
    return hours * 60 + (minutes || 0);
}

/**
 * Calculate trade amount based on money management
 */
function calculateTradeAmount(trader, consecutiveLosses = 0) {
    const baseAmount = trader.amount_per_trade || 10;
    const mmType = trader.money_management_type || 'flat';

    if (mmType === 'martingale' && consecutiveLosses > 0) {
        const maxSteps = 3;
        const step = Math.min(consecutiveLosses, maxSteps);
        return baseAmount * Math.pow(2, step);
    }

    return baseAmount;
}

/**
 * Queue trade with Robot API
 */
async function queueTrade(trader, signal) {
    try {
        const response = await axios.post(
            `${CONFIG.robotApi.url}/api/trade/execute`,
            {
                email: trader.olymptrade_email,
                password: trader.olymptrade_password_encrypted, // Will be decrypted by API
                direction: signal.direction,
                amount: calculateTradeAmount(trader),
                asset: signal.asset || 'EUR/USD',
                duration: signal.duration || 1,
                isDemo: trader.use_demo_account !== false,
                tier: trader.tier_name,
                signalId: signal.id,
                traderId: trader.trader_id,
                encrypted: true // Indicate password is encrypted
            },
            {
                headers: {
                    'Content-Type': 'application/json',
                    'X-API-Key': CONFIG.robotApi.key
                },
                timeout: 10000
            }
        );

        if (response.data.success) {
            await recordTradeRequest(trader, signal, response.data.jobId);
            return { success: true, jobId: response.data.jobId };
        } else {
            return { success: false, error: response.data.error };
        }
    } catch (error) {
        const errorMsg = error.response?.data?.error || error.message;
        log(`Failed to queue trade for trader ${trader.trader_id}: ${errorMsg}`, 'error');
        return { success: false, error: errorMsg };
    }
}

/**
 * Record trade request in database
 */
async function recordTradeRequest(trader, signal, jobId) {
    const query = `
        INSERT INTO trade_history (
            trader_id, signal_id, job_id, direction, amount, asset,
            status, account_type, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, NOW())
    `;

    await pool.execute(query, [
        trader.trader_id,
        signal.id,
        jobId,
        signal.direction,
        calculateTradeAmount(trader),
        signal.asset || 'EUR/USD',
        trader.use_demo_account !== false ? 'demo' : 'real'
    ]);

    // Update trader's last trade time
    await pool.execute(
        'UPDATE traders SET last_trade_at = NOW() WHERE id = ?',
        [trader.trader_id]
    );
}

/**
 * Mark signal as processed
 */
async function markSignalProcessed(signalId, tradersProcessed) {
    await pool.execute(
        `UPDATE trade_signals SET
            status = 'processed',
            processed_at = NOW(),
            traders_matched = ?
        WHERE id = ?`,
        [tradersProcessed, signalId]
    );
}

/**
 * Process a single signal
 */
async function processSignal(signal) {
    log(`Processing signal #${signal.id}: ${signal.direction} ${signal.asset}`);

    try {
        // Get eligible traders
        const traders = await getEligibleTraders(signal);
        log(`Found ${traders.length} eligible traders for signal #${signal.id}`);

        let queued = 0;
        let skipped = 0;

        // Process traders in parallel (limited)
        const chunks = [];
        for (let i = 0; i < traders.length; i += CONFIG.maxConcurrent) {
            chunks.push(traders.slice(i, i + CONFIG.maxConcurrent));
        }

        for (const chunk of chunks) {
            const results = await Promise.all(
                chunk.map(async (trader) => {
                    // Check schedule
                    if (!isWithinSchedule(trader)) {
                        return { skipped: true, reason: 'schedule' };
                    }

                    // Queue trade
                    const result = await queueTrade(trader, signal);
                    return result;
                })
            );

            for (const result of results) {
                if (result.skipped) {
                    skipped++;
                } else if (result.success) {
                    queued++;
                }
            }

            // Small delay between chunks
            if (chunks.length > 1) {
                await new Promise(r => setTimeout(r, 100));
            }
        }

        // Mark signal as processed
        await markSignalProcessed(signal.id, queued);

        stats.tradesQueued += queued;
        log(`Signal #${signal.id} processed: ${queued} trades queued, ${skipped} skipped`);

        return { queued, skipped };

    } catch (error) {
        log(`Error processing signal #${signal.id}: ${error.message}`, 'error');
        stats.errors++;

        // Mark signal as failed
        await pool.execute(
            `UPDATE trade_signals SET status = 'failed', error_message = ? WHERE id = ?`,
            [error.message, signal.id]
        );

        return { queued: 0, error: error.message };
    }
}

/**
 * Main scheduler loop
 */
async function runScheduler() {
    log('Starting Auto Trade Scheduler...');

    // Initialize database
    if (!await initDatabase()) {
        log('Failed to initialize database. Exiting.', 'error');
        process.exit(1);
    }

    // Main loop
    while (true) {
        try {
            stats.lastRun = new Date();

            // Get pending signals
            const signals = await getPendingSignals();

            if (signals.length > 0) {
                log(`Found ${signals.length} pending signals`);

                for (const signal of signals) {
                    await processSignal(signal);
                    stats.signalsProcessed++;
                }
            }

        } catch (error) {
            log(`Scheduler error: ${error.message}`, 'error');
            stats.errors++;
        }

        // Wait before next poll
        await new Promise(r => setTimeout(r, CONFIG.pollInterval));
    }
}

/**
 * Create signal manually (for testing or manual signals)
 */
async function createSignal(direction, asset = 'EUR/USD', strategyId = 1, options = {}) {
    const query = `
        INSERT INTO trade_signals (
            strategy_id, direction, asset, duration,
            priority, execute_at, expires_at, status, created_at
        ) VALUES (?, ?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 5 MINUTE), 'pending', NOW())
    `;

    const [result] = await pool.execute(query, [
        strategyId,
        direction.toUpperCase(),
        asset,
        options.duration || 1,
        options.priority || 50
    ]);

    return result.insertId;
}

/**
 * Get scheduler stats
 */
function getStats() {
    return {
        ...stats,
        uptime: process.uptime(),
        memory: process.memoryUsage()
    };
}

// Graceful shutdown
process.on('SIGTERM', async () => {
    log('Received SIGTERM, shutting down...');
    if (pool) {
        await pool.end();
    }
    process.exit(0);
});

process.on('SIGINT', async () => {
    log('Received SIGINT, shutting down...');
    if (pool) {
        await pool.end();
    }
    process.exit(0);
});

// Export for testing
module.exports = {
    runScheduler,
    processSignal,
    createSignal,
    getStats,
    initDatabase
};

// Run if called directly
if (require.main === module) {
    runScheduler().catch(error => {
        log(`Fatal error: ${error.message}`, 'error');
        process.exit(1);
    });
}
