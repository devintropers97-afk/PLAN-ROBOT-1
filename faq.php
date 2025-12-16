<?php
$page_title = 'FAQ';
require_once 'includes/header.php';
?>

<link rel="stylesheet" href="assets/css/legal.css">

<section class="legal-page">
    <div class="container">
        <!-- Page Header -->
        <div class="legal-header legal-fade-in">
            <div class="legal-badge">
                <i class="fas fa-question-circle"></i>
                Help Center
            </div>
            <h1 class="legal-title">Frequently Asked Questions</h1>
            <p class="legal-subtitle">Temukan jawaban untuk pertanyaan umum tentang ZYN Trade System</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Quick Links -->
                <div class="legal-toc legal-fade-in">
                    <div class="legal-toc-title">
                        <i class="fas fa-bolt"></i>
                        Quick Navigation
                    </div>
                    <ul class="legal-toc-list">
                        <li><a href="#getting-started">Getting Started</a></li>
                        <li><a href="#trading">Trading</a></li>
                        <li><a href="#risk-management">Risk Management</a></li>
                        <li><a href="#billing">Billing & Subscription</a></li>
                        <li><a href="#support">Support</a></li>
                    </ul>
                </div>

                <!-- Main Content -->
                <div class="legal-card legal-fade-in">

                    <!-- Getting Started Section -->
                    <div class="faq-section" id="getting-started">
                        <div class="faq-section-title">
                            <i class="fas fa-rocket"></i>
                            Getting Started
                        </div>
                        <div class="faq-accordion">
                            <div class="faq-item active">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <span>How do I get started with ZYN Trade System?</span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>Follow these simple steps to get started:</p>
                                        <ol>
                                            <li>Register on OlympTrade via our <a href="<?php echo OLYMPTRADE_AFFILIATE_LINK; ?>" target="_blank" style="color: #00d4ff;">official affiliate link</a></li>
                                            <li>Make a minimum deposit of $10 USD</li>
                                            <li>Create your ZYN account using your OlympTrade ID</li>
                                            <li>Wait for admin verification (typically 1-24 hours)</li>
                                            <li>Configure your robot settings and start trading!</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>

                            <div class="faq-item">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <span>Why do I need to register via the affiliate link?</span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>Registration via our affiliate link serves several purposes:</p>
                                        <ul>
                                            <li>Verifies that you're a legitimate user</li>
                                            <li>Enables us to offer the free trial period</li>
                                            <li>Helps support the continued development of ZYN Trade System</li>
                                            <li>Accounts not registered via our affiliate cannot be verified</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="faq-item">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <span>How long does verification take?</span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>Verification typically takes <strong>1-24 hours</strong>. During high-volume periods, it may take up to 48 hours.</p>
                                        <p>You'll receive a notification via email and Telegram once your account is verified and ready to use.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="faq-item">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <span>What if my verification is rejected?</span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>If your verification is rejected, you'll receive a detailed reason. Common rejection reasons include:</p>
                                        <ul>
                                            <li>Not registered via the official affiliate link</li>
                                            <li>Deposit below the minimum $10 requirement</li>
                                            <li>OlympTrade ID not found or incorrect</li>
                                            <li>Incomplete or inaccurate registration data</li>
                                        </ul>
                                        <p>Contact support via Telegram to resolve any issues and resubmit for verification.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Trading Section -->
                    <div class="faq-section" id="trading">
                        <div class="faq-section-title">
                            <i class="fas fa-chart-line"></i>
                            Trading
                        </div>
                        <div class="faq-accordion">
                            <div class="faq-item">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <span>How does the trading robot work?</span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>Our robot uses sophisticated algorithmic strategies to analyze market conditions and generate trading signals. When activated:</p>
                                        <ul>
                                            <li>Monitors the market 24/7 for trading opportunities</li>
                                            <li>Validates entry conditions based on your selected strategies</li>
                                            <li>Executes trades automatically within your configured risk parameters</li>
                                            <li>Applies stop-loss and take-profit rules to protect your capital</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="faq-item">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <span>What is the minimum balance required?</span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p><strong>Important:</strong> The robot will NOT trade if your OlympTrade balance is $0 or below your configured trade amount.</p>
                                        <p>We recommend maintaining at least <strong>$50-100</strong> for optimal performance with proper risk management. This allows for:</p>
                                        <ul>
                                            <li>Sufficient buffer for multiple trades</li>
                                            <li>Proper position sizing</li>
                                            <li>Better risk management implementation</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="faq-item">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <span>Can I use multiple strategies at once?</span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p><strong>Yes!</strong> You can enable multiple strategies simultaneously.</p>
                                        <p>The robot will analyze signals from all enabled strategies and execute trades based on the best opportunities. However, we recommend:</p>
                                        <ul>
                                            <li>Starting with 2-3 strategies initially</li>
                                            <li>Monitoring performance for each strategy</li>
                                            <li>Adjusting your selection based on results</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="faq-item">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <span>What is the expected win rate?</span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>Our strategies have historical win rates ranging from <strong>65% to 85%</strong>.</p>
                                        <p>However, please note:</p>
                                        <ul>
                                            <li>Past performance does not guarantee future results</li>
                                            <li>Win rates vary based on market conditions</li>
                                            <li>Results depend on selected strategies and risk settings</li>
                                            <li>We recommend starting conservatively and adjusting based on your results</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Risk Management Section -->
                    <div class="faq-section" id="risk-management">
                        <div class="faq-section-title">
                            <i class="fas fa-shield-alt"></i>
                            Risk Management
                        </div>
                        <div class="faq-accordion">
                            <div class="faq-item">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <span>How does risk management work?</span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>ZYN includes multiple risk management features to protect your capital:</p>
                                        <ul>
                                            <li><strong>Daily Trade Limit:</strong> Maximum number of trades executed per day</li>
                                            <li><strong>Stop Loss:</strong> Maximum loss threshold before robot stops trading</li>
                                            <li><strong>Take Profit:</strong> Target profit level to lock in gains</li>
                                            <li><strong>Trade Amount:</strong> Fixed amount per trade for consistent position sizing</li>
                                            <li><strong>Risk Level:</strong> Overall aggressiveness of strategy selection</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="faq-item">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <span>What happens when stop loss is hit?</span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>When your daily stop loss threshold is reached:</p>
                                        <ul>
                                            <li>The robot automatically turns <strong>OFF</strong></li>
                                            <li>All trading activity stops for the remainder of the day</li>
                                            <li>Your remaining capital is protected from further losses</li>
                                            <li>The robot will resume trading the next day (if auto-restart is enabled)</li>
                                        </ul>
                                        <p>This feature is designed to protect your capital during volatile market conditions.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Billing Section -->
                    <div class="faq-section" id="billing">
                        <div class="faq-section-title">
                            <i class="fas fa-credit-card"></i>
                            Billing & Subscription
                        </div>
                        <div class="faq-accordion">
                            <div class="faq-item">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <span>What payment methods are accepted?</span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>We accept the following payment methods:</p>
                                        <ul>
                                            <li><strong>QRIS</strong> (Indonesia) - Instant activation</li>
                                            <li><strong>Stripe</strong> (Credit/Debit cards) - Instant activation</li>
                                            <li><strong>PayPal</strong> - Instant activation</li>
                                            <li><strong>Wise</strong> (Bank transfer) - Manual verification (1-24 hours)</li>
                                            <li><strong>Bitcoin/Crypto</strong> - Manual verification (1-24 hours)</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="faq-item">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <span>Can I get a refund?</span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>Our refund policy is as follows:</p>
                                        <ul>
                                            <li><strong>Within 7 days, no trades executed:</strong> Full refund available</li>
                                            <li><strong>After 7 days:</strong> No refund</li>
                                            <li><strong>After any trade executed:</strong> No refund</li>
                                            <li><strong>Fraudulent or abusive accounts:</strong> No refund</li>
                                        </ul>
                                        <p>See our <a href="refund.php" style="color: #00d4ff;">Refund Policy</a> for complete details.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="faq-item">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <span>How do I cancel my subscription?</span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>You can cancel your subscription at any time:</p>
                                        <ol>
                                            <li>Go to your <strong>Dashboard</strong></li>
                                            <li>Navigate to <strong>Profile / Settings</strong></li>
                                            <li>Click on <strong>Subscription</strong> section</li>
                                            <li>Select <strong>Cancel Subscription</strong></li>
                                        </ol>
                                        <p>Your access will continue until the end of your current billing period.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Support Section -->
                    <div class="faq-section" id="support">
                        <div class="faq-section-title">
                            <i class="fas fa-headset"></i>
                            Support
                        </div>
                        <div class="faq-accordion">
                            <div class="faq-item">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <span>How can I contact support?</span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>Our primary support channel is Telegram for fastest response:</p>
                                        <ul>
                                            <li><strong>Telegram:</strong> <a href="<?php echo TELEGRAM_SUPPORT; ?>" target="_blank" style="color: #00d4ff;"><?php echo TELEGRAM_SUPPORT; ?></a></li>
                                            <li><strong>Email:</strong> <a href="mailto:<?php echo SITE_EMAIL; ?>" style="color: #00d4ff;"><?php echo SITE_EMAIL; ?></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="faq-item">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <span>What are the support hours?</span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <p>Support availability depends on your subscription package:</p>
                                        <ul>
                                            <li><strong>Free Trial & Starter:</strong> Standard hours (9 AM - 9 PM GMT+7)</li>
                                            <li><strong>Pro:</strong> Extended hours with priority response</li>
                                            <li><strong>Elite:</strong> 24/7 VIP support with dedicated assistance</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Still Have Questions CTA -->
                    <div class="legal-cta">
                        <h4 class="legal-cta-title">Still Have Questions?</h4>
                        <p class="legal-cta-text">Our support team is ready to help you with any questions</p>
                        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                            <a href="<?php echo TELEGRAM_SUPPORT; ?>" target="_blank" class="legal-btn">
                                <i class="fab fa-telegram"></i>
                                Contact Support
                            </a>
                            <a href="register.php" class="legal-btn legal-btn-outline">
                                <i class="fas fa-rocket"></i>
                                Get Started Free
                            </a>
                        </div>
                    </div>

                    <!-- Navigation -->
                    <div class="legal-nav">
                        <a href="terms.php">Terms of Service</a>
                        <a href="privacy.php">Privacy Policy</a>
                        <a href="disclaimer.php">Risk Disclaimer</a>
                        <a href="refund.php">Refund Policy</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// FAQ Accordion Toggle
function toggleFaq(element) {
    const faqItem = element.closest('.faq-item');
    const isActive = faqItem.classList.contains('active');

    // Close all items in the same accordion
    const accordion = faqItem.closest('.faq-accordion');
    accordion.querySelectorAll('.faq-item').forEach(item => {
        item.classList.remove('active');
    });

    // Toggle current item
    if (!isActive) {
        faqItem.classList.add('active');
    }
}

// Initialize first item in each section as open
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.faq-accordion').forEach(accordion => {
        const firstItem = accordion.querySelector('.faq-item');
        if (firstItem) {
            firstItem.classList.add('active');
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
