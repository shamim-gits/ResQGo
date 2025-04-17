-- Create database
CREATE DATABASE IF NOT EXISTS resqgo;
USE resqgo;

-- Users table (for both customers and drivers)
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    user_type ENUM('customer', 'driver') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Driver details
CREATE TABLE driver_details (
    driver_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    license_number VARCHAR(50) NOT NULL,
    experience_years INT NOT NULL,
    status ENUM('available', 'busy', 'offline') DEFAULT 'offline',
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Vehicle details
CREATE TABLE vehicles (
    vehicle_id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT NOT NULL,
    vehicle_type ENUM('ambulance', 'paramedic') NOT NULL,
    registration_number VARCHAR(50) NOT NULL,
    model VARCHAR(100) NOT NULL,
    year INT NOT NULL,
    FOREIGN KEY (driver_id) REFERENCES driver_details(driver_id) ON DELETE CASCADE
);

-- Driver documents
CREATE TABLE driver_documents (
    document_id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT NOT NULL,
    document_type ENUM('license', 'insurance', 'certification', 'other') NOT NULL,
    document_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    verified BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (driver_id) REFERENCES driver_details(driver_id) ON DELETE CASCADE
);

-- Rides table
CREATE TABLE rides (
    ride_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    driver_id INT,
    pickup_location VARCHAR(255) NOT NULL,
    destination VARCHAR(255) NOT NULL,
    emergency_type ENUM('cardiac', 'accident', 'pregnancy', 'other_medical') NOT NULL,
    fare DECIMAL(10, 2),
    status ENUM('requested', 'accepted', 'en_route', 'picked_up', 'completed', 'cancelled') DEFAULT 'requested',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (customer_id) REFERENCES users(user_id),
    FOREIGN KEY (driver_id) REFERENCES driver_details(driver_id)
);