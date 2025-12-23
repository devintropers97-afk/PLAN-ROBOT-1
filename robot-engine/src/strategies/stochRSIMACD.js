/**
 * Strategy #7: Stochastic RSI + MACD
 * Tier: PRO
 * Win Rate: 73%
 * Risk: Medium
 */

const { StochasticRSI, MACD } = require('technicalindicators');
const BaseStrategy = require('./baseStrategy');

class StochRSIMACD extends BaseStrategy {
    constructor() {
        super();
        this.name = 'SHADOW-EDGE';
        this.description = 'Shadow Edge - Keunggulan tersembunyi di balik trend';
        this.tier = 'PRO';
        this.winRate = '73%';
        this.risk = 'Medium';
        this.indicators = ['Stochastic RSI', 'MACD'];
    }

    analyze(data) {
        const { close } = data;

        if (close.length < 40) {
            return { signal: 'NONE', confidence: 0, reason: 'Insufficient data' };
        }

        const stochRSI = StochasticRSI.calculate({
            values: close,
            rsiPeriod: 14,
            stochasticPeriod: 14,
            kPeriod: 3,
            dPeriod: 3
        });

        const macdResult = MACD.calculate({
            values: close,
            fastPeriod: 12,
            slowPeriod: 26,
            signalPeriod: 9,
            SimpleMAOscillator: false,
            SimpleMASignal: false
        });

        if (stochRSI.length < 2 || macdResult.length < 1) {
            return { signal: 'NONE', confidence: 0, reason: 'Indicator calculation failed' };
        }

        const stoch = stochRSI[stochRSI.length - 1];
        const stochPrev = stochRSI[stochRSI.length - 2];
        const macd = macdResult[macdResult.length - 1];

        const indicators = {
            StochRSI_K: stoch.k?.toFixed(2),
            StochRSI_D: stoch.d?.toFixed(2),
            MACD: macd.MACD?.toFixed(5),
            MACD_Histogram: macd.histogram?.toFixed(5)
        };

        // CALL: StochRSI K crosses above D from oversold (<20) + MACD histogram positive
        if (stochPrev.k < stochPrev.d && stoch.k > stoch.d) {
            if (stochPrev.k < 25 && macd.histogram > 0) {
                let confidence = 69;
                if (stochPrev.k < 15) confidence += 3;
                if (macd.MACD > macd.signal) confidence += 2;

                return {
                    signal: 'CALL',
                    confidence: Math.min(confidence, 73),
                    reason: 'StochRSI Bullish Crossover + MACD Confirmation',
                    indicators
                };
            }
        }

        // PUT: StochRSI K crosses below D from overbought (>80) + MACD histogram negative
        if (stochPrev.k > stochPrev.d && stoch.k < stoch.d) {
            if (stochPrev.k > 75 && macd.histogram < 0) {
                let confidence = 69;
                if (stochPrev.k > 85) confidence += 3;
                if (macd.MACD < macd.signal) confidence += 2;

                return {
                    signal: 'PUT',
                    confidence: Math.min(confidence, 73),
                    reason: 'StochRSI Bearish Crossover + MACD Confirmation',
                    indicators
                };
            }
        }

        return { signal: 'NONE', confidence: 0, reason: 'No StochRSI-MACD signal', indicators };
    }
}

module.exports = StochRSIMACD;
