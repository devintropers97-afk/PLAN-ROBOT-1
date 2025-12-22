/**
 * =============================================
 * ZYN TRADE ROBOT - OLYMPTRADE DIRECT TEST
 * =============================================
 *
 * Script untuk test koneksi langsung ke OlympTrade
 * Tanpa perlu database - langsung test login & trading
 *
 * Usage:
 *   node src/test-olymptrade.js --email=your@email.com --password=yourpass
 *   node src/test-olymptrade.js --email=your@email.com --password=yourpass --demo
 *   node src/test-olymptrade.js --email=your@email.com --password=yourpass --real --trade
 *
 * Options:
 *   --email     : OlympTrade email
 *   --password  : OlympTrade password
 *   --demo      : Use demo account (default)
 *   --real      : Use real account
 *   --trade     : Execute test trade (CALL with minimum amount)
 *   --visible   : Show browser window (not headless)
 *   --screenshot: Take screenshots at each step
 */

require('dotenv').config();
const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

// Parse command line arguments
function parseArgs() {
    const args = {
        email: process.env.OLYMPTRADE_EMAIL || '',
        password: process.env.OLYMPTRADE_PASSWORD || '',
        isDemo: true,
        executeTrade: false,
        visible: false,
        screenshot: true
    };

    process.argv.forEach(arg => {
        if (arg.startsWith('--email=')) {
            args.email = arg.split('=')[1];
        } else if (arg.startsWith('--password=')) {
            args.password = arg.split('=')[1];
        } else if (arg === '--real') {
            args.isDemo = false;
        } else if (arg === '--demo') {
            args.isDemo = true;
        } else if (arg === '--trade') {
            args.executeTrade = true;
        } else if (arg === '--visible') {
            args.visible = true;
        } else if (arg === '--no-screenshot') {
            args.screenshot = false;
        }
    });

    return args;
}

// Create screenshots directory
function ensureScreenshotDir() {
    const dir = path.join(__dirname, '../logs/screenshots');
    if (!fs.existsSync(dir)) {
        fs.mkdirSync(dir, { recursive: true });
    }
    return dir;
}

// Sleep utility
const sleep = (ms) => new Promise(resolve => setTimeout(resolve, ms));

// Random delay for human-like typing
const randomDelay = (min, max) => Math.floor(Math.random() * (max - min + 1)) + min;

class OlympTradeTest {
    constructor(args) {
        this.args = args;
        this.browser = null;
        this.page = null;
        this.screenshotDir = ensureScreenshotDir();
        this.stepCount = 0;
    }

    log(message, type = 'info') {
        const timestamp = new Date().toLocaleTimeString();
        const prefix = {
            'info': '   ',
            'success': ' ✅',
            'error': ' ❌',
            'warn': ' ⚠️',
            'step': ' ▶️'
        }[type] || '   ';
        console.log(`${prefix} [${timestamp}] ${message}`);
    }

    async screenshot(name) {
        if (!this.args.screenshot || !this.page) return null;
        try {
            this.stepCount++;
            const filename = path.join(this.screenshotDir, `${this.stepCount.toString().padStart(2, '0')}_${name}_${Date.now()}.png`);
            await this.page.screenshot({ path: filename, fullPage: false });
            this.log(`Screenshot: ${path.basename(filename)}`, 'info');
            return filename;
        } catch (e) {
            this.log(`Screenshot failed: ${e.message}`, 'warn');
            return null;
        }
    }

    async initialize() {
        this.log('Launching browser...', 'step');

        const browserOptions = {
            headless: this.args.visible ? false : 'new',
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

        // Check for custom executable path
        if (process.env.PUPPETEER_EXECUTABLE_PATH) {
            browserOptions.executablePath = process.env.PUPPETEER_EXECUTABLE_PATH;
        }

        this.browser = await puppeteer.launch(browserOptions);
        this.page = await this.browser.newPage();

        // Set realistic user agent
        await this.page.setUserAgent(
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        );

        // Enable console log from page
        this.page.on('console', msg => {
            if (msg.type() === 'error') {
                this.log(`Browser console error: ${msg.text()}`, 'warn');
            }
        });

        this.log('Browser launched successfully', 'success');
        return true;
    }

    async navigateToOlympTrade() {
        this.log('Navigating to OlympTrade platform...', 'step');

        try {
            // Go directly to login page
            this.log('Loading OlympTrade login page...', 'info');
            await this.page.goto('https://olymptrade.com/login', {
                waitUntil: 'networkidle2',
                timeout: 60000
            });

            await sleep(3000);
            await this.screenshot('01_login_page');

            // Check if geo-blocked
            const pageContent = await this.page.evaluate(() => document.body.innerText.toLowerCase());
            if (pageContent.includes('registration unavailable') ||
                pageContent.includes('not available for clients from your region') ||
                pageContent.includes('unavailable in your region')) {
                throw new Error('OlympTrade is geo-blocked from this IP. Try using a VPN.');
            }

            const title = await this.page.title();
            this.log(`Page loaded: ${title}`, 'success');

            return true;
        } catch (error) {
            this.log(`Navigation failed: ${error.message}`, 'error');
            await this.screenshot('error_navigation');
            return false;
        }
    }

    async checkIfLoggedIn() {
        this.log('Checking if already logged in...', 'step');

        try {
            // Look for trading buttons which indicate logged in state
            const selectors = [
                '.btn-call',
                '.call-button',
                '[data-qa="call-btn"]',
                'button.call',
                '.trading-panel',
                '.deal-button--call',
                '.deal-button--put'
            ];

            for (const selector of selectors) {
                const element = await this.page.$(selector);
                if (element) {
                    this.log('Already logged in - trading panel found', 'success');
                    return true;
                }
            }

            return false;
        } catch {
            return false;
        }
    }

    async login() {
        this.log(`Attempting login with: ${this.args.email}`, 'step');

        try {
            await sleep(2000);

            const currentUrl = this.page.url();
            this.log(`Current URL: ${currentUrl}`, 'info');

            // Click Login button to open modal (required on /login page)
            this.log('Clicking Login button to open form...', 'info');
            await this.page.evaluate(() => {
                document.querySelectorAll('button').forEach(b => {
                    if (b.textContent.trim() === 'Login') b.click();
                });
            });

            // Wait for modal to appear
            this.log('Waiting for login form...', 'info');
            await sleep(5000);

            // Find email input - try multiple selectors
            let emailInput = null;
            const emailSelectors = [
                'input[type="email"]',
                'input[name="email"]',
                'input[placeholder*="email" i]',
                'input[placeholder*="E-mail" i]',
                'input[autocomplete="email"]'
            ];

            for (const selector of emailSelectors) {
                try {
                    emailInput = await this.page.$(selector);
                    if (emailInput) {
                        this.log(`Found email input: ${selector}`, 'info');
                        break;
                    }
                } catch {}
            }

            // If not found by selector, scan all visible inputs
            if (!emailInput) {
                this.log('Scanning all inputs...', 'info');
                const inputs = await this.page.$$('input');
                for (const input of inputs) {
                    try {
                        const info = await this.page.evaluate(el => ({
                            type: el.type,
                            visible: el.offsetParent !== null
                        }), input);

                        if (info.visible && (info.type === 'email' || info.type === 'text')) {
                            emailInput = input;
                            this.log(`Found input by scanning: type=${info.type}`, 'info');
                            break;
                        }
                    } catch {}
                }
            }

            if (!emailInput) {
                await this.screenshot('error_no_email');
                throw new Error('Email input not found');
            }

            // Type email
            await emailInput.click({ clickCount: 3 });
            await emailInput.type(this.args.email, { delay: 50 });
            this.log('Email entered', 'success');

            await sleep(500);

            // Find password input
            const passwordInput = await this.page.$('input[type="password"]');
            if (!passwordInput) {
                throw new Error('Password input not found');
            }

            await passwordInput.type(this.args.password, { delay: 50 });
            this.log('Password entered', 'success');

            await this.screenshot('03_credentials_entered');

            // Submit - try button first, then Enter
            let submitted = false;

            // Try clicking submit button by text
            const clicked = await this.page.evaluate(() => {
                const buttons = document.querySelectorAll('button');
                for (const btn of buttons) {
                    const text = btn.textContent.toLowerCase();
                    if (text.includes('log in') || text.includes('login') || text.includes('sign in')) {
                        btn.click();
                        return true;
                    }
                }
                return false;
            });

            if (clicked) {
                submitted = true;
                this.log('Clicked submit button', 'success');
            } else {
                await this.page.keyboard.press('Enter');
                this.log('Pressed Enter to submit', 'info');
                submitted = true;
            }

            // Wait for login
            this.log('Waiting for login to complete...', 'info');
            await sleep(8000);

            await this.screenshot('04_after_login');

            // Check if logged in
            const isLoggedIn = await this.checkIfLoggedIn();

            if (isLoggedIn) {
                this.log('Login successful!', 'success');
                return true;
            }

            // Check URL for success indicators
            const newUrl = this.page.url();
            if (newUrl.includes('platform') || newUrl.includes('trading') || newUrl.includes('trade')) {
                this.log('Login successful - redirected to platform!', 'success');
                return true;
            }

            // Check for error messages
            const errorText = await this.page.evaluate(() => {
                const errorEl = document.querySelector('.error, .error-message, [class*="error"]');
                return errorEl ? errorEl.textContent : null;
            });

            if (errorText) {
                this.log(`Login error: ${errorText}`, 'error');
            }

            return false;
        } catch (error) {
            this.log(`Login error: ${error.message}`, 'error');
            await this.screenshot('error_login');
            return false;
        }
    }

    async switchAccount() {
        const accountType = this.args.isDemo ? 'DEMO' : 'REAL';
        this.log(`Switching to ${accountType} account...`, 'step');

        try {
            await sleep(2000);

            // OlympTrade account switcher selectors
            const switcherSelectors = [
                '.account-switcher',
                '.balance-selector',
                '[data-qa="account-type"]',
                '.account-type-switch',
                '.account-balance',
                '.balance-block',
                '.account-info'
            ];

            for (const selector of switcherSelectors) {
                const switcher = await this.page.$(selector);
                if (switcher) {
                    await switcher.click();
                    await sleep(1500);
                    this.log(`Clicked account switcher: ${selector}`, 'info');

                    await this.screenshot('06_account_switcher');

                    // Select demo or real
                    const optionSelectors = this.args.isDemo ? [
                        '.demo-account',
                        '[data-type="demo"]',
                        '.practice-account',
                        '[data-qa="demo-account"]',
                        'button:contains("Demo")',
                        'div:contains("Demo")'
                    ] : [
                        '.real-account',
                        '[data-type="real"]',
                        '.live-account',
                        '[data-qa="real-account"]',
                        'button:contains("Real")',
                        'div:contains("Real")'
                    ];

                    for (const optSelector of optionSelectors) {
                        const option = await this.page.$(optSelector);
                        if (option) {
                            await option.click();
                            await sleep(2000);
                            this.log(`Switched to ${accountType} account`, 'success');
                            await this.screenshot('07_account_switched');
                            return true;
                        }
                    }
                }
            }

            this.log('Account switcher not found, may already be on correct account', 'warn');
            return true;
        } catch (error) {
            this.log(`Account switch error: ${error.message}`, 'warn');
            return false;
        }
    }

    async getBalance() {
        this.log('Getting current balance...', 'step');

        try {
            const balanceSelectors = [
                '.balance-value',
                '.account-balance',
                '[data-qa="balance"]',
                '.balance-amount',
                '.balance',
                '.account-value'
            ];

            for (const selector of balanceSelectors) {
                const element = await this.page.$(selector);
                if (element) {
                    const text = await this.page.evaluate(el => el.textContent, element);
                    const balance = parseFloat(text.replace(/[^0-9.-]+/g, ''));
                    if (!isNaN(balance)) {
                        this.log(`Current balance: $${balance.toFixed(2)}`, 'success');
                        return balance;
                    }
                }
            }

            this.log('Could not get balance', 'warn');
            return null;
        } catch (error) {
            this.log(`Balance error: ${error.message}`, 'warn');
            return null;
        }
    }

    async selectAsset(asset = 'EUR/USD') {
        this.log(`Selecting asset: ${asset}...`, 'step');

        try {
            const assetSelectors = [
                '.asset-selector',
                '.instrument-selector',
                '[data-qa="asset-select"]',
                '.asset-name',
                '.instrument-name'
            ];

            for (const selector of assetSelectors) {
                const btn = await this.page.$(selector);
                if (btn) {
                    await btn.click();
                    await sleep(1500);

                    await this.screenshot('08_asset_selector');

                    // Search for asset
                    const searchSelectors = [
                        '.asset-search input',
                        '.search-instrument input',
                        'input[placeholder*="search" i]',
                        'input[type="search"]'
                    ];

                    for (const searchSel of searchSelectors) {
                        const searchInput = await this.page.$(searchSel);
                        if (searchInput) {
                            await searchInput.click({ clickCount: 3 });
                            await searchInput.type(asset.replace('/', ''), { delay: 50 });
                            await sleep(1500);
                            break;
                        }
                    }

                    // Click first result
                    const resultSelectors = [
                        '.asset-item',
                        '.instrument-item',
                        '[data-asset]',
                        '.search-result'
                    ];

                    for (const resultSel of resultSelectors) {
                        const result = await this.page.$(resultSel);
                        if (result) {
                            await result.click();
                            await sleep(1500);
                            this.log(`Asset selected: ${asset}`, 'success');
                            return true;
                        }
                    }
                }
            }

            this.log('Asset selector not found, using default', 'warn');
            return true;
        } catch (error) {
            this.log(`Asset select error: ${error.message}`, 'warn');
            return false;
        }
    }

    async setAmount(amount = 1) {
        this.log(`Setting trade amount: $${amount}...`, 'step');

        try {
            const amountSelectors = [
                '.amount-input input',
                'input[data-qa="amount"]',
                '.trade-amount input',
                'input[name="amount"]',
                '.amount input',
                '.deal-amount input'
            ];

            for (const selector of amountSelectors) {
                const input = await this.page.$(selector);
                if (input) {
                    await input.click({ clickCount: 3 });
                    await sleep(200);
                    await input.type(amount.toString(), { delay: 30 });
                    this.log(`Amount set: $${amount}`, 'success');
                    return true;
                }
            }

            this.log('Amount input not found', 'warn');
            return false;
        } catch (error) {
            this.log(`Amount error: ${error.message}`, 'warn');
            return false;
        }
    }

    async setDuration(minutes = 1) {
        this.log(`Setting duration: ${minutes} minute(s)...`, 'step');

        try {
            const durationSelectors = [
                '.duration-selector',
                '.expiry-time',
                '[data-qa="time-select"]',
                '.time-selector',
                '.expiration'
            ];

            for (const selector of durationSelectors) {
                const btn = await this.page.$(selector);
                if (btn) {
                    await btn.click();
                    await sleep(1000);

                    // Select duration option
                    const option = await this.page.$(`[data-duration="${minutes}"], .time-option`);
                    if (option) {
                        await option.click();
                        await sleep(500);
                        this.log(`Duration set: ${minutes} minute(s)`, 'success');
                        return true;
                    }
                }
            }

            this.log('Duration selector not found, using default', 'warn');
            return true;
        } catch (error) {
            this.log(`Duration error: ${error.message}`, 'warn');
            return false;
        }
    }

    async executeTrade(direction = 'CALL') {
        this.log(`Executing ${direction} trade...`, 'step');

        try {
            await this.screenshot('09_before_trade');

            // OlympTrade button selectors (Indonesian: Naik=Up, Turun=Down)
            const callSelectors = [
                'button.deal-button_up',
                '.deal-button_up',
                'button[class*="deal-button_up"]',
                'button[class*="deal-button"][class*="up"]'
            ];

            const putSelectors = [
                'button.deal-button_down',
                '.deal-button_down',
                'button[class*="deal-button_down"]',
                'button[class*="deal-button"][class*="down"]'
            ];

            const selectors = direction.toUpperCase() === 'CALL' ? callSelectors : putSelectors;
            const buttonName = direction.toUpperCase() === 'CALL' ? 'Naik' : 'Turun';

            for (const selector of selectors) {
                const btn = await this.page.$(selector);
                if (btn) {
                    // Check if button is enabled
                    const isDisabled = await this.page.evaluate(el => el.disabled, btn);
                    if (isDisabled) {
                        this.log(`Trade button (${buttonName}) is disabled`, 'warn');
                        continue;
                    }

                    await btn.click();
                    this.log(`Clicked ${buttonName} (${direction}) button: ${selector}`, 'success');

                    await sleep(3000);
                    await this.screenshot('10_after_trade');

                    this.log(`Trade executed: ${direction}`, 'success');
                    return {
                        success: true,
                        direction: direction,
                        timestamp: new Date().toISOString()
                    };
                }
            }

            // Try by text content as fallback
            this.log('Trying to find button by text...', 'info');
            const clicked = await this.page.evaluate((btnText) => {
                const buttons = document.querySelectorAll('button');
                for (const btn of buttons) {
                    if (btn.textContent.includes(btnText)) {
                        btn.click();
                        return true;
                    }
                }
                return false;
            }, buttonName);

            if (clicked) {
                this.log(`Clicked ${buttonName} button by text`, 'success');
                await sleep(3000);
                await this.screenshot('10_after_trade');
                return {
                    success: true,
                    direction: direction,
                    timestamp: new Date().toISOString()
                };
            }

            this.log('Trade button not found', 'error');
            return { success: false, error: 'Trade button not found' };
        } catch (error) {
            this.log(`Trade error: ${error.message}`, 'error');
            await this.screenshot('error_trade');
            return { success: false, error: error.message };
        }
    }

    async close() {
        if (this.browser) {
            await this.browser.close();
            this.log('Browser closed', 'info');
        }
    }

    async runTest() {
        console.log('\n');
        console.log('═══════════════════════════════════════════════════════════');
        console.log('   ZYN TRADE ROBOT - OLYMPTRADE DIRECT TEST');
        console.log('═══════════════════════════════════════════════════════════');
        console.log(`   Email: ${this.args.email}`);
        console.log(`   Account: ${this.args.isDemo ? 'DEMO' : 'REAL'}`);
        console.log(`   Execute Trade: ${this.args.executeTrade ? 'YES' : 'NO'}`);
        console.log(`   Browser: ${this.args.visible ? 'VISIBLE' : 'HEADLESS'}`);
        console.log('═══════════════════════════════════════════════════════════');
        console.log('');

        if (!this.args.email || !this.args.password) {
            console.log(' ❌ Email and password required!');
            console.log('');
            console.log('   Usage:');
            console.log('   node src/test-olymptrade.js --email=your@email.com --password=yourpass');
            console.log('   node src/test-olymptrade.js --email=your@email.com --password=yourpass --trade');
            console.log('   node src/test-olymptrade.js --email=your@email.com --password=yourpass --visible');
            console.log('');
            console.log('   Or set in .env file:');
            console.log('   OLYMPTRADE_EMAIL=your@email.com');
            console.log('   OLYMPTRADE_PASSWORD=yourpassword');
            console.log('');
            process.exit(1);
        }

        const results = {
            browser: false,
            navigation: false,
            login: false,
            accountSwitch: false,
            balance: null,
            trade: null
        };

        try {
            // Step 1: Initialize browser
            results.browser = await this.initialize();
            if (!results.browser) throw new Error('Browser init failed');

            // Step 2: Navigate to OlympTrade
            results.navigation = await this.navigateToOlympTrade();
            if (!results.navigation) throw new Error('Navigation failed');

            // Step 3: Check if already logged in
            let alreadyLoggedIn = await this.checkIfLoggedIn();

            // Step 4: Login if needed
            if (!alreadyLoggedIn) {
                results.login = await this.login();
                if (!results.login) throw new Error('Login failed');
            } else {
                results.login = true;
            }

            // Step 5: Switch account
            results.accountSwitch = await this.switchAccount();

            // Step 6: Get balance
            results.balance = await this.getBalance();

            // Step 7: Execute trade if requested
            if (this.args.executeTrade) {
                // Set up trade
                await this.selectAsset('EUR/USD');
                await this.setAmount(1); // Minimum amount
                await this.setDuration(1); // 1 minute

                // Execute CALL trade
                results.trade = await this.executeTrade('CALL');
            }

            // Final screenshot
            await this.screenshot('11_final_state');

        } catch (error) {
            this.log(`Test failed: ${error.message}`, 'error');
        } finally {
            // Keep browser open if visible mode and test passed
            if (!this.args.visible || !results.login) {
                await this.close();
            } else {
                console.log('\n   Browser left open for inspection.');
                console.log('   Press Ctrl+C to close.\n');
            }
        }

        // Print results
        console.log('');
        console.log('═══════════════════════════════════════════════════════════');
        console.log('   TEST RESULTS');
        console.log('═══════════════════════════════════════════════════════════');
        console.log(`   Browser:        ${results.browser ? '✅ PASS' : '❌ FAIL'}`);
        console.log(`   Navigation:     ${results.navigation ? '✅ PASS' : '❌ FAIL'}`);
        console.log(`   Login:          ${results.login ? '✅ PASS' : '❌ FAIL'}`);
        console.log(`   Account Switch: ${results.accountSwitch ? '✅ PASS' : '⚠️ SKIPPED'}`);
        console.log(`   Balance:        ${results.balance !== null ? `✅ $${results.balance}` : '⚠️ N/A'}`);
        if (this.args.executeTrade) {
            console.log(`   Trade:          ${results.trade?.success ? '✅ PASS' : '❌ FAIL'}`);
        }
        console.log('═══════════════════════════════════════════════════════════');

        if (this.args.screenshot) {
            console.log(`\n   Screenshots saved to: ${this.screenshotDir}`);
        }

        if (results.login && results.balance !== null) {
            console.log('\n   🎉 Robot is ready to trade on OlympTrade!');
            console.log('   Run: npm start (to start full robot)');
        } else {
            console.log('\n   ⚠️ Some tests failed. Check the issues above.');
        }

        console.log('');

        process.exit(results.login ? 0 : 1);
    }
}

// Run test
const args = parseArgs();
const test = new OlympTradeTest(args);
test.runTest();
