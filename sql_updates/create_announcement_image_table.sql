-- =====================================================
-- KEBANA Digital Management System - Announcement Images Schema
-- File: sql_updates/create_announcement_image_table.sql
-- =====================================================

CREATE TABLE IF NOT EXISTS tbl_announcement_image (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    announcement_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (announcement_id) REFERENCES tbl_announcement(announcement_id) ON DELETE CASCADE,
    INDEX idx_announcement_id (announcement_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
