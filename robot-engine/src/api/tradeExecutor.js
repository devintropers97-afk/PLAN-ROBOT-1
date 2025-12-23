/**
 * Trade Executor - Executes trades on OlympTrade
 *
 * Uses session persistence, rate limiting, and captcha handling
 */

require('dotenv').config();
const puppeteer = require('puppeteer');
const path = require('path');
const SessionManager = require('../utils/sessionManager');
const CaptchaHandler = require('../utils/captchaHandler');
const RateLimiter = require('../utils/rateLimiter');

class TradeExecutor {
    constructor() {
        this.sessionManager = new SessionManager();
        this.captchaHandler = new CaptchaHandler();
        this.rateLimiter = new RateLimiter();
    }

    /**
     * Get browser launch options
     */
    getBrowserOptions(trader) {
        const sessionDir = this.sessionManager.getSessionDir(trader.email);

        const options = {
            headless: 'new',
            userDataDir: sessionDir,
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
        };

        if (process.env.PUPPETEER_EXECUTABLE_PATH) {
            options.executablePath = process.env.PUPPETEER_EXECUTABLE_PATH;
        }

        return options;
    }

    /**
     * Setup page with realistic settings
     */
    async setupPage(browser) {
        const page = await browser.newPage();

        await page.setUserAgent(
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        );

        // Block unnecessary resources for faster loading
        await page.setRequestInterception(true);
        page.on('request', (req) => {
            const resourceType = req.resourceType();
            if (['image', 'font', 'media'].includes(resourceType)) {
                req.abort();
            } else {
                req.continue();
            }
        });

        return page;
    }

    /**
     * Check if already logged in
     */
    async isLoggedIn(page) {
        try {
            await page.goto('https://olymptrade.com/platform', {
                waitUntil: 'domcontentloaded',
                timeout: 30000
            });

            await this.sleep(3000);

            const url = page.url();
            return url.includes('platform') || url.includes('trading');
        } catch {
            return false;
        }
    }

    /**
     * Perform login
     */
    async login(page, trader) {
        console.log(`[Executor] Logging in: ${trader.email}`);

        // Record login attempt
        this.sessionManager.recordLogin(trader.email, false);

        // Navigate to login
        await page.goto('https://olymptrade.com/login', {
            waitUntil: 'domcontentloaded',
            timeout: 60000
        });

        await this.sleep(3000);

        // Human-like behavior before login
        await this.captchaHandler.prepareForLogin(page);

        // Check for captcha
        const captchaResult = await this.captchaHandler.handleCaptcha(page);
        if (captchaResult.present && !captchaResult.handled) {
            throw new Error(`Captcha detected: ${captchaResult.message}`);
        }

        // Find and click login tab
        await page.evaluate(() => {
            const buttons = document.querySelectorAll('button, a, [role="tab"], span, div');
            for (const btn of buttons) {
                const text = btn.textContent.trim().toLowerCase();
                if (text === 'masuk' || text === 'login' || text === 'sign in') {
                    btn.click();
                    break;
                }
            }
        });

        await this.sleep(2000);

        // Find email input
        const emailInput = await this.findVisibleElement(page, [
            'input[type="email"]',
            'input[name="email"]',
            'input[placeholder*="email" i]',
            'input[autocomplete="email"]'
        ]);

        if (!emailInput) {
            throw new Error('Email input not found');
        }

        // Type credentials with human-like delays
        await emailInput.click({ clickCount: 3 });
        await this.typeHumanLike(page, trader.email);

        await this.sleep(500);

        const passwordInput = await page.$('input[type="password"]');
        if (!passwordInput) {
            throw new Error('Password input not found');
        }

        await passwordInput.click();
        await this.typeHumanLike(page, trader.password);

        await this.sleep(500);

        // Submit
        await page.evaluate(() => {
            const buttons = document.querySelectorAll('button');
            for (const btn of buttons) {
                const text = btn.textContent.trim().toLowerCase();
                if (text === 'masuk' || text.includes('login') || text.includes('sign in')) {
                    btn.click();
                    return true;
                }
            }
            return false;
        });

        // Wait for login to complete
        await this.sleep(10000);

        // Verify login
        const currentUrl = page.url();
        const isLoggedIn = currentUrl.includes('platform') || currentUrl.includes('trading');

        // Update session
        this.sessionManager.recordLogin(trader.email, isLoggedIn);
        this.sessionManager.updateSession(trader.email, { isLoggedIn });

        if (!isLoggedIn) {
            throw new Error('Login failed - could not access platform');
        }

        console.log(`[Executor] Login successful: ${trader.email}`);
        return true;
    }

    /**
     * Select account type (Demo/Real)
     */
    async selectAccount(page, isDemo) {
        const accountType = isDemo ? 'demo' : 'real';
        console.log(`[Executor] Selecting ${accountType} account`);

        await this.sleep(2000);

        const targetTexts = isDemo
            ? ['Akun demo', 'Demo', 'demo', 'Practice']
            : ['Akun real', 'Real', 'real', 'Live'];

        for (const text of targetTexts) {
            const clicked = await page.evaluate((searchText) => {
                const elements = document.querySelectorAll('button, a, div, span, [role="button"]');
                for (const el of elements) {
                    if (el.textContent.toLowerCase().includes(searchText.toLowerCase())) {
                        const rect = el.getBoundingClientRect();
                        if (rect.width > 0 && rect.height > 0) {
                            el.click();
                            return true;
                        }
                    }
                }
                return false;
            }, text);

            if (clicked) {
                await this.sleep(2000);
                return true;
            }
        }

        console.log('[Executor] Account selector not found, assuming already on correct account');
        return true;
    }

    /**
     * Execute a trade
     */
    async executeTrade(trader, trade) {
        let browser = null;
        let page = null;

        try {
            console.log(`[Executor] Starting trade execution for ${trader.email}`);

            // Rate limiting
            await this.rateLimiter.waitForRateLimit();

            // Launch browser
            browser = await puppeteer.launch(this.getBrowserOptions(trader));
            page = await this.setupPage(browser);

            // Check if already logged in (session persistence)
            const hasSession = await this.isLoggedIn(page);

            if (!hasSession) {
                await this.login(page, trader);
            } else {
                console.log('[Executor] Using existing session');
            }

            // Select account
            await this.selectAccount(page, trader.isDemo);

            // Wait for platform to fully load
            await this.sleep(5000);

            // Set amount
            await this.setAmount(page, trade.amount);

            // Execute trade
            const result = await this.clickTradeButton(page, trade.direction);

            if (!result.success) {
                throw new Error(result.error || 'Trade button not found');
            }

            // Get balance after trade
            const balance = await this.getBalanceFromPage(page);

            return {
                success: true,
                direction: trade.direction,
                amount: trade.amount,
                balance,
                timestamp: new Date().toISOString()
            };

        } catch (error) {
            console.error(`[Executor] Trade error: ${error.message}`);
            throw error;
        } finally {
            if (browser) {
                await browser.close();
            }
        }
    }

    /**
     * Test login (validates credentials)
     */
    async testLogin(trader) {
        let browser = null;
        let page = null;

        try {
            console.log(`[Executor] Testing login for ${trader.email}`);

            browser = await puppeteer.launch(this.getBrowserOptions(trader));
            page = await this.setupPage(browser);

            // Check existing session first
            const hasSession = await this.isLoggedIn(page);

            if (hasSession) {
                return {
                    success: true,
                    message: 'Already logged in (session valid)',
                    hasSession: true
                };
            }

            // Try to login
            await this.login(page, trader);

            return {
                success: true,
                message: 'Login successful',
                hasSession: false
            };

        } catch (error) {
            throw error;
        } finally {
            if (browser) {
                await browser.close();
            }
        }
    }

    /**
     * Get balance
     */
    async getBalance(trader) {
        let browser = null;
        let page = null;

        try {
            browser = await puppeteer.launch(this.getBrowserOptions(trader));
            page = await this.setupPage(browser);

            const hasSession = await this.isLoggedIn(page);

            if (!hasSession) {
                await this.login(page, trader);
            }

            await this.selectAccount(page, trader.isDemo);
            await this.sleep(3000);

            const balance = await this.getBalanceFromPage(page);

            return {
                success: true,
                balance,
                accountType: trader.isDemo ? 'demo' : 'real'
            };

        } catch (error) {
            throw error;
        } finally {
            if (browser) {
                await browser.close();
            }
        }
    }

    /**
     * Set trade amount
     */
    async setAmount(page, amount) {
        const amountSelectors = [
            '.amount-input input',
            'input[data-qa="amount"]',
            '.trade-amount input',
            'input[name="amount"]',
            '.deal-amount input'
        ];

        for (const selector of amountSelectors) {
            const input = await page.$(selector);
            if (input) {
                await input.click({ clickCount: 3 });
                await this.sleep(200);
                await page.keyboard.type(amount.toString());
                return true;
            }
        }

        return false;
    }

    /**
     * Click trade button
     */
    async clickTradeButton(page, direction) {
        const isCall = direction.toUpperCase() === 'CALL';

        const selectors = isCall ? [
            'button.deal-button_up',
            '.deal-button_up',
            'button[class*="deal-button"][class*="up"]',
            'button[class*="call"]',
            'button[class*="green"]'
        ] : [
            'button.deal-button_down',
            '.deal-button_down',
            'button[class*="deal-button"][class*="down"]',
            'button[class*="put"]',
            'button[class*="red"]'
        ];

        const buttonTexts = isCall
            ? ['Naik', 'Up', 'Call', 'Higher']
            : ['Turun', 'Down', 'Put', 'Lower'];

        // Try by selector first
        for (const selector of selectors) {
            try {
                const btn = await page.$(selector);
                if (btn) {
                    const isDisabled = await page.evaluate(el => el.disabled, btn);
                    if (!isDisabled) {
                        await btn.click();
                        await this.sleep(2000);
                        return { success: true };
                    }
                }
            } catch {}
        }

        // Try by text
        for (const text of buttonTexts) {
            const clicked = await page.evaluate((searchText) => {
                const buttons = document.querySelectorAll('button');
                for (const btn of buttons) {
                    if (btn.textContent.toLowerCase().includes(searchText.toLowerCase())) {
                        btn.click();
                        return true;
                    }
                }
                return false;
            }, text);

            if (clicked) {
                await this.sleep(2000);
                return { success: true };
            }
        }

        return { success: false, error: 'Trade button not found' };
    }

    /**
     * Get balance from page
     */
    async getBalanceFromPage(page) {
        const balanceSelectors = [
            '.balance-value',
            '.account-balance',
            '[data-qa="balance"]',
            '.balance-amount',
            '.balance'
        ];

        for (const selector of balanceSelectors) {
            try {
                const element = await page.$(selector);
                if (element) {
                    const text = await page.evaluate(el => el.textContent, element);
                    const balance = parseFloat(text.replace(/[^0-9.-]+/g, ''));
                    if (!isNaN(balance)) {
                        return balance;
                    }
                }
            } catch {}
        }

        return null;
    }

    /**
     * Find first visible element matching selectors
     */
    async findVisibleElement(page, selectors) {
        for (const selector of selectors) {
            try {
                const elements = await page.$$(selector);
                for (const element of elements) {
                    const isVisible = await page.evaluate(el => {
                        const rect = el.getBoundingClientRect();
                        return rect.width > 0 && rect.height > 0 && el.offsetParent !== null;
                    }, element);

                    if (isVisible) {
                        return element;
                    }
                }
            } catch {}
        }
        return null;
    }

    /**
     * Type with human-like delays
     */
    async typeHumanLike(page, text) {
        for (const char of text) {
            await page.keyboard.type(char);
            await this.sleep(this.rateLimiter.getTypingDelay());
        }
    }

    /**
     * Sleep utility
     */
    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
}

module.exports = TradeExecutor;
