/**
 * =============================================
 * ZYN TRADE ROBOT - PRODUCTION API SERVER
 * =============================================
 *
 * Production-ready API server for 10,000+ traders
 *
 * Features:
 * - Priority queue (VIP/PRO/FREE tiers)
 * - Per-trader rate limiting
 * - Health monitoring
 * - Graceful shutdown
 * - Request logging
 * - Error handling
 */

require('dotenv').config();
const express = require('express');
const cors = require('cors');
const helmet = require('helmet');
const ProductionQueue = require('./productionQueue');
const TradeExecutor = require('./tradeExecutor');
const SessionManager = require('../utils/sessionManager');

const app = express();
const PORT = process.env.API_PORT || 3001;
const API_KEY = process.env.API_KEY || 'zyn-robot-secret-key';

// Initialize components
const queue = new ProductionQueue({
    maxConcurrent: parseInt(process.env.MAX_CONCURRENT) || 5,
    maxPerTrader: 2,
    traderMaxPerHour: 20,
    traderCooldown: 30000
});

const executor = new TradeExecutor();
const sessionManager = new SessionManager();

// Set executor on queue
queue.setExecutor(executor);

// Middleware
app.use(cors());
app.use(express.json({ limit: '10kb' }));

// Request logging
app.use((req, res, next) => {
    const start = Date.now();
    res.on('finish', () => {
        const duration = Date.now() - start;
        console.log(`[API] ${req.method} ${req.path} - ${res.statusCode} (${duration}ms)`);
    });
    next();
});

// API Key authentication
const authenticate = (req, res, next) => {
    const apiKey = req.headers['x-api-key'] || req.query.api_key;

    if (!apiKey || apiKey !== API_KEY) {
        return res.status(401).json({
            success: false,
            error: 'Unauthorized - Invalid API key'
        });
    }

    next();
};

// ========================================
// PUBLIC ENDPOINTS
// ========================================

/**
 * Health check - no auth required
 */
app.get('/health', (req, res) => {
    const stats = queue.getStats();

    res.json({
        status: 'healthy',
        version: '3.1.0',
        queue: stats.queue,
        rates: stats.rates,
        uptime: stats.uptime,
        timestamp: new Date().toISOString()
    });
});

/**
 * Readiness check for load balancer
 */
app.get('/ready', (req, res) => {
    if (queue.isRunning) {
        res.json({ ready: true });
    } else {
        res.status(503).json({ ready: false });
    }
});

// ========================================
// TRADE ENDPOINTS
// ========================================

/**
 * Execute trade
 * POST /api/trade/execute
 */
app.post('/api/trade/execute', authenticate, async (req, res) => {
    try {
        const {
            email,
            password,
            direction,
            amount,
            asset = 'EUR/USD',
            duration = 1,
            isDemo = true,
            tier = 'FREE'
        } = req.body;

        // Validation
        if (!email || !password) {
            return res.status(400).json({
                success: false,
                error: 'Email and password required'
            });
        }

        if (!direction || !['CALL', 'PUT'].includes(direction.toUpperCase())) {
            return res.status(400).json({
                success: false,
                error: 'Direction must be CALL or PUT'
            });
        }

        if (!amount || amount < 1) {
            return res.status(400).json({
                success: false,
                error: 'Amount must be at least 1'
            });
        }

        // Get priority based on tier
        const priority = ProductionQueue.PRIORITY[tier.toUpperCase()] || ProductionQueue.PRIORITY.FREE;

        // Add to queue
        const result = await queue.addJob({
            type: 'EXECUTE_TRADE',
            trader: { email, password, isDemo },
            trade: { direction: direction.toUpperCase(), amount, asset, duration }
        }, priority);

        if (!result.success) {
            return res.status(429).json(result);
        }

        res.json({
            success: true,
            message: 'Trade queued',
            ...result
        });

    } catch (error) {
        console.error('[API] Trade error:', error);
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

/**
 * Get trade status
 * GET /api/trade/status/:jobId
 */
app.get('/api/trade/status/:jobId', authenticate, (req, res) => {
    const job = queue.getJob(req.params.jobId);

    if (!job) {
        return res.status(404).json({
            success: false,
            error: 'Job not found'
        });
    }

    res.json({
        success: true,
        job: {
            id: job.id,
            type: job.type,
            status: job.status,
            attempts: job.attempts,
            result: job.result,
            error: job.error,
            createdAt: job.createdAt,
            completedAt: job.completedAt,
            position: job.status === 'pending' ? queue.getPosition(job.id) : null
        }
    });
});

/**
 * Cancel pending trade
 * DELETE /api/trade/:jobId
 */
app.delete('/api/trade/:jobId', authenticate, (req, res) => {
    const result = queue.cancelJob(req.params.jobId);
    res.json(result);
});

// ========================================
// TRADER ENDPOINTS
// ========================================

/**
 * Test login
 * POST /api/trader/login
 */
app.post('/api/trader/login', authenticate, async (req, res) => {
    try {
        const { email, password, isDemo = true, tier = 'FREE' } = req.body;

        if (!email || !password) {
            return res.status(400).json({
                success: false,
                error: 'Email and password required'
            });
        }

        // Check if already has valid session
        if (sessionManager.isSessionValid(email)) {
            const meta = sessionManager.getSessionMeta(email);
            if (meta?.lastLoginSuccess) {
                return res.json({
                    success: true,
                    message: 'Session active',
                    hasSession: true
                });
            }
        }

        const priority = ProductionQueue.PRIORITY[tier.toUpperCase()] || ProductionQueue.PRIORITY.FREE;

        const result = await queue.addJob({
            type: 'LOGIN_TEST',
            trader: { email, password, isDemo }
        }, priority);

        if (!result.success) {
            return res.status(429).json(result);
        }

        res.json({
            success: true,
            message: 'Login test queued',
            ...result
        });

    } catch (error) {
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

/**
 * Get balance
 * POST /api/trader/balance
 */
app.post('/api/trader/balance', authenticate, async (req, res) => {
    try {
        const { email, password, isDemo = true, tier = 'FREE' } = req.body;

        if (!email || !password) {
            return res.status(400).json({
                success: false,
                error: 'Email and password required'
            });
        }

        const priority = ProductionQueue.PRIORITY[tier.toUpperCase()] || ProductionQueue.PRIORITY.FREE;

        const result = await queue.addJob({
            type: 'GET_BALANCE',
            trader: { email, password, isDemo }
        }, priority);

        if (!result.success) {
            return res.status(429).json(result);
        }

        res.json({
            success: true,
            message: 'Balance request queued',
            ...result
        });

    } catch (error) {
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

/**
 * Get trader session info
 * GET /api/trader/session/:email
 */
app.get('/api/trader/session/:email', authenticate, (req, res) => {
    const { email } = req.params;
    const session = sessionManager.getSessionMeta(email);

    if (!session) {
        return res.json({
            success: true,
            hasSession: false
        });
    }

    res.json({
        success: true,
        hasSession: true,
        isValid: sessionManager.isSessionValid(email),
        lastActive: session.lastActive,
        lastLoginSuccess: session.lastLoginSuccess
    });
});

/**
 * Clear trader session
 * DELETE /api/trader/session/:email
 */
app.delete('/api/trader/session/:email', authenticate, (req, res) => {
    try {
        sessionManager.clearSession(req.params.email);
        res.json({ success: true, message: 'Session cleared' });
    } catch (error) {
        res.status(500).json({ success: false, error: error.message });
    }
});

/**
 * Get trader's trade history
 * GET /api/trader/history/:email
 */
app.get('/api/trader/history/:email', authenticate, (req, res) => {
    const jobs = queue.getJobsByTrader(req.params.email, 50);

    res.json({
        success: true,
        count: jobs.length,
        trades: jobs.map(j => ({
            id: j.id,
            type: j.type,
            status: j.status,
            direction: j.trade?.direction,
            amount: j.trade?.amount,
            result: j.result,
            createdAt: j.createdAt,
            completedAt: j.completedAt
        }))
    });
});

// ========================================
// QUEUE MANAGEMENT
// ========================================

/**
 * Get queue statistics
 * GET /api/queue/stats
 */
app.get('/api/queue/stats', authenticate, (req, res) => {
    res.json({
        success: true,
        ...queue.getStats()
    });
});

/**
 * Get pending jobs
 * GET /api/queue/pending
 */
app.get('/api/queue/pending', authenticate, (req, res) => {
    const limit = parseInt(req.query.limit) || 100;
    const jobs = queue.getJobsByStatus(ProductionQueue.STATUS.PENDING, limit);

    res.json({
        success: true,
        count: jobs.length,
        jobs: jobs.map(j => ({
            id: j.id,
            type: j.type,
            trader: j.trader?.email,
            priority: j.priority,
            createdAt: j.createdAt,
            position: queue.getPosition(j.id)
        }))
    });
});

/**
 * Get failed jobs
 * GET /api/queue/failed
 */
app.get('/api/queue/failed', authenticate, (req, res) => {
    const limit = parseInt(req.query.limit) || 100;
    const jobs = queue.getJobsByStatus(ProductionQueue.STATUS.FAILED, limit);

    res.json({
        success: true,
        count: jobs.length,
        jobs: jobs.map(j => ({
            id: j.id,
            type: j.type,
            trader: j.trader?.email,
            error: j.error,
            attempts: j.attempts,
            createdAt: j.createdAt
        }))
    });
});

// ========================================
// ADMIN ENDPOINTS
// ========================================

/**
 * Get all active sessions
 * GET /api/admin/sessions
 */
app.get('/api/admin/sessions', authenticate, (req, res) => {
    const sessions = sessionManager.getAllSessions();

    res.json({
        success: true,
        count: sessions.length,
        sessions: sessions.map(s => ({
            email: s.email,
            isValid: s.isValid,
            lastActive: s.lastActive,
            loginCount: s.loginCount
        }))
    });
});

/**
 * Cleanup expired sessions
 * POST /api/admin/sessions/cleanup
 */
app.post('/api/admin/sessions/cleanup', authenticate, (req, res) => {
    const cleaned = sessionManager.cleanupExpiredSessions();
    res.json({
        success: true,
        cleaned
    });
});

/**
 * Cleanup old jobs
 * POST /api/admin/queue/cleanup
 */
app.post('/api/admin/queue/cleanup', authenticate, (req, res) => {
    const cleaned = queue.cleanup();
    res.json({
        success: true,
        cleaned
    });
});

// ========================================
// ERROR HANDLING
// ========================================

app.use((err, req, res, next) => {
    console.error('[API] Error:', err);
    res.status(500).json({
        success: false,
        error: 'Internal server error'
    });
});

// 404 handler
app.use((req, res) => {
    res.status(404).json({
        success: false,
        error: 'Endpoint not found'
    });
});

// ========================================
// SERVER STARTUP
// ========================================

let server;

function startServer() {
    return new Promise((resolve) => {
        server = app.listen(PORT, () => {
            console.log(`
╔════════════════════════════════════════════════════════════════╗
║          ZYN TRADE ROBOT - PRODUCTION API SERVER               ║
╠════════════════════════════════════════════════════════════════╣
║  Status:       RUNNING                                         ║
║  Port:         ${PORT}                                              ║
║  Max Workers:  ${queue.config.maxConcurrent}                                                ║
║  Trader Limit: ${queue.config.traderMaxPerHour} trades/hour per trader                      ║
╠════════════════════════════════════════════════════════════════╣
║  Endpoints:                                                    ║
║    POST /api/trade/execute     Execute trade                   ║
║    GET  /api/trade/status/:id  Get trade status                ║
║    POST /api/trader/login      Test login                      ║
║    POST /api/trader/balance    Get balance                     ║
║    GET  /api/queue/stats       Queue statistics                ║
║    GET  /health                Health check                    ║
╚════════════════════════════════════════════════════════════════╝
            `);

            // Start queue processor
            queue.start();

            resolve(server);
        });
    });
}

// Graceful shutdown
async function shutdown(signal) {
    console.log(`\n[API] Received ${signal}, shutting down gracefully...`);

    // Stop accepting new requests
    if (server) {
        server.close();
    }

    // Shutdown queue
    await queue.shutdown();

    console.log('[API] Shutdown complete');
    process.exit(0);
}

process.on('SIGTERM', () => shutdown('SIGTERM'));
process.on('SIGINT', () => shutdown('SIGINT'));

// Handle uncaught errors
process.on('uncaughtException', (err) => {
    console.error('[API] Uncaught Exception:', err);
    shutdown('uncaughtException');
});

process.on('unhandledRejection', (err) => {
    console.error('[API] Unhandled Rejection:', err);
});

// Export for testing
module.exports = { app, startServer, queue, sessionManager };

// Start if run directly
if (require.main === module) {
    startServer();
}
