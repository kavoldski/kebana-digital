<?php
/**
 * KEBANA Digital Management System - Chat Helper
 * File: app/Helpers/ChatHelper.php
 */

namespace App\Helpers;

use App\Core\Database;
use App\Helpers\AuditHelper;

class ChatHelper {
    private static $key = 'KEBANA_SECURE_CHAT_KEY_2024_@!'; // The Master Key

    /**
     * Encrypt message
     */
    private static function encrypt($message) {
        if (empty($message)) return '';
        $method = "AES-256-CBC";
        $iv_length = openssl_cipher_iv_length($method);
        $iv = openssl_random_pseudo_bytes($iv_length);
        $encrypted = openssl_encrypt($message, $method, self::$key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt message
     */
    private static function decrypt($encryptedMessage) {
        if (empty($encryptedMessage)) return '';
        $data = base64_decode($encryptedMessage);
        $method = "AES-256-CBC";
        $iv_length = openssl_cipher_iv_length($method);
        if (strlen($data) <= $iv_length) return $encryptedMessage; // Fallback for old plain text
        $iv = substr($data, 0, $iv_length);
        $encrypted = substr($data, $iv_length);
        $decrypted = openssl_decrypt($encrypted, $method, self::$key, 0, $iv);
        return $decrypted === false ? $encryptedMessage : $decrypted;
    }

    /**
     * Send a message
     */
    public static function sendMessage($senderId, $receiverId, $message) {
        $db = Database::getInstance()->getConnection();
        $encrypted = self::encrypt($message);
        $stmt = $db->prepare("INSERT INTO tbl_chat (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("iis", $senderId, $receiverId, $encrypted);
            $success = $stmt->execute();
            $stmt->close();
            
            // Audit Log
            AuditHelper::log($senderId, 'Mesej dihantar', 'CHAT', 'Penerima ID: ' . $receiverId);
            
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
                $row['message'] = self::decrypt($row['message']);
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
    public static function getChatList($userId, $onlyRecent = false) {
        $db = Database::getInstance()->getConnection();
        $having = $onlyRecent ? "HAVING last_message_raw IS NOT NULL OR unread_count > 0" : "";
        $sql = "
            SELECT 
                u.user_id, 
                u.username, 
                u.role,
                (SELECT message FROM tbl_chat 
                 WHERE (sender_id = u.user_id AND receiver_id = $userId) 
                    OR (sender_id = $userId AND receiver_id = u.user_id)
                 ORDER BY created_at DESC LIMIT 1) as last_message_raw,
                (SELECT created_at FROM tbl_chat 
                 WHERE (sender_id = u.user_id AND receiver_id = $userId) 
                    OR (sender_id = $userId AND receiver_id = u.user_id)
                 ORDER BY created_at DESC LIMIT 1) as last_time,
                (SELECT COUNT(*) FROM tbl_chat 
                 WHERE sender_id = u.user_id AND receiver_id = $userId AND is_read = 0) as unread_count
            FROM tbl_user u
            WHERE u.user_id != $userId
            $having
            ORDER BY last_time DESC, u.username ASC
        ";
        $result = $db->query($sql);
        $list = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $row['last_message'] = $row['last_message_raw'] ? self::decrypt($row['last_message_raw']) : null;
                $list[] = $row;
            }
        }
        return $list;
    }

    /**
     * Get all possible chat users
     */
    public static function getAllUsers($excludeUserId) {
        $db = Database::getInstance()->getConnection();
        $result = $db->query("SELECT user_id, username, role FROM tbl_user WHERE user_id != $excludeUserId ORDER BY username ASC");
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
     * Get conversation state (count and last message ID)
     */
    public static function getConversationState($user1, $user2) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT COUNT(*) as msg_count, MAX(chat_id) as last_id 
            FROM tbl_chat 
            WHERE (sender_id = ? AND receiver_id = ?) 
               OR (sender_id = ? AND receiver_id = ?)
        ");
        if ($stmt) {
            $stmt->bind_param("iiii", $user1, $user2, $user2, $user1);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
            $stmt->close();
            return $data;
        }
        return ['msg_count' => 0, 'last_id' => 0];
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
