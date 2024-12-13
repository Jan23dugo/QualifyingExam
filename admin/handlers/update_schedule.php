<?php
require_once '../../config/config.php';
session_start();

if (!isset($_SESSION['loggedin']) || !isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exam_id = $_POST['exam_id'];
    $enabled = $_POST['enabled'] === 'true';
    
    try {
        if ($enabled && !empty($_POST['exam_date']) && !empty($_POST['exam_time'])) {
            $exam_date = $_POST['exam_date'];
            $exam_time = $_POST['exam_time'];
            
            $query = "UPDATE exams SET exam_date = ?, exam_time = ?, status = 'scheduled' WHERE exam_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssi", $exam_date, $exam_time, $exam_id);
        } else {
            $query = "UPDATE exams SET exam_date = NULL, exam_time = NULL, status = 'unscheduled' WHERE exam_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $exam_id);
        }
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Schedule updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
} 