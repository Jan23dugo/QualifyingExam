<?php
require_once '../../config/config.php';
header('Content-Type: application/json');

try {
    // Validate input parameters
    if (!isset($_POST['num_questions']) || !isset($_POST['question_types'])) {
        throw new Exception('Missing required parameters');
    }

    $numQuestions = intval($_POST['num_questions']);
    $questionTypes = json_decode($_POST['question_types'], true);
    $categories = isset($_POST['categories']) ? json_decode($_POST['categories'], true) : [];
    $examId = isset($_POST['exam_id']) ? intval($_POST['exam_id']) : null;

    // Validate decoded parameters
    if ($numQuestions <= 0) {
        throw new Exception('Number of questions must be greater than 0');
    }
    if (empty($questionTypes)) {
        throw new Exception('At least one question type must be selected');
    }

    // Build the query to randomly select questions
    $sql = "SELECT * FROM question_bank WHERE question_type IN (";
    $sql .= str_repeat('?,', count($questionTypes) - 1) . '?)';
    
    // Add category filter if categories are selected
    if (!empty($categories)) {
        $sql .= " AND category IN (" . str_repeat('?,', count($categories) - 1) . "?)";
    }
    
    // Add random order and limit
    $sql .= " ORDER BY RAND() LIMIT ?";

    // Prepare parameters array
    $params = array_merge($questionTypes, $categories, [$numQuestions]);
    $types = str_repeat('s', count($questionTypes)) . 
             str_repeat('s', count($categories)) . 
             'i';

    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $selectedQuestions = [];
    while ($row = $result->fetch_assoc()) {
        $questionId = $row['question_id'];
        $question = $row;

        // Fetch choices for multiple choice questions
        if ($row['question_type'] === 'multiple_choice') {
            $choicesSql = "SELECT * FROM question_bank_choices WHERE question_id = ?";
            $choicesStmt = $conn->prepare($choicesSql);
            $choicesStmt->bind_param('i', $questionId);
            $choicesStmt->execute();
            $choicesResult = $choicesStmt->get_result();
            $question['choices'] = $choicesResult->fetch_all(MYSQLI_ASSOC);
        }

        // Fetch test cases for programming questions
        if ($row['question_type'] === 'programming') {
            $testCasesSql = "SELECT * FROM question_bank_test_cases WHERE question_id = ?";
            $testCasesStmt = $conn->prepare($testCasesSql);
            $testCasesStmt->bind_param('i', $questionId);
            $testCasesStmt->execute();
            $testCasesResult = $testCasesStmt->get_result();
            $question['test_cases'] = $testCasesResult->fetch_all(MYSQLI_ASSOC);
        }

        $selectedQuestions[] = $question;
    }

    // Check if we found enough questions
    if (count($selectedQuestions) < $numQuestions) {
        throw new Exception('Not enough questions available matching your criteria');
    }

    // Return the selected questions
    echo json_encode([
        'success' => true,
        'questions' => $selectedQuestions,
        'message' => sprintf('Successfully selected %d questions', count($selectedQuestions))
    ]);

} catch (Exception $e) {
    error_log("Error in auto_generate_questions.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}