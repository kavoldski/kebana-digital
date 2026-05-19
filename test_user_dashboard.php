<?php
/**
 * Test User Dashboard Diagnostics (Robust Output Flush version)
 * File: test_user_dashboard.php
 */

// Disable all buffering and force immediate output flush
while (ob_get_level() > 0) {
    ob_end_flush();
}
ob_implicit_flush(true);
header('Content-Type: text/plain; charset=utf-8');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "=== KEBANA Digital - Diagnostics Runner ===\n";

try {
    echo "Loading bootstrap.php... ";
    require_once __DIR__ . '/bootstrap.php';
    echo "OK\n";
    
    echo "Connecting to database... ";
    $conn = \App\Core\Database::getInstance()->getConnection();
    echo "OK\n";
    
    echo "Fetching user 'kavoldski'... ";
    $stmt = $conn->prepare("SELECT * FROM tbl_user WHERE username = ?");
    if (!$stmt) {
        throw new Exception("SQL Prepare error: " . $conn->error);
    }
    $u = 'kavoldski';
    $stmt->bind_param('s', $u);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    echo "OK\n";
    
    if (!$user) {
        echo "ERROR: User 'kavoldski' not found!\n";
        exit();
    }
    
    echo "User role: " . $user['role'] . "\n";
    echo "User cawangan_id: " . ($user['cawangan_id'] ?? 'NULL') . "\n";
    
    $current_role = (int)$user['role'];
    $current_cawangan_id = $user['cawangan_id'] ? (int)$user['cawangan_id'] : null;
    $current_user_id = (int)$user['user_id'];
    
    echo "\n--- Running Helper Queries ---\n";
    
    echo "Query 1: MembersHelper::getMemberCount()... ";
    $total_members = \App\Helpers\MembersHelper::getMemberCount();
    echo "SUCCESS: $total_members\n";
    
    echo "Query 2: MembersHelper::getMembersByStatus('Active')... ";
    $active_members = count(\App\Helpers\MembersHelper::getMembersByStatus('Active'));
    echo "SUCCESS: $active_members\n";
    
    echo "Query 3: DashboardHelper::getUpcomingEventsCount... ";
    $upcoming_events = \App\Helpers\DashboardHelper::getUpcomingEventsCount($current_cawangan_id);
    echo "SUCCESS: $upcoming_events\n";
    
    echo "Query 4: DashboardHelper::getPastEventsCount... ";
    $past_events = \App\Helpers\DashboardHelper::getPastEventsCount($current_cawangan_id);
    echo "SUCCESS: $past_events\n";
    
    echo "Query 5: DashboardHelper::getPendingDocumentsCount... ";
    $pending_docs = \App\Helpers\DashboardHelper::getPendingDocumentsCount($current_role, $current_cawangan_id);
    echo "SUCCESS: $pending_docs\n";
    
    echo "Query 6: DashboardHelper::getTotalDocumentsCount()... ";
    $total_docs = \App\Helpers\DashboardHelper::getTotalDocumentsCount();
    echo "SUCCESS: $total_docs\n";
    
    echo "Query 7: DashboardHelper::getFundBalance()... ";
    $fund_balance = \App\Helpers\DashboardHelper::getFundBalance();
    echo "SUCCESS: $fund_balance\n";
    
    echo "Query 8: FinanceHelper::getFinanceTotals()... ";
    $finance_totals = \App\Helpers\FinanceHelper::getFinanceTotals();
    echo "SUCCESS: " . json_encode($finance_totals) . "\n";
    
    echo "Query 9: DashboardHelper::getPendingApprovalsCount... ";
    $pending_approvals = \App\Helpers\DashboardHelper::getPendingApprovalsCount($current_role, $current_cawangan_id);
    echo "SUCCESS: $pending_approvals\n";
    
    echo "Query 10: FinanceHelper::getBranchTotals()... ";
    $branch_finance = in_array($current_role, [888, 1, 2, 3]) ? \App\Helpers\FinanceHelper::getBranchTotals() : [];
    echo "SUCCESS: " . count($branch_finance) . " branches\n";
    
    echo "Query 11: AuditHelper::getRecentLogs(5)... ";
    $recent_activities = \App\Helpers\AuditHelper::getRecentLogs(5);
    echo "SUCCESS: " . count($recent_activities) . " logs\n";
    
    echo "Query 12: ChatHelper::getTotalUnreadCount... ";
    $unread_chats = \App\Helpers\ChatHelper::getTotalUnreadCount($current_user_id);
    echo "SUCCESS: $unread_chats unread chats\n";
    
    $is_presiden = ($current_role === 1);
    if ($is_presiden) {
        echo "Query 13: DashboardHelper::getBranchCount()... ";
        $total_branches = \App\Helpers\DashboardHelper::getBranchCount();
        echo "SUCCESS: $total_branches\n";
        
        echo "Query 14: DashboardHelper::getRecentSubmittedEvents(3)... ";
        $submitted_events = \App\Helpers\DashboardHelper::getRecentSubmittedEvents(3);
        echo "SUCCESS: " . count($submitted_events) . " events\n";
    }
    
    echo "\n=== ALL DIAGNOSTICS COMPLETED PERFECTLY! ===\n";

} catch (Throwable $e) {
    echo "\n!!! FATAL ERROR CAUGHT !!!\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " on line " . $e->getLine() . "\n";
    echo "Stack Trace:\n" . $e->getTraceAsString() . "\n";
}
