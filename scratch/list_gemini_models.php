<?php
require_once __DIR__ . '/../bootstrap.php';

$config = require APP_ROOT . '/config/ai.php';
$apiKey = $config['api_key'] ?? '';

if (empty($apiKey) || $apiKey === 'YOUR_GEMINI_API_KEY_HERE') {
    echo "ERROR: Gemini API Key is not set!\n";
    exit(1);
}

$url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . $apiKey;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
if ($httpCode === 200 && $response) {
    $data = json_decode($response, true);
    if (isset($data['models'])) {
        foreach ($data['models'] as $m) {
            echo "- Name: " . $m['name'] . "\n";
            echo "  Supported Methods: " . implode(", ", $m['supportedGenerationMethods']) . "\n\n";
        }
    } else {
        echo "No models listed. Response: $response\n";
    }
} else {
    echo "Request failed: $response\n";
}
