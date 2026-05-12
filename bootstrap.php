<?php
/**
 * KEBANA Management System - Bootstrap
 * File: bootstrap.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('APP_ROOT', __DIR__);
define('URL_ROOT', '/kebana-digital');
$base_path = URL_ROOT . '/';

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
