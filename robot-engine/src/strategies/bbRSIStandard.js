/**
 * Strategy #8: BB + RSI Standard
 * Tier: FREE
 * Win Rate: 60-78%
 * Risk: Low
 */

const { BollingerBands, RSI } = require('technicalindicators');
const BaseStrategy = require('./baseStrategy');

class BBRSIStandard extends BaseStrategy {
    constructor() {
        super();
        this.name = 'BLITZ-SIGNAL';
        this.description = 'Blitz Signal - Sinyal cepat dan akurat';
        this.tier = 'FREE';
        this.winRate = '60-78%';
        this.risk = 'Low';
        this.indicators = ['Bollinger Bands', 'RSI 14'];
    }

    analyze(data) {
        const { close } = data;

        if (close.length < 25) {
            return { signal: 'NONE', confidence: 0, reason: 'Insufficient data' };
        }

        const bbResult = BollingerBands.calculate({
            period: 20,
            values: close,
            stdDev: 2
        });

        const rsiValues = RSI.calculate({ period: 14, values: close });

        if (bbResult.length < 1 || rsiValues.length < 2) {
            return { signal: 'NONE', confidence: 0, reason: 'Indicator calculation failed' };
        }

        const bb = bbResult[bbResult.length - 1];
        const rsi = rsiValues[rsiValues.length - 1];
        const rsiPrev = rsiValues[rsiValues.length - 2];
        const currentPrice = close[close.length - 1];

        const indicators = {
            BB_Upper: bb.upper?.toFixed(5),
            BB_Lower: bb.lower?.toFixed(5),
            BB_Middle: bb.middle?.toFixed(5),
            RSI: rsi?.toFixed(2),
            Price: currentPrice?.toFixed(5)
        };

        // CALL: Price touches lower BB + RSI oversold (<30) and rising
        if (currentPrice <= bb.lower * 1.002) {
            if (rsi < 35 && rsi > rsiPrev) {
                let confidence = 67;
                if (rsi < 30) confidence += 3;
                if (currentPrice < bb.lower) confidence += 2;

                return {
                    signal: 'CALL',
                    confidence: Math.min(confidence, 78),
                    reason: 'Lower BB Touch + RSI Oversold Recovery',
                    indicators
                };
            }
        }

        // PUT: Price touches upper BB + RSI overbought (>70) and falling
        if (currentPrice >= bb.upper * 0.998) {
            if (rsi > 65 && rsi < rsiPrev) {
                let confidence = 67;
                if (rsi > 70) confidence += 3;
                if (currentPrice > bb.upper) confidence += 2;

                return {
                    signal: 'PUT',
                    confidence: Math.min(confidence, 78),
                    reason: 'Upper BB Touch + RSI Overbought Decline',
                    indicators
                };
            }
        }

        return { signal: 'NONE', confidence: 0, reason: 'No BB-RSI signal', indicators };
    }
}

module.exports = BBRSIStandard;
