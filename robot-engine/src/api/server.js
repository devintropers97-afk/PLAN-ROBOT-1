/**
 * ZYN Trade Robot - API Server
 *
 * Provides REST API for website integration
 * Handles trader requests and manages queue
 */

require('dotenv').config();
const express = require('express');
const cors = require('cors');
const path = require('path');
const TradeQueue = require('./tradeQueue');
const SessionManager = require('../utils/sessionManager');

const app = express();
const PORT = process.env.API_PORT || 3001;

// Middleware
app.use(cors());
app.use(express.json());

// Initialize managers
const sessionManager = new SessionManager();
const tradeQueue = new TradeQueue();

// API Key authentication middleware
const authenticateApiKey = (req, res, next) => {
    const apiKey = req.headers['x-api-key'] || req.query.api_key;
    const validApiKey = process.env.API_KEY || 'zyn-robot-secret-key';

    if (!apiKey || apiKey !== validApiKey) {
        return res.status(401).json({
            success: false,
            error: 'Invalid or missing API key'
        });
    }

    next();
};

// Health check
app.get('/health', (req, res) => {
    res.json({
        success: true,
        status: 'running',
        queue: {
            pending: tradeQueue.getPendingCount(),
            processing: tradeQueue.getProcessingCount()
        },
        timestamp: new Date().toISOString()
    });
});

// ============================================
// TRADER ENDPOINTS
// ============================================

/**
 * POST /api/trade/execute
 * Execute a trade for a trader
 *
 * Body: {
 *   email: string,
 *   password: string,
 *   direction: 'CALL' | 'PUT',
 *   amount: number,
 *   asset: string (optional, default EUR/USD),
 *   duration: number (optional, default 1 minute),
 *   isDemo: boolean (optional, default true)
 * }
 */
app.post('/api/trade/execute', authenticateApiKey, async (req, res) => {
    try {
        const {
            email,
            password,
            direction,
            amount,
            asset = 'EUR/USD',
            duration = 1,
            isDemo = true
        } = req.body;

        // Validation
        if (!email || !password) {
            return res.status(400).json({
                success: false,
                error: 'Email and password are required'
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

        // Check session status
        const sessionMeta = sessionManager.getSessionMeta(email);
        const shouldWait = sessionManager.shouldAvoidLogin(email);
        const waitTime = sessionManager.getWaitTime(email);

        if (shouldWait && waitTime > 0) {
            return res.status(429).json({
                success: false,
                error: 'Too many login attempts. Please wait.',
                waitTime: Math.ceil(waitTime / 1000 / 60), // minutes
                retryAfter: new Date(Date.now() + waitTime).toISOString()
            });
        }

        // Add to queue
        const job = await tradeQueue.addJob({
            type: 'EXECUTE_TRADE',
            trader: {
                email,
                password,
                isDemo
            },
            trade: {
                direction: direction.toUpperCase(),
                amount,
                asset,
                duration
            }
        });

        res.json({
            success: true,
            message: 'Trade queued successfully',
            jobId: job.id,
            position: tradeQueue.getPosition(job.id),
            estimatedWait: tradeQueue.getEstimatedWait(job.id)
        });

    } catch (error) {
        console.error('Trade execute error:', error);
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

/**
 * GET /api/trade/status/:jobId
 * Get status of a queued trade
 */
app.get('/api/trade/status/:jobId', authenticateApiKey, (req, res) => {
    const { jobId } = req.params;
    const job = tradeQueue.getJob(jobId);

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
            status: job.status,
            result: job.result || null,
            error: job.error || null,
            createdAt: job.createdAt,
            completedAt: job.completedAt || null
        }
    });
});

/**
 * POST /api/trader/login
 * Test login for a trader (validates credentials)
 */
app.post('/api/trader/login', authenticateApiKey, async (req, res) => {
    try {
        const { email, password, isDemo = true } = req.body;

        if (!email || !password) {
            return res.status(400).json({
                success: false,
                error: 'Email and password are required'
            });
        }

        // Check if should avoid login
        if (sessionManager.shouldAvoidLogin(email)) {
            const sessionMeta = sessionManager.getSessionMeta(email);

            if (sessionMeta?.lastLoginSuccess) {
                return res.json({
                    success: true,
                    message: 'Session already active',
                    hasActiveSession: true
                });
            }

            const waitTime = sessionManager.getWaitTime(email);
            return res.status(429).json({
                success: false,
                error: 'Too many login attempts',
                waitTime: Math.ceil(waitTime / 1000 / 60)
            });
        }

        // Add login job to queue
        const job = await tradeQueue.addJob({
            type: 'LOGIN_TEST',
            trader: { email, password, isDemo }
        });

        res.json({
            success: true,
            message: 'Login test queued',
            jobId: job.id
        });

    } catch (error) {
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

/**
 * GET /api/trader/balance
 * Get balance for a trader
 */
app.post('/api/trader/balance', authenticateApiKey, async (req, res) => {
    try {
        const { email, password, isDemo = true } = req.body;

        if (!email || !password) {
            return res.status(400).json({
                success: false,
                error: 'Email and password are required'
            });
        }

        const job = await tradeQueue.addJob({
            type: 'GET_BALANCE',
            trader: { email, password, isDemo }
        });

        res.json({
            success: true,
            message: 'Balance request queued',
            jobId: job.id
        });

    } catch (error) {
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

/**
 * GET /api/trader/session/:email
 * Get session info for a trader
 */
app.get('/api/trader/session/:email', authenticateApiKey, (req, res) => {
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
        session: {
            isValid: sessionManager.isSessionValid(email),
            lastActive: session.lastActive,
            loginCount: session.loginCount,
            lastLoginSuccess: session.lastLoginSuccess
        }
    });
});

/**
 * DELETE /api/trader/session/:email
 * Clear session for a trader
 */
app.delete('/api/trader/session/:email', authenticateApiKey, (req, res) => {
    const { email } = req.params;

    try {
        sessionManager.clearSession(email);
        res.json({
            success: true,
            message: 'Session cleared'
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

// ============================================
// QUEUE MANAGEMENT ENDPOINTS
// ============================================

/**
 * GET /api/queue/status
 * Get queue status
 */
app.get('/api/queue/status', authenticateApiKey, (req, res) => {
    res.json({
        success: true,
        queue: {
            pending: tradeQueue.getPendingCount(),
            processing: tradeQueue.getProcessingCount(),
            completed: tradeQueue.getCompletedCount(),
            failed: tradeQueue.getFailedCount()
        }
    });
});

/**
 * GET /api/queue/jobs
 * Get all jobs in queue
 */
app.get('/api/queue/jobs', authenticateApiKey, (req, res) => {
    const { status, limit = 50 } = req.query;

    let jobs = tradeQueue.getAllJobs();

    if (status) {
        jobs = jobs.filter(j => j.status === status);
    }

    jobs = jobs.slice(0, parseInt(limit));

    res.json({
        success: true,
        count: jobs.length,
        jobs: jobs.map(j => ({
            id: j.id,
            type: j.type,
            status: j.status,
            trader: j.trader?.email,
            createdAt: j.createdAt
        }))
    });
});

/**
 * DELETE /api/queue/job/:jobId
 * Cancel a pending job
 */
app.delete('/api/queue/job/:jobId', authenticateApiKey, (req, res) => {
    const { jobId } = req.params;

    try {
        const result = tradeQueue.cancelJob(jobId);
        res.json({
            success: result,
            message: result ? 'Job cancelled' : 'Job not found or already processing'
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

// ============================================
// ADMIN ENDPOINTS
// ============================================

/**
 * GET /api/sessions
 * Get all active sessions
 */
app.get('/api/sessions', authenticateApiKey, (req, res) => {
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
 * POST /api/sessions/cleanup
 * Cleanup expired sessions
 */
app.post('/api/sessions/cleanup', authenticateApiKey, (req, res) => {
    const cleaned = sessionManager.cleanupExpiredSessions();

    res.json({
        success: true,
        message: `Cleaned ${cleaned} expired sessions`
    });
});

// Error handler
app.use((err, req, res, next) => {
    console.error('API Error:', err);
    res.status(500).json({
        success: false,
        error: 'Internal server error'
    });
});

// Start server
function startServer() {
    return new Promise((resolve) => {
        const server = app.listen(PORT, () => {
            console.log(`
╔════════════════════════════════════════════════════════╗
║     ZYN TRADE ROBOT - API SERVER                       ║
╠════════════════════════════════════════════════════════╣
║  Status:    RUNNING                                    ║
║  Port:      ${PORT}                                         ║
║  Endpoints:                                            ║
║    POST /api/trade/execute    - Execute trade          ║
║    GET  /api/trade/status/:id - Get trade status       ║
║    POST /api/trader/login     - Test login             ║
║    POST /api/trader/balance   - Get balance            ║
║    GET  /api/queue/status     - Queue status           ║
╚════════════════════════════════════════════════════════╝
            `);
            resolve(server);
        });
    });
}

// Start queue processor
tradeQueue.startProcessor();

// Export for use as module
module.exports = { app, startServer, tradeQueue, sessionManager };

// Start if run directly
if (require.main === module) {
    startServer();
}
