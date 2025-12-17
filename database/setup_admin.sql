-- =============================================
-- ZYN TRADE SYSTEM - CREATE ADMIN USER
-- Jalankan SQL ini di phpMyAdmin cPanel
-- =============================================

-- Insert Admin User dengan License Key
-- License Key Admin: ZYN-A-ADMN-2024
-- Setelah login, Anda bisa verifikasi user pending

INSERT INTO `users` (
    `license_key`,
    `email`,
    `password`,
    `fullname`,
    `phone`,
    `country`,
    `olymptrade_id`,
    `role`,
    `status`,
    `package`,
    `created_at`
) VALUES (
    'ZYN-A-ADMN-2024',
    'admin@zyntrade.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    'Admin ZYN Trade',
    '6281234567890',
    'ID',
    '999999999',
    'admin',
    'active',
    'vip',
    NOW()
);

-- Verifikasi admin berhasil dibuat
SELECT id, email, fullname, license_key, role, status FROM users WHERE role = 'admin';
