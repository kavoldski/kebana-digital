<?php
/**
 * KEBANA Digital Management System - RAG Service
 * File: app/Services/RAGService.php
 */

namespace App\Services;

use App\Core\Database;
use Exception;

class RAGService {

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

        $fullPath = get_absolute_upload_path($doc['file_path']);
        
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

        $docName = $doc['doc_name'] ?? 'Dokumen';
        $docTags = $doc['doc_tags'] ?? 'Tiada Tag';
        $metaPrefix = "[Dokumen: {$docName} | Tag: {$docTags}]\n";

        foreach ($chunks as $index => $chunkText) {
            $chunkWithMeta = $metaPrefix . $chunkText;
            $embedding = EmbeddingService::embed($chunkWithMeta);
            
            if ($embedding) {
                $serialized = serialize($embedding);
                $stmt->bind_param("iiss", $docId, $index, $chunkWithMeta, $serialized);
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
    public static function search($query, $topK = 3) {
        $db = Database::getInstance()->getConnection();
        
        // 1. Embed query
        $queryVector = EmbeddingService::embed($query);
        if (!$queryVector) return [];

        // 2. Extract key terms for lexical boosting
        $keywords = [];
        $cleanQuery = preg_replace('/[^\w\s]/u', ' ', mb_strtolower($query));
        $words = preg_split('/\s+/', $cleanQuery, -1, PREG_SPLIT_NO_EMPTY);
        
        $stopwords = ['yang', 'dan', 'untuk', 'dengan', 'pada', 'dari', 'bagi', 'atau', 'ini', 'itu', 'adalah', 'the', 'and', 'for', 'with', 'this', 'that', 'does', 'what', 'how', 'who', 'where', 'why'];
        foreach ($words as $w) {
            if (mb_strlen($w) >= 3 && !in_array($w, $stopwords)) {
                $keywords[] = $w;
            }
        }

        // 3. Retrieve all chunks and compute similarity
        $results = [];
        $res = $db->query("SELECT c.*, d.doc_name, d.file_path 
                          FROM tbl_document_chunks c
                          JOIN tbl_document d ON c.doc_id = d.doc_id");
        
        while ($row = $res->fetch_assoc()) {
            $chunkVector = unserialize($row['embedding']);
            $similarity = EmbeddingService::cosineSimilarity($queryVector, $chunkVector);
            
            // Lower threshold to 0.40 to capture matches that might have slightly lower similarity but high keyword overlap
            if ($similarity > 0.40) {
                // Apply lexical keyword boost
                $boost = 0.0;
                if (!empty($keywords)) {
                    $chunkTextLower = mb_strtolower($row['chunk_text']);
                    $matchedCount = 0;
                    foreach ($keywords as $kw) {
                        if (mb_strpos($chunkTextLower, $kw) !== false) {
                            $matchedCount++;
                        }
                    }
                    if ($matchedCount > 0) {
                        $boost = min(0.20, $matchedCount * 0.05); // Boost up to +0.20
                    }
                }
                
                $row['score'] = $similarity + $boost;
                $results[] = $row;
            }
        }

        // 4. Sort by score
        usort($results, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        // 5. Deduplicate by document to show unique source files (max 3 documents)
        $bestChunksByDoc = [];
        foreach ($results as $row) {
            $docId = $row['doc_id'];
            if (!isset($bestChunksByDoc[$docId])) {
                $bestChunksByDoc[$docId] = $row;
            }
        }

        return array_slice(array_values($bestChunksByDoc), 0, $topK);
    }

    /**
     * Get adjacent chunks for a document to expand context.
     */
    private static function getExpandedContext($docId, $chunkIndex) {
        $db = Database::getInstance()->getConnection();
        
        // Query chunk_index - 1, chunk_index, chunk_index + 1
        $stmt = $db->prepare("SELECT chunk_text FROM tbl_document_chunks 
                              WHERE doc_id = ? AND chunk_index BETWEEN ? AND ? 
                              ORDER BY chunk_index ASC");
        $startIdx = max(0, $chunkIndex - 1);
        $endIdx = $chunkIndex + 1;
        $stmt->bind_param("iii", $docId, $startIdx, $endIdx);
        $stmt->execute();
        $res = $stmt->get_result();
        
        $texts = [];
        while ($row = $res->fetch_assoc()) {
            $texts[] = $row['chunk_text'];
        }
        $stmt->close();
        
        return implode("\n...\n", $texts);
    }

    /**
     * Full RAG pipeline: Search + Synthesis.
     */
    public static function ask($question) {
        $startTime = microtime(true);
        
        // 1. Retrieve relevant context
        $chunks = self::search($question, 3);
        
        if (empty($chunks)) {
            return [
                'success' => true,
                'answer' => "Maaf, saya tidak menjumpai sebarang maklumat berkaitan soalan anda dalam arkib dokumen. / Sorry, I could not find any information related to your question in the document archive.",
                'sources' => [],
                'time' => round((microtime(true) - $startTime) * 1000)
            ];
        }

        // 2. Construct prompt
        $context = "";
        foreach ($chunks as $i => $chunk) {
            // Expand context by fetching adjacent chunks (±1 index)
            $expandedText = self::getExpandedContext($chunk['doc_id'], $chunk['chunk_index']);
            $context .= "[" . ($i + 1) . "] Dokumen: " . $chunk['doc_name'] . "\nKandungan:\n" . $expandedText . "\n\n";
        }

        $prompt = "Anda adalah pembantu AI pintar untuk Sistem Pengurusan Digital KEBANA.
Gunakan petikan dokumen di bawah untuk menjawab soalan pengguna secara ringkas, tepat dan profesional.

--- SYARAT UTAMA ---
1. Kenalpasti bahasa soalan. Jika soalan dalam Bahasa Melayu, jawab dalam Bahasa Melayu. Jika soalan dalam Bahasa Inggeris, jawab dalam Bahasa Inggeris.
2. Nyatakan rujukan [1], [2], atau [3] jika maklumat tersebut diambil dari dokumen berkenaan.
3. Rujuk HANYA pada kandungan dokumen yang diberikan. JANGAN buat andaian atau menambah maklumat luar.
4. Jika jawapan tiada dalam petikan dokumen, sila nyatakan dengan jelas: 'Maklumat tidak dijumpai dalam arkib dokumen.'

--- PETIKAN DOKUMEN ---
$context
--- TAMAT PETIKAN ---

Soalan: $question
Jawapan:";

        // 3. Call Google Gemini API for synthesis
        $config = require APP_ROOT . '/config/ai.php';
        $apiKey = $config['api_key'] ?? '';
        $model = $config['synthesis_model'] ?? 'gemini-2.5-flash';
        $verifySsl = $config['verify_ssl'] ?? true;

        if (empty($apiKey)) {
            return [
                'success' => false,
                'answer' => "Google Gemini API Key is not configured. Sila semak config/ai.local.php.",
                'sources' => [],
                'time' => round((microtime(true) - $startTime) * 1000)
            ];
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ];

        $maxRetries = 4;
        $retryDelay = 1;
        $response = null;
        $httpCode = 0;
        $error = '';

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 120);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $verifySsl);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $verifySsl ? 2 : 0);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($httpCode === 200) {
                break;
            }

            if (($httpCode === 429 || $httpCode === 503) && $attempt < $maxRetries) {
                error_log("Google Gemini API HTTP $httpCode on synthesis attempt $attempt. Retrying in {$retryDelay}s...");
                sleep($retryDelay);
                $retryDelay *= 2;
                continue;
            }

            break;
        }

        $answer = "Ralat semasa menjana jawapan.";
        if ($error) {
            $answer = "Ralat cURL: " . $error;
        } elseif ($httpCode === 200 && $response) {
            $decoded = json_decode($response, true);
            $answer = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? "Tiada jawapan diterima.";
        } else {
            error_log("Google Gemini API Synthesis Error: HTTP $httpCode, Response: $response");
            if ($httpCode === 429 || $httpCode === 423) {
                $answer = "Maaf, sistem sedang mengalami trafik yang tinggi (Had API dicapai). Sila cuba lagi sebentar lagi.";
            } else {
                $answer = "Maaf, perkhidmatan AI tergendala buat sementara waktu (HTTP $httpCode). Sila cuba lagi.";
            }
        }

        return [
            'success' => true,
            'answer' => $answer,
            'sources' => $chunks,
            'time' => round((microtime(true) - $startTime) * 1000)
        ];
    }
}
