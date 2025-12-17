/**
 * ZYN Trade Robot - Signal Generator Module
 * Generates trading signals based on 10 strategies
 */

const logger = require('../utils/logger');

// Import all strategy modules
const TripleRSI = require('../strategies/tripleRSI');
const StructuredMACD = require('../strategies/structuredMACD');
const WilliamsR = require('../strategies/williamsR');
const ConnorsRSI2 = require('../strategies/connorsRSI2');
const MACDBollinger = require('../strategies/macdBollinger');
const MACDRSICombo = require('../strategies/macdRSICombo');
const StochRSIMACD = require('../strategies/stochRSIMACD');
const BBRSIStandard = require('../strategies/bbRSIStandard');
const RSIDivergence = require('../strategies/rsiDivergence');
const MultiIndicator = require('../strategies/multiIndicator');

class SignalGenerator {
    constructor(priceDataFeed) {
        this.priceData = priceDataFeed;

        // Initialize all 10 strategies
        this.strategies = {
            '1': new TripleRSI(),
            '2': new StructuredMACD(),
            '3': new WilliamsR(),
            '4': new ConnorsRSI2(),
            '5': new MACDBollinger(),
            '6': new MACDRSICombo(),
            '7': new StochRSIMACD(),
            '8': new BBRSIStandard(),
            '9': new RSIDivergence(),
            '10': new MultiIndicator()
        };

        logger.info(`Signal Generator initialized with ${Object.keys(this.strategies).length} strategies`);
    }

    /**
     * Generate signal for specific strategy
     * @param {string} strategyId - Strategy ID (1-10)
     * @param {Array} candles - Array of candle data
     * @returns {Object} Signal object
     */
    async generateSignal(strategyId, candles) {
        const strategy = this.strategies[strategyId];

        if (!strategy) {
            logger.warn(`Unknown strategy ID: ${strategyId}`);
            return { execute: false, reason: 'Unknown strategy' };
        }

        try {
            // Get close prices for indicators
            const closePrices = candles.map(c => c.close);
            const highPrices = candles.map(c => c.high);
            const lowPrices = candles.map(c => c.low);

            // Analyze with strategy
            const result = strategy.analyze({
                candles,
                close: closePrices,
                high: highPrices,
                low: lowPrices
            });

            if (result.signal && result.signal !== 'NONE') {
                logger.info(`Strategy #${strategyId} (${strategy.name}): ${result.signal} signal, confidence: ${result.confidence}%`);

                return {
                    execute: result.confidence >= 70, // Minimum confidence threshold
                    direction: result.signal.toLowerCase(), // 'call' or 'put'
                    strategyId: strategyId,
                    strategyName: strategy.name,
                    confidence: result.confidence,
                    reason: result.reason,
                    indicators: result.indicators
                };
            }

            return {
                execute: false,
                strategyId: strategyId,
                strategyName: strategy.name,
                reason: 'No valid signal'
            };

        } catch (error) {
            logger.error(`Error generating signal for strategy ${strategyId}:`, error);
            return { execute: false, reason: error.message };
        }
    }

    /**
     * Generate signals for multiple strategies
     * Returns the best signal based on confidence
     */
    async generateBestSignal(strategyIds, candles) {
        let bestSignal = null;
        let highestConfidence = 0;

        for (const strategyId of strategyIds) {
            const signal = await this.generateSignal(strategyId, candles);

            if (signal.execute && signal.confidence > highestConfidence) {
                highestConfidence = signal.confidence;
                bestSignal = signal;
            }
        }

        return bestSignal || { execute: false, reason: 'No valid signals' };
    }

    /**
     * Get strategy info
     */
    getStrategyInfo(strategyId) {
        const strategy = this.strategies[strategyId];
        if (!strategy) return null;

        return {
            id: strategyId,
            name: strategy.name,
            description: strategy.description,
            winRate: strategy.winRate,
            risk: strategy.risk,
            indicators: strategy.indicators
        };
    }
}

module.exports = SignalGenerator;
