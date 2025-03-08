<?php
/**
 * Ketua Review Application page
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

// Require ketua role
requireKetua();

// Create models
$applicationModel = new ApplicationModel($pdo);
$userModel = new UserModel($pdo);

// Get application ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validate ID
if ($id <= 0) {
    header('Location: ' . SITE_URL . '/ketua_dashboard.php?error=invalid_id');
    exit;
}

// Get application details
$application = $applicationModel->getApplicationById($id);

// Check if application exists
if (!$application) {
    header('Location: ' . SITE_URL . '/ketua_dashboard.php?error=not_found');
    exit;
}

// Handle approve or reject
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve']) || isset($_POST['reject'])) {
        $status = isset($_POST['approve']) ? 'approved' : 'rejected';
        $remarks = sanitizeInput($_POST['remarks'] ?? '');
        
        // Update application status
        $success = $applicationModel->updateKetuaStatus($id, $status, $_SESSION['user_id'], $remarks);
        
        if ($success) {
            // Set success message
            if ($status === 'approved') {
                setFlashMessage('success', 'Permohonan telah diluluskan.');
            } else {
                setFlashMessage('success', 'Permohonan telah ditolak.');
            }
            
            // Redirect to refresh application data
            header('Location: ' . SITE_URL . '/ketua_dashboard.php');
            exit;
        } else {
            $error = 'Kemaskini status tidak berjaya. Sila cuba lagi.';
        }
    }
}

// Get refreshed application data
$application = $applicationModel->getApplicationById($id);
$approvalDetails = $applicationModel->getApprovalStatusDetails($application);

// Format dates and times
$startDate = formatDate($application['start_date']);
$endDate = formatDate($application['end_date']);
$exitTime = date('h:i A', strtotime($application['exit_time']));
$returnTime = date('h:i A', strtotime($application['return_time']));
$createdDate = date('d/m/Y h:i A', strtotime($application['created_at']));

// Set page title
$pageTitle = 'Semakan Permohonan #' . $id;

// Include header
include '../app/views/includes/header.php';
?>

<div class="content">
    <h1 class="title">
        <i class="fas fa-clipboard-check mr-2"></i> Semakan Permohonan #<?= $id ?>
    </h1>
    <h2 class="subtitle">
        <?php $statusInfo = getStatusInfo($application['status']); ?>
        Status: <span class="tag <?= $statusInfo['class'] ?> is-medium"><?= $statusInfo['label'] ?></span>
    </h2>
</div>

<?php if (isset($error)): ?>
    <div class="notification is-danger is-light">
        <button class="delete"></button>
        <?= $error ?>
    </div>
<?php endif; ?>

<div class="buttons mb-5">
    <a href="<?= SITE_URL ?>/ketua_dashboard.php" class="button">
        <i class="fas fa-arrow-left mr-2"></i> Kembali ke Dashboard
    </a>
    
    <a href="<?= SITE_URL ?>/print_pdf.php?id=<?= $application['id'] ?>&regenerate=1" class="button is-info" target="_blank">
        <i class="fas fa-print mr-2"></i> Pratonton Borang
    </a>
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
    </div>
</div>

<?php if ($application['ketua_approval_status'] === 'pending'): ?>
    <div class="card no-print">
        <div class="card-header">
            <p class="card-header-title">
                <i class="fas fa-tasks mr-2"></i> Tindakan Ketua Jabatan/Ketua Unit
            </p>
        </div>
        <div class="card-content">
            <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>?id=<?= $id ?>">
                <div class="field">
                    <label class="label">Catatan (jika ada)</label>
                    <div class="control">
                        <textarea class="textarea" name="remarks" placeholder="Masukkan catatan untuk permohonan ini"></textarea>
                    </div>
                </div>
                
                <div class="field is-grouped">
                    <div class="control">
                        <button type="submit" name="approve" class="button is-success">
                            <i class="fas fa-check mr-2"></i> Lulus
                        </button>
                    </div>
                    <div class="control">
                        <button type="submit" name="reject" class="button is-danger">
                            <i class="fas fa-times mr-2"></i> Tolak
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-header">
            <p class="card-header-title">
                <i class="fas fa-clipboard-check mr-2"></i> Maklumat Kelulusan
            </p>
        </div>
        <div class="card-content">
            <div class="columns">
                <div class="column">
                    <div class="field application-details">
                        <label class="label">Status Ketua Jabatan/Unit</label>
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
                    <?php endif; ?>
                </div>
                
                <div class="column">
                    <?php if ($approvalDetails['ketua']['remarks']): ?>
                        <div class="field application-details">
                            <label class="label">Catatan</label>
                            <p><?= nl2br($approvalDetails['ketua']['remarks']) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (isset($approvalDetails['pengarah']['status']) && $approvalDetails['pengarah']['status'] !== 'pending'): ?>
                <hr>
                <div class="columns">
                    <div class="column">
                        <div class="field application-details">
                            <label class="label">Status Pengarah</label>
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
                        <?php endif; ?>
                    </div>
                    
                    <div class="column">
                        <?php if ($approvalDetails['pengarah']['remarks']): ?>
                            <div class="field application-details">
                                <label class="label">Catatan</label>
                                <p><?= nl2br($approvalDetails['pengarah']['remarks']) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php
// Include footer
include '../app/views/includes/footer.php';
?> 