<?php
/**
 * Test RAG steps one by one under Apache to locate the segfault.
 */
require_once __DIR__ . '/../../bootstrap.php';
use App\Services\EmbeddingService;
use App\Core\Database;

header('Content-Type: text/plain');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Disable output buffering
while (ob_get_level()) {
    ob_end_flush();
}
ob_implicit_flush(true);

echo "Step 1: Connecting to database...\n";
$db = Database::getInstance()->getConnection();
echo "Connected successfully.\n\n";

echo "Step 2: Mocking query 'What is KEBANA?'...\n";
$query = "What is KEBANA?";
echo "Query: $query\n\n";

echo "Step 3: Generating query embedding...\n";
$queryVector = EmbeddingService::embed($query);
if ($queryVector) {
    echo "Query vector generated. Length: " . count($queryVector) . "\n\n";
} else {
    echo "Failed to generate query vector.\n\n";
    exit;
}

echo "Step 4: Querying document chunks from database...\n";
$res = $db->query("SELECT chunk_id, doc_id, chunk_index, embedding FROM tbl_document_chunks LIMIT 10");
echo "Found chunks: " . $res->num_rows . "\n\n";

echo "Step 5: Unserializing and calculating similarity for each...\n";
$i = 0;
while ($row = $res->fetch_assoc()) {
    $i++;
    echo "  Processing chunk #$i (chunk_id={$row['chunk_id']})... ";
    
    $emb_data = $row['embedding'];
    echo "[Data len: " . strlen($emb_data) . "] ";
    
    $chunkVector = unserialize($emb_data);
    if ($chunkVector) {
        echo "[Unserialized length: " . count($chunkVector) . "] ";
    } else {
        echo "[Unserialize FAILED] ";
    }
    
    $sim = EmbeddingService::cosineSimilarity($queryVector, $chunkVector);
    echo "[Similarity: " . round($sim, 4) . "]\n";
}

echo "\nStep 6: Completed successfully without crash!\n";
