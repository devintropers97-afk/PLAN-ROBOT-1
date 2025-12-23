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
                        <li><a href="mobile.php"><i class="fas fa-mobile-alt"></i> Mobile App</a></li>
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
                        <a href="<?php echo getLocalizedAffiliateLink(); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
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

    <!-- Floating Telegram/Support Button -->
    <div class="floating-whatsapp">
        <a href="https://t.me/<?php echo ltrim(TELEGRAM_SUPPORT, '@'); ?>" target="_blank" class="whatsapp-btn" title="Chat with Support">
            <i class="fab fa-telegram"></i>
        </a>
        <div class="whatsapp-tooltip">Chat with Support</div>
    </div>

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

    <!-- GSAP Animation Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>

    <!-- Three.js for 3D Effects -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>

    <!-- Premium Edition JS -->
    <script src="assets/js/premium.js"></script>
    <?php if (strpos($current_page, 'dashboard') !== false): ?>
    <script src="assets/js/dashboard.js"></script>
    <?php endif; ?>

    <!-- Page specific scripts -->
    <?php if (isset($page_scripts)): ?>
    <?php echo $page_scripts; ?>
    <?php endif; ?>

    <!-- Premium Preloader is handled by premium.js -->

    <!-- Anti-Inspect/DevTools Protection -->
    <script>
    (function() {
        'use strict';

        // Disable right-click context menu
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            return false;
        });

        // Disable keyboard shortcuts for DevTools
        document.addEventListener('keydown', function(e) {
            // F12
            if (e.key === 'F12' || e.keyCode === 123) {
                e.preventDefault();
                return false;
            }

            // Ctrl+Shift+I (DevTools)
            if (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'i' || e.keyCode === 73)) {
                e.preventDefault();
                return false;
            }

            // Ctrl+Shift+J (Console)
            if (e.ctrlKey && e.shiftKey && (e.key === 'J' || e.key === 'j' || e.keyCode === 74)) {
                e.preventDefault();
                return false;
            }

            // Ctrl+Shift+C (Inspect Element)
            if (e.ctrlKey && e.shiftKey && (e.key === 'C' || e.key === 'c' || e.keyCode === 67)) {
                e.preventDefault();
                return false;
            }

            // Ctrl+U (View Source)
            if (e.ctrlKey && (e.key === 'U' || e.key === 'u' || e.keyCode === 85)) {
                e.preventDefault();
                return false;
            }

            // Ctrl+S (Save Page)
            if (e.ctrlKey && (e.key === 'S' || e.key === 's' || e.keyCode === 83)) {
                e.preventDefault();
                return false;
            }
        });

        // Disable text selection on certain elements
        document.addEventListener('selectstart', function(e) {
            const target = e.target;
            if (target.tagName !== 'INPUT' && target.tagName !== 'TEXTAREA' && !target.classList.contains('selectable')) {
                e.preventDefault();
                return false;
            }
        });

        // Detect DevTools opening (basic detection)
        let devToolsOpen = false;
        const threshold = 160;

        const detectDevTools = function() {
            const widthThreshold = window.outerWidth - window.innerWidth > threshold;
            const heightThreshold = window.outerHeight - window.innerHeight > threshold;

            if (widthThreshold || heightThreshold) {
                if (!devToolsOpen) {
                    devToolsOpen = true;
                    // Optional: redirect or show warning
                    console.clear();
                    console.log('%cStop!', 'color: red; font-size: 50px; font-weight: bold;');
                    console.log('%cThis browser feature is for developers.', 'color: gray; font-size: 16px;');
                }
            } else {
                devToolsOpen = false;
            }
        };

        setInterval(detectDevTools, 1000);

        // Clear console periodically
        setInterval(function() {
            console.clear();
        }, 5000);

        // Disable drag and drop
        document.addEventListener('dragstart', function(e) {
            e.preventDefault();
            return false;
        });

        // Disable copy on certain elements
        document.addEventListener('copy', function(e) {
            const selection = window.getSelection().toString();
            if (selection.length > 100) {
                e.preventDefault();
                return false;
            }
        });
    })();
    </script>
</body>
</html>
