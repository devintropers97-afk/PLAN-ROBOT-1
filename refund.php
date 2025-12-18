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
                        <h4><?php _e('refund_overview'); ?></h4>
                        <p><?php _e('refund_overview_text'); ?></p>

                        <h4 class="mt-4"><?php _e('refund_eligibility'); ?></h4>

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th><?php _e('refund_condition'); ?></th>
                                        <th><?php _e('refund_status'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><?php _e('refund_cond_7days_no_trade'); ?></td>
                                        <td><span class="badge bg-success"><?php _e('refund_full_available'); ?></span></td>
                                    </tr>
                                    <tr>
                                        <td><?php _e('refund_cond_7days_traded'); ?></td>
                                        <td><span class="badge bg-danger"><?php _e('refund_no_refund'); ?></span></td>
                                    </tr>
                                    <tr>
                                        <td><?php _e('refund_cond_after_7days'); ?></td>
                                        <td><span class="badge bg-danger"><?php _e('refund_no_refund'); ?></span></td>
                                    </tr>
                                    <tr>
                                        <td><?php _e('refund_cond_fraud'); ?></td>
                                        <td><span class="badge bg-danger"><?php _e('refund_no_refund'); ?></span></td>
                                    </tr>
                                    <tr>
                                        <td><?php _e('refund_cond_tos_violation'); ?></td>
                                        <td><span class="badge bg-danger"><?php _e('refund_no_refund'); ?></span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h4 class="mt-4"><?php _e('refund_free_trial'); ?></h4>
                        <p><?php _e('refund_free_trial_text'); ?></p>

                        <h4 class="mt-4"><?php _e('refund_how_to_request'); ?></h4>
                        <p><?php _e('refund_request_intro'); ?></p>
                        <ol>
                            <li><?php _e('refund_step1'); ?> <a href="<?php echo TELEGRAM_SUPPORT; ?>"><?php echo TELEGRAM_SUPPORT; ?></a></li>
                            <li><?php _e('refund_step2'); ?></li>
                            <li><?php _e('refund_step3'); ?></li>
                            <li><?php _e('refund_step4'); ?></li>
                            <li><?php _e('refund_step5'); ?></li>
                        </ol>

                        <h4 class="mt-4"><?php _e('refund_processing'); ?></h4>
                        <ul>
                            <li><strong>Stripe/PayPal:</strong> <?php _e('refund_proc_stripe'); ?></li>
                            <li><strong>QRIS:</strong> <?php _e('refund_proc_qris'); ?></li>
                            <li><strong>Wise:</strong> <?php _e('refund_proc_wise'); ?></li>
                            <li><strong>Bitcoin:</strong> <?php _e('refund_proc_bitcoin'); ?></li>
                        </ul>

                        <h4 class="mt-4"><?php _e('refund_non_refundable'); ?></h4>
                        <ul>
                            <li><?php _e('refund_nonref_traded'); ?></li>
                            <li><?php _e('refund_nonref_news'); ?></li>
                            <li><?php _e('refund_nonref_partial'); ?></li>
                            <li><?php _e('refund_nonref_fees'); ?></li>
                        </ul>

                        <h4 class="mt-4"><?php _e('refund_cancellation'); ?></h4>
                        <p><?php _e('refund_cancel_intro'); ?></p>
                        <ul>
                            <li><?php _e('refund_cancel_step1'); ?></li>
                            <li><?php _e('refund_cancel_step2'); ?></li>
                            <li><?php _e('refund_cancel_step3'); ?></li>
                            <li><?php _e('refund_cancel_step4'); ?></li>
                        </ul>

                        <h4 class="mt-4"><?php _e('refund_disputes'); ?></h4>
                        <p><?php _e('refund_dispute_intro'); ?></p>
                        <ul>
                            <li><?php _e('refund_dispute1'); ?></li>
                            <li><?php _e('refund_dispute2'); ?></li>
                            <li><?php _e('refund_dispute3'); ?></li>
                        </ul>
                        <p><?php _e('refund_dispute_note'); ?></p>

                        <h4 class="mt-4"><?php _e('legal_contact'); ?></h4>
                        <p><?php _e('refund_contact_intro'); ?></p>
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
