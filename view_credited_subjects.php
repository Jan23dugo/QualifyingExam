<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credited Subjects</title>
</head>
<body>
    <?php if (isset($_SESSION['success'])): ?>
        <h3><?php echo htmlspecialchars($_SESSION['success']); ?></h3>
    <?php endif; ?>

    <?php if (isset($_SESSION['credited_subjects']) && !empty($_SESSION['credited_subjects'])): ?>
        <h3>Recommended Credited Subjects</h3>
        <table border="1">
            <tr><th>Subject Code</th><th>Description</th><th>Units</th><th>Grade</th></tr>
            <?php foreach ($_SESSION['credited_subjects'] as $subject): ?>
                <tr>
                    <td><?php echo htmlspecialchars($subject['subject_code']); ?></td>
                    <td><?php echo htmlspecialchars($subject['description']); ?></td>
                    <td><?php echo htmlspecialchars($subject['units']); ?></td>
                    <td><?php echo htmlspecialchars($subject['grade']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No credited subjects available at this time.</p>
    <?php endif; ?>

    <?php if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])): ?>
        <h3>Errors:</h3>
        <?php foreach ($_SESSION['errors'] as $error): ?>
            <p><?php echo htmlspecialchars($error); ?></p>
        <?php endforeach; ?>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <!-- Optionally provide a link to go back or log out -->
    <a href="index.php">Go back to Home</a>
</body>
</html>
