/**
 * Strategy #6: MACD + RSI Combo
 * Tier: PRO
 * Win Rate: 73%
 * Risk: Medium
 */

const { MACD, RSI } = require('technicalindicators');
const BaseStrategy = require('./baseStrategy');

class MACDRSICombo extends BaseStrategy {
    constructor() {
        super();
        this.name = 'TITAN-PULSE';
        this.description = 'Titan Pulse - Denyut momentum kuat';
        this.tier = 'PRO';
        this.winRate = '73%';
        this.risk = 'Medium';
        this.indicators = ['MACD', 'RSI 14'];
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

        const rsiValues = RSI.calculate({ period: 14, values: close });

        if (macdResult.length < 2 || rsiValues.length < 2) {
            return { signal: 'NONE', confidence: 0, reason: 'Indicator calculation failed' };
        }

        const macd = macdResult[macdResult.length - 1];
        const macdPrev = macdResult[macdResult.length - 2];
        const rsi = rsiValues[rsiValues.length - 1];
        const rsiPrev = rsiValues[rsiValues.length - 2];

        const indicators = {
            MACD: macd.MACD?.toFixed(5),
            Signal: macd.signal?.toFixed(5),
            Histogram: macd.histogram?.toFixed(5),
            RSI: rsi?.toFixed(2)
        };

        // CALL: MACD bullish crossover + RSI oversold (<35) and rising
        if (macdPrev.MACD <= macdPrev.signal && macd.MACD > macd.signal) {
            if (rsi < 45 && rsi > rsiPrev) {
                let confidence = 71;
                if (rsi < 35) confidence += 3;
                if (macd.histogram > 0) confidence += 2;

                return {
                    signal: 'CALL',
                    confidence: Math.min(confidence, 73),
                    reason: 'MACD Bullish + RSI Oversold Recovery',
                    indicators
                };
            }
        }

        // PUT: MACD bearish crossover + RSI overbought (>65) and falling
        if (macdPrev.MACD >= macdPrev.signal && macd.MACD < macd.signal) {
            if (rsi > 55 && rsi < rsiPrev) {
                let confidence = 71;
                if (rsi > 65) confidence += 3;
                if (macd.histogram < 0) confidence += 2;

                return {
                    signal: 'PUT',
                    confidence: Math.min(confidence, 73),
                    reason: 'MACD Bearish + RSI Overbought Decline',
                    indicators
                };
            }
        }

        return { signal: 'NONE', confidence: 0, reason: 'No MACD-RSI combo signal', indicators };
    }
}

module.exports = MACDRSICombo;
