<?php
require_once '../config/config.php';

header('Content-Type: application/json');

try {
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $category = isset($_GET['category']) ? $_GET['category'] : '';

    $query = "SELECT * FROM question_bank q";

    $whereConditions = [];
    $params = [];
    $types = '';

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
        $query .= " WHERE " . implode(" AND ", $whereConditions);
    }

    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $questions = [];
    while ($row = $result->fetch_assoc()) {
        if ($row['question_type'] === 'multiple_choice') {
            $choices_query = "SELECT choice_text, is_correct FROM question_bank_choices 
                            WHERE question_id = ?";
            $choices_stmt = $conn->prepare($choices_query);
            $choices_stmt->bind_param('i', $row['question_id']);
            $choices_stmt->execute();
            $choices_result = $choices_stmt->get_result();
            $row['choices'] = $choices_result->fetch_all(MYSQLI_ASSOC);
        }

        if ($row['question_type'] === 'programming') {
            $prog_query = "SELECT * FROM question_bank_programming 
                          WHERE question_id = ?";
            $prog_stmt = $conn->prepare($prog_query);
            $prog_stmt->bind_param('i', $row['question_id']);
            $prog_stmt->execute();
            $prog_result = $prog_stmt->get_result();
            $prog_details = $prog_result->fetch_assoc();
            if ($prog_details) {
                $row = array_merge($row, $prog_details);
            }
            
            $test_query = "SELECT test_input, expected_output FROM question_bank_test_cases 
                          WHERE question_id = ?";
            $test_stmt = $conn->prepare($test_query);
            $test_stmt->bind_param('i', $row['question_id']);
            $test_stmt->execute();
            $test_result = $test_stmt->get_result();
            $row['test_cases'] = $test_result->fetch_all(MYSQLI_ASSOC);
        }

        $questions[] = $row;
    }

    echo json_encode(['success' => true, 'questions' => $questions]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage(),
        'questions' => []
    ]);
}
?> 