-- London Community Park Christmas Event Booking System
-- Database Schema

-- Drop existing tables if they exist (for fresh installation)
DROP TABLE IF EXISTS booking_details;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS prices;
DROP TABLE IF EXISTS seats;
DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS users;

-- Users Table: Stores both customers and administrators
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    photo VARCHAR(255),
    role ENUM('customer', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

-- Events Table: Stores all Christmas event information
CREATE TABLE events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(100) NOT NULL,
    event_description TEXT,
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    venue VARCHAR(100) NOT NULL,
    total_capacity INT NOT NULL,
    requires_adult TINYINT(1) DEFAULT 0,
    max_tickets_per_booking INT DEFAULT 8,
    event_image VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Seats Table: Stores seat types for each event
CREATE TABLE seats (
    seat_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    seat_type ENUM('without_table', 'with_table') NOT NULL,
    total_seats INT NOT NULL,
    available_seats INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Prices Table: Stores pricing for different seat types and age groups
CREATE TABLE prices (
    price_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    seat_type ENUM('without_table', 'with_table') NOT NULL,
    ticket_type ENUM('adult', 'child', 'senior') NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bookings Table: Stores main booking information
CREATE TABLE bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    booking_reference VARCHAR(20) NOT NULL UNIQUE,
    total_tickets INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    booking_status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    adult_photo VARCHAR(255),
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Booking Details Table: Stores individual ticket details
CREATE TABLE booking_details (
    detail_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    seat_type ENUM('without_table', 'with_table') NOT NULL,
    ticket_type ENUM('adult', 'child', 'senior') NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password, first_name, last_name, role) 
VALUES ('admin', 'admin@londonpark.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', 'admin');

-- Insert sample Christmas events
INSERT INTO events (event_name, event_description, event_date, event_time, venue, total_capacity, requires_adult, max_tickets_per_booking, event_image) VALUES
('Christmas Carol Concert', 'A magical evening of traditional Christmas carols performed by the London Community Choir', '2025-12-18', '19:00:00', 'Indoor Circus Theatre', 500, 0, 8, 'carol_concert.jpg'),
('Santa\'s Winter Wonderland', 'Meet Santa and enjoy festive activities for the whole family', '2025-12-20', '14:00:00', 'Main Park Area', 300, 1, 8, 'winter_wonderland.jpg'),
('Sweeney Steam Train Christmas Special', 'A magical journey on our vintage steam train with Christmas treats', '2025-12-21', '16:00:00', 'Sweeney Railway Station', 150, 1, 8, 'steam_train.jpg'),
('New Year\'s Eve Gala', 'Ring in the New Year with live music, fireworks, and champagne', '2025-12-31', '21:00:00', 'Indoor Circus Theatre', 400, 0, 8, 'new_year_gala.jpg'),
('Children\'s Christmas Party', 'Fun-filled party with games, entertainment, and presents', '2025-12-22', '11:00:00', 'Park Activity Centre', 200, 1, 8, 'kids_party.jpg'),
('Christmas Water Sports Festival', 'Winter water sports activities with festive twist', '2025-12-19', '10:00:00', 'Water Sports Centre', 100, 1, 8, 'water_sports.jpg');

-- Insert seats for each event
INSERT INTO seats (event_id, seat_type, total_seats, available_seats) VALUES
(1, 'without_table', 350, 350), (1, 'with_table', 150, 150),
(2, 'without_table', 200, 200), (2, 'with_table', 100, 100),
(3, 'without_table', 100, 100), (3, 'with_table', 50, 50),
(4, 'without_table', 250, 250), (4, 'with_table', 150, 150),
(5, 'without_table', 150, 150), (5, 'with_table', 50, 50),
(6, 'without_table', 70, 70), (6, 'with_table', 30, 30);

-- Insert prices for events
INSERT INTO prices (event_id, seat_type, ticket_type, price) VALUES
-- Carol Concert
(1, 'without_table', 'adult', 25.00), (1, 'without_table', 'child', 12.50), (1, 'without_table', 'senior', 20.00),
(1, 'with_table', 'adult', 45.00), (1, 'with_table', 'child', 22.50), (1, 'with_table', 'senior', 35.00),
-- Winter Wonderland
(2, 'without_table', 'adult', 30.00), (2, 'without_table', 'child', 15.00), (2, 'without_table', 'senior', 25.00),
(2, 'with_table', 'adult', 50.00), (2, 'with_table', 'child', 25.00), (2, 'with_table', 'senior', 40.00),
-- Steam Train
(3, 'without_table', 'adult', 35.00), (3, 'without_table', 'child', 18.00), (3, 'without_table', 'senior', 28.00),
(3, 'with_table', 'adult', 55.00), (3, 'with_table', 'child', 28.00), (3, 'with_table', 'senior', 45.00),
-- New Year Gala
(4, 'without_table', 'adult', 75.00), (4, 'without_table', 'child', 40.00), (4, 'without_table', 'senior', 60.00),
(4, 'with_table', 'adult', 120.00), (4, 'with_table', 'child', 60.00), (4, 'with_table', 'senior', 95.00),
-- Kids Party
(5, 'without_table', 'adult', 20.00), (5, 'without_table', 'child', 15.00), (5, 'without_table', 'senior', 18.00),
(5, 'with_table', 'adult', 35.00), (5, 'with_table', 'child', 25.00), (5, 'with_table', 'senior', 30.00),
-- Water Sports
(6, 'without_table', 'adult', 40.00), (6, 'without_table', 'child', 25.00), (6, 'without_table', 'senior', 32.00),
(6, 'with_table', 'adult', 60.00), (6, 'with_table', 'child', 35.00), (6, 'with_table', 'senior', 48.00);