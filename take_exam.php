<?php
// Include database configuration file
include('config/config.php');

// Initialize variables for error messages or success message
$errors = [];
$student_name = "";
$exam_assigned = "";

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the reference number from the form input
    $reference_number = mysqli_real_escape_string($conn, $_POST['reference_number']);

    // Query the database to check if the reference number exists
    $query = "SELECT first_name, last_name, exam_assigned FROM students WHERE reference_id = '$reference_number'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        // Fetch student data
        $row = mysqli_fetch_assoc($result);
        $student_name = $row['first_name'] . ' ' . $row['last_name'];
        $exam_assigned = $row['exam_assigned']; // Assume you have an 'exam_assigned' column in the students table

    } else {
        // Error if the reference number is not found
        $errors[] = "No student found with the provided reference number.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Exam</title>
</head>
<body>
    <h2>Exam</h2>
    <p>Input your reference number that you have received in your email address to proceed to examination</p>
    
    <!-- Form for reference number input -->
    <form method="POST" action="">
        <label for="reference_number">Input Reference Number</label>
        <input type="text" id="reference_number" name="reference_number" required>
        <button type="submit">Search</button>
    </form>

    <?php if (!empty($student_name)) { ?>
        <!-- If student is found, display their name and assigned exam -->
        <h3>Results</h3>
        <p>Student's Name: <?php echo $student_name; ?></p>
        <p>Exam Assigned: <?php echo $exam_assigned; ?></p>
        <form method="POST" action="start_exam.php">
            <button type="submit">Take Exam</button>
        </form>
    <?php } elseif (!empty($errors)) { ?>
        <!-- Display errors if there are any -->
        <p style="color: red;"><?php echo implode('<br>', $errors); ?></p>
    <?php } ?>
</body>
</html>
