<?php
/**
 * KEBANA Management System - Member Helper Functions
 * File: includes/members_helper.php
 * 
 * Helper functions for member management operations with new schema
 */

/**
 * Add a new member to tbl_member
 * 
 * @param mysqli $conn Database connection
 * @param array $member_data Member details array
 * @return array Result array with status and message
 */
function addMember($conn, $member_data) {
    // Validate required fields
    if (empty($member_data['full_name']) || empty($member_data['ic_number']) || empty($member_data['village'])) {
        return ['status' => false, 'message' => 'Full name, IC number, and village are required'];
    }

    // Check if IC number already exists
    $ic_check = $conn->prepare("SELECT member_id FROM tbl_member WHERE ic_number = ?");
    if (!$ic_check) {
        return ['status' => false, 'message' => 'Database error: ' . $conn->error];
    }

    $ic_check->bind_param("s", $member_data['ic_number']);
    $ic_check->execute();
    $ic_result = $ic_check->get_result();
    
    if ($ic_result->num_rows > 0) {
        $ic_check->close();
        return ['status' => false, 'message' => 'IC number already exists'];
    }
    $ic_check->close();

    // Insert member record
    $stmt = $conn->prepare("
        INSERT INTO tbl_member (full_name, ic_number, village, phone_no, status) 
        VALUES (?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        return ['status' => false, 'message' => 'Database error: ' . $conn->error];
    }

    $full_name = $member_data['full_name'];
    $ic_number = $member_data['ic_number'];
    $village = $member_data['village'];
    $phone_no = $member_data['phone_no'] ?? null;
    $status = $member_data['status'] ?? 'Active';

    $stmt->bind_param("sssss", $full_name, $ic_number, $village, $phone_no, $status);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return ['status' => false, 'message' => 'Database error: ' . $error];
    }

    $member_id = $stmt->insert_id;
    $stmt->close();

    return [
        'status' => true,
        'message' => 'Member added successfully',
        'member_id' => $member_id
    ];
}

/**
 * Get member profile details by member ID
 * 
 * @param mysqli $conn Database connection
 * @param int $member_id Member ID
 * @return array Member data or empty array if not found
 */
function getMemberById($conn, $member_id) {
    $stmt = $conn->prepare("SELECT * FROM tbl_member WHERE member_id = ?");
    if (!$stmt) {
        return [];
    }

    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $member = $result->num_rows > 0 ? $result->fetch_assoc() : [];
    $stmt->close();
    
    return $member;
}

/**
 * Get all members with pagination
 *
 * @param mysqli $conn Database connection
 * @param int $page Page number (1-indexed)
 * @param int $per_page Records per page
 * @return array Array with 'members' array and 'total' count
 */
function getAllMembers($conn, $page = 1, $per_page = 20) {
    $offset = ($page - 1) * $per_page;

    // Use direct integer values for LIMIT/OFFSET to avoid MySQLi prepared-statement binding issues
    // Order by member_id DESC as a fallback in case created_at column is missing from the actual DB
    $sql = "SELECT * FROM tbl_member ORDER BY member_id DESC LIMIT " . (int)$per_page . " OFFSET " . (int)$offset;
    $result = $conn->query($sql);

    $members = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $members[] = $row;
        }
    }

    // Get total count
    $count_result = $conn->query("SELECT COUNT(*) as total FROM tbl_member");
    $total = $count_result ? $count_result->fetch_assoc()['total'] : 0;

    return ['members' => $members, 'total' => $total];
}

/**
 * Get members by status
 * 
 * @param mysqli $conn Database connection
 * @param string $status Member status
 * @return array Array of members with given status
 */
function getMembersByStatus($conn, $status = 'Active') {
    $stmt = $conn->prepare("
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

/**
 * Update member profile
 * 
 * @param mysqli $conn Database connection
 * @param int $member_id Member ID to update
 * @param array $member_data Updated member details
 * @return array Result array with status and message
 */
function updateMember($conn, $member_id, $member_data) {
    if (empty($member_id)) {
        return ['status' => false, 'message' => 'Invalid member ID'];
    }

    $updates = [];
    $types = "";
    $values = [];

    if (isset($member_data['full_name'])) {
        $updates[] = "full_name = ?";
        $types .= "s";
        $values[] = $member_data['full_name'];
    }
    if (isset($member_data['ic_number'])) {
        $updates[] = "ic_number = ?";
        $types .= "s";
        $values[] = $member_data['ic_number'];
    }
    if (isset($member_data['village'])) {
        $updates[] = "village = ?";
        $types .= "s";
        $values[] = $member_data['village'];
    }
    if (isset($member_data['phone_no'])) {
        $updates[] = "phone_no = ?";
        $types .= "s";
        $values[] = $member_data['phone_no'];
    }
    if (isset($member_data['status'])) {
        $updates[] = "status = ?";
        $types .= "s";
        $values[] = $member_data['status'];
    }

    if (empty($updates)) {
        return ['status' => false, 'message' => 'No data to update'];
    }

    $updates[] = "updated_at = NOW()";
    $types .= "i";
    $values[] = $member_id;

    $sql = "UPDATE tbl_member SET " . implode(', ', $updates) . " WHERE member_id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return ['status' => false, 'message' => 'Database error: ' . $conn->error];
    }

    $stmt->bind_param($types, ...$values);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return ['status' => false, 'message' => 'Database error: ' . $error];
    }

    $stmt->close();
    return ['status' => true, 'message' => 'Member profile updated successfully'];
}

/**
 * Delete member profile
 * 
 * @param mysqli $conn Database connection
 * @param int $member_id Member ID to delete
 * @return array Result array with status and message
 */
function deleteMember($conn, $member_id) {
    if (empty($member_id)) {
        return ['status' => false, 'message' => 'Invalid member ID'];
    }

    $stmt = $conn->prepare("DELETE FROM tbl_member WHERE member_id = ?");

    if (!$stmt) {
        return ['status' => false, 'message' => 'Database error: ' . $conn->error];
    }

    $stmt->bind_param("i", $member_id);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return ['status' => false, 'message' => 'Database error: ' . $error];
    }

    $stmt->close();
    return ['status' => true, 'message' => 'Member profile deleted successfully'];
}

/**
 * Search members by name or IC number
 * 
 * @param mysqli $conn Database connection
 * @param string $search_term Search keyword
 * @return array Array of members matching search criteria
 */
function searchMembers($conn, $search_term) {
    $search = "%$search_term%";
    $stmt = $conn->prepare("
        SELECT * FROM tbl_member 
        WHERE full_name LIKE ? OR ic_number LIKE ? 
        ORDER BY full_name ASC
    ");

    if (!$stmt) {
        return [];
    }

    $stmt->bind_param("ss", $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $members = [];
    while ($row = $result->fetch_assoc()) {
        $members[] = $row;
    }
    $stmt->close();
    
    return $members;
}

/**
 * Get member count
 * 
 * @param mysqli $conn Database connection
 * @return int Total number of members
 */
function getMemberCount($conn) {
    $result = $conn->query("SELECT COUNT(*) as total FROM tbl_member");
    return $result->fetch_assoc()['total'] ?? 0;
}

?>
