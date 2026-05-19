<?php
/**
 * Diagnostic tool to fetch users and roles
 * File: get_users.php
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    require_once __DIR__ . '/bootstrap.php';
    $conn = \App\Core\Database::getInstance()->getConnection();
    
    $result = $conn->query("SELECT user_id, username, email, role, cawangan_id, status FROM tbl_user");
    echo "<h2>Registered Users:</h2><ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li>ID: {$row['user_id']} | Username: <b>{$row['username']}</b> | Email: {$row['email']} | Role: <b>{$row['role']}</b> | Cawangan: {$row['cawangan_id']} | Status: {$row['status']}</li>";
    }
    echo "</ul>";
} catch (Throwable $e) {
    echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
}
