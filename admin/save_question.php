<?php
include_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

// Function to sanitize input but preserve HTML from CKEditor
function sanitize_input($data) {
    if (empty($data)) return '';
    return $data; // Preserve CKEditor formatting
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
        $postData = file_get_contents('php://input');
        $data = json_decode($postData, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON data: ' . json_last_error_msg());
        }
        
        // Add more detailed validation
        if (!isset($data['exam_id'])) {
            throw new Exception('Exam ID is missing from request');
        }
        
        if (empty($data['exam_id']) || !is_numeric($data['exam_id'])) {
            throw new Exception('Invalid Exam ID format');
        }

        $exam_id = intval($data['exam_id']);
        
        // Verify exam exists
        $stmt = $conn->prepare("SELECT exam_id FROM exams WHERE exam_id = ?");
        if (!$stmt) {
            throw new Exception('Failed to prepare exam verification query');
        }
        
        $stmt->bind_param("i", $exam_id);
        if (!$stmt->execute()) {
            throw new Exception('Failed to verify exam existence');
        }
        
        $result = $stmt->get_result();
        if (!$result->fetch_assoc()) {
            throw new Exception("Exam with ID $exam_id not found");
        }

        $conn->begin_transaction();

        try {
            // Handle different actions
            $action = $data['action'] ?? 'save_sections';
            
            switch ($action) {
                case 'delete_section':
                    if (empty($data['section_id'])) {
                        throw new Exception('Section ID is required for deletion');
                    }
                    
                    $section_id = intval($data['section_id']);
                    
                    // Delete the section (cascading delete will handle related records)
                    $stmt = $conn->prepare("DELETE FROM sections WHERE section_id = ? AND exam_id = ?");
                    $stmt->bind_param("ii", $section_id, $exam_id);
                    if (!$stmt->execute()) {
                        throw new Exception("Failed to delete section: " . $stmt->error);
                    }
                    break;

                case 'save_sections':
                    if (!empty($data['sections'])) {
                        foreach ($data['sections'] as $section) {
                            $section_id = $section['section_id'] ? intval($section['section_id']) : null;
                            
                            if ($section_id) {
                                // Update existing section
                                $stmt = $conn->prepare("UPDATE sections SET title = ?, description = ?, section_order = ? WHERE section_id = ?");
                                $stmt->bind_param("ssii", $section['title'], $section['description'], $section['order'], $section_id);
                            } else {
                                // Insert new section
                                $stmt = $conn->prepare("INSERT INTO sections (exam_id, title, description, section_order) VALUES (?, ?, ?, ?)");
                                $stmt->bind_param("issi", $exam_id, $section['title'], $section['description'], $section['order']);
                            }
                            $stmt->execute();
                            
                            if (!$section_id) {
                                $section_id = $conn->insert_id;
                            }

                            // Handle questions for this section
                            if (!empty($section['questions'])) {
                                foreach ($section['questions'] as $question) {
                                    $question_id = $question['question_id'] ? intval($question['question_id']) : null;
                                    
                                    if ($question_id) {
                                        // Update existing question
                                        $stmt = $conn->prepare("UPDATE questions SET question_text = ?, question_type = ?, points = ?, question_order = ? WHERE question_id = ?");
                                        $stmt->bind_param("ssiii", $question['question_text'], $question['question_type'], $question['points'], $question['order'], $question_id);
                                    } else {
                                        // Insert new question
                                        $stmt = $conn->prepare("INSERT INTO questions (section_id, question_text, question_type, points, question_order) VALUES (?, ?, ?, ?, ?)");
                                        $stmt->bind_param("issii", $section_id, $question['question_text'], $question['question_type'], $question['points'], $question['order']);
                                    }
                                    $stmt->execute();
                                    
                                    if (!$question_id) {
                                        $question_id = $conn->insert_id;
                                    }

                                    // Handle question type specific data
                                    switch ($question['question_type']) {
                                        case 'multiple_choice':
                                            // Delete existing options if updating
                                            if ($question_id) {
                                                $stmt = $conn->prepare("DELETE FROM multiple_choice_options WHERE question_id = ?");
                                                $stmt->bind_param("i", $question_id);
                                                $stmt->execute();
                                            }
                                            
                                            // Insert options
                                            foreach ($question['options'] as $option) {
                                                $stmt = $conn->prepare("INSERT INTO multiple_choice_options (question_id, choice_text, is_correct, option_order) VALUES (?, ?, ?, ?)");
                                                $stmt->bind_param("isii", $question_id, $option['text'], $option['is_correct'], $option['order']);
                                                $stmt->execute();
                                            }
                                            break;

                                        case 'programming':
                                            // Delete existing test cases if updating
                                            if ($question_id) {
                                                $stmt = $conn->prepare("DELETE FROM test_cases WHERE question_id = ?");
                                                $stmt->bind_param("i", $question_id);
                                                $stmt->execute();
                                            }
                                            
                                            // Insert test cases
                                            foreach ($question['test_cases'] as $testCase) {
                                                $stmt = $conn->prepare("INSERT INTO test_cases (question_id, test_input, expected_output, test_case_order) VALUES (?, ?, ?, ?)");
                                                $stmt->bind_param("issi", $question_id, $testCase['input'], $testCase['expected_output'], $testCase['order']);
                                                $stmt->execute();
                                            }
                                            break;
                                    }
                                }
                            }
                        }
                    }
                    break;

                default:
                    throw new Exception('Invalid action specified');
            }
            
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Changes saved successfully']);
            
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    } else {
        throw new Exception('Invalid request method');
    }
    
} catch (Exception $e) {
    logError($e->getMessage(), 'save_question.php');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

exit;
?>