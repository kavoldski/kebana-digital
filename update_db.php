<?php
require_once __DIR__ . '/app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();

$queries = [
    "ALTER TABLE tbl_document ADD COLUMN IF NOT EXISTS doc_tags VARCHAR(255) NULL AFTER file_path",
    "ALTER TABLE tbl_document ADD COLUMN IF NOT EXISTS uploaded_by INT(11) NULL AFTER doc_tags",
    "ALTER TABLE tbl_document ADD COLUMN IF NOT EXISTS doc_size INT(11) NULL AFTER uploaded_by",
    "ALTER TABLE tbl_document ADD CONSTRAINT fk_uploaded_by FOREIGN KEY (uploaded_by) REFERENCES tbl_user(user_id) ON DELETE SET NULL"
];

foreach ($queries as $sql) {
    if ($db->query($sql)) {
        echo "Executed: $sql\n";
    } else {
        echo "Failed: $sql - Error: " . $db->error . "\n";
    }
}
