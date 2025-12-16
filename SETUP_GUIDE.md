# Panduan Setup ZYN Trade System

Panduan ini dibuat untuk pemula yang tidak punya background programming.
Ikuti langkah-langkah di bawah ini dengan teliti.

---

## Daftar Isi
1. [Persiapan Hosting](#1-persiapan-hosting)
2. [Upload Files](#2-upload-files)
3. [Setup Database](#3-setup-database)
4. [Konfigurasi](#4-konfigurasi)
5. [Google Analytics](#5-google-analytics)
6. [PWA & Push Notifications](#6-pwa--push-notifications)
7. [Backup Otomatis](#7-backup-otomatis)
8. [Badges & Achievements](#8-badges--achievements)
9. [Domain & SSL](#9-domain--ssl)
10. [Troubleshooting](#10-troubleshooting)

---

## 1. Persiapan Hosting

### Hosting yang Direkomendasikan
- **Niagahoster** (Indonesia) - Rp 100rb/bulan
- **Hostinger** (International) - $2.99/bulan
- **Dewaweb** (Indonesia) - Rp 150rb/bulan

### Spesifikasi Minimum
- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- 1GB Storage
- SSL Certificate (biasanya gratis)

### Cara Cek PHP Version
1. Buka cPanel hosting Anda
2. Cari "PHP Version" atau "Select PHP Version"
3. Pastikan minimal PHP 7.4

---

## 2. Upload Files

### Cara Upload via cPanel File Manager
1. Login ke cPanel hosting
2. Klik "File Manager"
3. Masuk ke folder `public_html`
4. Klik "Upload" di toolbar atas
5. Upload semua file dari folder project ini
6. Pastikan file `.htaccess` juga ter-upload (file hidden)

### Cara Upload via FTP (FileZilla)
1. Download FileZilla dari https://filezilla-project.org
2. Buka FileZilla
3. Isi:
   - Host: ftp.domainanda.com (lihat di cPanel)
   - Username: username FTP Anda
   - Password: password FTP Anda
   - Port: 21
4. Klik "Quickconnect"
5. Di panel kanan, masuk ke `public_html`
6. Di panel kiri, pilih semua file project
7. Drag & drop ke panel kanan

### Struktur Folder
```
public_html/
├── admin/           (halaman admin)
├── api/             (API endpoints)
├── assets/          (CSS, JS, images)
├── backups/         (auto-created)
├── database/        (SQL schema)
├── downloads/       (APK files)
├── includes/        (PHP helpers)
├── lang/            (translation files)
├── logs/            (auto-created)
├── robot-engine/    (trading bot)
├── uploads/         (user uploads)
├── index.php        (homepage)
├── .htaccess        (server config)
└── ... (other files)
```

---

## 3. Setup Database

### Langkah-langkah
1. **Buat Database Baru**
   - Login cPanel
   - Klik "MySQL Databases"
   - Isi nama database: `zyn_trade`
   - Klik "Create Database"

2. **Buat User Database**
   - Scroll ke bagian "MySQL Users"
   - Isi username: `zyn_user`
   - Isi password: (buat password kuat)
   - Klik "Create User"

3. **Hubungkan User ke Database**
   - Scroll ke "Add User To Database"
   - Pilih user dan database yang baru dibuat
   - Klik "Add"
   - Centang "ALL PRIVILEGES"
   - Klik "Make Changes"

4. **Import SQL Schema**
   - Klik "phpMyAdmin" di cPanel
   - Pilih database `zyn_trade`
   - Klik tab "Import"
   - Klik "Choose File"
   - Pilih file `database/zyn_trade.sql`
   - Klik "Go"

---

## 4. Konfigurasi

### Edit File config.php
1. Buka File Manager di cPanel
2. Masuk ke folder `includes`
3. Klik kanan file `config.php` → Edit
4. Ubah bagian berikut:

```php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'nama_database_anda');    // Ganti!
define('DB_USER', 'username_database');     // Ganti!
define('DB_PASS', 'password_database');     // Ganti!

// Site Configuration
define('SITE_URL', 'https://zyntrading.com');  // Ganti dengan domain Anda
define('SITE_NAME', 'ZYN Trade System');

// Affiliate Link (ganti dengan link affiliate OlympTrade Anda)
define('OLYMPTRADE_AFFILIATE', 'https://olymptrade.com/?affiliate_id=XXXXX');
```

5. Klik "Save Changes"

### Set Folder Permissions
Beberapa folder perlu permission khusus:
1. Di File Manager, klik kanan folder
2. Pilih "Permissions" atau "Change Permissions"
3. Set permission:
   - `uploads/` → 755
   - `logs/` → 755
   - `backups/` → 755

---

## 5. Google Analytics

### Cara Mendapatkan Measurement ID
1. Buka https://analytics.google.com
2. Klik "Start measuring" atau "Mulai mengukur"
3. Buat akun baru (isi nama bisnis Anda)
4. Buat Property baru:
   - Property name: ZYN Trade
   - Time zone: Jakarta (GMT+7)
   - Currency: USD
5. Pilih "Web" sebagai platform
6. Isi URL website Anda
7. Klik "Create Stream"
8. Copy **Measurement ID** (format: G-XXXXXXXXXX)

### Setup di Website
1. Buka file `includes/analytics.php`
2. Cari baris:
   ```php
   const GA_MEASUREMENT_ID = 'G-XXXXXXXXXX';
   ```
3. Ganti `G-XXXXXXXXXX` dengan ID Anda
4. Save file

### Cara Cek Analytics Bekerja
1. Buka website Anda
2. Buka Google Analytics
3. Klik "Realtime" di sidebar
4. Anda akan melihat 1 user aktif (itu Anda!)

---

## 6. PWA & Push Notifications

### Apa itu PWA?
PWA (Progressive Web App) membuat website bisa diinstall seperti aplikasi di HP.

### Setup PWA
PWA sudah otomatis aktif! User tinggal:
1. Buka website di Chrome HP
2. Klik titik tiga (menu)
3. Pilih "Add to Home screen" atau "Install app"

### Push Notifications (Lanjutan)
Untuk push notifications, Anda perlu:
1. Daftar di https://web-push-codelab.glitch.me/
2. Generate VAPID keys
3. Update di `sw.js`:
   ```javascript
   const vapidPublicKey = 'YOUR_VAPID_PUBLIC_KEY';
   ```

---

## 7. Backup Otomatis

### Cara Setup Cron Job di cPanel
1. Login cPanel
2. Klik "Cron Jobs"
3. Di bagian "Add New Cron Job":
   - Common Settings: Once Per Day
   - Command: `php /home/username/public_html/admin/backup.php --cron`
   (ganti `username` dengan username cPanel Anda)
4. Klik "Add New Cron Job"

### Backup Manual
1. Login sebagai admin
2. Buka `/admin/backup.php`
3. Klik "Backup Sekarang"

---

## 8. Badges & Achievements

### Setup Database Badges
1. Buka phpMyAdmin di cPanel
2. Pilih database Anda
3. Klik tab "Import"
4. Pilih file `database/badges_schema.sql`
5. Klik "Go"

### Cara Kerja Badges
System badges otomatis memberikan badge saat user mencapai milestone:
- **Trading Milestones**: 1, 10, 50, 100, 500, 1000 trades
- **Profit Milestones**: $100, $500, $1K, $5K, $10K profit
- **Win Rate**: 60%, 70%, 80%, 90%+ win rate
- **Streak**: 5, 10, 20 winning trades berturut-turut
- **Special**: VIP member, referral ambassador, dll

### Badge Ranks
| Rank | Points Required |
|------|-----------------|
| Rookie | 0-49 |
| Bronze | 50+ |
| Silver | 200+ |
| Gold | 500+ |
| Platinum | 1,000+ |
| Diamond | 2,000+ |

### Halaman Badges
- `/badges.php` - Lihat semua badges
- `/leaderboard.php?view=badges` - Badge leaderboard

---

## 9. Domain & SSL

### Cara Menghubungkan Domain
1. **Di Registrar Domain (Niagahoster, GoDaddy, etc)**:
   - Login ke panel registrar
   - Cari menu "DNS" atau "Nameservers"
   - Ganti nameserver ke hosting Anda:
     ```
     ns1.hostinganda.com
     ns2.hostinganda.com
     ```

2. **Di cPanel Hosting**:
   - Klik "Domains" atau "Addon Domains"
   - Tambahkan domain Anda

### Aktifkan SSL (HTTPS)
1. Di cPanel, cari "SSL/TLS" atau "Let's Encrypt"
2. Klik "Issue" atau "Generate" untuk domain Anda
3. Tunggu beberapa menit
4. SSL aktif!

### Force HTTPS
Buka file `.htaccess`, hapus tanda `#` pada baris:
```apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## 10. Troubleshooting

### Error: "500 Internal Server Error"
**Penyebab**: Biasanya error PHP atau .htaccess
**Solusi**:
1. Cek file `logs/app.log` untuk detail error
2. Pastikan PHP version 7.4+
3. Cek permission folder (755)

### Error: "Database Connection Failed"
**Penyebab**: Config database salah
**Solusi**:
1. Cek `includes/config.php`
2. Pastikan DB_NAME, DB_USER, DB_PASS benar
3. Cek user sudah ditambahkan ke database

### Halaman Blank (Putih)
**Penyebab**: PHP error tapi display_errors off
**Solusi**:
1. Tambahkan di awal file PHP:
   ```php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```
2. Atau cek `logs/app.log`

### CSS/JS Tidak Load
**Penyebab**: Path salah atau cache
**Solusi**:
1. Clear browser cache (Ctrl+Shift+R)
2. Cek path di Network tab (F12 → Network)

### Form Tidak Bekerja
**Penyebab**: CSRF token expired
**Solusi**:
1. Refresh halaman
2. Coba lagi

---

## Fitur yang Sudah Tersedia

| Fitur | Status | File |
|-------|--------|------|
| Multi-language | ✅ | `lang/`, `includes/language.php` |
| CSRF Protection | ✅ | `includes/security.php` |
| Toast Notifications | ✅ | `assets/js/components.js` |
| Cookie Consent | ✅ | `assets/js/components.js` |
| PWA Support | ✅ | `manifest.json`, `sw.js` |
| Push Notifications | ✅ | `sw.js` |
| Error Logging | ✅ | `includes/logger.php` |
| Database Backup | ✅ | `admin/backup.php` |
| SEO Optimization | ✅ | `includes/header.php` |
| Sitemap | ✅ | `sitemap.php` |
| robots.txt | ✅ | `robots.txt` |
| 404/500 Pages | ✅ | `404.php`, `500.php` |
| Google Analytics | ✅ | `includes/analytics.php` |
| Referral Program | ✅ | `includes/referral.php` |
| Badges/Achievements | ✅ | `includes/badges.php` |
| PDF Export | ✅ | `includes/pdf-export.php` |
| Dashboard Widgets | ✅ | `includes/dashboard-widgets.php` |
| Anti-Inspect | ✅ | `includes/footer.php` |

---

## Butuh Bantuan?

Jika ada pertanyaan atau butuh bantuan:
1. Buat issue di GitHub repository
2. Hubungi via WhatsApp: +62xxx (isi nomor Anda)

---

**Selamat! Website ZYN Trade System Anda sudah siap!**
