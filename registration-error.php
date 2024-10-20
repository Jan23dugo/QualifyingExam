<?php
// Start session
session_start();

// Check if there are any errors stored in the session
$errors = isset($_SESSION['registration_errors']) ? $_SESSION['registration_errors'] : [];

// Unset errors after loading to prevent them from persisting on refresh
unset($_SESSION['registration_errors']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Error</title>
    <link rel="stylesheet" href="assets/css/style.css"> 
</head>
<body>
    <section class="error-section">
        <div class="error-container">
            <h1>Registration Error</h1>
            <?php if (!empty($errors)): ?>
                <div class="errors">
                    <h3>We encountered the following errors:</h3>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php else: ?>
                <p>An unknown error occurred. Please try again later.</p>
            <?php endif; ?>

            <div class="actions">
                <a href="register-test.php" class="btn">Back to Registration</a>
            </div>
        </div>
    </section>
</body>
</html>
