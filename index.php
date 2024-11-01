<?php 
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Qualifying Examination</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="index">

    <?php include 'navbar.php'?>

    <div class="index-container">
        <h3>Hello, PUPian!</h3>
        <p>Please click or tap your destination.</p>
        <div class="index-buttons">
            <button class="index-button"><a href="registerFront.php">Student</a></button>
            <button class="index-button"><a href="loginAdmin.php">Admin</a></button>
        </div>
        <p class="terms">
            By using this service, you understand and agree to the
            <a href="#">PUP Online Services Terms of Use</a> and
            <a href="#">Privacy Statement</a>.
        </p>
    </div>
    <footer class="footer">
        <div class="footer-content">
            <p>&copy; 2024 CCIS Qualifying Exam. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>
