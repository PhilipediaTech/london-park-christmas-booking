<?php
/**
 * Events Browsing Page
 * London Community Park Christmas Event Booking System
 */

$pageTitle = 'Christmas Events';
require_once 'includes/header.php';

// Get all active events with their prices
$sql = "SELECT e.*, 
        MIN(p.price) as min_price, 
        MAX(p.price) as max_price,
        SUM(s.available_seats) as total_available
        FROM events e
        LEFT JOIN prices p ON e.event_id = p.event_id
        LEFT JOIN seats s ON e.event_id = s.event_id
        WHERE e.is_active = 1
        GROUP BY e.event_id
        ORDER BY e.event_date ASC";

$stmt = $pdo->query($sql);
$events = $stmt->fetchAll();
?>

<!-- Page Header -->
<section class="hero" style="padding: 40px 20px;">
    <div class="container">
        <h1>🎪 Christmas Events 2024 🎪</h1>
        <p>Discover magical experiences this festive season at London Community Park</p>
    </div>
</section>

<div class="container">
    
    <?php echo displayMessage(); ?>
    
    <!-- Events Filter Info -->
    <div class="card" style="margin-bottom: 30px;">
        <div class="card-body" style="text-align: center;">
            <p style="margin: 0;">
                <strong>📅 Event Season:</strong> December 18th - December 31st, 2024 | 
                <strong>🎫 <?php echo count($events); ?></strong> Events Available |
                <strong>💺</strong> Book up to 8 tickets per event
            </p>
        </div>
    </div>
    
    <!-- Events Grid -->
    <?php if (empty($events)): ?>
        <div class="card">
            <div class="card-body" style="text-align: center; padding: 50px;">
                <h3>No Events Available</h3>
                <p>There are currently no Christmas events scheduled. Please check back later!</p>
            </div>
        </div>
    <?php else: ?>
        <div class="events-grid">
            <?php foreach ($events as $event): ?>
                <?php
                // Determine event emoji
                $emoji = '🎄';
                if (stripos($event['event_name'], 'carol') !== false) $emoji = '🎵';
                elseif (stripos($event['event_name'], 'santa') !== false) $emoji = '🎅';
                elseif (stripos($event['event_name'], 'train') !== false) $emoji = '🚂';
                elseif (stripos($event['event_name'], 'year') !== false) $emoji = '🎆';
                elseif (stripos($event['event_name'], 'children') !== false) $emoji = '🎁';
                elseif (stripos($event['event_name'], 'water') !== false) $emoji = '💧';
                
                // Check if event is sold out
                $isSoldOut = ($event['total_available'] <= 0);
                
                // Check if event date has passed
                $isPast = (strtotime($event['event_date']) < strtotime('today'));
                ?>
                
                <div class="event-card" <?php if ($isSoldOut || $isPast) echo 'style="opacity: 0.7;"'; ?>>
                    <div class="event-image" style="position: relative;">
                        <?php echo $emoji; ?>
                        <?php if ($isSoldOut): ?>
                            <span style="position: absolute; top: 10px; right: 10px; background: #c41e3a; color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.8rem;">
                                SOLD OUT
                            </span>
                        <?php elseif ($isPast): ?>
                            <span style="position: absolute; top: 10px; right: 10px; background: #666; color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.8rem;">
                                EVENT PASSED
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="event-details">
                        <h3><?php echo sanitize($event['event_name']); ?></h3>
                        <p><?php echo sanitize($event['event_description']); ?></p>
                        
                        <div class="event-meta">
                            <span>📅 <?php echo formatDate($event['event_date']); ?></span>
                            <span>⏰ <?php echo formatTime($event['event_time']); ?></span>
                        </div>
                        <div class="event-meta">
                            <span>📍 <?php echo sanitize($event['venue']); ?></span>
                            <span>👥 <?php echo number_format($event['total_available']); ?> seats left</span>
                        </div>
                        
                        <div style="margin: 15px 0;">
                            <?php if ($event['requires_adult']): ?>
                                <span class="badge badge-warning">👨‍👩‍👧 Adult Supervision Required</span>
                            <?php endif; ?>
                            <span class="badge badge-info">Max <?php echo $event['max_tickets_per_booking']; ?> tickets</span>
                        </div>
                        
                        <div class="event-price">
                            <?php if ($event['min_price'] == $event['max_price']): ?>
                                From <?php echo formatCurrency($event['min_price']); ?>
                            <?php else: ?>
                                <?php echo formatCurrency($event['min_price']); ?> - <?php echo formatCurrency($event['max_price']); ?>
                            <?php endif; ?>
                        </div>
                        
                        <div style="margin-top: 20px;">
                            <?php if ($isSoldOut || $isPast): ?>
                                <button class="btn btn-primary btn-block" disabled style="opacity: 0.5; cursor: not-allowed;">
                                    <?php echo $isSoldOut ? '🚫 Sold Out' : '📅 Event Passed'; ?>
                                </button>
                            <?php elseif (!isLoggedIn()): ?>
                                <a href="<?php echo SITE_URL; ?>/login.php" class="btn btn-gold btn-block">
                                    🔑 Login to Book
                                </a>
                            <?php else: ?>
                                <a href="<?php echo SITE_URL; ?>/user/book_event.php?id=<?php echo $event['event_id']; ?>" 
                                   class="btn btn-primary btn-block">
                                    🎫 Book Now
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <!-- Booking Information -->
    <section style="margin-top: 50px;">
        <div class="card">
            <div class="card-header">
                <h2>📋 Booking Information</h2>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
                    <div>
                        <h4 style="color: var(--christmas-green); margin-bottom: 15px;">🎟️ Ticket Types</h4>
                        <ul style="line-height: 2;">
                            <li><strong>Adult:</strong> Ages 18 and over</li>
                            <li><strong>Child:</strong> Ages 3-17</li>
                            <li><strong>Senior:</strong> Ages 65 and over</li>
                        </ul>
                    </div>
                    <div>
                        <h4 style="color: var(--christmas-green); margin-bottom: 15px;">💺 Seating Options</h4>
                        <ul style="line-height: 2;">
                            <li><strong>Without Table:</strong> Standard seating</li>
                            <li><strong>With Table:</strong> Premium seating with table service</li>
                        </ul>
                    </div>
                    <div>
                        <h4 style="color: var(--christmas-green); margin-bottom: 15px;">⚠️ Important Notes</h4>
                        <ul style="line-height: 2;">
                            <li>Maximum 8 tickets per booking</li>
                            <li>Some events require adult supervision</li>
                            <li>Photo ID may be required for entry</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
</div>

<?php require_once 'includes/footer.php'; ?>