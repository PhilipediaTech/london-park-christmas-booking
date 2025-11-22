<?php
/**
 * Booking Confirmation Page
 * London Community Park Christmas Event Booking System
 */

$pageTitle = 'Booking Confirmation';
require_once '../includes/header.php';

requireLogin();

$bookingId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($bookingId <= 0) {
    $_SESSION['error'] = 'Invalid booking';
    redirect(SITE_URL . '/user/bookings.php');
}

// Get booking details
$stmt = $pdo->prepare("
    SELECT b.*, e.event_name, e.event_date, e.event_time, e.venue, e.event_description,
           u.first_name, u.last_name, u.email
    FROM bookings b
    JOIN events e ON b.event_id = e.event_id
    JOIN users u ON b.user_id = u.user_id
    WHERE b.booking_id = ? AND b.user_id = ?
");
$stmt->execute([$bookingId, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    $_SESSION['error'] = 'Booking not found';
    redirect(SITE_URL . '/user/bookings.php');
}

// Get booking details
$stmt = $pdo->prepare("SELECT * FROM booking_details WHERE booking_id = ?");
$stmt->execute([$bookingId]);
$details = $stmt->fetchAll();

// Generate QR Code URL (using Google Charts API - free)
$qrData = urlencode("LONDON PARK TICKET\nRef: " . $booking['booking_reference'] . "\nEvent: " . $booking['event_name'] . "\nDate: " . $booking['event_date'] . "\nTickets: " . $booking['total_tickets']);
$qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . $qrData;
?>

<div class="container" style="max-width: 800px; padding-top: 40px; padding-bottom: 40px;">
    
    <?php echo displayMessage(); ?>
    
    <!-- Success Message -->
    <div class="card" style="margin-bottom: 30px; border: 3px solid var(--christmas-green);">
        <div class="card-body" style="text-align: center; padding: 40px;">
            <div style="font-size: 5rem; margin-bottom: 20px;">✅</div>
            <h1 style="color: var(--christmas-green); margin-bottom: 15px;">Booking Confirmed!</h1>
            <p style="font-size: 1.1rem; color: #666;">
                Thank you for your booking. Your tickets are ready!
            </p>
        </div>
    </div>
    
    <!-- Ticket Card -->
    <div class="card" id="ticket-card" style="background: linear-gradient(135deg, #1a472a 0%, #c41e3a 100%); color: white; overflow: visible;">
        
        <!-- Ticket Header -->
        <div style="padding: 30px; text-align: center; border-bottom: 3px dashed rgba(255,255,255,0.3);">
            <h2 style="color: var(--gold); font-size: 1.8rem; margin-bottom: 10px;">🎄 London Community Park 🎄</h2>
            <p style="opacity: 0.9;">Christmas Event Ticket <?php echo date('Y'); ?></p>
        </div>
        
        <!-- Ticket Body -->
        <div style="padding: 30px; display: grid; grid-template-columns: 1fr auto; gap: 30px; align-items: center;">
            
            <!-- Event Details -->
            <div>
                <h3 style="color: var(--gold); font-size: 1.5rem; margin-bottom: 20px;">
                    <?php echo sanitize($booking['event_name']); ?>
                </h3>
                
                <div style="display: grid; gap: 12px;">
                    <p><strong>📅 Date:</strong> <?php echo formatDate($booking['event_date']); ?></p>
                    <p><strong>⏰ Time:</strong> <?php echo formatTime($booking['event_time']); ?></p>
                    <p><strong>📍 Venue:</strong> <?php echo sanitize($booking['venue']); ?></p>
                    <p><strong>🎫 Tickets:</strong> <?php echo $booking['total_tickets']; ?></p>
                    <p><strong>👤 Name:</strong> <?php echo sanitize($booking['first_name'] . ' ' . $booking['last_name']); ?></p>
                </div>
                
                <!-- Ticket Details -->
                <div style="margin-top: 20px; padding: 15px; background: rgba(255,255,255,0.1); border-radius: 10px;">
                    <?php foreach ($details as $detail): ?>
                        <p style="margin: 5px 0;">
                            <?php echo ucfirst($detail['ticket_type']); ?> 
                            (<?php echo $detail['seat_type'] === 'with_table' ? 'With Table' : 'Without Table'; ?>): 
                            <strong><?php echo $detail['quantity']; ?></strong>
                        </p>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- QR Code -->
            <div style="text-align: center;">
                <img src="<?php echo $qrCodeUrl; ?>" alt="QR Code" style="width: 150px; height: 150px; border-radius: 10px; background: white; padding: 10px;">
                <p style="margin-top: 10px; font-size: 0.85rem; opacity: 0.8;">Scan at entry</p>
            </div>
        </div>
        
        <!-- Ticket Footer -->
        <div style="padding: 20px 30px; background: rgba(0,0,0,0.2); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <p style="font-size: 0.9rem; opacity: 0.8;">Booking Reference</p>
                <p style="font-size: 1.5rem; font-weight: bold; color: var(--gold);">
                    <?php echo sanitize($booking['booking_reference']); ?>
                </p>
            </div>
            <div style="text-align: right;">
                <p style="font-size: 0.9rem; opacity: 0.8;">Total Paid</p>
                <p style="font-size: 1.5rem; font-weight: bold; color: var(--gold);">
                    <?php echo formatCurrency($booking['total_amount']); ?>
                </p>
            </div>
        </div>
        
    </div>
    
    <!-- Action Buttons -->
    <div style="display: flex; gap: 15px; margin-top: 30px; flex-wrap: wrap; justify-content: center;">
        <a href="<?php echo SITE_URL; ?>/user/download_ticket.php?id=<?php echo $bookingId; ?>" class="btn btn-success">
            📥 Download PDF Ticket
        </a>
        <button onclick="window.print()" class="btn btn-primary">
            🖨️ Print Ticket
        </button>
        <a href="<?php echo SITE_URL; ?>/user/bookings.php" class="btn btn-gold">
            📋 View All Bookings
        </a>
    </div>
    
    <!-- Important Information -->
    <div class="card" style="margin-top: 30px;">
        <div class="card-body">
            <h3 style="color: var(--christmas-green); margin-bottom: 15px;">ℹ️ Important Information</h3>
            <ul style="line-height: 2; color: #666;">
                <li>Please arrive at least 30 minutes before the event starts</li>
                <li>Bring a valid photo ID for verification</li>
                <li>Show your QR code or booking reference at the entrance</li>
                <li>Cancellations must be made at least 24 hours before the event</li>
                <li>A confirmation email has been sent to <?php echo sanitize($booking['email']); ?></li>
            </ul>
        </div>
    </div>
    
</div>

<!-- Print Styles -->
<style>
@media print {
    .navbar, .christmas-lights, .footer, .btn, .alert, .card:last-child {
        display: none !important;
    }
    body {
        background: white !important;
    }
    body::before {
        display: none !important;
    }
    #ticket-card {
        box-shadow: none !important;
        margin: 0 !important;
    }
}
</style>

<?php require_once '../includes/footer.php'; ?>