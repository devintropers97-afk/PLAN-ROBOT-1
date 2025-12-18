<?php
$page_title = __('refund_title');
require_once 'includes/header.php';
?>

<section class="section" style="padding-top: calc(var(--navbar-height) + 3rem);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="mb-4"><?php _e('refund_heading'); ?></h1>
                <p class="text-muted mb-4"><?php _e('refund_updated'); ?>: <?php echo date('F d, Y'); ?></p>

                <div class="card mb-4">
                    <div class="card-body">
                        <h4>Overview</h4>
                        <p>We want you to be satisfied with ZYN Trade System. This policy outlines when refunds are available and how to request one.</p>

                        <h4 class="mt-4">Refund Eligibility</h4>

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Condition</th>
                                        <th>Refund Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Within 7 days, <strong>NO trades executed</strong></td>
                                        <td><span class="badge bg-success">Full Refund Available</span></td>
                                    </tr>
                                    <tr>
                                        <td>Within 7 days, trades executed</td>
                                        <td><span class="badge bg-danger">No Refund</span></td>
                                    </tr>
                                    <tr>
                                        <td>After 7 days</td>
                                        <td><span class="badge bg-danger">No Refund</span></td>
                                    </tr>
                                    <tr>
                                        <td>Fraudulent or duplicate account</td>
                                        <td><span class="badge bg-danger">No Refund</span></td>
                                    </tr>
                                    <tr>
                                        <td>Terms of Service violation</td>
                                        <td><span class="badge bg-danger">No Refund</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h4 class="mt-4">Free Trial</h4>
                        <p>Free trial accounts are not eligible for refunds as no payment is made. If you're unsatisfied during the trial period, simply do not subscribe to a paid plan.</p>

                        <h4 class="mt-4">How to Request a Refund</h4>
                        <p>To request a refund:</p>
                        <ol>
                            <li>Contact us via Telegram: <a href="<?php echo TELEGRAM_SUPPORT; ?>"><?php echo TELEGRAM_SUPPORT; ?></a></li>
                            <li>Provide your registered email address</li>
                            <li>State your reason for requesting a refund</li>
                            <li>Our team will verify your eligibility</li>
                            <li>If approved, refund will be processed within 5-10 business days</li>
                        </ol>

                        <h4 class="mt-4">Refund Processing</h4>
                        <ul>
                            <li><strong>Stripe/PayPal:</strong> Refunded to original payment method (5-10 business days)</li>
                            <li><strong>QRIS:</strong> Refunded via bank transfer (requires bank details)</li>
                            <li><strong>Wise:</strong> Refunded to original Wise account</li>
                            <li><strong>Bitcoin:</strong> Refunded to provided wallet address (may incur network fees)</li>
                        </ul>

                        <h4 class="mt-4">Non-Refundable Items</h4>
                        <ul>
                            <li>Subscription fees after any trade has been executed</li>
                            <li>News Hunter addon if signals have been received</li>
                            <li>Partial month refunds (we don't prorate)</li>
                            <li>Payment processing fees (charged by payment providers)</li>
                        </ul>

                        <h4 class="mt-4">Subscription Cancellation</h4>
                        <p>You can cancel your subscription anytime:</p>
                        <ul>
                            <li>Go to Dashboard > Settings > Subscription</li>
                            <li>Click "Cancel Subscription"</li>
                            <li>Your access continues until the end of the billing period</li>
                            <li>No refund for the remaining days</li>
                        </ul>

                        <h4 class="mt-4">Disputes</h4>
                        <p>If you dispute a charge with your payment provider (chargeback) without first contacting us:</p>
                        <ul>
                            <li>Your account will be immediately suspended</li>
                            <li>You will be permanently banned from the Service</li>
                            <li>We reserve the right to dispute the chargeback with evidence</li>
                        </ul>
                        <p>Please contact us first to resolve any issues before initiating a dispute.</p>

                        <h4 class="mt-4">Contact</h4>
                        <p>For refund requests or questions:</p>
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
