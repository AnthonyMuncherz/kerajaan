<?php
/**
 * User Profile page
 * Sistem Permohonan Keluar
 */

// Load configuration
require_once '../app/config/config.php';
require_once '../app/config/database.php';
require_once '../app/utils/helpers.php';
require_once '../app/utils/auth.php';
require_once '../app/models/UserModel.php';

// Require login
requireLogin();

// Create user model
$userModel = new UserModel($pdo);

// Get user ID
$userId = $_SESSION['user_id'];

// Get user details
$user = $userModel->getUserById($userId);

// Initialize variables
$error = '';
$success = '';
$formData = [
    'name' => $user['name'],
    'email' => $user['email'],
    'phone' => $user['phone'] ?? '',
    'department' => $user['department'],
    'position' => $user['position']
];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    // Get form data
    $formData = [
        'name' => sanitizeInput($_POST['name'] ?? ''),
        'email' => sanitizeInput($_POST['email'] ?? ''),
        'phone' => sanitizeInput($_POST['phone'] ?? ''),
        'department' => sanitizeInput($_POST['department'] ?? ''),
        'position' => sanitizeInput($_POST['position'] ?? '')
    ];
    
    // Validate form data
    if (empty($formData['name']) || empty($formData['email']) || 
        empty($formData['department']) || empty($formData['position'])) {
        $error = 'Sila lengkapkan semua maklumat yang diperlukan.';
    } elseif (!isValidEmail($formData['email'])) {
        $error = 'Sila masukkan alamat email yang sah.';
    } else {
        // Update profile
        $success = $userModel->updateProfile($userId, $formData);
        
        if ($success) {
            $success = 'Profil telah berjaya dikemaskini.';
            
            // Update session data
            $_SESSION['user_name'] = $formData['name'];
            
            // Refresh user data
            $user = $userModel->getUserById($userId);
        } else {
            $error = 'Kemaskini profil tidak berjaya. Sila cuba lagi.';
        }
    }
}

// Handle password update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    // Get form data
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validate form data
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = 'Sila lengkapkan semua maklumat kata laluan.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Kata laluan baru dan pengesahan kata laluan tidak sepadan.';
    } else {
        // Verify current password
        $userData = $userModel->getUserByUsername($user['username']);
        
        if (!$userData || !password_verify($currentPassword, $userData['password'])) {
            $error = 'Kata laluan semasa tidak sah.';
        } else {
            // Update password
            $success = $userModel->updatePassword($userId, $newPassword);
            
            if ($success) {
                $success = 'Kata laluan telah berjaya dikemaskini.';
            } else {
                $error = 'Kemaskini kata laluan tidak berjaya. Sila cuba lagi.';
            }
        }
    }
}

// Handle signature upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_signature'])) {
    // Check if file was uploaded
    if (isset($_FILES['signature']) && $_FILES['signature']['error'] === UPLOAD_ERR_OK) {
        // Upload signature
        $signaturePath = $userModel->uploadSignature($userId, $_FILES['signature']);
        
        if ($signaturePath) {
            // Update user profile with signature path
            $updateData = [
                'name' => $user['name'],
                'email' => $user['email'],
                'department' => $user['department'],
                'position' => $user['position'],
                'signature_path' => $signaturePath
            ];
            
            $success = $userModel->updateProfile($userId, $updateData);
            
            if ($success) {
                $success = 'Tandatangan telah berjaya dimuat naik.';
                
                // Refresh user data
                $user = $userModel->getUserById($userId);
            } else {
                $error = 'Kemaskini tandatangan tidak berjaya. Sila cuba lagi.';
            }
        } else {
            $error = 'Muat naik tandatangan tidak berjaya. Sila cuba lagi.';
        }
    } else {
        $error = 'Sila pilih fail tandatangan untuk dimuat naik.';
    }
}

// Set page title
$pageTitle = 'Profil Pengguna';

// Include header
include '../app/views/includes/header.php';
?>

<div class="content">
    <h1 class="title">
        <i class="fas fa-user-circle mr-2"></i> Profil Pengguna
    </h1>
    <h2 class="subtitle">
        Kemaskini maklumat profil anda.
    </h2>
</div>

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
    </div>
<?php endif; ?>

<div class="columns">
    <div class="column is-4">
        <div class="card mb-5">
            <div class="card-header">
                <p class="card-header-title">
                    <i class="fas fa-id-card mr-2"></i> Maklumat Pengguna
                </p>
            </div>
            <div class="card-content">
                <div class="content">
                    <p><strong>Nama Pengguna:</strong> <?= $user['username'] ?></p>
                    <p><strong>Nama:</strong> <?= $user['name'] ?></p>
                    <p><strong>Email:</strong> <?= $user['email'] ?></p>
                    <p><strong>Nombor Telefon:</strong> <?= $user['phone'] ?></p>
                    <p><strong>Jabatan:</strong> <?= $user['department'] ?></p>
                    <p><strong>Jawatan:</strong> <?= $user['position'] ?></p>
                    <p><strong>Peranan:</strong> <?= $user['role'] === 'admin' ? 'Pentadbir' : 'Pengguna' ?></p>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <p class="card-header-title">
                    <i class="fas fa-signature mr-2"></i> Tandatangan
                </p>
            </div>
            <div class="card-content">
                <div class="content">
                    <?php if (!empty($user['signature_path'])): ?>
                        <div class="has-text-centered mb-4">
                            <?php
                            $signaturePath = APP_PATH . '/uploads/signatures/' . $user['signature_path'];
                            if (file_exists($signaturePath) && is_readable($signaturePath)):
                                $imageData = base64_encode(file_get_contents($signaturePath));
                                $imageType = pathinfo($signaturePath, PATHINFO_EXTENSION);
                                if ($imageType === 'jpg' || $imageType === 'jpeg') $imageType = 'jpeg';
                                elseif ($imageType === 'png') $imageType = 'png';
                                elseif ($imageType === 'gif') $imageType = 'gif';
                                else $imageType = 'png'; // Default
                            ?>
                                <img src="data:image/<?= $imageType ?>;base64,<?= $imageData ?>" alt="Tandatangan" class="signature-preview">
                            <?php else: ?>
                                <div class="notification is-warning is-light">
                                    Tandatangan tidak dapat diakses. Sila muat naik semula.
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="notification is-warning is-light">
                            Anda belum memuat naik tandatangan anda.
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" enctype="multipart/form-data">
                        <div class="field">
                            <label class="label">Muat Naik Tandatangan</label>
                            <div class="control">
                                <div class="file has-name is-fullwidth">
                                    <label class="file-label">
                                        <input class="file-input" type="file" name="signature" accept="image/jpeg,image/png,image/gif">
                                        <span class="file-cta">
                                            <span class="file-icon">
                                                <i class="fas fa-upload"></i>
                                            </span>
                                            <span class="file-label">
                                                Pilih fail...
                                            </span>
                                        </span>
                                        <span class="file-name">
                                            Tiada fail dipilih
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <p class="help">Format yang diterima: JPG, PNG, GIF. Saiz maksimum: 2MB.</p>
                        </div>
                        
                        <div class="field">
                            <div class="control">
                                <button type="submit" name="upload_signature" class="button is-info is-fullwidth">
                                    <i class="fas fa-upload mr-2"></i> Muat Naik
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="column is-8">
        <div class="card mb-5">
            <div class="card-header">
                <p class="card-header-title">
                    <i class="fas fa-user-edit mr-2"></i> Kemaskini Profil
                </p>
            </div>
            <div class="card-content">
                <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                    <div class="field">
                        <label class="label">Nama Penuh</label>
                        <div class="control">
                            <input class="input" type="text" name="name" value="<?= $formData['name'] ?>" required>
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
                            <input class="input" type="tel" name="phone" placeholder="Masukkan nombor telefon" value="<?= $formData['phone'] ?>">
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
                        <div class="control">
                            <input class="input" type="text" name="position" value="<?= $formData['position'] ?>" required>
                        </div>
                    </div>
                    
                    <div class="field">
                        <div class="control">
                            <button type="submit" name="update_profile" class="button is-primary">
                                <i class="fas fa-save mr-2"></i> Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <p class="card-header-title">
                    <i class="fas fa-key mr-2"></i> Tukar Kata Laluan
                </p>
            </div>
            <div class="card-content">
                <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                    <div class="field">
                        <label class="label">Kata Laluan Semasa</label>
                        <div class="control">
                            <input class="input" type="password" name="current_password" required>
                        </div>
                    </div>
                    
                    <div class="field">
                        <label class="label">Kata Laluan Baru</label>
                        <div class="control">
                            <input class="input" type="password" name="new_password" required>
                        </div>
                    </div>
                    
                    <div class="field">
                        <label class="label">Pengesahan Kata Laluan Baru</label>
                        <div class="control">
                            <input class="input" type="password" name="confirm_password" required>
                        </div>
                    </div>
                    
                    <div class="field">
                        <div class="control">
                            <button type="submit" name="update_password" class="button is-primary">
                                <i class="fas fa-key mr-2"></i> Tukar Kata Laluan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- File input JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const fileInput = document.querySelector('.file-input');
        const fileName = document.querySelector('.file-name');
        
        fileInput.addEventListener('change', () => {
            if (fileInput.files.length > 0) {
                fileName.textContent = fileInput.files[0].name;
            } else {
                fileName.textContent = 'Tiada fail dipilih';
            }
        });
    });
</script>

<?php
// Include footer
include '../app/views/includes/footer.php';
?> 