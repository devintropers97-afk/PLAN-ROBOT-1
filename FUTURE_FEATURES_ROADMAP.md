# 🚀 INSTRUKSI CLAUDE CODE - FITUR MASA DEPAN
## ZYN TRADE SYSTEM - Development Roadmap

**Tanggal:** 18 Desember 2024
**Sumber:** Analisis 15 File Plan (materi + FINAL ZYN TRADE SYSTEM)
**Status:** Untuk dikerjakan SETELAH fitur wajib selesai

---

# 📋 DAFTAR FITUR MASA DEPAN

## Ringkasan

| Prioritas | Jumlah Fitur | Estimasi Waktu |
|-----------|--------------|----------------|
| 🟡 MEDIUM (Segera Setelah Launch) | 6 fitur | 2-3 minggu |
| 🟢 LOW (Nice to Have) | 8 fitur | 1-2 bulan |
| 🔵 TEKNIS (Backend Enhancement) | 4 fitur | Ongoing |
| **TOTAL** | **18 fitur** | **2-3 bulan** |

---

# 🟡 PRIORITAS MEDIUM - Segera Setelah Launch

---

## FITUR M1: MULTI-BAHASA (8 BAHASA)

### Konteks dari Plan
```
Dari "FINAL ZYN TRADE SYSTEM":
- Target: 8 bahasa
- Bahasa: Indonesia, English, Melayu, Vietnam, Thailand, Tagalog, Hindi, Arabic
- Saat ini: Hanya 2 (ID, EN)
```

### File yang Perlu Dibuat/Diubah
```
lang/
├── id.php      ✅ Ada
├── en.php      ✅ Ada
├── ms.php      ❌ Buat baru (Melayu)
├── vi.php      ❌ Buat baru (Vietnam)
├── th.php      ❌ Buat baru (Thailand)
├── tl.php      ❌ Buat baru (Tagalog)
├── hi.php      ❌ Buat baru (Hindi)
└── ar.php      ❌ Buat baru (Arabic)
```

### Implementasi

**LANGKAH 1:** Buat template language file

**File:** `lang/ms.php` (Melayu - contoh)
```php
<?php
/**
 * ZYN Trade System - Malay Language Pack
 * Bahasa Melayu (Malaysia)
 */

return [
    // ========== GENERAL ==========
    'app_name' => 'ZYN Trade System',
    'dashboard' => 'Papan Pemuka',
    'settings' => 'Tetapan',
    'statistics' => 'Statistik',
    'logout' => 'Log Keluar',
    'save' => 'Simpan',
    'cancel' => 'Batal',
    'confirm' => 'Sahkan',
    'success' => 'Berjaya',
    'error' => 'Ralat',
    'warning' => 'Amaran',

    // ========== AUTH ==========
    'login' => 'Log Masuk',
    'register' => 'Daftar',
    'email' => 'E-mel',
    'password' => 'Kata Laluan',
    'license_key' => 'Kunci Lesen',
    'forgot_password' => 'Lupa Kata Laluan?',
    'remember_me' => 'Ingat Saya',

    // ========== DASHBOARD ==========
    'robot_status' => 'Status Robot',
    'robot_running' => 'Robot Berjalan',
    'robot_stopped' => 'Robot Berhenti',
    'robot_paused' => 'Robot Dijeda',
    'start_robot' => 'Mulakan Robot',
    'stop_robot' => 'Hentikan Robot',
    'daily_profit' => 'Keuntungan Harian',
    'total_trades' => 'Jumlah Dagangan',
    'win_rate' => 'Kadar Kemenangan',

    // ========== TRADING ==========
    'market' => 'Pasaran',
    'timeframe' => 'Tempoh Masa',
    'strategy' => 'Strategi',
    'trade_amount' => 'Jumlah Dagangan',
    'take_profit' => 'Ambil Untung',
    'max_loss' => 'Kerugian Maksimum',
    'current_pnl' => 'PnL Semasa',

    // ========== SCHEDULE ==========
    'schedule_mode' => 'Mod Jadual',
    'auto_24h' => '24/7 Automatik',
    'best_hours' => 'Waktu Terbaik',
    'custom_single' => 'Custom Tunggal',
    'multi_session' => 'Multi-Sesi',
    'per_day' => 'Setiap Hari Berbeza',
    'monday' => 'Isnin',
    'tuesday' => 'Selasa',
    'wednesday' => 'Rabu',
    'thursday' => 'Khamis',
    'friday' => 'Jumaat',
    'saturday' => 'Sabtu',
    'sunday' => 'Ahad',

    // ========== AUTO-PAUSE ==========
    'auto_pause' => 'Jeda Automatik',
    'tp_reached' => 'Sasaran Untung Tercapai!',
    'ml_reached' => 'Had Kerugian Tercapai',
    'resume_robot' => 'Sambung Robot',
    'resume_next_session' => 'Sambung Sesi Seterusnya',
    'resume_next_day' => 'Sambung Esok',
    'resume_manual' => 'Sambung Manual Sahaja',

    // ========== TIERS ==========
    'tier_free' => 'Percuma',
    'tier_pro' => 'Pro',
    'tier_elite' => 'Elite',
    'tier_vip' => 'VIP',
    'upgrade' => 'Naik Taraf',
    'current_plan' => 'Pelan Semasa',

    // ========== VERIFICATION ==========
    'verification_pending' => 'Pengesahan Menunggu',
    'verification_approved' => 'Pengesahan Diluluskan',
    'verification_rejected' => 'Pengesahan Ditolak',
    'upload_proof' => 'Muat Naik Bukti',

    // ========== ERRORS ==========
    'error_invalid_license' => 'Kunci lesen tidak sah',
    'error_demo_account' => 'Akaun demo tidak dibenarkan. Sila gunakan akaun sebenar.',
    'error_session_expired' => 'Sesi tamat tempoh. Sila log masuk semula.',

    // ========== MESSAGES ==========
    'msg_settings_saved' => 'Tetapan berjaya disimpan!',
    'msg_robot_started' => 'Robot berjaya dimulakan!',
    'msg_robot_stopped' => 'Robot berjaya dihentikan.',
];
```

**LANGKAH 2:** Update Language Selector di Header

**File:** `includes/header.php` atau `templates/header.php`
```php
<!-- Language Selector -->
<div class="dropdown">
    <button class="btn btn-outline-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
        <i class="fas fa-globe me-1"></i>
        <?php echo strtoupper($_SESSION['lang'] ?? 'ID'); ?>
    </button>
    <ul class="dropdown-menu dropdown-menu-dark">
        <li><a class="dropdown-item <?php echo ($_SESSION['lang'] ?? 'id') === 'id' ? 'active' : ''; ?>" href="?lang=id">
            🇮🇩 Bahasa Indonesia
        </a></li>
        <li><a class="dropdown-item <?php echo ($_SESSION['lang'] ?? '') === 'en' ? 'active' : ''; ?>" href="?lang=en">
            🇬🇧 English
        </a></li>
        <li><a class="dropdown-item <?php echo ($_SESSION['lang'] ?? '') === 'ms' ? 'active' : ''; ?>" href="?lang=ms">
            🇲🇾 Bahasa Melayu
        </a></li>
        <li><a class="dropdown-item <?php echo ($_SESSION['lang'] ?? '') === 'vi' ? 'active' : ''; ?>" href="?lang=vi">
            🇻🇳 Tiếng Việt
        </a></li>
        <li><a class="dropdown-item <?php echo ($_SESSION['lang'] ?? '') === 'th' ? 'active' : ''; ?>" href="?lang=th">
            🇹🇭 ภาษาไทย
        </a></li>
        <li><a class="dropdown-item <?php echo ($_SESSION['lang'] ?? '') === 'tl' ? 'active' : ''; ?>" href="?lang=tl">
            🇵🇭 Tagalog
        </a></li>
        <li><a class="dropdown-item <?php echo ($_SESSION['lang'] ?? '') === 'hi' ? 'active' : ''; ?>" href="?lang=hi">
            🇮🇳 हिंदी
        </a></li>
        <li><a class="dropdown-item <?php echo ($_SESSION['lang'] ?? '') === 'ar' ? 'active' : ''; ?>" href="?lang=ar">
            🇸🇦 العربية
        </a></li>
    </ul>
</div>
```

**LANGKAH 3:** Buat Language Helper Function

**File:** `includes/language.php`
```php
<?php
/**
 * Language Helper Functions
 */

// Supported languages
define('SUPPORTED_LANGUAGES', ['id', 'en', 'ms', 'vi', 'th', 'tl', 'hi', 'ar']);
define('DEFAULT_LANGUAGE', 'id');
define('RTL_LANGUAGES', ['ar']); // Right-to-left languages

/**
 * Initialize language system
 */
function initLanguage() {
    // Check URL parameter
    if (isset($_GET['lang']) && in_array($_GET['lang'], SUPPORTED_LANGUAGES)) {
        $_SESSION['lang'] = $_GET['lang'];

        // Save to database if user logged in
        if (isset($_SESSION['user_id'])) {
            saveUserLanguage($_SESSION['user_id'], $_GET['lang']);
        }
    }

    // Set default if not set
    if (!isset($_SESSION['lang'])) {
        $_SESSION['lang'] = DEFAULT_LANGUAGE;
    }

    // Load language file
    loadLanguageFile($_SESSION['lang']);
}

/**
 * Load language file into global $lang array
 */
function loadLanguageFile($langCode) {
    global $lang;

    $langFile = __DIR__ . '/../lang/' . $langCode . '.php';

    if (file_exists($langFile)) {
        $lang = require $langFile;
    } else {
        // Fallback to default
        $lang = require __DIR__ . '/../lang/' . DEFAULT_LANGUAGE . '.php';
    }
}

/**
 * Get translated string
 * Usage: __('dashboard') returns 'Dashboard' or 'Papan Pemuka' based on language
 */
function __($key, $replacements = []) {
    global $lang;

    $string = $lang[$key] ?? $key;

    // Replace placeholders
    foreach ($replacements as $placeholder => $value) {
        $string = str_replace(':' . $placeholder, $value, $string);
    }

    return $string;
}

/**
 * Echo translated string
 */
function _e($key, $replacements = []) {
    echo __($key, $replacements);
}

/**
 * Check if current language is RTL
 */
function isRTL() {
    return in_array($_SESSION['lang'] ?? DEFAULT_LANGUAGE, RTL_LANGUAGES);
}

/**
 * Save user language preference
 */
function saveUserLanguage($userId, $langCode) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE users SET language = ? WHERE id = ?");
    $stmt->execute([$langCode, $userId]);
}
```

**LANGKAH 4:** Update semua file PHP untuk menggunakan translation

**Contoh di dashboard.php:**
```php
<!-- Sebelum -->
<h1>Dashboard</h1>
<p>Robot Status: Running</p>

<!-- Sesudah -->
<h1><?php _e('dashboard'); ?></h1>
<p><?php _e('robot_status'); ?>: <?php _e('robot_running'); ?></p>
```

### Estimasi Waktu
- Template untuk 6 bahasa baru: 2-3 hari
- Update semua file PHP: 2-3 hari
- Testing: 1 hari
- **Total: 5-7 hari**

---

## FITUR M2: PUSH NOTIFICATION (Firebase)

### Konteks dari Plan
```
Dari "FINAL ZYN TRADE SYSTEM":
- Push notification untuk trade alerts
- Support web push dan mobile
- Notif saat TP/ML tercapai, signal baru, dll
```

### File yang Perlu Dibuat
```
includes/
├── firebase.php           ❌ Buat baru
├── notification.php       ❌ Buat baru
assets/
├── js/
│   └── firebase-messaging.js  ❌ Buat baru
firebase-messaging-sw.js   ❌ Service Worker (root)
```

### Implementasi

**LANGKAH 1:** Setup Firebase Project
1. Buka https://console.firebase.google.com
2. Create new project "ZYN Trade System"
3. Enable Cloud Messaging
4. Get Server Key dan Sender ID

**LANGKAH 2:** Tambah Firebase Config

**File:** `includes/firebase.php`
```php
<?php
/**
 * Firebase Cloud Messaging Integration
 */

class FirebasePush {

    private $serverKey;
    private $fcmUrl = 'https://fcm.googleapis.com/fcm/send';

    public function __construct() {
        $this->serverKey = getenv('FIREBASE_SERVER_KEY') ?: 'YOUR_SERVER_KEY_HERE';
    }

    /**
     * Send push notification to single device
     */
    public function sendToDevice($deviceToken, $title, $body, $data = []) {
        $payload = [
            'to' => $deviceToken,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'icon' => '/assets/images/icon-192.png',
                'click_action' => $data['url'] ?? '/'
            ],
            'data' => $data
        ];

        return $this->sendRequest($payload);
    }

    /**
     * Send to multiple devices
     */
    public function sendToMultiple($deviceTokens, $title, $body, $data = []) {
        $payload = [
            'registration_ids' => $deviceTokens,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'icon' => '/assets/images/icon-192.png'
            ],
            'data' => $data
        ];

        return $this->sendRequest($payload);
    }

    /**
     * Send to topic subscribers
     */
    public function sendToTopic($topic, $title, $body, $data = []) {
        $payload = [
            'to' => '/topics/' . $topic,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'icon' => '/assets/images/icon-192.png'
            ],
            'data' => $data
        ];

        return $this->sendRequest($payload);
    }

    /**
     * Execute FCM request
     */
    private function sendRequest($payload) {
        $headers = [
            'Authorization: key=' . $this->serverKey,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->fcmUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, true);
    }
}

// ================================================================
// NOTIFICATION FUNCTIONS
// ================================================================

/**
 * Send Take Profit notification
 */
function sendTakeProfitNotification($userId, $amount) {
    $token = getUserFcmToken($userId);
    if (!$token) return false;

    $firebase = new FirebasePush();
    return $firebase->sendToDevice(
        $token,
        '🎯 Take Profit Tercapai!',
        'Selamat! Profit hari ini: $' . number_format($amount, 2),
        [
            'type' => 'take_profit',
            'amount' => $amount,
            'url' => '/dashboard.php'
        ]
    );
}

/**
 * Send Max Loss notification
 */
function sendMaxLossNotification($userId, $amount) {
    $token = getUserFcmToken($userId);
    if (!$token) return false;

    $firebase = new FirebasePush();
    return $firebase->sendToDevice(
        $token,
        '⚠️ Max Loss Tercapai',
        'Robot dihentikan. Loss: $' . number_format(abs($amount), 2),
        [
            'type' => 'max_loss',
            'amount' => $amount,
            'url' => '/dashboard.php'
        ]
    );
}

/**
 * Send Trade Executed notification
 */
function sendTradeNotification($userId, $trade) {
    $token = getUserFcmToken($userId);
    if (!$token) return false;

    $icon = $trade['result'] === 'win' ? '✅' : '❌';
    $resultText = $trade['result'] === 'win' ? 'WIN' : 'LOSS';

    $firebase = new FirebasePush();
    return $firebase->sendToDevice(
        $token,
        $icon . ' Trade ' . $resultText,
        $trade['market'] . ' ' . $trade['direction'] . ' - $' . number_format($trade['profit'], 2),
        [
            'type' => 'trade',
            'trade_id' => $trade['id'],
            'url' => '/statistics.php'
        ]
    );
}

/**
 * Get user's FCM token
 */
function getUserFcmToken($userId) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT fcm_token FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['fcm_token'] ?? null;
}

/**
 * Save user's FCM token
 */
function saveUserFcmToken($userId, $token) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE users SET fcm_token = ? WHERE id = ?");
    return $stmt->execute([$token, $userId]);
}
```

**LANGKAH 3:** Frontend Firebase Setup

**File:** `assets/js/firebase-messaging.js`
```javascript
/**
 * Firebase Cloud Messaging - Client Side
 */

// Firebase config (from Firebase Console)
const firebaseConfig = {
    apiKey: "YOUR_API_KEY",
    authDomain: "zyn-trade-system.firebaseapp.com",
    projectId: "zyn-trade-system",
    storageBucket: "zyn-trade-system.appspot.com",
    messagingSenderId: "YOUR_SENDER_ID",
    appId: "YOUR_APP_ID"
};

// Initialize Firebase
firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();

/**
 * Request notification permission and get token
 */
async function initPushNotifications() {
    try {
        // Request permission
        const permission = await Notification.requestPermission();

        if (permission !== 'granted') {
            console.log('Notification permission denied');
            return null;
        }

        // Get FCM token
        const token = await messaging.getToken({
            vapidKey: 'YOUR_VAPID_KEY'
        });

        if (token) {
            console.log('FCM Token:', token);
            // Save to server
            await saveTokenToServer(token);
            return token;
        }

    } catch (error) {
        console.error('Error getting FCM token:', error);
        return null;
    }
}

/**
 * Save FCM token to server
 */
async function saveTokenToServer(token) {
    try {
        const response = await fetch('/api/save-fcm-token.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ token: token })
        });

        const result = await response.json();
        console.log('Token saved:', result);

    } catch (error) {
        console.error('Error saving token:', error);
    }
}

/**
 * Handle foreground messages
 */
messaging.onMessage((payload) => {
    console.log('Message received:', payload);

    // Show custom notification UI
    showInAppNotification(payload.notification);
});

/**
 * Show in-app notification toast
 */
function showInAppNotification(notification) {
    // Create notification element
    const toast = document.createElement('div');
    toast.className = 'push-notification-toast';
    toast.innerHTML = `
        <div class="notification-content">
            <strong>${notification.title}</strong>
            <p>${notification.body}</p>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;

    document.body.appendChild(toast);

    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.remove();
    }, 5000);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Check if notifications are supported
    if ('Notification' in window && 'serviceWorker' in navigator) {
        initPushNotifications();
    }
});
```

**LANGKAH 4:** Service Worker

**File:** `firebase-messaging-sw.js` (di root folder)
```javascript
/**
 * Firebase Messaging Service Worker
 * Handles background push notifications
 */

importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging-compat.js');

firebase.initializeApp({
    apiKey: "YOUR_API_KEY",
    authDomain: "zyn-trade-system.firebaseapp.com",
    projectId: "zyn-trade-system",
    storageBucket: "zyn-trade-system.appspot.com",
    messagingSenderId: "YOUR_SENDER_ID",
    appId: "YOUR_APP_ID"
});

const messaging = firebase.messaging();

// Handle background messages
messaging.onBackgroundMessage((payload) => {
    console.log('Background message:', payload);

    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: '/assets/images/icon-192.png',
        badge: '/assets/images/badge-72.png',
        data: payload.data
    };

    self.registration.showNotification(notificationTitle, notificationOptions);
});

// Handle notification click
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const url = event.notification.data?.url || '/dashboard.php';

    event.waitUntil(
        clients.openWindow(url)
    );
});
```

**LANGKAH 5:** Database Migration

```sql
-- Tambah kolom FCM token
ALTER TABLE users
ADD COLUMN IF NOT EXISTS fcm_token VARCHAR(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS push_enabled TINYINT(1) DEFAULT 1;

-- Table untuk notification history
CREATE TABLE IF NOT EXISTS notification_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    body TEXT,
    data JSON,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### Estimasi Waktu
- Setup Firebase: 1 hari
- Backend integration: 2 hari
- Frontend + Service Worker: 1 hari
- Testing: 1 hari
- **Total: 5 hari**

---

## FITUR M3: TWO-FACTOR AUTHENTICATION (2FA)

### Konteks dari Plan
```
Dari "FINAL ZYN TRADE SYSTEM":
- Google Authenticator support
- TOTP (Time-based One-Time Password)
- Backup codes untuk recovery
```

### File yang Perlu Dibuat
```
includes/
├── two_factor.php         ❌ Buat baru
pages/
├── enable-2fa.php         ❌ Buat baru
├── verify-2fa.php         ❌ Buat baru
```

### Implementasi

**LANGKAH 1:** Install library (via Composer atau manual)

```bash
composer require pragmarx/google2fa
composer require bacon/bacon-qr-code
```

**LANGKAH 2:** 2FA Helper Class

**File:** `includes/two_factor.php`
```php
<?php
/**
 * Two-Factor Authentication Helper
 * Using Google Authenticator (TOTP)
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TwoFactorAuth {

    private $google2fa;
    private $issuer = 'ZYN Trade System';

    public function __construct() {
        $this->google2fa = new Google2FA();
    }

    /**
     * Generate new secret key for user
     */
    public function generateSecretKey() {
        return $this->google2fa->generateSecretKey();
    }

    /**
     * Generate QR code URL for Google Authenticator
     */
    public function getQRCodeUrl($email, $secretKey) {
        return $this->google2fa->getQRCodeUrl(
            $this->issuer,
            $email,
            $secretKey
        );
    }

    /**
     * Generate QR code image as SVG
     */
    public function getQRCodeSVG($email, $secretKey) {
        $qrCodeUrl = $this->getQRCodeUrl($email, $secretKey);

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        return $writer->writeString($qrCodeUrl);
    }

    /**
     * Verify OTP code
     */
    public function verifyCode($secretKey, $code) {
        return $this->google2fa->verifyKey($secretKey, $code);
    }

    /**
     * Generate backup codes
     */
    public function generateBackupCodes($count = 10) {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(4))); // 8 char codes
        }
        return $codes;
    }
}

// ================================================================
// DATABASE FUNCTIONS
// ================================================================

/**
 * Enable 2FA for user
 */
function enable2FA($userId, $secretKey, $backupCodes) {
    $pdo = getDBConnection();

    $stmt = $pdo->prepare("
        UPDATE users SET
            two_factor_enabled = 1,
            two_factor_secret = ?,
            two_factor_backup_codes = ?,
            two_factor_enabled_at = NOW()
        WHERE id = ?
    ");

    return $stmt->execute([
        $secretKey,
        json_encode($backupCodes),
        $userId
    ]);
}

/**
 * Disable 2FA for user
 */
function disable2FA($userId) {
    $pdo = getDBConnection();

    $stmt = $pdo->prepare("
        UPDATE users SET
            two_factor_enabled = 0,
            two_factor_secret = NULL,
            two_factor_backup_codes = NULL,
            two_factor_enabled_at = NULL
        WHERE id = ?
    ");

    return $stmt->execute([$userId]);
}

/**
 * Check if user has 2FA enabled
 */
function is2FAEnabled($userId) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT two_factor_enabled FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return (bool)($result['two_factor_enabled'] ?? false);
}

/**
 * Get user's 2FA secret
 */
function get2FASecret($userId) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT two_factor_secret FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['two_factor_secret'] ?? null;
}

/**
 * Use backup code (one-time use)
 */
function useBackupCode($userId, $code) {
    $pdo = getDBConnection();

    // Get current backup codes
    $stmt = $pdo->prepare("SELECT two_factor_backup_codes FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $codes = json_decode($result['two_factor_backup_codes'] ?? '[]', true);

    // Check if code exists
    $index = array_search(strtoupper($code), $codes);
    if ($index === false) {
        return false;
    }

    // Remove used code
    unset($codes[$index]);
    $codes = array_values($codes);

    // Update database
    $stmt = $pdo->prepare("UPDATE users SET two_factor_backup_codes = ? WHERE id = ?");
    $stmt->execute([json_encode($codes), $userId]);

    return true;
}
```

**LANGKAH 6:** Database Migration

```sql
-- Tambah kolom 2FA
ALTER TABLE users
ADD COLUMN IF NOT EXISTS two_factor_enabled TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS two_factor_secret VARCHAR(32) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS two_factor_backup_codes JSON DEFAULT NULL,
ADD COLUMN IF NOT EXISTS two_factor_enabled_at TIMESTAMP NULL;
```

### Estimasi Waktu
- Library setup: 0.5 hari
- Backend functions: 1 hari
- UI pages: 1 hari
- Login flow update: 0.5 hari
- Testing: 1 hari
- **Total: 4 hari**

---

## FITUR M4: ZYN NEWS HUNTER (Premium Add-on)

### Konteks dari Plan
```
Dari "FINAL ZYN TRADE SYSTEM":
- Premium add-on untuk berita market
- News Coverage: Economic calendar, market news, central bank decisions
- Impact analysis on trading
- Separate subscription dari tier utama
```

### File yang Perlu Dibuat
```
modules/
├── news-hunter/
│   ├── index.php          ❌ Main page
│   ├── api.php            ❌ News API integration
│   ├── analyzer.php       ❌ Impact analyzer
│   └── settings.php       ❌ News settings
includes/
├── news_hunter.php        ❌ Helper functions
```

### Database Migration

```sql
-- News Hunter subscriptions
CREATE TABLE IF NOT EXISTS news_hunter_subscriptions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    plan VARCHAR(20) NOT NULL DEFAULT 'monthly',
    price DECIMAL(10,2) NOT NULL,
    starts_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    payment_id VARCHAR(100),
    status ENUM('active', 'expired', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- News events cache
CREATE TABLE IF NOT EXISTS news_events_cache (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_date DATE NOT NULL,
    event_time TIME,
    currency VARCHAR(10),
    title VARCHAR(255),
    impact ENUM('High', 'Medium', 'Low'),
    forecast VARCHAR(50),
    previous VARCHAR(50),
    actual VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_event_date (event_date)
);
```

### Estimasi Waktu
- API Integration: 2 hari
- UI Pages: 2 hari
- Subscription system: 1 hari
- Testing: 1 hari
- **Total: 6 hari**

---

## FITUR M5: SMART NOTIFICATION SETTINGS

### Konteks dari Plan
```
User bisa customize notifikasi:
- Toggle notif per jenis (trade, TP, ML, news)
- Quiet hours (jangan kirim notif jam tertentu)
- Email vs Push preference
```

### Database

```sql
ALTER TABLE users
ADD COLUMN IF NOT EXISTS notification_settings JSON DEFAULT NULL;

-- Default JSON structure:
-- {
--   "trade": true,
--   "take_profit": true,
--   "max_loss": true,
--   "daily_summary": true,
--   "quiet_hours": {
--     "enabled": false,
--     "start": "22:00",
--     "end": "07:00"
--   },
--   "channels": {
--     "push": true,
--     "email": false,
--     "telegram": false
--   }
-- }
```

### Estimasi Waktu: 2 hari

---

## FITUR M6: WEBHOOK INTEGRATION

### Konteks dari Plan
```
Webhook untuk integrasi third-party:
- Trading events webhook
- Custom URL callback
- Support untuk Discord, Slack, custom
```

### Database

```sql
CREATE TABLE IF NOT EXISTS webhooks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    url VARCHAR(500) NOT NULL,
    type ENUM('custom', 'discord', 'slack') DEFAULT 'custom',
    events JSON NOT NULL, -- ['trade_win', 'trade_loss', 'take_profit', etc]
    secret_key VARCHAR(64),
    enabled TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS webhook_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    webhook_id INT NOT NULL,
    event VARCHAR(50),
    http_code INT,
    response TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (webhook_id) REFERENCES webhooks(id)
);
```

### Estimasi Waktu: 3 hari

---

# 🟢 PRIORITAS LOW - Nice to Have

---

## FITUR L1: ZYN ACADEMY (Edukasi)

### Konteks
```
Halaman edukasi untuk user:
- Tutorial trading
- Penjelasan strategi
- Video guides
- FAQ
```

### File Structure
```
academy/
├── index.php           # Main page
├── lessons/
│   ├── basics.php      # Trading basics
│   ├── strategies.php  # Strategy explanations
│   └── risk.php        # Risk management
├── videos.php          # Video tutorials
└── faq.php             # FAQ
```

### Estimasi Waktu: 3 hari

---

## FITUR L2: MARKET SENTIMENT INDICATOR

### Konteks
```
Indikator sentimen market:
- Bull/Bear meter
- Community sentiment
- Technical analysis summary
```

### Estimasi Waktu: 2 hari

---

## FITUR L3: IN-APP ANNOUNCEMENTS

### Konteks
```
Sistem pengumuman dalam app:
- Admin bisa post announcement
- Banner di dashboard
- Popup untuk important updates
```

### Estimasi Waktu: 2 hari

---

## FITUR L4: QUICK TRADE MODE

### Konteks
```
Mode trading cepat:
- 1-click trade
- Preset amounts
- Quick strategy switch
```

### Estimasi Waktu: 2 hari

---

## FITUR L5: CUSTOMIZABLE DASHBOARD

### Konteks
```
Dashboard yang bisa di-customize:
- Drag & drop widgets
- Hide/show sections
- Save layout
```

### Estimasi Waktu: 4 hari

---

## FITUR L6: STRATEGY PERFORMANCE COMPARISON

### Konteks
```
Perbandingan performa strategi:
- Side-by-side comparison
- Win rate per strategy
- Best performing strategy
```

### Estimasi Waktu: 2 hari

---

## FITUR L7: TELEGRAM BOT INTEGRATION

### Konteks
```
Bot Telegram untuk:
- Receive notifications
- Check status
- Basic commands (/status, /stop, /start)
```

### Estimasi Waktu: 4 hari

---

## FITUR L8: DARK/LIGHT MODE TOGGLE

### Konteks
```
Toggle antara dark dan light mode:
- User preference saved
- Smooth transition
```

### Estimasi Waktu: 1 hari

---

# 🔵 INOVASI TEKNIS (Backend Enhancement)

---

## FITUR T1: REAL-TIME WEBSOCKET

### Konteks
```
WebSocket untuk real-time updates:
- Live trade notifications
- Real-time PnL update
- No need to refresh page
```

### Estimasi Waktu: 5 hari

---

## FITUR T2: API RATE LIMITING ENHANCEMENT

### Konteks
```
Rate limiting yang lebih sophisticated:
- Per-user limits
- Per-endpoint limits
- Sliding window
```

### Estimasi Waktu: 2 hari

---

## FITUR T3: ADVANCED ANALYTICS DASHBOARD (Admin)

### Konteks
```
Dashboard analytics untuk admin:
- User growth charts
- Revenue tracking
- Strategy usage stats
```

### Estimasi Waktu: 4 hari

---

## FITUR T4: AUTOMATED BACKUP SYSTEM

### Konteks
```
Backup otomatis:
- Daily database backup
- Config backup
- Cloud storage integration
```

### Estimasi Waktu: 2 hari

---

# 📅 ROADMAP DEVELOPMENT

## Fase 1: Segera Setelah Launch (Minggu 1-3)
| Fitur | Waktu |
|-------|-------|
| Multi-Bahasa (8 bahasa) | 7 hari |
| Push Notification | 5 hari |
| Smart Notification Settings | 2 hari |

## Fase 2: Bulan Pertama (Minggu 4-6)
| Fitur | Waktu |
|-------|-------|
| Two-Factor Auth (2FA) | 4 hari |
| ZYN News Hunter | 6 hari |
| Webhook Integration | 3 hari |

## Fase 3: Bulan Kedua (Minggu 7-10)
| Fitur | Waktu |
|-------|-------|
| ZYN Academy | 3 hari |
| Telegram Bot | 4 hari |
| Dark/Light Toggle | 1 hari |
| In-App Announcements | 2 hari |

## Fase 4: Bulan Ketiga (Minggu 11-14)
| Fitur | Waktu |
|-------|-------|
| Real-time WebSocket | 5 hari |
| Customizable Dashboard | 4 hari |
| Strategy Comparison | 2 hari |
| Advanced Analytics | 4 hari |

---

# ✅ CHECKLIST TOTAL

## 🟡 MEDIUM (6 Fitur)
- [ ] M1: Multi-Bahasa 8 bahasa
- [ ] M2: Push Notification (Firebase)
- [ ] M3: Two-Factor Authentication
- [ ] M4: ZYN News Hunter
- [ ] M5: Smart Notification Settings
- [ ] M6: Webhook Integration

## 🟢 LOW (8 Fitur)
- [ ] L1: ZYN Academy
- [ ] L2: Market Sentiment
- [ ] L3: In-App Announcements
- [ ] L4: Quick Trade Mode
- [ ] L5: Customizable Dashboard
- [ ] L6: Strategy Comparison
- [ ] L7: Telegram Bot
- [ ] L8: Dark/Light Toggle

## 🔵 TEKNIS (4 Fitur)
- [ ] T1: Real-time WebSocket
- [ ] T2: API Rate Limiting
- [ ] T3: Advanced Analytics
- [ ] T4: Automated Backup

---

**TOTAL: 18 Fitur Masa Depan**
**Estimasi Total: 2-3 Bulan Development**

---

*Document created: 18 Desember 2024*
*Last updated: 18 Desember 2024*
