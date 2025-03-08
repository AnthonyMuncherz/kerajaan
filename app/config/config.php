<?php
/**
 * Main configuration file
 * Sistem Permohonan Keluar
 */

// Automatically detect the site URL based on the current hostname
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$script_name = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
$script_path = dirname($script_name);
$base_url = $protocol . '://' . $host . $script_path;

// Site configuration
define('SITE_NAME', 'Sistem Permohonan Keluar');

// Set SITE_URL based on environment
// For local development (kerajaan.test)
if ($host === 'kerajaan.test') {
    define('SITE_URL', 'http://kerajaan.test');
} 
// For production (zahar.my)
else if (strpos($host, 'zahar.my') !== false) {
    define('SITE_URL', 'https://zahar.my/kerajaan/public');
}
// For any other host, use auto-detected URL
else {
    define('SITE_URL', $base_url);
}

define('BASE_PATH', dirname(dirname(__DIR__)));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');

// Paths
define('UPLOAD_PATH', APP_PATH . '/uploads');
define('SIGNATURE_PATH', UPLOAD_PATH . '/signatures');
define('PDF_TEMPLATE_PATH', APP_PATH . '/templates');
define('PDF_OUTPUT_PATH', APP_PATH . '/pdf');

// Session timeout in seconds (30 minutes)
define('SESSION_TIMEOUT', 1800);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session with secure settings
session_start([
    'cookie_lifetime' => SESSION_TIMEOUT,
    'cookie_httponly' => true,
    'cookie_secure' => $protocol === 'https', // Secure cookies when using HTTPS
]);

// Check for session timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
    session_unset();
    session_destroy();
    header('Location: ' . SITE_URL . '/login.php?timeout=1');
    exit;
}

// Update last activity time
$_SESSION['last_activity'] = time(); 