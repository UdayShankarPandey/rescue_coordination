-- Rescue Coordination System Database Schema

-- Create database
CREATE DATABASE IF NOT EXISTS rescue_coordination;
USE rescue_coordination;

-- Users/Agencies table
CREATE TABLE IF NOT EXISTS agencies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    agency_type ENUM('medical', 'fire', 'police', 'military', 'ngo', 'other') NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    country VARCHAR(100) NOT NULL,
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL,
    last_active TIMESTAMP NULL,
    resources TEXT NULL,
    verified BOOLEAN DEFAULT FALSE,
    verification_code VARCHAR(64) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Disasters table
CREATE TABLE IF NOT EXISTS disasters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    disaster_type ENUM('earthquake', 'flood', 'fire', 'hurricane', 'tsunami', 'landslide', 'pandemic', 'other') NOT NULL,
    severity ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    radius_km DECIMAL(10, 2) NOT NULL,
    status ENUM('reported', 'active', 'contained', 'resolved') DEFAULT 'reported',
    reported_by INT,
    started_at TIMESTAMP NULL,
    ended_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (reported_by) REFERENCES agencies(id) ON DELETE SET NULL
);

-- Agency locations (historical)
CREATE TABLE IF NOT EXISTS agency_locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    agency_id INT NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    status ENUM('available', 'en_route', 'on_site', 'unavailable') DEFAULT 'available',
    disaster_id INT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (agency_id) REFERENCES agencies(id) ON DELETE CASCADE,
    FOREIGN KEY (disaster_id) REFERENCES disasters(id) ON DELETE SET NULL
);

-- Resources table
CREATE TABLE IF NOT EXISTS resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    agency_id INT NOT NULL,
    resource_type VARCHAR(100) NOT NULL,
    name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    unit VARCHAR(50) NULL,
    available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (agency_id) REFERENCES agencies(id) ON DELETE CASCADE
);

-- Resource requests
CREATE TABLE IF NOT EXISTS resource_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    disaster_id INT NOT NULL,
    requesting_agency_id INT NOT NULL,
    resource_type VARCHAR(100) NOT NULL,
    quantity INT NOT NULL,
    priority ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    status ENUM('pending', 'approved', 'fulfilled', 'rejected', 'cancelled') DEFAULT 'pending',
    fulfilling_agency_id INT NULL,
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fulfilled_at TIMESTAMP NULL,
    FOREIGN KEY (disaster_id) REFERENCES disasters(id) ON DELETE CASCADE,
    FOREIGN KEY (requesting_agency_id) REFERENCES agencies(id) ON DELETE CASCADE,
    FOREIGN KEY (fulfilling_agency_id) REFERENCES agencies(id) ON DELETE SET NULL
);

-- Communications
CREATE TABLE IF NOT EXISTS communications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    disaster_id INT NULL,
    message TEXT NOT NULL,
    is_broadcast BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES agencies(id) ON DELETE CASCADE,
    FOREIGN KEY (disaster_id) REFERENCES disasters(id) ON DELETE SET NULL
);

-- Communication recipients
CREATE TABLE IF NOT EXISTS communication_recipients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    communication_id INT NOT NULL,
    recipient_id INT NOT NULL,
    read_at TIMESTAMP NULL,
    FOREIGN KEY (communication_id) REFERENCES communications(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES agencies(id) ON DELETE CASCADE
);

-- AI Predictions
CREATE TABLE IF NOT EXISTS ai_predictions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    disaster_id INT NOT NULL,
    prediction_type ENUM('spread', 'severity', 'duration', 'resource_need') NOT NULL,
    prediction_data JSON NOT NULL,
    confidence DECIMAL(5, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (disaster_id) REFERENCES disasters(id) ON DELETE CASCADE
);

-- Alerts
CREATE TABLE IF NOT EXISTS alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    disaster_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    severity ENUM('info', 'warning', 'danger', 'critical') NOT NULL,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (disaster_id) REFERENCES disasters(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES agencies(id) ON DELETE SET NULL
);

-- Alert recipients
CREATE TABLE IF NOT EXISTS alert_recipients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alert_id INT NOT NULL,
    agency_id INT NOT NULL,
    read_at TIMESTAMP NULL,
    FOREIGN KEY (alert_id) REFERENCES alerts(id) ON DELETE CASCADE,
    FOREIGN KEY (agency_id) REFERENCES agencies(id) ON DELETE CASCADE
);

-- Create an admin user (password: admin123)
INSERT INTO agencies (name, email, password, phone, agency_type, address, city, state, country, verified)
VALUES ('System Administrator', 'admin@rescuecoord.org', '$2y$10$8zf0bvFUxHqZ5VqgQ3z3ZOZ9WvDumOQTRnJ7Yw/Dk7SLU19YbGq.e', '+1234567890', 'other', 'System Address', 'System City', 'System State', 'System Country', TRUE);
