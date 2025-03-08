<?php
/**
 * Login page
 * Sistem Permohonan Keluar
 */

// Load configuration
require_once '../app/config/config.php';
require_once '../app/config/database.php';
require_once '../app/utils/helpers.php';
require_once '../app/utils/auth.php';

// Initialize variables
$error = '';
$username = '';

// Redirect if already logged in
if (isLoggedIn()) {
    // Redirect based on role
    if (isKetua()) {
        header('Location: ' . SITE_URL . '/ketua_dashboard.php');
    } else if (isPengarah()) {
        header('Location: ' . SITE_URL . '/pengarah_dashboard.php');
    } else {
        header('Location: ' . SITE_URL . '/dashboard.php');
    }
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate form data
    if (empty($username) || empty($password)) {
        $error = 'Sila masukkan nama pengguna dan kata laluan.';
    } else {
        // Authenticate user
        $user = authenticateUser($username, $password);
        
        if ($user) {
            // Store user data in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            
            // Redirect based on role
            if ($user['role'] === UserModel::ROLE_KETUA) {
                header('Location: ' . SITE_URL . '/ketua_dashboard.php');
            } else if ($user['role'] === UserModel::ROLE_PENGARAH) {
                header('Location: ' . SITE_URL . '/pengarah_dashboard.php');
            } else {
                header('Location: ' . SITE_URL . '/dashboard.php');
            }
            exit;
        } else {
            $error = 'Nama pengguna atau kata laluan tidak sah.';
        }
    }
}

// Set page title
$pageTitle = 'Log Masuk';

// Include header
include '../app/views/includes/header.php';
?>

<div class="columns is-centered">
    <div class="column is-half">
        <div class="card">
            <div class="card-header">
                <p class="card-header-title">
                    <i class="fas fa-sign-in-alt mr-2"></i> Log Masuk
                </p>
            </div>
            <div class="card-content">
                <div class="has-text-centered mb-5">
                    <img src="<?= SITE_URL ?>/images/logo_ipgkbm_2024.png" alt="Logo IPGKBM" style="max-width: 200px; height: auto;">
                </div>
                
                <?php if (!empty($error)): ?>
                    <div class="notification is-danger is-light">
                        <button class="delete"></button>
                        <?= $error ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['timeout'])): ?>
                    <div class="notification is-warning is-light">
                        <button class="delete"></button>
                        Sesi anda telah tamat. Sila log masuk semula.
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['logout'])): ?>
                    <div class="notification is-success is-light">
                        <button class="delete"></button>
                        Anda telah berjaya log keluar.
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" class="auth-form">
                    <div class="field">
                        <label class="label">Nama Pengguna</label>
                        <div class="control has-icons-left">
                            <input class="input" type="text" name="username" placeholder="Masukkan nama pengguna" value="<?= $username ?>" required>
                            <span class="icon is-small is-left">
                                <i class="fas fa-user"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div class="field">
                        <label class="label">Kata Laluan</label>
                        <div class="control has-icons-left">
                            <input class="input" type="password" name="password" placeholder="Masukkan kata laluan" required>
                            <span class="icon is-small is-left">
                                <i class="fas fa-lock"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div class="field">
                        <div class="control">
                            <button type="submit" class="button is-primary is-fullwidth">
                                <i class="fas fa-sign-in-alt mr-2"></i> Log Masuk
                            </button>
                        </div>
                    </div>
                </form>
                
                <div class="has-text-centered mt-5">
                    <p>Belum mempunyai akaun? <a href="<?= SITE_URL ?>/register.php">Daftar di sini</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include '../app/views/includes/footer.php';
?> 