-- =====================================================
-- Quick SQL - Add Missing Columns to tbl_event
-- Run each statement separately in phpMyAdmin
-- =====================================================

USE kebana_db;

-- Step 1: Check existing columns first
SHOW COLUMNS FROM tbl_event;

-- Step 2: Add guideline_file (if missing)
ALTER TABLE tbl_event ADD COLUMN guideline_file VARCHAR(255) DEFAULT NULL;

-- Step 3: Add approval_status (if missing)  
ALTER TABLE tbl_event ADD COLUMN approval_status VARCHAR(50) DEFAULT 'Pending President';

-- Step 4: Add parent_event_id (if missing)
ALTER TABLE tbl_event ADD COLUMN parent_event_id INT DEFAULT NULL;

-- Step 5: Add cawangan_id (if missing)
ALTER TABLE tbl_event ADD COLUMN cawangan_id INT DEFAULT NULL;

-- Step 6: Add indexes (if not exists)
ALTER TABLE tbl_event ADD INDEX idx_parent_event_id (parent_event_id);
ALTER TABLE tbl_event ADD INDEX idx_cawangan_id (cawangan_id);
ALTER TABLE tbl_event ADD INDEX idx_approval_status (approval_status);

-- Step 7: Create tbl_cawangan table (if not exists)
CREATE TABLE IF NOT EXISTS tbl_cawangan (
    cawangan_id INT AUTO_INCREMENT PRIMARY KEY,
    cawangan_name VARCHAR(150) NOT NULL,
    cawangan_code VARCHAR(20) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Step 8: Update existing records
UPDATE tbl_event SET event_level = 'MASTER' WHERE event_level IS NULL;
UPDATE tbl_event SET approval_status = 'Pending President' WHERE approval_status IS NULL;

-- Done!
