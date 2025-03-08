<?php
/**
 * Main entry point
 * Sistem Permohonan Keluar
 */

// Load configuration
require_once '../app/config/config.php';
require_once '../app/config/database.php';
require_once '../app/utils/helpers.php';
require_once '../app/utils/auth.php';

// Redirect to dashboard if logged in, otherwise to login page
if (isLoggedIn()) {
    // Redirect based on role
    if (isKetua()) {
        header('Location: ' . SITE_URL . '/ketua_dashboard.php');
    } else if (isPengarah()) {
        header('Location: ' . SITE_URL . '/pengarah_dashboard.php');
    } else {
        header('Location: ' . SITE_URL . '/dashboard.php');
    }
} else {
    header('Location: ' . SITE_URL . '/login.php');
}
exit; 