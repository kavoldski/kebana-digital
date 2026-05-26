<?php
/**
 * KEBANA Digital Management System - Transparent File Loader
 * File: file_loader.php
 * Streams uploaded files securely from the external storage (protected from Git wipes)
 * or falls back to local uploads if they exist.
 */

require_once 'bootstrap.php';

$file = $_GET['file'] ?? '';

// Sanitize path to prevent directory traversal attacks
$file = str_replace(['..', '\\'], '', $file);

if (empty($file)) {
    http_response_code(404);
    die("File not specified");
}

// 1. Resolve paths
$externalPath = dirname(APP_ROOT) . '/kebana_uploads/' . $file;
$localPath = APP_ROOT . '/uploads/' . $file;

$targetPath = '';

// Check external secure path first
if (file_exists($externalPath) && is_file($externalPath)) {
    $targetPath = $externalPath;
} 
// Fallback to local public path if it exists
elseif (file_exists($localPath) && is_file($localPath)) {
    $targetPath = $localPath;
}

if (!empty($targetPath)) {
    // Determine MIME type
    $mimeType = @mime_content_type($targetPath);
    if ($mimeType === false) {
        $ext = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));
        $mimes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
        $mimeType = $mimes[$ext] ?? 'application/octet-stream';
    }

    // Set headers and stream file
    header('Content-Type: ' . $mimeType);
    header('Content-Length: ' . filesize($targetPath));
    header('Cache-Control: public, max-age=31536000'); // Cache for 1 year
    @readfile($targetPath);
    exit;
} else {
    http_response_code(404);
    die("File not found");
}
