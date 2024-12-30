<?php
require_once '../../config/config.php';
session_start();

// this file is for the delition of the exam 

if (!isset($_SESSION['loggedin']) || !isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!isset($_POST['exam_id'])) {
            throw new Exception('Exam ID is required');
        }

        $exam_id = (int)$_POST['exam_id'];
        
        // Start transaction
        $conn->begin_transaction();
        
        // First, check if the exam exists
        $check_sql = "SELECT exam_id FROM exams WHERE exam_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $exam_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('Exam not found');
        }
        
        // Delete related records first (if any exist)
        // Delete from questions table
        $delete_questions_sql = "DELETE FROM questions WHERE exam_id = ?";
        $delete_questions_stmt = $conn->prepare($delete_questions_sql);
        $delete_questions_stmt->bind_param("i", $exam_id);
        $delete_questions_stmt->execute();
        
        // Delete exam results if they exist
        $delete_results_sql = "DELETE FROM exam_results WHERE exam_id = ?";
        $delete_results_stmt = $conn->prepare($delete_results_sql);
        $delete_results_stmt->bind_param("i", $exam_id);
        $delete_results_stmt->execute();
        
        // Finally, delete the exam
        $delete_exam_sql = "DELETE FROM exams WHERE exam_id = ?";
        $delete_exam_stmt = $conn->prepare($delete_exam_sql);
        $delete_exam_stmt->bind_param("i", $exam_id);
        
        if (!$delete_exam_stmt->execute()) {
            throw new Exception('Failed to delete exam');
        }
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Exam deleted successfully'
        ]);
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        error_log("Error deleting exam: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}

$conn->close(); 