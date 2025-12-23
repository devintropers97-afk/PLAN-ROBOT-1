# ZYN Trade Robot Engine

Robot trading otomatis untuk OlympTrade dengan fitur anti-captcha, rate limiting, dan queue system untuk multiple traders.

## Features

- **Session Persistence**: Menyimpan session login untuk menghindari login berulang
- **Rate Limiting**: Mencegah blocking dari OlympTrade karena terlalu banyak request
- **Anti-Captcha**: Strategi menghindari captcha + integrasi 2Captcha (opsional)
- **Queue System**: Menangani multiple trader dengan antrian
- **REST API**: Integrasi mudah dengan website PHP/lainnya
- **Multi-Trader Support**: Satu robot untuk banyak trader
- **10 Technical Strategies**: RSI, MACD, Bollinger Bands, Williams %R, Stochastic RSI

## Requirements

- Node.js >= 18.0.0
- VPS di Indonesia/Singapore (OlympTrade block US/EU)
- Chromium browser

## Installation

```bash
# Clone repository
git clone <repo-url>
cd PLAN-ROBOT-1/robot-engine

# Install dependencies
npm install

# Install Chromium browser
npx playwright install chromium

# Setup environment
cp .env.example .env
# Edit .env dengan konfigurasi
```

## Environment Variables

```env
# OlympTrade Credentials (untuk testing)
OLYMPTRADE_EMAIL=your@email.com
OLYMPTRADE_PASSWORD=yourpassword

# Chromium path (dari playwright)
PUPPETEER_EXECUTABLE_PATH=/root/.cache/ms-playwright/chromium-1200/chrome-linux64/chrome

# API Server
API_PORT=3001
API_KEY=your-secret-api-key

# Optional: 2Captcha untuk solve captcha otomatis
TWOCAPTCHA_API_KEY=your-2captcha-key
```

## Usage

### 1. Test Mode (Single Trader)

```bash
# Test akses OlympTrade
npm run test:access:simple

# Test login dan trading
npm run test:olymptrade -- --trade --demo

# Dengan browser visible
npm run test:olymptrade -- --trade --demo --visible
```

### 2. Standalone Robot

```bash
# Jalankan robot standalone
npm run standalone -- --demo --amount=1 --strategy=simple_rsi

# Dengan browser visible
npm run standalone:visible -- --demo
```

### 3. API Server (Multi-Trader)

```bash
# Start API server
npm run api

# Dengan auto-reload (development)
npm run api:dev
```

## API Endpoints

### Health Check
```
GET /health
```

### Execute Trade
```
POST /api/trade/execute
Content-Type: application/json
X-API-Key: your-api-key

{
  "email": "trader@email.com",
  "password": "password123",
  "direction": "CALL",
  "amount": 10,
  "asset": "EUR/USD",
  "duration": 1,
  "isDemo": true
}
```

### Get Trade Status
```
GET /api/trade/status/:jobId
X-API-Key: your-api-key
```

### Test Login
```
POST /api/trader/login
X-API-Key: your-api-key

{
  "email": "trader@email.com",
  "password": "password123",
  "isDemo": true
}
```

### Get Balance
```
POST /api/trader/balance
X-API-Key: your-api-key

{
  "email": "trader@email.com",
  "password": "password123",
  "isDemo": true
}
```

### Queue Status
```
GET /api/queue/status
X-API-Key: your-api-key
```

## PHP Integration

Lihat folder `website-integration/` untuk contoh integrasi dengan PHP:

```php
require_once 'ZynRobotClient.php';

$robot = new ZynRobotClient('http://localhost:3001', 'your-api-key');

// Execute trade
$result = $robot->executeTrade([
    'email' => 'trader@email.com',
    'password' => 'password123',
    'direction' => 'CALL',
    'amount' => 10,
    'isDemo' => true
]);

// Wait for result
$finalResult = $robot->waitForTrade($result['jobId']);
```

## Strategies

| # | Name | Tier | Win Rate | Signal Frequency |
|---|------|------|----------|------------------|
| 1 | ORACLE-PRIME ⭐ | VIP | 90-91% | ~1-2 jam sekali |
| 2 | NEXUS-WAVE ⭐ | VIP | 87% | ~45-90 menit |
| 3 | STEALTH-MODE | ELITE | 81% | ~40-60 menit |
| 4 | PHOENIX-X1 | ELITE | 75-83% | ~35-55 menit |
| 5 | VORTEX-PRO | ELITE | 78% | ~30-50 menit |
| 6 | TITAN-PULSE | PRO | 73% | ~25-40 menit |
| 7 | SHADOW-EDGE | PRO | 73% | ~30-50 menit |
| 8 | BLITZ-SIGNAL | FREE | 60-78% | ~15-30 menit |
| 9 | APEX-HUNTER | FREE | 55-86% | ~20-45 menit |
| 10 | QUANTUM-FLOW | VIP | 80-90% | ~30-60 menit |

## Anti-Captcha Strategy

Robot menggunakan beberapa strategi untuk menghindari captcha:

1. **Session Persistence**: Menyimpan cookies/session untuk menghindari login berulang
2. **Rate Limiting**: Membatasi request per menit dan login per jam
3. **Human-like Behavior**: Simulasi gerakan mouse dan typing delay
4. **Minimal Login**: Menggunakan session yang ada jika masih valid

Jika captcha tetap muncul, robot mendukung 2Captcha:

```env
TWOCAPTCHA_API_KEY=your-key
```

## Troubleshooting

### Error: Trade button not found
- Platform belum fully loaded
- Ada popup/modal yang menutupi
- Coba tunggu lebih lama atau jalankan dengan `--visible`

### Error: Login failed
- Credentials salah
- Terlalu banyak login attempts (tunggu 1 jam)
- Captcha muncul (setup 2Captcha atau tunggu)

### Error: net::ERR_TUNNEL_CONNECTION_FAILED
- VPS di-block OlympTrade (gunakan VPS Indonesia/Singapore)
- Network issue

### Error: Session expired
- Clear session: hapus folder `sessions/`
- Login ulang

## File Structure

```
robot-engine/
├── src/
│   ├── api/
│   │   ├── server.js        # REST API server
│   │   ├── tradeQueue.js    # Queue management
│   │   └── tradeExecutor.js # Trade execution
│   ├── utils/
│   │   ├── rateLimiter.js   # Rate limiting
│   │   ├── sessionManager.js # Session management
│   │   └── captchaHandler.js # Captcha handling
│   ├── strategies/          # 10 trading strategies
│   ├── test-olymptrade.js   # Test script
│   └── standalone-robot.js  # Standalone robot
├── sessions/                 # Browser sessions
├── logs/                     # Logs and screenshots
└── .env                      # Configuration
```

## Production Deployment

```bash
# Install PM2
npm install -g pm2

# Start with PM2
pm2 start src/api/server.js --name "zyn-robot-api"

# Auto-restart on reboot
pm2 startup
pm2 save
```

## License

PROPRIETARY - ZYN Trade System
