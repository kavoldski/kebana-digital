<?php
/**
 * Database Connection Diagnostic Script
 * File: test_db_prod.php
 */

// Enable full error reporting to screen for diagnostics
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>KEBANA Digital - Database Diagnostics</h2>";

if (!file_exists(__DIR__ . '/config/database.local.php')) {
    echo "<p style='color:red;'><b>ERROR:</b> <code>config/database.local.php</code> does not exist on Hostinger! Please create it in the File Manager.</p>";
} else {
    echo "<p style='color:green;'><b>OK:</b> <code>config/database.local.php</code> is present.</p>";
}

try {
    require_once __DIR__ . '/bootstrap.php';
    echo "<p>Bootstrap loaded successfully.</p>";
    
    echo "<p>Attempting database connection...</p>";
    $db = \App\Core\Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<p style='color:green; font-weight:bold;'>SUCCESS: Connected to the database successfully!</p>";
    echo "<p>MySQL Server Info: " . $conn->server_info . "</p>";
} catch (Throwable $e) {
    echo "<div style='background-color:#ffe6e6; border:1px solid red; padding:15px; margin-top:20px;'>";
    echo "<h3 style='color:red; margin-top:0;'>Fatal Error Occurred:</h3>";
    echo "<p><b>Message:</b> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><b>File:</b> " . htmlspecialchars($e->getFile()) . " on line " . $e->getLine() . "</p>";
    echo "<p><b>Stack Trace:</b></p><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}
