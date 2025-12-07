<!-- Database SQL: MySQL-compatible schema and sample seed data -->
<!-- Put these statements into your backend migration or run directly in MySQL/MariaDB. -->

<!--
File: db_schema.sql
Description: Schema for Agro Business Management & Invoice System (MySQL)
-->

<!-- SQL START -->

-- Use or create database
CREATE DATABASE IF NOT EXISTS agro_management DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE agro_management;

-- Roles / Users
CREATE TABLE IF NOT EXISTS roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  role_id INT NOT NULL,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  phone VARCHAR(30),
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Categories and Products
CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL UNIQUE,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT NULL,
  sku VARCHAR(80) UNIQUE,
  name VARCHAR(250) NOT NULL,
  unit ENUM('ml','l','g','kg','pcs','box','litre') DEFAULT 'pcs',
  unit_size DECIMAL(10,3) DEFAULT 1.000, -- e.g. 250 (ml), 2 (kg) etc.
  purchase_price DECIMAL(12,2) DEFAULT 0.00,
  selling_price DECIMAL(12,2) DEFAULT 0.00,
  stock_qty DECIMAL(12,3) DEFAULT 0.000,
  batch_number VARCHAR(80),
  expiry_date DATE NULL,
  image_path VARCHAR(500) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Stock logs track purchases, adjustments, sales impacting stock
CREATE TABLE IF NOT EXISTS stock_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  change_qty DECIMAL(12,3) NOT NULL,
  reason ENUM('purchase','sale','adjustment','return','expiry') NOT NULL,
  reference VARCHAR(150), -- e.g. invoice number or purchase order id
  created_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Customers
CREATE TABLE IF NOT EXISTS customers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  phone VARCHAR(50),
  email VARCHAR(150),
  address TEXT,
  customer_type ENUM('retail','wholesale') DEFAULT 'retail',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Invoices and items
CREATE TABLE IF NOT EXISTS invoices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  invoice_no VARCHAR(80) NOT NULL UNIQUE,
  customer_id INT NULL,
  user_id INT NOT NULL, -- seller / creator
  date DATE NOT NULL DEFAULT (CURRENT_DATE),
  subtotal DECIMAL(12,2) DEFAULT 0.00,
  discount DECIMAL(12,2) DEFAULT 0.00,
  tax DECIMAL(12,2) DEFAULT 0.00,
  total DECIMAL(12,2) DEFAULT 0.00,
  paid_amount DECIMAL(12,2) DEFAULT 0.00,
  due_amount DECIMAL(12,2) AS (total - paid_amount) STORED,
  status ENUM('paid','partial','due') GENERATED ALWAYS AS (CASE WHEN paid_amount >= total THEN 'paid' WHEN paid_amount > 0 AND paid_amount < total THEN 'partial' ELSE 'due' END) VIRTUAL,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS invoice_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  invoice_id INT NOT NULL,
  product_id INT NOT NULL,
  description VARCHAR(500),
  qty DECIMAL(12,3) NOT NULL DEFAULT 1.000,
  unit_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  discount DECIMAL(12,2) DEFAULT 0.00,
  line_total DECIMAL(12,2) AS ((qty * unit_price) - discount) STORED,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Payments / Collections (optional simple table)
CREATE TABLE IF NOT EXISTS payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  invoice_id INT NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  method ENUM('cash','bank','mobile','cheque') DEFAULT 'cash',
  reference VARCHAR(150),
  paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  created_by INT,
  FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Expenses (optional)
CREATE TABLE IF NOT EXISTS expenses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  category VARCHAR(120),
  amount DECIMAL(12,2) NOT NULL,
  note TEXT,
  expense_date DATE NOT NULL DEFAULT (CURRENT_DATE),
  created_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Settings (key-value)
CREATE TABLE IF NOT EXISTS settings (
  `key` VARCHAR(190) PRIMARY KEY,
  `value` TEXT,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Indexes for common queries
CREATE INDEX idx_products_name ON products(name);
CREATE INDEX idx_invoices_date ON invoices(date);
CREATE INDEX idx_customers_name ON customers(name);

-- Sample seed data (minimal demo)
INSERT INTO roles (name) VALUES ('Admin'),('Staff')
  ON DUPLICATE KEY UPDATE name=VALUES(name);

INSERT INTO users (role_id, name, email, password_hash, phone) VALUES
((SELECT id FROM roles WHERE name='Admin'),'Owner','owner@example.com','$2y$10$examplehashreplace', '0123456789')
ON DUPLICATE KEY UPDATE name=VALUES(name);

INSERT INTO categories (name, description) VALUES
('Pesticide','Insecticides and pesticides'),
('Fertilizer','All kinds of fertilizers'),
('Seeds','Various seed packets')
ON DUPLICATE KEY UPDATE name=VALUES(name);

INSERT INTO products (category_id, sku, name, unit, unit_size, purchase_price, selling_price, stock_qty, batch_number, expiry_date) VALUES
((SELECT id FROM categories WHERE name='Pesticide'),'PST-001','Alpha Insecticide 250ml','ml',250,120.00,150.00,50,'BATCH-A1','2026-12-31'),
((SELECT id FROM categories WHERE name='Fertilizer'),'FRT-001','GrowFast 2kg','kg',2,400.00,500.00,30,'BATCH-F1','2027-06-30'),
((SELECT id FROM categories WHERE name='Seeds'),'SD-001','Hybrid Maize Pack','pcs',1,80.00,120.00,200,'BATCH-S1',NULL)
ON DUPLICATE KEY UPDATE name=VALUES(name);

INSERT INTO customers (name, phone, email, address, customer_type) VALUES
('M. Rahman','01710000000','rahman@example.com','Village Road, District','retail'),
('Green Farm Ltd.','01820000000','contact@greenfarm.com','Industrial Area, City','wholesale')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Create a demo invoice with two items
INSERT INTO invoices (invoice_no, customer_id, user_id, date, subtotal, discount, tax, total, paid_amount, notes) VALUES
('INV-2025-0001', (SELECT id FROM customers WHERE name='M. Rahman'), (SELECT id FROM users WHERE email='owner@example.com'), CURDATE(), 270.00, 0.00, 0.00, 270.00, 270.00, 'Demo invoice')
ON DUPLICATE KEY UPDATE notes=VALUES(notes);

INSERT INTO invoice_items (invoice_id, product_id, description, qty, unit_price, discount) VALUES
((SELECT id FROM invoices WHERE invoice_no='INV-2025-0001'), (SELECT id FROM products WHERE sku='PST-001'), 'Alpha Insecticide 250ml', 1, 150.00, 0.00),
((SELECT id FROM invoices WHERE invoice_no='INV-2025-0001'), (SELECT id FROM products WHERE sku='SD-001'), 'Hybrid Maize Pack', 1, 120.00, 0.00)
ON DUPLICATE KEY UPDATE description=VALUES(description);

-- Adjust stock logs for demo sale (decrement stock)
INSERT INTO stock_logs (product_id, change_qty, reason, reference, created_by) VALUES
((SELECT id FROM products WHERE sku='PST-001'), -1, 'sale', 'INV-2025-0001', (SELECT id FROM users WHERE email='owner@example.com')),
((SELECT id FROM products WHERE sku='SD-001'), -1, 'sale', 'INV-2025-0001', (SELECT id FROM users WHERE email='owner@example.com'))
ON DUPLICATE KEY UPDATE reference=VALUES(reference);

-- Simple settings
INSERT INTO settings (`key`,`value`) VALUES
('company_name','Agro Business Ltd.'),
('company_address','Dhaka, Bangladesh'),
('invoice_prefix','INV-2025-')
ON DUPLICATE KEY UPDATE `value`=VALUES(`value`);

-- SQL END -->
