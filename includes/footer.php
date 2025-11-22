<?php
/**
 * Footer Template
 * London Community Park Christmas Event Booking System
 * 
 * This file is included at the bottom of every page
 */
?>
    </main>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <h3>🎄 London Community Park 🎄</h3>
            <p>Making Christmas Magical Since 1990</p>
            <p>📍 123 Park Lane, London, UK | 📞 +44 20 1234 5678 | ✉️ info@londonpark.com</p>
            
            <div class="footer-links">
                <a href="<?php echo SITE_URL; ?>/index.php">Home</a>
                <a href="<?php echo SITE_URL; ?>/events.php">Events</a>
                <a href="#">About Us</a>
                <a href="#">Contact</a>
                <a href="#">Privacy Policy</a>
                <a href="#">Terms & Conditions</a>
            </div>
            
            <p style="margin-top: 20px; opacity: 0.6;">
                © <?php echo date('Y'); ?> London Community Park. All rights reserved.<br>
                🎅 Wishing you a Merry Christmas and Happy New Year! 🎅
            </p>
        </div>
    </footer>
    
    <!-- JavaScript -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body>
</html>