<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/language.php';

$page_title = __('admin_users_title');
require_once 'includes/admin-header.php';

$db = getDBConnection();
$message = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    $user_id = intval($_POST['user_id'] ?? 0);

    if ($user_id > 0) {
        switch ($action) {
            case 'suspend':
                $stmt = $db->prepare("UPDATE users SET status = 'suspended' WHERE id = ? AND role != 'admin'");
                $stmt->execute([$user_id]);
                $message = __('admin_user_suspended') . " #$user_id.";
                break;

            case 'activate':
                $stmt = $db->prepare("UPDATE users SET status = 'active' WHERE id = ?");
                $stmt->execute([$user_id]);
                $message = __('admin_user_activated') . " #$user_id.";
                break;

            case 'upgrade':
                $package = $_POST['package'] ?? 'free';
                $days = intval($_POST['days'] ?? 30);
                $expiry = date('Y-m-d H:i:s', strtotime("+$days days"));

                $stmt = $db->prepare("UPDATE users SET package = ?, package_expiry = ? WHERE id = ?");
                $stmt->execute([$package, $expiry, $user_id]);
                $message = __('admin_user_upgraded') . " " . strtoupper($package) . " " . __('admin_for') . " $days " . __('admin_days') . " (User #$user_id)";
                break;

            case 'reset_password':
                $new_password = bin2hex(random_bytes(4));
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);

                $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed, $user_id]);
                $message = __('admin_password_reset') . " #$user_id. " . __('admin_new_password') . ": <code class='text-warning'>$new_password</code>";
                break;
        }
    }
}

// Get filters
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query
$sql = "SELECT * FROM users WHERE role = 'user'";
$params = [];

if ($filter !== 'all') {
    $sql .= " AND status = ?";
    $params[] = $filter;
}

if ($search) {
    $sql .= " AND (fullname LIKE ? OR email LIKE ? OR olymptrade_id LIKE ? OR license_key LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Count by status
$stmt = $db->query("SELECT status, COUNT(*) as count FROM users WHERE role = 'user' GROUP BY status");
$counts = [];
while ($row = $stmt->fetch()) {
    $counts[$row['status']] = $row['count'];
}

// Package counts
$stmt = $db->query("SELECT package, COUNT(*) as count FROM users WHERE role = 'user' AND status = 'active' GROUP BY package");
$packageCounts = [];
while ($row = $stmt->fetch()) {
    $packageCounts[$row['package']] = $row['count'];
}
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-users"></i> <?php _e('admin_users_title'); ?></h1>
        <p class="page-subtitle"><?php echo array_sum($counts); ?> <?php _e('admin_users_subtitle'); ?></p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i><?php _e('admin_back_dashboard'); ?>
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
    <div class="stat-card primary fade-in">
        <div class="stat-value"><?php echo array_sum($counts); ?></div>
        <div class="stat-label"><?php _e('admin_total_users'); ?></div>
    </div>
    <div class="stat-card success fade-in">
        <div class="stat-value"><?php echo $counts['active'] ?? 0; ?></div>
        <div class="stat-label"><?php _e('admin_active'); ?></div>
    </div>
    <div class="stat-card warning fade-in">
        <div class="stat-value"><?php echo $counts['pending'] ?? 0; ?></div>
        <div class="stat-label"><?php _e('admin_pending'); ?></div>
    </div>
    <div class="stat-card info fade-in">
        <div class="stat-value"><?php echo $packageCounts['pro'] ?? 0; ?></div>
        <div class="stat-label">PRO</div>
    </div>
    <div class="stat-card warning fade-in">
        <div class="stat-value"><?php echo $packageCounts['elite'] ?? 0; ?></div>
        <div class="stat-label">ELITE</div>
    </div>
    <div class="stat-card danger fade-in">
        <div class="stat-value"><?php echo $packageCounts['vip'] ?? 0; ?></div>
        <div class="stat-label">VIP</div>
    </div>
</div>

<!-- Search & Filter -->
<div class="admin-card mb-4 fade-in">
    <div class="admin-card-body py-3">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small"><?php _e('admin_search'); ?></label>
                <input type="text" name="search" class="form-control" placeholder="<?php _e('admin_search_placeholder'); ?>" value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small"><?php _e('admin_status_filter'); ?></label>
                <select name="filter" class="form-select">
                    <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>><?php _e('admin_all_status'); ?></option>
                    <option value="active" <?php echo $filter === 'active' ? 'selected' : ''; ?>><?php _e('admin_active'); ?></option>
                    <option value="pending" <?php echo $filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="rejected" <?php echo $filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    <option value="suspended" <?php echo $filter === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-2"></i><?php _e('admin_search'); ?>
                </button>
            </div>
            <?php if ($search || $filter !== 'all'): ?>
            <div class="col-md-2">
                <a href="users.php" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-times me-2"></i><?php _e('admin_reset'); ?>
                </a>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="admin-card fade-in">
    <div class="admin-card-header">
        <h5 class="admin-card-title"><i class="fas fa-list"></i> <?php _e('admin_user_list'); ?></h5>
        <span class="badge badge-primary"><?php echo count($users); ?> results</span>
    </div>
    <div class="admin-card-body" style="padding: 0;">
        <?php if (empty($users)): ?>
        <div class="empty-state">
            <div class="empty-state-icon"><i class="fas fa-users"></i></div>
            <h4 class="empty-state-title"><?php _e('admin_no_users'); ?></h4>
            <p class="empty-state-desc"><?php _e('admin_no_users'); ?></p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th><?php _e('admin_user'); ?></th>
                        <th>OlympTrade</th>
                        <th>Country</th>
                        <th><?php _e('admin_package'); ?></th>
                        <th><?php _e('admin_status'); ?></th>
                        <th><?php _e('admin_registered'); ?></th>
                        <th><?php _e('admin_actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><span class="badge badge-secondary">#<?php echo $user['id']; ?></span></td>
                        <td>
                            <div class="user-cell">
                                <span class="user-name"><?php echo htmlspecialchars($user['fullname']); ?></span>
                                <span class="user-email"><?php echo htmlspecialchars($user['email']); ?></span>
                                <?php if ($user['license_key']): ?>
                                <span class="user-license"><i class="fas fa-key me-1"></i><?php echo htmlspecialchars($user['license_key']); ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <code><?php echo htmlspecialchars($user['olymptrade_id']); ?></code>
                            <br><small class="text-muted"><?php echo ucfirst($user['olymptrade_account_type'] ?? 'real'); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($user['country']); ?></td>
                        <td>
                            <?php $pkgColors = ['free' => 'secondary', 'pro' => 'info', 'elite' => 'warning', 'vip' => 'primary']; ?>
                            <span class="badge badge-<?php echo $pkgColors[$user['package']] ?? 'secondary'; ?>">
                                <?php echo strtoupper($user['package']); ?>
                            </span>
                            <?php if ($user['package_expiry'] && $user['package'] !== 'free'): ?>
                            <br><small class="text-muted">Until <?php echo date('M d', strtotime($user['package_expiry'])); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php $statusColors = ['pending' => 'warning', 'active' => 'success', 'rejected' => 'danger', 'suspended' => 'secondary']; ?>
                            <span class="badge badge-<?php echo $statusColors[$user['status']] ?? 'secondary'; ?>">
                                <?php echo ucfirst($user['status']); ?>
                            </span>
                            <?php if ($user['rejection_code']): ?>
                            <br><small class="text-danger"><?php echo $user['rejection_code']; ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="d-block"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                            <?php if ($user['last_login']): ?>
                            <small class="text-muted">Last: <?php echo date('M d', strtotime($user['last_login'])); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                    Actions
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <?php if ($user['status'] === 'pending'): ?>
                                    <li>
                                        <a class="dropdown-item text-success" href="verify-users.php?action=verify&id=<?php echo $user['id']; ?>">
                                            <i class="fas fa-check me-2"></i>Verify
                                        </a>
                                    </li>
                                    <?php endif; ?>

                                    <?php if ($user['status'] === 'active'): ?>
                                    <li>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="action" value="suspend">
                                            <button type="submit" class="dropdown-item text-warning" data-confirm="Suspend this user?">
                                                <i class="fas fa-ban me-2"></i>Suspend
                                            </button>
                                        </form>
                                    </li>
                                    <?php elseif ($user['status'] === 'suspended'): ?>
                                    <li>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="action" value="activate">
                                            <button type="submit" class="dropdown-item text-success">
                                                <i class="fas fa-check me-2"></i>Activate
                                            </button>
                                        </form>
                                    </li>
                                    <?php endif; ?>

                                    <li><hr class="dropdown-divider"></li>

                                    <li>
                                        <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#upgradeModal<?php echo $user['id']; ?>">
                                            <i class="fas fa-arrow-up me-2"></i>Upgrade Package
                                        </button>
                                    </li>

                                    <li>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="action" value="reset_password">
                                            <button type="submit" class="dropdown-item" data-confirm="Reset password for this user?">
                                                <i class="fas fa-key me-2"></i>Reset Password
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>

                            <!-- Upgrade Modal -->
                            <div class="modal fade" id="upgradeModal<?php echo $user['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST">
                                            <div class="modal-header">
                                                <h5 class="modal-title"><i class="fas fa-arrow-up me-2 text-primary"></i>Upgrade User</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="action" value="upgrade">

                                                <div class="mb-3 p-3" style="background: rgba(var(--primary-rgb), 0.05); border-radius: 8px;">
                                                    <p class="mb-1"><strong>User:</strong> <?php echo htmlspecialchars($user['fullname']); ?></p>
                                                    <p class="mb-0"><strong>Current Package:</strong> <span class="badge badge-<?php echo $pkgColors[$user['package']] ?? 'secondary'; ?>"><?php echo strtoupper($user['package']); ?></span></p>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">New Package</label>
                                                    <select name="package" class="form-select">
                                                        <option value="free">FREE</option>
                                                        <option value="pro">PRO ($29/mo)</option>
                                                        <option value="elite">ELITE ($79/mo)</option>
                                                        <option value="vip">VIP ($149/mo)</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Duration</label>
                                                    <select name="days" class="form-select">
                                                        <option value="7">7 days</option>
                                                        <option value="14">14 days</option>
                                                        <option value="30" selected>30 days (1 month)</option>
                                                        <option value="90">90 days (3 months)</option>
                                                        <option value="365">365 days (1 year)</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-arrow-up me-2"></i>Upgrade
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
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
