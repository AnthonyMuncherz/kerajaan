<?php
/**
 * Registration page
 * Sistem Permohonan Keluar
 */

// Load configuration
require_once '../app/config/config.php';
require_once '../app/config/database.php';
require_once '../app/utils/helpers.php';
require_once '../app/utils/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . SITE_URL . '/dashboard.php');
    exit;
}

// Initialize variables
$error = '';
$success = '';
$formData = [
    'username' => '',
    'name' => '',
    'email' => '',
    'phone' => '',
    'department' => '',
    'position' => ''
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $formData = [
        'username' => sanitizeInput($_POST['username'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? '',
        'name' => sanitizeInput($_POST['name'] ?? ''),
        'email' => sanitizeInput($_POST['email'] ?? ''),
        'phone' => sanitizeInput($_POST['phone'] ?? ''),
        'department' => sanitizeInput($_POST['department'] ?? ''),
        'position' => sanitizeInput($_POST['position'] ?? '')
    ];
    
    // Validate form data
    if (empty($formData['username']) || empty($formData['password']) || 
        empty($formData['name']) || empty($formData['email']) || 
        empty($formData['department']) || empty($formData['position'])) {
        $error = 'Sila lengkapkan semua maklumat yang diperlukan.';
    } elseif ($formData['password'] !== $formData['confirm_password']) {
        $error = 'Kata laluan dan pengesahan kata laluan tidak sepadan.';
    } elseif (!isValidEmail($formData['email'])) {
        $error = 'Sila masukkan alamat email yang sah.';
    } else {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$formData['username']]);
        $existingUser = $stmt->fetch();
        
        if ($existingUser) {
            $error = 'Nama pengguna tersebut telah digunakan. Sila pilih nama pengguna lain.';
        } else {
            // Register user
            $userId = registerUser($formData);
            
            if ($userId) {
                $success = 'Pendaftaran berjaya! Sila log masuk dengan nama pengguna dan kata laluan anda.';
                // Clear form data
                $formData = [
                    'username' => '',
                    'name' => '',
                    'email' => '',
                    'phone' => '',
                    'department' => '',
                    'position' => ''
                ];
            } else {
                $error = 'Pendaftaran tidak berjaya. Sila cuba lagi.';
            }
        }
    }
}

// Set page title
$pageTitle = 'Pendaftaran';

// Include header
include '../app/views/includes/header.php';
?>

<div class="columns is-centered">
    <div class="column is-two-thirds">
        <div class="card">
            <div class="card-header">
                <p class="card-header-title">
                    <i class="fas fa-user-plus mr-2"></i> Pendaftaran Akaun Baru
                </p>
            </div>
            <div class="card-content">
                <?php if (!empty($error)): ?>
                    <div class="notification is-danger is-light">
                        <button class="delete"></button>
                        <?= $error ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="notification is-success is-light">
                        <button class="delete"></button>
                        <?= $success ?>
                        <p class="mt-2"><a href="<?= SITE_URL ?>/login.php">Klik di sini untuk log masuk</a></p>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" class="auth-form">
                    <div class="field">
                        <label class="label">Nama Pengguna</label>
                        <div class="control has-icons-left">
                            <input class="input" type="text" name="username" placeholder="Masukkan nama pengguna" value="<?= $formData['username'] ?>" required>
                            <span class="icon is-small is-left">
                                <i class="fas fa-user"></i>
                            </span>
                        </div>
                        <p class="help">Nama pengguna untuk log masuk ke sistem</p>
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
                        <label class="label">Pengesahan Kata Laluan</label>
                        <div class="control has-icons-left">
                            <input class="input" type="password" name="confirm_password" placeholder="Masukkan semula kata laluan" required>
                            <span class="icon is-small is-left">
                                <i class="fas fa-lock"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div class="field">
                        <label class="label">Nama Penuh</label>
                        <div class="control has-icons-left">
                            <input class="input" type="text" name="name" placeholder="Masukkan nama penuh" value="<?= $formData['name'] ?>" required>
                            <span class="icon is-small is-left">
                                <i class="fas fa-id-card"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div class="field">
                        <label class="label">Alamat Email</label>
                        <div class="control has-icons-left">
                            <input class="input" type="email" name="email" placeholder="Masukkan alamat email" value="<?= $formData['email'] ?>" required>
                            <span class="icon is-small is-left">
                                <i class="fas fa-envelope"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div class="field">
                        <label class="label">Nombor Telefon</label>
                        <div class="control has-icons-left">
                            <input class="input" type="tel" name="phone" placeholder="Masukkan nombor telefon" value="<?= $formData['phone'] ?>" required>
                            <span class="icon is-small is-left">
                                <i class="fas fa-phone"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div class="field">
                        <label class="label">Jabatan</label>
                        <div class="control has-icons-left">
                            <input class="input" type="text" name="department" placeholder="Masukkan jabatan" value="<?= $formData['department'] ?>" required>
                            <span class="icon is-small is-left">
                                <i class="fas fa-building"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div class="field">
                        <label class="label">Jawatan</label>
                        <div class="control has-icons-left">
                            <input class="input" type="text" name="position" placeholder="Masukkan jawatan" value="<?= $formData['position'] ?>" required>
                            <span class="icon is-small is-left">
                                <i class="fas fa-briefcase"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div class="field">
                        <div class="control">
                            <button type="submit" class="button is-primary is-fullwidth">
                                <i class="fas fa-user-plus mr-2"></i> Daftar
                            </button>
                        </div>
                    </div>
                </form>
                
                <div class="has-text-centered mt-5">
                    <p>Sudah mempunyai akaun? <a href="<?= SITE_URL ?>/login.php">Log masuk di sini</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include '../app/views/includes/footer.php';
?> 