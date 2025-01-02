<?php
require_once '../../config/config.php';
header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug log the incoming request
error_log("Received POST data: " . print_r($_POST, true));

try {
    if (!isset($_POST['exam_id'])) {
        throw new Exception('Missing exam ID');
    }

    $exam_id = (int)$_POST['exam_id'];
    $status = isset($_POST['status']) ? $_POST['status'] : 'scheduled';
    
    // Debug log the processed values
    error_log("Processing exam update: " . json_encode([
        'exam_id' => $exam_id,
        'status' => $status,
        'exam_date' => $_POST['exam_date'] ?? null,
        'exam_time' => $_POST['exam_time'] ?? null
    ]));

    // Start transaction
    $conn->begin_transaction();

    if ($status === 'scheduled') {
        // Handle scheduled exam
        if (isset($_POST['exam_date']) && isset($_POST['exam_time'])) {
            $exam_date = $_POST['exam_date'];
            $exam_time = $_POST['exam_time'];

            // Debug log the date/time processing
            error_log("Processing date/time: Date=$exam_date, Time=$exam_time");

            // Validate date format
            $dateObj = date_create_from_format('Y-m-d', $exam_date);
            if (!$dateObj) {
                error_log("Date parsing failed. Errors: " . print_r(date_get_last_errors(), true));
                throw new Exception("Invalid date format: $exam_date");
            }

            // Validate time format
            if (strtotime($exam_time) === false) {
                error_log("Time parsing failed for: $exam_time");
                throw new Exception("Invalid time format: $exam_time");
            }

            $exam_date = $dateObj->format('Y-m-d');
            $exam_time = date('H:i:s', strtotime($exam_time));
        }
    } else {
        // Handle unscheduled exam
        $exam_date = null;
        $exam_time = null;
    }

    // Update query - now handles both scheduled and unscheduled cases
    $sql = "UPDATE exams SET exam_date = ?, exam_time = ?, status = ? WHERE exam_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $exam_date, $exam_time, $status, $exam_id);
    
    if (!$stmt->execute()) {
        error_log("SQL Error: " . $conn->error);
        throw new Exception("Database update failed: " . $conn->error);
    }

    $conn->commit();
    
    // Debug log the successful update
    error_log("Exam updated successfully. Status: $status, Date: " . ($exam_date ?? 'null') . ", Time: " . ($exam_time ?? 'null'));
    
    echo json_encode([
        'success' => true,
        'message' => 'Exam updated successfully',
        'debug_info' => [
            'status' => $status,
            'exam_date' => $exam_date,
            'exam_time' => $exam_time
        ]
    ]);

} catch (Exception $e) {
    error_log("Error in update_exam.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    if ($conn->connect_errno) {
        $conn->rollback();
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug_info' => [
            'post_data' => $_POST,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
}
?> 