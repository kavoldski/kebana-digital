<?php
/**
 * KEBANA Digital Management System - Documents Helper
 * File: app/Helpers/DocumentsHelper.php
 */

namespace App\Helpers;

use App\Core\Database;
use App\Helpers\NotificationHelper;

class DocumentsHelper {
    public static function getAllDocuments($filters = [], $limit = 20, $offset = 0) {
        $db = Database::getInstance()->getConnection();
        $where = "1=1";
        $params = [];
        $types = "";

        if (!empty($filters['tag'])) {
            $where .= " AND doc_tags LIKE ?";
            $params[] = "%" . $filters['tag'] . "%";
            $types .= "s";
        }
        if (!empty($filters['search'])) {
            $where .= " AND (doc_name LIKE ? OR doc_tags LIKE ?)";
            $params[] = "%" . $filters['search'] . "%";
            $params[] = "%" . $filters['search'] . "%";
            $types .= "ss";
        }

        $sql = "SELECT d.*, e.event_title, u.username as uploader_name 
                FROM tbl_document d
                LEFT JOIN tbl_event e ON d.event_id = e.event_id
                LEFT JOIN tbl_user u ON d.uploaded_by = u.user_id
                WHERE $where
                ORDER BY d.uploaded_at DESC
                LIMIT ? OFFSET ?";
        
        $params[] = (int)$limit;
        $params[] = (int)$offset;
        $types .= "ii";

        $stmt = $db->prepare($sql);
        $docs = [];
        if ($stmt) {
            if (!empty($params)) $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $docs[] = $row;
            }
            $stmt->close();
        }
        return $docs;
    }

    public static function countAllDocuments($filters = []) {
        $db = Database::getInstance()->getConnection();
        $where = "1=1";
        $params = [];
        $types = "";

        if (!empty($filters['tag'])) {
            $where .= " AND doc_tags LIKE ?";
            $params[] = "%" . $filters['tag'] . "%";
            $types .= "s";
        }
        if (!empty($filters['search'])) {
            $where .= " AND (doc_name LIKE ? OR doc_tags LIKE ?)";
            $params[] = "%" . $filters['search'] . "%";
            $params[] = "%" . $filters['search'] . "%";
            $types .= "ss";
        }

        $sql = "SELECT COUNT(*) as total FROM tbl_document d WHERE $where";
        $stmt = $db->prepare($sql);
        if ($stmt) {
            if (!empty($params)) $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            return (int)($row['total'] ?? 0);
        }
        return 0;
    }

    public static function uploadDocument($file, $userId, $eventId = null, $tags = '') {
        $db = Database::getInstance()->getConnection();
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'docx', 'xlsx'];
        if (!in_array($ext, $allowed)) return false;

        $uploadDir = APP_ROOT . '/uploads/archive/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $newName = 'doc_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
        $target = $uploadDir . $newName;
        $size = $file['size'];

        if (move_uploaded_file($file['tmp_name'], $target)) {
            $path = 'uploads/archive/' . $newName;
            $name = basename($file['name']);
            
            $stmt = $db->prepare("INSERT INTO tbl_document (event_id, doc_name, file_path, doc_tags, uploaded_by, doc_size) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("isssii", $eventId, $name, $path, $tags, $userId, $size);
                $success = $stmt->execute();
                if (!$success) {
                    error_log("Database execution failed: " . $stmt->error);
                }
                $stmt->close();
                
                if ($success) {
                    NotificationHelper::notifyRoles([888, 4], 'document_uploaded', 'Dokumen Baru Dimuatnaik', "Fail \"$name\" telah dimuatnaik ke pusat arkib.", "documents");
                }

                return $success;
            } else {
                error_log("Database prepare failed: " . $db->error);
            }
        } else {
            error_log("move_uploaded_file failed for: " . $target);
        }
        return false;
    }

    public static function deleteDocument($docId) {
        $db = Database::getInstance()->getConnection();
        
        // Get file path first
        $stmt = $db->prepare("SELECT file_path FROM tbl_document WHERE doc_id = ?");
        $stmt->bind_param("i", $docId);
        $stmt->execute();
        $result = $stmt->get_result();
        $doc = $result->fetch_assoc();
        $stmt->close();

        if ($doc) {
            $fullPath = APP_ROOT . '/' . $doc['file_path'];
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
            
            $stmt = $db->prepare("DELETE FROM tbl_document WHERE doc_id = ?");
            $stmt->bind_param("i", $docId);
            $success = $stmt->execute();
            $stmt->close();
            return $success;
        }
        return false;
    }

    public static function getUniqueTags() {
        $db = Database::getInstance()->getConnection();
        $result = $db->query("SELECT DISTINCT doc_tags FROM tbl_document WHERE doc_tags IS NOT NULL AND doc_tags != ''");
        $all_tags = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $tags = explode(',', $row['doc_tags']);
                foreach ($tags as $tag) {
                    $trimmed = trim($tag);
                    if ($trimmed && !in_array($trimmed, $all_tags)) {
                        $all_tags[] = $trimmed;
                    }
                }
            }
        }
        return $all_tags;
    }

    public static function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
