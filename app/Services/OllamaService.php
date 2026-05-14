<?php
/**
 * KEBANA Digital Management System - Ollama Service
 * File: app/Services/OllamaService.php
 */

namespace App\Services;

class OllamaService {
    private static $model = 'gemma4:31b-cloud';
    private static $apiUrl = 'http://localhost:11434/api/generate';

    /**
     * Extract data from a receipt image using Ollama.
     * 
     * @param string $filePath Relative path to the file (e.g., 'uploads/receipts/...')
     * @return string|null JSON string of extracted data or null on failure.
     */
    public static function extractReceiptData($filePath) {
        $fullPath = APP_ROOT . '/' . $filePath;
        
        if (!file_exists($fullPath)) {
            return null;
        }

        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $imageExtensions = ['jpg', 'jpeg', 'png'];

        if (!in_array($extension, $imageExtensions)) {
            return null; // Skip non-image files for now
        }

        // Read image and encode to Base64
        $imageData = base64_encode(file_get_contents($fullPath));

        $prompt = "You are a professional accounting assistant. Analyze the provided receipt image and extract only the following information into a structured JSON format:
        1. amount (The total amount paid as a number)
        2. date (The transaction date in YYYY-MM-DD format)
        3. category (Categorize this receipt into one of these: Food, Transport, Utilities, Supplies, Rental, or Others)

        Return ONLY the raw JSON object. Do not include any explanation or markdown formatting.";

        $data = [
            'model' => self::$model,
            'prompt' => $prompt,
            'images' => [$imageData],
            'stream' => false,
            'format' => 'json'
        ];

        $ch = curl_init(self::$apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300); // Increased to 180 seconds
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($error) {
            error_log("Ollama CURL Error: " . $error);
            return null;
        }

        if ($httpCode !== 200) {
            error_log("Ollama API returned HTTP " . $httpCode . ": " . $response);
            return null;
        }

        if ($response) {
            $decoded = json_decode($response, true);
            $rawResponse = $decoded['response'] ?? '';

            // Clean the response (sometimes AI adds markdown backticks)
            $cleanResponse = preg_replace('/^```json\s*|\s*```$/', '', trim($rawResponse));
            
            // Validate that it's actually JSON before returning
            json_decode($cleanResponse);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $cleanResponse;
            } else {
                error_log("Ollama returned invalid JSON. Raw: " . substr($rawResponse, 0, 200));
            }
        }

        return null;
    }
}
