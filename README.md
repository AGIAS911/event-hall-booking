    # Event Hall Booking System

A comprehensive web application for managing event hall bookings, built with PHP following a modular MVC-like architecture that ensures complete separation of concerns between data logic, request handlers, and UI views, with high standards of security and performance.

---

## Key Features

* **Identity & Role Management:** Secure authentication and registration system using **Bcrypt** password hashing, with strict role separation between customers (`customer`) and system administrators (`admin`).
* **Security & Route Protection:** A middleware (`auth_guard.php`) protecting sensitive pages and the admin dashboard from unauthorized access.
* **Concurrency Control:** A precise time-overlap algorithm inside the booking model preventing double-booking of the same hall during the same timeframe.
* **Comprehensive Admin Dashboard:**
  * Statistical metrics for total halls, total bookings, pending requests, and confirmed revenue.
  * Full hall management (add, edit, image upload, and maintenance status toggle).
  * Review, approval, rejection, or status update for customer booking requests.
* **Responsive User Experience:** Modern interfaces styled with **Bootstrap 5**, interactive flash messages, and JavaScript confirmation dialogs.

---

## Project Structure

```text
event-hall-booking/
├── config/
│   └── Database.php          # Database connection (Singleton Pattern + PDO)
├── classes/
│   ├── User.php              # User model & permissions
│   ├── Hall.php              # Hall management model
│   └── Booking.php           # Booking management & overlap prevention model
├── handlers/
│   ├── auth_handler.php      # Login & registration handler
│   ├── hall_handler.php      # Hall CRUD & image upload handler
│   └── booking_handler.php   # Booking creation & status update handler
├── includes/
│   ├── header.php            # Top template, navbar, and flash messages
│   ├── footer.php            # Footer template & script imports
│   └── auth_guard.php        # Security guard & route authorization
├── admin/
│   ├── dashboard.php         # Admin general statistics dashboard
│   ├── manage_halls.php      # Hall management page (Add/Edit/Delete)
│   └── manage_bookings.php   # Booking requests filtering and management
├── assets/
│   ├── css/style.css         # Custom styles
│   └── uploads/halls/        # Uploaded hall images directory
├── index.php                 # Home page & available halls catalog
├── hall_details.php          # Hall details & booking request form
├── my_bookings.php           # Customer booking history & status tracking
├── login.php                 # Login page
└── register.php              # Registration page