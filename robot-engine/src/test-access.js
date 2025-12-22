/**
 * =============================================
 * ZYN TRADE ROBOT - OLYMPTRADE ACCESS TEST
 * =============================================
 *
 * Script sederhana untuk test apakah OlympTrade bisa diakses
 * dari lokasi VPS ini. Tidak perlu login.
 *
 * Usage:
 *   node src/test-access.js
 *   node src/test-access.js --visible
 */

require('dotenv').config();
const puppeteer = require('puppeteer');
const https = require('https');
const http = require('http');

// Sleep utility
const sleep = (ms) => new Promise(resolve => setTimeout(resolve, ms));

// Get public IP
async function getPublicIP() {
    return new Promise((resolve) => {
        https.get('https://api.ipify.org?format=json', (res) => {
            let data = '';
            res.on('data', chunk => data += chunk);
            res.on('end', () => {
                try {
                    resolve(JSON.parse(data).ip);
                } catch {
                    resolve('Unknown');
                }
            });
        }).on('error', () => {
            // Try backup
            https.get('https://ipinfo.io/ip', (res) => {
                let data = '';
                res.on('data', chunk => data += chunk);
                res.on('end', () => resolve(data.trim() || 'Unknown'));
            }).on('error', () => resolve('Unknown'));
        });
    });
}

// Get IP location
async function getIPLocation(ip) {
    return new Promise((resolve) => {
        http.get(`http://ip-api.com/json/${ip}`, (res) => {
            let data = '';
            res.on('data', chunk => data += chunk);
            res.on('end', () => {
                try {
                    const info = JSON.parse(data);
                    resolve({
                        country: info.country || 'Unknown',
                        city: info.city || 'Unknown',
                        isp: info.isp || 'Unknown',
                        org: info.org || 'Unknown'
                    });
                } catch {
                    resolve({ country: 'Unknown', city: 'Unknown', isp: 'Unknown', org: 'Unknown' });
                }
            });
        }).on('error', () => {
            resolve({ country: 'Unknown', city: 'Unknown', isp: 'Unknown', org: 'Unknown' });
        });
    });
}

async function testOlympTradeAccess() {
    const visible = process.argv.includes('--visible');

    console.log('\n');
    console.log('═══════════════════════════════════════════════════════════');
    console.log('   OLYMPTRADE ACCESS TEST');
    console.log('═══════════════════════════════════════════════════════════');

    // Get IP info
    console.log('\n   Checking IP address...');
    const ip = await getPublicIP();
    const location = await getIPLocation(ip);

    console.log(`\n   IP Address: ${ip}`);
    console.log(`   Country: ${location.country}`);
    console.log(`   City: ${location.city}`);
    console.log(`   ISP: ${location.isp}`);
    console.log(`   Organization: ${location.org}`);

    console.log('\n   Starting browser test...\n');

    let browser = null;
    let result = {
        canAccess: false,
        hasLoginForm: false,
        hasGeoBlock: false,
        blockMessage: null,
        pageTitle: null
    };

    try {
        // Launch browser
        browser = await puppeteer.launch({
            headless: visible ? false : 'new',
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--window-size=1920,1080'
            ],
            defaultViewport: { width: 1920, height: 1080 }
        });

        const page = await browser.newPage();

        // Set realistic user agent
        await page.setUserAgent(
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        );

        console.log('   [1/4] Loading olymptrade.com...');

        // Test main domain
        await page.goto('https://olymptrade.com', {
            waitUntil: 'networkidle2',
            timeout: 60000
        });

        await sleep(3000);

        result.pageTitle = await page.title();
        console.log(`   [2/4] Page title: ${result.pageTitle}`);

        // Check page content for geo-block messages
        console.log('   [3/4] Checking for geo-block...');
        const pageContent = await page.evaluate(() => document.body.innerText.toLowerCase());

        const blockPhrases = [
            'registration unavailable',
            'not available for clients from your region',
            'unavailable in your region',
            'service is not available',
            'country is not supported',
            'access denied',
            'blocked',
            'restricted'
        ];

        for (const phrase of blockPhrases) {
            if (pageContent.includes(phrase)) {
                result.hasGeoBlock = true;
                result.blockMessage = phrase;
                break;
            }
        }

        // Check for login/registration form
        console.log('   [4/4] Checking for login form...');

        await page.goto('https://olymptrade.com/login', {
            waitUntil: 'networkidle2',
            timeout: 60000
        });

        await sleep(2000);

        // Look for email input
        const hasEmailInput = await page.evaluate(() => {
            const inputs = document.querySelectorAll('input[type="email"], input[name="email"], input[placeholder*="email" i]');
            return inputs.length > 0;
        });

        // Look for login button
        const hasLoginButton = await page.evaluate(() => {
            const buttons = document.querySelectorAll('button');
            for (const btn of buttons) {
                const text = btn.textContent.toLowerCase();
                if (text.includes('masuk') || text.includes('login') || text.includes('sign in')) {
                    return true;
                }
            }
            return false;
        });

        result.hasLoginForm = hasEmailInput && hasLoginButton;
        result.canAccess = !result.hasGeoBlock && result.hasLoginForm;

        // Take screenshot if visible
        if (visible) {
            console.log('\n   Browser left open for inspection.');
            console.log('   Press Ctrl+C to close.\n');
        }

    } catch (error) {
        console.log(`\n   ❌ Error: ${error.message}`);
        result.blockMessage = error.message;
    } finally {
        if (browser && !visible) {
            await browser.close();
        }
    }

    // Print results
    console.log('\n');
    console.log('═══════════════════════════════════════════════════════════');
    console.log('   TEST RESULTS');
    console.log('═══════════════════════════════════════════════════════════');
    console.log(`   IP Address:    ${ip}`);
    console.log(`   Location:      ${location.city}, ${location.country}`);
    console.log(`   ISP:           ${location.isp}`);
    console.log('───────────────────────────────────────────────────────────');

    if (result.canAccess) {
        console.log('   Status:        ✅ OLYMPTRADE ACCESSIBLE');
        console.log('   Login Form:    ✅ Found');
        console.log('   Geo-Block:     ✅ No block detected');
        console.log('───────────────────────────────────────────────────────────');
        console.log('\n   🎉 VPS lokasi ini BISA digunakan untuk robot trading!');
        console.log('   Jalankan test login: npm run test:olymptrade\n');
    } else {
        console.log('   Status:        ❌ OLYMPTRADE BLOCKED');
        console.log(`   Login Form:    ${result.hasLoginForm ? '✅ Found' : '❌ Not found'}`);
        console.log(`   Geo-Block:     ${result.hasGeoBlock ? '❌ BLOCKED' : '⚠️ Unknown'}`);
        if (result.blockMessage) {
            console.log(`   Block Message: ${result.blockMessage}`);
        }
        console.log('───────────────────────────────────────────────────────────');
        console.log('\n   ⚠️ VPS lokasi ini TIDAK bisa digunakan untuk robot.');
        console.log('   Coba VPS di lokasi lain atau gunakan residential proxy.\n');
    }

    console.log('═══════════════════════════════════════════════════════════');
    console.log('\n   REKOMENDASI LOKASI VPS UNTUK OLYMPTRADE:');
    console.log('───────────────────────────────────────────────────────────');
    console.log('   ✅ Indonesia      - Biasanya works (IDC/datacenter)');
    console.log('   ✅ Singapore      - Biasanya works');
    console.log('   ✅ Malaysia       - Biasanya works');
    console.log('   ✅ Thailand       - Biasanya works');
    console.log('   ✅ Philippines    - Biasanya works');
    console.log('   ✅ India          - Biasanya works');
    console.log('   ✅ Brazil         - Biasanya works');
    console.log('   ⚠️ Europe         - Kadang diblock (datacenter IP)');
    console.log('   ⚠️ USA            - Kadang diblock (datacenter IP)');
    console.log('───────────────────────────────────────────────────────────');
    console.log('\n   ALTERNATIF SOLUSI:');
    console.log('   1. Pindah VPS ke negara Asia Tenggara');
    console.log('   2. Gunakan residential proxy (Bright Data, Smartproxy)');
    console.log('   3. VPN dengan residential IP');
    console.log('');
    console.log('═══════════════════════════════════════════════════════════\n');

    process.exit(result.canAccess ? 0 : 1);
}

// Run test
testOlympTradeAccess();
