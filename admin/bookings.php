<?php
/**
 * Admin - View All Bookings
 * London Community Park Christmas Event Booking System
 */

$pageTitle = 'All Bookings';
require_once '../includes/header.php';

// Require admin access
requireLogin();
requireAdmin();

// Handle filters
$eventFilter = isset($_GET['event']) ? (int)$_GET['event'] : 0;
$statusFilter = sanitize($_GET['status'] ?? '');
$search = sanitize($_GET['search'] ?? '');

// Build query
$sql = "SELECT b.*, u.first_name, u.last_name, u.email, u.phone, 
               e.event_name, e.event_date, e.event_time, e.venue
        FROM bookings b
        JOIN users u ON b.user_id = u.user_id
        JOIN events e ON b.event_id = e.event_id
        WHERE 1=1";
$params = [];

if ($eventFilter > 0) {
    $sql .= " AND b.event_id = ?";
    $params[] = $eventFilter;
}

if (!empty($statusFilter)) {
    $sql .= " AND b.booking_status = ?";
    $params[] = $statusFilter;
}

if (!empty($search)) {
    $sql .= " AND (b.booking_reference LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}

$sql .= " ORDER BY b.booking_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

// Get all events for filter
$events = getAllEvents($pdo, false);

// Get statistics
$totalRevenue = array_sum(array_column(array_filter($bookings, fn($b) => $b['booking_status'] === 'confirmed'), 'total_amount'));
$totalTickets = array_sum(array_column($bookings, 'total_tickets'));
?>

<!-- Page Header -->
<section class="hero" style="padding: 40px 20px;">
    <div class="container">
        <h1>🎫 All Bookings</h1>
        <p>View and manage all event bookings</p>
    </div>
</section>

<div class="container">
    
    <?php echo displayMessage(); ?>
    
    <!-- Statistics -->
    <div class="stats-grid" style="margin-bottom: 30px;">
        <div class="stat-card">
            <div class="stat-number"><?php echo count($bookings); ?></div>
            <div class="stat-label">Total Bookings</div>
        </div>
        <div class="stat-card green">
            <div class="stat-number"><?php echo $totalTickets; ?></div>
            <div class="stat-label">Total Tickets Sold</div>
        </div>
        <div class="stat-card gold">
            <div class="stat-number"><?php echo formatCurrency($totalRevenue); ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="card" style="margin-bottom: 30px;">
        <div class="card-body">
            <form method="GET" action="" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
                <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
                    <label for="search">Search</label>
                    <input type="text" 
                           id="search" 
                           name="search" 
                           class="form-control" 
                           placeholder="Reference, name, or email..."
                           value="<?php echo sanitize($search); ?>">
                </div>
                
                <div class="form-group" style="min-width: 200px; margin-bottom: 0;">
                    <label for="event">Event</label>
                    <select id="event" name="event" class="form-control">
                        <option value="">All Events</option>
                        <?php foreach ($events as $event): ?>
                            <option value="<?php echo $event['event_id']; ?>" 
                                    <?php echo $eventFilter == $event['event_id'] ? 'selected' : ''; ?>>
                                <?php echo sanitize($event['event_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group" style="min-width: 150px; margin-bottom: 0;">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo $statusFilter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">🔍 Filter</button>
                <a href="<?php echo SITE_URL; ?>/admin/bookings.php" class="btn btn-gold">Clear</a>
            </form>
        </div>
    </div>
    
    <!-- Bookings Table -->
    <div class="card">
        <div class="card-header">
            <h2>📋 Bookings List</h2>
        </div>
        <div class="card-body">
            <?php if (empty($bookings)): ?>
                <p style="text-align: center; padding: 40px; color: #666;">
                    No bookings found matching your criteria.
                </p>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Customer</th>
                                <th>Event</th>
                                <th>Event Date</th>
                                <th>Tickets</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Booked On</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><strong><?php echo sanitize($booking['booking_reference']); ?></strong></td>
                                    <td>
                                        <?php echo sanitize($booking['first_name'] . ' ' . $booking['last_name']); ?>
                                        <br><small><?php echo sanitize($booking['email']); ?></small>
                                        <?php if ($booking['phone']): ?>
                                            <br><small>📞 <?php echo sanitize($booking['phone']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo sanitize($booking['event_name']); ?>
                                        <br><small>📍 <?php echo sanitize($booking['venue']); ?></small>
                                    </td>
                                    <td>
                                        <?php echo formatDate($booking['event_date']); ?>
                                        <br><small><?php echo formatTime($booking['event_time']); ?></small>
                                    </td>
                                    <td style="text-align: center;">
                                        <strong><?php echo $booking['total_tickets']; ?></strong>
                                    </td>
                                    <td><strong><?php echo formatCurrency($booking['total_amount']); ?></strong></td>
                                    <td>
                                        <?php
                                        $statusClass = 'badge-info';
                                        if ($booking['booking_status'] === 'confirmed') $statusClass = 'badge-success';
                                        elseif ($booking['booking_status'] === 'cancelled') $statusClass = 'badge-danger';
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?>">
                                            <?php echo ucfirst($booking['booking_status']); ?>
                                        </span>
                                        <?php if ($booking['adult_photo']): ?>
                                            <br><small>📷 Photo on file</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo formatDate($booking['booking_date']); ?>
                                        <br><small><?php echo date('g:i A', strtotime($booking['booking_date'])); ?></small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div style="margin-top: 20px; text-align: center; color: #666;">
                    Showing <?php echo count($bookings); ?> booking(s)
                </div>
            <?php endif; ?>
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