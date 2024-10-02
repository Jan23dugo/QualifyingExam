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
<body>

    <!-- Include the navbar -->
    <div id="navbar">
        <?php include 'navbar.php'; ?>
    </div>

    <header class="exam-header">
        <div class="header-content">
            <h1>College of Computer Information and Sciences</h1>
            <h2>Qualifying Examination</h2>
            <a href="register.php" class="register-button">Register</a>
        </div>
    </header>

    <section class="exam-schedule">
        <div class="schedule-box">
            <h3>Exam Schedule</h3>
            <p>Shiftees/Transferees</p>
            <p><strong>Date:</strong> September 17, 2025</p>
            <p><strong>Time:</strong> 00:00 AM - 00:00 PM</p>
            <p><strong>Place:</strong> CCIS Laboratory Room (5th Floor, South Wing)</p>
        </div>
    </section>

</body>
</html>