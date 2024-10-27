<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload TOR</title>
</head>
<body>
    <form action="process_tor.php" method="post" enctype="multipart/form-data">
        <input type="file" name="tor_file" accept="image/*,application/pdf" required>
        <input type="submit" value="Upload and Process">
    </form>
</body>
</html>