<?php
/**
 * OlympTrade Account Setup
 * Trader MUST setup their OlympTrade credentials before using robot
 */

// Load config and functions FIRST (no HTML output)
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Require login
if (!isLoggedIn()) {
    redirect('login.php');
    exit;
}

$db = getDBConnection();
$userId = $_SESSION['user_id'];
$message = '';
$messageType = '';

// Get current user data
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_credentials') {
        $otEmail = cleanInput($_POST['ot_email'] ?? '');
        $otPassword = $_POST['ot_password'] ?? '';
        $accountType = $_POST['account_type'] ?? 'demo';

        // Validation - password required only for new setup
        $isNewSetup = empty($user['olymptrade_setup_completed']);

        if (empty($otEmail)) {
            $message = __('ot_email_required');
            $messageType = 'danger';
        } elseif ($isNewSetup && empty($otPassword)) {
            $message = __('ot_password_required');
            $messageType = 'danger';
        } elseif (!filter_var($otEmail, FILTER_VALIDATE_EMAIL)) {
            $message = __('ot_email_invalid');
            $messageType = 'danger';
        } else {
            // Only encrypt and update password if provided
            if (!empty($otPassword)) {
                // Encrypt new password
                $encryptedPassword = encryptPassword($otPassword);

                // Update with new password
                $stmt = $db->prepare("
                    UPDATE users SET
                        olymptrade_email = ?,
                        olymptrade_password = ?,
                        olymptrade_account_type = ?,
                        olymptrade_setup_completed = 1,
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $result = $stmt->execute([$otEmail, $encryptedPassword, $accountType, $userId]);
            } else {
                // Update without changing password
                $stmt = $db->prepare("
                    UPDATE users SET
                        olymptrade_email = ?,
                        olymptrade_account_type = ?,
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $result = $stmt->execute([$otEmail, $accountType, $userId]);
            }

            if ($result) {
                $message = __('ot_setup_success');
                $messageType = 'success';

                // Refresh user data
                $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch();
            } else {
                $message = __('ot_setup_failed');
                $messageType = 'danger';
            }
        }
    } elseif ($action === 'test_connection') {
        // Test connection to OlympTrade (simulation)
        $message = __('ot_test_info');
        $messageType = 'info';
    } elseif ($action === 'remove_credentials') {
        // Remove credentials
        $stmt = $db->prepare("
            UPDATE users SET
                olymptrade_email = NULL,
                olymptrade_password = NULL,
                olymptrade_setup_completed = 0,
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$userId]);

        // Also disable robot
        $stmt = $db->prepare("UPDATE robot_settings SET robot_enabled = 0 WHERE user_id = ?");
        $stmt->execute([$userId]);

        $message = __('ot_delete_success');
        $messageType = 'warning';

        // Refresh user data
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
    }
}

// Check if setup is completed
$setupCompleted = !empty($user['olymptrade_setup_completed']);

// NOW load header (HTML output starts here)
$page_title = 'OlympTrade Setup';
require_once 'dashboard/includes/dashboard-header.php';
?>

<!-- Page Header -->
<div class="db-page-header">
    <div>
        <h1 class="db-page-title"><i class="fas fa-link"></i> OlympTrade Setup</h1>
        <p class="db-page-subtitle">Connect your OlympTrade account for automated trading</p>
    </div>
    <a href="dashboard.php" class="db-btn db-btn-outline">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</div>

<?php if ($message): ?>
<div class="db-alert <?php echo $messageType; ?> db-fade-in">
    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : ($messageType === 'danger' ? 'exclamation-circle' : 'info-circle'); ?>"></i>
    <span><?php echo $message; ?></span>
    <button type="button" class="btn-close btn-close-white ms-auto" onclick="this.parentElement.remove()"></button>
</div>
<?php endif; ?>

<!-- Status Card -->
<div class="db-card db-fade-in mb-4" style="border: 2px solid <?php echo $setupCompleted ? 'var(--db-success)' : 'var(--db-warning)'; ?>;">
    <div class="db-card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center gap-3">
                    <div class="setup-status-icon <?php echo $setupCompleted ? 'completed' : 'pending'; ?>">
                        <i class="fas fa-<?php echo $setupCompleted ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                    </div>
                    <div>
                        <h4 class="mb-1">
                            <?php echo $setupCompleted ? 'OlympTrade Connected' : 'Setup Required'; ?>
                        </h4>
                        <p class="mb-0 text-muted">
                            <?php if ($setupCompleted): ?>
                                Account: <strong><?php echo htmlspecialchars($user['olymptrade_email']); ?></strong>
                                <span class="db-badge <?php echo $user['olymptrade_account_type'] === 'demo' ? 'warning' : 'success'; ?> ms-2">
                                    <?php echo strtoupper($user['olymptrade_account_type'] ?? 'demo'); ?>
                                </span>
                            <?php else: ?>
                                Please setup your OlympTrade credentials to enable robot trading
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <?php if ($setupCompleted): ?>
                <span class="db-badge success" style="font-size: 1rem; padding: 0.5rem 1rem;">
                    <i class="fas fa-check me-1"></i> Ready to Trade
                </span>
                <?php else: ?>
                <span class="db-badge warning" style="font-size: 1rem; padding: 0.5rem 1rem;">
                    <i class="fas fa-clock me-1"></i> Setup Pending
                </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Setup Form -->
    <div class="col-lg-8 mb-4">
        <div class="db-card db-fade-in">
            <div class="db-card-header">
                <h5 class="db-card-title">
                    <i class="fas fa-key text-primary"></i>
                    <?php echo $setupCompleted ? 'Update Credentials' : 'Setup Credentials'; ?>
                </h5>
            </div>
            <div class="db-card-body">
                <form method="POST" id="credentialsForm">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="save_credentials">

                    <div class="db-form-group">
                        <label class="db-form-label">
                            <i class="fas fa-envelope me-1"></i> Email OlympTrade
                        </label>
                        <input type="email" name="ot_email" class="db-form-control"
                               value="<?php echo htmlspecialchars($user['olymptrade_email'] ?? ''); ?>"
                               placeholder="email@example.com" required>
                        <small class="text-muted">Email yang digunakan untuk login ke OlympTrade</small>
                    </div>

                    <div class="db-form-group">
                        <label class="db-form-label">
                            <i class="fas fa-lock me-1"></i> Password OlympTrade
                        </label>
                        <div class="input-group">
                            <input type="password" name="ot_password" class="db-form-control" id="otPassword"
                                   placeholder="<?php echo $setupCompleted ? '••••••••' : 'Masukkan password'; ?>"
                                   <?php echo $setupCompleted ? '' : 'required'; ?>>
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword()">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                        <small class="text-muted">
                            <?php echo $setupCompleted ? 'Kosongkan jika tidak ingin mengubah password' : 'Password untuk login ke OlympTrade'; ?>
                        </small>
                    </div>

                    <div class="db-form-group">
                        <label class="db-form-label">
                            <i class="fas fa-wallet me-1"></i> Tipe Akun
                        </label>
                        <div class="d-flex gap-3">
                            <label class="account-type-option <?php echo ($user['olymptrade_account_type'] ?? 'demo') === 'demo' ? 'selected' : ''; ?>">
                                <input type="radio" name="account_type" value="demo"
                                       <?php echo ($user['olymptrade_account_type'] ?? 'demo') === 'demo' ? 'checked' : ''; ?>>
                                <div class="option-content">
                                    <i class="fas fa-flask"></i>
                                    <span class="option-title">DEMO</span>
                                    <span class="option-desc">Virtual money for testing</span>
                                </div>
                            </label>
                            <label class="account-type-option <?php echo ($user['olymptrade_account_type'] ?? '') === 'real' ? 'selected' : ''; ?>">
                                <input type="radio" name="account_type" value="real"
                                       <?php echo ($user['olymptrade_account_type'] ?? '') === 'real' ? 'checked' : ''; ?>>
                                <div class="option-content">
                                    <i class="fas fa-dollar-sign"></i>
                                    <span class="option-title">REAL</span>
                                    <span class="option-desc">Real money trading</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="alert alert-warning py-2 mb-4">
                        <i class="fas fa-shield-alt me-2"></i>
                        <strong>Security:</strong> Password Anda dienkripsi dan hanya digunakan oleh robot untuk login.
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="db-btn db-btn-primary">
                            <i class="fas fa-save me-1"></i>
                            <?php echo $setupCompleted ? 'Update Credentials' : 'Save & Connect'; ?>
                        </button>

                        <?php if ($setupCompleted): ?>
                        <button type="button" class="db-btn db-btn-outline" onclick="testConnection()">
                            <i class="fas fa-plug me-1"></i> Test Connection
                        </button>
                        <?php endif; ?>
                    </div>
                </form>

                <?php if ($setupCompleted): ?>
                <hr class="my-4" style="border-color: rgba(255,255,255,0.1);">
                <form method="POST" onsubmit="return confirm('Yakin ingin menghapus credentials? Robot akan dinonaktifkan.');">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="remove_credentials">
                    <button type="submit" class="db-btn db-btn-danger">
                        <i class="fas fa-trash me-1"></i> Remove Credentials
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Info Panel -->
    <div class="col-lg-4 mb-4">
        <div class="db-card db-fade-in mb-4">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="fas fa-info-circle text-info"></i> How It Works</h5>
            </div>
            <div class="db-card-body">
                <div class="info-steps">
                    <div class="info-step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <strong>Input Credentials</strong>
                            <p>Masukkan email dan password OlympTrade Anda</p>
                        </div>
                    </div>
                    <div class="info-step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <strong>Choose Account Type</strong>
                            <p>Pilih DEMO untuk testing atau REAL untuk trading</p>
                        </div>
                    </div>
                    <div class="info-step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <strong>Enable Robot</strong>
                            <p>Aktifkan robot di Dashboard untuk mulai trading</p>
                        </div>
                    </div>
                    <div class="info-step">
                        <div class="step-number">4</div>
                        <div class="step-content">
                            <strong>Auto Trading</strong>
                            <p>Robot akan login dan trading otomatis di akun Anda</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="db-card db-fade-in">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="fas fa-shield-alt text-success"></i> Security</h5>
            </div>
            <div class="db-card-body">
                <ul class="security-list">
                    <li><i class="fas fa-check text-success"></i> Password terenkripsi AES-256</li>
                    <li><i class="fas fa-check text-success"></i> Hanya robot yang bisa akses</li>
                    <li><i class="fas fa-check text-success"></i> Tidak bisa dilihat admin</li>
                    <li><i class="fas fa-check text-success"></i> Bisa dihapus kapan saja</li>
                    <li><i class="fas fa-check text-success"></i> SSL encrypted connection</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
.setup-status-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.setup-status-icon.completed {
    background: rgba(16, 185, 129, 0.15);
    color: var(--db-success);
}

.setup-status-icon.pending {
    background: rgba(245, 158, 11, 0.15);
    color: var(--db-warning);
}

.account-type-option {
    flex: 1;
    cursor: pointer;
}

.account-type-option input {
    display: none;
}

.account-type-option .option-content {
    padding: 1.5rem;
    background: var(--db-surface-light);
    border: 2px solid var(--db-border);
    border-radius: 12px;
    text-align: center;
    transition: all 0.3s ease;
}

.account-type-option .option-content i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    display: block;
    color: var(--db-text-secondary);
}

.account-type-option .option-title {
    display: block;
    font-weight: 700;
    font-size: 1.1rem;
    margin-bottom: 0.25rem;
}

.account-type-option .option-desc {
    font-size: 0.8rem;
    color: var(--db-text-secondary);
}

.account-type-option input:checked + .option-content {
    border-color: var(--db-primary);
    background: rgba(var(--db-primary-rgb), 0.1);
}

.account-type-option input:checked + .option-content i {
    color: var(--db-primary);
}

.account-type-option:hover .option-content {
    border-color: rgba(var(--db-primary-rgb), 0.5);
}

.info-steps {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.info-step {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
}

.step-number {
    width: 32px;
    height: 32px;
    background: var(--db-primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    flex-shrink: 0;
}

.step-content strong {
    display: block;
    margin-bottom: 0.25rem;
}

.step-content p {
    font-size: 0.85rem;
    color: var(--db-text-secondary);
    margin: 0;
}

.security-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.security-list li {
    padding: 0.5rem 0;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

.security-list li:last-child {
    border-bottom: none;
}

.security-list li i {
    margin-right: 0.5rem;
}
</style>

<script>
function togglePassword() {
    var input = document.getElementById('otPassword');
    var icon = document.getElementById('toggleIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function testConnection() {
    alert('Robot akan melakukan test koneksi ke OlympTrade. Cek status di Dashboard.');
}

// Account type selection
document.querySelectorAll('.account-type-option').forEach(option => {
    option.addEventListener('click', function() {
        document.querySelectorAll('.account-type-option').forEach(o => o.classList.remove('selected'));
        this.classList.add('selected');
    });
});
</script>

<?php require_once 'dashboard/includes/dashboard-footer.php'; ?>
