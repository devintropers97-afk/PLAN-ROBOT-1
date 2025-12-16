<?php
$page_title = 'FAQ';
require_once 'includes/header.php';
?>

<section class="section" style="padding-top: calc(var(--navbar-height) + 3rem);">
    <div class="container">
        <div class="section-header fade-in">
            <span class="section-badge">Support</span>
            <h1 class="section-title">Frequently Asked Questions</h1>
            <p class="section-desc">
                Find answers to common questions about ZYN Trade System
            </p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Getting Started -->
                <h4 class="mb-3 mt-4 fade-in"><i class="fas fa-rocket text-primary"></i> Getting Started</h4>
                <div class="accordion mb-4 fade-in" id="faqGettingStarted">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#gs1">
                                How do I get started with ZYN Trade System?
                            </button>
                        </h2>
                        <div id="gs1" class="accordion-collapse collapse show" data-bs-parent="#faqGettingStarted">
                            <div class="accordion-body">
                                <ol>
                                    <li>Register on OlympTrade via our <a href="<?php echo OLYMPTRADE_AFFILIATE_LINK; ?>" target="_blank">affiliate link</a></li>
                                    <li>Deposit minimum $10 USD</li>
                                    <li>Create your ZYN account with your OlympTrade ID</li>
                                    <li>Wait for admin verification (within 24 hours)</li>
                                    <li>Configure your robot settings and start trading!</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#gs2">
                                Why do I need to register via the affiliate link?
                            </button>
                        </h2>
                        <div id="gs2" class="accordion-collapse collapse" data-bs-parent="#faqGettingStarted">
                            <div class="accordion-body">
                                Registration via our affiliate link is how we verify that you're a legitimate user and enables us to offer the free trial. It also helps support the development of ZYN Trade System. Accounts not registered via our affiliate cannot be verified.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#gs3">
                                How long does verification take?
                            </button>
                        </h2>
                        <div id="gs3" class="accordion-collapse collapse" data-bs-parent="#faqGettingStarted">
                            <div class="accordion-body">
                                Verification typically takes 1-24 hours. During high-volume periods, it may take up to 48 hours. You'll receive a notification via email and Telegram once your account is verified.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#gs4">
                                What if my verification is rejected?
                            </button>
                        </h2>
                        <div id="gs4" class="accordion-collapse collapse" data-bs-parent="#faqGettingStarted">
                            <div class="accordion-body">
                                If your verification is rejected, you'll receive a detailed reason. Common reasons include:
                                <ul>
                                    <li>Not registered via official affiliate link</li>
                                    <li>Deposit below $10</li>
                                    <li>OlympTrade ID not found</li>
                                    <li>Incomplete registration data</li>
                                </ul>
                                Contact support via Telegram to resolve any issues.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Trading -->
                <h4 class="mb-3 fade-in"><i class="fas fa-chart-line text-primary"></i> Trading</h4>
                <div class="accordion mb-4 fade-in" id="faqTrading">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#t1">
                                How does the trading robot work?
                            </button>
                        </h2>
                        <div id="t1" class="accordion-collapse collapse" data-bs-parent="#faqTrading">
                            <div class="accordion-body">
                                Our robot uses algorithmic strategies to analyze market conditions and generate trading signals. When you turn on the robot:
                                <ul>
                                    <li>It monitors the market 24/7</li>
                                    <li>Validates entry conditions based on your selected strategies</li>
                                    <li>Executes trades automatically within your risk parameters</li>
                                    <li>Applies stop-loss and take-profit rules</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#t2">
                                What is the minimum balance required?
                            </button>
                        </h2>
                        <div id="t2" class="accordion-collapse collapse" data-bs-parent="#faqTrading">
                            <div class="accordion-body">
                                <strong>Important:</strong> The robot will NOT trade if your OlympTrade balance is $0 or below your set trade amount. We recommend maintaining at least $50-100 for optimal performance with proper risk management.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#t3">
                                Can I use multiple strategies at once?
                            </button>
                        </h2>
                        <div id="t3" class="accordion-collapse collapse" data-bs-parent="#faqTrading">
                            <div class="accordion-body">
                                Yes! You can enable multiple strategies simultaneously. The robot will analyze signals from all enabled strategies and execute trades based on the best opportunities. However, we recommend starting with 2-3 strategies and adjusting based on performance.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#t4">
                                What is the expected win rate?
                            </button>
                        </h2>
                        <div id="t4" class="accordion-collapse collapse" data-bs-parent="#faqTrading">
                            <div class="accordion-body">
                                Our strategies have historical win rates ranging from 65% to 85%. However, past performance does not guarantee future results. Win rates vary based on market conditions, selected strategies, and risk settings. We recommend starting conservatively and adjusting based on your results.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Risk Management -->
                <h4 class="mb-3 fade-in"><i class="fas fa-shield-alt text-primary"></i> Risk Management</h4>
                <div class="accordion mb-4 fade-in" id="faqRisk">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#r1">
                                How does risk management work?
                            </button>
                        </h2>
                        <div id="r1" class="accordion-collapse collapse" data-bs-parent="#faqRisk">
                            <div class="accordion-body">
                                ZYN includes multiple risk management features:
                                <ul>
                                    <li><strong>Daily Limit:</strong> Maximum number of trades per day</li>
                                    <li><strong>Stop Loss:</strong> Maximum loss before robot stops trading</li>
                                    <li><strong>Take Profit:</strong> Target profit to lock in gains</li>
                                    <li><strong>Trade Amount:</strong> Fixed amount per trade</li>
                                    <li><strong>Risk Level:</strong> Overall aggressiveness of strategy selection</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#r2">
                                What happens when stop loss is hit?
                            </button>
                        </h2>
                        <div id="r2" class="accordion-collapse collapse" data-bs-parent="#faqRisk">
                            <div class="accordion-body">
                                When your daily stop loss is hit, the robot automatically turns OFF and stops all trading for the day. This protects your capital from excessive losses. The robot will resume trading the next day (if enabled).
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Billing -->
                <h4 class="mb-3 fade-in"><i class="fas fa-credit-card text-primary"></i> Billing & Subscription</h4>
                <div class="accordion mb-4 fade-in" id="faqBilling">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#b1">
                                What payment methods are accepted?
                            </button>
                        </h2>
                        <div id="b1" class="accordion-collapse collapse" data-bs-parent="#faqBilling">
                            <div class="accordion-body">
                                We accept:
                                <ul>
                                    <li><strong>QRIS</strong> (Indonesia) - Automatic</li>
                                    <li><strong>Stripe</strong> (Credit/Debit cards) - Automatic</li>
                                    <li><strong>PayPal</strong> - Automatic</li>
                                    <li><strong>Wise</strong> (Bank transfer) - Manual (1-24 hours)</li>
                                    <li><strong>Bitcoin</strong> - Manual (1-24 hours)</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#b2">
                                Can I get a refund?
                            </button>
                        </h2>
                        <div id="b2" class="accordion-collapse collapse" data-bs-parent="#faqBilling">
                            <div class="accordion-body">
                                Our refund policy:
                                <ul>
                                    <li><strong>Within 7 days, no trades:</strong> Full refund available</li>
                                    <li><strong>After 7 days:</strong> No refund</li>
                                    <li><strong>After any trade executed:</strong> No refund</li>
                                    <li><strong>Fraudulent accounts:</strong> No refund</li>
                                </ul>
                                See our <a href="refund.php">Refund Policy</a> for details.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#b3">
                                How do I cancel my subscription?
                            </button>
                        </h2>
                        <div id="b3" class="accordion-collapse collapse" data-bs-parent="#faqBilling">
                            <div class="accordion-body">
                                You can cancel your subscription anytime from your Dashboard > Settings > Subscription. Your access will continue until the end of your current billing period.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Support -->
                <h4 class="mb-3 fade-in"><i class="fas fa-headset text-primary"></i> Support</h4>
                <div class="accordion fade-in" id="faqSupport">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#s1">
                                How can I contact support?
                            </button>
                        </h2>
                        <div id="s1" class="accordion-collapse collapse" data-bs-parent="#faqSupport">
                            <div class="accordion-body">
                                Primary support is via Telegram: <a href="<?php echo TELEGRAM_SUPPORT; ?>" target="_blank"><?php echo TELEGRAM_SUPPORT; ?></a>
                                <br>You can also email us at <a href="mailto:<?php echo SITE_EMAIL; ?>"><?php echo SITE_EMAIL; ?></a>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#s2">
                                What are the support hours?
                            </button>
                        </h2>
                        <div id="s2" class="accordion-collapse collapse" data-bs-parent="#faqSupport">
                            <div class="accordion-body">
                                Support availability depends on your package:
                                <ul>
                                    <li><strong>Free Trial & Starter:</strong> Standard hours (9 AM - 9 PM GMT+7)</li>
                                    <li><strong>Pro:</strong> Extended hours with priority</li>
                                    <li><strong>Elite:</strong> 24/7 VIP support</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Still have questions -->
                <div class="text-center mt-5 fade-in">
                    <h4>Still have questions?</h4>
                    <p class="text-muted">Our support team is ready to help you</p>
                    <a href="<?php echo TELEGRAM_SUPPORT; ?>" target="_blank" class="btn btn-primary btn-lg">
                        <i class="fab fa-telegram"></i> Contact Support
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
