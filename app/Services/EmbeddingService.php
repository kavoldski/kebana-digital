<?php
/**
 * KEBANA Digital Management System - Embedding Service
 * File: app/Services/EmbeddingService.php
 */

namespace App\Services;

class EmbeddingService {
    private static $model = 'nomic-embed-text';
    private static $apiUrl = 'http://localhost:11434/api/embed';

    /**
     * Generate embedding for a single text.
     * 
     * @param string $text
     * @return array|null Vector embedding (array of floats)
     */
    public static function embed($text) {
        if (empty($text)) return null;

        // Try new /api/embed endpoint first
        $result = self::callOllama('/api/embed', [
            'model' => self::$model,
            'input' => $text
        ]);

        if ($result && isset($result['embeddings'][0])) {
            return $result['embeddings'][0];
        }

        // Fallback to legacy /api/embeddings endpoint
        $result = self::callOllama('/api/embeddings', [
            'model' => self::$model,
            'prompt' => $text
        ]);

        if ($result && isset($result['embedding'])) {
            return $result['embedding'];
        }

        return null;
    }

    /**
     * Helper to call Ollama API.
     */
    private static function callOllama($endpoint, $data) {
        $ch = curl_init('http://localhost:11434' . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            return json_decode($response, true);
        }
        
        if ($httpCode !== 404) {
            error_log("Ollama API $endpoint Error: HTTP $httpCode");
        }
        
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
