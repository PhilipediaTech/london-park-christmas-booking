<?php
/**
 * Admin - Edit Event
 * London Community Park Christmas Event Booking System
 */

$pageTitle = 'Edit Event';
require_once '../includes/header.php';

requireLogin();
requireAdmin();

$eventId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($eventId <= 0) {
    $_SESSION['error'] = 'Invalid event ID';
    redirect(SITE_URL . '/admin/events.php');
}

// Get event details
$event = getEventById($pdo, $eventId);
if (!$event) {
    $_SESSION['error'] = 'Event not found';
    redirect(SITE_URL . '/admin/events.php');
}

// Get seats
$stmt = $pdo->prepare("SELECT * FROM seats WHERE event_id = ?");
$stmt->execute([$eventId]);
$seats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Get prices
$stmt = $pdo->prepare("SELECT CONCAT(seat_type, '_', ticket_type) as key_name, price FROM prices WHERE event_id = ?");
$stmt->execute([$eventId]);
$prices = [];
while ($row = $stmt->fetch()) {
    $prices[$row['key_name']] = $row['price'];
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Invalid form submission';
    } else {
        $eventName = sanitize($_POST['event_name'] ?? '');
        $eventDescription = sanitize($_POST['event_description'] ?? '');
        $eventDate = sanitize($_POST['event_date'] ?? '');
        $eventTime = sanitize($_POST['event_time'] ?? '');
        $venue = sanitize($_POST['venue'] ?? '');
        $requiresAdult = isset($_POST['requires_adult']) ? 1 : 0;
        $maxTickets = (int)($_POST['max_tickets'] ?? 8);
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        if (empty($eventName)) $errors[] = 'Event name is required';
        if (empty($eventDate)) $errors[] = 'Event date is required';
        if (empty($eventTime)) $errors[] = 'Event time is required';
        if (empty($venue)) $errors[] = 'Venue is required';
        
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("
                    UPDATE events 
                    SET event_name = ?, event_description = ?, event_date = ?, event_time = ?, 
                        venue = ?, requires_adult = ?, max_tickets_per_booking = ?, is_active = ?
                    WHERE event_id = ?
                ");
                $stmt->execute([
                    $eventName, $eventDescription, $eventDate, $eventTime,
                    $venue, $requiresAdult, $maxTickets, $isActive, $eventId
                ]);
                
                $_SESSION['success'] = 'Event updated successfully!';
                redirect(SITE_URL . '/admin/events.php');
                
            } catch (Exception $e) {
                $errors[] = 'Update failed. Please try again.';
            }
        }
        
        // Refresh event data
        $event = getEventById($pdo, $eventId);
    }
}

$csrfToken = generateCsrfToken();
?>

<section class="hero" style="padding: 40px 20px;">
    <div class="container">
        <h1>✏️ Edit Event</h1>
        <p><?php echo sanitize($event['event_name']); ?></p>
    </div>
</section>

<div class="container" style="max-width: 800px;">
    
    <?php echo displayMessage(); ?>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <span class="alert-icon">✕</span>
            <ul style="margin: 0; padding-left: 20px;">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo sanitize($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h2>📝 Event Details</h2>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                
                <div class="form-group">
                    <label for="event_name" class="required">Event Name</label>
                    <input type="text" id="event_name" name="event_name" class="form-control" 
                           value="<?php echo sanitize($event['event_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="event_description">Description</label>
                    <textarea id="event_description" name="event_description" class="form-control" 
                              rows="4"><?php echo sanitize($event['event_description']); ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="event_date" class="required">Event Date</label>
                        <input type="date" id="event_date" name="event_date" class="form-control" 
                               value="<?php echo $event['event_date']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="event_time" class="required">Event Time</label>
                        <input type="time" id="event_time" name="event_time" class="form-control" 
                               value="<?php echo $event['event_time']; ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="venue" class="required">Venue</label>
                    <input type="text" id="venue" name="venue" class="form-control" 
                           value="<?php echo sanitize($event['venue']); ?>" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="max_tickets">Max Tickets Per Booking</label>
                        <input type="number" id="max_tickets" name="max_tickets" class="form-control" 
                               value="<?php echo $event['max_tickets_per_booking']; ?>" min="1" max="20">
                    </div>
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div style="padding: 14px 0; display: flex; gap: 20px;">
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="checkbox" name="requires_adult" <?php echo $event['requires_adult'] ? 'checked' : ''; ?>>
                                <span>Requires Adult</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="checkbox" name="is_active" <?php echo $event['is_active'] ? 'checked' : ''; ?>>
                                <span>Active</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Current Stats -->
                <div style="background: var(--frost-blue); padding: 20px; border-radius: 10px; margin: 25px 0;">
                    <h4 style="margin-bottom: 15px; color: var(--christmas-green);">📊 Current Statistics</h4>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; text-align: center;">
                        <div>
                            <p style="font-size: 1.5rem; font-weight: bold; color: var(--christmas-red);">
                                <?php echo $event['total_capacity']; ?>
                            </p>
                            <small>Total Capacity</small>
                        </div>
                        <div>
                            <p style="font-size: 1.5rem; font-weight: bold; color: var(--christmas-green);">
                                <?php 
                                $stmt = $pdo->prepare("SELECT SUM(available_seats) FROM seats WHERE event_id = ?");
                                $stmt->execute([$eventId]);
                                echo $stmt->fetchColumn() ?? 0;
                                ?>
                            </p>
                            <small>Available Seats</small>
                        </div>
                        <div>
                            <p style="font-size: 1.5rem; font-weight: bold; color: var(--gold-dark);">
                                <?php 
                                $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE event_id = ? AND booking_status != 'cancelled'");
                                $stmt->execute([$eventId]);
                                echo $stmt->fetchColumn();
                                ?>
                            </p>
                            <small>Bookings</small>
                        </div>
                    </div>
                </div>
                
                <div style="display: flex; gap: 15px;">
                    <button type="submit" class="btn btn-primary">💾 Save Changes</button>
                    <a href="<?php echo SITE_URL; ?>/admin/events.php" class="btn btn-gold">← Cancel</a>
                </div>
            </form>
        </div>
    </div>
    
</div>

<?php require_once '../includes/footer.php'; ?>