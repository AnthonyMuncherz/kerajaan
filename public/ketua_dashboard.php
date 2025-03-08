<?php
/**
 * Ketua Jabatan/Ketua Unit Dashboard
 * Sistem Permohonan Keluar
 */

// Load configuration
require_once '../app/config/config.php';
require_once '../app/config/database.php';
require_once '../app/utils/helpers.php';
require_once '../app/utils/auth.php';
require_once '../app/models/ApplicationModel.php';
require_once '../app/models/UserModel.php';

// Require ketua role
requireKetua();

// Create models
$applicationModel = new ApplicationModel($pdo);
$userModel = new UserModel($pdo);

// Update database schema if needed
$applicationModel->updateSchema();

// Get current user information
$currentUser = $userModel->getUserById($_SESSION['user_id']);

// Get filter parameters
$status = isset($_GET['status']) ? $_GET['status'] : 'pending';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Get applications based on status filter and user's department
$applications = $applicationModel->getAllApplications($status, $perPage, $offset, $currentUser);

// Get counts for dashboard statistics
$counts = $applicationModel->countByStatus(null, $currentUser);

// Set page title
$pageTitle = 'Dashboard Ketua Jabatan/Ketua Unit';

// Include header
include '../app/views/includes/header.php';
?>

<div class="content">
    <h1 class="title">
        <i class="fas fa-tachometer-alt mr-2"></i> Dashboard Ketua Jabatan/Ketua Unit
    </h1>
    <h2 class="subtitle">Urus Permohonan Keluar</h2>
</div>

<?php include '../app/views/includes/flash_messages.php'; ?>

<div class="box mb-4">
    <h4 class="is-size-5 mb-3">Status Permohonan</h4>
    
    <div class="columns is-multiline">
        <div class="column is-3">
            <div class="notification is-warning">
                <h5 class="is-size-5"><?= $counts['pending'] ?? 0 ?></h5>
                <p>Menunggu Kelulusan</p>
            </div>
        </div>
        
        <div class="column is-3">
            <div class="notification is-info">
                <h5 class="is-size-5"><?= $counts['ketua_approved'] ?? 0 ?></h5>
                <p>Diluluskan oleh Ketua</p>
            </div>
        </div>
        
        <div class="column is-3">
            <div class="notification is-success">
                <h5 class="is-size-5"><?= $counts['approved'] ?? 0 ?></h5>
                <p>Telah Diluluskan</p>
            </div>
        </div>
        
        <div class="column is-3">
            <div class="notification is-danger">
                <h5 class="is-size-5"><?= $counts['rejected'] ?? 0 ?></h5>
                <p>Telah Ditolak</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <p class="card-header-title">
            <i class="fas fa-list mr-2"></i> Senarai Permohonan
        </p>
    </div>
    <div class="card-content">
        <div class="buttons mb-3">
            <a href="<?= SITE_URL ?>/ketua_dashboard.php" class="button <?= empty($_GET['status']) ? 'is-primary' : '' ?>">
                Semua
            </a>
            <a href="<?= SITE_URL ?>/ketua_dashboard.php?status=pending" class="button <?= ($status === 'pending' && isset($_GET['status'])) || (empty($_GET['status']) && $status === 'pending') ? 'is-primary' : '' ?>">
                Menunggu Kelulusan
            </a>
            <a href="<?= SITE_URL ?>/ketua_dashboard.php?status=ketua_approved" class="button <?= $status === 'ketua_approved' && isset($_GET['status']) ? 'is-primary' : '' ?>">
                Diluluskan oleh Ketua
            </a>
            <a href="<?= SITE_URL ?>/ketua_dashboard.php?status=approved" class="button <?= $status === 'approved' && isset($_GET['status']) ? 'is-primary' : '' ?>">
                Diluluskan
            </a>
            <a href="<?= SITE_URL ?>/ketua_dashboard.php?status=rejected" class="button <?= $status === 'rejected' && isset($_GET['status']) ? 'is-primary' : '' ?>">
                Ditolak
            </a>
        </div>

        <?php if (empty($applications)): ?>
            <div class="notification is-info is-light">
                Tiada permohonan yang perlu diproses buat masa ini.
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="table is-fullwidth is-hoverable">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Pemohon</th>
                            <th>Jabatan</th>
                            <th>Tarikh</th>
                            <th>Status</th>
                            <th>Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $application): ?>
                            <?php 
                            $statusInfo = getStatusInfo($application['status']); 
                            $approvalDetails = $applicationModel->getApprovalStatusDetails($application);
                            ?>
                            <tr>
                                <td><?= $application['id'] ?></td>
                                <td><?= $application['user_name'] ?></td>
                                <td><?= $application['department'] ?></td>
                                <td><?= date('d/m/Y', strtotime($application['created_at'])) ?></td>
                                <td>
                                    <span class="tag <?= $statusInfo['class'] ?>"><?= $statusInfo['label'] ?></span>
                                </td>
                                <td>
                                    <div class="buttons are-small">
                                        <a href="<?= SITE_URL ?>/ketua_review.php?id=<?= $application['id'] ?>" class="button is-info">
                                            <i class="fas fa-eye mr-1"></i> Lihat
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Include footer
include '../app/views/includes/footer.php';
?> 