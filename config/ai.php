<?php
/**
 * KEBANA Digital Management System - AI Configuration
 * File: config/ai.php
 */

$localConfig = [];
if (file_exists(__DIR__ . '/ai.local.php')) {
    $localConfig = require __DIR__ . '/ai.local.php';
}

return array_merge([
    'api_key' => getenv('GEMINI_API_KEY') ?: '',
    'embedding_model' => 'gemini-embedding-001',
    'synthesis_model' => 'gemini-flash-latest',
    'verify_ssl' => true, // Set to false in ai.local.php if you have local SSL cert issues
], $localConfig);
