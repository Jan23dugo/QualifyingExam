<?php
require_once '../config/config.php';

header('Content-Type: application/json');

try {
    // Get form data
    $examName = $_POST['exam_name'];
    $description = $_POST['description'];
    $duration = $_POST['duration'];
    $scheduleDate = $_POST['schedule_date'];
    $folderId = ($_POST['folder_id'] === '0') ? NULL : (int)$_POST['folder_id'];
    $studentType = isset($_POST['student_type']) ? $_POST['student_type'] : null;
    $studentYear = isset($_POST['student_year']) ? $_POST['student_year'] : null;

    // Start transaction
    $conn->begin_transaction();

    // Insert exam details
    $stmt = $conn->prepare("INSERT INTO exams (exam_name, description, duration, schedule_date, folder_id, student_type, student_year) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssissss", $examName, $description, $duration, $scheduleDate, $folderId, $studentType, $studentYear);
    $stmt->execute();
    $examId = $conn->insert_id;

    // Get eligible students based on type and year
    if ($studentType && $studentYear) {
        $query = "SELECT student_id FROM students WHERE is_tech = ? AND YEAR(registration_date) = ?";
        $stmt = $conn->prepare($query);
        $isTech = ($studentType === 'tech') ? 1 : 0;
        $stmt->bind_param("is", $isTech, $studentYear);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $assignStmt = $conn->prepare("INSERT INTO exam_assignments (exam_id, student_id) VALUES (?, ?)");
            while ($student = $result->fetch_assoc()) {
                $assignStmt->bind_param("ii", $examId, $student['student_id']);
                $assignStmt->execute();
            }
        }
    }

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Exam created and assigned successfully',
        'exam_id' => $examId
    ]);

} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
