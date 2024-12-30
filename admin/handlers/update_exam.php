<?php
require_once '../../config/config.php';
session_start();

if (!isset($_SESSION['loggedin']) || !isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->begin_transaction();
        
        $exam_id = $_POST['exam_id'];
        $exam_name = trim($_POST['exam_name']);
        $description = trim($_POST['description']);
        $duration = (int)$_POST['duration'];
        $status = $_POST['status'];
        
        // Basic validation
        if (empty($exam_name)) {
            throw new Exception('Exam name is required');
        }
        
        if ($duration <= 0) {
            throw new Exception('Duration must be greater than 0');
        }
        
        // Update exam details
        $query = "UPDATE exams SET 
                    exam_name = ?, 
                    description = ?, 
                    duration = ?,
                    status = ?,
                    exam_date = ?,
                    exam_time = ?
                 WHERE exam_id = ?";
                 
        $stmt = $conn->prepare($query);
        
        // Handle NULL values for date and time when unscheduled
        $exam_date = ($status === 'scheduled' && !empty($_POST['exam_date'])) ? $_POST['exam_date'] : null;
        $exam_time = ($status === 'scheduled' && !empty($_POST['exam_time'])) ? $_POST['exam_time'] : null;
        
        $stmt->bind_param("ssisssi", 
            $exam_name, 
            $description, 
            $duration, 
            $status,
            $exam_date,
            $exam_time,
            $exam_id
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update exam: ' . $conn->error);
        }
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Exam updated successfully']);
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error updating exam: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?> 