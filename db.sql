CREATE DATABASE village_monitoring;

USE village_monitoring;

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    contact_number VARCHAR(15) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Population Statistics
CREATE TABLE population (
    id INT AUTO_INCREMENT PRIMARY KEY,
    total INT NOT NULL,
    adults INT NOT NULL,
    seniors INT NOT NULL,
    youth INT NOT NULL,
    children INT NOT NULL,
    recorded_date DATE NOT NULL
);

-- Projects
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    status ENUM('active', 'completed', 'pending') DEFAULT 'pending',
    start_date DATE,
    end_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Emergency Alerts
CREATE TABLE emergency_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    status ENUM('active', 'resolved') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bus Schedules
CREATE TABLE bus_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    route VARCHAR(100) NOT NULL,
    next_arrival TIME NOT NULL,
    status ENUM('on_time', 'delayed', 'cancelled') DEFAULT 'on_time',
    capacity_percentage INT NOT NULL,
    schedule_date DATE NOT NULL
);

-- Activities
CREATE TABLE activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    activity_type ENUM('project', 'meeting', 'alert') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE weather_forecast (
    id INT AUTO_INCREMENT PRIMARY KEY,
    day VARCHAR(10) NOT NULL,
    temperature INT NOT NULL,
    weather_condition VARCHAR(50) NOT NULL,
    forecast_date DATE NOT NULL
);

-- Contact Messages
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE resource_utilization (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resource_name VARCHAR(50) NOT NULL,
    usage_percentage INT NOT NULL,
    recorded_date DATE NOT NULL
);
CREATE TABLE complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone_number VARCHAR(15) NOT NULL,
    complaint TEXT NOT NULL,
    file_path VARCHAR(255), -- Path to uploaded file
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

