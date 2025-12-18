# Panduan Setup Robot di VPS

## Persyaratan VPS
- OS: Ubuntu 20.04+ atau Debian 10+
- RAM: Minimal 2GB
- Storage: Minimal 20GB
- Node.js: v18+

---

## Step 1: Setup Remote MySQL di cPanel

**PENTING**: Robot perlu akses ke database dari VPS.

### Di cPanel website (tester.situneo.my.id):

1. Login ke cPanel
2. Cari **"Remote MySQL"** atau **"Remote MySQL Access"**
3. Tambahkan IP VPS Anda:
   ```
   Masukkan IP VPS Anda, contoh: 123.45.67.89
   ```
4. Klik **"Add Host"**

### Cek Hostname MySQL:
1. Di cPanel > **MySQL Databases**
2. Scroll ke bawah, cari **"Server Hostname"**
3. Catat hostname ini (biasanya: `localhost`, `domain.com`, atau `mysql.domain.com`)

---

## Step 2: Setup VPS

### Login ke VPS via SSH:
```bash
ssh root@IP_VPS_ANDA
```

### Install Dependencies:
```bash
# Update system
apt update && apt upgrade -y

# Install Node.js 18+
curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
apt install -y nodejs

# Verify installation
node -v
npm -v

# Install Chromium (untuk Puppeteer)
apt install -y chromium-browser

# Install PM2 (process manager)
npm install -g pm2
```

---

## Step 3: Upload Robot ke VPS

### Option A: Via Git
```bash
cd /home
git clone https://github.com/YOUR_REPO/robot-engine.git
cd robot-engine
```

### Option B: Via SCP/SFTP
Upload folder `robot-engine` ke `/home/robot-engine` di VPS

---

## Step 4: Konfigurasi Robot

```bash
cd /home/robot-engine

# Copy environment file
cp .env.example .env

# Edit konfigurasi
nano .env
```

### Edit file .env:
```env
# Database - gunakan hostname dari cPanel Remote MySQL
DB_HOST=tester.situneo.my.id
DB_NAME=nrrskfvk_ZYNtradesystem
DB_USER=nrrskfvk_userZYNtradesystem
DB_PASS=Devin1922$

# API
API_BASE_URL=https://tester.situneo.my.id
API_SECRET_KEY=zyn_robot_secret_2024

# OlympTrade - WAJIB ISI!
OLYMPTRADE_EMAIL=email_olymptrade_anda
OLYMPTRADE_PASSWORD=password_olymptrade_anda

# Settings
ROBOT_MODE=production
LOG_LEVEL=info
HEADLESS=true
PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium-browser
```

---

## Step 5: Install Dependencies

```bash
cd /home/robot-engine
npm install
```

---

## Step 6: Test Koneksi Database

Buat file test sederhana:
```bash
nano test-db.js
```

Isi dengan:
```javascript
require('dotenv').config();
const mysql = require('mysql2/promise');

async function testConnection() {
    try {
        const conn = await mysql.createConnection({
            host: process.env.DB_HOST,
            user: process.env.DB_USER,
            password: process.env.DB_PASS,
            database: process.env.DB_NAME
        });

        console.log('✅ Database connected successfully!');

        const [rows] = await conn.query('SELECT COUNT(*) as total FROM users');
        console.log(`Total users: ${rows[0].total}`);

        await conn.end();
    } catch (error) {
        console.error('❌ Database connection failed:', error.message);
    }
}

testConnection();
```

Jalankan:
```bash
node test-db.js
```

**Jika error**: Pastikan Remote MySQL sudah di-enable di cPanel!

---

## Step 7: Test Robot

```bash
# Test mode
npm test

# Atau jalankan langsung
npm start
```

---

## Step 8: Running dengan PM2

```bash
# Start robot
pm2 start src/index.js --name "zyn-robot"

# Set auto-start saat boot
pm2 startup
pm2 save

# Monitoring
pm2 logs zyn-robot
pm2 monit

# Restart
pm2 restart zyn-robot

# Stop
pm2 stop zyn-robot
```

---

## Step 9: Monitoring

### Cek Status Robot:
```bash
pm2 status
```

### Lihat Logs:
```bash
pm2 logs zyn-robot --lines 100
```

### Lihat Resource Usage:
```bash
pm2 monit
```

---

## Troubleshooting

### Error: "Access denied for user"
- Pastikan Remote MySQL sudah di-enable di cPanel
- Pastikan IP VPS sudah di-whitelist
- Cek username/password database

### Error: "ECONNREFUSED"
- Pastikan hostname database benar
- Coba ganti DB_HOST ke IP server hosting

### Error: "Protocol timeout"
- Tambahkan memory swap di VPS
- Increase timeout di puppeteer settings

### Robot Tidak Berjalan:
```bash
# Cek logs
pm2 logs zyn-robot

# Restart
pm2 restart zyn-robot
```

---

## Commands Berguna

```bash
# Status semua proses
pm2 status

# Lihat logs real-time
pm2 logs zyn-robot

# Restart robot
pm2 restart zyn-robot

# Stop robot
pm2 stop zyn-robot

# Delete dari PM2
pm2 delete zyn-robot

# Clear logs
pm2 flush
```

---

## Keamanan

1. **Jangan share file .env** - berisi password sensitif
2. **Gunakan firewall**:
   ```bash
   ufw allow ssh
   ufw allow 80
   ufw allow 443
   ufw enable
   ```
3. **Update system regularly**:
   ```bash
   apt update && apt upgrade -y
   ```

---

## Support

Jika ada masalah, hubungi:
- Telegram: @aheenkgans
- Channel: https://t.me/OlymptradeCopytrade
