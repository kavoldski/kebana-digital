<?php
/**
 * KEBANA Management System - Session & Authentication Handler
 * File: includes/auth.php
 * 
 * Validates user session and checks authentication status
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // If not logged in, redirect to login page
    header('Location: ../../modules/auth/login.php');
    exit();
}

// Validate session variables
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    session_destroy();
    header('Location: ../../modules/auth/login.php?error=Session expired');
    exit();
}

// Store session variables in convenient variables
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$email = $_SESSION['email'] ?? 'User';
$role = $_SESSION['role'];

/**
 * Function to check if user has required role
 * @param string|array $required_role
 * @return bool
 */
function hasRole($required_role) {
    global $role;
    if (is_array($required_role)) {
        return in_array($role, $required_role);
    }
    return $role === $required_role;
}

/**
 * Function to check if user is admin
 * @return bool
 */
function isAdmin() {
    return hasRole('Super Admin');
}

/**
 * Function to log out user
 */
function logout() {
    session_destroy();
    header('Location: ../../modules/auth/login.php?logout=true');
    exit();
}

/**
 * Function to log user activity to audit log
 * @param string $action
 * @param string $table_name
 * @param int $record_id
 * @param string $old_value
 * @param string $new_value
 */
function logAudit($action, $table_name = '', $record_id = null, $old_value = '', $new_value = '') {
    global $conn, $user_id;
    
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    
    $stmt = $conn->prepare("
        INSERT INTO audit_log (user_id, action, table_name, record_id, old_value, new_value, ip_address) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    if ($stmt) {
        $stmt->bind_param("issiiss", $user_id, $action, $table_name, $record_id, $old_value, $new_value, $ip_address);
        $stmt->execute();
        $stmt->close();
    }
}
?>
