<?php
$page_title = __('admin_subscriptions');
require_once 'includes/admin-header.php';

$db = getDBConnection();
$message = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    $sub_id = intval($_POST['subscription_id'] ?? 0);

    if ($sub_id > 0) {
        switch ($action) {
            case 'approve':
                $stmt = $db->prepare("SELECT * FROM subscriptions WHERE id = ?");
                $stmt->execute([$sub_id]);
                $sub = $stmt->fetch();

                if ($sub && $sub['status'] === 'pending') {
                    $starts_at = date('Y-m-d H:i:s');
                    $expires_at = date('Y-m-d H:i:s', strtotime('+1 month'));

                    $stmt = $db->prepare("
                        UPDATE subscriptions
                        SET status = 'active', starts_at = ?, expires_at = ?, verified_by = ?, verified_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([$starts_at, $expires_at, $_SESSION['user_id'], $sub_id]);

                    $stmt = $db->prepare("
                        UPDATE users
                        SET package = ?, package_expiry = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$sub['plan'], $expires_at, $sub['user_id']]);

                    $stmt = $db->prepare("SELECT license_key FROM users WHERE id = ?");
                    $stmt->execute([$sub['user_id']]);
                    $user = $stmt->fetch();

                    if (empty($user['license_key'])) {
                        $prefix = 'ZYN-' . strtoupper(substr($sub['plan'], 0, 1));
                        $new_key = $prefix . '-' . strtoupper(substr(md5(uniqid()), 0, 4)) . '-' . strtoupper(substr(md5(rand()), 0, 4));

                        $stmt = $db->prepare("UPDATE users SET license_key = ? WHERE id = ?");
                        $stmt->execute([$new_key, $sub['user_id']]);
                    }

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

// Revenue stats
$stmt = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM subscriptions WHERE status = 'active'");
$totalRevenue = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM subscriptions WHERE status = 'active' AND MONTH(created_at) = MONTH(CURDATE())");
$monthlyRevenue = $stmt->fetch()['total'];
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-credit-card"></i> Manage Subscriptions</h1>
        <p class="page-subtitle"><?php echo array_sum($counts); ?> total subscriptions</p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
    </a>
</div>

<?php if ($message): ?>
<div class="alert alert-success fade-in">
    <i class="fas fa-check-circle"></i>
    <span><?php echo $message; ?></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Stats Row -->
<div class="stat-grid" style="grid-template-columns: repeat(6, 1fr);">
    <div class="stat-card warning fade-in">
        <div class="stat-value"><?php echo $counts['pending'] ?? 0; ?></div>
        <div class="stat-label">Pending</div>
    </div>
    <div class="stat-card success fade-in">
        <div class="stat-value"><?php echo $counts['active'] ?? 0; ?></div>
        <div class="stat-label">Active</div>
    </div>
    <div class="stat-card danger fade-in">
        <div class="stat-value"><?php echo $counts['rejected'] ?? 0; ?></div>
        <div class="stat-label">Rejected</div>
    </div>
    <div class="stat-card secondary fade-in">
        <div class="stat-value"><?php echo $counts['expired'] ?? 0; ?></div>
        <div class="stat-label">Expired</div>
    </div>
    <div class="stat-card primary fade-in">
        <div class="stat-value">$<?php echo number_format($monthlyRevenue); ?></div>
        <div class="stat-label">This Month</div>
    </div>
    <div class="stat-card info fade-in">
        <div class="stat-value">$<?php echo number_format($totalRevenue); ?></div>
        <div class="stat-label">Total Revenue</div>
    </div>
</div>

<!-- Filter Tabs -->
<div class="admin-card mb-4 fade-in">
    <div class="admin-card-body py-2">
        <ul class="nav nav-pills gap-2">
            <li class="nav-item">
                <a class="nav-link <?php echo $filter === 'pending' ? 'active' : ''; ?>" href="?filter=pending">
                    <i class="fas fa-clock me-1"></i>Pending
                    <span class="badge badge-warning ms-1"><?php echo $counts['pending'] ?? 0; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $filter === 'active' ? 'active' : ''; ?>" href="?filter=active">
                    <i class="fas fa-check me-1"></i>Active
                    <span class="badge badge-success ms-1"><?php echo $counts['active'] ?? 0; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $filter === 'rejected' ? 'active' : ''; ?>" href="?filter=rejected">
                    <i class="fas fa-times me-1"></i>Rejected
                    <span class="badge badge-danger ms-1"><?php echo $counts['rejected'] ?? 0; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $filter === 'expired' ? 'active' : ''; ?>" href="?filter=expired">
                    <i class="fas fa-calendar-times me-1"></i>Expired
                    <span class="badge badge-secondary ms-1"><?php echo $counts['expired'] ?? 0; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $filter === 'all' ? 'active' : ''; ?>" href="?filter=all">
                    <i class="fas fa-list me-1"></i>All
                </a>
            </li>
        </ul>
    </div>
</div>

<!-- Subscriptions Table -->
<div class="admin-card fade-in">
    <div class="admin-card-header">
        <h5 class="admin-card-title"><i class="fas fa-list"></i> Subscriptions</h5>
        <span class="badge badge-primary"><?php echo count($subscriptions); ?> results</span>
    </div>
    <div class="admin-card-body" style="padding: 0;">
        <?php if (empty($subscriptions)): ?>
        <div class="empty-state">
            <div class="empty-state-icon"><i class="fas fa-inbox"></i></div>
            <h4 class="empty-state-title">No Subscriptions Found</h4>
            <p class="empty-state-desc">No subscriptions match your filter criteria.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table">
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
                        <td><span class="badge badge-secondary">#<?php echo $sub['id']; ?></span></td>
                        <td>
                            <div class="user-cell">
                                <span class="user-name"><?php echo htmlspecialchars($sub['fullname']); ?></span>
                                <span class="user-email"><?php echo htmlspecialchars($sub['email']); ?></span>
                                <span class="user-license"><i class="fas fa-id-card me-1"></i><?php echo htmlspecialchars($sub['olymptrade_id']); ?></span>
                            </div>
                        </td>
                        <td>
                            <?php $planColors = ['vip' => 'primary', 'elite' => 'warning', 'pro' => 'info', 'free' => 'secondary']; ?>
                            <span class="badge badge-<?php echo $planColors[$sub['plan']] ?? 'secondary'; ?>">
                                <?php echo strtoupper($sub['plan']); ?>
                            </span>
                        </td>
                        <td><strong class="text-success">$<?php echo number_format($sub['amount'], 2); ?></strong></td>
                        <td>
                            <?php
                            $paymentIcons = [
                                'paypal' => ['icon' => 'fab fa-paypal', 'color' => 'primary'],
                                'wise' => ['icon' => 'fas fa-exchange-alt', 'color' => 'success'],
                                'crypto' => ['icon' => 'fab fa-bitcoin', 'color' => 'warning'],
                                'bank_transfer' => ['icon' => 'fas fa-university', 'color' => 'info']
                            ];
                            $payment = $paymentIcons[$sub['payment_method']] ?? ['icon' => 'fas fa-credit-card', 'color' => 'secondary'];
                            ?>
                            <span class="d-flex align-items-center gap-2">
                                <i class="<?php echo $payment['icon']; ?> text-<?php echo $payment['color']; ?>"></i>
                                <?php echo ucfirst(str_replace('_', ' ', $sub['payment_method'])); ?>
                            </span>
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
                            <?php $statusColors = ['pending' => 'warning', 'active' => 'success', 'rejected' => 'danger', 'expired' => 'secondary', 'cancelled' => 'dark']; ?>
                            <span class="badge badge-<?php echo $statusColors[$sub['status']] ?? 'secondary'; ?>">
                                <?php echo ucfirst($sub['status']); ?>
                            </span>
                            <?php if ($sub['status'] === 'active' && $sub['expires_at']): ?>
                            <br><small class="text-muted">Until <?php echo date('M d', strtotime($sub['expires_at'])); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="d-block"><?php echo date('M d, Y', strtotime($sub['created_at'])); ?></span>
                            <small class="text-muted"><?php echo date('H:i', strtotime($sub['created_at'])); ?></small>
                        </td>
                        <td>
                            <?php if ($sub['status'] === 'pending'): ?>
                            <div class="action-btns">
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="subscription_id" value="<?php echo $sub['id']; ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn-approve" data-confirm="Approve this subscription?">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                </form>
                                <button type="button" class="btn-reject" data-bs-toggle="modal" data-bs-target="#rejectModal<?php echo $sub['id']; ?>">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </div>

                            <!-- Reject Modal -->
                            <div class="modal fade" id="rejectModal<?php echo $sub['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST">
                                            <div class="modal-header">
                                                <h5 class="modal-title"><i class="fas fa-times-circle me-2 text-danger"></i>Reject Subscription</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                <input type="hidden" name="subscription_id" value="<?php echo $sub['id']; ?>">
                                                <input type="hidden" name="action" value="reject">

                                                <div class="mb-3 p-3" style="background: rgba(var(--primary-rgb), 0.05); border-radius: 8px;">
                                                    <p class="mb-1"><strong>User:</strong> <?php echo htmlspecialchars($sub['fullname']); ?></p>
                                                    <p class="mb-0"><strong>Plan:</strong> <span class="badge badge-<?php echo $planColors[$sub['plan']] ?? 'secondary'; ?>"><?php echo strtoupper($sub['plan']); ?></span> - $<?php echo number_format($sub['amount'], 2); ?></p>
                                                </div>

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
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger">
                                                    <i class="fas fa-times me-2"></i>Reject
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php elseif ($sub['status'] === 'active'): ?>
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#extendModal<?php echo $sub['id']; ?>">
                                <i class="fas fa-calendar-plus me-1"></i>Extend
                            </button>

                            <!-- Extend Modal -->
                            <div class="modal fade" id="extendModal<?php echo $sub['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST">
                                            <div class="modal-header">
                                                <h5 class="modal-title"><i class="fas fa-calendar-plus me-2 text-primary"></i>Extend Subscription</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                <input type="hidden" name="subscription_id" value="<?php echo $sub['id']; ?>">
                                                <input type="hidden" name="action" value="extend">

                                                <div class="mb-3 p-3" style="background: rgba(var(--primary-rgb), 0.05); border-radius: 8px;">
                                                    <p class="mb-1"><strong>User:</strong> <?php echo htmlspecialchars($sub['fullname']); ?></p>
                                                    <p class="mb-1"><strong>Current Plan:</strong> <span class="badge badge-<?php echo $planColors[$sub['plan']] ?? 'secondary'; ?>"><?php echo strtoupper($sub['plan']); ?></span></p>
                                                    <p class="mb-0"><strong>Expires:</strong> <?php echo date('M d, Y', strtotime($sub['expires_at'])); ?></p>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Extend by</label>
                                                    <select name="extend_days" class="form-select">
                                                        <option value="7">7 days</option>
                                                        <option value="14">14 days</option>
                                                        <option value="30" selected>30 days (1 month)</option>
                                                        <option value="90">90 days (3 months)</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-calendar-plus me-2"></i>Extend
                                                </button>
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

<style>
.nav-pills .nav-link {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
    color: var(--text-secondary);
    padding: 0.5rem 1rem;
    border-radius: 8px;
    transition: all 0.3s ease;
}
.nav-pills .nav-link:hover {
    background: rgba(255, 255, 255, 0.06);
    color: var(--text-primary);
}
.nav-pills .nav-link.active {
    background: var(--primary);
    border-color: var(--primary);
    color: #fff;
}
</style>

<?php require_once 'includes/admin-footer.php'; ?>
