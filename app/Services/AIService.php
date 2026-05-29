<?php
/**
 * KEBANA Digital Management System - AI Service
 * File: app/Services/AIService.php
 */

namespace App\Services;

use Exception;

class AIService {
    /**
     * Generate announcement description based on keywords and selected tone using Google Gemini API.
     * 
     * @param string $prompt User prompt/keywords
     * @param string $tone Preferred tone (e.g. Professional, Kasual, Hebahan Rasmi)
     * @return string Generated content
     */
    public static function generateContent($prompt, $tone = 'Professional') {
        if (empty($prompt)) {
            return "Sila berikan maklumat atau isi utama untuk menjana hebahan.";
        }

        // Load AI configuration
        $config = require APP_ROOT . '/config/ai.php';
        $apiKey = $config['api_key'] ?? '';
        $model = $config['synthesis_model'] ?? 'gemini-2.5-flash';
        $verifySsl = $config['verify_ssl'] ?? true;

        if (empty($apiKey)) {
            error_log("Google Gemini API Error: API Key is not configured in config/ai.local.php");
            return "Ralat: Google Gemini API Key tidak dikonfigurasikan di dalam config/ai.local.php.";
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        // Construct high-quality system-guided prompt
        $fullPrompt = "Anda adalah pembantu AI pintar untuk Sistem Pengurusan Digital KEBANA.
Tugas anda adalah untuk merangka draf kandungan hebahan/artikel rasmi untuk persatuan dalam Bahasa Melayu.

Topik / Isi Penting:
\"{$prompt}\"

Gaya dan Nada Penulisan:
\"{$tone}\"

--- SYARAT UTAMA ---
1. Tulis kandungan hebahan dengan sangat tersusun, kemas, dan profesional dalam Bahasa Melayu.
2. Gunakan pembuka kata yang mesra atau formal bersesuaian dengan nada \"{$tone}\".
3. Nyatakan butiran seperti tarikh, masa, dan tempat dengan format yang jelas (seperti senarai bulet) sekiranya ada dalam isi penting.
4. Gunakan gaya penulisan yang menarik minat ahli persatuan untuk membaca.
5. JANGAN letakkan tajuk hebahan dalam output anda, tulis bahagian isi kandungan sahaja.
6. Berikan terus hasil teks draf hebahan tersebut tanpa sebarang ulasan permulaan (seperti \"Berikut adalah draf...\").

Draf Kandungan:";

        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $fullPrompt]
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
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
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
                error_log("Google Gemini AIService generateContent HTTP $httpCode on attempt $attempt. Retrying in {$retryDelay}s...");
                sleep($retryDelay);
                $retryDelay *= 2;
                continue;
            }

            break;
        }

        if ($error) {
            error_log("Google Gemini AIService cURL Error: " . $error);
            return "Ralat: Masalah sambungan internet atau perkhidmatan AI tergendala. Sila cuba lagi.";
        }

        if ($httpCode === 200 && $response) {
            $decoded = json_decode($response, true);
            $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? '';
            if (!empty($text)) {
                return trim($text);
            }
        }

        error_log("Google Gemini AIService API Error: HTTP Code $httpCode, Response: " . $response);
        
        if ($httpCode === 429 || $httpCode === 423) {
            return "Ralat: Had penggunaan sistem (API Limit) telah dicapai. Sila cuba lagi sebentar lagi.";
        }

        return "Ralat: Gagal menjana kandungan daripada Google Gemini API (HTTP $httpCode).";
    }

    /**
     * Extract data from a receipt image using Google Gemini API (multimodal).
     * 
     * @param string $filePath Relative path to the file (e.g., 'uploads/receipts/...')
     * @return string JSON string of extracted data.
     * @throws Exception when failure occurs or API limit is reached.
     */
    public static function extractReceiptData($filePath) {
        $fullPath = get_absolute_upload_path($filePath);
        
        if (!file_exists($fullPath)) {
            throw new Exception("Fail resit tidak ditemui di dalam sistem.");
        }

        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $imageExtensions = ['jpg', 'jpeg', 'png'];

        if (!in_array($extension, $imageExtensions)) {
            throw new Exception("Format fail tidak disokong. Sila muat naik imej JPG, JPEG atau PNG.");
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
            throw new Exception("Google Gemini API Key tidak dikonfigurasikan.");
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
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
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
                error_log("Google Gemini OCR API HTTP $httpCode on attempt $attempt. Retrying in {$retryDelay}s...");
                sleep($retryDelay);
                $retryDelay *= 2;
                continue;
            }

            break;
        }

        if ($error) {
            error_log("Google Gemini OCR CURL Error: " . $error);
            throw new Exception("Masalah sambungan rangkaian untuk perkhidmatan AI.");
        }

        if ($httpCode !== 200) {
            error_log("Google Gemini OCR API returned HTTP " . $httpCode . ": " . $response);
            if ($httpCode === 429 || $httpCode === 423) {
                throw new Exception("Had penggunaan sistem (API Limit) telah dicapai. Sila cuba lagi sebentar lagi.");
            }
            throw new Exception("Sistem AI gagal memproses resit (HTTP $httpCode).");
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
                throw new Exception("AI gagal mengekstrak data dari resit secara automatik. Sila isi secara manual.");
            }
        }

        throw new Exception("Tiada maklum balas diterima daripada perkhidmatan AI.");
    }
}
