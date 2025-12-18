/**
 * Strategy #4: Connors RSI-2
 * Tier: ELITE
 * Win Rate: 75-83%
 * Risk: Medium
 */

const { RSI, SMA } = require('technicalindicators');
const BaseStrategy = require('./baseStrategy');

class ConnorsRSI2 extends BaseStrategy {
    constructor() {
        super();
        this.name = 'PHOENIX-X1';
        this.description = 'Phoenix X1 - Bangkit dari zona ekstrem';
        this.tier = 'ELITE';
        this.winRate = '75-83%';
        this.risk = 'Medium';
        this.indicators = ['RSI 2', 'SMA 200'];
    }

    analyze(data) {
        const { close } = data;

        if (close.length < 205) {
            return { signal: 'NONE', confidence: 0, reason: 'Insufficient data' };
        }

        // RSI with period 2 (very fast)
        const rsi2Values = RSI.calculate({ period: 2, values: close });
        // 200 SMA for trend filter
        const sma200Values = SMA.calculate({ period: 200, values: close });

        if (rsi2Values.length < 2 || sma200Values.length < 1) {
            return { signal: 'NONE', confidence: 0, reason: 'Indicator calculation failed' };
        }

        const rsi2 = rsi2Values[rsi2Values.length - 1];
        const rsi2Prev = rsi2Values[rsi2Values.length - 2];
        const sma200 = sma200Values[sma200Values.length - 1];
        const currentPrice = close[close.length - 1];

        const indicators = {
            RSI_2: rsi2?.toFixed(2),
            SMA_200: sma200?.toFixed(5),
            Price: currentPrice?.toFixed(5)
        };

        // CALL: RSI2 < 10 (extremely oversold) + Price above SMA200 (uptrend)
        if (rsi2 < 10 && currentPrice > sma200) {
            if (rsi2 > rsi2Prev) { // RSI starting to recover
                let confidence = 75;
                if (rsi2 < 5) confidence += 5;
                if (currentPrice > sma200 * 1.005) confidence += 3;

                return {
                    signal: 'CALL',
                    confidence: Math.min(confidence, 83),
                    reason: 'RSI-2 Extreme Oversold in Uptrend',
                    indicators
                };
            }
        }

        // PUT: RSI2 > 90 (extremely overbought) + Price below SMA200 (downtrend)
        if (rsi2 > 90 && currentPrice < sma200) {
            if (rsi2 < rsi2Prev) {
                let confidence = 75;
                if (rsi2 > 95) confidence += 5;
                if (currentPrice < sma200 * 0.995) confidence += 3;

                return {
                    signal: 'PUT',
                    confidence: Math.min(confidence, 83),
                    reason: 'RSI-2 Extreme Overbought in Downtrend',
                    indicators
                };
            }
        }

        return { signal: 'NONE', confidence: 0, reason: 'No RSI-2 signal', indicators };
    }
}

module.exports = ConnorsRSI2;
