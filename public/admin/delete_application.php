<?php
/**
 * Delete Application handler
 * Sistem Permohonan Keluar
 */

// Load configuration
require_once '../../app/config/config.php';
require_once '../../app/config/database.php';
require_once '../../app/utils/helpers.php';
require_once '../../app/utils/auth.php';
require_once '../../app/models/ApplicationModel.php';

// Require admin role
requireAdmin();

// Create models
$applicationModel = new ApplicationModel($pdo);

// Get application ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validate ID
if ($id <= 0) {
    $_SESSION['error'] = 'ID permohonan tidak sah.';
    header('Location: ' . SITE_URL . '/admin/applications.php');
    exit;
}

// Check if we need to confirm deletion
if (!isset($_GET['confirm']) || $_GET['confirm'] !== '1') {
    $_SESSION['confirm_delete'] = $id;
    $_SESSION['info'] = 'Sila sahkan untuk memadam permohonan #' . $id . '.';
    header('Location: ' . SITE_URL . '/admin/applications.php');
    exit;
}

// Process deletion
if (isset($_SESSION['confirm_delete']) && $_SESSION['confirm_delete'] === $id) {
    // Delete the application
    if ($applicationModel->deleteApplication($id)) {
        $_SESSION['success'] = 'Permohonan #' . $id . ' telah berjaya dipadam.';
        unset($_SESSION['confirm_delete']);
    } else {
        $_SESSION['error'] = 'Gagal memadam permohonan #' . $id . '.';
    }
} else {
    $_SESSION['error'] = 'Sesi pengesahan tidak sah.';
}

// Redirect back to applications list
header('Location: ' . SITE_URL . '/admin/applications.php');
exit; 