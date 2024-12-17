<?php
include_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

// At the start of the file, add:
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to sanitize input but preserve HTML
function sanitize_input($data) {
    if (empty($data)) return '';
    return $data;
}

// Function to log errors
function logError($error, $context = '') {
    $logFile = __DIR__ . '/logs/error.log';
    $timestamp = date('Y-m-d H:i:s');
    $message = "[$timestamp] $context: $error\n";
    error_log($message, 3, $logFile);
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get and decode JSON data
        $json_data = file_get_contents('php://input');
        error_log("Received JSON data: " . $json_data);
        $data = json_decode($json_data, true);
        error_log("Decoded data: " . print_r($data, true));
        
        // Log received data for debugging
        logError("Received data: " . print_r($data, true), 'debug');

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON data: ' . json_last_error_msg());
        }

        // Validate exam_id
        if (!isset($data['exam_id']) || empty($data['exam_id'])) {
            throw new Exception('Exam ID is required');
        }

        $exam_id = intval($data['exam_id']);

        // Start transaction
        $conn->begin_transaction();

        try {
            // Get all existing sections for this exam
            $existing_sections = [];
            $stmt = $conn->prepare("SELECT section_id FROM sections WHERE exam_id = ?");
            $stmt->bind_param("i", $exam_id);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $existing_sections[] = $row['section_id'];
            }

            // Keep track of processed sections
            $processed_sections = [];

            // Process each section
            foreach ($data['sections'] as $section) {
                // Ensure exam_id is set for the section
                $section['exam_id'] = $exam_id;
                
                if (empty($section['exam_id'])) {
                    throw new Exception('Section exam_id is required');
                }

                $section_id = isset($section['section_id']) ? intval($section['section_id']) : null;
                if ($section_id) {
                    $processed_sections[] = $section_id;
                }

                $section_title = sanitize_input($section['title']);
                $section_description = sanitize_input($section['description']);
                $section_order = intval($section['order']);

                if ($section_id) {
                    // Update existing section
                    $stmt = $conn->prepare("UPDATE sections SET title = ?, description = ?, section_order = ? WHERE section_id = ? AND exam_id = ?");
                    if (!$stmt) {
                        throw new Exception("Failed to prepare update statement: " . $conn->error);
                    }
                    $stmt->bind_param("ssiii", $section_title, $section_description, $section_order, $section_id, $exam_id);
                } else {
                    // Insert new section
                    $stmt = $conn->prepare("INSERT INTO sections (exam_id, title, description, section_order) VALUES (?, ?, ?, ?)");
                    if (!$stmt) {
                        throw new Exception("Failed to prepare insert statement: " . $conn->error);
                    }
                    $stmt->bind_param("issi", $exam_id, $section_title, $section_description, $section_order);
                }

                if (!$stmt->execute()) {
                    throw new Exception("Failed to save section: " . $stmt->error);
                }

                // Get section_id for new sections
                if (!$section_id) {
                    $section_id = $conn->insert_id;
                    $processed_sections[] = $section_id;
                }

                // Process questions for this section
                if (isset($section['questions']) && is_array($section['questions'])) {
                    $processed_questions = [];  // Keep track of processed questions

                    foreach ($section['questions'] as $question) {
                        $question_id = isset($question['question_id']) ? intval($question['question_id']) : null;
                        if ($question_id) {
                            $processed_questions[] = $question_id;
                        }

                        $question_text = sanitize_input($question['question_text']);
                        $question_type = $question['question_type'];
                        $points = intval($question['points']);
                        $question_order = intval($question['order']);

                        if ($question_id) {
                            // Update existing question
                            $stmt = $conn->prepare("UPDATE questions SET 
                                exam_id = ?,
                                section_id = ?, 
                                question_text = ?, 
                                question_type = ?, 
                                points = ?, 
                                question_order = ? 
                                WHERE question_id = ?");
                            if (!$stmt) {
                                throw new Exception("Failed to prepare question update statement: " . $conn->error);
                            }
                            $stmt->bind_param("iissiii", 
                                $exam_id,
                                $section_id, 
                                $question_text, 
                                $question_type, 
                                $points, 
                                $question_order, 
                                $question_id
                            );
                        } else {
                            // Insert new question
                            $stmt = $conn->prepare("INSERT INTO questions (
                                exam_id,
                                section_id, 
                                question_text, 
                                question_type, 
                                points, 
                                question_order
                            ) VALUES (?, ?, ?, ?, ?, ?)");
                            if (!$stmt) {
                                throw new Exception("Failed to prepare question insert statement: " . $conn->error);
                            }
                            $stmt->bind_param("iissii", 
                                $exam_id,
                                $section_id, 
                                $question_text, 
                                $question_type, 
                                $points, 
                                $question_order
                            );
                        }

                        if (!$stmt->execute()) {
                            throw new Exception("Failed to save question: " . $stmt->error);
                        }

                        if (!$question_id) {
                            $question_id = $conn->insert_id;
                        }

                        // Handle question type specific data
                        switch ($question_type) {
                            case 'multiple_choice':
                                // Clear existing options
                                $stmt = $conn->prepare("DELETE FROM multiple_choice_options WHERE question_id = ?");
                                $stmt->bind_param("i", $question_id);
                                $stmt->execute();

                                // Insert new options
                                if (isset($question['options']) && is_array($question['options'])) {
                                    error_log("Processing options: " . print_r($question['options'], true));
                                    
                                    foreach ($question['options'] as $option) {
                                        if (empty($option['text'])) {
                                            continue;
                                        }

                                        $stmt = $conn->prepare("INSERT INTO multiple_choice_options (
                                            question_id, 
                                            choice_text, 
                                            is_correct
                                        ) VALUES (?, ?, ?)");
                                        
                                        error_log("Saving option: " . print_r($option, true));
                                        
                                        $stmt->bind_param("isi", 
                                            $question_id, 
                                            $option['text'], 
                                            $option['is_correct']
                                        );
                                        
                                        if (!$stmt->execute()) {
                                            throw new Exception("Failed to save option: " . $stmt->error);
                                        }
                                    }
                                }
                                break;

                            case 'true_false':
                                // Update the question's correct answer
                                $stmt = $conn->prepare("UPDATE questions SET correct_answer = ? WHERE question_id = ?");
                                if (!$stmt) {
                                    throw new Exception("Failed to prepare true/false answer update statement: " . $conn->error);
                                }
                                
                                $correct_answer = $question['correct_answer'];
                                $stmt->bind_param("si", $correct_answer, $question_id);
                                
                                if (!$stmt->execute()) {
                                    throw new Exception("Failed to save true/false answer: " . $stmt->error);
                                }
                                break;

                            case 'programming':
                                // Clear existing test cases
                                $stmt = $conn->prepare("DELETE FROM test_cases WHERE question_id = ?");
                                if (!$stmt) {
                                    error_log("Error preparing delete statement: " . $conn->error);
                                    throw new Exception("Failed to prepare delete statement: " . $conn->error);
                                }
                                $stmt->bind_param("i", $question_id);
                                if (!$stmt->execute()) {
                                    error_log("Error executing delete statement: " . $stmt->error);
                                    throw new Exception("Failed to delete existing test cases: " . $stmt->error);
                                }

                                // Insert new test cases
                                if (isset($question['test_cases']) && is_array($question['test_cases'])) {
                                    error_log("Processing test cases: " . print_r($question['test_cases'], true));
                                    
                                    foreach ($question['test_cases'] as $test_case) {
                                        error_log("Processing test case: " . print_r($test_case, true));
                                        
                                        $stmt = $conn->prepare("INSERT INTO test_cases (
                                            question_id, 
                                            test_input, 
                                            expected_output, 
                                            created_at,
                                            is_hidden,
                                            description
                                        ) VALUES (?, ?, ?, NOW(), ?, ?)");
                                        
                                        if (!$stmt) {
                                            error_log("Error preparing insert statement: " . $conn->error);
                                            throw new Exception("Failed to prepare insert statement: " . $conn->error);
                                        }

                                        $is_hidden = isset($test_case['is_hidden']) ? (int)$test_case['is_hidden'] : 0;
                                        $description = isset($test_case['description']) ? $test_case['description'] : '';
                                        
                                        $stmt->bind_param("issis", 
                                            $question_id, 
                                            $test_case['test_input'], 
                                            $test_case['expected_output'], 
                                            $is_hidden,
                                            $description
                                        );
                                        
                                        if (!$stmt->execute()) {
                                            error_log("Error executing insert statement: " . $stmt->error);
                                            throw new Exception("Failed to save test case: " . $stmt->error);
                                        }
                                    }
                                }
                                break;
                        }
                    }

                    // Only delete questions that were removed from the current section
                    if (!empty($existing_questions)) {
                        foreach ($existing_questions as $existing_id => $existing_type) {
                            if (!in_array($existing_id, $processed_questions)) {
                                // This question was removed, delete it
                                $delete_stmt = $conn->prepare("DELETE FROM questions WHERE question_id = ?");
                                $delete_stmt->bind_param("i", $existing_id);
                                $delete_stmt->execute();
                                
                                // Delete related data based on question type
                                switch ($existing_type) {
                                    case 'multiple_choice':
                                        $delete_options = $conn->prepare("DELETE FROM multiple_choice_options WHERE question_id = ?");
                                        $delete_options->bind_param("i", $existing_id);
                                        $delete_options->execute();
                                        break;
                                    case 'programming':
                                        $delete_tests = $conn->prepare("DELETE FROM test_cases WHERE question_id = ?");
                                        $delete_tests->bind_param("i", $existing_id);
                                        $delete_tests->execute();
                                        break;
                                }
                            }
                        }
                    }
                }
            }

            // Don't delete sections that weren't in the current update
            if (!empty($existing_sections)) {
                foreach ($existing_sections as $existing_section_id) {
                    if (!in_array($existing_section_id, $processed_sections)) {
                        continue;  // Skip sections that weren't processed
                    }
                }
            }

            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Questions saved successfully']);

        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    } else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Handle GET request to fetch existing questions
        if (!isset($_GET['exam_id'])) {
            throw new Exception('Exam ID is required');
        }
        
        $exam_id = intval($_GET['exam_id']);
        
        error_log("Fetching sections and questions for exam_id: " . $exam_id);
        
        // Fetch sections and questions
        $stmt = $conn->prepare("SELECT * FROM sections WHERE exam_id = ? ORDER BY section_order");
        $stmt->bind_param("i", $exam_id);
        $stmt->execute();
        $sections_result = $stmt->get_result();
        
        $sections = [];
        while ($section = $sections_result->fetch_assoc()) {
            error_log("Processing section: " . print_r($section, true));
            
            // Fetch questions for this section
            $questions_stmt = $conn->prepare("
                SELECT 
                    question_id,
                    exam_id,
                    section_id,
                    question_text,
                    question_type,
                    points,
                    question_order,
                    correct_answer,
                    programming_language,
                    created_at,
                    updated_at
                FROM questions 
                WHERE section_id = ? 
                ORDER BY question_order
            ");
            $questions_stmt->bind_param("i", $section['section_id']);
            $questions_stmt->execute();
            $questions_result = $questions_stmt->get_result();
            
            $questions = [];
            while ($question = $questions_result->fetch_assoc()) {
                error_log("Processing question: " . print_r($question, true));
                
                // Add question type specific data
                if ($question['question_type'] === 'multiple_choice') {
                    error_log("Fetching options for question ID: " . $question['question_id']);
                    $options_stmt = $conn->prepare("
                        SELECT 
                            option_id,
                            question_id,
                            choice_text as text,
                            is_correct 
                        FROM multiple_choice_options 
                        WHERE question_id = ?
                        ORDER BY option_id
                    ");
                    $options_stmt->bind_param("i", $question['question_id']);
                    $options_stmt->execute();
                    $options_result = $options_stmt->get_result();
                    $question['options'] = $options_result->fetch_all(MYSQLI_ASSOC);
                    error_log("Fetched options: " . print_r($question['options'], true));
                } else if ($question['question_type'] === 'programming') {
                    $test_cases_stmt = $conn->prepare("SELECT * FROM test_cases WHERE question_id = ? ORDER BY test_case_id");
                    $test_cases_stmt->bind_param("i", $question['question_id']);
                    $test_cases_stmt->execute();
                    $question['test_cases'] = $test_cases_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                }
                $questions[] = $question;
            }
            
            $section['questions'] = $questions;
            $sections[] = $section;
        }
        
        error_log("Sending response: " . print_r(['success' => true, 'sections' => $sections], true));
        echo json_encode(['success' => true, 'sections' => $sections]);
    }
} catch (Exception $e) {
    logError($e->getMessage(), 'save_question.php');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>