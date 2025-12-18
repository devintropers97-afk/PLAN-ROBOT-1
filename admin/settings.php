<?php
$page_title = __('admin_settings');
require_once 'includes/admin-header.php';

$db = getDBConnection();
$message = '';

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
    'Site Settings' => [
        'icon' => 'fas fa-globe',
        'color' => 'primary',
        'keys' => ['site_name', 'site_tagline', 'currency']
    ],
    'OlympTrade' => [
        'icon' => 'fas fa-chart-line',
        'color' => 'info',
        'keys' => ['affiliate_id', 'olymptrade_affiliate_link', 'olymptrade_affiliate_link_id']
    ],
    'Telegram' => [
        'icon' => 'fab fa-telegram',
        'color' => 'primary',
        'keys' => ['telegram_channel', 'telegram_channel_id', 'telegram_support']
    ],
    'Trading Rules' => [
        'icon' => 'fas fa-sliders-h',
        'color' => 'warning',
        'keys' => ['min_deposit', 'allowed_markets', 'allowed_timeframes', 'default_tp_target', 'default_ml_limit', 'real_account_only', 'weekend_auto_off']
    ],
    'Pricing (USD)' => [
        'icon' => 'fas fa-tags',
        'color' => 'success',
        'keys' => ['price_pro', 'price_elite', 'price_vip']
    ],
    'Payment - PayPal/Wise' => [
        'icon' => 'fab fa-paypal',
        'color' => 'primary',
        'keys' => ['paypal_email', 'wise_email']
    ],
    'Payment - Crypto' => [
        'icon' => 'fab fa-bitcoin',
        'color' => 'warning',
        'keys' => ['crypto_usdt_trc20', 'crypto_btc']
    ],
    'Payment - Bank Transfer' => [
        'icon' => 'fas fa-university',
        'color' => 'info',
        'keys' => ['bank_name', 'bank_account', 'bank_holder']
    ]
];
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-cog"></i> System Settings</h1>
        <p class="page-subtitle">Configure your application settings</p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
    </a>
</div>

<?php if ($message): ?>
<div class="alert alert-success fade-in">
    <i class="fas fa-check-circle"></i>
    <span><?php echo $message; ?></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

    <div class="row">
        <?php foreach ($settingGroups as $groupName => $group): ?>
        <div class="col-md-6 mb-4">
            <div class="admin-card h-100 fade-in">
                <div class="admin-card-header">
                    <h5 class="admin-card-title">
                        <i class="<?php echo $group['icon']; ?> text-<?php echo $group['color']; ?>"></i>
                        <?php echo $groupName; ?>
                    </h5>
                </div>
                <div class="admin-card-body">
                    <?php foreach ($group['keys'] as $key): ?>
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
                        <div class="toggle-switch-wrapper">
                            <select name="settings[<?php echo $key; ?>]" class="form-select">
                                <option value="0" <?php echo $setting['value'] == '0' ? 'selected' : ''; ?>>OFF</option>
                                <option value="1" <?php echo $setting['value'] == '1' ? 'selected' : ''; ?>>ON</option>
                            </select>
                        </div>

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

                        <?php elseif (in_array($key, ['crypto_usdt_trc20', 'crypto_btc'])): ?>
                        <!-- Crypto address with copy button -->
                        <div class="input-group">
                            <input type="text" name="settings[<?php echo $key; ?>]" class="form-control font-monospace"
                                   value="<?php echo htmlspecialchars($setting['value']); ?>">
                            <button type="button" class="btn btn-outline-primary" onclick="copyToClipboard(this.previousElementSibling)">
                                <i class="fas fa-copy"></i>
                            </button>
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

    <!-- Save Button Card -->
    <div class="admin-card mb-4 fade-in">
        <div class="admin-card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1"><i class="fas fa-save me-2 text-primary"></i>Save Changes</h5>
                    <p class="text-muted mb-0 small">All settings will be updated immediately</p>
                </div>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save me-2"></i>Save All Settings
                </button>
            </div>
        </div>
    </div>
</form>

<!-- System Status Section -->
<div class="row">
    <!-- Maintenance Mode -->
    <div class="col-md-6 mb-4">
        <div class="admin-card fade-in" style="border-color: var(--danger);">
            <div class="admin-card-header" style="background: rgba(239, 68, 68, 0.1);">
                <h5 class="admin-card-title text-danger">
                    <i class="fas fa-exclamation-triangle"></i> Danger Zone
                </h5>
            </div>
            <div class="admin-card-body">
                <h6 class="mb-2">Maintenance Mode</h6>
                <p class="text-muted small mb-3">When enabled, users cannot access the system. Only admins can login.</p>
                <div class="alert alert-<?php echo ($allSettings['maintenance_mode']['value'] ?? '0') === '1' ? 'danger' : 'success'; ?> py-2 mb-0">
                    <i class="fas fa-<?php echo ($allSettings['maintenance_mode']['value'] ?? '0') === '1' ? 'exclamation-circle' : 'check-circle'; ?> me-2"></i>
                    <strong>Current Status:</strong>
                    <?php echo ($allSettings['maintenance_mode']['value'] ?? '0') === '1' ? 'MAINTENANCE MODE ON' : 'System Online'; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- System Info -->
    <div class="col-md-6 mb-4">
        <div class="admin-card fade-in">
            <div class="admin-card-header">
                <h5 class="admin-card-title">
                    <i class="fas fa-server text-info"></i> System Information
                </h5>
            </div>
            <div class="admin-card-body">
                <div class="system-info-grid">
                    <div class="system-info-item">
                        <span class="system-info-label">PHP Version</span>
                        <span class="system-info-value"><?php echo phpversion(); ?></span>
                    </div>
                    <div class="system-info-item">
                        <span class="system-info-label">Database</span>
                        <span class="system-info-value">MySQL</span>
                    </div>
                    <div class="system-info-item">
                        <span class="system-info-label">Timezone</span>
                        <span class="system-info-value"><?php echo date_default_timezone_get(); ?></span>
                    </div>
                    <div class="system-info-item">
                        <span class="system-info-label">Server Time</span>
                        <span class="system-info-value"><?php echo date('Y-m-d H:i:s'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.system-info-grid {
    display: grid;
    gap: 0.75rem;
}
.system-info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background: rgba(255, 255, 255, 0.03);
    border-radius: 8px;
    border: 1px solid rgba(255, 255, 255, 0.05);
}
.system-info-label {
    color: var(--text-secondary);
    font-size: 0.875rem;
}
.system-info-value {
    color: var(--text-primary);
    font-weight: 500;
    font-family: 'SF Mono', 'Monaco', 'Consolas', monospace;
    font-size: 0.875rem;
}
.input-group-text {
    background: rgba(255, 255, 255, 0.05);
    border-color: rgba(255, 255, 255, 0.1);
    color: var(--text-secondary);
}
.font-monospace {
    font-family: 'SF Mono', 'Monaco', 'Consolas', monospace;
    font-size: 0.85rem;
}
</style>

<script>
function copyToClipboard(input) {
    input.select();
    document.execCommand('copy');

    const btn = input.nextElementSibling;
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check"></i>';
    btn.classList.remove('btn-outline-primary');
    btn.classList.add('btn-success');

    setTimeout(() => {
        btn.innerHTML = originalHtml;
        btn.classList.remove('btn-success');
        btn.classList.add('btn-outline-primary');
    }, 1500);
}
</script>

<?php require_once 'includes/admin-footer.php'; ?>
