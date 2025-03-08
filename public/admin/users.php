<?php
/**
 * Admin User Management page
 * Sistem Permohonan Keluar
 */

// Load configuration
require_once '../../app/config/config.php';
require_once '../../app/config/database.php';
require_once '../../app/utils/helpers.php';
require_once '../../app/utils/auth.php';
require_once '../../app/models/UserModel.php';

// Require admin role
requireAdmin();

// Create models
$userModel = new UserModel($pdo);

// Handle role update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $userId = (int)$_POST['user_id'];
    $newRole = sanitizeInput($_POST['role']);
    $department = sanitizeInput($_POST['department']);
    
    // Update user role and department
    if ($userModel->updateUserRoleAndDepartment($userId, $newRole, $department)) {
        // Set success message
        setFlashMessage('success', 'Peranan dan jabatan pengguna telah berjaya dikemaskini.');
    } else {
        // Set error message
        setFlashMessage('danger', 'Kemaskini peranan dan jabatan pengguna gagal. Sila cuba lagi.');
    }
    
    // Redirect to refresh page
    header('Location: ' . SITE_URL . '/admin/users.php');
    exit;
}

// Set up pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Get search term
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Get sort parameters
$sortBy = isset($_GET['sort']) ? sanitizeInput($_GET['sort']) : 'name';
$sortDir = isset($_GET['dir']) && strtolower($_GET['dir']) === 'desc' ? 'desc' : 'asc';

// Get users and count
$users = $userModel->getAllUsers($perPage, $offset, $search, $sortBy, $sortDir);
$totalUsers = $userModel->countUsers($search);
$totalPages = ceil($totalUsers / $perPage);

// Available roles for dropdown
$roles = $userModel->getUserRoles();

// Set page title
$pageTitle = 'Pengurusan Pengguna';

// Include header
include '../../app/views/includes/header.php';
?>

<!-- Custom CSS for sortable tables -->
<style>
    /* Sortable table styles */
    th a {
        color: #363636;
        display: flex;
        align-items: center;
        text-decoration: none;
    }
    
    th a:hover {
        color: #3273dc;
    }
    
    th a i.fas {
        font-size: 0.8rem;
    }
</style>

<div class="content">
    <h1 class="title">
        <i class="fas fa-users-cog mr-2"></i> Pengurusan Pengguna
    </h1>
    <h2 class="subtitle">
        Semak dan kemaskini peranan pengguna dalam sistem.
    </h2>
</div>

<?php include '../../app/views/includes/flash_messages.php'; ?>

<div class="card mb-5">
    <div class="card-header">
        <p class="card-header-title">Carian Pengguna</p>
    </div>
    <div class="card-content">
        <form method="GET" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
            <div class="field has-addons">
                <div class="control is-expanded">
                    <input class="input" type="text" name="search" placeholder="Cari mengikut nama, username, emel atau jabatan..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="control">
                    <button type="submit" class="button is-primary">
                        <i class="fas fa-search mr-2"></i> Cari
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <p class="card-header-title">Senarai Pengguna</p>
    </div>
    <div class="card-content">
        <?php if (empty($users)): ?>
            <div class="notification is-info is-light">
                <p class="has-text-centered">Tiada pengguna dijumpai.</p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="table is-fullwidth is-striped is-hoverable">
                    <thead>
                        <tr>
                            <th>
                                <a href="?sort=id&dir=<?= $sortBy === 'id' && $sortDir === 'asc' ? 'desc' : 'asc' ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= $page > 1 ? '&page=' . $page : '' ?>">
                                    ID
                                    <?php if ($sortBy === 'id'): ?>
                                        <i class="fas fa-sort-<?= $sortDir === 'asc' ? 'up' : 'down' ?> ml-1"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="?sort=username&dir=<?= $sortBy === 'username' && $sortDir === 'asc' ? 'desc' : 'asc' ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= $page > 1 ? '&page=' . $page : '' ?>">
                                    Username
                                    <?php if ($sortBy === 'username'): ?>
                                        <i class="fas fa-sort-<?= $sortDir === 'asc' ? 'up' : 'down' ?> ml-1"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="?sort=name&dir=<?= $sortBy === 'name' && $sortDir === 'asc' ? 'desc' : 'asc' ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= $page > 1 ? '&page=' . $page : '' ?>">
                                    Nama
                                    <?php if ($sortBy === 'name'): ?>
                                        <i class="fas fa-sort-<?= $sortDir === 'asc' ? 'up' : 'down' ?> ml-1"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="?sort=email&dir=<?= $sortBy === 'email' && $sortDir === 'asc' ? 'desc' : 'asc' ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= $page > 1 ? '&page=' . $page : '' ?>">
                                    Emel
                                    <?php if ($sortBy === 'email'): ?>
                                        <i class="fas fa-sort-<?= $sortDir === 'asc' ? 'up' : 'down' ?> ml-1"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="?sort=department&dir=<?= $sortBy === 'department' && $sortDir === 'asc' ? 'desc' : 'asc' ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= $page > 1 ? '&page=' . $page : '' ?>">
                                    Jabatan
                                    <?php if ($sortBy === 'department'): ?>
                                        <i class="fas fa-sort-<?= $sortDir === 'asc' ? 'up' : 'down' ?> ml-1"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="?sort=position&dir=<?= $sortBy === 'position' && $sortDir === 'asc' ? 'desc' : 'asc' ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= $page > 1 ? '&page=' . $page : '' ?>">
                                    Jawatan
                                    <?php if ($sortBy === 'position'): ?>
                                        <i class="fas fa-sort-<?= $sortDir === 'asc' ? 'up' : 'down' ?> ml-1"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="?sort=role&dir=<?= $sortBy === 'role' && $sortDir === 'asc' ? 'desc' : 'asc' ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= $page > 1 ? '&page=' . $page : '' ?>">
                                    Peranan
                                    <?php if ($sortBy === 'role'): ?>
                                        <i class="fas fa-sort-<?= $sortDir === 'asc' ? 'up' : 'down' ?> ml-1"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr data-user-id="<?= $user['id'] ?>">
                                <td><?= $user['id'] ?></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['name']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td class="department-cell"><?= htmlspecialchars($user['department'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($user['position'] ?? '-') ?></td>
                                <td>
                                    <span class="tag <?= getTagColorForRole($user['role']) ?>">
                                        <?= $roles[$user['role']] ?? $user['role'] ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="button is-small is-primary" 
                                            onclick="openRoleModal(<?= $user['id'] ?>, '<?= htmlspecialchars($user['name']) ?>', '<?= $user['role'] ?>')">
                                        <i class="fas fa-edit mr-1"></i> Kemaskini Peranan & Jabatan
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav class="pagination is-centered mt-4" role="navigation" aria-label="pagination">
                    <ul class="pagination-list">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li>
                                <a href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($sortBy) ? '&sort=' . $sortBy . '&dir=' . $sortDir : '' ?>" 
                                   class="pagination-link <?= $i === $page ? 'is-current' : '' ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Role Update Modal -->
<div id="roleModal" class="modal">
    <div class="modal-background"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Kemaskini Peranan & Jabatan Pengguna</p>
            <button class="delete" aria-label="close" onclick="closeRoleModal()"></button>
        </header>
        <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
            <section class="modal-card-body">
                <input type="hidden" id="user_id" name="user_id" value="">
                
                <div class="field">
                    <label class="label">Pengguna</label>
                    <div class="control">
                        <input class="input" type="text" id="user_name" readonly>
                    </div>
                </div>
                
                <div class="field">
                    <label class="label">Jabatan</label>
                    <div class="control">
                        <input class="input" type="text" id="department" name="department" placeholder="Masukkan jabatan pengguna">
                    </div>
                    <p class="help">Contoh: ICT, Kewangan, Sumber Manusia, dll.</p>
                </div>
                
                <div class="field">
                    <label class="label">Peranan</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="role" id="role">
                                <?php foreach ($roles as $roleValue => $roleLabel): ?>
                                    <option value="<?= $roleValue ?>"><?= $roleLabel ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button type="submit" name="update_role" class="button is-primary">Simpan Perubahan</button>
                <button type="button" class="button" onclick="closeRoleModal()">Batal</button>
            </footer>
        </form>
    </div>
</div>

<script>
function openRoleModal(userId, userName, role) {
    document.getElementById('user_id').value = userId;
    document.getElementById('user_name').value = userName;
    document.getElementById('role').value = role;
    
    // Get department from the table row
    const departmentCell = document.querySelector(`tr[data-user-id="${userId}"] td.department-cell`);
    if (departmentCell) {
        const department = departmentCell.textContent.trim();
        document.getElementById('department').value = department === '-' ? '' : department;
    }
    
    document.getElementById('roleModal').classList.add('is-active');
}

function closeRoleModal() {
    document.getElementById('roleModal').classList.remove('is-active');
}

// Close modal when clicking on background
document.addEventListener('DOMContentLoaded', () => {
    const modalBg = document.querySelector('.modal-background');
    if (modalBg) {
        modalBg.addEventListener('click', closeRoleModal);
    }
    
    // Close modal when Escape key is pressed
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeRoleModal();
        }
    });
});
</script>

<?php
/**
 * Get tag color for role
 * 
 * @param string $role User role
 * @return string CSS class for tag color
 */
function getTagColorForRole($role) {
    switch ($role) {
        case 'admin':
            return 'is-danger';
        case 'ketua':
            return 'is-info';
        case 'pengarah':
            return 'is-primary';
        case 'user':
            return 'is-success';
        default:
            return 'is-light';
    }
}

// Include footer
include '../../app/views/includes/footer.php';
?> 