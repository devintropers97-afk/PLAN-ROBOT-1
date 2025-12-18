<?php
/**
 * ZYN Trade System - Login Page
 * IMPORTANT: Login logic MUST be before any HTML output
 */

// Load config and functions FIRST (no HTML output)
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if already logged in - redirect BEFORE any output
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin/index.php');
    } else {
        redirect('dashboard.php');
    }
    exit;
}

$error = '';
$success = '';

// Handle login form submission BEFORE any output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = __('login_error_invalid_request');
    } else {
        $license_key = strtoupper(cleanInput($_POST['license_key'] ?? ''));

        if (empty($license_key)) {
            $error = __('login_error_empty_key');
        } else {
            $result = loginWithLicenseKey($license_key);

            if ($result['success']) {
                // Redirect admin to admin panel, users to dashboard
                if (isset($result['user']['role']) && $result['user']['role'] === 'admin') {
                    redirect('admin/index.php');
                } else {
                    redirect('dashboard.php');
                }
                exit;
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Check for success message from registration
if (isset($_GET['registered'])) {
    $success = __('login_success_registered');
}
if (isset($_GET['verified'])) {
    $success = __('login_success_verified');
}

// NOW load header (HTML output starts here)
$page_title = __('login_title');
require_once 'includes/header.php';

// WhatsApp number for support
$whatsappNumber = '6281234567890';
$whatsappLink = "https://wa.me/{$whatsappNumber}";
?>

<section class="auth-page">
    <div class="container">
        <div class="card auth-card">
            <div class="auth-header">
                <div class="mb-4">
                    <span class="brand-logo" style="font-size: 2.5rem;">ZYN</span>
                    <small class="d-block text-muted mt-1">Trade System</small>
                </div>
                <p class="tagline-hero text-primary mb-3"><?php _e('hero_tagline'); ?></p>
                <h1 class="auth-title"><?php _e('login_title'); ?></h1>
                <p class="auth-subtitle"><?php _e('login_subtitle'); ?></p>
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
                    <label for="license_key" class="form-label"><?php _e('login_label_key'); ?></label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="fas fa-key text-primary"></i>
                        </span>
                        <input type="text" class="form-control border-start-0 text-uppercase"
                               id="license_key" name="license_key"
                               placeholder="<?php _e('login_placeholder'); ?>" required
                               style="letter-spacing: 2px; font-family: monospace;"
                               value="<?php echo isset($_POST['license_key']) ? htmlspecialchars($_POST['license_key']) : ''; ?>"
                               autocomplete="off">
                    </div>
                    <small class="text-muted"><?php _e('login_format'); ?></small>
                </div>

                <button type="submit" class="btn btn-primary w-100 btn-lg" id="loginBtn">
                    <span class="btn-text"><i class="fas fa-sign-in-alt"></i> <?php _e('login_btn'); ?></span>
                    <span class="btn-loading" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i> <?php _e('login_processing'); ?>
                    </span>
                </button>
            </form>

            <script>
            document.getElementById('loginForm').addEventListener('submit', function(e) {
                var btn = document.getElementById('loginBtn');
                var btnText = btn.querySelector('.btn-text');
                var btnLoading = btn.querySelector('.btn-loading');
                var licenseKey = document.getElementById('license_key').value.trim();

                if (!licenseKey) {
                    e.preventDefault();
                    alert('<?php echo addslashes(__('login_error_empty_key')); ?>');
                    return false;
                }

                btnText.style.display = 'none';
                btnLoading.style.display = 'inline';
                btn.disabled = true;

                setTimeout(function() {
                    btnText.style.display = 'inline';
                    btnLoading.style.display = 'none';
                    btn.disabled = false;
                }, 5000);
            });
            </script>

            <div class="mt-4 p-3 bg-dark rounded">
                <h6 class="text-warning mb-2"><i class="fas fa-info-circle"></i> <?php _e('login_how_title'); ?></h6>
                <ol class="small text-muted mb-0 ps-3">
                    <li><?php _e('login_how_1'); ?></li>
                    <li><?php _e('login_how_2'); ?></li>
                    <li><?php _e('login_how_3'); ?></li>
                    <li><?php _e('login_how_4'); ?></li>
                </ol>
            </div>

            <div class="auth-divider">
                <span><?php _e('login_or'); ?></span>
            </div>

            <p class="text-center mb-0">
                <?php _e('login_no_key'); ?>
                <a href="register.php" class="text-primary fw-bold"><?php _e('login_register'); ?></a>
            </p>

            <div class="text-center mt-3">
                <a href="<?php echo $whatsappLink; ?>" target="_blank" class="text-muted small">
                    <i class="fab fa-whatsapp"></i> <?php _e('login_help'); ?>
                </a>
            </div>

            <!-- INI RUMAHMU Section -->
            <div class="mt-4 p-3 border border-primary rounded text-center">
                <h6 class="text-primary mb-2"><i class="fas fa-home"></i> <?php _e('support_title'); ?></h6>
                <p class="small text-muted mb-2">
                    <?php _e('support_desc'); ?>
                </p>
                <ul class="small text-muted mb-0 text-start ps-4">
                    <li><?php _e('support_step_1'); ?></li>
                    <li><?php _e('support_step_2'); ?></li>
                </ul>
                <p class="small text-primary mt-2 mb-0">
                    <strong><?php _e('support_footer'); ?></strong>
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
