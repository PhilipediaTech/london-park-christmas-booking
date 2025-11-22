<?php
/**
 * Cancel Booking Page
 * London Community Park Christmas Event Booking System
 */

$pageTitle = 'Cancel Booking';
require_once '../includes/header.php';

requireLogin();

$bookingId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($bookingId <= 0) {
    $_SESSION['error'] = 'Invalid booking';
    redirect(SITE_URL . '/user/bookings.php');
}

// Get booking details
$stmt = $pdo->prepare("
    SELECT b.*, e.event_name, e.event_date, e.event_time, e.venue
    FROM bookings b
    JOIN events e ON b.event_id = e.event_id
    WHERE b.booking_id = ? AND b.user_id = ?
");
$stmt->execute([$bookingId, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    $_SESSION['error'] = 'Booking not found';
    redirect(SITE_URL . '/user/bookings.php');
}

// Check if already cancelled
if ($booking['booking_status'] === 'cancelled') {
    $_SESSION['error'] = 'This booking has already been cancelled';
    redirect(SITE_URL . '/user/bookings.php');
}

// Check if event has passed
if (strtotime($booking['event_date']) < strtotime('today')) {
    $_SESSION['error'] = 'Cannot cancel a booking for a past event';
    redirect(SITE_URL . '/user/bookings.php');
}

// Calculate refund (full refund if more than 24 hours before event)
$hoursUntilEvent = (strtotime($booking['event_date'] . ' ' . $booking['event_time']) - time()) / 3600;
$refundPercentage = $hoursUntilEvent >= 24 ? 100 : ($hoursUntilEvent >= 12 ? 50 : 0);
$refundAmount = ($booking['total_amount'] * $refundPercentage) / 100;

$errors = [];

// Process cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Invalid form submission';
    } else {
        $reason = sanitize($_POST['cancellation_reason'] ?? '');
        
        if (empty($reason)) {
            $errors[] = 'Please provide a reason for cancellation';
        }
        
        if (empty($errors)) {
            try {
                $pdo->beginTransaction();
                
                // Update booking status
                $stmt = $pdo->prepare("
                    UPDATE bookings 
                    SET booking_status = 'cancelled',
                        cancellation_date = NOW(),
                        cancellation_reason = ?
                    WHERE booking_id = ?
                ");
                $stmt->execute([$reason, $bookingId]);
                
                // Restore seats
                $stmt = $pdo->prepare("SELECT * FROM booking_details WHERE booking_id = ?");
                $stmt->execute([$bookingId]);
                $details = $stmt->fetchAll();
                
                foreach ($details as $detail) {
                    $stmt = $pdo->prepare("
                        UPDATE seats 
                        SET available_seats = available_seats + ?
                        WHERE event_id = ? AND seat_type = ?
                    ");
                    $stmt->execute([$detail['quantity'], $booking['event_id'], $detail['seat_type']]);
                }
                
                // If paid, create refund record
                if ($booking['payment_status'] === 'paid' && $refundAmount > 0) {
                    $stmt = $pdo->prepare("
                        UPDATE bookings SET payment_status = 'refunded' WHERE booking_id = ?
                    ");
                    $stmt->execute([$bookingId]);
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO payments (booking_id, amount, payment_method, transaction_id, payment_status)
                        VALUES (?, ?, 'Refund', ?, 'completed')
                    ");
                    $stmt->execute([$bookingId, -$refundAmount, 'REF' . strtoupper(uniqid())]);
                }
                
                $pdo->commit();
                
                $message = 'Booking cancelled successfully.';
                if ($refundAmount > 0) {
                    $message .= ' A refund of ' . formatCurrency($refundAmount) . ' will be processed within 5-7 business days.';
                }
                $_SESSION['success'] = $message;
                redirect(SITE_URL . '/user/bookings.php');
                
            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = 'Cancellation failed. Please try again.';
            }
        }
    }
}

$csrfToken = generateCsrfToken();
?>

<div class="container" style="max-width: 700px; padding-top: 40px; padding-bottom: 40px;">
    
    <?php echo displayMessage(); ?>
    
    <div class="card">
        <div class="card-header" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
            <h2>⚠️ Cancel Booking</h2>
            <p>Please review before confirming cancellation</p>
        </div>
        
        <div class="card-body">
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <span class="alert-icon">✕</span>
                    <?php echo implode('<br>', $errors); ?>
                </div>
            <?php endif; ?>
            
            <!-- Booking Summary -->
            <div style="background: var(--frost-blue); padding: 20px; border-radius: 10px; margin-bottom: 25px;">
                <h3 style="color: var(--christmas-red); margin-bottom: 15px;">
                    <?php echo sanitize($booking['event_name']); ?>
                </h3>
                <p><strong>📅 Date:</strong> <?php echo formatDate($booking['event_date']); ?></p>
                <p><strong>⏰ Time:</strong> <?php echo formatTime($booking['event_time']); ?></p>
                <p><strong>🎫 Tickets:</strong> <?php echo $booking['total_tickets']; ?></p>
                <p><strong>🔖 Reference:</strong> <?php echo sanitize($booking['booking_reference']); ?></p>
                <p><strong>💰 Amount Paid:</strong> <?php echo formatCurrency($booking['total_amount']); ?></p>
            </div>
            
            <!-- Refund Information -->
            <div style="background: <?php echo $refundPercentage > 0 ? '#d4edda' : '#f8d7da'; ?>; padding: 20px; border-radius: 10px; margin-bottom: 25px;">
                <h4 style="margin-bottom: 10px;">💰 Refund Information</h4>
                
                <?php if ($refundPercentage === 100): ?>
                    <p style="color: #155724;">
                        <strong>Full Refund Available!</strong><br>
                        You will receive a full refund of <?php echo formatCurrency($refundAmount); ?>
                    </p>
                <?php elseif ($refundPercentage === 50): ?>
                    <p style="color: #856404;">
                        <strong>Partial Refund Available</strong><br>
                        As you are cancelling within 24 hours of the event, you will receive a 50% refund of <?php echo formatCurrency($refundAmount); ?>
                    </p>
                <?php else: ?>
                    <p style="color: #721c24;">
                        <strong>No Refund Available</strong><br>
                        As you are cancelling within 12 hours of the event, no refund can be issued.
                    </p>
                <?php endif; ?>
                
                <p style="font-size: 0.9rem; margin-top: 10px; opacity: 0.8;">
                    Refund Policy: 100% if cancelled 24+ hours before, 50% if 12-24 hours before, 0% if less than 12 hours.
                </p>
            </div>
            
            <!-- Cancellation Form -->
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                
                <div class="form-group">
                    <label for="cancellation_reason" class="required">Reason for Cancellation</label>
                    <select id="cancellation_reason" name="cancellation_reason" class="form-control" required>
                        <option value="">Select a reason...</option>
                        <option value="Change of plans">Change of plans</option>
                        <option value="Unable to attend">Unable to attend</option>
                        <option value="Booked wrong date">Booked wrong date</option>
                        <option value="Booked wrong event">Booked wrong event</option>
                        <option value="Financial reasons">Financial reasons</option>
                        <option value="Health reasons">Health reasons</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div style="background: #fff3cd; padding: 15px; border-radius: 10px; margin-bottom: 25px;">
                    <strong>⚠️ Warning:</strong> This action cannot be undone. Once cancelled, you will need to make a new booking if you change your mind.
                </div>
                
                <div style="display: flex; gap: 15px;">
                    <button type="submit" class="btn btn-danger" style="flex: 1;">
                        ❌ Confirm Cancellation
                    </button>
                    <a href="<?php echo SITE_URL; ?>/user/bookings.php" class="btn btn-gold" style="flex: 1;">
                        ← Keep Booking
                    </a>
                </div>
            </form>
            
        </div>
    </div>
    
</div>

<?php require_once '../includes/footer.php'; ?>