<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="regcon">
    <div id="navbar">
        <?php include 'navbar.php'; ?>
    </div>
    <div class="confirm-container">
        <header class="header">
            <img src="puplogo.png" class="puplogo" alt="School Logo">
            <h1>STREAM Student Registration and Requirements Submission</h1>
        </header>
        <div class="success-message">
            <h2>Your registration has been submitted successfully!</h2>
            <p>Wait for an Email from us to know if you are qualified to take the exam or not.<br>It may take 4-7 days.</p>
            <a href="resend_email.php" class="email-help">Did Not Receive Email?</a>
        </div>
    </div>

    <div class="course-buttons">
        <!-- Pass Reference ID to the accredited_subjects.php page via URL parameter -->
        <a href="accredited_subjects.php?refid=<?php echo isset($_GET['refid']) ? urlencode($_GET['refid']) : '#'; ?>" class="course-btn">View Recommended Credited Subjects</a><br>
    </div>

    <script>
        function showRecommendedCourses() {
            document.getElementById('recommended-courses').style.display = 'block';
            document.getElementById('accreditation-courses').style.display = 'none';
        }

        function showAccreditationCourses() {
            document.getElementById('accreditation-courses').style.display = 'block';
            document.getElementById('recommended-courses').style.display = 'none';
        }
    </script>

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
