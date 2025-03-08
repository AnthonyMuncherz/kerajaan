<?php
/**
 * Admin Applications Management page
 * Sistem Permohonan Keluar
 */

// Load configuration
require_once '../../app/config/config.php';
require_once '../../app/config/database.php';
require_once '../../app/utils/helpers.php';
require_once '../../app/utils/auth.php';
require_once '../../app/models/ApplicationModel.php';
require_once '../../app/models/UserModel.php';

// Require admin role
requireAdmin();

// Create models
$applicationModel = new ApplicationModel($pdo);
$userModel = new UserModel($pdo);

// Set up pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Get status filter
$statusFilter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';

// Get applications
$applications = $applicationModel->getAllApplications($statusFilter, $perPage, $offset);

// Count applications by status
$counts = $applicationModel->countByStatus();
$totalPages = ceil($counts['total'] / $perPage);

// Set page title
$pageTitle = 'Pengurusan Permohonan';

// Include header
include '../../app/views/includes/header.php';
?>

<div class="content">
    <h1 class="title">
        <i class="fas fa-clipboard-list mr-2"></i> Pengurusan Permohonan
    </h1>
    <h2 class="subtitle">
        Semak dan urus permohonan keluar dari semua pengguna.
    </h2>
</div>

<?php include '../../app/views/includes/flash_messages.php'; ?>

<!-- Status filters -->
<div class="level mb-5">
    <div class="level-left">
        <div class="level-item">
            <div class="buttons has-addons">
                <a href="<?= SITE_URL ?>/admin/applications.php" class="button <?= empty($statusFilter) ? 'is-primary is-selected' : '' ?>">
                    <span>Semua (<?= $counts['total'] ?>)</span>
                </a>
                <a href="<?= SITE_URL ?>/admin/applications.php?status=pending" class="button <?= $statusFilter === 'pending' ? 'is-warning is-selected' : '' ?>">
                    <span>Menunggu (<?= $counts['pending'] ?>)</span>
                </a>
                <a href="<?= SITE_URL ?>/admin/applications.php?status=ketua_approved" class="button <?= $statusFilter === 'ketua_approved' ? 'is-info is-selected' : '' ?>">
                    <span>Diluluskan Ketua (<?= $counts['ketua_approved'] ?>)</span>
                </a>
                <a href="<?= SITE_URL ?>/admin/applications.php?status=approved" class="button <?= $statusFilter === 'approved' ? 'is-success is-selected' : '' ?>">
                    <span>Diluluskan (<?= $counts['approved'] ?>)</span>
                </a>
                <a href="<?= SITE_URL ?>/admin/applications.php?status=rejected" class="button <?= $statusFilter === 'rejected' ? 'is-danger is-selected' : '' ?>">
                    <span>Ditolak (<?= $counts['rejected'] ?>)</span>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-content">
        <?php if (empty($applications)): ?>
            <div class="notification is-info is-light">
                <p class="has-text-centered">Tiada permohonan dijumpai.</p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="table is-fullwidth is-striped is-hoverable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Pemohon</th>
                            <th>Jabatan</th>
                            <th>Jenis Urusan</th>
                            <th>Lokasi</th>
                            <th>Tarikh Mula</th>
                            <th>Status</th>
                            <th>Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $application): ?>
                            <?php $statusInfo = getStatusInfo($application['status']); ?>
                            <tr>
                                <td><?= $application['id'] ?></td>
                                <td><?= htmlspecialchars($application['user_name']) ?></td>
                                <td><?= htmlspecialchars($application['department']) ?></td>
                                <td><?= htmlspecialchars($application['purpose_type']) ?></td>
                                <td><?= htmlspecialchars($application['duty_location']) ?></td>
                                <td><?= formatDate($application['start_date']) ?></td>
                                <td>
                                    <span class="tag <?= $statusInfo['class'] ?>">
                                        <?= $statusInfo['label'] ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="buttons are-small">
                                        <a href="<?= SITE_URL ?>/view_application.php?id=<?= $application['id'] ?>" class="button is-info">
                                            <i class="fas fa-eye mr-1"></i> Lihat
                                        </a>
                                        <?php if ($application['status'] === 'approved'): ?>
                                            <a href="<?= SITE_URL ?>/print_pdf.php?id=<?= $application['id'] ?>&regenerate=1" class="button is-success" target="_blank">
                                                <i class="fas fa-print mr-1"></i> Cetak
                                            </a>
                                        <?php endif; ?>
                                    </div>
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
                                <a href="?page=<?= $i ?><?= !empty($statusFilter) ? '&status=' . urlencode($statusFilter) : '' ?>" 
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

<?php
// Include footer
include '../../app/views/includes/footer.php';
?> 