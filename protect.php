<?php
header('Content-Type: application/json');

// Check if all required fields are present
if (!isset($_FILES['pdfFile']) || !isset($_POST['password'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

$pdfFile = $_FILES['pdfFile'];
$password = trim($_POST['password']);

// Validate password
if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Password must be at least 6 characters']);
    exit;
}

// Validate file upload
if ($pdfFile['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'File upload error: ' . $pdfFile['error']]);
    exit;
}

// Validate file type
$fileType = strtolower(pathinfo($pdfFile['name'], PATHINFO_EXTENSION));
if ($fileType !== 'pdf') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Only PDF files are allowed']);
    exit;
}

// Create directories if they don't exist
$uploadDir = 'uploads';
$protectedDir = 'protected';

if (!file_exists($uploadDir) && !mkdir($uploadDir, 0755, true)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to create upload directory']);
    exit;
}

if (!file_exists($protectedDir) && !mkdir($protectedDir, 0755, true)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to create protected directory']);
    exit;
}

// Generate unique filenames
$uploadPath = $uploadDir . '/' . uniqid() . '.pdf';
$outputFilename = uniqid() . '_protected.pdf';
$outputPath = $protectedDir . '/' . $outputFilename;

// Move uploaded file
if (!move_uploaded_file($pdfFile['tmp_name'], $uploadPath)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to save uploaded file']);
    exit;
}

// Check if qpdf is available
exec('which qpdf', $output, $returnCode);
if ($returnCode !== 0) {
    unlink($uploadPath);
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'qpdf is not installed on server']);
    exit;
}

// Escape password for shell command
$escapedPassword = escapeshellarg($password);

// Protect the PDF using qpdf
$command = "qpdf --encrypt {$escapedPassword} {$escapedPassword} 128 -- {$uploadPath} {$outputPath} 2>&1";
exec($command, $output, $returnCode);

// Clean up uploaded file
unlink($uploadPath);

if ($returnCode !== 0) {
    // If output file was created but command failed, clean it up
    if (file_exists($outputPath)) {
        unlink($outputPath);
    }
    
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to protect PDF',
        'debug' => [
            'command' => $command,
            'output' => $output,
            'returnCode' => $returnCode
        ]
    ]);
    exit;
}

// Verify the output file exists
if (!file_exists($outputPath)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Protected PDF was not created']);
    exit;
}

// Return success with download link
echo json_encode([
    'status' => 'success',
    'message' => 'PDF protected successfully!',
    'downloadLink' => $protectedDir . '/' . $outputFilename
]);
?>