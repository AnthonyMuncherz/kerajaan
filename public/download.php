<?php
/**
 * File download script
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

// Require login
requireLogin();

// Create models
$applicationModel = new ApplicationModel($pdo);
$userModel = new UserModel($pdo);

// Get file parameter
$file = isset($_GET['file']) ? sanitizeInput($_GET['file']) : '';
$regenerate = isset($_GET['regenerate']) && $_GET['regenerate'] == 1;

// Validate file parameter
if (empty($file)) {
    die('Invalid file parameter');
}

// Check if the file path contains valid characters
if (!preg_match('/^[a-zA-Z0-9_\/-]+\.[a-zA-Z0-9]+$/', basename($file))) {
    die('Invalid file format');
}

// Check if this is an attachment
$isAttachment = strpos($file, 'uploads/attachments/') !== false;

// For attachments, we'll just download directly
if ($isAttachment) {
    $filePath = dirname(dirname(__FILE__)) . '/' . $file;
    
    // Check if file exists
    if (!file_exists($filePath)) {
        die('Attachment file not found');
    }
    
    // Get file information
    $fileInfo = pathinfo($filePath);
    $fileName = $fileInfo['basename'];
    
    // Set appropriate content type
    $contentType = 'application/pdf';
    
    // Set headers for download
    header('Content-Type: ' . $contentType);
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Output file content
    readfile($filePath);
    exit;
}

// Get application ID from file path
preg_match('/exit_form_(\d+)_/', $file, $matches);
$applicationId = isset($matches[1]) ? (int)$matches[1] : 0;

if ($applicationId <= 0) {
    die('Invalid application ID in file path');
}

// Get application details
$application = $applicationModel->getApplicationById($applicationId);

if (!$application) {
    die('Application not found');
}

// Check if user has permission to access this file
$userId = $_SESSION['user_id'];
$isAdmin = isAdmin();
$isApprover = isApprover();

// If not admin or approver, check if the file belongs to the user
if (!$isAdmin && !$isApprover && $application['user_id'] != $userId) {
    die('You do not have permission to access this file');
}

// Get user details
$user = $userModel->getUserById($application['user_id']);

if (!$user) {
    die('User not found');
}

// Determine file path
$filePath = '';
if ($regenerate) {
    // Generate a new PDF with the current approvals
    $pdfPath = generateExitFormPDF($application, $user);
    
    if ($pdfPath) {
        // Update application with new PDF path
        $applicationModel->updatePDFPath($applicationId, $pdfPath);
        $filePath = dirname(dirname(__FILE__)) . '/' . $pdfPath;
    } else {
        die('Failed to generate PDF');
    }
} else {
    // Use existing file
    $filePath = dirname(dirname(__FILE__)) . '/' . $file;
}

// Check if file exists
if (!file_exists($filePath)) {
    die('File not found: ' . $filePath);
}

// Get file information
$fileInfo = pathinfo($filePath);
$fileName = $fileInfo['basename'];

// Set headers for download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Output file content
readfile($filePath);
exit; 