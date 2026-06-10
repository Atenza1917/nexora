-- =============================================
-- NEXORA E-COMMERCE DATABASE SETUP
-- Run this in phpMyAdmin or MySQL CLI
-- =============================================

CREATE DATABASE IF NOT EXISTS nexora_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nexora_db;

-- USERS TABLE
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- PRODUCTS TABLE
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0,
    category VARCHAR(100),
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ORDERS TABLE
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
    shipping_address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- ORDER ITEMS TABLE
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- =============================================
-- SAMPLE DATA
-- =============================================

-- Default admin user (password: admin123)
INSERT INTO users (name, email, password, role) VALUES
('Admin User', 'admin@nexora.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer');

-- Sample products
INSERT INTO products (name, description, price, stock, category) VALUES
('Wireless Pro Headphones', 'Premium noise-cancelling wireless headphones with 40hr battery life.', 129.99, 50, 'Electronics'),
('Smart Watch Series X', 'Feature-packed smartwatch with health monitoring and GPS.', 249.99, 30, 'Electronics'),
('Bluetooth Speaker', 'Waterproof portable speaker with rich 360° sound.', 79.99, 75, 'Electronics'),
('Slim Fit Blazer', 'Modern slim-fit blazer in premium Italian wool blend.', 149.99, 40, 'Clothing'),
('Classic Denim Jacket', 'Timeless denim jacket with vintage wash finish.', 89.99, 60, 'Clothing'),
('Linen Summer Shirt', 'Breathable linen shirt perfect for warm weather.', 54.99, 80, 'Clothing'),
('The Art of Clean Code', 'A comprehensive guide to writing maintainable software.', 34.99, 100, 'Books'),
('Digital Marketing 2026', 'Master modern digital marketing strategies and tools.', 29.99, 90, 'Books'),
('LED Desk Lamp', 'Adjustable LED lamp with wireless charging base.', 59.99, 55, 'Home'),
('Minimalist Wall Clock', 'Elegant silent wall clock with Scandinavian design.', 39.99, 45, 'Home');
