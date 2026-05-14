<?php
/**
 * KEBANA Digital Management System - Notifications API
 * File: modules/api/notifications.php
 */

require_once '../../bootstrap.php';

use App\Helpers\NotificationHelper;

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$action = $_GET['action'] ?? '';
$userId = $_SESSION['user_id'];
$input = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    if (!empty($rawInput)) {
        $decoded = json_decode($rawInput, true);
        if (is_array($decoded)) {
            $input = $decoded;
            $action = $input['action'] ?? $action;
        }
    }
}

switch ($action) {
    case 'mark_as_read':
        $notificationId = (int)($_GET['id'] ?? ($input['id'] ?? 0));
        if ($notificationId > 0) {
            $success = NotificationHelper::markAsRead($notificationId);
            echo json_encode(['success' => $success]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        }
        break;

    case 'mark_all_read':
        $success = NotificationHelper::markAllAsRead($userId);
        echo json_encode(['success' => $success]);
        break;
    
    case 'clear_all':
        $success = NotificationHelper::deleteAll($userId);
        echo json_encode(['success' => $success]);
        break;

    case 'get_unread_count':
        $count = NotificationHelper::countUnread($userId);
        echo json_encode(['success' => true, 'count' => $count]);
        break;

    case 'get_latest':
        $count = NotificationHelper::countUnread($userId);
        $notifications = NotificationHelper::getLatest($userId, 5);
        echo json_encode([
            'success' => true, 
            'count' => $count, 
            'notifications' => $notifications
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
