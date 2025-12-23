/**
 * Rate Limiter - Prevent OlympTrade from blocking due to too many requests
 */

class RateLimiter {
    constructor(options = {}) {
        this.maxRequestsPerMinute = options.maxRequestsPerMinute || 10;
        this.maxLoginAttemptsPerHour = options.maxLoginAttemptsPerHour || 3;
        this.minDelayBetweenActions = options.minDelayBetweenActions || 2000; // 2 seconds
        this.maxDelayBetweenActions = options.maxDelayBetweenActions || 5000; // 5 seconds

        this.requests = [];
        this.loginAttempts = [];
        this.lastAction = 0;
    }

    /**
     * Get random delay between min and max
     */
    getRandomDelay() {
        return Math.floor(
            Math.random() * (this.maxDelayBetweenActions - this.minDelayBetweenActions) +
            this.minDelayBetweenActions
        );
    }

    /**
     * Wait for rate limit
     */
    async waitForRateLimit() {
        const now = Date.now();
        const timeSinceLastAction = now - this.lastAction;

        if (timeSinceLastAction < this.minDelayBetweenActions) {
            const waitTime = this.minDelayBetweenActions - timeSinceLastAction + this.getRandomDelay();
            await this.sleep(waitTime);
        }

        this.lastAction = Date.now();
    }

    /**
     * Check if we can make a request
     */
    canMakeRequest() {
        const oneMinuteAgo = Date.now() - 60000;
        this.requests = this.requests.filter(time => time > oneMinuteAgo);
        return this.requests.length < this.maxRequestsPerMinute;
    }

    /**
     * Check if we can attempt login
     */
    canAttemptLogin() {
        const oneHourAgo = Date.now() - 3600000;
        this.loginAttempts = this.loginAttempts.filter(time => time > oneHourAgo);
        return this.loginAttempts.length < this.maxLoginAttemptsPerHour;
    }

    /**
     * Record a request
     */
    recordRequest() {
        this.requests.push(Date.now());
    }

    /**
     * Record a login attempt
     */
    recordLoginAttempt() {
        this.loginAttempts.push(Date.now());
    }

    /**
     * Get wait time until next login attempt allowed
     */
    getLoginWaitTime() {
        if (this.canAttemptLogin()) return 0;

        const oneHourAgo = Date.now() - 3600000;
        const oldestAttempt = Math.min(...this.loginAttempts);
        return Math.max(0, oldestAttempt - oneHourAgo);
    }

    /**
     * Sleep utility
     */
    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    /**
     * Human-like delay for typing
     */
    getTypingDelay() {
        return Math.floor(Math.random() * 100) + 50; // 50-150ms
    }

    /**
     * Human-like delay for clicking
     */
    getClickDelay() {
        return Math.floor(Math.random() * 500) + 200; // 200-700ms
    }
}

module.exports = RateLimiter;
