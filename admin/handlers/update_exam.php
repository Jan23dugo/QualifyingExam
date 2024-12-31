<?php
require_once '../../config/config.php';
header('Content-Type: application/json');

try {
    if (!isset($_POST['exam_id'])) {
        throw new Exception('Missing exam ID');
    }

    $exam_id = (int)$_POST['exam_id'];
    $status = isset($_POST['status']) ? $_POST['status'] : 'scheduled';
    
    // Initialize variables
    $exam_date = null;
    $exam_time = null;
    $exam_name = null;
    $description = null;
    $duration = null;

    // Start transaction
    $conn->begin_transaction();

    if ($status === 'scheduled') {
        // Handle date and time
        if (isset($_POST['exam_date']) && isset($_POST['exam_time'])) {
            $exam_date = $_POST['exam_date'];
            $exam_time = $_POST['exam_time'];

            // Try to parse and standardize the date format
            $dateObj = date_create_from_format('Y-m-d', $exam_date);
            if (!$dateObj) {
                // Try alternate format (m/d/Y)
                $dateObj = date_create_from_format('m/d/Y', $exam_date);
            }
            
            if (!$dateObj) {
                throw new Exception('Invalid date format');
            }
            
            // Standardize date format
            $exam_date = $dateObj->format('Y-m-d');

            // Validate and format time
            if (strtotime($exam_time) === false) {
                throw new Exception('Invalid time format');
            }
            // Standardize time format to 24-hour
            $exam_time = date('H:i', strtotime($exam_time));
        }

        // Basic update query for calendar drag-and-drop
        $sql = "UPDATE exams SET exam_date = ?, exam_time = ?, status = ? WHERE exam_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $exam_date, $exam_time, $status, $exam_id);
    }

    // If additional exam details are provided (from create-exam.php)
    if (isset($_POST['exam_name'])) {
        $exam_name = trim($_POST['exam_name']);
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $duration = isset($_POST['duration']) ? (int)$_POST['duration'] : 0;

        if (empty($exam_name)) {
            throw new Exception('Exam name is required');
        }

        if ($duration <= 0) {
            throw new Exception('Duration must be greater than 0');
        }

        // Full update query for create-exam.php
        $sql = "UPDATE exams SET 
                exam_name = ?, 
                description = ?, 
                duration = ?,
                status = ?,
                exam_date = ?,
                exam_time = ?,
                student_type = ?,
                student_year = ?
                WHERE exam_id = ?";
        
        $stmt = $conn->prepare($sql);
        $student_type = $_POST['student_type'];
        $student_year = $_POST['student_year'] ?: null;

        $stmt->bind_param("ssisssssi", 
            $exam_name, 
            $description, 
            $duration, 
            $status,
            $exam_date,
            $exam_time,
            $student_type,
            $student_year,
            $exam_id
        );

        // Update student types and year levels
        if (isset($_POST['student_type']) || isset($_POST['year_level'])) {
            error_log("Updating student types and year levels");
            error_log("Student types: " . print_r($_POST['student_type'], true));
            error_log("Year levels: " . print_r($_POST['year_level'], true));
            
            // First delete existing entries
            $delete_sql = "DELETE FROM exam_students WHERE exam_id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("i", $exam_id);
            $delete_stmt->execute();

            // Insert new entries
            if (isset($_POST['student_type']) && is_array($_POST['student_type'])) {
                $insert_sql = "INSERT INTO exam_students (exam_id, student_type, year_level) VALUES (?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_sql);

                foreach ($_POST['student_type'] as $type) {
                    if (isset($_POST['year_level']) && is_array($_POST['year_level'])) {
                        foreach ($_POST['year_level'] as $year) {
                            $insert_stmt->bind_param("iss", $exam_id, $type, $year);
                            if (!$insert_stmt->execute()) {
                                throw new Exception('Failed to update student types and year levels');
                            }
                        }
                    }
                }
            }
        }
    }

    if (!$stmt->execute()) {
        throw new Exception('Failed to update exam: ' . $conn->error);
    }

    $conn->commit();
    echo json_encode([
        'success' => true,
        'message' => 'Exam updated successfully'
    ]);

} catch (Exception $e) {
    if ($conn->connect_errno) {
        $conn->rollback();
    }
    error_log("Error updating exam: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 