<?php
/**
 * Scratch script to reindex all documents with new metadata-enriched 900-char chunks.
 */
require_once __DIR__ . '/../bootstrap.php';

use App\Services\RAGService;
use App\Core\Database;

echo "Starting reindexing of all archived documents...\n";

$db = Database::getInstance()->getConnection();
$res = $db->query("SELECT doc_id, doc_name FROM tbl_document");

$indexed = 0;
$failed = 0;

while ($row = $res->fetch_assoc()) {
    $docId = $row['doc_id'];
    $docName = $row['doc_name'];
    echo "Indexing doc_id={$docId} ('{$docName}')... ";
    
    $start = microtime(true);
    if (RAGService::indexDocument($docId)) {
        $elapsed = round(microtime(true) - $start, 2);
        echo "SUCCESS in {$elapsed}s\n";
        $indexed++;
    } else {
        echo "FAILED\n";
        $failed++;
    }
}

echo "\nReindexing finished. Success: {$indexed}, Failed: {$failed}.\n";
