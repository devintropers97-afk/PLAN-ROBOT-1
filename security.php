<?php
/**
 * ZYN Trade System - Security & 2FA Settings
 * FASE 3: 2FA Authentication (FINAL ZYN - Inovasi #15)
 */
$page_title = 'Security Settings';
require_once 'dashboard/includes/dashboard-header.php';

// Get user security settings
$user = getUserById($_SESSION['user_id']);
$twoFactorEnabled = $user['two_factor_enabled'] ?? false;
$twoFactorMethod = $user['two_factor_method'] ?? null;
$lastLogin = $user['last_login'] ?? date('Y-m-d H:i:s');
$loginHistory = []; // Would be fetched from database

// Mock login history
$loginHistory = [
    ['date' => date('Y-m-d H:i:s'), 'ip' => '103.123.xxx.xxx', 'device' => 'Chrome on Windows', 'location' => 'Jakarta, ID', 'status' => 'success'],
    ['date' => date('Y-m-d H:i:s', strtotime('-1 day')), 'ip' => '103.123.xxx.xxx', 'device' => 'Mobile Safari on iOS', 'location' => 'Jakarta, ID', 'status' => 'success'],
    ['date' => date('Y-m-d H:i:s', strtotime('-2 days')), 'ip' => '182.xxx.xxx.xxx', 'device' => 'Firefox on macOS', 'location' => 'Unknown', 'status' => 'failed'],
    ['date' => date('Y-m-d H:i:s', strtotime('-3 days')), 'ip' => '103.123.xxx.xxx', 'device' => 'Chrome on Windows', 'location' => 'Jakarta, ID', 'status' => 'success'],
    ['date' => date('Y-m-d H:i:s', strtotime('-5 days')), 'ip' => '103.123.xxx.xxx', 'device' => 'Chrome on Android', 'location' => 'Bandung, ID', 'status' => 'success'],
];

// Active sessions
$activeSessions = [
    ['device' => 'Chrome on Windows', 'ip' => '103.123.xxx.xxx', 'location' => 'Jakarta, ID', 'last_active' => 'Just now', 'current' => true],
    ['device' => 'Mobile Safari on iOS', 'ip' => '103.123.xxx.xxx', 'location' => 'Jakarta, ID', 'last_active' => '2 hours ago', 'current' => false],
];

// Generate backup codes (for demo)
$backupCodes = ['A1B2-C3D4', 'E5F6-G7H8', 'I9J0-K1L2', 'M3N4-O5P6', 'Q7R8-S9T0'];

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'enable_2fa':
            $method = $_POST['2fa_method'] ?? 'authenticator';
            // In production: generate secret, show QR code, verify OTP
            $message = '2FA setup initiated. Please complete verification.';
            $messageType = 'info';
            break;

        case 'disable_2fa':
            // In production: verify current OTP before disabling
            $message = '2FA has been disabled.';
            $messageType = 'warning';
            break;

        case 'change_password':
            $currentPass = $_POST['current_password'] ?? '';
            $newPass = $_POST['new_password'] ?? '';
            $confirmPass = $_POST['confirm_password'] ?? '';

            if (empty($currentPass) || empty($newPass) || empty($confirmPass)) {
                $message = 'All password fields are required.';
                $messageType = 'danger';
            } elseif ($newPass !== $confirmPass) {
                $message = 'New passwords do not match.';
                $messageType = 'danger';
            } elseif (strlen($newPass) < 8) {
                $message = 'Password must be at least 8 characters.';
                $messageType = 'danger';
            } else {
                // In production: verify current password and update
                $message = 'Password updated successfully!';
                $messageType = 'success';
            }
            break;

        case 'logout_all':
            // In production: invalidate all sessions except current
            $message = 'All other sessions have been logged out.';
            $messageType = 'success';
            break;

        case 'logout_session':
            $sessionId = $_POST['session_id'] ?? '';
            // In production: invalidate specific session
            $message = 'Session terminated.';
            $messageType = 'success';
            break;
    }
}
?>

<!-- Page Header -->
<div class="db-page-header">
    <div>
        <h1 class="db-page-title"><i class="fas fa-shield-alt"></i> Security Settings</h1>
        <p class="db-page-subtitle">Manage your account security and authentication</p>
    </div>
</div>

<?php if ($message): ?>
<div class="db-alert <?php echo $messageType; ?> db-fade-in">
    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : ($messageType === 'danger' ? 'exclamation-circle' : 'info-circle'); ?>"></i>
    <span><?php echo $message; ?></span>
    <button type="button" class="btn-close btn-close-white ms-auto" onclick="this.parentElement.remove()"></button>
</div>
<?php endif; ?>

<!-- Security Overview -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="db-stat-card <?php echo $twoFactorEnabled ? 'success' : 'warning'; ?> db-fade-in">
            <div class="db-stat-icon"><i class="fas fa-mobile-alt"></i></div>
            <div class="db-stat-value"><?php echo $twoFactorEnabled ? 'ENABLED' : 'DISABLED'; ?></div>
            <div class="db-stat-label">Two-Factor Auth</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="db-stat-card db-fade-in">
            <div class="db-stat-icon"><i class="fas fa-clock"></i></div>
            <div class="db-stat-value"><?php echo date('M d, H:i', strtotime($lastLogin)); ?></div>
            <div class="db-stat-label">Last Login</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="db-stat-card db-fade-in">
            <div class="db-stat-icon"><i class="fas fa-desktop"></i></div>
            <div class="db-stat-value"><?php echo count($activeSessions); ?></div>
            <div class="db-stat-label">Active Sessions</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column -->
    <div class="col-lg-8">
        <!-- Two-Factor Authentication -->
        <div class="db-card mb-4 db-fade-in">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="fas fa-key"></i> Two-Factor Authentication</h5>
                <span class="db-badge <?php echo $twoFactorEnabled ? 'success' : 'warning'; ?>">
                    <?php echo $twoFactorEnabled ? 'Active' : 'Not Configured'; ?>
                </span>
            </div>
            <div class="db-card-body">
                <p class="text-muted mb-4">
                    Tambahkan lapisan keamanan ekstra ke akun Anda. Setiap login akan memerlukan kode verifikasi dari authenticator app atau SMS.
                </p>

                <?php if (!$twoFactorEnabled): ?>
                <!-- 2FA Setup Options -->
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="auth-method-card" data-method="authenticator">
                            <div class="method-icon">
                                <i class="fab fa-google"></i>
                            </div>
                            <h6>Authenticator App</h6>
                            <p class="small text-muted">Google Authenticator, Authy, dll.</p>
                            <span class="db-badge success">Recommended</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="auth-method-card" data-method="sms">
                            <div class="method-icon">
                                <i class="fas fa-sms"></i>
                            </div>
                            <h6>SMS OTP</h6>
                            <p class="small text-muted">Kode via SMS ke nomor HP</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="auth-method-card" data-method="email">
                            <div class="method-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <h6>Email OTP</h6>
                            <p class="small text-muted">Kode via email</p>
                        </div>
                    </div>
                </div>

                <form method="POST" class="mt-4" id="enable2faForm">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="enable_2fa">
                    <input type="hidden" name="2fa_method" id="2faMethod" value="authenticator">
                    <button type="submit" class="db-btn db-btn-primary">
                        <i class="fas fa-shield-alt me-1"></i> Enable 2FA
                    </button>
                </form>

                <?php else: ?>
                <!-- 2FA Enabled State -->
                <div class="enabled-2fa-info">
                    <div class="d-flex align-items-center gap-3 mb-4 p-3" style="background: rgba(var(--db-success-rgb), 0.1); border-radius: 12px;">
                        <div class="success-icon">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                        <div>
                            <strong>2FA is Active</strong>
                            <p class="mb-0 small text-muted">Method: <?php echo ucfirst($twoFactorMethod ?? 'Authenticator'); ?></p>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <button class="db-btn db-btn-outline w-100" onclick="showBackupCodes()">
                                <i class="fas fa-key me-1"></i> View Backup Codes
                            </button>
                        </div>
                        <div class="col-md-6">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="action" value="disable_2fa">
                                <button type="submit" class="db-btn db-btn-danger w-100" onclick="return confirm('Are you sure? This will reduce your account security.')">
                                    <i class="fas fa-times me-1"></i> Disable 2FA
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Change Password -->
        <div class="db-card mb-4 db-fade-in">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="fas fa-lock"></i> Change Password</h5>
            </div>
            <div class="db-card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="change_password">

                    <div class="db-form-group mb-3">
                        <label class="db-form-label">Current Password</label>
                        <div class="input-group">
                            <input type="password" name="current_password" class="db-form-control" required>
                            <button type="button" class="db-btn db-btn-outline" onclick="togglePassword(this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="db-form-group mb-3">
                        <label class="db-form-label">New Password</label>
                        <div class="input-group">
                            <input type="password" name="new_password" id="newPassword" class="db-form-control" required minlength="8">
                            <button type="button" class="db-btn db-btn-outline" onclick="togglePassword(this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength mt-2" id="passwordStrength"></div>
                    </div>

                    <div class="db-form-group mb-4">
                        <label class="db-form-label">Confirm New Password</label>
                        <div class="input-group">
                            <input type="password" name="confirm_password" class="db-form-control" required minlength="8">
                            <button type="button" class="db-btn db-btn-outline" onclick="togglePassword(this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="password-requirements mb-4">
                        <small class="text-muted">Password harus mengandung:</small>
                        <ul class="small text-muted mt-1 mb-0">
                            <li id="req-length">Minimal 8 karakter</li>
                            <li id="req-upper">Huruf besar (A-Z)</li>
                            <li id="req-lower">Huruf kecil (a-z)</li>
                            <li id="req-number">Angka (0-9)</li>
                        </ul>
                    </div>

                    <button type="submit" class="db-btn db-btn-primary">
                        <i class="fas fa-save me-1"></i> Update Password
                    </button>
                </form>
            </div>
        </div>

        <!-- Login History -->
        <div class="db-card db-fade-in">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="fas fa-history"></i> Login History</h5>
            </div>
            <div class="db-card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="db-table">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Device</th>
                                <th>IP Address</th>
                                <th>Location</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($loginHistory as $login): ?>
                            <tr>
                                <td><?php echo date('d M Y, H:i', strtotime($login['date'])); ?></td>
                                <td>
                                    <i class="fas fa-<?php echo strpos($login['device'], 'Mobile') !== false ? 'mobile-alt' : 'desktop'; ?> me-1"></i>
                                    <?php echo htmlspecialchars($login['device']); ?>
                                </td>
                                <td><code><?php echo $login['ip']; ?></code></td>
                                <td><?php echo htmlspecialchars($login['location']); ?></td>
                                <td>
                                    <span class="db-badge <?php echo $login['status'] === 'success' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($login['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="col-lg-4">
        <!-- Active Sessions -->
        <div class="db-card mb-4 db-fade-in">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="fas fa-desktop"></i> Active Sessions</h5>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="logout_all">
                    <button type="submit" class="db-btn db-btn-danger db-btn-sm">
                        Logout All
                    </button>
                </form>
            </div>
            <div class="db-card-body">
                <?php foreach ($activeSessions as $session): ?>
                <div class="session-item <?php echo $session['current'] ? 'current' : ''; ?>">
                    <div class="session-icon">
                        <i class="fas fa-<?php echo strpos($session['device'], 'Mobile') !== false ? 'mobile-alt' : 'desktop'; ?>"></i>
                    </div>
                    <div class="session-info">
                        <div class="session-device">
                            <?php echo htmlspecialchars($session['device']); ?>
                            <?php if ($session['current']): ?>
                            <span class="db-badge success ms-1">Current</span>
                            <?php endif; ?>
                        </div>
                        <div class="session-meta">
                            <span><?php echo $session['location']; ?></span>
                            <span class="mx-1">•</span>
                            <span><?php echo $session['last_active']; ?></span>
                        </div>
                    </div>
                    <?php if (!$session['current']): ?>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="action" value="logout_session">
                        <input type="hidden" name="session_id" value="">
                        <button type="submit" class="db-btn db-btn-danger db-btn-sm">
                            <i class="fas fa-times"></i>
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Security Tips -->
        <div class="db-card db-fade-in">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="fas fa-lightbulb"></i> Security Tips</h5>
            </div>
            <div class="db-card-body">
                <div class="security-tips">
                    <div class="tip-item">
                        <i class="fas fa-check-circle text-success"></i>
                        <span>Enable Two-Factor Authentication</span>
                    </div>
                    <div class="tip-item">
                        <i class="fas fa-check-circle text-success"></i>
                        <span>Use a strong, unique password</span>
                    </div>
                    <div class="tip-item">
                        <i class="fas fa-exclamation-circle text-warning"></i>
                        <span>Review login history regularly</span>
                    </div>
                    <div class="tip-item">
                        <i class="fas fa-exclamation-circle text-warning"></i>
                        <span>Never share your login credentials</span>
                    </div>
                    <div class="tip-item">
                        <i class="fas fa-info-circle text-info"></i>
                        <span>Log out from public devices</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Backup Codes Modal -->
<div class="modal fade" id="backupCodesModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: var(--db-surface); border: 1px solid var(--db-border);">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-key me-2"></i>Backup Codes</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning" style="background: rgba(var(--db-warning-rgb), 0.1); border-color: var(--db-warning);">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Simpan kode ini di tempat aman! Setiap kode hanya bisa digunakan sekali.
                </div>
                <div class="backup-codes-grid">
                    <?php foreach ($backupCodes as $code): ?>
                    <div class="backup-code"><?php echo $code; ?></div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-3">
                    <button class="db-btn db-btn-outline" onclick="copyBackupCodes()">
                        <i class="fas fa-copy me-1"></i> Copy All
                    </button>
                    <button class="db-btn db-btn-outline" onclick="downloadBackupCodes()">
                        <i class="fas fa-download me-1"></i> Download
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Auth Method Cards */
.auth-method-card {
    padding: 1.5rem;
    border-radius: 12px;
    background: var(--db-surface-light);
    border: 2px solid transparent;
    cursor: pointer;
    text-align: center;
    transition: all 0.3s ease;
}

.auth-method-card:hover,
.auth-method-card.selected {
    border-color: var(--db-primary);
    background: rgba(var(--db-primary-rgb), 0.1);
}

.method-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: rgba(var(--db-primary-rgb), 0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: var(--db-primary);
    margin: 0 auto 1rem;
}

/* Password Strength */
.password-strength {
    height: 4px;
    border-radius: 2px;
    background: var(--db-surface-light);
    overflow: hidden;
}

.password-strength .strength-bar {
    height: 100%;
    transition: all 0.3s ease;
}

.password-strength.weak .strength-bar { width: 25%; background: var(--db-danger); }
.password-strength.fair .strength-bar { width: 50%; background: var(--db-warning); }
.password-strength.good .strength-bar { width: 75%; background: var(--db-info); }
.password-strength.strong .strength-bar { width: 100%; background: var(--db-success); }

.password-requirements li.valid {
    color: var(--db-success) !important;
}

/* Session Item */
.session-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 0.5rem;
    background: var(--db-surface-light);
}

.session-item.current {
    border: 1px solid var(--db-success);
    background: rgba(var(--db-success-rgb), 0.05);
}

.session-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background: rgba(var(--db-primary-rgb), 0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--db-primary);
}

.session-info {
    flex: 1;
}

.session-device {
    font-weight: 500;
    display: flex;
    align-items: center;
}

.session-meta {
    font-size: 0.8rem;
    color: var(--db-text-muted);
}

/* Security Tips */
.security-tips {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.tip-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.9rem;
}

/* Backup Codes */
.backup-codes-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.5rem;
}

.backup-code {
    padding: 0.75rem;
    background: var(--db-surface-light);
    border-radius: 8px;
    font-family: monospace;
    font-size: 1.1rem;
    text-align: center;
    letter-spacing: 2px;
}
</style>

<script>
// Auth method selection
document.querySelectorAll('.auth-method-card').forEach(card => {
    card.addEventListener('click', function() {
        document.querySelectorAll('.auth-method-card').forEach(c => c.classList.remove('selected'));
        this.classList.add('selected');
        document.getElementById('2faMethod').value = this.dataset.method;
    });
});

// Toggle password visibility
function togglePassword(btn) {
    const input = btn.previousElementSibling;
    const icon = btn.querySelector('i');

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

// Password strength checker
document.getElementById('newPassword')?.addEventListener('input', function() {
    const password = this.value;
    const strength = document.getElementById('passwordStrength');

    let score = 0;
    if (password.length >= 8) { score++; document.getElementById('req-length').classList.add('valid'); }
    else { document.getElementById('req-length').classList.remove('valid'); }

    if (/[A-Z]/.test(password)) { score++; document.getElementById('req-upper').classList.add('valid'); }
    else { document.getElementById('req-upper').classList.remove('valid'); }

    if (/[a-z]/.test(password)) { score++; document.getElementById('req-lower').classList.add('valid'); }
    else { document.getElementById('req-lower').classList.remove('valid'); }

    if (/[0-9]/.test(password)) { score++; document.getElementById('req-number').classList.add('valid'); }
    else { document.getElementById('req-number').classList.remove('valid'); }

    strength.className = 'password-strength';
    if (score === 1) strength.classList.add('weak');
    else if (score === 2) strength.classList.add('fair');
    else if (score === 3) strength.classList.add('good');
    else if (score === 4) strength.classList.add('strong');

    strength.innerHTML = '<div class="strength-bar"></div>';
});

function showBackupCodes() {
    new bootstrap.Modal(document.getElementById('backupCodesModal')).show();
}

function copyBackupCodes() {
    const codes = <?php echo json_encode($backupCodes); ?>;
    navigator.clipboard.writeText(codes.join('\n')).then(() => {
        showToast('Backup codes copied!', 'success');
    });
}

function downloadBackupCodes() {
    const codes = <?php echo json_encode($backupCodes); ?>;
    const blob = new Blob([codes.join('\n')], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'zyn-backup-codes.txt';
    a.click();
}
</script>

<?php require_once 'dashboard/includes/dashboard-footer.php'; ?>
