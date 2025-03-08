<?php
// Test script to verify Dompdf is working
require_once __DIR__ . '/vendor/autoload.php';

// Enable error logging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Testing PDF generation with Dompdf...<br>";

try {
    // Check if Dompdf class exists
    if (!class_exists('Dompdf\Dompdf')) {
        echo "ERROR: Dompdf class not found!<br>";
        echo "Make sure composer require dompdf/dompdf was successful.<br>";
        exit;
    }
    
    echo "Dompdf class found.<br>";
    
    // Create a simple HTML document
    $html = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Test PDF</title>
        <style>
            body { font-family: Arial, sans-serif; }
            h1 { color: #2c3e50; }
            .container { padding: 20px; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>PDF Generation Test</h1>
            <p>This is a test to verify that Dompdf is working correctly.</p>
            <p>Current date and time: ' . date('Y-m-d H:i:s') . '</p>
        </div>
    </body>
    </html>';
    
    // Initialize Dompdf
    $dompdf = new \Dompdf\Dompdf();
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->set_option('isHtml5ParserEnabled', true);
    $dompdf->set_option('isRemoteEnabled', true);
    $dompdf->set_option('defaultFont', 'Arial');
    
    // Load HTML content
    $dompdf->loadHtml($html);
    
    // Render the PDF
    echo "Rendering PDF...<br>";
    $dompdf->render();
    
    // Output directory
    $pdfDir = __DIR__ . '/app/pdf';
    if (!is_dir($pdfDir)) {
        mkdir($pdfDir, 0755, true);
        echo "Created output directory: $pdfDir<br>";
    }
    
    // Save the PDF
    $outputPath = $pdfDir . '/test_' . time() . '.pdf';
    file_put_contents($outputPath, $dompdf->output());
    
    // Check if file was created
    if (file_exists($outputPath)) {
        echo "SUCCESS: PDF generated successfully!<br>";
        echo "PDF saved to: $outputPath<br>";
        echo "<a href='app/pdf/" . basename($outputPath) . "' target='_blank'>View PDF</a>";
    } else {
        echo "ERROR: Failed to save PDF.<br>";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
} 