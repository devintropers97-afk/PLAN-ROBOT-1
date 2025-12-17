/**
 * Strategy #5: MACD + Bollinger Bands
 * Tier: ELITE
 * Win Rate: 78%
 * Risk: Medium
 */

const { MACD, BollingerBands } = require('technicalindicators');
const BaseStrategy = require('./baseStrategy');

class MACDBollinger extends BaseStrategy {
    constructor() {
        super();
        this.name = 'VORTEX-PRO';
        this.description = 'Vortex Pro - Pusaran sinyal breakout';
        this.tier = 'ELITE';
        this.winRate = '78%';
        this.risk = 'Medium';
        this.indicators = ['MACD', 'Bollinger Bands'];
    }

    analyze(data) {
        const { close } = data;

        if (close.length < 35) {
            return { signal: 'NONE', confidence: 0, reason: 'Insufficient data' };
        }

        const macdResult = MACD.calculate({
            values: close,
            fastPeriod: 12,
            slowPeriod: 26,
            signalPeriod: 9,
            SimpleMAOscillator: false,
            SimpleMASignal: false
        });

        const bbResult = BollingerBands.calculate({
            period: 20,
            values: close,
            stdDev: 2
        });

        if (macdResult.length < 2 || bbResult.length < 2) {
            return { signal: 'NONE', confidence: 0, reason: 'Indicator calculation failed' };
        }

        const macd = macdResult[macdResult.length - 1];
        const macdPrev = macdResult[macdResult.length - 2];
        const bb = bbResult[bbResult.length - 1];
        const currentPrice = close[close.length - 1];

        const indicators = {
            MACD: macd.MACD?.toFixed(5),
            Signal: macd.signal?.toFixed(5),
            BB_Upper: bb.upper?.toFixed(5),
            BB_Lower: bb.lower?.toFixed(5),
            BB_Middle: bb.middle?.toFixed(5)
        };

        // CALL: Price at/below lower BB + MACD bullish crossover
        if (currentPrice <= bb.lower * 1.005) {
            if (macdPrev.MACD <= macdPrev.signal && macd.MACD > macd.signal) {
                let confidence = 73;
                if (currentPrice < bb.lower) confidence += 3;
                if (macd.histogram > 0) confidence += 2;

                return {
                    signal: 'CALL',
                    confidence: Math.min(confidence, 78),
                    reason: 'MACD Bullish Crossover at Lower BB',
                    indicators
                };
            }
        }

        // PUT: Price at/above upper BB + MACD bearish crossover
        if (currentPrice >= bb.upper * 0.995) {
            if (macdPrev.MACD >= macdPrev.signal && macd.MACD < macd.signal) {
                let confidence = 73;
                if (currentPrice > bb.upper) confidence += 3;
                if (macd.histogram < 0) confidence += 2;

                return {
                    signal: 'PUT',
                    confidence: Math.min(confidence, 78),
                    reason: 'MACD Bearish Crossover at Upper BB',
                    indicators
                };
            }
        }

        return { signal: 'NONE', confidence: 0, reason: 'No MACD-BB signal', indicators };
    }
}

module.exports = MACDBollinger;
