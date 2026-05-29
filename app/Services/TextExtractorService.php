<?php
/**
 * KEBANA Digital Management System - Text Extractor Service
 * File: app/Services/TextExtractorService.php
 */

namespace App\Services;

use Smalot\PdfParser\Parser;
use ZipArchive;
use Exception;

class TextExtractorService {
    /**
     * Extract text from a file.
     * 
     * @param string $filePath Full path to the file
     * @return string Extracted text
     */
    public static function extractText($filePath) {
        if (!file_exists($filePath)) {
            return "";
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        switch ($extension) {
            case 'pdf':
                return self::extractFromPdf($filePath);
            case 'docx':
                return self::extractFromDocx($filePath);
            case 'txt':
                return file_get_contents($filePath);
            case 'jpg':
            case 'jpeg':
                return self::extractUsingGemini($filePath, 'image/jpeg');
            case 'png':
                return self::extractUsingGemini($filePath, 'image/png');
            default:
                return "";
        }
    }

    /**
     * Extract text from PDF using smalot/pdfparser.
     */
    private static function extractFromPdf($filePath) {
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($filePath);
            $text = $pdf->getText();
            
            // If text is empty, it's likely a scanned PDF. Trigger Gemini OCR.
            if (empty(trim($text))) {
                error_log("PDF Extraction: Native text extraction is empty. Falling back to Gemini OCR for: " . $filePath);
                return self::extractUsingGemini($filePath, 'application/pdf');
            }
            
            return $text;
        } catch (Exception $e) {
            error_log("PDF Extraction Error: " . $e->getMessage() . ". Falling back to Gemini OCR.");
            return self::extractUsingGemini($filePath, 'application/pdf');
        }
    }

    /**
     * Extract text using Gemini API's multimodal capabilities (OCR).
     */
    private static function extractUsingGemini($filePath, $mimeType) {
        if (!file_exists($filePath)) {
            return "";
        }

        // Load API key and config
        if (!defined('APP_ROOT')) {
            define('APP_ROOT', dirname(__DIR__, 2));
        }
        $configPath = APP_ROOT . '/config/ai.php';
        if (!file_exists($configPath)) {
            error_log("Gemini OCR Error: config/ai.php not found");
            return "";
        }
        $config = require $configPath;
        $apiKey = $config['api_key'] ?? '';
        $model = $config['synthesis_model'] ?? 'gemini-2.5-flash';
        $verifySsl = $config['verify_ssl'] ?? true;

        if (empty($apiKey)) {
            error_log("Gemini OCR Error: API Key not configured.");
            return "";
        }

        $fileData = base64_encode(file_get_contents($filePath));
        
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => "Extract all readable text from this document. Output ONLY the raw extracted text. Maintain paragraphs if possible. Do not add any introduction, explanations, or markdown wrappers. If no text is readable, output nothing."],
                        [
                            'inlineData' => [
                                'mimeType' => $mimeType,
                                'data' => $fileData
                            ]
                        ]
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
                error_log("Google Gemini API HTTP $httpCode on OCR attempt $attempt. Retrying in {$retryDelay}s...");
                sleep($retryDelay);
                $retryDelay *= 2;
                continue;
            }

            break;
        }

        if ($error) {
            error_log("Gemini OCR cURL Error: " . $error);
            return "";
        }

        if ($httpCode === 200 && $response) {
            $decoded = json_decode($response, true);
            $extractedText = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? "";
            return trim($extractedText);
        } else {
            error_log("Gemini OCR API Error: HTTP $httpCode, Response: $response");
            return "";
        }
    }

    /**
     * Extract text from DOCX (Office Open XML).
     */
    private static function extractFromDocx($filePath) {
        $text = "";
        $zip = new ZipArchive();

        if ($zip->open($filePath) === true) {
            if (($index = $zip->locateName('word/document.xml')) !== false) {
                $data = $zip->getFromIndex($index);
                $zip->close();

                // Remove XML tags and extract content
                $text = strip_tags($data);
            } else {
                $zip->close();
            }
        }

        return $text;
    }

    /**
     * Chunk text into smaller pieces for embedding using a Recursive Character Splitter.
     * This preserves semantic context such as paragraphs, table rows, and complete sentences.
     * 
     * @param string $text The full text to chunk
     * @param int $chunkSize Target character count per chunk
     * @param int $overlap Character overlap between chunks
     * @return array Array of text chunks
     */
    public static function chunkText($text, $chunkSize = 1200, $overlap = 200) {
        // Increase memory limit for processing large files
        ini_set('memory_limit', '1024M');
        
        $textLength = mb_strlen($text);
        
        // Safety cap: don't process more than 1MB of text at once for RAG
        // This prevents memory exhaustion and extremely long wait times
        if ($textLength > 1000000) {
            $text = mb_substr($text, 0, 1000000);
            $textLength = 1000000;
        }

        if ($textLength <= $chunkSize) {
            return [trim($text)];
        }

        $separators = ["\n\n", "\n", ". ", " ", ""];
        return self::recursiveSplit($text, $separators, $chunkSize, $overlap);
    }

    /**
     * Helper to recursively split text by a list of separators.
     */
    private static function recursiveSplit($text, $separators, $chunkSize, $overlap) {
        $finalChunks = [];
        $separator = "";
        $nextSeparators = [];

        // Find the first separator that actually exists in the text
        foreach ($separators as $i => $sep) {
            if ($sep === "") {
                $separator = $sep;
                $nextSeparators = array_slice($separators, $i + 1);
                break;
            }
            if (mb_strpos($text, $sep) !== false) {
                $separator = $sep;
                $nextSeparators = array_slice($separators, $i + 1);
                break;
            }
        }

        // Split text by the separator
        if ($separator === "") {
            $splits = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
        } else {
            $splits = explode($separator, $text);
        }

        $goodSplits = [];
        foreach ($splits as $split) {
            if ($split !== "") {
                $goodSplits[] = $split;
            }
        }

        $currentChunk = [];
        $currentLength = 0;

        foreach ($goodSplits as $split) {
            $splitLength = mb_strlen($split);

            // If a single split is larger than chunkSize, we must split it recursively
            if ($splitLength > $chunkSize) {
                // Save current group if any
                if (!empty($currentChunk)) {
                    $finalChunks[] = implode($separator, $currentChunk);
                    $currentChunk = [];
                    $currentLength = 0;
                }

                if (!empty($nextSeparators)) {
                    $recursiveChunks = self::recursiveSplit($split, $nextSeparators, $chunkSize, $overlap);
                    $finalChunks = array_merge($finalChunks, $recursiveChunks);
                } else {
                    // Hard character chunking as a final resort
                    $start = 0;
                    while ($start < $splitLength) {
                        $finalChunks[] = mb_substr($split, $start, $chunkSize);
                        $start += ($chunkSize - $overlap);
                    }
                }
            } else {
                // Check if adding this split exceeds chunkSize
                $separatorLength = !empty($currentChunk) ? mb_strlen($separator) : 0;
                if ($currentLength + $separatorLength + $splitLength > $chunkSize) {
                    // Save current group
                    if (!empty($currentChunk)) {
                        $finalChunks[] = implode($separator, $currentChunk);
                    }

                    // Carry over overlap by accumulating previous splits from the tail of currentChunk
                    $overlapChunk = [];
                    $overlapLength = 0;
                    for ($i = count($currentChunk) - 1; $i >= 0; $i--) {
                        $prevSplit = $currentChunk[$i];
                        $prevLength = mb_strlen($prevSplit);
                        $sepLength = !empty($overlapChunk) ? mb_strlen($separator) : 0;
                        if ($overlapLength + $sepLength + $prevLength <= $overlap) {
                            array_unshift($overlapChunk, $prevSplit);
                            $overlapLength += $sepLength + $prevLength;
                        } else {
                            break;
                        }
                    }

                    $currentChunk = $overlapChunk;
                    $currentLength = $overlapLength;
                }

                // Add current split
                $currentChunk[] = $split;
                $currentLength += (!empty($currentChunk) && $currentLength > $splitLength ? mb_strlen($separator) : 0) + $splitLength;
            }
        }

        // Add remaining chunk
        if (!empty($currentChunk)) {
            $finalChunks[] = implode($separator, $currentChunk);
        }

        // Post-processing: clean up and trim chunks
        $trimmedChunks = [];
        foreach ($finalChunks as $c) {
            $trimmed = trim($c);
            if ($trimmed !== "") {
                $trimmedChunks[] = $trimmed;
            }
        }

        return $trimmedChunks;
    }
}
