<?php
require_once '../config/config.php';

header('Content-Type: application/json');

try {
    $count = isset($_GET['count']) ? intval($_GET['count']) : 5;
    $types = isset($_GET['types']) ? explode(',', $_GET['types']) : ['multiple_choice'];
    $category = isset($_GET['category']) ? $_GET['category'] : '';
    
    // Create placeholders for the IN clause
    $placeholders = str_repeat('?,', count($types) - 1) . '?';
    
    $query = "SELECT q.*, 
-             GROUP_CONCAT(o.option_text) as options,
-             GROUP_CONCAT(t.input_data, ':', t.expected_output) as test_cases 
-             FROM questions q 
-             LEFT JOIN multiple_choice_options o ON q.question_id = o.question_id 
-             LEFT JOIN test_cases t ON q.question_id = t.question_id
+             GROUP_CONCAT(c.choice_text) as options,
+             GROUP_CONCAT(c.is_correct) as correct_answers,
+             p.programming_language,
+             p.problem_description,
+             GROUP_CONCAT(CONCAT(t.test_input, ':', t.expected_output)) as test_cases
+             FROM question_bank q 
+             LEFT JOIN question_bank_choices c ON q.question_id = c.question_id 
+             LEFT JOIN question_bank_programming p ON q.question_id = p.question_id
+             LEFT JOIN question_bank_test_cases t ON q.question_id = t.question_id
              WHERE q.question_type IN ($placeholders)";
    
    if (!empty($category)) {
-        $query .= " AND q.category_id IN (SELECT category_id FROM categories WHERE category_name = ?)";
+        $query .= " AND q.category = ?";
    }
    
    $query .= " GROUP BY q.question_id ORDER BY RAND() LIMIT ?";
    
    $stmt = $conn->prepare($query);
    
    // Create array of parameters
    $params = array_merge($types, !empty($category) ? [$category] : [], [$count]);
    $types_str = str_repeat('s', count($types)) . (!empty($category) ? 's' : '') . 'i';
    
    $stmt->bind_param($types_str, ...$params);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $questions = [];
    
    while ($row = $result->fetch_assoc()) {
        $questions[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'questions' => $questions
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'questions' => []
    ]);
}
?> 