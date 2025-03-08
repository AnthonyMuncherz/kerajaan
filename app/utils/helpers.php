<?php
/**
 * Helper functions
 * Sistem Permohonan Keluar
 */

/**
 * Sanitize user input
 * 
 * @param string $input The input to sanitize
 * @return string The sanitized input
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email address
 * 
 * @param string $email The email to validate
 * @return bool True if valid, false otherwise
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate date format (YYYY-MM-DD)
 * 
 * @param string $date The date to validate
 * @return bool True if valid, false otherwise
 */
function isValidDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

/**
 * Validate time format (HH:MM)
 * 
 * @param string $time The time to validate
 * @return bool True if valid, false otherwise
 */
function isValidTime($time) {
    $t = DateTime::createFromFormat('H:i', $time);
    return $t && $t->format('H:i') === $time;
}

/**
 * Generate a random filename
 * 
 * @param string $extension The file extension
 * @return string The random filename
 */
function generateRandomFilename($extension) {
    return uniqid() . '_' . time() . '.' . $extension;
}

/**
 * Format date for display (DD/MM/YYYY)
 * 
 * @param string $date The date in YYYY-MM-DD format
 * @return string The formatted date
 */
function formatDate($date) {
    if (empty($date)) return '';
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d ? $d->format('d/m/Y') : '';
}

/**
 * Get status label with color class
 * 
 * @param string $status The status (pending, ketua_approved, approved, rejected)
 * @return array The status label and color class
 */
function getStatusInfo($status) {
    switch ($status) {
        case 'pending':
            return ['label' => 'Menunggu Kelulusan', 'class' => 'is-warning'];
        case 'ketua_approved':
            return ['label' => 'Diluluskan oleh Ketua', 'class' => 'is-info'];
        case 'approved':
            return ['label' => 'Diluluskan', 'class' => 'is-success'];
        case 'rejected':
            return ['label' => 'Ditolak', 'class' => 'is-danger'];
        default:
            return ['label' => 'Tidak Diketahui', 'class' => 'is-light'];
    }
}

/**
 * Flash message handling
 * 
 * @param string $type The message type (success, error, warning, info)
 * @param string $message The message text
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

/**
 * Get and clear flash message
 * 
 * @return array|null The flash message or null if none
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        
        return [
            'message' => $message,
            'type' => $type
        ];
    }
    return null;
}

/**
 * Redirect to a URL
 * 
 * @param string $url The URL to redirect to
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
} 