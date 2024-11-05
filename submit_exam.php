<?php
session_start();
include_once 'config/config.php';

if (!isset($_SESSION['student_id']) || !isset($_POST['exam_id'])) {
    die("Invalid request");
}

$student_id = $_SESSION['student_id'];
$exam_id = $_POST['exam_id'];
$answers = $_POST['answers'] ?? [];

$conn->begin_transaction();

try {
    // Get result_id
    $stmt = $conn->prepare("
        SELECT result_id, total_points 
        FROM exam_results 
        WHERE exam_id = ? AND student_id = ?
    ");
    $stmt->bind_param("ii", $exam_id, $student_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if (!$result) {
        throw new Exception("No exam result found");
    }
    
    $result_id = $result['result_id'];
    $total_score = 0;
    
    // Process each answer
    foreach ($answers as $question_id => $answer) {
        // Get question details
        $q_stmt = $conn->prepare("
            SELECT question_type, points 
            FROM questions 
            WHERE question_id = ?
        ");
        $q_stmt->bind_param("i", $question_id);
        $q_stmt->execute();
        $question = $q_stmt->get_result()->fetch_assoc();
        
        $is_correct = false;
        $points_earned = 0;
        
        if ($question['question_type'] === 'multiple_choice') {
            // Check if answer is correct for multiple choice
            $check_stmt = $conn->prepare("
                SELECT is_correct 
                FROM multiple_choice_options 
                WHERE option_id = ? AND question_id = ?
            ");
            $check_stmt->bind_param("ii", $answer, $question_id);
            $check_stmt->execute();
            $option_result = $check_stmt->get_result()->fetch_assoc();
            
            if ($option_result && $option_result['is_correct']) {
                $is_correct = true;
                $points_earned = $question['points'];
                $total_score += $points_earned;
            }
        }
        
        // Save student's answer
        $save_stmt = $conn->prepare("
            INSERT INTO student_answers 
            (result_id, question_id, student_answer, is_correct, points_earned) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $save_stmt->bind_param("iisii", $result_id, $question_id, $answer, $is_correct, $points_earned);
        $save_stmt->execute();
    }
    
    // Update exam result
    $update_stmt = $conn->prepare("
        UPDATE exam_results 
        SET status = 'Completed',
            score = ?,
            end_time = CURRENT_TIMESTAMP,
            completion_time = TIMEDIFF(CURRENT_TIMESTAMP, start_time)
        WHERE result_id = ?
    ");
    $update_stmt->bind_param("ii", $total_score, $result_id);
    $update_stmt->execute();
    
    $conn->commit();
    
    // Redirect to results page
    header("Location: exam_complete.php?exam_id=" . $exam_id);
    exit();
    
} catch (Exception $e) {
    $conn->rollback();
    die("Error submitting exam: " . $e->getMessage());
}
?> 