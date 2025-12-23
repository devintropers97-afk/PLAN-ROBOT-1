/**
 * =============================================
 * ZYN TRADE ROBOT - PRODUCTION QUEUE SYSTEM
 * =============================================
 *
 * Optimized for 10,000+ traders with:
 * - Priority queue (VIP > PRO > FREE)
 * - Per-trader rate limiting
 * - Concurrent execution with limits
 * - Auto-retry with exponential backoff
 * - Job persistence (survives restart)
 * - Real-time statistics
 */

const crypto = require('crypto');
const fs = require('fs');
const path = require('path');
const EventEmitter = require('events');

class ProductionQueue extends EventEmitter {
    constructor(options = {}) {
        super();

        // Configuration
        this.config = {
            maxConcurrent: options.maxConcurrent || 5,          // Max concurrent jobs
            maxPerTrader: options.maxPerTrader || 2,            // Max jobs per trader at once
            retryAttempts: options.retryAttempts || 3,          // Retry attempts
            retryDelay: options.retryDelay || 5000,             // Base retry delay (ms)
            jobTimeout: options.jobTimeout || 180000,           // Job timeout (3 min)
            cleanupInterval: options.cleanupInterval || 300000, // Cleanup every 5 min
            persistPath: options.persistPath || path.join(__dirname, '../../data/queue.json'),

            // Rate limiting per trader
            traderCooldown: options.traderCooldown || 30000,    // 30s between trades per trader
            traderMaxPerHour: options.traderMaxPerHour || 20,   // Max trades per trader per hour
        };

        // State
        this.jobs = new Map();
        this.pendingQueue = [];       // Array of job IDs, sorted by priority
        this.processingJobs = new Set();
        this.traderJobs = new Map();  // trader email -> Set of job IDs
        this.traderStats = new Map(); // trader email -> stats
        this.isRunning = false;

        // Statistics
        this.stats = {
            totalQueued: 0,
            totalProcessed: 0,
            totalCompleted: 0,
            totalFailed: 0,
            totalRetried: 0,
            startTime: Date.now()
        };

        // Ensure data directory exists
        const dataDir = path.dirname(this.config.persistPath);
        if (!fs.existsSync(dataDir)) {
            fs.mkdirSync(dataDir, { recursive: true });
        }

        // Load persisted jobs
        this.loadJobs();

        // Start cleanup interval
        this.cleanupTimer = setInterval(() => this.cleanup(), this.config.cleanupInterval);
    }

    /**
     * Priority levels (higher = more priority)
     */
    static PRIORITY = {
        VIP: 100,
        ELITE: 75,
        PRO: 50,
        FREE: 25,
        LOW: 10
    };

    /**
     * Job statuses
     */
    static STATUS = {
        PENDING: 'pending',
        PROCESSING: 'processing',
        COMPLETED: 'completed',
        FAILED: 'failed',
        CANCELLED: 'cancelled',
        RATE_LIMITED: 'rate_limited'
    };

    /**
     * Generate unique job ID
     */
    generateJobId() {
        return `job_${Date.now()}_${crypto.randomBytes(6).toString('hex')}`;
    }

    /**
     * Add job to queue
     */
    async addJob(data, priority = ProductionQueue.PRIORITY.FREE) {
        const traderEmail = data.trader?.email;

        // Check trader rate limit
        if (traderEmail) {
            const canAdd = this.checkTraderRateLimit(traderEmail);
            if (!canAdd.allowed) {
                return {
                    success: false,
                    error: canAdd.reason,
                    retryAfter: canAdd.retryAfter
                };
            }
        }

        const job = {
            id: this.generateJobId(),
            type: data.type,
            trader: data.trader,
            trade: data.trade,
            priority,
            status: ProductionQueue.STATUS.PENDING,
            attempts: 0,
            maxAttempts: this.config.retryAttempts,
            createdAt: Date.now(),
            startedAt: null,
            completedAt: null,
            result: null,
            error: null,
            retryAt: null
        };

        // Add to maps
        this.jobs.set(job.id, job);

        // Track per-trader jobs
        if (traderEmail) {
            if (!this.traderJobs.has(traderEmail)) {
                this.traderJobs.set(traderEmail, new Set());
            }
            this.traderJobs.get(traderEmail).add(job.id);
        }

        // Add to queue with priority sorting
        this.insertByPriority(job.id, priority);

        this.stats.totalQueued++;
        this.emit('job:added', job);

        // Persist
        this.persistJobs();

        // Trigger processing
        this.processNext();

        return {
            success: true,
            jobId: job.id,
            position: this.getPosition(job.id),
            estimatedWait: this.getEstimatedWait(job.id),
            queueLength: this.pendingQueue.length
        };
    }

    /**
     * Insert job ID into queue sorted by priority
     */
    insertByPriority(jobId, priority) {
        // Find position to insert (after jobs with same or higher priority)
        let insertIndex = this.pendingQueue.length;

        for (let i = 0; i < this.pendingQueue.length; i++) {
            const existingJob = this.jobs.get(this.pendingQueue[i]);
            if (existingJob && existingJob.priority < priority) {
                insertIndex = i;
                break;
            }
        }

        this.pendingQueue.splice(insertIndex, 0, jobId);
    }

    /**
     * Check trader rate limit
     */
    checkTraderRateLimit(email) {
        const stats = this.traderStats.get(email) || {
            lastTrade: 0,
            tradesThisHour: 0,
            hourStart: Date.now()
        };

        const now = Date.now();

        // Reset hourly counter if needed
        if (now - stats.hourStart > 3600000) {
            stats.tradesThisHour = 0;
            stats.hourStart = now;
        }

        // Check cooldown
        if (now - stats.lastTrade < this.config.traderCooldown) {
            const waitTime = this.config.traderCooldown - (now - stats.lastTrade);
            return {
                allowed: false,
                reason: 'Please wait between trades',
                retryAfter: waitTime
            };
        }

        // Check hourly limit
        if (stats.tradesThisHour >= this.config.traderMaxPerHour) {
            const waitTime = stats.hourStart + 3600000 - now;
            return {
                allowed: false,
                reason: 'Hourly trade limit reached',
                retryAfter: waitTime
            };
        }

        // Check concurrent jobs for this trader
        const activeJobs = this.traderJobs.get(email);
        if (activeJobs) {
            let activeCount = 0;
            for (const jobId of activeJobs) {
                const job = this.jobs.get(jobId);
                if (job && (job.status === ProductionQueue.STATUS.PENDING ||
                           job.status === ProductionQueue.STATUS.PROCESSING)) {
                    activeCount++;
                }
            }
            if (activeCount >= this.config.maxPerTrader) {
                return {
                    allowed: false,
                    reason: 'Too many pending trades',
                    retryAfter: 30000
                };
            }
        }

        return { allowed: true };
    }

    /**
     * Update trader stats after trade
     */
    updateTraderStats(email) {
        const stats = this.traderStats.get(email) || {
            lastTrade: 0,
            tradesThisHour: 0,
            hourStart: Date.now()
        };

        const now = Date.now();

        // Reset hourly counter if needed
        if (now - stats.hourStart > 3600000) {
            stats.tradesThisHour = 0;
            stats.hourStart = now;
        }

        stats.lastTrade = now;
        stats.tradesThisHour++;

        this.traderStats.set(email, stats);
    }

    /**
     * Get job by ID
     */
    getJob(jobId) {
        return this.jobs.get(jobId);
    }

    /**
     * Get position in queue
     */
    getPosition(jobId) {
        const index = this.pendingQueue.indexOf(jobId);
        return index === -1 ? null : index + 1;
    }

    /**
     * Estimate wait time based on position and processing speed
     */
    getEstimatedWait(jobId) {
        const position = this.getPosition(jobId);
        if (!position) return 0;

        // Estimate based on average processing time (30s) and concurrency
        const avgProcessTime = 30; // seconds
        const concurrent = this.config.maxConcurrent;

        return Math.ceil((position / concurrent) * avgProcessTime);
    }

    /**
     * Cancel a job
     */
    cancelJob(jobId) {
        const job = this.jobs.get(jobId);

        if (!job) return { success: false, error: 'Job not found' };

        if (job.status === ProductionQueue.STATUS.PROCESSING) {
            return { success: false, error: 'Cannot cancel processing job' };
        }

        if (job.status !== ProductionQueue.STATUS.PENDING) {
            return { success: false, error: 'Job is not pending' };
        }

        job.status = ProductionQueue.STATUS.CANCELLED;
        job.completedAt = Date.now();

        // Remove from pending queue
        const index = this.pendingQueue.indexOf(jobId);
        if (index > -1) {
            this.pendingQueue.splice(index, 1);
        }

        this.emit('job:cancelled', job);
        this.persistJobs();

        return { success: true };
    }

    /**
     * Start queue processor
     */
    start() {
        this.isRunning = true;
        console.log('[ProductionQueue] Started');
        this.emit('queue:started');
        this.processNext();
    }

    /**
     * Stop queue processor
     */
    stop() {
        this.isRunning = false;
        console.log('[ProductionQueue] Stopped');
        this.emit('queue:stopped');
    }

    /**
     * Process next job in queue
     */
    async processNext() {
        if (!this.isRunning) return;
        if (this.processingJobs.size >= this.config.maxConcurrent) return;
        if (this.pendingQueue.length === 0) return;

        // Find next eligible job
        let jobId = null;
        let jobIndex = -1;

        for (let i = 0; i < this.pendingQueue.length; i++) {
            const id = this.pendingQueue[i];
            const job = this.jobs.get(id);

            if (!job) continue;

            // Skip if retry time hasn't passed
            if (job.retryAt && Date.now() < job.retryAt) continue;

            jobId = id;
            jobIndex = i;
            break;
        }

        if (!jobId || jobIndex === -1) return;

        // Remove from pending
        this.pendingQueue.splice(jobIndex, 1);

        const job = this.jobs.get(jobId);
        if (!job) return;

        // Mark as processing
        job.status = ProductionQueue.STATUS.PROCESSING;
        job.startedAt = Date.now();
        job.attempts++;

        this.processingJobs.add(jobId);
        this.emit('job:processing', job);

        console.log(`[ProductionQueue] Processing: ${jobId} (attempt ${job.attempts}/${job.maxAttempts})`);

        try {
            // Execute with timeout
            const result = await this.executeWithTimeout(job);

            // Success
            job.status = ProductionQueue.STATUS.COMPLETED;
            job.result = result;
            job.completedAt = Date.now();

            this.stats.totalCompleted++;

            // Update trader stats
            if (job.trader?.email) {
                this.updateTraderStats(job.trader.email);
            }

            console.log(`[ProductionQueue] Completed: ${jobId}`);
            this.emit('job:completed', job);

        } catch (error) {
            console.error(`[ProductionQueue] Error: ${jobId} - ${error.message}`);

            if (job.attempts < job.maxAttempts) {
                // Retry with exponential backoff
                const delay = this.config.retryDelay * Math.pow(2, job.attempts - 1);
                job.status = ProductionQueue.STATUS.PENDING;
                job.retryAt = Date.now() + delay;
                job.error = error.message;

                // Add back to queue
                this.insertByPriority(jobId, job.priority);

                this.stats.totalRetried++;
                console.log(`[ProductionQueue] Retry scheduled: ${jobId} in ${delay}ms`);
                this.emit('job:retry', job);

            } else {
                // Final failure
                job.status = ProductionQueue.STATUS.FAILED;
                job.error = error.message;
                job.completedAt = Date.now();

                this.stats.totalFailed++;
                console.log(`[ProductionQueue] Failed: ${jobId}`);
                this.emit('job:failed', job);
            }
        }

        this.processingJobs.delete(jobId);
        this.stats.totalProcessed++;

        this.persistJobs();

        // Process next job
        setImmediate(() => this.processNext());
    }

    /**
     * Execute job with timeout
     */
    async executeWithTimeout(job) {
        return new Promise((resolve, reject) => {
            const timeout = setTimeout(() => {
                reject(new Error('Job timeout'));
            }, this.config.jobTimeout);

            this.executeJob(job)
                .then(result => {
                    clearTimeout(timeout);
                    resolve(result);
                })
                .catch(error => {
                    clearTimeout(timeout);
                    reject(error);
                });
        });
    }

    /**
     * Execute job - Override this in subclass or set executor
     */
    async executeJob(job) {
        if (this.executor) {
            switch (job.type) {
                case 'EXECUTE_TRADE':
                    return await this.executor.executeTrade(job.trader, job.trade);
                case 'LOGIN_TEST':
                    return await this.executor.testLogin(job.trader);
                case 'GET_BALANCE':
                    return await this.executor.getBalance(job.trader);
                default:
                    throw new Error(`Unknown job type: ${job.type}`);
            }
        }
        throw new Error('No executor configured');
    }

    /**
     * Set executor instance
     */
    setExecutor(executor) {
        this.executor = executor;
    }

    /**
     * Get comprehensive statistics
     */
    getStats() {
        const uptime = Date.now() - this.stats.startTime;

        return {
            queue: {
                pending: this.pendingQueue.length,
                processing: this.processingJobs.size,
                total: this.jobs.size
            },
            processed: {
                total: this.stats.totalProcessed,
                completed: this.stats.totalCompleted,
                failed: this.stats.totalFailed,
                retried: this.stats.totalRetried
            },
            rates: {
                successRate: this.stats.totalProcessed > 0
                    ? ((this.stats.totalCompleted / this.stats.totalProcessed) * 100).toFixed(2) + '%'
                    : 'N/A',
                avgPerHour: uptime > 0
                    ? Math.round((this.stats.totalProcessed / uptime) * 3600000)
                    : 0
            },
            traders: {
                active: this.traderJobs.size,
                tracked: this.traderStats.size
            },
            config: {
                maxConcurrent: this.config.maxConcurrent,
                maxPerTrader: this.config.maxPerTrader,
                traderMaxPerHour: this.config.traderMaxPerHour
            },
            uptime: Math.floor(uptime / 1000)
        };
    }

    /**
     * Get jobs by status
     */
    getJobsByStatus(status, limit = 100) {
        const jobs = [];
        for (const job of this.jobs.values()) {
            if (job.status === status) {
                jobs.push(job);
                if (jobs.length >= limit) break;
            }
        }
        return jobs;
    }

    /**
     * Get jobs by trader
     */
    getJobsByTrader(email, limit = 50) {
        const jobIds = this.traderJobs.get(email);
        if (!jobIds) return [];

        const jobs = [];
        for (const jobId of jobIds) {
            const job = this.jobs.get(jobId);
            if (job) {
                jobs.push(job);
                if (jobs.length >= limit) break;
            }
        }
        return jobs.sort((a, b) => b.createdAt - a.createdAt);
    }

    /**
     * Cleanup old completed/failed jobs
     */
    cleanup() {
        const maxAge = 3600000; // 1 hour
        const cutoff = Date.now() - maxAge;
        let cleaned = 0;

        for (const [id, job] of this.jobs) {
            if ((job.status === ProductionQueue.STATUS.COMPLETED ||
                 job.status === ProductionQueue.STATUS.FAILED ||
                 job.status === ProductionQueue.STATUS.CANCELLED) &&
                job.completedAt && job.completedAt < cutoff) {

                this.jobs.delete(id);

                // Remove from trader jobs
                if (job.trader?.email) {
                    const traderJobs = this.traderJobs.get(job.trader.email);
                    if (traderJobs) {
                        traderJobs.delete(id);
                    }
                }

                cleaned++;
            }
        }

        if (cleaned > 0) {
            console.log(`[ProductionQueue] Cleaned ${cleaned} old jobs`);
            this.persistJobs();
        }

        return cleaned;
    }

    /**
     * Persist jobs to disk
     */
    persistJobs() {
        try {
            const data = {
                jobs: Array.from(this.jobs.entries()),
                pendingQueue: this.pendingQueue,
                stats: this.stats,
                savedAt: Date.now()
            };
            fs.writeFileSync(this.config.persistPath, JSON.stringify(data, null, 2));
        } catch (error) {
            console.error('[ProductionQueue] Persist error:', error.message);
        }
    }

    /**
     * Load jobs from disk
     */
    loadJobs() {
        try {
            if (fs.existsSync(this.config.persistPath)) {
                const data = JSON.parse(fs.readFileSync(this.config.persistPath, 'utf8'));

                // Restore jobs
                if (data.jobs) {
                    this.jobs = new Map(data.jobs);

                    // Rebuild trader jobs map
                    for (const [id, job] of this.jobs) {
                        if (job.trader?.email) {
                            if (!this.traderJobs.has(job.trader.email)) {
                                this.traderJobs.set(job.trader.email, new Set());
                            }
                            this.traderJobs.get(job.trader.email).add(id);
                        }

                        // Re-queue pending jobs that were interrupted
                        if (job.status === ProductionQueue.STATUS.PROCESSING) {
                            job.status = ProductionQueue.STATUS.PENDING;
                            job.retryAt = Date.now() + 5000; // Retry in 5s
                        }
                    }
                }

                // Restore pending queue
                if (data.pendingQueue) {
                    this.pendingQueue = data.pendingQueue.filter(id => {
                        const job = this.jobs.get(id);
                        return job && job.status === ProductionQueue.STATUS.PENDING;
                    });
                }

                // Restore stats
                if (data.stats) {
                    this.stats = { ...this.stats, ...data.stats };
                }

                console.log(`[ProductionQueue] Loaded ${this.jobs.size} jobs, ${this.pendingQueue.length} pending`);
            }
        } catch (error) {
            console.error('[ProductionQueue] Load error:', error.message);
        }
    }

    /**
     * Shutdown gracefully
     */
    async shutdown() {
        console.log('[ProductionQueue] Shutting down...');
        this.stop();

        // Wait for processing jobs to complete (max 30s)
        const maxWait = 30000;
        const startWait = Date.now();

        while (this.processingJobs.size > 0 && Date.now() - startWait < maxWait) {
            await new Promise(resolve => setTimeout(resolve, 1000));
        }

        // Clear cleanup timer
        if (this.cleanupTimer) {
            clearInterval(this.cleanupTimer);
        }

        // Final persist
        this.persistJobs();

        console.log('[ProductionQueue] Shutdown complete');
    }
}

module.exports = ProductionQueue;
