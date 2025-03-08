<?php
/**
 * Database update script
 * Adds form_240km_data column to applications table
 */

// Load configuration
require_once 'app/config/config.php';
require_once 'app/config/database.php';

// Check if column exists first
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM applications LIKE 'form_240km_data'");
    $columnExists = ($stmt->rowCount() > 0);
    
    if (!$columnExists) {
        // Add the column
        $pdo->exec("ALTER TABLE applications ADD COLUMN form_240km_data TEXT AFTER pdf_path");
        echo "Column form_240km_data added successfully.<br>";
    } else {
        echo "Column form_240km_data already exists.<br>";
    }
    
    echo "Database update completed.";
} catch (PDOException $e) {
    echo "Error updating database: " . $e->getMessage();
} 