<?php
/**
 * KEBANA Digital Management System - Cawangan Helper
 * File: app/Helpers/CawanganHelper.php
 */

namespace App\Helpers;

use App\Core\Database;
use App\Helpers\AuditHelper;

class CawanganHelper {
    /**
     * Get all cawangan
     * @param bool $activeOnly Only return active cawangans
     * @return array
     */
    public static function getAllCawangan($activeOnly = false) {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT * FROM tbl_cawangan";
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY cawangan_name ASC";
        
        $result = $db->query($sql);
        $cawangan = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $cawangan[] = $row;
            }
        }
        return $cawangan;
    }

    /**
     * Get cawangan by ID
     * @param int $id
     * @return array|false
     */
    public static function getCawanganById($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM tbl_cawangan WHERE cawangan_id = ?");
        if (!$stmt) return false;
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        
        return $data;
    }

    /**
     * Add new cawangan
     * @param array $data
     * @return array
     */
    public static function addCawangan($data) {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("INSERT INTO tbl_cawangan (cawangan_name, cawangan_code, is_active) VALUES (?, ?, ?)");
        if (!$stmt) return ['status' => false, 'message' => 'Ralat sistem: ' . $db->error];
        
        $name = trim($data['cawangan_name']);
        $code = strtoupper(trim($data['cawangan_code']));
        $active = isset($data['is_active']) ? (int)$data['is_active'] : 1;
        
        $stmt->bind_param("ssi", $name, $code, $active);
        
        if ($stmt->execute()) {
            $newId = $db->insert_id;
            $stmt->close();
            
            // Audit Log
            $userId = $_SESSION['user_id'] ?? 0;
            AuditHelper::log($userId, 'Cawangan Didaftarkan', 'CAWANGAN', "Nama: $name, Kod: $code");
            
            return ['status' => true, 'message' => 'Cawangan berjaya didaftarkan.'];
        } else {
            $error = $stmt->error;
            $stmt->close();
            if (strpos($error, 'Duplicate entry') !== false) {
                return ['status' => false, 'message' => 'Kod cawangan sudah wujud.'];
            }
            return ['status' => false, 'message' => 'Gagal mendaftar cawangan: ' . $error];
        }
    }

    /**
     * Update cawangan
     * @param int $id
     * @param array $data
     * @return array
     */
        // Check if anything changed
        $current = self::getCawanganById($id);
        if ($current && $current['cawangan_name'] === $name && $current['cawangan_code'] === $code && (int)$current['is_active'] === $active) {
            return ['status' => true, 'message' => 'Tiada perubahan dikesan.'];
        }

        $stmt->bind_param("ssii", $name, $code, $active, $id);
        
        if ($stmt->execute()) {
            $stmt->close();
            
            // Audit Log
            $userId = $_SESSION['user_id'] ?? 0;
            $details = "ID: $id";
            if ($current) {
                if ($current['cawangan_name'] !== $name) $details .= " | Nama: " . $current['cawangan_name'] . " -> $name";
                if ($current['cawangan_code'] !== $code) $details .= " | Kod: " . $current['cawangan_code'] . " -> $code";
                if ((int)$current['is_active'] !== $active) $details .= " | Status: " . ($current['is_active'] ? 'Aktif' : 'Tidak Aktif') . " -> " . ($active ? 'Aktif' : 'Tidak Aktif');
            }
            AuditHelper::log($userId, 'Cawangan Dikemaskini', 'CAWANGAN', $details);
            
            return ['status' => true, 'message' => 'Maklumat cawangan berjaya dikemaskini.'];
        } else {
            $error = $stmt->error;
            $stmt->close();
            if (strpos($error, 'Duplicate entry') !== false) {
                return ['status' => false, 'message' => 'Kod cawangan sudah wujud.'];
            }
            return ['status' => false, 'message' => 'Gagal mengemaskini cawangan: ' . $error];
        }
    }

    /**
     * Toggle cawangan status (Flag disable)
     * @param int $id
     * @return array
     */
    public static function toggleStatus($id) {
        $db = Database::getInstance()->getConnection();
        
        $cawangan = self::getCawanganById($id);
        if (!$cawangan) return ['status' => false, 'message' => 'Cawangan tidak ditemui.'];
        
        $new_status = $cawangan['is_active'] ? 0 : 1;
        
        $stmt = $db->prepare("UPDATE tbl_cawangan SET is_active = ? WHERE cawangan_id = ?");
        $stmt->bind_param("ii", $new_status, $id);
        
        if ($stmt->execute()) {
            $stmt->close();
            $status_text = $new_status ? 'diaktifkan' : 'dinyahaktifkan';
            
            // Audit Log
            $userId = $_SESSION['user_id'] ?? 0;
            AuditHelper::log($userId, "Status Cawangan Tukar ($status_text)", 'CAWANGAN', "Cawangan: " . $cawangan['cawangan_name'] . " (ID: $id)");
            
            return ['status' => true, 'message' => "Cawangan berjaya $status_text."];
        }
        
        return ['status' => false, 'message' => 'Gagal menukar status cawangan.'];
    }
}
