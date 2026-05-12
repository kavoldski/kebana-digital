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

echo "Running Event Schema Update...\n";

$queries = [
    "ALTER TABLE tbl_event ADD COLUMN IF NOT EXISTS event_end_date DATE DEFAULT NULL AFTER event_date"
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

echo "Event Schema Update complete.\n";
