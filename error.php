<?php
session_start();

// Check if the error message is set in the session
if (isset($_SESSION['error'])) {
    $errorMessage = $_SESSION['error'];
} else {
    header('Location: index.php'); // Redirect to the main page if accessed without registration
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Error</title>
</head>
<body>
    <h1>Registration Failed</h1>

    <!-- Display a specific message for ineligible students -->
    <?php if ($errorMessage === 'You are not eligible based on your grades.'): ?>
        <p>Unfortunately, you are not qualified based on your grades. Please try again or contact support for more details.</p>
    <?php else: ?>
        <!-- Display other types of errors, such as system errors -->
        <p><?php echo htmlspecialchars($errorMessage); ?></p>
    <?php endif; ?>

    <a href="index.php">Go to Home</a>
</body>
</html>
