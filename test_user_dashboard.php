<?php
/**
 * Test User Dashboard Diagnostics
 * File: test_user_dashboard.php
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>KEBANA Digital - 'kavoldski' Session Simulation</h2>";

try {
    require_once __DIR__ . '/bootstrap.php';
    $conn = \App\Core\Database::getInstance()->getConnection();
    
    // Fetch kavoldski info
    $stmt = $conn->prepare("SELECT * FROM tbl_user WHERE username = ?");
    if (!$stmt) {
        throw new Exception("SQL Prepare error: " . $conn->error);
    }
    $u = 'kavoldski';
    $stmt->bind_param('s', $u);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$user) {
        echo "<p style='color:red;'><b>ERROR:</b> User 'kavoldski' not found!</p>";
        exit();
    }
    
    echo "<p><b>User Data:</b></p><pre>";
    print_r($user);
    echo "</pre>";
    
    $current_role = (int)$user['role'];
    $current_cawangan_id = $user['cawangan_id'] ? (int)$user['cawangan_id'] : null;
    
    echo "<h3>Executing Dashboard Helper Queries:</h3>";
    
    echo "1. MembersHelper::getMemberCount()... ";
    $total_members = \App\Helpers\MembersHelper::getMemberCount();
    echo "<span style='color:green;'>SUCCESS: $total_members</span><br/>";
    
    echo "2. MembersHelper::getMembersByStatus('Active')... ";
    $active_members = count(\App\Helpers\MembersHelper::getMembersByStatus('Active'));
    echo "<span style='color:green;'>SUCCESS: $active_members</span><br/>";
    
    echo "3. DashboardHelper::getUpcomingEventsCount($current_cawangan_id)... ";
    $upcoming_events = \App\Helpers\DashboardHelper::getUpcomingEventsCount($current_cawangan_id);
    echo "<span style='color:green;'>SUCCESS: $upcoming_events</span><br/>";
    
    echo "4. DashboardHelper::getPastEventsCount($current_cawangan_id)... ";
    $past_events = \App\Helpers\DashboardHelper::getPastEventsCount($current_cawangan_id);
    echo "<span style='color:green;'>SUCCESS: $past_events</span><br/>";
    
    echo "5. DashboardHelper::getPendingDocumentsCount($current_role, $current_cawangan_id)... ";
    $pending_docs = \App\Helpers\DashboardHelper::getPendingDocumentsCount($current_role, $current_cawangan_id);
    echo "<span style='color:green;'>SUCCESS: $pending_docs</span><br/>";
    
    echo "6. DashboardHelper::getTotalDocumentsCount()... ";
    $total_docs = \App\Helpers\DashboardHelper::getTotalDocumentsCount();
    echo "<span style='color:green;'>SUCCESS: $total_docs</span><br/>";
    
    echo "7. DashboardHelper::getFundBalance()... ";
    $fund_balance = \App\Helpers\DashboardHelper::getFundBalance();
    echo "<span style='color:green;'>SUCCESS: $fund_balance</span><br/>";
    
    echo "8. FinanceHelper::getFinanceTotals()... ";
    $finance_totals = \App\Helpers\FinanceHelper::getFinanceTotals();
    echo "<span style='color:green;'>SUCCESS: " . json_encode($finance_totals) . "</span><br/>";
    
    echo "9. DashboardHelper::getPendingApprovalsCount($current_role, $current_cawangan_id)... ";
    $pending_approvals = \App\Helpers\DashboardHelper::getPendingApprovalsCount($current_role, $current_cawangan_id);
    echo "<span style='color:green;'>SUCCESS: $pending_approvals</span><br/>";
    
    echo "10. FinanceHelper::getBranchTotals()... ";
    $branch_finance = in_array($current_role, [888, 1, 2, 3]) ? \App\Helpers\FinanceHelper::getBranchTotals() : [];
    echo "<span style='color:green;'>SUCCESS: " . count($branch_finance) . " branches</span><br/>";
    
    echo "11. AuditHelper::getRecentLogs(5)... ";
    $recent_activities = \App\Helpers\AuditHelper::getRecentLogs(5);
    echo "<span style='color:green;'>SUCCESS: " . count($recent_activities) . " logs</span><br/>";
    
    $is_presiden = ($current_role === 1);
    if ($is_presiden) {
        echo "12. DashboardHelper::getBranchCount()... ";
        $total_branches = \App\Helpers\DashboardHelper::getBranchCount();
        echo "<span style='color:green;'>SUCCESS: $total_branches</span><br/>";
        
        echo "13. DashboardHelper::getRecentSubmittedEvents(3)... ";
        $submitted_events = \App\Helpers\DashboardHelper::getRecentSubmittedEvents(3);
        echo "<span style='color:green;'>SUCCESS: " . count($submitted_events) . " events</span><br/>";
    }
    
    echo "<h3 style='color:green;'>ALL DIAGNOSTICS COMPLETED PERFECTLY! NO QUERY FAILED.</h3>";
} catch (Throwable $e) {
    echo "<div style='background-color:#ffe6e6; border:1px solid red; padding:15px; margin-top:20px;'>";
    echo "<h3 style='color:red; margin-top:0;'>Fatal Error Occurred:</h3>";
    echo "<p><b>Message:</b> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><b>File:</b> " . htmlspecialchars($e->getFile()) . " on line " . $e->getLine() . "</p>";
    echo "<p><b>Stack Trace:</b></p><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}
