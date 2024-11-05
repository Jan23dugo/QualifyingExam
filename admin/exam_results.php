<?php
include_once __DIR__ . '/../config/config.php';

// Get exam_id from query string
$exam_id = $_GET['exam_id'] ?? null;

if (!$exam_id) {
    die("Exam ID is required.");
}

// Fetch exam details
$stmt = $conn->prepare("SELECT exam_name FROM exams WHERE exam_id = ?");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();

// Fetch exam results with student details
$results_query = "
    SELECT 
        s.student_id,
        s.first_name,
        s.last_name,
        s.reference_id,
        CASE 
            WHEN s.is_tech = 1 THEN 'Tech Track'
            ELSE 'Non-Tech Track'
        END as track_name,
        er.score,
        er.total_points,
        er.completion_time,
        er.status,
        er.start_time,
        er.end_time
    FROM exam_results er
    JOIN students s ON er.student_id = s.student_id
    WHERE er.exam_id = ?
    ORDER BY er.score DESC";

$results_stmt = $conn->prepare($results_query);
$results_stmt->bind_param("i", $exam_id);
$results_stmt->execute();
$results = $results_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Results</title>
    
    <!-- Include your existing stylesheets -->
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/styles.min.css">
    
    <style>
        .results-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            margin-top: 20px;
        }
        
        .table thead th {
            background-color: #6200ea;
            color: white;
            border-bottom: none;
        }
        
        .status-completed {
            color: #28a745;
        }
        
        .status-pending {
            color: #ffc107;
        }
        
        .status-failed {
            color: #dc3545;
        }
        
        .score-cell {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div id="wrapper">
        <!-- Include sidebar -->
        <?php include 'sidebar.php'; ?>
        
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- Include navbar -->
                <?php include 'topbar.php'; ?>
                
                <div class="container-fluid">
                    <h2 class="mb-4">Exam Results: <?php echo htmlspecialchars($exam['exam_name']); ?></h2>
                    
                    <div class="tab-menu" style="margin-bottom: 20px;">
                        <a href="create-exam.php?exam_id=<?php echo $exam_id; ?>" class="btn btn-secondary">Back to Exam Creation</a>
                        <a href="test2.php?exam_id=<?php echo $exam_id; ?>" class="btn btn-primary">Questions</a>
                        <a href="preview_exam.php?exam_id=<?php echo $exam_id; ?>" class="btn btn-primary">Preview</a>
                        <a href="exam_settings.php?exam_id=<?php echo $exam_id; ?>" class="btn btn-primary">Settings</a>
                        <a href="assign_exam.php?exam_id=<?php echo $exam_id; ?>" class="btn btn-primary">Assign</a>
                        <button class="btn btn-primary active">Results</button>
                    </div>
                    
                    <div class="results-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Name</th>
                                    <th>Track</th>
                                    <th>Score</th>
                                    <th>Percentage</th>
                                    <th>Completion Time</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results as $result): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($result['student_id']); ?></td>
                                        <td><?php echo htmlspecialchars($result['first_name'] . ' ' . $result['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($result['track_name']); ?></td>
                                        <td class="score-cell">
                                            <?php echo $result['score'] . ' / ' . $result['total_points']; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $percentage = ($result['score'] / $result['total_points']) * 100;
                                            echo number_format($percentage, 1) . '%';
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                            echo $result['completion_time'] ? 
                                                date('H:i:s', strtotime($result['completion_time'])) : 
                                                'N/A';
                                            ?>
                                        </td>
                                        <td class="<?php echo 'status-' . strtolower($result['status']); ?>">
                                            <?php echo htmlspecialchars($result['status']); ?>
                                        </td>
                                        <td>
                                            <a href="view_student_answers.php?exam_id=<?php echo $exam_id; ?>&student_id=<?php echo $result['student_id']; ?>" 
                                               class="btn btn-sm btn-primary">
                                                View Details
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <?php if (empty($results)): ?>
                            <div class="alert alert-info">No results found for this exam.</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mt-3">

                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 