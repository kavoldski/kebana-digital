<?php
/**
 * KEBANA Digital Management System - Upload Mobile OCR Scanned Image (Public)
 * File: modules/api/ocr/upload_image.php
 */

header('Content-Type: application/json');

use App\Core\Database;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Kaedah permintaan tidak dibenarkan.'
    ]);
    exit;
}

$token = trim($_POST['token'] ?? '');
$image_data = trim($_POST['image_data'] ?? '');

if (empty($token) || empty($image_data)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Token atau data gambar tidak sah.'
    ]);
    exit;
}

// Make sure the image_data is a valid base64 image string
if (strpos($image_data, 'data:image/') !== 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Format gambar tidak disokong. Sila pastikan format yang betul.'
    ]);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if session token exists and is pending
    $stmt = $db->prepare("SELECT status FROM mobile_ocr_sessions WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Sesi tidak dijumpai.'
        ]);
        exit;
    }
    
    $row = $result->fetch_assoc();
    if ($row['status'] !== 'pending') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Sesi ini telah digunakan atau tamat tempoh.'
        ]);
        exit;
    }
    
    $stmt->close();
    
    // Update the session with the image data and set status to uploaded
    $update_stmt = $db->prepare("UPDATE mobile_ocr_sessions SET status = 'uploaded', image_data = ? WHERE token = ?");
    $update_stmt->bind_param("ss", $image_data, $token);
    
    if ($update_stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Gambar berjaya dimuat naik!'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Gagal mengemas kini sesi: ' . $db->error
        ]);
    }
    $update_stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Ralat: ' . $e->getMessage()
    ]);
}
exit;
