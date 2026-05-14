<?php
/**
 * KEBANA Digital Management System - Announcement Helper
 * File: app/Helpers/AnnouncementHelper.php
 */

namespace App\Helpers;

use App\Core\Database;
use App\Helpers\AuditHelper;

class AnnouncementHelper {

    /**
     * Get all announcements
     */
    public static function getAllAnnouncements($status = null) {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT a.*, u.username as creator_name, u.role as creator_role 
                FROM tbl_announcement a 
                LEFT JOIN tbl_user u ON a.created_by = u.user_id ";
        
        if ($status !== null) {
            $sql .= " WHERE a.status = '" . $db->real_escape_string($status) . "'";
        }
        
        $sql .= " ORDER BY a.created_at DESC";
        
        $result = $db->query($sql);
        $announcements = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $announcements[] = $row;
            }
        }
        return $announcements;
    }

    /**
     * Get a single announcement by ID
     */
    public static function getAnnouncementById($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT a.*, u.username as creator_name FROM tbl_announcement a LEFT JOIN tbl_user u ON a.created_by = u.user_id WHERE a.announcement_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
            $stmt->close();
            return $data;
        }
        return null;
    }

    /**
     * Add a new announcement
     */
    public static function addAnnouncement($data, $userId) {
        $db = Database::getInstance()->getConnection();
        
        $title = $data['title'] ?? '';
        $content = $data['content'] ?? '';
        $status = $data['status'] ?? 'Active';
        
        $stmt = $db->prepare("INSERT INTO tbl_announcement (title, content, status, created_by) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssi", $title, $content, $status, $userId);
            $success = $stmt->execute();
            $stmt->close();
            
            if ($success) {
                AuditHelper::log($userId, 'Hebahan baru ditambah: ' . $title, 'ANNOUNCEMENT');
            }
            return $success;
        }
        return false;
    }

    /**
     * Update an existing announcement
     */
    public static function updateAnnouncement($id, $data, $userId) {
        $db = Database::getInstance()->getConnection();
        
        $title = $data['title'] ?? '';
        $content = $data['content'] ?? '';
        $status = $data['status'] ?? 'Active';
        
        $stmt = $db->prepare("UPDATE tbl_announcement SET title = ?, content = ?, status = ? WHERE announcement_id = ?");
        if ($stmt) {
            $stmt->bind_param("sssi", $title, $content, $status, $id);
            $success = $stmt->execute();
            $stmt->close();
            
            if ($success) {
                AuditHelper::log($userId, 'Hebahan dikemaskini: ' . $title, 'ANNOUNCEMENT', 'ID: ' . $id);
            }
            return $success;
        }
        return false;
    }

    /**
     * Delete an announcement
     */
    public static function deleteAnnouncement($id, $userId) {
        $db = Database::getInstance()->getConnection();
        
        $ann = self::getAnnouncementById($id);
        if (!$ann) return false;

        $stmt = $db->prepare("DELETE FROM tbl_announcement WHERE announcement_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $success = $stmt->execute();
            $stmt->close();
            
            if ($success) {
                AuditHelper::log($userId, 'Hebahan dipadam: ' . $ann['title'], 'ANNOUNCEMENT', 'ID: ' . $id);
            }
            return $success;
        }
        return false;
    }
}
