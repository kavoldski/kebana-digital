<?php
/**
 * KEBANA Digital Management System - User Helper
 * File: app/Helpers/UserHelper.php
 */

namespace App\Helpers;

use App\Core\Database;

class UserHelper {
    /**
     * Get all users
     * @return array
     */
    public static function getAllUsers() {
        $db = Database::getInstance()->getConnection();
        $result = $db->query("
            SELECT u.*, c.cawangan_name 
            FROM tbl_user u 
            LEFT JOIN tbl_cawangan c ON u.cawangan_id = c.cawangan_id 
            ORDER BY u.role DESC, u.username ASC
        ");
        
        $users = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }
        return $users;
    }

    /**
     * Get user by ID
     * @param int $user_id
     * @return array|false
     */
    public static function getUserById($user_id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM tbl_user WHERE user_id = ?");
        if (!$stmt) return false;
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        return $user;
    }

    /**
     * Update user details
     * @param int $user_id
     * @param array $data
     * @return array
     */
    public static function updateUser($user_id, $data) {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            UPDATE tbl_user 
            SET username = ?, full_name = ?, email = ?, role = ?, cawangan_id = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE user_id = ?
        ");
        
        if (!$stmt) {
            return ['status' => false, 'message' => 'Ralat sistem (Prepare failed): ' . $db->error];
        }

        $username = $data['username'];
        $full_name = $data['full_name'];
        $email = $data['email'];
        $role = $data['role'];
        $cawangan_id = $data['cawangan_id'] ?: null;

        $stmt->bind_param("sssiii", $username, $full_name, $email, $role, $cawangan_id, $user_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['status' => true, 'message' => 'Maklumat pengguna berjaya dikemaskini.'];
        } else {
            $error = $stmt->error;
            $stmt->close();
            if (strpos($error, 'Duplicate entry') !== false) {
                return ['status' => false, 'message' => 'Username atau Email sudah wujud.'];
            }
            return ['status' => false, 'message' => 'Ralat sistem: ' . $error];
        }
    }

    /**
     * Reset user password
     * @param int $user_id
     * @param string $new_password
     * @return array
     */
    public static function resetPassword($user_id, $new_password) {
        $db = Database::getInstance()->getConnection();
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("UPDATE tbl_user SET password_hash = ? WHERE user_id = ?");
        if (!$stmt) return ['status' => false, 'message' => 'Ralat sistem: ' . $db->error];
        
        $stmt->bind_param("si", $password_hash, $user_id);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return ['status' => true, 'message' => 'Kata laluan berjaya dikeset semula.'];
        }
        return ['status' => false, 'message' => 'Gagal mengeset semula kata laluan.'];
    }

    /**
     * Delete user
     * @param int $user_id
     * @return array
     */
    public static function deleteUser($user_id) {
        // Prevent deleting self
        if ($user_id == $_SESSION['user_id']) {
            return ['status' => false, 'message' => 'Anda tidak boleh memadam akaun sendiri.'];
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM tbl_user WHERE user_id = ?");
        if (!$stmt) return ['status' => false, 'message' => 'Ralat sistem: ' . $db->error];
        
        $stmt->bind_param("i", $user_id);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return ['status' => true, 'message' => 'Pengguna berjaya dipadam.'];
        }
        return ['status' => false, 'message' => 'Gagal memadam pengguna.'];
    }
}
