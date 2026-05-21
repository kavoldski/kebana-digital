<?php
/**
 * KEBANA Digital Management System - Delete Announcement Image AJAX Endpoint
 * File: modules/announcements/delete_image.php
 */

use App\Helpers\AnnouncementHelper;

header('Content-Type: application/json');

// Only allow Setiausaha Pusat, Super Admin, Presiden
if (!in_array($current_role, [888, 1, 4])) {
    http_response_code(430);
    echo json_encode([
        'success' => false,
        'error' => 'Akses disekat. Anda tidak mempunyai kebenaran untuk melakukan tindakan ini.'
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

$imageId = isset($_POST['image_id']) ? (int)$_POST['image_id'] : 0;

if ($imageId <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'ID Gambar tidak sah.'
    ]);
    exit;
}

try {
    $success = AnnouncementHelper::deleteAnnouncementImage($imageId);
    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Gambar berjaya dipadam.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Gagal memadam gambar daripada pangkalan data atau pelayan.'
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
