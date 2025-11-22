<?php
/**
 * Admin - Events Management (Updated with CRUD)
 * London Community Park Christmas Event Booking System
 */

$pageTitle = 'Manage Events';
require_once '../includes/header.php';

requireLogin();
requireAdmin();

// Get all events with statistics
$sql = "SELECT e.*, 
        (SELECT SUM(s.total_seats) FROM seats s WHERE s.event_id = e.event_id) as total_seats,
        (SELECT SUM(s.available_seats) FROM seats s WHERE s.event_id = e.event_id) as available_seats,
        (SELECT COUNT(*) FROM bookings b WHERE b.event_id = e.event_id AND b.booking_status != 'cancelled') as total_bookings,
        (SELECT SUM(b.total_amount) FROM bookings b WHERE b.event_id = e.event_id AND b.payment_status = 'paid') as revenue
        FROM events e
        ORDER BY e.event_date ASC";

$events = $pdo->query($sql)->fetchAll();
?>

<section class="hero" style="padding: 40px 20px;">
    <div class="container">
        <h1>🎪 Events Management</h1>
        <p>Create, edit, and manage Christmas events</p>
    </div>
</section>

<div class="container">
    
    <?php echo displayMessage(); ?>
    
    <!-- Add New Event Button -->
    <div style="margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <div>
            <span style="color: white; font-size: 1.1rem;">
                📊 Total: <strong><?php echo count($events); ?></strong> events | 
                💰 Revenue: <strong><?php echo formatCurrency(array_sum(array_column($events, 'revenue'))); ?></strong>
            </span>
        </div>
        <a href="<?php echo SITE_URL; ?>/admin/add_event.php" class="btn btn-success">
            ➕ Add New Event
        </a>
    </div>
    
    <!-- Events List -->
    <?php if (empty($events)): ?>
        <div class="card">
            <div class="card-body" style="text-align: center; padding: 60px;">
                <p style="font-size: 4rem; margin-bottom: 20px;">🎪</p>
                <h2>No Events Yet</h2>
                <p style="color: #666; margin: 20px 0;">Create your first Christmas event!</p>
                <a href="<?php echo SITE_URL; ?>/admin/add_event.php" class="btn btn-success">➕ Add New Event</a>
            </div>
        </div>
    <?php else: ?>
        
        <div class="card">
            <div class="card-body" style="padding: 0;">
                <div class="table-container" style="box-shadow: none;">
                    <table>
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Date & Time</th>
                                <th>Venue</th>
                                <th>Seats</th>
                                <th>Bookings</th>
                                <th>Revenue</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events as $event): ?>
                                <?php
                                $soldSeats = ($event['total_seats'] ?? 0) - ($event['available_seats'] ?? 0);
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
                                        <strong><?php echo $event['available_seats'] ?? 0; ?></strong> / <?php echo $event['total_seats'] ?? 0; ?>
                                        <div style="background: #eee; border-radius: 10px; height: 6px; margin-top: 5px; overflow: hidden;">
                                            <div style="background: <?php echo $percentSold > 80 ? '#c41e3a' : '#165b33'; ?>; height: 100%; width: <?php echo $percentSold; ?>%;"></div>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <strong><?php echo $event['total_bookings'] ?? 0; ?></strong>
                                    </td>
                                    <td><strong><?php echo formatCurrency($event['revenue'] ?? 0); ?></strong></td>
                                    <td>
                                        <?php if ($isPast): ?>
                                            <span class="badge badge-warning">Past</span>
                                        <?php elseif ($event['is_active']): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="<?php echo SITE_URL; ?>/admin/edit_event.php?id=<?php echo $event['event_id']; ?>" 
                                               class="btn btn-sm btn-primary" title="Edit">
                                                ✏️
                                            </a>
                                            <?php if (($event['total_bookings'] ?? 0) == 0): ?>
                                                <a href="<?php echo SITE_URL; ?>/admin/delete_event.php?id=<?php echo $event['event_id']; ?>" 
                                                   class="btn btn-sm btn-danger btn-delete" 
                                                   data-confirm="Are you sure you want to delete '<?php echo sanitize($event['event_name']); ?>'?"
                                                   title="Delete">
                                                    🗑️
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
    <?php endif; ?>
    
    <div style="margin-top: 30px;">
        <a href="<?php echo SITE_URL; ?>/admin/index.php" class="btn btn-gold">← Back to Dashboard</a>
    </div>
    
</div>

<?php require_once '../includes/footer.php'; ?>