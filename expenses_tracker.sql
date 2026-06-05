-- ============================================
-- Personal Expense Tracker - Database Schema
-- ============================================
-- Database: expenses_tracker
-- Run this file in phpMyAdmin or MySQL CLI
-- ============================================

CREATE DATABASE IF NOT EXISTS expenses_tracker
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE expenses_tracker;

-- -----------------------------------------------
-- Table: users
-- Stores registered user accounts
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50)  NOT NULL UNIQUE,
    email       VARCHAR(100) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,          -- hashed with password_hash()
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- -----------------------------------------------
-- Table: expenses
-- Stores every income / expense entry
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS expenses (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT          NOT NULL,
    type        ENUM('income','expense') NOT NULL DEFAULT 'expense',
    amount      DECIMAL(10,2) NOT NULL,
    category    VARCHAR(50)  NOT NULL,
    description VARCHAR(255) DEFAULT '',
    entry_date  DATE         NOT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,

    -- Link every record to its owner
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- -----------------------------------------------
-- Sample demo user  (password = "demo1234")
-- Remove this before going to production!
-- -----------------------------------------------
INSERT INTO users (username, email, password) VALUES
('demo', 'demo@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- -----------------------------------------------
-- Sample data for the demo user (user_id = 1)
-- -----------------------------------------------
INSERT INTO expenses (user_id, type, amount, category, description, entry_date) VALUES
(1, 'income',  50000.00, 'Salary',        'Monthly salary',         CURDATE() - INTERVAL 20 DAY),
(1, 'expense',  3500.00, 'Rent',          'House rent',             CURDATE() - INTERVAL 18 DAY),
(1, 'expense',  1200.00, 'Food',          'Groceries',              CURDATE() - INTERVAL 15 DAY),
(1, 'expense',   450.00, 'Transport',     'Matatu fare this week',  CURDATE() - INTERVAL 10 DAY),
(1, 'expense',   800.00, 'Shopping',      'New shoes',              CURDATE() - INTERVAL 7  DAY),
(1, 'income',   5000.00, 'Freelance',     'Web design project',     CURDATE() - INTERVAL 5  DAY),
(1, 'expense',   300.00, 'Entertainment', 'Cinema tickets',         CURDATE() - INTERVAL 3  DAY),
(1, 'expense',   650.00, 'Utilities',     'Electricity bill',       CURDATE() - INTERVAL 2  DAY);
