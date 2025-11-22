<?php
/**
 * Payment Page (Fake Payment Processing)
 * London Community Park Christmas Event Booking System
 */

$pageTitle = 'Payment';
require_once '../includes/header.php';

requireLogin();

// Get booking ID
$bookingId = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;

if ($bookingId <= 0) {
    $_SESSION['error'] = 'Invalid booking';
    redirect(SITE_URL . '/user/bookings.php');
}

// Get booking details
$stmt = $pdo->prepare("
    SELECT b.*, e.event_name, e.event_date, e.event_time, e.venue
    FROM bookings b
    JOIN events e ON b.event_id = e.event_id
    WHERE b.booking_id = ? AND b.user_id = ?
");
$stmt->execute([$bookingId, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    $_SESSION['error'] = 'Booking not found';
    redirect(SITE_URL . '/user/bookings.php');
}

if ($booking['payment_status'] === 'paid') {
    $_SESSION['success'] = 'This booking has already been paid';
    redirect(SITE_URL . '/user/bookings.php');
}

$errors = [];

// Process payment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Invalid form submission';
    } else {
        $cardName = sanitize($_POST['card_name'] ?? '');
        $cardNumber = preg_replace('/\s+/', '', $_POST['card_number'] ?? '');
        $expiryDate = sanitize($_POST['expiry_date'] ?? '');
        $cvv = sanitize($_POST['cvv'] ?? '');
        
        // Validation
        if (empty($cardName)) $errors[] = 'Cardholder name is required';
        if (empty($cardNumber) || strlen($cardNumber) < 13) $errors[] = 'Valid card number is required';
        if (empty($expiryDate)) $errors[] = 'Expiry date is required';
        if (empty($cvv) || strlen($cvv) < 3) $errors[] = 'Valid CVV is required';
        
        if (empty($errors)) {
            // Simulate payment processing (fake)
            $transactionId = 'TXN' . strtoupper(uniqid());
            $cardLastFour = substr($cardNumber, -4);
            
            try {
                $pdo->beginTransaction();
                
                // Insert payment record
                $stmt = $pdo->prepare("
                    INSERT INTO payments (booking_id, amount, payment_method, card_last_four, transaction_id, payment_status, payment_date)
                    VALUES (?, ?, 'Credit Card', ?, ?, 'completed', NOW())
                ");
                $stmt->execute([$bookingId, $booking['total_amount'], $cardLastFour, $transactionId]);
                
                // Update booking status
                $stmt = $pdo->prepare("
                    UPDATE bookings 
                    SET booking_status = 'confirmed', 
                        payment_status = 'paid', 
                        payment_date = NOW(),
                        payment_method = 'Credit Card',
                        card_last_four = ?
                    WHERE booking_id = ?
                ");
                $stmt->execute([$cardLastFour, $bookingId]);
                
                $pdo->commit();
                
                $_SESSION['success'] = 'Payment successful! Your booking is confirmed. Transaction ID: ' . $transactionId;
                redirect(SITE_URL . '/user/booking_confirmation.php?id=' . $bookingId);
                
            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = 'Payment failed. Please try again.';
            }
        }
    }
}

$csrfToken = generateCsrfToken();
?>

<div class="container" style="max-width: 900px; padding-top: 40px; padding-bottom: 40px;">
    
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
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
        
        <!-- Payment Form -->
        <div class="card">
            <div class="card-header">
                <h2>💳 Payment Details</h2>
                <p>Enter your card information</p>
            </div>
            <div class="card-body">
                
                <!-- Fake Payment Notice -->
                <div style="background: #fff3cd; border: 2px dashed #ffc107; padding: 15px; border-radius: 10px; margin-bottom: 25px; text-align: center;">
                    <strong>🎭 DEMO MODE</strong><br>
                    <small>This is a simulated payment. No real charges will be made.<br>
                    Use any fake card details to test.</small>
                </div>
                
                <form method="POST" action="" id="paymentForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    
                    <div class="form-group">
                        <label for="card_name" class="required">Cardholder Name</label>
                        <input type="text" 
                               id="card_name" 
                               name="card_name" 
                               class="form-control" 
                               placeholder="Name on card"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="card_number" class="required">Card Number</label>
                        <input type="text" 
                               id="card_number" 
                               name="card_number" 
                               class="form-control" 
                               placeholder="1234 5678 9012 3456"
                               maxlength="19"
                               required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="expiry_date" class="required">Expiry Date</label>
                            <input type="text" 
                                   id="expiry_date" 
                                   name="expiry_date" 
                                   class="form-control" 
                                   placeholder="MM/YY"
                                   maxlength="5"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="cvv" class="required">CVV</label>
                            <input type="text" 
                                   id="cvv" 
                                   name="cvv" 
                                   class="form-control" 
                                   placeholder="123"
                                   maxlength="4"
                                   required>
                        </div>
                    </div>
                    
                    <!-- Card Icons -->
                    <div style="display: flex; gap: 10px; margin-bottom: 20px; opacity: 0.7;">
                        <span style="font-size: 2rem;">💳</span>
                        <span style="font-size: 1.5rem;">VISA</span>
                        <span style="font-size: 1.5rem;">MC</span>
                        <span style="font-size: 1.5rem;">AMEX</span>
                    </div>
                    
                    <button type="submit" class="btn btn-success btn-block" style="font-size: 1.1rem; padding: 18px;">
                        🔒 Pay <?php echo formatCurrency($booking['total_amount']); ?>
                    </button>
                </form>
                
                <p style="text-align: center; margin-top: 15px; color: #666; font-size: 0.85rem;">
                    🔒 Your payment information is secure
                </p>
            </div>
        </div>
        
        <!-- Order Summary -->
        <div class="card" style="height: fit-content;">
            <div class="card-header" style="background: linear-gradient(135deg, var(--christmas-green) 0%, var(--christmas-green-light) 100%);">
                <h2>📋 Order Summary</h2>
            </div>
            <div class="card-body">
                <div style="margin-bottom: 20px;">
                    <h3 style="color: var(--christmas-red); margin-bottom: 15px;">
                        <?php echo sanitize($booking['event_name']); ?>
                    </h3>
                    
                    <p><strong>📅 Date:</strong> <?php echo formatDate($booking['event_date']); ?></p>
                    <p><strong>⏰ Time:</strong> <?php echo formatTime($booking['event_time']); ?></p>
                    <p><strong>📍 Venue:</strong> <?php echo sanitize($booking['venue']); ?></p>
                    <p><strong>🎫 Tickets:</strong> <?php echo $booking['total_tickets']; ?></p>
                    <p><strong>🔖 Reference:</strong> <?php echo sanitize($booking['booking_reference']); ?></p>
                </div>
                
                <hr style="margin: 20px 0;">
                
                <div style="display: flex; justify-content: space-between; font-size: 1.3rem; font-weight: bold;">
                    <span>Total:</span>
                    <span style="color: var(--christmas-green);"><?php echo formatCurrency($booking['total_amount']); ?></span>
                </div>
            </div>
        </div>
        
    </div>
    
    <div style="margin-top: 20px;">
        <a href="<?php echo SITE_URL; ?>/user/bookings.php" class="btn btn-gold">
            ← Back to Bookings
        </a>
    </div>
    
</div>

<script>
// Format card number with spaces
document.getElementById('card_number').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
    let formatted = value.match(/.{1,4}/g)?.join(' ') || value;
    e.target.value = formatted;
});

// Format expiry date
document.getElementById('expiry_date').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
    }
    e.target.value = value;
});

// Only numbers for CVV
document.getElementById('cvv').addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/\D/g, '');
});
</script>

<?php require_once '../includes/footer.php'; ?>