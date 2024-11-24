<?php
include('../../config/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate file upload
        if (!isset($_FILES['question_file']) || $_FILES['question_file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload failed');
        }

        // Get category
        $category = isset($_POST['new_category']) && !empty($_POST['new_category']) 
            ? $_POST['new_category'] 
            : $_POST['category'];

        // Check file type
        $file_type = strtolower(pathinfo($_FILES['question_file']['name'], PATHINFO_EXTENSION));
        if ($file_type != 'csv') {
            throw new Exception('Only CSV files are allowed');
        }

        // Open uploaded CSV file
        if (($handle = fopen($_FILES['question_file']['tmp_name'], "r")) !== FALSE) {
            // Start transaction
            $conn->begin_transaction();

            // Prepare insert statement
            $sql = "INSERT INTO question_bank (category, question_type, question_text, options, correct_answer) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            // Skip header row
            fgetcsv($handle);

            // Process each row
            while (($data = fgetcsv($handle)) !== FALSE) {
                // Skip empty rows
                if (empty($data[1])) continue;

                $question_type = $data[0];
                $question_text = $data[1];
                $options = $data[2];
                $correct_answer = $data[3];

                // Format options for multiple choice questions
                if ($question_type === 'multiple_choice' && !empty($options)) {
                    $options = json_encode(explode('|', $options));
                }

                $stmt->bind_param("sssss", $category, $question_type, $question_text, $options, $correct_answer);
                if (!$stmt->execute()) {
                    throw new Exception('Failed to import question');
                }
            }

            fclose($handle);
            $conn->commit();
            echo json_encode(['status' => 'success']);
        } else {
            throw new Exception('Failed to open file');
        }

    } catch (Exception $e) {
        if (isset($conn)) $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} 