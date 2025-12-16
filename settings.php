<?php
$page_title = 'Register';
require_once 'includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';
$success = '';
$countries = getCountryList();

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $email = cleanInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $fullname = cleanInput($_POST['fullname'] ?? '');
        $country = cleanInput($_POST['country'] ?? '');
        $olymptrade_id = cleanInput($_POST['olymptrade_id'] ?? '');
        $phone = cleanInput($_POST['phone'] ?? '');

        // Validation
        $errors = [];

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }

        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }

        if ($password !== $confirm_password) {
            $errors[] = 'Passwords do not match.';
        }

        if (empty($fullname)) {
            $errors[] = 'Please enter your full name.';
        }

        if (empty($country)) {
            $errors[] = 'Please select your country.';
        }

        if (empty($olymptrade_id) || strlen($olymptrade_id) < 6) {
            $errors[] = 'Please enter a valid OlympTrade ID.';
        }

        if (!isset($_POST['terms'])) {
            $errors[] = 'You must agree to the Terms of Service.';
        }

        if (!isset($_POST['affiliate'])) {
            $errors[] = 'Please confirm you registered via our affiliate link.';
        }

        if (!empty($errors)) {
            $error = implode('<br>', $errors);
        } else {
            $result = registerUser($email, $password, $fullname, $country, $olymptrade_id, $phone);

            if ($result['success']) {
                redirect('login.php?registered=1');
            } else {
                $error = $result['message'];
            }
        }
    }
}
?>

<section class="auth-page">
    <div class="container">
        <div class="card auth-card" style="max-width: 600px;">
            <div class="auth-header">
                <div class="mb-4">
                    <span class="brand-logo" style="font-size: 2.5rem;">ZYN</span>
                </div>
                <h1 class="auth-title">Daftar & Dapatkan Robot GRATIS</h1>
                <p class="auth-subtitle">🎁 BONUS: Akses SEMUA 10 Strategy GRATIS 1 Bulan!</p>
            </div>

            <!-- STEP BY STEP GUIDE -->
            <div class="registration-steps mb-4">
                <div class="alert alert-warning border-warning">
                    <h5 class="mb-3"><i class="fas fa-exclamation-triangle"></i> WAJIB BACA SEBELUM DAFTAR!</h5>

                    <div class="step-item mb-3 p-3 bg-dark rounded">
                        <div class="d-flex align-items-start">
                            <span class="step-number">1</span>
                            <div class="ms-3">
                                <strong class="text-warning">Buat Akun OlympTrade BARU</strong>
                                <p class="mb-2 small text-light">Kamu WAJIB daftar akun baru via link di bawah untuk mendapatkan bonus 1 bulan VIP!</p>
                                <a href="<?php echo getLocalizedAffiliateLink(); ?>" target="_blank" class="btn btn-success btn-sm">
                                    <i class="fas fa-external-link-alt"></i> DAFTAR OLYMPTRADE SEKARANG
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="step-item mb-3 p-3 bg-dark rounded">
                        <div class="d-flex align-items-start">
                            <span class="step-number">2</span>
                            <div class="ms-3">
                                <strong class="text-info">Deposit Minimal $10</strong>
                                <p class="mb-0 small text-light">Setelah daftar, lakukan deposit minimal $10 untuk aktivasi akun.</p>
                            </div>
                        </div>
                    </div>

                    <div class="step-item mb-3 p-3 bg-dark rounded">
                        <div class="d-flex align-items-start">
                            <span class="step-number">3</span>
                            <div class="ms-3">
                                <strong class="text-primary">Catat Broker ID Kamu</strong>
                                <p class="mb-0 small text-light">Buka Profile di OlympTrade → Catat ID kamu (angka 8+ digit).</p>
                            </div>
                        </div>
                    </div>

                    <div class="step-item p-3 bg-dark rounded">
                        <div class="d-flex align-items-start">
                            <span class="step-number">4</span>
                            <div class="ms-3">
                                <strong class="text-success">Isi Form di Bawah</strong>
                                <p class="mb-0 small text-light">Masukkan Broker ID baru kamu dan tunggu approval admin (max 24 jam).</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-success mb-0">
                    <i class="fas fa-gift"></i>
                    <strong>BONUS EKSKLUSIF:</strong> Daftar via link kami = Akses SEMUA 10 Strategy (senilai Rp 1.500.000) <strong>GRATIS 1 BULAN!</strong>
                </div>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="" class="needs-validation" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="fullname" class="form-label">Nama Lengkap *</label>
                        <input type="text" class="form-control" id="fullname" name="fullname"
                               placeholder="Masukkan nama lengkap" required
                               value="<?php echo isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : ''; ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="country" class="form-label">Negara *</label>
                        <select class="form-select" id="country" name="country" required>
                            <option value="">Pilih negara</option>
                            <?php foreach ($countries as $code => $name): ?>
                            <option value="<?php echo $code; ?>" <?php echo (isset($_POST['country']) && $_POST['country'] === $code) ? 'selected' : ''; ?>>
                                <?php echo $name; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Alamat Email *</label>
                    <input type="email" class="form-control" id="email" name="email"
                           placeholder="Masukkan email aktif" required
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    <div class="form-text">License key akan dikirim ke email ini</div>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">No. WhatsApp (Opsional)</label>
                    <input type="tel" class="form-control" id="phone" name="phone"
                           placeholder="Contoh: +6281234567890"
                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                </div>

                <div class="mb-3">
                    <label for="olymptrade_id" class="form-label">Broker ID OlympTrade *</label>
                    <input type="text" class="form-control" id="olymptrade_id" name="olymptrade_id"
                           placeholder="Contoh: 12345678" required
                           value="<?php echo isset($_POST['olymptrade_id']) ? htmlspecialchars($_POST['olymptrade_id']) : ''; ?>">
                    <div class="form-text">
                        <i class="fas fa-info-circle"></i> Buka OlympTrade → Profile → Lihat ID kamu (angka 8+ digit)
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Password *</label>
                        <div class="input-group">
                            <input type="password" class="form-control border-end-0" id="password" name="password"
                                   placeholder="Minimal 8 karakter" required minlength="8">
                            <button type="button" class="input-group-text bg-transparent border-start-0 password-toggle">
                                <i class="fas fa-eye text-muted"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="confirm_password" class="form-label">Ulangi Password *</label>
                        <div class="input-group">
                            <input type="password" class="form-control border-end-0" id="confirm_password" name="confirm_password"
                                   placeholder="Ketik ulang password" required>
                            <button type="button" class="input-group-text bg-transparent border-start-0 password-toggle">
                                <i class="fas fa-eye text-muted"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="affiliate" name="affiliate" required>
                        <label class="form-check-label" for="affiliate">
                            <strong>Saya konfirmasi</strong> telah mendaftar akun OlympTrade BARU via link ZYN dan sudah deposit minimal $10 *
                        </label>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                        <label class="form-check-label" for="terms">
                            Saya setuju dengan <a href="terms.php" target="_blank">Syarat & Ketentuan</a>,
                            <a href="privacy.php" target="_blank">Kebijakan Privasi</a>, dan
                            <a href="disclaimer.php" target="_blank">Disclaimer Risiko</a> *
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 btn-lg">
                    <i class="fas fa-rocket"></i> Daftar & Dapatkan Robot GRATIS
                </button>
            </form>

            <div class="auth-divider">
                <span>or</span>
            </div>

            <p class="text-center mb-0">
                Already have an account?
                <a href="login.php" class="text-primary fw-bold">Login</a>
            </p>
        </div>
    </div>
</section>

<style>
.step-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: #fff;
    font-weight: 700;
    border-radius: 50%;
    flex-shrink: 0;
}
.registration-steps .step-item {
    border-left: 3px solid var(--primary);
}
</style>

<script>
// Password match validation
document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    if (this.value !== password) {
        this.setCustomValidity('Passwords do not match');
    } else {
        this.setCustomValidity('');
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>