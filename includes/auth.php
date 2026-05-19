<?php
/**
 * KEBANA Digital Management System - Session & Authentication Handler
 * File: includes/auth.php
 * 
 * Validates user session and checks authentication status
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // If not logged in, redirect to login page
    header('Location: ' . URL_ROOT . '/login');
    exit();
}

define('INACTIVITY_TIMEOUT_SECONDS', 900); // 15 minutes

// Validate session variables
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    session_destroy();
    header('Location: ../../modules/auth/login.php?error=Session expired');
    exit();
}

// Inactivity timeout enforcement
if (isset($_SESSION['last_activity']) && (time() - (int)$_SESSION['last_activity']) > INACTIVITY_TIMEOUT_SECONDS) {
    session_unset();
    session_destroy();
    header('Location: ' . URL_ROOT . '/login?error=Logged out due to inactivity');
    exit();
}

// Refresh last activity timestamp for active sessions
$_SESSION['last_activity'] = time();

// Store session variables in convenient variables
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$email = $_SESSION['email'] ?? 'User';
$role = isset($_SESSION['role']) ? (int)$_SESSION['role'] : 0;

$KEBANA_PUSAT_ROLES = [888, 1, 2, 3, 4, 5, 6, 7];
$KEBANA_CAWANGAN_ROLES = [11, 22, 33, 44, 55, 66];
$KEBANA_FINANCE_ROLES = [6, 7, 55, 66];

/**
 * Function to check if user has required role
 * @param string|array $required_role
 * @return bool
 */
function hasRole($required_role) {
    $session_role = $_SESSION['role'] ?? null;
    
    if ($session_role === null) {
        return false;
    }

    if (is_array($required_role)) {
        $required_roles = array_map('intval', $required_role);
        return in_array((int)$session_role, $required_roles, true);
    }

    return (int)$session_role === (int)$required_role;
}

/**
 * Function to check if user is admin
 * @return bool
 */
function isAdmin() {
    return hasRole(888);
}

/**
 * Function to log out user
 */
function logout() {
    session_destroy();
    header('Location: ' . URL_ROOT . '/login?logout=true');
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
