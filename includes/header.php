<?php
/**
 * Header Template
 * London Community Park Christmas Event Booking System
 * 
 * This file is included at the top of every page
 */

// Include required files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

// Set page title if not already set
$pageTitle = $pageTitle ?? 'London Community Park';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Book tickets for Christmas events at London Community Park">
    <title><?php echo sanitize($pageTitle); ?> | London Community Park</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <!-- Google Fonts for Christmas feel -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Mountains+of+Christmas:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .navbar-brand h1 {
            font-family: 'Mountains of Christmas', cursive;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container">
            <a href="<?php echo SITE_URL; ?>/index.php" class="navbar-brand">
                <span class="logo-icon">🎄</span>
                <h1>London Community Park</h1>
            </a>
            
            <ul class="nav-links">
                <li><a href="<?php echo SITE_URL; ?>/index.php">🏠 Home</a></li>
                <li><a href="<?php echo SITE_URL; ?>/events.php">🎪 Events</a></li>
                
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <li><a href="<?php echo SITE_URL; ?>/admin/index.php">⚙️ Admin Panel</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo SITE_URL; ?>/user/dashboard.php">📋 My Dashboard</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/user/bookings.php">🎫 My Bookings</a></li>
                    <?php endif; ?>
                    <li>
                        <a href="<?php echo SITE_URL; ?>/logout.php" class="btn-nav">
                            👋 Logout (<?php echo sanitize($_SESSION['first_name']); ?>)
                        </a>
                    </li>
                <?php else: ?>
                    <li><a href="<?php echo SITE_URL; ?>/login.php">🔑 Login</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/register.php" class="btn-nav">📝 Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    
    <!-- Christmas Lights Decoration -->
    <div class="christmas-lights"></div>
    
    <!-- Main Content Container -->
    <main>