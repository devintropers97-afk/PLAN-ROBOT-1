<?php
$page_title = 'Terms of Service';
require_once 'includes/header.php';
?>

<section class="section" style="padding-top: calc(var(--navbar-height) + 3rem);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="mb-4">Terms of Service</h1>
                <p class="text-muted mb-4">Last updated: <?php echo date('F d, Y'); ?></p>

                <div class="card mb-4">
                    <div class="card-body">
                        <h4>1. Acceptance of Terms</h4>
                        <p>By accessing and using ZYN Trade System ("Service"), you accept and agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use the Service.</p>

                        <h4 class="mt-4">2. Description of Service</h4>
                        <p>ZYN Trade System provides automated trading signals and robot functionality for use with the OlympTrade platform. The Service includes:</p>
                        <ul>
                            <li>Algorithmic trading strategies</li>
                            <li>Automated trade execution</li>
                            <li>Trading statistics and analytics</li>
                            <li>Customer support</li>
                        </ul>

                        <h4 class="mt-4">3. Eligibility</h4>
                        <p>To use our Service, you must:</p>
                        <ul>
                            <li>Be at least 18 years of age</li>
                            <li>Register on OlympTrade via our official affiliate link</li>
                            <li>Maintain a minimum deposit of $10 USD on OlympTrade</li>
                            <li>Provide accurate registration information</li>
                            <li>Comply with all applicable laws in your jurisdiction</li>
                        </ul>

                        <h4 class="mt-4">4. Account Registration</h4>
                        <p>When creating an account, you agree to:</p>
                        <ul>
                            <li>Provide accurate and complete information</li>
                            <li>Maintain the security of your account credentials</li>
                            <li>Notify us immediately of any unauthorized access</li>
                            <li>Not share your account with others</li>
                            <li>Use only one account per person</li>
                        </ul>

                        <h4 class="mt-4">5. Subscription and Payment</h4>
                        <p>Paid subscriptions are billed monthly. By subscribing, you authorize us to charge your selected payment method. Subscriptions auto-renew unless cancelled before the renewal date.</p>

                        <h4 class="mt-4">6. Refund Policy</h4>
                        <p>Refunds are available within 7 days of purchase if no trades have been executed. After 7 days or after any trade execution, no refunds will be provided. See our <a href="refund.php">Refund Policy</a> for details.</p>

                        <h4 class="mt-4">7. Risk Disclaimer</h4>
                        <p><strong>IMPORTANT:</strong> Trading binary options involves substantial risk of loss. Past performance is not indicative of future results. You should not trade with money you cannot afford to lose. ZYN Trade System does not guarantee profits. See our full <a href="disclaimer.php">Risk Disclaimer</a>.</p>

                        <h4 class="mt-4">8. Prohibited Activities</h4>
                        <p>You agree not to:</p>
                        <ul>
                            <li>Use the Service for any illegal purpose</li>
                            <li>Attempt to reverse engineer or copy our algorithms</li>
                            <li>Share, resell, or redistribute signals without authorization</li>
                            <li>Create multiple accounts</li>
                            <li>Use automated tools to access the Service</li>
                            <li>Interfere with the Service's operation</li>
                        </ul>

                        <h4 class="mt-4">9. Intellectual Property</h4>
                        <p>All content, algorithms, and branding are the exclusive property of ZYN Trade System. You may not copy, modify, or distribute any part of our Service without written permission.</p>

                        <h4 class="mt-4">10. Service Availability</h4>
                        <p>We strive for 99.9% uptime but do not guarantee uninterrupted service. We reserve the right to modify, suspend, or discontinue the Service at any time.</p>

                        <h4 class="mt-4">11. Limitation of Liability</h4>
                        <p>ZYN Trade System shall not be liable for any direct, indirect, incidental, or consequential damages arising from your use of the Service, including trading losses.</p>

                        <h4 class="mt-4">12. Termination</h4>
                        <p>We may terminate or suspend your account at any time for violation of these terms. You may also terminate your account by contacting support.</p>

                        <h4 class="mt-4">13. Changes to Terms</h4>
                        <p>We may update these terms at any time. Continued use of the Service after changes constitutes acceptance of the new terms.</p>

                        <h4 class="mt-4">14. Contact</h4>
                        <p>For questions about these terms, contact us:</p>
                        <ul>
                            <li>Telegram: <a href="<?php echo TELEGRAM_SUPPORT; ?>"><?php echo TELEGRAM_SUPPORT; ?></a></li>
                            <li>Email: <a href="mailto:<?php echo SITE_EMAIL; ?>"><?php echo SITE_EMAIL; ?></a></li>
                        </ul>
                    </div>
                </div>

                <div class="text-center">
                    <a href="register.php" class="btn btn-primary">I Accept - Create Account</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
