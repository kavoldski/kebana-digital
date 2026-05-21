<?php
/**
 * KEBANA Digital Management System - AI Generation Endpoint
 * File: modules/api/generate_ai.php
 */

header('Content-Type: application/json');

// Only allow Setiausaha Pusat, Super Admin, Presiden
$current_role = (int)($_SESSION['role'] ?? 0);
if (!in_array($current_role, [888, 1, 4])) {
    http_response_code(430);
    echo json_encode([
        'success' => false,
        'error' => 'Akses disekat. Anda tidak mempunyai kebenaran untuk menggunakan AI.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Kaedah permintaan tidak dibenarkan.'
    ]);
    exit;
}

// Get input parameters
$prompt = $_POST['prompt'] ?? '';
$tone = $_POST['tone'] ?? 'Professional';

if (empty(trim($prompt))) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Sila masukkan isi utama atau topik untuk dijana.'
    ]);
    exit;
}

try {
    $generatedContent = App\Services\AIService::generateContent($prompt, $tone);
    
    // Check if generation returned an error string
    if (strpos($generatedContent, 'Ralat') === 0 || strpos($generatedContent, 'Error') === 0) {
        echo json_encode([
            'success' => false,
            'error' => $generatedContent
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'content' => $generatedContent
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Ralat dalaman server: ' . $e->getMessage()
    ]);
}
exit;
