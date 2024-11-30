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
        logDebug("Executing SQL", [
            'sql' => $sql,
            'category' => $category,
            'question_type' => $data['question_type'],
            'question_text' => $data['question_text']
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
  