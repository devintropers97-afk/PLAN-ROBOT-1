<?php
require_once 'includes/language.php';
$page_title = __('register_title');
require_once 'includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';
$success = '';
$showSuccess = false;
$countries = getCountryList();

// Country to affiliate link mapping
$countryLinks = [
    'ID' => 'https://olymptrade-vid.com/id-id/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'MY' => 'https://olymptrade-vid.com/ms-ms/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'PH' => 'https://olymptrade.com/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'TH' => 'https://olymptrade-vid.com/th-th/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'VN' => 'https://olymptrade-vid.com/vi-vi/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'IN' => 'https://olymptrade-vid.com/hi-hi/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'PK' => 'https://olymptrade.com/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'BD' => 'https://olymptrade-vid.com/bn-bn/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'BR' => 'https://olymptrade-vid.com/pt-pt/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'MX' => 'https://olymptrade-vid.com/es-es/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'CO' => 'https://olymptrade-vid.com/es-es/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'AR' => 'https://olymptrade-vid.com/es-es/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'PE' => 'https://olymptrade-vid.com/es-es/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'CL' => 'https://olymptrade-vid.com/es-es/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'NG' => 'https://olymptrade.com/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'KE' => 'https://olymptrade.com/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'ZA' => 'https://olymptrade.com/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'EG' => 'https://olymptrade-vid.com/ar-ar/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'TR' => 'https://olymptrade-vid.com/tr-tr/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'RU' => 'https://olymptrade-vid.com/ru-ru/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'UA' => 'https://olymptrade-vid.com/ru-ru/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'PL' => 'https://olymptrade.com/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'ES' => 'https://olymptrade-vid.com/es-es/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'PT' => 'https://olymptrade-vid.com/pt-pt/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'IT' => 'https://olymptrade.com/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'DE' => 'https://olymptrade-vid.com/de-de/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'FR' => 'https://olymptrade-vid.com/fr-fr/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'GB' => 'https://olymptrade.com/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'US' => 'https://olymptrade.com/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'CA' => 'https://olymptrade.com/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'AU' => 'https://olymptrade.com/?affiliate_id=660784&subid1=ZYNtradeSystem',
    'OTHER' => 'https://olymptrade.com/?affiliate_id=660784&subid1=ZYNtradeSystem',
];

// WhatsApp support
$whatsappNumber = '6281234567890';
$whatsappLink = "https://wa.me/{$whatsappNumber}";

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = __('register_error_csrf');
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
            $errors[] = __('register_error_email');
        }

        if (strlen($password) < 8) {
            $errors[] = __('register_error_password_length');
        }

        if ($password !== $confirm_password) {
            $errors[] = __('register_error_password_match');
        }

        if (empty($fullname)) {
            $errors[] = __('register_error_fullname');
        }

        if (empty($country)) {
            $errors[] = __('register_error_country');
        }

        if (empty($olymptrade_id) || strlen($olymptrade_id) < 6) {
            $errors[] = __('register_error_olympid');
        }

        // WhatsApp mandatory
        if (empty($phone)) {
            $errors[] = __('register_error_whatsapp');
        }

        if (!isset($_POST['terms'])) {
            $errors[] = __('register_error_terms');
        }

        if (!isset($_POST['affiliate'])) {
            $errors[] = __('register_error_affiliate');
        }

        if (!empty($errors)) {
            $error = implode('<br>', $errors);
        } else {
            $result = registerUser($email, $password, $fullname, $country, $olymptrade_id, $phone);

            if ($result['success']) {
                $showSuccess = true;
            } else {
                $error = $result['message'];
            }
        }
    }
}
?>

<section class="auth-page section" style="padding-top: calc(var(--navbar-height) + 4rem);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <?php if ($showSuccess): ?>
                <!-- Registration Success Message -->
                <div class="card auth-card text-center" style="max-width: 100%; border-radius: 20px;">
                    <div class="card-body py-5 px-4">
                        <div class="mb-4">
                            <div class="success-icon mb-4" style="font-size: 5rem; color: #10b981;">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h2 class="text-success mb-3"><?php _e('register_success_title'); ?></h2>
                            <p class="lead" style="color: #c8c8d8;"><?php _e('register_success_desc'); ?></p>
                        </div>

                        <div class="alert alert-info text-start" style="background: rgba(0, 212, 255, 0.1); border: 1px solid rgba(0, 212, 255, 0.3); border-radius: 16px;">
                            <h5 class="alert-heading mb-3" style="color: #ffffff;"><i class="fas fa-clock me-2"></i> <?php _e('register_next_steps_title'); ?></h5>
                            <ol class="mb-0 ps-3" style="color: #c8c8d8;">
                                <?php
                                $successSteps = __('register_success_steps');
                                if (is_array($successSteps)) {
                                    $icons = ['fa-search text-primary', 'fa-hourglass-half text-warning', 'fa-key text-success', 'fa-bell text-info'];
                                    foreach ($successSteps as $index => $step): ?>
                                <li class="<?php echo $index < count($successSteps) - 1 ? 'mb-2' : ''; ?>"><i class="fas <?php echo $icons[$index] ?? 'fa-check'; ?> me-2"></i> <?php echo $step; ?></li>
                                <?php endforeach;
                                }
                                ?>
                            </ol>
                        </div>

                        <div class="alert alert-warning mt-4" style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 12px;">
                            <p class="mb-0" style="color: #c8c8d8;">
                                <i class="fas fa-info-circle text-warning me-2"></i>
                                <?php _e('register_success_note'); ?>
                            </p>
                        </div>

                        <div class="mt-4 d-flex gap-3 justify-content-center flex-wrap">
                            <a href="<?php echo $whatsappLink; ?>" target="_blank" class="btn btn-success btn-lg">
                                <i class="fab fa-whatsapp me-2"></i> <?php _e('register_btn_contact_support'); ?>
                            </a>
                            <a href="login.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i> <?php _e('register_btn_go_login'); ?>
                            </a>
                        </div>
                    </div>
                </div>
                <?php else: ?>

                <!-- Important Warning Banner -->
                <div class="alert alert-warning mb-4" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(245, 158, 11, 0.05)); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 16px;">
                    <div class="d-flex align-items-start gap-3">
                        <div class="alert-icon" style="font-size: 2rem;">
                            <i class="fas fa-exclamation-triangle text-warning"></i>
                        </div>
                        <div>
                            <h5 class="text-warning mb-2"><i class="fas fa-ban me-2"></i><?php _e('register_warning_title'); ?></h5>
                            <p class="mb-2" style="color: #ffffff;">
                                <strong><?php _e('register_warning_text'); ?></strong>
                            </p>
                            <ul class="mb-0" style="color: #c8c8d8;">
                                <li><?php _e('register_warning_1'); ?></li>
                                <li><?php _e('register_warning_2'); ?></li>
                                <li><?php _e('register_warning_3'); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Registration Steps Card -->
                <div class="card auth-card" style="max-width: 100%; border-radius: 20px;">
                    <div class="auth-header text-center pb-0">
                        <div class="mb-3">
                            <span class="brand-logo" style="font-size: 2.5rem;">ZYN</span>
                            <small class="d-block text-muted mt-1">Trade System</small>
                        </div>
                        <h1 class="auth-title h3"><?php _e('register_title'); ?></h1>
                        <p class="auth-subtitle" style="color: #c8c8d8;"><?php _e('register_subtitle'); ?></p>
                    </div>

                    <!-- Progress Steps -->
                    <div class="registration-steps px-4 py-3">
                        <div class="step-progress">
                            <div class="step active" data-step="1">
                                <div class="step-number">1</div>
                                <div class="step-label"><?php _e('register_step_1'); ?></div>
                            </div>
                            <div class="step-line"></div>
                            <div class="step" data-step="2">
                                <div class="step-number">2</div>
                                <div class="step-label"><?php _e('register_step_2'); ?></div>
                            </div>
                            <div class="step-line"></div>
                            <div class="step" data-step="3">
                                <div class="step-number">3</div>
                                <div class="step-label"><?php _e('register_step_3'); ?></div>
                            </div>
                            <div class="step-line"></div>
                            <div class="step" data-step="4">
                                <div class="step-number">4</div>
                                <div class="step-label"><?php _e('register_step_4'); ?></div>
                            </div>
                        </div>
                    </div>

                    <?php if ($error): ?>
                    <div class="alert alert-danger mx-4">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Step 1: Country Selection -->
                    <div class="step-content" id="step1Content">
                        <div class="card-body px-4 pb-4">
                            <div class="text-center mb-4">
                                <div class="step-icon mb-3">
                                    <i class="fas fa-globe-asia" style="font-size: 3rem; color: var(--primary);"></i>
                                </div>
                                <h4 style="color: #ffffff;"><?php _e('register_step_1_title'); ?></h4>
                                <p style="color: #c8c8d8;"><?php _e('register_step_1_desc'); ?></p>
                            </div>

                            <div class="mb-4">
                                <label for="selectCountry" class="form-label"><?php _e('register_country_label'); ?> <span class="text-danger">*</span></label>
                                <select class="form-select form-select-lg" id="selectCountry" required>
                                    <option value=""><?php _e('register_country_placeholder'); ?></option>
                                    <?php foreach ($countries as $code => $name): ?>
                                    <option value="<?php echo $code; ?>" data-link="<?php echo $countryLinks[$code] ?? $countryLinks['OTHER']; ?>">
                                        <?php echo getCountryFlag($name); ?> <?php echo $name; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <button type="button" class="btn btn-primary w-100 btn-lg" id="btnStep1Next" disabled>
                                <span><?php _e('register_btn_continue'); ?></span> <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Register OlympTrade -->
                    <div class="step-content d-none" id="step2Content">
                        <div class="card-body px-4 pb-4">
                            <div class="text-center mb-4">
                                <div class="step-icon mb-3">
                                    <i class="fas fa-user-plus" style="font-size: 3rem; color: var(--primary);"></i>
                                </div>
                                <h4 style="color: #ffffff;"><?php _e('register_step_2_title'); ?></h4>
                                <p style="color: #c8c8d8;"><?php _e('register_step_2_desc'); ?></p>
                            </div>

                            <div class="alert alert-info mb-4" style="border-radius: 12px; background: rgba(0, 212, 255, 0.1); border-color: rgba(0, 212, 255, 0.3);">
                                <h6 class="alert-heading" style="color: #ffffff;"><i class="fas fa-info-circle me-2"></i><?php _e('register_olymp_instructions_title'); ?></h6>
                                <ol class="mb-0 ps-3" style="color: #c8c8d8;">
                                    <?php
                                    $instructions = __('register_olymp_instructions');
                                    if (is_array($instructions)) {
                                        foreach ($instructions as $instruction): ?>
                                    <li><?php echo $instruction; ?></li>
                                    <?php endforeach;
                                    }
                                    ?>
                                </ol>
                            </div>

                            <div class="text-center mb-4">
                                <a href="" id="olymptradeLink" target="_blank" class="btn btn-success btn-lg px-5 py-3" style="border-radius: 12px; font-size: 1.1rem;">
                                    <i class="fas fa-external-link-alt me-2"></i> <?php _e('register_olymp_btn'); ?>
                                </a>
                                <p class="small mt-2" style="color: #b8b8c8;">
                                    <i class="fas fa-shield-alt me-1"></i> <?php _e('register_olymp_safe'); ?> <strong id="selectedCountryName"></strong>
                                </p>
                            </div>

                            <hr class="my-4">

                            <div class="form-check mb-4">
                                <input type="checkbox" class="form-check-input" id="confirmOlymptrade" style="width: 20px; height: 20px;">
                                <label class="form-check-label ms-2" for="confirmOlymptrade" style="font-size: 1rem; color: #c8c8d8;">
                                    <strong><?php _e('register_olymp_confirm'); ?></strong>
                                </label>
                            </div>

                            <div class="d-flex gap-3">
                                <button type="button" class="btn btn-outline-secondary btn-lg flex-fill" id="btnStep2Back">
                                    <i class="fas fa-arrow-left me-2"></i> <?php _e('register_btn_back'); ?>
                                </button>
                                <button type="button" class="btn btn-primary btn-lg flex-fill" id="btnStep2Next" disabled>
                                    <?php _e('register_btn_continue'); ?> <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Registration Form -->
                    <div class="step-content d-none" id="step3Content">
                        <div class="card-body px-4 pb-4">
                            <div class="text-center mb-4">
                                <div class="step-icon mb-3">
                                    <i class="fas fa-edit" style="font-size: 3rem; color: var(--primary);"></i>
                                </div>
                                <h4 style="color: #ffffff;"><?php _e('register_step_3_title'); ?></h4>
                                <p style="color: #c8c8d8;"><?php _e('register_step_3_desc'); ?></p>
                            </div>

                            <form method="POST" action="" class="needs-validation" novalidate id="registerForm">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="country" id="hiddenCountry" value="">

                                <!-- OlympTrade ID - Most Important -->
                                <div class="mb-4 p-3" style="background: rgba(var(--primary-rgb), 0.05); border-radius: 12px; border: 2px solid var(--primary);">
                                    <label for="olymptrade_id" class="form-label fw-bold">
                                        <i class="fas fa-id-card text-primary me-2"></i><?php _e('form_olymptrade_id'); ?> <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control form-control-lg" id="olymptrade_id" name="olymptrade_id"
                                           placeholder="<?php _e('form_olymptrade_id_placeholder'); ?>" required
                                           pattern="[0-9]{6,12}"
                                           value="<?php echo isset($_POST['olymptrade_id']) ? htmlspecialchars($_POST['olymptrade_id']) : ''; ?>">
                                    <div class="form-text" style="color: #b8b8c8;">
                                        <i class="fas fa-question-circle me-1"></i>
                                        <?php _e('form_olymptrade_id_hint'); ?>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="fullname" class="form-label"><?php _e('form_fullname'); ?> <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="fullname" name="fullname"
                                               placeholder="<?php _e('form_fullname_placeholder'); ?>" required
                                               value="<?php echo isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : ''; ?>">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label"><?php _e('form_email'); ?> <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email"
                                               placeholder="<?php _e('form_email_placeholder'); ?>" required
                                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                        <div class="form-text" style="color: #b8b8c8;"><?php _e('form_email_hint'); ?></div>
                                    </div>
                                </div>

                                <!-- WhatsApp - WAJIB -->
                                <div class="mb-3 p-3" style="background: rgba(37, 211, 102, 0.1); border-radius: 12px; border: 2px solid #25d366;">
                                    <label for="phone" class="form-label fw-bold">
                                        <i class="fab fa-whatsapp text-success me-2"></i><?php _e('form_whatsapp'); ?> <span class="text-danger">* <?php _e('form_required'); ?></span>
                                    </label>
                                    <input type="tel" class="form-control form-control-lg" id="phone" name="phone"
                                           placeholder="<?php _e('form_whatsapp_placeholder'); ?>" required
                                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                    <div class="form-text" style="color: #b8b8c8;">
                                        <i class="fas fa-info-circle me-1"></i>
                                        <?php _e('form_whatsapp_hint'); ?>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="password" class="form-label"><?php _e('form_password'); ?> <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="password" class="form-control border-end-0" id="password" name="password"
                                                   placeholder="<?php _e('form_password_placeholder'); ?>" required minlength="8">
                                            <button type="button" class="input-group-text bg-transparent border-start-0 password-toggle">
                                                <i class="fas fa-eye" style="color: #b8b8c8;"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="confirm_password" class="form-label"><?php _e('form_confirm_password'); ?> <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="password" class="form-control border-end-0" id="confirm_password" name="confirm_password"
                                                   placeholder="<?php _e('form_confirm_password_placeholder'); ?>" required>
                                            <button type="button" class="input-group-text bg-transparent border-start-0 password-toggle">
                                                <i class="fas fa-eye" style="color: #b8b8c8;"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="affiliate" name="affiliate" required>
                                        <label class="form-check-label" for="affiliate" style="color: #c8c8d8;">
                                            <?php _e('form_affiliate_confirm'); ?> <span class="text-danger">*</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                                        <label class="form-check-label" for="terms" style="color: #c8c8d8;">
                                            <?php _e('form_terms_confirm'); ?> <a href="terms.php" target="_blank"><?php _e('form_terms'); ?></a>,
                                            <a href="privacy.php" target="_blank"><?php _e('form_privacy'); ?></a>, <?php _e('form_and'); ?>
                                            <a href="disclaimer.php" target="_blank"><?php _e('form_disclaimer'); ?></a> <span class="text-danger">*</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="d-flex gap-3">
                                    <button type="button" class="btn btn-outline-secondary btn-lg flex-fill" id="btnStep3Back">
                                        <i class="fas fa-arrow-left me-2"></i> <?php _e('register_btn_back'); ?>
                                    </button>
                                    <button type="submit" class="btn btn-primary btn-lg flex-fill" id="btnSubmit">
                                        <span class="btn-text"><i class="fas fa-paper-plane me-2"></i> <?php _e('form_btn_register'); ?></span>
                                        <span class="btn-loading d-none"><i class="fas fa-spinner fa-spin me-2"></i> <?php _e('form_processing'); ?></span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Step 4: What Happens Next -->
                    <div class="info-section px-4 pb-4 after-register-info">
                        <div class="alert mb-0" style="background: rgba(var(--primary-rgb), 0.05); border: 1px solid rgba(var(--primary-rgb), 0.2); border-radius: 12px;">
                            <h6 class="mb-3" style="color: #ffffff;"><i class="fas fa-clock me-2 text-primary"></i><?php _e('after_register_title'); ?></h6>
                            <div class="d-flex align-items-start mb-2">
                                <span class="badge bg-primary me-3" style="min-width: 24px;">1</span>
                                <span style="color: #c8c8d8;"><?php _e('after_register_1'); ?></span>
                            </div>
                            <div class="d-flex align-items-start mb-2">
                                <span class="badge bg-primary me-3" style="min-width: 24px;">2</span>
                                <span style="color: #c8c8d8;"><?php _e('after_register_2'); ?></span>
                            </div>
                            <div class="d-flex align-items-start mb-2">
                                <span class="badge bg-primary me-3" style="min-width: 24px;">3</span>
                                <span style="color: #c8c8d8;"><?php _e('after_register_3'); ?></span>
                            </div>
                            <div class="d-flex align-items-start">
                                <span class="badge bg-primary me-3" style="min-width: 24px;">4</span>
                                <span style="color: #c8c8d8;"><?php _e('after_register_4'); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="text-center px-4 pb-4">
                        <p class="mb-0" style="color: #c8c8d8;">
                            <?php _e('already_have_key'); ?>
                            <a href="login.php" class="text-primary fw-bold"><?php _e('login_here'); ?></a>
                        </p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<style>
/* Step Progress Styles */
.step-progress {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem 0;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
}

.step-number {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    border: 2px solid rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.9rem;
    color: var(--text-secondary);
    transition: all 0.3s ease;
}

.step.active .step-number,
.step.completed .step-number {
    background: var(--primary);
    border-color: var(--primary);
    color: #fff;
    box-shadow: 0 0 15px rgba(0, 212, 255, 0.5);
}

.step.completed .step-number::after {
    content: '\f00c';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
}

.step-label {
    font-size: 0.7rem;
    color: var(--text-muted);
    margin-top: 0.5rem;
    text-align: center;
    max-width: 70px;
}

.step.active .step-label {
    color: var(--primary);
    font-weight: 600;
}

.step-line {
    width: 40px;
    height: 2px;
    background: rgba(255, 255, 255, 0.1);
    margin: 0 0.5rem;
    margin-bottom: 1.5rem;
}

.step.completed + .step-line {
    background: var(--primary);
}

/* Step Content Animation */
.step-content {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Form Styles */
.form-control:focus,
.form-select:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(0, 212, 255, 0.15);
}

/* Password Toggle */
.password-toggle {
    cursor: pointer;
    border-color: rgba(255, 255, 255, 0.1);
}

.password-toggle:hover {
    background: rgba(255, 255, 255, 0.05) !important;
}

/* Responsive */
@media (max-width: 576px) {
    .step-label {
        font-size: 0.6rem;
        max-width: 50px;
    }

    .step-line {
        width: 20px;
    }

    .step-number {
        width: 30px;
        height: 30px;
        font-size: 0.8rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const selectCountry = document.getElementById('selectCountry');
    const btnStep1Next = document.getElementById('btnStep1Next');
    const btnStep2Back = document.getElementById('btnStep2Back');
    const btnStep2Next = document.getElementById('btnStep2Next');
    const btnStep3Back = document.getElementById('btnStep3Back');
    const confirmOlymptrade = document.getElementById('confirmOlymptrade');
    const olymptradeLink = document.getElementById('olymptradeLink');
    const selectedCountryName = document.getElementById('selectedCountryName');
    const hiddenCountry = document.getElementById('hiddenCountry');

    const step1Content = document.getElementById('step1Content');
    const step2Content = document.getElementById('step2Content');
    const step3Content = document.getElementById('step3Content');

    const steps = document.querySelectorAll('.step');

    // Check if elements exist (not on success page)
    if (!selectCountry) return;

    // Step 1: Country selection
    selectCountry.addEventListener('change', function() {
        btnStep1Next.disabled = !this.value;

        if (this.value) {
            const selectedOption = this.options[this.selectedIndex];
            const link = selectedOption.dataset.link;
            olymptradeLink.href = link;
            selectedCountryName.textContent = selectedOption.text;
            hiddenCountry.value = this.value;
        }
    });

    // Step navigation
    btnStep1Next.addEventListener('click', function() {
        step1Content.classList.add('d-none');
        step2Content.classList.remove('d-none');
        updateSteps(2);
    });

    btnStep2Back.addEventListener('click', function() {
        step2Content.classList.add('d-none');
        step1Content.classList.remove('d-none');
        updateSteps(1);
    });

    confirmOlymptrade.addEventListener('change', function() {
        btnStep2Next.disabled = !this.checked;
    });

    btnStep2Next.addEventListener('click', function() {
        step2Content.classList.add('d-none');
        step3Content.classList.remove('d-none');
        updateSteps(3);
    });

    btnStep3Back.addEventListener('click', function() {
        step3Content.classList.add('d-none');
        step2Content.classList.remove('d-none');
        updateSteps(2);
    });

    function updateSteps(currentStep) {
        steps.forEach((step, index) => {
            const stepNum = index + 1;
            step.classList.remove('active', 'completed');

            if (stepNum < currentStep) {
                step.classList.add('completed');
            } else if (stepNum === currentStep) {
                step.classList.add('active');
            }
        });
    }

    // Password toggle
    document.querySelectorAll('.password-toggle').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    // Password match validation
    document.getElementById('confirm_password').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        if (this.value !== password) {
            this.setCustomValidity('<?php echo addslashes(__('register_error_password_match')); ?>');
        } else {
            this.setCustomValidity('');
        }
    });

    // Form submit loading state
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        const btn = document.getElementById('btnSubmit');
        const btnText = btn.querySelector('.btn-text');
        const btnLoading = btn.querySelector('.btn-loading');

        btnText.classList.add('d-none');
        btnLoading.classList.remove('d-none');
        btn.disabled = true;

        // Reset after 10 seconds
        setTimeout(function() {
            btnText.classList.remove('d-none');
            btnLoading.classList.add('d-none');
            btn.disabled = false;
        }, 10000);
    });

    // If there's an error, show step 3
    <?php if ($error): ?>
    step1Content.classList.add('d-none');
    step2Content.classList.add('d-none');
    step3Content.classList.remove('d-none');
    updateSteps(3);
    <?php endif; ?>
});
</script>

<?php require_once 'includes/footer.php'; ?>
