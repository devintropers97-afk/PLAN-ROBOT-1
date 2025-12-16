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
        <div class="card auth-card" style="max-width: 550px;">
            <div class="auth-header">
                <div class="mb-4">
                    <span class="brand-logo" style="font-size: 2.5rem;">ZYN</span>
                </div>
                <h1 class="auth-title">Create Account</h1>
                <p class="auth-subtitle">Start your automated trading journey</p>
            </div>

            <!-- Important Notice -->
            <div class="alert alert-info mb-4">
                <i class="fas fa-info-circle"></i>
                <strong>Important:</strong> You must register on OlympTrade via our affiliate link before creating an account here.
                <a href="<?php echo OLYMPTRADE_AFFILIATE_LINK; ?>" target="_blank" class="alert-link">
                    Register OlympTrade <i class="fas fa-external-link-alt"></i>
                </a>
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
                        <label for="fullname" class="form-label">Full Name *</label>
                        <input type="text" class="form-control" id="fullname" name="fullname"
                               placeholder="Enter your full name" required
                               value="<?php echo isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : ''; ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="country" class="form-label">Country *</label>
                        <select class="form-select" id="country" name="country" required>
                            <option value="">Select country</option>
                            <?php foreach ($countries as $code => $name): ?>
                            <option value="<?php echo $code; ?>" <?php echo (isset($_POST['country']) && $_POST['country'] === $code) ? 'selected' : ''; ?>>
                                <?php echo $name; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address *</label>
                    <input type="email" class="form-control" id="email" name="email"
                           placeholder="Enter your email" required
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number (Optional)</label>
                    <input type="tel" class="form-control" id="phone" name="phone"
                           placeholder="e.g., +62812345678"
                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                </div>

                <div class="mb-3">
                    <label for="olymptrade_id" class="form-label">OlympTrade ID *</label>
                    <input type="text" class="form-control" id="olymptrade_id" name="olymptrade_id"
                           placeholder="Enter your OlympTrade ID" required
                           value="<?php echo isset($_POST['olymptrade_id']) ? htmlspecialchars($_POST['olymptrade_id']) : ''; ?>">
                    <div class="form-text">
                        Find your ID in OlympTrade profile settings
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Password *</label>
                        <div class="input-group">
                            <input type="password" class="form-control border-end-0" id="password" name="password"
                                   placeholder="Min 8 characters" required minlength="8">
                            <button type="button" class="input-group-text bg-transparent border-start-0 password-toggle">
                                <i class="fas fa-eye text-muted"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password *</label>
                        <div class="input-group">
                            <input type="password" class="form-control border-end-0" id="confirm_password" name="confirm_password"
                                   placeholder="Repeat password" required>
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
                            I confirm that I registered on OlympTrade via ZYN's affiliate link and deposited minimum $10 *
                        </label>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                        <label class="form-check-label" for="terms">
                            I agree to the <a href="terms.php" target="_blank">Terms of Service</a>,
                            <a href="privacy.php" target="_blank">Privacy Policy</a>, and
                            <a href="disclaimer.php" target="_blank">Risk Disclaimer</a> *
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 btn-lg">
                    <i class="fas fa-user-plus"></i> Create Account
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
