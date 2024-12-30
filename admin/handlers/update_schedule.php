<?php
require_once '../../config/config.php';
session_start();

if (!isset($_SESSION['loggedin']) || !isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exam_id = $_POST['exam_id'];
    $enabled = filter_var($_POST['enabled'], FILTER_VALIDATE_BOOLEAN);
    
    try {
        $conn->begin_transaction();
        
        if ($enabled && !empty($_POST['exam_date']) && !empty($_POST['exam_time'])) {
            // Validate date and time format
            $exam_date = date('Y-m-d', strtotime($_POST['exam_date']));
            $exam_time = date('H:i:s', strtotime($_POST['exam_time']));
            
            if ($exam_date === false || $exam_time === false) {
                throw new Exception('Invalid date or time format');
            }
            
            $query = "UPDATE exams SET exam_date = ?, exam_time = ?, status = 'scheduled' WHERE exam_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssi", $exam_date, $exam_time, $exam_id);
        } else {
            // If schedule is disabled, clear the schedule
            $query = "UPDATE exams SET exam_date = NULL, exam_time = NULL, status = 'unscheduled' WHERE exam_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $exam_id);
        }
        
        if ($stmt->execute()) {
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Schedule updated successfully']);
        } else {
            throw new Exception('Database error: ' . $conn->error);
        }
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Schedule update error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
} 