<?php
/**
 * Check Signature Script
 */

// Load configuration
require_once '../app/config/config.php';
require_once '../app/config/database.php';
require_once '../app/utils/auth.php';

// Require login
requireLogin();

// Get user ID
$userId = $_SESSION['user_id'];

// Get user signature path
$stmt = $pdo->prepare("SELECT signature_path FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

echo "<h1>Signature Checker</h1>";

if (!empty($user['signature_path'])) {
    $signaturePath = SIGNATURE_PATH . '/' . $user['signature_path'];
    
    echo "<p>Signature filename: " . htmlspecialchars($user['signature_path']) . "</p>";
    echo "<p>Full signature path: " . htmlspecialchars($signaturePath) . "</p>";
    
    if (file_exists($signaturePath)) {
        echo "<p style='color: green'>The signature file exists!</p>";
        echo "<p>File size: " . filesize($signaturePath) . " bytes</p>";
        echo "<p>Is readable: " . (is_readable($signaturePath) ? 'Yes' : 'No') . "</p>";
        
        echo "<p>URL path for image: " . SITE_URL . "/app/uploads/signatures/" . $user['signature_path'] . "</p>";
        
        echo "<h2>Trying to display the image:</h2>";
        echo "<img src='" . SITE_URL . "/app/uploads/signatures/" . $user['signature_path'] . "' style='border: 2px solid red; padding: 5px;'>";
        
        echo "<h2>Display using direct file path:</h2>";
        echo "<img src='data:image/png;base64," . base64_encode(file_get_contents($signaturePath)) . "' style='border: 2px solid blue; padding: 5px;'>";
    } else {
        echo "<p style='color: red'>The signature file does not exist!</p>";
    }
} else {
    echo "<p>No signature found for this user.</p>";
}
?> 