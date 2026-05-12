<?php
require_once __DIR__ . '/../../../config/database.php';
define('APP_ROOT', __DIR__ . '/../../..');

// Simple Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = APP_ROOT . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

use App\Core\Database;

$db = Database::getInstance()->getConnection();

echo "Running migrations...\n";

$queries = [
    "ALTER TABLE tbl_event ADD COLUMN IF NOT EXISTS approval_status VARCHAR(50) DEFAULT 'Pending President' AFTER status",
    "ALTER TABLE tbl_event ADD COLUMN IF NOT EXISTS cawangan_id INT AFTER approval_status",
    "ALTER TABLE tbl_event ADD COLUMN IF NOT EXISTS event_level ENUM('MASTER', 'SUB') DEFAULT 'MASTER' AFTER cawangan_id",
    "ALTER TABLE tbl_event ADD COLUMN IF NOT EXISTS parent_event_id INT AFTER event_level",
    "ALTER TABLE tbl_event ADD CONSTRAINT fk_event_cawangan FOREIGN KEY (cawangan_id) REFERENCES tbl_cawangan(cawangan_id) ON DELETE SET NULL",
    "ALTER TABLE tbl_event ADD CONSTRAINT fk_event_parent FOREIGN KEY (parent_event_id) REFERENCES tbl_event(event_id) ON DELETE SET NULL"
];

foreach ($queries as $query) {
    try {
        if ($db->query($query)) {
            echo "Success: $query\n";
        } else {
            echo "Failed: $query - " . $db->error . "\n";
        }
    } catch (Exception $e) {
        echo "Skipped/Error: " . $e->getMessage() . "\n";
    }
}

echo "Migrations complete.\n";
