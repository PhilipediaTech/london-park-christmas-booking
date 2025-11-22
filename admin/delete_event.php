<?php
/**
 * Admin - Delete Event
 * London Community Park Christmas Event Booking System
 */

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

requireLogin();
requireAdmin();

$eventId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($eventId <= 0) {
    $_SESSION['error'] = 'Invalid event ID';
    redirect(SITE_URL . '/admin/events.php');
}

// Get event
$event = getEventById($pdo, $eventId);

if (!$event) {
    $_SESSION['error'] = 'Event not found';
    redirect(SITE_URL . '/admin/events.php');
}

// Check if event has bookings
$stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE event_id = ? AND booking_status != 'cancelled'");
$stmt->execute([$eventId]);
$bookingCount = $stmt->fetchColumn();

if ($bookingCount > 0) {
    $_SESSION['error'] = 'Cannot delete event with active bookings. Please cancel all bookings first or deactivate the event instead.';
    redirect(SITE_URL . '/admin/events.php');
}

try {
    // Delete event (cascades to seats, prices, and cancelled bookings)
    $stmt = $pdo->prepare("DELETE FROM events WHERE event_id = ?");
    $stmt->execute([$eventId]);
    
    $_SESSION['success'] = 'Event "' . $event['event_name'] . '" has been deleted successfully';
    
} catch (PDOException $e) {
    $_SESSION['error'] = 'Failed to delete event. Please try again.';
}

redirect(SITE_URL . '/admin/events.php');
?>