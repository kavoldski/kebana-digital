<?php
require_once 'bootstrap.php';
use App\Core\Database;
use App\Services\TextExtractorService;
use App\Services\EmbeddingService;

$db = Database::getInstance()->getConnection();

echo "Step 1: Checking Database...\n";
$res = $db->query("SELECT * FROM tbl_document LIMIT 1");
$doc = $res->fetch_assoc();
if (!$doc) {
    die("No documents found in DB.\n");
}
echo "Found Document: " . $doc['doc_name'] . "\n";

echo "\nStep 2: Testing Text Extraction...\n";
$fullPath = APP_ROOT . '/' . $doc['file_path'];
$text = TextExtractorService::extractText($fullPath);
echo "Extracted Text Length: " . strlen($text) . " characters.\n";
if (strlen($text) > 0) {
    echo "Snippet: " . substr($text, 0, 100) . "...\n";
} else {
    echo "ERROR: Text extraction failed.\n";
}

echo "\nStep 3: Testing Chunking...\n";
$chunks = TextExtractorService::chunkText($text);
echo "Total Chunks: " . count($chunks) . "\n";
if (count($chunks) > 0) {
    echo "First Chunk: " . substr($chunks[0], 0, 50) . "...\n";
}

echo "\nStep 4: Testing Ollama Embedding...\n";
if (count($chunks) > 0) {
    $vec = EmbeddingService::embed($chunks[0]);
    if ($vec) {
        echo "SUCCESS: Generated vector of size " . count($vec) . "\n";
    } else {
        echo "ERROR: Ollama embedding failed. Is the model 'nomic-embed-text' pulled? Run 'ollama pull nomic-embed-text'\n";
    }
}
