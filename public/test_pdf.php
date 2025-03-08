<?php
/**
 * Test PDF Generation
 * Sistem Permohonan Keluar
 */

// Load configuration
require_once '../app/config/config.php';
require_once '../app/config/database.php';
require_once '../app/utils/helpers.php';
require_once '../app/utils/auth.php';
require_once '../app/utils/pdf.php';
require_once '../app/models/ApplicationModel.php';
require_once '../app/models/UserModel.php';

// Require login and admin
requireLogin();
if (!isAdmin()) {
    die('Admin access required.');
}

// Create models
$applicationModel = new ApplicationModel($pdo);
$userModel = new UserModel($pdo);

// Get application ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Check if we have a valid ID
if ($id > 0) {
    // Get application details
    $application = $applicationModel->getApplicationById($id);
    
    if (!$application) {
        die('Application not found.');
    }
    
    // Get user data
    $user = $userModel->getUserById($application['user_id']);
    
    if (!$user) {
        die('User not found.');
    }
    
    // Generate PDF
    $pdfFile = generateExitFormPDF($application, $user);
    
    if ($pdfFile) {
        echo "PDF successfully generated: " . $pdfFile;
        echo "<br><a href='" . SITE_URL . "/download.php?file=" . $pdfFile . "'>Download PDF</a>";
    } else {
        echo "Failed to generate PDF.";
    }
} else {
    // No ID specified - show form to select an application
    // Get all applications
    $applications = $applicationModel->getAllApplications();
    
    echo "<h1>Test PDF Generation</h1>";
    echo "<p>Select an application to generate a PDF for:</p>";
    echo "<ul>";
    
    foreach ($applications as $app) {
        echo "<li><a href='test_pdf.php?id=" . $app['id'] . "'>#" . $app['id'] . " - " . $app['user_name'] . " - " . $app['purpose_type'] . " (" . $app['status'] . ")</a></li>";
    }
    
    echo "</ul>";
} 