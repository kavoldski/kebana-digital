<?php
/**
 * KEBANA Digital Management System - Events API
 * File: modules/api/events.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../app/Core/Database.php';
require_once '../../app/Helpers/NotificationHelper.php';
require_once '../../app/Helpers/EventsHelper.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid ID']);
    exit;
}

$event = App\Helpers\EventsHelper::getEventById($id);

if ($event) {
    // Add additional info if needed
    $event['formatted_date'] = date('d F Y', strtotime($event['event_date']));
    $event['formatted_budget'] = number_format($event['budget_est'] ?? 0, 2);
    echo json_encode($event);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Event not found']);
}
