<?php
ob_start();
/**
 * AJAX Receipt Scanner Endpoint
 */
require_once __DIR__ . '/../../../bootstrap.php';

use App\Helpers\FinanceHelper;
use App\Services\AIService;

header('Content-Type: application/json');

// Simple session check
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['receipt'])) {
    // We reuse the existing upload helper
    $receipt_path = FinanceHelper::handleReceiptUpload($_FILES['receipt']);
    
    if ($receipt_path) {
        try {
            $extracted = AIService::extractReceiptData($receipt_path);
            
            if ($extracted) {
                $data = json_decode($extracted, true);
                ob_clean();
                echo json_encode([
                    'success' => true,
                    'data' => $data,
                    'path' => $receipt_path
                ]);
                exit;
            }
        } catch (Exception $e) {
            ob_clean();
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }
}

ob_clean();
echo json_encode(['success' => false, 'message' => 'Imbasan gagal atau tiada data yang dapat diekstrak.']);
