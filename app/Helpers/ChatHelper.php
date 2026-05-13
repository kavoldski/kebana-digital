<?php
/**
 * KEBANA Management System - Chat Helper
 * File: app/Helpers/ChatHelper.php
 */

namespace App\Helpers;

use App\Core\Database;

class ChatHelper {
    /**
     * Send a message
     */
    public static function sendMessage($senderId, $receiverId, $message) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("INSERT INTO tbl_chat (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("iis", $senderId, $receiverId, $message);
            $success = $stmt->execute();
            $stmt->close();
            return $success;
        }
        return false;
    }

    /**
     * Get conversation between two users
     */
    public static function getConversation($user1, $user2, $limit = 50) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT * FROM tbl_chat 
            WHERE (sender_id = ? AND receiver_id = ?) 
               OR (sender_id = ? AND receiver_id = ?)
            ORDER BY created_at ASC
            LIMIT ?
        ");
        $messages = [];
        if ($stmt) {
            $stmt->bind_param("iiiii", $user1, $user2, $user2, $user1, $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $messages[] = $row;
            }
            $stmt->close();
            
            // Mark as read for the current user
            self::markAsRead($user2, $user1);
        }
        return $messages;
    }

    /**
     * Mark messages as read
     */
    public static function markAsRead($senderId, $receiverId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE tbl_chat SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0");
        if ($stmt) {
            $stmt->bind_param("ii", $senderId, $receiverId);
            $stmt->execute();
            $stmt->close();
        }
    }

    /**
     * Get list of users with last message
     */
    public static function getChatList($userId) {
        $db = Database::getInstance()->getConnection();
        $sql = "
            SELECT 
                u.user_id, 
                u.username, 
                u.role,
                (SELECT message FROM tbl_chat 
                 WHERE (sender_id = u.user_id AND receiver_id = $userId) 
                    OR (sender_id = $userId AND receiver_id = u.user_id)
                 ORDER BY created_at DESC LIMIT 1) as last_message,
                (SELECT created_at FROM tbl_chat 
                 WHERE (sender_id = u.user_id AND receiver_id = $userId) 
                    OR (sender_id = $userId AND receiver_id = u.user_id)
                 ORDER BY created_at DESC LIMIT 1) as last_time,
                (SELECT COUNT(*) FROM tbl_chat 
                 WHERE sender_id = u.user_id AND receiver_id = $userId AND is_read = 0) as unread_count
            FROM tbl_user u
            WHERE u.user_id != $userId
            ORDER BY last_time DESC, u.username ASC
        ";
        $result = $db->query($sql);
        $list = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $list[] = $row;
            }
        }
        return $list;
    }

    /**
     * Clear conversation between two users
     */
    public static function clearChat($user1, $user2) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM tbl_chat WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)");
        if ($stmt) {
            $stmt->bind_param("iiii", $user1, $user2, $user2, $user1);
            $success = $stmt->execute();
            $stmt->close();
            return $success;
        }
        return false;
    }

    /**
     * Get total unread count for a user
     */
    public static function getTotalUnreadCount($userId) {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT COUNT(*) as total FROM tbl_chat WHERE receiver_id = $userId AND is_read = 0";
        $result = $db->query($sql);
        return $result ? (int)$result->fetch_assoc()['total'] : 0;
    }
}
