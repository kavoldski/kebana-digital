<?php
/**
 * KEBANA Digital Management System - Dashboard Helper
 * File: app/Helpers/DashboardHelper.php
 */

namespace App\Helpers;

use App\Core\Database;

class DashboardHelper {
    public static function getUpcomingEventsCount($cawanganId = null) {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT COUNT(*) as total FROM tbl_event WHERE event_date >= CURDATE() AND status = 'Approved'";
        if ($cawanganId !== null) {
            $sql .= " AND cawangan_id = " . (int)$cawanganId;
        }
        $result = $db->query($sql);
        $count = $result ? $result->fetch_assoc()['total'] : 0;
        return (int) $count;
    }

    public static function getPastEventsCount($cawanganId = null) {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT COUNT(*) as total FROM tbl_event WHERE event_date < CURDATE() AND status = 'Approved'";
        if ($cawanganId !== null) {
            $sql .= " AND cawangan_id = " . (int)$cawanganId;
        }
        $result = $db->query($sql);
        $count = $result ? $result->fetch_assoc()['total'] : 0;
        return (int) $count;
    }

    public static function getPendingDocumentsCount($role = 0, $cawanganId = null) {
        $db = Database::getInstance()->getConnection();
        
        // Base SQL
        $sql = "SELECT COUNT(*) as total FROM tbl_document d ";
        $sql .= "JOIN tbl_event e ON d.event_id = e.event_id ";
        $sql .= "WHERE d.status = 'Pending'";

        // Role-based status filtering to align with workflow
        if (in_array($role, [1, 888, 2, 3])) {
            // President/Super Admin/VP: Only see documents for items submitted to HQ
            $sql .= " AND e.status = 'Submitted'";
        } elseif ($role === 11) {
            // Pengerusi Cawangan: Only see documents for items pending branch approval
            $sql .= " AND e.status = 'Pending Branch Approval'";
        } else {
            // For others, at least exclude Drafts to prevent clutter
            $sql .= " AND e.status != 'Draft'";
        }

        // Filter by branch if not Pusat role
        $pusat_roles = [888, 1, 2, 3, 4, 5, 6, 7];
        if (!in_array($role, $pusat_roles) && $cawanganId !== null) {
            $sql .= " AND e.cawangan_id = " . (int)$cawanganId;
        }

        $result = $db->query($sql);
        $count = $result ? $result->fetch_assoc()['total'] : 0;
        return (int) $count;
    }

    public static function getTotalDocumentsCount() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM tbl_document");
        if (!$stmt) return 0;
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_assoc()['total'] ?? 0;
        $stmt->close();
        return (int) $count;
    }

    public static function getPendingApprovalsCount($role = 0, $cawanganId = null) {
        $db = Database::getInstance()->getConnection();
        
        // Role-based status filtering
        if (in_array($role, [1, 888])) {
            // President/Super Admin: See items submitted to Pusat
            $status = 'Submitted';
            $sql = "SELECT COUNT(*) as total FROM tbl_event WHERE status = '$status'";
        } elseif ($role === 11) {
            // Pengerusi Cawangan: See items pending branch approval
            $status = 'Pending Branch Approval';
            $sql = "SELECT COUNT(e.event_id) as total FROM tbl_event e LEFT JOIN tbl_event p ON e.parent_event_id = p.event_id WHERE e.status = '$status' AND (e.cawangan_id = " . (int)$cawanganId . " OR p.cawangan_id = " . (int)$cawanganId . ")";
        } else {
            // Other roles might not have direct approval authority in the dashboard count for now
            // or we show Submitted for Pusat roles 2-7 if needed.
            if (in_array($role, [2, 3, 4, 5, 6, 7])) {
                $sql = "SELECT COUNT(*) as total FROM tbl_event WHERE status = 'Submitted'";
            } else {
                return 0;
            }
        }

        $result = $db->query($sql);
        $count = $result ? $result->fetch_assoc()['total'] : 0;
        return (int) $count;
    }

    public static function getFundBalance($cawanganId = null) {
        $db = Database::getInstance()->getConnection();
        
        $sql = "
            SELECT 
                COALESCE(SUM(CASE WHEN t.trans_type = 'Income' THEN t.amount ELSE 0 END), 0) as total_income,
                COALESCE(SUM(CASE WHEN t.trans_type = 'Expense' THEN t.amount ELSE 0 END), 0) as total_expense
            FROM tbl_transaction t
        ";
        
        if ($cawanganId !== null) {
            $sql .= "
                LEFT JOIN tbl_event e ON t.event_id = e.event_id
                LEFT JOIN tbl_user u ON t.recorded_by = u.user_id
                WHERE COALESCE(e.cawangan_id, u.cawangan_id) = ?
            ";
        }
        
        $stmt = $db->prepare($sql);
        if (!$stmt) return 0.00;
        
        if ($cawanganId !== null) {
            $stmt->bind_param("i", $cawanganId);
        }
        
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $income = (float) ($result['total_income'] ?? 0);
        $expense = (float) ($result['total_expense'] ?? 0);
        return $income - $expense;
    }

    public static function formatFundBalance($amount) {
        if ($amount >= 1000000) {
            return 'RM ' . number_format($amount / 1000000, 1) . 'M';
        } elseif ($amount >= 1000) {
            return 'RM ' . number_format($amount / 1000) . 'K';
        } else {
            return 'RM ' . number_format($amount, 2);
        }
    }

    public static function getRecentActivities($limit = 6, $role = 0, $cawanganId = null) {
        $db = Database::getInstance()->getConnection();
        $activities = [];
        
        $pusat_roles = [888, 1, 2, 3, 4, 5, 6, 7];
        $is_pusat = in_array($role, $pusat_roles);

        // 1. Member registrations
        $member_sql = "SELECT m.full_name, m.created_at FROM tbl_member m ";
        if (!$is_pusat && $cawanganId !== null) {
            $member_sql .= "JOIN tbl_user u ON m.created_by = u.user_id WHERE u.cawangan_id = " . (int)$cawanganId;
        }
        $member_sql .= " ORDER BY m.created_at DESC LIMIT $limit";
        
        $res = $db->query($member_sql);
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $activities[] = [
                    'text' => 'Ahli Baru: ' . $row['full_name'],
                    'time' => $row['created_at'],
                    'icon' => 'fa-user-plus',
                    'color' => 'text-kebana-blue'
                ];
            }
        }

        // 2. Documents
        $doc_sql = "SELECT d.doc_name, d.uploaded_at FROM tbl_document d ";
        $doc_sql .= "JOIN tbl_event e ON d.event_id = e.event_id ";
        if (!$is_pusat && $cawanganId !== null) {
            $doc_sql .= " WHERE e.cawangan_id = " . (int)$cawanganId;
        }
        $doc_sql .= " ORDER BY d.uploaded_at DESC LIMIT $limit";
        
        $res = $db->query($doc_sql);
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $activities[] = [
                    'text' => 'Fail Dimuatnaik: ' . $row['doc_name'],
                    'time' => $row['uploaded_at'],
                    'icon' => 'fa-file-arrow-up',
                    'color' => 'text-amber-500'
                ];
            }
        }

        // 3. Transactions
        $trans_sql = "SELECT t.trans_type, t.category, t.amount, t.trans_date as created_at FROM tbl_transaction t ";
        if (!$is_pusat && $cawanganId !== null) {
            $trans_sql .= "JOIN tbl_user u ON t.recorded_by = u.user_id WHERE u.cawangan_id = " . (int)$cawanganId;
        }
        $trans_sql .= " ORDER BY t.trans_id DESC LIMIT $limit";
        
        $res = $db->query($trans_sql);
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $prefix = ($row['trans_type'] === 'Income') ? '+' : '-';
                $activities[] = [
                    'text' => "Aliran Tunai ($prefix RM{$row['amount']}): " . $row['category'],
                    'time' => $row['created_at'],
                    'icon' => 'fa-money-bill-transfer',
                    'color' => ($row['trans_type'] === 'Income') ? 'text-green-600' : 'text-red-500'
                ];
            }
        }

        // Sort by time desc
        usort($activities, function($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });

        return array_slice($activities, 0, $limit);
    }

    public static function getBranchCount() {
        $db = Database::getInstance()->getConnection();
        $result = $db->query("SELECT COUNT(*) as total FROM tbl_cawangan");
        return $result ? (int)$result->fetch_assoc()['total'] : 0;
    }

    public static function getRecentSubmittedEvents($limit = 5) {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT e.*, c.cawangan_name 
                FROM tbl_event e 
                LEFT JOIN tbl_cawangan c ON e.cawangan_id = c.cawangan_id 
                WHERE e.status = 'Submitted' 
                ORDER BY e.event_date DESC 
                LIMIT " . (int)$limit;
        $result = $db->query($sql);
        $rows = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        return $rows;
    }

    public static function calculateCompositeHealthScore($finance, $members, $events) {
        // 1. Financial Health (40%) - Sustainability
        $finScore = 0;
        if ($finance['income'] > 0) {
            // Surplus ratio: (Income - Expense) / Income
            // A healthy org should have at least 10% surplus/reserve ratio
            $surplus = $finance['income'] - $finance['expense'];
            $ratio = $surplus / $finance['income'];
            // Normalize: 15% surplus or more = 100 points
            $finScore = max(0, min(100, ($ratio + 0.1) * 400)); 
        }

        // 2. Member Engagement (30%) - Active Rate
        $memScore = ($members['total'] > 0) ? ($members['active'] / $members['total']) * 100 : 0;

        // 3. Activity Momentum (30%) - Future vs Past
        // Reward having upcoming programs
        $eventScore = 0;
        if ($events['upcoming'] > 0) {
            $eventScore = 100; // Active momentum
        } elseif ($events['past'] > 0) {
            $eventScore = 30; // Legacy only
        }

        $totalScore = ($finScore * 0.4) + ($memScore * 0.3) + ($eventScore * 0.3);
        return round($totalScore, 1);
    }

    /**
     * Get pending actionable items for a user, role-aware.
     * Returns an array of items: [title, subtitle, url, icon, color, time]
     */
    public static function getPendingActionsForUser($role, $cawanganId = null, $limit = 4) {
        $db = Database::getInstance()->getConnection();
        $items = [];
        $pusat_roles = [888, 1, 2, 3, 4, 5, 6, 7];
        $is_pusat = in_array($role, $pusat_roles);

        // --- EVENTS: Pending approvals ---
        if (in_array($role, [888, 1, 2, 3])) {
            // Pusat senior: events submitted for HQ approval
            $sql = "SELECT e.event_id, e.event_title, e.event_date, c.cawangan_name
                    FROM tbl_event e
                    LEFT JOIN tbl_cawangan c ON e.cawangan_id = c.cawangan_id
                    WHERE e.status = 'Submitted'
                    ORDER BY e.created_at DESC LIMIT " . (int)$limit;
            $res = $db->query($sql);
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $items[] = [
                        'title'    => htmlspecialchars($row['event_title']),
                        'subtitle' => 'Menunggu kelulusan' . ($row['cawangan_name'] ? ' • ' . htmlspecialchars($row['cawangan_name']) : ''),
                        'url'      => URL_ROOT . '/events/view/' . $row['event_id'],
                        'icon'     => 'fa-file-signature',
                        'color'    => 'text-amber-500',
                        'badge_color' => 'bg-amber-100 text-amber-700',
                        'badge'    => 'Lulus',
                        'time'     => $row['event_date'],
                    ];
                }
            }
        } elseif ($role == 11) {
            // Pengerusi Cawangan: events pending branch approval
            $sql = "SELECT e.event_id, e.event_title, e.event_date
                    FROM tbl_event e
                    LEFT JOIN tbl_event p ON e.parent_event_id = p.event_id
                    WHERE e.status = 'Pending Branch Approval'
                    AND (e.cawangan_id = ? OR p.cawangan_id = ?)
                    ORDER BY e.created_at DESC LIMIT " . (int)$limit;
            $stmt = $db->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("ii", $cawanganId, $cawanganId);
                $stmt->execute();
                $res = $stmt->get_result();
                while ($row = $res->fetch_assoc()) {
                    $items[] = [
                        'title'       => htmlspecialchars($row['event_title']),
                        'subtitle'    => 'Menunggu pengesahan cawangan anda',
                        'url'         => URL_ROOT . '/events/view/' . $row['event_id'],
                        'icon'        => 'fa-calendar-check',
                        'color'       => 'text-kebana-blue',
                        'badge_color' => 'bg-blue-100 text-blue-700',
                        'badge'       => 'Sahkan',
                        'time'        => $row['event_date'],
                    ];
                }
                $stmt->close();
            }
        } elseif (in_array($role, [22, 33, 44])) {
            // Other Cawangan roles: upcoming events for their branch
            $sql = "SELECT e.event_id, e.event_title, e.event_date
                    FROM tbl_event e
                    WHERE e.cawangan_id = ? AND e.event_date >= CURDATE() AND e.status = 'Approved'
                    ORDER BY e.event_date ASC LIMIT " . (int)$limit;
            $stmt = $db->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("i", $cawanganId);
                $stmt->execute();
                $res = $stmt->get_result();
                while ($row = $res->fetch_assoc()) {
                    $items[] = [
                        'title'       => htmlspecialchars($row['event_title']),
                        'subtitle'    => 'Aktiviti akan datang cawangan',
                        'url'         => URL_ROOT . '/events/view/' . $row['event_id'],
                        'icon'        => 'fa-calendar-star',
                        'color'       => 'text-blue-500',
                        'badge_color' => 'bg-slate-100 text-slate-500',
                        'badge'       => date('d M', strtotime($row['event_date'])),
                        'time'        => $row['event_date'],
                    ];
                }
                $stmt->close();
            }
        }

        // --- FINANCE: Pending entries for Bendahari roles ---
        if (in_array($role, [55, 66])) {
            $sql = "SELECT t.trans_id, t.category, t.amount, t.trans_type, t.trans_date
                    FROM tbl_transaction t
                    JOIN tbl_user u ON t.recorded_by = u.user_id
                    WHERE u.cawangan_id = ?
                    ORDER BY t.trans_id DESC LIMIT " . (int)$limit;
            $stmt = $db->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("i", $cawanganId);
                $stmt->execute();
                $res = $stmt->get_result();
                while ($row = $res->fetch_assoc()) {
                    $isIncome = ($row['trans_type'] === 'Income');
                    $items[] = [
                        'title'       => htmlspecialchars($row['category']),
                        'subtitle'    => ($isIncome ? '+' : '-') . ' RM ' . number_format($row['amount'], 2),
                        'url'         => URL_ROOT . '/finance',
                        'icon'        => $isIncome ? 'fa-circle-arrow-up' : 'fa-circle-arrow-down',
                        'color'       => $isIncome ? 'text-green-600' : 'text-red-500',
                        'badge_color' => $isIncome ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700',
                        'badge'       => $isIncome ? 'Masuk' : 'Keluar',
                        'time'        => $row['trans_date'],
                    ];
                }
                $stmt->close();
            }
        }

        // --- DOCUMENTS: Pending for Pusat Setiausaha / Bendahari ---
        if (in_array($role, [4, 5, 6, 7])) {
            $sql = "SELECT d.doc_id, d.doc_name, d.uploaded_at, e.event_title
                    FROM tbl_document d
                    JOIN tbl_event e ON d.event_id = e.event_id
                    WHERE d.status = 'Pending' AND e.status = 'Submitted'
                    ORDER BY d.uploaded_at DESC LIMIT " . (int)$limit;
            $res = $db->query($sql);
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $items[] = [
                        'title'       => htmlspecialchars($row['doc_name']),
                        'subtitle'    => 'Fail untuk: ' . htmlspecialchars($row['event_title']),
                        'url'         => URL_ROOT . '/documents',
                        'icon'        => 'fa-file-circle-check',
                        'color'       => 'text-purple-600',
                        'badge_color' => 'bg-purple-100 text-purple-700',
                        'badge'       => 'Semak',
                        'time'        => $row['uploaded_at'],
                    ];
                }
            }
        }

        // Sort by time desc and cap
        usort($items, function($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });

        return array_slice($items, 0, $limit);
    }

    public static function formatRelativeTime($datetime) {
        if (!$datetime) return 'Tiada data masa';
        $ts = strtotime($datetime);
        if (!$ts) return 'Format masa ralat';
        
        $diff = time() - $ts;
        if ($diff < 60) return 'Baru sahaja';
        if ($diff < 3600) return floor($diff / 60) . ' minit lepas';
        if ($diff < 86400) return floor($diff / 3600) . ' jam lepas';
        if ($diff < 604800) return floor($diff / 86400) . ' hari lepas';
        
        return date('d/m/Y', $ts);
    }
}
