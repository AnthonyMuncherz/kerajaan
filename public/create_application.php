<?php
/**
 * Create Application page
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

// Update schema if needed
$applicationModel->updateSchema();

// Initialize variables
$error = '';
$success = '';
$formData = [
    'purpose_type' => '',
    'purpose_details' => '',
    'duty_location' => '',
    'transportation_type' => '',
    'transportation_details' => '',
    'distance_estimate' => '',
    'personal_vehicle_reason' => '',
    'start_date' => '',
    'end_date' => '',
    'exit_time' => '',
    'return_time' => '',
    'form_240km_data' => ''
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $purposeTypes = isset($_POST['purpose_types']) ? $_POST['purpose_types'] : [];
    $purposeTypeString = implode(', ', $purposeTypes);
    
    $transportationTypes = isset($_POST['transportation_types']) ? $_POST['transportation_types'] : [];
    $transportationTypeString = implode(', ', $transportationTypes);
    
    $formData = [
        'user_id' => $_SESSION['user_id'],
        'purpose_type' => $purposeTypeString,
        'purpose_details' => sanitizeInput($_POST['purpose_details'] ?? ''),
        'duty_location' => sanitizeInput($_POST['duty_location'] ?? ''),
        'transportation_type' => $transportationTypeString,
        'transportation_details' => sanitizeInput($_POST['transportation_details'] ?? ''),
        'distance_estimate' => sanitizeInput($_POST['distance_estimate'] ?? ''),
        'personal_vehicle_reason' => sanitizeInput($_POST['personal_vehicle_reason'] ?? ''),
        'start_date' => sanitizeInput($_POST['start_date'] ?? ''),
        'end_date' => sanitizeInput($_POST['end_date'] ?? ''),
        'exit_time' => sanitizeInput($_POST['exit_time'] ?? ''),
        'return_time' => sanitizeInput($_POST['return_time'] ?? ''),
        'form_240km_data' => sanitizeInput($_POST['form_240km_data'] ?? '')
    ];
    
    // Check for 240km form requirement
    $distance = floatval($formData['distance_estimate']);
    $usesPersonalVehicle = in_array('Kenderaan Sendiri', $transportationTypes);
    $needs240kmForm = $usesPersonalVehicle && $distance >= 240;
    $has240kmForm = !empty($formData['form_240km_data']);
    
    // Validate form data
    if (empty($purposeTypes) || empty($formData['purpose_details']) || 
        empty($formData['duty_location']) || empty($transportationTypes) || 
        empty($formData['start_date']) || empty($formData['end_date']) || 
        empty($formData['exit_time']) || empty($formData['return_time'])) {
        $error = 'Sila lengkapkan semua maklumat yang diperlukan.';
    } elseif (in_array('Kenderaan Sendiri', $transportationTypes) && empty($formData['personal_vehicle_reason'])) {
        $error = 'Sila nyatakan sebab menggunakan kenderaan sendiri.';
    } elseif (!isValidDate($formData['start_date']) || !isValidDate($formData['end_date'])) {
        $error = 'Sila masukkan tarikh yang sah (YYYY-MM-DD).';
    } elseif (!isValidTime($formData['exit_time']) || !isValidTime($formData['return_time'])) {
        $error = 'Sila masukkan masa yang sah (HH:MM).';
    } elseif (strtotime($formData['start_date']) > strtotime($formData['end_date'])) {
        $error = 'Tarikh mula tidak boleh lebih lewat daripada tarikh tamat.';
    } elseif ($needs240kmForm && !$has240kmForm) {
        $error = 'Untuk perjalanan melebihi 240KM dengan kenderaan sendiri, anda perlu melengkapkan borang tambahan. Sila isi borang tambahan terlebih dahulu.';
    } else {
        // Process attachment upload if provided
        $attachmentPath = null;
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $fileInfo = pathinfo($_FILES['attachment']['name']);
            $fileExtension = strtolower($fileInfo['extension']);
            
            // Validate file type
            if ($fileExtension !== 'pdf') {
                $error = 'Hanya fail PDF diterima untuk lampiran surat tugas rasmi.';
            } else {
                // Create upload directory if it doesn't exist
                $uploadDir = '../uploads/attachments/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                // Generate unique filename
                $uniqueFilename = uniqid('attachment_') . '_' . time() . '.pdf';
                $uploadPath = $uploadDir . $uniqueFilename;
                
                // Move uploaded file
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadPath)) {
                    $attachmentPath = 'uploads/attachments/' . $uniqueFilename;
                } else {
                    $error = 'Gagal memuat naik fail lampiran. Sila cuba lagi.';
                }
            }
        }
        
        if (empty($error)) {
            // Include attachment path in form data
            $formData['attachment_path'] = $attachmentPath;
            
            // Create application
            $applicationId = $applicationModel->createApplication($formData);
            
            if ($applicationId) {
                // Set success message
                $success = 'Permohonan telah berjaya dihantar. ID Permohonan: ' . $applicationId;
                
                // Clear form data
                $formData = [
                    'purpose_type' => '',
                    'purpose_details' => '',
                    'duty_location' => '',
                    'transportation_type' => '',
                    'transportation_details' => '',
                    'distance_estimate' => '',
                    'personal_vehicle_reason' => '',
                    'start_date' => '',
                    'end_date' => '',
                    'exit_time' => '',
                    'return_time' => '',
                    'form_240km_data' => ''
                ];
                
                // Redirect to view application page
                header('Location: ' . SITE_URL . '/view_application.php?id=' . $applicationId . '&success=1');
                exit;
            } else {
                $error = 'Permohonan tidak berjaya. Sila cuba lagi.';
            }
        }
    }
}

// Set page title
$pageTitle = 'Permohonan Baru';

// Include header
include '../app/views/includes/header.php';
?>

<div class="content">
    <h1 class="title">
        <i class="fas fa-plus-circle mr-2"></i> Permohonan Keluar Baru
    </h1>
    <h2 class="subtitle">
        Sila lengkapkan maklumat di bawah.
    </h2>
</div>

<div class="card">
    <div class="card-content">
        <?php if (!empty($error)): ?>
            <div class="notification is-danger is-light">
                <button class="delete"></button>
                <?= $error ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="notification is-success is-light">
                <button class="delete"></button>
                <?= $success ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" class="application-form" enctype="multipart/form-data">
            <!-- Hidden field for 240km form data -->
            <input type="hidden" name="form_240km_data" id="form_240km_data" value="">
            
            <div class="field">
                <label class="label">Jenis Urusan</label>
                <div class="control">
                    <div class="field">
                        <div class="control">
                            <p class="help">Boleh pilih lebih dari satu jenis urusan</p>
                            <label class="checkbox mr-4">
                                <input type="checkbox" name="purpose_types[]" value="Urusan Rasmi" 
                                    <?= strpos($formData['purpose_type'] ?? '', 'Urusan Rasmi') !== false ? 'checked' : '' ?>>
                                Urusan Rasmi
                            </label>
                            <label class="checkbox mr-4">
                                <input type="checkbox" name="purpose_types[]" value="Anjuran IPG KBM"
                                    <?= strpos($formData['purpose_type'] ?? '', 'Anjuran IPG KBM') !== false ? 'checked' : '' ?>>
                                Anjuran IPG KBM
                            </label>
                            <label class="checkbox mr-4">
                                <input type="checkbox" name="purpose_types[]" value="Anjuran KPM"
                                    <?= strpos($formData['purpose_type'] ?? '', 'Anjuran KPM') !== false ? 'checked' : '' ?>>
                                Anjuran KPM
                            </label>
                            <label class="checkbox mr-4">
                                <input type="checkbox" name="purpose_types[]" value="Anjuran Agensi Kerajaan"
                                    <?= strpos($formData['purpose_type'] ?? '', 'Anjuran Agensi Kerajaan') !== false ? 'checked' : '' ?>>
                                Anjuran Agensi Kerajaan
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="field">
                <label class="label">Butiran Urusan</label>
                <div class="control">
                    <textarea class="textarea" name="purpose_details" placeholder="Masukkan butiran urusan" required><?= $formData['purpose_details'] ?></textarea>
                </div>
            </div>
            
            <div class="field">
                <label class="label">Lokasi Bertugas</label>
                <div class="control">
                    <input class="input" type="text" name="duty_location" placeholder="Masukkan lokasi bertugas" value="<?= $formData['duty_location'] ?>" required>
                </div>
            </div>
            
            <div class="field">
                <label class="label">Jenis Pengangkutan</label>
                <div class="control">
                    <p class="help mb-2">Boleh pilih lebih dari satu jenis pengangkutan</p>
                    
                    <div class="columns">
                        <div class="column">
                            <div class="box">
                                <h5 class="subtitle is-5 mb-2">Pengangkutan Awam</h5>
                                <div class="field">
                                    <label class="checkbox mr-4 is-block mb-2">
                                        <input type="checkbox" name="transportation_types[]" value="Bas"
                                            <?= strpos($formData['transportation_type'] ?? '', 'Bas') !== false ? 'checked' : '' ?>>
                                        Bas
                                    </label>
                                    <label class="checkbox mr-4 is-block mb-2">
                                        <input type="checkbox" name="transportation_types[]" value="Teksi"
                                            <?= strpos($formData['transportation_type'] ?? '', 'Teksi') !== false ? 'checked' : '' ?>>
                                        Teksi
                                    </label>
                                    <label class="checkbox mr-4 is-block mb-2">
                                        <input type="checkbox" name="transportation_types[]" value="Keretaapi"
                                            <?= strpos($formData['transportation_type'] ?? '', 'Keretaapi') !== false ? 'checked' : '' ?>>
                                        Keretapi
                                    </label>
                                    <label class="checkbox mr-4 is-block mb-2">
                                        <input type="checkbox" name="transportation_types[]" value="Kapal Terbang"
                                            <?= strpos($formData['transportation_type'] ?? '', 'Kapal Terbang') !== false ? 'checked' : '' ?>>
                                        Kapal Terbang
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="column">
                            <div class="box">
                                <h5 class="subtitle is-5 mb-2">Kenderaan Darat</h5>
                                <div class="field">
                                    <label class="checkbox mr-4 is-block mb-2" id="kenderaan-sendiri-label">
                                        <input type="checkbox" name="transportation_types[]" value="Kenderaan Sendiri" 
                                            id="kenderaan-sendiri-checkbox"
                                            <?= strpos($formData['transportation_type'] ?? '', 'Kenderaan Sendiri') !== false ? 'checked' : '' ?>>
                                        Kenderaan Sendiri
                                    </label>
                                    <label class="checkbox mr-4 is-block mb-2">
                                        <input type="checkbox" name="transportation_types[]" value="Kenderaan Rasmi"
                                            <?= strpos($formData['transportation_type'] ?? '', 'Kenderaan Rasmi') !== false ? 'checked' : '' ?>>
                                        Kenderaan Rasmi
                                    </label>
                                    <label class="checkbox mr-4 is-block mb-2">
                                        <input type="checkbox" name="transportation_types[]" value="Berkongsi Kenderaan"
                                            <?= strpos($formData['transportation_type'] ?? '', 'Berkongsi Kenderaan') !== false ? 'checked' : '' ?>>
                                        Berkongsi Kenderaan
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="personal-vehicle-reason-container" class="mt-3" style="display: <?= strpos($formData['transportation_type'] ?? '', 'Kenderaan Sendiri') !== false ? 'block' : 'none' ?>;">
                        <div class="field">
                            <label class="label">Sebab-sebab membuat perjalanan dengan kenderaan sendiri:</label>
                            <div class="control">
                                <textarea class="textarea" name="personal_vehicle_reason" placeholder="Sila nyatakan sebab menggunakan kenderaan sendiri"><?= $formData['personal_vehicle_reason'] ?? '' ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="field">
                <label class="label">Butiran Pengangkutan</label>
                <div class="control">
                    <input class="input" type="text" name="transportation_details" placeholder="Contoh: Nombor plat kenderaan, model, dll." value="<?= $formData['transportation_details'] ?>">
                </div>
                <p class="help">Jika berkenaan</p>
            </div>
            
            <div class="field">
                <label class="label">Anggaran Jarak (KM)</label>
                <div class="control">
                    <input class="input" type="text" name="distance_estimate" placeholder="Anggaran jarak pergi/balik dalam KM" value="<?= $formData['distance_estimate'] ?>">
                </div>
            </div>
            
            <div class="columns">
                <div class="column">
                    <div class="field">
                        <label class="label">Tarikh Mula</label>
                        <div class="control has-icons-right">
                            <input class="input datepicker" type="text" name="start_date" placeholder="YYYY-MM-DD" value="<?= $formData['start_date'] ?>" required>
                            <span class="icon is-small is-right">
                                <i class="fas fa-calendar"></i>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="column">
                    <div class="field">
                        <label class="label">Tarikh Tamat</label>
                        <div class="control has-icons-right">
                            <input class="input datepicker" type="text" name="end_date" placeholder="YYYY-MM-DD" value="<?= $formData['end_date'] ?>" required>
                            <span class="icon is-small is-right">
                                <i class="fas fa-calendar"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="columns">
                <div class="column">
                    <div class="field">
                        <label class="label">Masa Keluar</label>
                        <div class="control has-icons-right">
                            <input class="input timepicker" type="text" name="exit_time" placeholder="HH:MM" value="<?= $formData['exit_time'] ?>" required>
                            <span class="icon is-small is-right">
                                <i class="fas fa-clock"></i>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="column">
                    <div class="field">
                        <label class="label">Masa Kembali</label>
                        <div class="control has-icons-right">
                            <input class="input timepicker" type="text" name="return_time" placeholder="HH:MM" value="<?= $formData['return_time'] ?>" required>
                            <span class="icon is-small is-right">
                                <i class="fas fa-clock"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="field">
                <label class="label">LAMPIRAN SURAT KELUAR ATAS TUGAS RASMI</label>
                <div class="file has-name is-fullwidth">
                    <label class="file-label">
                        <input class="file-input" type="file" name="attachment" accept=".pdf">
                        <span class="file-cta">
                            <span class="file-icon">
                                <i class="fas fa-upload"></i>
                            </span>
                            <span class="file-label">
                                Pilih fail PDF...
                            </span>
                        </span>
                        <span class="file-name">
                            Tiada fail dipilih
                        </span>
                    </label>
                </div>
                <p class="help">Lampirkan surat tempat bertugas sebagai bukti tugas rasmi (format PDF sahaja)</p>
            </div>
            
            <div class="field mt-5">
                <div class="control">
                    <button type="submit" class="button is-primary">
                        <i class="fas fa-paper-plane mr-2"></i> Hantar Permohonan
                    </button>
                    <a href="<?= SITE_URL ?>/dashboard.php" class="button is-light">
                        <i class="fas fa-times mr-2"></i> Batal
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- 240KM Form Modal -->
<div class="modal" id="form240kmModal">
    <div class="modal-background"></div>
    <div class="modal-card" style="width: 800px; max-width: 95%; max-height: 90vh; display: flex; flex-direction: column;">
        <div id="form240kmContent" style="display: flex; flex-direction: column; height: 100%;">
            <!-- Content will be loaded here via AJAX -->
            <div class="has-text-centered p-5">
                <span class="icon is-large">
                    <i class="fas fa-spinner fa-pulse fa-3x"></i>
                </span>
                <p class="mt-3">Memuat...</p>
            </div>
        </div>
    </div>
</div>

<script>
    // Show/hide personal vehicle reason field based on checkbox
    document.addEventListener('DOMContentLoaded', function() {
        const kenderaanSendiriCheckbox = document.getElementById('kenderaan-sendiri-checkbox');
        const personalVehicleReasonContainer = document.getElementById('personal-vehicle-reason-container');
        const distanceEstimateInput = document.querySelector('input[name="distance_estimate"]');
        const modal240km = document.getElementById('form240kmModal');
        const form240kmContent = document.getElementById('form240kmContent');
        const form240kmDataField = document.getElementById('form_240km_data');
        const mainForm = document.querySelector('form[method="post"]');
        
        // File input display
        const fileInput = document.querySelector('.file-input');
        const fileName = document.querySelector('.file-name');
        
        if (fileInput && fileName) {
            fileInput.addEventListener('change', function() {
                if (fileInput.files.length > 0) {
                    fileName.textContent = fileInput.files[0].name;
                } else {
                    fileName.textContent = 'Tiada fail dipilih';
                }
            });
        }
        
        // Handle personal vehicle checkbox
        if (kenderaanSendiriCheckbox && personalVehicleReasonContainer) {
            kenderaanSendiriCheckbox.addEventListener('change', function() {
                personalVehicleReasonContainer.style.display = this.checked ? 'block' : 'none';
            });
        }
        
        // Handle distance estimate input to trigger 240km form
        if (distanceEstimateInput) {
            distanceEstimateInput.addEventListener('blur', function() {
                const distance = parseFloat(this.value);
                
                // Check if distance is valid and exceeds 240 KM
                if (!isNaN(distance) && distance >= 240 && kenderaanSendiriCheckbox && kenderaanSendiriCheckbox.checked) {
                    // Get form data to pre-fill
                    const startDate = document.querySelector('input[name="start_date"]').value;
                    const dutyLocation = document.querySelector('input[name="duty_location"]').value;
                    const purposeDetails = document.querySelector('textarea[name="purpose_details"]').value;
                    
                    // Get user information from the page if possible
                    const userInfo = {
                        name: '<?= $_SESSION['user_name'] ?? '' ?>',
                        position: '<?= $_SESSION['user_position'] ?? '' ?>',
                        department: '<?= $_SESSION['user_department'] ?? '' ?>'
                    };
                    
                    // Create the URL with all parameters
                    const url = `<?= SITE_URL ?>/form_240km_modal.php?` + 
                        `start_date=${encodeURIComponent(startDate)}` + 
                        `&duty_location=${encodeURIComponent(dutyLocation)}` + 
                        `&purpose_details=${encodeURIComponent(purposeDetails)}` + 
                        `&distance_estimate=${encodeURIComponent(distance)}` +
                        `&user_name=${encodeURIComponent(userInfo.name)}` +
                        `&user_position=${encodeURIComponent(userInfo.position)}` +
                        `&user_department=${encodeURIComponent(userInfo.department)}`;
                    
                    // Load 240km form via AJAX
                    fetch(url)
                        .then(response => response.text())
                        .then(html => {
                            form240kmContent.innerHTML = html;
                            modal240km.classList.add('is-active');
                            
                            // Debug info to the console
                            console.log('Modal activated');
                            
                            // Set up event handlers for modal interaction
                            const setupModalCloseHandlers = () => {
                                console.log('Setting up close handlers');
                                
                                // All close triggers
                                const closeElements = [
                                    document.querySelector('#form240kmModal .close-modal'),
                                    document.getElementById('close240kmForm'),
                                    document.querySelector('#form240kmModal .modal-background')
                                ];
                                
                                // Add event listeners to all close elements
                                closeElements.forEach(element => {
                                    if (element) {
                                        console.log('Adding close handler to', element);
                                        element.addEventListener('click', function() {
                                            console.log('Close clicked');
                                            modal240km.classList.remove('is-active');
                                        });
                                    } else {
                                        console.log('Close element not found');
                                    }
                                });
                                
                                // Keyboard escape key
                                document.addEventListener('keydown', function(e) {
                                    if (e.key === 'Escape' && modal240km.classList.contains('is-active')) {
                                        console.log('Escape key pressed, closing modal');
                                        modal240km.classList.remove('is-active');
                                    }
                                });
                            };
                            
                            // Run after a small delay to ensure elements are available
                            setTimeout(setupModalCloseHandlers, 100);
                            
                            document.getElementById('submit240kmForm')?.addEventListener('click', function() {
                                // Get form data
                                const formData = new FormData(document.getElementById('form240km'));
                                
                                // Process form data and handle checkbox arrays properly
                                const formDataObj = {};
                                const sebabValues = [];
                                
                                formData.forEach((value, key) => {
                                    // Handle special case for checkbox array
                                    if (key === 'sebab[]') {
                                        sebabValues.push(value);
                                    } else {
                                        formDataObj[key] = value;
                                    }
                                });
                                
                                // Add the sebab array
                                if (sebabValues.length > 0) {
                                    formDataObj['sebab'] = sebabValues;
                                }
                                
                                // Store in localStorage and hidden field
                                const formDataJSON = JSON.stringify(formDataObj);
                                localStorage.setItem('form240kmData', formDataJSON);
                                form240kmDataField.value = formDataJSON;
                                
                                // Log for debugging
                                console.log('240km form data:', formDataObj);
                                console.log('JSON data:', formDataJSON);
                                
                                // Close modal
                                modal240km.classList.remove('is-active');
                                
                                // Indicate form was completed
                                distanceEstimateInput.dataset.form240kmCompleted = "true";
                            });
                        })
                        .catch(error => {
                            console.error('Error loading 240km form:', error);
                            form240kmContent.innerHTML = '<div class="p-5"><p class="has-text-danger">Ralat memuatkan borang. Sila cuba lagi.</p></div>';
                        });
                }
            });
        }
        
        // Check for form submission
        if (mainForm) {
            mainForm.addEventListener('submit', function(e) {
                // Check if we need the 240km form
                const distance = parseFloat(distanceEstimateInput?.value || '0');
                const needsForm240km = kenderaanSendiriCheckbox?.checked && !isNaN(distance) && distance >= 240;
                
                if (needsForm240km && !form240kmDataField.value) {
                    e.preventDefault();
                    
                    // Show notification
                    const notification = document.createElement('div');
                    notification.className = 'notification is-warning';
                    notification.innerHTML = '<button class="delete"></button>Sila lengkapkan borang tambahan untuk perjalanan melebihi 240KM.';
                    mainForm.insertBefore(notification, mainForm.firstChild);
                    
                    // Add delete button functionality
                    notification.querySelector('.delete').addEventListener('click', function() {
                        notification.remove();
                    });
                    
                    // Show the 240km form if it's not completed
                    setTimeout(() => {
                        const event = new Event('blur');
                        distanceEstimateInput.dispatchEvent(event);
                    }, 500);
                }
            });
        }
        
        // Check localStorage for previously saved form data
        const savedForm240kmData = localStorage.getItem('form240kmData');
        if (savedForm240kmData && form240kmDataField) {
            form240kmDataField.value = savedForm240kmData;
            distanceEstimateInput.dataset.form240kmCompleted = "true";
        }
    });
</script>

<?php
// Include footer
include '../app/views/includes/footer.php';
?> 