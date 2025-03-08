<?php
/**
 * Simplified Pengarah Review Application page
 * For troubleshooting rendering issues
 */

// Start output buffering
ob_start();

// Basic HTML structure
echo '<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengesahan Permohonan (Simplified View)</title>
    
    <!-- Bulma CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <section class="section">
        <div class="container">
            <h1 class="title">
                <i class="fas fa-clipboard-check mr-2"></i> Pengesahan Permohonan (Simplified View)
            </h1>';

try {
    // Load configuration
    require_once '../app/config/config.php';
    require_once '../app/config/database.php';
    require_once '../app/utils/helpers.php';
    require_once '../app/utils/auth.php';
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
        echo '<div class="notification is-danger">ID Permohonan tidak sah.</div>';
        echo '<a href="' . SITE_URL . '/pengarah_dashboard.php" class="button">Kembali ke Dashboard</a>';
    } else {
        // Get application details
        $application = $applicationModel->getApplicationById($id);

        // Check if application exists
        if (!$application) {
            echo '<div class="notification is-danger">Permohonan tidak dijumpai.</div>';
            echo '<a href="' . SITE_URL . '/pengarah_dashboard.php" class="button">Kembali ke Dashboard</a>';
        } else {
            // Show application details
            echo '<div class="card mb-5">
                <div class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-info-circle mr-2"></i> Maklumat Permohonan #' . $id . '
                    </p>
                </div>
                <div class="card-content">
                    <p><strong>Pemohon:</strong> ' . htmlspecialchars($application['user_name']) . '</p>
                    <p><strong>Jabatan:</strong> ' . htmlspecialchars($application['department']) . '</p>
                    <p><strong>Jenis Urusan:</strong> ' . htmlspecialchars($application['purpose_type']) . '</p>
                    <p><strong>Status:</strong> ' . htmlspecialchars($application['status']) . '</p>
                </div>
            </div>';

            // If it's pending, show approval form
            if ($application['status'] === 'ketua_approved' && $application['pengarah_approval_status'] === 'pending') {
                echo '<div class="card mb-5">
                    <div class="card-header">
                        <p class="card-header-title">
                            <i class="fas fa-tasks mr-2"></i> Tindakan Pengarah
                        </p>
                    </div>
                    <div class="card-content">
                        <form method="POST" action="' . SITE_URL . '/pengarah_review.php?id=' . $id . '">
                            <div class="field">
                                <label class="label">Catatan (jika ada)</label>
                                <div class="control">
                                    <textarea class="textarea" name="remarks" placeholder="Masukkan catatan untuk permohonan ini"></textarea>
                                </div>
                            </div>';

                // Check if this is a 240km form application
                $distance = floatval($application['distance_estimate'] ?? 0);
                if ($distance >= 240 && !empty($application['form_240km_data'])) {
                    echo '<div class="box mt-4">
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
                    </div>';
                }

                echo '<div class="field is-grouped">
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
            </div>';
            }
        }
    }
} catch (Exception $e) {
    echo '<div class="notification is-danger is-light">';
    echo '<strong>Error:</strong> ' . htmlspecialchars($e->getMessage());
    echo '</div>';
}

echo '</div>
    </section>
    
    <footer class="footer">
        <div class="content has-text-centered">
            <p>
                <strong>' . SITE_NAME . '</strong> - Sistem Pengurusan Permohonan Keluar
                <br>
                &copy; ' . date('Y') . ' Institut Pendidikan Guru
            </p>
        </div>
    </footer>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>';

// End output buffering and flush content
ob_end_flush();
?> 