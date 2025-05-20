<?php
$qpdfPath = 'qpdf'; // Path to qpdf binary

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
    $uploadedFile = $_FILES['pdf_file']['tmp_name'];
    $originalName = basename($_FILES['pdf_file']['name']);
    $outputFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'protected_' . time() . '.pdf';

    // Get passwords
    $userPassword = $_POST['user_password'] ?? '';
    $ownerPassword = $_POST['owner_password'] ?? 'Dwip@1123';

    // Get permissions
    $allowPrint = isset($_POST['allow_print']) ? 'full' : 'none';
    $allowModify = isset($_POST['allow_modify']) ? 'all' : 'none';
    $allowCopy = isset($_POST['allow_copy']) ? 'y' : 'n';

    // Escape passwords
    function escape_qpdf_password($pass) {
        $pass = str_replace('"', '\"', $pass);
        return '"' . $pass . '"';
    }

    $userPwdEsc = escape_qpdf_password($userPassword);
    $ownerPwdEsc = escape_qpdf_password($ownerPassword);

    // Build qpdf command
    $cmd = sprintf(
        '%s --encrypt %s %s 256 --print=%s --modify=%s --extract=%s -- %s %s',
        escapeshellcmd($qpdfPath),
        $userPwdEsc,
        $ownerPwdEsc,
        $allowPrint,
        $allowModify,
        $allowCopy,
        escapeshellarg($uploadedFile),
        escapeshellarg($outputFile)
    );

    exec($cmd, $output, $return_var);

    if ($return_var !== 0) {
        $errorMessage = 'Failed to encrypt PDF. Please try again.';
    } else {
        // Store the file path in session for download
        session_start();
        $_SESSION['protected_pdf'] = $outputFile;
        $_SESSION['original_name'] = $originalName;
        header('Location: ' . $_SERVER['PHP_SELF'] . '?success=1');
        exit;
    }
}

// Handle download
if (isset($_GET['download'])) {
    session_start();
    if (isset($_SESSION['protected_pdf']) && file_exists($_SESSION['protected_pdf'])) {
        $outputFile = $_SESSION['protected_pdf'];
        $originalName = $_SESSION['original_name'] ?? 'protected.pdf';
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="protected_' . $originalName . '"');
        header('Content-Length: ' . filesize($outputFile));
        readfile($outputFile);
        unlink($outputFile);
        unset($_SESSION['protected_pdf']);
        unset($_SESSION['original_name']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Secure | Protect Your Documents</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --secondary: #3f37c9;
            --light: #ffffff;
            --light-gray: #f8f9fa;
            --medium-gray: #e9ecef;
            --dark-gray: #6c757d;
            --dark: #212529;
            --success: #4cc9f0;
            --error: #f72585;
            --warning: #ff9a00;
            --border-radius: 16px;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            --animation-duration: 0.5s;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: var(--light-gray);
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            display: flex;
            flex-direction: row;
            width: 100%;
            max-width: 1200px;
            background-color: var(--light);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            animation: fadeIn var(--animation-duration) ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .info-panel {
            flex: 1;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
            order: 1;
        }

        .info-panel::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .info-content {
            position: relative;
            z-index: 2;
        }

        .info-panel h2 {
            font-size: 2rem;
            margin-bottom: 1.5rem;
            font-weight: 700;
        }

        .info-panel p {
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .features {
            list-style: none;
            margin-top: 2rem;
        }

        .features li {
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .features i {
            font-size: 1.2rem;
            color: var(--success);
        }

        .form-panel {
            flex: 1;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            transition: var(--transition);
            order: 2;
        }

        .success-panel {
            flex: 1;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            order: 2;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 2rem;
            color: var(--primary);
            font-weight: 700;
            font-size: 1.5rem;
        }

        .logo i {
            font-size: 1.8rem;
        }

        .form-panel h3, .success-panel h3 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: var(--dark);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark-gray);
        }

        .file-upload {
            border: 2px dashed var(--medium-gray);
            border-radius: var(--border-radius);
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            background-color: var(--light-gray);
        }

        .file-upload:hover {
            border-color: var(--primary);
            background-color: rgba(67, 97, 238, 0.05);
        }

        .file-upload i {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 0.75rem;
        }

        .file-upload p {
            color: var(--dark-gray);
            margin-bottom: 0.5rem;
        }

        .file-upload input[type="file"] {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0;
            top: 0;
            left: 0;
            cursor: pointer;
        }

        .file-name {
            font-size: 0.875rem;
            color: var(--primary);
            font-weight: 500;
            margin-top: 0.5rem;
            word-break: break-all;
        }

        .input-group {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .input-group input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 1px solid var(--medium-gray);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
            background-color: var(--light-gray);
        }

        .input-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .input-group i {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--dark-gray);
            cursor: pointer;
            transition: var(--transition);
        }

        .input-group i:hover {
            color: var(--primary);
        }

        .password-strength {
            margin-top: 0.5rem;
            height: 4px;
            background-color: var(--medium-gray);
            border-radius: 2px;
            overflow: hidden;
        }

        .strength-meter {
            height: 100%;
            width: 0%;
            transition: var(--transition);
        }

        .strength-labels {
            display: flex;
            justify-content: space-between;
            margin-top: 0.5rem;
            font-size: 0.75rem;
            color: var(--dark-gray);
        }

        .permissions-group {
            margin-bottom: 1.5rem;
        }

        .permissions-title {
            font-weight: 500;
            color: var(--dark-gray);
            margin-bottom: 0.75rem;
        }

        .permission-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .permission-item input[type="checkbox"] {
            margin-right: 0.75rem;
            width: 1.1em;
            height: 1.1em;
            accent-color: var(--primary);
        }

        .permission-item label {
            cursor: pointer;
            color: var(--dark);
        }

        .btn {
            width: 100%;
            padding: 0.875rem 1.5rem;
            background: linear-gradient(to right, var(--primary), var(--primary-light));
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 1rem;
            position: relative;
            overflow: hidden;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background: rgba(67, 97, 238, 0.1);
        }

        .btn span {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .error-message {
            background-color: rgba(247, 37, 133, 0.1);
            border-left: 4px solid var(--error);
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 0 var(--border-radius) var(--border-radius) 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .error-message i {
            color: var(--error);
            font-size: 1.2rem;
        }

        .error-message p {
            margin: 0;
            color: var(--error);
            font-weight: 500;
        }

        .success-message {
            background-color: rgba(76, 201, 240, 0.1);
            border-left: 4px solid var(--success);
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 0 var(--border-radius) var(--border-radius) 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .success-message i {
            color: var(--success);
            font-size: 1.2rem;
        }

        .success-message p {
            margin: 0;
            color: var(--success);
            font-weight: 500;
        }

        .note {
            font-size: 0.875rem;
            color: var(--dark-gray);
            margin-top: 1.5rem;
            padding: 0.75rem;
            background-color: rgba(108, 117, 125, 0.1);
            border-radius: var(--border-radius);
        }

        .pdf-preview {
            width: 100%;
            height: 400px;
            border: 1px solid var(--medium-gray);
            border-radius: var(--border-radius);
            margin: 2rem 0;
            overflow: hidden;
            position: relative;
        }

        .pdf-preview iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .pdf-preview .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            opacity: 0;
            transition: var(--transition);
        }

        .pdf-preview:hover .overlay {
            opacity: 1;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            width: 100%;
            margin-top: 1rem;
        }

        .action-buttons .btn {
            flex: 1;
        }

        @media (max-width: 992px) {
            .container {
                flex-direction: column;
                max-width: 600px;
            }
            
            .info-panel, .form-panel, .success-panel {
                padding: 2rem;
            }
            
            .info-panel {
                order: 2;
            }
            
            .form-panel, .success-panel {
                order: 1;
            }
        }

        @media (max-width: 576px) {
            .info-panel, .form-panel, .success-panel {
                padding: 1.5rem;
            }
            
            .info-panel h2 {
                font-size: 1.5rem;
            }
            
            .form-panel h3, .success-panel h3 {
                font-size: 1.25rem;
            }

            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="info-panel">
            <div class="info-content">
                <h2>Advanced PDF Protection</h2>
                <p>Secure your sensitive documents with customizable permissions and military-grade encryption.</p>
                <ul class="features">
                    <li><i class="fas fa-shield-alt"></i> AES-256 encryption standard</li>
                    <li><i class="fas fa-key"></i> Separate user and owner passwords</li>
                    <li><i class="fas fa-cogs"></i> Granular permission controls</li>
                    <li><i class="fas fa-cloud-upload-alt"></i> Secure file processing</li>
                </ul>
            </div>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="success-panel">
                <div class="logo">
                    <i class="fas fa-lock"></i>
                    <span>PDFSecure Pro</span>
                </div>
                
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <p>Your PDF has been successfully protected!</p>
                </div>
                
                <div class="pdf-preview">
                    <iframe src="<?= htmlspecialchars($_SESSION['protected_pdf'] ?? '') ?>" type="application/pdf"></iframe>
                    <div class="overlay">
                        <i class="fas fa-lock fa-3x"></i>
                        <p>Protected PDF Document</p>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <a href="?download=1" class="btn">
                        <span><i class="fas fa-download"></i> Download PDF</span>
                    </a>
                    <a href="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" class="btn btn-outline">
                        <span><i class="fas fa-lock"></i> Protect Another</span>
                    </a>
                </div>
                
                <div class="note">
                    <strong>Note:</strong> The protected PDF will be deleted from our servers after download.
                </div>
            </div>
        <?php else: ?>
            <div class="form-panel">
                <div class="form-content">
                    <div class="logo">
                        <i class="fas fa-lock"></i>
                        <span>PDFSecure Pro</span>
                    </div>
                    <h3>Custom PDF Protection</h3>
                    
                    <?php if (isset($errorMessage)): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <p><?= htmlspecialchars($errorMessage) ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" enctype="multipart/form-data" id="pdf-form">
                        <div class="form-group">
                            <label for="pdf_file">Select PDF File</label>
                            <div class="file-upload">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Drag & drop your file or click to browse</p>
                                <span class="file-name" id="file-name">No file selected</span>
                                <input type="file" name="pdf_file" id="pdf_file" accept="application/pdf" required>
                            </div>
                        </div>
                        
                        <div class="input-group">
                            <label for="user_password">Open Password (required to view)</label>
                            <input type="password" name="user_password" id="user_password" placeholder="Enter user password" required minlength="6">
                            <i class="fas fa-eye" id="toggle-user-password"></i>
                            <div class="password-strength">
                                <div class="strength-meter" id="strength-meter"></div>
                            </div>
                            <div class="strength-labels">
                                <span>Weak</span>
                                <span>Strong</span>
                            </div>
                        </div>
                        
                        <div class="input-group">
                            <label for="owner_password">Owner Password (required to edit)</label>
                            <input type="password" name="owner_password" id="owner_password" placeholder="Enter owner password" required minlength="6">
                            <i class="fas fa-eye" id="toggle-owner-password"></i>
                        </div>
                        
                        <div class="permissions-group">
                            <div class="permissions-title">Document Permissions:</div>
                            <div class="permission-item">
                                <input type="checkbox" name="allow_print" id="print">
                                <label for="print">Allow Printing</label>
                            </div>
                            <div class="permission-item">
                                <input type="checkbox" name="allow_modify" id="modify">
                                <label for="modify">Allow Modifications</label>
                            </div>
                            <div class="permission-item">
                                <input type="checkbox" name="allow_copy" id="copy">
                                <label for="copy">Allow Text Copying</label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn">
                            <span><i class="fas fa-lock"></i> Protect PDF</span>
                        </button>
                        
                        <div class="note">
                            <strong>Note:</strong> PDF permissions work only in compliant viewers like Adobe Acrobat. Browsers may ignore them.
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Display selected file name
        document.getElementById('pdf_file').addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : 'No file selected';
            document.getElementById('file-name').textContent = fileName;
        });
        
        // Toggle password visibility for user password
        document.getElementById('toggle-user-password').addEventListener('click', function() {
            const passwordInput = document.getElementById('user_password');
            const icon = this;
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Toggle password visibility for owner password
        document.getElementById('toggle-owner-password').addEventListener('click', function() {
            const passwordInput = document.getElementById('owner_password');
            const icon = this;
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Password strength meter for user password
        document.getElementById('user_password').addEventListener('input', function() {
            const password = this.value;
            const strengthMeter = document.getElementById('strength-meter');
            let strength = 0;
            
            // Length checks
            if (password.length > 0) strength += 20;
            if (password.length >= 8) strength += 20;
            if (password.length >= 12) strength += 10;
            
            // Complexity checks
            if (/[A-Z]/.test(password)) strength += 15;
            if (/[a-z]/.test(password)) strength += 15;
            if (/[0-9]/.test(password)) strength += 15;
            if (/[^A-Za-z0-9]/.test(password)) strength += 15;
            
            // Cap at 100%
            strength = Math.min(100, strength);
            
            // Update meter
            strengthMeter.style.width = strength + '%';
            
            // Change color based on strength
            if (strength < 40) {
                strengthMeter.style.backgroundColor = 'var(--error)';
            } else if (strength < 70) {
                strengthMeter.style.backgroundColor = 'var(--warning)';
            } else {
                strengthMeter.style.backgroundColor = 'var(--success)';
            }
        });
    </script>
</body>
</html>