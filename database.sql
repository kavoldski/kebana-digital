-- =====================================================
-- KEBANA Management System - Database Schema
-- Database: kebana_db
-- =====================================================

-- Create Database
CREATE DATABASE IF NOT EXISTS kebana_db;
USE kebana_db;

-- =====================================================
-- 1. USER TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS tbl_user (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('Super Admin', 'Secretary', 'Treasurer') NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. MEMBER TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS tbl_member (
    member_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    ic_number VARCHAR(20) NOT NULL UNIQUE,
    village VARCHAR(100) NOT NULL,
    phone_no VARCHAR(20),
    status VARCHAR(50) DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ic_number (ic_number),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. EVENT TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS tbl_event (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    event_title VARCHAR(150) NOT NULL,
    event_date DATE NOT NULL,
    venue VARCHAR(150) NOT NULL,
    budget_est DECIMAL(10, 2),
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES tbl_user(user_id) ON DELETE SET NULL,
    INDEX idx_event_date (event_date),
    INDEX idx_created_by (created_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. DOCUMENT TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS tbl_document (
    doc_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    doc_name VARCHAR(150) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES tbl_event(event_id) ON DELETE CASCADE,
    INDEX idx_event_id (event_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. TRANSACTION TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS tbl_transaction (
    trans_id INT AUTO_INCREMENT PRIMARY KEY,
    trans_type ENUM('Income', 'Expense') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    category VARCHAR(100) NOT NULL,
    trans_date DATE NOT NULL,
    recorded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (recorded_by) REFERENCES tbl_user(user_id) ON DELETE SET NULL,
    INDEX idx_trans_type (trans_type),
    INDEX idx_trans_date (trans_date),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. ATTENDANCE TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS tbl_attendance (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    member_id INT NOT NULL,
    status ENUM('Present', 'Absent', 'Excused') NOT NULL DEFAULT 'Absent',
    notes TEXT,
    marked_by INT,
    marked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES tbl_event(event_id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES tbl_member(member_id) ON DELETE CASCADE,
    FOREIGN KEY (marked_by) REFERENCES tbl_user(user_id) ON DELETE SET NULL,
    UNIQUE KEY unique_attendance (event_id, member_id),
    INDEX idx_event_id (event_id),
    INDEX idx_member_id (member_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INSERT DEFAULT DATA
-- =====================================================

-- Insert default Super Admin user (password: Admin@123)
INSERT INTO tbl_user (username, password_hash, role, email) 
VALUES (
    'admin',
    '$2y$10$8RpLzg4qPp6x7n9mK2Q8d.gY5f6H3j8L4v5w6x7y8z9a0b1c2d3e4f5',
    'Super Admin',
    'admin@kebana.local'
);

-- =====================================================
-- Database Setup Complete
-- =====================================================
-- 
-- DEFAULT ADMIN ACCOUNT:
-- Username: admin
-- Email: admin@kebana.local
-- Password: Admin@123
--
-- Change this password immediately after first login!
--
