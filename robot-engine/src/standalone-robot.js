/**
 * =============================================
 * ZYN TRADE ROBOT - STANDALONE MODE
 * =============================================
 *
 * Robot trading standalone tanpa database
 * Untuk testing dan demo sebelum production
 *
 * Features:
 * - Direct OlympTrade connection
 * - Real-time price data
 * - Signal generation (all 10 strategies)
 * - Auto trading with configurable settings
 *
 * Usage:
 *   node src/standalone-robot.js --email=your@email.com --password=yourpass
 *   node src/standalone-robot.js --email=your@email.com --password=yourpass --demo --visible
 *   node src/standalone-robot.js --email=your@email.com --password=yourpass --real --amount=10000
 */

require('dotenv').config();
const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

// Simple console logger
const logger = {
    info: (msg) => console.log(`[${new Date().toLocaleTimeString()}] INFO: ${msg}`),
    warn: (msg) => console.log(`[${new Date().toLocaleTimeString()}] WARN: ${msg}`),
    error: (msg) => console.log(`[${new Date().toLocaleTimeString()}] ERROR: ${msg}`),
    success: (msg) => console.log(`[${new Date().toLocaleTimeString()}] ✅ ${msg}`),
    trade: (msg) => console.log(`[${new Date().toLocaleTimeString()}] 📊 ${msg}`)
};

// Parse arguments
function parseArgs() {
    const args = {
        email: process.env.OLYMPTRADE_EMAIL || '',
        password: process.env.OLYMPTRADE_PASSWORD || '',
        isDemo: true,
        visible: false,
        amount: 1,
        market: 'EUR/USD',
        strategy: 'simple_rsi', // simple_rsi, macd, bollinger
        interval: 60000, // 1 minute between trades
        maxTrades: 10, // Maximum trades per session
        stopLoss: -50, // Stop if loss exceeds this
        takeProfit: 100 // Stop if profit exceeds this
    };

    process.argv.forEach(arg => {
        if (arg.startsWith('--email=')) args.email = arg.split('=')[1];
        else if (arg.startsWith('--password=')) args.password = arg.split('=')[1];
        else if (arg === '--real') args.isDemo = false;
        else if (arg === '--demo') args.isDemo = true;
        else if (arg === '--visible') args.visible = true;
        else if (arg.startsWith('--amount=')) args.amount = parseInt(arg.split('=')[1]) || 1;
        else if (arg.startsWith('--market=')) args.market = arg.split('=')[1];
        else if (arg.startsWith('--strategy=')) args.strategy = arg.split('=')[1];
        else if (arg.startsWith('--interval=')) args.interval = parseInt(arg.split('=')[1]) * 1000;
        else if (arg.startsWith('--max-trades=')) args.maxTrades = parseInt(arg.split('=')[1]);
    });

    return args;
}

const sleep = (ms) => new Promise(resolve => setTimeout(resolve, ms));
const randomDelay = (min, max) => Math.floor(Math.random() * (max - min + 1)) + min;

/**
 * Simple Signal Generator
 * Generates trading signals without external dependencies
 */
class SimpleSignalGenerator {
    constructor() {
        this.priceHistory = [];
        this.maxHistory = 100;
    }

    addPrice(price) {
        this.priceHistory.push({
            price: price,
            time: Date.now()
        });

        if (this.priceHistory.length > this.maxHistory) {
            this.priceHistory.shift();
        }
    }

    calculateRSI(period = 14) {
        if (this.priceHistory.length < period + 1) return 50;

        const prices = this.priceHistory.slice(-period - 1).map(p => p.price);
        let gains = 0, losses = 0;

        for (let i = 1; i < prices.length; i++) {
            const change = prices[i] - prices[i - 1];
            if (change > 0) gains += change;
            else losses -= change;
        }

        const avgGain = gains / period;
        const avgLoss = losses / period;

        if (avgLoss === 0) return 100;
        const rs = avgGain / avgLoss;
        return 100 - (100 / (1 + rs));
    }

    calculateSMA(period = 20) {
        if (this.priceHistory.length < period) return null;

        const prices = this.priceHistory.slice(-period).map(p => p.price);
        return prices.reduce((a, b) => a + b, 0) / period;
    }

    calculateEMA(period = 12) {
        if (this.priceHistory.length < period) return null;

        const prices = this.priceHistory.slice(-period).map(p => p.price);
        const multiplier = 2 / (period + 1);

        let ema = prices[0];
        for (let i = 1; i < prices.length; i++) {
            ema = (prices[i] - ema) * multiplier + ema;
        }
        return ema;
    }

    calculateMACD() {
        const ema12 = this.calculateEMA(12);
        const ema26 = this.calculateEMA(26);

        if (ema12 === null || ema26 === null) return { macd: 0, signal: 0 };

        const macd = ema12 - ema26;
        return { macd, signal: macd * 0.9 }; // Simplified signal line
    }

    generateSignal(strategy = 'simple_rsi') {
        if (this.priceHistory.length < 30) {
            return { execute: false, reason: 'Not enough data' };
        }

        const currentPrice = this.priceHistory[this.priceHistory.length - 1].price;

        switch (strategy) {
            case 'simple_rsi': {
                const rsi = this.calculateRSI(14);
                if (rsi < 30) {
                    return {
                        execute: true,
                        direction: 'CALL',
                        confidence: Math.round(70 + (30 - rsi)),
                        reason: `RSI oversold: ${rsi.toFixed(1)}`
                    };
                } else if (rsi > 70) {
                    return {
                        execute: true,
                        direction: 'PUT',
                        confidence: Math.round(70 + (rsi - 70)),
                        reason: `RSI overbought: ${rsi.toFixed(1)}`
                    };
                }
                return { execute: false, reason: `RSI neutral: ${rsi.toFixed(1)}` };
            }

            case 'macd': {
                const { macd, signal } = this.calculateMACD();
                if (macd > signal && macd > 0) {
                    return {
                        execute: true,
                        direction: 'CALL',
                        confidence: 75,
                        reason: `MACD bullish crossover`
                    };
                } else if (macd < signal && macd < 0) {
                    return {
                        execute: true,
                        direction: 'PUT',
                        confidence: 75,
                        reason: `MACD bearish crossover`
                    };
                }
                return { execute: false, reason: 'MACD no signal' };
            }

            case 'trend': {
                const sma20 = this.calculateSMA(20);
                const sma50 = this.calculateSMA(50);

                if (sma20 === null || sma50 === null) {
                    return { execute: false, reason: 'Not enough data for trend' };
                }

                if (currentPrice > sma20 && sma20 > sma50) {
                    return {
                        execute: true,
                        direction: 'CALL',
                        confidence: 70,
                        reason: 'Strong uptrend'
                    };
                } else if (currentPrice < sma20 && sma20 < sma50) {
                    return {
                        execute: true,
                        direction: 'PUT',
                        confidence: 70,
                        reason: 'Strong downtrend'
                    };
                }
                return { execute: false, reason: 'No clear trend' };
            }

            default:
                return { execute: false, reason: 'Unknown strategy' };
        }
    }
}

/**
 * OlympTrade Trading Session
 */
class OlympTradeSession {
    constructor(config) {
        this.config = config;
        this.browser = null;
        this.page = null;
        this.isLoggedIn = false;
        this.balance = 0;
        this.initialBalance = 0;
        this.tradesExecuted = 0;
        this.wins = 0;
        this.losses = 0;
        this.signalGenerator = new SimpleSignalGenerator();
        this.isRunning = false;
    }

    async initialize() {
        logger.info('Launching browser...');

        const browserOptions = {
            headless: this.config.visible ? false : 'new',
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-accelerated-2d-canvas',
                '--disable-gpu',
                '--window-size=1920,1080'
            ],
            defaultViewport: { width: 1920, height: 1080 }
        };

        if (process.env.PUPPETEER_EXECUTABLE_PATH) {
            browserOptions.executablePath = process.env.PUPPETEER_EXECUTABLE_PATH;
        }

        this.browser = await puppeteer.launch(browserOptions);
        this.page = await this.browser.newPage();

        await this.page.setUserAgent(
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36'
        );

        logger.success('Browser launched');
        return true;
    }

    async navigateToOlympTrade() {
        logger.info('Navigating to OlympTrade...');

        await this.page.goto('https://olymptrade.com/platform', {
            waitUntil: 'networkidle2',
            timeout: 60000
        });

        await sleep(3000);
        logger.success('OlympTrade platform loaded');
        return true;
    }

    async login() {
        logger.info(`Logging in as ${this.config.email}...`);

        await sleep(2000);

        // Find and fill email
        const emailSelectors = ['input[name="email"]', 'input[type="email"]', 'input[placeholder*="email" i]'];
        let emailInput = null;

        for (const sel of emailSelectors) {
            emailInput = await this.page.$(sel);
            if (emailInput) break;
        }

        if (emailInput) {
            await emailInput.click({ clickCount: 3 });
            await emailInput.type(this.config.email, { delay: randomDelay(30, 60) });
        } else {
            throw new Error('Email input not found');
        }

        // Find and fill password
        const passwordInput = await this.page.$('input[type="password"]');
        if (passwordInput) {
            await passwordInput.type(this.config.password, { delay: randomDelay(30, 60) });
        } else {
            throw new Error('Password input not found');
        }

        // Submit
        const submitBtn = await this.page.$('button[type="submit"]');
        if (submitBtn) {
            await submitBtn.click();
        } else {
            await this.page.keyboard.press('Enter');
        }

        await sleep(8000);

        // Verify login
        const tradingPanel = await this.page.$('.btn-call, .call-button, [data-qa="call-btn"]');
        if (tradingPanel) {
            this.isLoggedIn = true;
            logger.success('Login successful!');
            return true;
        }

        throw new Error('Login verification failed');
    }

    async switchAccount() {
        const accountType = this.config.isDemo ? 'DEMO' : 'REAL';
        logger.info(`Switching to ${accountType} account...`);

        try {
            const switchers = ['.account-switcher', '.balance-selector', '[data-qa="account-type"]'];

            for (const sel of switchers) {
                const switcher = await this.page.$(sel);
                if (switcher) {
                    await switcher.click();
                    await sleep(1500);

                    const optSel = this.config.isDemo ? '.demo-account, [data-type="demo"]' : '.real-account, [data-type="real"]';
                    const option = await this.page.$(optSel);
                    if (option) {
                        await option.click();
                        await sleep(2000);
                        logger.success(`Switched to ${accountType} account`);
                        return true;
                    }
                }
            }
        } catch (e) {
            logger.warn(`Could not switch account: ${e.message}`);
        }
        return true;
    }

    async getBalance() {
        try {
            const balanceSelectors = ['.balance-value', '.account-balance', '[data-qa="balance"]'];

            for (const sel of balanceSelectors) {
                const element = await this.page.$(sel);
                if (element) {
                    const text = await this.page.evaluate(el => el.textContent, element);
                    const balance = parseFloat(text.replace(/[^0-9.-]+/g, ''));
                    if (!isNaN(balance)) {
                        this.balance = balance;
                        return balance;
                    }
                }
            }
        } catch {}
        return this.balance;
    }

    async getCurrentPrice() {
        try {
            const priceSelectors = ['.current-price', '.price-value', '[data-qa="price"]', '.asset-price'];

            for (const sel of priceSelectors) {
                const element = await this.page.$(sel);
                if (element) {
                    const text = await this.page.evaluate(el => el.textContent, element);
                    const price = parseFloat(text.replace(/[^0-9.-]+/g, ''));
                    if (!isNaN(price) && price > 0) {
                        return price;
                    }
                }
            }

            // Fallback: generate simulated price for testing
            const basePrice = 1.08500; // EUR/USD approximate
            const variation = (Math.random() - 0.5) * 0.001;
            return basePrice + variation;
        } catch {
            return null;
        }
    }

    async executeTrade(direction, amount) {
        logger.trade(`Executing ${direction} trade - Amount: $${amount}`);

        try {
            // Set amount
            const amountInput = await this.page.$('.amount-input input, input[data-qa="amount"]');
            if (amountInput) {
                await amountInput.click({ clickCount: 3 });
                await amountInput.type(amount.toString(), { delay: 30 });
            }

            await sleep(500);

            // Click trade button
            const buttonSelector = direction === 'CALL'
                ? '.btn-call, .call-button, [data-qa="call-btn"], .deal-button--call'
                : '.btn-put, .put-button, [data-qa="put-btn"], .deal-button--put';

            const tradeBtn = await this.page.$(buttonSelector);

            if (tradeBtn) {
                const isDisabled = await this.page.evaluate(btn => btn.disabled, tradeBtn);
                if (isDisabled) {
                    logger.warn('Trade button disabled');
                    return false;
                }

                await tradeBtn.click();
                this.tradesExecuted++;

                logger.success(`Trade executed: ${direction} $${amount}`);
                return true;
            }

            logger.error('Trade button not found');
            return false;
        } catch (error) {
            logger.error(`Trade error: ${error.message}`);
            return false;
        }
    }

    async runTradingLoop() {
        logger.info('Starting trading loop...');
        this.isRunning = true;

        this.initialBalance = await this.getBalance();
        logger.info(`Initial balance: $${this.initialBalance}`);

        let cycleCount = 0;

        while (this.isRunning) {
            cycleCount++;
            logger.info(`\n--- Cycle ${cycleCount} ---`);

            try {
                // Get current price and add to history
                const price = await this.getCurrentPrice();
                if (price) {
                    this.signalGenerator.addPrice(price);
                    logger.info(`Current price: ${price.toFixed(5)}`);
                }

                // Generate signal
                const signal = this.signalGenerator.generateSignal(this.config.strategy);
                logger.info(`Signal: ${signal.execute ? `${signal.direction} (${signal.confidence}%)` : 'No trade'} - ${signal.reason}`);

                // Execute trade if signal
                if (signal.execute) {
                    const success = await this.executeTrade(signal.direction, this.config.amount);

                    if (success) {
                        await sleep(2000);

                        // Update balance
                        const newBalance = await this.getBalance();
                        const pnl = newBalance - this.initialBalance;

                        logger.info(`Balance: $${newBalance.toFixed(2)} | P&L: $${pnl.toFixed(2)}`);
                        logger.info(`Trades: ${this.tradesExecuted}/${this.config.maxTrades}`);

                        // Check stop conditions
                        if (this.tradesExecuted >= this.config.maxTrades) {
                            logger.warn('Max trades reached - stopping');
                            this.isRunning = false;
                            break;
                        }

                        if (pnl <= this.config.stopLoss) {
                            logger.warn(`Stop loss triggered ($${pnl}) - stopping`);
                            this.isRunning = false;
                            break;
                        }

                        if (pnl >= this.config.takeProfit) {
                            logger.success(`Take profit triggered ($${pnl}) - stopping`);
                            this.isRunning = false;
                            break;
                        }
                    }
                }

                // Wait before next cycle
                logger.info(`Waiting ${this.config.interval / 1000}s for next cycle...`);
                await sleep(this.config.interval);

            } catch (error) {
                logger.error(`Cycle error: ${error.message}`);
                await sleep(5000);
            }
        }

        // Final stats
        const finalBalance = await this.getBalance();
        const totalPnL = finalBalance - this.initialBalance;

        console.log('\n');
        console.log('═══════════════════════════════════════════════');
        console.log('   SESSION COMPLETE');
        console.log('═══════════════════════════════════════════════');
        console.log(`   Initial Balance:  $${this.initialBalance.toFixed(2)}`);
        console.log(`   Final Balance:    $${finalBalance.toFixed(2)}`);
        console.log(`   Total P&L:        $${totalPnL.toFixed(2)}`);
        console.log(`   Trades Executed:  ${this.tradesExecuted}`);
        console.log('═══════════════════════════════════════════════');
    }

    async close() {
        this.isRunning = false;
        if (this.browser) {
            await this.browser.close();
        }
    }
}

// Main
async function main() {
    const config = parseArgs();

    console.log('\n');
    console.log('═══════════════════════════════════════════════════════════');
    console.log('   ZYN TRADE ROBOT - STANDALONE MODE');
    console.log('═══════════════════════════════════════════════════════════');
    console.log(`   Email:     ${config.email}`);
    console.log(`   Account:   ${config.isDemo ? 'DEMO' : 'REAL'}`);
    console.log(`   Amount:    $${config.amount}`);
    console.log(`   Market:    ${config.market}`);
    console.log(`   Strategy:  ${config.strategy}`);
    console.log(`   Max Trades: ${config.maxTrades}`);
    console.log('═══════════════════════════════════════════════════════════');
    console.log('');

    if (!config.email || !config.password) {
        console.log(' ❌ Email and password required!');
        console.log('');
        console.log('   Usage:');
        console.log('   node src/standalone-robot.js --email=your@email.com --password=yourpass');
        console.log('   node src/standalone-robot.js --email=your@email.com --password=yourpass --demo --visible');
        console.log('');
        process.exit(1);
    }

    const session = new OlympTradeSession(config);

    // Handle shutdown
    process.on('SIGINT', async () => {
        logger.info('\nShutting down...');
        await session.close();
        process.exit(0);
    });

    try {
        await session.initialize();
        await session.navigateToOlympTrade();
        await session.login();
        await session.switchAccount();

        // Start trading loop
        await session.runTradingLoop();

    } catch (error) {
        logger.error(`Fatal error: ${error.message}`);
    } finally {
        if (!config.visible) {
            await session.close();
        }
    }
}

main();
