<?php
/**
 * KEBANA Management System - Dashboard Helper Functions
 * File: includes/dashboard_helper.php
 *
 * Helper functions for dashboard statistics and KPIs
 */

/**
 * Get count of upcoming events (events with date today or in the future)
 *
 * @param mysqli $conn Database connection
 * @return int Number of upcoming events
 */
function getUpcomingEventsCount($conn) {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM tbl_event WHERE event_date >= CURDATE()");
    if (!$stmt) {
        return 0;
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['total'] ?? 0;
    $stmt->close();

    return (int) $count;
}

/**
 * Get count of pending documents
 *
 * @param mysqli $conn Database connection
 * @return int Number of pending documents
 */
function getPendingDocumentsCount($conn) {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM tbl_document WHERE status = 'Pending'");
    if (!$stmt) {
        return 0;
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['total'] ?? 0;
    $stmt->close();

    return (int) $count;
}

/**
 * Get current fund balance (Total Income - Total Expenses)
 *
 * @param mysqli $conn Database connection
 * @return float Current fund balance
 */
function getFundBalance($conn) {
    $stmt = $conn->prepare("
        SELECT 
            COALESCE(SUM(CASE WHEN trans_type = 'Income' THEN amount ELSE 0 END), 0) as total_income,
            COALESCE(SUM(CASE WHEN trans_type = 'Expense' THEN amount ELSE 0 END), 0) as total_expense
        FROM tbl_transaction
    ");
    if (!$stmt) {
        return 0.00;
    }

    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $income = (float) ($result['total_income'] ?? 0);
    $expense = (float) ($result['total_expense'] ?? 0);

    return $income - $expense;
}

/**
 * Format fund balance for display (e.g., RM 54K, RM 1.2M, RM 5,400.00)
 *
 * @param float $amount Fund balance amount
 * @return string Formatted balance string
 */
function formatFundBalance($amount) {
    if ($amount >= 1000000) {
        return 'RM ' . number_format($amount / 1000000, 1) . 'M';
    } elseif ($amount >= 1000) {
        return 'RM ' . number_format($amount / 1000) . 'K';
    } else {
        return 'RM ' . number_format($amount, 2);
    }
}

