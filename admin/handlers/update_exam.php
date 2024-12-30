<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

try {
    // Validate input
    if (!isset($_POST['exam_id']) || !isset($_POST['exam_date']) || !isset($_POST['exam_time'])) {
        throw new Exception('Missing required fields');
    }

    $exam_id = intval($_POST['exam_id']);
    $exam_date = $_POST['exam_date'];
    $exam_time = $_POST['exam_time'];
    
    // Validate date and time format
    if (!strtotime($exam_date) || !strtotime($exam_time)) {
        throw new Exception('Invalid date or time format');
    }

    // Prepare and execute the update query
    $stmt = $conn->prepare("UPDATE exams SET exam_date = ?, exam_time = ?, status = 'scheduled' WHERE exam_id = ?");
    $stmt->bind_param("ssi", $exam_date, $exam_time, $exam_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Exam schedule updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update exam schedule');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?> 