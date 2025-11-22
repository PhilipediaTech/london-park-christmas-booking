<?php
/**
 * Database Configuration File
 * London Community Park Christmas Event Booking System
 * 
 * This file contains the database connection settings
 * and creates a PDO connection object
 */

// Database credentials
define('DB_HOST', 'localhost');      // Database host (usually localhost)
define('DB_NAME', 'london_park_db'); // Database name we created
define('DB_USER', 'root');           // Default XAMPP username
define('DB_PASS', '');               // Default XAMPP password (empty)
define('DB_CHARSET', 'utf8mb4');     // Character set for proper encoding

// Site configuration
define('SITE_NAME', 'London Community Park');
define('SITE_URL', 'http://localhost/london-park-christmas-booking');

// Create PDO connection
try {
    // DSN (Data Source Name) string
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    
    // PDO options for better error handling and security
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // Throw exceptions on errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // Return associative arrays
        PDO::ATTR_EMULATE_PREPARES   => false,                   // Use real prepared statements
    ];
    
    // Create the PDO connection
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
} catch (PDOException $e) {
    // If connection fails, show error message
    die("Database Connection Failed: " . $e->getMessage());
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>