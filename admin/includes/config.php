<?php
// Database connection and other configurations
include_once __DIR__ . '/../../config/config.php';

// Common functions
function fetchQuestionsForExam($conn, $exam_id) {
    $questions = array();
    $stmt = $conn->prepare("SELECT * FROM questions WHERE exam_id = ?");
    $stmt->bind_param("i", $exam_id);
    $stmt->execute();
    $questions_result = $stmt->get_result();

    while ($row = $questions_result->fetch_assoc()) {
        $question_id = $row['question_id'];
        if ($row['question_type'] == 'multiple_choice') {
            $row['options'] = fetchMultipleChoiceOptions($conn, $question_id);
        }
        if ($row['question_type'] == 'programming') {
            $row['test_cases'] = fetchTestCases($conn, $question_id);
        }
        $questions[] = $row;
    }
    return $questions;
}

function fetchMultipleChoiceOptions($conn, $question_id) {
    $options_stmt = $conn->prepare("SELECT * FROM multiple_choice_options WHERE question_id = ?");
    $options_stmt->bind_param("i", $question_id);
    $options_stmt->execute();
    return $options_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function fetchTestCases($conn, $question_id) {
    $test_stmt = $conn->prepare("SELECT * FROM test_cases WHERE question_id = ?");
    $test_stmt->bind_param("i", $question_id);
    $test_stmt->execute();
    return $test_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?> 