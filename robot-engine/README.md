# ZYN Trade Robot Engine

Robot trading otomatis untuk OlympTrade dengan 10 strategi teknikal.

## Requirements

- Node.js >= 18.0.0
- MySQL Database
- VPS (recommended: Ubuntu 20.04+)

## Installation

```bash
# 1. Install dependencies
npm install

# 2. Copy environment file
cp .env.example .env

# 3. Configure .env with your settings
nano .env

# 4. Run tests
npm test

# 5. Start robot
npm start
```

## Configuration (.env)

```env
# Database
DB_HOST=localhost
DB_USER=your_db_user
DB_PASSWORD=your_db_password
DB_NAME=zyn_trade

# API
API_BASE_URL=https://your-website.com
API_KEY=your_api_key

# OlympTrade (per user - stored in database)
# OLYMPTRADE_EMAIL=user@email.com
# OLYMPTRADE_PASSWORD=password
# OLYMPTRADE_DEMO_MODE=true
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

### Tier Access:
- **FREE**: Strategi #8, #9 (2 strategi)
- **PRO**: FREE + #6, #7 (4 strategi)
- **ELITE**: PRO + #3, #4, #5 (7 strategi)
- **VIP**: Semua 10 strategi

## Architecture

```
robot-engine/
├── src/
│   ├── index.js          # Main entry point
│   ├── test.js           # Test script
│   ├── modules/
│   │   ├── database.js   # MySQL operations
│   │   ├── priceData.js  # Price data feed
│   │   ├── signalGenerator.js  # Signal generation
│   │   ├── tradeExecutor.js    # Puppeteer automation
│   │   └── apiClient.js        # PHP API integration
│   ├── strategies/
│   │   ├── baseStrategy.js
│   │   ├── tripleRSI.js        # ORACLE-PRIME (#1)
│   │   ├── structuredMACD.js   # NEXUS-WAVE (#2)
│   │   ├── williamsR.js        # STEALTH-MODE (#3)
│   │   ├── connorsRSI2.js      # PHOENIX-X1 (#4)
│   │   ├── macdBollinger.js    # VORTEX-PRO (#5)
│   │   ├── macdRSICombo.js     # TITAN-PULSE (#6)
│   │   ├── stochRSIMACD.js     # SHADOW-EDGE (#7)
│   │   ├── bbRSIStandard.js    # BLITZ-SIGNAL (#8)
│   │   ├── rsiDivergence.js    # APEX-HUNTER (#9)
│   │   └── multiIndicator.js   # QUANTUM-FLOW (#10)
│   └── utils/
│       ├── logger.js
│       └── helpers.js
├── logs/              # Log files
├── screenshots/       # Debug screenshots
├── package.json
├── .env.example
└── README.md
```

## Features

- **10 Technical Strategies**: RSI, MACD, Bollinger Bands, Williams %R, Stochastic RSI
- **Multi-Asset Support**: EUR/USD, GBP/USD
- **Multi-Timeframe**: 5M, 15M, 30M, 1H
- **Money Management**: Flat Amount & Martingale
- **Auto-Pause**: Take Profit & Max Loss triggers
- **5 Schedule Modes**: Auto 24H, Best Hours, Custom, Multi-Session, Per Day
- **Weekend Auto-Off**: Saturday & Sunday
- **Real-time Logging**: Winston logger with daily rotation

## Running as Service

```bash
# Using PM2 (recommended)
npm install -g pm2
pm2 start src/index.js --name "zyn-robot"
pm2 save
pm2 startup

# Using systemd
sudo nano /etc/systemd/system/zyn-robot.service
```

Example systemd service:

```ini
[Unit]
Description=ZYN Trade Robot
After=network.target

[Service]
Type=simple
User=ubuntu
WorkingDirectory=/path/to/robot-engine
ExecStart=/usr/bin/node src/index.js
Restart=on-failure
RestartSec=10

[Install]
WantedBy=multi-user.target
```

## License

Private - ZYN Trade System
