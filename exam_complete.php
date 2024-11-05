<?php
session_start();
include_once 'config/config.php';

if (!isset($_SESSION['student_id']) || !isset($_GET['exam_id'])) {
    header("Location: take_exam.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$exam_id = $_GET['exam_id'];

// Fetch exam results
$stmt = $conn->prepare("
    SELECT 
        er.*,
        e.exam_name,
        e.duration,
        s.first_name,
        s.last_name,
        s.reference_id
    FROM exam_results er
    JOIN exams e ON er.exam_id = e.exam_id
    JOIN students s ON er.student_id = s.student_id
    WHERE er.exam_id = ? AND er.student_id = ?
");
$stmt->bind_param("ii", $exam_id, $student_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if (!$result) {
    header("Location: take_exam.php");
    exit();
}

// Calculate percentage
$percentage = ($result['score'] / $result['total_points']) * 100;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Complete</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/styles.min.css">
    <style>
        .completion-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .score-circle {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: #f8f9fa;
            margin: 20px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            border: 8px solid #6200ea;
        }
        
        .score-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #6200ea;
            line-height: 1;
        }
        
        .score-label {
            font-size: 0.9em;
            color: #666;
        }
        
        .details-table {
            width: 100%;
            margin: 20px 0;
            text-align: left;
        }
        
        .details-table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
        
        .details-table td:first-child {
            color: #666;
            width: 40%;
        }
        
        .completion-message {
            margin: 20px 0;
            padding: 15px;
            border-radius: 5px;
        }
        
        .success {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .warning {
            background-color: #fff3e0;
            color: #ef6c00;
        }
    </style>
</head>
<body>
    <div class="completion-container">
        <h2>Exam Completed!</h2>
        
        <div class="score-circle">
            <div class="score-number"><?php echo number_format($percentage, 1); ?>%</div>
            <div class="score-label">Score</div>
        </div>
        
        <div class="completion-message <?php echo $percentage >= 85 ? 'success' : 'warning'; ?>">
            <?php if ($percentage >= 85): ?>
                Congratulations! You have passed the exam.
            <?php else: ?>
                Thank you for completing the exam.
            <?php endif; ?>
        </div>
        
        <table class="details-table">
            <tr>
                <td>Student Name:</td>
                <td><?php echo htmlspecialchars($result['first_name'] . ' ' . $result['last_name']); ?></td>
            </tr>
            <tr>
                <td>Reference ID:</td>
                <td><?php echo htmlspecialchars($result['reference_id']); ?></td>
            </tr>
            <tr>
                <td>Exam:</td>
                <td><?php echo htmlspecialchars($result['exam_name']); ?></td>
            </tr>
            <tr>
                <td>Score:</td>
                <td><?php echo $result['score'] . ' out of ' . $result['total_points']; ?></td>
            </tr>
            <tr>
                <td>Completion Time:</td>
                <td><?php echo $result['completion_time']; ?></td>
            </tr>
            <tr>
                <td>Submission Date:</td>
                <td><?php echo date('F j, Y g:i A', strtotime($result['submission_date'])); ?></td>
            </tr>
        </table>
        
        <div class="mt-4">
            <a href="take_exam.php" class="btn btn-primary">Back to Exams</a>
            <form method="POST" action="logout.php" class="d-inline">
                <button type="submit" class="btn btn-secondary">Exit</button>
            </form>
        </div>
    </div>
    
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
</body>
</html> 