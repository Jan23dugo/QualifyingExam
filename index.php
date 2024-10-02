<?php 
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Qualifying Examination</title>
</head>
<body>

    <!-- Include the navbar -->
    <div id="navbar">
        <?php include 'navbar.php'; ?>
    </div>

    <header>
        <h1>College of Computer Information and Sciences</h1>
        <h2>Qualifying Examination</h2>
        <!-- Link to the registration page or form processing in PHP -->
        <a href="register.php" style="background-color: #007bff; color: white; padding: 10px; text-decoration: none;">Register</a>
    </header>

    <section>
        <div>
            <h3>Exam Schedule</h3>
            <p>Shiftees/Transferees</p>
            <p><strong>Date:</strong> 
                <?php 
                echo "September 17, 2025"; 
                ?>
            </p>
            <p><strong>Time:</strong> 
                <?php 
                echo "00:00 AM - 00:00 PM"; 
                ?>
            </p>
            <p><strong>Place:</strong> 
                <?php 
                echo "CCIS Laboratory Room (5th Floor, South Wing)"; 
                ?>
            </p>
        </div>
    </section>

</body>
</html>
