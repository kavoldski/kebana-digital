<?php
require 'includes/dbconnect.php'; // Has $conn

$sql = "ALTER TABLE tbl_announcement ADD COLUMN expires_at DATETIME DEFAULT NULL AFTER content";

if ($conn->query($sql) === TRUE) {
    echo "Table tbl_announcement updated successfully with expires_at column\n";
} else {
    echo "Error updating table: " . $conn->error . "\n";
}
