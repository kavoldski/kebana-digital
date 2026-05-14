<?php
/**
 * KEBANA Digital Management System - Member Helper
 * File: app/Helpers/MembersHelper.php
 */

namespace App\Helpers;

use App\Core\Database;
use App\Helpers\NotificationHelper;

class MembersHelper {
    public static function getMemberCount($search = '') {
        $db = Database::getInstance()->getConnection();
        $where = "1=1";
        $params = [];
        $types = "";

        if ($search) {
            $where .= " AND (full_name LIKE ? OR ic_number LIKE ? OR member_id LIKE ?)";
            $searchTerm = "%$search%";
            $params = [$searchTerm, $searchTerm, $searchTerm];
            $types = "sss";
        }

        $sql = "SELECT COUNT(*) as total FROM tbl_member WHERE $where";
        $stmt = $db->prepare($sql);
        if ($search) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['total'] ?? 0;
    }

    public static function getMembersByStatus($status = 'Active') {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT * FROM tbl_member 
            WHERE status = ? 
            ORDER BY full_name ASC
        ");

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("s", $status);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $members = [];
        while ($row = $result->fetch_assoc()) {
            $members[] = $row;
        }
        $stmt->close();
        
        return $members;
    }

    public static function getMembersPaginated($page = 1, $per_page = 20, $search = '') {
        $db = Database::getInstance()->getConnection();
        $offset = ($page - 1) * $per_page;

        $where = "1=1";
        $params = [];
        $types = "";

        if ($search) {
            $where .= " AND (full_name LIKE ? OR ic_number LIKE ? OR member_id LIKE ?)";
            $searchTerm = "%$search%";
            $params = [$searchTerm, $searchTerm, $searchTerm];
            $types = "sss";
        }

        $sql = "SELECT * FROM tbl_member WHERE $where ORDER BY member_id DESC LIMIT ? OFFSET ?";
        $params[] = (int)$per_page;
        $params[] = (int)$offset;
        $types .= "ii";

        $stmt = $db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        $members = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $members[] = $row;
            }
        }

        $total = self::getMemberCount($search);

        return ['members' => $members, 'total' => $total];
    }

    public static function addMember($member_data) {
        $db = Database::getInstance()->getConnection();
        
        // Validate required fields
        if (empty($member_data['full_name']) || empty($member_data['ic_number']) || empty($member_data['village'])) {
            return ['status' => false, 'message' => 'Full name, IC number, and village are required'];
        }

        // Check if IC number already exists
        $ic_check = $db->prepare("SELECT member_id FROM tbl_member WHERE ic_number = ?");
        $ic_check->bind_param("s", $member_data['ic_number']);
        $ic_check->execute();
        $ic_result = $ic_check->get_result();
        
        if ($ic_result->num_rows > 0) {
            $ic_check->close();
            return ['status' => false, 'message' => 'IC number already exists'];
        }
        $ic_check->close();

        $stmt = $db->prepare("
            INSERT INTO tbl_member (full_name, gender, ic_number, village, phone_no, status) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $full_name = $member_data['full_name'];
        $gender = $member_data['gender'] ?? null;
        $ic_number = $member_data['ic_number'];
        $village = $member_data['village'];
        $phone_no = $member_data['phone_no'] ?? null;
        $status = $member_data['status'] ?? 'Active';

        $stmt->bind_param("ssssss", $full_name, $gender, $ic_number, $village, $phone_no, $status);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            return ['status' => false, 'message' => 'Database error: ' . $error];
        }

        $member_id = $stmt->insert_id;
        $stmt->close();

        NotificationHelper::notifyRoles([888, 4], 'member_added', 'Ahli Baru Didaftarkan', "Ahli baru \"$full_name\" ($ic_number) telah berjaya didaftarkan.", "members/view/$member_id");

        return [
            'status' => true,
            'message' => 'Member added successfully',
            'member_id' => $member_id
        ];
    }

    public static function deleteMember($member_id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM tbl_member WHERE member_id = ?");
        $stmt->bind_param("i", $member_id);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            return ['status' => false, 'message' => 'Database error: ' . $error];
        }

        $stmt->close();
        return ['status' => true, 'message' => 'Member profile deleted successfully'];
    }

    public static function getMemberById($member_id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM tbl_member WHERE member_id = ?");
        $stmt->bind_param("i", $member_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $member = $result->fetch_assoc();
        $stmt->close();
        return $member;
    }

    public static function extractICInfo($ic) {
        $ic = preg_replace('/[^0-9]/', '', $ic);
        if (strlen($ic) !== 12) return null;
        
        $year = substr($ic, 0, 2);
        $month = substr($ic, 2, 2);
        $day = substr($ic, 4, 2);
        $last_digit = substr($ic, -1);
        
        $current_year = date('Y');
        $birth_year = ($year > date('y')) ? "19$year" : "20$year";
        $age = $current_year - $birth_year;
        $gender = ($last_digit % 2 === 0) ? 'Wanita' : 'Lelaki';
        
        return ['age' => $age, 'gender' => $gender];
    }

    public static function getGenderLabel($member) {
        $gender = $member['gender'] ?? '';
        
        // Normalize "Perempuan" to "Wanita"
        if ($gender === 'Perempuan') $gender = 'Wanita';
        
        if (!empty($gender)) {
            return $gender;
        }
        
        // Fallback to IC detection
        $info = self::extractICInfo($member['ic_number'] ?? '');
        return $info ? $info['gender'] : 'TIDAK DINYATAKAN';
    }

    public static function getGrowthRate() {
        $db = Database::getInstance()->getConnection();
        
        // Count this month
        $this_month = $db->query("SELECT COUNT(*) as total FROM tbl_member WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())")->fetch_assoc()['total'];
        
        // Count last month
        $last_month = $db->query("SELECT COUNT(*) as total FROM tbl_member WHERE MONTH(created_at) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) AND YEAR(created_at) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)")->fetch_assoc()['total'];
        
        if ($last_month == 0) {
            return $this_month > 0 ? 100 : 0;
        }
        
        return round((($this_month - $last_month) / $last_month) * 100);
    }

    public static function getGrowthDataForLast6Months() {
        $db = Database::getInstance()->getConnection();
        $data = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month_label = date('M Y', strtotime("-$i months"));
            $month_sql = date('Y-m', strtotime("-$i months"));
            
            $res = $db->query("SELECT COUNT(*) as total FROM tbl_member WHERE created_at <= LAST_DAY('$month_sql-01')");
            $total = $res->fetch_assoc()['total'] ?? 0;
            
            $data[] = [
                'label' => $month_label,
                'total' => $total
            ];
        }
        
        return $data;
    }
}
