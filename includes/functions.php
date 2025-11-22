<?php
/**
 * Helper Functions
 * London Community Park Christmas Event Booking System
 * 
 * Contains reusable functions used throughout the application
 */

/**
 * Sanitize user input to prevent XSS attacks
 * @param string $data - The input data to sanitize
 * @return string - The sanitized data
 */
function sanitize($data) {
    $data = trim($data);                    // Remove whitespace
    $data = stripslashes($data);            // Remove backslashes
    $data = htmlspecialchars($data);        // Convert special characters to HTML entities
    return $data;
}

/**
 * Redirect to a specific page
 * @param string $url - The URL to redirect to
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Check if user is logged in
 * @return bool - True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is an admin
 * @return bool - True if admin, false otherwise
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Require user to be logged in
 * Redirects to login page if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = "Please log in to access this page.";
        redirect(SITE_URL . '/login.php');
    }
}

/**
 * Require user to be an admin
 * Redirects to homepage if not an admin
 */
function requireAdmin() {
    if (!isAdmin()) {
        $_SESSION['error'] = "You don't have permission to access this page.";
        redirect(SITE_URL . '/index.php');
    }
}

/**
 * Display flash messages (success/error messages)
 * @return string - HTML for the message alert
 */
function displayMessage() {
    $html = '';
    
    if (isset($_SESSION['success'])) {
        $html .= '<div class="alert alert-success">';
        $html .= '<span class="alert-icon">✓</span>';
        $html .= sanitize($_SESSION['success']);
        $html .= '</div>';
        unset($_SESSION['success']);
    }
    
    if (isset($_SESSION['error'])) {
        $html .= '<div class="alert alert-error">';
        $html .= '<span class="alert-icon">✕</span>';
        $html .= sanitize($_SESSION['error']);
        $html .= '</div>';
        unset($_SESSION['error']);
    }
    
    return $html;
}

/**
 * Generate a unique booking reference
 * @return string - A unique booking reference
 */
function generateBookingReference() {
    $prefix = 'LP';  // London Park
    $date = date('ymd');
    $random = strtoupper(substr(md5(uniqid()), 0, 6));
    return $prefix . $date . $random;
}

/**
 * Format date for display
 * @param string $date - Date in Y-m-d format
 * @return string - Formatted date (e.g., "25th December 2024")
 */
function formatDate($date) {
    return date('jS F Y', strtotime($date));
}

/**
 * Format time for display
 * @param string $time - Time in H:i:s format
 * @return string - Formatted time (e.g., "7:00 PM")
 */
function formatTime($time) {
    return date('g:i A', strtotime($time));
}

/**
 * Format currency
 * @param float $amount - The amount to format
 * @return string - Formatted currency (e.g., "£25.00")
 */
function formatCurrency($amount) {
    return '£' . number_format($amount, 2);
}

/**
 * Upload image file
 * @param array $file - The $_FILES array element
 * @param string $directory - Directory to save the file
 * @return string|false - Filename on success, false on failure
 */
function uploadImage($file, $directory = 'uploads/photos/') {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $filename = $file['name'];
    $tmpname = $file['tmp_name'];
    $size = $file['size'];
    $error = $file['error'];
    
    // Check for upload errors
    if ($error !== 0) {
        return false;
    }
    
    // Check file size (max 5MB)
    if ($size > 5 * 1024 * 1024) {
        return false;
    }
    
    // Get file extension
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    // Check if extension is allowed
    if (!in_array($ext, $allowed)) {
        return false;
    }
    
    // Generate unique filename
    $newname = uniqid() . '_' . time() . '.' . $ext;
    $destination = $directory . $newname;
    
    // Move the uploaded file
    if (move_uploaded_file($tmpname, $destination)) {
        return $newname;
    }
    
    return false;
}

/**
 * Get user by ID
 * @param PDO $pdo - Database connection
 * @param int $id - User ID
 * @return array|false - User data or false if not found
 */
function getUserById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Get all events
 * @param PDO $pdo - Database connection
 * @param bool $activeOnly - Whether to get only active events
 * @return array - Array of events
 */
function getAllEvents($pdo, $activeOnly = true) {
    $sql = "SELECT * FROM events";
    if ($activeOnly) {
        $sql .= " WHERE is_active = 1";
    }
    $sql .= " ORDER BY event_date ASC";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

/**
 * Get event by ID
 * @param PDO $pdo - Database connection
 * @param int $id - Event ID
 * @return array|false - Event data or false if not found
 */
function getEventById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Count total users (excluding admins)
 * @param PDO $pdo - Database connection
 * @return int - Number of users
 */
function countUsers($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'");
    return $stmt->fetchColumn();
}

/**
 * Count total bookings
 * @param PDO $pdo - Database connection
 * @return int - Number of bookings
 */
function countBookings($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM bookings");
    return $stmt->fetchColumn();
}

/**
 * Count total events
 * @param PDO $pdo - Database connection
 * @return int - Number of active events
 */
function countEvents($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM events WHERE is_active = 1");
    return $stmt->fetchColumn();
}
?>