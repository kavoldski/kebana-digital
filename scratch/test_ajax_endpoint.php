<?php
/**
 * Simulate calling modules/documents/ajax_rag_search.php as a logged-in user.
 */
// Start session and mock user login
session_start();
$_SESSION['user_id'] = 20; // kavoldski
$_SESSION['role'] = 888;
$_SESSION['cawangan_id'] = 1;

// Set up mock request body
$query = "What is the bachelor degree of the author Cedric Hilary Samah?";
$_POST = [];
$_GET = [];

// Capture output
ob_start();
// Mock php://input wrapper
// Since we can't easily override php://input in standard include, we can run it using curl or mock the logic.
// Wait, let's look at ajax_rag_search.php:
// $input = json_decode(file_get_contents('php://input'), true);
// $query = $input['query'] ?? '';
//
// Since it reads php://input, if we include it directly, it will read empty input.
// Instead, let's write a script that makes an actual local HTTP POST request to the endpoint!
// We can use cURL to login first and then post to the endpoint, or we can temporarily modify the endpoint or check.
// Let's do a curl request!

// Create a cookie jar
$cookieJar = tempnam(sys_get_temp_dir(), 'cookie');

echo "1. Attempting login...\n";
$ch = curl_init('http://127.0.0.1/kebana-digital/modules/auth/authenticate.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'username' => 'kavoldski',
    'password' => 'password123'
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$response = curl_exec($ch);
curl_close($ch);
echo "Login response: " . substr(strip_tags($response), 0, 500) . "\n\n";

echo "2. Querying RAG endpoint...\n";
$ch = curl_init('http://127.0.0.1/kebana-digital/modules/documents/ajax_rag_search.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['query' => $query]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
curl_close($ch);

echo "HTTP Code: {$httpCode}\n";
echo "Response: \n{$response}\n";

unlink($cookieJar);
