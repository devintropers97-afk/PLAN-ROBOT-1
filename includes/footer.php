    </main>
    <!-- End Main Content -->

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <div class="footer-brand">
                        <span class="brand-logo">ZYN</span>
                        <span class="brand-text">Trade System</span>
                    </div>
                    <p class="footer-tagline"><?php echo SITE_TAGLINE; ?></p>
                    <p class="footer-desc">
                        Zero Emotion Trading | Yield-Oriented Logic | Next-Level Automation
                    </p>
                    <div class="social-links">
                        <a href="<?php echo TELEGRAM_SUPPORT; ?>" target="_blank" class="social-link">
                            <i class="fab fa-telegram"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <h5 class="footer-title">Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="strategies.php">Strategies</a></li>
                        <li><a href="pricing.php">Pricing</a></li>
                        <li><a href="faq.php">FAQ</a></li>
                    </ul>
                </div>

                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <h5 class="footer-title">Account</h5>
                    <ul class="footer-links">
                        <?php if (isLoggedIn()): ?>
                            <li><a href="dashboard.php">Dashboard</a></li>
                            <li><a href="profile.php">Profile</a></li>
                            <li><a href="settings.php">Settings</a></li>
                        <?php else: ?>
                            <li><a href="login.php">Login</a></li>
                            <li><a href="register.php">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <h5 class="footer-title">Legal</h5>
                    <ul class="footer-links">
                        <li><a href="terms.php">Terms of Service</a></li>
                        <li><a href="privacy.php">Privacy Policy</a></li>
                        <li><a href="disclaimer.php">Risk Disclaimer</a></li>
                        <li><a href="refund.php">Refund Policy</a></li>
                    </ul>
                </div>

                <div class="col-lg-2 col-md-4">
                    <h5 class="footer-title">Support</h5>
                    <ul class="footer-links">
                        <li><a href="<?php echo TELEGRAM_SUPPORT; ?>" target="_blank">
                            <i class="fab fa-telegram"></i> Telegram
                        </a></li>
                        <li><a href="mailto:<?php echo SITE_EMAIL; ?>">
                            <i class="fas fa-envelope"></i> Email
                        </a></li>
                    </ul>
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
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle text-warning"></i> Risk Disclaimer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Trading mengandung risiko. Tidak ada sistem yang menjamin profit. Hasil trading bergantung pada kondisi market dan disiplin penggunaan sistem.</p>
                    <p>Trading involves risk. No system guarantees profit. Trading results depend on market conditions and system discipline.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Understand</button>
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
</body>
</html>
