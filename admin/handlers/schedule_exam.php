<?php
require_once '../../config/config.php';
session_start();

if (!isset($_SESSION['loggedin']) || !isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $exam_id = $_POST['exam_id'];
        
        if (!empty($_POST['schedule_date']) && !empty($_POST['schedule_time'])) {
            $schedule_date = $_POST['schedule_date'];
            $schedule_time = $_POST['schedule_time'];
            $schedule_datetime = $schedule_date . ' ' . $schedule_time;
            
            $query = "UPDATE exams SET schedule_date = ?, status = 'scheduled' WHERE exam_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("si", $schedule_datetime, $exam_id);
        } else {
            $query = "UPDATE exams SET schedule_date = '1000-01-01 00:00:00', status = 'unscheduled' WHERE exam_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $exam_id);
        }
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Exam scheduled successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
} 