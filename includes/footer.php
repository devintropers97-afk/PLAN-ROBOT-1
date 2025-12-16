    </main>
    <!-- End Main Content -->

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <!-- Brand Column -->
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <div class="footer-brand">
                        <!-- Footer Logo SVG -->
                        <div class="footer-logo-icon">
                            <svg viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                    <linearGradient id="footerLogoGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" style="stop-color:#00d4ff"/>
                                        <stop offset="100%" style="stop-color:#7c3aed"/>
                                    </linearGradient>
                                    <filter id="footerGlow" x="-50%" y="-50%" width="200%" height="200%">
                                        <feGaussianBlur stdDeviation="1.5" result="coloredBlur"/>
                                        <feMerge>
                                            <feMergeNode in="coloredBlur"/>
                                            <feMergeNode in="SourceGraphic"/>
                                        </feMerge>
                                    </filter>
                                </defs>
                                <polygon points="25,2 45,14 45,36 25,48 5,36 5,14" fill="none" stroke="url(#footerLogoGradient)" stroke-width="1.5" filter="url(#footerGlow)"/>
                                <text x="25" y="32" font-family="Orbitron, sans-serif" font-size="18" fill="url(#footerLogoGradient)" text-anchor="middle" font-weight="900">Z</text>
                                <circle cx="25" cy="5" r="2" fill="#00d4ff" opacity="0.8"/>
                                <circle cx="25" cy="45" r="2" fill="#00d4ff" opacity="0.8"/>
                            </svg>
                        </div>
                        <div class="footer-brand-text">
                            <span class="brand-logo">ZYN</span>
                            <span class="brand-text">Trade System</span>
                        </div>
                    </div>
                    <p class="footer-tagline"><?php echo SITE_TAGLINE; ?></p>
                    <p class="footer-desc">
                        <strong>Z</strong>ero Emotion Trading | <strong>Y</strong>ield-Oriented Logic | <strong>N</strong>ext-Level Automation
                    </p>
                    <div class="footer-version">
                        <small>Version <?php echo defined('SITE_VERSION') ? SITE_VERSION : '3.0.0'; ?></small>
                    </div>
                    <div class="social-links">
                        <a href="<?php echo TELEGRAM_CHANNEL; ?>" target="_blank" class="social-link" title="Telegram Channel">
                            <i class="fab fa-telegram"></i>
                        </a>
                        <a href="https://t.me/<?php echo ltrim(TELEGRAM_SUPPORT, '@'); ?>" target="_blank" class="social-link" title="Telegram Support">
                            <i class="fas fa-headset"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <h5 class="footer-title">Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="index.php"><i class="fas fa-chevron-right"></i> Home</a></li>
                        <li><a href="strategies.php"><i class="fas fa-chevron-right"></i> Strategies</a></li>
                        <li><a href="pricing.php"><i class="fas fa-chevron-right"></i> Pricing</a></li>
                        <li><a href="faq.php"><i class="fas fa-chevron-right"></i> FAQ</a></li>
                        <li><a href="calculator.php"><i class="fas fa-chevron-right"></i> Calculator</a></li>
                    </ul>
                </div>

                <!-- Account -->
                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <h5 class="footer-title">Account</h5>
                    <ul class="footer-links">
                        <?php if (isLoggedIn()): ?>
                            <li><a href="dashboard.php"><i class="fas fa-chevron-right"></i> Dashboard</a></li>
                            <li><a href="profile.php"><i class="fas fa-chevron-right"></i> Profile</a></li>
                            <li><a href="settings.php"><i class="fas fa-chevron-right"></i> Settings</a></li>
                            <li><a href="statistics.php"><i class="fas fa-chevron-right"></i> Statistics</a></li>
                        <?php else: ?>
                            <li><a href="login.php"><i class="fas fa-chevron-right"></i> Login</a></li>
                            <li><a href="register.php"><i class="fas fa-chevron-right"></i> Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Legal -->
                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <h5 class="footer-title">Legal</h5>
                    <ul class="footer-links">
                        <li><a href="terms.php"><i class="fas fa-chevron-right"></i> Terms of Service</a></li>
                        <li><a href="privacy.php"><i class="fas fa-chevron-right"></i> Privacy Policy</a></li>
                        <li><a href="disclaimer.php"><i class="fas fa-chevron-right"></i> Risk Disclaimer</a></li>
                        <li><a href="refund.php"><i class="fas fa-chevron-right"></i> Refund Policy</a></li>
                    </ul>
                </div>

                <!-- Support -->
                <div class="col-lg-2 col-md-4">
                    <h5 class="footer-title">Support</h5>
                    <ul class="footer-links">
                        <li>
                            <a href="<?php echo TELEGRAM_CHANNEL; ?>" target="_blank">
                                <i class="fab fa-telegram"></i> Channel
                            </a>
                        </li>
                        <li>
                            <a href="https://t.me/<?php echo ltrim(TELEGRAM_SUPPORT, '@'); ?>" target="_blank">
                                <i class="fas fa-user-headset"></i> <?php echo TELEGRAM_SUPPORT; ?>
                            </a>
                        </li>
                        <li>
                            <a href="mailto:<?php echo SITE_EMAIL; ?>">
                                <i class="fas fa-envelope"></i> Email Us
                            </a>
                        </li>
                    </ul>
                    <div class="footer-cta mt-3">
                        <a href="<?php echo OLYMPTRADE_AFFILIATE_LINK; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-external-link-alt me-1"></i> Open OlympTrade
                        </a>
                    </div>
                </div>
            </div>

            <hr class="footer-divider">

            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="copyright">
                        &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="disclaimer-short">
                        <i class="fas fa-exclamation-triangle text-warning me-1"></i>
                        Trading involves risk. Past performance does not guarantee future results.
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Risk Disclaimer Modal -->
    <div class="modal fade" id="riskModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>Risk Disclaimer
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Trading mengandung risiko. Tidak ada sistem yang menjamin profit. Hasil trading bergantung pada kondisi market dan disiplin penggunaan sistem.</p>
                    <p class="mb-0">Trading involves risk. No system guarantees profit. Trading results depend on market conditions and system discipline.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                        <i class="fas fa-check me-1"></i>I Understand
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
    <?php if (strpos($current_page, 'dashboard') !== false): ?>
    <script src="assets/js/dashboard.js"></script>
    <?php endif; ?>

    <!-- Page specific scripts -->
    <?php if (isset($page_scripts)): ?>
    <?php echo $page_scripts; ?>
    <?php endif; ?>

    <!-- Preloader hide script -->
    <script>
        (function() {
            const hidePreloader = function() {
                const preloader = document.getElementById('preloader');
                if (preloader && preloader.style.display !== 'none') {
                    preloader.classList.add('fade-out');
                    setTimeout(() => {
                        preloader.style.display = 'none';
                    }, 500);
                }
            };

            // Hide on window load
            window.addEventListener('load', hidePreloader);

            // Fallback: hide after 5 seconds regardless
            setTimeout(hidePreloader, 5000);

            // Also hide on DOMContentLoaded as backup
            if (document.readyState === 'complete') {
                hidePreloader();
            } else {
                document.addEventListener('DOMContentLoaded', function() {
                    setTimeout(hidePreloader, 1000);
                });
            }
        })();
    </script>
</body>
</html>
