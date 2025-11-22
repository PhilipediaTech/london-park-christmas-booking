<?php
/**
 * Admin - Events Management
 * London Community Park Christmas Event Booking System
 */

$pageTitle = 'Manage Events';
require_once '../includes/header.php';

// Require admin access
requireLogin();
requireAdmin();

// Get all events with statistics
$sql = "SELECT e.*, 
        (SELECT SUM(s.total_seats) FROM seats s WHERE s.event_id = e.event_id) as total_seats,
        (SELECT SUM(s.available_seats) FROM seats s WHERE s.event_id = e.event_id) as available_seats,
        (SELECT COUNT(*) FROM bookings b WHERE b.event_id = e.event_id) as total_bookings,
        (SELECT SUM(b.total_amount) FROM bookings b WHERE b.event_id = e.event_id AND b.booking_status = 'confirmed') as revenue
        FROM events e
        ORDER BY e.event_date ASC";

$events = $pdo->query($sql)->fetchAll();
?>

<!-- Page Header -->
<section class="hero" style="padding: 40px 20px;">
    <div class="container">
        <h1>🎪 Events Management</h1>
        <p>View and manage all Christmas events</p>
    </div>
</section>

<div class="container">
    
    <?php echo displayMessage(); ?>
    
    <!-- Events Table -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h2>📋 Events List</h2>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Date & Time</th>
                            <th>Venue</th>
                            <th>Capacity</th>
                            <th>Bookings</th>
                            <th>Revenue</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                            <?php
                            $soldSeats = $event['total_seats'] - $event['available_seats'];
                            $percentSold = $event['total_seats'] > 0 ? round(($soldSeats / $event['total_seats']) * 100) : 0;
                            $isPast = strtotime($event['event_date']) < strtotime('today');
                            ?>
                            <tr style="<?php echo $isPast ? 'opacity: 0.6;' : ''; ?>">
                                <td>
                                    <strong><?php echo sanitize($event['event_name']); ?></strong>
                                    <?php if ($event['requires_adult']): ?>
                                        <br><span class="badge badge-warning" style="font-size: 0.7rem;">Adult Required</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo formatDate($event['event_date']); ?>
                                    <br><small><?php echo formatTime($event['event_time']); ?></small>
                                </td>
                                <td><?php echo sanitize($event['venue']); ?></td>
                                <td>
                                    <strong><?php echo $event['available_seats']; ?></strong> / <?php echo $event['total_seats']; ?>
                                    <div style="background: #eee; border-radius: 10px; height: 8px; margin-top: 5px; overflow: hidden;">
                                        <div style="background: <?php echo $percentSold > 80 ? 'var(--christmas-red)' : 'var(--christmas-green)'; ?>; height: 100%; width: <?php echo $percentSold; ?>%;"></div>
                                    </div>
                                    <small><?php echo $percentSold; ?>% sold</small>
                                </td>
                                <td style="text-align: center;">
                                    <strong><?php echo $event['total_bookings'] ?? 0; ?></strong>
                                </td>
                                <td>
                                    <strong><?php echo formatCurrency($event['revenue'] ?? 0); ?></strong>
                                </td>
                                <td>
                                    <?php if ($isPast): ?>
                                        <span class="badge badge-warning">Past</span>
                                    <?php elseif ($event['is_active']): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Summary -->
    <div class="card" style="margin-top: 30px;">
        <div class="card-body">
            <h3 style="margin-bottom: 20px;">📊 Events Summary</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <div>
                    <strong>Total Events:</strong> <?php echo count($events); ?>
                </div>
                <div>
                    <strong>Total Capacity:</strong> <?php echo number_format(array_sum(array_column($events, 'total_seats'))); ?> seats
                </div>
                <div>
                    <strong>Seats Available:</strong> <?php echo number_format(array_sum(array_column($events, 'available_seats'))); ?> seats
                </div>
                <div>
                    <strong>Total Revenue:</strong> <?php echo formatCurrency(array_sum(array_column($events, 'revenue'))); ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Back Button -->
    <div style="margin-top: 30px;">
        <a href="<?php echo SITE_URL; ?>/admin/index.php" class="btn btn-gold">
            ← Back to Dashboard
        </a>
    </div>
    
</div>

<?php require_once '../includes/footer.php'; ?>