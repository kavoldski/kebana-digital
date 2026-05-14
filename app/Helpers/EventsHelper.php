<?php
/**
 * KEBANA Management System - Events Helper
 * File: app/Helpers/EventsHelper.php
 */

namespace App\Helpers;

use App\Core\Database;
use App\Helpers\NotificationHelper;

class EventsHelper {
    public static function getAllEvents($viewMode = 'all', $userId = null, $cawanganId = null) {
        $db = Database::getInstance()->getConnection();
        $events = [];

        if ($viewMode === 'all') {
            $sql = "
                SELECT e.*, u.username as creator_name,
                       c.cawangan_name,
                       COALESCE(e.event_level, 'MASTER') as event_level,
                       e.parent_event_id, e.kawasan
                FROM tbl_event e
                LEFT JOIN tbl_user u ON e.created_by = u.user_id
                LEFT JOIN tbl_cawangan c ON e.cawangan_id = c.cawangan_id
                ORDER BY e.cawangan_id ASC, COALESCE(e.event_level, 'MASTER') ASC, e.event_date DESC
            ";
            $result = $db->query($sql);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $events[] = $row;
                }
            }
            return $events;
        }

        if ($viewMode === 'creator_only') {
            if ($userId === null) return [];
            $stmt = $db->prepare("
                SELECT e.*, u.username as creator_name,
                       c.cawangan_name,
                       COALESCE(e.event_level, 'MASTER') as event_level,
                       e.parent_event_id, e.kawasan
                FROM tbl_event e
                LEFT JOIN tbl_user u ON e.created_by = u.user_id
                LEFT JOIN tbl_cawangan c ON e.cawangan_id = c.cawangan_id
                WHERE e.created_by = ?
                ORDER BY e.event_date DESC
            ");
            if ($stmt) {
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    $events[] = $row;
                }
                $stmt->close();
            }
            return $events;
        }

        if ($viewMode === 'cawangan_only') {
            if ($cawanganId === null) return [];
            $stmt = $db->prepare("
                SELECT e.*, u.username as creator_name,
                       c.cawangan_name,
                       COALESCE(e.event_level, 'MASTER') as event_level,
                       e.parent_event_id, e.kawasan
                FROM tbl_event e
                LEFT JOIN tbl_user u ON e.created_by = u.user_id
                LEFT JOIN tbl_cawangan c ON e.cawangan_id = c.cawangan_id
                WHERE e.cawangan_id = ?
                ORDER BY e.event_date DESC
            ");
            if ($stmt) {
                $stmt->bind_param("i", $cawanganId);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    $events[] = $row;
                }
                $stmt->close();
            }
            return $events;
        }

        return $events;
    }

    public static function getAllCawangan() {
        $db = Database::getInstance()->getConnection();
        $rows = [];
        $result = $db->query("SELECT cawangan_id, cawangan_name FROM tbl_cawangan ORDER BY cawangan_name ASC");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        return $rows;
    }

    public static function getMasterEventsByCawangan($cawanganId) {
        $db = Database::getInstance()->getConnection();
        $rows = [];
        $stmt = $db->prepare("
            SELECT event_id, event_title, event_date
            FROM tbl_event
            WHERE event_level = 'MASTER' AND cawangan_id = ? AND status = 'Approved'
            ORDER BY event_date DESC, event_id DESC
        ");
        if ($stmt) {
            $stmt->bind_param("i", $cawanganId);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            $stmt->close();
        }
        return $rows;
    }

    public static function addEvent($data, $userId, $isPusatCreator, $cawanganId = null) {
        $db = Database::getInstance()->getConnection();
        $title = $data['event_title'] ?? '';
        $date = $data['event_date'] ?? '';
        $end_date = !empty($data['event_end_date']) ? $data['event_end_date'] : null;
        $venue = $data['venue'] ?? '';
        $kawasan = $data['kawasan'] ?? '';
        $budget = !empty($data['budget_est']) ? (float)$data['budget_est'] : null;
        $status = 'Draft';
        $approval_status = 'Pending Submission';

        if ($isPusatCreator) {
            $assigned_cawangan_id = !empty($data['assigned_cawangan_id']) ? (int)$data['assigned_cawangan_id'] : null;
            $level = 'MASTER';
            $stmt = $db->prepare("
                INSERT INTO tbl_event (event_title, event_date, event_end_date, venue, kawasan, budget_est, created_by, status, approval_status, cawangan_id, event_level)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            if ($stmt) {
                $stmt->bind_param("ssssdisisss", $title, $date, $end_date, $venue, $kawasan, $budget, $userId, $status, $approval_status, $assigned_cawangan_id, $level);
                $success = $stmt->execute();
                $insert_id = $stmt->insert_id;
                $stmt->close();

                if ($success) {
                    // Notify: SU Cawangan (33), Pengerusi Cawangan (11) (President only on Submit)
                    NotificationHelper::notifyRoles([33, 11], 'master_event_created', 'Aktiviti Master Baru Dicipta', "Aktiviti Master \"$title\" telah dicipta oleh HQ. Sila rujuk guideline yang disertakan.", "events/view/$insert_id");
                    NotificationHelper::notifyRoles([888, 4], 'event_created', 'Aktiviti Baru Dicipta (HQ)', "Aktiviti \"$title\" telah dicipta oleh HQ.", "events/view/$insert_id");
                }

                return $success ? $insert_id : false;
            }
        } else {
            $parent_master_id = !empty($data['parent_master_event_id']) ? (int)$data['parent_master_event_id'] : null;
            $level = 'SUB';
            $stmt = $db->prepare("
                INSERT INTO tbl_event (event_title, event_date, event_end_date, venue, kawasan, budget_est, created_by, status, approval_status, cawangan_id, event_level, parent_event_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            if ($stmt) {
                $stmt->bind_param("ssssdisisssi", $title, $date, $end_date, $venue, $kawasan, $budget, $userId, $status, $approval_status, $cawanganId, $level, $parent_master_id);
                $success = $stmt->execute();
                $insert_id = $stmt->insert_id;
                $stmt->close();

                if ($success) {
                    // For sub-events, initially it's a draft.
                    NotificationHelper::notifyRoles([888, 4], 'event_created', 'Aktiviti Baru Dicipta (Cawangan)', "Aktiviti \"$title\" telah dicipta oleh cawangan.", "events/view/$insert_id");
                }

                return $success ? $insert_id : false;
            }
        }
        return false;
    }

    public static function handleDocumentUpload($eventId, $file, $userId = null) {
        $db = Database::getInstance()->getConnection();
        
        // Fetch event info for smart tagging
        $eventTitle = 'Unknown Event';
        $cawanganName = 'HQ';
        $eventLevel = 'MASTER';
        $stmt_info = $db->prepare("SELECT e.event_title, e.event_level, c.cawangan_name FROM tbl_event e LEFT JOIN tbl_cawangan c ON e.cawangan_id = c.cawangan_id WHERE e.event_id = ?");
        if ($stmt_info) {
            $stmt_info->bind_param("i", $eventId);
            $stmt_info->execute();
            $res = $stmt_info->get_result();
            if ($row = $res->fetch_assoc()) {
                $eventTitle = $row['event_title'];
                $eventLevel = $row['event_level'] ?? 'MASTER';
                $cawanganName = $row['cawangan_name'] ?? 'HQ';
            }
            $stmt_info->close();
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
        if (!in_array($ext, $allowed)) return false;

        $uploadDir = APP_ROOT . '/uploads/events/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $newName = 'event_' . $eventId . '_doc_' . time() . '.' . $ext;
        $target = $uploadDir . $newName;

        if (move_uploaded_file($file['tmp_name'], $target)) {
            $path = 'uploads/events/' . $newName;
            $doc_prefix = ($eventLevel === 'MASTER') ? 'Event Guideline' : 'Event Proposal';
            $name = "$doc_prefix - " . basename($file['name']);
            // Smart auto-tagging
            $tags = "Event, Proposal, $eventTitle, $cawanganName";
            
            $stmt = $db->prepare("INSERT INTO tbl_document (event_id, doc_name, file_path, doc_tags, uploaded_by) VALUES (?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("isssi", $eventId, $name, $path, $tags, $userId);
                $stmt->execute();
                $stmt->close();
                return true;
            }
        }
        return false;
    }

    public static function markAttendance($eventId, $memberId, $status, $notes = '', $userId = null) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            INSERT INTO tbl_attendance (event_id, member_id, status, notes, marked_by)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE status = VALUES(status), notes = VALUES(notes), marked_by = VALUES(marked_by), marked_at = CURRENT_TIMESTAMP
        ");
        if ($stmt) {
            $stmt->bind_param("iissi", $eventId, $memberId, $status, $notes, $userId);
            $success = $stmt->execute();
            $stmt->close();
            return $success;
        }
        return false;
    }

    public static function getAttendanceSummary($eventId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT status, COUNT(*) as count
            FROM tbl_attendance
            WHERE event_id = ?
            GROUP BY status
        ");
        $summary = ['Present' => 0, 'Absent' => 0, 'Excused' => 0];
        if ($stmt) {
            $stmt->bind_param("i", $eventId);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $summary[$row['status']] = (int)$row['count'];
            }
            $stmt->close();
        }
        return $summary;
    }

    public static function getAllMembersWithAttendance($eventId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT m.member_id, m.full_name, m.ic_number, m.village,
                   a.status as attendance_status, a.notes as attendance_notes
            FROM tbl_member m
            LEFT JOIN tbl_attendance a ON m.member_id = a.member_id AND a.event_id = ?
            WHERE m.status = 'Active'
            ORDER BY m.full_name ASC
        ");
        $members = [];
        if ($stmt) {
            $stmt->bind_param("i", $eventId);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $members[] = $row;
            }
            $stmt->close();
        }
        return $members;
    }

    public static function getEventById($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT e.*, u.username as creator_name,
                   COALESCE(e.approval_status, 'Pending Submission') as approval_status
            FROM tbl_event e
            LEFT JOIN tbl_user u ON e.created_by = u.user_id
            WHERE e.event_id = ?
        ");
        if (!$stmt) return null;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $event = $result->fetch_assoc();
        $stmt->close();
        return $event;
    }

    public static function submitEvent($id) {
        $db = Database::getInstance()->getConnection();
        
        $event = self::getEventById($id);
        $title = $event['event_title'] ?? 'Unknown Event';
        $current_status = strtoupper($event['status'] ?? 'Draft');

        // Allow submission if it's a fresh Draft OR if it has been Approved by the Branch
        $stmt = $db->prepare("UPDATE tbl_event SET status = 'Submitted', approval_status = 'Pending President' WHERE event_id = ? AND (UPPER(status) = 'DRAFT' OR UPPER(status) = 'BRANCH APPROVED' OR status = '0' OR status IS NULL)");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        $stmt->close();

        if ($success) {
            $level = $event['event_level'] ?? 'MASTER';
            if ($level === 'MASTER') {
                NotificationHelper::notifyRoles([1, 888], 'event_submission', 'Permohonan Kelulusan Aktiviti Master', "Aktiviti Master \"$title\" memerlukan kelulusan Presiden.", "events/view/$id");
            } else {
                // Sub event being submitted to Pusat after branch approval
                NotificationHelper::notifyRoles([1, 4, 888], 'sub_event_submitted', 'Kertas Kerja Sub-Aktiviti Diterima', "SU Cawangan telah menghantar PROPOSAL bagi Sub-Aktiviti \"$title\".", "events/view/$id");
            }
        }

        return $success;
    }

    public static function submitToBranch($id) {
        $db = Database::getInstance()->getConnection();
        $event = self::getEventById($id);
        $title = $event['event_title'] ?? 'Unknown Event';
        $cawanganId = $event['cawangan_id'] ?? null;

        $stmt = $db->prepare("UPDATE tbl_event SET status = 'Pending Branch Approval', approval_status = 'Pending Branch Approval' WHERE event_id = ? AND (UPPER(status) = 'DRAFT' OR status = '0' OR status IS NULL)");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        $stmt->close();

        if ($success) {
            // Notify Pengerusi Cawangan (11)
            NotificationHelper::notifyRoles([11], 'branch_approval_required', 'Pengesahan Kertas Kerja Diperlukan', "SU Cawangan telah menghantar Kertas Kerja \"$title\" untuk tujuan pengesahan anda.", "events/view/$id");
        }
        return $success;
    }

    public static function branchApprove($id) {
        $db = Database::getInstance()->getConnection();
        $event = self::getEventById($id);
        $title = $event['event_title'] ?? 'Unknown Event';
        $creatorId = $event['created_by'] ?? null;

        $level = $event['event_level'] ?? 'MASTER';
        
        if ($level === 'SUB') {
            // Sub Events are finalized by Branch Approval
            $stmt = $db->prepare("UPDATE tbl_event SET status = 'Approved', approval_status = 'Approved by Branch' WHERE event_id = ?");
        } else {
            // Master Events still need HQ submission
            $stmt = $db->prepare("UPDATE tbl_event SET status = 'Branch Approved', approval_status = 'Approved by Branch' WHERE event_id = ?");
        }
        
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        $stmt->close();

        if ($success && $creatorId) {
            NotificationHelper::create($creatorId, 'branch_approved', 'Kertas Kerja Disahkan', "Pengerusi Cawangan telah meluluskan Kertas Kerja \"$title\". Anda kini boleh menghantar ke peringkat Pusat.", "events/view/$id");
        }
        return $success;
    }

    public static function approveEvent($id) {
        $db = Database::getInstance()->getConnection();
        
        $event = self::getEventById($id);
        $title = $event['event_title'] ?? 'Unknown Event';
        $creatorId = $event['created_by'] ?? null;

        $stmt = $db->prepare("UPDATE tbl_event SET status = 'Approved', approval_status = 'Approved by President' WHERE event_id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        $stmt->close();

        if ($success && $creatorId) {
            NotificationHelper::create($creatorId, 'event_approved', 'Aktiviti Diluluskan', "Tahniah! Aktiviti \"$title\" telah diluluskan.", "events/view/$id");
        }

        return $success;
    }

    public static function rejectEvent($id) {
        $db = Database::getInstance()->getConnection();
        
        $event = self::getEventById($id);
        $title = $event['event_title'] ?? 'Unknown Event';
        $creatorId = $event['created_by'] ?? null;

        $current_role = (int)($_SESSION['role'] ?? 0);
        $reject_label = ($current_role == 11) ? 'Rejected by Branch' : 'Rejected by President';
        
        $stmt = $db->prepare("UPDATE tbl_event SET status = 'Rejected', approval_status = ? WHERE event_id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("si", $reject_label, $id);
        $success = $stmt->execute();
        $stmt->close();

        if ($success && $creatorId) {
            NotificationHelper::create($creatorId, 'event_rejected', 'Aktiviti Ditolak', "Maaf, aktiviti \"$title\" telah ditolak oleh Presiden.", "events/view/$id");
        }

        return $success;
    }

    public static function deleteEvent($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM tbl_event WHERE event_id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    /**
     * Fetch sub-events for a given master event.
     */
    public static function getSubEvents($parentId) {
        $db = Database::getInstance()->getConnection();
        $rows = [];
        $stmt = $db->prepare("
            SELECT e.*, c.cawangan_name, u.username as creator_name
            FROM tbl_event e
            LEFT JOIN tbl_cawangan c ON e.cawangan_id = c.cawangan_id
            LEFT JOIN tbl_user u ON e.created_by = u.user_id
            WHERE e.parent_event_id = ?
            ORDER BY e.event_date ASC
        ");
        if ($stmt) {
            $stmt->bind_param("i", $parentId);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            $stmt->close();
        }
        return $rows;
    }

    /**
     * Get parent event for a sub-event.
     */
    public static function getParentEvent($parentId) {
        return self::getEventById($parentId);
    }

    /**
     * Fetch documents for a given event.
     */
    public static function getDocumentsByEventId($eventId) {
        $db = Database::getInstance()->getConnection();
        $rows = [];
        $stmt = $db->prepare("
            SELECT d.*, u.username as uploader_name
            FROM tbl_document d
            LEFT JOIN tbl_user u ON d.uploaded_by = u.user_id
            WHERE d.event_id = ?
            ORDER BY d.uploaded_at DESC
        ");
        if ($stmt) {
            $stmt->bind_param("i", $eventId);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            $stmt->close();
        }
        return $rows;
    }

    /**
     * Get unique venues and kawasan for suggestions.
     */
    public static function getUniqueLocations() {
        $db = Database::getInstance()->getConnection();
        $locations = ['venues' => [], 'kawasan' => []];
        
        $res = $db->query("SELECT DISTINCT venue FROM tbl_event WHERE venue != '' AND venue != '0' ORDER BY venue ASC LIMIT 50");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $locations['venues'][] = $row['venue'];
            }
        }
        
        $res = $db->query("SELECT DISTINCT kawasan FROM tbl_event WHERE kawasan != '' AND kawasan != '0' ORDER BY kawasan ASC LIMIT 50");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $locations['kawasan'][] = $row['kawasan'];
            }
        }
        
        return $locations;
    }
}
