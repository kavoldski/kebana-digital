<?php
require_once 'bootstrap.php';

function test_ollama($endpoint, $data) {
    echo "Testing $endpoint...\n";
    $ch = curl_init('http://127.0.0.1:11434' . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    echo "HTTP Code: $httpCode\n";
    if ($error) echo "CURL Error: $error\n";
    if ($response) echo "Response: " . substr($response, 0, 500) . "...\n";
    echo "-------------------\n";
}

test_ollama('/api/tags', []); // List models
test_ollama('/api/embed', ['model' => 'nomic-embed-text', 'input' => 'test']);
test_ollama('/api/embeddings', ['model' => 'nomic-embed-text', 'prompt' => 'test']);
