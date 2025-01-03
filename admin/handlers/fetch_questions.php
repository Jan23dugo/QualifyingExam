<?php
require_once '../../config/config.php';
header('Content-Type: application/json');

try {
    if (!isset($_GET['category'])) {
        throw new Exception('Category is required');
    }

    $category = $_GET['category'];
    
    // Modified query to properly fetch choices and correct answers
    $sql = "SELECT q.*, 
            GROUP_CONCAT(
                CONCAT(qc.choice_text, ':', COALESCE(qc.is_correct, 0))
                ORDER BY qc.choice_id
                SEPARATOR '|'
            ) as choices,
            q.correct_answer as correct_answer  -- Get correct_answer directly from question_bank
            FROM question_bank q
            LEFT JOIN question_bank_choices qc ON q.question_id = qc.question_id
            WHERE q.category = ?
            GROUP BY q.question_id
            ORDER BY q.question_id DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $category);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $questions = [];
    while ($row = $result->fetch_assoc()) {
        // Add debug logging
        error_log("Processing question ID: " . $row['question_id'] . ", Type: " . $row['question_type']);
        error_log("Choices data: " . print_r($row['choices'], true));
        error_log("Correct answer: " . $row['correct_answer']);

        $question = [
            'question_id' => $row['question_id'],
            'question_text' => $row['question_text'],
            'question_type' => $row['question_type'],
            'choices' => [],
            'test_cases' => [],
            'correct_answer' => $row['correct_answer']  // Set correct_answer directly from the row
        ];
        
        // Process choices
        if ($row['choices']) {
            $choices = explode('|', $row['choices']);
            foreach ($choices as $choice) {
                if (strpos($choice, ':') !== false) {
                    list($text, $isCorrect) = explode(':', $choice);
                    $question['choices'][] = [
                        'choice_text' => $text,
                        'is_correct' => (bool)$isCorrect
                    ];
                }
            }
        }

        // Add debug logging
        error_log("Question data: " . print_r($question, true));
        
        if ($row['question_type'] === 'programming') {
            // Fetch test cases
            $testCasesSql = "SELECT test_input, expected_output, is_hidden 
                             FROM question_bank_test_cases 
                             WHERE question_id = ?";
            $testStmt = $conn->prepare($testCasesSql);
            $testStmt->bind_param('i', $row['question_id']);
            $testStmt->execute();
            $testResult = $testStmt->get_result();
            
            while ($testCase = $testResult->fetch_assoc()) {
                $question['test_cases'][] = $testCase;
            }
        }
        
        $questions[] = $question;
    }
    
    echo json_encode([
        'success' => true,
        'questions' => $questions
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 