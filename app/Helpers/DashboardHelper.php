<?php
/**
 * KEBANA Management System - Dashboard Helper
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
            $sql = "SELECT COUNT(*) as total FROM tbl_event WHERE status = '$status' AND cawangan_id = " . (int)$cawanganId;
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

    public static function getFundBalance() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT 
                COALESCE(SUM(CASE WHEN trans_type = 'Income' THEN amount ELSE 0 END), 0) as total_income,
                COALESCE(SUM(CASE WHEN trans_type = 'Expense' THEN amount ELSE 0 END), 0) as total_expense
            FROM tbl_transaction
        ");
        if (!$stmt) return 0.00;
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

    public static function getRecentActivities($limit = 6) {
        $db = Database::getInstance()->getConnection();
        $activities = [];

        // Member registrations
        $res = $db->query("SELECT full_name, created_at FROM tbl_member ORDER BY created_at DESC LIMIT $limit");
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

        // Documents
        $res = $db->query("SELECT doc_name, uploaded_at FROM tbl_document ORDER BY uploaded_at DESC LIMIT $limit");
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

        // Transactions
        $res = $db->query("SELECT trans_type, category, amount, created_at FROM tbl_transaction ORDER BY created_at DESC LIMIT $limit");
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
