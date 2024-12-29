<?php
require_once '../config/config.php';

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        throw new Exception('Invalid input data');
    }

    $conn->begin_transaction();

    // Update basic question info
    $stmt = $conn->prepare("UPDATE question_bank SET question_text = ? WHERE question_id = ?");
    $stmt->bind_param("si", $data['question_text'], $data['question_id']);
    if (!$stmt->execute()) {
        throw new Exception('Failed to update question text');
    }

    // Handle type-specific updates
    switch ($data['question_type']) {
        case 'multiple_choice':
            // Clear existing choices
            $stmt = $conn->prepare("DELETE FROM question_bank_choices WHERE question_id = ?");
            $stmt->bind_param("i", $data['question_id']);
            $stmt->execute();

            // Insert new choices
            $stmt = $conn->prepare("INSERT INTO question_bank_choices (question_id, choice_text, is_correct) VALUES (?, ?, ?)");
            foreach ($data['options'] as $index => $option) {
                $is_correct = ($index == $data['correct_answer']) ? 1 : 0;
                $stmt->bind_param("isi", $data['question_id'], $option, $is_correct);
                if (!$stmt->execute()) {
                    throw new Exception('Failed to update choices');
                }
            }
            break;

        case 'true_false':
            // Clear existing choices
            $stmt = $conn->prepare("DELETE FROM question_bank_choices WHERE question_id = ?");
            $stmt->bind_param("i", $data['question_id']);
            $stmt->execute();

            // Insert true/false answer
            $stmt = $conn->prepare("INSERT INTO question_bank_choices (question_id, choice_text, is_correct) VALUES (?, ?, 1)");
            $stmt->bind_param("is", $data['question_id'], $data['correct_answer']);
            if (!$stmt->execute()) {
                throw new Exception('Failed to update true/false answer');
            }
            break;

        case 'programming':
            // Update programming language
            $stmt = $conn->prepare("UPDATE question_bank_programming SET programming_language = ? WHERE question_id = ?");
            $stmt->bind_param("si", $data['programming_language'], $data['question_id']);
            $stmt->execute();

            // Clear existing test cases
            $stmt = $conn->prepare("DELETE FROM question_bank_test_cases WHERE question_id = ?");
            $stmt->bind_param("i", $data['question_id']);
            $stmt->execute();

            // Insert new test cases
            if (!empty($data['test_cases'])) {
                $stmt = $conn->prepare("INSERT INTO question_bank_test_cases (question_id, test_input, expected_output, is_hidden, description) VALUES (?, ?, ?, ?, ?)");
                foreach ($data['test_cases'] as $test_case) {
                    $is_hidden = $test_case['is_hidden'] ? 1 : 0;
                    $stmt->bind_param("issis", 
                        $data['question_id'],
                        $test_case['test_input'],
                        $test_case['expected_output'],
                        $is_hidden,
                        $test_case['description']
                    );
                    if (!$stmt->execute()) {
                        throw new Exception('Failed to update test cases');
                    }
                }
            }
            break;
    }

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?> 