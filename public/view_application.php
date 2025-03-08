<?php
/**
 * View Application page
 * Sistem Permohonan Keluar
 */

// Load configuration
require_once '../app/config/config.php';
require_once '../app/config/database.php';
require_once '../app/utils/helpers.php';
require_once '../app/utils/auth.php';
require_once '../app/utils/pdf.php';
require_once '../app/models/ApplicationModel.php';
require_once '../app/models/UserModel.php';

// Require login
requireLogin();

// Create models
$applicationModel = new ApplicationModel($pdo);
$userModel = new UserModel($pdo);

// Get application ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validate ID
if ($id <= 0) {
    header('Location: ' . SITE_URL . '/applications.php?error=invalid_id');
    exit;
}

// Get application details
$application = $applicationModel->getApplicationById($id);

// Check if application exists and user has permission
if (!$application || (!isAdmin() && !isApprover() && $application['user_id'] != $_SESSION['user_id'])) {
    header('Location: ' . SITE_URL . '/applications.php?error=not_found');
    exit;
}

// Get approval status details
$approvalDetails = $applicationModel->getApprovalStatusDetails($application);

// Format dates and times
$startDate = formatDate($application['start_date']);
$endDate = formatDate($application['end_date']);
$exitTime = date('h:i A', strtotime($application['exit_time']));
$returnTime = date('h:i A', strtotime($application['return_time']));
$createdDate = date('d/m/Y h:i A', strtotime($application['created_at']));

// Set page title
$pageTitle = 'Maklumat Permohonan #' . $id;

// Include header
include '../app/views/includes/header.php';
?>

<div class="content">
    <h1 class="title">
        <i class="fas fa-clipboard-list mr-2"></i> Maklumat Permohonan #<?= $id ?>
    </h1>
    <h2 class="subtitle">
        <?php $statusInfo = getStatusInfo($application['status']); ?>
        Status: <span class="tag <?= $statusInfo['class'] ?> is-medium"><?= $statusInfo['label'] ?></span>
    </h2>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="notification is-success is-light">
        <button class="delete"></button>
        Permohonan telah berjaya dihantar.
    </div>
<?php endif; ?>

<div class="buttons mb-5">
    <a href="<?= SITE_URL ?>/applications.php" class="button">
        <i class="fas fa-arrow-left mr-2"></i> Kembali ke Senarai
    </a>
    
    <?php if ($application['status'] === 'approved' && !empty($application['pdf_path'])): ?>
        <a href="<?= SITE_URL ?>/download.php?file=<?= $application['pdf_path'] ?>&regenerate=1" class="button is-success">
            <i class="fas fa-download mr-2"></i> Muat Turun PDF
        </a>
    <?php endif; ?>
    
    <a href="<?= SITE_URL ?>/print_pdf.php?id=<?= $application['id'] ?>&regenerate=1" class="button is-info" target="_blank">
        <i class="fas fa-print mr-2"></i> Cetak
    </a>
    
    <?php if ($application['ketua_approval_status'] === 'approved' || $application['pengarah_approval_status'] === 'approved'): ?>
        <a href="<?= SITE_URL ?>/print_pdf.php?id=<?= $application['id'] ?>&regenerate=1" class="button is-warning" target="_blank">
            <i class="fas fa-sync-alt mr-2"></i> Cetak Dengan Tandatangan Terkini
        </a>
    <?php endif; ?>
</div>

<div class="card mb-5">
    <div class="card-header">
        <p class="card-header-title">
            <i class="fas fa-info-circle mr-2"></i> Maklumat Permohonan
        </p>
    </div>
    <div class="card-content">
        <div class="columns">
            <div class="column">
                <div class="field application-details">
                    <label class="label">Pemohon</label>
                    <p><?= $application['user_name'] ?></p>
                </div>
                
                <div class="field application-details">
                    <label class="label">Jabatan</label>
                    <p><?= $application['department'] ?></p>
                </div>
                
                <div class="field application-details">
                    <label class="label">Jawatan</label>
                    <p><?= $application['position'] ?></p>
                </div>
            </div>
            
            <div class="column">
                <div class="field application-details">
                    <label class="label">Tarikh Permohonan</label>
                    <p><?= $createdDate ?></p>
                </div>
                
                <div class="field application-details">
                    <label class="label">Status</label>
                    <p><span class="tag <?= $statusInfo['class'] ?>"><?= $statusInfo['label'] ?></span></p>
                </div>
                
                <?php if ($application['status'] !== 'pending'): ?>
                    <div class="field application-details">
                        <label class="label">Diluluskan/Ditolak Oleh</label>
                        <p><?= $application['approver_name'] ?? 'Tidak ditetapkan' ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <hr>
        
        <div class="columns">
            <div class="column">
                <div class="field application-details">
                    <label class="label">Jenis Urusan</label>
                    <p><?= $application['purpose_type'] ?></p>
                </div>
                
                <div class="field application-details">
                    <label class="label">Butiran Urusan</label>
                    <p><?= nl2br($application['purpose_details']) ?></p>
                </div>
                
                <div class="field application-details">
                    <label class="label">Lokasi Bertugas</label>
                    <p><?= $application['duty_location'] ?></p>
                </div>
                
                <div class="field application-details">
                    <label class="label">Jenis Pengangkutan</label>
                    <p><?= $application['transportation_type'] ?></p>
                </div>
            </div>
            
            <div class="column">
                <div class="field application-details">
                    <label class="label">Tarikh Mula</label>
                    <p><?= $startDate ?></p>
                </div>
                
                <div class="field application-details">
                    <label class="label">Tarikh Tamat</label>
                    <p><?= $endDate ?></p>
                </div>
                
                <div class="field application-details">
                    <label class="label">Masa Keluar</label>
                    <p><?= $exitTime ?></p>
                </div>
                
                <div class="field application-details">
                    <label class="label">Masa Kembali</label>
                    <p><?= $returnTime ?></p>
                </div>
            </div>
        </div>
        
        <?php if (!empty($application['remarks'])): ?>
            <hr>
            
            <div class="field application-details">
                <label class="label">Catatan</label>
                <p><?= nl2br($application['remarks']) ?></p>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($application['attachment_path'])): ?>
            <hr>
            
            <div class="field application-details">
                <label class="label">LAMPIRAN SURAT KELUAR ATAS TUGAS RASMI</label>
                <p>
                    <a href="<?= SITE_URL ?>/download.php?file=<?= $application['attachment_path'] ?>" class="button is-small is-primary">
                        <i class="fas fa-file-pdf mr-2"></i> Lihat Lampiran
                    </a>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Display kelulusan details -->
<?php if (isset($approvalDetails) && !empty($approvalDetails)): ?>
<div class="card mb-5">
    <div class="card-header">
        <p class="card-header-title">
            <i class="fas fa-clipboard-check mr-2"></i> Status Kelulusan
        </p>
    </div>
    <div class="card-content">
        <div class="columns">
            <div class="column">
                <h4 class="subtitle is-5">Ketua Jabatan/Ketua Unit</h4>
                <div class="field application-details">
                    <label class="label">Status</label>
                    <p>
                        <span class="tag <?= $approvalDetails['ketua']['class'] ?>">
                            <?= $approvalDetails['ketua']['label'] ?>
                        </span>
                    </p>
                </div>
                
                <?php if ($approvalDetails['ketua']['approver_name']): ?>
                    <div class="field application-details">
                        <label class="label">Dilulus/Ditolak Oleh</label>
                        <p><?= $approvalDetails['ketua']['approver_name'] ?></p>
                    </div>
                    
                    <div class="field application-details">
                        <label class="label">Tarikh</label>
                        <p><?= date('d/m/Y h:i A', strtotime($approvalDetails['ketua']['approval_date'])) ?></p>
                    </div>
                    
                    <?php if (!empty($approvalDetails['ketua']['remarks'])): ?>
                        <div class="field application-details">
                            <label class="label">Catatan</label>
                            <div class="notification is-light is-<?= $approvalDetails['ketua']['status'] === 'approved' ? 'success' : ($approvalDetails['ketua']['status'] === 'rejected' ? 'danger' : 'info') ?>">
                                <?= nl2br($approvalDetails['ketua']['remarks']) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <div class="column">
                <h4 class="subtitle is-5">Pengarah</h4>
                <div class="field application-details">
                    <label class="label">Status</label>
                    <p>
                        <span class="tag <?= $approvalDetails['pengarah']['class'] ?>">
                            <?= $approvalDetails['pengarah']['label'] ?>
                        </span>
                    </p>
                </div>
                
                <?php if ($approvalDetails['pengarah']['approver_name']): ?>
                    <div class="field application-details">
                        <label class="label">Dilulus/Ditolak Oleh</label>
                        <p><?= $approvalDetails['pengarah']['approver_name'] ?></p>
                    </div>
                    
                    <div class="field application-details">
                        <label class="label">Tarikh</label>
                        <p><?= date('d/m/Y h:i A', strtotime($approvalDetails['pengarah']['approval_date'])) ?></p>
                    </div>
                    
                    <?php if (!empty($approvalDetails['pengarah']['remarks'])): ?>
                        <div class="field application-details">
                            <label class="label">Catatan</label>
                            <div class="notification is-light is-<?= $approvalDetails['pengarah']['status'] === 'approved' ? 'success' : ($approvalDetails['pengarah']['status'] === 'rejected' ? 'danger' : 'info') ?>">
                                <?= nl2br($approvalDetails['pengarah']['remarks']) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (isAdmin() && $application['status'] === 'pending'): ?>
    <div class="card no-print">
        <div class="card-header">
            <p class="card-header-title">
                <i class="fas fa-info-circle mr-2"></i> Maklumat Kelulusan
            </p>
        </div>
        <div class="card-content">
            <div class="notification is-info is-light">
                <p>Permohonan ini perlu diluluskan oleh:</p>
                <ol style="margin-left: 20px; margin-top: 10px;">
                    <li>Ketua Jabatan/Ketua Unit</li>
                    <li>Pengarah</li>
                </ol>
                <p class="mt-3">Status semasa: <?= $statusInfo['label'] ?></p>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
// Include footer
include '../app/views/includes/footer.php';
?> 