<?php
include('../../config/config.php');

// Set proper headers for JSON response
header('Content-Type: application/json');

// Debug log function
function debug_log($message, $data = null) {
    error_log("Import Debug - " . $message . ": " . print_r($data, true));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        debug_log("Starting import process");
        
        if (!isset($_FILES['question_file'])) {
            debug_log("No file uploaded");
            throw new Exception('No file uploaded');
        }

        $file = $_FILES['question_file']['tmp_name'];
        $category = $_POST['category'];
        
        debug_log("File info", $_FILES['question_file']);
        debug_log("Category", $category);
        
        if (!$file || !is_uploaded_file($file)) {
            debug_log("Invalid file upload");
            throw new Exception('Invalid file upload');
        }
        
        if (($handle = fopen($file, "r")) !== FALSE) {
            // Skip header row
            $headers = fgetcsv($handle);
            debug_log("CSV Headers", $headers);
            
            $totalImported = 0;
            $conn->begin_transaction();
            
            while (($data = fgetcsv($handle)) !== FALSE) {
                debug_log("Processing row", $data);
                
                // Skip empty rows
                if (empty(trim($data[0])) || empty(trim($data[1]))) {
                    debug_log("Skipping empty row");
                    continue;
                }
                
                $questionType = strtolower(trim($data[0]));
                $questionText = trim($data[1]);
                
                debug_log("Question type", $questionType);
                debug_log("Question text", $questionText);

                // First insert into question_bank
                switch ($questionType) {
                    case 'multiple_choice':
                        // Insert the question
                        $sql = "INSERT INTO question_bank (category, question_type, question_text, correct_answer) 
                                VALUES (?, ?, ?, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("ssss", 
                            $category,
                            $questionType,
                            $questionText,
                            $data[6]
                        );
                        
                        if ($stmt->execute()) {
                            debug_log("Question inserted successfully", $conn->insert_id);
                            $totalImported++;
                        } else {
                            debug_log("Error inserting question", $conn->error);
                        }
                        break;
                        
                    case 'true_false':
                        // Insert the question
                        $sql = "INSERT INTO question_bank (category, question_type, question_text, correct_answer) 
                                VALUES (?, ?, ?, ?)";
                        $stmt = $conn->prepare($sql);
                        $correctAnswer = trim($data[6]); // Should be 'True' or 'False'
                        $stmt->bind_param("ssss", 
                            $category,
                            $questionType,
                            $questionText,
                            $correctAnswer
                        );
                        
                        if ($stmt->execute()) {
                            $questionId = $conn->insert_id;
                            
                            // Insert True and False as choices
                            $choiceSql = "INSERT INTO question_bank_choices (question_id, choice_text, is_correct) VALUES (?, ?, ?)";
                            $choiceStmt = $conn->prepare($choiceSql);
                            
                            // Insert True choice
                            $trueChoice = 'True';
                            $isCorrectTrue = (strtolower($correctAnswer) === strtolower('True')) ? 1 : 0;
                            $choiceStmt->bind_param("isi", $questionId, $trueChoice, $isCorrectTrue);
                            $choiceStmt->execute();
                            
                            // Insert False choice
                            $falseChoice = 'False';
                            $isCorrectFalse = (strtolower($correctAnswer) === strtolower('False')) ? 1 : 0;
                            $choiceStmt->bind_param("isi", $questionId, $falseChoice, $isCorrectFalse);
                            $choiceStmt->execute();
                            
                            $totalImported++;
                        }
                        break;
                        
                    case 'programming':
                        // Insert the question
                        $sql = "INSERT INTO question_bank (category, question_type, question_text) 
                                VALUES (?, ?, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("sss", 
                            $category,
                            $questionType,
                            $questionText
                        );
                        
                        if ($stmt->execute()) {
                            $questionId = $conn->insert_id;
                            
                            // Insert programming language
                            if (!empty(trim($data[7]))) {
                                $progSql = "INSERT INTO question_bank_programming (question_id, programming_language) 
                                          VALUES (?, ?)";
                                $progStmt = $conn->prepare($progSql);
                                $progStmt->bind_param("is", 
                                    $questionId,
                                    trim($data[7])
                                );
                                $progStmt->execute();
                                
                                // Insert test case
                                if (!empty(trim($data[8])) && !empty(trim($data[9]))) {
                                    $testSql = "INSERT INTO question_bank_test_cases 
                                              (question_id, test_input, expected_output, is_hidden, description) 
                                              VALUES (?, ?, ?, ?, ?)";
                                    $testStmt = $conn->prepare($testSql);
                                    $isHidden = !empty(trim($data[10])) ? trim($data[10]) : '0';
                                    $description = !empty(trim($data[11])) ? trim($data[11]) : '';
                                    $testStmt->bind_param("issss", 
                                        $questionId,
                                        trim($data[8]),
                                        trim($data[9]),
                                        $isHidden,
                                        $description
                                    );
                                    $testStmt->execute();
                                }
                            }
                            $totalImported++;
                        }
                        break;
                }
            }
            
            $conn->commit();
            fclose($handle);
            
            debug_log("Import completed", ["total_imported" => $totalImported]);
            
            // Clean the response data
            $response = [
                'status' => 'success',
                'total_imported' => (int)$totalImported,
                'message' => $totalImported . ' questions imported successfully'
            ];
            
            debug_log("Sending response", $response);
            
            // Make sure there's no output before this
            ob_clean(); // Clear any previous output
            
            // Send JSON response
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    } catch (Exception $e) {
        if (isset($conn)) {
            $conn->rollback();
        }
        
        debug_log("Error occurred", $e->getMessage());
        
        // Clean output and send error response
        ob_clean();
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

// Invalid request method response
debug_log("Invalid request method", $_SERVER['REQUEST_METHOD']);
ob_clean();
http_response_code(400);
echo json_encode([
    'status' => 'error',
    'message' => 'Invalid request method or missing data'
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit;
?>
  