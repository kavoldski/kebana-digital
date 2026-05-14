<?php
/**
 * KEBANA Digital Management System - RAG Service
 * File: app/Services/RAGService.php
 */

namespace App\Services;

use App\Core\Database;
use Exception;

class RAGService {
    private static $synthesisModel = 'gemma4:31b-cloud';
    private static $synthesisUrl = 'http://localhost:11434/api/generate';

    /**
     * Index a document: Extract -> Chunk -> Embed -> Store.
     */
    public static function indexDocument($docId) {
        $db = Database::getInstance()->getConnection();

        // Get document info
        $stmt = $db->prepare("SELECT * FROM tbl_document WHERE doc_id = ?");
        $stmt->bind_param("i", $docId);
        $stmt->execute();
        $doc = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$doc) return false;

        $fullPath = APP_ROOT . '/' . $doc['file_path'];
        
        // 1. Extract Text
        $text = TextExtractorService::extractText($fullPath);
        
        // If extraction fails, use metadata as a fallback
        if (empty(trim($text))) {
            $text = "Nama Fail: " . $doc['doc_name'] . "\nTag: " . ($doc['doc_tags'] ?: 'Tiada Tag');
        }

        // 2. Chunk Text
        $chunks = TextExtractorService::chunkText($text);

        // Clear existing chunks for this doc
        $db->query("DELETE FROM tbl_document_chunks WHERE doc_id = $docId");

        // 3. Embed and Store
        $successCount = 0;
        $stmt = $db->prepare("INSERT INTO tbl_document_chunks (doc_id, chunk_index, chunk_text, embedding) VALUES (?, ?, ?, ?)");

        foreach ($chunks as $index => $chunkText) {
            $embedding = EmbeddingService::embed($chunkText);
            
            if ($embedding) {
                $serialized = serialize($embedding);
                $stmt->bind_param("iiss", $docId, $index, $chunkText, $serialized);
                if ($stmt->execute()) {
                    $successCount++;
                }
            }
        }
        $stmt->close();

        return $successCount > 0;
    }

    /**
     * Perform semantic search.
     */
    public static function search($query, $topK = 5) {
        $db = Database::getInstance()->getConnection();
        
        // 1. Embed query
        $queryVector = EmbeddingService::embed($query);
        if (!$queryVector) return [];

        // 2. Retrieve all chunks (for simplicity in small-scale, we calculate similarity in PHP)
        // In a production environment with millions of docs, we'd use a vector DB or MySQL vector extension
        $results = [];
        $res = $db->query("SELECT c.*, d.doc_name, d.file_path 
                          FROM tbl_document_chunks c
                          JOIN tbl_document d ON c.doc_id = d.doc_id");
        
        while ($row = $res->fetch_assoc()) {
            $chunkVector = unserialize($row['embedding']);
            $similarity = EmbeddingService::cosineSimilarity($queryVector, $chunkVector);
            
            if ($similarity > 0.3) { // Threshold
                $row['score'] = $similarity;
                $results[] = $row;
            }
        }

        // 3. Sort by score
        usort($results, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return array_slice($results, 0, $topK);
    }

    /**
     * Full RAG pipeline: Search + Synthesis.
     */
    public static function ask($question) {
        $startTime = microtime(true);
        
        // 1. Retrieve relevant context
        $chunks = self::search($question, 5);
        
        if (empty($chunks)) {
            return [
                'success' => true,
                'answer' => "Maaf, saya tidak menjumpai sebarang maklumat berkaitan soalan anda dalam arkib dokumen.",
                'sources' => [],
                'time' => round((microtime(true) - $startTime) * 1000)
            ];
        }

        // 2. Construct prompt
        $context = "";
        foreach ($chunks as $i => $chunk) {
            $context .= "[" . ($i + 1) . "] Dokumen: " . $chunk['doc_name'] . "\nKandungan: " . $chunk['chunk_text'] . "\n\n";
        }

        $prompt = "Anda adalah pembantu AI pintar untuk Sistem Pengurusan Digital KEBANA.
Gunakan petikan dokumen di bawah untuk menjawab soalan pengguna. 
Jika jawapan tiada dalam petikan, katakan 'Maklumat tidak dijumpai dalam arkib.'
Sila jawab dalam Bahasa Melayu. Nyatakan rujukan [1], [2] dsb jika berkaitan.

--- PETIKAN DOKUMEN ---
$context
--- TAMAT PETIKAN ---

Soalan: $question
Jawapan:";

        // 3. Call LLM for synthesis
        $data = [
            'model' => self::$synthesisModel,
            'prompt' => $prompt,
            'stream' => false
        ];

        $ch = curl_init(self::$synthesisUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        $answer = "Ralat semasa menjana jawapan.";
        if ($httpCode === 200) {
            $decoded = json_decode($response, true);
            $answer = $decoded['response'] ?? "Tiada jawapan diterima.";
        }

        return [
            'success' => true,
            'answer' => $answer,
            'sources' => $chunks,
            'time' => round((microtime(true) - $startTime) * 1000)
        ];
    }
}
