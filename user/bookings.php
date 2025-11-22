<?php
/**
 * User Bookings Page
 * London Community Park Christmas Event Booking System
 */

$pageTitle = 'My Bookings';
require_once '../includes/header.php';

// Require user login
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

<!-- Page Header -->
<section class="hero" style="padding: 40px 20px;">
    <div class="container">
        <h1>🎫 My Bookings</h1>
        <p>View all your Christmas event bookings</p>
    </div>
</section>

<div class="container">
    
    <?php echo displayMessage(); ?>
    
    <?php if (empty($bookings)): ?>
        <div class="card">
            <div class="card-body" style="text-align: center; padding: 60px;">
                <p style="font-size: 5rem; margin-bottom: 20px;">🎭</p>
                <h2>No Bookings Yet</h2>
                <p style="color: #666; margin: 20px 0;">You haven't made any bookings yet. Explore our Christmas events and book your experience!</p>
                <a href="<?php echo SITE_URL; ?>/events.php" class="btn btn-primary">
                    🎪 Browse Events
                </a>
            </div>
        </div>
    <?php else: ?>
        
        <!-- Bookings Summary -->
        <div class="stats-grid" style="margin-bottom: 30px;">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($bookings); ?></div>
                <div class="stat-label">Total Bookings</div>
            </div>
            <div class="stat-card green">
                <div class="stat-number">
                    <?php 
                    $confirmed = array_filter($bookings, fn($b) => $b['booking_status'] === 'confirmed');
                    echo count($confirmed);
                    ?>
                </div>
                <div class="stat-label">Confirmed</div>
            </div>
            <div class="stat-card gold">
                <div class="stat-number">
                    <?php 
                    $totalSpent = array_sum(array_column($bookings, 'total_amount'));
                    echo formatCurrency($totalSpent);
                    ?>
                </div>
                <div class="stat-label">Total Spent</div>
            </div>
        </div>
        
        <!-- Bookings List -->
        <?php foreach ($bookings as $booking): ?>
            <?php
            // Get booking details
            $detailStmt = $pdo->prepare("SELECT * FROM booking_details WHERE booking_id = ?");
            $detailStmt->execute([$booking['booking_id']]);
            $details = $detailStmt->fetchAll();
            
            // Determine if upcoming or past
            $isPast = strtotime($booking['event_date']) < strtotime('today');
            
            // Status badge class
            $statusClass = 'badge-info';
            if ($booking['booking_status'] === 'confirmed') $statusClass = 'badge-success';
            elseif ($booking['booking_status'] === 'cancelled') $statusClass = 'badge-danger';
            ?>
            
            <div class="card" style="margin-bottom: 25px; <?php echo $isPast ? 'opacity: 0.8;' : ''; ?>">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <h3 style="color: white; margin: 0;"><?php echo sanitize($booking['event_name']); ?></h3>
                        <small>Booking Reference: <strong><?php echo sanitize($booking['booking_reference']); ?></strong></small>
                    </div>
                    <div>
                        <span class="badge <?php echo $statusClass; ?>" style="font-size: 1rem;">
                            <?php echo ucfirst($booking['booking_status']); ?>
                        </span>
                        <?php if ($isPast): ?>
                            <span class="badge badge-warning" style="margin-left: 5px;">Past Event</span>
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
                            <p><strong>🎫 Total Tickets:</strong> <?php echo $booking['total_tickets']; ?></p>
                            <p><strong>💰 Total Amount:</strong> <?php echo formatCurrency($booking['total_amount']); ?></p>
                            <p><strong>📆 Booked On:</strong> <?php echo formatDate($booking['booking_date']); ?></p>
                        </div>
                    </div>
                    
                    <!-- Ticket Details -->
                    <div style="background: var(--frost-blue); padding: 15px; border-radius: 10px;">
                        <h4 style="margin-bottom: 15px; color: var(--christmas-green);">Ticket Details</h4>
                        <div class="table-container" style="box-shadow: none;">
                            <table style="font-size: 0.9rem;">
                                <thead>
                                    <tr>
                                        <th>Seat Type</th>
                                        <th>Ticket Type</th>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($details as $detail): ?>
                                        <tr>
                                            <td>
                                                <?php echo $detail['seat_type'] === 'with_table' ? '💺 With Table' : '🪑 Without Table'; ?>
                                            </td>
                                            <td><?php echo ucfirst($detail['ticket_type']); ?></td>
                                            <td><?php echo $detail['quantity']; ?></td>
                                            <td><?php echo formatCurrency($detail['unit_price']); ?></td>
                                            <td><strong><?php echo formatCurrency($detail['subtotal']); ?></strong></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr style="background: var(--christmas-green); color: white;">
                                        <td colspan="4" style="text-align: right;"><strong>Total:</strong></td>
                                        <td><strong><?php echo formatCurrency($booking['total_amount']); ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    
                    <?php if ($booking['adult_photo']): ?>
                        <div style="margin-top: 15px;">
                            <p><strong>👤 Adult Photo on file:</strong> ✓ Uploaded</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        
    <?php endif; ?>
    
    <!-- Back Button -->
    <div style="margin-top: 30px;">
        <a href="<?php echo SITE_URL; ?>/user/dashboard.php" class="btn btn-gold">
            ← Back to Dashboard
        </a>
        <a href="<?php echo SITE_URL; ?>/events.php" class="btn btn-primary">
            🎪 Book More Events
        </a>
    </div>
    
</div>

<?php require_once '../includes/footer.php'; ?>