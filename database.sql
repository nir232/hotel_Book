CREATE DATABASE IF NOT EXISTS hotel_booking;
USE hotel_booking;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(120) NOT NULL UNIQUE,
    phone VARCHAR(30) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('manager','customer') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_number VARCHAR(20) NOT NULL UNIQUE,
    room_type VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    capacity INT NOT NULL,
    description TEXT,
    status ENUM('available','maintenance') NOT NULL DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    room_id INT NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    guests INT NOT NULL,
    special_request TEXT,
    status ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_booking_customer FOREIGN KEY (customer_id)
        REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_booking_room FOREIGN KEY (room_id)
        REFERENCES rooms(id) ON DELETE CASCADE
);

INSERT IGNORE INTO rooms (room_number, room_type, price, capacity, description, status) VALUES
('101', 'Single Room', 45.00, 1, 'Comfortable room for one guest.', 'available'),
('102', 'Double Room', 70.00, 2, 'Spacious room for two guests.', 'available'),
('201', 'Deluxe Room', 110.00, 3, 'Deluxe room with extra sitting space.', 'available'),
('202', 'Family Suite', 160.00, 5, 'Large suite suitable for families.', 'available');
