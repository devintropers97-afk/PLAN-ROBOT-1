/**
 * Trade Executor Module
 * Menggunakan Puppeteer untuk automasi trading di OlympTrade
 */

const puppeteer = require('puppeteer');
const logger = require('../utils/logger');
const { sleep, formatCurrency } = require('../utils/helpers');

class TradeExecutor {
    constructor() {
        this.browser = null;
        this.page = null;
        this.isLoggedIn = false;
        this.lastTradeTime = null;
        this.minTradeInterval = 5000; // 5 seconds minimum between trades
    }

    /**
     * Initialize browser and login to OlympTrade
     */
    async initialize(email, password, demoMode = true) {
        try {
            logger.info('Initializing Trade Executor...');

            this.browser = await puppeteer.launch({
                headless: 'new', // Use new headless mode
                args: [
                    '--no-sandbox',
                    '--disable-setuid-sandbox',
                    '--disable-dev-shm-usage',
                    '--disable-accelerated-2d-canvas',
                    '--disable-gpu',
                    '--window-size=1920,1080'
                ],
                defaultViewport: {
                    width: 1920,
                    height: 1080
                }
            });

            this.page = await this.browser.newPage();

            // Set user agent to avoid detection
            await this.page.setUserAgent(
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
            );

            // Navigate to OlympTrade
            await this.page.goto('https://olymptrade.com/platform', {
                waitUntil: 'networkidle2',
                timeout: 60000
            });

            await sleep(3000);

            // Try to login
            await this.login(email, password);

            // Switch to demo mode if specified
            if (demoMode) {
                await this.switchToDemoAccount();
            }

            this.isLoggedIn = true;
            logger.info('Trade Executor initialized successfully');

            return true;
        } catch (error) {
            logger.error('Failed to initialize Trade Executor:', error);
            return false;
        }
    }

    /**
     * Login to OlympTrade account
     */
    async login(email, password) {
        try {
            logger.info('Attempting to login to OlympTrade...');

            // Wait for login form or check if already logged in
            const loginSelector = 'input[name="email"], input[type="email"]';
            const isLoginPage = await this.page.$(loginSelector);

            if (isLoginPage) {
                // Fill email
                await this.page.waitForSelector(loginSelector, { timeout: 10000 });
                await this.page.type(loginSelector, email, { delay: 50 });

                // Fill password
                const passwordSelector = 'input[name="password"], input[type="password"]';
                await this.page.waitForSelector(passwordSelector, { timeout: 5000 });
                await this.page.type(passwordSelector, password, { delay: 50 });

                // Click login button
                const loginButton = await this.page.$('button[type="submit"], .login-button, .btn-login');
                if (loginButton) {
                    await loginButton.click();
                }

                // Wait for navigation/login completion
                await sleep(5000);

                logger.info('Login attempt completed');
            } else {
                logger.info('Already logged in or different page structure');
            }
        } catch (error) {
            logger.error('Login error:', error);
            throw error;
        }
    }

    /**
     * Switch to demo account
     */
    async switchToDemoAccount() {
        try {
            logger.info('Switching to demo account...');

            // Look for account switcher
            const accountSelector = '.account-switcher, .balance-selector, [data-qa="account-type"]';
            const accountSwitch = await this.page.$(accountSelector);

            if (accountSwitch) {
                await accountSwitch.click();
                await sleep(1000);

                // Click demo option
                const demoOption = await this.page.$('.demo-account, [data-type="demo"], .practice-account');
                if (demoOption) {
                    await demoOption.click();
                    await sleep(2000);
                }
            }

            logger.info('Demo account selected');
        } catch (error) {
            logger.warn('Could not switch to demo account:', error.message);
        }
    }

    /**
     * Select trading asset
     */
    async selectAsset(asset) {
        try {
            logger.info(`Selecting asset: ${asset}`);

            // Click asset selector
            const assetSelector = '.asset-selector, .instrument-selector, [data-qa="asset-select"]';
            const assetButton = await this.page.$(assetSelector);

            if (assetButton) {
                await assetButton.click();
                await sleep(1000);

                // Search for asset
                const searchInput = await this.page.$('.asset-search input, .search-instrument input');
                if (searchInput) {
                    await searchInput.type(asset, { delay: 50 });
                    await sleep(1000);
                }

                // Click on asset result
                const assetResult = await this.page.$(`[data-asset="${asset}"], .asset-item:first-child`);
                if (assetResult) {
                    await assetResult.click();
                    await sleep(2000);
                }
            }

            logger.info(`Asset ${asset} selected`);
            return true;
        } catch (error) {
            logger.error(`Failed to select asset ${asset}:`, error);
            return false;
        }
    }

    /**
     * Set trade amount
     */
    async setAmount(amount) {
        try {
            logger.info(`Setting trade amount: ${amount}`);

            // Find amount input
            const amountSelector = '.amount-input input, input[data-qa="amount"], .trade-amount input';
            const amountInput = await this.page.$(amountSelector);

            if (amountInput) {
                // Clear existing value
                await amountInput.click({ clickCount: 3 });
                await amountInput.type(amount.toString(), { delay: 30 });
            }

            logger.info(`Amount set to ${amount}`);
            return true;
        } catch (error) {
            logger.error('Failed to set amount:', error);
            return false;
        }
    }

    /**
     * Set trade duration/expiry
     */
    async setDuration(minutes) {
        try {
            logger.info(`Setting trade duration: ${minutes} minutes`);

            // Click duration selector
            const durationSelector = '.duration-selector, .expiry-time, [data-qa="time-select"]';
            const durationButton = await this.page.$(durationSelector);

            if (durationButton) {
                await durationButton.click();
                await sleep(500);

                // Select duration option
                const durationOption = await this.page.$(`[data-duration="${minutes}"], .time-option:contains("${minutes}")`);
                if (durationOption) {
                    await durationOption.click();
                    await sleep(500);
                }
            }

            logger.info(`Duration set to ${minutes} minutes`);
            return true;
        } catch (error) {
            logger.error('Failed to set duration:', error);
            return false;
        }
    }

    /**
     * Execute a trade (CALL or PUT)
     */
    async executeTrade(direction, amount, asset, duration = 1) {
        try {
            // Check minimum trade interval
            if (this.lastTradeTime) {
                const timeSinceLastTrade = Date.now() - this.lastTradeTime;
                if (timeSinceLastTrade < this.minTradeInterval) {
                    await sleep(this.minTradeInterval - timeSinceLastTrade);
                }
            }

            logger.info(`Executing ${direction} trade: ${asset}, Amount: ${amount}, Duration: ${duration}min`);

            // Select asset
            await this.selectAsset(asset);

            // Set amount
            await this.setAmount(amount);

            // Set duration
            await this.setDuration(duration);

            // Click trade button based on direction
            let buttonSelector;
            if (direction === 'CALL') {
                buttonSelector = '.btn-call, .call-button, [data-qa="call-btn"], .up-button, .green-button';
            } else {
                buttonSelector = '.btn-put, .put-button, [data-qa="put-btn"], .down-button, .red-button';
            }

            const tradeButton = await this.page.$(buttonSelector);

            if (tradeButton) {
                // Check if button is enabled
                const isDisabled = await this.page.evaluate(btn => btn.disabled, tradeButton);
                if (isDisabled) {
                    logger.warn('Trade button is disabled');
                    return { success: false, error: 'Trade button disabled' };
                }

                await tradeButton.click();
                this.lastTradeTime = Date.now();

                await sleep(2000);

                logger.info(`${direction} trade executed successfully`);

                return {
                    success: true,
                    direction,
                    amount,
                    asset,
                    duration,
                    timestamp: new Date().toISOString()
                };
            } else {
                logger.error('Trade button not found');
                return { success: false, error: 'Trade button not found' };
            }
        } catch (error) {
            logger.error('Trade execution error:', error);
            return { success: false, error: error.message };
        }
    }

    /**
     * Get current account balance
     */
    async getBalance() {
        try {
            const balanceSelector = '.balance-value, .account-balance, [data-qa="balance"]';
            const balanceElement = await this.page.$(balanceSelector);

            if (balanceElement) {
                const balanceText = await this.page.evaluate(el => el.textContent, balanceElement);
                const balance = parseFloat(balanceText.replace(/[^0-9.-]+/g, ''));
                return balance;
            }

            return null;
        } catch (error) {
            logger.error('Failed to get balance:', error);
            return null;
        }
    }

    /**
     * Get open trades/positions
     */
    async getOpenTrades() {
        try {
            const trades = [];
            const tradeSelector = '.open-trade, .active-position, [data-qa="open-trade"]';
            const tradeElements = await this.page.$$(tradeSelector);

            for (const element of tradeElements) {
                const trade = await this.page.evaluate(el => {
                    return {
                        asset: el.querySelector('.trade-asset')?.textContent || '',
                        direction: el.querySelector('.trade-direction')?.textContent || '',
                        amount: el.querySelector('.trade-amount')?.textContent || '',
                        expiry: el.querySelector('.trade-expiry')?.textContent || ''
                    };
                }, element);
                trades.push(trade);
            }

            return trades;
        } catch (error) {
            logger.error('Failed to get open trades:', error);
            return [];
        }
    }

    /**
     * Check if trading platform is ready
     */
    async isPlatformReady() {
        try {
            // Check for trading buttons
            const callButton = await this.page.$('.btn-call, .call-button, [data-qa="call-btn"]');
            const putButton = await this.page.$('.btn-put, .put-button, [data-qa="put-btn"]');

            return callButton !== null && putButton !== null;
        } catch (error) {
            return false;
        }
    }

    /**
     * Take screenshot for debugging
     */
    async takeScreenshot(filename) {
        try {
            const path = `./screenshots/${filename}_${Date.now()}.png`;
            await this.page.screenshot({ path, fullPage: true });
            logger.info(`Screenshot saved: ${path}`);
            return path;
        } catch (error) {
            logger.error('Failed to take screenshot:', error);
            return null;
        }
    }

    /**
     * Close browser and cleanup
     */
    async close() {
        try {
            if (this.browser) {
                await this.browser.close();
                this.browser = null;
                this.page = null;
                this.isLoggedIn = false;
                logger.info('Trade Executor closed');
            }
        } catch (error) {
            logger.error('Error closing browser:', error);
        }
    }

    /**
     * Check connection status
     */
    isConnected() {
        return this.browser !== null && this.page !== null && this.isLoggedIn;
    }

    /**
     * Reconnect if disconnected
     */
    async reconnect(email, password, demoMode = true) {
        await this.close();
        return await this.initialize(email, password, demoMode);
    }
}

module.exports = TradeExecutor;
