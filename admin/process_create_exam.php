<?php
// Include the database configuration file
include('../config/config.php');

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $exam_name = $_POST['exam_name'];
    $description = $_POST['description'];
    $duration = $_POST['duration'];
    $schedule_date = $_POST['schedule_date'];

    // Validate the inputs
    if (empty($exam_name) || empty($duration) || empty($schedule_date)) {
        echo "Please fill in all required fields.";
        exit();
    }

    // Prepare an SQL statement to insert data into the exams table
    $sql = "INSERT INTO exams (exam_name, description, duration, schedule_date) 
            VALUES (?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $exam_name, $description, $duration, $schedule_date);

    // Execute the query
    if ($stmt->execute()) {
        // Get the newly inserted exam's ID
        $exam_id = $stmt->insert_id;
        $stmt->close();

        // Redirect to test2.php (or create-exam-test.php) with the exam_id to add questions
        header("Location: ../admin/test2.php?exam_id=$exam_id");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
