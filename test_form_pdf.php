<?php
/**
 * Test script for generating Exit Form PDF
 */

// Load configuration
require_once 'app/config/config.php';
require_once 'app/config/database.php';
require_once 'app/utils/helpers.php';
require_once 'app/utils/pdf.php';
require_once 'app/models/ApplicationModel.php';
require_once 'app/models/UserModel.php';

// Get the application ID from command line or use a default
$applicationId = isset($argv[1]) ? (int)$argv[1] : 1;

// Create models
$applicationModel = new ApplicationModel($pdo);
$userModel = new UserModel($pdo);

echo "Starting PDF generation test for application ID: $applicationId\n";

// Get application details
$application = $applicationModel->getApplicationById($applicationId);

if (!$application) {
    echo "Error: Application with ID $applicationId not found!\n";
    echo "Available applications:\n";
    
    // Get list of available applications
    try {
        $stmt = $pdo->query("SELECT id, status FROM applications ORDER BY id LIMIT 10");
        $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($applications)) {
            echo "No applications found in the database.\n";
        } else {
            foreach ($applications as $app) {
                echo "ID: {$app['id']}, Status: {$app['status']}\n";
            }
        }
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage() . "\n";
    }
    
    exit(1);
}

echo "Found application #{$application['id']} for user {$application['user_name']}\n";

// Get user details
$user = $userModel->getUserById($application['user_id']);

if (!$user) {
    echo "Error: User with ID {$application['user_id']} not found!\n";
    exit(1);
}

echo "Found user: {$user['name']}\n";

// Generate PDF
echo "Generating PDF...\n";
$pdfPath = generateExitFormPDF($application, $user);

if ($pdfPath) {
    $fullPath = dirname(__FILE__) . '/' . $pdfPath;
    echo "PDF successfully generated!\n";
    echo "PDF saved to: $fullPath\n";
    
    // Update application with PDF path if needed
    if (empty($application['pdf_path'])) {
        $updated = $applicationModel->updatePDFPath($application['id'], $pdfPath);
        if ($updated) {
            echo "Application updated with new PDF path.\n";
        } else {
            echo "Failed to update application with PDF path.\n";
        }
    }
} else {
    echo "Error: Failed to generate PDF!\n";
    echo "Check the server logs for more details.\n";
} 