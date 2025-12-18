<?php
$page_title = __('terms_title');
require_once 'includes/header.php';
?>

<link rel="stylesheet" href="assets/css/legal.css">

<section class="legal-page">
    <div class="container">
        <!-- Page Header -->
        <div class="legal-header legal-fade-in">
            <div class="legal-badge">
                <i class="fas fa-file-contract"></i>
                <?php _e('terms_badge'); ?>
            </div>
            <h1 class="legal-title"><?php _e('terms_heading'); ?></h1>
            <p class="legal-subtitle"><?php _e('terms_subtitle'); ?></p>
            <div class="legal-updated">
                <i class="fas fa-calendar-alt"></i>
                <?php _e('terms_updated'); ?>: <?php echo date('F d, Y'); ?>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Table of Contents -->
                <div class="legal-toc legal-fade-in">
                    <div class="legal-toc-title">
                        <i class="fas fa-list"></i>
                        Daftar Isi
                    </div>
                    <ul class="legal-toc-list">
                        <li><a href="#acceptance">Acceptance of Terms</a></li>
                        <li><a href="#description">Description of Service</a></li>
                        <li><a href="#eligibility">Eligibility</a></li>
                        <li><a href="#registration">Account Registration</a></li>
                        <li><a href="#subscription">Subscription & Payment</a></li>
                        <li><a href="#refund">Refund Policy</a></li>
                        <li><a href="#risk">Risk Disclaimer</a></li>
                        <li><a href="#prohibited">Prohibited Activities</a></li>
                        <li><a href="#intellectual">Intellectual Property</a></li>
                        <li><a href="#availability">Service Availability</a></li>
                        <li><a href="#liability">Limitation of Liability</a></li>
                        <li><a href="#termination">Termination</a></li>
                        <li><a href="#changes">Changes to Terms</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>

                <!-- Main Content -->
                <div class="legal-card legal-fade-in">
                    <!-- Section 1 -->
                    <div class="legal-section" id="acceptance">
                        <h3 class="legal-section-title">
                            <span class="section-number">1</span>
                            Acceptance of Terms
                        </h3>
                        <p>By accessing and using ZYN Trade System ("Service"), you accept and agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use the Service.</p>
                        <p>These terms constitute a legally binding agreement between you and ZYN Trade System. Your continued use of the Service signifies your acceptance of any modifications to these terms.</p>
                    </div>

                    <!-- Section 2 -->
                    <div class="legal-section" id="description">
                        <h3 class="legal-section-title">
                            <span class="section-number">2</span>
                            Description of Service
                        </h3>
                        <p>ZYN Trade System provides automated trading signals and robot functionality for use with the OlympTrade platform. The Service includes:</p>
                        <ul class="legal-list">
                            <li>Algorithmic trading strategies with multiple approach options</li>
                            <li>Automated trade execution based on market analysis</li>
                            <li>Comprehensive trading statistics and analytics dashboard</li>
                            <li>Risk management tools and configurations</li>
                            <li>Customer support via multiple channels</li>
                        </ul>
                    </div>

                    <!-- Section 3 -->
                    <div class="legal-section" id="eligibility">
                        <h3 class="legal-section-title">
                            <span class="section-number">3</span>
                            Eligibility
                        </h3>
                        <p>To use our Service, you must meet the following requirements:</p>
                        <ul class="legal-list numbered">
                            <li>Be at least 18 years of age or the legal age in your jurisdiction</li>
                            <li>Register on OlympTrade via our official affiliate link</li>
                            <li>Maintain a minimum deposit of $10 USD on your OlympTrade account</li>
                            <li>Provide accurate and truthful registration information</li>
                            <li>Comply with all applicable laws and regulations in your jurisdiction</li>
                            <li>Have the legal capacity to enter into binding agreements</li>
                        </ul>
                    </div>

                    <!-- Section 4 -->
                    <div class="legal-section" id="registration">
                        <h3 class="legal-section-title">
                            <span class="section-number">4</span>
                            Account Registration
                        </h3>
                        <p>When creating an account, you agree to:</p>
                        <ul class="legal-list">
                            <li>Provide accurate, current, and complete information</li>
                            <li>Maintain the security of your account credentials</li>
                            <li>Notify us immediately of any unauthorized access or security breach</li>
                            <li>Not share your account credentials with third parties</li>
                            <li>Use only one account per person (no multiple accounts)</li>
                            <li>Keep your contact information up to date</li>
                        </ul>
                        <div class="legal-alert info">
                            <div class="legal-alert-icon"><i class="fas fa-info-circle"></i></div>
                            <div class="legal-alert-content">
                                <div class="legal-alert-title">Account Verification</div>
                                <p class="legal-alert-text">All accounts require verification before full access is granted. Verification typically takes 1-24 hours.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Section 5 -->
                    <div class="legal-section" id="subscription">
                        <h3 class="legal-section-title">
                            <span class="section-number">5</span>
                            Subscription and Payment
                        </h3>
                        <p>Paid subscriptions are billed on a monthly basis. By subscribing to a paid plan, you authorize us to charge your selected payment method automatically.</p>
                        <p>Subscriptions will auto-renew at the end of each billing period unless cancelled before the renewal date. You may cancel your subscription at any time through your dashboard settings.</p>
                        <ul class="legal-list">
                            <li>Payment is due immediately upon subscription</li>
                            <li>All prices are displayed in USD unless otherwise specified</li>
                            <li>We accept various payment methods including QRIS, Stripe, PayPal, and cryptocurrency</li>
                            <li>Failed payments may result in service interruption</li>
                        </ul>
                    </div>

                    <!-- Section 6 -->
                    <div class="legal-section" id="refund">
                        <h3 class="legal-section-title">
                            <span class="section-number">6</span>
                            Refund Policy
                        </h3>
                        <p>Refunds are available within 7 days of purchase under specific conditions:</p>
                        <ul class="legal-list">
                            <li>No trades have been executed using the robot</li>
                            <li>The request is made within 7 calendar days of purchase</li>
                            <li>The account is in good standing without policy violations</li>
                        </ul>
                        <p>After 7 days or after any trade execution, no refunds will be provided. See our <a href="refund.php" class="text-cyan">Refund Policy</a> for complete details.</p>
                    </div>

                    <!-- Section 7 -->
                    <div class="legal-section" id="risk">
                        <h3 class="legal-section-title">
                            <span class="section-number">7</span>
                            Risk Disclaimer
                        </h3>
                        <div class="legal-alert warning">
                            <div class="legal-alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
                            <div class="legal-alert-content">
                                <div class="legal-alert-title">Important Risk Warning</div>
                                <p class="legal-alert-text">Trading binary options involves substantial risk of loss. Past performance is not indicative of future results. You should not trade with money you cannot afford to lose.</p>
                            </div>
                        </div>
                        <p>ZYN Trade System does not guarantee profits or specific returns. Market conditions are unpredictable and all trading involves risk. Please read our full <a href="disclaimer.php" class="text-cyan">Risk Disclaimer</a> before using the Service.</p>
                    </div>

                    <!-- Section 8 -->
                    <div class="legal-section" id="prohibited">
                        <h3 class="legal-section-title">
                            <span class="section-number">8</span>
                            Prohibited Activities
                        </h3>
                        <p>You agree not to engage in the following activities:</p>
                        <ul class="legal-list">
                            <li>Use the Service for any illegal or unauthorized purpose</li>
                            <li>Attempt to reverse engineer, decompile, or copy our algorithms</li>
                            <li>Share, resell, or redistribute signals without authorization</li>
                            <li>Create multiple accounts or use false identities</li>
                            <li>Use automated tools or bots to access the Service</li>
                            <li>Interfere with or disrupt the Service's operation</li>
                            <li>Violate any applicable laws or regulations</li>
                            <li>Attempt to gain unauthorized access to our systems</li>
                        </ul>
                    </div>

                    <!-- Section 9 -->
                    <div class="legal-section" id="intellectual">
                        <h3 class="legal-section-title">
                            <span class="section-number">9</span>
                            Intellectual Property
                        </h3>
                        <p>All content, algorithms, strategies, branding, and other intellectual property are the exclusive property of ZYN Trade System. This includes but is not limited to:</p>
                        <ul class="legal-list">
                            <li>Trading algorithms and signal generation methods</li>
                            <li>Website design, logos, and visual elements</li>
                            <li>Software, code, and technical implementations</li>
                            <li>Documentation and educational materials</li>
                        </ul>
                        <p>You may not copy, modify, distribute, sell, or lease any part of our Service without prior written permission.</p>
                    </div>

                    <!-- Section 10 -->
                    <div class="legal-section" id="availability">
                        <h3 class="legal-section-title">
                            <span class="section-number">10</span>
                            Service Availability
                        </h3>
                        <p>We strive for 99.9% uptime but cannot guarantee uninterrupted service. The Service may be temporarily unavailable due to:</p>
                        <ul class="legal-list">
                            <li>Scheduled maintenance and updates</li>
                            <li>Technical issues or server problems</li>
                            <li>Third-party service outages (e.g., OlympTrade)</li>
                            <li>Force majeure events</li>
                        </ul>
                        <p>We reserve the right to modify, suspend, or discontinue the Service at any time with or without notice.</p>
                    </div>

                    <!-- Section 11 -->
                    <div class="legal-section" id="liability">
                        <h3 class="legal-section-title">
                            <span class="section-number">11</span>
                            Limitation of Liability
                        </h3>
                        <p>ZYN Trade System shall not be liable for any direct, indirect, incidental, special, consequential, or exemplary damages arising from:</p>
                        <ul class="legal-list">
                            <li>Your use or inability to use the Service</li>
                            <li>Trading losses or financial decisions based on our signals</li>
                            <li>Unauthorized access to your account</li>
                            <li>Technical failures or service interruptions</li>
                            <li>Actions of third-party platforms</li>
                        </ul>
                        <div class="legal-alert danger">
                            <div class="legal-alert-icon"><i class="fas fa-shield-alt"></i></div>
                            <div class="legal-alert-content">
                                <div class="legal-alert-title">Maximum Liability</div>
                                <p class="legal-alert-text">Our total liability shall not exceed the amount you paid for the Service in the 12 months preceding the claim.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Section 12 -->
                    <div class="legal-section" id="termination">
                        <h3 class="legal-section-title">
                            <span class="section-number">12</span>
                            Termination
                        </h3>
                        <p>We may terminate or suspend your account immediately, without prior notice, for any reason including:</p>
                        <ul class="legal-list">
                            <li>Violation of these Terms of Service</li>
                            <li>Fraudulent or suspicious activity</li>
                            <li>Non-payment of subscription fees</li>
                            <li>Request from law enforcement</li>
                        </ul>
                        <p>You may terminate your account at any time by contacting support. Upon termination, your right to use the Service will cease immediately.</p>
                    </div>

                    <!-- Section 13 -->
                    <div class="legal-section" id="changes">
                        <h3 class="legal-section-title">
                            <span class="section-number">13</span>
                            Changes to Terms
                        </h3>
                        <p>We reserve the right to update or modify these Terms of Service at any time. Changes will be effective immediately upon posting to the website.</p>
                        <p>We will notify users of material changes via email or dashboard notification. Your continued use of the Service after any changes constitutes acceptance of the new terms.</p>
                    </div>

                    <!-- Section 14 -->
                    <div class="legal-section" id="contact">
                        <h3 class="legal-section-title">
                            <span class="section-number">14</span>
                            Contact Information
                        </h3>
                        <p>For questions or concerns about these Terms of Service, please contact us:</p>
                        <ul class="legal-list">
                            <li><strong>Telegram:</strong> <a href="<?php echo TELEGRAM_SUPPORT; ?>" class="text-cyan"><?php echo TELEGRAM_SUPPORT; ?></a></li>
                            <li><strong>Email:</strong> <a href="mailto:<?php echo SITE_EMAIL; ?>" class="text-cyan"><?php echo SITE_EMAIL; ?></a></li>
                        </ul>
                    </div>

                    <!-- CTA Section -->
                    <div class="legal-cta">
                        <h4 class="legal-cta-title">Ready to Start Trading?</h4>
                        <p class="legal-cta-text">By creating an account, you agree to these Terms of Service</p>
                        <a href="register.php" class="legal-btn">
                            <i class="fas fa-check-circle"></i>
                            I Accept - Create Account
                        </a>
                    </div>

                    <!-- Navigation -->
                    <div class="legal-nav">
                        <a href="privacy.php">Privacy Policy</a>
                        <a href="disclaimer.php">Risk Disclaimer</a>
                        <a href="refund.php">Refund Policy</a>
                        <a href="faq.php">FAQ</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.text-cyan { color: #00d4ff !important; }
.text-cyan:hover { color: #7c3aed !important; }
</style>

<?php require_once 'includes/footer.php'; ?>
