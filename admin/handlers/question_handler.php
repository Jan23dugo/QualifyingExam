<?php
require_once '../../config/config.php';
header('Content-Type: application/json'); // Add this line to ensure JSON response

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                handleAddQuestion();
                break;
            case 'edit':
                handleEditQuestion();
                break;
            case 'delete':
                handleDeleteQuestion();
                break;
            default:
                echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        }
    }
}

function handleAddQuestion() {
    global $conn;
    
    try {
        // Debug log
        error_log("Received POST data: " . print_r($_POST, true));
        
        // Check category values
        error_log("Category value: " . (isset($_POST['category']) ? $_POST['category'] : 'not set'));
        error_log("New category value: " . (isset($_POST['new_category']) ? $_POST['new_category'] : 'not set'));
        
        // Validate required fields
        if (empty($_POST['category']) && empty($_POST['new_category'])) {
            throw new Exception('Category is required');
        }
        
        $conn->begin_transaction();

        // Get form data with better validation
        $category = !empty($_POST['new_category']) ? 
                   trim($_POST['new_category']) : 
                   trim($_POST['category']);

        if (empty($category)) {
            throw new Exception('Category cannot be empty');
        }

        $question_type = $_POST['question_type'];
        $question_text = trim($_POST['question_text']);

        // Debug log
        error_log("Processing question - Category: $category, Type: $question_type");

        // Insert base question
        $sql = "INSERT INTO question_bank (category, question_type, question_text) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $category, $question_type, $question_text);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to add question: ' . $conn->error);
        }
        
        $question_id = $conn->insert_id;
        error_log("Inserted base question with ID: $question_id");

        // Handle different question types
        switch($question_type) {
            case 'multiple_choice':
                if (isset($_POST['options']) && is_array($_POST['options'])) {
                    $sql = "INSERT INTO question_bank_choices (question_id, choice_text, is_correct) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    
                    foreach ($_POST['options'] as $index => $option) {
                        $is_correct = ($index == $_POST['correct_answer']) ? 1 : 0;
                        $stmt->bind_param("isi", $question_id, $option, $is_correct);
                        if (!$stmt->execute()) {
                            throw new Exception('Failed to add choice');
                        }
                    }
                }
                break;

            case 'true_false':
                if (!isset($_POST['correct_answer'])) {
                    error_log("Missing correct_answer in POST data");
                    throw new Exception('Correct answer is required for true/false questions');
                }
                
                // Debug log
                error_log("True/False answer received: " . $_POST['correct_answer']);
                
                // Update the question with the correct answer
                $sql = "UPDATE question_bank SET correct_answer = ? WHERE question_id = ?";
                $stmt = $conn->prepare($sql);
                
                // Store the actual string value 'True' or 'False'
                $correct_answer = $_POST['correct_answer']; // This will be 'True' or 'False'
                error_log("Saving true/false answer - Question ID: $question_id, Answer: $correct_answer");
                
                $stmt->bind_param("si", $correct_answer, $question_id);
                
                if (!$stmt->execute()) {
                    error_log("SQL Error in true/false update: " . $conn->error);
                    error_log("SQL Query: " . $sql);
                    throw new Exception('Failed to save true/false answer: ' . $conn->error);
                }
                
                // Verify the update
                $verify_sql = "SELECT correct_answer FROM question_bank WHERE question_id = ?";
                $verify_stmt = $conn->prepare($verify_sql);
                $verify_stmt->bind_param("i", $question_id);
                $verify_stmt->execute();
                $verify_result = $verify_stmt->get_result();
                $saved_data = $verify_result->fetch_assoc();
                error_log("Verified saved answer: " . print_r($saved_data, true));
                
                break;

            case 'programming':
                if (empty($_POST['programming_language'])) {
                    throw new Exception('Programming language is required');
                }

                // Insert programming details
                $sql = "INSERT INTO question_bank_programming (question_id, programming_language) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("is", $question_id, $_POST['programming_language']);
                
                if (!$stmt->execute()) {
                    throw new Exception('Failed to add programming details');
                }

                // Handle test cases
                if (!empty($_POST['test_case_input']) && is_array($_POST['test_case_input'])) {
                    $sql = "INSERT INTO question_bank_test_cases (question_id, test_input, expected_output, is_hidden, description) 
                           VALUES (?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    
                    foreach ($_POST['test_case_input'] as $index => $input) {
                        if (!isset($_POST['test_case_output'][$index])) {
                            continue;
                        }

                        $testInput = trim($input);
                        $expectedOutput = trim($_POST['test_case_output'][$index]);
                        $isHidden = isset($_POST['test_case_hidden'][$index]) ? 1 : 0;
                        $description = isset($_POST['test_case_description'][$index]) ? 
                                     trim($_POST['test_case_description'][$index]) : '';

                        $stmt->bind_param("issis", 
                            $question_id,
                            $testInput,
                            $expectedOutput,
                            $isHidden,
                            $description
                        );

                        if (!$stmt->execute()) {
                            throw new Exception('Failed to add test case');
                        }
                    }
                }
                break;
        }

        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Question saved successfully']);
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error in handleAddQuestion: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

// Keep other existing functions (handleEditQuestion, handleDeleteQuestion) as they were... 