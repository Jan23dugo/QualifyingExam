<?php
include_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exam_id = $_POST['exam_id'] ?? null;
    
    if (!$exam_id) {
        echo json_encode(['success' => false, 'error' => 'Exam ID is required']);
        exit;
    }
    
    try {
        // Convert checkbox values to integers
        $randomize_questions = isset($_POST['randomize_questions']) ? 1 : 0;
        $randomize_options = isset($_POST['randomize_options']) ? 1 : 0;
        $allow_view_after = isset($_POST['allow_view_after']) ? 1 : 0;
        $show_results_immediately = isset($_POST['show_results_immediately']) ? 1 : 0;
        $allow_retake = isset($_POST['allow_retake']) ? 1 : 0;
        
        // Get numeric values
        $time_limit = $_POST['time_limit'] ? intval($_POST['time_limit']) : null;
        $passing_score = $_POST['passing_score'] ? intval($_POST['passing_score']) : null;
        $max_attempts = $_POST['max_attempts'] ? intval($_POST['max_attempts']) : 1;
        
        $stmt = $conn->prepare("
            UPDATE exam_settings SET 
            randomize_questions = ?,
            randomize_options = ?,
            allow_view_after = ?,
            time_limit = ?,
            passing_score = ?,
            show_results_immediately = ?,
            allow_retake = ?,
            max_attempts = ?
            WHERE exam_id = ?
        ");
        
        $stmt->bind_param("iiiiiiiii",
            $randomize_questions,
            $randomize_options,
            $allow_view_after,
            $time_limit,
            $passing_score,
            $show_results_immediately,
            $allow_retake,
            $max_attempts,
            $exam_id
        );
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception("Failed to save settings");
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>