<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ineligible for Qualifying Exam</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include('navbar.php'); ?>

    <section class="ineligible-section">
        <h1>Ineligible for Qualifying Exam</h1>
        <?php
        if (isset($_SESSION['ineligible_reason'])) {
            echo "<p>" . htmlspecialchars($_SESSION['ineligible_reason']) . "</p>";
            unset($_SESSION['ineligible_reason']); // Clear the session variable
        } else {
            echo "<p>You are not eligible for the qualifying exam at this time.</p>";
        }
        ?>
        <p>If you believe this is an error, please contact the admissions office.</p>
        <a href="index.php" class="button">Return to Home</a>
    </section>
</body>
</html>