/**
 * =============================================
 * OLYMPTRADE ACCESS TEST - SIMPLE VERSION
 * =============================================
 *
 * Test sederhana tanpa Puppeteer - hanya HTTP request
 * Untuk cek apakah OlympTrade bisa diakses dari VPS ini
 */

const https = require('https');
const http = require('http');

// Colors for console
const colors = {
    reset: '\x1b[0m',
    green: '\x1b[32m',
    red: '\x1b[31m',
    yellow: '\x1b[33m',
    cyan: '\x1b[36m'
};

// Simple HTTPS GET
function httpsGet(url, timeout = 15000) {
    return new Promise((resolve, reject) => {
        const req = https.get(url, {
            timeout,
            headers: {
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            }
        }, (res) => {
            let data = '';
            res.on('data', chunk => data += chunk);
            res.on('end', () => {
                resolve({
                    statusCode: res.statusCode,
                    headers: res.headers,
                    body: data
                });
            });
        });
        req.on('timeout', () => {
            req.destroy();
            reject(new Error('Request timeout'));
        });
        req.on('error', reject);
    });
}

function httpGet(url, timeout = 10000) {
    return new Promise((resolve, reject) => {
        const req = http.get(url, { timeout }, (res) => {
            let data = '';
            res.on('data', chunk => data += chunk);
            res.on('end', () => {
                resolve({
                    statusCode: res.statusCode,
                    body: data
                });
            });
        });
        req.on('timeout', () => {
            req.destroy();
            reject(new Error('Request timeout'));
        });
        req.on('error', reject);
    });
}

async function getPublicIP() {
    try {
        const res = await httpsGet('https://api.ipify.org?format=json');
        return JSON.parse(res.body).ip;
    } catch {
        try {
            const res = await httpsGet('https://ipinfo.io/ip');
            return res.body.trim();
        } catch {
            return 'Unknown';
        }
    }
}

async function getIPLocation(ip) {
    try {
        const res = await httpGet(`http://ip-api.com/json/${ip}`);
        const info = JSON.parse(res.body);
        return {
            country: info.country || 'Unknown',
            countryCode: info.countryCode || 'XX',
            city: info.city || 'Unknown',
            isp: info.isp || 'Unknown',
            org: info.org || 'Unknown',
            isHosting: info.hosting || false
        };
    } catch {
        return { country: 'Unknown', countryCode: 'XX', city: 'Unknown', isp: 'Unknown', org: 'Unknown', isHosting: false };
    }
}

async function testOlympTradeAccess() {
    console.log('\n');
    console.log('═══════════════════════════════════════════════════════════════');
    console.log('   OLYMPTRADE ACCESS TEST (Simple HTTP Version)');
    console.log('═══════════════════════════════════════════════════════════════');

    // Get IP info
    console.log('\n   [1/3] Getting IP address...');
    const ip = await getPublicIP();
    const location = await getIPLocation(ip);

    console.log(`\n   ${colors.cyan}IP Address:${colors.reset}  ${ip}`);
    console.log(`   ${colors.cyan}Country:${colors.reset}     ${location.country} (${location.countryCode})`);
    console.log(`   ${colors.cyan}City:${colors.reset}        ${location.city}`);
    console.log(`   ${colors.cyan}ISP:${colors.reset}         ${location.isp}`);
    console.log(`   ${colors.cyan}Datacenter:${colors.reset}  ${location.isHosting ? 'Yes (VPS/Hosting)' : 'No (Residential)'}`);

    console.log('\n   [2/3] Testing OlympTrade domains...\n');

    const domains = [
        'https://olymptrade.com',
        'https://olymptrade.com/login',
        'https://o-pt.trade',
        'https://olymptrade.club'
    ];

    let workingDomains = [];
    let blockedDomains = [];

    for (const domain of domains) {
        try {
            process.stdout.write(`   Testing ${domain}... `);
            const res = await httpsGet(domain, 20000);

            const bodyLower = res.body.toLowerCase();

            // Check for block indicators
            const isBlocked =
                bodyLower.includes('registration unavailable') ||
                bodyLower.includes('not available for clients from your region') ||
                bodyLower.includes('unavailable in your region') ||
                bodyLower.includes('service is not available') ||
                bodyLower.includes('access denied') ||
                res.statusCode === 403 ||
                res.statusCode === 451; // Unavailable for legal reasons

            // Check for login form indicators
            const hasLoginForm =
                bodyLower.includes('login') ||
                bodyLower.includes('masuk') ||
                bodyLower.includes('sign in') ||
                bodyLower.includes('email') ||
                bodyLower.includes('password');

            if (isBlocked) {
                console.log(`${colors.red}❌ BLOCKED${colors.reset}`);
                blockedDomains.push(domain);
            } else if (res.statusCode === 200 && hasLoginForm) {
                console.log(`${colors.green}✅ ACCESSIBLE${colors.reset} (Status: ${res.statusCode})`);
                workingDomains.push(domain);
            } else if (res.statusCode === 200 || res.statusCode === 302 || res.statusCode === 301) {
                console.log(`${colors.yellow}⚠️ MAYBE${colors.reset} (Status: ${res.statusCode})`);
                workingDomains.push(domain);
            } else {
                console.log(`${colors.red}❌ FAILED${colors.reset} (Status: ${res.statusCode})`);
                blockedDomains.push(domain);
            }
        } catch (error) {
            console.log(`${colors.red}❌ ERROR${colors.reset} (${error.message})`);
            blockedDomains.push(domain);
        }
    }

    console.log('\n   [3/3] Analyzing results...');

    // Print results
    console.log('\n');
    console.log('═══════════════════════════════════════════════════════════════');
    console.log('   TEST RESULTS');
    console.log('═══════════════════════════════════════════════════════════════');
    console.log(`\n   IP Address:     ${ip}`);
    console.log(`   Location:       ${location.city}, ${location.country}`);
    console.log(`   ISP:            ${location.isp}`);
    console.log(`   Type:           ${location.isHosting ? 'Datacenter/VPS' : 'Residential'}`);
    console.log('───────────────────────────────────────────────────────────────');
    console.log(`   Working:        ${workingDomains.length} domain(s)`);
    console.log(`   Blocked:        ${blockedDomains.length} domain(s)`);
    console.log('───────────────────────────────────────────────────────────────');

    if (workingDomains.length > 0) {
        console.log(`\n   ${colors.green}✅ VPS INI BISA DIGUNAKAN!${colors.reset}`);
        console.log('\n   Domain yang bisa diakses:');
        workingDomains.forEach(d => console.log(`     - ${d}`));
        console.log('\n   Langkah selanjutnya:');
        console.log('   1. Install dependencies: npm install');
        console.log('   2. Test login: npm run test:olymptrade');
        console.log('   3. Jalankan robot: npm start');
    } else {
        console.log(`\n   ${colors.red}❌ VPS INI TIDAK BISA DIGUNAKAN!${colors.reset}`);
        console.log('\n   OlympTrade memblokir IP dari lokasi ini.');
        console.log('   Kemungkinan penyebab:');
        console.log('   - IP datacenter/VPS diblokir');
        console.log('   - Negara/region tidak didukung');
    }

    console.log('\n═══════════════════════════════════════════════════════════════');
    console.log('   REKOMENDASI LOKASI VPS');
    console.log('═══════════════════════════════════════════════════════════════');
    console.log('\n   Berdasarkan pengalaman, lokasi berikut biasanya work:');
    console.log('');
    console.log('   ASIA TENGGARA (Recommended):');
    console.log('   ├─ 🇮🇩 Indonesia  - DigitalOcean SGP, Vultr SGP');
    console.log('   ├─ 🇸🇬 Singapore  - AWS, DigitalOcean, Vultr');
    console.log('   ├─ 🇲🇾 Malaysia   - Various providers');
    console.log('   ├─ 🇹🇭 Thailand   - Various providers');
    console.log('   └─ 🇵🇭 Philippines - Various providers');
    console.log('');
    console.log('   ASIA LAINNYA:');
    console.log('   ├─ 🇮🇳 India      - AWS Mumbai, DigitalOcean');
    console.log('   └─ 🇯🇵 Japan      - Kadang work');
    console.log('');
    console.log('   AMERIKA LATIN:');
    console.log('   ├─ 🇧🇷 Brazil     - AWS Sao Paulo');
    console.log('   └─ 🇲🇽 Mexico     - Kadang work');
    console.log('');
    console.log('   YANG SERING DIBLOKIR:');
    console.log('   ├─ 🇩🇪 Germany    - Sering diblokir');
    console.log('   ├─ 🇳🇱 Netherlands - Sering diblokir');
    console.log('   ├─ 🇺🇸 USA        - Tergantung provider');
    console.log('   └─ 🇬🇧 UK         - Kadang diblokir');
    console.log('');
    console.log('═══════════════════════════════════════════════════════════════');
    console.log('   ALTERNATIF SOLUSI');
    console.log('═══════════════════════════════════════════════════════════════');
    console.log('');
    console.log('   1. Pindah VPS ke lokasi Asia Tenggara');
    console.log('      - Singapore paling stabil');
    console.log('      - Provider: DigitalOcean, Vultr, Linode, AWS');
    console.log('');
    console.log('   2. Gunakan Residential Proxy');
    console.log('      - Bright Data (brightdata.com)');
    console.log('      - Smartproxy (smartproxy.com)');
    console.log('      - Oxylabs (oxylabs.io)');
    console.log('');
    console.log('   3. VPN dengan Residential IP');
    console.log('      - Tidak semua VPN work');
    console.log('      - ProtonVPN kadang diblokir juga');
    console.log('');
    console.log('═══════════════════════════════════════════════════════════════\n');

    process.exit(workingDomains.length > 0 ? 0 : 1);
}

// Run
testOlympTradeAccess().catch(err => {
    console.error('Error:', err.message);
    process.exit(1);
});
