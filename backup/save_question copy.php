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

// Begin transaction
$conn->begin_transaction();

try {
    foreach ($section_titles as $sectionId => $section_title) {
        $section_description = isset($section_descriptions[$sectionId]) ? $section_descriptions[$sectionId] : '';

        // Insert section into database
        $stmt = $conn->prepare("INSERT INTO sections (exam_id, section_title, section_description) VALUES (?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        $stmt->bind_param("iss", $exam_id, $section_title, $section_description);
        $stmt->execute();
        $section_id = $stmt->insert_id;
        $stmt->close();

        // Now process questions in this section
        if (isset($_POST['question_text'][$sectionId])) {
            $question_texts = $_POST['question_text'][$sectionId];
            $question_types = isset($_POST['question_type'][$sectionId]) ? $_POST['question_type'][$sectionId] : [];
            $points = isset($_POST['points'][$sectionId]) ? $_POST['points'][$sectionId] : [];

            for ($q = 0; $q < count($question_texts); $q++) {
                $question_text = $question_texts[$q];
                if (empty($question_text)) {
                    throw new Exception('Question text cannot be empty');
                }

                $question_type = isset($question_types[$q]) ? $question_types[$q] : '';
                $point = isset($points[$q]) ? intval($points[$q]) : 0;

                // Insert question into database
                $stmt = $conn->prepare("INSERT INTO questions (exam_id, section_id, question_text, question_type, points) VALUES (?, ?, ?, ?, ?)");
                if (!$stmt) {
                    throw new Exception("Prepare statement failed: " . $conn->error);
                }
                $stmt->bind_param("iissi", $exam_id, $section_id, $question_text, $question_type, $point);
                $stmt->execute();
                $question_id = $stmt->insert_id;
                $stmt->close();

                // Process question types
                if ($question_type == 'multiple_choice') {
                    // Handle multiple-choice questions
                    $options = isset($_POST['multiple_choice_options'][$sectionId][$q]) ? $_POST['multiple_choice_options'][$sectionId][$q] : [];
                    $correct_answer_index = isset($_POST['correct_answer'][$sectionId][$q]) ? intval($_POST['correct_answer'][$sectionId][$q]) : null;

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
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Rollback transaction
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
