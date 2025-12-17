# ZYN Trade System - VPS Setup Notes

**Setup Date:** December 17, 2025
**VPS Provider:** Hostinger
**Location:** Germany - Frankfurt

---

## Server Information

| Item | Value |
|------|-------|
| **IP Address** | 72.62.93.215 |
| **Hostname** | srv1205066.hstgr.cloud |
| **OS** | Ubuntu 22.04 LTS |
| **RAM** | 4 GB |
| **Disk** | 50 GB NVMe |

---

## Access Credentials

### SSH Access
```
ssh root@72.62.93.215
Password: Devinganteng1922#
```

### HestiaCP Panel
```
URL: https://72.62.93.215:8083
Username: devin
Password: Devinganteng1922#
```

### Database (MySQL/MariaDB)
```
Host: 127.0.0.1
Database: devin_zyntrade
Username: devin_zyntrade
Password: ZynTrade2024
```

---

## Installed Components

| Component | Version | Status |
|-----------|---------|--------|
| Ubuntu | 22.04 LTS | Running |
| HestiaCP | 1.9.4 | Running |
| Node.js | 18.x | Installed |
| PM2 | Latest | Running |
| Nginx | Latest | Running |
| Apache | Latest | Running |
| MariaDB | 11.4 | Running |
| PHP-FPM | 8.x | Running |
| Chromium | Latest | Installed |

---

## URLs

| Service | URL |
|---------|-----|
| **Website** | http://72.62.93.215 |
| **HestiaCP** | https://72.62.93.215:8083 |
| **phpMyAdmin** | https://72.62.93.215:8083 → DB → phpMyAdmin |

---

## File Locations

| Item | Path |
|------|------|
| **Website Files** | /home/devin/web/srv1205066.hstgr.cloud/public_html/ |
| **Robot Engine** | /home/devin/web/srv1205066.hstgr.cloud/public_html/robot-engine/ |
| **Robot .env** | /home/devin/web/srv1205066.hstgr.cloud/public_html/robot-engine/.env |
| **PHP Config** | /home/devin/web/srv1205066.hstgr.cloud/public_html/includes/config.php |
| **PM2 Logs** | /root/.pm2/logs/ |

---

## PM2 Commands (Robot Management)

```bash
# Check robot status
pm2 status

# View robot logs
pm2 logs zyn-robot

# View last 50 lines of logs
pm2 logs zyn-robot --lines 50

# Restart robot
pm2 restart zyn-robot

# Stop robot
pm2 stop zyn-robot

# Start robot
pm2 start zyn-robot

# Save PM2 config (for auto-start on reboot)
pm2 save
```

---

## Robot Engine .env Configuration

```env
DB_HOST=127.0.0.1
DB_NAME=devin_zyntrade
DB_USER=devin_zyntrade
DB_PASS=ZynTrade2024
ROBOT_MODE=production
PRICE_DATA_SOURCE=tradingview
LOG_LEVEL=info
HEADLESS=true
API_BASE_URL=http://72.62.93.215
API_SECRET_KEY=secret123
```

---

## Website config.php Key Settings

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'devin_zyntrade');
define('DB_USER', 'devin_zyntrade');
define('DB_PASS', 'ZynTrade2024');
define('SITE_URL', 'http://72.62.93.215');
```

---

## Default Admin Login (Website)

```
Email: admin@zyntrade.com
Password: admin123
```

**IMPORTANT: Change this password immediately after first login!**

---

## Martingale Settings

- Multiplier: 1.5x (50% increase per step)
- Max Steps: 10
- Sequence: 1x → 1.5x → 2.25x → 3.38x → 5.06x → 7.59x → 11.39x → 17.09x → 25.63x → 38.44x → 57.67x

---

## Troubleshooting

### Robot Database Connection Error
1. Check .env file: `cat /home/devin/web/srv1205066.hstgr.cloud/public_html/robot-engine/.env`
2. Verify database credentials in HestiaCP
3. Restart robot: `pm2 restart zyn-robot`

### Robot Not Running
1. Check status: `pm2 status`
2. Check logs: `pm2 logs zyn-robot`
3. Restart: `pm2 restart zyn-robot`

### Website Error
1. Check PHP error logs in HestiaCP
2. Verify config.php database settings
3. Check file permissions: `chown -R devin:devin /home/devin/web/`

---

## Next Steps

1. Login ke website dengan admin account
2. Ubah password admin
3. Setup user baru di admin panel
4. Konfigurasi OlympTrade credentials untuk setiap user
5. Test robot dengan mode demo dulu

---

*Document created by Claude Code*
*Last updated: December 17, 2025*
