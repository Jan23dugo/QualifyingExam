<?php
// Include the database connection
include_once('../config/config.php');

if (isset($_GET['exam_id'])) {
    $exam_id = $_GET['exam_id'];

    // Delete the exam from the database
    $sql = "DELETE FROM exams WHERE exam_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $exam_id);
    
    if ($stmt->execute()) {
        echo "Exam deleted successfully.";
    } else {
        echo "Error deleting exam: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
header("Location: ../admin/create-exam.php");
exit();
