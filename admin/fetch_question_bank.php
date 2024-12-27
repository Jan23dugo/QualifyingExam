<?php
require_once '../config/config.php';

header('Content-Type: application/json');

try {
    $search = isset($_GET['search']) ? $_GET['search'] : '';

    // Query to fetch questions and related choices
    $query = "
        SELECT q.*, 
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

    if (!empty($search)) {
        $query .= " WHERE q.question_text LIKE ?";
        $params[] = "%$search%";
        $types .= 's';
    }

    $query .= " GROUP BY q.question_id";

    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

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

    echo json_encode([
        'success' => true,
        'questions' => $questions
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 