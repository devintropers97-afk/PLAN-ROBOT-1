/**
 * Trade Queue - Manages async trade requests
 *
 * Features:
 * - FIFO queue processing
 * - Concurrent job limiting
 * - Job status tracking
 * - Automatic retries
 */

const crypto = require('crypto');
const path = require('path');

// Import robot executor
const TradeExecutor = require('./tradeExecutor');

class TradeQueue {
    constructor(options = {}) {
        this.maxConcurrent = options.maxConcurrent || 2;
        this.retryAttempts = options.retryAttempts || 2;
        this.jobTimeout = options.jobTimeout || 120000; // 2 minutes

        this.jobs = new Map();
        this.pendingQueue = [];
        this.processingCount = 0;
        this.isProcessing = false;

        // Stats
        this.stats = {
            completed: 0,
            failed: 0,
            totalProcessed: 0
        };

        // Executor instance
        this.executor = new TradeExecutor();
    }

    /**
     * Generate unique job ID
     */
    generateJobId() {
        return `job_${Date.now()}_${crypto.randomBytes(4).toString('hex')}`;
    }

    /**
     * Add job to queue
     */
    async addJob(data) {
        const job = {
            id: this.generateJobId(),
            type: data.type,
            trader: data.trader,
            trade: data.trade,
            status: 'pending',
            attempts: 0,
            createdAt: new Date().toISOString(),
            result: null,
            error: null
        };

        this.jobs.set(job.id, job);
        this.pendingQueue.push(job.id);

        console.log(`[Queue] Job added: ${job.id} (${job.type})`);

        // Trigger processing
        this.processNext();

        return job;
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
     * Get estimated wait time (seconds)
     */
    getEstimatedWait(jobId) {
        const position = this.getPosition(jobId);
        if (!position) return 0;

        // Estimate 30 seconds per job
        return position * 30;
    }

    /**
     * Cancel pending job
     */
    cancelJob(jobId) {
        const job = this.jobs.get(jobId);

        if (!job || job.status !== 'pending') {
            return false;
        }

        job.status = 'cancelled';
        job.completedAt = new Date().toISOString();

        // Remove from pending queue
        const index = this.pendingQueue.indexOf(jobId);
        if (index > -1) {
            this.pendingQueue.splice(index, 1);
        }

        return true;
    }

    /**
     * Start queue processor
     */
    startProcessor() {
        this.isProcessing = true;
        console.log('[Queue] Processor started');
    }

    /**
     * Stop queue processor
     */
    stopProcessor() {
        this.isProcessing = false;
        console.log('[Queue] Processor stopped');
    }

    /**
     * Process next job in queue
     */
    async processNext() {
        if (!this.isProcessing) return;
        if (this.processingCount >= this.maxConcurrent) return;
        if (this.pendingQueue.length === 0) return;

        const jobId = this.pendingQueue.shift();
        const job = this.jobs.get(jobId);

        if (!job || job.status !== 'pending') {
            // Skip invalid jobs, try next
            this.processNext();
            return;
        }

        this.processingCount++;
        job.status = 'processing';
        job.startedAt = new Date().toISOString();

        console.log(`[Queue] Processing job: ${jobId}`);

        try {
            const result = await this.executeJob(job);

            job.status = 'completed';
            job.result = result;
            job.completedAt = new Date().toISOString();
            this.stats.completed++;

            console.log(`[Queue] Job completed: ${jobId}`);

        } catch (error) {
            job.attempts++;

            if (job.attempts < this.retryAttempts) {
                // Retry
                console.log(`[Queue] Job failed, retrying: ${jobId} (attempt ${job.attempts})`);
                job.status = 'pending';
                this.pendingQueue.unshift(jobId); // Add back to front
            } else {
                // Final failure
                job.status = 'failed';
                job.error = error.message;
                job.completedAt = new Date().toISOString();
                this.stats.failed++;
                console.log(`[Queue] Job failed: ${jobId} - ${error.message}`);
            }
        }

        this.processingCount--;
        this.stats.totalProcessed++;

        // Process next job
        setImmediate(() => this.processNext());
    }

    /**
     * Execute a job based on type
     */
    async executeJob(job) {
        const timeoutPromise = new Promise((_, reject) => {
            setTimeout(() => reject(new Error('Job timeout')), this.jobTimeout);
        });

        const executionPromise = (async () => {
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
        })();

        return Promise.race([executionPromise, timeoutPromise]);
    }

    /**
     * Get queue stats
     */
    getPendingCount() {
        return this.pendingQueue.length;
    }

    getProcessingCount() {
        return this.processingCount;
    }

    getCompletedCount() {
        return this.stats.completed;
    }

    getFailedCount() {
        return this.stats.failed;
    }

    /**
     * Get all jobs
     */
    getAllJobs() {
        return Array.from(this.jobs.values());
    }

    /**
     * Clear old completed jobs (cleanup)
     */
    cleanupOldJobs(maxAge = 3600000) { // 1 hour
        const cutoff = Date.now() - maxAge;
        let cleaned = 0;

        for (const [id, job] of this.jobs) {
            if (job.status === 'completed' || job.status === 'failed') {
                const completedTime = new Date(job.completedAt).getTime();
                if (completedTime < cutoff) {
                    this.jobs.delete(id);
                    cleaned++;
                }
            }
        }

        return cleaned;
    }
}

module.exports = TradeQueue;
