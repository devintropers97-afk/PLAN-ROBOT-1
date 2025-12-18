/**
 * Base Strategy Class
 * All strategies extend this class
 */

class BaseStrategy {
    constructor() {
        this.name = 'Base Strategy';
        this.description = '';
        this.tier = 'FREE';
        this.winRate = '0%';
        this.risk = 'Medium';
        this.indicators = [];
    }

    /**
     * Analyze market data and generate signal
     * Must be overridden by child classes
     * @param {Object} data - { candles, close, high, low }
     * @returns {Object} - { signal, confidence, reason, indicators }
     */
    analyze(data) {
        throw new Error('analyze() must be implemented by child class');
    }
}

module.exports = BaseStrategy;
