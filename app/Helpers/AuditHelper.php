<?php
/**
 * KEBANA Digital Management System - Audit Helper
 * File: app/Helpers/AuditHelper.php
 */

namespace App\Helpers;

use App\Core\Database;

class AuditHelper {
    /**
     * Record a new log entry
     */
    public static function log($userId, $action, $module, $details = null) {
        $db = Database::getInstance()->getConnection();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $stmt = $db->prepare("INSERT INTO tbl_audit_log (user_id, action, module, details, ip_address) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("issss", $userId, $action, $module, $details, $ip);
            $success = $stmt->execute();
            $stmt->close();
            return $success;
        }
        return false;
    }

    /**
     * Get recent logs for dashboard (simple format)
     */
    public static function getRecentLogs($limit = 5) {
        $db = Database::getInstance()->getConnection();
        $sql = "
            SELECT l.*, u.username 
            FROM tbl_audit_log l 
            LEFT JOIN tbl_user u ON l.user_id = u.user_id 
            ORDER BY l.created_at DESC 
            LIMIT $limit
        ";
        $result = $db->query($sql);
        $logs = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $logs[] = $row;
            }
        }
        return $logs;
    }

    /**
     * Get all logs with pagination and filters
     */
    public static function getLogs($page = 1, $limit = 20, $moduleFilter = null) {
        $db = Database::getInstance()->getConnection();
        $offset = ($page - 1) * $limit;
        
        $where = "";
        if ($moduleFilter) {
            $where = " WHERE module = '" . $db->real_escape_string($moduleFilter) . "'";
        }

        $sql = "
            SELECT l.*, u.username, u.role
            FROM tbl_audit_log l 
            LEFT JOIN tbl_user u ON l.user_id = u.user_id 
            $where
            ORDER BY l.created_at DESC 
            LIMIT $offset, $limit
        ";
        
        $result = $db->query($sql);
        $logs = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $logs[] = $row;
            }
        }
        return $logs;
    }

    /**
     * Get total log count for pagination
     */
    public static function getTotalCount($moduleFilter = null) {
        $db = Database::getInstance()->getConnection();
        $where = "";
        if ($moduleFilter) {
            $where = " WHERE module = '" . $db->real_escape_string($moduleFilter) . "'";
        }
        $sql = "SELECT COUNT(*) as total FROM tbl_audit_log $where";
        $result = $db->query($sql);
        return $result ? (int)$result->fetch_assoc()['total'] : 0;
    }
}
