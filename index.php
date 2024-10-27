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

    <!-- Include the navbar -->
    <div id="navbar">
        <?php include 'navbar.php'; ?>
    </div>

<div class="index-home">            
        <h1>Welcome to the</h1>
        <h1>Polytechnic Univeristy of the Philippines</h1>
        <h1>CCIS Qualifying Examination</h1>
        <div class="mini-sched">
            <h2>Hello, PUPian!</h2>
            <p>Please click or tap your destination.</p>
            <button class="student-btn" ><a href="registerFront.php">Student</a></button> <br>
            <button class="admin-btn"><a href="../admin/loginAdmin.php">Admin</a></button>
            <div class="terms-privacy">
                <p>By using this service, you understood and agree to the <br>PUP Online Services <a href="terms">Terms of Use</a> and <a href="#privacy">Privacy Statement</a></p>
            </div> 
        </div>
    </div>
    <footer class="footer">
        <div class="footer-content">
            <p>&copy; 2024 CCIS Qualifying Exam. All rights reserved.</p>
            <ul class="footer-links">
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#">Terms of Service</a></li>
                <li><a href="#">Contact Us</a></li>
            </ul>
            <div class="footer-social">
                <a href="#"><img src="facebook-icon.png" alt="Facebook"></a>
                <a href="#"><img src="twitter-icon.png" alt="Twitter"></a>
                <a href="#"><img src="instagram-icon.png" alt="Instagram"></a>
            </div>
        </div>
    </footer>

</body>
</html>
