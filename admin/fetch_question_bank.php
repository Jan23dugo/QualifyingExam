<?php
require_once '../config/config.php';

header('Content-Type: application/json');

try {
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $category = isset($_GET['category']) ? $_GET['category'] : '';

    // Query to fetch questions with categories, choices, and programming details
    $query = "
        SELECT DISTINCT q.*, 
               q.category,
               qbp.programming_language,
               GROUP_CONCAT(
                   DISTINCT CONCAT(c.choice_text, ':', c.is_correct) 
                   ORDER BY c.choice_id 
                   SEPARATOR '||'
               ) as choices
        FROM question_bank q
        LEFT JOIN question_bank_choices c ON q.question_id = c.question_id
        LEFT JOIN question_bank_programming qbp ON q.question_id = qbp.question_id
        WHERE 1=1
    ";

    $params = [];
    $types = '';

    if (!empty($search)) {
        $query .= " AND q.question_text LIKE ?";
        $params[] = "%$search%";
        $types .= 's';
    }

    if (!empty($category)) {
        $query .= " AND q.category = ?";
        $params[] = $category;
        $types .= 's';
    }

    $query .= " GROUP BY q.question_id, q.category, q.question_type, q.question_text, qbp.programming_language";

    // Execute the main query
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch all questions
    $questions = [];
    while ($row = $result->fetch_assoc()) {
        // Process choices if it's a multiple choice question
        if ($row['question_type'] === 'multiple_choice' && !empty($row['choices'])) {
            $choices = explode('||', $row['choices']);
            $options = [];
            foreach ($choices as $choice) {
                list($text, $is_correct) = explode(':', $choice);
                $options[] = [
                    'choice_text' => $text,
                    'is_correct' => (bool)$is_correct
                ];
            }
            $row['choices'] = $options;
        }

        // Fetch test cases if it's a programming question
        if ($row['question_type'] === 'programming') {
            // Separate query for test cases
            $testCaseQuery = "
                SELECT test_input, expected_output, is_hidden 
                FROM question_bank_test_cases 
                WHERE question_id = ?
                ORDER BY id";
            
            $tcStmt = $conn->prepare($testCaseQuery);
            $tcStmt->bind_param('i', $row['question_id']);
            $tcStmt->execute();
            $testCasesResult = $tcStmt->get_result();
            
            $row['test_cases'] = [];
            while ($testCase = $testCasesResult->fetch_assoc()) {
                $row['test_cases'][] = [
                    'test_input' => $testCase['test_input'],
                    'expected_output' => $testCase['expected_output'],
                    'is_hidden' => (bool)$testCase['is_hidden']
                ];
            }
        }

        $questions[] = $row;
    }

    // Fetch distinct categories
    $categoryQuery = "SELECT DISTINCT category FROM question_bank WHERE category IS NOT NULL AND category != '' ORDER BY category";
    $categoryResult = $conn->query($categoryQuery);
    $categories = [];
    
    while ($categoryRow = $categoryResult->fetch_assoc()) {
        $categories[] = $categoryRow['category'];
    }

    // Return both questions and categories
    echo json_encode([
        'success' => true,
        'questions' => $questions,
        'categories' => $categories
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 