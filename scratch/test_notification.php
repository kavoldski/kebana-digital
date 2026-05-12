<?php
/**
 * Test script for notifications
 */
require_once '../bootstrap.php';

use App\Helpers\NotificationHelper;

// Target the current user if logged in, otherwise default to 1
$userId = $_SESSION['user_id'] ?? 1;

$success = NotificationHelper::create(
    $userId,
    'system_test',
    'Ujian Notifikasi Realtime',
    'Ini adalah notifikasi ujian yang dihantar pada ' . date('H:i:s') . '. Sistem realtime anda sedang berfungsi!',
    'dashboard'
);

if ($success) {
    echo "Notification created successfully for User 1. Check your dashboard/header!";
} else {
    echo "Failed to create notification.";
}
