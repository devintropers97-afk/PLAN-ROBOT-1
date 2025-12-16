<?php
$page_title = 'Profile';
require_once 'dashboard/includes/dashboard-header.php';

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

<!-- Page Header -->
<div class="db-page-header">
    <div>
        <h1 class="db-page-title"><i class="fas fa-user-circle"></i> Profile</h1>
        <p class="db-page-subtitle">Manage your account settings</p>
    </div>
    <a href="dashboard.php" class="db-btn db-btn-outline">
        <i class="fas fa-arrow-left"></i> Dashboard
    </a>
</div>

<?php if ($message): ?>
<div class="db-alert <?php echo $messageType; ?> db-fade-in">
    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
    <span><?php echo $message; ?></span>
    <button type="button" class="btn-close btn-close-white ms-auto" onclick="this.parentElement.remove()"></button>
</div>
<?php endif; ?>

<div class="row g-4">
    <!-- Left Column - Profile Card -->
    <div class="col-lg-4">
        <div class="db-card db-fade-in mb-4">
            <div class="db-card-body text-center py-4">
                <div class="profile-avatar-lg mx-auto mb-3">
                    <?php echo strtoupper(substr($user['fullname'], 0, 2)); ?>
                </div>
                <h4 class="mb-1"><?php echo htmlspecialchars($user['fullname']); ?></h4>
                <p class="text-muted mb-3"><?php echo htmlspecialchars($user['email']); ?></p>
                <span class="db-badge <?php echo strtolower($user['package'] ?? 'free'); ?>" style="font-size: 0.85rem; padding: 0.5rem 1rem;">
                    <?php echo $packageInfo['name']; ?>
                </span>

                <hr class="my-4" style="border-color: var(--db-border);">

                <div class="text-start">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Status</span>
                        <span class="db-badge success"><?php echo ucfirst($user['status']); ?></span>
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
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="db-card db-fade-in">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="fas fa-chart-pie"></i> Your Stats (30d)</h5>
            </div>
            <div class="db-card-body">
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

    <!-- Right Column - Edit Profile -->
    <div class="col-lg-8">
        <div class="db-card db-fade-in mb-4">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="fas fa-user-edit"></i> Edit Profile</h5>
            </div>
            <div class="db-card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="db-form-group">
                                <label class="db-form-label">Full Name</label>
                                <input type="text" class="db-form-control" name="fullname"
                                       value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="db-form-group">
                                <label class="db-form-label">Email Address</label>
                                <input type="email" class="db-form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                <small class="text-muted">Contact support to change email</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="db-form-group">
                                <label class="db-form-label">Phone Number</label>
                                <input type="tel" class="db-form-control" name="phone"
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="db-form-group">
                                <label class="db-form-label">Country</label>
                                <select class="db-form-control db-form-select" name="country">
                                    <?php foreach ($countries as $code => $name): ?>
                                    <option value="<?php echo $code; ?>" <?php echo $user['country'] === $code ? 'selected' : ''; ?>>
                                        <?php echo $name; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="db-form-group">
                                <label class="db-form-label">OlympTrade ID</label>
                                <input type="text" class="db-form-control" value="<?php echo htmlspecialchars($user['olymptrade_id']); ?>" disabled>
                                <small class="text-muted">Cannot be changed after registration</small>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="db-btn db-btn-primary mt-3">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>

        <!-- Change Password -->
        <div class="db-card db-fade-in">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="fas fa-key"></i> Change Password</h5>
            </div>
            <div class="db-card-body">
                <form method="POST" action="change-password.php">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="db-form-group">
                                <label class="db-form-label">Current Password</label>
                                <input type="password" class="db-form-control" name="current_password" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="db-form-group">
                                <label class="db-form-label">New Password</label>
                                <input type="password" class="db-form-control" name="new_password" required minlength="8">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="db-form-group">
                                <label class="db-form-label">Confirm Password</label>
                                <input type="password" class="db-form-control" name="confirm_password" required>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="db-btn db-btn-outline mt-3">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.profile-avatar-lg {
    width: 100px;
    height: 100px;
    border-radius: 20px;
    background: linear-gradient(135deg, var(--db-primary), var(--db-secondary));
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 2rem;
    color: white;
}
</style>

<?php require_once 'dashboard/includes/dashboard-footer.php'; ?>
