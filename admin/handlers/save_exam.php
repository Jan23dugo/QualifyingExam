<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

try {
    // Validate required fields
    if (empty($_POST['exam_name'])) {
        throw new Exception('Exam name is required');
    }

    if (empty($_POST['duration'])) {
        throw new Exception('Duration is required');
    }

    if (empty($_POST['student_type'])) {
        throw new Exception('Student type is required');
    }

    // Sanitize inputs
    $examName = htmlspecialchars(trim($_POST['exam_name']));
    $description = htmlspecialchars(trim($_POST['description'] ?? ''));
    $duration = intval($_POST['duration']);
    $studentType = $_POST['student_type'];
    $studentYear = !empty($_POST['student_year']) ? intval($_POST['student_year']) : null;
    $folderId = !empty($_POST['folder_id']) ? intval($_POST['folder_id']) : null;
    
    // Optional schedule date
    $scheduleDate = !empty($_POST['schedule_date']) ? $_POST['schedule_date'] : null;
    $examDate = null;
    $examTime = null;
    
    if ($scheduleDate) {
        $dateTime = new DateTime($scheduleDate);
        $examDate = $dateTime->format('Y-m-d');
        $examTime = $dateTime->format('H:i:s');
    }

    // Prepare the SQL statement
    $sql = "INSERT INTO exams (exam_name, description, duration, student_type, student_year, folder_id, exam_date, exam_time, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $status = $scheduleDate ? 'scheduled' : 'unscheduled';
    
    $stmt->bind_param("ssissssss", 
        $examName,
        $description,
        $duration,
        $studentType,
        $studentYear,
        $folderId,
        $examDate,
        $examTime,
        $status
    );

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $exam_id = $conn->insert_id;
    
    // Automatically assign exam to eligible students
    require_once 'assign_exams.php';
    assignExamToStudents($exam_id);
    
    echo json_encode(['success' => true, 'exam_id' => $exam_id]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    error_log('Error creating exam: ' . $e->getMessage());
}

$conn->close(); 