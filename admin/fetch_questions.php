<?php
header('Content-Type: application/json');
include_once __DIR__ . '/../config/config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page = $_GET['page'] ?? 1;
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$limit = 10;
$offset = ($page - 1) * $limit;

try {
    // Simplified query to test basic functionality
    $query = "SELECT * FROM question_bank";
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $questions = [];
    while ($row = $result->fetch_assoc()) {
        // Basic question data
        $question = [
            'question_id' => $row['question_id'],
            'question_text' => $row['question_text'],
            'question_type' => $row['question_type'],
            'category' => $row['category']
        ];
        
        // If it's a multiple choice question, get the choices
        if ($row['question_type'] === 'multiple_choice') {
            $choicesQuery = "SELECT * FROM question_bank_choices WHERE question_id = ?";
            $stmt = $conn->prepare($choicesQuery);
            $stmt->bind_param("i", $row['question_id']);
            $stmt->execute();
            $choicesResult = $stmt->get_result();
            $choices = [];
            while ($choice = $choicesResult->fetch_assoc()) {
                $choices[] = [
                    'text' => $choice['choice_text'],
                    'is_correct' => (bool)$choice['is_correct']
                ];
            }
            $question['choices'] = $choices;
        }
        
        // If it's a true/false question
        if ($row['question_type'] === 'true_false') {
            $question['correct_answer'] = $row['correct_answer'];
        }
        
        // If it's a programming question
        if ($row['question_type'] === 'programming') {
            $progQuery = "SELECT * FROM question_bank_programming WHERE question_id = ?";
            $stmt = $conn->prepare($progQuery);
            $stmt->bind_param("i", $row['question_id']);
            $stmt->execute();
            $progResult = $stmt->get_result();
            if ($progDetails = $progResult->fetch_assoc()) {
                $question['programming_details'] = $progDetails;
            }
            
            // Get test cases
            $testQuery = "SELECT * FROM question_bank_test_cases WHERE question_id = ?";
            $stmt = $conn->prepare($testQuery);
            $stmt->bind_param("i", $row['question_id']);
            $stmt->execute();
            $testResult = $stmt->get_result();
            $testCases = [];
            while ($test = $testResult->fetch_assoc()) {
                $testCases[] = $test;
            }
            $question['test_cases'] = $testCases;
        }
        
        $questions[] = $question;
    }
    
    // Calculate total pages
    $totalQuestions = count($questions);
    $totalPages = ceil($totalQuestions / $limit);
    
    // Apply pagination to the results array
    $questions = array_slice($questions, $offset, $limit);
    
    echo json_encode([
        'success' => true,
        'questions' => $questions,
        'totalQuestions' => $totalQuestions,
        'totalPages' => $totalPages,
        'currentPage' => $page
    ]);

} catch (Exception $e) {
    error_log("Question Bank Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => debug_backtrace()
    ]);
}

$conn->close(); 