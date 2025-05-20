<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>PDF Unlocker Tool</title>
</head>
<body>
  <h2>Upload Password-Protected PDF to Unlock</h2>
  <form action="unlock.php" method="post" enctype="multipart/form-data">
    <label>Select PDF File:</label><br />
    <input type="file" name="pdf_file" accept="application/pdf" required /><br /><br />

    <label>Enter Password:</label><br />
    <input type="password" name="pdf_password" required /><br /><br />

    <button type="submit">Unlock PDF</button>
  </form>
</body>
</html>
