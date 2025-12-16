<?php
$page_title = 'Profile';
require_once 'includes/header.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user = getUserById($_SESSION['user_id']);
$stats = getUserStats($_SESSION['user_id'], 30);
$packageInfo = getPackageDetails($user['package'] ?? 'trial');
$countries = getCountryList();

$message = '';
$messageType = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid request. Please try again.';
        $messageType = 'danger';
    } else {
        $data = [
            'fullname' => cleanInput($_POST['fullname'] ?? ''),
            'phone' => cleanInput($_POST['phone'] ?? ''),
            'country' => cleanInput($_POST['country'] ?? '')
        ];

        if (updateUserProfile($_SESSION['user_id'], $data)) {
            $message = 'Profile updated successfully!';
            $messageType = 'success';
            $user = getUserById($_SESSION['user_id']);
            $_SESSION['user_name'] = $user['fullname'];
        } else {
            $message = 'Failed to update profile.';
            $messageType = 'danger';
        }
    }
}
?>

<section class="dashboard-page">
    <div class="container py-4">
        <div class="row">
            <div class="col-lg-4">
                <!-- Profile Card -->
                <div class="profile-card mb-4">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <?php echo strtoupper(substr($user['fullname'], 0, 2)); ?>
                        </div>
                        <div class="profile-info">
                            <h3><?php echo htmlspecialchars($user['fullname']); ?></h3>
                            <p><?php echo htmlspecialchars($user['email']); ?></p>
                            <span class="profile-badge"><?php echo $packageInfo['name']; ?></span>
                        </div>
                    </div>

                    <hr style="border-color: var(--border-color);">

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Status</span>
                        <span class="badge bg-success"><?php echo ucfirst($user['status']); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Country</span>
                        <span><?php echo $countries[$user['country']] ?? $user['country']; ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">OlympTrade ID</span>
                        <code><?php echo htmlspecialchars($user['olymptrade_id']); ?></code>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Member Since</span>
                        <span><?php echo formatDate($user['created_at']); ?></span>
                    </div>
                    <?php if ($user['package_expiry']): ?>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Package Expires</span>
                        <span><?php echo formatDate($user['package_expiry']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Stats -->
                <div class="card">
                    <div class="card-body">
                        <h5><i class="fas fa-chart-pie text-primary"></i> Your Stats (30d)</h5>
                        <hr style="border-color: var(--border-color);">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Total Trades</span>
                            <span class="fw-bold"><?php echo $stats['total_trades'] ?? 0; ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Win Rate</span>
                            <span class="fw-bold text-success"><?php echo number_format($stats['win_rate'] ?? 0, 1); ?>%</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Total P&L</span>
                            <span class="fw-bold <?php echo ($stats['total_pnl'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?>">
                                <?php echo ($stats['total_pnl'] ?? 0) >= 0 ? '+' : ''; ?>$<?php echo number_format($stats['total_pnl'] ?? 0, 2); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h4 class="mb-4"><i class="fas fa-user-edit text-primary"></i> Edit Profile</h4>

                        <?php if ($message): ?>
                        <div class="alert alert-<?php echo $messageType; ?>">
                            <?php echo $message; ?>
                        </div>
                        <?php endif; ?>

                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control" name="fullname"
                                           value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                    <small class="text-muted">Contact support to change email</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" name="phone"
                                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Country</label>
                                    <select class="form-select" name="country">
                                        <?php foreach ($countries as $code => $name): ?>
                                        <option value="<?php echo $code; ?>" <?php echo $user['country'] === $code ? 'selected' : ''; ?>>
                                            <?php echo $name; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">OlympTrade ID</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['olymptrade_id']); ?>" disabled>
                                <small class="text-muted">Cannot be changed after registration</small>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </form>

                        <hr style="border-color: var(--border-color);" class="my-4">

                        <h5 class="mb-3"><i class="fas fa-key text-primary"></i> Change Password</h5>
                        <form method="POST" action="change-password.php">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" class="form-control" name="current_password" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">New Password</label>
                                    <input type="password" class="form-control" name="new_password" required minlength="8">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" name="confirm_password" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-secondary">
                                <i class="fas fa-key"></i> Change Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
