<?php
$page_title = __('mobile_title');
require_once 'includes/header.php';
?>

<section class="section" style="padding-top: calc(var(--navbar-height) + 3rem);">
    <div class="container">
        <div class="section-header fade-in">
            <span class="section-badge"><?php _e('mobile_badge'); ?></span>
            <h1 class="section-title"><?php _e('mobile_heading'); ?></h1>
            <p class="section-desc">
                <?php _e('mobile_desc'); ?>
            </p>
        </div>

        <!-- Download Options -->
        <div class="row g-4 justify-content-center mb-5">
            <!-- Android APK Download -->
            <div class="col-lg-5 col-md-6 fade-in">
                <div class="card mobile-download-card h-100">
                    <div class="card-body text-center">
                        <div class="mobile-icon android-icon mb-4">
                            <i class="fab fa-android"></i>
                        </div>
                        <h3 class="mb-3"><?php _e('mobile_android'); ?></h3>
                        <p class="text-muted mb-4">
                            <?php _e('mobile_android_desc'); ?>
                        </p>
                        <div class="app-specs mb-4">
                            <span class="badge bg-dark me-2">APK v3.0.0</span>
                            <span class="badge bg-dark me-2">12 MB</span>
                            <span class="badge bg-dark">Android 6.0+</span>
                        </div>
                        <a href="downloads/zyn-trade-v3.0.0.apk" class="btn btn-success btn-lg w-100 mb-3" download>
                            <i class="fas fa-download me-2"></i><?php _e('mobile_download_apk'); ?>
                        </a>
                        <small class="d-block text-muted">
                            <i class="fas fa-shield-alt me-1"></i>
                            <?php _e('mobile_safe_verified'); ?>
                        </small>
                    </div>
                </div>
            </div>

            <!-- iOS / Web App -->
            <div class="col-lg-5 col-md-6 fade-in">
                <div class="card mobile-download-card h-100">
                    <div class="card-body text-center">
                        <div class="mobile-icon ios-icon mb-4">
                            <i class="fab fa-apple"></i>
                        </div>
                        <h3 class="mb-3"><?php _e('mobile_ios'); ?></h3>
                        <p class="text-muted mb-4">
                            <?php _e('mobile_ios_desc'); ?>
                        </p>
                        <div class="app-specs mb-4">
                            <span class="badge bg-dark me-2">Web App</span>
                            <span class="badge bg-dark me-2">PWA</span>
                            <span class="badge bg-dark">iOS 12+</span>
                        </div>
                        <button class="btn btn-outline-light btn-lg w-100 mb-3" data-bs-toggle="modal" data-bs-target="#iosGuideModal">
                            <i class="fas fa-plus-square me-2"></i><?php _e('mobile_how_install'); ?>
                        </button>
                        <small class="d-block text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            <?php _e('mobile_ios_guide'); ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Comparison -->
        <div class="fade-in mb-5">
            <h3 class="text-center mb-4">
                <i class="fas fa-mobile-alt me-2 text-primary"></i>
                <?php _e('mobile_features'); ?>
            </h3>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-box text-center">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h5><?php _e('mobile_quick_access'); ?></h5>
                        <p class="text-muted"><?php _e('mobile_quick_access_desc'); ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box text-center">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-bell"></i>
                        </div>
                        <h5><?php _e('mobile_notifications'); ?></h5>
                        <p class="text-muted"><?php _e('mobile_notifications_desc'); ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box text-center">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-expand"></i>
                        </div>
                        <h5><?php _e('mobile_fullscreen'); ?></h5>
                        <p class="text-muted"><?php _e('mobile_fullscreen_desc'); ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box text-center">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <h5><?php _e('mobile_auto_update'); ?></h5>
                        <p class="text-muted"><?php _e('mobile_auto_update_desc'); ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box text-center">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-lock"></i>
                        </div>
                        <h5><?php _e('mobile_secure_login'); ?></h5>
                        <p class="text-muted"><?php _e('mobile_secure_login_desc'); ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box text-center">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h5><?php _e('mobile_monitor'); ?></h5>
                        <p class="text-muted"><?php _e('mobile_monitor_desc'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Android Installation Guide -->
        <div class="fade-in mb-5">
            <h3 class="text-center mb-4">
                <i class="fab fa-android me-2 text-success"></i>
                <?php _e('mobile_android_install'); ?>
            </h3>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="installation-steps">
                        <div class="step-item">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h5><?php _e('mobile_step1'); ?></h5>
                                <p class="text-muted mb-0"><?php _e('mobile_step1_desc'); ?></p>
                            </div>
                        </div>
                        <div class="step-item">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h5><?php _e('mobile_step2'); ?></h5>
                                <p class="text-muted mb-0"><?php _e('mobile_step2_desc'); ?></p>
                            </div>
                        </div>
                        <div class="step-item">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h5><?php _e('mobile_step3'); ?></h5>
                                <p class="text-muted mb-0"><?php _e('mobile_step3_desc'); ?></p>
                            </div>
                        </div>
                        <div class="step-item">
                            <div class="step-number">4</div>
                            <div class="step-content">
                                <h5><?php _e('mobile_step4'); ?></h5>
                                <p class="text-muted mb-0"><?php _e('mobile_step4_desc'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- QR Code Section -->
        <div class="fade-in mb-5">
            <div class="qr-section text-center">
                <h4 class="mb-3">
                    <i class="fas fa-qrcode me-2"></i>
                    <?php _e('mobile_qr_title'); ?>
                </h4>
                <p class="text-muted mb-4"><?php _e('mobile_qr_desc'); ?></p>
                <div class="qr-code-placeholder">
                    <div class="qr-code-box">
                        <i class="fas fa-qrcode"></i>
                        <small>QR Code</small>
                    </div>
                </div>
                <small class="d-block text-muted mt-3">
                    Atau kunjungi: <strong>zyntrading.com/mobile</strong>
                </small>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="fade-in">
            <h3 class="text-center mb-4">
                <i class="fas fa-question-circle me-2 text-primary"></i>
                <?php _e('mobile_faq'); ?>
            </h3>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion" id="mobileFaq">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#mfaq1">
                                    <i class="fas fa-shield-alt me-2 text-success"></i>
                                    <?php _e('mobile_faq_safe'); ?>
                                </button>
                            </h2>
                            <div id="mfaq1" class="accordion-collapse collapse show" data-bs-parent="#mobileFaq">
                                <div class="accordion-body">
                                    <?php _e('mobile_faq_safe_answer'); ?>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mfaq2">
                                    <i class="fas fa-sync-alt me-2 text-primary"></i>
                                    <?php _e('mobile_faq_update'); ?>
                                </button>
                            </h2>
                            <div id="mfaq2" class="accordion-collapse collapse" data-bs-parent="#mobileFaq">
                                <div class="accordion-body">
                                    <?php _e('mobile_faq_update_answer'); ?>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mfaq3">
                                    <i class="fab fa-apple me-2 text-info"></i>
                                    <?php _e('mobile_faq_appstore'); ?>
                                </button>
                            </h2>
                            <div id="mfaq3" class="accordion-collapse collapse" data-bs-parent="#mobileFaq">
                                <div class="accordion-body">
                                    <?php _e('mobile_faq_appstore_answer'); ?>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mfaq4">
                                    <i class="fas fa-mobile-alt me-2 text-warning"></i>
                                    <?php _e('mobile_faq_diff'); ?>
                                </button>
                            </h2>
                            <div id="mfaq4" class="accordion-collapse collapse" data-bs-parent="#mobileFaq">
                                <div class="accordion-body">
                                    <?php _e('mobile_faq_diff_answer'); ?>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mfaq5">
                                    <i class="fas fa-database me-2 text-danger"></i>
                                    <?php _e('mobile_faq_data'); ?>
                                </button>
                            </h2>
                            <div id="mfaq5" class="accordion-collapse collapse" data-bs-parent="#mobileFaq">
                                <div class="accordion-body">
                                    <?php _e('mobile_faq_data_answer'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="mt-5 pt-4 fade-in">
            <div class="cta-box text-center">
                <h3 class="mb-3"><?php _e('mobile_no_account'); ?></h3>
                <p class="text-muted mb-4"><?php _e('mobile_no_account_desc'); ?></p>
                <a href="register.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-rocket me-2"></i><?php _e('mobile_register_free'); ?>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- iOS Guide Modal -->
<div class="modal fade" id="iosGuideModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fab fa-apple me-2"></i><?php _e('mobile_ios_modal_title'); ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="ios-guide-steps">
                    <div class="ios-step">
                        <div class="ios-step-number">1</div>
                        <div class="ios-step-content">
                            <h6><?php _e('mobile_ios_step1'); ?></h6>
                            <p class="text-muted mb-0"><?php _e('mobile_ios_step1_desc'); ?></p>
                        </div>
                    </div>
                    <div class="ios-step">
                        <div class="ios-step-number">2</div>
                        <div class="ios-step-content">
                            <h6><?php _e('mobile_ios_step2'); ?></h6>
                            <p class="text-muted mb-0"><?php _e('mobile_ios_step2_desc'); ?></p>
                        </div>
                    </div>
                    <div class="ios-step">
                        <div class="ios-step-number">3</div>
                        <div class="ios-step-content">
                            <h6><?php _e('mobile_ios_step3'); ?></h6>
                            <p class="text-muted mb-0"><?php _e('mobile_ios_step3_desc'); ?></p>
                        </div>
                    </div>
                    <div class="ios-step">
                        <div class="ios-step-number">4</div>
                        <div class="ios-step-content">
                            <h6><?php _e('mobile_ios_step4'); ?></h6>
                            <p class="text-muted mb-0"><?php _e('mobile_ios_step4_desc'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                    <i class="fas fa-check me-1"></i><?php _e('mobile_understood'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Mobile Page Specific Styles */
.mobile-download-card {
    background: rgba(18, 18, 26, 0.8);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    transition: all var(--transition-normal);
}

.mobile-download-card:hover {
    transform: translateY(-5px);
    border-color: rgba(var(--primary-rgb), 0.3);
}

.mobile-icon {
    width: 100px;
    height: 100px;
    border-radius: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    margin: 0 auto;
}

.android-icon {
    background: linear-gradient(135deg, #3ddc84 0%, #00c853 100%);
    color: #fff;
}

.ios-icon {
    background: linear-gradient(135deg, #555 0%, #333 100%);
    color: #fff;
}

.app-specs .badge {
    font-weight: 500;
    padding: 0.5rem 0.75rem;
}

/* Feature Box */
.feature-box {
    background: rgba(18, 18, 26, 0.6);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    padding: 2rem 1.5rem;
    transition: all var(--transition-normal);
}

.feature-box:hover {
    border-color: var(--primary);
    transform: translateY(-3px);
}

.feature-icon {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.2) 0%, rgba(var(--secondary-rgb), 0.2) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: var(--primary);
    margin: 0 auto;
}

/* Installation Steps */
.installation-steps {
    background: rgba(18, 18, 26, 0.6);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    padding: 2rem;
}

.step-item {
    display: flex;
    align-items: flex-start;
    gap: 1.5rem;
    padding: 1.5rem 0;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

.step-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.step-item:first-child {
    padding-top: 0;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.1rem;
    color: #fff;
    flex-shrink: 0;
}

.step-content h5 {
    margin-bottom: 0.5rem;
    color: var(--text-primary);
}

/* QR Code Section */
.qr-section {
    background: rgba(18, 18, 26, 0.6);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    padding: 2rem;
}

.qr-code-box {
    width: 150px;
    height: 150px;
    background: #fff;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    color: var(--dark);
}

.qr-code-box i {
    font-size: 4rem;
    margin-bottom: 0.5rem;
}

/* iOS Guide Modal */
.ios-guide-steps {
    padding: 1rem 0;
}

.ios-step {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

.ios-step:last-child {
    border-bottom: none;
}

.ios-step-number {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: #fff;
    flex-shrink: 0;
}

.ios-step-content h6 {
    margin-bottom: 0.25rem;
    color: var(--text-primary);
}

/* CTA Box */
.cta-box {
    background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.1) 0%, rgba(var(--secondary-rgb), 0.1) 100%);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    padding: 3rem 2rem;
}

@media (max-width: 768px) {
    .mobile-icon {
        width: 80px;
        height: 80px;
        font-size: 2.5rem;
    }

    .step-item {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }

    .step-number {
        margin: 0 auto;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>
