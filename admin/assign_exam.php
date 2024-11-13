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

// Handle form submission for assigning exam
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['student_ids'])) {
        $student_ids = $_POST['student_ids'];
        
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            // First delete existing assignments for this exam
            $delete_stmt = $conn->prepare("DELETE FROM exam_assignments WHERE exam_id = ?");
            $delete_stmt->bind_param("i", $exam_id);
            $delete_stmt->execute();
            
            // Insert new assignments and create initial exam_results entries
            $insert_stmt = $conn->prepare("INSERT INTO exam_assignments (exam_id, student_id) VALUES (?, ?)");
            $result_stmt = $conn->prepare("INSERT INTO exam_results (exam_id, student_id, total_points, status) 
                                         SELECT ?, ?, (SELECT SUM(points) FROM questions WHERE exam_id = ?), 'Pending'");
            
            foreach ($student_ids as $student_id) {
                // Insert assignment
                $insert_stmt->bind_param("ii", $exam_id, $student_id);
                $insert_stmt->execute();
                
                // Create initial exam result entry
                $result_stmt->bind_param("iii", $exam_id, $student_id, $exam_id);
                $result_stmt->execute();
            }
            
            $conn->commit();
            $success_message = "Exam assigned successfully!";
        } catch (Exception $e) {
            $conn->rollback();
            $error_message = "Error assigning exam: " . $e->getMessage();
        }
    }
}

// Pagination settings
$students_per_page = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $students_per_page;

// Get total number of students
$total_query = "SELECT COUNT(*) as total FROM students";
$total_result = $conn->query($total_query);
$total_students = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_students / $students_per_page);

// Modify student query to include pagination
$student_query = "
    SELECT 
        s.student_id,
        s.first_name,
        s.last_name,
        s.reference_id,
        s.is_tech,
        CASE 
            WHEN s.is_tech = 1 THEN 'Tech'
            ELSE 'Non-Tech'
        END as track_name
    FROM students s 
    ORDER BY s.last_name
    LIMIT ? OFFSET ?";

$stmt = $conn->prepare($student_query);
$stmt->bind_param("ii", $students_per_page, $offset);
$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch currently assigned students
$assigned_query = "SELECT student_id FROM exam_assignments WHERE exam_id = ?";
$assigned_stmt = $conn->prepare($assigned_query);
$assigned_stmt->bind_param("i", $exam_id);
$assigned_stmt->execute();
$assigned_result = $assigned_stmt->get_result();
$assigned_students = array_column($assigned_result->fetch_all(MYSQLI_ASSOC), 'student_id');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Exam</title>
    
    <!-- Include your existing stylesheets -->
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900&display=swap">
  <link rel="stylesheet" href="assets/fonts/fontawesome-all.min.css">
  <link rel="stylesheet" href="assets/css/styles.min.css">
    
    <style>
        .filter-section {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .student-table {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .table thead th {
            background-color: #6200ea;
            color: white;
            border-bottom: none;
        }
        
        .assign-button {
            margin-top: 20px;
        }
        
        .alert {
            margin-top: 20px;
        }
        
        .pagination-container {
            margin-top: 20px;
            border-top: 1px solid #dee2e6;
        }
        
        .pagination {
            margin-bottom: 0;
        }
        
        .page-link {
            color: #6200ea;
            border-color: #dee2e6;
        }
        
        .page-item.active .page-link {
            background-color: #6200ea;
            border-color: #6200ea;
        }
        
        .page-link:hover {
            color: #3700b3;
            background-color: #e9ecef;
            border-color: #dee2e6;
        }
        
        .page-item.active .page-link:hover {
            background-color: #6200ea;
            color: white;
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
                    <h2 class="mb-4">Assign Exam: <?php echo htmlspecialchars($exam['exam_name']); ?></h2>
                    
                    <div class="tab-menu" style="margin-bottom: 20px;">
                        <a href="create-exam.php?exam_id=<?php echo $exam_id; ?>" class="btn btn-secondary">Back to Exam Creation</a>
                        <a href="test2.php?exam_id=<?php echo $exam_id; ?>" class="btn btn-primary">Questions</a>
                        <a href="preview_exam.php?exam_id=<?php echo $exam_id; ?>" class="btn btn-primary">Preview</a>
                        <a href="exam_settings.php?exam_id=<?php echo $exam_id; ?>" class="btn btn-primary">Settings</a>
                        <button class="btn btn-primary active">Assign</button>
                        <a href="exam_results.php?exam_id=<?php echo $exam_id; ?>" class="btn btn-primary">Results</a>
                    </div>
                    
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    
                    <div class="filter-section">
                        <div class="row">
                            <div class="col-md-4">
                                <select id="trackFilter" class="form-control">
                                    <option value="">All</option>
                                    <option value="tech">Tech</option>
                                    <option value="non-tech">Non-Tech</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <form method="POST">
                        <div class="student-table">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="selectAll">
                                        </th>
                                        <th>Student ID</th>
                                        <th>Name</th>
                                        <th>Student Type</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                    <tr class="student-row" data-track="<?php echo strtolower($student['track_name']); ?>">
                                        <td>
                                            <input type="checkbox" name="student_ids[]" 
                                                   value="<?php echo $student['student_id']; ?>"
                                                   <?php echo in_array($student['student_id'], $assigned_students) ? 'checked' : ''; ?>>
                                        </td>
                                        <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                        <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['track_name']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            
                            <!-- Add pagination -->
                            <div class="pagination-container" style="padding: 20px; display: flex; justify-content: center;">
                                <nav aria-label="Student list pagination">
                                    <ul class="pagination">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?exam_id=<?php echo $exam_id; ?>&page=<?php echo ($page - 1); ?>">
                                                    Previous
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <?php
                                        // Show up to 5 page numbers
                                        $start_page = max(1, $page - 2);
                                        $end_page = min($total_pages, $start_page + 4);
                                        $start_page = max(1, $end_page - 4);

                                        for ($i = $start_page; $i <= $end_page; $i++):
                                        ?>
                                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                                <a class="page-link" href="?exam_id=<?php echo $exam_id; ?>&page=<?php echo $i; ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?exam_id=<?php echo $exam_id; ?>&page=<?php echo ($page + 1); ?>">
                                                    Next
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                        
                        <div class="assign-button">
                            <button type="submit" class="btn btn-primary">Assign Exam</button>
                            <a href="exam_list.php" class="btn btn-secondary">Back to Exam List</a>
                        </div>
                    </form>
                </div>
            </div>
            <?php include 'footer.php'; ?>
        </div>
        
    </div>
    <script src="assets/js/script.min.js"></script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const studentCheckboxes = document.querySelectorAll('input[name="student_ids[]"]');
    const trackFilter = document.getElementById('trackFilter');
    
    // Get filter value from URL if it exists
    const urlParams = new URLSearchParams(window.location.search);
    const filterValue = urlParams.get('filter');
    if (filterValue) {
        trackFilter.value = filterValue;
        filterStudents(filterValue);
    }
            
             // Select All functionality
    selectAll.addEventListener('change', function() {
        studentCheckboxes.forEach(checkbox => {
            const row = checkbox.closest('tr');
            if (row.style.display !== 'none') {
                checkbox.checked = selectAll.checked;
            }
        });
    });
            
 // Track filter functionality
 trackFilter.addEventListener('change', function() {
        const selectedTrack = this.value;
        filterStudents(selectedTrack);
        
        // Update URL with filter
        const url = new URL(window.location.href);
        if (selectedTrack) {
            url.searchParams.set('filter', selectedTrack);
        } else {
            url.searchParams.delete('filter');
        }
        window.history.replaceState({}, '', url);
    });
            
    function filterStudents(selectedTrack) {
        document.querySelectorAll('.student-row').forEach(row => {
            const rowTrack = row.getAttribute('data-track');
            
            if (!selectedTrack || 
                (selectedTrack === 'tech' && rowTrack === 'tech') ||
                (selectedTrack === 'non-tech' && rowTrack === 'non-tech')) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
});
    </script>
</body>
</html> 