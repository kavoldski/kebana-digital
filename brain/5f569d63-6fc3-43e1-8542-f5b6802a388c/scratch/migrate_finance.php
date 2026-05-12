<?php
require_once __DIR__ . '/../../../config/database.php';
define('APP_ROOT', __DIR__ . '/../../..');

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

echo "Running Finance Migrations...\n";

$queries = [
    "ALTER TABLE tbl_transaction ADD COLUMN IF NOT EXISTS payment_mode VARCHAR(50) DEFAULT 'Cash' AFTER trans_date",
    "ALTER TABLE tbl_transaction ADD COLUMN IF NOT EXISTS event_id INT DEFAULT NULL AFTER payment_mode",
    "ALTER TABLE tbl_transaction ADD COLUMN IF NOT EXISTS month_label VARCHAR(10) DEFAULT NULL AFTER event_id",
    "ALTER TABLE tbl_transaction ADD CONSTRAINT fk_trans_event FOREIGN KEY (event_id) REFERENCES tbl_event(event_id) ON DELETE SET NULL"
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

echo "Finance Migrations complete.\n";
