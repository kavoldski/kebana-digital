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

    public static function getCategories($type = null) {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT DISTINCT category FROM tbl_transaction";
        if ($type) {
            $sql .= " WHERE trans_type = '" . $db->real_escape_string($type) . "'";
        }
        $sql .= " ORDER BY category";
        $result = $db->query($sql);
        $cats = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $cats[] = $row['category'];
            }
        }
        return $cats;
    }

    public static function addTransaction($data, $userId, $cawanganId = null, $receiptFile = null) {
        $db = Database::getInstance()->getConnection();
        $type = $data['trans_type'];
        $amount = (float)$data['amount'];
        $category = $data['category'];
        $date = $data['trans_date'];
        $payment_mode = $data['payment_mode'] ?? 'Cash';
        $event_id = !empty($data['event_id']) ? (int)$data['event_id'] : null;
        $month_label = strtoupper(date('M', strtotime($date)));
        $receipt_path = null;

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

        // Handle Receipt Upload
        if ($receiptFile && $receiptFile['error'] === UPLOAD_ERR_OK) {
            $receipt_path = self::handleReceiptUpload($receiptFile);
        }

        $sql = "INSERT INTO tbl_transaction (trans_type, amount, category, trans_date, payment_mode, receipt_path, event_id, month_label, recorded_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("sdsssssis", $type, $amount, $category, $date, $payment_mode, $receipt_path, $event_id, $month_label, $userId);
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

    public static function handleReceiptUpload($file) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
        if (!in_array($ext, $allowed)) return null;

        $uploadDir = APP_ROOT . '/uploads/receipts/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $newName = 'receipt_' . time() . '_' . uniqid() . '.' . $ext;
        $target = $uploadDir . $newName;

        if (move_uploaded_file($file['tmp_name'], $target)) {
            return 'uploads/receipts/' . $newName;
        }
        return null;
    }

    /**
     * Monthly Income vs Expense for the current year (for bar chart).
     * Returns array with 12 entries Jan–Dec.
     */
    public static function getMonthlyChartData($year = null) {
        $db = Database::getInstance()->getConnection();
        $year = $year ?? date('Y');
        $sql = "SELECT 
                    MONTH(trans_date) as month_num,
                    SUM(CASE WHEN trans_type = 'Income'  THEN amount ELSE 0 END) as income,
                    SUM(CASE WHEN trans_type = 'Expense' THEN amount ELSE 0 END) as expense
                FROM tbl_transaction
                WHERE YEAR(trans_date) = ?
                GROUP BY MONTH(trans_date)
                ORDER BY MONTH(trans_date)";
        $stmt = $db->prepare($sql);
        $months = array_fill(1, 12, ['income' => 0, 'expense' => 0]);
        if ($stmt) {
            $stmt->bind_param("i", $year);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $months[(int)$row['month_num']] = [
                    'income'  => (float)$row['income'],
                    'expense' => (float)$row['expense'],
                ];
            }
            $stmt->close();
        }
        return $months;
    }

    /**
     * Chronological transactions for the running balance line chart.
     */
    public static function getRunningBalanceData($year = null) {
        $db = Database::getInstance()->getConnection();
        $year = $year ?? date('Y');
        $sql = "SELECT trans_date, trans_type, amount
                FROM tbl_transaction
                WHERE YEAR(trans_date) = ?
                ORDER BY trans_date ASC, trans_id ASC";
        $stmt = $db->prepare($sql);
        $rows = [];
        if ($stmt) {
            $stmt->bind_param("i", $year);
            $stmt->execute();
            $result = $stmt->get_result();
            $running = 0;
            while ($row = $result->fetch_assoc()) {
                $running += ($row['trans_type'] === 'Income') ? $row['amount'] : -$row['amount'];
                $rows[] = [
                    'date'    => $row['trans_date'],
                    'balance' => round($running, 2),
                ];
            }
            $stmt->close();
        }
        return $rows;
    }

    /**
     * Expense breakdown by category for the donut chart (top 8 + Others).
     */
    public static function getCategoryBreakdown($year = null) {
        $db = Database::getInstance()->getConnection();
        $year = $year ?? date('Y');
        $sql = "SELECT category, SUM(amount) as total
                FROM tbl_transaction
                WHERE trans_type = 'Expense' AND YEAR(trans_date) = ?
                GROUP BY category
                ORDER BY total DESC
                LIMIT 8";
        $stmt = $db->prepare($sql);
        $rows = [];
        if ($stmt) {
            $stmt->bind_param("i", $year);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $rows[] = ['label' => $row['category'], 'total' => (float)$row['total']];
            }
            $stmt->close();
        }
        return $rows;
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
                    COALESCE(e.event_level, 'MASTER') AS event_level,
                    e.parent_event_id,
                    c.cawangan_name,
                    COALESCE(e.budget_est, 0) AS planned_budget,
                    COALESCE(SUM(CASE WHEN t.trans_type = 'Expense' THEN t.amount ELSE 0 END), 0) AS actual_expense,
                    COALESCE(SUM(CASE WHEN t.trans_type = 'Income'  THEN t.amount ELSE 0 END), 0) AS actual_income
                FROM tbl_event e
                LEFT JOIN tbl_transaction t ON t.event_id = e.event_id
                LEFT JOIN tbl_cawangan c ON e.cawangan_id = c.cawangan_id
                WHERE $where
                GROUP BY e.event_id, e.event_title, e.event_date, e.budget_est,
                         e.event_level, e.parent_event_id, c.cawangan_name
                ORDER BY COALESCE(e.event_level,'MASTER') ASC, e.event_date DESC";
        
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

    /**
     * All transactions linked to a specific event, with recorder name.
     * Used by the event financial drilldown page (Phase B).
     */
    public static function getTransactionsByEvent($eventId) {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT t.*, u.username AS recorder_name
                FROM tbl_transaction t
                LEFT JOIN tbl_user u ON t.recorded_by = u.user_id
                WHERE t.event_id = ?
                ORDER BY t.trans_date ASC, t.trans_id ASC";
        $stmt = $db->prepare($sql);
        $rows = [];
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
     * Category-level breakdown of expenses for a single event.
     * Used for the event drilldown donut chart.
     */
    public static function getEventCategoryBreakdown($eventId) {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT category, SUM(amount) AS total
                FROM tbl_transaction
                WHERE event_id = ? AND trans_type = 'Expense'
                GROUP BY category
                ORDER BY total DESC";
        $stmt = $db->prepare($sql);
        $rows = [];
        if ($stmt) {
            $stmt->bind_param("i", $eventId);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $rows[] = ['label' => $row['category'], 'total' => (float)$row['total']];
            }
            $stmt->close();
        }
        return $rows;
    }
}
