<?php
/**
 * User Dashboard
 * London Community Park Christmas Event Booking System
 */

$pageTitle = 'My Dashboard';
require_once '../includes/header.php';

// Require user login
requireLogin();

// Get user information
$user = getUserById($pdo, $_SESSION['user_id']);

// Get user's bookings count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$bookingCount = $stmt->fetchColumn();

// Get user's recent bookings
$stmt = $pdo->prepare("
    SELECT b.*, e.event_name, e.event_date, e.event_time, e.venue
    FROM bookings b
    JOIN events e ON b.event_id = e.event_id
    WHERE b.user_id = ?
    ORDER BY b.booking_date DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$recentBookings = $stmt->fetchAll();

// Get total spent
$stmt = $pdo->prepare("SELECT SUM(total_amount) FROM bookings WHERE user_id = ? AND booking_status = 'confirmed'");
$stmt->execute([$_SESSION['user_id']]);
$totalSpent = $stmt->fetchColumn() ?: 0;

// Get upcoming events count
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM bookings b
    JOIN events e ON b.event_id = e.event_id
    WHERE b.user_id = ? AND e.event_date >= CURDATE() AND b.booking_status = 'confirmed'
");
$stmt->execute([$_SESSION['user_id']]);
$upcomingEvents = $stmt->fetchColumn();
?>

<!-- Page Header -->
<section class="hero" style="padding: 40px 20px;">
    <div class="container">
        <h1>👋 Welcome, <?php echo sanitize($user['first_name']); ?>!</h1>
        <p>Manage your bookings and account details</p>
    </div>
</section>

<div class="container">
    
    <?php echo displayMessage(); ?>
    
    <!-- Stats Overview -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo $bookingCount; ?></div>
            <div class="stat-label">Total Bookings</div>
        </div>
        
        <div class="stat-card green">
            <div class="stat-number"><?php echo $upcomingEvents; ?></div>
            <div class="stat-label">Upcoming Events</div>
        </div>
        
        <div class="stat-card gold">
            <div class="stat-number"><?php echo formatCurrency($totalSpent); ?></div>
            <div class="stat-label">Total Spent</div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="card" style="margin: 30px 0;">
        <div class="card-body" style="text-align: center;">
            <h3 style="margin-bottom: 20px;">🎄 Quick Actions</h3>
            <a href="<?php echo SITE_URL; ?>/events.php" class="btn btn-primary" style="margin: 5px;">
                🎪 Browse Events
            </a>
            <a href="<?php echo SITE_URL; ?>/user/bookings.php" class="btn btn-success" style="margin: 5px;">
                🎫 My Bookings
            </a>
            <a href="<?php echo SITE_URL; ?>/user/profile.php" class="btn btn-gold" style="margin: 5px;">
                👤 Edit Profile
            </a>
        </div>
    </div>
    
    <!-- Recent Bookings -->
    <div class="card">
        <div class="card-header">
            <h2>🎫 Recent Bookings</h2>
        </div>
        <div class="card-body">
            <?php if (empty($recentBookings)): ?>
                <div style="text-align: center; padding: 40px;">
                    <p style="font-size: 3rem; margin-bottom: 15px;">🎭</p>
                    <h3>No Bookings Yet</h3>
                    <p>You haven't booked any events yet. Start exploring our Christmas events!</p>
                    <a href="<?php echo SITE_URL; ?>/events.php" class="btn btn-primary" style="margin-top: 15px;">
                        Browse Events
                    </a>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Event</th>
                                <th>Date & Time</th>
                                <th>Tickets</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentBookings as $booking): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo sanitize($booking['booking_reference']); ?></strong>
                                    </td>
                                    <td><?php echo sanitize($booking['event_name']); ?></td>
                                    <td>
                                        <?php echo formatDate($booking['event_date']); ?><br>
                                        <small><?php echo formatTime($booking['event_time']); ?></small>
                                    </td>
                                    <td><?php echo $booking['total_tickets']; ?></td>
                                    <td><?php echo formatCurrency($booking['total_amount']); ?></td>
                                    <td>
                                        <?php
                                        $statusClass = 'badge-info';
                                        if ($booking['booking_status'] === 'confirmed') $statusClass = 'badge-success';
                                        elseif ($booking['booking_status'] === 'cancelled') $statusClass = 'badge-danger';
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?>">
                                            <?php echo ucfirst($booking['booking_status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div style="text-align: right; margin-top: 20px;">
                    <a href="<?php echo SITE_URL; ?>/user/bookings.php" class="btn btn-success btn-sm">
                        View All Bookings →
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Account Info -->
    <div class="card" style="margin-top: 30px;">
        <div class="card-header">
            <h2>👤 Account Information</h2>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <div>
                    <p><strong>Name:</strong> <?php echo sanitize($user['first_name'] . ' ' . $user['last_name']); ?></p>
                    <p><strong>Username:</strong> <?php echo sanitize($user['username']); ?></p>
                    <p><strong>Email:</strong> <?php echo sanitize($user['email']); ?></p>
                </div>
                <div>
                    <p><strong>Phone:</strong> <?php echo sanitize($user['phone'] ?: 'Not provided'); ?></p>
                    <p><strong>Member Since:</strong> <?php echo formatDate($user['created_at']); ?></p>
                    <p><strong>Account Type:</strong> <span class="badge badge-success">Customer</span></p>
                </div>
            </div>
            
            <div style="margin-top: 20px;">
                <a href="<?php echo SITE_URL; ?>/user/profile.php" class="btn btn-primary btn-sm">
                    ✏️ Edit Profile
                </a>
            </div>
        </div>
    </div>
    
</div>

<?php require_once '../includes/footer.php'; ?>