<?php
require_once '../../config/config.php';
header('Content-Type: application/json');

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        $data = $_POST;
    }

    // Validate required fields
    if (!isset($data['num_questions']) || !isset($data['question_types']) || !isset($data['categories'])) {
        throw new Exception('Missing required fields');
    }

    $numQuestions = intval($data['num_questions']);
    $questionTypes = $data['question_types'];
    $categories = $data['categories'];
    $programmingLanguages = $data['programming_languages'] ?? [];
    $includeTestCases = $data['include_test_cases'] ?? false;

    // Validate number of questions
    if ($numQuestions <= 0 || $numQuestions > 50) {
        throw new Exception('Invalid number of questions');
    }

    // Prepare the query to fetch random questions
    $questionTypeStr = "'" . implode("','", array_map([$conn, 'real_escape_string'], $questionTypes)) . "'";
    $categoryStr = "'" . implode("','", array_map([$conn, 'real_escape_string'], $categories)) . "'";

    // Modified SQL query to use question_bank table
    $sql = "SELECT qb.*, 
            GROUP_CONCAT(DISTINCT qbc.choice_text, ':::', qbc.is_correct) as multiple_choice_options,
            GROUP_CONCAT(DISTINCT tc.test_input, ':::', tc.expected_output, ':::', tc.is_hidden) as test_cases
            FROM question_bank qb
            LEFT JOIN question_bank_choices qbc ON qb.question_id = qbc.question_id
            LEFT JOIN question_bank_test_cases tc ON qb.question_id = tc.question_id
            WHERE qb.question_type IN ($questionTypeStr)
            AND qb.category IN ($categoryStr)";

    // Add programming language filter if specified
    if (!empty($programmingLanguages) && in_array('programming', $questionTypes)) {
        $langStr = "'" . implode("','", array_map([$conn, 'real_escape_string'], $programmingLanguages)) . "'";
        $sql .= " AND (qb.question_type != 'programming' OR qb.programming_language IN ($langStr))";
    }

    $sql .= " GROUP BY qb.question_id
              ORDER BY RAND()
              LIMIT $numQuestions";

    // Debug log
    error_log("Generated SQL Query: " . $sql);

    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Database error: " . $conn->error);
    }

    $questions = [];
    while ($row = $result->fetch_assoc()) {
        $question = [
            'question_id' => $row['question_id'],
            'question_text' => $row['question_text'],
            'question_type' => $row['question_type'],
            'category' => $row['category'],
            'points' => $row['points'] ?? 1
        ];

        // Handle multiple choice options
        if ($row['multiple_choice_options']) {
            $options = [];
            $optionPairs = explode(',', $row['multiple_choice_options']);
            foreach ($optionPairs as $pair) {
                list($text, $isCorrect) = explode(':::', $pair);
                $options[] = [
                    'choice_text' => $text,
                    'is_correct' => $isCorrect
                ];
            }
            $question['choices'] = $options;
        }

        // Handle test cases for programming questions
        if ($row['test_cases']) {
            $testCases = [];
            $casePairs = explode(',', $row['test_cases']);
            foreach ($casePairs as $pair) {
                list($input, $output, $isHidden) = explode(':::', $pair);
                $testCases[] = [
                    'test_input' => $input,
                    'expected_output' => $output,
                    'is_hidden' => $isHidden
                ];
            }
            $question['test_cases'] = $testCases;
        }

        $questions[] = $question;
    }

    echo json_encode([
        'success' => true,
        'count' => count($questions),
        'questions' => $questions
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 