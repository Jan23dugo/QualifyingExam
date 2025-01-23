<?php
require_once '../../config/config.php';

// Enable detailed error reporting and set custom log file
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', dirname(__DIR__) . '/logs/error.log');

// Add a timestamp to our logs
error_log("\n\n" . date('[Y-m-d H:i:s] ') . "=== Starting new exam creation process ===");

header('Content-Type: application/json');

try {
    // Debug: Log incoming data
    error_log(date('[Y-m-d H:i:s] ') . "Received POST data: " . print_r($_POST, true));

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
    
    // Debug: Log processed inputs
    error_log(date('[Y-m-d H:i:s] ') . "Processed inputs: " . print_r([
        'examName' => $examName,
        'description' => $description,
        'duration' => $duration,
        'studentType' => $studentType,
        'studentYear' => $studentYear,
        'folderId' => $folderId
    ], true));
    
    // Optional schedule date
    $scheduleDate = !empty($_POST['schedule_date']) ? $_POST['schedule_date'] : null;
    $examDate = null;
    $examTime = null;
    
    if ($scheduleDate) {
        $dateTime = new DateTime($scheduleDate);
        $examDate = $dateTime->format('Y-m-d');
        $examTime = $dateTime->format('H:i:s');
    }

    // Debug: Show the actual SQL query being prepared
    $sql = "INSERT INTO exams (exam_name, description, duration, student_type, student_year, folder_id, exam_date, exam_time, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    error_log(date('[Y-m-d H:i:s] ') . "SQL Query: " . $sql);

    // Debug: Show table structure
    $result = $conn->query("DESCRIBE exams");
    if ($result) {
        $table_structure = [];
        while ($row = $result->fetch_assoc()) {
            $table_structure[] = $row;
        }
        error_log(date('[Y-m-d H:i:s] ') . "Table structure: " . print_r($table_structure, true));
    }
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $status = $scheduleDate ? 'scheduled' : 'unscheduled';
    
    // Debug: Log bind parameters
    error_log("Binding parameters with values: " . print_r([
        'examName' => $examName,
        'description' => $description,
        'duration' => $duration,
        'studentType' => $studentType,
        'studentYear' => $studentYear,
        'folderId' => $folderId,
        'examDate' => $examDate,
        'examTime' => $examTime,
        'status' => $status
    ], true));

    $stmt->bind_param("ssississs", 
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
        throw new Exception("Execute failed: " . $stmt->error . " (Error #" . $stmt->errno . ")");
    }

    $exam_id = $conn->insert_id;
    
    // Debug: Log successful insertion
    error_log("Successfully inserted exam with ID: " . $exam_id);
    
    // Automatically assign exam to eligible students
    require_once 'assign_exams.php';
    assignExamToStudents($exam_id);
    
    echo json_encode(['success' => true, 'exam_id' => $exam_id]);

} catch (Exception $e) {
    error_log(date('[Y-m-d H:i:s] ') . "Error in save_exam.php: " . $e->getMessage());
    error_log(date('[Y-m-d H:i:s] ') . "Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug_info' => [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}

$conn->close(); 