<?php
/**
 * KEBANA Finance Helper Functions
 * Provides utility functions for financial data retrieval and calculations
 */

/**
 * Get overall financial totals (income, expense, balance)
 * @param object $conn Database connection
 * @return array Associative array with total_income, total_expense, balance
 */
function getFinanceTotals($conn) {
    $stmt = $conn->prepare("
        SELECT 
            COALESCE(SUM(CASE WHEN trans_type = 'Income' THEN amount ELSE 0 END), 0) as total_income,
            COALESCE(SUM(CASE WHEN trans_type = 'Expense' THEN amount ELSE 0 END), 0) as total_expense
        FROM tbl_transaction
    ");
    
    if (!$stmt) {
        return ['total_income' => 0, 'total_expense' => 0, 'balance' => 0];
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    $total_income = (float)($row['total_income'] ?? 0);
    $total_expense = (float)($row['total_expense'] ?? 0);
    
    return [
        'total_income' => $total_income,
        'total_expense' => $total_expense,
        'balance' => $total_income - $total_expense
    ];
}

/**
 * Get recent transactions (limited)
 * @param object $conn Database connection
 * @param int $limit Number of records to retrieve
 * @return array Array of transaction records
 */
function getRecentTransactions($conn, $limit = 10) {
    $stmt = $conn->prepare("
        SELECT trans_id, trans_date, trans_type, category, amount, recorded_by, created_at 
        FROM tbl_transaction 
        ORDER BY created_at DESC 
        LIMIT ?
    ");
    
    if (!$stmt) {
        return [];
    }
    
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $transactions = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    return $transactions;
}

/**
 * Get financial summary by category
 * @param object $conn Database connection
 * @param string $type 'Income' or 'Expense'
 * @return array Array of categories with totals
 */
function getFinanceSummaryByCategory($conn, $type = 'Expense') {
    $stmt = $conn->prepare("
        SELECT category, SUM(amount) as total, COUNT(*) as count
        FROM tbl_transaction
        WHERE trans_type = ?
        GROUP BY category
        ORDER BY total DESC
    ");
    
    if (!$stmt) {
        return [];
    }
    
    $stmt->bind_param("s", $type);
    $stmt->execute();
    $result = $stmt->get_result();
    $summary = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    return $summary;
}

/**
 * Get transactions for a specific date range
 * @param object $conn Database connection
 * @param string $from_date Start date (YYYY-MM-DD)
 * @param string $to_date End date (YYYY-MM-DD)
 * @return array Totals for the period
 */
function getTransactionsByDateRange($conn, $from_date, $to_date) {
    $stmt = $conn->prepare("
        SELECT 
            COALESCE(SUM(CASE WHEN trans_type = 'Income' THEN amount ELSE 0 END), 0) as total_income,
            COALESCE(SUM(CASE WHEN trans_type = 'Expense' THEN amount ELSE 0 END), 0) as total_expense,
            COUNT(*) as count
        FROM tbl_transaction
        WHERE trans_date >= ? AND trans_date <= ?
    ");
    
    if (!$stmt) {
        return ['total_income' => 0, 'total_expense' => 0, 'count' => 0];
    }
    
    $stmt->bind_param("ss", $from_date, $to_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row;
}
