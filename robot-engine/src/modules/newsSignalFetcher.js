/**
 * =============================================
 * ZYN Trade Robot - News Signal Fetcher
 * =============================================
 *
 * Fetches economic calendar news and generates trading signals
 * based on economic events (Non-Farm Payroll, CPI, GDP, etc.)
 *
 * Signal Sources:
 * 1. ForexFactory.com Economic Calendar (Free - Scraping)
 * 2. Investing.com Calendar API (Free - Limited)
 * 3. TradingEconomics API (Paid - Premium)
 *
 * This is a PREMIUM feature with additional monthly cost.
 */

require('dotenv').config();
const axios = require('axios');

const LOG_PREFIX = '[NewsSignal]';

// Configuration
const CONFIG = {
    // API Options
    apis: {
        tradingEconomics: {
            baseUrl: 'https://api.tradingeconomics.com',
            apiKey: process.env.TRADING_ECONOMICS_API_KEY || '',
            enabled: !!process.env.TRADING_ECONOMICS_API_KEY
        },
        investingCom: {
            baseUrl: 'https://www.investing.com/economic-calendar/Service/getCalendarFilteredData',
            enabled: true
        }
    },
    // High-impact events that affect trading
    highImpactEvents: [
        'Non-Farm Payrolls',
        'Unemployment Rate',
        'CPI',
        'Core CPI',
        'GDP',
        'Interest Rate Decision',
        'Fed Funds Rate',
        'ECB Interest Rate',
        'Retail Sales',
        'Manufacturing PMI',
        'Services PMI',
        'Trade Balance',
        'Consumer Confidence'
    ],
    // Currency pairs mapping
    currencyPairs: {
        'USD': ['EUR/USD', 'GBP/USD', 'USD/JPY'],
        'EUR': ['EUR/USD', 'EUR/GBP', 'EUR/JPY'],
        'GBP': ['GBP/USD', 'EUR/GBP'],
        'JPY': ['USD/JPY', 'EUR/JPY'],
        'AUD': ['AUD/USD'],
        'CAD': ['USD/CAD'],
        'CHF': ['USD/CHF'],
        'NZD': ['NZD/USD']
    }
};

class NewsSignalFetcher {
    constructor() {
        this.cache = new Map();
        this.cacheExpiry = 5 * 60 * 1000; // 5 minutes
    }

    /**
     * Log message
     */
    log(message, level = 'info') {
        const timestamp = new Date().toISOString();
        console.log(`${LOG_PREFIX} ${timestamp} [${level.toUpperCase()}] ${message}`);
    }

    /**
     * Fetch upcoming economic events
     */
    async fetchUpcomingEvents(hours = 24) {
        const cacheKey = `events_${hours}`;
        const cached = this.cache.get(cacheKey);

        if (cached && Date.now() - cached.timestamp < this.cacheExpiry) {
            return cached.data;
        }

        try {
            let events = [];

            // Try TradingEconomics API first (if configured)
            if (CONFIG.apis.tradingEconomics.enabled) {
                events = await this.fetchFromTradingEconomics(hours);
            }

            // Fallback to free sources
            if (events.length === 0) {
                events = await this.fetchFromFreeSource(hours);
            }

            // Cache results
            this.cache.set(cacheKey, { data: events, timestamp: Date.now() });

            return events;

        } catch (error) {
            this.log(`Error fetching events: ${error.message}`, 'error');
            return [];
        }
    }

    /**
     * Fetch from TradingEconomics API (Premium)
     */
    async fetchFromTradingEconomics(hours) {
        const { baseUrl, apiKey } = CONFIG.apis.tradingEconomics;

        if (!apiKey) {
            this.log('TradingEconomics API key not configured', 'warn');
            return [];
        }

        try {
            const now = new Date();
            const endTime = new Date(now.getTime() + hours * 60 * 60 * 1000);

            const response = await axios.get(`${baseUrl}/calendar`, {
                params: {
                    c: apiKey,
                    d1: now.toISOString().split('T')[0],
                    d2: endTime.toISOString().split('T')[0],
                    importance: 3 // High impact only
                },
                timeout: 10000
            });

            return this.parseEvents(response.data, 'tradingEconomics');

        } catch (error) {
            this.log(`TradingEconomics API error: ${error.message}`, 'error');
            return [];
        }
    }

    /**
     * Fetch from free sources (scraping/public APIs)
     * Note: This is a simulation since actual scraping requires specific implementation
     */
    async fetchFromFreeSource(hours) {
        // In production, you would implement:
        // 1. ForexFactory calendar scraping
        // 2. FXStreet calendar API
        // 3. DailyFX calendar

        // For now, return simulated high-impact events
        this.log('Using simulated economic calendar (configure API for real data)');

        return this.getSimulatedEvents();
    }

    /**
     * Get simulated events for testing
     */
    getSimulatedEvents() {
        const now = new Date();

        // Generate realistic upcoming events
        return [
            {
                id: 'nfp_next',
                name: 'Non-Farm Payrolls',
                currency: 'USD',
                impact: 'high',
                eventTime: this.getNextEventTime(5), // First Friday of month
                previous: '256K',
                forecast: '180K',
                actual: null,
                direction: null,
                confidence: 0
            },
            {
                id: 'cpi_us',
                name: 'CPI m/m',
                currency: 'USD',
                impact: 'high',
                eventTime: this.getNextEventTime(12), // Mid month
                previous: '0.4%',
                forecast: '0.3%',
                actual: null,
                direction: null,
                confidence: 0
            },
            {
                id: 'ecb_rate',
                name: 'ECB Interest Rate Decision',
                currency: 'EUR',
                impact: 'high',
                eventTime: this.getNextEventTime(7),
                previous: '4.50%',
                forecast: '4.50%',
                actual: null,
                direction: null,
                confidence: 0
            }
        ];
    }

    /**
     * Get next event time (simulated)
     */
    getNextEventTime(daysAhead) {
        const date = new Date();
        date.setDate(date.getDate() + daysAhead);
        date.setHours(14, 30, 0, 0); // Most US events at 14:30 UTC
        return date.toISOString();
    }

    /**
     * Parse events from API response
     */
    parseEvents(data, source) {
        if (!Array.isArray(data)) return [];

        return data.map(event => ({
            id: event.id || `${event.Event}_${event.Date}`,
            name: event.Event || event.name,
            currency: event.Country || event.currency,
            impact: this.normalizeImpact(event.Importance || event.impact),
            eventTime: event.Date || event.eventTime,
            previous: event.Previous || event.previous,
            forecast: event.Forecast || event.forecast,
            actual: event.Actual || event.actual,
            source
        })).filter(e => e.impact === 'high');
    }

    /**
     * Normalize impact level
     */
    normalizeImpact(impact) {
        if (typeof impact === 'number') {
            return impact >= 3 ? 'high' : impact === 2 ? 'medium' : 'low';
        }
        return (impact || '').toLowerCase();
    }

    /**
     * Generate trading signal from news event
     */
    generateSignalFromEvent(event) {
        if (!event.actual || !event.forecast) {
            return { signal: 'NONE', reason: 'No actual data yet' };
        }

        // Parse values
        const actual = this.parseValue(event.actual);
        const forecast = this.parseValue(event.forecast);
        const previous = this.parseValue(event.previous);

        if (actual === null || forecast === null) {
            return { signal: 'NONE', reason: 'Cannot parse values' };
        }

        // Determine direction based on deviation
        const deviation = actual - forecast;
        const deviationPercent = Math.abs(deviation / forecast) * 100;

        // Minimum 0.5% deviation for signal
        if (deviationPercent < 0.5) {
            return { signal: 'NONE', reason: 'Deviation too small' };
        }

        // Get affected pairs
        const pairs = CONFIG.currencyPairs[event.currency] || [];
        if (pairs.length === 0) {
            return { signal: 'NONE', reason: 'No pairs for currency' };
        }

        // Determine signal direction
        // Better than expected = currency strengthens
        const isBetterThanExpected = this.isBetterResult(event.name, actual, forecast);

        let signal = 'NONE';
        let confidence = 50;

        if (isBetterThanExpected) {
            // Currency strengthens
            signal = this.getSignalForStrongCurrency(event.currency, pairs[0]);
            confidence = Math.min(50 + deviationPercent * 5, 85);
        } else {
            // Currency weakens
            signal = this.getSignalForWeakCurrency(event.currency, pairs[0]);
            confidence = Math.min(50 + deviationPercent * 5, 85);
        }

        return {
            signal,
            confidence: Math.round(confidence),
            asset: pairs[0],
            reason: `${event.name}: Actual ${event.actual} vs Forecast ${event.forecast}`,
            event
        };
    }

    /**
     * Parse numeric value from string
     */
    parseValue(value) {
        if (typeof value === 'number') return value;
        if (!value) return null;

        // Remove K, M, B, %, etc.
        const cleaned = value.toString()
            .replace(/[K]/gi, '000')
            .replace(/[M]/gi, '000000')
            .replace(/[B]/gi, '000000000')
            .replace(/[%,]/g, '');

        const num = parseFloat(cleaned);
        return isNaN(num) ? null : num;
    }

    /**
     * Determine if result is better than expected
     * Note: For some indicators like Unemployment, lower is better
     */
    isBetterResult(eventName, actual, forecast) {
        const lowerIsBetter = [
            'Unemployment Rate',
            'Jobless Claims',
            'CPI',
            'Core CPI',
            'PPI',
            'Trade Balance Deficit'
        ];

        const isNegativeIndicator = lowerIsBetter.some(
            indicator => eventName.toLowerCase().includes(indicator.toLowerCase())
        );

        if (isNegativeIndicator) {
            return actual < forecast;
        }

        return actual > forecast;
    }

    /**
     * Get signal when currency strengthens
     */
    getSignalForStrongCurrency(currency, pair) {
        // If currency is base (first in pair), CALL
        // If currency is quote (second in pair), PUT
        if (pair.startsWith(currency)) {
            return 'CALL';
        }
        return 'PUT';
    }

    /**
     * Get signal when currency weakens
     */
    getSignalForWeakCurrency(currency, pair) {
        if (pair.startsWith(currency)) {
            return 'PUT';
        }
        return 'CALL';
    }

    /**
     * Get signals for upcoming events
     */
    async getUpcomingSignals(hoursAhead = 24) {
        const events = await this.fetchUpcomingEvents(hoursAhead);

        return events.map(event => ({
            ...event,
            recommendedPairs: CONFIG.currencyPairs[event.currency] || [],
            tradingWindow: this.getTradingWindow(event.eventTime)
        }));
    }

    /**
     * Get optimal trading window around event
     */
    getTradingWindow(eventTime) {
        const eventDate = new Date(eventTime);

        return {
            // Trade 5-15 minutes after release (wait for reaction)
            start: new Date(eventDate.getTime() + 5 * 60 * 1000).toISOString(),
            end: new Date(eventDate.getTime() + 30 * 60 * 1000).toISOString(),
            note: 'Wait 5 minutes after release for initial reaction'
        };
    }

    /**
     * Check if news service is enabled and configured
     */
    isEnabled() {
        return CONFIG.apis.tradingEconomics.enabled ||
               process.env.NEWS_SIGNALS_ENABLED === 'true';
    }

    /**
     * Get service status
     */
    getStatus() {
        return {
            enabled: this.isEnabled(),
            tradingEconomicsConfigured: CONFIG.apis.tradingEconomics.enabled,
            cachedEvents: this.cache.size,
            highImpactEvents: CONFIG.highImpactEvents
        };
    }
}

module.exports = NewsSignalFetcher;
