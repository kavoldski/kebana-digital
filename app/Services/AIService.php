<?php
/**
 * KEBANA Digital Management System - AI Service
 * File: app/Services/AIService.php
 */

namespace App\Services;

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

        if ($error) {
            error_log("Google Gemini AIService cURL Error: " . $error);
            return "Ralat cURL: " . $error;
        }

        if ($httpCode === 200 && $response) {
            $decoded = json_decode($response, true);
            $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? '';
            if (!empty($text)) {
                return trim($text);
            }
        }

        error_log("Google Gemini AIService API Error: HTTP Code $httpCode, Response: " . $response);
        return "Ralat menjana kandungan daripada Google Gemini API (HTTP $httpCode).";
    }
}
