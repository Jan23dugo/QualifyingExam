<?php
session_start();
include('../config/config.php');

// Function to validate and sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to handle API responses
function send_response($status, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

// Check if it's an AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $exam_id = $_POST['exam_id'] ?? null;

    if (!$exam_id) {
        send_response('error', 'Exam ID is required');
    }

    switch ($action) {
        case 'add_question':
            try {
                $conn->begin_transaction();

                $question_text = sanitize_input($_POST['question_text']);
                $question_type = sanitize_input($_POST['question_type']);

                // Insert the question
                $stmt = $conn->prepare("INSERT INTO questions (exam_id, question_text, question_type) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $exam_id, $question_text, $question_type);
                $stmt->execute();
                $question_id = $stmt->insert_id;

                // Handle different question types
                switch ($question_type) {
                    case 'multiple-choice':
                        $options = [
                            'A' => $_POST['optionA'],
                            'B' => $_POST['optionB'],
                            'C' => $_POST['optionC'],
                            'D' => $_POST['optionD']
                        ];
                        $correct_answer = $_POST['mcAnswer'];

                        foreach ($options as $label => $option_text) {
                            $is_correct = ($label === $correct_answer);
                            $stmt = $conn->prepare("INSERT INTO multiple_choice_options (question_id, option_text, is_correct, option_label) VALUES (?, ?, ?, ?)");
                            $stmt->bind_param("isis", $question_id, $option_text, $is_correct, $label);
                            $stmt->execute();
                        }
                        break;

                    case 'true-false':
                        $answer = $_POST['tfAnswer'];
                        $stmt = $conn->prepare("INSERT INTO question_answers (question_id, answer_text) VALUES (?, ?)");
                        $stmt->bind_param("is", $question_id, $answer);
                        $stmt->execute();
                        break;

                    case 'matching':
                        $term = sanitize_input($_POST['term']);
                        $match = sanitize_input($_POST['match']);
                        $stmt = $conn->prepare("INSERT INTO matching_pairs (question_id, term, match_text) VALUES (?, ?, ?)");
                        $stmt->bind_param("iss", $question_id, $term, $match);
                        $stmt->execute();
                        break;

                    case 'coding':
                        $code_answer = $_POST['codeAnswer'];
                        $stmt = $conn->prepare("INSERT INTO question_answers (question_id, answer_text) VALUES (?, ?)");
                        $stmt->bind_param("is", $question_id, $code_answer);
                        $stmt->execute();
                        break;

                    case 'identification':
                        $answer = $_POST['identificationAnswer'];
                        $stmt = $conn->prepare("INSERT INTO question_answers (question_id, answer_text) VALUES (?, ?)");
                        $stmt->bind_param("is", $question_id, $answer);
                        $stmt->execute();
                        break;
                }

                $conn->commit();
                send_response('success', 'Question added successfully', ['question_id' => $question_id]);

            } catch (Exception $e) {
                $conn->rollback();
                send_response('error', 'Error adding question: ' . $e->getMessage());
            }
            break;

        case 'edit_question':
            try {
                $conn->begin_transaction();

                $question_id = $_POST['question_id'];
                $question_text = sanitize_input($_POST['question_text']);

                // Update question text
                $stmt = $conn->prepare("UPDATE questions SET question_text = ? WHERE question_id = ?");
                $stmt->bind_param("si", $question_text, $question_id);
                $stmt->execute();

                // Handle different question types similar to add_question
                // ... (similar switch case structure as add_question)

                $conn->commit();
                send_response('success', 'Question updated successfully');

            } catch (Exception $e) {
                $conn->rollback();
                send_response('error', 'Error updating question: ' . $e->getMessage());
            }
            break;

        case 'remove_question':
            try {
                $question_id = $_POST['question_id'];
                
                // Due to foreign key constraints with CASCADE, this will automatically remove related entries
                $stmt = $conn->prepare("DELETE FROM questions WHERE question_id = ?");
                $stmt->bind_param("i", $question_id);
                $stmt->execute();

                send_response('success', 'Question removed successfully');

            } catch (Exception $e) {
                send_response('error', 'Error removing question: ' . $e->getMessage());
            }
            break;

        case 'get_questions':
            try {
                $stmt = $conn->prepare("
                    SELECT q.*, 
                           mco.option_text, mco.is_correct, mco.option_label,
                           qa.answer_text,
                           mp.term, mp.match_text
                    FROM questions q
                    LEFT JOIN multiple_choice_options mco ON q.question_id = mco.question_id
                    LEFT JOIN question_answers qa ON q.question_id = qa.question_id
                    LEFT JOIN matching_pairs mp ON q.question_id = mp.question_id
                    WHERE q.exam_id = ?
                    ORDER BY q.question_id
                ");
                $stmt->bind_param("i", $exam_id);
                $stmt->execute();
                $result = $stmt->get_result();

                $questions = [];
                while ($row = $result->fetch_assoc()) {
                    // Process and format the questions based on their type
                    // Add to $questions array
                }

                send_response('success', 'Questions retrieved successfully', $questions);

            } catch (Exception $e) {
                send_response('error', 'Error retrieving questions: ' . $e->getMessage());
            }
            break;

        default:
            send_response('error', 'Invalid action');
    }
}

// Handle non-POST requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // You can implement GET requests for retrieving questions here
    send_response('error', 'Method not allowed');
}
?>