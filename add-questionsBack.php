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
                // First get all questions
                $stmt = $conn->prepare("SELECT * FROM questions WHERE exam_id = ? ORDER BY question_id");
                $stmt->bind_param("i", $exam_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                $questions = [];
                while ($row = $result->fetch_assoc()) {
                    $question = [
                        'question_id' => $row['question_id'],
                        'question_text' => $row['question_text'],
                        'question_type' => $row['question_type']
                    ];
                    
                    // Get additional data based on question type
                    switch ($row['question_type']) {
                        case 'multiple-choice':
                            $optStmt = $conn->prepare("SELECT option_text, is_correct, option_label FROM multiple_choice_options WHERE question_id = ?");
                            $optStmt->bind_param("i", $row['question_id']);
                            $optStmt->execute();
                            $optResult = $optStmt->get_result();
                            
                            $options = [];
                            while ($opt = $optResult->fetch_assoc()) {
                                $options[$opt['option_label']] = $opt['option_text'];
                                if ($opt['is_correct']) {
                                    $question['answer'] = $opt['option_label'];
                                }
                            }
                            $question['options'] = $options;
                            break;

                        case 'true-false':
                        case 'identification':
                        case 'coding':
                            $ansStmt = $conn->prepare("SELECT answer_text FROM question_answers WHERE question_id = ?");
                            $ansStmt->bind_param("i", $row['question_id']);
                            $ansStmt->execute();
                            $ansResult = $ansStmt->get_result();
                            if ($ans = $ansResult->fetch_assoc()) {
                                $question['answer'] = $ans['answer_text'];
                            }
                            break;

                        case 'matching':
                            $matchStmt = $conn->prepare("SELECT term, match_text FROM matching_pairs WHERE question_id = ?");
                            $matchStmt->bind_param("i", $row['question_id']);
                            $matchStmt->execute();
                            $matchResult = $matchStmt->get_result();
                            if ($match = $matchResult->fetch_assoc()) {
                                $question['term'] = $match['term'];
                                $question['match'] = $match['match_text'];
                            }
                            break;
                    }
                    
                    $questions[] = $question;
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