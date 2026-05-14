<?php
/**
 * KEBANA Digital Management System - Attendance API
 * File: modules/api/checkin_api.php
 */

require_once '../../bootstrap.php';
use App\Helpers\EventsHelper;

header('Content-Type: application/json');

// Handle Check-in (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;
    $token = $_POST['token'] ?? '';
    $ic_number = $_POST['ic_number'] ?? '';

    if ($event_id <= 0 || empty($token) || empty($ic_number)) {
        echo json_encode(['status' => false, 'message' => 'Maklumat tidak lengkap.']);
        exit;
    }

    // Validate token
    if (!EventsHelper::validateCheckinToken($event_id, $token)) {
        echo json_encode(['status' => false, 'message' => 'Token tidak sah.']);
        exit;
    }

    // Perform check-in
    $result = EventsHelper::checkinByIC($event_id, $ic_number);
    echo json_encode($result);
    exit;
}

// Handle Live Data Polling (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
    
    if ($event_id <= 0) {
        echo json_encode(['error' => 'ID Acara tidak sah']);
        exit;
    }

    // Security check: Only logged-in users can poll live data (admin view)
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $data = EventsHelper::getLiveAttendanceData($event_id);
    echo json_encode($data);
    exit;
}
