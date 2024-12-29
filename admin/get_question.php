<?php
require_once '../config/config.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Question ID is required');
    }

    $question_id = intval($_GET['id']);
    
    // Get basic question info and join with programming details
    $sql = "SELECT q.*, qbp.programming_language 
            FROM question_bank q 
            LEFT JOIN question_bank_programming qbp ON q.question_id = qbp.question_id 
            WHERE q.question_id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $question_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Question not found');
    }
    
    $question = $result->fetch_assoc();
    
    // Get additional data based on question type
    switch($question['question_type']) {
        case 'multiple_choice':
            $sql = "SELECT choice_id, choice_text, is_correct 
                   FROM question_bank_choices 
                   WHERE question_id = ? 
                   ORDER BY choice_id";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $question_id);
            $stmt->execute();
            $choices_result = $stmt->get_result();
            
            $question['choices'] = [];
            while($choice = $choices_result->fetch_assoc()) {
                $question['choices'][] = [
                    'text' => $choice['choice_text'],
                    'is_correct' => (bool)$choice['is_correct'],
                    'id' => $choice['choice_id']
                ];
            }
            break;

        case 'true_false':
            // Get true/false answer
            $sql = "SELECT choice_text, is_correct 
                   FROM question_bank_choices 
                   WHERE question_id = ? AND is_correct = 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $question_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $question['correct_answer'] = $row['choice_text'];
            }
            break;

        case 'programming':
            // Get test cases
            $sql = "SELECT test_input, expected_output, is_hidden, description 
                   FROM question_bank_test_cases 
                   WHERE question_id = ? 
                   ORDER BY id";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $question_id);
            $stmt->execute();
            $test_cases_result = $stmt->get_result();
            
            $question['test_cases'] = [];
            while($test_case = $test_cases_result->fetch_assoc()) {
                $question['test_cases'][] = [
                    'test_input' => $test_case['test_input'],
                    'expected_output' => $test_case['expected_output'],
                    'is_hidden' => (bool)$test_case['is_hidden'],
                    'description' => $test_case['description']
                ];
            }
            break;
    }
    
    echo json_encode(['success' => true, 'question' => $question]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?> 