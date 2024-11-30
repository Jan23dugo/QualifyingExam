<?php
include('../../config/config.php');

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Create logs directory if it doesn't exist
if (!file_exists("../../logs")) {
    mkdir("../../logs", 0777, true);
}

function logError($message, $data = null) {
    $logFile = "../../logs/import_error.log";
    $logMessage = date('Y-m-d H:i:s') . " - Error: " . $message;
    if ($data !== null) {
        $logMessage .= "\nData: " . print_r($data, true);
    }
    $logMessage .= "\n";
    error_log($logMessage, 3, $logFile);
    echo "Error: " . $message;
}

function logDebug($message, $data = null) {
    $logFile = "../../logs/import_debug.log";
    $logMessage = date('Y-m-d H:i:s') . " - Debug: " . $message;
    if ($data !== null) {
        $logMessage .= "\nData: " . print_r($data, true);
    }
    $logMessage .= "\n";
    error_log($logMessage, 3, $logFile);
}

// At the start of the file, after the existing error reporting setup
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Add this function at the top of your file
function validateQuestionType($type) {
    $valid_types = ['multiple_choice', 'true_false', 'programming', 'essay'];
    $type = trim(strtolower($type));
    
    if (!in_array($type, $valid_types)) {
        throw new Exception("Invalid question type: '$type'. Must be one of: " . implode(', ', $valid_types));
    }
    
    return $type;
}

try {
    // Log the start of import process
    logDebug("Starting import process");
    
    // Check if file was uploaded
    if (!isset($_FILES['question_file'])) {
        throw new Exception('No file uploaded');
    }

    // Log file details
    logDebug("File upload details", $_FILES['question_file']);

    // Get the uploaded file
    $file = $_FILES['question_file'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload error: ' . $file['error']);
    }

    // Get category and log it
    $category = $_POST['category'] ?? '';
    logDebug("Selected category", $category);
    if (empty($category)) {
        throw new Exception('Category is required');
    }

    // Read CSV file
    $handle = fopen($file['tmp_name'], 'r');
    if ($handle === false) {
        throw new Exception('Could not open file');
    }

    // Read headers
    $headers = fgetcsv($handle);
    logDebug("CSV Headers", $headers);
    if ($headers === false) {
        throw new Exception('Could not read CSV headers');
    }

    // Store CSV data
    $csvData = [];
    $rowNumber = 0;
    while (($row = fgetcsv($handle)) !== false) {
        $rowNumber++;
        logDebug("Reading row $rowNumber", $row);
        
        // Skip completely empty rows
        if (empty(array_filter($row))) {
            logDebug("Skipping empty row", $rowNumber);
            continue;
        }
        
        // Skip rows where first column (question_type) is empty
        if (empty(trim($row[0]))) {
            logDebug("Skipping row with empty question type", $rowNumber);
            continue;
        }
        
        $csvData[] = $row;
    }
    fclose($handle);

    if (empty($csvData)) {
        throw new Exception('No valid data rows found in CSV file');
    }

    logDebug("Total valid rows found", count($csvData));

    // Start transaction
    $conn->begin_transaction();
    logDebug("Started database transaction");

    foreach ($csvData as $rowIndex => $row) {
        // Skip empty rows
        if (empty(array_filter($row))) {
            continue;
        }

        // Ensure row has same number of elements as headers
        while (count($row) < count($headers)) {
            $row[] = ''; // Pad with empty strings if necessary
        }
        
        // Combine headers with data
        $data = array_combine($headers, $row);
        logDebug("Processing row " . ($rowIndex + 1), $data);

        // Validate and clean question type
        try {
            $data['question_type'] = validateQuestionType($data['question_type']);
        } catch (Exception $e) {
            logError("Invalid row " . ($rowIndex + 1) . ": " . $e->getMessage());
            continue; // Skip this row
        }

        // Skip rows with empty question type (likely blank rows)
        if (empty(trim($data['question_type']))) {
            logDebug("Skipping empty row", $rowIndex + 1);
            continue;
        }

        // Validate required fields
        if (empty($data['question_type'])) {
            throw new Exception("Row " . ($rowIndex + 1) . ": question_type is required");
        }
        if (empty($data['question_text'])) {
            throw new Exception("Row " . ($rowIndex + 1) . ": question_text is required");
        }

        // Insert question
        $sql = "INSERT INTO question_bank (category, question_type, question_text) VALUES (?, ?, ?)";
        logDebug("Inserting question with data", [
            'row_number' => $rowIndex + 1,
            'question_type' => $data['question_type'],
            'question_text' => $data['question_text'],
            'category' => $category,
            'data_length' => [
                'question_type_length' => strlen($data['question_type']),
                'question_text_length' => strlen($data['question_text']),
                'category_length' => strlen($category)
            ]
        ]);
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("sss", $category, $data['question_type'], $data['question_text']);
        if (!$stmt->execute()) {
            throw new Exception("Error inserting question at row " . ($rowIndex + 1) . ": " . $stmt->error);
        }

        $questionId = $conn->insert_id;
        logDebug("Question inserted with ID", $questionId);

        // Insert choices
        if ($data['question_type'] === 'multiple_choice') {
            logDebug("Processing multiple choice options for question", $questionId);
            // Insert multiple choice options
            for ($i = 1; $i <= 4; $i++) {
                if (!empty($data["choice$i"])) {
                    $isCorrect = ($i == $data['correct_choice_number']) ? 1 : 0;
                    $sql = "INSERT INTO question_bank_choices (question_id, choice_text, is_correct) 
                            VALUES (?, ?, ?)";
                    
                    logDebug("Inserting choice $i", [
                        'question_id' => $questionId,
                        'choice_text' => $data["choice$i"],
                        'is_correct' => $isCorrect
                    ]);
                    
                    $stmt = $conn->prepare($sql);
                    if (!$stmt) {
                        throw new Exception("Prepare failed for choice insert: " . $conn->error);
                    }
                    
                    $stmt->bind_param("isi", $questionId, $data["choice$i"], $isCorrect);
                    if (!$stmt->execute()) {
                        throw new Exception("Error inserting choice $i: " . $stmt->error);
                    }
                    logDebug("Choice $i inserted successfully");
                }
            }
        } 
        elseif ($data['question_type'] === 'true_false') {
            logDebug("Processing true/false options for question", $questionId);
            // Insert True/False options
            $sql = "INSERT INTO question_bank_choices (question_id, choice_text, is_correct) VALUES 
                   (?, 'True', ?), 
                   (?, 'False', ?)";
            
            $isTrue = ($data['correct_choice_number'] == 1) ? 1 : 0;
            $isFalse = ($data['correct_choice_number'] == 2) ? 1 : 0;
            
            logDebug("Inserting true/false choices", [
                'question_id' => $questionId,
                'isTrue' => $isTrue,
                'isFalse' => $isFalse
            ]);
            
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed for true/false insert: " . $conn->error);
            }
            
            $stmt->bind_param("iiii", 
                $questionId, $isTrue,
                $questionId, $isFalse
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Error inserting true/false choices: " . $stmt->error);
            }
            logDebug("True/false choices inserted successfully");
        }
        elseif ($data['question_type'] === 'programming') {
            logDebug("Processing programming question for question", $questionId);
            
            // Insert programming details
            $sql = "INSERT INTO question_bank_programming (
                question_id, 
                programming_language, 
                problem_description, 
                input_format, 
                output_format, 
                constraints,
                solution_template
            ) VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            logDebug("Inserting programming details", [
                'question_id' => $questionId,
                'programming_language' => $data['choice1'], // programming language is in choice1
                'problem_description' => $data['choice2'], // description is in choice2
                'input_format' => $data['choice3'], // input format is in choice3
                'output_format' => $data['choice4'], // output format is in choice4
                'constraints' => $data['constraints'],
                'solution_template' => $data['solution_template']
            ]);
            
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed for programming details: " . $conn->error);
            }
            
            $stmt->bind_param("issssss", 
                $questionId,
                $data['choice1'], // programming language
                $data['choice2'], // problem description
                $data['choice3'], // input format
                $data['choice4'], // output format
                $data['constraints'],
                $data['solution_template']
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Error inserting programming details: " . $stmt->error);
            }
            logDebug("Programming details inserted successfully");
            
            // Insert test cases
            $sql = "INSERT INTO question_bank_test_cases (
                question_id, 
                test_input, 
                expected_output, 
                explanation,
                is_hidden
            ) VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed for test cases: " . $conn->error);
            }
            
            // Insert visible test cases
            if (!empty($data['test_input1'])) {
                $isHidden = 0;
                $stmt->bind_param("isssi", 
                    $questionId,
                    $data['test_input1'],
                    $data['test_output1'],
                    $data['test_explanation1'],
                    $isHidden
                );
                if (!$stmt->execute()) {
                    throw new Exception("Error inserting test case 1: " . $stmt->error);
                }
                logDebug("Test case 1 inserted successfully");
            }
            
            if (!empty($data['test_input2'])) {
                $isHidden = 0;
                $stmt->bind_param("isssi", 
                    $questionId,
                    $data['test_input2'],
                    $data['test_output2'],
                    $data['test_explanation2'],
                    $isHidden
                );
                if (!$stmt->execute()) {
                    throw new Exception("Error inserting test case 2: " . $stmt->error);
                }
                logDebug("Test case 2 inserted successfully");
            }
            
            // Insert hidden test cases
            if (!empty($data['hidden_test_input1'])) {
                $isHidden = 1;
                $explanation = ''; // Hidden test cases don't need explanations
                $stmt->bind_param("isssi", 
                    $questionId,
                    $data['hidden_test_input1'],
                    $data['hidden_test_output1'],
                    $explanation,
                    $isHidden
                );
                if (!$stmt->execute()) {
                    throw new Exception("Error inserting hidden test case 1: " . $stmt->error);
                }
                logDebug("Hidden test case 1 inserted successfully");
            }
            
            if (!empty($data['hidden_test_input2'])) {
                $isHidden = 1;
                $explanation = ''; // Hidden test cases don't need explanations
                $stmt->bind_param("isssi", 
                    $questionId,
                    $data['hidden_test_input2'],
                    $data['hidden_test_output2'],
                    $explanation,
                    $isHidden
                );
                if (!$stmt->execute()) {
                    throw new Exception("Error inserting hidden test case 2: " . $stmt->error);
                }
                logDebug("Hidden test case 2 inserted successfully");
            }
        }
    }

    // Add right before the commit
    logDebug("Import summary", [
        'total_questions' => count($csvData),
        'last_question_id' => $questionId ?? 'none',
        'transaction_status' => 'ready to commit'
    ]);
    
    // Commit transaction
    $conn->commit();
    logDebug("Transaction committed successfully");

    // Clear any output buffers and send clean response
    while (ob_get_level()) {
        ob_end_clean();
    }

    // Send JSON response
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    echo json_encode([
        'status' => 'success',
        'message' => 'Questions imported successfully',
        'total_imported' => count($csvData)
    ]);
    exit();

} catch (Exception $e) {
    if (isset($conn) && $conn->ping()) {
        $conn->rollback();
    }
    
    logError("Failed to complete import", [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);

    // Clear any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }

    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
    exit();
}
  