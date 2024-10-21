<?php
// Include the database configuration file
include('../config/config.php');
// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $exam_name = $_POST['exam_name'];
    $description = $_POST['description'];
    $duration = $_POST['duration'];
    $schedule_date = $_POST['schedule_date'];  // Replace start_date and end_date with schedule_date

        // Validate the inputs
        if (empty($exam_name) || empty($duration) || empty($schedule_date)) {
            echo "Please fill in all required fields.";
            exit();
        }
        
    // Prepare an SQL statement to insert data into the exams table
    $sql = "INSERT INTO exams (exam_name, description, duration, schedule_date) 
            VALUES (?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $exam_name, $description, $duration, $schedule_date);  // Update bind parameters

    if ($stmt->execute()) {
        // Redirect to create-exam-1.php after a successful insert
        header("Location: ../admin/create-exam-1.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
