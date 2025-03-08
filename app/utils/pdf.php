<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

/**
 * PDF utilities
 * Sistem Permohonan Keluar
 */

// Define a debug constant that can be set in config.php
if (!defined('DEBUG_PDF')) {
    define('DEBUG_PDF', false);
}

/**
 * Log message to error log if debugging is enabled
 */
function pdfDebugLog($message) {
    if (DEBUG_PDF) {
        error_log($message);
    }
}

/**
 * Generate exit form PDF using HTML template
 * 
 * @param array $application Leave application data
 * @param array $user User data
 * @return string|null Path to generated PDF or null on failure
 */
function generateExitFormPDF($application, $user) {
    // Create directory if it doesn't exist
    $pdfDir = dirname(dirname(__DIR__)) . '/app/pdf';
    if (!is_dir($pdfDir)) {
        mkdir($pdfDir, 0755, true);
    }

    // Make $pdo available in this function
    global $pdo;

    // Generate unique filenames for the two forms
    $timestamp = time();
    $mainFormFilename = 'exit_form_' . $application['id'] . '_' . $timestamp . '.pdf';
    $form240kmFilename = 'form_240km_' . $application['id'] . '_' . $timestamp . '.pdf';
    $combinedFilename = 'combined_forms_' . $application['id'] . '_' . $timestamp . '.pdf';
    
    $mainFormOutputPath = $pdfDir . '/' . $mainFormFilename;
    $form240kmOutputPath = $pdfDir . '/' . $form240kmFilename;
    $combinedOutputPath = $pdfDir . '/' . $combinedFilename;
    
    $mainFormHtmlPath = $pdfDir . '/main_form_' . $timestamp . '.html';
    $form240kmHtmlPath = $pdfDir . '/240km_form_' . $timestamp . '.html';
    
    // Check if this is a 240km form application
    $has240kmForm = false;
    $form240kmData = null;
    $distance = floatval($application['distance_estimate'] ?? 0);
    
    // Check if distance > 240km and we have form data
    if ($distance >= 240 && !empty($application['form_240km_data'])) {
        $has240kmForm = true;
        
        // Decode HTML entities before parsing JSON
        $formDataString = html_entity_decode($application['form_240km_data']);
        $form240kmData = json_decode($formDataString, true);
        
        // Log the form data for debugging
        pdfDebugLog('240km form data: ' . print_r($form240kmData, true));
    }
    
    try {
        // Generate main form HTML and save it
        $mainFormHtml = generateMainFormHTML($application, $user);
        file_put_contents($mainFormHtmlPath, $mainFormHtml);
        pdfDebugLog("Main form HTML saved to: $mainFormHtmlPath");
        
        // Generate PDF for main form
        $mainFormPdfGenerated = generatePdfFromHtml($mainFormHtml, $mainFormOutputPath);
        
        // If we have 240km form data, generate that as well
        $form240kmPdfGenerated = false;
        if ($has240kmForm && $form240kmData) {
            pdfDebugLog('Generating 240km form HTML');
            $form240kmHtml = generate240kmFormHTML($form240kmData, $user, $application);
            file_put_contents($form240kmHtmlPath, $form240kmHtml);
            pdfDebugLog("240km form HTML saved to: $form240kmHtmlPath");
            
            // Generate PDF for 240km form
            $form240kmPdfGenerated = generatePdfFromHtml($form240kmHtml, $form240kmOutputPath);
        }
        
        // Determine which path to return
        $returnPath = '';
        
        if ($mainFormPdfGenerated && $form240kmPdfGenerated) {
            // Try to merge the PDFs
            try {
                // First check if we can use FPDI
                require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';
                
                if (class_exists('\\setasign\\Fpdi\\Fpdi') && class_exists('FPDF')) {
                    $pdf = new \setasign\Fpdi\Fpdi();
                    
                    // Add first PDF
                    $pageCount = $pdf->setSourceFile($mainFormOutputPath);
                    for ($i = 1; $i <= $pageCount; $i++) {
                        $template = $pdf->importPage($i);
                        $pdf->AddPage();
                        $pdf->useTemplate($template);
                    }
                    
                    // Add second PDF
                    $pageCount = $pdf->setSourceFile($form240kmOutputPath);
                    for ($i = 1; $i <= $pageCount; $i++) {
                        $template = $pdf->importPage($i);
                        $pdf->AddPage();
                        $pdf->useTemplate($template);
                    }
                    
                    // Save the merged PDF
                    $pdf->Output('F', $combinedOutputPath);
                    pdfDebugLog("Combined PDF saved to: $combinedOutputPath");
                    
                    $returnPath = 'app/pdf/' . $combinedFilename;
                } else {
                    // Alternative approach: create a special HTML file with both forms
                    pdfDebugLog("FPDI or FPDF class not found, using alternative approach");
                    
                    // Read both HTML files
                    $mainFormHtmlContent = file_get_contents($mainFormHtmlPath);
                    $form240kmHtmlContent = file_get_contents($form240kmHtmlPath);
                    
                    // Extract the body content from the 240km form
                    preg_match('/<body>(.*?)<\/body>/s', $form240kmHtmlContent, $matches);
                    $form240kmBodyContent = isset($matches[1]) ? $matches[1] : $form240kmHtmlContent;
                    
                    // Create a combined HTML with pagebreak
                    $combinedHtml = $mainFormHtmlContent;
                    $combinedHtml = str_replace('</body>', 
                        '<div style="page-break-after: always;"></div><h2 style="page-break-before: always;">Borang 240KM</h2>' . 
                        $form240kmBodyContent . 
                        '</body>', $combinedHtml);
                    
                    // Save the combined HTML
                    $combinedHtmlPath = $pdfDir . '/combined_' . $timestamp . '.html';
                    file_put_contents($combinedHtmlPath, $combinedHtml);
                    
                    // Generate a new PDF from the combined HTML
                    if (generatePdfFromHtml($combinedHtml, $combinedOutputPath)) {
                        pdfDebugLog("Combined PDF generated using HTML approach");
                        $returnPath = 'app/pdf/' . $combinedFilename;
                        
                        // Clean up the combined HTML file
                        if (file_exists($combinedHtmlPath)) {
                            unlink($combinedHtmlPath);
                        }
                    } else {
                        pdfDebugLog("Failed to generate combined PDF, returning both PDFs");
                        // Return both PDFs in an array - this will be handled by print_pdf.php
                        return [
                            'main' => 'app/pdf/' . $mainFormFilename,
                            '240km' => 'app/pdf/' . $form240kmFilename
                        ];
                    }
                }
            } catch (Exception $e) {
                pdfDebugLog("Error merging PDFs: " . $e->getMessage() . "\n" . $e->getTraceAsString());
                // Return both PDFs in an array
                return [
                    'main' => 'app/pdf/' . $mainFormFilename,
                    '240km' => 'app/pdf/' . $form240kmFilename
                ];
            }
        } else if ($mainFormPdfGenerated) {
            $returnPath = 'app/pdf/' . $mainFormFilename;
        } else {
            pdfDebugLog("Failed to generate any PDFs");
            return null;
        }
        
        // Clean up temporary files
        foreach ([$mainFormHtmlPath, $form240kmHtmlPath] as $file) {
            if (file_exists($file)) {
                unlink($file);
                pdfDebugLog("Temporary HTML file removed: $file");
            }
        }
        
        // If we used combined PDF, also remove the individual PDFs
        if ($returnPath === 'app/pdf/' . $combinedFilename) {
            foreach ([$mainFormOutputPath, $form240kmOutputPath] as $file) {
                if (file_exists($file)) {
                    unlink($file);
                    pdfDebugLog("Temporary PDF file removed: $file");
                }
            }
        }
        
        return $returnPath;
    } catch (Exception $e) {
        pdfDebugLog('Error generating PDF: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
        return null;
    }
}

/**
 * Generate HTML for the main application form
 *
 * @param array $application Application data
 * @param array $user User data
 * @return string HTML content
 */
function generateMainFormHTML($application, $user) {
    // Format dates and times
    $startDate = date('d/m/Y', strtotime($application['start_date']));
    $endDate = date('d/m/Y', strtotime($application['end_date']));
    $exitTime = date('H:i', strtotime($application['exit_time']));
    $returnTime = date('H:i', strtotime($application['return_time']));
    $currentDate = date('d/m/Y');
    
    // Prepare user signature
    $userSignatureHtml = '';
    if (!empty($user['signature_path'])) {
        $signaturePath = dirname(dirname(__DIR__)) . '/app/uploads/signatures/' . $user['signature_path'];
        if (file_exists($signaturePath)) {
            $signatureData = base64_encode(file_get_contents($signaturePath));
            $userSignatureHtml = '<img src="data:image/png;base64,' . $signatureData . '" style="max-width: 120px; height: auto;" alt="Signature" />';
        }
    }
    
    // Prepare purpose type checkboxes
    $purposeType = strtolower($application['purpose_type']);
    $urusanRasmi = (strpos($purposeType, 'rasmi') !== false) ? true : false;
    $anjuranIpg = (strpos($purposeType, 'ipg') !== false) ? true : false;
    $anjuranKpm = (strpos($purposeType, 'kpm') !== false) ? true : false;
    $anjuranAgensi = (strpos($purposeType, 'agensi') !== false || strpos($purposeType, 'kerajaan') !== false) ? true : false;
    
    // Prepare transportation checkboxes
    $transportTypes = explode(',', $application['transportation_type']);
    $kenderaanSendiri = false;
    $kenderaanRasmi = false;
    $berkongsiKenderaan = false;
    $bas = false;
    $teksi = false;
    $keretapi = false;
    $kapalTerbang = false;
    
    // Check each transportation type
    foreach ($transportTypes as $transportType) {
        $transportType = trim(strtolower($transportType));
        
        if (strpos($transportType, 'sendiri') !== false) {
            $kenderaanSendiri = true;
        }
        if (strpos($transportType, 'rasmi') !== false) {
            $kenderaanRasmi = true;
        }
        if (strpos($transportType, 'berkongsi') !== false) {
            $berkongsiKenderaan = true;
        }
        if (strpos($transportType, 'bas') !== false) {
            $bas = true;
        }
        if (strpos($transportType, 'teksi') !== false) {
            $teksi = true;
        }
        if (strpos($transportType, 'keretapi') !== false) {
            $keretapi = true;
        }
        if (strpos($transportType, 'terbang') !== false || strpos($transportType, 'kapal') !== false) {
            $kapalTerbang = true;
        }
    }
    
    // Get ketua details and signature if available
    $ketuaSignatureHtml = '';
    $ketuaName = '';
    if ($application['ketua_approval_status'] === 'approved' && !empty($application['ketua_approver_id'])) {
        // Get approver details
        global $pdo;
        $stmt = $pdo->prepare("SELECT name, signature_path FROM users WHERE id = ?");
        $stmt->execute([$application['ketua_approver_id']]);
        $ketua = $stmt->fetch();
        
        if ($ketua && !empty($ketua['signature_path'])) {
            $signaturePath = dirname(dirname(__DIR__)) . '/app/uploads/signatures/' . $ketua['signature_path'];
            if (file_exists($signaturePath)) {
                $signatureData = base64_encode(file_get_contents($signaturePath));
                $ketuaSignatureHtml = '<img src="data:image/png;base64,' . $signatureData . '" style="max-width: 120px; height: auto;" alt="Signature" />';
            }
        }
        
        $ketuaName = $ketua['name'] ?? '';
    }
    
    // Get pengarah details and signature if available
    $pengarahSignatureHtml = '';
    $pengarahName = '';
    if ($application['pengarah_approval_status'] === 'approved' && !empty($application['pengarah_approver_id'])) {
        // Get approver details
        global $pdo;
        $stmt = $pdo->prepare("SELECT name, signature_path FROM users WHERE id = ?");
        $stmt->execute([$application['pengarah_approver_id']]);
        $pengarah = $stmt->fetch();
        
        if ($pengarah && !empty($pengarah['signature_path'])) {
            $signaturePath = dirname(dirname(__DIR__)) . '/app/uploads/signatures/' . $pengarah['signature_path'];
            if (file_exists($signaturePath)) {
                $signatureData = base64_encode(file_get_contents($signaturePath));
                $pengarahSignatureHtml = '<img src="data:image/png;base64,' . $signatureData . '" style="max-width: 140px; height: auto; margin-top: 3px; margin-bottom: 3px;" alt="Pengarah Signature" />';
            }
        }
        
        $pengarahName = $pengarah['name'] ?? '';
    }
    
    // Original HTML generation code from generateExitFormPDF
    return '<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borang Permohonan Keluar Rasmi Institut Dan Penggunaan Kenderaan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            max-width: 800px;
            margin: 0 auto;
        }
        .container {
            padding: 10px 20px;
        }
        .header {
            display: table;
            width: 100%;
            position: relative;
            margin-bottom: 12px;
        }
        .form-number {
            position: absolute;
            top: 0;
            right: 0;
            font-size: 10px;
        }
        .logo-cell {
            display: table-cell;
            vertical-align: top;
            width: 70px;
        }
        .logo {
            width: 55px;
            height: auto;
        }
        .header-text-cell {
            display: table-cell;
            vertical-align: top;
            padding-top: 5px;
        }
        .header-text {
            font-size: 11px;
            font-weight: bold;
            line-height: 1.3;
        }
        .form-title {
            text-align: center;
            font-weight: bold;
            margin: 12px 0 4px 0;
            font-size: 12px;
        }
        .form-subtitle {
            text-align: center;
            font-style: italic;
            margin-bottom: 12px;
            font-size: 11px;
        }
        .checkbox-row {
            display: flex;
            align-items: center;
            margin: 2px 0;
            position: relative;
        }
        .checkbox-row-number {
            margin-right: 5px;
            width: 15px;
            display: inline-block;
        }
        .checkbox {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1px solid black;
            margin-right: 5px;
            vertical-align: middle;
            position: relative;
        }
        .arrow-indicator {
            position: absolute;
            left: -25px;
            top: 0;
            color: red;
            font-weight: bold;
            font-size: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
        }
        table, th, td {
            border: 1px solid black;
        }
        td {
            padding: 3px 5px;
            font-size: 11px;
            vertical-align: top;
        }
        .signature-section {
            margin: 12px 0;
            position: relative;
        }
        .signature-line {
            border-bottom: 1px solid black;
            margin: 8px 0;
            width: 100%;
        }
        .date-field {
            margin: 8px 0;
        }
        .signature-right {
            text-align: right;
            margin-top: 12px;
            margin-bottom: 4px;
        }
        .nama-right {
            text-align: right;
            color: #000;
        }
        .section-header {
            font-weight: bold;
            text-align: center;
            margin: 8px 0 4px 0;
            border-top: 1px solid black;
            padding-top: 4px;
            font-size: 12px;
        }
        .form-note {
            font-size: 10px;
            margin-top: 8px;
        }
        table input[type="text"] {
            width: 100%;
            border: none;
            background: transparent;
            outline: none;
            font-size: 11px;
        }
        .col-30 {
            width: 30%;
        }
        .col-20 {
            width: 20%;
        }
        .checkbox-cell {
            padding: 2px 5px;
            position: relative;
        }
        .transportation-section td {
            vertical-align: top;
        }
        .checked {
            background: white;
        }
        .checked::after {
            content: "X";
            color: black;
            position: absolute;
            top: -2px;
            left: 2px;
            font-size: 11px;
            font-weight: bold;
        }
        .filled-text {
            border-bottom: 1px solid #ccc;
            min-height: 12px;
            padding: 2px 0;
        }
        /* Signature table with no borders */
        .signature-table, .signature-table tr, .signature-table td {
            border: none;
            padding: 3px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="form-number">IPGKBM.UKP/BPKRIPKv1</div>
            <div class="logo-cell">
                <img class="logo" src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/94/Jata_MalaysiaV2.svg/2558px-Jata_MalaysiaV2.svg.png" alt="Jata Malaysia">
            </div>
            <div class="header-text-cell">
                <div class="header-text">
                    INSTITUT PENDIDIKAN GURU<br>
                    KAMPUS BAHASA MELAYU<br>
                    Jalan Pantai Baru<br>
                    59990 KUALA LUMPUR
                </div>
            </div>
        </div>

        <div class="form-title">BORANG PERMOHONAN KELUAR RASMI INSTITUT DAN PENGGUNAAN KENDERAAN</div>
        <div class="form-subtitle">(Isikan dalam 2 salinan)</div>

        <div class="checkbox-row">
            <div class="checkbox ' . ($urusanRasmi ? 'checked' : '') . '"></div>
            <label for="urusan_rasmi">Urusan Rasmi</label>
        </div>

        <div class="checkbox-row">
            <span class="checkbox-row-number">1.</span>
            <div class="checkbox ' . ($anjuranIpg ? 'checked' : '') . '"></div>
            <label for="anjuran_ipg">Anjuran IPG KBM</label>
        </div>
        
        <div class="checkbox-row">
            <span class="checkbox-row-number">2.</span>
            <div class="checkbox ' . ($anjuranKpm ? 'checked' : '') . '"></div>
            <label for="anjuran_kpm">Anjuran KPM</label>
        </div>
        
        <div class="checkbox-row">
            <span class="checkbox-row-number">3.</span>
            <div class="checkbox ' . ($anjuranAgensi ? 'checked' : '') . '"></div>
            <label for="anjuran_agensi">Anjuran Agensi Kerajaan (selain 2 di atas)</label>
        </div>

        <table>
            <tr>
                <td class="col-30">Nama Pemohon :</td>
                <td colspan="3"><div class="filled-text">' . htmlspecialchars($user['name']) . '</div></td>
            </tr>
            <tr>
                <td>Jawatan:</td>
                <td><div class="filled-text">' . htmlspecialchars($user['position']) . '</div></td>
                <td>Jabatan/Unit:</td>
                <td><div class="filled-text">' . htmlspecialchars($user['department']) . '</div></td>
            </tr>
            <tr>
                <td>Urusan:</td>
                <td colspan="3"><div class="filled-text">' . htmlspecialchars($application['purpose_details']) . '</div></td>
            </tr>
            <tr>
                <td>Alamat Tempat<br>Bertugas Rasmi:</td>
                <td colspan="3"><div class="filled-text">' . htmlspecialchars($application['duty_location']) . '</div></td>
            </tr>
            <tr>
                <td>Waktu Keluar:</td>
                <td><div class="filled-text">' . $exitTime . '</div></td>
                <td>Waktu Balik:</td>
                <td><div class="filled-text">' . $returnTime . '</div></td>
            </tr>
            <tr>
                <td>Tarikh dari:</td>
                <td><div class="filled-text">' . $startDate . '</div></td>
                <td>Hingga:</td>
                <td><div class="filled-text">' . $endDate . '</div></td>
            </tr>
            <tr>
                <td>Jenis Kenderaan/<br>Kapasiti Enjin:</td>
                <td><div class="filled-text">' . htmlspecialchars($application['transportation_details'] ?? '') . '</div></td>
                <td>Anggaran km<br>pergi/balik:</td>
                <td><div class="filled-text">' . htmlspecialchars($application['distance_estimate'] ?? '') . '</div></td>
            </tr>
            <tr class="transportation-section">
                <td rowspan="3">Kenderaan ke<br>tempat bertugas:</td>
                <td colspan="2" class="checkbox-cell">
                    <div class="checkbox-row">
                        <div class="checkbox ' . ($bas ? 'checked' : '') . '"></div>
                        <label for="bas">Bas</label>
                    </div>
                </td>
                <td class="checkbox-cell">
                    <div class="checkbox-row">
                        <div class="checkbox ' . ($berkongsiKenderaan ? 'checked' : '') . '"></div>
                        <label for="berkongsi">Berkongsi Kenderaan</label>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="checkbox-cell">
                    <div class="checkbox-row">
                        <div class="checkbox ' . ($teksi ? 'checked' : '') . '"></div>
                        <label for="teksi">Teksi</label>
                    </div>
                </td>
                <td class="checkbox-cell">
                    <div class="checkbox-row">
                        <div class="checkbox ' . ($kenderaanRasmi ? 'checked' : '') . '"></div>
                        <label for="rasmi">Kenderaan Rasmi</label>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="checkbox-cell">
                    <div class="checkbox-row">
                        <div class="checkbox ' . ($kapalTerbang ? 'checked' : '') . '"></div>
                        <label for="kapal">Kapal Terbang</label>
                    </div>
                    <div class="checkbox-row">
                        <div class="checkbox ' . ($keretapi ? 'checked' : '') . '"></div>
                        <label for="keretapi">Keretapi</label>
                    </div>
                </td>
                <td class="checkbox-cell">
                    <div class="checkbox-row">
                        <div class="checkbox ' . ($kenderaanSendiri ? 'checked' : '') . '"></div>
                        <label for="sendiri">Kenderaan Sendiri**</label>
                    </div>
                </td>
            </tr>
        </table>

        <div>Sebab-sebab membuat perjalanan dengan kenderaan sendiri:</div>
        <div class="filled-text">' . htmlspecialchars($application['personal_vehicle_reason'] ?? '') . '</div>

        <table class="signature-table">
            <tr>
                <td style="width: 50%; vertical-align: bottom;">
                    <div class="date-field" style="position: relative;">
                        Tarikh: <span style="border-bottom: 1px solid black; padding: 0 5px;">' . $currentDate . '</span>
                    </div>
                </td>
                <td style="width: 50%; vertical-align: bottom; text-align: right;">
                    <div class="signature-right">Tandatangan Pemohon</div>
                </td>
            </tr>
            <tr>
                <td></td>
                <td style="text-align: right; position: relative;">
                    <div class="signature-area">' . $userSignatureHtml . '</div>
                    <div class="nama-right">Nama: <span class="filled-text">' . htmlspecialchars($user['name']) . '</span></div>
                </td>
            </tr>
        </table>
        
        <div class="section-header" style="margin: 6px 0 3px 0; padding-top: 3px;">SOKONGAN KETUA JABATAN/KETUA UNIT</div>
        
        <div>Permohonan pegawai ini ' . ($application['ketua_approval_status'] === 'approved' ? 'disokong' : 'tidak disokong') . '.</div>
        
        <table class="signature-table">
            <tr>
                <td style="width: 50%; vertical-align: bottom;">
                    <div class="date-field">Tarikh: <span style="border-bottom: 1px solid black; padding: 0 5px;">' . 
                    ($application['ketua_approval_status'] === 'approved' ? date('d/m/Y', strtotime($application['ketua_approval_date'])) : '______________________________') . '</span></div>
                </td>
                <td style="width: 50%; vertical-align: bottom; text-align: right;">
                    <div class="signature-right">Tandatangan Ketua Jabatan/Ketua Unit</div>
                </td>
            </tr>
            <tr>
                <td></td>
                <td style="text-align: right;">
                    <div class="signature-area" style="min-height: 25px;">' . $ketuaSignatureHtml . '</div>
                    <div class="nama-right">Nama: <span class="filled-text">' . htmlspecialchars($ketuaName) . '</span></div>
                </td>
            </tr>
        </table>
        
        <div class="section-header" style="margin: 6px 0 3px 0; padding-top: 3px;">KELULUSAN PENGARAH</div>
        
        <div>Permohonan pegawai ini ' . ($application['pengarah_approval_status'] === 'approved' ? 'diluluskan' : 'tidak diluluskan') . ', tertakluk kepada Pekeliling Jabatan KP 9607/3 Jld.2(47)</div>
        
        <table class="signature-table">
            <tr>
                <td style="width: 50%; vertical-align: bottom;">
                    <div class="date-field">Tarikh: <span style="border-bottom: 1px solid black; padding: 0 5px;">' . 
                    ($application['pengarah_approval_status'] === 'approved' ? date('d/m/Y', strtotime($application['pengarah_approval_date'])) : '______________________________') . '</span></div>
                </td>
                <td style="width: 50%; vertical-align: bottom; text-align: right;">
                    <div class="signature-right">Tandatangan Pengarah</div>
                </td>
            </tr>
            <tr>
                <td></td>
                <td style="text-align: right;">
                    <div class="signature-area" style="min-height: 25px;">' . $pengarahSignatureHtml . '</div>
                    <div class="nama-right">Nama: <span class="filled-text">' . htmlspecialchars($pengarahName) . '</span></div>
                </td>
            </tr>
        </table>
        
        <div class="form-note" style="font-size: 9px; margin-top: 5px;">**Sila sertakan borang IPGKBM.UKP/BPGK240.v1 bagi perjalanan menggunakan kenderaan sendiri jarak melebihi 240km sehala.</div>
    </div>
</body>
</html>';
}

/**
 * Generate PDF from HTML using Dompdf
 * 
 * @param string $html HTML content
 * @param string $outputPath Output file path
 * @return bool Success status
 */
function generatePdfFromHtml($html, $outputPath) {
    try {
            require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';
            
        if (class_exists('Dompdf\\Dompdf')) {
            pdfDebugLog("Dompdf class found");
                $dompdf = new \Dompdf\Dompdf();
                $dompdf->setPaper('A4', 'portrait');
            
            // Enhanced Dompdf configuration
            $options = $dompdf->getOptions();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'Arial');
            $options->set('isFontSubsettingEnabled', true);
            $options->set('debugKeepTemp', true);
            $options->set('debugCss', true);
            $dompdf->setOptions($options);
            
            // Load HTML
                $dompdf->loadHtml($html);
                
            pdfDebugLog("Rendering PDF with Dompdf");
                $dompdf->render();
                
            pdfDebugLog("Saving PDF to: $outputPath");
                file_put_contents($outputPath, $dompdf->output());
                
                if (file_exists($outputPath)) {
                pdfDebugLog("PDF generated successfully with Dompdf");
                return true;
                } else {
                pdfDebugLog("Failed to save PDF with Dompdf");
                return false;
                }
            } else {
            pdfDebugLog("Dompdf class not found");
            return false;
            }
        } catch (Exception $e) {
        pdfDebugLog("Dompdf error: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate HTML for the 240km form
 * 
 * @param array $formData Form data from JSON
 * @param array $user User data
 * @param array $application Application data
 * @return string HTML content
 */
function generate240kmFormHTML($formData, $user, $application) {
    // Get current date formatted
    $currentDate = date('d/m/Y');
    
    // Prepare user signature
    $userSignatureHtml = '';
    if (!empty($user['signature_path'])) {
        $signaturePath = dirname(dirname(__DIR__)) . '/app/uploads/signatures/' . $user['signature_path'];
        if (file_exists($signaturePath)) {
            $signatureData = base64_encode(file_get_contents($signaturePath));
            $userSignatureHtml = '<img src="data:image/png;base64,' . $signatureData . '" style="max-width: 120px; height: auto;" alt="Signature" />';
        }
    }
    
    // Prepare pengarah signature
    $pengarahSignatureHtml = '';
    // Check if we have pengarah approval data
    $pengarahApproval = isset($formData['pengarah_approval']) ? $formData['pengarah_approval'] : '';
    $pengarahName = isset($formData['pengarah_name']) ? $formData['pengarah_name'] : '';
    $pengarahApprovalDate = isset($formData['pengarah_approval_date']) ? date('d/m/Y', strtotime($formData['pengarah_approval_date'])) : '';
    
    // Get pengarah signature if available
    if (!empty($pengarahName)) {
        try {
            // Find pengarah signature
            $stmt = $GLOBALS['pdo']->prepare("SELECT signature_path FROM users WHERE name = ? AND role = 'pengarah' LIMIT 1");
            $stmt->execute([$pengarahName]);
            $pengarahData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($pengarahData && !empty($pengarahData['signature_path'])) {
                $pengarahSignaturePath = dirname(dirname(__DIR__)) . '/app/uploads/signatures/' . $pengarahData['signature_path'];
                if (file_exists($pengarahSignaturePath)) {
                    $pengarahSignatureData = base64_encode(file_get_contents($pengarahSignaturePath));
                    $pengarahSignatureHtml = '<img src="data:image/png;base64,' . $pengarahSignatureData . '" style="max-width: 140px; height: auto; margin-top: 3px; margin-bottom: 3px;" alt="Pengarah Signature" />';
                }
            }
        } catch (Exception $e) {
            pdfDebugLog('Error getting pengarah signature: ' . $e->getMessage());
        }
    }
    
    // Prepare reason checkboxes
    $reasons = isset($formData['sebab[]']) ? [$formData['sebab[]']] : ($formData['sebab'] ?? []);
    if (!is_array($reasons)) {
        $reasons = [$reasons];
    }
    
    // Log reasons for debugging
    pdfDebugLog('Reasons for 240km form: ' . print_r($reasons, true));
    
    $reason1Checked = in_array('Perlu menjalankan tugas rasmi di beberapa tempat disepanjang perjalanan', $reasons) ? true : false;
    $reason2Checked = in_array('Mustahak dan terpaksa berkenderaan sendiri', $reasons) ? true : false;
    $reason3Checked = in_array('Membawa pegawai lain', $reasons) ? true : false;
    $reason4Checked = in_array('Menuntut tambang gantian', $reasons) ? true : false;
    
    // Prepare pengarah approval checkboxes
    $diluluskanBiasaChecked = ($pengarahApproval === 'diluluskan_biasa') ? true : false;
    $diluluskanTambangChecked = ($pengarahApproval === 'diluluskan_tambang') ? true : false;
    $tidakDiluluskanChecked = ($pengarahApproval === 'tidak_diluluskan') ? true : false;
    
    // Build the HTML content
    return '<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borang 240KM</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.3;
            max-width: 800px;
            margin: 0 auto;
        }
        .container {
            padding: 10px 20px;
        }
        .header {
            display: table;
            width: 100%;
            position: relative;
            margin-bottom: 12px;
        }
        .logo-cell {
            display: table-cell;
            vertical-align: top;
            width: 70px;
        }
        .logo {
            width: 55px;
            height: auto;
        }
        .header-text-cell {
            display: table-cell;
            vertical-align: top;
            padding-top: 5px;
        }
        .header-text {
            font-size: 11px;
            font-weight: bold;
            line-height: 1.3;
        }
        .form-number {
            position: absolute;
            top: 0;
            right: 0;
            font-size: 10px;
        }
        .form-title {
            text-align: center;
            font-weight: bold;
            margin: 10px 0 3px 0;
            font-size: 12px;
        }
        .form-subtitle {
            text-align: center;
            margin-bottom: 10px;
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
        }
        table, th, td {
            border: 1px solid black;
        }
        th {
            font-weight: bold;
            text-align: center;
            background-color: #f2f2f2;
            font-size: 11px;
            padding: 4px;
        }
        td {
            padding: 4px 5px;
            font-size: 11px;
            vertical-align: top;
        }
        .info-section {
            margin: 10px 0;
            font-size: 11px;
        }
        .info-section p {
            margin: 5px 0;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            min-width: 100px;
        }
        .reasons-section {
            margin: 15px 0;
            font-size: 11px;
        }
        .reasons-title {
            font-weight: bold;
            margin-bottom: 8px;
        }
        .reason-item {
            margin: 6px 0;
            position: relative;
        }
        .checkbox {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1px solid black;
            margin-right: 8px;
            position: relative;
            vertical-align: top;
        }
        .checkbox.checked {
            background: white;
        }
        .checkbox.checked::after {
            content: "X";
            position: absolute;
            color: black;
            font-size: 10px;
            font-weight: bold;
            top: -2px;
            left: 2px;
        }
        .reason-text {
            display: inline-block;
            width: calc(100% - 25px);
            vertical-align: top;
        }
        .signature-table {
            width: 100%;
            margin-top: 30px;
            border: none;
        }
        .signature-table td {
            border: none;
            padding: 3px 0;
            vertical-align: bottom;
        }
        .signature-left {
            width: 50%;
            text-align: left;
        }
        .signature-right {
            width: 50%;
            text-align: right;
        }
        .signature-line {
            border-bottom: 1px solid black;
            padding: 0 5px;
            display: inline-block;
        }
        .signature-image {
            min-height: 40px;
            margin-top: 5px;
            text-align: right;
        }
        .approval-section {
            margin-top: 20px;
            border-top: 1px solid black;
            padding-top: 10px;
            font-size: 11px;
        }
        .approval-title {
            text-align: center;
            font-weight: bold;
            margin: 10px 0;
            font-size: 12px;
        }
        .approval-field {
            margin: 8px 0;
        }
        .approval-label {
            font-weight: bold;
            margin-bottom: 3px;
            display: inline-block;
            width: 160px;
        }
        .approval-line {
            display: inline-block;
            width: calc(100% - 170px);
            vertical-align: top;
        }
        .approval-field-signed {
            margin: 6px 0;
        }
        .approval-value {
            display: inline-block;
            vertical-align: top;
            width: calc(100% - 170px);
            padding-left: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="form-number">IPGKBM.UKP/BPGK240.v1</div>
            <div class="logo-cell">
                <img class="logo" src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/94/Jata_MalaysiaV2.svg/2558px-Jata_MalaysiaV2.svg.png" alt="Jata Malaysia">
            </div>
            <div class="header-text-cell">
                <div class="header-text">
                    INSTITUT PENDIDIKAN GURU<br>
                    KAMPUS BAHASA MELAYU<br>
                    Jalan Pantai Baru<br>
                    59990 KUALA LUMPUR
                </div>
            </div>
        </div>

        <div class="form-title">BORANG PERMOHONAN UNTUK MENGGUNAKAN KENDERAAN SENDIRI DAN MENUNTUT ELAUN</div>
        <div class="form-title">KILOMETER BAGI JARAK MELEBIHI 240KM SEHALA</div>
        <div class="form-subtitle">(PEKELILING PERBENDAHARAAN BIL.2/1992 – PARA 4.7.3)</div>

        <div class="info-section">
            <p><span class="info-label">Nama Pegawai:</span> ' . htmlspecialchars($formData['nama_pegawai'] ?? $user['name']) . '</p>
            <p><span class="info-label">Jawatan:</span> ' . htmlspecialchars($formData['jawatan'] ?? $user['position']) . '</p>
            <p><span class="info-label">Jabatan / Unit:</span> ' . htmlspecialchars($formData['jabatan_unit'] ?? $user['department']) . '</p>
        </div>

        <p>Saya dengan ini memohon untuk menggunakan kenderaan sendiri bagi menjalankan tugas rasmi di luar pejabat seperti berikut:</p>

        <table>
            <tr>
                <th style="width:15%">Tarikh</th>
                <th style="width:35%">Tempat Bertugas Rasmi</th>
                <th style="width:25%">Jenis Tugas</th>
                <th style="width:25%">Anggaran Jarak Pergi/Balik</th>
            </tr>
            <tr>
                <td>' . htmlspecialchars($formData['tarikh'] ?? date('d/m/Y', strtotime($application['start_date']))) . '</td>
                <td>' . htmlspecialchars($formData['tempat_bertugas'] ?? $application['duty_location']) . '</td>
                <td>' . htmlspecialchars($formData['jenis_tugas'] ?? $application['purpose_details']) . '</td>
                <td>' . htmlspecialchars($formData['anggaran_jarak'] ?? $application['distance_estimate']) . '</td>
            </tr>
        </table>

        <div class="reasons-section">
            <div class="reasons-title">Sebab-sebab membuat perjalanan dengan menggunakan kenderaan sendiri:</div>
            
            <div class="reason-item">
                <div class="checkbox ' . ($reason1Checked ? 'checked' : '') . '"></div><div class="reason-text">Perlu menjalankan tugas rasmi di beberapa tempat disepanjang perjalanan</div>
            </div>
            
            <div class="reason-item">
                <div class="checkbox ' . ($reason2Checked ? 'checked' : '') . '"></div><div class="reason-text">Mustahak dan terpaksa berkenderaan sendiri kerana ' . htmlspecialchars($formData['sebab_kenderaan_sendiri'] ?? '') . '</div>
            </div>
            
            <div class="reason-item">
                <div class="checkbox ' . ($reason3Checked ? 'checked' : '') . '"></div><div class="reason-text">Mustahak dan terpaksa membawa pegawai lain sebagai penumpang yang juga menjalankan tugas rasmi. Nama pegawai yang dibawa:<br>
                    a) ' . htmlspecialchars($formData['pegawai_lain_1'] ?? '') . '<br>
                    b) ' . htmlspecialchars($formData['pegawai_lain_2'] ?? '') . '</div>
            </div>
            
            <div class="reason-item">
                <div class="checkbox ' . ($reason4Checked ? 'checked' : '') . '"></div><div class="reason-text">Menggunakan kenderaan sendiri dengan menuntut tambang gantian persamaan dengan tambang kapal terbang</div>
            </div>
        </div>

        <table class="signature-table">
            <tr>
                <td class="signature-left">
                    Tarikh: <span class="signature-line">' . $currentDate . '</span>
                </td>
                <td class="signature-right">
                    Tandatangan Pemohon:
                </td>
            </tr>
            <tr>
                <td></td>
                <td class="signature-right">
                    <div class="signature-image">' . $userSignatureHtml . '</div>
                </td>
            </tr>
        </table>

        <div class="approval-section">
            <div class="approval-title">KELULUSAN PENGARAH</div>
            
            <div class="reason-item">
                <div class="checkbox ' . ($diluluskanBiasaChecked ? 'checked' : '') . '"></div><div class="reason-text">Diluluskan termaktub kepada syarat-syarat di dalam Pekeliling Perbendaharaan Bil. 3 Tahun 2003</div>
            </div>
            
            <div class="reason-item">
                <div class="checkbox ' . ($diluluskanTambangChecked ? 'checked' : '') . '"></div><div class="reason-text">Diluluskan dengan menuntut tambang gantian</div>
            </div>
            
            <div class="reason-item">
                <div class="checkbox ' . ($tidakDiluluskanChecked ? 'checked' : '') . '"></div><div class="reason-text">Tidak diluluskan</div>
            </div>
            
            ' . (!empty($pengarahSignatureHtml) ? '
            <div class="approval-field-signed" style="margin-top:10px;">
                <div class="approval-label">Tandatangan:</div>
                <div class="approval-value">' . $pengarahSignatureHtml . '</div>
            </div>
            ' : '
            <div class="approval-field" style="margin-top:10px;">
                <div class="approval-label">Tandatangan:</div>
                <div class="approval-line" style="border-bottom: 1px solid black; height: 25px;"></div>
            </div>
            ') . '
            
            <div class="approval-field">
                <div class="approval-label">Cop Nama dan Jawatan:</div>
                ' . (!empty($pengarahName) ? '
                <div class="approval-value">' . htmlspecialchars($pengarahName) . '</div>
                ' : '
                <div class="approval-line" style="border-bottom: 1px solid black; height: 1px;"></div>
                ') . '
            </div>
            
            <div class="approval-field">
                <div class="approval-label">Tarikh:</div>
                ' . (!empty($pengarahApprovalDate) ? '
                <div class="approval-value">' . $pengarahApprovalDate . '</div>
                ' : '
                <div class="approval-line" style="border-bottom: 1px solid black; height: 1px;"></div>
                ') . '
            </div>
        </div>
    </div>
</body>
</html>';
}

/**
 * Update application with PDF path
 * 
 * @param int $applicationId Application ID
 * @param string $pdfPath Path to generated PDF
 * @return bool Success status
 */
function updateApplicationPDFPath($applicationId, $pdfPath) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("UPDATE applications SET pdf_path = ? WHERE id = ?");
        return $stmt->execute([$pdfPath, $applicationId]);
    } catch (PDOException $e) {
        pdfDebugLog('Update PDF Path Error: ' . $e->getMessage());
        return false;
    }
} 
