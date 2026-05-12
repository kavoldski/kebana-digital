<?php
/**
 * KEBANA Management System - Finance Helper
 * File: app/Helpers/FinanceHelper.php
 */

namespace App\Helpers;

use App\Core\Database;
use App\Helpers\NotificationHelper;

class FinanceHelper {
    public static function getFinanceTotals() {
        return self::getTotals();
    }

    public static function getTotals() {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT 
                    SUM(CASE WHEN trans_type = 'Income' THEN amount ELSE 0 END) as income, 
                    SUM(CASE WHEN trans_type = 'Expense' THEN amount ELSE 0 END) as expense 
                FROM tbl_transaction";
        $result = $db->query($sql);
        $totals = ['income' => 0, 'expense' => 0, 'balance' => 0];
        if ($row = $result->fetch_assoc()) {
            $totals['income'] = (float)$row['income'];
            $totals['expense'] = (float)$row['expense'];
            $totals['total_income'] = $totals['income'];
            $totals['total_expense'] = $totals['expense'];
            $totals['balance'] = $totals['income'] - $totals['expense'];
        }
        return $totals;
    }

    public static function getRecentTransactions($limit = 10) {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT t.*, e.event_title, u.username as recorder_name
                FROM tbl_transaction t
                LEFT JOIN tbl_event e ON t.event_id = e.event_id
                LEFT JOIN tbl_user u ON t.recorded_by = u.user_id
                ORDER BY t.trans_date DESC, t.trans_id DESC
                LIMIT ?";
        $stmt = $db->prepare($sql);
        $rows = [];
        if ($stmt) {
            $stmt->bind_param("i", $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            $stmt->close();
        }
        return $rows;
    }

    public static function getCategories() {
        $db = Database::getInstance()->getConnection();
        $result = $db->query("SELECT DISTINCT category FROM tbl_transaction ORDER BY category");
        $cats = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $cats[] = $row['category'];
            }
        }
        return $cats;
    }

    public static function addTransaction($data, $userId, $cawanganId = null) {
        $db = Database::getInstance()->getConnection();
        $type = $data['trans_type'];
        $amount = (float)$data['amount'];
        $category = $data['category'];
        $date = $data['trans_date'];
        $payment_mode = $data['payment_mode'] ?? 'Cash';
        $event_id = !empty($data['event_id']) ? (int)$data['event_id'] : null;
        $month_label = strtoupper(date('M', strtotime($date)));

        // Security check for Branch Finance roles
        if ($event_id !== null && $cawanganId !== null) {
            $stmt_check = $db->prepare("SELECT event_id FROM tbl_event WHERE event_id = ? AND (cawangan_id = ? OR event_level = 'MASTER')");
            $stmt_check->bind_param("ii", $event_id, $cawanganId);
            $stmt_check->execute();
            $result = $stmt_check->get_result();
            if ($result->num_rows === 0) {
                return false; // Unauthorized event selection
            }
            $stmt_check->close();
        }

        $sql = "INSERT INTO tbl_transaction (trans_type, amount, category, trans_date, payment_mode, event_id, month_label, recorded_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("sdssisss", $type, $amount, $category, $date, $payment_mode, $event_id, $month_label, $userId);
            $success = $stmt->execute();
            $stmt->close();

            if ($success) {
                $formattedAmount = number_format($amount, 2);
                NotificationHelper::notifyRoles([888, 6, 55], 'transaction_added', 'Transaksi Kewangan Baru', "Satu transaksi $type berjumlah RM$formattedAmount (Kategori: $category) telah direkodkan.", "finance");
            }

            return $success;
        }
        return false;
    }

    public static function getBudgetSummary($filters = []) {
        $db = Database::getInstance()->getConnection();
        $where = "1=1";
        $params = [];
        $types = "";

        if (!empty($filters['year'])) {
            $where .= " AND YEAR(e.event_date) = ?";
            $params[] = (int)$filters['year'];
            $types .= "i";
        }
        if (!empty($filters['search'])) {
            $where .= " AND e.event_title LIKE ?";
            $params[] = "%" . $filters['search'] . "%";
            $types .= "s";
        }

        $sql = "SELECT
                    e.event_id,
                    e.event_title,
                    e.event_date,
                    COALESCE(e.budget_est, 0) AS planned_budget,
                    COALESCE(SUM(CASE WHEN t.trans_type = 'Expense' THEN t.amount ELSE 0 END), 0) AS actual_expense
                FROM tbl_event e
                LEFT JOIN tbl_transaction t ON t.event_id = e.event_id
                WHERE $where
                GROUP BY e.event_id, e.event_title, e.event_date, e.budget_est
                ORDER BY e.event_date DESC";
        
        $stmt = $db->prepare($sql);
        $rows = [];
        if ($stmt) {
            if (!empty($params)) $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            $stmt->close();
        }
        return $rows;
    }
}
