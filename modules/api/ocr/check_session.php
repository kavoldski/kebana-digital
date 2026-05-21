<?php
/**
 * KEBANA Digital Management System - Check Mobile OCR Session Status
 * File: modules/api/ocr/check_session.php
 */

header('Content-Type: application/json');

use App\Core\Database;

// Verify authorization (only users authorized to add/edit members)
if (!hasRole([888, 1, 4, 33])) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'error' => 'Akses disekat.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Kaedah permintaan tidak dibenarkan.'
    ]);
    exit;
}

$token = trim($_GET['token'] ?? '');

if (empty($token)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Token tidak sah.'
    ]);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("SELECT status, image_data FROM mobile_ocr_sessions WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(444);
        echo json_encode([
            'success' => false,
            'error' => 'Sesi tidak dijumpai.'
        ]);
        exit;
    }
    
    $row = $result->fetch_assoc();
    $status = $row['status'];
    $image_data = $row['image_data'];
    
    if ($status === 'uploaded') {
        // Update status to completed so it doesn't get processed again
        $update_stmt = $db->prepare("UPDATE mobile_ocr_sessions SET status = 'completed' WHERE token = ?");
        $update_stmt->bind_param("s", $token);
        $update_stmt->execute();
        $update_stmt->close();
        
        echo json_encode([
            'success' => true,
            'status' => 'uploaded',
            'image_data' => $image_data
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'status' => $status
        ]);
    }
    
    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Ralat: ' . $e->getMessage()
    ]);
}
exit;
