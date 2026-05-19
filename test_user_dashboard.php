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
    echo "SUCCESS: $total_members\n";
    
    echo "2. MembersHelper::getMembersByStatus('Active')... ";
    $active_members = count(\App\Helpers\MembersHelper::getMembersByStatus('Active'));
    echo "SUCCESS: $active_members\n";
    
    echo "3. DashboardHelper::getUpcomingEventsCount... ";
    $upcoming_events = \App\Helpers\DashboardHelper::getUpcomingEventsCount($current_cawangan_id);
    echo "SUCCESS: $upcoming_events\n";
    
    echo "4. DashboardHelper::getPastEventsCount... ";
    $past_events = \App\Helpers\DashboardHelper::getPastEventsCount($current_cawangan_id);
    echo "SUCCESS: $past_events\n";
    
    echo "5. DashboardHelper::getPendingDocumentsCount... ";
    $pending_docs = \App\Helpers\DashboardHelper::getPendingDocumentsCount($current_role, $current_cawangan_id);
    echo "SUCCESS: $pending_docs\n";
    
    echo "6. DashboardHelper::getTotalDocumentsCount()... ";
    $total_docs = \App\Helpers\DashboardHelper::getTotalDocumentsCount();
    echo "SUCCESS: $total_docs\n";
    
    echo "7. DashboardHelper::getFundBalance()... ";
    $fund_balance = \App\Helpers\DashboardHelper::getFundBalance();
    echo "SUCCESS: $fund_balance\n";
    
    echo "8. FinanceHelper::getFinanceTotals()... ";
    $finance_totals = \App\Helpers\FinanceHelper::getFinanceTotals();
    echo "SUCCESS: " . json_encode($finance_totals) . "\n";
    
    echo "9. DashboardHelper::getPendingApprovalsCount... ";
    $pending_approvals = \App\Helpers\DashboardHelper::getPendingApprovalsCount($current_role, $current_cawangan_id);
    echo "SUCCESS: $pending_approvals\n";
    
    echo "10. FinanceHelper::getBranchTotals()... ";
    $branch_finance = in_array($current_role, [888, 1, 2, 3]) ? \App\Helpers\FinanceHelper::getBranchTotals() : [];
    echo "SUCCESS: " . count($branch_finance) . " branches\n";
    
    echo "11. AuditHelper::getRecentLogs(5)... ";
    $recent_activities = \App\Helpers\AuditHelper::getRecentLogs(5);
    echo "SUCCESS: " . count($recent_activities) . " logs\n";
    
    $is_presiden = ($current_role === 1);
    if ($is_presiden) {
        echo "12. DashboardHelper::getBranchCount()... ";
        $total_branches = \App\Helpers\DashboardHelper::getBranchCount();
        echo "SUCCESS: $total_branches\n";
        
        echo "13. DashboardHelper::getRecentSubmittedEvents(3)... ";
        $submitted_events = \App\Helpers\DashboardHelper::getRecentSubmittedEvents(3);
        echo "SUCCESS: " . count($submitted_events) . " events\n";
    }
    
    echo "ALL DIAGNOSTICS COMPLETED PERFECTLY! NO QUERY FAILED.\n";

} catch (Throwable $e) {
    echo "<div style='background-color:#ffe6e6; border:1px solid red; padding:15px; margin-top:20px;'>";
    echo "<h3 style='color:red; margin-top:0;'>Fatal Error Occurred:</h3>";
    echo "<p><b>Message:</b> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><b>File:</b> " . htmlspecialchars($e->getFile()) . " on line " . $e->getLine() . "</p>";
    echo "<p><b>Stack Trace:</b></p><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}
