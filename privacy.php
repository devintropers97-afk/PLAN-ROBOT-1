<?php
$page_title = 'Privacy Policy';
require_once 'includes/header.php';
?>

<section class="section" style="padding-top: calc(var(--navbar-height) + 3rem);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="mb-4">Privacy Policy</h1>
                <p class="text-muted mb-4">Last updated: <?php echo date('F d, Y'); ?></p>

                <div class="card mb-4">
                    <div class="card-body">
                        <h4>1. Introduction</h4>
                        <p>ZYN Trade System ("we", "our", "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, and protect your personal information.</p>

                        <h4 class="mt-4">2. Information We Collect</h4>
                        <h5>2.1 Personal Information</h5>
                        <ul>
                            <li>Full name</li>
                            <li>Email address</li>
                            <li>Phone number (optional)</li>
                            <li>Country of residence</li>
                            <li>OlympTrade account ID</li>
                        </ul>

                        <h5 class="mt-3">2.2 Usage Data</h5>
                        <ul>
                            <li>Trading history and statistics</li>
                            <li>Robot settings and preferences</li>
                            <li>Login history and IP addresses</li>
                            <li>Device information</li>
                        </ul>

                        <h4 class="mt-4">3. How We Use Your Information</h4>
                        <p>We use your information to:</p>
                        <ul>
                            <li>Provide and maintain the Service</li>
                            <li>Verify your identity and OlympTrade account</li>
                            <li>Process payments and subscriptions</li>
                            <li>Send service-related notifications</li>
                            <li>Provide customer support</li>
                            <li>Improve our Service</li>
                            <li>Detect and prevent fraud</li>
                        </ul>

                        <h4 class="mt-4">4. Data Protection</h4>
                        <p>We implement industry-standard security measures including:</p>
                        <ul>
                            <li>SSL/TLS encryption for all data transmission</li>
                            <li>Secure password hashing (bcrypt)</li>
                            <li>Regular security audits</li>
                            <li>Limited employee access to personal data</li>
                        </ul>

                        <h4 class="mt-4">5. Data Sharing</h4>
                        <p>We do NOT sell your personal information. We may share data with:</p>
                        <ul>
                            <li>Payment processors (Stripe, PayPal) for transactions</li>
                            <li>Law enforcement when legally required</li>
                            <li>Service providers who assist our operations</li>
                        </ul>

                        <h4 class="mt-4">6. Cookies</h4>
                        <p>We use essential cookies for:</p>
                        <ul>
                            <li>Session management and authentication</li>
                            <li>Security (CSRF protection)</li>
                            <li>Remembering user preferences</li>
                        </ul>

                        <h4 class="mt-4">7. Data Retention</h4>
                        <p>We retain your data for:</p>
                        <ul>
                            <li>Account information: Until account deletion</li>
                            <li>Trading history: As per your package (7-365 days)</li>
                            <li>Payment records: 7 years (legal requirement)</li>
                        </ul>

                        <h4 class="mt-4">8. Your Rights</h4>
                        <p>You have the right to:</p>
                        <ul>
                            <li>Access your personal data</li>
                            <li>Correct inaccurate information</li>
                            <li>Request data deletion</li>
                            <li>Export your data</li>
                            <li>Withdraw consent</li>
                        </ul>

                        <h4 class="mt-4">9. International Users</h4>
                        <p>Your data may be processed in countries outside your residence. By using our Service, you consent to such data transfer.</p>

                        <h4 class="mt-4">10. Children's Privacy</h4>
                        <p>Our Service is not intended for users under 18. We do not knowingly collect information from minors.</p>

                        <h4 class="mt-4">11. Changes to This Policy</h4>
                        <p>We may update this policy at any time. Material changes will be notified via email or dashboard notification.</p>

                        <h4 class="mt-4">12. Contact Us</h4>
                        <p>For privacy-related inquiries:</p>
                        <ul>
                            <li>Telegram: <a href="<?php echo TELEGRAM_SUPPORT; ?>"><?php echo TELEGRAM_SUPPORT; ?></a></li>
                            <li>Email: <a href="mailto:<?php echo SITE_EMAIL; ?>"><?php echo SITE_EMAIL; ?></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
