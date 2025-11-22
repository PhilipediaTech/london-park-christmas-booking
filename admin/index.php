<?php
/**
 * Admin Dashboard
 * London Community Park Christmas Event Booking System
 */

$pageTitle = 'Admin Dashboard';
require_once '../includes/header.php';

// Require admin access
requireLogin();
requireAdmin();

// Get statistics
$totalUsers = countUsers($pdo);
$totalBookings = countBookings($pdo);
$totalEvents = countEvents($pdo);

// Get total revenue
$stmt = $pdo->query("SELECT SUM(total_amount) FROM bookings WHERE booking_status = 'confirmed'");
$totalRevenue = $stmt->fetchColumn() ?: 0;

// Get recent bookings
$stmt = $pdo->query("
    SELECT b.*, u.first_name, u.last_name, u.email, e.event_name, e.event_date
    FROM bookings b
    JOIN users u ON b.user_id = u.user_id
    JOIN events e ON b.event_id = e.event_id
    ORDER BY b.booking_date DESC
    LIMIT 10
");
$recentBookings = $stmt->fetchAll();

// Get recent users
$stmt = $pdo->query("
    SELECT * FROM users 
    WHERE role = 'customer' 
    ORDER BY created_at DESC 
    LIMIT 5
");
$recentUsers = $stmt->fetchAll();
?>

<!-- Page Header -->
<section class="hero" style="padding: 40px 20px;">
    <div class="container">
        <h1>⚙️ Admin Dashboard</h1>
        <p>Welcome, <?php echo sanitize($_SESSION['first_name']); ?>! Manage the Christmas Event Booking System</p>
    </div>
</section>

<div class="container">
    
    <?php echo displayMessage(); ?>
    
    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo $totalUsers; ?></div>
            <div class="stat-label">Registered Users</div>
        </div>
        
        <div class="stat-card green">
            <div class="stat-number"><?php echo $totalBookings; ?></div>
            <div class="stat-label">Total Bookings</div>
        </div>
        
        <div class="stat-card gold">
            <div class="stat-number"><?php echo formatCurrency($totalRevenue); ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-number"><?php echo $totalEvents; ?></div>
            <div class="stat-label">Active Events</div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="card" style="margin: 30px 0;">
        <div class="card-body" style="text-align: center;">
            <h3 style="margin-bottom: 20px;">🎄 Quick Actions</h3>
            <a href="<?php echo SITE_URL; ?>/admin/users.php" class="btn btn-primary" style="margin: 5px;">
                👥 Manage Users
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/events.php" class="btn btn-success" style="margin: 5px;">
                🎪 Manage Events
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/bookings.php" class="btn btn-gold" style="margin: 5px;">
                🎫 View All Bookings
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/add_user.php" class="btn btn-primary" style="margin: 5px;">
                ➕ Add New User
            </a>
        </div>
    </div>
    
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
        
        <!-- Recent Bookings -->
        <div class="card">
            <div class="card-header">
                <h2>🎫 Recent Bookings</h2>
            </div>
            <div class="card-body">
                <?php if (empty($recentBookings)): ?>
                    <p style="text-align: center; color: #666;">No bookings yet.</p>
                <?php else: ?>
                    <div class="table-container" style="box-shadow: none;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Customer</th>
                                    <th>Event</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentBookings as $booking): ?>
                                    <tr>
                                        <td><strong><?php echo sanitize($booking['booking_reference']); ?></strong></td>
                                        <td>
                                            <?php echo sanitize($booking['first_name'] . ' ' . $booking['last_name']); ?>
                                            <br><small><?php echo sanitize($booking['email']); ?></small>
                                        </td>
                                        <td>
                                            <?php echo sanitize($booking['event_name']); ?>
                                            <br><small><?php echo formatDate($booking['event_date']); ?></small>
                                        </td>
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
                    <div style="text-align: right; margin-top: 15px;">
                        <a href="<?php echo SITE_URL; ?>/admin/bookings.php" class="btn btn-sm btn-success">
                            View All Bookings →
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Recent Users -->
        <div class="card">
            <div class="card-header">
                <h2>👥 Recent Users</h2>
            </div>
            <div class="card-body">
                <?php if (empty($recentUsers)): ?>
                    <p style="text-align: center; color: #666;">No users registered yet.</p>
                <?php else: ?>
                    <ul style="list-style: none; padding: 0;">
                        <?php foreach ($recentUsers as $user): ?>
                            <li style="padding: 15px; border-bottom: 1px solid #eee; display: flex; align-items: center; gap: 15px;">
                                <div style="width: 45px; height: 45px; background: var(--christmas-green); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                    <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <strong><?php echo sanitize($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                                    <br><small style="color: #666;"><?php echo sanitize($user['email']); ?></small>
                                    <br><small style="color: #999;">Joined: <?php echo formatDate($user['created_at']); ?></small>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div style="text-align: right; margin-top: 15px;">
                        <a href="<?php echo SITE_URL; ?>/admin/users.php" class="btn btn-sm btn-primary">
                            View All Users →
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
    
</div>

<?php require_once '../includes/footer.php'; ?>