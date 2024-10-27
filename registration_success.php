<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful</title>
</head>
<body>
    <?php if (isset($_SESSION['success']) && isset($_SESSION['student_id'])): ?>
        <h3>Registration Successful</h3>
        <p><?php echo htmlspecialchars($_SESSION['success']); ?></p>

        <!-- Provide a button to view credited subjects -->
        <form method="post" action="credit_subjects.php">
            <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($_SESSION['student_id']); ?>">
            <button type="submit">View Recommended Credited Subjects</button>
        </form>
    <?php else: ?>
        <h3>No Registration Data Available</h3>
        <p>It looks like there was an issue with your registration.</p>
    <?php endif; ?>

    <!-- Optionally provide a link to go back or log out -->
    <a href="index.php">Go back to Home</a>
</body>
</html>
