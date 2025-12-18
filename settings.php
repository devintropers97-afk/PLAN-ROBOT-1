<?php
/**
 * User Robot Settings Page
 */
$page_title = 'Robot Settings';
require_once 'dashboard/includes/dashboard-header.php';

$user = getUserById($_SESSION['user_id']);
$package = $user['package'] ?? 'free';
$packageInfo = getPackageDetails($package);
$settings = getRobotSettings($_SESSION['user_id']);
$availableStrategies = getAvailableStrategies($package);
$allStrategies = getAllStrategies();

$message = '';
$messageType = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid request. Please try again.';
        $messageType = 'danger';
    } else {
        $action = $_POST['action'] ?? 'save_settings';

        if ($action === 'save_settings') {
            $data = [
                'market' => cleanInput($_POST['market'] ?? 'EUR/USD'),
                'timeframe' => cleanInput($_POST['timeframe'] ?? '15M'),
                'risk_level' => cleanInput($_POST['risk_level'] ?? 'medium'),
                'trade_amount' => intval($_POST['trade_amount'] ?? 10000),
                'daily_limit' => intval($_POST['daily_limit'] ?? 10),
                'take_profit_target' => floatval($_POST['take_profit'] ?? 50),
                'max_loss_limit' => floatval($_POST['max_loss'] ?? 25),
                'schedule_mode' => cleanInput($_POST['schedule_mode'] ?? 'auto_24h'),
                'active_strategies' => json_encode($_POST['strategies'] ?? [])
            ];

            // Validate strategies against package
            $selectedStrategies = $_POST['strategies'] ?? [];
            $allowedIds = array_column($availableStrategies, 'id');
            $validStrategies = array_intersect($selectedStrategies, $allowedIds);

            if (count($validStrategies) !== count($selectedStrategies)) {
                $message = 'Some selected strategies are not available in your package.';
                $messageType = 'warning';
            }

            $data['active_strategies'] = json_encode($validStrategies);

            if (updateRobotSettings($_SESSION['user_id'], $data)) {
                $message = 'Settings saved successfully!';
                $messageType = 'success';
                $settings = getRobotSettings($_SESSION['user_id']);
                logActivity($_SESSION['user_id'], 'settings_updated', 'Robot settings updated');
            } else {
                $message = 'Failed to save settings.';
                $messageType = 'danger';
            }
        }
    }
}

// Parse active strategies
$activeStrategies = json_decode($settings['active_strategies'] ?? '[]', true) ?: [];

// Available markets
$markets = ['EUR/USD', 'GBP/USD', 'USD/JPY', 'AUD/USD', 'EUR/GBP', 'USD/CAD', 'EUR/JPY', 'NZD/USD'];

// Available timeframes
$timeframes = ['5M' => '5 Menit', '15M' => '15 Menit', '30M' => '30 Menit', '1H' => '1 Jam'];

// Risk levels
$riskLevels = [
    'low' => ['name' => 'Low Risk', 'desc' => 'Conservative, lower returns', 'color' => 'success'],
    'medium' => ['name' => 'Medium Risk', 'desc' => 'Balanced approach', 'color' => 'warning'],
    'high' => ['name' => 'High Risk', 'desc' => 'Aggressive, higher returns', 'color' => 'danger']
];

// Schedule modes
$scheduleModes = [
    'auto_24h' => ['name' => '24 Jam Otomatis', 'desc' => 'Robot aktif sepanjang hari', 'icon' => 'fa-clock'],
    'best_hours' => ['name' => 'Jam Terbaik', 'desc' => '14:00 - 22:00 WIB (London & NY session)', 'icon' => 'fa-star'],
    'custom_single' => ['name' => 'Custom Single Session', 'desc' => 'Atur jam mulai dan selesai sendiri', 'icon' => 'fa-edit'],
    'multi_session' => ['name' => 'Multi-Session', 'desc' => 'Atur beberapa sesi trading dalam sehari', 'icon' => 'fa-layer-group'],
    'per_day' => ['name' => 'Per Hari Berbeda', 'desc' => 'Jadwal berbeda untuk setiap hari', 'icon' => 'fa-calendar-week']
];
?>

<!-- Page Header -->
<div class="db-page-header">
    <div>
        <h1 class="db-page-title"><i class="fas fa-cog"></i> Robot Settings</h1>
        <p class="db-page-subtitle">Konfigurasi pengaturan robot trading Anda</p>
    </div>
    <a href="dashboard.php" class="db-btn db-btn-outline">
        <i class="fas fa-arrow-left"></i> Dashboard
    </a>
</div>

<?php if ($message): ?>
<div class="db-alert <?php echo $messageType; ?> db-fade-in">
    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : ($messageType === 'warning' ? 'exclamation-triangle' : 'exclamation-circle'); ?>"></i>
    <span><?php echo $message; ?></span>
    <button type="button" class="btn-close btn-close-white ms-auto" onclick="this.parentElement.remove()"></button>
</div>
<?php endif; ?>

<form method="POST" id="settingsForm">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    <input type="hidden" name="action" value="save_settings">

    <div class="row g-4">
        <!-- Left Column - Trading Settings -->
        <div class="col-lg-8">
            <!-- Strategy Selection -->
            <div class="db-card mb-4 db-fade-in">
                <div class="db-card-header">
                    <h5 class="db-card-title"><i class="fas fa-chess"></i> Pilih Strategi</h5>
                    <span class="db-badge <?php echo strtolower($package); ?>"><?php echo strtoupper($package); ?></span>
                </div>
                <div class="db-card-body">
                    <p class="text-muted mb-3">
                        Package <?php echo $packageInfo['name']; ?>: <?php echo $packageInfo['strategies']; ?> strategi tersedia
                    </p>

                    <div class="row g-3">
                        <?php foreach ($allStrategies as $strategy): ?>
                        <?php
                            $isAvailable = in_array($strategy['id'], array_column($availableStrategies, 'id'));
                            $isActive = in_array($strategy['id'], $activeStrategies);
                        ?>
                        <div class="col-md-6">
                            <div class="strategy-card <?php echo !$isAvailable ? 'locked' : ''; ?> <?php echo $isActive ? 'active' : ''; ?>">
                                <label class="strategy-label">
                                    <input type="checkbox" name="strategies[]" value="<?php echo $strategy['id']; ?>"
                                           <?php echo $isActive ? 'checked' : ''; ?>
                                           <?php echo !$isAvailable ? 'disabled' : ''; ?>>
                                    <div class="strategy-content">
                                        <div class="strategy-header">
                                            <span class="strategy-icon"><i class="fas fa-<?php echo $strategy['icon']; ?>"></i></span>
                                            <div>
                                                <strong><?php echo $strategy['name']; ?></strong>
                                                <small class="d-block text-muted"><?php echo $strategy['subtitle']; ?></small>
                                            </div>
                                        </div>
                                        <div class="strategy-stats">
                                            <span class="db-badge <?php echo $strategy['risk'] === 'Low' ? 'success' : ($strategy['risk'] === 'Medium' ? 'warning' : 'danger'); ?>">
                                                <?php echo $strategy['win_rate']; ?>
                                            </span>
                                            <span class="db-badge secondary"><?php echo $strategy['tier']; ?></span>
                                            <?php if (!$isAvailable): ?>
                                            <span class="db-badge secondary"><i class="fas fa-lock"></i></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if (count($availableStrategies) < count($allStrategies)): ?>
                    <div class="mt-3 text-center">
                        <a href="pricing.php" class="db-btn db-btn-sm db-btn-outline">
                            <i class="fas fa-crown"></i> Upgrade untuk lebih banyak strategi
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Trading Parameters -->
            <div class="db-card mb-4 db-fade-in">
                <div class="db-card-header">
                    <h5 class="db-card-title"><i class="fas fa-sliders-h"></i> Parameter Trading</h5>
                </div>
                <div class="db-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="db-form-group">
                                <label class="db-form-label">Market</label>
                                <select name="market" class="db-form-control db-form-select">
                                    <?php foreach ($markets as $market): ?>
                                    <option value="<?php echo $market; ?>" <?php echo ($settings['market'] ?? '') === $market ? 'selected' : ''; ?>>
                                        <?php echo $market; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="db-form-group">
                                <label class="db-form-label">Timeframe</label>
                                <select name="timeframe" class="db-form-control db-form-select">
                                    <?php foreach ($timeframes as $tf => $name): ?>
                                    <option value="<?php echo $tf; ?>" <?php echo ($settings['timeframe'] ?? '') === $tf ? 'selected' : ''; ?>>
                                        <?php echo $name; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="db-form-group">
                                <label class="db-form-label">Jumlah per Trade (Rp)</label>
                                <input type="number" name="trade_amount" class="db-form-control"
                                       value="<?php echo $settings['trade_amount'] ?? 10000; ?>"
                                       min="10000" max="5000000" step="5000">
                                <small class="text-muted">Min: Rp10.000 | Max: Rp5.000.000</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="db-form-group">
                                <label class="db-form-label">Batas Trade Harian</label>
                                <input type="number" name="daily_limit" class="db-form-control"
                                       value="<?php echo $settings['daily_limit'] ?? 10; ?>"
                                       min="1" max="100">
                                <small class="text-muted">Maksimum trade per hari</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Risk Management -->
            <div class="db-card mb-4 db-fade-in">
                <div class="db-card-header">
                    <h5 class="db-card-title"><i class="fas fa-shield-alt"></i> Risk Management</h5>
                </div>
                <div class="db-card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="db-form-group">
                                <label class="db-form-label">Risk Level</label>
                                <select name="risk_level" class="db-form-control db-form-select">
                                    <?php foreach ($riskLevels as $level => $info): ?>
                                    <option value="<?php echo $level; ?>" <?php echo ($settings['risk_level'] ?? 'medium') === $level ? 'selected' : ''; ?>>
                                        <?php echo $info['name']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="db-form-group">
                                <label class="db-form-label">Take Profit ($)</label>
                                <input type="number" name="take_profit" class="db-form-control"
                                       value="<?php echo $settings['take_profit_target'] ?? 50; ?>"
                                       min="0" step="5">
                                <small class="text-muted">Auto-pause jika profit tercapai</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="db-form-group">
                                <label class="db-form-label">Max Loss ($)</label>
                                <input type="number" name="max_loss" class="db-form-control"
                                       value="<?php echo $settings['max_loss_limit'] ?? 25; ?>"
                                       min="0" step="5">
                                <small class="text-muted">Auto-pause jika loss tercapai</small>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <i class="fas fa-info-circle"></i>
                        <strong>Auto-Pause System:</strong> Robot akan otomatis berhenti jika target profit atau batas loss harian tercapai untuk melindungi modal Anda.
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Schedule & Summary -->
        <div class="col-lg-4">
            <!-- Schedule Settings -->
            <div class="db-card mb-4 db-fade-in">
                <div class="db-card-header">
                    <h5 class="db-card-title"><i class="fas fa-clock"></i> Jadwal Trading</h5>
                </div>
                <div class="db-card-body">
                    <?php foreach ($scheduleModes as $mode => $info): ?>
                    <div class="schedule-option mb-3">
                        <label class="d-flex align-items-start">
                            <input type="radio" name="schedule_mode" value="<?php echo $mode; ?>"
                                   <?php echo ($settings['schedule_mode'] ?? 'auto_24h') === $mode ? 'checked' : ''; ?>
                                   class="me-2 mt-1">
                            <div>
                                <strong><?php echo $info['name']; ?></strong>
                                <small class="d-block text-muted"><?php echo $info['desc']; ?></small>
                            </div>
                        </label>
                    </div>
                    <?php endforeach; ?>

                    <div class="alert alert-warning mt-3 mb-0">
                        <i class="fas fa-moon"></i>
                        <strong>Weekend Auto-Off:</strong> Robot otomatis nonaktif di hari Sabtu & Minggu karena market tutup.
                    </div>
                </div>
            </div>

            <!-- Current Settings Summary -->
            <div class="db-card mb-4 db-fade-in">
                <div class="db-card-header">
                    <h5 class="db-card-title"><i class="fas fa-list-check"></i> Ringkasan</h5>
                </div>
                <div class="db-card-body">
                    <ul class="settings-summary">
                        <li>
                            <span class="text-muted">Package</span>
                            <span class="db-badge <?php echo strtolower($package); ?>"><?php echo strtoupper($package); ?></span>
                        </li>
                        <li>
                            <span class="text-muted">Strategi Aktif</span>
                            <strong><?php echo count($activeStrategies); ?></strong>
                        </li>
                        <li>
                            <span class="text-muted">Market</span>
                            <strong><?php echo $settings['market'] ?? 'EUR/USD'; ?></strong>
                        </li>
                        <li>
                            <span class="text-muted">Timeframe</span>
                            <strong><?php echo $settings['timeframe'] ?? '15M'; ?></strong>
                        </li>
                        <li>
                            <span class="text-muted">Trade Amount</span>
                            <strong>Rp<?php echo number_format($settings['trade_amount'] ?? 10000, 0, ',', '.'); ?></strong>
                        </li>
                        <li>
                            <span class="text-muted">Daily Limit</span>
                            <strong><?php echo $settings['daily_limit'] ?? 10; ?> trades</strong>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Save Button -->
            <button type="submit" class="db-btn db-btn-primary w-100 db-fade-in">
                <i class="fas fa-save"></i> Simpan Pengaturan
            </button>
        </div>
    </div>
</form>

<style>
.strategy-card {
    border: 2px solid var(--db-border);
    border-radius: 12px;
    padding: 1rem;
    transition: var(--db-transition);
    cursor: pointer;
}
.strategy-card:hover {
    border-color: var(--db-primary);
    background: rgba(var(--db-primary-rgb), 0.05);
}
.strategy-card.active {
    border-color: var(--db-success);
    background: rgba(var(--db-success-rgb), 0.1);
}
.strategy-card.locked {
    opacity: 0.5;
    cursor: not-allowed;
}
.strategy-card.locked:hover {
    border-color: var(--db-border);
    background: transparent;
}
.strategy-label {
    display: block;
    cursor: inherit;
}
.strategy-label input {
    display: none;
}
.strategy-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}
.strategy-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, var(--db-primary), var(--db-secondary));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}
.strategy-stats {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.schedule-option {
    padding: 0.75rem;
    border: 1px solid var(--db-border);
    border-radius: 8px;
    transition: var(--db-transition);
}
.schedule-option:has(input:checked) {
    border-color: var(--db-primary);
    background: rgba(var(--db-primary-rgb), 0.05);
}
.settings-summary {
    list-style: none;
    padding: 0;
    margin: 0;
}
.settings-summary li {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--db-border);
}
.settings-summary li:last-child {
    border-bottom: none;
}
</style>

<script>
// Update summary on form change
document.getElementById('settingsForm').addEventListener('change', function() {
    // Could add live summary update here
});

// Strategy card click handling
document.querySelectorAll('.strategy-card:not(.locked)').forEach(card => {
    card.addEventListener('click', function(e) {
        if (e.target.tagName !== 'INPUT') {
            const checkbox = this.querySelector('input[type="checkbox"]');
            checkbox.checked = !checkbox.checked;
            this.classList.toggle('active', checkbox.checked);
        }
    });
});

// Checkbox change
document.querySelectorAll('.strategy-card input').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        this.closest('.strategy-card').classList.toggle('active', this.checked);
    });
});
</script>

<?php require_once 'dashboard/includes/dashboard-footer.php'; ?>
