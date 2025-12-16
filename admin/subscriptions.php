<?php
$page_title = 'Manage Subscriptions';
require_once '../includes/header.php';

// Admin only
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$db = getDBConnection();
$message = '';
$error = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    $sub_id = intval($_POST['subscription_id'] ?? 0);

    if ($sub_id > 0) {
        switch ($action) {
            case 'approve':
                // Get subscription details
                $stmt = $db->prepare("SELECT * FROM subscriptions WHERE id = ?");
                $stmt->execute([$sub_id]);
                $sub = $stmt->fetch();

                if ($sub && $sub['status'] === 'pending') {
                    $starts_at = date('Y-m-d H:i:s');
                    $expires_at = date('Y-m-d H:i:s', strtotime('+1 month'));

                    // Update subscription
                    $stmt = $db->prepare("
                        UPDATE subscriptions
                        SET status = 'active', starts_at = ?, expires_at = ?, verified_by = ?, verified_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([$starts_at, $expires_at, $_SESSION['user_id'], $sub_id]);

                    // Update user package
                    $stmt = $db->prepare("
                        UPDATE users
                        SET package = ?, package_expiry = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$sub['plan'], $expires_at, $sub['user_id']]);

                    // Generate license key if not exists
                    $stmt = $db->prepare("SELECT license_key FROM users WHERE id = ?");
                    $stmt->execute([$sub['user_id']]);
                    $user = $stmt->fetch();

                    if (empty($user['license_key'])) {
                        $prefix = 'ZYN-' . strtoupper(substr($sub['plan'], 0, 1));
                        $new_key = $prefix . '-' . strtoupper(substr(md5(uniqid()), 0, 4)) . '-' . strtoupper(substr(md5(rand()), 0, 4));

                        $stmt = $db->prepare("UPDATE users SET license_key = ? WHERE id = ?");
                        $stmt->execute([$new_key, $sub['user_id']]);
                    }

                    // Send notification
                    $stmt = $db->prepare("
                        INSERT INTO notifications (user_id, type, message)
                        VALUES (?, 'subscription', ?)
                    ");
                    $stmt->execute([
                        $sub['user_id'],
                        "Your {$sub['plan']} subscription has been activated! Valid until " . date('M d, Y', strtotime($expires_at))
                    ]);

                    $message = "Subscription #$sub_id approved successfully!";
                }
                break;

            case 'reject':
                $reason = cleanInput($_POST['rejection_reason'] ?? 'Payment not verified');

                $stmt = $db->prepare("
                    UPDATE subscriptions
                    SET status = 'rejected', rejection_reason = ?, verified_by = ?, verified_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$reason, $_SESSION['user_id'], $sub_id]);

                // Send notification
                $stmt = $db->prepare("SELECT user_id FROM subscriptions WHERE id = ?");
                $stmt->execute([$sub_id]);
                $sub = $stmt->fetch();

                $stmt = $db->prepare("
                    INSERT INTO notifications (user_id, type, message)
                    VALUES (?, 'subscription', ?)
                ");
                $stmt->execute([
                    $sub['user_id'],
                    "Your subscription request was rejected. Reason: $reason. Please contact support."
                ]);

                $message = "Subscription #$sub_id rejected.";
                break;

            case 'extend':
                $days = intval($_POST['extend_days'] ?? 30);

                $stmt = $db->prepare("
                    UPDATE subscriptions
                    SET expires_at = DATE_ADD(expires_at, INTERVAL ? DAY)
                    WHERE id = ?
                ");
                $stmt->execute([$days, $sub_id]);

                // Also update user
                $stmt = $db->prepare("SELECT user_id, expires_at FROM subscriptions WHERE id = ?");
                $stmt->execute([$sub_id]);
                $sub = $stmt->fetch();

                $stmt = $db->prepare("UPDATE users SET package_expiry = ? WHERE id = ?");
                $stmt->execute([$sub['expires_at'], $sub['user_id']]);

                $message = "Subscription #$sub_id extended by $days days.";
                break;
        }
    }
}

// Get filter
$filter = $_GET['filter'] ?? 'pending';

// Get subscriptions
$sql = "
    SELECT s.*, u.fullname, u.email, u.olymptrade_id
    FROM subscriptions s
    JOIN users u ON s.user_id = u.id
";
if ($filter !== 'all') {
    $sql .= " WHERE s.status = ?";
}
$sql .= " ORDER BY s.created_at DESC";

$stmt = $db->prepare($sql);
if ($filter !== 'all') {
    $stmt->execute([$filter]);
} else {
    $stmt->execute();
}
$subscriptions = $stmt->fetchAll();

// Count by status
$stmt = $db->query("SELECT status, COUNT(*) as count FROM subscriptions GROUP BY status");
$counts = [];
while ($row = $stmt->fetch()) {
    $counts[$row['status']] = $row['count'];
}
?>

<section class="section" style="padding-top: calc(var(--navbar-height) + 2rem);">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-credit-card me-2"></i>Manage Subscriptions</h2>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Filter Tabs -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link <?php echo $filter === 'pending' ? 'active' : ''; ?>" href="?filter=pending">
                    <i class="fas fa-clock me-1"></i>Pending
                    <span class="badge bg-warning"><?php echo $counts['pending'] ?? 0; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $filter === 'active' ? 'active' : ''; ?>" href="?filter=active">
                    <i class="fas fa-check me-1"></i>Active
                    <span class="badge bg-success"><?php echo $counts['active'] ?? 0; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $filter === 'rejected' ? 'active' : ''; ?>" href="?filter=rejected">
                    <i class="fas fa-times me-1"></i>Rejected
                    <span class="badge bg-danger"><?php echo $counts['rejected'] ?? 0; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $filter === 'expired' ? 'active' : ''; ?>" href="?filter=expired">
                    <i class="fas fa-calendar-times me-1"></i>Expired
                    <span class="badge bg-secondary"><?php echo $counts['expired'] ?? 0; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $filter === 'all' ? 'active' : ''; ?>" href="?filter=all">
                    <i class="fas fa-list me-1"></i>All
                </a>
            </li>
        </ul>

        <!-- Subscriptions Table -->
        <div class="card">
            <div class="card-body">
                <?php if (empty($subscriptions)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No subscriptions found</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Plan</th>
                                <th>Amount</th>
                                <th>Payment</th>
                                <th>Proof</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subscriptions as $sub): ?>
                            <tr>
                                <td>#<?php echo $sub['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($sub['fullname']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($sub['email']); ?></small><br>
                                    <small class="text-info">OT: <?php echo htmlspecialchars($sub['olymptrade_id']); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-<?php
                                        echo $sub['plan'] === 'vip' ? 'primary' :
                                            ($sub['plan'] === 'elite' ? 'warning' : 'info');
                                    ?>">
                                        <?php echo strtoupper($sub['plan']); ?>
                                    </span>
                                </td>
                                <td><strong>$<?php echo number_format($sub['amount'], 2); ?></strong></td>
                                <td>
                                    <?php
                                    $icons = [
                                        'paypal' => 'fab fa-paypal text-primary',
                                        'wise' => 'fas fa-exchange-alt text-success',
                                        'crypto' => 'fab fa-bitcoin text-warning',
                                        'bank_transfer' => 'fas fa-university text-info'
                                    ];
                                    $icon = $icons[$sub['payment_method']] ?? 'fas fa-credit-card';
                                    ?>
                                    <i class="<?php echo $icon; ?> me-1"></i>
                                    <?php echo ucfirst(str_replace('_', ' ', $sub['payment_method'])); ?>
                                </td>
                                <td>
                                    <?php if ($sub['payment_proof']): ?>
                                    <a href="../<?php echo htmlspecialchars($sub['payment_proof']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-image me-1"></i>View
                                    </a>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'active' => 'success',
                                        'rejected' => 'danger',
                                        'expired' => 'secondary',
                                        'cancelled' => 'dark'
                                    ];
                                    ?>
                                    <span class="badge bg-<?php echo $statusColors[$sub['status']] ?? 'secondary'; ?>">
                                        <?php echo ucfirst($sub['status']); ?>
                                    </span>
                                    <?php if ($sub['status'] === 'active' && $sub['expires_at']): ?>
                                    <br><small class="text-muted">Until <?php echo date('M d', strtotime($sub['expires_at'])); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo date('M d, Y', strtotime($sub['created_at'])); ?><br>
                                    <small class="text-muted"><?php echo date('H:i', strtotime($sub['created_at'])); ?></small>
                                </td>
                                <td>
                                    <?php if ($sub['status'] === 'pending'): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                        <input type="hidden" name="subscription_id" value="<?php echo $sub['id']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve this subscription?')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal<?php echo $sub['id']; ?>">
                                        <i class="fas fa-times"></i>
                                    </button>

                                    <!-- Reject Modal -->
                                    <div class="modal fade" id="rejectModal<?php echo $sub['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Reject Subscription #<?php echo $sub['id']; ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                        <input type="hidden" name="subscription_id" value="<?php echo $sub['id']; ?>">
                                                        <input type="hidden" name="action" value="reject">
                                                        <div class="mb-3">
                                                            <label class="form-label">Rejection Reason</label>
                                                            <select name="rejection_reason" class="form-select">
                                                                <option value="Payment not verified">Payment not verified</option>
                                                                <option value="Invalid payment proof">Invalid payment proof</option>
                                                                <option value="Amount mismatch">Amount mismatch</option>
                                                                <option value="Duplicate submission">Duplicate submission</option>
                                                                <option value="Other">Other</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger">Reject</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php elseif ($sub['status'] === 'active'): ?>
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#extendModal<?php echo $sub['id']; ?>">
                                        <i class="fas fa-calendar-plus"></i> Extend
                                    </button>

                                    <!-- Extend Modal -->
                                    <div class="modal fade" id="extendModal<?php echo $sub['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Extend Subscription #<?php echo $sub['id']; ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                        <input type="hidden" name="subscription_id" value="<?php echo $sub['id']; ?>">
                                                        <input type="hidden" name="action" value="extend">
                                                        <div class="mb-3">
                                                            <label class="form-label">Extend by (days)</label>
                                                            <select name="extend_days" class="form-select">
                                                                <option value="7">7 days</option>
                                                                <option value="14">14 days</option>
                                                                <option value="30" selected>30 days (1 month)</option>
                                                                <option value="90">90 days (3 months)</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary">Extend</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
