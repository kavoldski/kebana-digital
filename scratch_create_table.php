<?php
require 'includes/dbconnect.php'; // Has $conn

$sql = "CREATE TABLE IF NOT EXISTS tbl_announcement (
    announcement_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    status ENUM('Active', 'Draft', 'Inactive') DEFAULT 'Active',
    created_by INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES tbl_user(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql) === TRUE) {
    echo "Table tbl_announcement created successfully\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}
