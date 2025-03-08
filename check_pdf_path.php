<?php
/**
 * Check PDF Path in Database
 */

// Load configuration
require_once 'app/config/database.php';

// Get application ID from command line or use default
$id = isset($argv[1]) ? (int)$argv[1] : 1;

// Query the database
try {
    $stmt = $pdo->prepare('SELECT id, pdf_path, status FROM applications WHERE id = ?');
    $stmt->execute([$id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo "Application ID: {$result['id']}\n";
        echo "Status: {$result['status']}\n";
        echo "PDF Path: " . ($result['pdf_path'] ? $result['pdf_path'] : 'Not set') . "\n";
        
        if ($result['pdf_path']) {
            $fullPath = dirname(__FILE__) . '/' . $result['pdf_path'];
            echo "Full path: $fullPath\n";
            echo "File exists: " . (file_exists($fullPath) ? 'Yes' : 'No') . "\n";
            echo "File size: " . (file_exists($fullPath) ? filesize($fullPath) . ' bytes' : 'N/A') . "\n";
        }
    } else {
        echo "No application found with ID: $id\n";
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} 