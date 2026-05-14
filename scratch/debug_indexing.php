<?php
require_once 'bootstrap.php';
use App\Core\Database;
use App\Services\RAGService;

$db = Database::getInstance()->getConnection();

echo "Checking tbl_document...\n";
$res = $db->query("SELECT * FROM tbl_document");
if ($res->num_rows == 0) {
    echo "No documents found in tbl_document.\n";
} else {
    while ($row = $res->fetch_assoc()) {
        echo "ID: " . $row['doc_id'] . " | Name: " . $row['doc_name'] . " | Path: " . $row['file_path'] . "\n";
        $fullPath = APP_ROOT . '/' . $row['file_path'];
        if (file_exists($fullPath)) {
            echo " - File exists on disk.\n";
            echo " - Attempting to index...\n";
            $success = RAGService::indexDocument($row['doc_id']);
            echo " - Indexing " . ($success ? "SUCCESSFUL" : "FAILED") . "\n";
        } else {
            echo " - File MISSING on disk at: $fullPath\n";
        }
        echo "-------------------\n";
    }
}

echo "\nChecking tbl_document_chunks count...\n";
$res = $db->query("SELECT COUNT(*) as count FROM tbl_document_chunks");
$row = $res->fetch_assoc();
echo "Total chunks in DB: " . $row['count'] . "\n";
