<?php
/**
 * Logout Handler
 * London Community Park Christmas Event Booking System
 */

require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Logout the user
logoutUser();

// Set success message (need to start session again after logout)
session_start();
$_SESSION['success'] = 'You have been logged out successfully. See you soon!';

// Redirect to login page
redirect(SITE_URL . '/login.php');
?>