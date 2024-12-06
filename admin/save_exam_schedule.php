<?php
require_once '../config/config.php';
session_start();

if (!isset($_SESSION['loggedin']) || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

try {
    // Get form data
    $exam_name = $_POST['exam_name'];
    $student_type = $_POST['student_type'];
    $schedule_date = $_POST['schedule_date'];
    $duration = $_POST['duration'];
    $description = $_POST['description'];
    $student_year = $_POST['student_year'];

    // Insert into exams table
    $stmt = $conn->prepare("INSERT INTO exams (exam_name, description, duration, schedule_date, student_type, student_year) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssissi", $exam_name, $description, $duration, $schedule_date, $student_type, $student_year);
    
    if ($stmt->execute()) {
        $exam_id = $conn->insert_id;
        echo json_encode([
            'success' => true,
            'exam_id' => $exam_id,
            'message' => 'Schedule created successfully'
        ]);
    } else {
        throw new Exception("Error executing query");
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 