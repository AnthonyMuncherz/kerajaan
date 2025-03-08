<?php
/**
 * wkhtmltopdf Installer Script
 * 
 * This script helps users install wkhtmltopdf for PDF generation.
 * It detects the system architecture and provides download links
 * and installation instructions.
 */

// Enable error display
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define wkhtmltopdf versions and download links
$wkhtmltopdfLinks = [
    'windows-64bit' => 'https://github.com/wkhtmltopdf/packaging/releases/download/0.12.6-1/wkhtmltox-0.12.6-1.msvc2015-win64.exe',
    'windows-32bit' => 'https://github.com/wkhtmltopdf/packaging/releases/download/0.12.6-1/wkhtmltox-0.12.6-1.msvc2015-win32.exe',
    'linux-64bit' => 'https://github.com/wkhtmltopdf/packaging/releases/download/0.12.6-1/wkhtmltox_0.12.6-1.bionic_amd64.deb',
    'linux-32bit' => 'https://github.com/wkhtmltopdf/packaging/releases/download/0.12.6-1/wkhtmltox_0.12.6-1.bionic_i386.deb',
    'macos' => 'https://github.com/wkhtmltopdf/packaging/releases/download/0.12.6-1/wkhtmltox-0.12.6-1.macos-cocoa.pkg'
];

// Helper function to check if a program is installed
function isProgramInstalled($program) {
    $whereCommand = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') 
        ? "where $program 2>nul" 
        : "which $program 2>/dev/null";
    
    exec($whereCommand, $output, $returnCode);
    return $returnCode === 0;
}

// Helper function to detect system architecture
function getSystemArchitecture() {
    // Check OS type
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Windows
        return (PHP_INT_SIZE === 8) ? 'windows-64bit' : 'windows-32bit';
    } elseif (strtoupper(substr(PHP_OS, 0, 3)) === 'DAR') {
        // macOS
        return 'macos';
    } else {
        // Linux or other Unix-like
        $arch = php_uname('m');
        return (strpos($arch, '64') !== false) ? 'linux-64bit' : 'linux-32bit';
    }
}

// Generate HTML content
$html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>wkhtmltopdf Installer</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        h2 {
            color: #2c3e50;
            margin-top: 20px;
        }
        .status {
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .status-success {
            background-color: #d4edda;
            border-left: 5px solid #28a745;
            color: #155724;
        }
        .status-warning {
            background-color: #fff3cd;
            border-left: 5px solid #ffc107;
            color: #856404;
        }
        .status-error {
            background-color: #f8d7da;
            border-left: 5px solid #dc3545;
            color: #721c24;
        }
        a.button {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        }
        a.button:hover {
            background-color: #2980b9;
        }
        .steps {
            background-color: #e8f4fc;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
        }
        code {
            background-color: #f1f1f1;
            padding: 2px 5px;
            border-radius: 3px;
            font-family: Consolas, Monaco, "Courier New", monospace;
        }
        ul {
            padding-left: 20px;
        }
        li {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">';

// Check if wkhtmltopdf is already installed
$wkhtmltopdfInstalled = isProgramInstalled('wkhtmltopdf');
$systemArch = getSystemArchitecture();
$downloadLink = $wkhtmltopdfLinks[$systemArch] ?? $wkhtmltopdfLinks['windows-64bit'];

if ($wkhtmltopdfInstalled) {
    // Get version info
    exec('wkhtmltopdf --version', $versionOutput, $returnCode);
    $versionInfo = $returnCode === 0 ? implode("\n", $versionOutput) : 'Unknown version';
    
    $html .= '<h1>wkhtmltopdf Installation Status</h1>
        <div class="status status-success">
            <h2>✅ wkhtmltopdf is already installed!</h2>
            <p><strong>Version information:</strong></p>
            <pre>' . htmlspecialchars($versionInfo) . '</pre>
        </div>
        <h2>Next Steps</h2>
        <div class="steps">
            <p>You can now use wkhtmltopdf for PDF generation in your application. No further action is required.</p>
            <p>If you want to update your installation, you can download the latest version from the link below:</p>
            <p><a class="button" href="' . $downloadLink . '">Download Latest Version</a></p>
        </div>';
} else {
    $html .= '<h1>wkhtmltopdf Installation Guide</h1>
        <div class="status status-warning">
            <h2>⚠️ wkhtmltopdf is not installed on this system</h2>
            <p>wkhtmltopdf is required for PDF generation in this application.</p>
        </div>
        
        <h2>Installation Instructions</h2>
        <div class="steps">
            <p>We\'ve detected your system as: <strong>' . $systemArch . '</strong></p>
            <p>Follow these steps to install wkhtmltopdf:</p>';
    
    if (strpos($systemArch, 'windows') !== false) {
        $html .= '<ol>
                <li>Download the wkhtmltopdf installer: <a class="button" href="' . $downloadLink . '">Download wkhtmltopdf</a></li>
                <li>Run the installer and follow the prompts</li>
                <li>Make sure to check the option to add wkhtmltopdf to your system PATH</li>
                <li>Restart your web server (Apache/Nginx) after installation</li>
                <li>Refresh this page to verify the installation</li>
            </ol>';
    } elseif ($systemArch === 'macos') {
        $html .= '<ol>
                <li>Download the wkhtmltopdf package: <a class="button" href="' . $downloadLink . '">Download wkhtmltopdf</a></li>
                <li>Open the .pkg file and follow the installation prompts</li>
                <li>Alternatively, you can use Homebrew: <code>brew install wkhtmltopdf</code></li>
                <li>Restart your web server after installation</li>
                <li>Refresh this page to verify the installation</li>
            </ol>';
    } else {
        $html .= '<ol>
                <li>On Debian/Ubuntu systems, download: <a class="button" href="' . $downloadLink . '">Download wkhtmltopdf</a></li>
                <li>Install the package: <code>sudo dpkg -i wkhtmltox_0.12.6-1.bionic_amd64.deb</code></li>
                <li>If you encounter any dependencies issues: <code>sudo apt-get install -f</code></li>
                <li>On other Linux distributions, check the <a href="https://wkhtmltopdf.org/downloads.html">official website</a> for instructions</li>
                <li>Restart your web server after installation</li>
                <li>Refresh this page to verify the installation</li>
            </ol>';
    }
    
    $html .= '</div>
        
        <h2>Alternative: Using Dompdf</h2>
        <div class="steps">
            <p>Your application is already configured to use Dompdf as a fallback for PDF generation when wkhtmltopdf is not available.</p>
            <p>Dompdf is already installed, but for the best PDF quality, we recommend installing wkhtmltopdf.</p>
        </div>';
}

$html .= '<h2>Test PDF Generation</h2>
        <p>You can test PDF generation with your current setup:</p>
        <p><a class="button" href="test_pdf.php">Generate Test PDF</a></p>
        
        <h2>Form Generation</h2>
        <p>To test the form generation functionality:</p>
        <p><a class="button" href="test_form_pdf.php">Generate Test Form PDF</a></p>
    </div>
</body>
</html>';

// Output the HTML
echo $html; 