<?php
/**
 * Root redirect to public directory
 * Sistem Permohonan Keluar
 */

// Load configuration to get site URL
require_once 'app/config/config.php';

// Redirect to public directory
header('Location: ' . SITE_URL);
exit;
?> 