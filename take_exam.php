<?php
session_start();
require_once 'config/config.php';

// Initialize error message variable
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reference_id = trim($_POST['reference_id'] ?? '');
    
    if (empty($reference_id)) {
        $error_message = 'Please enter your reference ID.';
    } else {
        try {
            // First get the student using their reference_id
            $query = "SELECT ea.assignment_id, ea.exam_id, ea.student_id, ea.assigned_date,
                            e.exam_name, e.duration, s.reference_id
                     FROM exam_assignments ea 
                     JOIN exams e ON ea.exam_id = e.exam_id
                     JOIN students s ON ea.student_id = s.student_id
                     WHERE s.reference_id = ?";
            
            $stmt = $conn->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("s", $reference_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            
            // Debug information
            echo "<!-- Query executed: " . str_replace('?', $reference_id, $query) . " -->";
            echo "<!-- Number of rows found: " . $result->num_rows . " -->";
            
            if ($result->num_rows > 0) {
                $exam_data = $result->fetch_assoc();
                
                // Debug information
                echo "<!-- Found exam data: " . print_r($exam_data, true) . " -->";
                
                // Store exam data in session
                $_SESSION['assignment_id'] = $exam_data['assignment_id'];
                $_SESSION['exam_id'] = $exam_data['exam_id'];
                $_SESSION['student_id'] = $exam_data['student_id'];
                $_SESSION['exam_name'] = $exam_data['exam_name'];
                $_SESSION['reference_id'] = $exam_data['reference_id'];
                
                // Redirect to start exam page
                header('Location: start_exam.php');
                exit;
            } else {
                $error_message = 'Invalid reference ID or no exam assigned. Please check and try again.';
            }
            $stmt->close();
            
        } catch (Exception $e) {
            $error_message = "An error occurred: " . $e->getMessage();
            // Debug information
            echo "<!-- Error details: " . $e->getMessage() . " -->";
        }
    }
}

// Debug: Show what's in the database
try {
    $debug_query = "SELECT s.reference_id, ea.* 
                    FROM students s 
                    LEFT JOIN exam_assignments ea ON s.student_id = ea.student_id 
                    LIMIT 5";
    $debug_result = $conn->query($debug_query);
    echo "<!-- Available assignments in database: \n";
    while ($row = $debug_result->fetch_assoc()) {
        echo print_r($row, true) . "\n";
    }
    echo "-->";
} catch (Exception $e) {
    echo "<!-- Debug query failed: " . $e->getMessage() . " -->";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Exam - Enter Reference ID</title>
    <link rel="stylesheet" href="assets/css/exam.css">
</head>
<body>
    <div class="exam-container">
        <div class="reference-form">
            <h1>Enter Reference ID</h1>
            <p class="instruction">Please enter the reference ID provided to you by your instructor.</p>
            
            <?php if ($error_message): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <input type="text" 
                           name="reference_id" 
                           id="reference_id" 
                           placeholder="Enter your Reference ID"
                           value="<?php echo htmlspecialchars($_POST['reference_id'] ?? ''); ?>"
                           autocomplete="off"
                           required>
                </div>
                <button type="submit" class="submit-btn">Submit</button>
            </form>
        </div>
    </div>
</body>
</html>
