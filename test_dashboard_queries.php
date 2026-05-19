<?php
/**
 * Test Dashboard Queries Diagnostic Script
 * File: test_dashboard_queries.php
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>KEBANA Digital - Dashboard Queries Check</h2>";

try {
    require_once __DIR__ . '/bootstrap.php';
    
    // Simulate a typical login session
    $_SESSION['username'] = 'admin';
    $_SESSION['role'] = 888; // Super Admin
    $_SESSION['cawangan_id'] = null;
    
    $current_role = 888;
    $current_cawangan_id = null;
    
    echo "<p>Running MembersHelper::getMemberCount()...</p>";
    $total_members = \App\Helpers\MembersHelper::getMemberCount();
    echo "<p style='color:green;'>SUCCESS: Member Count = $total_members</p>";
    
    echo "<p>Running MembersHelper::getMembersByStatus('Active')...</p>";
    $active_members = count(\App\Helpers\MembersHelper::getMembersByStatus('Active'));
    echo "<p style='color:green;'>SUCCESS: Active Members Count = $active_members</p>";
    
    echo "<p>Running DashboardHelper::getUpcomingEventsCount()...</p>";
    $upcoming_events = \App\Helpers\DashboardHelper::getUpcomingEventsCount($current_cawangan_id);
    echo "<p style='color:green;'>SUCCESS: Upcoming Events = $upcoming_events</p>";
    
    echo "<p>Running DashboardHelper::getPastEventsCount()...</p>";
    $past_events = \App\Helpers\DashboardHelper::getPastEventsCount($current_cawangan_id);
    echo "<p style='color:green;'>SUCCESS: Past Events = $past_events</p>";
    
    echo "<p>Running DashboardHelper::getPendingDocumentsCount()...</p>";
    $pending_docs = \App\Helpers\DashboardHelper::getPendingDocumentsCount($current_role, $current_cawangan_id);
    echo "<p style='color:green;'>SUCCESS: Pending Docs = $pending_docs</p>";
    
    echo "<p>Running DashboardHelper::getTotalDocumentsCount()...</p>";
    $total_docs = \App\Helpers\DashboardHelper::getTotalDocumentsCount();
    echo "<p style='color:green;'>SUCCESS: Total Docs = $total_docs</p>";
    
    echo "<p>Running DashboardHelper::getFundBalance()...</p>";
    $fund_balance = \App\Helpers\DashboardHelper::getFundBalance();
    echo "<p style='color:green;'>SUCCESS: Fund Balance = $fund_balance</p>";
    
    echo "<p>Running FinanceHelper::getFinanceTotals()...</p>";
    $finance_totals = \App\Helpers\FinanceHelper::getFinanceTotals();
    echo "<p style='color:green;'>SUCCESS: Finance Totals = Income: " . $finance_totals['total_income'] . ", Expense: " . $finance_totals['total_expense'] . "</p>";
    
    echo "<p>Running DashboardHelper::getPendingApprovalsCount()...</p>";
    $pending_approvals = \App\Helpers\DashboardHelper::getPendingApprovalsCount($current_role, $current_cawangan_id);
    echo "<p style='color:green;'>SUCCESS: Pending Approvals = $pending_approvals</p>";
    
    echo "<p>Running FinanceHelper::getBranchTotals()...</p>";
    $branch_finance = \App\Helpers\FinanceHelper::getBranchTotals();
    echo "<p style='color:green;'>SUCCESS: Branch Totals Count = " . count($branch_finance) . "</p>";
    
    echo "<p>Running AuditHelper::getRecentLogs(5)...</p>";
    $recent_activities = \App\Helpers\AuditHelper::getRecentLogs(5);
    echo "<p style='color:green;'>SUCCESS: Recent activities fetched (" . count($recent_activities) . " items)</p>";
    
    echo "<p style='color:green; font-weight:bold;'>ALL QUERIES COMPLETED SUCCESSFULLY!</p>";

} catch (Throwable $e) {
    echo "<div style='background-color:#ffe6e6; border:1px solid red; padding:15px; margin-top:20px;'>";
    echo "<h3 style='color:red; margin-top:0;'>Fatal Error Occurred:</h3>";
    echo "<p><b>Message:</b> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><b>File:</b> " . htmlspecialchars($e->getFile()) . " on line " . $e->getLine() . "</p>";
    echo "<p><b>Stack Trace:</b></p><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}
