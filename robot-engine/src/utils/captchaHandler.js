/**
 * Captcha Handler - Strategies to avoid and handle captcha
 *
 * OlympTrade uses captcha when:
 * 1. Too many login attempts
 * 2. Suspicious browser behavior
 * 3. New device/IP
 *
 * Strategies:
 * 1. Session persistence (avoid re-login)
 * 2. Human-like behavior simulation
 * 3. Rate limiting
 * 4. Optional: 2Captcha/Anti-Captcha service integration
 */

const RateLimiter = require('./rateLimiter');

class CaptchaHandler {
    constructor(options = {}) {
        this.rateLimiter = new RateLimiter(options);
        this.twoCaptchaKey = options.twoCaptchaKey || process.env.TWOCAPTCHA_API_KEY;
        this.antiCaptchaKey = options.antiCaptchaKey || process.env.ANTICAPTCHA_API_KEY;

        // Behavior settings
        this.mouseMovement = options.mouseMovement !== false;
        this.randomScrolling = options.randomScrolling !== false;
    }

    /**
     * Simulate human-like mouse movement
     */
    async simulateMouseMovement(page) {
        if (!this.mouseMovement) return;

        try {
            const viewport = page.viewport();
            const steps = Math.floor(Math.random() * 5) + 3;

            for (let i = 0; i < steps; i++) {
                const x = Math.floor(Math.random() * (viewport.width - 100)) + 50;
                const y = Math.floor(Math.random() * (viewport.height - 100)) + 50;

                await page.mouse.move(x, y, { steps: 10 });
                await this.sleep(Math.random() * 200 + 100);
            }
        } catch (e) {
            // Ignore mouse movement errors
        }
    }

    /**
     * Simulate random scrolling
     */
    async simulateScrolling(page) {
        if (!this.randomScrolling) return;

        try {
            const scrolls = Math.floor(Math.random() * 3) + 1;

            for (let i = 0; i < scrolls; i++) {
                const distance = Math.floor(Math.random() * 300) + 100;
                const direction = Math.random() > 0.5 ? 1 : -1;

                await page.evaluate((d) => {
                    window.scrollBy(0, d);
                }, distance * direction);

                await this.sleep(Math.random() * 500 + 200);
            }
        } catch (e) {
            // Ignore scroll errors
        }
    }

    /**
     * Human-like typing with random delays
     */
    async humanType(page, selector, text) {
        const element = await page.$(selector);
        if (!element) throw new Error(`Element not found: ${selector}`);

        await element.click();
        await this.sleep(this.rateLimiter.getClickDelay());

        for (const char of text) {
            await page.keyboard.type(char);
            await this.sleep(this.rateLimiter.getTypingDelay());
        }
    }

    /**
     * Human-like click with delay
     */
    async humanClick(page, selector) {
        const element = await page.$(selector);
        if (!element) throw new Error(`Element not found: ${selector}`);

        // Move mouse to element first
        const box = await element.boundingBox();
        if (box) {
            await page.mouse.move(
                box.x + box.width / 2 + (Math.random() * 10 - 5),
                box.y + box.height / 2 + (Math.random() * 10 - 5),
                { steps: 5 }
            );
        }

        await this.sleep(this.rateLimiter.getClickDelay());
        await element.click();
    }

    /**
     * Check if captcha is present on page
     */
    async isCaptchaPresent(page) {
        const captchaSelectors = [
            'iframe[src*="recaptcha"]',
            'iframe[src*="hcaptcha"]',
            '.g-recaptcha',
            '.h-captcha',
            '#captcha',
            '[class*="captcha"]',
            'img[src*="captcha"]'
        ];

        for (const selector of captchaSelectors) {
            const element = await page.$(selector);
            if (element) {
                return { present: true, type: this.getCaptchaType(selector) };
            }
        }

        return { present: false, type: null };
    }

    /**
     * Get captcha type from selector
     */
    getCaptchaType(selector) {
        if (selector.includes('recaptcha')) return 'recaptcha';
        if (selector.includes('hcaptcha')) return 'hcaptcha';
        return 'unknown';
    }

    /**
     * Solve captcha using 2Captcha service (if API key provided)
     */
    async solveCaptcha2Captcha(page, siteKey) {
        if (!this.twoCaptchaKey) {
            throw new Error('2Captcha API key not configured');
        }

        const pageUrl = page.url();

        // Submit captcha to 2Captcha
        const submitResponse = await fetch(
            `http://2captcha.com/in.php?key=${this.twoCaptchaKey}&method=userrecaptcha&googlekey=${siteKey}&pageurl=${pageUrl}&json=1`
        );
        const submitData = await submitResponse.json();

        if (submitData.status !== 1) {
            throw new Error(`2Captcha submit failed: ${submitData.request}`);
        }

        const requestId = submitData.request;

        // Poll for result
        for (let i = 0; i < 30; i++) {
            await this.sleep(5000); // Wait 5 seconds between polls

            const resultResponse = await fetch(
                `http://2captcha.com/res.php?key=${this.twoCaptchaKey}&action=get&id=${requestId}&json=1`
            );
            const resultData = await resultResponse.json();

            if (resultData.status === 1) {
                return resultData.request; // Token
            }

            if (resultData.request !== 'CAPCHA_NOT_READY') {
                throw new Error(`2Captcha failed: ${resultData.request}`);
            }
        }

        throw new Error('2Captcha timeout');
    }

    /**
     * Apply solved captcha token to page
     */
    async applyCaptchaToken(page, token) {
        await page.evaluate((t) => {
            // For reCAPTCHA v2
            const responseField = document.querySelector('[name="g-recaptcha-response"]');
            if (responseField) {
                responseField.value = t;
            }

            // Trigger callback if exists
            if (typeof window.grecaptcha !== 'undefined') {
                const callback = window.grecaptcha.getResponse;
                if (callback) callback(t);
            }
        }, token);
    }

    /**
     * Main captcha handling flow
     */
    async handleCaptcha(page) {
        const { present, type } = await this.isCaptchaPresent(page);

        if (!present) {
            return { handled: true, message: 'No captcha present' };
        }

        // Try to get site key
        const siteKey = await page.evaluate(() => {
            const recaptcha = document.querySelector('.g-recaptcha');
            if (recaptcha) return recaptcha.getAttribute('data-sitekey');

            const iframe = document.querySelector('iframe[src*="recaptcha"]');
            if (iframe) {
                const match = iframe.src.match(/k=([^&]+)/);
                return match ? match[1] : null;
            }

            return null;
        });

        // If 2Captcha key is available, try to solve
        if (this.twoCaptchaKey && siteKey) {
            try {
                const token = await this.solveCaptcha2Captcha(page, siteKey);
                await this.applyCaptchaToken(page, token);
                return { handled: true, message: 'Captcha solved via 2Captcha' };
            } catch (e) {
                return { handled: false, message: `Captcha solve failed: ${e.message}` };
            }
        }

        return {
            handled: false,
            type,
            message: 'Captcha present but no solver configured. Set TWOCAPTCHA_API_KEY in .env'
        };
    }

    /**
     * Pre-login preparation to avoid captcha
     */
    async prepareForLogin(page) {
        // Wait for rate limiter
        await this.rateLimiter.waitForRateLimit();

        // Simulate human behavior
        await this.simulateMouseMovement(page);
        await this.simulateScrolling(page);

        // Small delay before action
        await this.sleep(this.rateLimiter.getRandomDelay());
    }

    /**
     * Sleep utility
     */
    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
}

module.exports = CaptchaHandler;
