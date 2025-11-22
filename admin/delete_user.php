<?php
/**
 * Admin - Delete User
 * London Community Park Christmas Event Booking System
 */

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require admin access
requireLogin();
requireAdmin();

// Get user ID
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($userId <= 0) {
    $_SESSION['error'] = 'Invalid user ID';
    redirect(SITE_URL . '/admin/users.php');
}

// Prevent self-deletion
if ($userId === $_SESSION['user_id']) {
    $_SESSION['error'] = 'You cannot delete your own account';
    redirect(SITE_URL . '/admin/users.php');
}

// Check if user exists
$user = getUserById($pdo, $userId);

if (!$user) {
    $_SESSION['error'] = 'User not found';
    redirect(SITE_URL . '/admin/users.php');
}

try {
    // Delete user (cascades to bookings due to foreign key)
    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    $_SESSION['success'] = 'User "' . $user['first_name'] . ' ' . $user['last_name'] . '" has been deleted successfully';
    
} catch (PDOException $e) {
    $_SESSION['error'] = 'Failed to delete user. Please try again.';
}

redirect(SITE_URL . '/admin/users.php');
?>