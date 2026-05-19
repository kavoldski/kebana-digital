<?php
/**
 * KEBANA Digital Management System - Database Configuration
 * File: config/database.php
 */

$localConfig = [];
if (file_exists(__DIR__ . '/database.local.php')) {
    $localConfig = require __DIR__ . '/database.local.php';
}

return array_merge([
    'host' => 'localhost',
    'user' => 'u123456789_kebana', // Fill this with your Hostinger DB User
    'pass' => 'YOUR_HOSTINGER_DB_PASSWORD', // Fill this with your Hostinger DB Pass
    'name' => 'u123456789_kebana_db', // Fill this with your Hostinger DB Name
    'charset' => 'utf8mb4'
], $localConfig);
