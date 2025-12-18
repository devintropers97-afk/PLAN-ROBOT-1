<?php
$page_title = __('subscribe_title');
require_once 'includes/header.php';

// Require login
if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = 'subscribe.php' . (isset($_GET['plan']) ? '?plan=' . $_GET['plan'] : '');
    redirect('login.php');
}

// Get user data
$db = getDBConnection();
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Get selected plan
$selected_plan = isset($_GET['plan']) ? strtolower($_GET['plan']) : 'pro';
$valid_plans = ['pro', 'elite', 'vip'];
if (!in_array($selected_plan, $valid_plans)) {
    $selected_plan = 'pro';
}

// Plan details
$plans = [
    'pro' => [
        'name' => 'PRO',
        'price' => PRICE_PRO,
        'strategies' => 4,
        'features' => ['4 Strategies', 'TITAN-PULSE & SHADOW-EDGE', '90 days history', 'Priority support'],
        'color' => 'info'
    ],
    'elite' => [
        'name' => 'ELITE',
        'price' => PRICE_ELITE,
        'strategies' => 7,
        'features' => ['7 Strategies', 'STEALTH-MODE, PHOENIX-X1, VORTEX-PRO', '180 days history', 'Auto-pause system'],
        'color' => 'warning'
    ],
    'vip' => [
        'name' => 'VIP',
        'price' => PRICE_VIP,
        'strategies' => 10,
        'features' => ['All 10 Strategies', 'ORACLE-PRIME (90-91%)', '1 year history', 'Direct owner support'],
        'color' => 'primary'
    ]
];

$plan = $plans[$selected_plan];

// Handle form submission
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $payment_method = cleanInput($_POST['payment_method'] ?? '');
    $payment_proof = '';

    // Handle file upload
    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/payments/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_ext = strtolower(pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];

        if (in_array($file_ext, $allowed) && $_FILES['payment_proof']['size'] <= MAX_FILE_SIZE) {
            $filename = 'payment_' . $user['id'] . '_' . time() . '.' . $file_ext;
            $filepath = $upload_dir . $filename;

            if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $filepath)) {
                $payment_proof = $filepath;
            }
        }
    }

    // Create subscription request
    try {
        $stmt = $db->prepare("
            INSERT INTO subscriptions (user_id, plan, amount, payment_method, payment_proof, status, created_at)
            VALUES (?, ?, ?, ?, ?, 'pending', NOW())
        ");
        $stmt->execute([
            $user['id'],
            $selected_plan,
            $plan['price'],
            $payment_method,
            $payment_proof
        ]);

        $message = 'Subscription request submitted! We will verify your payment within 1-24 hours. You will receive confirmation via email/Telegram.';
    } catch (PDOException $e) {
        $error = 'Failed to submit subscription request. Please try again.';
    }
}
?>

<section class="section" style="padding-top: calc(var(--navbar-height) + 3rem);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Plan Selection -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Select Plan</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <?php foreach ($plans as $key => $p): ?>
                            <div class="col-md-4">
                                <div class="card h-100 <?php echo $key === $selected_plan ? 'border-' . $p['color'] : ''; ?>"
                                     onclick="window.location='subscribe.php?plan=<?php echo $key; ?>'"
                                     style="cursor: pointer;">
                                    <div class="card-body text-center">
                                        <span class="badge bg-<?php echo $p['color']; ?> mb-2"><?php echo $p['name']; ?></span>
                                        <h4 class="mb-0">$<?php echo $p['price']; ?></h4>
                                        <small class="text-muted">/ month</small>
                                        <p class="small mt-2 mb-0"><?php echo $p['strategies']; ?> Strategies</p>
                                        <?php if ($key === $selected_plan): ?>
                                        <i class="fas fa-check-circle text-success mt-2"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Selected Plan Details -->
                <div class="card mb-4 border-<?php echo $plan['color']; ?>">
                    <div class="card-header bg-<?php echo $plan['color']; ?> text-<?php echo $plan['color'] === 'warning' ? 'dark' : 'white'; ?>">
                        <h5 class="mb-0"><i class="fas fa-crown me-2"></i><?php echo $plan['name']; ?> Plan</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h3 class="text-<?php echo $plan['color']; ?>">$<?php echo $plan['price']; ?> <small class="text-muted fs-6">/ month</small></h3>
                                <ul class="list-unstyled">
                                    <?php foreach ($plan['features'] as $feature): ?>
                                    <li><i class="fas fa-check text-success me-2"></i><?php echo $feature; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-dark p-3 rounded">
                                    <p class="small mb-2"><strong>Your Account:</strong></p>
                                    <p class="small mb-1">Email: <?php echo htmlspecialchars($user['email']); ?></p>
                                    <p class="small mb-0">Current Plan: <span class="badge bg-secondary"><?php echo strtoupper($user['subscription_type'] ?? 'FREE'); ?></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
                    <hr>
                    <p class="mb-0 small">Need help? Contact us: <a href="https://t.me/<?php echo str_replace('@', '', TELEGRAM_SUPPORT); ?>" target="_blank"><?php echo TELEGRAM_SUPPORT; ?></a></p>
                </div>
                <?php elseif ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                </div>
                <?php else: ?>

                <!-- Payment Form -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Payment Method</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                            <!-- Payment Methods -->
                            <div class="mb-4">
                                <label class="form-label">Choose Payment Method</label>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-check card p-3">
                                            <input class="form-check-input" type="radio" name="payment_method" id="paypal" value="paypal" checked>
                                            <label class="form-check-label d-flex align-items-center" for="paypal">
                                                <i class="fab fa-paypal fa-2x text-primary me-3"></i>
                                                <div>
                                                    <strong>PayPal</strong>
                                                    <small class="d-block text-muted">International payments</small>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check card p-3">
                                            <input class="form-check-input" type="radio" name="payment_method" id="wise" value="wise">
                                            <label class="form-check-label d-flex align-items-center" for="wise">
                                                <i class="fas fa-exchange-alt fa-2x text-success me-3"></i>
                                                <div>
                                                    <strong>Wise (TransferWise)</strong>
                                                    <small class="d-block text-muted">Low fee transfers</small>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check card p-3">
                                            <input class="form-check-input" type="radio" name="payment_method" id="crypto" value="crypto">
                                            <label class="form-check-label d-flex align-items-center" for="crypto">
                                                <i class="fab fa-bitcoin fa-2x text-warning me-3"></i>
                                                <div>
                                                    <strong>Crypto (USDT/BTC)</strong>
                                                    <small class="d-block text-muted">TRC20 / BTC Network</small>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check card p-3">
                                            <input class="form-check-input" type="radio" name="payment_method" id="bank_transfer" value="bank_transfer">
                                            <label class="form-check-label d-flex align-items-center" for="bank_transfer">
                                                <i class="fas fa-university fa-2x text-info me-3"></i>
                                                <div>
                                                    <strong>Bank Transfer</strong>
                                                    <small class="d-block text-muted">Indonesia (BCA/Mandiri/BNI)</small>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Details (Dynamic) -->
                            <div id="payment-details" class="mb-4">
                                <div class="alert alert-info" id="paypal-info">
                                    <h6><i class="fab fa-paypal me-2"></i>PayPal Payment</h6>
                                    <p class="mb-2">Send <strong>$<?php echo $plan['price']; ?> USD</strong> to:</p>
                                    <code class="d-block p-2 bg-dark rounded mb-2">payment@zyntrade.com</code>
                                    <small class="text-muted">Include your email (<?php echo htmlspecialchars($user['email']); ?>) in the payment note</small>
                                </div>

                                <div class="alert alert-success d-none" id="wise-info">
                                    <h6><i class="fas fa-exchange-alt me-2"></i>Wise Transfer</h6>
                                    <p class="mb-2">Send <strong>$<?php echo $plan['price']; ?> USD</strong> to:</p>
                                    <code class="d-block p-2 bg-dark rounded mb-2">payment@zyntrade.com</code>
                                    <small class="text-muted">Use email as reference</small>
                                </div>

                                <div class="alert alert-warning d-none" id="crypto-info">
                                    <h6><i class="fab fa-bitcoin me-2"></i>Crypto Payment</h6>
                                    <p class="mb-2">Send <strong>$<?php echo $plan['price']; ?> USDT (TRC20)</strong> to:</p>
                                    <code class="d-block p-2 bg-dark rounded mb-2" style="word-break: break-all;">TRC20_WALLET_ADDRESS_HERE</code>
                                    <p class="mb-2 mt-3">Or <strong>BTC equivalent</strong> to:</p>
                                    <code class="d-block p-2 bg-dark rounded mb-2" style="word-break: break-all;">BTC_WALLET_ADDRESS_HERE</code>
                                </div>

                                <div class="alert alert-secondary d-none" id="bank-info">
                                    <h6><i class="fas fa-university me-2"></i>Bank Transfer (Indonesia)</h6>
                                    <p class="mb-2">Transfer <strong>Rp <?php echo number_format($plan['price'] * 15500, 0, ',', '.'); ?></strong> (±$<?php echo $plan['price']; ?>) to:</p>
                                    <table class="table table-sm table-dark mb-0">
                                        <tr><td>Bank</td><td><strong>BCA</strong></td></tr>
                                        <tr><td>Account</td><td><code>1234567890</code></td></tr>
                                        <tr><td>Name</td><td>ZYN TRADE SYSTEM</td></tr>
                                    </table>
                                </div>
                            </div>

                            <!-- Payment Proof Upload -->
                            <div class="mb-4">
                                <label class="form-label">Upload Payment Proof <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" name="payment_proof" accept=".jpg,.jpeg,.png,.pdf" required>
                                <small class="text-muted">Screenshot or PDF of payment confirmation (max 5MB)</small>
                            </div>

                            <!-- Terms -->
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="agree_terms" required>
                                    <label class="form-check-label" for="agree_terms">
                                        I agree to the <a href="terms.php" target="_blank">Terms of Service</a> and <a href="refund.php" target="_blank">Refund Policy</a>
                                    </label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-<?php echo $plan['color']; ?> btn-lg w-100">
                                <i class="fas fa-lock me-2"></i>Submit Payment - $<?php echo $plan['price']; ?>/month
                            </button>

                            <p class="text-center text-muted small mt-3">
                                <i class="fas fa-shield-alt me-1"></i>Your payment is secure. Activation within 1-24 hours after verification.
                            </p>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Help Section -->
                <div class="card mt-4">
                    <div class="card-body text-center">
                        <h6>Need Help?</h6>
                        <p class="small text-muted mb-2">Contact us via Telegram for instant support</p>
                        <a href="https://t.me/<?php echo str_replace('@', '', TELEGRAM_SUPPORT); ?>" class="btn btn-outline-primary" target="_blank">
                            <i class="fab fa-telegram me-2"></i><?php echo TELEGRAM_SUPPORT; ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Toggle payment info based on selection
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('#payment-details .alert').forEach(el => el.classList.add('d-none'));

        const infoMap = {
            'paypal': 'paypal-info',
            'wise': 'wise-info',
            'crypto': 'crypto-info',
            'bank_transfer': 'bank-info'
        };

        document.getElementById(infoMap[this.value]).classList.remove('d-none');
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
