<?php
require_once '../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get the POST data
$exam_id = $_POST['exam_id'] ?? null;
$exam_name = $_POST['exam_name'] ?? null;
$schedule_date = $_POST['schedule_date'] ?? null;

// Validate inputs
if (!$exam_id || !$exam_name || !$schedule_date) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    // Prepare and execute the update query
    $stmt = $conn->prepare("UPDATE exams SET exam_name = ?, schedule_date = ? WHERE exam_id = ?");
    $stmt->bind_param("ssi", $exam_name, $schedule_date, $exam_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Exam updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update exam']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

$stmt->close();
$conn->close(); 