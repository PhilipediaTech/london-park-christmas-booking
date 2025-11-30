<?php
/**
 * Homepage
 * London Community Park Christmas Event Booking System
 */

$pageTitle = 'Welcome';
require_once 'includes/header.php';

// Get upcoming events
$events = getAllEvents($pdo, true);

// Get first 3 events for featured section
$featuredEvents = array_slice($events, 0, 3);
?>

<!-- Hero Section -->
<!-- REPLACE THE HERO SECTION in index.php with this -->

<!-- Animated Hero Slider -->
<!-- REPLACE THE HERO SLIDER SECTION in index.php with this -->

<!-- Animated Hero Slider -->
<section class="hero-slider">
    <div class="slider-container">
        <!-- Slide 1 -->
        <div class="slide active">
            <div class="slide-image" style="background-image: url('<?php echo SITE_URL; ?>/assets/images/slider/slide1.jpg');"></div>
            <div class="slide-overlay"></div>
            <div class="slide-content">
                <h1 class="animate-text">🎄 Welcome to Christmas Magic 🎄</h1>
                <p class="animate-text-delay">Experience the wonder of the season at London Community Park</p>
                <a href="<?php echo SITE_URL; ?>/events.php" class="btn btn-gold animate-button">
                    🎫 Book Your Tickets Now
                </a>
            </div>
        </div>
        
        <!-- Slide 2 -->
        <div class="slide">
            <div class="slide-image" style="background-image: url('<?php echo SITE_URL; ?>/assets/images/slider/slide2.jpg');"></div>
            <div class="slide-overlay"></div>
            <div class="slide-content">
                <h1 class="animate-text">✨ Festive Events Await ✨</h1>
                <p class="animate-text-delay">From carol concerts to Santa's wonderland - magic for everyone!</p>
                <a href="<?php echo SITE_URL; ?>/events.php" class="btn btn-gold animate-button">
                    🎪 Explore Events
                </a>
            </div>
        </div>
        
        <!-- Slide 3 -->
        <div class="slide">
            <div class="slide-image" style="background-image: url('<?php echo SITE_URL; ?>/assets/images/slider/slide3.jpg');"></div>
            <div class="slide-overlay"></div>
            <div class="slide-content">
                <h1 class="animate-text">🎅 Meet Santa & More 🎅</h1>
                <p class="animate-text-delay">Create unforgettable memories with your loved ones this Christmas</p>
                <a href="<?php echo SITE_URL; ?>/register.php" class="btn btn-gold animate-button">
                    📝 Join Us Today
                </a>
            </div>
        </div>
        
        <!-- Slide 4 -->
        <div class="slide">
            <div class="slide-image" style="background-image: url('<?php echo SITE_URL; ?>/assets/images/slider/slide4.jpg');"></div>
            <div class="slide-overlay"></div>
            <div class="slide-content">
                <h1 class="animate-text">🎆 Ring in the New Year 🎆</h1>
                <p class="animate-text-delay">Celebrate New Year's Eve with fireworks, music, and champagne</p>
                <a href="<?php echo SITE_URL; ?>/events.php" class="btn btn-gold animate-button">
                    🎉 Book Now
                </a>
            </div>
        </div>
    </div>
    
    <!-- Slider Navigation -->
    <div class="slider-nav">
        <button class="slider-arrow prev" onclick="changeSlide(-1)">‹</button>
        <button class="slider-arrow next" onclick="changeSlide(1)">›</button>
    </div>
    
    <!-- Slider Dots -->
    <div class="slider-dots">
        <span class="dot active" onclick="goToSlide(0)"></span>
        <span class="dot" onclick="goToSlide(1)"></span>
        <span class="dot" onclick="goToSlide(2)"></span>
        <span class="dot" onclick="goToSlide(3)"></span>
    </div>
</section>

<!-- Rest of your index.php content continues here... -->

<div class="container">
    
    <?php echo displayMessage(); ?>
    
    <!-- About Section -->
    <section style="margin: 50px 0;">
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <h2 style="margin-bottom: 20px;">✨ About Our Park ✨</h2>
                <p style="font-size: 1.1rem; line-height: 1.8; max-width: 800px; margin: 0 auto;">
                    London Community Park has become a beloved destination for young people and families alike. 
                    Our state-of-the-art attractions include <strong>Sweeney</strong> - a private rail track with 
                    vintage steam engines, thrilling <strong>water sports</strong>, an enchanting 
                    <strong>indoor circus theatre</strong>, and many more exciting experiences.
                </p>
                <p style="font-size: 1.1rem; line-height: 1.8; max-width: 800px; margin: 20px auto 0;">
                    This Christmas season, we've prepared special events during the last two weeks of December. 
                    <strong>Book online now</strong> to secure your spot and enjoy a safe, crowd-free experience!
                </p>
            </div>
        </div>
    </section>
    
    <!-- Featured Events -->
    <section style="margin: 50px 0;">
        <h2 style="text-align: center; margin-bottom: 30px; color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">
            🎪 Featured Christmas Events 🎪
        </h2>
        
        <div class="events-grid">
            <?php foreach ($featuredEvents as $event): ?>
                <div class="event-card">
                    <div class="event-image">
                        <?php
                        // Christmas-themed emoji based on event name
                        $emoji = '🎄';
                        if (stripos($event['event_name'], 'carol') !== false) $emoji = '🎵';
                        elseif (stripos($event['event_name'], 'santa') !== false) $emoji = '🎅';
                        elseif (stripos($event['event_name'], 'train') !== false) $emoji = '🚂';
                        elseif (stripos($event['event_name'], 'year') !== false) $emoji = '🎆';
                        elseif (stripos($event['event_name'], 'children') !== false) $emoji = '🎁';
                        elseif (stripos($event['event_name'], 'water') !== false) $emoji = '💧';
                        echo $emoji;
                        ?>
                    </div>
                    <div class="event-details">
                        <h3><?php echo sanitize($event['event_name']); ?></h3>
                        <p><?php echo sanitize(substr($event['event_description'], 0, 100)) . '...'; ?></p>
                        
                        <div class="event-meta">
                            <span>📅 <?php echo formatDate($event['event_date']); ?></span>
                            <span>⏰ <?php echo formatTime($event['event_time']); ?></span>
                            <span>📍 <?php echo sanitize($event['venue']); ?></span>
                        </div>
                        
                        <?php if ($event['requires_adult']): ?>
                            <span class="badge badge-warning">👨‍👩‍👧 Adult Supervision Required</span>
                        <?php endif; ?>
                        
                        <div style="margin-top: 20px;">
                            <a href="<?php echo SITE_URL; ?>/user/book_event.php?id=<?php echo $event['event_id']; ?>" 
                               class="btn btn-primary btn-block">
                                🎫 Book Tickets
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="<?php echo SITE_URL; ?>/events.php" class="btn btn-success">
                View All Events →
            </a>
        </div>
    </section>
    
    <!-- Features Section -->
    <section style="margin: 50px 0;">
        <h2 style="text-align: center; margin-bottom: 30px; color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">
            🌟 Why Book Online? 🌟
        </h2>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">🎫</div>
                <div class="stat-label">Guaranteed Entry</div>
                <p style="margin-top: 10px; font-size: 0.9rem;">Secure your tickets in advance and skip the queues</p>
            </div>
            
            <div class="stat-card green">
                <div class="stat-number">💺</div>
                <div class="stat-label">Choose Your Seats</div>
                <p style="margin-top: 10px; font-size: 0.9rem;">Select from table or non-table seating options</p>
            </div>
            
            <div class="stat-card gold">
                <div class="stat-number">👨‍👩‍👧‍👦</div>
                <div class="stat-label">Family Friendly</div>
                <p style="margin-top: 10px; font-size: 0.9rem;">Book up to 8 tickets per transaction</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-number">🔒</div>
                <div class="stat-label">Safe & Secure</div>
                <p style="margin-top: 10px; font-size: 0.9rem;">Your data is protected with our secure system</p>
            </div>
        </div>
    </section>
    
    <!-- Call to Action -->
    <section style="margin: 50px 0;">
        <div class="card">
            <div class="card-header">
                <h2>🎅 Ready to Experience the Magic? 🎅</h2>
            </div>
            <div class="card-body" style="text-align: center;">
                <?php if (!isLoggedIn()): ?>
                    <p style="font-size: 1.1rem; margin-bottom: 25px;">
                        Create an account today and start booking your Christmas adventure!
                    </p>
                    <a href="<?php echo SITE_URL; ?>/register.php" class="btn btn-success" style="margin-right: 10px;">
                        📝 Register Now
                    </a>
                    <a href="<?php echo SITE_URL; ?>/login.php" class="btn btn-primary">
                        🔑 Login
                    </a>
                <?php else: ?>
                    <p style="font-size: 1.1rem; margin-bottom: 25px;">
                        Welcome back, <?php echo sanitize($_SESSION['first_name']); ?>! 
                        Browse our events and book your Christmas experience.
                    </p>
                    <a href="<?php echo SITE_URL; ?>/events.php" class="btn btn-gold">
                        🎪 Browse Events
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>
    
</div>

<?php require_once 'includes/footer.php'; ?>