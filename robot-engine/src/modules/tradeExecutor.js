/**
 * Trade Executor Module - Multi-User Support
 * Menggunakan Puppeteer untuk automasi trading di OlympTrade
 * Supports multiple users with their own browser sessions
 */

const puppeteer = require('puppeteer');
const logger = require('../utils/logger');
const { sleep, randomDelay } = require('../utils/helpers');

/**
 * Individual User Trading Session
 */
class UserSession {
    constructor(userId, email) {
        this.userId = userId;
        this.email = email;
        this.browser = null;
        this.page = null;
        this.isLoggedIn = false;
        this.lastTradeTime = null;
        this.minTradeInterval = 5000;
        this.loginAttempts = 0;
        this.maxLoginAttempts = 3;
        this.lastError = null;
        this.balance = null;
        this.createdAt = new Date();
        this.pendingTrades = []; // Track trades waiting for result
    }

    /**
     * Initialize browser and login
     */
    async initialize(email, password, isDemo = true) {
        try {
            logger.info(`[User ${this.userId}] Initializing session...`);

            this.browser = await puppeteer.launch({
                headless: 'new',
                args: [
                    '--no-sandbox',
                    '--disable-setuid-sandbox',
                    '--disable-dev-shm-usage',
                    '--disable-accelerated-2d-canvas',
                    '--disable-gpu',
                    '--window-size=1920,1080',
                    '--disable-web-security',
                    '--disable-features=IsolateOrigins,site-per-process'
                ],
                defaultViewport: { width: 1920, height: 1080 }
            });

            this.page = await this.browser.newPage();

            // Set realistic user agent
            await this.page.setUserAgent(
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
            );

            // Block unnecessary resources for faster loading
            await this.page.setRequestInterception(true);
            this.page.on('request', (req) => {
                const resourceType = req.resourceType();
                if (['image', 'stylesheet', 'font'].includes(resourceType)) {
                    req.abort();
                } else {
                    req.continue();
                }
            });

            // Navigate to OlympTrade
            logger.info(`[User ${this.userId}] Navigating to OlympTrade...`);
            await this.page.goto('https://olymptrade.com/platform', {
                waitUntil: 'networkidle2',
                timeout: 60000
            });

            await sleep(3000);

            // Login
            const loginSuccess = await this.login(email, password);
            if (!loginSuccess) {
                throw new Error('Login failed');
            }

            // Switch account type if needed
            if (isDemo) {
                await this.switchToDemoAccount();
            } else {
                await this.switchToRealAccount();
            }

            // Get initial balance
            this.balance = await this.getBalance();

            this.isLoggedIn = true;
            logger.info(`[User ${this.userId}] Session initialized. Balance: ${this.balance}`);

            return true;
        } catch (error) {
            logger.error(`[User ${this.userId}] Init failed:`, error.message);
            this.lastError = error.message;
            await this.close();
            return false;
        }
    }

    /**
     * Login to OlympTrade
     * Updated with comprehensive selectors for latest OlympTrade platform
     */
    async login(email, password) {
        try {
            this.loginAttempts++;
            logger.info(`[User ${this.userId}] Login attempt ${this.loginAttempts}...`);

            // Check if already on platform (logged in)
            const platformReady = await this.isPlatformReady();
            if (platformReady) {
                logger.info(`[User ${this.userId}] Already logged in`);
                return true;
            }

            // Wait for login form
            await sleep(3000);

            // Comprehensive email input selectors for OlympTrade
            const emailSelectors = [
                'input[name="email"]',
                'input[type="email"]',
                'input[placeholder*="email" i]',
                'input[placeholder*="Email" i]',
                'input[placeholder*="E-mail" i]',
                'input[autocomplete="email"]',
                'input[autocomplete="username"]',
                '.login-form input[type="text"]',
                '.auth-form input[type="text"]',
                '.signin-form input[type="text"]',
                'form input[type="email"]',
                '#email',
                '#login-email',
                '[data-qa="email-input"]',
                '[data-testid="email-input"]'
            ];

            let emailInput = null;
            for (const selector of emailSelectors) {
                try {
                    emailInput = await this.page.$(selector);
                    if (emailInput) {
                        logger.debug(`[User ${this.userId}] Found email input: ${selector}`);
                        break;
                    }
                } catch {}
            }

            // Fallback: scan all inputs
            if (!emailInput) {
                const inputs = await this.page.$$('input');
                for (const input of inputs) {
                    try {
                        const type = await this.page.evaluate(el => el.type, input);
                        const placeholder = await this.page.evaluate(el => el.placeholder || '', input);
                        const name = await this.page.evaluate(el => el.name || '', input);
                        if (type === 'email' ||
                            placeholder.toLowerCase().includes('email') ||
                            name.toLowerCase().includes('email')) {
                            emailInput = input;
                            logger.debug(`[User ${this.userId}] Found email by scanning`);
                            break;
                        }
                    } catch {}
                }
            }

            if (emailInput) {
                // Clear and type email with human-like delay
                await emailInput.click({ clickCount: 3 });
                await sleep(200);
                await emailInput.type(email, { delay: randomDelay(30, 80) });
                await sleep(500);

                // Comprehensive password selectors
                const passwordSelectors = [
                    'input[name="password"]',
                    'input[type="password"]',
                    '#password',
                    '#login-password',
                    '[data-qa="password-input"]',
                    '[data-testid="password-input"]',
                    '.password-input input'
                ];

                let passwordInput = null;
                for (const selector of passwordSelectors) {
                    try {
                        passwordInput = await this.page.$(selector);
                        if (passwordInput) break;
                    } catch {}
                }

                if (passwordInput) {
                    await passwordInput.type(password, { delay: randomDelay(30, 80) });
                    await sleep(500);

                    // Comprehensive login button selectors
                    const loginSelectors = [
                        'button[type="submit"]',
                        'input[type="submit"]',
                        '.login-button',
                        '.btn-login',
                        '.signin-button',
                        '.auth-button',
                        '[data-qa="login-button"]',
                        '[data-qa="signin-button"]',
                        '[data-testid="login-button"]',
                        'button.submit',
                        'form button',
                        '.form-submit button'
                    ];

                    let clicked = false;
                    for (const selector of loginSelectors) {
                        try {
                            const btn = await this.page.$(selector);
                            if (btn) {
                                const isVisible = await this.page.evaluate(el => {
                                    const style = window.getComputedStyle(el);
                                    return style.display !== 'none' &&
                                           style.visibility !== 'hidden' &&
                                           el.offsetParent !== null;
                                }, btn);

                                if (isVisible) {
                                    await btn.click();
                                    clicked = true;
                                    logger.debug(`[User ${this.userId}] Clicked login: ${selector}`);
                                    break;
                                }
                            }
                        } catch {}
                    }

                    if (!clicked) {
                        // Fallback: press Enter
                        await this.page.keyboard.press('Enter');
                        logger.debug(`[User ${this.userId}] Pressed Enter to submit`);
                    }

                    // Wait for login to complete (increased timeout)
                    await sleep(8000);

                    // Verify login success
                    const success = await this.isPlatformReady();
                    if (success) {
                        logger.info(`[User ${this.userId}] Login successful`);
                        return true;
                    }
                }
            }

            // Check for login errors
            const errorSelectors = [
                '.error-message',
                '.login-error',
                '.alert-danger',
                '.error',
                '.form-error',
                '[data-qa="error"]',
                '[data-testid="error"]',
                '.notification-error'
            ];

            for (const selector of errorSelectors) {
                try {
                    const errorElement = await this.page.$(selector);
                    if (errorElement) {
                        const errorText = await this.page.evaluate(el => el.textContent, errorElement);
                        logger.error(`[User ${this.userId}] Login error: ${errorText}`);
                        this.lastError = errorText;
                        break;
                    }
                } catch {}
            }

            return false;
        } catch (error) {
            logger.error(`[User ${this.userId}] Login error:`, error.message);
            this.lastError = error.message;
            return false;
        }
    }

    /**
     * Switch to demo account
     */
    async switchToDemoAccount() {
        try {
            logger.info(`[User ${this.userId}] Switching to DEMO account...`);

            const accountSwitchers = [
                '.account-switcher',
                '.balance-selector',
                '[data-qa="account-type"]',
                '.account-type-switch'
            ];

            for (const selector of accountSwitchers) {
                const switcher = await this.page.$(selector);
                if (switcher) {
                    await switcher.click();
                    await sleep(1000);

                    const demoOptions = [
                        '.demo-account',
                        '[data-type="demo"]',
                        '.practice-account',
                        'button:contains("Demo")'
                    ];

                    for (const optSelector of demoOptions) {
                        const option = await this.page.$(optSelector);
                        if (option) {
                            await option.click();
                            await sleep(2000);
                            logger.info(`[User ${this.userId}] Switched to DEMO`);
                            return true;
                        }
                    }
                }
            }

            return false;
        } catch (error) {
            logger.warn(`[User ${this.userId}] Could not switch to demo:`, error.message);
            return false;
        }
    }

    /**
     * Switch to real account
     */
    async switchToRealAccount() {
        try {
            logger.info(`[User ${this.userId}] Switching to REAL account...`);

            const accountSwitchers = [
                '.account-switcher',
                '.balance-selector',
                '[data-qa="account-type"]'
            ];

            for (const selector of accountSwitchers) {
                const switcher = await this.page.$(selector);
                if (switcher) {
                    await switcher.click();
                    await sleep(1000);

                    const realOptions = [
                        '.real-account',
                        '[data-type="real"]',
                        '.live-account'
                    ];

                    for (const optSelector of realOptions) {
                        const option = await this.page.$(optSelector);
                        if (option) {
                            await option.click();
                            await sleep(2000);
                            logger.info(`[User ${this.userId}] Switched to REAL`);
                            return true;
                        }
                    }
                }
            }

            return false;
        } catch (error) {
            logger.warn(`[User ${this.userId}] Could not switch to real:`, error.message);
            return false;
        }
    }

    /**
     * Execute a trade
     * Updated with comprehensive selectors for OlympTrade platform
     */
    async executeTrade(direction, amount, asset, duration = 1) {
        try {
            // Check minimum interval
            if (this.lastTradeTime) {
                const elapsed = Date.now() - this.lastTradeTime;
                if (elapsed < this.minTradeInterval) {
                    await sleep(this.minTradeInterval - elapsed);
                }
            }

            logger.info(`[User ${this.userId}] Executing ${direction} on ${asset}, Amount: ${amount}`);

            // Select asset
            await this.selectAsset(asset);
            await sleep(1000);

            // Set amount
            await this.setAmount(amount);
            await sleep(500);

            // Set duration
            await this.setDuration(duration);
            await sleep(500);

            // Comprehensive CALL button selectors for OlympTrade
            const callSelectors = [
                '.btn-call',
                '.call-button',
                '[data-qa="call-btn"]',
                '[data-qa="buy-btn"]',
                '[data-testid="call-btn"]',
                '.up-button',
                '.green-button',
                'button.call',
                '.deal-button--call',
                '.trade-button--call',
                '[data-direction="call"]',
                '[data-direction="up"]',
                '.button-up',
                '.buy-button',
                '.higher-button',
                '[class*="call"]',
                '[class*="green"]',
                '[class*="up-btn"]'
            ];

            // Comprehensive PUT button selectors for OlympTrade
            const putSelectors = [
                '.btn-put',
                '.put-button',
                '[data-qa="put-btn"]',
                '[data-qa="sell-btn"]',
                '[data-testid="put-btn"]',
                '.down-button',
                '.red-button',
                'button.put',
                '.deal-button--put',
                '.trade-button--put',
                '[data-direction="put"]',
                '[data-direction="down"]',
                '.button-down',
                '.sell-button',
                '.lower-button',
                '[class*="put"]',
                '[class*="red"]',
                '[class*="down-btn"]'
            ];

            const selectors = (direction === 'CALL' || direction === 'call') ? callSelectors : putSelectors;

            let tradeButton = null;
            for (const selector of selectors) {
                try {
                    tradeButton = await this.page.$(selector);
                    if (tradeButton) {
                        // Verify button is visible and enabled
                        const isValid = await this.page.evaluate(btn => {
                            const style = window.getComputedStyle(btn);
                            return style.display !== 'none' &&
                                   style.visibility !== 'hidden' &&
                                   !btn.disabled &&
                                   btn.offsetParent !== null;
                        }, tradeButton);

                        if (isValid) {
                            logger.debug(`[User ${this.userId}] Found trade button: ${selector}`);
                            break;
                        }
                        tradeButton = null;
                    }
                } catch {}
            }

            if (tradeButton) {
                // Get balance before trade
                const balanceBefore = this.balance || await this.getBalance();

                await tradeButton.click();
                this.lastTradeTime = Date.now();

                await sleep(2000);

                // Update balance after trade
                this.balance = await this.getBalance();

                // Calculate expected expiry time
                const expiryTime = Date.now() + (duration * 60 * 1000);

                // Create pending trade record
                const pendingTrade = {
                    id: `trade_${Date.now()}_${this.userId}`,
                    direction,
                    amount,
                    asset,
                    duration,
                    balanceBefore,
                    executedAt: Date.now(),
                    expiryTime: expiryTime
                };

                this.pendingTrades.push(pendingTrade);

                logger.info(`[User ${this.userId}] Trade executed! Balance: ${this.balance}, Expires in ${duration}min`);

                return {
                    success: true,
                    tradeId: pendingTrade.id,
                    direction,
                    amount,
                    asset,
                    duration,
                    balance: this.balance,
                    balanceBefore: balanceBefore,
                    expiryTime: expiryTime,
                    timestamp: new Date().toISOString()
                };
            }

            logger.warn(`[User ${this.userId}] Trade button not found for ${direction}`);
            return { success: false, error: 'Trade button not found' };
        } catch (error) {
            logger.error(`[User ${this.userId}] Trade error:`, error.message);
            return { success: false, error: error.message };
        }
    }

    /**
     * Select trading asset
     */
    async selectAsset(asset) {
        try {
            const assetSelectors = [
                '.asset-selector',
                '.instrument-selector',
                '[data-qa="asset-select"]'
            ];

            for (const selector of assetSelectors) {
                const btn = await this.page.$(selector);
                if (btn) {
                    await btn.click();
                    await sleep(1000);

                    // Search for asset
                    const searchInput = await this.page.$('.asset-search input, .search-instrument input, input[placeholder*="search" i]');
                    if (searchInput) {
                        await searchInput.click({ clickCount: 3 });
                        await searchInput.type(asset.replace('/', ''), { delay: 50 });
                        await sleep(1000);
                    }

                    // Click first result
                    const result = await this.page.$('.asset-item, .instrument-item, [data-asset]');
                    if (result) {
                        await result.click();
                        await sleep(1000);
                    }

                    return true;
                }
            }
            return false;
        } catch (error) {
            logger.warn(`[User ${this.userId}] Select asset error:`, error.message);
            return false;
        }
    }

    /**
     * Set trade amount
     */
    async setAmount(amount) {
        try {
            const amountSelectors = [
                '.amount-input input',
                'input[data-qa="amount"]',
                '.trade-amount input',
                'input[name="amount"]'
            ];

            for (const selector of amountSelectors) {
                const input = await this.page.$(selector);
                if (input) {
                    await input.click({ clickCount: 3 });
                    await input.type(amount.toString(), { delay: 30 });
                    return true;
                }
            }
            return false;
        } catch (error) {
            return false;
        }
    }

    /**
     * Set trade duration
     */
    async setDuration(minutes) {
        try {
            const durationSelectors = [
                '.duration-selector',
                '.expiry-time',
                '[data-qa="time-select"]'
            ];

            for (const selector of durationSelectors) {
                const btn = await this.page.$(selector);
                if (btn) {
                    await btn.click();
                    await sleep(500);

                    const option = await this.page.$(`[data-duration="${minutes}"], .time-option`);
                    if (option) {
                        await option.click();
                        return true;
                    }
                }
            }
            return false;
        } catch (error) {
            return false;
        }
    }

    /**
     * Get current balance
     */
    async getBalance() {
        try {
            const balanceSelectors = [
                '.balance-value',
                '.account-balance',
                '[data-qa="balance"]',
                '.balance-amount'
            ];

            for (const selector of balanceSelectors) {
                const element = await this.page.$(selector);
                if (element) {
                    const text = await this.page.evaluate(el => el.textContent, element);
                    const balance = parseFloat(text.replace(/[^0-9.-]+/g, ''));
                    if (!isNaN(balance)) {
                        return balance;
                    }
                }
            }
            return null;
        } catch (error) {
            return null;
        }
    }

    /**
     * Check if platform is ready for trading
     * Updated with comprehensive selectors
     */
    async isPlatformReady() {
        try {
            // Comprehensive selectors to detect trading platform is loaded
            const readyIndicators = [
                // Trade buttons
                '.btn-call',
                '.call-button',
                '[data-qa="call-btn"]',
                '.deal-button--call',
                '.up-button',
                '.btn-put',
                '.put-button',
                '[data-qa="put-btn"]',
                '.deal-button--put',
                '.down-button',
                // Trading panel indicators
                '.trading-panel',
                '.trade-panel',
                '.deal-form',
                '.trading-form',
                '[data-qa="trading-panel"]',
                // Amount input
                '.amount-input',
                '[data-qa="amount"]',
                // Balance indicator
                '.balance-value',
                '.account-balance',
                '[data-qa="balance"]'
            ];

            for (const selector of readyIndicators) {
                try {
                    const element = await this.page.$(selector);
                    if (element) {
                        const isVisible = await this.page.evaluate(el => {
                            const style = window.getComputedStyle(el);
                            return style.display !== 'none' &&
                                   style.visibility !== 'hidden' &&
                                   el.offsetParent !== null;
                        }, element);

                        if (isVisible) {
                            logger.debug(`[User ${this.userId}] Platform ready indicator: ${selector}`);
                            return true;
                        }
                    }
                } catch {}
            }

            return false;
        } catch {
            return false;
        }
    }

    /**
     * Check expired trades and determine win/loss
     * Call this periodically to get trade results
     */
    async checkExpiredTrades() {
        const results = [];
        const now = Date.now();

        // Filter trades that have expired
        const expiredTrades = this.pendingTrades.filter(t => now >= t.expiryTime + 5000); // +5s buffer

        for (const trade of expiredTrades) {
            try {
                // Get current balance to determine result
                const currentBalance = await this.getBalance();

                if (currentBalance === null) {
                    logger.warn(`[User ${this.userId}] Could not get balance for trade result`);
                    continue;
                }

                // Calculate profit/loss
                // Note: OlympTrade typically pays 80-92% on win
                const expectedPayout = trade.amount * 0.85; // Average 85% payout
                const balanceChange = currentBalance - trade.balanceBefore;

                let result, profitLoss;

                if (balanceChange > 0) {
                    // Won - balance increased
                    result = 'win';
                    profitLoss = Math.min(balanceChange, expectedPayout);
                } else if (balanceChange < -trade.amount * 0.5) {
                    // Lost - balance decreased by more than half the trade
                    result = 'loss';
                    profitLoss = -trade.amount;
                } else {
                    // Tie or uncertain - small change
                    result = 'tie';
                    profitLoss = balanceChange;
                }

                this.balance = currentBalance;

                results.push({
                    tradeId: trade.id,
                    result: result,
                    profitLoss: profitLoss,
                    balanceBefore: trade.balanceBefore,
                    balanceAfter: currentBalance,
                    direction: trade.direction,
                    amount: trade.amount,
                    asset: trade.asset
                });

                logger.info(`[User ${this.userId}] Trade ${trade.id} result: ${result}, P&L: ${profitLoss}`);

            } catch (error) {
                logger.error(`[User ${this.userId}] Error checking trade result:`, error.message);
            }
        }

        // Remove processed trades from pending
        this.pendingTrades = this.pendingTrades.filter(t => now < t.expiryTime + 5000);

        return results;
    }

    /**
     * Get count of pending trades
     */
    getPendingTradesCount() {
        return this.pendingTrades.length;
    }

    /**
     * Take screenshot for debugging
     */
    async takeScreenshot(name) {
        try {
            const filename = `./logs/screenshots/${this.userId}_${name}_${Date.now()}.png`;
            await this.page.screenshot({ path: filename, fullPage: true });
            return filename;
        } catch {
            return null;
        }
    }

    /**
     * Check if session is healthy
     */
    isHealthy() {
        return this.browser !== null && this.page !== null && this.isLoggedIn;
    }

    /**
     * Close session
     */
    async close() {
        try {
            if (this.browser) {
                await this.browser.close();
            }
        } catch (error) {
            logger.warn(`[User ${this.userId}] Close error:`, error.message);
        } finally {
            this.browser = null;
            this.page = null;
            this.isLoggedIn = false;
        }
    }
}

/**
 * Trade Executor - Manages multiple user sessions
 */
class TradeExecutor {
    constructor(db) {
        this.db = db;
        this.sessions = new Map(); // userId -> UserSession
        this.maxConcurrentSessions = 5; // Limit concurrent browser instances
    }

    /**
     * Get or create session for user
     */
    async getSession(userId) {
        // Check if session exists and is healthy
        if (this.sessions.has(userId)) {
            const session = this.sessions.get(userId);
            if (session.isHealthy()) {
                return session;
            }
            // Close unhealthy session
            await session.close();
            this.sessions.delete(userId);
        }

        // Check session limit
        if (this.sessions.size >= this.maxConcurrentSessions) {
            // Close oldest session
            const oldest = [...this.sessions.entries()]
                .sort((a, b) => a[1].createdAt - b[1].createdAt)[0];
            if (oldest) {
                await oldest[1].close();
                this.sessions.delete(oldest[0]);
            }
        }

        // Get credentials from database
        const creds = await this.db.getOlympTradeCredentials(userId);
        if (!creds) {
            logger.error(`No credentials for user ${userId}`);
            return null;
        }

        // Create new session
        const session = new UserSession(userId, creds.email);
        const success = await session.initialize(creds.email, creds.password, creds.isDemo);

        if (success) {
            this.sessions.set(userId, session);
            await this.db.updateRobotStatus(userId, 'running');
            return session;
        }

        await this.db.updateRobotStatus(userId, 'error', session.lastError);
        return null;
    }

    /**
     * Execute trade for a user
     */
    async executeTrade(tradeParams) {
        const { userId, direction, amount, pair, timeframe } = tradeParams;

        const session = await this.getSession(userId);
        if (!session) {
            return { success: false, error: 'Could not create session' };
        }

        // Calculate duration from timeframe
        const durationMap = { '1M': 1, '5M': 5, '15M': 15, '30M': 30, '1H': 60 };
        const duration = durationMap[timeframe] || 1;

        const result = await session.executeTrade(direction, amount, pair, duration);

        // Update balance in database
        if (session.balance) {
            await this.db.updateUserBalance(userId, session.balance);
        }

        return result;
    }

    /**
     * Get session status for user
     */
    getSessionStatus(userId) {
        const session = this.sessions.get(userId);
        if (!session) {
            return { connected: false };
        }

        return {
            connected: session.isHealthy(),
            balance: session.balance,
            lastTradeTime: session.lastTradeTime,
            email: session.email
        };
    }

    /**
     * Close all sessions
     */
    async closeAll() {
        logger.info('Closing all trading sessions...');
        for (const [userId, session] of this.sessions) {
            await session.close();
            await this.db.updateRobotStatus(userId, 'stopped');
        }
        this.sessions.clear();
    }

    /**
     * Close session for specific user
     */
    async closeSession(userId) {
        const session = this.sessions.get(userId);
        if (session) {
            await session.close();
            this.sessions.delete(userId);
            await this.db.updateRobotStatus(userId, 'stopped');
        }
    }

    /**
     * Get active session count
     */
    getActiveSessionCount() {
        return this.sessions.size;
    }

    /**
     * Check all sessions for expired trades and return results
     */
    async checkAllExpiredTrades() {
        const allResults = [];

        for (const [userId, session] of this.sessions) {
            if (session.isHealthy() && session.getPendingTradesCount() > 0) {
                const results = await session.checkExpiredTrades();
                for (const result of results) {
                    allResults.push({
                        userId: userId,
                        ...result
                    });
                }
            }
        }

        return allResults;
    }

    /**
     * Get total pending trades across all sessions
     */
    getTotalPendingTrades() {
        let total = 0;
        for (const [, session] of this.sessions) {
            total += session.getPendingTradesCount();
        }
        return total;
    }
}

module.exports = TradeExecutor;
