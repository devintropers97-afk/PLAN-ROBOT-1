<?php
$page_title = __('privacy_title');
require_once 'includes/header.php';
?>

<link rel="stylesheet" href="assets/css/legal.css">

<section class="legal-page">
    <div class="container">
        <!-- Page Header -->
        <div class="legal-header legal-fade-in">
            <div class="legal-badge">
                <i class="fas fa-shield-alt"></i>
                <?php _e('privacy_badge'); ?>
            </div>
            <h1 class="legal-title"><?php _e('privacy_heading'); ?></h1>
            <p class="legal-subtitle"><?php _e('privacy_subtitle'); ?></p>
            <div class="legal-updated">
                <i class="fas fa-calendar-alt"></i>
                <?php _e('privacy_updated'); ?>: <?php echo date('F d, Y'); ?>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Table of Contents -->
                <div class="legal-toc legal-fade-in">
                    <div class="legal-toc-title">
                        <i class="fas fa-list"></i>
                        <?php _e('legal_toc'); ?>
                    </div>
                    <ul class="legal-toc-list">
                        <li><a href="#introduction">Introduction</a></li>
                        <li><a href="#collection">Information We Collect</a></li>
                        <li><a href="#usage">How We Use Information</a></li>
                        <li><a href="#protection">Data Protection</a></li>
                        <li><a href="#sharing">Data Sharing</a></li>
                        <li><a href="#cookies">Cookies</a></li>
                        <li><a href="#retention">Data Retention</a></li>
                        <li><a href="#rights">Your Rights</a></li>
                        <li><a href="#international">International Users</a></li>
                        <li><a href="#children">Children's Privacy</a></li>
                        <li><a href="#changes">Policy Changes</a></li>
                        <li><a href="#contact">Contact Us</a></li>
                    </ul>
                </div>

                <!-- Main Content -->
                <div class="legal-card legal-fade-in">
                    <!-- Section 1 -->
                    <div class="legal-section" id="introduction">
                        <h3 class="legal-section-title">
                            <span class="section-number">1</span>
                            Introduction
                        </h3>
                        <p>ZYN Trade System ("we", "our", "us") is committed to protecting your privacy and ensuring the security of your personal information.</p>
                        <p>This Privacy Policy explains how we collect, use, store, and protect your information when you use our trading platform and services. By using our Service, you consent to the practices described in this policy.</p>
                        <div class="legal-alert info">
                            <div class="legal-alert-icon"><i class="fas fa-lock"></i></div>
                            <div class="legal-alert-content">
                                <div class="legal-alert-title">Your Privacy Matters</div>
                                <p class="legal-alert-text">We use industry-standard encryption and security measures to protect all personal data.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2 -->
                    <div class="legal-section" id="collection">
                        <h3 class="legal-section-title">
                            <span class="section-number">2</span>
                            Information We Collect
                        </h3>

                        <h5 style="color: #00d4ff; margin-top: 1.5rem;">2.1 Personal Information</h5>
                        <p>When you register and use our Service, we collect:</p>
                        <ul class="legal-list">
                            <li>Full name as provided during registration</li>
                            <li>Email address for account verification and communications</li>
                            <li>Phone number (optional) for additional security</li>
                            <li>Country of residence for compliance purposes</li>
                            <li>OlympTrade account ID for service integration</li>
                        </ul>

                        <h5 style="color: #00d4ff; margin-top: 1.5rem;">2.2 Usage Data</h5>
                        <p>We automatically collect certain information when you use our Service:</p>
                        <ul class="legal-list">
                            <li>Trading history, statistics, and robot performance data</li>
                            <li>Robot configuration settings and preferences</li>
                            <li>Login timestamps and session information</li>
                            <li>IP addresses and approximate location</li>
                            <li>Device information (browser type, operating system)</li>
                        </ul>

                        <h5 style="color: #00d4ff; margin-top: 1.5rem;">2.3 Payment Information</h5>
                        <p>For paid subscriptions, we may collect:</p>
                        <ul class="legal-list">
                            <li>Payment method details (processed by third-party providers)</li>
                            <li>Transaction history and billing records</li>
                            <li>Subscription status and renewal information</li>
                        </ul>
                    </div>

                    <!-- Section 3 -->
                    <div class="legal-section" id="usage">
                        <h3 class="legal-section-title">
                            <span class="section-number">3</span>
                            How We Use Your Information
                        </h3>
                        <p>We use your information for the following purposes:</p>
                        <ul class="legal-list numbered">
                            <li>Provide, maintain, and improve the Service</li>
                            <li>Verify your identity and OlympTrade account linkage</li>
                            <li>Process payments and manage subscriptions</li>
                            <li>Send service-related notifications and updates</li>
                            <li>Provide customer support and respond to inquiries</li>
                            <li>Analyze usage patterns to enhance user experience</li>
                            <li>Detect, prevent, and address fraud or security issues</li>
                            <li>Comply with legal obligations and regulations</li>
                        </ul>
                    </div>

                    <!-- Section 4 -->
                    <div class="legal-section" id="protection">
                        <h3 class="legal-section-title">
                            <span class="section-number">4</span>
                            Data Protection
                        </h3>
                        <p>We implement industry-standard security measures to protect your data:</p>
                        <ul class="legal-list">
                            <li><strong>Encryption:</strong> SSL/TLS encryption for all data transmission</li>
                            <li><strong>Password Security:</strong> Secure password hashing using bcrypt algorithm</li>
                            <li><strong>Access Control:</strong> Limited employee access to personal data on need-to-know basis</li>
                            <li><strong>Security Audits:</strong> Regular security assessments and vulnerability testing</li>
                            <li><strong>Secure Infrastructure:</strong> Enterprise-grade server security and firewalls</li>
                        </ul>
                        <div class="legal-alert success">
                            <div class="legal-alert-icon"><i class="fas fa-check-circle"></i></div>
                            <div class="legal-alert-content">
                                <div class="legal-alert-title">Security Standards</div>
                                <p class="legal-alert-text">We follow OWASP security guidelines and best practices for web application security.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Section 5 -->
                    <div class="legal-section" id="sharing">
                        <h3 class="legal-section-title">
                            <span class="section-number">5</span>
                            Data Sharing
                        </h3>
                        <div class="legal-alert warning">
                            <div class="legal-alert-icon"><i class="fas fa-hand-paper"></i></div>
                            <div class="legal-alert-content">
                                <div class="legal-alert-title">We Do NOT Sell Your Data</div>
                                <p class="legal-alert-text">Your personal information is never sold to third parties for marketing purposes.</p>
                            </div>
                        </div>
                        <p>We may share data only in the following circumstances:</p>
                        <ul class="legal-list">
                            <li><strong>Payment Processors:</strong> Stripe, PayPal, and other payment providers to process transactions</li>
                            <li><strong>Legal Requirements:</strong> Law enforcement or government agencies when legally required</li>
                            <li><strong>Service Providers:</strong> Trusted partners who assist our operations under strict confidentiality agreements</li>
                            <li><strong>Business Transfers:</strong> In connection with merger, acquisition, or sale of assets</li>
                        </ul>
                    </div>

                    <!-- Section 6 -->
                    <div class="legal-section" id="cookies">
                        <h3 class="legal-section-title">
                            <span class="section-number">6</span>
                            Cookies
                        </h3>
                        <p>We use essential cookies for the following purposes:</p>
                        <ul class="legal-list">
                            <li><strong>Session Management:</strong> To keep you logged in and maintain your session</li>
                            <li><strong>Security:</strong> CSRF protection and fraud prevention tokens</li>
                            <li><strong>Preferences:</strong> Remembering your language and display preferences</li>
                            <li><strong>Performance:</strong> Understanding how you interact with our Service</li>
                        </ul>
                        <p>You can control cookie settings through your browser preferences. However, disabling cookies may affect Service functionality.</p>
                    </div>

                    <!-- Section 7 -->
                    <div class="legal-section" id="retention">
                        <h3 class="legal-section-title">
                            <span class="section-number">7</span>
                            Data Retention
                        </h3>
                        <p>We retain different types of data for different periods:</p>
                        <ul class="legal-list">
                            <li><strong>Account Information:</strong> Retained until you request account deletion</li>
                            <li><strong>Trading History:</strong> Based on your subscription package (7-365 days)</li>
                            <li><strong>Payment Records:</strong> 7 years as required by financial regulations</li>
                            <li><strong>Server Logs:</strong> 90 days for security and debugging purposes</li>
                            <li><strong>Support Communications:</strong> 2 years from last interaction</li>
                        </ul>
                    </div>

                    <!-- Section 8 -->
                    <div class="legal-section" id="rights">
                        <h3 class="legal-section-title">
                            <span class="section-number">8</span>
                            Your Rights
                        </h3>
                        <p>You have the following rights regarding your personal data:</p>
                        <ul class="legal-list numbered">
                            <li><strong>Access:</strong> Request a copy of your personal data we hold</li>
                            <li><strong>Correction:</strong> Request correction of inaccurate or incomplete information</li>
                            <li><strong>Deletion:</strong> Request deletion of your personal data (subject to legal requirements)</li>
                            <li><strong>Export:</strong> Receive your data in a portable, machine-readable format</li>
                            <li><strong>Withdraw Consent:</strong> Opt out of optional data processing activities</li>
                            <li><strong>Restrict Processing:</strong> Limit how we use your data in certain circumstances</li>
                        </ul>
                        <p>To exercise these rights, contact us via the information provided below.</p>
                    </div>

                    <!-- Section 9 -->
                    <div class="legal-section" id="international">
                        <h3 class="legal-section-title">
                            <span class="section-number">9</span>
                            International Users
                        </h3>
                        <p>Our Service is operated globally. Your data may be processed in countries outside your residence, which may have different data protection laws.</p>
                        <p>By using our Service, you consent to the transfer of your information to these countries. We ensure appropriate safeguards are in place to protect your data during international transfers.</p>
                    </div>

                    <!-- Section 10 -->
                    <div class="legal-section" id="children">
                        <h3 class="legal-section-title">
                            <span class="section-number">10</span>
                            Children's Privacy
                        </h3>
                        <p>Our Service is not intended for users under 18 years of age. We do not knowingly collect personal information from minors.</p>
                        <p>If we discover that we have collected information from a user under 18, we will promptly delete such data. If you believe a minor has provided us with personal information, please contact us immediately.</p>
                    </div>

                    <!-- Section 11 -->
                    <div class="legal-section" id="changes">
                        <h3 class="legal-section-title">
                            <span class="section-number">11</span>
                            Changes to This Policy
                        </h3>
                        <p>We may update this Privacy Policy from time to time to reflect changes in our practices or applicable laws.</p>
                        <p>Material changes will be communicated to you via:</p>
                        <ul class="legal-list">
                            <li>Email notification to your registered address</li>
                            <li>Dashboard notification within the Service</li>
                            <li>Prominent notice on our website</li>
                        </ul>
                        <p>We encourage you to review this policy periodically for any updates.</p>
                    </div>

                    <!-- Section 12 -->
                    <div class="legal-section" id="contact">
                        <h3 class="legal-section-title">
                            <span class="section-number">12</span>
                            <?php _e('legal_contact'); ?>
                        </h3>
                        <p>For privacy-related inquiries, questions, or to exercise your data rights, please contact us:</p>
                        <ul class="legal-list">
                            <li><strong>Telegram:</strong> <a href="<?php echo TELEGRAM_SUPPORT; ?>" class="text-cyan"><?php echo TELEGRAM_SUPPORT; ?></a></li>
                            <li><strong>Email:</strong> <a href="mailto:<?php echo SITE_EMAIL; ?>" class="text-cyan"><?php echo SITE_EMAIL; ?></a></li>
                        </ul>
                        <p>We aim to respond to all privacy inquiries within 72 hours.</p>
                    </div>

                    <!-- Navigation -->
                    <div class="legal-nav">
                        <a href="terms.php">Terms of Service</a>
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
