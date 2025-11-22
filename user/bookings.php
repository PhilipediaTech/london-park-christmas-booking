<?php
/**
 * User Bookings Page - Updated with Payment & Cancel Options
 * London Community Park Christmas Event Booking System
 */

$pageTitle = 'My Bookings';
require_once '../includes/header.php';

requireLogin();

// Get user's bookings
$stmt = $pdo->prepare("
    SELECT b.*, e.event_name, e.event_date, e.event_time, e.venue, e.requires_adult
    FROM bookings b
    JOIN events e ON b.event_id = e.event_id
    WHERE b.user_id = ?
    ORDER BY b.booking_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll();
?>

<section class="hero" style="padding: 40px 20px;">
    <div class="container">
        <h1>🎫 My Bookings</h1>
        <p>View and manage your Christmas event bookings</p>
    </div>
</section>

<div class="container">
    
    <?php echo displayMessage(); ?>
    
    <?php if (empty($bookings)): ?>
        <div class="card">
            <div class="card-body" style="text-align: center; padding: 60px;">
                <p style="font-size: 5rem; margin-bottom: 20px;">🎭</p>
                <h2>No Bookings Yet</h2>
                <p style="color: #666; margin: 20px 0;">Start exploring our Christmas events!</p>
                <a href="<?php echo SITE_URL; ?>/events.php" class="btn btn-primary">🎪 Browse Events</a>
            </div>
        </div>
    <?php else: ?>
        
        <!-- Stats -->
        <div class="stats-grid" style="margin-bottom: 30px;">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($bookings); ?></div>
                <div class="stat-label">Total Bookings</div>
            </div>
            <div class="stat-card green">
                <div class="stat-number">
                    <?php echo count(array_filter($bookings, fn($b) => $b['booking_status'] === 'confirmed' && $b['payment_status'] === 'paid')); ?>
                </div>
                <div class="stat-label">Confirmed & Paid</div>
            </div>
            <div class="stat-card gold">
                <div class="stat-number">
                    <?php echo formatCurrency(array_sum(array_map(fn($b) => $b['payment_status'] === 'paid' ? $b['total_amount'] : 0, $bookings))); ?>
                </div>
                <div class="stat-label">Total Spent</div>
            </div>
        </div>
        
        <!-- Bookings List -->
        <?php foreach ($bookings as $booking): ?>
            <?php
            $stmt = $pdo->prepare("SELECT * FROM booking_details WHERE booking_id = ?");
            $stmt->execute([$booking['booking_id']]);
            $details = $stmt->fetchAll();
            
            $isPast = strtotime($booking['event_date']) < strtotime('today');
            $isCancelled = $booking['booking_status'] === 'cancelled';
            $needsPayment = $booking['payment_status'] !== 'paid' && !$isCancelled;
            
            // Status colors
            $statusClass = 'badge-info';
            $paymentClass = 'badge-warning';
            
            if ($booking['booking_status'] === 'confirmed') $statusClass = 'badge-success';
            elseif ($isCancelled) $statusClass = 'badge-danger';
            
            if ($booking['payment_status'] === 'paid') $paymentClass = 'badge-success';
            elseif ($booking['payment_status'] === 'refunded') $paymentClass = 'badge-info';
            ?>
            
            <div class="card" style="margin-bottom: 25px; <?php echo ($isPast || $isCancelled) ? 'opacity: 0.7;' : ''; ?>">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <h3 style="color: white; margin: 0; font-size: 1.2rem;">
                            <?php echo sanitize($booking['event_name']); ?>
                        </h3>
                        <small>Ref: <strong><?php echo sanitize($booking['booking_reference']); ?></strong></small>
                    </div>
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <span class="badge <?php echo $statusClass; ?>">
                            <?php echo ucfirst($booking['booking_status']); ?>
                        </span>
                        <span class="badge <?php echo $paymentClass; ?>">
                            <?php echo ucfirst($booking['payment_status'] ?? 'unpaid'); ?>
                        </span>
                        <?php if ($isPast && !$isCancelled): ?>
                            <span class="badge badge-warning">Past Event</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px;">
                        <div>
                            <p><strong>📅 Date:</strong> <?php echo formatDate($booking['event_date']); ?></p>
                            <p><strong>⏰ Time:</strong> <?php echo formatTime($booking['event_time']); ?></p>
                            <p><strong>📍 Venue:</strong> <?php echo sanitize($booking['venue']); ?></p>
                        </div>
                        <div>
                            <p><strong>🎫 Tickets:</strong> <?php echo $booking['total_tickets']; ?></p>
                            <p><strong>💰 Amount:</strong> <?php echo formatCurrency($booking['total_amount']); ?></p>
                            <p><strong>📆 Booked:</strong> <?php echo formatDate($booking['booking_date']); ?></p>
                        </div>
                    </div>
                    
                    <!-- Ticket Details -->
                    <div style="background: var(--frost-blue); padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                        <strong>Ticket Details:</strong>
                        <?php foreach ($details as $detail): ?>
                            <span style="margin-left: 15px;">
                                <?php echo ucfirst($detail['ticket_type']); ?> 
                                (<?php echo $detail['seat_type'] === 'with_table' ? 'Table' : 'Standard'; ?>): 
                                <?php echo $detail['quantity']; ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <?php if ($needsPayment): ?>
                            <a href="<?php echo SITE_URL; ?>/user/payment.php?booking_id=<?php echo $booking['booking_id']; ?>" 
                               class="btn btn-success btn-sm">
                                💳 Pay Now
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($booking['payment_status'] === 'paid' && !$isCancelled): ?>
                            <a href="<?php echo SITE_URL; ?>/user/booking_confirmation.php?id=<?php echo $booking['booking_id']; ?>" 
                               class="btn btn-primary btn-sm">
                                🎫 View Ticket
                            </a>
                            <a href="<?php echo SITE_URL; ?>/user/download_ticket.php?id=<?php echo $booking['booking_id']; ?>" 
                               class="btn btn-gold btn-sm">
                                📥 Download PDF
                            </a>
                        <?php endif; ?>
                        
                        <?php if (!$isPast && !$isCancelled): ?>
                            <a href="<?php echo SITE_URL; ?>/user/cancel_booking.php?id=<?php echo $booking['booking_id']; ?>" 
                               class="btn btn-danger btn-sm">
                                ❌ Cancel
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($isCancelled): ?>
                            <span style="color: #666; font-style: italic;">
                                Cancelled on <?php echo formatDate($booking['cancellation_date']); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
    <?php endif; ?>
    
    <div style="margin-top: 30px;">
        <a href="<?php echo SITE_URL; ?>/user/dashboard.php" class="btn btn-gold">← Back to Dashboard</a>
        <a href="<?php echo SITE_URL; ?>/events.php" class="btn btn-primary">🎪 Book More Events</a>
    </div>
    
</div>

<?php require_once '../includes/footer.php'; ?>