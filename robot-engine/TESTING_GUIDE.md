# PANDUAN TESTING ROBOT OLYMPTRADE

## Daftar Isi
1. [Persiapan](#persiapan)
2. [Test Koneksi OlympTrade](#test-koneksi-olymptrade)
3. [Test Trading (Demo)](#test-trading-demo)
4. [Robot Standalone](#robot-standalone)
5. [Troubleshooting](#troubleshooting)

---

## Persiapan

### 1. Install Dependencies

```bash
cd robot-engine
npm install
```

### 2. Setup Environment

Buat file `.env` dari template:

```bash
cp .env.example .env
```

Edit `.env` dengan kredensial OlympTrade Anda:

```env
# OlympTrade Credentials
OLYMPTRADE_EMAIL=email_olymptrade_anda@gmail.com
OLYMPTRADE_PASSWORD=password_olymptrade_anda

# Robot Settings
HEADLESS=true
LOG_LEVEL=info
```

### 3. Install Chromium (untuk VPS/Linux)

```bash
# Ubuntu/Debian
sudo apt update
sudo apt install -y chromium-browser

# Atau install via npm
npx puppeteer install
```

---

## Test Koneksi OlympTrade

### Test Dasar (Headless Mode)

Test koneksi dan login ke OlympTrade tanpa membuka browser:

```bash
npm run test:olymptrade -- --email=email@gmail.com --password=yourpassword
```

### Test dengan Browser Visible

Untuk melihat apa yang terjadi di browser:

```bash
npm run test:olymptrade:visible -- --email=email@gmail.com --password=yourpassword
```

Atau:

```bash
node src/test-olymptrade.js --email=email@gmail.com --password=yourpassword --visible
```

### Test dengan Trade (DEMO)

Test login + eksekusi trade di akun DEMO:

```bash
npm run test:olymptrade:trade -- --email=email@gmail.com --password=yourpassword --demo
```

Atau lengkap:

```bash
node src/test-olymptrade.js \
  --email=email@gmail.com \
  --password=yourpassword \
  --demo \
  --trade \
  --visible
```

### Opsi Lengkap test-olymptrade.js

| Option | Deskripsi |
|--------|-----------|
| `--email=xxx` | Email OlympTrade |
| `--password=xxx` | Password OlympTrade |
| `--demo` | Gunakan akun DEMO (default) |
| `--real` | Gunakan akun REAL |
| `--trade` | Eksekusi test trade (CALL) |
| `--visible` | Tampilkan browser window |
| `--no-screenshot` | Jangan ambil screenshot |

### Screenshot

Semua screenshot disimpan di:
```
robot-engine/logs/screenshots/
```

---

## Test Trading (Demo)

Setelah test koneksi berhasil, Anda bisa test trading:

### 1. Test Single Trade

```bash
node src/test-olymptrade.js \
  --email=email@gmail.com \
  --password=yourpassword \
  --demo \
  --trade \
  --visible
```

### 2. Hasil yang Diharapkan

```
═══════════════════════════════════════════════════════════
   TEST RESULTS
═══════════════════════════════════════════════════════════
   Browser:        ✅ PASS
   Navigation:     ✅ PASS
   Login:          ✅ PASS
   Account Switch: ✅ PASS
   Balance:        ✅ $10000.00
   Trade:          ✅ PASS
═══════════════════════════════════════════════════════════
```

---

## Robot Standalone

Robot standalone bisa berjalan tanpa database untuk testing:

### Menjalankan Robot Standalone

```bash
npm run standalone -- --email=email@gmail.com --password=yourpassword --demo --visible
```

Atau:

```bash
node src/standalone-robot.js \
  --email=email@gmail.com \
  --password=yourpassword \
  --demo \
  --visible \
  --amount=1 \
  --max-trades=5
```

### Opsi Standalone Robot

| Option | Default | Deskripsi |
|--------|---------|-----------|
| `--email` | - | Email OlympTrade |
| `--password` | - | Password OlympTrade |
| `--demo` | true | Akun demo |
| `--real` | false | Akun real |
| `--visible` | false | Tampilkan browser |
| `--amount=X` | 1 | Jumlah trade ($) |
| `--max-trades=X` | 10 | Maksimum trade |
| `--strategy=X` | simple_rsi | Strategy (simple_rsi, macd, trend) |
| `--interval=X` | 60 | Interval antar trade (detik) |

### Strategies yang Tersedia

1. **simple_rsi** - RSI oversold/overbought
2. **macd** - MACD crossover
3. **trend** - SMA trend following

### Menghentikan Robot

Tekan `Ctrl+C` untuk menghentikan robot.

---

## Troubleshooting

### Error: "Browser could not be launched"

```bash
# Install dependencies chromium
sudo apt install -y libx11-xcb1 libxcomposite1 libxcursor1 libxdamage1 \
  libxi6 libxtst6 libnss3 libcups2 libxss1 libxrandr2 libatk1.0-0 \
  libatk-bridge2.0-0 libpangocairo-1.0-0 libgtk-3-0

# Install chromium
sudo apt install -y chromium-browser

# Set executable path di .env
PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium-browser
```

### Error: "Login failed"

1. Pastikan email dan password benar
2. Coba dengan `--visible` untuk melihat apa yang terjadi
3. Periksa apakah ada CAPTCHA atau 2FA
4. Pastikan akun OlympTrade aktif

### Error: "Trade button not found"

1. OlympTrade mungkin mengubah UI mereka
2. Coba dengan `--visible` untuk debug
3. Periksa screenshot di `logs/screenshots/`
4. Laporkan issue jika masalah berlanjut

### Error: "Navigation timeout"

1. Cek koneksi internet
2. Coba URL alternatif di konfigurasi
3. Tingkatkan timeout di environment

### Tips Debugging

1. Selalu gunakan `--visible` saat pertama kali test
2. Periksa folder `logs/screenshots/` untuk melihat apa yang terjadi
3. Aktifkan log level debug: `LOG_LEVEL=debug`

---

## Alur Testing Lengkap

### Step 1: Test Koneksi Awal

```bash
node src/test-connection.js
```

### Step 2: Test Login OlympTrade

```bash
node src/test-olymptrade.js --email=xxx --password=xxx --visible
```

### Step 3: Test Trade Demo

```bash
node src/test-olymptrade.js --email=xxx --password=xxx --demo --trade --visible
```

### Step 4: Test Robot Standalone

```bash
node src/standalone-robot.js --email=xxx --password=xxx --demo --visible --max-trades=3
```

### Step 5: Jalankan Full Robot

Setelah semua test berhasil:

```bash
# Setup database credentials di .env
npm start
```

---

## Catatan Penting

1. **Selalu test di DEMO dulu** sebelum menggunakan akun REAL
2. **Backup credentials** jangan simpan di repository
3. **Monitor robot** terus saat pertama kali jalan
4. **Screenshot tersimpan** untuk debugging jika ada masalah
5. **Gunakan VPS** untuk robot 24/7 (bukan PC pribadi)

---

## Kontak Support

Jika ada masalah yang tidak bisa diselesaikan:
- Buat issue di repository
- Sertakan log error dan screenshot
