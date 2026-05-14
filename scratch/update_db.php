<?php
require_once 'bootstrap.php';
use App\Core\Database;

$db = Database::getInstance()->getConnection();
$sql = "ALTER TABLE tbl_transaction ADD COLUMN receipt_path VARCHAR(255) DEFAULT NULL AFTER payment_mode";

if ($db->query($sql)) {
    echo "Database updated successfully: receipt_path column added to tbl_transaction.\n";
} else {
    echo "Error updating database: " . $db->error . "\n";
}
