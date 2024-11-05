<?php
include_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['exam_id'])) {
        $exam_id = $_GET['exam_id'];
        echo json_encode([
            'success' => true,
            'sections' => fetchExamData($conn, $exam_id)
        ]);
        exit;
    }
}

try {
    $exam_id = $_POST['exam_id'] ?? null;
    if (!$exam_id) {
        throw new Exception('Exam ID is required');
    }

    // Start transaction
    $conn->begin_transaction();

    // Get existing sections for this exam
    $existing_sections = [];
    $stmt = $conn->prepare("SELECT section_id FROM exam_sections WHERE exam_id = ?");
    $stmt->bind_param("i", $exam_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $existing_sections[] = $row['section_id'];
    }

    // Process sections
    $processed_sections = [];
    if (isset($_POST['section_title'])) {
        foreach ($_POST['section_title'] as $section_index => $title) {
            $description = $_POST['section_description'][$section_index] ?? '';
            
            if (isset($_POST['section_id'][$section_index])) {
                // Update existing section
                $section_id = $_POST['section_id'][$section_index];
                $stmt = $conn->prepare("UPDATE exam_sections SET section_title = ?, section_description = ? WHERE section_id = ?");
                $stmt->bind_param("ssi", $title, $description, $section_id);
                $stmt->execute();
            } else {
                // Insert new section
                $stmt = $conn->prepare("INSERT INTO exam_sections (exam_id, section_title, section_description, section_order) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("issi", $exam_id, $title, $description, $section_index);
                $stmt->execute();
                $section_id = $conn->insert_id;
            }
            
            $processed_sections[] = $section_id;
            
            // Process questions for this section
            if (isset($_POST['question_text'][$section_index])) {
                processQuestions($conn, $exam_id, $section_id, $section_index, $_POST);
            }
        }
    }

    // Delete sections that no longer exist
    $sections_to_delete = array_diff($existing_sections, $processed_sections);
    if (!empty($sections_to_delete)) {
        $sections_to_delete_str = implode(',', $sections_to_delete);
        $conn->query("DELETE FROM exam_sections WHERE section_id IN ($sections_to_delete_str)");
    }

    // Commit transaction
    $conn->commit();

    // Return success response with updated data
    echo json_encode([
        'success' => true,
        'message' => 'Questions saved successfully',
        'sections' => fetchExamData($conn, $exam_id)
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

function processQuestions($conn, $exam_id, $section_id, $section_index, $post_data) {
    // Get existing questions for this section
    $existing_questions = [];
    $stmt = $conn->prepare("SELECT question_id FROM questions WHERE section_id = ?");
    $stmt->bind_param("i", $section_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $existing_questions[] = $row['question_id'];
    }

    $processed_questions = [];
    foreach ($post_data['question_text'][$section_index] as $question_index => $question_text) {
        $question_type = $post_data['question_type'][$section_index][$question_index];
        $points = $post_data['points'][$section_index][$question_index] ?? 1;

        if (isset($post_data['question_id'][$section_index][$question_index])) {
            // Update existing question
            $question_id = $post_data['question_id'][$section_index][$question_index];
            $stmt = $conn->prepare("UPDATE questions SET question_text = ?, question_type = ?, points = ? WHERE question_id = ?");
            $stmt->bind_param("ssii", $question_text, $question_type, $points, $question_id);
            $stmt->execute();
        } else {
            // Insert new question
            $stmt = $conn->prepare("INSERT INTO questions (section_id, exam_id, question_text, question_type, points, question_order) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iissii", $section_id, $exam_id, $question_text, $question_type, $points, $question_index);
            $stmt->execute();
            $question_id = $conn->insert_id;
        }

        $processed_questions[] = $question_id;

        // Process question options based on type
        switch ($question_type) {
            case 'multiple_choice':
                processMultipleChoiceOptions($conn, $question_id, $section_index, $question_index, $post_data);
                break;
            case 'true_false':
                processTrueFalseAnswer($conn, $question_id, $section_index, $question_index, $post_data);
                break;
            case 'programming':
                processProgrammingOptions($conn, $question_id, $section_index, $question_index, $post_data);
                break;
        }
    }

    // Delete questions that no longer exist
    $questions_to_delete = array_diff($existing_questions, $processed_questions);
    if (!empty($questions_to_delete)) {
        $questions_to_delete_str = implode(',', $questions_to_delete);
        $conn->query("DELETE FROM questions WHERE question_id IN ($questions_to_delete_str)");
    }
}

function processMultipleChoiceOptions($conn, $question_id, $section_index, $question_index, $post_data) {
    // Get existing options
    $existing_options = [];
    $stmt = $conn->prepare("SELECT option_id FROM multiple_choice_options WHERE question_id = ?");
    $stmt->bind_param("i", $question_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $existing_options[] = $row['option_id'];
    }

    $processed_options = [];
    if (isset($post_data['options'][$section_index][$question_index])) {
        foreach ($post_data['options'][$section_index][$question_index] as $option_index => $option_text) {
            $is_correct = ($post_data['correct_answer'][$section_index][$question_index] == $option_index) ? 1 : 0;

            if (isset($post_data['option_id'][$section_index][$question_index][$option_index])) {
                // Update existing option
                $option_id = $post_data['option_id'][$section_index][$question_index][$option_index];
                $stmt = $conn->prepare("UPDATE multiple_choice_options SET option_text = ?, is_correct = ? WHERE option_id = ?");
                $stmt->bind_param("sii", $option_text, $is_correct, $option_id);
                $stmt->execute();
                $processed_options[] = $option_id;
            } else {
                // Insert new option
                $stmt = $conn->prepare("INSERT INTO multiple_choice_options (question_id, option_text, is_correct, option_order) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isii", $question_id, $option_text, $is_correct, $option_index);
                $stmt->execute();
                $processed_options[] = $conn->insert_id;
            }
        }
    }

    // Delete options that no longer exist
    $options_to_delete = array_diff($existing_options, $processed_options);
    if (!empty($options_to_delete)) {
        $options_to_delete_str = implode(',', $options_to_delete);
        $conn->query("DELETE FROM multiple_choice_options WHERE option_id IN ($options_to_delete_str)");
    }
}

function processProgrammingOptions($conn, $question_id, $section_index, $question_index, $post_data) {
    // Save programming language
    if (isset($post_data['programming_language'][$section_index][$question_index])) {
        $language = $post_data['programming_language'][$section_index][$question_index];
        $stmt = $conn->prepare("INSERT INTO programming_languages (question_id, language_name) VALUES (?, ?) 
                               ON DUPLICATE KEY UPDATE language_name = ?");
        $stmt->bind_param("iss", $question_id, $language, $language);
        $stmt->execute();
    }

    // Process test cases
    $existing_test_cases = [];
    $stmt = $conn->prepare("SELECT test_case_id FROM test_cases WHERE question_id = ?");
    $stmt->bind_param("i", $question_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $existing_test_cases[] = $row['test_case_id'];
    }

    $processed_test_cases = [];
    if (isset($post_data['test_case_input'][$section_index][$question_index])) {
        foreach ($post_data['test_case_input'][$section_index][$question_index] as $case_index => $input) {
            $output = $post_data['test_case_output'][$section_index][$question_index][$case_index];

            if (isset($post_data['test_case_id'][$section_index][$question_index][$case_index])) {
                // Update existing test case
                $test_case_id = $post_data['test_case_id'][$section_index][$question_index][$case_index];
                $stmt = $conn->prepare("UPDATE test_cases SET input_data = ?, expected_output = ? WHERE test_case_id = ?");
                $stmt->bind_param("ssi", $input, $output, $test_case_id);
                $stmt->execute();
                $processed_test_cases[] = $test_case_id;
            } else {
                // Insert new test case
                $stmt = $conn->prepare("INSERT INTO test_cases (question_id, input_data, expected_output, test_case_order) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("issi", $question_id, $input, $output, $case_index);
                $stmt->execute();
                $processed_test_cases[] = $conn->insert_id;
            }
        }
    }

    // Delete test cases that no longer exist
    $test_cases_to_delete = array_diff($existing_test_cases, $processed_test_cases);
    if (!empty($test_cases_to_delete)) {
        $test_cases_to_delete_str = implode(',', $test_cases_to_delete);
        $conn->query("DELETE FROM test_cases WHERE test_case_id IN ($test_cases_to_delete_str)");
    }
}

function processTrueFalseAnswer($conn, $question_id, $section_index, $question_index, $post_data) {
    // First, check if there's an existing answer in multiple_choice_options
    $stmt = $conn->prepare("DELETE FROM multiple_choice_options WHERE question_id = ?");
    $stmt->bind_param("i", $question_id);
    $stmt->execute();

    // Insert the true/false answer
    if (isset($post_data['correct_answer'][$section_index][$question_index])) {
        $correct_answer = $post_data['correct_answer'][$section_index][$question_index];
        
        // Insert both options, marking one as correct
        $stmt = $conn->prepare("INSERT INTO multiple_choice_options (question_id, option_text, is_correct, option_order) VALUES (?, 'True', ?, 0), (?, 'False', ?, 1)");
        $is_true_correct = ($correct_answer === 'true') ? 1 : 0;
        $is_false_correct = ($correct_answer === 'false') ? 1 : 0;
        $stmt->bind_param("iiii", $question_id, $is_true_correct, $question_id, $is_false_correct);
        $stmt->execute();
    }
}

function fetchExamData($conn, $exam_id) {
    // Fetch all sections, questions, and their related data
    $sections = [];
    
    $stmt = $conn->prepare("SELECT * FROM exam_sections WHERE exam_id = ? ORDER BY section_order");
    $stmt->bind_param("i", $exam_id);
    $stmt->execute();
    $sections_result = $stmt->get_result();

    while ($section = $sections_result->fetch_assoc()) {
        $section_id = $section['section_id'];
        $section['questions'] = [];

        // Fetch questions for this section
        $stmt = $conn->prepare("SELECT * FROM questions WHERE section_id = ? ORDER BY question_order");
        $stmt->bind_param("i", $section_id);
        $stmt->execute();
        $questions_result = $stmt->get_result();

        while ($question = $questions_result->fetch_assoc()) {
            $question_id = $question['question_id'];

            // Fetch options or test cases based on question type
            if ($question['question_type'] == 'multiple_choice' || $question['question_type'] == 'true_false') {
                $stmt = $conn->prepare("SELECT * FROM multiple_choice_options WHERE question_id = ? ORDER BY option_order");
                $stmt->bind_param("i", $question_id);
                $stmt->execute();
                $options = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $question['options'] = $options;
                
                if ($question['question_type'] == 'true_false') {
                    // Find the correct answer
                    foreach ($options as $option) {
                        if ($option['is_correct']) {
                            $question['correct_answer'] = strtolower($option['option_text']);
                            break;
                        }
                    }
                }
            } elseif ($question['question_type'] == 'programming') {
                // Fetch programming language
                $stmt = $conn->prepare("SELECT * FROM programming_languages WHERE question_id = ?");
                $stmt->bind_param("i", $question_id);
                $stmt->execute();
                $question['programming_language'] = $stmt->get_result()->fetch_assoc();

                // Fetch test cases
                $stmt = $conn->prepare("SELECT * FROM test_cases WHERE question_id = ? ORDER BY test_case_order");
                $stmt->bind_param("i", $question_id);
                $stmt->execute();
                $question['test_cases'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            }

            $section['questions'][] = $question;
        }

        $sections[] = $section;
    }

    return $sections;
}
?>
