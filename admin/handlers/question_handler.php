<?php
include('../../config/config.php');

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
        $conn->begin_transaction();

        // Get form data
        $category = isset($_POST['new_category']) && !empty($_POST['new_category']) 
            ? $_POST['new_category'] 
            : $_POST['category'];
        $question_type = $_POST['question_type'];
        $question_text = $_POST['question_text'];
        
        // Insert question
        $sql = "INSERT INTO question_bank (category, question_type, question_text) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $category, $question_type, $question_text);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to add question');
        }
        
        $question_id = $conn->insert_id;
        
        // Handle different question types
        if ($question_type === 'multiple_choice' && isset($_POST['options'])) {
            $options = $_POST['options'];
            $correct_answer = $_POST['correct_answer'];
            
            // Insert choices
            $sql = "INSERT INTO question_bank_choices (question_id, choice_text, is_correct) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            foreach ($options as $index => $option) {
                $is_correct = ($index == $correct_answer) ? 1 : 0;
                $stmt->bind_param("isi", $question_id, $option, $is_correct);
                if (!$stmt->execute()) {
                    throw new Exception('Failed to add choice');
                }
            }
        }
        
        // For programming questions
        if ($question_type === 'programming') {
            $sql = "INSERT INTO question_bank_programming (
                question_id, programming_language, problem_description, 
                input_format, output_format, constraints, solution_template
            ) VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issssss", 
                $question_id,
                $_POST['programming_language'],
                $_POST['problem_description'],
                $_POST['input_format'],
                $_POST['output_format'],
                $_POST['constraints'],
                $_POST['solution_template']
            );
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to add programming details');
            }
            
            // Add test cases
            $sql = "INSERT INTO question_bank_test_cases (
                question_id, test_input, expected_output, explanation, is_hidden
            ) VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            
            // Add sample test cases
            foreach ($_POST['test_case_input'] as $index => $input) {
                $is_hidden = 0;
                $stmt->bind_param("isssi", 
                    $question_id,
                    $input,
                    $_POST['test_case_output'][$index],
                    $_POST['test_case_explanation'][$index],
                    $is_hidden
                );
                if (!$stmt->execute()) {
                    throw new Exception('Failed to add test case');
                }
            }
            
            // Add hidden test cases
            foreach ($_POST['hidden_test_input'] as $index => $input) {
                $is_hidden = 1;
                $explanation = '';
                $stmt->bind_param("isssi", 
                    $question_id,
                    $input,
                    $_POST['hidden_test_output'][$index],
                    $explanation,
                    $is_hidden
                );
                if (!$stmt->execute()) {
                    throw new Exception('Failed to add hidden test case');
                }
            }
        }
        
        $conn->commit();
        echo json_encode(['status' => 'success']);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function handleEditQuestion() {
    global $conn;
    
    try {
        $conn->begin_transaction();

        if (!isset($_POST['question_id'])) {
            throw new Exception('Question ID is required');
        }
        
        $question_id = $_POST['question_id'];
        $question_text = $_POST['question_text'];
        $question_type = $_POST['question_type'];
        
        // Update question
        $sql = "UPDATE question_bank 
                SET question_text = ?, 
                    question_type = ?,
                    updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $question_text, $question_type, $question_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update question');
        }
        
        // Handle multiple choice questions
        if ($question_type === 'multiple_choice') {
            // Delete existing choices
            $sql = "DELETE FROM question_bank_choices WHERE question_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $question_id);
            $stmt->execute();
            
            // Insert new choices
            $sql = "INSERT INTO question_bank_choices (question_id, choice_text, is_correct) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            foreach ($_POST['options'] as $index => $option) {
                $is_correct = ($index == $_POST['correct_answer']) ? 1 : 0;
                $stmt->bind_param("isi", $question_id, $option, $is_correct);
                if (!$stmt->execute()) {
                    throw new Exception('Failed to update choice');
                }
            }
        }
        
        $conn->commit();
        echo json_encode(['status' => 'success']);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function handleDeleteQuestion() {
    global $conn;
    
    try {
        $conn->begin_transaction();

        if (!isset($_POST['question_id'])) {
            throw new Exception('Question ID is required');
        }
        
        $question_id = $_POST['question_id'];
        
        // Delete related records first
        $tables = ['question_bank_choices', 'question_bank_programming', 'question_bank_test_cases'];
        
        foreach ($tables as $table) {
            $sql = "DELETE FROM $table WHERE question_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $question_id);
            $stmt->execute();
        }
        
        // Delete the question
        $sql = "DELETE FROM question_bank WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $question_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to delete question');
        }
        
        $conn->commit();
        echo json_encode(['status' => 'success']);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} 