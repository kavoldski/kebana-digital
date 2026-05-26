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
 * Auto-Healing Symlink for uploads directory to prevent Git deployment wipes on Hostinger
 */
$symlinkPath = APP_ROOT . '/uploads';
$targetStorage = dirname(APP_ROOT) . '/kebana_uploads';

$isLocalhost = isset($_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1' || strpos($_SERVER['HTTP_HOST'], '192.168.') === 0);

if (!$isLocalhost) {
    try {
        // 1. Create target storage if not exists
        if (!file_exists($targetStorage)) {
            @mkdir($targetStorage, 0755, true);
            @mkdir($targetStorage . '/announcements', 0755, true);
            @mkdir($targetStorage . '/documents', 0755, true);
            @mkdir($targetStorage . '/receipts', 0755, true);
            @mkdir($targetStorage . '/events', 0755, true);
        }

        // 2. Handle existing physical directory migration
        if (is_dir($symlinkPath) && !is_link($symlinkPath)) {
            // Move any existing physical files to the external storage to prevent loss
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($symlinkPath, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );
            foreach ($iterator as $item) {
                $relPath = substr($item->getPathname(), strlen($symlinkPath));
                $destPath = $targetStorage . $relPath;
                if ($item->isDir()) {
                    if (!file_exists($destPath)) {
                        @mkdir($destPath, 0755, true);
                    }
                } else {
                    $destDir = dirname($destPath);
                    if (!file_exists($destDir)) {
                        @mkdir($destDir, 0755, true);
                    }
                    @rename($item->getPathname(), $destPath);
                }
            }
            
            // Delete the empty physical directory
            $deleteDir = function($dir) use (&$deleteDir) {
                if (!file_exists($dir)) return true;
                if (!is_dir($dir)) return unlink($dir);
                foreach (scandir($dir) as $item) {
                    if ($item == '.' || $item == '..') continue;
                    if (!$deleteDir($dir . DIRECTORY_SEPARATOR . $item)) return false;
                }
                return rmdir($dir);
            };
            @$deleteDir($symlinkPath);
        }

        // 3. Handle broken symlink
        if (is_link($symlinkPath) && !is_dir($symlinkPath)) {
            @unlink($symlinkPath);
        }

        // 4. Create the symlink if missing
        if (!file_exists($symlinkPath) && !is_link($symlinkPath)) {
            @symlink($targetStorage, $symlinkPath);
        }
    } catch (\Throwable $e) {
        // Safe fallback: never crash bootstrap on physical server file permission issues
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
