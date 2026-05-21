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
            $sql .= " AND (a.expires_at IS NULL OR a.expires_at > NOW())";
        } else {
            $sql .= " WHERE (a.expires_at IS NULL OR a.expires_at > NOW())";
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
     * Fetch the first (cover) image path for each announcement in a single query.
     * Returns an associative array: [announcement_id => image_path]
     */
    public static function getCoverImageMap(array $announcementIds): array {
        if (empty($announcementIds)) {
            return [];
        }
        $db = Database::getInstance()->getConnection();
        $placeholders = implode(',', array_fill(0, count($announcementIds), '?'));
        $types = str_repeat('i', count($announcementIds));

        $stmt = $db->prepare(
            "SELECT announcement_id, image_path
             FROM tbl_announcement_image
             WHERE announcement_id IN ($placeholders)
             ORDER BY image_id ASC"
        );
        $map = [];
        if ($stmt) {
            $stmt->bind_param($types, ...$announcementIds);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                // Only keep the FIRST image per announcement
                if (!isset($map[$row['announcement_id']])) {
                    $map[$row['announcement_id']] = $row['image_path'];
                }
            }
            $stmt->close();
        }
        return $map;
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
     * Add a new announcement (returns insert ID on success, false on failure)
     */
    public static function addAnnouncement($data, $userId) {
        $db = Database::getInstance()->getConnection();
        
        $title = $data['title'] ?? '';
        $content = $data['content'] ?? '';
        $status = $data['status'] ?? 'Active';
        $expires_at = !empty($data['expires_at']) ? $data['expires_at'] : null;
        
        $stmt = $db->prepare("INSERT INTO tbl_announcement (title, content, status, created_by, expires_at) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssis", $title, $content, $status, $userId, $expires_at);
            $success = $stmt->execute();
            
            if ($success) {
                $newId = $db->insert_id;
                AuditHelper::log($userId, 'Hebahan baru ditambah: ' . $title, 'ANNOUNCEMENT');
                $stmt->close();
                return $newId;
            }
            $stmt->close();
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
        $expires_at = !empty($data['expires_at']) ? $data['expires_at'] : null;
        
        $stmt = $db->prepare("UPDATE tbl_announcement SET title = ?, content = ?, status = ?, expires_at = ? WHERE announcement_id = ?");
        if ($stmt) {
            $stmt->bind_param("ssssi", $title, $content, $status, $expires_at, $id);
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
     * Delete an announcement and its physical image attachments.
     * File-system failures during image cleanup are non-fatal so the
     * DB record is always removed on success.
     */
    public static function deleteAnnouncement($id, $userId) {
        $db = Database::getInstance()->getConnection();

        $ann = self::getAnnouncementById($id);
        if (!$ann) return false;

        // Best-effort deletion of physical image files — errors are swallowed
        try {
            $images = self::getAnnouncementImages($id);
            foreach ($images as $img) {
                $fullPath = APP_ROOT . '/' . $img['image_path'];
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }
            }
        } catch (\Throwable $e) {
            // Non-fatal: continue to DB delete even if file cleanup fails
        }

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

    /**
     * Fetch all image attachments associated with an announcement
     */
    public static function getAnnouncementImages($announcementId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM tbl_announcement_image WHERE announcement_id = ? ORDER BY image_id ASC");
        $images = [];
        if ($stmt) {
            $stmt->bind_param("i", $announcementId);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $images[] = $row;
            }
            $stmt->close();
        }
        return $images;
    }

    /**
     * Helper to compress images based on type before saving (GD backend).
     * Falls back to move_uploaded_file() if GD is unavailable or the
     * specific MIME handler is not compiled in (e.g. WebP on some hosts).
     */
    public static function compressAndSaveImage($source, $destination, $quality = 80) {
        $info = @getimagesize($source);
        if ($info === false) {
            // Cannot read image info — fall back to raw move
            return move_uploaded_file($source, $destination);
        }

        $mime = $info['mime'];
        $image = null;

        try {
            switch ($mime) {
                case 'image/jpeg':
                case 'image/jpg':
                    if (function_exists('imagecreatefromjpeg')) {
                        $image = @imagecreatefromjpeg($source);
                        if ($image) {
                            $result = imagejpeg($image, $destination, $quality);
                            imagedestroy($image);
                            return $result;
                        }
                    }
                    break;

                case 'image/png':
                    if (function_exists('imagecreatefrompng')) {
                        $image = @imagecreatefrompng($source);
                        if ($image) {
                            imagealphablending($image, false);
                            imagesavealpha($image, true);
                            $result = imagepng($image, $destination, 7);
                            imagedestroy($image);
                            return $result;
                        }
                    }
                    break;

                case 'image/gif':
                    if (function_exists('imagecreatefromgif')) {
                        $image = @imagecreatefromgif($source);
                        if ($image) {
                            $result = imagegif($image, $destination);
                            imagedestroy($image);
                            return $result;
                        }
                    }
                    break;

                case 'image/webp':
                    if (function_exists('imagecreatefromwebp')) {
                        $image = @imagecreatefromwebp($source);
                        if ($image) {
                            $result = imagewebp($image, $destination, $quality);
                            imagedestroy($image);
                            return $result;
                        }
                    }
                    break;
            }
        } catch (\Throwable $e) {
            // GD threw — fall through to move_uploaded_file()
            if ($image) {
                @imagedestroy($image);
            }
        }

        // Fallback: raw copy without compression
        return move_uploaded_file($source, $destination);
    }

    /**
     * Upload up to 5 compressed images and save their paths in the DB.
     * Individual image failures are skipped (not fatal) unless the
     * upload directory is missing/unwritable.
     *
     * @return true on full success, string error message on failure.
     */
    public static function uploadAnnouncementImages($announcementId, $files) {
        $db = Database::getInstance()->getConnection();
        $uploadDir = APP_ROOT . '/uploads/announcements/';

        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        // Guard: if directory still doesn't exist after mkdir(), bail early
        if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
            return 'Direktori muat naik tidak wujud atau tidak boleh ditulis.';
        }

        // Check current image count to prevent exceeding 5
        $currentImages = self::getAnnouncementImages($announcementId);
        $currentCount  = count($currentImages);

        $fileCount = is_array($files['name']) ? count($files['name']) : 0;
        if ($currentCount + $fileCount > 5) {
            return 'Jumlah gambar melebihi had maksimum 5.';
        }

        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        for ($i = 0; $i < $fileCount; $i++) {
            // Skip files that did not upload cleanly
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                // Skip disallowed extension instead of aborting the whole batch
                continue;
            }

            $newName    = 'ann_' . $announcementId . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
            $targetPath = $uploadDir . $newName;

            try {
                $saved = self::compressAndSaveImage($files['tmp_name'][$i], $targetPath);
            } catch (\Throwable $e) {
                $saved = false;
            }

            if ($saved) {
                $dbPath = 'uploads/announcements/' . $newName;
                $stmt = $db->prepare("INSERT INTO tbl_announcement_image (announcement_id, image_path) VALUES (?, ?)");
                if ($stmt) {
                    $stmt->bind_param("is", $announcementId, $dbPath);
                    $stmt->execute();
                    $stmt->close();
                }
            }
            // If a single image fails to save, log and continue — don't abort the batch
        }

        return true;
    }

    /**
     * Delete an individual announcement image physically and logically
     */
    public static function deleteAnnouncementImage($imageId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM tbl_announcement_image WHERE image_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $imageId);
            $stmt->execute();
            $img = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if ($img) {
                $fullPath = APP_ROOT . '/' . $img['image_path'];
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }
                
                $delStmt = $db->prepare("DELETE FROM tbl_announcement_image WHERE image_id = ?");
                if ($delStmt) {
                    $delStmt->bind_param("i", $imageId);
                    $success = $delStmt->execute();
                    $delStmt->close();
                    return $success;
                }
            }
        }
        return false;
    }
}
