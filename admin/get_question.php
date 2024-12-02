<?php
require_once '../config/config.php';

header('Content-Type: application/json');

try {
    $question_id = $_GET['id'];
    
    // Get question details
    $stmt = $conn->prepare("
        SELECT q.*, c.choice_text, c.is_correct 
        FROM question_bank q
        LEFT JOIN question_bank_choices c ON q.question_id = c.question_id
        WHERE q.question_id = ?
    ");
    $stmt->bind_param("i", $question_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $question = null;
    $choices = [];
    
    while ($row = $result->fetch_assoc()) {
        if (!$question) {
            $question = [
                'question_id' => $row['question_id'],
                'question_text' => $row['question_text'],
                'question_type' => $row['question_type'],
                'choices' => []
            ];
        }
        if ($row['choice_text']) {
            $question['choices'][] = [
                'choice_text' => $row['choice_text'],
                'is_correct' => $row['is_correct']
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'question' => $question
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?> 