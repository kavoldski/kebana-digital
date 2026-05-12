<?php
/**
 * KEBANA Management System - Cawangan Helper
 * File: app/Helpers/CawanganHelper.php
 */

namespace App\Helpers;

use App\Core\Database;

class CawanganHelper {
    public static function getAllCawangan() {
        $db = Database::getInstance()->getConnection();
        $result = $db->query("SELECT * FROM tbl_cawangan ORDER BY cawangan_name ASC");
        $cawangan = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $cawangan[] = $row;
            }
        }
        return $cawangan;
    }

    public static function getCawanganById($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM tbl_cawangan WHERE cawangan_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
