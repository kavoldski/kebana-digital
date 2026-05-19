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
            return $pdf->getText();
        } catch (Exception $e) {
            error_log("PDF Extraction Error: " . $e->getMessage());
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
     * Chunk text into smaller pieces for embedding.
     * 
     * @param string $text The full text to chunk
     * @param int $chunkSize Target character count per chunk
     * @param int $overlap Character overlap between chunks
     * @return array Array of text chunks
     */
    public static function chunkText($text, $chunkSize = 900, $overlap = 200) {
        // Increase memory limit for processing large files
        ini_set('memory_limit', '1024M');
        
        $chunks = [];
        $textLength = mb_strlen($text);
        
        // Safety cap: don't process more than 1MB of text at once for RAG
        // This prevents memory exhaustion and extremely long Ollama wait times
        if ($textLength > 1000000) {
            $text = mb_substr($text, 0, 1000000);
            $textLength = 1000000;
        }

        if ($textLength <= $chunkSize) {
            return [trim($text)];
        }

        $start = 0;
        $maxChunks = 2000; // Hard cap on chunks per document
        
        while ($start < $textLength && count($chunks) < $maxChunks) {
            $end = min($start + $chunkSize, $textLength);
            $chunk = mb_substr($text, $start, $end - $start);
            
            // Try to find a good breaking point (space or newline) near the end
            if ($end < $textLength) {
                $lastSpace = mb_strrpos($chunk, ' ');
                $lastNewline = mb_strrpos($chunk, "\n");
                $breakPoint = max($lastSpace, $lastNewline);
                
                // Only break if it's in the last 20% of the chunk
                if ($breakPoint !== false && $breakPoint > ($chunkSize * 0.8)) {
                    $chunk = mb_substr($chunk, 0, $breakPoint);
                    $end = $start + mb_strlen($chunk);
                }
            }
            
            $trimmedChunk = trim($chunk);
            if (!empty($trimmedChunk)) {
                $chunks[] = $trimmedChunk;
            }
            
            $nextStart = $end - $overlap;
            
            // Ensure we are making progress to avoid infinite loops
            if ($nextStart <= $start) {
                $start = $end;
            } else {
                $start = $nextStart;
            }
            
            if ($start >= $textLength) break;
        }

        return $chunks;
    }
}
