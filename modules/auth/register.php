<?php
/**
 * KEBANA Management System - User Registration Handler
 * File: modules/auth/register.php
 * 
 * Handles user registration and account creation with new schema
 */

session_start();
require_once '../../includes/dbconnect.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form inputs
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = trim($_POST['role'] ?? 'Treasurer');
    $terms = isset($_POST['terms']) ? true : false;

    // Validation errors array
    $errors = [];

    // Validate inputs
    if (empty($username)) {
        $errors[] = 'Username is required';
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = 'Username must be between 3 and 50 characters';
    }

    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }

    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    }

    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    }

    if (!$terms) {
        $errors[] = 'You must agree to the Terms and Conditions';
    }

    if (!in_array($role, ['Super Admin', 'Secretary', 'Treasurer'])) {
        $errors[] = 'Invalid role selected';
    }

    // If there are validation errors, redirect back to sign-up with error message
    if (!empty($errors)) {
        $error_message = implode(', ', $errors);
        header('Location: sign_up.php?error=' . urlencode($error_message));
        exit();
    }

    try {
        // Check if username already exists
        $check_stmt = $conn->prepare("SELECT user_id FROM tbl_user WHERE username = ?");
        
        if (!$check_stmt) {
            throw new Exception("Database error: " . $conn->error);
        }

        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $check_stmt->close();
            header('Location: sign_up.php?error=' . urlencode('Username already exists'));
            exit();
        }

        // Check if email already exists
        $email_stmt = $conn->prepare("SELECT user_id FROM tbl_user WHERE email = ?");
        if (!$email_stmt) {
            throw new Exception("Database error: " . $conn->error);
        }

        $email_stmt->bind_param("s", $email);
        $email_stmt->execute();
        $email_result = $email_stmt->get_result();

        if ($email_result->num_rows > 0) {
            $email_stmt->close();
            header('Location: sign_up.php?error=' . urlencode('Email already registered'));
            exit();
        }

        $email_stmt->close();
        $check_stmt->close();

        // Hash password using bcrypt
        $hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

        // Prepare SQL statement to insert new user
        $insert_stmt = $conn->prepare("
            INSERT INTO tbl_user (username, email, password_hash, role) 
            VALUES (?, ?, ?, ?)
        ");

        if (!$insert_stmt) {
            throw new Exception("Database error: " . $conn->error);
        }

        $insert_stmt->bind_param("ssss", $username, $email, $hashed_password, $role);

        if (!$insert_stmt->execute()) {
            throw new Exception("Registration failed: " . $insert_stmt->error);
        }

        $user_id = $insert_stmt->insert_id;
        $insert_stmt->close();

        // Set session variables
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $_SESSION['role'] = $role;
        $_SESSION['logged_in'] = true;
        $_SESSION['new_user'] = true; // Flag for first-time setup

        // Redirect to dashboard with welcome message
        header('Location: ../../src/php/index.php?welcome=true');
        exit();

    } catch (Exception $e) {
        error_log("Registration error: " . $e->getMessage());
        header('Location: sign_up.php?error=' . urlencode('An error occurred during registration. Please try again.'));
        exit();
    }
} else {
    // If accessed directly without POST, redirect to sign-up
    header('Location: sign_up.php');
    exit();
}
?>
