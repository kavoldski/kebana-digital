<?php
/**
 * KEBANA Management System - Login Authentication Handler
 * File: modules/auth/authenticate.php
 * 
 * Handles user login authentication with new schema
 */

session_start();
require_once '../../includes/dbconnect.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form inputs
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) ? true : false;

    // Basic validation
    if (empty($username) || empty($password)) {
        header('Location: login.php?error=All fields are required');
        exit();
    }

    try {
        // Prepare SQL statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT user_id, username, email, password_hash, role, cawangan_id FROM tbl_user WHERE username = ?");
        
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if user exists
        if ($result->num_rows === 0) {
            header('Location: login.php?error=Invalid username or password');
            exit();
        }

        $user = $result->fetch_assoc();

        // Verify password using bcrypt
        if (!password_verify($password, $user['password_hash'])) {
            header('Location: login.php?error=Invalid username or password');
            exit();
        }

        // Update last login timestamp
        $update_stmt = $conn->prepare("UPDATE tbl_user SET updated_at = NOW() WHERE user_id = ?");
        if ($update_stmt) {
            $update_stmt->bind_param("i", $user['user_id']);
            $update_stmt->execute();
            $update_stmt->close();
        }

        // Set session variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = isset($user['role']) ? (int)$user['role'] : 0;
        $_SESSION['cawangan_id'] = isset($user['cawangan_id']) && $user['cawangan_id'] !== null ? (int)$user['cawangan_id'] : null;
        $_SESSION['logged_in'] = true;

        // Handle "Remember Me" functionality
        if ($remember) {
            $cookie_token = bin2hex(random_bytes(32));
            $cookie_hash = hash('sha256', $cookie_token);
            $expiry = time() + (30 * 24 * 60 * 60); // 30 days

            // Store cookie token in database
            $cookie_stmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE user_id = ?");
            if ($cookie_stmt) {
                $cookie_stmt->bind_param("si", $cookie_hash, $user['user_id']);
                $cookie_stmt->execute();
                $cookie_stmt->close();
            }

            // Set cookie
            setcookie('kebana_remember', $cookie_token, $expiry, '/', '', false, true);
        }

        $stmt->close();

        // Redirect to dashboard
        header('Location: ../../src/php/index.php');
        exit();

    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        header('Location: login.php?error=An error occurred. Please try again.');
        exit();
    }
} else {
    // If accessed directly without POST, redirect to login
    header('Location: login.php');
    exit();
}
?>
