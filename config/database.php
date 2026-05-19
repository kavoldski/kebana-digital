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
    'user' => 'u350551567_kebana_db', // Hostinger DB User
    'pass' => 'K3b@n@_data123**',     // Hostinger DB Password
    'name' => 'u350551567_kebana_db', // Hostinger DB Name
    'charset' => 'utf8mb4'
], $localConfig);
