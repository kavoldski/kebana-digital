<?php
/**
 * KEBANA Digital Management System - Generate Mobile OCR Session
 * File: modules/api/ocr/generate_session.php
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Kaedah permintaan tidak dibenarkan.'
    ]);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Self-healing: Ensure table exists on production/local database
    $create_table_sql = "CREATE TABLE IF NOT EXISTS mobile_ocr_sessions (
        token VARCHAR(64) PRIMARY KEY,
        status ENUM('pending', 'uploaded', 'completed') DEFAULT 'pending',
        image_data LONGTEXT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX(created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $db->query($create_table_sql);
    
    // Generate a secure 64-character token
    $token = bin2hex(random_bytes(32));
    
    $stmt = $db->prepare("INSERT INTO mobile_ocr_sessions (token, status, created_at) VALUES (?, 'pending', NOW())");
    $stmt->bind_param("s", $token);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'token' => $token
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Gagal mencipta sesi: ' . $db->error
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
