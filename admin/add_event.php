<?php
/**
 * Admin - Add New Event
 * London Community Park Christmas Event Booking System
 */

$pageTitle = 'Add New Event';
require_once '../includes/header.php';

requireLogin();
requireAdmin();

$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Invalid form submission';
    } else {
        // Get form data
        $old['event_name'] = sanitize($_POST['event_name'] ?? '');
        $old['event_description'] = sanitize($_POST['event_description'] ?? '');
        $old['event_date'] = sanitize($_POST['event_date'] ?? '');
        $old['event_time'] = sanitize($_POST['event_time'] ?? '');
        $old['venue'] = sanitize($_POST['venue'] ?? '');
        $old['total_capacity'] = (int)($_POST['total_capacity'] ?? 0);
        $old['requires_adult'] = isset($_POST['requires_adult']) ? 1 : 0;
        $old['max_tickets'] = (int)($_POST['max_tickets'] ?? 8);
        
        // Seat allocation
        $old['seats_without_table'] = (int)($_POST['seats_without_table'] ?? 0);
        $old['seats_with_table'] = (int)($_POST['seats_with_table'] ?? 0);
        
        // Prices
        $old['price_adult_standard'] = (float)($_POST['price_adult_standard'] ?? 0);
        $old['price_child_standard'] = (float)($_POST['price_child_standard'] ?? 0);
        $old['price_senior_standard'] = (float)($_POST['price_senior_standard'] ?? 0);
        $old['price_adult_table'] = (float)($_POST['price_adult_table'] ?? 0);
        $old['price_child_table'] = (float)($_POST['price_child_table'] ?? 0);
        $old['price_senior_table'] = (float)($_POST['price_senior_table'] ?? 0);
        
        // Validation
        if (empty($old['event_name'])) $errors[] = 'Event name is required';
        if (empty($old['event_date'])) $errors[] = 'Event date is required';
        if (empty($old['event_time'])) $errors[] = 'Event time is required';
        if (empty($old['venue'])) $errors[] = 'Venue is required';
        if ($old['seats_without_table'] + $old['seats_with_table'] <= 0) {
            $errors[] = 'Total seats must be greater than 0';
        }
        if ($old['price_adult_standard'] <= 0) $errors[] = 'Adult price is required';
        
        if (empty($errors)) {
            try {
                $pdo->beginTransaction();
                
                $totalCapacity = $old['seats_without_table'] + $old['seats_with_table'];
                
                // Insert event
                $stmt = $pdo->prepare("
                    INSERT INTO events (event_name, event_description, event_date, event_time, venue, total_capacity, requires_adult, max_tickets_per_booking, is_active)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
                ");
                $stmt->execute([
                    $old['event_name'],
                    $old['event_description'],
                    $old['event_date'],
                    $old['event_time'],
                    $old['venue'],
                    $totalCapacity,
                    $old['requires_adult'],
                    $old['max_tickets']
                ]);
                $eventId = $pdo->lastInsertId();
                
                // Insert seats
                if ($old['seats_without_table'] > 0) {
                    $stmt = $pdo->prepare("INSERT INTO seats (event_id, seat_type, total_seats, available_seats) VALUES (?, 'without_table', ?, ?)");
                    $stmt->execute([$eventId, $old['seats_without_table'], $old['seats_without_table']]);
                }
                if ($old['seats_with_table'] > 0) {
                    $stmt = $pdo->prepare("INSERT INTO seats (event_id, seat_type, total_seats, available_seats) VALUES (?, 'with_table', ?, ?)");
                    $stmt->execute([$eventId, $old['seats_with_table'], $old['seats_with_table']]);
                }
                
                // Insert prices
                $priceStmt = $pdo->prepare("INSERT INTO prices (event_id, seat_type, ticket_type, price) VALUES (?, ?, ?, ?)");
                
                // Standard (without table) prices
                $priceStmt->execute([$eventId, 'without_table', 'adult', $old['price_adult_standard']]);
                $priceStmt->execute([$eventId, 'without_table', 'child', $old['price_child_standard']]);
                $priceStmt->execute([$eventId, 'without_table', 'senior', $old['price_senior_standard']]);
                
                // Table prices
                $priceStmt->execute([$eventId, 'with_table', 'adult', $old['price_adult_table']]);
                $priceStmt->execute([$eventId, 'with_table', 'child', $old['price_child_table']]);
                $priceStmt->execute([$eventId, 'with_table', 'senior', $old['price_senior_table']]);
                
                $pdo->commit();
                
                $_SESSION['success'] = 'Event created successfully!';
                redirect(SITE_URL . '/admin/events.php');
                
            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = 'Failed to create event: ' . $e->getMessage();
            }
        }
    }
}

$csrfToken = generateCsrfToken();
?>

<section class="hero" style="padding: 40px 20px;">
    <div class="container">
        <h1>➕ Add New Event</h1>
        <p>Create a new Christmas event</p>
    </div>
</section>

<div class="container">
    
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
    
    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
        
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
            
            <!-- Event Details -->
            <div class="card">
                <div class="card-header">
                    <h2>📝 Event Details</h2>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="event_name" class="required">Event Name</label>
                        <input type="text" id="event_name" name="event_name" class="form-control" 
                               value="<?php echo $old['event_name'] ?? ''; ?>" placeholder="e.g., Christmas Carol Concert" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="event_description">Description</label>
                        <textarea id="event_description" name="event_description" class="form-control" rows="4" 
                                  placeholder="Describe the event..."><?php echo $old['event_description'] ?? ''; ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="event_date" class="required">Event Date</label>
                            <input type="date" id="event_date" name="event_date" class="form-control" 
                                   value="<?php echo $old['event_date'] ?? ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="event_time" class="required">Event Time</label>
                            <input type="time" id="event_time" name="event_time" class="form-control" 
                                   value="<?php echo $old['event_time'] ?? ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="venue" class="required">Venue</label>
                        <input type="text" id="venue" name="venue" class="form-control" 
                               value="<?php echo $old['venue'] ?? ''; ?>" placeholder="e.g., Indoor Circus Theatre" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="max_tickets">Max Tickets Per Booking</label>
                            <input type="number" id="max_tickets" name="max_tickets" class="form-control" 
                                   value="<?php echo $old['max_tickets'] ?? 8; ?>" min="1" max="20">
                        </div>
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div style="padding: 14px 0;">
                                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                    <input type="checkbox" name="requires_adult" <?php echo ($old['requires_adult'] ?? 0) ? 'checked' : ''; ?>>
                                    <span>Requires Adult Supervision</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Seats & Pricing -->
            <div>
                <div class="card" style="margin-bottom: 20px;">
                    <div class="card-header" style="background: linear-gradient(135deg, var(--christmas-green) 0%, var(--christmas-green-light) 100%);">
                        <h2>💺 Seat Allocation</h2>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="seats_without_table">Standard Seats (Without Table)</label>
                            <input type="number" id="seats_without_table" name="seats_without_table" class="form-control" 
                                   value="<?php echo $old['seats_without_table'] ?? 100; ?>" min="0">
                        </div>
                        <div class="form-group">
                            <label for="seats_with_table">Premium Seats (With Table)</label>
                            <input type="number" id="seats_with_table" name="seats_with_table" class="form-control" 
                                   value="<?php echo $old['seats_with_table'] ?? 50; ?>" min="0">
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header" style="background: linear-gradient(135deg, var(--gold-dark) 0%, var(--gold) 100%);">
                        <h2 style="color: var(--dark-text);">💰 Pricing (£)</h2>
                    </div>
                    <div class="card-body">
                        <p style="font-weight: 600; margin-bottom: 10px;">Standard Seats:</p>
                        <div class="form-group">
                            <label>Adult</label>
                            <input type="number" name="price_adult_standard" class="form-control" 
                                   value="<?php echo $old['price_adult_standard'] ?? 25; ?>" min="0" step="0.01">
                        </div>
                        <div class="form-group">
                            <label>Child</label>
                            <input type="number" name="price_child_standard" class="form-control" 
                                   value="<?php echo $old['price_child_standard'] ?? 12.50; ?>" min="0" step="0.01">
                        </div>
                        <div class="form-group">
                            <label>Senior</label>
                            <input type="number" name="price_senior_standard" class="form-control" 
                                   value="<?php echo $old['price_senior_standard'] ?? 20; ?>" min="0" step="0.01">
                        </div>
                        
                        <hr style="margin: 20px 0;">
                        
                        <p style="font-weight: 600; margin-bottom: 10px;">Premium (Table) Seats:</p>
                        <div class="form-group">
                            <label>Adult</label>
                            <input type="number" name="price_adult_table" class="form-control" 
                                   value="<?php echo $old['price_adult_table'] ?? 45; ?>" min="0" step="0.01">
                        </div>
                        <div class="form-group">
                            <label>Child</label>
                            <input type="number" name="price_child_table" class="form-control" 
                                   value="<?php echo $old['price_child_table'] ?? 22.50; ?>" min="0" step="0.01">
                        </div>
                        <div class="form-group">
                            <label>Senior</label>
                            <input type="number" name="price_senior_table" class="form-control" 
                                   value="<?php echo $old['price_senior_table'] ?? 35; ?>" min="0" step="0.01">
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        
        <div style="margin-top: 30px; display: flex; gap: 15px;">
            <button type="submit" class="btn btn-success">✓ Create Event</button>
            <a href="<?php echo SITE_URL; ?>/admin/events.php" class="btn btn-gold">← Cancel</a>
        </div>
    </form>
    
</div>

<?php require_once '../includes/footer.php'; ?>