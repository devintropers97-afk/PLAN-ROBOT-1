# ZYN Trade System

**Precision Over Emotion**

Automated trading robot platform for OlympTrade with 10 powerful algorithmic strategies.

![ZYN Trade System](assets/images/preview.png)

---

## Features

- **Zero Emotion Trading** - Rule-based algorithmic system
- **10 Trading Strategies** - From conservative to aggressive
- **Full Automation** - Set it and forget it
- **Risk Management** - Stop loss, take profit, daily limits
- **Real-Time Statistics** - Track your performance
- **Admin Panel** - User verification system
- **Multi-Language** - English, Indonesian, Spanish, Portuguese

---

## Installation Guide (cPanel)

### Step 1: Download

Download the ZIP file from GitHub and extract it.

### Step 2: Upload to cPanel

1. Login to your cPanel
2. Go to **File Manager**
3. Navigate to `public_html` (or your domain folder)
4. Upload and extract the `zyn-trade-system` folder contents
5. Files should be in `public_html/` directly (not in a subfolder)

### Step 3: Create Database

1. In cPanel, go to **MySQL Databases**
2. Create a new database (e.g., `youruser_zyn_trade`)
3. Create a new database user
4. Add user to database with **ALL PRIVILEGES**
5. Note down: database name, username, password

### Step 4: Import Database Schema

1. Go to **phpMyAdmin** in cPanel
2. Select your new database
3. Click **Import** tab
4. Choose file: `database/zyn_trade.sql`
5. Click **Go** to import

### Step 5: Configure

Edit `includes/config.php` with your settings:

```php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'youruser_zyn_trade');
define('DB_USER', 'youruser_dbuser');
define('DB_PASS', 'your_password');

// Site Configuration
define('SITE_URL', 'https://yourdomain.com');
define('SITE_EMAIL', 'support@yourdomain.com');

// OlympTrade Affiliate Link - IMPORTANT!
define('OLYMPTRADE_AFFILIATE_LINK', 'https://olymptrade.com/partner/?affiliate_id=YOUR_ID');

// Telegram Support
define('TELEGRAM_SUPPORT', '@your_telegram_username');
```

### Step 6: Set Permissions

In File Manager, set permissions:
- `includes/config.php` - 644
- All folders - 755
- All PHP files - 644

### Step 7: Test

1. Visit your domain
2. Login with default admin:
   - Email: `admin@zyntrade.com`
   - Password: `admin123`
3. **IMPORTANT:** Change admin password immediately!

---

## Default Admin Login

- **Email:** admin@zyntrade.com
- **Password:** admin123

⚠️ **Change this password immediately after first login!**

---

## File Structure

```
zyn-trade-system/
├── index.php              # Landing page
├── login.php              # Login page
├── register.php           # Registration page
├── dashboard.php          # User dashboard
├── strategies.php         # Strategies page
├── pricing.php            # Pricing page
├── faq.php               # FAQ page
├── profile.php           # User profile
├── settings.php          # User settings
├── logout.php            # Logout handler
├── terms.php             # Terms of Service
├── privacy.php           # Privacy Policy
├── disclaimer.php        # Risk Disclaimer
├── refund.php            # Refund Policy
├── assets/
│   ├── css/
│   │   ├── style.css     # Main stylesheet
│   │   └── dashboard.css # Dashboard stylesheet
│   ├── js/
│   │   ├── main.js       # Main JavaScript
│   │   └── dashboard.js  # Dashboard JavaScript
│   └── images/           # Image assets
├── includes/
│   ├── config.php        # Configuration file
│   ├── functions.php     # Helper functions
│   ├── header.php        # Header template
│   └── footer.php        # Footer template
├── admin/
│   ├── index.php         # Admin dashboard
│   └── verify-users.php  # User verification
├── database/
│   └── zyn_trade.sql     # Database schema
└── README.md             # This file
```

---

## Configuration Options

### Database Settings
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'database_name');
define('DB_USER', 'database_user');
define('DB_PASS', 'database_password');
```

### Site Settings
```php
define('SITE_NAME', 'ZYN Trade System');
define('SITE_TAGLINE', 'Precision Over Emotion');
define('SITE_URL', 'https://yourdomain.com');
define('SITE_EMAIL', 'support@yourdomain.com');
```

### Trading Settings
```php
define('MIN_DEPOSIT', 10);           // Minimum deposit ($)
define('TRIAL_DURATION', 30);         // Trial duration (days)
define('PRICE_STARTER', 29);          // Starter package price
define('PRICE_PRO', 79);              // Pro package price
define('PRICE_ELITE', 149);           // Elite package price
```

### Security Settings
```php
define('WATERMARK_ENABLED', true);    // Signal watermarks
define('MULTI_ACCOUNT_CHECK', false); // Multi-account detection
```

---

## User Verification Flow

1. User registers with OlympTrade ID
2. Admin receives notification in Admin Panel
3. Admin verifies OlympTrade account manually
4. Admin approves or rejects with reason code
5. User receives email notification
6. If approved, free trial activates

### Rejection Codes

| Code | Reason |
|------|--------|
| R01 | ID not found |
| R02 | Not registered via affiliate |
| R03 | Deposit below $10 |
| R04 | ID already used |
| R05 | Incomplete data |
| R06 | OlympTrade account inactive |
| R07 | Country mismatch |
| R08 | Invalid screenshot |
| R09 | Duplicate account |
| R10 | Custom reason |

---

## Support

- **Telegram:** Contact support via Telegram
- **Email:** support@yourdomain.com

---

## Security Notes

1. Always use HTTPS in production
2. Change default admin password immediately
3. Set proper file permissions
4. Keep PHP and MySQL updated
5. Regular backups recommended

---

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- PDO PHP Extension
- cURL PHP Extension
- mbstring PHP Extension

---

## License

This software is proprietary. Unauthorized distribution is prohibited.

---

## Version

**Version 2.1** - December 2024

---

*ZYN Trade System - Precision Over Emotion*
