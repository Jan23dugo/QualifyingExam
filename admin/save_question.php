<?php
// save_question.php

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
include_once __DIR__ . '/../config/config.php'; // Adjust the path as necessary

// Validate required fields
if (!isset($_POST['exam_id']) || empty($_POST['exam_id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing or empty exam_id']);
    exit;
}

$exam_id = intval($_POST['exam_id']); // Convert to integer to ensure correct data type
$section_titles = isset($_POST['section_title']) ? $_POST['section_title'] : [];
$section_descriptions = isset($_POST['section_description']) ? $_POST['section_description'] : [];
$existing_sections = isset($_POST['existing_section_id']) ? $_POST['existing_section_id'] : [];

// Begin transaction
$conn->begin_transaction();

try {
    // Fetch existing sections for the given exam_id
    $stmt = $conn->prepare("SELECT section_id, section_title, section_description FROM sections WHERE exam_id = ?");
    if (!$stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    $stmt->bind_param("i", $exam_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $sections = [];
    while ($row = $result->fetch_assoc()) {
        $sections[] = [
            'section_id' => $row['section_id'],
            'section_title' => $row['section_title'],
            'section_description' => $row['section_description'],
            'questions' => [] // Placeholder for questions
        ];
    }
    $stmt->close();

    // Fetch existing questions for each section
    foreach ($sections as &$section) {
        $stmt = $conn->prepare("SELECT question_id, question_text, question_type, points FROM questions WHERE section_id = ?");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        $stmt->bind_param("i", $section['section_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $question = [
                'question_id' => $row['question_id'],
                'question_text' => $row['question_text'],
                'question_type' => $row['question_type'],
                'points' => $row['points'],
                'options' => [],
                'test_cases' => []
            ];

           // Fetch multiple-choice options if applicable
if ($row['question_type'] == 'multiple_choice') {
    $stmt_options = $conn->prepare("SELECT option_text, is_correct FROM multiple_choice_options WHERE question_id = ?");
    if (!$stmt_options) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    $stmt_options->bind_param("i", $row['question_id']);
    $stmt_options->execute();
    $result_options = $stmt_options->get_result();

    while ($option_row = $result_options->fetch_assoc()) {
        $question['options'][] = [
            'option_text' => $option_row['option_text'],
            'is_correct' => $option_row['is_correct']
        ];
    }
    // Debug: Log retrieved options
    if (empty($question['options'])) {
        error_log("No options found for question_id: " . $row['question_id']);
    } else {
        error_log("Options found for question_id: " . $row['question_id'] . " - " . json_encode($question['options']));
    }
    $stmt_options->close();
}
            // Fetch test cases if applicable (for programming questions)
            if ($row['question_type'] == 'programming') {
                $stmt_test_cases = $conn->prepare("SELECT input, expected_output FROM test_cases WHERE question_id = ?");
                if (!$stmt_test_cases) {
                    throw new Exception("Prepare statement failed: " . $conn->error);
                }
                $stmt_test_cases->bind_param("i", $row['question_id']);
                $stmt_test_cases->execute();
                $result_test_cases = $stmt_test_cases->get_result();

                while ($test_case_row = $result_test_cases->fetch_assoc()) {
                    $question['test_cases'][] = [
                        'input' => $test_case_row['input'],
                        'expected_output' => $test_case_row['expected_output']
                    ];
                }
                $stmt_test_cases->close();
            }

            $section['questions'][] = $question;
        }
        $stmt->close();
    }

    // Process sections from the POST data
    foreach ($section_titles as $sectionId => $section_title) {
        $section_description = isset($section_descriptions[$sectionId]) ? $section_descriptions[$sectionId] : '';
        $section_id = isset($existing_sections[$sectionId]) ? intval($existing_sections[$sectionId]) : null;

        if ($section_id) {
            // Update existing section
            $stmt = $conn->prepare("UPDATE sections SET section_title = ?, section_description = ? WHERE section_id = ?");
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }
            $stmt->bind_param("ssi", $section_title, $section_description, $section_id);
            $stmt->execute();
            $stmt->close();
        } else {
            // Insert new section
            $stmt = $conn->prepare("INSERT INTO sections (exam_id, section_title, section_description) VALUES (?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }
            $stmt->bind_param("iss", $exam_id, $section_title, $section_description);
            $stmt->execute();
            $section_id = $stmt->insert_id;
            $stmt->close();
        }

        // Now process questions in this section
        if (isset($_POST['question_text'][$sectionId])) {
            $question_texts = $_POST['question_text'][$sectionId];
            $question_types = isset($_POST['question_type'][$sectionId]) ? $_POST['question_type'][$sectionId] : [];
            $points = isset($_POST['points'][$sectionId]) ? $_POST['points'][$sectionId] : [];
            $existing_questions = isset($_POST['existing_question_id'][$sectionId]) ? $_POST['existing_question_id'][$sectionId] : [];

            for ($q = 0; $q < count($question_texts); $q++) {
                $question_text = $question_texts[$q];
                if (empty($question_text)) {
                    throw new Exception('Question text cannot be empty');
                }

                $question_type = isset($question_types[$q]) ? $question_types[$q] : '';
                $point = isset($points[$q]) ? intval($points[$q]) : 0;
                $question_id = isset($existing_questions[$q]) ? intval($existing_questions[$q]) : null;

                if ($question_id) {
                    // Update existing question
                    $stmt = $conn->prepare("UPDATE questions SET question_text = ?, question_type = ?, points = ? WHERE question_id = ?");
                    if (!$stmt) {
                        throw new Exception("Prepare statement failed: " . $conn->error);
                    }
                    $stmt->bind_param("ssii", $question_text, $question_type, $point, $question_id);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    // Insert new question
                    $stmt = $conn->prepare("INSERT INTO questions (exam_id, section_id, question_text, question_type, points) VALUES (?, ?, ?, ?, ?)");
                    if (!$stmt) {
                        throw new Exception("Prepare statement failed: " . $conn->error);
                    }
                    $stmt->bind_param("iissi", $exam_id, $section_id, $question_text, $question_type, $point);
                    $stmt->execute();
                    $question_id = $stmt->insert_id;
                    $stmt->close();
                }

                // Process question types
                if ($question_type == 'multiple_choice') {
                    // Handle multiple-choice questions
                    $options = isset($_POST['multiple_choice_options'][$sectionId][$q]) ? $_POST['multiple_choice_options'][$sectionId][$q] : [];
                    $correct_answer_index = isset($_POST['correct_answer'][$sectionId][$q]) ? intval($_POST['correct_answer'][$sectionId][$q]) : null;

                    // Delete existing options before re-inserting
                    $stmt = $conn->prepare("DELETE FROM multiple_choice_options WHERE question_id = ?");
                    if (!$stmt) {
                        throw new Exception("Prepare statement failed: " . $conn->error);
                    }
                    $stmt->bind_param("i", $question_id);
                    $stmt->execute();
                    $stmt->close();

                    for ($o = 0; $o < count($options); $o++) {
                        $option_text = $options[$o];
                        $is_correct = ($o == $correct_answer_index) ? 1 : 0;
                        $stmt = $conn->prepare("INSERT INTO multiple_choice_options (question_id, option_text, is_correct) VALUES (?, ?, ?)");
                        if (!$stmt) {
                            throw new Exception("Prepare statement failed: " . $conn->error);
                        }
                        $stmt->bind_param("isi", $question_id, $option_text, $is_correct);
                        $stmt->execute();
                        $stmt->close();
                    }
                } elseif ($question_type == 'true_false') {
                    // Handle true/false questions
                    $correct_answer = isset($_POST['true_false_correct'][$sectionId][$q]) ? $_POST['true_false_correct'][$sectionId][$q] : null;
                    $stmt = $conn->prepare("UPDATE questions SET correct_answer = ? WHERE question_id = ?");
                    if (!$stmt) {
                        throw new Exception("Prepare statement failed: " . $conn->error);
                    }
                    $stmt->bind_param("si", $correct_answer, $question_id);
                    $stmt->execute();
                    $stmt->close();
                } elseif ($question_type == 'programming') {
                    // Handle programming questions
                    $programming_language = isset($_POST['programming_language'][$sectionId][$q]) ? $_POST['programming_language'][$sectionId][$q] : '';
                    $test_case_inputs = isset($_POST['test_case_input'][$sectionId][$q]) ? $_POST['test_case_input'][$sectionId][$q] : [];
                    $test_case_outputs = isset($_POST['test_case_output'][$sectionId][$q]) ? $_POST['test_case_output'][$sectionId][$q] : [];

                    // Update question with programming language
                    $stmt = $conn->prepare("UPDATE questions SET programming_language = ? WHERE question_id = ?");
                    if (!$stmt) {
                        throw new Exception("Prepare statement failed: " . $conn->error);
                    }
                    $stmt->bind_param("si", $programming_language, $question_id);
                    $stmt->execute();
                    $stmt->close();

                    // Delete existing test cases before re-inserting
                    $stmt = $conn->prepare("DELETE FROM test_cases WHERE question_id = ?");
                    if (!$stmt) {
                        throw new Exception("Prepare statement failed: " . $conn->error);
                    }
                    $stmt->bind_param("i", $question_id);
                    $stmt->execute();
                    $stmt->close();

                    // Insert test cases
                    for ($t = 0; $t < count($test_case_inputs); $t++) {
                        $test_input = $test_case_inputs[$t];
                        $test_output = isset($test_case_outputs[$t]) ? $test_case_outputs[$t] : '';
                        $stmt = $conn->prepare("INSERT INTO test_cases (question_id, input, expected_output) VALUES (?, ?, ?)");
                        if (!$stmt) {
                            throw new Exception("Prepare statement failed: " . $conn->error);
                        }
                        $stmt->bind_param("iss", $question_id, $test_input, $test_output);
                        $stmt->execute();
                        $stmt->close();
                    }
                }
            }
        }
    }
    // Commit transaction
    $conn->commit();
    echo json_encode(['success' => true, 'sections' => $sections]);
} catch (Exception $e) {
    // Rollback transaction
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
