<?php
/**
 * Diagnostic page for troubleshooting
 */

// Start output buffering
ob_start();

// Set content type
header('Content-Type: text/html; charset=UTF-8');

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Test basic PHP functionality
echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Diagnostic Page</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        h1 { color: #3273dc; }
        .section { margin-bottom: 20px; border: 1px solid #ddd; padding: 10px; }
        .code { font-family: monospace; background-color: #f5f5f5; padding: 10px; }
    </style>
</head>
<body>
    <h1>Diagnostic Page</h1>
    <div class="section">
        <h2>PHP Version</h2>
        <div class="code">' . phpversion() . '</div>
    </div>
    <div class="section">
        <h2>Loaded Extensions</h2>
        <div class="code">' . implode(', ', get_loaded_extensions()) . '</div>
    </div>
    <div class="section">
        <h2>Server Information</h2>
        <div class="code">' . $_SERVER['SERVER_SOFTWARE'] . '</div>
    </div>
    <div class="section">
        <h2>Path Information</h2>
        <div class="code">
            Document Root: ' . $_SERVER['DOCUMENT_ROOT'] . '<br>
            Script Filename: ' . $_SERVER['SCRIPT_FILENAME'] . '<br>
            PHP Self: ' . $_SERVER['PHP_SELF'] . '
        </div>
    </div>
    <div class="section">
        <h2>Include Path</h2>
        <div class="code">' . get_include_path() . '</div>
    </div>
</body>
</html>';

// Flush output buffer
ob_end_flush();
?> 