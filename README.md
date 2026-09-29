# GrandStay Hotel Booking Management System

This application follows the same general two-dashboard CRUD style as the uploaded patient-management reference.

## Main features

### Customer
- Register and login
- Homepage link from both login and registration pages
- Dashboard
- Book/request a room
- View booking status
- Cancel pending booking
- Profile
- Settings / change password
- Logout

### Hotel Manager
- Dashboard
- View pending booking requests
- Accept or delete booking requests
- View confirmed reservations
- Delete reservations
- Add rooms
- Edit rooms
- Delete rooms
- Profile
- Settings / change password
- Logout

## Setup

1. Put the `HotelBookingManagement` folder inside `htdocs` if using XAMPP.
2. Start Apache and MySQL.
3. Open phpMyAdmin.
4. Import `database.sql`.
5. Open:
   `http://localhost/HotelBookingManagement/create_manager.php`
6. The default manager account is:
   Username: `manager1`
   Password: `manager123`
7. Delete `create_manager.php` after creating the manager.
8. Open:
   `http://localhost/HotelBookingManagement/home.php`

## Customer registration
Use the Register button on the homepage, or the Register link on the Login page.

## Important
The application uses PHP sessions, MySQLi prepared statements, password hashing, and role-based dashboard redirects.

The uploaded patient-management ZIP was used as the structural reference for this new application.
