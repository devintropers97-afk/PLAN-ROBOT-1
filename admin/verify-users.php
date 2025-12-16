<?php
$page_title = 'Verify Users';
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Require admin login
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$message = '';
$messageType = '';

// Handle actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $userId = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === 'verify') {
        if (verifyUser($userId, $_SESSION['user_id'])) {
            $message = 'User verified successfully!';
            $messageType = 'success';
        } else {
            $message = 'Failed to verify user.';
            $messageType = 'danger';
        }
    }
}

// Handle rejection form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject_user'])) {
    $userId = intval($_POST['user_id']);
    $reasonCode = $_POST['reason_code'];
    $customReason = cleanInput($_POST['custom_reason'] ?? '');

    if (rejectUser($userId, $reasonCode, $customReason)) {
        $message = 'User rejected successfully.';
        $messageType = 'success';
    } else {
        $message = 'Failed to reject user.';
        $messageType = 'danger';
    }
}

// Get pending users
$pendingUsers = getPendingUsers();

// Rejection reasons
$rejectionReasons = [
    'R01' => 'ID tidak ditemukan / ID not found',
    'R02' => 'Tidak terdaftar via link afiliasi resmi / Not registered via official affiliate',
    'R03' => 'Deposit di bawah $10 / Deposit below $10',
    'R04' => 'ID sudah digunakan akun lain / ID already used by another account',
    'R05' => 'Data tidak lengkap / Incomplete data',
    'R06' => 'Akun OlympTrade tidak aktif / OlympTrade account inactive',
    'R07' => 'Negara tidak sesuai / Country mismatch',
    'R08' => 'Screenshot tidak valid / Invalid screenshot',
    'R09' => 'Duplikat akun terdeteksi / Duplicate account detected',
    'R10' => 'Alasan lain / Custom reason'
];

// Get pending count
$db = getDBConnection();
$stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE status = 'pending'");
$pendingCount = $stmt->fetch()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="dashboard-page">
    <!-- Admin Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <span class="brand-logo">ZYN</span>
                <span class="brand-text">Admin Panel</span>
            </a>

            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../dashboard.php">
                    <i class="fas fa-home"></i> User Dashboard
                </a>
                <a class="nav-link" href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4" style="margin-top: 80px;">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-2">
                <div class="card mb-4">
                    <div class="card-body p-2">
                        <nav class="nav flex-column">
                            <a class="nav-link" href="index.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                            <a class="nav-link active" href="verify-users.php">
                                <i class="fas fa-user-check"></i> Verify Users
                                <?php if ($pendingCount > 0): ?>
                                <span class="badge bg-danger"><?php echo $pendingCount; ?></span>
                                <?php endif; ?>
                            </a>
                            <a class="nav-link" href="users.php">
                                <i class="fas fa-users"></i> All Users
                            </a>
                            <a class="nav-link" href="trades.php">
                                <i class="fas fa-chart-line"></i> Trades
                            </a>
                            <a class="nav-link" href="subscriptions.php">
                                <i class="fas fa-credit-card"></i> Subscriptions
                            </a>
                            <a class="nav-link" href="settings.php">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-10">
                <div class="dashboard-header">
                    <div>
                        <h1 class="dashboard-title">Verify Users</h1>
                        <p class="dashboard-subtitle"><?php echo $pendingCount; ?> users pending verification</p>
                    </div>
                </div>

                <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if (empty($pendingUsers)): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                        <h4>All Caught Up!</h4>
                        <p class="text-muted">No pending users to verify.</p>
                    </div>
                </div>
                <?php else: ?>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table verification-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>User Details</th>
                                        <th>OlympTrade ID</th>
                                        <th>Country</th>
                                        <th>Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingUsers as $index => $user): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($user['fullname']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                                            <?php if ($user['phone']): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($user['phone']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <code class="fs-5"><?php echo htmlspecialchars($user['olymptrade_id']); ?></code>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['country']); ?></td>
                                        <td>
                                            <?php echo formatDate($user['created_at'], 'M d, Y H:i'); ?>
                                            <br>
                                            <small class="text-muted"><?php echo timeAgo($user['created_at']); ?></small>
                                        </td>
                                        <td>
                                            <div class="verification-actions">
                                                <a href="?action=verify&id=<?php echo $user['id']; ?>"
                                                   class="btn-approve"
                                                   onclick="return confirm('Approve this user?')">
                                                    <i class="fas fa-check"></i> Approve
                                                </a>
                                                <button type="button"
                                                        class="btn-reject"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#rejectModal<?php echo $user['id']; ?>">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Reject Modal -->
                                    <div class="modal fade" id="rejectModal<?php echo $user['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST">
                                                    <input type="hidden" name="reject_user" value="1">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">

                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Reject User</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p><strong>User:</strong> <?php echo htmlspecialchars($user['fullname']); ?></p>
                                                        <p><strong>OlympTrade ID:</strong> <?php echo htmlspecialchars($user['olymptrade_id']); ?></p>

                                                        <div class="mb-3">
                                                            <label class="form-label">Rejection Reason</label>
                                                            <select class="form-select" name="reason_code" required onchange="toggleCustomReason(this, <?php echo $user['id']; ?>)">
                                                                <option value="">Select reason...</option>
                                                                <?php foreach ($rejectionReasons as $code => $reason): ?>
                                                                <option value="<?php echo $code; ?>"><?php echo $code; ?> - <?php echo $reason; ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>

                                                        <div class="mb-3" id="customReasonDiv<?php echo $user['id']; ?>" style="display: none;">
                                                            <label class="form-label">Custom Reason</label>
                                                            <textarea class="form-control" name="custom_reason" rows="3" placeholder="Enter custom rejection reason..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger">Reject User</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Verification Guidelines -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h5><i class="fas fa-info-circle text-primary"></i> Verification Guidelines</h5>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <h6 class="text-success"><i class="fas fa-check"></i> Approve if:</h6>
                                <ul class="small">
                                    <li>OlympTrade ID exists and is active</li>
                                    <li>Registered via official affiliate link</li>
                                    <li>Has minimum $10 deposit</li>
                                    <li>All registration data is complete</li>
                                    <li>No duplicate accounts detected</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-danger"><i class="fas fa-times"></i> Reject if:</h6>
                                <ul class="small">
                                    <li>OlympTrade ID not found</li>
                                    <li>Not registered via affiliate link</li>
                                    <li>Deposit below $10</li>
                                    <li>Duplicate account detected</li>
                                    <li>Suspicious or fraudulent activity</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function toggleCustomReason(select, userId) {
        const customDiv = document.getElementById('customReasonDiv' + userId);
        if (select.value === 'R10') {
            customDiv.style.display = 'block';
        } else {
            customDiv.style.display = 'none';
        }
    }

    function timeAgo(date) {
        // Placeholder - implemented server-side
    }
    </script>
</body>
</html>

<?php
// Helper function for time ago
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return date('M d, Y', $time);
}
?>
