<?php
$page_title = 'Risk Disclaimer';
require_once 'includes/header.php';
?>

<section class="section" style="padding-top: calc(var(--navbar-height) + 3rem);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="alert alert-warning mb-4">
                    <h4 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Important Risk Warning</h4>
                    <p class="mb-0">Please read this disclaimer carefully before using ZYN Trade System. Trading involves substantial risk of loss.</p>
                </div>

                <h1 class="mb-4">Risk Disclaimer</h1>
                <p class="text-muted mb-4">Last updated: <?php echo date('F d, Y'); ?></p>

                <div class="card mb-4">
                    <div class="card-body">
                        <h4>General Risk Warning</h4>
                        <p class="lead">Trading mengandung risiko. Tidak ada sistem yang menjamin profit. Hasil trading bergantung pada kondisi market dan disiplin penggunaan sistem.</p>
                        <p class="lead">Trading involves risk. No system guarantees profit. Trading results depend on market conditions and system discipline.</p>

                        <hr>

                        <h4 class="mt-4">1. High Risk Investment</h4>
                        <p>Binary options and Fixed Time trading are high-risk financial instruments. You can lose some or all of your invested capital. Only trade with money you can afford to lose.</p>

                        <h4 class="mt-4">2. No Guaranteed Returns</h4>
                        <p><strong>ZYN Trade System does NOT guarantee profits or specific returns.</strong> Past performance and historical win rates are not indicative of future results. Market conditions change, and strategies that worked in the past may not work in the future.</p>

                        <h4 class="mt-4">3. Automated Trading Risks</h4>
                        <p>Automated trading systems carry additional risks:</p>
                        <ul>
                            <li>Technical failures may result in missed trades or errors</li>
                            <li>Internet connectivity issues can affect execution</li>
                            <li>Market volatility can cause unexpected results</li>
                            <li>Algorithmic strategies may underperform in certain conditions</li>
                        </ul>

                        <h4 class="mt-4">4. Win Rate Disclaimer</h4>
                        <p>The win rates displayed (65%-85%) are based on historical backtesting and past performance. They represent average results under specific conditions and should NOT be interpreted as guaranteed future performance.</p>

                        <h4 class="mt-4">5. Financial Advice Disclaimer</h4>
                        <p>ZYN Trade System is NOT a financial advisor. The Service does not provide personalized investment advice. All trading decisions are ultimately your responsibility. We recommend consulting with a licensed financial advisor before trading.</p>

                        <h4 class="mt-4">6. Third-Party Platform</h4>
                        <p>ZYN Trade System operates with OlympTrade but is an independent service. We are not affiliated with, endorsed by, or partners of OlympTrade. Any issues with OlympTrade platform are beyond our control.</p>

                        <h4 class="mt-4">7. Regulatory Notice</h4>
                        <p>Binary options trading may be restricted or prohibited in certain jurisdictions. It is your responsibility to ensure compliance with local laws and regulations before using our Service.</p>

                        <h4 class="mt-4">8. Emotional Trading Warning</h4>
                        <p>While ZYN aims to remove emotion from trading, users must still practice discipline by:</p>
                        <ul>
                            <li>Not overriding robot decisions based on emotion</li>
                            <li>Maintaining proper risk management settings</li>
                            <li>Not trading with money needed for essential expenses</li>
                            <li>Taking breaks during losing streaks</li>
                        </ul>

                        <h4 class="mt-4">9. Limitation of Liability</h4>
                        <p>ZYN Trade System, its owners, developers, and affiliates shall not be held liable for:</p>
                        <ul>
                            <li>Any trading losses incurred while using the Service</li>
                            <li>Technical issues, downtime, or connectivity problems</li>
                            <li>Decisions made based on our signals or strategies</li>
                            <li>Actions of third-party platforms (OlympTrade)</li>
                        </ul>

                        <h4 class="mt-4">10. Acknowledgment</h4>
                        <p>By using ZYN Trade System, you acknowledge that:</p>
                        <ul>
                            <li>You have read and understood this Risk Disclaimer</li>
                            <li>You accept all risks associated with trading</li>
                            <li>You are trading with funds you can afford to lose</li>
                            <li>You will not hold us responsible for trading losses</li>
                            <li>You are of legal age in your jurisdiction</li>
                        </ul>

                        <div class="alert alert-danger mt-4">
                            <strong>WARNING:</strong> If you do not understand the risks involved in trading, or if you cannot afford to lose your investment, DO NOT USE THIS SERVICE.
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <a href="register.php" class="btn btn-primary">I Understand the Risks - Continue</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
