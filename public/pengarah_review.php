<?php
/**
 * Pengarah Review Application page
 * Sistem Permohonan Keluar
 */

// Start output buffering to prevent any content leakage
ob_start();

// Set content type explicitly
header('Content-Type: text/html; charset=UTF-8');

// Enable error reporting for debugging (temporary)
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    // Load configuration
    require_once '../app/config/config.php';
    require_once '../app/config/database.php';
    require_once '../app/utils/helpers.php';
    require_once '../app/utils/auth.php';
    require_once '../app/utils/pdf.php';
    require_once '../app/models/ApplicationModel.php';
    require_once '../app/models/UserModel.php';

    // Require pengarah role
    requirePengarah();

    // Create models
    $applicationModel = new ApplicationModel($pdo);
    $userModel = new UserModel($pdo);

    // Get application ID
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    // Validate ID
    if ($id <= 0) {
        header('Location: ' . SITE_URL . '/pengarah_dashboard.php?error=invalid_id');
        exit;
    }

    // Get application details
    $application = $applicationModel->getApplicationById($id);

    // Check if application exists
    if (!$application) {
        header('Location: ' . SITE_URL . '/pengarah_dashboard.php?error=not_found');
        exit;
    }

    // Handle approve or reject
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['approve']) || isset($_POST['reject'])) {
            $status = isset($_POST['approve']) ? 'approved' : 'rejected';
            $remarks = sanitizeInput($_POST['remarks'] ?? '');
            
            // Get Form 240km approval option if it exists
            $form240kmApproval = isset($_POST['form_240km_approval']) ? sanitizeInput($_POST['form_240km_approval']) : null;
            
            // Update application status
            $success = $applicationModel->updatePengarahStatus($id, $status, $_SESSION['user_id'], $remarks);
            
            // If distance is >= 240km and we have form data, update the form data with pengarah approval
            $distance = floatval($application['distance_estimate'] ?? 0);
            if ($success && $status === 'approved' && $distance >= 240 && !empty($application['form_240km_data']) && !empty($form240kmApproval)) {
                // Decode the existing form data
                $formDataString = html_entity_decode($application['form_240km_data']);
                $form240kmData = json_decode($formDataString, true);
                
                // Add pengarah approval information
                $form240kmData['pengarah_approval'] = $form240kmApproval;
                $form240kmData['pengarah_name'] = $_SESSION['user_name'];
                $form240kmData['pengarah_approval_date'] = date('Y-m-d H:i:s');
                
                // Update the application with the new form data
                $stmt = $pdo->prepare("UPDATE applications SET form_240km_data = ? WHERE id = ?");
                $stmt->execute([json_encode($form240kmData), $id]);
            }
            
            if ($success) {
                // If approved, generate PDF
                if ($status === 'approved') {
                    // Get user data
                    $user = $userModel->getUserById($application['user_id']);
                    
                    // Generate PDF
                    $pdfFile = generateExitFormPDF($application, $user);
                    
                    if ($pdfFile) {
                        // Set success message
                        setFlashMessage('success', 'Permohonan telah diluluskan dan PDF telah dijana.');
                    } else {
                        // Set warning message
                        setFlashMessage('warning', 'Permohonan telah diluluskan tetapi PDF tidak dapat dijana.');
                    }
                } else {
                    // Set success message for rejection
                    setFlashMessage('success', 'Permohonan telah ditolak.');
                }
                
                // Redirect to dashboard
                header('Location: ' . SITE_URL . '/pengarah_dashboard.php');
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
    $pageTitle = 'Pengesahan Permohonan #' . $id;

    // Include header
    include '../app/views/includes/header.php';
} catch (Exception $e) {
    // Log error for debugging
    error_log('Error in pengarah_review.php: ' . $e->getMessage());
    
    // Display error details for debugging
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Error</title></head><body>';
    echo '<h1>Error Occurred</h1>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    echo '</body></html>';
    exit;
}
?>

<div class="content">
    <h1 class="title">
        <i class="fas fa-clipboard-check mr-2"></i> Pengesahan Permohonan #<?= $id ?>
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
    <a href="<?= SITE_URL ?>/pengarah_dashboard.php" class="button">
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

<!-- Ketua Approval Info -->
<div class="card mb-5">
    <div class="card-header">
        <p class="card-header-title">
            <i class="fas fa-clipboard-check mr-2"></i> Maklumat Kelulusan Ketua Jabatan/Unit
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
    </div>
</div>

<?php if ($application['status'] === 'ketua_approved' && $application['pengarah_approval_status'] === 'pending'): ?>
    <div class="card no-print">
        <div class="card-header">
            <p class="card-header-title">
                <i class="fas fa-tasks mr-2"></i> Tindakan Pengarah
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
                
                <?php 
                // Check if this is a 240km form application
                $has240kmForm = false;
                $distance = floatval($application['distance_estimate'] ?? 0);
                
                // Debug output - temporary
                echo "<!-- Debug info: Distance = $distance -->";
                echo "<!-- Form data exists: " . (!empty($application['form_240km_data']) ? 'Yes' : 'No') . " -->";
                
                if ($distance >= 240 && !empty($application['form_240km_data'])): 
                    $has240kmForm = true;
                    echo "<!-- Form 240km section enabled -->";
                ?>
                <div class="box mt-4">
                    <h4 class="title is-5">Kelulusan Borang 240KM</h4>
                    
                    <div class="field">
                        <div class="control">
                            <label class="radio">
                                <input type="radio" name="form_240km_approval" value="diluluskan_biasa" checked>
                                Diluluskan termaktub kepada syarat-syarat di dalam Pekeliling Perbendaharaan Bil. 3 Tahun 2003
                            </label>
                        </div>
                        <div class="control mt-2">
                            <label class="radio">
                                <input type="radio" name="form_240km_approval" value="diluluskan_tambang">
                                Diluluskan dengan menuntut tambang gantian
                            </label>
                        </div>
                        <div class="control mt-2">
                            <label class="radio">
                                <input type="radio" name="form_240km_approval" value="tidak_diluluskan">
                                Tidak diluluskan
                            </label>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
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
    <!-- Pengarah Approval Info -->
    <?php if (isset($approvalDetails['pengarah']['status']) && $approvalDetails['pengarah']['status'] !== 'pending'): ?>
        <div class="card">
            <div class="card-header">
                <p class="card-header-title">
                    <i class="fas fa-clipboard-check mr-2"></i> Maklumat Kelulusan Pengarah
                </p>
            </div>
            <div class="card-content">
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
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php
try {
    // Include footer
    include '../app/views/includes/footer.php';
} catch (Exception $e) {
    // Log error for debugging
    error_log('Error in footer inclusion: ' . $e->getMessage());
    
    // Close HTML structure
    echo '</div></section></body></html>';
}

// End output buffering and flush content
ob_end_flush();
?> 