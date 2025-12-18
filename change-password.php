<?php
/**
 * Change Password Handler
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';

// Must be logged in
if (!isLoggedIn()) {
    redirect('login.php');
    exit;
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('profile.php');
    exit;
}

// Verify CSRF
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['flash_message'] = 'Invalid request. Please try again.';
    $_SESSION['flash_type'] = 'danger';
    redirect('profile.php');
    exit;
}

$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Validation
if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
    $_SESSION['flash_message'] = 'All fields are required.';
    $_SESSION['flash_type'] = 'danger';
    redirect('profile.php');
    exit;
}

if (strlen($newPassword) < 8) {
    $_SESSION['flash_message'] = 'New password must be at least 8 characters.';
    $_SESSION['flash_type'] = 'danger';
    redirect('profile.php');
    exit;
}

if ($newPassword !== $confirmPassword) {
    $_SESSION['flash_message'] = 'New passwords do not match.';
    $_SESSION['flash_type'] = 'danger';
    redirect('profile.php');
    exit;
}

// Get current user
$db = getDBConnection();
$stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['flash_message'] = 'User not found.';
    $_SESSION['flash_type'] = 'danger';
    redirect('profile.php');
    exit;
}

// Verify current password
if (!password_verify($currentPassword, $user['password'])) {
    $_SESSION['flash_message'] = 'Current password is incorrect.';
    $_SESSION['flash_type'] = 'danger';
    redirect('profile.php');
    exit;
}

// Update password
$hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
$stmt = $db->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
$result = $stmt->execute([$hashedPassword, $_SESSION['user_id']]);

if ($result) {
    $_SESSION['flash_message'] = 'Password changed successfully!';
    $_SESSION['flash_type'] = 'success';

    // Log the activity
    logActivity($_SESSION['user_id'], 'password_changed', 'User changed their password');
} else {
    $_SESSION['flash_message'] = 'Failed to change password. Please try again.';
    $_SESSION['flash_type'] = 'danger';
}

redirect('profile.php');
