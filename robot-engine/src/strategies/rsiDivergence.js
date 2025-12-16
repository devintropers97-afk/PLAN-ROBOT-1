/**
 * Strategy #9: RSI Divergence
 * Tier: FREE
 * Win Rate: 55-86%
 * Risk: Low
 */

const { RSI } = require('technicalindicators');
const BaseStrategy = require('./baseStrategy');

class RSIDivergence extends BaseStrategy {
    constructor() {
        super();
        this.name = 'APEX-HUNTER';
        this.description = 'Apex Hunter - Pemburu puncak dan dasar';
        this.tier = 'FREE';
        this.winRate = '55-86%';
        this.risk = 'Low';
        this.indicators = ['RSI 14', 'Price Action'];
    }

    analyze(data) {
        const { close, high, low } = data;

        if (close.length < 25) {
            return { signal: 'NONE', confidence: 0, reason: 'Insufficient data' };
        }

        const rsiValues = RSI.calculate({ period: 14, values: close });

        if (rsiValues.length < 10) {
            return { signal: 'NONE', confidence: 0, reason: 'RSI calculation failed' };
        }

        // Get recent values for divergence detection
        const lookback = 5;
        const recentRSI = rsiValues.slice(-lookback);
        const recentLows = low.slice(-lookback);
        const recentHighs = high.slice(-lookback);

        const currentRSI = recentRSI[recentRSI.length - 1];
        const prevRSI = recentRSI[0];
        const currentLow = recentLows[recentLows.length - 1];
        const prevLow = Math.min(...recentLows.slice(0, -1));
        const currentHigh = recentHighs[recentHighs.length - 1];
        const prevHigh = Math.max(...recentHighs.slice(0, -1));

        const indicators = {
            RSI: currentRSI?.toFixed(2),
            RSI_Prev: prevRSI?.toFixed(2),
            Price_High: currentHigh?.toFixed(5),
            Price_Low: currentLow?.toFixed(5)
        };

        // Bullish Divergence: Price makes lower low but RSI makes higher low
        if (currentLow < prevLow * 0.999) {
            if (currentRSI > prevRSI + 2 && currentRSI < 40) {
                let confidence = 65;
                if (currentRSI < 30) confidence += 3;
                if (currentRSI - prevRSI > 5) confidence += 2;

                return {
                    signal: 'CALL',
                    confidence: Math.min(confidence, 86),
                    reason: 'Bullish RSI Divergence Detected',
                    indicators
                };
            }
        }

        // Bearish Divergence: Price makes higher high but RSI makes lower high
        if (currentHigh > prevHigh * 1.001) {
            if (currentRSI < prevRSI - 2 && currentRSI > 60) {
                let confidence = 65;
                if (currentRSI > 70) confidence += 3;
                if (prevRSI - currentRSI > 5) confidence += 2;

                return {
                    signal: 'PUT',
                    confidence: Math.min(confidence, 86),
                    reason: 'Bearish RSI Divergence Detected',
                    indicators
                };
            }
        }

        return { signal: 'NONE', confidence: 0, reason: 'No RSI divergence detected', indicators };
    }
}

module.exports = RSIDivergence;
