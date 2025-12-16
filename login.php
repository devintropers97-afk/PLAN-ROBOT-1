<?php
$page_title = 'Login';
require_once 'includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin/index.php');
    } else {
        redirect('dashboard.php');
    }
}

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Request tidak valid. Silakan coba lagi.';
    } else {
        $license_key = strtoupper(cleanInput($_POST['license_key'] ?? ''));

        if (empty($license_key)) {
            $error = 'Silakan masukkan License Key Anda.';
        } else {
            $result = loginWithLicenseKey($license_key);

            if ($result['success']) {
                // Redirect admin to admin panel, users to dashboard
                if (isset($result['user']['role']) && $result['user']['role'] === 'admin') {
                    redirect('admin/index.php');
                } else {
                    redirect('dashboard.php');
                }
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Check for success message from registration
if (isset($_GET['registered'])) {
    $success = 'Registrasi berhasil! License Key Anda akan dikirim setelah verifikasi admin.';
}
if (isset($_GET['verified'])) {
    $success = 'Akun Anda telah diverifikasi! Silakan login dengan License Key.';
}
?>

<section class="auth-page">
    <div class="container">
        <div class="card auth-card">
            <div class="auth-header">
                <div class="mb-4">
                    <span class="brand-logo" style="font-size: 2.5rem;">ZYN</span>
                    <small class="d-block text-muted mt-1">Trade System</small>
                </div>
                <p class="tagline-hero text-primary mb-3">💰 "Tidur Nyenyak, Bangun Profit"</p>
                <h1 class="auth-title">Selamat Datang</h1>
                <p class="auth-subtitle">Profit 5-10% sehari? Cukup ON-kan Robot, sisanya AUTOPILOT! 🚀</p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="" class="needs-validation" novalidate id="loginForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                <div class="mb-4">
                    <label for="license_key" class="form-label">License Key</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="fas fa-key text-primary"></i>
                        </span>
                        <input type="text" class="form-control border-start-0 text-uppercase"
                               id="license_key" name="license_key"
                               placeholder="ZYN-XXXX-XXXX" required
                               style="letter-spacing: 2px; font-family: monospace;"
                               value="<?php echo isset($_POST['license_key']) ? htmlspecialchars($_POST['license_key']) : ''; ?>"
                               autocomplete="off">
                    </div>
                    <small class="text-muted">Format: ZYN-XXXX-XXXX</small>
                </div>

                <button type="submit" class="btn btn-primary w-100 btn-lg" id="loginBtn">
                    <span class="btn-text"><i class="fas fa-sign-in-alt"></i> Login</span>
                    <span class="btn-loading" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i> Memproses...
                    </span>
                </button>
            </form>

            <script>
            document.getElementById('loginForm').addEventListener('submit', function(e) {
                var btn = document.getElementById('loginBtn');
                var btnText = btn.querySelector('.btn-text');
                var btnLoading = btn.querySelector('.btn-loading');
                var licenseKey = document.getElementById('license_key').value.trim();

                // Validate license key format
                if (!licenseKey) {
                    e.preventDefault();
                    alert('Silakan masukkan License Key Anda.');
                    return false;
                }

                // Show loading state
                btnText.style.display = 'none';
                btnLoading.style.display = 'inline';
                btn.disabled = true;

                // Reset button after 10 seconds (in case of error)
                setTimeout(function() {
                    btnText.style.display = 'inline';
                    btnLoading.style.display = 'none';
                    btn.disabled = false;
                }, 10000);
            });
            </script>

            <div class="mt-4 p-3 bg-dark rounded">
                <h6 class="text-warning mb-2"><i class="fas fa-info-circle"></i> Cara Mendapatkan License Key</h6>
                <ol class="small text-muted mb-0 ps-3">
                    <li>Daftar OlympTrade via link afiliasi kami</li>
                    <li>Registrasi di ZYN Trade System</li>
                    <li>Tunggu verifikasi admin (maks 24 jam)</li>
                    <li>License Key dikirim via Email/Telegram</li>
                </ol>
            </div>

            <div class="auth-divider">
                <span>atau</span>
            </div>

            <p class="text-center mb-0">
                Belum punya License Key?
                <a href="register.php" class="text-primary fw-bold">Daftar Sekarang</a>
            </p>

            <div class="text-center mt-3">
                <a href="<?php echo TELEGRAM_SUPPORT; ?>" target="_blank" class="text-muted small">
                    <i class="fab fa-telegram"></i> Butuh bantuan? Hubungi Support
                </a>
            </div>

            <!-- INI RUMAHMU Section -->
            <div class="mt-4 p-3 border border-primary rounded text-center">
                <h6 class="text-primary mb-2"><i class="fas fa-home"></i> 🏠 INI RUMAHMU, TOLONG DIRAWAT!</h6>
                <p class="small text-muted mb-2">
                    Cara support kami agar sistem selalu kasih yang terbaik untuk kamu:
                </p>
                <ul class="small text-muted mb-0 text-start ps-4">
                    <li>1️⃣ Daftar akun GRATIS via link kami</li>
                    <li>2️⃣ Ketika sudah profit, naik level PREMIUM dengan bayar bulanan</li>
                </ul>
                <p class="small text-primary mt-2 mb-0">
                    <strong>Itu sudah cukup sebagai tanda support! 🙏</strong><br>
                    Kami akan selalu kasih yang TERBAIK untuk kamu.
                </p>
            </div>
        </div>
    </div>
</section>

<style>
.auth-card {
    max-width: 450px;
    margin: 0 auto;
}

#license_key {
    font-size: 1.2rem;
    text-align: center;
}

#license_key::placeholder {
    font-size: 1rem;
    letter-spacing: 3px;
}
</style>

<?php require_once 'includes/footer.php'; ?>
