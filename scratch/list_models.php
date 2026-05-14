<?php
echo "Listing Ollama models via GET /api/tags...\n";
$ch = curl_init('http://localhost:11434/api/tags');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    $data = json_decode($response, true);
    if (isset($data['models'])) {
        foreach ($data['models'] as $m) {
            echo "- " . $m['name'] . "\n";
        }
    } else {
        echo "No models found.\n";
    }
} else {
    echo "Could not connect to Ollama.\n";
}
