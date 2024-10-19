<?php
// Include database connection
include('config/config.php');

// Get the reference ID from the URL
if (isset($_GET['refid'])) {
    $reference_id = $_GET['refid'];
} else {
    echo "Reference ID is missing. Please register first.";
    exit();
}

// Fetch credited subjects from the database using the reference_id
$sql = "SELECT subject_code, subject_description, units FROM credited_subjects WHERE reference_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $reference_id);
$stmt->execute();
$result = $stmt->get_result();

// Store the results in an array
$accreditedSubjects = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $accreditedSubjects[] = $row;
    }
}

$stmt->close();
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
    <h1>Credited Subjects</h1>

    <?php
    if (!empty($accreditedSubjects)) {
        foreach ($accreditedSubjects as $subject) {
            echo "<div class='subject-item'>";
            echo "<p><strong>Subject Code:</strong> " . htmlspecialchars($subject['subject_code']) . "</p>";
            echo "<p><strong>Description:</strong> " . htmlspecialchars($subject['subject_description']) . "</p>";
            echo "<p><strong>Units:</strong> " . htmlspecialchars($subject['units']) . "</p>";
            echo "</div><br>";
        }
    } else {
        echo "<p>No credited subjects found for this Reference ID.</p>";
    }
    ?>

    <a href="register-test.php" class="btn">Back to Registration</a>
</div>

</body>
</html>
