<?php
session_start();
require_once 'config/config.php';

// Check if user is authorized
if (!isset($_SESSION['reference_id']) || !isset($_SESSION['exam_id'])) {
    header('Location: take_exam.php');
    exit;
}

// Fetch exam details
$stmt = $conn->prepare("SELECT e.*, ea.assigned_date 
                       FROM exams e 
                       JOIN exam_assignments ea ON e.exam_id = ea.exam_id 
                       WHERE e.exam_id = ? AND ea.student_id = ?");
$stmt->bind_param("ii", $_SESSION['exam_id'], $_SESSION['student_id']);
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();

if (!$exam) {
    header('Location: take_exam.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($exam['exam_name']); ?> - Start Exam</title>
    <link rel="stylesheet" href="assets/css/exam.css">
</head>
<body>
    <div class="exam-container">
        <div class="exam-start-card">
            <h1><?php echo htmlspecialchars($exam['exam_name']); ?></h1>
            
            <div class="exam-details">
                <p><strong>Duration:</strong> <?php echo htmlspecialchars($exam['duration']); ?> minutes</p>
                <p><strong>Total Questions:</strong> <?php echo htmlspecialchars($exam['total_questions'] ?? 'N/A'); ?></p>
                <p><strong>Assigned Date:</strong> <?php echo date('F j, Y g:i A', strtotime($exam['assigned_date'])); ?></p>
            </div>

            <div class="exam-instructions">
                <h2>Instructions:</h2>
                <div class="instruction-content">
                    <?php echo nl2br(htmlspecialchars($exam['instructions'] ?? 'Please read all questions carefully before answering.')); ?>
                </div>
            </div>

            <div class="warning-text">
                <p>⚠️ Once you start the exam, the timer will begin and cannot be paused.</p>
                <p>⚠️ Ensure you have a stable internet connection before proceeding.</p>
            </div>

            <form action="exam.php" method="POST">
                <input type="hidden" name="start_exam" value="1">
                <button type="submit" class="start-exam-btn">Start Exam Now</button>
            </form>
        </div>
    </div>
</body>
</html> 