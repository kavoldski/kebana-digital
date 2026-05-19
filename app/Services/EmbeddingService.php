<?php
/**
 * KEBANA Digital Management System - Embedding Service
 * File: app/Services/EmbeddingService.php
 */

namespace App\Services;

class EmbeddingService {
    /**
     * Generate embedding for a single text using Google Gemini API.
     * 
     * @param string $text
     * @return array|null Vector embedding (array of floats)
     */
    public static function embed($text) {
        if (empty($text)) return null;

        // Load AI configuration
        $config = require APP_ROOT . '/config/ai.php';
        $apiKey = $config['api_key'] ?? '';
        $model = $config['embedding_model'] ?? 'text-embedding-004';
        $verifySsl = $config['verify_ssl'] ?? true;

        if (empty($apiKey)) {
            error_log("Google Gemini API Error: API Key is not configured in config/ai.local.php");
            return null;
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:embedContent?key={$apiKey}";
        
        $data = [
            'model' => "models/{$model}",
            'content' => [
                'parts' => [
                    ['text' => $text]
                ]
            ],
            'output_dimensionality' => 768
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $verifySsl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $verifySsl ? 2 : 0);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("Google Gemini API Curl Error: " . $error);
            return null;
        }

        if ($httpCode === 200 && $response) {
            $result = json_decode($response, true);
            if (isset($result['embedding']['values'])) {
                return $result['embedding']['values'];
            }
        }

        error_log("Google Gemini API Embed Error: HTTP Code $httpCode, Response: " . $response);
        return null;
    }


    /**
     * Compute cosine similarity between two vectors.
     */
    public static function cosineSimilarity($vecA, $vecB) {
        if (!$vecA || !$vecB || count($vecA) !== count($vecB)) {
            return 0;
        }

        $dotProduct = 0;
        $normA = 0;
        $normB = 0;

        for ($i = 0; $i < count($vecA); $i++) {
            $dotProduct += $vecA[$i] * $vecB[$i];
            $normA += $vecA[$i] ** 2;
            $normB += $vecB[$i] ** 2;
        }

        $normA = sqrt($normA);
        $normB = sqrt($normB);

        if ($normA * $normB == 0) return 0;

        return $dotProduct / ($normA * $normB);
    }
}
