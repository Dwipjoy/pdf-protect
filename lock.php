<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Lock Tool</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2, h3 {
            color: #2c3e50;
        }
        .success {
            color: #27ae60;
            background-color: #e8f5e9;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #27ae60;
            margin-bottom: 20px;
        }
        .error {
            color: #e74c3c;
            background-color: #fdedec;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #e74c3c;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #2980b9;
        }
        .btn-download {
            background: #27ae60;
        }
        .btn-download:hover {
            background: #219653;
        }
        form {
            margin-top: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="file"], input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            background: #3498db;
            color: white;
            border: none;
            padding: 12px 20px;
            cursor: pointer;
            border-radius: 4px;
            font-size: 16px;
        }
        input[type="submit"]:hover {
            background: #2980b9;
        }
        pre {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo h1 {
            color: #3498db;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>🔒 PDF Lock Tool</h1>
            <p>Protect your PDF files with a password</p>
        </div>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'];

            if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === 0) {
                $uploadDir = __DIR__ . '/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $originalName = basename($_FILES['pdf_file']['name']);
                $uniqueName = uniqid() . '_' . $originalName;
                $inputPath = $uploadDir . $uniqueName;
                $outputPath = $uploadDir . 'locked_' . $uniqueName;

                // Move uploaded file
                if (!move_uploaded_file($_FILES['pdf_file']['tmp_name'], $inputPath)) {
                    echo '<div class="error"><h3>❌ Failed to move uploaded file.</h3></div>';
                } else {
                    // Full path to qpdf
                    $qpdfPath = "C:\\qpdf\\bin\\qpdf.exe";  // Update if your qpdf path is different
                    $cmd = "\"$qpdfPath\" --encrypt \"$password\" \"$password\" 256 -- \"$inputPath\" \"$outputPath\"";

                    exec($cmd, $outputLines, $resultCode);

                    if ($resultCode === 0 && file_exists($outputPath)) {
                        echo '<div class="success">';
                        echo '<h2>✅ PDF locked successfully!</h2>';
                        echo '<p>Your PDF file has been encrypted with the password you provided.</p>';
                        echo '<a href="uploads/' . basename($outputPath) . '" class="btn btn-download" download>⬇️ Download Locked PDF</a>';
                        echo '</div>';
                    } else {
                        echo '<div class="error">';
                        echo '<h3>❌ Error locking PDF</h3>';
                        echo '<p>There was an error while trying to encrypt your PDF file.</p>';
                        echo '<details>';
                        echo '<summary>Technical details</summary>';
                        echo '<pre>Command: ' . htmlspecialchars($cmd) . "\n";
                        print_r($outputLines);
                        echo "\nResult code: $resultCode</pre>";
                        echo '</details>';
                        echo '</div>';
                    }

                    // Clean up original
                    if (file_exists($inputPath)) {
                        unlink($inputPath);
                    }
                }
            } else {
                echo '<div class="error">';
                echo '<h3>❌ Error: No valid file uploaded</h3>';
                echo '<p>Please select a PDF file to upload.</p>';
                echo '</div>';
            }
        }
        ?>

        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="pdf_file">Select PDF File:</label>
                <input type="file" name="pdf_file" id="pdf_file" accept=".pdf" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" placeholder="Enter password" required>
            </div>
            <input type="submit" value="🔒 Lock PDF">
        </form>

        <div style="margin-top: 30px; font-size: 0.9em; color: #7f8c8d;">
            <p><strong>Note:</strong> This tool uses strong 256-bit AES encryption to protect your PDF files.</p>
        </div>
    </div>
</body>
</html>