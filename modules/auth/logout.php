<?php
/**
 * KEBANA Management System - Logout Handler
 * File: modules/auth/logout.php
 * 
 * Handles user logout
 */

session_start();

// Destroy session
session_destroy();

// Clear cookies if remember me was set
if (isset($_COOKIE['kebana_remember'])) {
    setcookie('kebana_remember', '', time() - 3600, '/', '', false, true);
}

// Redirect to login page
header('Location: login.php?logout=true');
exit();
?>
