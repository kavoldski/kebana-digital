<?php
/**
 * KEBANA Management System - Dashboard Helper
 * File: app/Helpers/DashboardHelper.php
 */

namespace App\Helpers;

use App\Core\Database;

class DashboardHelper {
    public static function getUpcomingEventsCount() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM tbl_event WHERE event_date >= CURDATE()");
        if (!$stmt) return 0;
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_assoc()['total'] ?? 0;
        $stmt->close();
        return (int) $count;
    }

    public static function getPastEventsCount() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM tbl_event WHERE event_date < CURDATE()");
        if (!$stmt) return 0;
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_assoc()['total'] ?? 0;
        $stmt->close();
        return (int) $count;
    }

    public static function getPendingDocumentsCount() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM tbl_document WHERE status = 'Pending'");
        if (!$stmt) return 0;
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_assoc()['total'] ?? 0;
        $stmt->close();
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

    public static function getPendingApprovalsCount() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM tbl_event WHERE status = 'Submitted'");
        if (!$stmt) return 0;
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_assoc()['total'] ?? 0;
        $stmt->close();
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
