-- Database SQL: Simple MySQL-compatible schema for Agro Management
-- Clean and minimal schema for basic invoicing system

-- Create database
CREATE DATABASE IF NOT EXISTS agro_management DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE agro_management;

-- Roles
CREATE TABLE IF NOT EXISTS roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- Users
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  role_id INT NOT NULL,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  phone VARCHAR(30),
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB;

-- Categories
CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL UNIQUE,
  description TEXT
) ENGINE=InnoDB;

-- Products
CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT NULL,
  name VARCHAR(250) NOT NULL,
  selling_price DECIMAL(12,2) DEFAULT 0.00,
  stock_qty DECIMAL(12,3) DEFAULT 0.000,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Customers
CREATE TABLE IF NOT EXISTS customers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  phone VARCHAR(50),
  email VARCHAR(150),
  address TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Invoices
CREATE TABLE IF NOT EXISTS invoices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  invoice_no VARCHAR(80) NOT NULL UNIQUE,
  customer_id INT NULL,
  user_id INT NOT NULL,
  date DATE NOT NULL DEFAULT (CURRENT_DATE),
  total DECIMAL(12,2) DEFAULT 0.00,
  paid_amount DECIMAL(12,2) DEFAULT 0.00,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
  FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- Invoice Items
CREATE TABLE IF NOT EXISTS invoice_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  invoice_id INT NOT NULL,
  product_id INT NOT NULL,
  qty DECIMAL(12,3) NOT NULL DEFAULT 1.000,
  unit_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Indexes
ALTER TABLE products ADD INDEX IF NOT EXISTS idx_products_name (name);
ALTER TABLE invoices ADD INDEX IF NOT EXISTS idx_invoices_date (date);

-- Sample data
INSERT INTO roles (name) VALUES ('Admin'),('Staff')
  ON DUPLICATE KEY UPDATE name=VALUES(name);

INSERT INTO users (role_id, name, email, password_hash, phone) VALUES
(1, 'Admin User', 'admin@example.com', '$2y$10$examplehashreplace', '0123456789')
ON DUPLICATE KEY UPDATE name=VALUES(name);

INSERT INTO categories (name, description) VALUES
('Pesticides', 'Insecticides and pesticides'),
('Fertilizers', 'All kinds of fertilizers'),
('Seeds', 'Various seed packets')
ON DUPLICATE KEY UPDATE name=VALUES(name);

INSERT INTO products (category_id, name, selling_price, stock_qty) VALUES
(1, 'Alpha Insecticide 250ml', 150.00, 50),
(2, 'GrowFast 2kg', 500.00, 30),
(3, 'Hybrid Maize Pack', 120.00, 200)
ON DUPLICATE KEY UPDATE name=VALUES(name);

INSERT INTO customers (name, phone, email, address) VALUES
('John Doe', '01710000000', 'john@example.com', 'Village Road'),
('Green Farm Ltd.', '01820000000', 'contact@greenfarm.com', 'Industrial Area')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Sample invoice
INSERT INTO invoices (invoice_no, customer_id, user_id, date, total, paid_amount, notes) VALUES
('INV-2025-0001', 1, 1, CURDATE(), 270.00, 270.00, 'Sample invoice')
ON DUPLICATE KEY UPDATE notes=VALUES(notes);

INSERT INTO invoice_items (invoice_id, product_id, qty, unit_price) VALUES
(1, 1, 1, 150.00),
(1, 3, 1, 120.00)
ON DUPLICATE KEY UPDATE qty=VALUES(qty);

-- SQL END
