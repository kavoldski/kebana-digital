<?php
/**
 * KEBANA Digital Management System - Logout Handler
 * File: modules/auth/logout.php
 * 
 * Handles user logout
 */

if (!defined('APP_ROOT')) {
    require_once dirname(__DIR__, 2) . '/bootstrap.php';
}

// Destroy session
session_destroy();

// Clear cookies if remember me was set
if (isset($_COOKIE['kebana_remember'])) {
    setcookie('kebana_remember', '', time() - 3600, '/', '', false, true);
}

// Redirect to login page
header('Location: /kebana-digital/login?logout=true');
exit();
?>
