<?php
include_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

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
        $data = json_decode($json_data, true);
        
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
            // Process each section
            foreach ($data['sections'] as $section) {
                // Ensure exam_id is set for the section
                $section['exam_id'] = $exam_id;
                
                if (empty($section['exam_id'])) {
                    throw new Exception('Section exam_id is required');
                }

                $section_id = isset($section['section_id']) ? intval($section['section_id']) : null;
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
                }

                // Process questions for this section
                if (isset($section['questions']) && is_array($section['questions'])) {
                    foreach ($section['questions'] as $question) {
                        $question_id = isset($question['question_id']) ? intval($question['question_id']) : null;
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
                                    foreach ($question['options'] as $option) {
                                        // Debug log to see what data we're getting
                                        logError("Option data: " . print_r($option, true), 'debug');
                                        
                                        $stmt = $conn->prepare("INSERT INTO multiple_choice_options (
                                            question_id, 
                                            choice_text, 
                                            is_correct
                                        ) VALUES (?, ?, ?)");
                                        
                                        if (!$stmt) {
                                            throw new Exception("Failed to prepare option insert statement: " . $conn->error);
                                        }
                                        
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

                            case 'programming':
                                // Clear existing test cases
                                $stmt = $conn->prepare("DELETE FROM test_cases WHERE question_id = ?");
                                $stmt->bind_param("i", $question_id);
                                $stmt->execute();

                                // Insert new test cases
                                if (isset($question['test_cases']) && is_array($question['test_cases'])) {
                                    foreach ($question['test_cases'] as $test_case) {
                                        $stmt = $conn->prepare("INSERT INTO test_cases (question_id, input_data, expected_output, test_case_order) VALUES (?, ?, ?, ?)");
                                        $stmt->bind_param("issi", $question_id, $test_case['input'], $test_case['expected_output'], $test_case['order']);
                                        $stmt->execute();
                                    }
                                }
                                break;
                        }
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
        
        // Fetch sections and questions
        $stmt = $conn->prepare("SELECT * FROM sections WHERE exam_id = ? ORDER BY section_order");
        $stmt->bind_param("i", $exam_id);
        $stmt->execute();
        $sections_result = $stmt->get_result();
        
        $sections = [];
        while ($section = $sections_result->fetch_assoc()) {
            // Fetch questions for this section
            $questions_stmt = $conn->prepare("SELECT * FROM questions WHERE section_id = ? ORDER BY question_order");
            $questions_stmt->bind_param("i", $section['section_id']);
            $questions_stmt->execute();
            $questions_result = $questions_stmt->get_result();
            
            $questions = [];
            while ($question = $questions_result->fetch_assoc()) {
                // Add question type specific data
                if ($question['question_type'] === 'multiple_choice') {
                    $options_stmt = $conn->prepare("SELECT 
                        option_id,
                        question_id,
                        choice_text,
                        is_correct,
                        created_at
                    FROM multiple_choice_options 
                    WHERE question_id = ?");
                    
                    if (!$options_stmt) {
                        throw new Exception("Failed to prepare options select statement: " . $conn->error);
                    }
                    
                    $options_stmt->bind_param("i", $question['question_id']);
                    $options_stmt->execute();
                    $options_result = $options_stmt->get_result();
                    
                    // Transform the options data to match the format expected by the frontend
                    $options = [];
                    while ($option = $options_result->fetch_assoc()) {
                        $options[] = [
                            'id' => $option['option_id'],
                            'text' => $option['choice_text'],
                            'is_correct' => (int)$option['is_correct']
                        ];
                    }
                    $question['options'] = $options;
                } else if ($question['question_type'] === 'programming') {
                    $test_cases_stmt = $conn->prepare("SELECT * FROM test_cases WHERE question_id = ? ORDER BY test_case_order");
                    $test_cases_stmt->bind_param("i", $question['question_id']);
                    $test_cases_stmt->execute();
                    $question['test_cases'] = $test_cases_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                }
                $questions[] = $question;
            }
            
            $section['questions'] = $questions;
            $sections[] = $section;
        }
        
        echo json_encode(['success' => true, 'sections' => $sections]);
    }
} catch (Exception $e) {
    logError($e->getMessage(), 'save_question.php');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>