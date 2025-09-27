-- ========================
-- Create Database
-- ========================
CREATE DATABASE IF NOT EXISTS traffic_app;
USE traffic_app;

-- ========================
-- Police login accounts
-- ========================
CREATE TABLE police (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- ========================
-- Vehicle registry
-- ========================
CREATE TABLE vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plate_no VARCHAR(20) UNIQUE NOT NULL,
    model VARCHAR(100),
    owner_name VARCHAR(100),
    owner_email VARCHAR(100),
    owner_phone VARCHAR(20)
);

-- ========================
-- Crimes / fines master
-- ========================
CREATE TABLE crimes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    crime_name VARCHAR(100),
    fine_amount DECIMAL(10,2)
);

-- ========================
-- Violations log
-- ========================
CREATE TABLE violations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plate_no VARCHAR(20) NOT NULL,
    owner_name VARCHAR(100) NOT NULL,
    owner_email VARCHAR(100) NOT NULL,
    owner_phone VARCHAR(20),
    vehicle_model VARCHAR(100),
    crime VARCHAR(100) NOT NULL,
    fine_amount INT NOT NULL DEFAULT 0,
    location VARCHAR(100),
    notes TEXT,
    photo_path VARCHAR(255),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    -- If you want to keep foreign keys, you can also include:
    -- vehicle_id INT,
    -- crime_id INT,
    -- police_id INT,
    -- FOREIGN KEY (vehicle_id) REFERENCES vehicles(id),
    -- FOREIGN KEY (crime_id) REFERENCES crimes(id),
    -- FOREIGN KEY (police_id) REFERENCES police(id)
);

-- ========================
-- Seed Data
-- ========================

-- Default police user (username: admin, password: admin123)
INSERT INTO police (username, password) VALUES 
('admin', MD5('admin123'));

-- Some example crimes
INSERT INTO crimes (crime_name, fine_amount) VALUES
('Signal Jumping', 500.00),
('Over Speeding', 1000.00),
('No Helmet', 300.00),
('Wrong Parking', 200.00);

-- Sample registered vehicles
INSERT INTO vehicles (plate_no, model, owner_name, owner_email, owner_phone) VALUES
('MH12AB1234', 'Honda Activa', 'Rahul Patil', 'rahul@example.com', '9876543210'),
('MH14CD5678', 'Hyundai i20', 'Sneha Desai', 'sneha@example.com', '9123456789'),
('MH01XY9999', 'Maruti Swift', 'Arjun Mehta', 'psr261004@gmail.com', '9988776655');
