<?php
$page_title = 'System Settings';
require_once '../includes/header.php';

// Admin only
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$db = getDBConnection();
$message = '';
$error = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $settings = $_POST['settings'] ?? [];

    foreach ($settings as $key => $value) {
        $stmt = $db->prepare("UPDATE settings SET value = ? WHERE `key` = ?");
        $stmt->execute([$value, $key]);
    }

    $message = "Settings updated successfully!";
}

// Get all settings
$stmt = $db->query("SELECT * FROM settings ORDER BY `key`");
$allSettings = [];
while ($row = $stmt->fetch()) {
    $allSettings[$row['key']] = $row;
}

// Group settings
$settingGroups = [
    'Site Settings' => ['site_name', 'site_tagline', 'currency'],
    'OlympTrade' => ['affiliate_id', 'olymptrade_affiliate_link', 'olymptrade_affiliate_link_id'],
    'Telegram' => ['telegram_channel', 'telegram_channel_id', 'telegram_support'],
    'Trading Rules' => ['min_deposit', 'allowed_markets', 'allowed_timeframes', 'default_tp_target', 'default_ml_limit', 'real_account_only', 'weekend_auto_off'],
    'Pricing (USD)' => ['price_pro', 'price_elite', 'price_vip'],
    'Payment - PayPal/Wise' => ['paypal_email', 'wise_email'],
    'Payment - Crypto' => ['crypto_usdt_trc20', 'crypto_btc'],
    'Payment - Bank Transfer' => ['bank_name', 'bank_account', 'bank_holder'],
    'System' => ['maintenance_mode']
];
?>

<section class="section" style="padding-top: calc(var(--navbar-height) + 2rem);">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-cog me-2"></i>System Settings</h2>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

            <div class="row">
                <?php foreach ($settingGroups as $groupName => $keys): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0"><?php echo $groupName; ?></h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($keys as $key): ?>
                            <?php if (isset($allSettings[$key])): ?>
                            <?php $setting = $allSettings[$key]; ?>
                            <div class="mb-3">
                                <label class="form-label">
                                    <?php echo ucwords(str_replace('_', ' ', $key)); ?>
                                    <?php if ($setting['description']): ?>
                                    <small class="text-muted d-block"><?php echo htmlspecialchars($setting['description']); ?></small>
                                    <?php endif; ?>
                                </label>

                                <?php if ($key === 'maintenance_mode' || $key === 'real_account_only' || $key === 'weekend_auto_off'): ?>
                                <!-- Toggle Switch -->
                                <select name="settings[<?php echo $key; ?>]" class="form-select">
                                    <option value="0" <?php echo $setting['value'] == '0' ? 'selected' : ''; ?>>OFF</option>
                                    <option value="1" <?php echo $setting['value'] == '1' ? 'selected' : ''; ?>>ON</option>
                                </select>

                                <?php elseif (in_array($key, ['olymptrade_affiliate_link', 'olymptrade_affiliate_link_id'])): ?>
                                <!-- Long URL -->
                                <textarea name="settings[<?php echo $key; ?>]" class="form-control" rows="2"><?php echo htmlspecialchars($setting['value']); ?></textarea>

                                <?php elseif (in_array($key, ['allowed_markets', 'allowed_timeframes'])): ?>
                                <!-- Comma-separated values -->
                                <input type="text" name="settings[<?php echo $key; ?>]" class="form-control"
                                       value="<?php echo htmlspecialchars($setting['value']); ?>"
                                       placeholder="Comma-separated values">
                                <small class="text-muted">Separate multiple values with commas</small>

                                <?php elseif (in_array($key, ['price_pro', 'price_elite', 'price_vip', 'min_deposit', 'default_tp_target', 'default_ml_limit'])): ?>
                                <!-- Number input -->
                                <div class="input-group">
                                    <?php if (strpos($key, 'price') !== false || $key === 'min_deposit'): ?>
                                    <span class="input-group-text">$</span>
                                    <?php endif; ?>
                                    <input type="number" name="settings[<?php echo $key; ?>]" class="form-control"
                                           value="<?php echo htmlspecialchars($setting['value']); ?>" step="0.01">
                                    <?php if (strpos($key, 'tp_target') !== false || strpos($key, 'ml_limit') !== false): ?>
                                    <span class="input-group-text">USD</span>
                                    <?php endif; ?>
                                </div>

                                <?php else: ?>
                                <!-- Default text input -->
                                <input type="text" name="settings[<?php echo $key; ?>]" class="form-control"
                                       value="<?php echo htmlspecialchars($setting['value']); ?>">
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">Save Changes</h5>
                            <p class="text-muted mb-0 small">All settings will be updated immediately</p>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>Save All Settings
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Danger Zone -->
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Danger Zone</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Maintenance Mode</h6>
                        <p class="text-muted small">When enabled, users cannot access the system. Only admins can login.</p>
                        <div class="alert alert-<?php echo ($allSettings['maintenance_mode']['value'] ?? '0') === '1' ? 'danger' : 'success'; ?> py-2">
                            <strong>Current Status:</strong>
                            <?php echo ($allSettings['maintenance_mode']['value'] ?? '0') === '1' ? 'MAINTENANCE MODE ON' : 'System Online'; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>System Info</h6>
                        <ul class="small text-muted mb-0">
                            <li>PHP Version: <?php echo phpversion(); ?></li>
                            <li>Database: MySQL</li>
                            <li>Timezone: <?php echo date_default_timezone_get(); ?></li>
                            <li>Server Time: <?php echo date('Y-m-d H:i:s'); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
