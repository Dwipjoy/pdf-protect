<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
        die("Error uploading file.");
    }

    $password = escapeshellarg($_POST['pdf_password']);
    $uploadedFile = $_FILES['pdf_file']['tmp_name'];
    $originalName = basename($_FILES['pdf_file']['name']);
    $outputFile = sys_get_temp_dir() . '/' . uniqid('unlocked_') . '.pdf';

    // Validate file MIME type for security (optional)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $uploadedFile);
    finfo_close($finfo);
    if ($mimeType !== 'application/pdf') {
        die("Uploaded file is not a valid PDF.");
    }

    // Run qpdf command to decrypt PDF
    // Note: qpdf must be installed and in the server PATH
    $cmd = "qpdf --password=$password --decrypt " . escapeshellarg($uploadedFile) . " " . escapeshellarg($outputFile);
    exec($cmd . " 2>&1", $output, $return_var);

    if ($return_var !== 0) {
        // Unlock failed (probably wrong password)
        unlink($outputFile);
        die("Failed to unlock PDF. Check your password and try again.");
    }

    // Output the unlocked PDF to browser for download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="unlocked_' . $originalName . '"');
    header('Content-Length: ' . filesize($outputFile));
    readfile($outputFile);

    // Clean up temporary file
    unlink($outputFile);
    exit;
}
?>
