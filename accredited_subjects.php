<?php
// accredited_subjects.php

// Start session to access session variables
session_start();

// Include header or any other common files if needed
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accredited Subjects</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <div class="accredited-subjects">
        <h1>Recommended Credited Subjects</h1>

        <?php
        // Check if there are accredited subjects stored in the session
        if (isset($_SESSION['accredited_subjects']) && !empty($_SESSION['accredited_subjects'])) {
            // Display the accredited subjects
            foreach ($_SESSION['accredited_subjects'] as $subject) {
                echo "<div class='subject-item'>";
                echo "<p><strong>Subject Code:</strong> " . htmlspecialchars($subject['subject_code']) . "</p>";
                echo "<p><strong>Description:</strong> " . htmlspecialchars($subject['description']) . "</p>";
                echo "<p><strong>Units:</strong> " . htmlspecialchars($subject['units']) . "</p>";
                echo "</div><br>";
            }
        } else {
            // Message to display if there are no accredited subjects
            echo "<p>No accredited subjects found. Please check back later or ensure your registration was completed successfully.</p>";
        }
        ?>

        <a href="register.php" class="btn">Back to Registration</a>
    </div>

</body>
</html>
