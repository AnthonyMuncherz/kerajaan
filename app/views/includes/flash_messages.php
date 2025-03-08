<?php
/**
 * Flash Messages Component
 * Displays session-based notification messages
 */

// Check for flash messages in session
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    $type = $_SESSION['flash_type'] ?? 'info';
    
    // Clear the message from session
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);
    
    // Output the message
    echo '<div class="notification is-' . $type . ' is-light">';
    echo '<button class="delete"></button>';
    echo $message;
    echo '</div>';
}

// Check for URL query parameters that might indicate messages
if (isset($_GET['error'])) {
    $errorType = $_GET['error'];
    $errorMessage = '';
    
    switch ($errorType) {
        case 'unauthorized':
            $errorMessage = 'Anda tidak mempunyai akses untuk halaman tersebut.';
            break;
        case 'not_found':
            $errorMessage = 'Item yang diminta tidak dijumpai.';
            break;
        case 'invalid_request':
            $errorMessage = 'Permintaan tidak sah.';
            break;
        case 'update_failed':
            $errorMessage = 'Kemaskini gagal. Sila cuba lagi.';
            break;
        default:
            $errorMessage = 'Ralat berlaku. Sila cuba lagi.';
    }
    
    echo '<div class="notification is-danger is-light">';
    echo '<button class="delete"></button>';
    echo $errorMessage;
    echo '</div>';
}

if (isset($_GET['success'])) {
    $successType = $_GET['success'];
    $successMessage = '';
    
    switch ($successType) {
        case 'created':
            $successMessage = 'Item berjaya dicipta.';
            break;
        case 'updated':
            $successMessage = 'Item berjaya dikemaskini.';
            break;
        case 'deleted':
            $successMessage = 'Item berjaya dihapuskan.';
            break;
        case 'approved':
            $successMessage = 'Permohonan telah diluluskan.';
            break;
        case 'rejected':
            $successMessage = 'Permohonan telah ditolak.';
            break;
        default:
            $successMessage = 'Operasi berjaya.';
    }
    
    echo '<div class="notification is-success is-light">';
    echo '<button class="delete"></button>';
    echo $successMessage;
    echo '</div>';
}
?>

<script>
// Add event listeners to close notification messages
document.addEventListener('DOMContentLoaded', function() {
    // Get all delete buttons
    var deleteButtons = document.querySelectorAll('.notification .delete');
    
    // Add click event to each button
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            // Get the parent notification element
            var notification = this.parentNode;
            
            // Remove the notification
            notification.parentNode.removeChild(notification);
        });
    });
});
</script> 