<?php
/**
 * KEBANA Digital Management System - Database Connection
 * File: includes/dbconnect.php
 * 
 * Database connection script for MySQL
 * Configure your database credentials below
 */

// Dynamic connection from core Database wrapper
require_once __DIR__ . '/../bootstrap.php';
$conn = \App\Core\Database::getInstance()->getConnection();

// Optional: Display connection status in development
// echo "Connected successfully to database: " . DB_NAME;

/**
 * Database Schema Reference - New Schema for FYP
 * 
 * tbl_user: user_id, username, password_hash, role, email, created_at, updated_at
 * tbl_member: member_id, full_name, ic_number, village, phone_no, status, created_at, updated_at
 * tbl_event: event_id, event_title, event_date, venue, budget_est, created_by, created_at, updated_at
 * tbl_document: doc_id, event_id, doc_name, file_path, uploaded_at
 * tbl_transaction: trans_id, trans_type, amount, category, trans_date, recorded_by, created_at, updated_at
 */
?>
