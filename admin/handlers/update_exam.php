<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

try {
    // Validate required fields
    if (empty($_POST['exam_id'])) {
        throw new Exception('Exam ID is required');
    }

    if (empty($_POST['exam_name'])) {
        throw new Exception('Exam name is required');
    }

    // Sanitize inputs
    $examId = intval($_POST['exam_id']);
    $examName = htmlspecialchars(trim($_POST['exam_name']));
    $description = htmlspecialchars(trim($_POST['description'] ?? ''));
    $duration = intval($_POST['duration']);
    $studentType = $_POST['student_type'];
    $studentYear = !empty($_POST['student_year']) ? intval($_POST['student_year']) : null;
    
    // Handle schedule status
    $status = isset($_POST['status']) ? $_POST['status'] : 'unscheduled';
    $examDate = null;
    $examTime = null;
    
    if ($status === 'scheduled') {
        if (!empty($_POST['exam_date'])) {
            $examDate = $_POST['exam_date'];
        }
        if (!empty($_POST['exam_time'])) {
            $examTime = $_POST['exam_time'];
        }
    }

    // Update exam details
    $sql = "UPDATE exams SET 
            exam_name = ?,
            description = ?,
            duration = ?,
            student_type = ?,
            student_year = ?,
            status = ?,
            exam_date = ?,
            exam_time = ?
            WHERE exam_id = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ssississi", 
        $examName,
        $description,
        $duration,
        $studentType,
        $studentYear,
        $status,
        $examDate,
        $examTime,
        $examId
    );

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?> 