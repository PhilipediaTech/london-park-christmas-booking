# london-park-christmas-booking

Christmas Event Booking System for London Community Park

# London Community Park - Christmas Event Booking System 🎄

A web-based content management system (CMS) for booking Christmas event tickets at London Community Park.

## 📋 Project Overview

This system enables customers to:

- Register and create accounts
- Browse Christmas events
- Book tickets online
- View their booking history

Administrators can:

- View all registered users
- Perform CRUD operations on users (Create, Read, Update, Delete)
- View all bookings
- Manage events

## 🚀 Features

### Customer Features

- User registration and login
- Profile management
- Browse events with pricing
- Book tickets (up to 8 per booking)
- Select seat types (with/without table)
- Multiple ticket types (Adult, Child, Senior)
- View booking history
- Photo upload for events requiring adult supervision

### Admin Features

- Admin dashboard with statistics
- User management (CRUD)
- View all bookings with filters
- Events overview with revenue tracking
- Search and filter functionality

### Security Features

- Password hashing (bcrypt)
- CSRF protection
- Input sanitization (XSS prevention)
- Prepared statements (SQL injection prevention)
- Session management
- Role-based access control

## 🛠️ Technologies Used

- **Frontend**: HTML5, CSS3, JavaScript
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Server**: Apache (XAMPP)

## 📁 Folder Structure

```
london-park-christmas-booking/
├── config/
│   └── database.php          # Database connection
├── includes/
│   ├── header.php            # Common header
│   ├── footer.php            # Common footer
│   ├── functions.php         # Helper functions
│   └── auth.php              # Authentication functions
├── assets/
│   ├── css/
│   │   └── style.css         # Main stylesheet (Christmas theme)
│   ├── js/
│   │   └── main.js           # JavaScript functions
│   └── images/               # Image assets
├── uploads/
│   └── photos/               # User uploaded photos
├── admin/
│   ├── index.php             # Admin dashboard
│   ├── users.php             # User management
│   ├── add_user.php          # Add new user
│   ├── edit_user.php         # Edit user
│   ├── delete_user.php       # Delete user
│   ├── bookings.php          # View all bookings
│   └── events.php            # Events management
├── user/
│   ├── dashboard.php         # User dashboard
│   ├── profile.php           # User profile
│   ├── bookings.php          # User's bookings
│   └── book_event.php        # Book an event
├── database/
│   └── schema.sql            # Database schema
├── index.php                 # Homepage
├── register.php              # User registration
├── login.php                 # User login
├── logout.php                # Logout handler
├── events.php                # Browse events
└── README.md                 # This file
```

## ⚙️ Installation

### Prerequisites

1. XAMPP (or similar PHP/MySQL environment)
2. Web browser

### Setup Steps

1. **Clone the repository**

   ```bash
   cd C:\xampp\htdocs
   git clone https://github.com/YOUR_USERNAME/london-park-christmas-booking.git
   ```

2. **Start XAMPP**

   - Open XAMPP Control Panel
   - Start Apache and MySQL

3. **Create Database**

   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named `london_park_db`
   - Import `database/schema.sql`

4. **Configure Database Connection**

   - Open `config/database.php`
   - Update credentials if needed (default: root with no password)

5. **Access the Application**
   - Open browser and go to: `http://localhost/london-park-christmas-booking`

## 🔐 Default Login Credentials

### Admin Account

- **Username**: admin
- **Password**: admin123

### Customer Account

- Register a new account through the registration page

## 📊 Database Schema

### Tables

1. **users** - Customer and admin accounts
2. **events** - Christmas event information
3. **seats** - Seat types and availability
4. **prices** - Ticket pricing
5. **bookings** - Booking records
6. **booking_details** - Individual ticket details

## 🎨 Design Features

- Christmas-themed UI with red and green color scheme
- Snowfall animation effect
- Responsive design for all devices
- Interactive elements and hover effects
- Clear navigation and user feedback

## 📝 Business Rules

1. Maximum 8 tickets per booking
2. Some events require at least 1 adult ticket
3. Adult photo required for events with child supervision
4. Two seat types: with table and without table
5. Three ticket types: Adult (18+), Child (3-17), Senior (65+)

## 🧪 Testing

The system has been tested for:

- User registration and login
- CRUD operations on users
- Event booking process
- Role-based access control
- Form validation
- Security features

## 👤 Author

[Your Name]
[Your Student ID]
[University of Greenwich]

## 📄 License

This project is created for educational purposes as part of university coursework.

---

🎄 **Merry Christmas and Happy New Year!** 🎄
