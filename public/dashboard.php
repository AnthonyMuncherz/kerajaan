<?php
/**
 * Dashboard page
 * Sistem Permohonan Keluar
 */

// Load configuration
require_once '../app/config/config.php';
require_once '../app/config/database.php';
require_once '../app/utils/helpers.php';
require_once '../app/utils/auth.php';
require_once '../app/models/ApplicationModel.php';

// Require login
requireLogin();

// Create application model
$applicationModel = new ApplicationModel($pdo);

// Get user stats
$userId = $_SESSION['user_id'];
$stats = $applicationModel->countByStatus($userId);
$recentApplications = $applicationModel->getRecentApplications($userId, 5);

// Set page title
$pageTitle = 'Dashboard';

// Include header
include '../app/views/includes/header.php';
?>

<div class="content">
    <h1 class="title">
        <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
    </h1>
    <h2 class="subtitle">
        Selamat datang, <?= $_SESSION['user_name'] ?>!
    </h2>
</div>

<!-- Statistics -->
<div class="dashboard-stats mb-5">
    <div class="columns">
        <div class="column">
            <div class="card has-background-primary-light">
                <div class="card-content">
                    <div class="level is-mobile">
                        <div class="level-left">
                            <div>
                                <p class="heading has-text-primary">Jumlah Permohonan</p>
                                <p class="stat-value has-text-primary"><?= $stats['total'] ?></p>
                            </div>
                        </div>
                        <div class="level-right">
                            <span class="icon is-large has-text-primary">
                                <i class="fas fa-clipboard-list fa-2x"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="column">
            <div class="card has-background-warning-light">
                <div class="card-content">
                    <div class="level is-mobile">
                        <div class="level-left">
                            <div>
                                <p class="heading has-text-warning-dark">Menunggu Kelulusan</p>
                                <p class="stat-value has-text-warning-dark"><?= $stats['pending'] ?></p>
                            </div>
                        </div>
                        <div class="level-right">
                            <span class="icon is-large has-text-warning-dark">
                                <i class="fas fa-clock fa-2x"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="column">
            <div class="card has-background-success-light">
                <div class="card-content">
                    <div class="level is-mobile">
                        <div class="level-left">
                            <div>
                                <p class="heading has-text-success">Diluluskan</p>
                                <p class="stat-value has-text-success"><?= $stats['approved'] ?></p>
                            </div>
                        </div>
                        <div class="level-right">
                            <span class="icon is-large has-text-success">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="column">
            <div class="card has-background-danger-light">
                <div class="card-content">
                    <div class="level is-mobile">
                        <div class="level-left">
                            <div>
                                <p class="heading has-text-danger">Ditolak</p>
                                <p class="stat-value has-text-danger"><?= $stats['rejected'] ?></p>
                            </div>
                        </div>
                        <div class="level-right">
                            <span class="icon is-large has-text-danger">
                                <i class="fas fa-times-circle fa-2x"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card mb-5">
    <div class="card-header">
        <p class="card-header-title">
            <i class="fas fa-bolt mr-2"></i> Tindakan Pantas
        </p>
    </div>
    <div class="card-content">
        <div class="buttons">
            <a href="<?= SITE_URL ?>/create_application.php" class="button is-primary">
                <i class="fas fa-plus-circle mr-2"></i> Permohonan Baru
            </a>
            <a href="<?= SITE_URL ?>/applications.php" class="button is-link">
                <i class="fas fa-list mr-2"></i> Senarai Permohonan
            </a>
            <a href="<?= SITE_URL ?>/profile.php" class="button is-info">
                <i class="fas fa-user-edit mr-2"></i> Kemaskini Profil
            </a>
        </div>
    </div>
</div>

<!-- Recent Applications -->
<div class="card">
    <div class="card-header">
        <p class="card-header-title">
            <i class="fas fa-history mr-2"></i> Permohonan Terkini
        </p>
    </div>
    <div class="card-content">
        <?php if (empty($recentApplications)): ?>
            <p class="has-text-centered">Tiada permohonan yang dijumpai.</p>
        <?php else: ?>
            <div class="table-container">
                <table class="table is-fullwidth is-striped is-hoverable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tarikh Mula</th>
                            <th>Tarikh Tamat</th>
                            <th>Tujuan</th>
                            <th>Status</th>
                            <th>Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentApplications as $app): ?>
                            <?php $statusInfo = getStatusInfo($app['status']); ?>
                            <tr>
                                <td><?= $app['id'] ?></td>
                                <td><?= formatDate($app['start_date']) ?></td>
                                <td><?= formatDate($app['end_date']) ?></td>
                                <td><?= $app['purpose_type'] ?></td>
                                <td>
                                    <span class="tag <?= $statusInfo['class'] ?>"><?= $statusInfo['label'] ?></span>
                                </td>
                                <td>
                                    <a href="<?= SITE_URL ?>/view_application.php?id=<?= $app['id'] ?>" class="button is-small is-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($app['status'] === 'approved' && !empty($app['pdf_path'])): ?>
                                        <a href="<?= SITE_URL ?>/download.php?file=<?= $app['pdf_path'] ?>" class="button is-small is-success">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="has-text-centered mt-4">
                <a href="<?= SITE_URL ?>/applications.php" class="button is-link is-light">
                    Lihat Semua Permohonan
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Include footer
include '../app/views/includes/footer.php';
?> 