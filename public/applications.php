<?php
/**
 * Applications List page
 * Sistem Permohonan Keluar
 */

// Load configuration
require_once '../app/config/config.php';
require_once '../app/config/database.php';
require_once '../app/utils/helpers.php';
require_once '../app/utils/auth.php';
require_once '../app/models/ApplicationModel.php';
require_once '../app/models/UserModel.php';

// Require login
requireLogin();

// Create models
$applicationModel = new ApplicationModel($pdo);
$userModel = new UserModel($pdo);

// Get user ID and information
$userId = $_SESSION['user_id'];
$currentUser = $userModel->getUserById($userId);
$isAdmin = isAdmin();
$isApprover = isApprover();

// Get status filter
$statusFilter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';

// Set up pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Get applications based on user role
if ($isAdmin) {
    // Admin sees all applications
    $applications = $applicationModel->getAllApplications($statusFilter, $perPage, $offset, $currentUser);
    $counts = $applicationModel->countByStatus(null, $currentUser);
} else if ($currentUser['role'] === 'ketua' || $currentUser['role'] === 'pengarah') {
    // Ketua and Pengarah see applications based on their role
    $applications = $applicationModel->getAllApplications($statusFilter, $perPage, $offset, $currentUser);
    $counts = $applicationModel->countByStatus(null, $currentUser);
} else {
    // Normal users only see their own applications
    $applications = $applicationModel->getUserApplications($userId, $perPage, $offset);
    $counts = $applicationModel->countByStatus($userId);
}

// Set page title
$pageTitle = 'Senarai Permohonan';

// Include header
include '../app/views/includes/header.php';
?>

<div class="content">
    <h1 class="title">
        <i class="fas fa-list mr-2"></i> Senarai Permohonan
    </h1>
    <h2 class="subtitle">
        Lihat dan urus permohonan keluar anda.
    </h2>
</div>

<!-- Error messages -->
<?php if (isset($_GET['error'])): ?>
    <?php $errorMsg = ''; ?>
    <?php if ($_GET['error'] === 'invalid_id'): ?>
        <?php $errorMsg = 'ID permohonan tidak sah.'; ?>
    <?php elseif ($_GET['error'] === 'not_found'): ?>
        <?php $errorMsg = 'Permohonan tidak dijumpai atau anda tidak mempunyai akses.'; ?>
    <?php endif; ?>
    
    <?php if (!empty($errorMsg)): ?>
        <div class="notification is-danger is-light">
            <button class="delete"></button>
            <?= $errorMsg ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- Stats and filters -->
<div class="level mb-5">
    <div class="level-left">
        <div class="level-item">
            <div class="buttons has-addons">
                <a href="<?= SITE_URL ?>/applications.php" class="button <?= empty($statusFilter) ? 'is-primary is-selected' : '' ?>">
                    Semua (<?= $counts['total'] ?>)
                </a>
                <a href="<?= SITE_URL ?>/applications.php?status=pending" class="button <?= $statusFilter === 'pending' ? 'is-warning is-selected' : '' ?>">
                    Menunggu (<?= $counts['pending'] ?>)
                </a>
                <a href="<?= SITE_URL ?>/applications.php?status=approved" class="button <?= $statusFilter === 'approved' ? 'is-success is-selected' : '' ?>">
                    Diluluskan (<?= $counts['approved'] ?>)
                </a>
                <a href="<?= SITE_URL ?>/applications.php?status=rejected" class="button <?= $statusFilter === 'rejected' ? 'is-danger is-selected' : '' ?>">
                    Ditolak (<?= $counts['rejected'] ?>)
                </a>
            </div>
        </div>
    </div>
    <div class="level-right">
        <div class="level-item">
            <a href="<?= SITE_URL ?>/create_application.php" class="button is-primary">
                <i class="fas fa-plus-circle mr-2"></i> Permohonan Baru
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-content">
        <?php if (empty($applications)): ?>
            <div class="notification is-info is-light">
                <p class="has-text-centered">Tiada permohonan dijumpai.</p>
                
                <?php if (empty($counts['total'])): ?>
                    <p class="has-text-centered mt-3">
                        <a href="<?= SITE_URL ?>/create_application.php" class="button is-primary is-small">
                            <i class="fas fa-plus-circle mr-2"></i> Cipta Permohonan Pertama Anda
                        </a>
                    </p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="table is-fullwidth is-striped is-hoverable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <?php if ($isAdmin): ?>
                                <th>Pemohon</th>
                                <th>Jabatan</th>
                            <?php endif; ?>
                            <th>Jenis Urusan</th>
                            <th>Lokasi</th>
                            <th>Tarikh Mula</th>
                            <th>Tarikh Tamat</th>
                            <th>Status</th>
                            <th>Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $app): ?>
                            <?php $statusInfo = getStatusInfo($app['status']); ?>
                            <tr>
                                <td><?= $app['id'] ?></td>
                                <?php if ($isAdmin): ?>
                                    <td><?= $app['user_name'] ?></td>
                                    <td><?= $app['department'] ?></td>
                                <?php endif; ?>
                                <td><?= $app['purpose_type'] ?></td>
                                <td><?= $app['duty_location'] ?></td>
                                <td><?= formatDate($app['start_date']) ?></td>
                                <td><?= formatDate($app['end_date']) ?></td>
                                <td>
                                    <span class="tag <?= $statusInfo['class'] ?>"><?= $statusInfo['label'] ?></span>
                                </td>
                                <td>
                                    <div class="buttons are-small">
                                        <a href="<?= SITE_URL ?>/view_application.php?id=<?= $app['id'] ?>" class="button is-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($app['status'] === 'approved' && !empty($app['pdf_path'])): ?>
                                            <a href="<?= SITE_URL ?>/download.php?file=<?= $app['pdf_path'] ?>" class="button is-success">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination placeholder - would need more code for actual pagination -->
            <nav class="pagination is-centered mt-4" role="navigation" aria-label="pagination">
                <?php if ($page > 1): ?>
                    <a href="<?= SITE_URL ?>/applications.php?page=<?= $page - 1 ?><?= !empty($statusFilter) ? '&status=' . $statusFilter : '' ?>" class="pagination-previous">Sebelumnya</a>
                <?php else: ?>
                    <a class="pagination-previous" disabled>Sebelumnya</a>
                <?php endif; ?>
                
                <?php if (count($applications) >= $perPage): ?>
                    <a href="<?= SITE_URL ?>/applications.php?page=<?= $page + 1 ?><?= !empty($statusFilter) ? '&status=' . $statusFilter : '' ?>" class="pagination-next">Seterusnya</a>
                <?php else: ?>
                    <a class="pagination-next" disabled>Seterusnya</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php
// Include footer
include '../app/views/includes/footer.php';
?> 