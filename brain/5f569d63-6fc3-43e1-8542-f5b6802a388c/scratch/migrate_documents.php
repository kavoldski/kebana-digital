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

echo "Running Document Archiving Migrations...\n";

$queries = [
    "ALTER TABLE tbl_document MODIFY COLUMN event_id INT NULL",
    "ALTER TABLE tbl_document ADD COLUMN IF NOT EXISTS doc_tags VARCHAR(255) DEFAULT NULL AFTER file_path",
    "ALTER TABLE tbl_document ADD COLUMN IF NOT EXISTS uploaded_by INT DEFAULT NULL AFTER doc_tags",
    "ALTER TABLE tbl_document ADD CONSTRAINT fk_doc_user FOREIGN KEY (uploaded_by) REFERENCES tbl_user(user_id) ON DELETE SET NULL"
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

echo "Document Archiving Migrations complete.\n";
