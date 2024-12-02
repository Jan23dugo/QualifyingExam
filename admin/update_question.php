<?php
require_once '../config/config.php';

header('Content-Type: application/json');

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

try {
    $question_id = $data['question_id'];
    $question_text = $data['question_text'];
    $options = $data['options'];
    $correct_answer = $data['correct_answer'];

    // Start transaction
    $conn->begin_transaction();

    // Update question text
    $stmt = $conn->prepare("UPDATE question_bank SET question_text = ? WHERE question_id = ?");
    $stmt->bind_param("si", $question_text, $question_id);
    $stmt->execute();

    // Delete old choices
    $stmt = $conn->prepare("DELETE FROM question_bank_choices WHERE question_id = ?");
    $stmt->bind_param("i", $question_id);
    $stmt->execute();

    // Insert new choices
    $stmt = $conn->prepare("INSERT INTO question_bank_choices (question_id, choice_text, is_correct) VALUES (?, ?, ?)");
    foreach ($options as $index => $option) {
        $is_correct = ($index == $correct_answer) ? 1 : 0;
        $stmt->bind_param("isi", $question_id, $option, $is_correct);
        $stmt->execute();
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Question updated successfully'
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?> 