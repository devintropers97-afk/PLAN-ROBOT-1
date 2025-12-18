/**
 * ZYN Trade Robot - Connection Test Script
 * Jalankan: node src/test-connection.js
 *
 * Script ini akan test:
 * 1. Koneksi database MySQL
 * 2. Koneksi API website
 * 3. Konfigurasi Puppeteer
 */

require('dotenv').config();
const mysql = require('mysql2/promise');
const axios = require('axios');
const puppeteer = require('puppeteer');

console.log('═══════════════════════════════════════════');
console.log('   ZYN TRADE ROBOT - CONNECTION TEST');
console.log('═══════════════════════════════════════════');
console.log('');

async function testDatabase() {
    console.log('🔍 [1/3] Testing Database Connection...');
    console.log(`   Host: ${process.env.DB_HOST}`);
    console.log(`   Database: ${process.env.DB_NAME}`);
    console.log(`   User: ${process.env.DB_USER}`);

    try {
        const connection = await mysql.createConnection({
            host: process.env.DB_HOST,
            user: process.env.DB_USER,
            password: process.env.DB_PASS,
            database: process.env.DB_NAME,
            connectTimeout: 30000
        });

        // Test query
        const [rows] = await connection.query('SELECT COUNT(*) as total FROM users');
        const [activeUsers] = await connection.query(`
            SELECT COUNT(*) as total FROM users
            WHERE status = 'active'
        `);

        console.log('   ✅ Database connected!');
        console.log(`   Total users: ${rows[0].total}`);
        console.log(`   Active users: ${activeUsers[0].total}`);

        // Check robot_settings table
        const [robotSettings] = await connection.query(`
            SELECT COUNT(*) as total FROM robot_settings
            WHERE robot_enabled = 1
        `);
        console.log(`   Users with robot enabled: ${robotSettings[0].total}`);

        await connection.end();
        return true;
    } catch (error) {
        console.log('   ❌ Database connection FAILED!');
        console.log(`   Error: ${error.message}`);
        console.log('');
        console.log('   Solutions:');
        console.log('   1. Enable Remote MySQL di cPanel');
        console.log('   2. Whitelist IP VPS di Remote MySQL');
        console.log('   3. Pastikan hostname DB_HOST benar');
        return false;
    }
}

async function testAPI() {
    console.log('');
    console.log('🔍 [2/3] Testing API Connection...');
    console.log(`   URL: ${process.env.API_BASE_URL}`);

    try {
        const response = await axios.get(`${process.env.API_BASE_URL}/api/health.php`, {
            timeout: 30000
        });

        if (response.data.status === 'healthy') {
            console.log('   ✅ API connected!');
            console.log(`   Status: ${response.data.status}`);
            console.log(`   Environment: ${response.data.environment}`);
            console.log(`   Database: ${response.data.components.database.status}`);
            return true;
        } else {
            console.log('   ⚠️ API response unexpected');
            return false;
        }
    } catch (error) {
        console.log('   ❌ API connection FAILED!');
        console.log(`   Error: ${error.message}`);
        return false;
    }
}

async function testPuppeteer() {
    console.log('');
    console.log('🔍 [3/3] Testing Puppeteer (Browser)...');

    try {
        const browserOptions = {
            headless: process.env.HEADLESS === 'true' ? 'new' : false,
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-accelerated-2d-canvas',
                '--disable-gpu'
            ]
        };

        // Add executable path if specified
        if (process.env.PUPPETEER_EXECUTABLE_PATH) {
            browserOptions.executablePath = process.env.PUPPETEER_EXECUTABLE_PATH;
            console.log(`   Executable: ${process.env.PUPPETEER_EXECUTABLE_PATH}`);
        }

        console.log('   Starting browser...');
        const browser = await puppeteer.launch(browserOptions);

        const page = await browser.newPage();
        await page.goto('https://olymptrade.com', {
            waitUntil: 'domcontentloaded',
            timeout: 60000
        });

        const title = await page.title();
        console.log('   ✅ Puppeteer working!');
        console.log(`   Page title: ${title}`);

        await browser.close();
        return true;
    } catch (error) {
        console.log('   ❌ Puppeteer FAILED!');
        console.log(`   Error: ${error.message}`);
        console.log('');
        console.log('   Solutions:');
        console.log('   1. Install chromium: apt install chromium-browser');
        console.log('   2. Set PUPPETEER_EXECUTABLE_PATH di .env');
        console.log('   3. Coba: which chromium-browser');
        return false;
    }
}

async function runAllTests() {
    console.log('Starting tests...\n');

    const dbOk = await testDatabase();
    const apiOk = await testAPI();
    const puppeteerOk = await testPuppeteer();

    console.log('');
    console.log('═══════════════════════════════════════════');
    console.log('   TEST RESULTS');
    console.log('═══════════════════════════════════════════');
    console.log(`   Database:  ${dbOk ? '✅ PASS' : '❌ FAIL'}`);
    console.log(`   API:       ${apiOk ? '✅ PASS' : '❌ FAIL'}`);
    console.log(`   Puppeteer: ${puppeteerOk ? '✅ PASS' : '❌ FAIL'}`);
    console.log('═══════════════════════════════════════════');

    if (dbOk && apiOk && puppeteerOk) {
        console.log('\n🎉 All tests passed! Robot ready to run.');
        console.log('   Run: npm start');
        console.log('   Or: pm2 start src/index.js --name "zyn-robot"');
    } else {
        console.log('\n⚠️ Some tests failed. Fix the issues before running robot.');
    }

    process.exit(dbOk && apiOk && puppeteerOk ? 0 : 1);
}

// Run tests
runAllTests();
