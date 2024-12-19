<?php
include_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

function logError($error, $context = '') {
    $logFile = __DIR__ . '/logs/error.log';
    $timestamp = date('Y-m-d H:i:s');
    $message = "[$timestamp] $context: $error\n";
    error_log($message, 3, $logFile);
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $json_data = file_get_contents('php://input');
        $data = json_decode($json_data, true);
        
        if (!isset($data['question_id'])) {
            throw new Exception('Question ID is required');
        }

        $question_id = intval($data['question_id']);

        // Start transaction
        $conn->begin_transaction();

        try {
            // Get question type first
            $type_stmt = $conn->prepare("SELECT question_type FROM questions WHERE question_id = ?");
            $type_stmt->bind_param("i", $question_id);
            $type_stmt->execute();
            $result = $type_stmt->get_result();
            $question = $result->fetch_assoc();

            if (!$question) {
                throw new Exception('Question not found');
            }

            // Delete related data based on question type
            switch ($question['question_type']) {
                case 'multiple_choice':
                    $delete_options = $conn->prepare("DELETE FROM multiple_choice_options WHERE question_id = ?");
                    $delete_options->bind_param("i", $question_id);
                    $delete_options->execute();
                    break;

                case 'programming':
                    $delete_tests = $conn->prepare("DELETE FROM test_cases WHERE question_id = ?");
                    $delete_tests->bind_param("i", $question_id);
                    $delete_tests->execute();
                    break;

                case 'true_false':
                    // No additional data to delete for true/false questions
                    break;
            }

            // Finally delete the question itself
            $delete_question = $conn->prepare("DELETE FROM questions WHERE question_id = ?");
            $delete_question->bind_param("i", $question_id);
            
            if (!$delete_question->execute()) {
                throw new Exception("Failed to delete question: " . $delete_question->error);
            }

            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Question deleted successfully']);

        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    } else {
        throw new Exception('Invalid request method');
    }
} catch (Exception $e) {
    logError($e->getMessage(), 'delete_question.php');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>