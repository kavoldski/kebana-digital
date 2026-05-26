<?php
/**
 * KEBANA Digital Management System - Bootstrap
 * File: bootstrap.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable secure debug diagnostics on production via query parameter
if (isset($_GET['debug']) && $_GET['debug'] === 'kebana_debug') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    if (function_exists('opcache_reset')) {
        opcache_reset();
    }
}

// Set Timezone to Malaysia
date_default_timezone_set('Asia/Kuala_Lumpur');

// Load Composer Autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

define('APP_ROOT', __DIR__);
// Automatically detect if running on localhost (local dev) or live production on Hostinger
if (isset($_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1' || strpos($_SERVER['HTTP_HOST'], '192.168.') === 0)) {
    define('URL_ROOT', '/kebana-digital');
} else {
    define('URL_ROOT', ''); // Production root domain (kebana.digital)
}
$base_path = URL_ROOT . '/';

/**
 * Upload System Configuration — Protect uploads from Git deployment wipes on Hostinger
 */
$isLocalhost = isset($_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1' || strpos($_SERVER['HTTP_HOST'], '192.168.') === 0);

if ($isLocalhost) {
    define('UPLOAD_ROOT_PATH', APP_ROOT . '/uploads');
} else {
    define('UPLOAD_ROOT_PATH', dirname(APP_ROOT) . '/kebana_uploads');
    
    // Automatically initialize the secure external storage directories
    try {
        if (!file_exists(UPLOAD_ROOT_PATH)) {
            @mkdir(UPLOAD_ROOT_PATH, 0755, true);
        }
        $subDirs = ['/announcements', '/documents', '/receipts', '/events', '/archive'];
        foreach ($subDirs as $sub) {
            $dir = UPLOAD_ROOT_PATH . $sub;
            if (!file_exists($dir)) {
                @mkdir($dir, 0755, true);
            }
        }
    } catch (\Throwable $e) {
        // Never crash bootstrap on filesystem permission checks
    }
}



define('LOGO_ICON', URL_ROOT . '/public/assets/img/kebana-logo-icon.png');


// Simple Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = APP_ROOT . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Load config
$config = require APP_ROOT . '/config/database.php';

/**
 * Resolves a database file path (e.g., 'uploads/announcements/file.jpg') to its absolute physical path.
 * Automatically handles localhost (public_html/uploads) and production (kebana_uploads) structures.
 */
function get_absolute_upload_path($dbPath) {
    // Standardize directory separators
    $dbPath = str_replace('\\', '/', $dbPath);
    if (strpos($dbPath, 'uploads/') === 0) {
        return UPLOAD_ROOT_PATH . '/' . substr($dbPath, 8);
    }
    return APP_ROOT . '/' . $dbPath;
}

