<?php
require_once '../config/config.php';

header('Content-Type: application/json');

try {
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $category = isset($_GET['category']) ? $_GET['category'] : '';

    // Query to fetch questions with categories and related choices
    $query = "
        SELECT q.*, 
               q.category,
               GROUP_CONCAT(
                   CONCAT(c.choice_text, ':', c.is_correct) 
                   ORDER BY c.choice_id 
                   SEPARATOR '||'
               ) as choices
        FROM question_bank q
        LEFT JOIN question_bank_choices c ON q.question_id = c.question_id
    ";

    $params = [];
    $types = '';
    $whereConditions = [];

    if (!empty($search)) {
        $whereConditions[] = "q.question_text LIKE ?";
        $params[] = "%$search%";
        $types .= 's';
    }

    if (!empty($category)) {
        $whereConditions[] = "q.category = ?";
        $params[] = $category;
        $types .= 's';
    }

    if (!empty($whereConditions)) {
        $query .= " WHERE " . implode(' AND ', $whereConditions);
    }

    $query .= " GROUP BY q.question_id";

    // Prepare and execute the main query
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