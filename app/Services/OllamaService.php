<?php
/**
 * KEBANA Digital Management System - Ollama Service
 * File: app/Services/OllamaService.php
 */

namespace App\Services;

class OllamaService {
    /**
     * Extract data from a receipt image using Google Gemini API (multimodal).
     * 
     * @param string $filePath Relative path to the file (e.g., 'uploads/receipts/...')
     * @return string|null JSON string of extracted data or null on failure.
     */
    public static function extractReceiptData($filePath) {
        $fullPath = get_absolute_upload_path($filePath);
        
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
        $mimeType = ($extension === 'png') ? 'image/png' : 'image/jpeg';

        $prompt = "Analyze the provided receipt image and extract only the following information into a structured JSON format:
        1. amount (The total amount paid as a number/float, do not include currency symbols)
        2. date (The transaction date in YYYY-MM-DD format)
        3. category (Categorize this receipt into exactly one of these: Food, Transport, Utilities, Supplies, Rental, or Others)

        Return ONLY a JSON object matching this schema. Do not write any explanations or markdown formatting outside the JSON.";

        // Load AI configuration
        $config = require APP_ROOT . '/config/ai.php';
        $apiKey = $config['api_key'] ?? '';
        $model = $config['synthesis_model'] ?? 'gemini-flash-latest';
        $verifySsl = $config['verify_ssl'] ?? true;

        if (empty($apiKey)) {
            error_log("Google Gemini API OCR Error: API Key is not configured.");
            return null;
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        // Gemini Multimodal payload
        $data = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $prompt
                        ],
                        [
                            'inlineData' => [
                                'mimeType' => $mimeType,
                                'data' => $imageData
                            ]
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json'
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $verifySsl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $verifySsl ? 2 : 0);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($error) {
            error_log("Google Gemini OCR CURL Error: " . $error);
            return null;
        }

        if ($httpCode !== 200) {
            error_log("Google Gemini OCR API returned HTTP " . $httpCode . ": " . $response);
            return null;
        }

        if ($response) {
            $decoded = json_decode($response, true);
            $rawResponse = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? '';
            
            // Validate that it's actually JSON before returning
            $cleanResponse = trim($rawResponse);
            json_decode($cleanResponse);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $cleanResponse;
            } else {
                error_log("Google Gemini OCR returned invalid JSON. Raw: " . substr($rawResponse, 0, 200));
            }
        }

        return null;
    }
}
