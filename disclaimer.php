<?php
$page_title = __('disclaimer_title');
require_once 'includes/header.php';
?>

<link rel="stylesheet" href="assets/css/legal.css">

<section class="legal-page">
    <div class="container">
        <!-- Page Header -->
        <div class="legal-header legal-fade-in">
            <div class="legal-badge" style="background: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.3); color: #ef4444;">
                <i class="fas fa-exclamation-triangle"></i>
                <?php _e('disclaimer_badge'); ?>
            </div>
            <h1 class="legal-title"><?php _e('disclaimer_heading'); ?></h1>
            <p class="legal-subtitle"><?php _e('disclaimer_subtitle'); ?></p>
            <div class="legal-updated">
                <i class="fas fa-calendar-alt"></i>
                <?php _e('disclaimer_updated'); ?>: <?php echo date('F d, Y'); ?>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Critical Warning Banner -->
                <div class="legal-alert danger legal-fade-in" style="margin-bottom: 2rem;">
                    <div class="legal-alert-icon"><i class="fas fa-exclamation-circle"></i></div>
                    <div class="legal-alert-content">
                        <div class="legal-alert-title"><?php _e('disclaimer_critical'); ?></div>
                        <p class="legal-alert-text"><?php _e('disclaimer_critical_text'); ?></p>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="legal-card legal-fade-in">
                    <!-- General Warning -->
                    <div class="legal-section">
                        <h3 class="legal-section-title">
                            <span class="section-number" style="background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(239, 68, 68, 0.1));"><i class="fas fa-exclamation" style="font-size: 0.8rem;"></i></span>
                            General Risk Warning
                        </h3>
                        <div style="background: rgba(239, 68, 68, 0.05); border: 1px solid rgba(239, 68, 68, 0.15); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem;">
                            <p style="font-size: 1.1rem; color: rgba(255,255,255,0.9); margin-bottom: 1rem; font-weight: 500;">Trading involves risk. No system guarantees profit. Trading results depend on market conditions and system discipline.</p>
                            <p style="font-size: 1.1rem; color: rgba(255,255,255,0.9); margin: 0; font-weight: 500;">Trading mengandung risiko. Tidak ada sistem yang menjamin profit. Hasil trading bergantung pada kondisi market dan disiplin penggunaan sistem.</p>
                        </div>
                    </div>

                    <!-- Section 1 -->
                    <div class="legal-section">
                        <h3 class="legal-section-title">
                            <span class="section-number">1</span>
                            High Risk Investment
                        </h3>
                        <p>Binary options and Fixed Time Trading are classified as high-risk financial instruments. The inherent risks include:</p>
                        <ul class="legal-list">
                            <li>Possibility of losing some or all of your invested capital</li>
                            <li>High volatility and unpredictable market movements</li>
                            <li>Limited time frames that amplify risk exposure</li>
                            <li>Leverage effects that can magnify both gains and losses</li>
                        </ul>
                        <div class="legal-alert danger">
                            <div class="legal-alert-icon"><i class="fas fa-ban"></i></div>
                            <div class="legal-alert-content">
                                <div class="legal-alert-title">Only Trade What You Can Afford to Lose</div>
                                <p class="legal-alert-text">Never trade with money needed for essential expenses such as rent, bills, food, or emergency funds.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2 -->
                    <div class="legal-section">
                        <h3 class="legal-section-title">
                            <span class="section-number">2</span>
                            No Guaranteed Returns
                        </h3>
                        <p><strong style="color: #ef4444;">ZYN Trade System does NOT guarantee profits or specific returns.</strong></p>
                        <ul class="legal-list">
                            <li>Past performance is not indicative of future results</li>
                            <li>Historical win rates are based on backtesting, not live trading guarantees</li>
                            <li>Market conditions change constantly and unpredictably</li>
                            <li>Strategies that worked in the past may not work in the future</li>
                            <li>Individual results will vary significantly</li>
                        </ul>
                        <p>Any displayed statistics, win rates, or performance metrics are for informational purposes only and should NOT be interpreted as guaranteed future performance.</p>
                    </div>

                    <!-- Section 3 -->
                    <div class="legal-section">
                        <h3 class="legal-section-title">
                            <span class="section-number">3</span>
                            Automated Trading Risks
                        </h3>
                        <p>Automated trading systems carry additional risks that you must understand:</p>
                        <ul class="legal-list">
                            <li><strong>Technical Failures:</strong> Software bugs, server issues, or system errors may result in missed trades, incorrect executions, or other errors</li>
                            <li><strong>Connectivity Issues:</strong> Internet outages or latency problems can affect trade execution timing</li>
                            <li><strong>Market Volatility:</strong> Extreme market conditions may cause unexpected results or slippage</li>
                            <li><strong>Algorithm Limitations:</strong> Strategies may underperform in certain market conditions they weren't designed for</li>
                            <li><strong>Third-Party Dependencies:</strong> Reliance on external platforms (OlympTrade) introduces additional risk factors</li>
                        </ul>
                    </div>

                    <!-- Section 4 -->
                    <div class="legal-section">
                        <h3 class="legal-section-title">
                            <span class="section-number">4</span>
                            Win Rate Disclaimer
                        </h3>
                        <div class="legal-alert warning">
                            <div class="legal-alert-icon"><i class="fas fa-chart-line"></i></div>
                            <div class="legal-alert-content">
                                <div class="legal-alert-title">About Displayed Win Rates (65%-85%)</div>
                                <p class="legal-alert-text">These rates are based on historical backtesting and past performance under specific market conditions. They represent averages and should NOT be interpreted as guaranteed future performance.</p>
                            </div>
                        </div>
                        <ul class="legal-list">
                            <li>Actual win rates may be higher or lower than displayed averages</li>
                            <li>Results vary based on market conditions, timing, and selected strategies</li>
                            <li>Past performance during backtesting does not equal live trading results</li>
                            <li>Even with high win rates, losing streaks can and will occur</li>
                        </ul>
                    </div>

                    <!-- Section 5 -->
                    <div class="legal-section">
                        <h3 class="legal-section-title">
                            <span class="section-number">5</span>
                            Financial Advice Disclaimer
                        </h3>
                        <p><strong>ZYN Trade System is NOT a licensed financial advisor.</strong></p>
                        <ul class="legal-list">
                            <li>Our Service does not provide personalized investment advice</li>
                            <li>Trading signals are educational tools, not investment recommendations</li>
                            <li>All trading decisions are ultimately your responsibility</li>
                            <li>We recommend consulting with a licensed financial advisor before trading</li>
                            <li>Consider your financial situation and risk tolerance before using our Service</li>
                        </ul>
                    </div>

                    <!-- Section 6 -->
                    <div class="legal-section">
                        <h3 class="legal-section-title">
                            <span class="section-number">6</span>
                            Third-Party Platform
                        </h3>
                        <p>ZYN Trade System operates with OlympTrade but maintains complete independence:</p>
                        <ul class="legal-list">
                            <li>We are NOT affiliated with, endorsed by, or partners of OlympTrade</li>
                            <li>OlympTrade platform issues are beyond our control</li>
                            <li>We are not responsible for OlympTrade's policies, terms, or actions</li>
                            <li>Any disputes with OlympTrade must be resolved directly with them</li>
                        </ul>
                    </div>

                    <!-- Section 7 -->
                    <div class="legal-section">
                        <h3 class="legal-section-title">
                            <span class="section-number">7</span>
                            Regulatory Notice
                        </h3>
                        <div class="legal-alert info">
                            <div class="legal-alert-icon"><i class="fas fa-globe"></i></div>
                            <div class="legal-alert-content">
                                <div class="legal-alert-title">Jurisdictional Restrictions</div>
                                <p class="legal-alert-text">Binary options trading may be restricted or prohibited in certain countries. It is YOUR responsibility to ensure compliance with local laws before using our Service.</p>
                            </div>
                        </div>
                        <p>We do not provide legal advice regarding trading regulations. Consult with local legal counsel if you are unsure about the legality of trading in your jurisdiction.</p>
                    </div>

                    <!-- Section 8 -->
                    <div class="legal-section">
                        <h3 class="legal-section-title">
                            <span class="section-number">8</span>
                            Emotional Trading Warning
                        </h3>
                        <p>While ZYN aims to remove emotion from trading through automation, users must still maintain discipline:</p>
                        <ul class="legal-list">
                            <li>Do not override robot decisions based on emotional impulses</li>
                            <li>Maintain proper risk management settings at all times</li>
                            <li>Never trade with money needed for essential living expenses</li>
                            <li>Take breaks during extended losing streaks</li>
                            <li>Do not chase losses by increasing trade amounts</li>
                            <li>Set realistic expectations and accept that losses are part of trading</li>
                        </ul>
                    </div>

                    <!-- Section 9 -->
                    <div class="legal-section">
                        <h3 class="legal-section-title">
                            <span class="section-number">9</span>
                            Limitation of Liability
                        </h3>
                        <p>ZYN Trade System, its owners, developers, employees, and affiliates shall NOT be held liable for:</p>
                        <ul class="legal-list">
                            <li>Any trading losses incurred while using the Service</li>
                            <li>Technical issues, downtime, or connectivity problems</li>
                            <li>Decisions made based on our signals, strategies, or recommendations</li>
                            <li>Actions, policies, or technical issues of third-party platforms</li>
                            <li>Loss of data, profits, or business opportunities</li>
                            <li>Indirect, incidental, special, or consequential damages</li>
                        </ul>
                    </div>

                    <!-- Section 10 -->
                    <div class="legal-section">
                        <h3 class="legal-section-title">
                            <span class="section-number">10</span>
                            Acknowledgment
                        </h3>
                        <p>By using ZYN Trade System, you acknowledge and confirm that:</p>
                        <ul class="legal-list numbered">
                            <li>You have read and fully understood this Risk Disclaimer</li>
                            <li>You accept all risks associated with trading binary options</li>
                            <li>You are trading only with funds you can afford to lose completely</li>
                            <li>You will not hold us responsible for any trading losses</li>
                            <li>You are of legal age to trade in your jurisdiction</li>
                            <li>You have verified that trading is legal in your country</li>
                            <li>You understand that past performance does not guarantee future results</li>
                        </ul>
                    </div>

                    <!-- Final Warning -->
                    <div class="legal-alert danger" style="margin-top: 2rem;">
                        <div class="legal-alert-icon"><i class="fas fa-skull-crossbones"></i></div>
                        <div class="legal-alert-content">
                            <div class="legal-alert-title">FINAL WARNING</div>
                            <p class="legal-alert-text">If you do not fully understand the risks involved in trading, or if you cannot afford to lose your investment, <strong>DO NOT USE THIS SERVICE</strong>. Trading is not suitable for everyone. Consider seeking independent financial advice.</p>
                        </div>
                    </div>

                    <!-- CTA Section -->
                    <div class="legal-cta">
                        <h4 class="legal-cta-title">I Understand the Risks</h4>
                        <p class="legal-cta-text">By proceeding, you confirm that you have read and understood this Risk Disclaimer</p>
                        <a href="register.php" class="legal-btn">
                            <i class="fas fa-check"></i>
                            I Accept the Risks - Continue
                        </a>
                    </div>

                    <!-- Navigation -->
                    <div class="legal-nav">
                        <a href="terms.php">Terms of Service</a>
                        <a href="privacy.php">Privacy Policy</a>
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
