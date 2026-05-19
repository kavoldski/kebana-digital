<?php
/**
 * KEBANA Digital Management System - AI Configuration
 * File: config/ai.php
 */

$localConfig = [];
// Check one level above public_html (Hostinger Git deploy safety)
$secretsFile = dirname(__DIR__, 2) . '/kebana_secrets.php';
if (file_exists($secretsFile)) {
    $secrets = require $secretsFile;
    if (isset($secrets['ai'])) {
        $localConfig = $secrets['ai'];
    }
}
// Fallback to local config file (local dev)
elseif (file_exists(__DIR__ . '/ai.local.php')) {
    $localConfig = require __DIR__ . '/ai.local.php';
}

return array_merge([
    'api_key' => getenv('GEMINI_API_KEY') ?: '',
    'embedding_model' => 'gemini-embedding-001',
    'synthesis_model' => 'gemini-2.5-flash',
    'verify_ssl' => true, // Set to false in ai.local.php if you have local SSL cert issues
], $localConfig);
