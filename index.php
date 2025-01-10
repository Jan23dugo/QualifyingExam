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
    <div class="navbar">
        <nav>
            <ul>
                <img src="assets/img/streamslogo.png" alt="STREAMS Logo" class="streamslogo">
                <li><a href=index.php>HOME</a></li>
                <li class="dropdown">
                    <a href="about-exam.php" class="dropbtn">ABOUT EXAM</a>
                    <div class="dropdown-content">
                        <a href="about-exam.php#schedule">Exam Schedule</a>
                        <a href="about-exam.php#requirements">Exam Requirements</a>
                        <a href="about-exam.php#documents">Required Documents</a>
                    </div>
                </li>
                <li><a href=take_exam.php>TAKE AN EXAM</a></li>
            </ul>
        </nav>
    </div>

    <div class="index-container">
        <div class="header-logo">
            <img src="assets/img/ccislogo.png" alt="PUP Logo" class="ccislogo">
            <h1>STREAMS: Student Registration, Examination, and Assessment Management System</h1>
            <img src="assets/img/puplogo.png" alt="PUP CCIS Logo" class="puplogo">
        </div>
        <h3>Polytechnic Univeristy of the Philippines</h3>
        <h3>CCIS Qualifying Examination</h3>
        <h3>Hello, PUPian!</h3>
        <p>Please click or tap your destination.</p>
        <div class="index-buttons">
            <button class="index-button"><a href="registerFront.php">Student Register</a></button>
            <button class="index-button"><a href="admin/loginAdmin.php">Admin Login</a></button>
        </div>
        <p class="terms">
            By using this service, you understand and agree to the
            <a href="#">PUP Online Services Terms of Use</a> and
            <a href="#">Privacy Statement</a>.
        </p>
    </div>
</body>
</html>
