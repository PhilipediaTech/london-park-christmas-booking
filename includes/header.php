<?php
/**
 * Header Template - Fixed & Improved
 * London Community Park Christmas Event Booking System
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

$pageTitle = $pageTitle ?? 'London Community Park';

// Safely get session variables
$isUserLoggedIn = isLoggedIn();
$isUserAdmin = isAdmin();
$userFirstName = isset($_SESSION['first_name']) ? $_SESSION['first_name'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Book tickets for Christmas events at London Community Park">
    <title><?php echo sanitize($pageTitle); ?> | London Community Park</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Mountains+of+Christmas:wght@400;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="<?php echo SITE_URL; ?>/index.php" class="navbar-brand">
                <span class="logo-icon">🎄</span>
                <span class="logo-text">London Community Park</span>
            </a>
            
            <!-- Mobile Menu Toggle -->
            <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
                <span></span>
                <span></span>
                <span></span>
            </button>
            
            <ul class="nav-links" id="navLinks">
                <li><a href="<?php echo SITE_URL; ?>/index.php"><span class="nav-icon">🏠</span> Home</a></li>
                <li><a href="<?php echo SITE_URL; ?>/events.php"><span class="nav-icon">🎪</span> Events</a></li>
                
                <?php if ($isUserLoggedIn): ?>
                    <?php if ($isUserAdmin): ?>
                        <li><a href="<?php echo SITE_URL; ?>/admin/index.php"><span class="nav-icon">⚙️</span> Admin</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo SITE_URL; ?>/user/dashboard.php"><span class="nav-icon">📋</span> My Dashboard</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/user/bookings.php"><span class="nav-icon">🎫</span> My Bookings</a></li>
                    <?php endif; ?>
                    <li>
                        <a href="<?php echo SITE_URL; ?>/logout.php" class="btn-nav btn-logout">
                            <span class="nav-icon">👋</span> Logout<?php echo !empty($userFirstName) ? ' (' . sanitize($userFirstName) . ')' : ''; ?>
                        </a>
                    </li>
                <?php else: ?>
                    <li><a href="<?php echo SITE_URL; ?>/login.php" class="btn-nav btn-login"><span class="nav-icon">🔑</span> Login</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/register.php" class="btn-nav btn-register"><span class="nav-icon">📝</span> Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    
    <!-- Christmas Lights Decoration -->
    <div class="christmas-lights"></div>
    
    <!-- Main Content -->
    <main class="main-content">