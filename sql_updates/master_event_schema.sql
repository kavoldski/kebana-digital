-- =====================================================
-- KEBANA Digital Management System - Master Event Schema Updates
-- File: sql_updates/master_event_schema.sql
-- =====================================================

USE kebana_db;

-- =====================================================
-- 1. ALTER tbl_event TABLE
-- Add new columns for Master Event workflow
-- IMPORTANT: Check if columns exist before adding
-- =====================================================

-- Add guideline_file column only if it doesn't exist
-- Use this approach since MySQL doesn't support IF NOT EXISTS for ADD COLUMN
-- Run this as a separate statement (check manually or use a procedure)
-- The column will be added - if error occurs, it already exists

-- Add approval_status column only if it doesn't exist
-- Check first using:
-- SHOW COLUMNS FROM tbl_event LIKE 'approval_status';
-- If it doesn't exist, run:
ALTER TABLE tbl_event ADD COLUMN approval_status VARCHAR(50) DEFAULT 'Pending President';

-- Note: If you get "Duplicate column name" error, skip that column - it already exists
-- =====================================================
-- QUICK FIX: Run these one at a time
-- =====================================================

-- Option 1: Add guideline_file (if not exists)
-- ALTER TABLE tbl_event ADD COLUMN guideline_file VARCHAR(255) DEFAULT NULL;

-- Option 2: Add approval_status (if not exists)  
-- ALTER TABLE tbl_event ADD COLUMN approval_status VARCHAR(50) DEFAULT 'Pending President';

-- Option 3: Add parent_event_id (if not exists)
-- ALTER TABLE tbl_event ADD COLUMN parent_event_id INT DEFAULT NULL;

-- Option 4: Add cawangan_id (if not exists)
-- ALTER TABLE tbl_event ADD COLUMN cawangan_id INT DEFAULT NULL;

-- =====================================================
-- 2. ALTERNATIVE: Use UPDATE to set existing records
-- =====================================================

-- If columns already exist, just update existing records:
UPDATE tbl_event SET event_level = 'MASTER' WHERE event_level IS NULL OR event_level = '';
UPDATE tbl_event SET approval_status = 'Pending President' WHERE approval_status IS NULL OR approval_status = '';

-- =====================================================
-- 2. CREATE tbl_cawangan TABLE (if not exists)
-- Branch/Chapter table for Cawangan events
-- =====================================================
CREATE TABLE IF NOT EXISTS tbl_cawangan (
    cawangan_id INT AUTO_INCREMENT PRIMARY KEY,
    cawangan_name VARCHAR(150) NOT NULL,
    cawangan_code VARCHAR(20) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_cawangan_name (cawangan_name),
    INDEX idx_cawangan_code (cawangan_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. UPDATE EXISTING RECORDS (if needed)
-- Set default event_level for existing events
-- =====================================================
UPDATE tbl_event 
SET event_level = 'MASTER' 
WHERE event_level IS NULL OR event_level = '';

-- =====================================================
-- 4. CREATE uploads/guidelines DIRECTORY
-- Run this command in your terminal:
-- mkdir -p uploads/guidelines
-- chmod 755 uploads/guidelines
-- =====================================================

-- =====================================================
-- Schema Update Complete
-- =====================================================
-- 
-- New Columns Added to tbl_event:
-- 1. guideline_file VARCHAR(255) - Path to PDF guideline
-- 2. approval_status VARCHAR(50) - 'Pending President' default
-- 3. event_level VARCHAR(20) - 'MASTER' or 'SUB'
-- 4. parent_event_id INT - Reference to parent MASTER event
-- 5. cawangan_id INT - Branch assignment
--
-- Approval Status Values:
-- - Pending President
-- - Approved by President
-- - Rejected by President
-- =====================================================
