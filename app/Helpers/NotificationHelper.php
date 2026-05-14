<?php
/**
 * KEBANA Digital Management System - Notification Helper
 * File: app/Helpers/NotificationHelper.php
 */

namespace App\Helpers;

use App\Core\Database;

class NotificationHelper {
    
    /**
     * Create a new notification for a user
     */
    public static function create($userId, $type, $title, $message, $actionUrl = null) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("INSERT INTO tbl_notification (user_id, type, title, message, action_url) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("issss", $userId, $type, $title, $message, $actionUrl);
            $success = $stmt->execute();
            $stmt->close();
            return $success;
        }
        return false;
    }

    /**
     * Get unread notifications for a user
     */
    public static function getUnread($userId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM tbl_notification WHERE user_id = ? AND status = 'unread' ORDER BY created_at DESC");
        $notifications = [];
        if ($stmt) {
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $notifications[] = $row;
            }
            $stmt->close();
        }
        return $notifications;
    }

    /**
     * Get latest notifications for a user
     */
    public static function getLatest($userId, $limit = 5) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM tbl_notification WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
        $notifications = [];
        if ($stmt) {
            $stmt->bind_param("ii", $userId, $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $notifications[] = $row;
            }
            $stmt->close();
        }
        return $notifications;
    }

    /**
     * Mark a notification as read
     */
    public static function markAsRead($notificationId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE tbl_notification SET status = 'read' WHERE notification_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $notificationId);
            $success = $stmt->execute();
            $stmt->close();
            return $success;
        }
        return false;
    }

    /**
     * Mark all notifications as read for a user
     */
    public static function markAllAsRead($userId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE tbl_notification SET status = 'read' WHERE user_id = ? AND status = 'unread'");
        if ($stmt) {
            $stmt->bind_param("i", $userId);
            $success = $stmt->execute();
            $stmt->close();
            return $success;
        }
        return false;
    }

    /**
     * Count unread notifications
     */
    public static function countUnread($userId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM tbl_notification WHERE user_id = ? AND status = 'unread'");
        if ($stmt) {
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            return (int)$row['total'];
        }
        return 0;
    }

    /**
     * Notify all users with specific roles
     */
    public static function notifyRoles($roles, $type, $title, $message, $actionUrl = null) {
        $db = Database::getInstance()->getConnection();
        if (!is_array($roles)) $roles = [$roles];
        
        $placeholders = implode(',', array_fill(0, count($roles), '?'));
        $types = str_repeat('i', count($roles));
        
        $stmt = $db->prepare("SELECT user_id FROM tbl_user WHERE role IN ($placeholders)");
        if ($stmt) {
            $stmt->bind_param($types, ...$roles);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                self::create($row['user_id'], $type, $title, $message, $actionUrl);
            }
            $stmt->close();
        }
    }
}
