<?php
require_once '../config/config.php';
session_start();

if (!isset($_SESSION['loggedin']) || !isset($_SESSION['user_id'])) {
    header('Location: loginAdmin.php');
    exit();
}

// Get exam ID from URL
$exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : 0;

// Get exam details
$exam_query = "SELECT e.*, 
    COUNT(DISTINCT ea.student_id) as total_students,
    COUNT(DISTINCT er.student_id) as completed_exams,
    SUM(CASE WHEN (er.score / er.total_questions * 100) >= 75 THEN 1 ELSE 0 END) as passed_count,
    ROUND(AVG(er.score / er.total_questions * 100), 2) as average_score,
    CASE 
        WHEN e.status = 'scheduled' AND NOW() < CONCAT(e.exam_date, ' ', e.exam_time) THEN 'upcoming'
        WHEN e.status = 'scheduled' AND NOW() > DATE_ADD(CONCAT(e.exam_date, ' ', e.exam_time), INTERVAL e.duration MINUTE) THEN 'completed'
        WHEN e.status = 'scheduled' THEN 'in_progress'
        ELSE e.status 
    END as current_status
    FROM exams e
    LEFT JOIN exam_assignments ea ON e.exam_id = ea.exam_id
    LEFT JOIN exam_results er ON e.exam_id = er.exam_id
    WHERE e.exam_id = ?
    GROUP BY e.exam_id";

$stmt = $conn->prepare($exam_query);
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();

if (!$exam) {
    die("Exam not found");
}

// Get detailed results
$results_query = "SELECT 
    er.result_id,
    er.student_id,
    s.first_name,
    s.last_name,
    s.student_type,
    er.score,
    er.total_questions,
    er.created_at as submission_date,
    ROUND((er.score / er.total_questions) * 100, 2) as percentage
    FROM exam_results er
    JOIN students s ON er.student_id = s.student_id
    WHERE er.exam_id = ?
    ORDER BY er.created_at DESC";

$stmt = $conn->prepare($results_query);
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$results = $stmt->get_result();

// Function to format schedule
function formatSchedule($date, $time) {
    if (empty($date) || empty($time)) {
        return "Not Scheduled";
    }
    return date('M d, Y', strtotime($date)) . ' at ' . date('h:i A', strtotime($time));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Results - <?php echo htmlspecialchars($exam['exam_name']); ?></title>
    
    <!-- Include your existing CSS -->
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fonts/fontawesome-all.min.css">
    <link rel="stylesheet" href="assets/css/styles.min.css">
    
    <style>
        .exam-info {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .stat-card h5 {
            color: #4e73df;
            margin-bottom: 10px;
        }
        
        .results-table {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .badge-status {
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: normal;
        }
        
        .status-passed {
            background-color: #1cc88a;
            color: white;
        }
        
        .status-failed {
            background-color: #e74a3b;
            color: white;
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'topbar.php'; ?>
                
                <div class="container-fluid">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <?php echo htmlspecialchars($exam['exam_name']); ?> Results
                        </h1>
                        <a href="result.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Results
                        </a>
                    </div>
                    
                    <!-- Exam Information -->
                    <div class="exam-info">
                        <div class="row">
                            <div class="col-md-4">
                                <h5 class="mb-3">Exam Details</h5>
                                <p><strong>Schedule:</strong> <?php echo formatSchedule($exam['exam_date'], $exam['exam_time']); ?></p>
                                <p><strong>Duration:</strong> <?php echo $exam['duration']; ?> minutes</p>
                                <p><strong>Status:</strong> 
                                    <?php
                                    $statusClass = '';
                                    $statusText = '';
                                    switch($exam['current_status']) {
                                        case 'upcoming':
                                            $statusClass = 'info';
                                            $statusText = 'Upcoming';
                                            break;
                                        case 'in_progress':
                                            $statusClass = 'warning';
                                            $statusText = 'In Progress';
                                            break;
                                        case 'completed':
                                            $statusClass = 'success';
                                            $statusText = 'Completed';
                                            break;
                                        default:
                                            $statusClass = 'secondary';
                                            $statusText = ucfirst($exam['current_status']);
                                    }
                                    ?>
                                    <span class="badge bg-<?php echo $statusClass; ?>">
                                        <?php echo $statusText; ?>
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-4">
                                <h5 class="mb-3">Participation</h5>
                                <p><strong>Total Students:</strong> <?php echo $exam['total_students']; ?></p>
                                <p><strong>Completed Exams:</strong> <?php echo $exam['completed_exams']; ?></p>
                                <p><strong>Completion Rate:</strong> 
                                    <?php 
                                    echo $exam['total_students'] > 0 
                                        ? round(($exam['completed_exams'] / $exam['total_students']) * 100, 1) . '%'
                                        : '0%';
                                    ?>
                                </p>
                            </div>
                            <div class="col-md-4">
                                <h5 class="mb-3">Results Summary</h5>
                                <p><strong>Average Score:</strong> 
                                    <?php echo $exam['average_score'] ? $exam['average_score'] . '%' : 'N/A'; ?>
                                </p>
                                <p><strong>Passed:</strong> 
                                    <?php echo $exam['passed_count']; ?> 
                                    (<?php echo $exam['completed_exams'] > 0 
                                        ? round(($exam['passed_count'] / $exam['completed_exams']) * 100, 1) 
                                        : 0; ?>%)
                                </p>
                                <p><strong>Failed:</strong> 
                                    <?php echo $exam['completed_exams'] - $exam['passed_count']; ?>
                                    (<?php echo $exam['completed_exams'] > 0 
                                        ? round((($exam['completed_exams'] - $exam['passed_count']) / $exam['completed_exams']) * 100, 1) 
                                        : 0; ?>%)
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Add a warning message for in-progress exams -->
                    <?php if ($exam['current_status'] === 'in_progress'): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-clock"></i> This exam is currently in progress. Results shown may be incomplete.
                        </div>
                    <?php endif; ?>
                    
                    <!-- Results Table -->
                    <div class="results-table">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="resultsTable">
                                <thead>
                                    <tr>
                                        <th>Student Name</th>
                                        <th>Student Type</th>
                                        <th>Score</th>
                                        <th>Percentage</th>
                                        <th>Status</th>
                                        <th>Submission Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $results->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                            <td><?php echo ucfirst($row['student_type']); ?></td>
                                            <td><?php echo $row['score'] . '/' . $row['total_questions']; ?></td>
                                            <td><?php echo $row['percentage'] . '%'; ?></td>
                                            <td>
                                                <span class="badge badge-status <?php echo $row['percentage'] >= 75 ? 'status-passed' : 'status-failed'; ?>">
                                                    <?php echo $row['percentage'] >= 75 ? 'Passed' : 'Failed'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y h:i A', strtotime($row['submission_date'])); ?></td>
                                            <td>
                                                <a href="view_student_answers.php?result_id=<?php echo $row['result_id']; ?>" 
                                                   class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i> View Answers
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php include 'footer.php'; ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script src="assets/js/script.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#resultsTable').DataTable({
                "pageLength": 25,
                "order": [[5, "desc"]], // Sort by submission date by default
                "language": {
                    "lengthMenu": "Show _MENU_ entries per page",
                    "zeroRecords": "No results found",
                    "info": "Showing page _PAGE_ of _PAGES_",
                    "infoEmpty": "No records available",
                    "infoFiltered": "(filtered from _MAX_ total records)"
                }
            });
        });
    </script>
</body>
</html> 