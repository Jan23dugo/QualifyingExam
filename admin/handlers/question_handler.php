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
            case 'add_multiple':
                try {
                    $conn->begin_transaction();
                    $questions = json_decode($_POST['questions'], true);
                    
                    foreach ($questions as $question) {
                        // Insert question
                        $sql = "INSERT INTO question_bank (category, question_type, question_text) VALUES (?, ?, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("sss", $question['category'], $question['question_type'], $question['question_text']);
                        
                        if (!$stmt->execute()) {
                            throw new Exception('Failed to add question');
                        }
                        
                        $question_id = $conn->insert_id;
                        
                        // Handle different question types
                        switch ($question['question_type']) {
                            case 'multiple_choice':
                                if (isset($question['options'])) {
                                    $sql = "INSERT INTO question_bank_choices (question_id, choice_text, is_correct) VALUES (?, ?, ?)";
                                    $stmt = $conn->prepare($sql);
                                    
                                    foreach ($question['options'] as $index => $option) {
                                        $is_correct = ($index == $question['correct_answer']) ? 1 : 0;
                                        $stmt->bind_param("isi", $question_id, $option, $is_correct);
                                        if (!$stmt->execute()) {
                                            throw new Exception('Failed to add choice');
                                        }
                                    }
                                }
                                break;

                            case 'programming':
                                $sql = "INSERT INTO question_bank_programming (
                                    question_id, 
                                    programming_language
                                ) VALUES (?, ?)";
                                
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("is", 
                                    $question_id,
                                    $question['programming_language']
                                );
                                
                                if (!$stmt->execute()) {
                                    throw new Exception('Failed to add programming details');
                                }
                                
                                // Add test cases if they exist
                                if (isset($question['test_cases']) && !empty($question['test_cases'])) {
                                    $sql = "INSERT INTO question_bank_test_cases (
                                        question_id, 
                                        test_input, 
                                        expected_output, 
                                        is_hidden,
                                        description
                                    ) VALUES (?, ?, ?, ?, ?)";
                                    
                                    $stmt = $conn->prepare($sql);
                                    
                                    foreach ($question['test_cases'] as $test_case) {
                                        $stmt->bind_param("issis", 
                                            $question_id,
                                            $test_case['test_input'],
                                            $test_case['expected_output'],
                                            $test_case['is_hidden'],
                                            $test_case['description']
                                        );
                                        
                                        if (!$stmt->execute()) {
                                            throw new Exception('Failed to add test case');
                                        }
                                    }
                                }
                                break;

                            case 'essay':
                                // Add essay-specific handling if needed
                                if (isset($question['answer_guidelines'])) {
                                    $sql = "UPDATE question_bank SET answer_guidelines = ? WHERE question_id = ?";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param("si", $question['answer_guidelines'], $question_id);
                                    if (!$stmt->execute()) {
                                        throw new Exception('Failed to add essay guidelines');
                                    }
                                }
                                break;

                            case 'true_false':
                                // Update the question with the correct answer
                                $sql = "UPDATE question_bank SET correct_answer = ? WHERE question_id = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("si", $question['correct_answer'], $question_id);
                                if (!$stmt->execute()) {
                                    throw new Exception('Failed to add true/false answer');
                                }
                                break;
                        }
                    }
                    
                    $conn->commit();
                    echo json_encode(['status' => 'success', 'message' => 'Questions added successfully']);
                } catch (Exception $e) {
                    $conn->rollback();
                    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
                }
                break;
            case 'get_question':
                try {
                    if (!isset($_POST['question_id'])) {
                        throw new Exception('Question ID is required');
                    }
                    
                    $question_id = $_POST['question_id'];
                    
                    // Get question data
                    $sql = "SELECT * FROM question_bank WHERE question_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $question_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows === 0) {
                        throw new Exception('Question not found');
                    }
                    
                    $question = $result->fetch_assoc();
                    
                    // Get additional data based on question type
                    switch($question['question_type']) {
                        case 'multiple_choice':
                            $sql = "SELECT choice_text, is_correct FROM question_bank_choices WHERE question_id = ? ORDER BY id";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("i", $question_id);
                            $stmt->execute();
                            $choices_result = $stmt->get_result();
                            
                            $question['choices'] = [];
                            while($choice = $choices_result->fetch_assoc()) {
                                $question['choices'][] = [
                                    'text' => $choice['choice_text'],
                                    'is_correct' => (bool)$choice['is_correct']
                                ];
                            }
                            break;

                        case 'programming':
                            // Get programming language
                            $sql = "SELECT programming_language FROM question_bank_programming WHERE question_id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("i", $question_id);
                            $stmt->execute();
                            $prog_result = $stmt->get_result();
                            if ($prog_details = $prog_result->fetch_assoc()) {
                                $question['programming_language'] = $prog_details['programming_language'];
                            }
                            
                            // Get test cases
                            $sql = "SELECT test_input, expected_output, is_hidden 
                                   FROM question_bank_test_cases 
                                   WHERE question_id = ? 
                                   ORDER BY test_case_id";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("i", $question_id);
                            $stmt->execute();
                            $test_cases_result = $stmt->get_result();
                            
                            $question['test_cases'] = [];
                            while ($test_case = $test_cases_result->fetch_assoc()) {
                                $question['test_cases'][] = [
                                    'test_input' => $test_case['test_input'],
                                    'expected_output' => $test_case['expected_output'],
                                    'is_hidden' => (bool)$test_case['is_hidden']
                                ];
                            }
                            break;
                    }
                    
                    echo json_encode(['status' => 'success', 'data' => $question]);
                    
                } catch (Exception $e) {
                    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
                }
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
                question_id, 
                programming_language
            ) VALUES (?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", 
                $question_id,
                $_POST['programming_language']
            );
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to add programming details');
            }
            
            // Add test cases
            $sql = "INSERT INTO question_bank_test_cases (
                question_id, 
                test_input, 
                expected_output, 
                is_hidden
            ) VALUES (?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            
            // Process test cases
            if (isset($_POST['test_case_input']) && is_array($_POST['test_case_input'])) {
                foreach ($_POST['test_case_input'] as $index => $input) {
                    if (empty($input) || !isset($_POST['test_case_output'][$index])) {
                        continue;
                    }

                    $isHidden = isset($_POST['test_case_hidden'][$index]) ? 1 : 0;
                    
                    $stmt->bind_param("issi", 
                        $question_id,
                        $input,
                        $_POST['test_case_output'][$index],
                        $isHidden
                    );
                    
                    if (!$stmt->execute()) {
                        throw new Exception('Failed to add test case');
                    }
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
                WHERE question_id = ?";
        
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
        $sql = "DELETE FROM question_bank WHERE question_id = ?";
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