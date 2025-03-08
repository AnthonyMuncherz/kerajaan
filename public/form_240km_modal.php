<?php
/**
 * 240KM Form Modal
 * This file renders the 240KM form in a modal format to be loaded via AJAX
 */

// Load configuration if needed in standalone mode
if (!defined('SITE_URL')) {
    require_once '../app/config/config.php';
    require_once '../app/config/database.php';
    require_once '../app/utils/helpers.php';
    require_once '../app/utils/auth.php';
}

// Get parameters passed from main form
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$dutyLocation = isset($_GET['duty_location']) ? $_GET['duty_location'] : '';
$dutyType = isset($_GET['purpose_details']) ? $_GET['purpose_details'] : '';
$distanceEstimate = isset($_GET['distance_estimate']) ? $_GET['distance_estimate'] : '';

// Get user profile information from URL parameters first
$userNameFromUrl = isset($_GET['user_name']) ? $_GET['user_name'] : '';
$userPositionFromUrl = isset($_GET['user_position']) ? $_GET['user_position'] : '';
$userDepartmentFromUrl = isset($_GET['user_department']) ? $_GET['user_department'] : '';

// Get user profile information from database as backup
$userData = [];
if (isset($_SESSION['user_id']) && (empty($userNameFromUrl) || empty($userPositionFromUrl) || empty($userDepartmentFromUrl))) {
    try {
        $stmt = $pdo->prepare("SELECT name, position, department FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Silently fail - we'll just have empty fields
    }
}

// Default values - prefer URL parameters, fallback to database values
$userName = !empty($userNameFromUrl) ? $userNameFromUrl : ($userData['name'] ?? '');
$userPosition = !empty($userPositionFromUrl) ? $userPositionFromUrl : ($userData['position'] ?? '');
$userDepartment = !empty($userDepartmentFromUrl) ? $userDepartmentFromUrl : ($userData['department'] ?? '');

?>
<div class="modal-card-head">
    <p class="modal-card-title">Borang Permohonan Untuk Menggunakan Kenderaan Sendiri (>240KM)</p>
    <button class="delete close-modal" aria-label="close" id="modal-close-button"></button>
</div>
<div class="modal-card-body" style="max-height: 70vh; overflow-y: auto;">
    <div class="box p-5">
        <div class="has-text-centered mb-4">
            <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 15px;">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/94/Jata_MalaysiaV2.svg/2558px-Jata_MalaysiaV2.svg.png" alt="Jata Malaysia" style="width:55px; height:auto; margin-right:10px;">
                <div style="font-weight:bold; font-size:11px; line-height:1.3; text-align: left;">
                    INSTITUT PENDIDIKAN GURU<br>
                    KAMPUS BAHASA MELAYU<br>
                    Jalan Pantai Baru<br>
                    59990 KUALA LUMPUR
                </div>
            </div>
            <p class="is-size-6 has-text-weight-bold">IPGKBM.UKP/BPGK240.v1</p>
            <p class="is-size-5 has-text-weight-bold">BORANG PERMOHONAN UNTUK MENGGUNAKAN KENDERAAN SENDIRI DAN MENUNTUT ELAUN</p>
            <p class="is-size-5 has-text-weight-bold">KILOMETER BAGI JARAK MELEBIHI 240KM SEHALA</p>
            <p class="is-size-6 mt-2">(PEKELILING PERBENDAHARAAN BIL.2/1992 – PARA 4.7.3)</p>
        </div>

        <form id="form240km" class="mt-5">
            <div class="field">
                <label class="label">Nama Pegawai</label>
                <div class="control">
                    <input type="text" class="input" name="nama_pegawai" id="nama_pegawai" value="<?= htmlspecialchars($userName) ?>">
                </div>
            </div>
            
            <div class="field">
                <label class="label">Jawatan</label>
                <div class="control">
                    <input type="text" class="input" name="jawatan" id="jawatan" value="<?= htmlspecialchars($userPosition) ?>">
                </div>
            </div>
            
            <div class="field">
                <label class="label">Jabatan / Unit</label>
                <div class="control">
                    <input type="text" class="input" name="jabatan_unit" id="jabatan_unit" value="<?= htmlspecialchars($userDepartment) ?>">
                </div>
            </div>

            <p class="my-3">Saya dengan ini memohon untuk menggunakan kenderaan sendiri bagi menjalankan tugas rasmi di luar pejabat seperti berikut:</p>

            <div class="table-container">
                <table class="table is-bordered is-fullwidth">
                    <thead>
                        <tr>
                            <th class="has-text-centered has-text-weight-bold" style="width:15%">Tarikh</th>
                            <th class="has-text-centered has-text-weight-bold" style="width:35%">Tempat Bertugas Rasmi</th>
                            <th class="has-text-centered has-text-weight-bold" style="width:25%">Jenis Tugas</th>
                            <th class="has-text-centered has-text-weight-bold" style="width:25%">Anggaran Jarak Pergi/Balik</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="text" class="input is-small" name="tarikh" id="tarikh" value="<?= htmlspecialchars($startDate) ?>"></td>
                            <td><input type="text" class="input is-small" name="tempat_bertugas" id="tempat_bertugas" value="<?= htmlspecialchars($dutyLocation) ?>"></td>
                            <td><input type="text" class="input is-small" name="jenis_tugas" id="jenis_tugas" value="<?= htmlspecialchars($dutyType) ?>"></td>
                            <td><input type="text" class="input is-small" name="anggaran_jarak" id="anggaran_jarak" value="<?= htmlspecialchars($distanceEstimate) ?>"></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="field mt-5">
                <p class="label">Sebab-sebab membuat perjalanan dengan menggunakan kenderaan sendiri:</p>
                
                <div class="field">
                    <div class="control">
                        <label class="checkbox">
                            <input type="checkbox" name="sebab[]" id="sebab1" value="Perlu menjalankan tugas rasmi di beberapa tempat disepanjang perjalanan">
                            Perlu menjalankan tugas rasmi di beberapa tempat disepanjang perjalanan
                        </label>
                    </div>
                </div>
                
                <div class="field">
                    <div class="control">
                        <label class="checkbox">
                            <input type="checkbox" name="sebab[]" id="sebab2" value="Mustahak dan terpaksa berkenderaan sendiri">
                            Mustahak dan terpaksa berkenderaan sendiri kerana 
                        </label>
                        <div class="ml-4 mt-2">
                            <input type="text" class="input" name="sebab_kenderaan_sendiri" style="width:100%">
                        </div>
                    </div>
                </div>
                
                <div class="field">
                    <div class="control">
                        <label class="checkbox">
                            <input type="checkbox" name="sebab[]" id="sebab3" value="Membawa pegawai lain">
                            Mustahak dan terpaksa membawa pegawai lain sebagai penumpang yang juga menjalankan tugas rasmi. Nama pegawai yang dibawa:
                        </label>
                        <div class="ml-4 mt-2">
                            <div class="field">
                                <label class="label is-small">a)</label>
                                <div class="control">
                                    <input type="text" class="input" name="pegawai_lain_1">
                                </div>
                            </div>
                            <div class="field">
                                <label class="label is-small">b)</label>
                                <div class="control">
                                    <input type="text" class="input" name="pegawai_lain_2">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="field">
                    <div class="control">
                        <label class="checkbox">
                            <input type="checkbox" name="sebab[]" id="sebab4" value="Menuntut tambang gantian">
                            Menggunakan kenderaan sendiri dengan menuntut tambang gantian persamaan dengan tambang kapal terbang
                        </label>
                    </div>
                </div>
            </div>

            <input type="hidden" name="form_240km_submitted" value="1">
        </form>
    </div>
</div>
<div class="modal-card-foot">
    <button type="button" class="button is-success" id="submit240kmForm">Hantar</button>
    <button type="button" class="button close-modal" id="close240kmForm">Tutup</button>
</div>

<script>
// Immediately add close handlers
document.querySelectorAll('.close-modal').forEach(element => {
    element.addEventListener('click', function() {
        const modal = document.getElementById('form240kmModal');
        if (modal) {
            console.log('Direct close button clicked');
            modal.classList.remove('is-active');
        }
    });
});
</script>