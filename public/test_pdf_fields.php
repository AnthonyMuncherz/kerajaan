<?php
/**
 * Test PDF Fields
 * Sistem Permohonan Keluar
 */

// Load configuration
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/utils/helpers.php';
require_once __DIR__ . '/../app/utils/auth.php';

// Require login and admin
requireLogin();
if (!isAdmin()) {
    die('Admin access required.');
}

// Check for composer autoload
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    die('Composer autoload not found. Please run "composer install"');
}

// Include composer autoload
require_once $autoloadPath;

// PDF template path
$templatePath = __DIR__ . '/../Borang Keluar.pdf';

// Check if template exists
if (!file_exists($templatePath)) {
    die('PDF template not found at: ' . $templatePath);
}

try {
    // Create PDFTK instance
    $pdf = new \mikehaertl\pdftk\Pdf($templatePath);
    
    // Get form field data
    $fields = $pdf->getDataFields();
    
    if ($fields) {
        echo "<h1>PDF Form Fields</h1>";
        echo "<pre>";
        print_r($fields);
        echo "</pre>";
    } else {
        echo "Failed to get form fields: " . $pdf->getError();
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} 