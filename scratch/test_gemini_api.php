<?php
/**
 * Test script for Google Gemini API integration (embeddings and synthesis).
 * File: scratch/test_gemini_api.php
 */
require_once __DIR__ . '/../bootstrap.php';

use App\Services\EmbeddingService;
use App\Services\RAGService;

echo "--- TESTING GOOGLE GEMINI API CONNECTIONS ---\n\n";

// Load configuration
$config = require APP_ROOT . '/config/ai.php';
$apiKey = $config['api_key'] ?? '';

if (empty($apiKey) || $apiKey === 'YOUR_GEMINI_API_KEY_HERE') {
    echo "ERROR: Gemini API Key is not set in config/ai.local.php!\n";
    echo "Please copy config/ai.local.php.example to config/ai.local.php and add your real key.\n";
    exit(1);
}

echo "API Key check: OK (starts with: " . substr($apiKey, 0, 5) . "...)\n\n";

// 1. Test Embedding Service
echo "1. Testing Embedding Service...\n";
$start = microtime(true);
$embedding = EmbeddingService::embed("Halo Dunia! Selamat datang ke KEBANA Digital.");
$time = round((microtime(true) - $start) * 1000, 2);

if ($embedding && is_array($embedding)) {
    echo "SUCCESS: Generated embedding in {$time}ms\n";
    echo "Vector size: " . count($embedding) . " dimensions (expected: 768)\n";
    echo "Vector sample (first 5 values): [" . implode(", ", array_slice($embedding, 0, 5)) . "]\n\n";
} else {
    echo "FAILED to generate embedding. Please check error logs.\n\n";
}

// 2. Test Synthesis / LLM Generation via RAGService (with a search ask)
echo "2. Testing Synthesis / Generation...\n";
$start = microtime(true);
$res = RAGService::ask("Siapakah pembina sistem ini?");
$time = round((microtime(true) - $start) * 1000, 2);

echo "Response received in {$time}ms\n";
echo "SUCCESS Status: " . ($res['success'] ? 'YES' : 'NO') . "\n";
echo "Answer:\n";
echo "--------------------------------------------------\n";
echo $res['answer'] . "\n";
echo "--------------------------------------------------\n\n";
