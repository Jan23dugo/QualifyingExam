<?php
require_once '../../config/config.php';
header('Content-Type: application/json');

try {
    if (!isset($_GET['exam_id'])) {
        throw new Exception('Exam ID is required');
    }

    $exam_id = (int)$_GET['exam_id'];
    
    // Update the query to match your actual database columns
    $sql = "SELECT 
            exam_id,
            exam_name,
            description,
            duration,
            status,
            exam_date,
            exam_time,
            student_type,
            student_year,
            folder_id
            FROM exams 
            WHERE exam_id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $exam_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $exam = $result->fetch_assoc();

    if (!$exam) {
        throw new Exception('Exam not found');
    }

    // Format dates for the response
    if ($exam['exam_date']) {
        $exam['exam_date'] = date('Y-m-d', strtotime($exam['exam_date']));
    }
    if ($exam['exam_time']) {
        $exam['exam_time'] = date('H:i', strtotime($exam['exam_time']));
    }

    // Debug log
    error_log("Fetched exam data: " . print_r($exam, true));

    echo json_encode([
        'success' => true,
        'exam' => $exam
    ]);

} catch (Exception $e) {
    error_log("Error fetching exam: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 