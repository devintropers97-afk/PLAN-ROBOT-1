<?php
$page_title = 'Manage Users';
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
    $user_id = intval($_POST['user_id'] ?? 0);

    if ($user_id > 0) {
        switch ($action) {
            case 'suspend':
                $stmt = $db->prepare("UPDATE users SET status = 'suspended' WHERE id = ? AND role != 'admin'");
                $stmt->execute([$user_id]);
                $message = "User #$user_id suspended.";
                break;

            case 'activate':
                $stmt = $db->prepare("UPDATE users SET status = 'active' WHERE id = ?");
                $stmt->execute([$user_id]);
                $message = "User #$user_id activated.";
                break;

            case 'upgrade':
                $package = $_POST['package'] ?? 'free';
                $days = intval($_POST['days'] ?? 30);
                $expiry = date('Y-m-d H:i:s', strtotime("+$days days"));

                $stmt = $db->prepare("UPDATE users SET package = ?, package_expiry = ? WHERE id = ?");
                $stmt->execute([$package, $expiry, $user_id]);
                $message = "User #$user_id upgraded to " . strtoupper($package) . " for $days days.";
                break;

            case 'reset_password':
                $new_password = bin2hex(random_bytes(4)); // Generate 8 char password
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);

                $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed, $user_id]);
                $message = "Password reset for User #$user_id. New password: <code>$new_password</code>";
                break;
        }
    }
}

// Get filter
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
    $sql .= " AND (fullname LIKE ? OR email LIKE ? OR olymptrade_id LIKE ?)";
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

<section class="section" style="padding-top: calc(var(--navbar-height) + 2rem);">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-users me-2"></i>Manage Users</h2>
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

        <!-- Stats Row -->
        <div class="row g-3 mb-4">
            <div class="col-md-2">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center py-2">
                        <h4 class="mb-0"><?php echo array_sum($counts); ?></h4>
                        <small>Total Users</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-success text-white">
                    <div class="card-body text-center py-2">
                        <h4 class="mb-0"><?php echo $counts['active'] ?? 0; ?></h4>
                        <small>Active</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-warning text-dark">
                    <div class="card-body text-center py-2">
                        <h4 class="mb-0"><?php echo $counts['pending'] ?? 0; ?></h4>
                        <small>Pending</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-info text-white">
                    <div class="card-body text-center py-2">
                        <h4 class="mb-0"><?php echo $packageCounts['pro'] ?? 0; ?></h4>
                        <small>PRO</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-warning text-dark">
                    <div class="card-body text-center py-2">
                        <h4 class="mb-0"><?php echo $packageCounts['elite'] ?? 0; ?></h4>
                        <small>ELITE</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center py-2">
                        <h4 class="mb-0"><?php echo $packageCounts['vip'] ?? 0; ?></h4>
                        <small>VIP</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search & Filter -->
        <div class="card mb-4">
            <div class="card-body py-2">
                <form method="GET" class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search name, email, or OT ID..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="filter" class="form-select">
                            <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                            <option value="active" <?php echo $filter === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="pending" <?php echo $filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="rejected" <?php echo $filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            <option value="suspended" <?php echo $filter === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i>Search
                        </button>
                    </div>
                    <?php if ($search || $filter !== 'all'): ?>
                    <div class="col-md-2">
                        <a href="users.php" class="btn btn-outline-secondary w-100">Clear</a>
                    </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card">
            <div class="card-body">
                <?php if (empty($users)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No users found</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>OlympTrade</th>
                                <th>Country</th>
                                <th>Package</th>
                                <th>Status</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td>#<?php echo $user['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($user['fullname']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                                    <?php if ($user['license_key']): ?>
                                    <br><small class="text-success"><i class="fas fa-key"></i> <?php echo htmlspecialchars($user['license_key']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <code><?php echo htmlspecialchars($user['olymptrade_id']); ?></code>
                                    <br><small class="text-muted"><?php echo ucfirst($user['olymptrade_account_type']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($user['country']); ?></td>
                                <td>
                                    <?php
                                    $packageColors = ['free' => 'secondary', 'pro' => 'info', 'elite' => 'warning', 'vip' => 'danger'];
                                    ?>
                                    <span class="badge bg-<?php echo $packageColors[$user['package']] ?? 'secondary'; ?>">
                                        <?php echo strtoupper($user['package']); ?>
                                    </span>
                                    <?php if ($user['package_expiry'] && $user['package'] !== 'free'): ?>
                                    <br><small class="text-muted">Until <?php echo date('M d', strtotime($user['package_expiry'])); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $statusColors = ['pending' => 'warning', 'active' => 'success', 'rejected' => 'danger', 'suspended' => 'secondary'];
                                    ?>
                                    <span class="badge bg-<?php echo $statusColors[$user['status']] ?? 'secondary'; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                    <?php if ($user['rejection_code']): ?>
                                    <br><small class="text-danger"><?php echo $user['rejection_code']; ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                    <?php if ($user['last_login']): ?>
                                    <br><small class="text-muted">Last: <?php echo date('M d', strtotime($user['last_login'])); ?></small>
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
                                                    <button type="submit" class="dropdown-item text-warning" onclick="return confirm('Suspend this user?')">
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
                                                    <button type="submit" class="dropdown-item" onclick="return confirm('Reset password for this user?')">
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
                                                        <h5 class="modal-title">Upgrade User #<?php echo $user['id']; ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <input type="hidden" name="action" value="upgrade">

                                                        <p><strong>User:</strong> <?php echo htmlspecialchars($user['fullname']); ?></p>
                                                        <p><strong>Current:</strong> <?php echo strtoupper($user['package']); ?></p>

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
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary">Upgrade</button>
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
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
