<?php
/**
 * Print PDF script
 * Sistem Permohonan Keluar
 */

// Start output buffering to prevent warnings about headers already sent
ob_start();

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

// Get application ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validate ID
if ($id <= 0) {
    die('Invalid application ID');
}

// Get application details
$application = $applicationModel->getApplicationById($id);

// Check if application exists and user has permission
if (!$application || (!isAdmin() && !isApprover() && $application['user_id'] != $_SESSION['user_id'])) {
    die('You do not have permission to access this application');
}

// Get user details
$user = $userModel->getUserById($application['user_id']);

if (!$user) {
    die('User not found');
}

// Determine if we need to regenerate the PDF
$regenerate = false;

// Check if any of these conditions are true
// 1. PDF doesn't exist
// 2. Force regeneration is requested
// 3. PDF was generated before approval statuses changed
if (
    empty($application['pdf_path']) || 
    !file_exists(dirname(dirname(__FILE__)) . '/' . $application['pdf_path']) ||
    isset($_GET['regenerate']) ||
    (
        // If the application has been approved by ketua or pengarah since the PDF was generated
        ($application['ketua_approval_status'] === 'approved' || $application['pengarah_approval_status'] === 'approved') &&
        // Check if the PDF path contains a timestamp before the approval dates
        (!empty($application['pdf_path']) && 
         preg_match('/exit_form_\d+_(\d+)\.pdf/', $application['pdf_path'], $matches) &&
         (
             ($application['ketua_approval_status'] === 'approved' && 
              !empty($application['ketua_approval_date']) && 
              $matches[1] < strtotime($application['ketua_approval_date'])) ||
             ($application['pengarah_approval_status'] === 'approved' && 
              !empty($application['pengarah_approval_date']) && 
              $matches[1] < strtotime($application['pengarah_approval_date']))
         )
        )
    )
) {
    $regenerate = true;
}

// Generate or use existing PDF
$pdfPath = '';
if ($regenerate) {
    // Generate a new PDF with the current approvals
    $pdfPath = generateExitFormPDF($application, $user);
    
    if ($pdfPath) {
        // Check if we got an array (multiple PDFs)
        if (is_array($pdfPath)) {
            // Update application with main form's PDF path
            $applicationModel->updatePDFPath($id, $pdfPath['main']);
            
            // Clean the buffer before sending our HTML response
            ob_end_clean();
            
            // Create the HTML response to handle multiple PDFs
            header('Content-Type: text/html');
            echo '<!DOCTYPE html>
            <html>
            <head>
                <title>Borang Permohonan - Print</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    h2 { color: #333; }
                    .pdf-container { margin: 20px 0; }
                    iframe { width: 100%; height: 800px; border: 1px solid #ddd; }
                    .btn { 
                        display: inline-block; 
                        background: #0275d8; 
                        color: white; 
                        padding: 8px 16px; 
                        text-decoration: none;
                        border-radius: 4px;
                        margin-right: 10px;
                    }
                    .btn-print { background: #5cb85c; }
                </style>
            </head>
            <body>
                <h2>Borang Permohonan Keluar Rasmi</h2>
                <div class="pdf-container">
                    <iframe src="' . SITE_URL . '/' . $pdfPath['main'] . '"></iframe>
                </div>
                
                <h2>Borang Kenderaan Sendiri 240KM</h2>
                <div class="pdf-container">
                    <iframe src="' . SITE_URL . '/' . $pdfPath['240km'] . '"></iframe>
                </div>
                
                <div class="buttons">
                    <a href="javascript:window.print()" class="btn btn-print">Cetak Semua</a>
                    <a href="' . SITE_URL . '/dashboard.php" class="btn">Kembali ke Dashboard</a>
                </div>
                
                <script>
                    // Print all PDFs on page load if requested
                    if (window.location.search.includes("autoprint=1")) {
                        window.print();
                    }
                </script>
            </body>
            </html>';
            exit;
        } else {
            // Single PDF path, update as usual
            $applicationModel->updatePDFPath($id, $pdfPath);
        }
    } else {
        die('Failed to generate PDF');
    }
} else {
    $pdfPath = $application['pdf_path'];
}

// Full path to the PDF file
$fullPath = dirname(dirname(__FILE__)) . '/' . $pdfPath;

// Check if file exists
if (!file_exists($fullPath)) {
    die('PDF file not found');
}

// Clean the buffer before sending headers
ob_end_clean();

// Check if headers have already been sent
if (!headers_sent()) {
    // Set headers to display PDF in browser
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . basename($pdfPath) . '"');
    header('Content-Length: ' . filesize($fullPath));
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
} else {
    // Headers already sent, use a different approach
    echo '<script>window.location.href = "' . SITE_URL . '/' . $pdfPath . '";</script>';
    exit;
}

// Output file content
readfile($fullPath);
exit; 