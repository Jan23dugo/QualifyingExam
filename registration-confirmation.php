<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <div class="success-message">
        <h1>YOU ARE NOW REGISTERED!</h1>
    
        <p>A <strong>Reference ID</strong> has been sent to your Email Address.<br>
        Wait and check your email!</p>
        <p>Your Reference ID: <strong><?php echo isset($_GET['refid']) ? $_GET['refid'] : 'No Reference ID'; ?></strong></p>
        <a href="resend_email.php" class="btn">Did Not Receive Email?</a>
    </div>

    <div class="course-buttons">
        <a href="accredited_subjects.php" class="course-btn">View Recommended Credited Subjects</a><br>
    </div>

</body>
</html>
