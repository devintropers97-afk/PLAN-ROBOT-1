<?php
$page_title = __('admin_verify_title') ?: 'Verify Users';
require_once 'includes/admin-header.php';

$message = '';
$messageType = '';

// Handle verify action (POST only for CSRF protection)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_user'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = __('admin_invalid_request');
        $messageType = 'danger';
    } else {
        $userId = intval($_POST['user_id']);
        $licenseKey = verifyUser($userId, $_SESSION['user_id']);

        if ($licenseKey) {
            $message = str_replace(':key', "<code class='text-success'>$licenseKey</code>", __('admin_verify_success'));
            $messageType = 'success';
        } else {
            $message = __('admin_verify_failed');
            $messageType = 'danger';
        }
    }
}

// Handle rejection form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject_user'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = __('admin_invalid_request');
        $messageType = 'danger';
    } else {
        $userId = intval($_POST['user_id']);
        $reasonCode = $_POST['reason_code'] ?? '';
        $customReason = cleanInput($_POST['custom_reason'] ?? '');

        if (empty($reasonCode)) {
            $message = __('admin_select_rejection_reason');
            $messageType = 'danger';
        } elseif (rejectUser($userId, $reasonCode, $customReason)) {
            $message = __('admin_reject_success');
            $messageType = 'success';
        } else {
            $message = __('admin_reject_failed');
            $messageType = 'danger';
        }
    }
}

// Get pending users
$pendingUsers = getPendingUsers();
$pendingCount = count($pendingUsers);

// Rejection reasons - using translation keys
$rejectionReasons = [
    'R01' => __('admin_reject_r01'),
    'R02' => __('admin_reject_r02'),
    'R03' => __('admin_reject_r03'),
    'R04' => __('admin_reject_r04'),
    'R05' => __('admin_reject_r05'),
    'R06' => __('admin_reject_r06'),
    'R07' => __('admin_reject_r07'),
    'R08' => __('admin_reject_r08'),
    'R09' => __('admin_reject_r09'),
    'R10' => __('admin_reject_r10')
];

// Helper function for time ago
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60) return __('admin_just_now') ?: 'Just now';
    if ($diff < 3600) return str_replace(':n', floor($diff / 60), __('admin_n_min_ago'));
    if ($diff < 86400) return str_replace(':n', floor($diff / 3600), __('admin_n_hours_ago'));
    if ($diff < 604800) return str_replace(':n', floor($diff / 86400), __('admin_n_days_ago'));
    return date('M d, Y', $time);
}
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-user-check"></i> <?php _e('admin_verify_title'); ?></h1>
        <p class="page-subtitle"><?php echo str_replace(':count', $pendingCount, __('admin_pending_verification_count')); ?></p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i><?php _e('admin_back_dashboard'); ?>
    </a>
</div>

<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType; ?> fade-in">
    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
    <span><?php echo $message; ?></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (empty($pendingUsers)): ?>
<!-- Empty State -->
<div class="admin-card fade-in">
    <div class="admin-card-body">
        <div class="empty-state">
            <div class="empty-state-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--success);">
                <i class="fas fa-check-circle"></i>
            </div>
            <h4 class="empty-state-title"><?php _e('admin_all_caught_up'); ?></h4>
            <p class="empty-state-desc"><?php _e('admin_no_pending_users'); ?></p>
            <a href="users.php" class="btn btn-outline-primary mt-3"><?php _e('admin_view_all_users'); ?></a>
        </div>
    </div>
</div>
<?php else: ?>
<!-- Pending Users Table -->
<div class="admin-card fade-in">
    <div class="admin-card-header">
        <h5 class="admin-card-title"><i class="fas fa-clock"></i> <?php _e('admin_pending_verifications'); ?></h5>
        <span class="badge badge-warning"><?php echo $pendingCount; ?> <?php _e('admin_status_pending'); ?></span>
    </div>
    <div class="admin-card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><?php _e('admin_user_details'); ?></th>
                        <th>OlympTrade ID</th>
                        <th><?php _e('admin_th_country'); ?></th>
                        <th><?php _e('admin_th_registered'); ?></th>
                        <th><?php _e('admin_th_actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingUsers as $index => $user): ?>
                    <tr>
                        <td><span class="badge badge-secondary"><?php echo $index + 1; ?></span></td>
                        <td>
                            <div class="user-cell">
                                <span class="user-name"><?php echo htmlspecialchars($user['fullname']); ?></span>
                                <span class="user-email"><?php echo htmlspecialchars($user['email']); ?></span>
                                <?php if ($user['phone']): ?>
                                <span class="user-email"><i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($user['phone']); ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <code style="font-size: 1rem; padding: 0.35rem 0.65rem; background: rgba(var(--primary-rgb), 0.1); border-radius: 6px;">
                                <?php echo htmlspecialchars($user['olymptrade_id']); ?>
                            </code>
                        </td>
                        <td>
                            <span class="d-flex align-items-center gap-2">
                                <i class="fas fa-globe text-muted"></i>
                                <?php echo htmlspecialchars($user['country']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="d-block"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                            <span class="text-muted small"><?php echo timeAgo($user['created_at']); ?></span>
                        </td>
                        <td>
                            <div class="action-btns">
                                <form method="POST" class="d-inline" onsubmit="return confirm('<?php echo addslashes(__('admin_confirm_approve')); ?>');">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="verify_user" value="1">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="btn-approve">
                                        <i class="fas fa-check"></i> <?php _e('admin_btn_approve'); ?>
                                    </button>
                                </form>
                                <button type="button"
                                        class="btn-reject"
                                        data-bs-toggle="modal"
                                        data-bs-target="#rejectModal<?php echo $user['id']; ?>">
                                    <i class="fas fa-times"></i> <?php _e('admin_btn_reject'); ?>
                                </button>
                            </div>

                            <!-- Reject Modal -->
                            <div class="modal fade" id="rejectModal<?php echo $user['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST">
                                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                            <input type="hidden" name="reject_user" value="1">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">

                                            <div class="modal-header">
                                                <h5 class="modal-title"><i class="fas fa-user-times me-2 text-danger"></i><?php _e('admin_reject_user'); ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3 p-3" style="background: rgba(var(--primary-rgb), 0.05); border-radius: 8px;">
                                                    <p class="mb-1"><strong><?php _e('admin_th_user'); ?>:</strong> <?php echo htmlspecialchars($user['fullname']); ?></p>
                                                    <p class="mb-0"><strong>OlympTrade ID:</strong> <code><?php echo htmlspecialchars($user['olymptrade_id']); ?></code></p>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label"><?php _e('admin_rejection_reason'); ?> <span class="text-danger">*</span></label>
                                                    <select class="form-select" name="reason_code" required onchange="toggleCustomReason(this, <?php echo $user['id']; ?>)">
                                                        <option value=""><?php _e('admin_select_reason'); ?></option>
                                                        <?php foreach ($rejectionReasons as $code => $reason): ?>
                                                        <option value="<?php echo $code; ?>"><?php echo $code; ?> - <?php echo $reason; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <div class="mb-3" id="customReasonDiv<?php echo $user['id']; ?>" style="display: none;">
                                                    <label class="form-label"><?php _e('admin_custom_reason'); ?></label>
                                                    <textarea class="form-control" name="custom_reason" rows="3" placeholder="<?php echo __('admin_enter_custom_reason'); ?>"></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?php _e('admin_cancel'); ?></button>
                                                <button type="submit" class="btn btn-danger">
                                                    <i class="fas fa-times me-2"></i><?php _e('admin_reject_user'); ?>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Verification Guidelines -->
<div class="admin-card guidelines-card mt-4 fade-in">
    <div class="admin-card-header">
        <h5 class="admin-card-title"><i class="fas fa-info-circle"></i> <?php _e('admin_verification_guidelines'); ?></h5>
    </div>
    <div class="admin-card-body">
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-success mb-3"><i class="fas fa-check-circle me-2"></i><?php _e('admin_approve_if'); ?></h6>
                <ul class="guidelines-list">
                    <li><i class="fas fa-check text-success"></i> <?php _e('admin_guideline_approve_1'); ?></li>
                    <li><i class="fas fa-check text-success"></i> <?php _e('admin_guideline_approve_2'); ?></li>
                    <li><i class="fas fa-check text-success"></i> <?php _e('admin_guideline_approve_3'); ?></li>
                    <li><i class="fas fa-check text-success"></i> <?php _e('admin_guideline_approve_4'); ?></li>
                    <li><i class="fas fa-check text-success"></i> <?php _e('admin_guideline_approve_5'); ?></li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6 class="text-danger mb-3"><i class="fas fa-times-circle me-2"></i><?php _e('admin_reject_if'); ?></h6>
                <ul class="guidelines-list">
                    <li><i class="fas fa-times text-danger"></i> <?php _e('admin_guideline_reject_1'); ?></li>
                    <li><i class="fas fa-times text-danger"></i> <?php _e('admin_guideline_reject_2'); ?></li>
                    <li><i class="fas fa-times text-danger"></i> <?php _e('admin_guideline_reject_3'); ?></li>
                    <li><i class="fas fa-times text-danger"></i> <?php _e('admin_guideline_reject_4'); ?></li>
                    <li><i class="fas fa-times text-danger"></i> <?php _e('admin_guideline_reject_5'); ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function toggleCustomReason(select, userId) {
    const customDiv = document.getElementById('customReasonDiv' + userId);
    customDiv.style.display = select.value === 'R10' ? 'block' : 'none';
}
</script>

<?php require_once 'includes/admin-footer.php'; ?>
