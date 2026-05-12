<?php
/**
 * KEBANA Management System - Notification SSE Stream
 * File: modules/api/notifications_stream.php
 */

require_once '../../bootstrap.php';

use App\Helpers\NotificationHelper;

// Set headers for SSE
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // Disable buffering for Nginx if applicable

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "data: " . json_encode(['error' => 'Unauthorized']) . "\n\n";
    exit();
}

$userId = $_SESSION['user_id'];

// IMPORTANT: Close the session for writing to prevent session locking!
// This allows other pages to load while this stream is running.
session_write_close();

// Prevent script timeout
set_time_limit(0);

$lastCount = -1;

// Loop to check for updates
while (true) {
    // Check for unread count
    $currentCount = NotificationHelper::countUnread($userId);
    
    if ($currentCount !== $lastCount) {
        $notifications = NotificationHelper::getLatest($userId, 5);
        $data = [
            'count' => $currentCount,
            'notifications' => $notifications
        ];
        
        echo "data: " . json_encode($data) . "\n\n";
        ob_flush();
        flush();
        
        $lastCount = $currentCount;
    }
    
    // Break if connection lost
    if (connection_aborted()) break;
    
    // Wait before next check
    sleep(3);
}
