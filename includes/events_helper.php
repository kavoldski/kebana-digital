<?php
/**
 * KEBANA Management System - Event Helper Functions
 * File: includes/events_helper.php
 *
 * Helper functions for event management and attendance tracking
 */

/**
 * Get all events with creator username
 *
 * @param mysqli $conn Database connection
 * @return array Array of events
 */
function getAllEvents($conn, $viewMode = 'all', $userId = null, $cawanganId = null) {
    $events = [];

    if ($viewMode === 'all') {
        $result = $conn->query("
            SELECT e.*, u.username as creator_name,
                   c.cawangan_name,
                   COALESCE(e.event_level, 'MASTER') as event_level,
                   e.parent_event_id
            FROM tbl_event e
            LEFT JOIN tbl_user u ON e.created_by = u.user_id
            LEFT JOIN tbl_cawangan c ON e.cawangan_id = c.cawangan_id
            ORDER BY e.cawangan_id ASC, COALESCE(e.event_level, 'MASTER') ASC, e.event_date DESC
        ");

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $events[] = $row;
            }
        }

        return $events;
    }

    if ($viewMode === 'creator_only') {
        if ($userId === null) {
            return $events;
        }

        $stmt = $conn->prepare("
            SELECT e.*, u.username as creator_name,
                   c.cawangan_name,
                   COALESCE(e.event_level, 'MASTER') as event_level,
                   e.parent_event_id
            FROM tbl_event e
            LEFT JOIN tbl_user u ON e.created_by = u.user_id
            LEFT JOIN tbl_cawangan c ON e.cawangan_id = c.cawangan_id
            WHERE e.created_by = ?
            ORDER BY e.event_date DESC
        ");

        if (!$stmt) {
            return $events;
        }

        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }

        $stmt->close();
        return $events;
    }

    if ($viewMode === 'cawangan_only') {
        if ($cawanganId === null) {
            return $events;
        }

        $stmt = $conn->prepare("
            SELECT e.*, u.username as creator_name,
                   c.cawangan_name,
                   COALESCE(e.event_level, 'MASTER') as event_level,
                   e.parent_event_id
            FROM tbl_event e
            LEFT JOIN tbl_user u ON e.created_by = u.user_id
            LEFT JOIN tbl_cawangan c ON e.cawangan_id = c.cawangan_id
            WHERE e.cawangan_id = ?
            ORDER BY e.event_date DESC
        ");

        if (!$stmt) {
            return $events;
        }

        $stmt->bind_param("i", $cawanganId);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }

        $stmt->close();
        return $events;
    }

    return $events;
}

/**
 * Get event by ID with creator username
 *
 * @param mysqli $conn Database connection
 * @param int $event_id Event ID
 * @return array|null Event data or null
 */
function getEventById($conn, $event_id) {
    $stmt = $conn->prepare("
        SELECT e.*, u.username as creator_name
        FROM tbl_event e
        LEFT JOIN tbl_user u ON e.created_by = u.user_id
        WHERE e.event_id = ?
    ");
    if (!$stmt) return null;

    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->num_rows > 0 ? $result->fetch_assoc() : null;
    $stmt->close();
    return $event;
}

/**
 * Add a new event
 *
 * @param mysqli $conn Database connection
 * @param array $event_data Event details
 * @param int $user_id Creator user ID
 * @return array Result with status and message
 */
function getAllCawangan($conn) {
    $rows = [];
    $result = $conn->query("SELECT cawangan_id, cawangan_name FROM tbl_cawangan ORDER BY cawangan_name ASC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
    }
    return $rows;
}

function getMasterEventsByCawangan($conn, $cawanganId) {
    $rows = [];
    $stmt = $conn->prepare("
        SELECT event_id, event_title, event_date
        FROM tbl_event
        WHERE event_level = 'MASTER' AND cawangan_id = ?
        ORDER BY event_date DESC, event_id DESC
    ");
    if (!$stmt) return $rows;

    $stmt->bind_param("i", $cawanganId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

function addEvent($conn, $event_data, $user_id, $isPusatCreator = false, $cawanganId = null) {
    if (empty($event_data['event_title']) || empty($event_data['event_date']) || empty($event_data['venue'])) {
        return ['status' => false, 'message' => 'Event title, date, and venue are required'];
    }

$title = $event_data['event_title'];
    $date = $event_data['event_date'];
    $end_date = !empty($event_data['event_end_date']) ? $event_data['event_end_date'] : null;
    $venue = $event_data['venue'];
    $budget = !empty($event_data['budget_est']) ? (float)$event_data['budget_est'] : null;
    $status = 'Draft';

    if ($isPusatCreator) {
        $assigned_cawangan_id = isset($event_data['assigned_cawangan_id']) && $event_data['assigned_cawangan_id'] !== ''
            ? (int)$event_data['assigned_cawangan_id']
            : null;

        if ($assigned_cawangan_id === null) {
            return ['status' => false, 'message' => 'Assigned cawangan is required for Master Event'];
        }

        $stmt = $conn->prepare("
            INSERT INTO tbl_event (event_title, event_date, event_end_date, venue, budget_est, created_by, status, cawangan_id, event_level, parent_event_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'MASTER', NULL)
        ");

        if (!$stmt) {
            return ['status' => false, 'message' => 'Database error: ' . $conn->error];
        }

        $stmt->bind_param("sssdisii", $title, $date, $end_date, $venue, $budget, $user_id, $status, $assigned_cawangan_id);
    } else {
        if ($cawanganId === null) {
            return ['status' => false, 'message' => 'Cawangan ID is required for branch event creation'];
        }

        $parent_master_event_id = isset($event_data['parent_master_event_id']) && $event_data['parent_master_event_id'] !== ''
            ? (int)$event_data['parent_master_event_id']
            : null;

        if ($parent_master_event_id === null) {
            return ['status' => false, 'message' => 'Master Event selection is required for Sub Event'];
        }

        $master_check_stmt = $conn->prepare("
            SELECT event_id
            FROM tbl_event
            WHERE event_id = ? AND event_level = 'MASTER' AND cawangan_id = ?
        ");
        if (!$master_check_stmt) {
            return ['status' => false, 'message' => 'Database error: ' . $conn->error];
        }

        $master_check_stmt->bind_param("ii", $parent_master_event_id, $cawanganId);
        $master_check_stmt->execute();
        $master_check_result = $master_check_stmt->get_result();
        $is_valid_master = $master_check_result && $master_check_result->num_rows > 0;
        $master_check_stmt->close();

        if (!$is_valid_master) {
            return ['status' => false, 'message' => 'Selected Master Event is invalid for your cawangan'];
        }

        $stmt = $conn->prepare("
            INSERT INTO tbl_event (event_title, event_date, event_end_date, venue, budget_est, created_by, status, cawangan_id, event_level, parent_event_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'SUB', ?)
        ");

        if (!$stmt) {
            return ['status' => false, 'message' => 'Database error: ' . $conn->error];
        }

        $stmt->bind_param("sssdisiii", $title, $date, $end_date, $venue, $budget, $user_id, $status, $cawanganId, $parent_master_event_id);
    }

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return ['status' => false, 'message' => 'Database error: ' . $error];
    }

    $event_id = $stmt->insert_id;
    $stmt->close();

    return [
        'status' => true,
        'message' => 'Event created successfully',
        'event_id' => $event_id
    ];
}

/**
 * Submit event proposal for review
 *
 * Allowed transition: Draft -> Submitted
 */
function submitEventProposal($conn, $event_id) {
    $current = getEventById($conn, $event_id);
    if (!$current) {
        return ['status' => false, 'message' => 'Event not found'];
    }

    $current_status = $current['status'] ?? 'Draft';
    if ($current_status !== 'Draft') {
        return ['status' => false, 'message' => 'Only Draft proposals can be submitted'];
    }

    $stmt = $conn->prepare("UPDATE tbl_event SET status = 'Submitted' WHERE event_id = ?");
    if (!$stmt) {
        return ['status' => false, 'message' => 'Database error: ' . $conn->error];
    }

    $stmt->bind_param("i", $event_id);
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return ['status' => false, 'message' => 'Database error: ' . $error];
    }

    $stmt->close();
    return ['status' => true, 'message' => 'Proposal submitted for approval'];
}

/**
 * Approve submitted event proposal
 *
 * Allowed transition: Submitted -> Approved
 */
function approveEventProposal($conn, $event_id) {
    $current = getEventById($conn, $event_id);
    if (!$current) {
        return ['status' => false, 'message' => 'Event not found'];
    }

    $current_status = $current['status'] ?? 'Draft';
    if ($current_status !== 'Submitted') {
        return ['status' => false, 'message' => 'Only Submitted proposals can be approved'];
    }

    $stmt = $conn->prepare("UPDATE tbl_event SET status = 'Approved' WHERE event_id = ?");
    if (!$stmt) {
        return ['status' => false, 'message' => 'Database error: ' . $conn->error];
    }

    $stmt->bind_param("i", $event_id);
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return ['status' => false, 'message' => 'Database error: ' . $error];
    }

    $stmt->close();
    return ['status' => true, 'message' => 'Proposal approved successfully'];
}

/**
 * Reject submitted event proposal
 *
 * Allowed transition: Submitted -> Rejected
 */
function rejectEventProposal($conn, $event_id) {
    $current = getEventById($conn, $event_id);
    if (!$current) {
        return ['status' => false, 'message' => 'Event not found'];
    }

    $current_status = $current['status'] ?? 'Draft';
    if ($current_status !== 'Submitted') {
        return ['status' => false, 'message' => 'Only Submitted proposals can be rejected'];
    }

    $stmt = $conn->prepare("UPDATE tbl_event SET status = 'Rejected' WHERE event_id = ?");
    if (!$stmt) {
        return ['status' => false, 'message' => 'Database error: ' . $conn->error];
    }

    $stmt->bind_param("i", $event_id);
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return ['status' => false, 'message' => 'Database error: ' . $error];
    }

    $stmt->close();
    return ['status' => true, 'message' => 'Proposal rejected'];
}

/**
 * Update an event
 *
 * @param mysqli $conn Database connection
 * @param int $event_id Event ID
 * @param array $event_data Updated event details
 * @return array Result with status and message
 */
function updateEvent($conn, $event_id, $event_data) {
    if (empty($event_id)) {
        return ['status' => false, 'message' => 'Invalid event ID'];
    }

    $updates = [];
    $types = "";
    $values = [];

    if (isset($event_data['event_title'])) {
        $updates[] = "event_title = ?";
        $types .= "s";
        $values[] = $event_data['event_title'];
    }
    if (isset($event_data['event_date'])) {
        $updates[] = "event_date = ?";
        $types .= "s";
        $values[] = $event_data['event_date'];
    }
    if (isset($event_data['venue'])) {
        $updates[] = "venue = ?";
        $types .= "s";
        $values[] = $event_data['venue'];
    }
    if (isset($event_data['budget_est'])) {
        $updates[] = "budget_est = ?";
        $types .= "d";
        $values[] = (float)$event_data['budget_est'];
    }

    if (empty($updates)) {
        return ['status' => false, 'message' => 'No data to update'];
    }

    $types .= "i";
    $values[] = $event_id;

    $sql = "UPDATE tbl_event SET " . implode(', ', $updates) . " WHERE event_id = ?";
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
    return ['status' => true, 'message' => 'Event updated successfully'];
}

/**
 * Delete an event
 *
 * @param mysqli $conn Database connection
 * @param int $event_id Event ID
 * @return array Result with status and message
 */
function deleteEvent($conn, $event_id) {
    if (empty($event_id)) {
        return ['status' => false, 'message' => 'Invalid event ID'];
    }

    $stmt = $conn->prepare("DELETE FROM tbl_event WHERE event_id = ?");
    if (!$stmt) {
        return ['status' => false, 'message' => 'Database error: ' . $conn->error];
    }

    $stmt->bind_param("i", $event_id);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return ['status' => false, 'message' => 'Database error: ' . $error];
    }

    $stmt->close();
    return ['status' => true, 'message' => 'Event deleted successfully'];
}

/**
 * Get upcoming events count (event_date >= today)
 *
 * @param mysqli $conn Database connection
 * @return int Count of upcoming events
 */
function getUpcomingEventsCount($conn) {
    $result = $conn->query("SELECT COUNT(*) as total FROM tbl_event WHERE event_date >= CURDATE()");
    return $result ? (int)$result->fetch_assoc()['total'] : 0;
}

/**
 * Get past events count
 *
 * @param mysqli $conn Database connection
 * @return int Count of past events
 */
function getPastEventsCount($conn) {
    $result = $conn->query("SELECT COUNT(*) as total FROM tbl_event WHERE event_date < CURDATE()");
    return $result ? (int)$result->fetch_assoc()['total'] : 0;
}

/**
 * Get attendance records for an event
 *
 * @param mysqli $conn Database connection
 * @param int $event_id Event ID
 * @return array Array of attendance records with member details
 */
function getEventAttendance($conn, $event_id) {
    $stmt = $conn->prepare("
        SELECT a.*, m.full_name, m.ic_number
        FROM tbl_attendance a
        JOIN tbl_member m ON a.member_id = m.member_id
        WHERE a.event_id = ?
        ORDER BY m.full_name ASC
    ");
    if (!$stmt) return [];

    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $attendance = [];
    while ($row = $result->fetch_assoc()) {
        $attendance[] = $row;
    }
    $stmt->close();
    return $attendance;
}

/**
 * Mark attendance for a member at an event
 *
 * @param mysqli $conn Database connection
 * @param int $event_id Event ID
 * @param int $member_id Member ID
 * @param string $status Present/Absent/Excused
 * @param string $notes Optional notes
 * @param int $marked_by User ID who marked attendance
 * @return array Result with status and message
 */
function markAttendance($conn, $event_id, $member_id, $status, $notes = '', $marked_by = null) {
    $valid_statuses = ['Present', 'Absent', 'Excused'];
    if (!in_array($status, $valid_statuses)) {
        return ['status' => false, 'message' => 'Invalid attendance status'];
    }

    // Check if attendance record already exists
    $check = $conn->prepare("SELECT attendance_id FROM tbl_attendance WHERE event_id = ? AND member_id = ?");
    if (!$check) {
        return ['status' => false, 'message' => 'Database error: ' . $conn->error];
    }
    $check->bind_param("ii", $event_id, $member_id);
    $check->execute();
    $check_result = $check->get_result();
    $exists = $check_result->num_rows > 0;
    $check->close();

    if ($exists) {
        // Update existing record
        $stmt = $conn->prepare("
            UPDATE tbl_attendance
            SET status = ?, notes = ?, marked_by = ?, marked_at = NOW()
            WHERE event_id = ? AND member_id = ?
        ");
        if (!$stmt) {
            return ['status' => false, 'message' => 'Database error: ' . $conn->error];
        }
        $stmt->bind_param("ssiii", $status, $notes, $marked_by, $event_id, $member_id);
    } else {
        // Insert new record
        $stmt = $conn->prepare("
            INSERT INTO tbl_attendance (event_id, member_id, status, notes, marked_by)
            VALUES (?, ?, ?, ?, ?)
        ");
        if (!$stmt) {
            return ['status' => false, 'message' => 'Database error: ' . $conn->error];
        }
        $stmt->bind_param("iissi", $event_id, $member_id, $status, $notes, $marked_by);
    }

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return ['status' => false, 'message' => 'Database error: ' . $error];
    }

    $stmt->close();
    return ['status' => true, 'message' => 'Attendance recorded successfully'];
}

/**
 * Get attendance summary for an event
 *
 * @param mysqli $conn Database connection
 * @param int $event_id Event ID
 * @return array Summary counts by status
 */
function getAttendanceSummary($conn, $event_id) {
    $stmt = $conn->prepare("
        SELECT status, COUNT(*) as count
        FROM tbl_attendance
        WHERE event_id = ?
        GROUP BY status
    ");
    if (!$stmt) return ['Present' => 0, 'Absent' => 0, 'Excused' => 0];

    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $summary = ['Present' => 0, 'Absent' => 0, 'Excused' => 0];
    while ($row = $result->fetch_assoc()) {
        $summary[$row['status']] = (int)$row['count'];
    }
    $stmt->close();
    return $summary;
}

/**
 * Get all members with their attendance status for a specific event
 *
 * @param mysqli $conn Database connection
 * @param int $event_id Event ID
 * @return array All members with attendance status (if recorded)
 */
function getAllMembersWithAttendance($conn, $event_id) {
    $result = $conn->query("
        SELECT
            m.member_id,
            m.full_name,
            m.ic_number,
            m.village,
            a.status as attendance_status,
            a.notes as attendance_notes
        FROM tbl_member m
        LEFT JOIN tbl_attendance a ON m.member_id = a.member_id AND a.event_id = " . (int)$event_id . "
        WHERE m.status = 'Active'
        ORDER BY m.full_name ASC
    ");

    $members = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $members[] = $row;
        }
    }
    return $members;
}

/**
 * Handle event document (proposal) upload
 *
 * @param mysqli $conn Database connection
 * @param int $event_id Event ID
 * @param array $file_data File data from $_FILES
 * @return array Result with status and message
 */
function handleEventDocumentUpload($conn, $event_id, $file_data) {
    // Allowed file extensions (RBAC proposal flow)
    $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png'];
    
    // Get file extension
    $file_name = $file_data['name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // Validate file extension
    if (!in_array($file_ext, $allowed_extensions, true)) {
        return ['status' => false, 'message' => 'Invalid file type. Allowed: PDF, JPG, JPEG, PNG'];
    }
    
    // Validate file size (max 5MB)
    $max_size = 5 * 1024 * 1024; // 5MB
    if ($file_data['size'] > $max_size) {
        return ['status' => false, 'message' => 'File size exceeds 5MB limit'];
    }
    
    // Create uploads directory if it doesn't exist
    $upload_dir = __DIR__ . '/../uploads/events';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generate unique filename
    $new_filename = 'event_' . $event_id . '_proposal_' . time() . '.' . $file_ext;
    $target_path = $upload_dir . '/' . $new_filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file_data['tmp_name'], $target_path)) {
        return ['status' => false, 'message' => 'Failed to save uploaded file'];
    }
    
    // Save document record to database
    $doc_name = 'Event Proposal - ' . basename($file_name);
    $file_path = 'uploads/events/' . $new_filename;
    
    $stmt = $conn->prepare("
        INSERT INTO tbl_document (event_id, doc_name, file_path)
        VALUES (?, ?, ?)
    ");
    
    if (!$stmt) {
        return ['status' => false, 'message' => 'Database error: ' . $conn->error];
    }
    
    $stmt->bind_param("iss", $event_id, $doc_name, $file_path);
    
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        // Remove uploaded file if database insert fails
        @unlink($target_path);
        return ['status' => false, 'message' => 'Database error: ' . $error];
    }
    
    $stmt->close();
    return ['status' => true, 'message' => 'Document uploaded successfully'];
}

/**
 * Get documents for an event
 *
 * @param mysqli $conn Database connection
 * @param int $event_id Event ID
 * @return array Array of document records
 */
function getEventDocuments($conn, $event_id) {
    $stmt = $conn->prepare("
        SELECT doc_id, doc_name, file_path, uploaded_at
        FROM tbl_document
        WHERE event_id = ?
        ORDER BY uploaded_at DESC
    ");
    
    if (!$stmt) return [];
    
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $documents = [];
    while ($row = $result->fetch_assoc()) {
        $documents[] = $row;
    }
    $stmt->close();
    return $documents;
}

/**
 * Delete event document
 *
 * @param mysqli $conn Database connection
 * @param int $doc_id Document ID
 * @return array Result with status and message
 */
function deleteEventDocument($conn, $doc_id) {
    if (empty($doc_id)) {
        return ['status' => false, 'message' => 'Invalid document ID'];
    }
    
    // Get file path before deleting
    $stmt = $conn->prepare("SELECT file_path FROM tbl_document WHERE doc_id = ?");
    if (!$stmt) {
        return ['status' => false, 'message' => 'Database error: ' . $conn->error];
    }
    
    $stmt->bind_param("i", $doc_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        return ['status' => false, 'message' => 'Document not found'];
    }
    
    $doc = $result->fetch_assoc();
    $file_path = $doc['file_path'];
    $stmt->close();
    
    // Delete from database
    $stmt = $conn->prepare("DELETE FROM tbl_document WHERE doc_id = ?");
    if (!$stmt) {
        return ['status' => false, 'message' => 'Database error: ' . $conn->error];
    }
    
    $stmt->bind_param("i", $doc_id);
    
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return ['status' => false, 'message' => 'Database error: ' . $error];
    }
    
    $stmt->close();
    
    // Delete physical file
    $full_path = __DIR__ . '/../' . $file_path;
    if (file_exists($full_path)) {
        @unlink($full_path);
    }
    
    return ['status' => true, 'message' => 'Document deleted successfully'];
}

/**
 * Get all event documents with optional search filter
 *
 * @param mysqli $conn Database connection
 * @param string $search Optional search text
 * @return array Document rows with event info
 */
function getAllDocuments($conn, $search = '') {
    $search = trim((string)$search);

    $sql = "
        SELECT d.doc_id, d.event_id, d.doc_name, d.file_path, d.uploaded_at, e.event_title, e.status AS event_status
        FROM tbl_document d
        LEFT JOIN tbl_event e ON d.event_id = e.event_id
    ";

    if ($search !== '') {
        $sql .= " WHERE d.doc_name LIKE ? OR e.event_title LIKE ? ";
    }

    $sql .= " ORDER BY d.uploaded_at DESC ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [];
    }

    if ($search !== '') {
        $q = '%' . $search . '%';
        $stmt->bind_param("ss", $q, $q);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $documents = [];
    while ($row = $result->fetch_assoc()) {
        $documents[] = $row;
    }

    $stmt->close();
    return $documents;
}

/**
 * Get documents by type keyword in doc_name with optional search
 *
 * @param mysqli $conn Database connection
 * @param string $doc_type_keyword Type keyword (e.g. 'minutes', 'report', 'proposal')
 * @param string $search Optional search text
 * @return array Document rows with event info
 */
function getDocumentsByType($conn, $doc_type_keyword, $search = '') {
    $search = trim((string)$search);
    $doc_type_keyword = trim((string)$doc_type_keyword);

    $sql = "
        SELECT d.doc_id, d.event_id, d.doc_name, d.file_path, d.uploaded_at, e.event_title, e.status AS event_status
        FROM tbl_document d
        LEFT JOIN tbl_event e ON d.event_id = e.event_id
        WHERE LOWER(d.doc_name) LIKE ?
    ";

    $type_like = '%' . strtolower($doc_type_keyword) . '%';

    if ($search !== '') {
        $sql .= " AND (d.doc_name LIKE ? OR e.event_title LIKE ?) ";
    }

    $sql .= " ORDER BY d.uploaded_at DESC ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [];
    }

    if ($search !== '') {
        $q = '%' . $search . '%';
        $stmt->bind_param("sss", $type_like, $q, $q);
    } else {
        $stmt->bind_param("s", $type_like);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $documents = [];
    while ($row = $result->fetch_assoc()) {
        $documents[] = $row;
    }

    $stmt->close();
    return $documents;
}

/**
 * Upload general document (Proposal/Minutes/Report) with optional event linkage
 *
 * @param mysqli $conn Database connection
 * @param int|null $event_id Nullable event ID
 * @param string $doc_type Proposal|Minutes|Report
 * @param array $file_data File data from $_FILES
 * @return array Result with status and message
 */
function uploadGeneralDocument($conn, $event_id, $doc_type, $file_data) {
    $allowed_types = ['Proposal', 'Minutes', 'Report'];
    if (!in_array($doc_type, $allowed_types, true)) {
        return ['status' => false, 'message' => 'Invalid document type selected'];
    }

    if (empty($file_data) || !isset($file_data['name'], $file_data['tmp_name'])) {
        return ['status' => false, 'message' => 'No file uploaded'];
    }

    if (!empty($file_data['error']) && (int)$file_data['error'] !== UPLOAD_ERR_OK) {
        return ['status' => false, 'message' => 'File upload error'];
    }

    $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png'];
    $max_size = 5 * 1024 * 1024; // 5MB

    $original_name = (string)$file_data['name'];
    $file_ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

    if (!in_array($file_ext, $allowed_extensions, true)) {
        return ['status' => false, 'message' => 'Invalid file type. Allowed: PDF, JPG, JPEG, PNG'];
    }

    if ((int)$file_data['size'] > $max_size) {
        return ['status' => false, 'message' => 'File size exceeds 5MB limit'];
    }

    $upload_dir = __DIR__ . '/../uploads/events';
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true) && !is_dir($upload_dir)) {
            return ['status' => false, 'message' => 'Failed to create upload directory'];
        }
    }

    $safe_type = strtolower($doc_type);
    $unique_name = 'doc_' . $safe_type . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $file_ext;
    $target_path = $upload_dir . '/' . $unique_name;

    if (!move_uploaded_file($file_data['tmp_name'], $target_path)) {
        return ['status' => false, 'message' => 'Failed to save uploaded file'];
    }

    $doc_name = $doc_type . ' - ' . basename($original_name);
    $file_path = 'uploads/events/' . $unique_name;

    $event_id_val = (!empty($event_id) && (int)$event_id > 0) ? (int)$event_id : null;

    if ($event_id_val === null) {
        $stmt = $conn->prepare("
            INSERT INTO tbl_document (event_id, doc_name, file_path)
            VALUES (NULL, ?, ?)
        ");
        if (!$stmt) {
            @unlink($target_path);
            return ['status' => false, 'message' => 'Database error: ' . $conn->error];
        }

        $stmt->bind_param("ss", $doc_name, $file_path);
    } else {
        $stmt = $conn->prepare("
            INSERT INTO tbl_document (event_id, doc_name, file_path)
            VALUES (?, ?, ?)
        ");
        if (!$stmt) {
            @unlink($target_path);
            return ['status' => false, 'message' => 'Database error: ' . $conn->error];
        }

        $stmt->bind_param("iss", $event_id_val, $doc_name, $file_path);
    }

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        @unlink($target_path);
        return ['status' => false, 'message' => 'Database error: ' . $error];
    }

    $stmt->close();
    return ['status' => true, 'message' => $doc_type . ' uploaded successfully'];
}

