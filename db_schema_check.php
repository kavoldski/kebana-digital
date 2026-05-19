<?php
/**
 * Database Schema Check Diagnostic Script
 * File: db_schema_check.php
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>KEBANA Digital - Database Schema Check</h2>";

try {
    require_once __DIR__ . '/bootstrap.php';
    $conn = \App\Core\Database::getInstance()->getConnection();
    
    echo "<h3>1. Tables present in database:</h3>";
    $result = $conn->query("SHOW TABLES");
    if ($result) {
        $tables = [];
        while ($row = $result->fetch_array()) {
            $tables[] = $row[0];
        }
        if (empty($tables)) {
            echo "<p style='color:red;'><b>WARNING: No tables found in the database! Please import sql_main/kebana_db.sql in phpMyAdmin.</b></p>";
        } else {
            echo "<ul>";
            foreach ($tables as $table) {
                // Count rows in the table
                $countResult = $conn->query("SELECT COUNT(*) FROM `$table`");
                $count = $countResult ? $countResult->fetch_row()[0] : 'unknown';
                echo "<li><code>$table</code> - <b>$count</b> rows</li>";
            }
            echo "</ul>";
        }
    } else {
        echo "<p style='color:red;'>Failed to execute SHOW TABLES: " . $conn->error . "</p>";
    }
    
    echo "<h3>2. Dashboard Helper Check:</h3>";
    $helpers = [
        'App\Helpers\MembersHelper',
        'App\Helpers\FinanceHelper',
        'App\Helpers\DashboardHelper',
        'App\Helpers\AuditHelper'
    ];
    
    foreach ($helpers as $helper) {
        if (class_exists($helper)) {
            echo "<p style='color:green;'>Class <code>$helper</code> is loaded.</p>";
        } else {
            echo "<p style='color:red;'>Class <code>$helper</code> could not be loaded!</p>";
        }
    }

} catch (Throwable $e) {
    echo "<div style='background-color:#ffe6e6; border:1px solid red; padding:15px; margin-top:20px;'>";
    echo "<h3 style='color:red; margin-top:0;'>Fatal Error Occurred:</h3>";
    echo "<p><b>Message:</b> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><b>File:</b> " . htmlspecialchars($e->getFile()) . " on line " . $e->getLine() . "</p>";
    echo "</div>";
}
