<?php
/**
 * Event Booking Page
 * London Community Park Christmas Event Booking System
 */

$pageTitle = 'Book Event';
require_once '../includes/header.php';

// Require user login
requireLogin();

// Get event ID from URL
$eventId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($eventId <= 0) {
    $_SESSION['error'] = 'Invalid event selected';
    redirect(SITE_URL . '/events.php');
}

// Get event details
$event = getEventById($pdo, $eventId);

if (!$event || !$event['is_active']) {
    $_SESSION['error'] = 'Event not found or no longer available';
    redirect(SITE_URL . '/events.php');
}

// Get seats information
$stmt = $pdo->prepare("SELECT * FROM seats WHERE event_id = ?");
$stmt->execute([$eventId]);
$seats = $stmt->fetchAll();

// Get prices
$stmt = $pdo->prepare("SELECT * FROM prices WHERE event_id = ? ORDER BY seat_type, ticket_type");
$stmt->execute([$eventId]);
$prices = $stmt->fetchAll();

// Organize prices by seat type and ticket type
$priceMatrix = [];
foreach ($prices as $price) {
    $priceMatrix[$price['seat_type']][$price['ticket_type']] = $price['price'];
}

$errors = [];

// Process booking form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        $seatType = sanitize($_POST['seat_type'] ?? '');
        $adultTickets = (int)($_POST['adult_tickets'] ?? 0);
        $childTickets = (int)($_POST['child_tickets'] ?? 0);
        $seniorTickets = (int)($_POST['senior_tickets'] ?? 0);
        $totalTickets = $adultTickets + $childTickets + $seniorTickets;
        
        // Validation
        if (empty($seatType)) {
            $errors[] = 'Please select a seat type';
        }
        
        if ($totalTickets <= 0) {
            $errors[] = 'Please select at least one ticket';
        }
        
        if ($totalTickets > $event['max_tickets_per_booking']) {
            $errors[] = 'Maximum ' . $event['max_tickets_per_booking'] . ' tickets per booking';
        }
        
        // Check if adult is required
        if ($event['requires_adult'] && $adultTickets <= 0) {
            $errors[] = 'At least one adult ticket is required for this event';
        }
        
        // Check seat availability
        $seatAvailable = false;
        foreach ($seats as $seat) {
            if ($seat['seat_type'] === $seatType && $seat['available_seats'] >= $totalTickets) {
                $seatAvailable = true;
                break;
            }
        }
        
        if (!$seatAvailable) {
            $errors[] = 'Not enough seats available for the selected type';
        }
        
        // Handle photo upload for events requiring adult
        $photoPath = null;
        if ($event['requires_adult'] && isset($_FILES['adult_photo']) && $_FILES['adult_photo']['error'] === 0) {
            $photoPath = uploadImage($_FILES['adult_photo'], '../uploads/photos/');
            if (!$photoPath) {
                $errors[] = 'Failed to upload photo. Please try again.';
            }
        } elseif ($event['requires_adult'] && $adultTickets > 0) {
            $errors[] = 'Adult photo is required for this event';
        }
        
        if (empty($errors)) {
            // Calculate total
            $totalAmount = 0;
            if ($adultTickets > 0) {
                $totalAmount += $adultTickets * $priceMatrix[$seatType]['adult'];
            }
            if ($childTickets > 0) {
                $totalAmount += $childTickets * $priceMatrix[$seatType]['child'];
            }
            if ($seniorTickets > 0) {
                $totalAmount += $seniorTickets * $priceMatrix[$seatType]['senior'];
            }
            
            // Generate booking reference
            $bookingRef = generateBookingReference();
            
            try {
                $pdo->beginTransaction();
                
                // Insert booking
                $stmt = $pdo->prepare("
                    INSERT INTO bookings (user_id, event_id, booking_reference, total_tickets, total_amount, booking_status, adult_photo)
                    VALUES (?, ?, ?, ?, ?, 'confirmed', ?)
                ");
                $stmt->execute([
                    $_SESSION['user_id'],
                    $eventId,
                    $bookingRef,
                    $totalTickets,
                    $totalAmount,
                    $photoPath
                ]);
                $bookingId = $pdo->lastInsertId();
                
                // Insert booking details
                $detailStmt = $pdo->prepare("
                    INSERT INTO booking_details (booking_id, seat_type, ticket_type, quantity, unit_price, subtotal)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                
                if ($adultTickets > 0) {
                    $price = $priceMatrix[$seatType]['adult'];
                    $detailStmt->execute([$bookingId, $seatType, 'adult', $adultTickets, $price, $adultTickets * $price]);
                }
                if ($childTickets > 0) {
                    $price = $priceMatrix[$seatType]['child'];
                    $detailStmt->execute([$bookingId, $seatType, 'child', $childTickets, $price, $childTickets * $price]);
                }
                if ($seniorTickets > 0) {
                    $price = $priceMatrix[$seatType]['senior'];
                    $detailStmt->execute([$bookingId, $seatType, 'senior', $seniorTickets, $price, $seniorTickets * $price]);
                }
                
                // Update available seats
                $stmt = $pdo->prepare("
                    UPDATE seats SET available_seats = available_seats - ? 
                    WHERE event_id = ? AND seat_type = ?
                ");
                $stmt->execute([$totalTickets, $eventId, $seatType]);
                
                $pdo->commit();
                
                $_SESSION['success'] = 'Booking confirmed! Your reference is: ' . $bookingRef;
                redirect(SITE_URL . '/user/bookings.php');
                
            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = 'Booking failed. Please try again.';
            }
        }
    }
}

// Generate CSRF token
$csrfToken = generateCsrfToken();

// Determine event emoji
$emoji = '🎄';
if (stripos($event['event_name'], 'carol') !== false) $emoji = '🎵';
elseif (stripos($event['event_name'], 'santa') !== false) $emoji = '🎅';
elseif (stripos($event['event_name'], 'train') !== false) $emoji = '🚂';
elseif (stripos($event['event_name'], 'year') !== false) $emoji = '🎆';
elseif (stripos($event['event_name'], 'children') !== false) $emoji = '🎁';
elseif (stripos($event['event_name'], 'water') !== false) $emoji = '💧';
?>

<!-- Page Header -->
<section class="hero" style="padding: 40px 20px;">
    <div class="container">
        <h1>🎫 Book Tickets</h1>
        <p><?php echo sanitize($event['event_name']); ?></p>
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
    
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
        
        <!-- Booking Form -->
        <div class="card">
            <div class="card-header">
                <h2><?php echo $emoji; ?> <?php echo sanitize($event['event_name']); ?></h2>
            </div>
            <div class="card-body">
                
                <!-- Event Info -->
                <div style="background: var(--frost-blue); padding: 20px; border-radius: 10px; margin-bottom: 25px;">
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                        <p><strong>📅 Date:</strong> <?php echo formatDate($event['event_date']); ?></p>
                        <p><strong>⏰ Time:</strong> <?php echo formatTime($event['event_time']); ?></p>
                        <p><strong>📍 Venue:</strong> <?php echo sanitize($event['venue']); ?></p>
                        <p><strong>🎫 Max Tickets:</strong> <?php echo $event['max_tickets_per_booking']; ?> per booking</p>
                    </div>
                    <?php if ($event['requires_adult']): ?>
                        <p style="margin-top: 15px; color: #c41e3a;">
                            <strong>⚠️ Note:</strong> At least one adult ticket is required. Adult photo must be uploaded for identification.
                        </p>
                    <?php endif; ?>
                </div>
                
                <form method="POST" action="" enctype="multipart/form-data" data-validate>
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    
                    <!-- Seat Type Selection -->
                    <div class="form-group">
                        <label class="required">Select Seat Type</label>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-top: 10px;">
                            <?php foreach ($seats as $seat): ?>
                                <label style="display: block; padding: 20px; border: 2px solid #ddd; border-radius: 10px; cursor: pointer; transition: all 0.3s;">
                                    <input type="radio" 
                                           name="seat_type" 
                                           value="<?php echo $seat['seat_type']; ?>"
                                           style="margin-right: 10px;"
                                           <?php echo $seat['available_seats'] <= 0 ? 'disabled' : ''; ?>
                                           required>
                                    <strong><?php echo $seat['seat_type'] === 'with_table' ? '💺 With Table' : '🪑 Without Table'; ?></strong>
                                    <br>
                                    <small style="color: #666;">
                                        <?php echo $seat['available_seats']; ?> seats available
                                    </small>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Ticket Quantities -->
                    <h4 style="margin: 25px 0 15px; color: var(--christmas-green);">Select Ticket Quantities</h4>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="adult_tickets">Adult Tickets (18+)</label>
                            <select name="adult_tickets" id="adult_tickets" class="form-control ticket-quantity" data-price="0">
                                <?php for ($i = 0; $i <= 8; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                            <small class="price-display">Price varies by seat type</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="child_tickets">Child Tickets (3-17)</label>
                            <select name="child_tickets" id="child_tickets" class="form-control ticket-quantity" data-price="0">
                                <?php for ($i = 0; $i <= 8; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                            <small class="price-display">Price varies by seat type</small>
                        </div>
                    </div>
                    
                    <div class="form-group" style="max-width: 200px;">
                        <label for="senior_tickets">Senior Tickets (65+)</label>
                        <select name="senior_tickets" id="senior_tickets" class="form-control ticket-quantity" data-price="0">
                            <?php for ($i = 0; $i <= 8; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                        <small class="price-display">Price varies by seat type</small>
                    </div>
                    
                    <?php if ($event['requires_adult']): ?>
                        <!-- Adult Photo Upload -->
                        <div class="form-group" style="margin-top: 25px;">
                            <label for="adult_photo" class="required">Adult Photo (for identification)</label>
                            <input type="file" 
                                   id="adult_photo" 
                                   name="adult_photo" 
                                   class="form-control" 
                                   accept="image/*"
                                   required>
                            <small style="color: #666;">Upload a clear photo of the supervising adult. Max 5MB. JPG, PNG, or GIF.</small>
                            <img id="image-preview" style="display: none; max-width: 200px; margin-top: 10px; border-radius: 10px;">
                        </div>
                    <?php endif; ?>
                    
                    <button type="submit" class="btn btn-primary btn-block" style="margin-top: 25px; font-size: 1.1rem; padding: 15px;">
                        🎄 Confirm Booking
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Price Summary -->
        <div>
            <div class="card" style="position: sticky; top: 100px;">
                <div class="card-header">
                    <h2>💰 Price Guide</h2>
                </div>
                <div class="card-body">
                    <h4 style="color: var(--christmas-green);">🪑 Without Table</h4>
                    <ul style="list-style: none; padding: 0; margin-bottom: 20px;">
                        <li>Adult: <?php echo formatCurrency($priceMatrix['without_table']['adult']); ?></li>
                        <li>Child: <?php echo formatCurrency($priceMatrix['without_table']['child']); ?></li>
                        <li>Senior: <?php echo formatCurrency($priceMatrix['without_table']['senior']); ?></li>
                    </ul>
                    
                    <h4 style="color: var(--christmas-green);">💺 With Table</h4>
                    <ul style="list-style: none; padding: 0;">
                        <li>Adult: <?php echo formatCurrency($priceMatrix['with_table']['adult']); ?></li>
                        <li>Child: <?php echo formatCurrency($priceMatrix['with_table']['child']); ?></li>
                        <li>Senior: <?php echo formatCurrency($priceMatrix['with_table']['senior']); ?></li>
                    </ul>
                    
                    <hr style="margin: 20px 0;">
                    
                    <div id="booking-summary" style="display: none;">
                        <h4 style="color: var(--christmas-red);">Your Selection</h4>
                        <p><strong>Total Tickets:</strong> <span id="ticket-count">0</span></p>
                        <p style="font-size: 1.5rem; color: var(--christmas-red);">
                            <strong>Total:</strong> <span id="total-amount">£0.00</span>
                        </p>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 20px;">
                <a href="<?php echo SITE_URL; ?>/events.php" class="btn btn-gold btn-block">
                    ← Back to Events
                </a>
            </div>
        </div>
        
    </div>
    
</div>

<script>
// Price matrix from PHP
const priceMatrix = <?php echo json_encode($priceMatrix); ?>;

// Update prices when seat type changes
document.querySelectorAll('input[name="seat_type"]').forEach(radio => {
    radio.addEventListener('change', updatePrices);
});

// Update prices and totals when quantities change
document.querySelectorAll('.ticket-quantity').forEach(select => {
    select.addEventListener('change', calculateTotal);
});

function updatePrices() {
    const seatType = document.querySelector('input[name="seat_type"]:checked')?.value;
    if (!seatType) return;
    
    document.getElementById('adult_tickets').setAttribute('data-price', priceMatrix[seatType].adult);
    document.getElementById('child_tickets').setAttribute('data-price', priceMatrix[seatType].child);
    document.getElementById('senior_tickets').setAttribute('data-price', priceMatrix[seatType].senior);
    
    calculateTotal();
}

function calculateTotal() {
    const adultQty = parseInt(document.getElementById('adult_tickets').value) || 0;
    const childQty = parseInt(document.getElementById('child_tickets').value) || 0;
    const seniorQty = parseInt(document.getElementById('senior_tickets').value) || 0;
    
    const adultPrice = parseFloat(document.getElementById('adult_tickets').getAttribute('data-price')) || 0;
    const childPrice = parseFloat(document.getElementById('child_tickets').getAttribute('data-price')) || 0;
    const seniorPrice = parseFloat(document.getElementById('senior_tickets').getAttribute('data-price')) || 0;
    
    const totalTickets = adultQty + childQty + seniorQty;
    const totalAmount = (adultQty * adultPrice) + (childQty * childPrice) + (seniorQty * seniorPrice);
    
    document.getElementById('ticket-count').textContent = totalTickets;
    document.getElementById('total-amount').textContent = '£' + totalAmount.toFixed(2);
    
    const summaryDiv = document.getElementById('booking-summary');
    if (totalTickets > 0) {
        summaryDiv.style.display = 'block';
    } else {
        summaryDiv.style.display = 'none';
    }
    
    // Check max tickets
    if (totalTickets > 8) {
        alert('Maximum 8 tickets per booking');
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>