<?php
require_once 'bootstrap.php';
use App\Core\Database;

$db = Database::getInstance()->getConnection();

$sql = "CREATE TABLE IF NOT EXISTS tbl_document_chunks (
    chunk_id      INT AUTO_INCREMENT PRIMARY KEY,
    doc_id        INT NOT NULL,
    chunk_index   INT NOT NULL DEFAULT 0,
    chunk_text    TEXT NOT NULL,
    embedding     LONGBLOB NOT NULL,           -- Serialized float array (768 dims)
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doc_id) REFERENCES tbl_document(doc_id) ON DELETE CASCADE,
    INDEX idx_doc_id (doc_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($db->query($sql)) {
    echo "Table tbl_document_chunks created successfully.\n";
} else {
    echo "Error creating table: " . $db->error . "\n";
}
