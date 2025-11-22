<?php
/**
 * Download Ticket as PDF (HTML-based)
 * London Community Park Christmas Event Booking System
 * 
 * This creates an HTML page optimized for printing/saving as PDF
 */

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

requireLogin();

$bookingId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($bookingId <= 0) {
    $_SESSION['error'] = 'Invalid booking';
    redirect(SITE_URL . '/user/bookings.php');
}

// Get booking details
$stmt = $pdo->prepare("
    SELECT b.*, e.event_name, e.event_date, e.event_time, e.venue,
           u.first_name, u.last_name, u.email, u.phone
    FROM bookings b
    JOIN events e ON b.event_id = e.event_id
    JOIN users u ON b.user_id = u.user_id
    WHERE b.booking_id = ? AND b.user_id = ?
");
$stmt->execute([$bookingId, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    $_SESSION['error'] = 'Booking not found';
    redirect(SITE_URL . '/user/bookings.php');
}

// Get booking details
$stmt = $pdo->prepare("SELECT * FROM booking_details WHERE booking_id = ?");
$stmt->execute([$bookingId]);
$details = $stmt->fetchAll();

// QR Code URL
$qrData = urlencode($booking['booking_reference']);
$qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . $qrData;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket - <?php echo $booking['booking_reference']; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f0f0f0;
            padding: 20px;
        }
        .ticket {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .ticket-header {
            background: linear-gradient(135deg, #c41e3a 0%, #165b33 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .ticket-header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .ticket-body {
            padding: 30px;
            display: flex;
            gap: 30px;
        }
        .ticket-info {
            flex: 1;
        }
        .ticket-info h2 {
            color: #c41e3a;
            font-size: 1.5rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }
        .info-row {
            display: flex;
            margin-bottom: 12px;
        }
        .info-label {
            width: 120px;
            font-weight: bold;
            color: #165b33;
        }
        .info-value {
            flex: 1;
            color: #333;
        }
        .ticket-qr {
            text-align: center;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 15px;
        }
        .ticket-qr img {
            width: 150px;
            height: 150px;
        }
        .ticket-qr p {
            margin-top: 10px;
            font-size: 0.9rem;
            color: #666;
        }
        .ticket-details {
            background: #f9f9f9;
            padding: 20px;
            margin: 0 30px;
            border-radius: 10px;
        }
        .ticket-details h3 {
            color: #165b33;
            margin-bottom: 15px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .ticket-footer {
            background: #1a1a2e;
            color: white;
            padding: 25px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .ref-number {
            font-size: 1.8rem;
            font-weight: bold;
            color: #ffd700;
        }
        .total-amount {
            font-size: 1.8rem;
            font-weight: bold;
            color: #ffd700;
        }
        .print-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #c41e3a;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 1rem;
            border-radius: 30px;
            cursor: pointer;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        }
        .print-btn:hover {
            background: #a01830;
        }
        .instructions {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #fff3cd;
            border-radius: 10px;
            text-align: center;
        }
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .ticket {
                box-shadow: none;
            }
            .print-btn, .instructions {
                display: none;
            }
        }
    </style>
</head>
<body>
    
    <div class="instructions">
        <strong>📥 To save as PDF:</strong> Press <strong>Ctrl+P</strong> (or Cmd+P on Mac), then select "Save as PDF" as the destination.
    </div>
    
    <div class="ticket">
        <div class="ticket-header">
            <h1>🎄 London Community Park 🎄</h1>
            <p>Christmas Event Ticket <?php echo date('Y'); ?></p>
        </div>
        
        <div class="ticket-body">
            <div class="ticket-info">
                <h2><?php echo sanitize($booking['event_name']); ?></h2>
                
                <div class="info-row">
                    <span class="info-label">📅 Date:</span>
                    <span class="info-value"><?php echo formatDate($booking['event_date']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">⏰ Time:</span>
                    <span class="info-value"><?php echo formatTime($booking['event_time']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">📍 Venue:</span>
                    <span class="info-value"><?php echo sanitize($booking['venue']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">👤 Name:</span>
                    <span class="info-value"><?php echo sanitize($booking['first_name'] . ' ' . $booking['last_name']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">✉️ Email:</span>
                    <span class="info-value"><?php echo sanitize($booking['email']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">🎫 Tickets:</span>
                    <span class="info-value"><?php echo $booking['total_tickets']; ?></span>
                </div>
            </div>
            
            <div class="ticket-qr">
                <img src="<?php echo $qrCodeUrl; ?>" alt="QR Code">
                <p>Scan at entry</p>
                <p style="font-weight: bold; margin-top: 5px;"><?php echo $booking['booking_reference']; ?></p>
            </div>
        </div>
        
        <div class="ticket-details">
            <h3>Ticket Breakdown</h3>
            <?php foreach ($details as $detail): ?>
                <div class="detail-row">
                    <span>
                        <?php echo ucfirst($detail['ticket_type']); ?> 
                        (<?php echo $detail['seat_type'] === 'with_table' ? 'With Table' : 'Without Table'; ?>)
                        × <?php echo $detail['quantity']; ?>
                    </span>
                    <span><?php echo formatCurrency($detail['subtotal']); ?></span>
                </div>
            <?php endforeach; ?>
            <div class="detail-row" style="border-bottom: none; font-weight: bold; font-size: 1.1rem; margin-top: 10px;">
                <span>Total</span>
                <span style="color: #165b33;"><?php echo formatCurrency($booking['total_amount']); ?></span>
            </div>
        </div>
        
        <div class="ticket-footer">
            <div>
                <p style="font-size: 0.9rem; opacity: 0.8;">Booking Reference</p>
                <p class="ref-number"><?php echo $booking['booking_reference']; ?></p>
            </div>
            <div style="text-align: right;">
                <p style="font-size: 0.9rem; opacity: 0.8;">Total Paid</p>
                <p class="total-amount"><?php echo formatCurrency($booking['total_amount']); ?></p>
            </div>
        </div>
    </div>
    
    <button class="print-btn" onclick="window.print()">🖨️ Print / Save PDF</button>
    
</body>
</html>