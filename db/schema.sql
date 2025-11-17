-- Coffee Shop POS database schema
CREATE DATABASE IF NOT EXISTS coffee_pos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE coffee_pos;

-- users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'barista', 'cashier') NOT NULL DEFAULT 'cashier',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);