<?php
// Include database connection
require_once('../config/config.php');

// Function to safely escape column names
function escapeSortColumn($column) {
    return '`' . str_replace('`', '``', $column) . '`';
}

// Get the sort parameter from URL
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'reference_id';

// Define allowed sort columns and their corresponding SQL
$allowed_sorts = [
    'reference_id' => 'reference_id',
    'full_name' => 'full_name', // Changed this to use the alias
    'student_type' => 'student_type'
];

// Validate and set the sort column, default to reference_id if invalid
$sort_column = isset($allowed_sorts[$sort]) ? $allowed_sorts[$sort] : $allowed_sorts['reference_id'];

// Get the direction parameter from URL
$sort_direction = isset($_GET['direction']) && strtoupper($_GET['direction']) === 'DESC' ? 'DESC' : 'ASC';

// Modified query to use proper alias for sorting
$query = "SELECT 
    reference_id, 
    CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) as full_name,
    email, 
    student_type 
FROM students 
ORDER BY " . $sort_column . " " . $sort_direction;

$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Manage Students - CCIS</title>

    <!-- External Stylesheets -->
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i&amp;display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans&amp;display=swap">
    <link rel="stylesheet" href="assets/fonts/fontawesome-all.min.css">
    <link rel="stylesheet" href="assets/css/styles.min.css">
</head>

<body id="page-top">
    <div id="wrapper">
        <!-- Include Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- Include Topbar -->
                <?php include 'topbar.php'; ?>

                <!-- Main Container -->
                <div class="container-fluid">
                    <h3 class="text-dark mb-4">Manage Students</h3>
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <p class="text-primary m-0 fw-bold">Student Records</p>
                        </div>
                        <div class="card-body" style="font-family: 'Open Sans', sans-serif;">
                            <div class="row">
                                <div class="col-md-6 col-xl-2 text-nowrap">
                                    <div class="btn-group">
                                        <button class="btn btn-primary" type="button" style="background: var(--bs-card-cap-bg); color: var(--bs-emphasis-color);">Sort by:</button>
                                        <button class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false" type="button" style="color: var(--bs-body-color); background: var(--bs-btn-hover-color);"></button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item <?php echo $sort === 'reference_id' ? 'active' : ''; ?>" 
                                               href="?sort=reference_id&direction=<?php echo ($sort === 'reference_id' && $sort_direction === 'ASC') ? 'DESC' : 'ASC'; ?>">
                                               Reference Number <?php echo $sort === 'reference_id' ? ($sort_direction === 'ASC' ? '↑' : '↓') : ''; ?>
                                            </a>
                                            <a class="dropdown-item <?php echo $sort === 'full_name' ? 'active' : ''; ?>" 
                                               href="?sort=full_name&direction=<?php echo ($sort === 'full_name' && $sort_direction === 'ASC') ? 'DESC' : 'ASC'; ?>">
                                               Name <?php echo $sort === 'full_name' ? ($sort_direction === 'ASC' ? '↑' : '↓') : ''; ?>
                                            </a>
                                            <a class="dropdown-item <?php echo $sort === 'student_type' ? 'active' : ''; ?>" 
                                               href="?sort=student_type&direction=<?php echo ($sort === 'student_type' && $sort_direction === 'ASC') ? 'DESC' : 'ASC'; ?>">
                                               Student Type <?php echo $sort === 'student_type' ? ($sort_direction === 'ASC' ? '↑' : '↓') : ''; ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col">
                                    <input type="search" class="form-control form-control-sm" id="searchInput" 
                                           aria-controls="dataTable" placeholder="Search" style="margin-top: -15px;">
                                </div>
                            </div>

                            <div class="table-responsive table mt-2" id="dataTable">
                                <table class="table my-0">
                                    <thead>
                                        <tr>
                                            <th>Reference Number</th>
                                            <th>Full Name</th>
                                            <th>Email</th>
                                            <th>Student Type</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ($result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {
                                                echo "<tr>";
                                                echo "<td>" . htmlspecialchars($row['reference_id']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['student_type']) . "</td>";
                                                echo "<td>
                                                        <button class='btn btn-primary btn-sm' onclick='viewStudent(\"" . $row['reference_id'] . "\")'>View</button>
                                                        <button class='btn btn-danger btn-sm' onclick='deleteStudent(\"" . $row['reference_id'] . "\")'>Delete</button>
                                                      </td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='5' class='text-center'>No students found</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="row">
                                <div class="col-md-6 align-self-center">
                                    <p id="dataTable_info" class="dataTables_info" role="status" aria-live="polite">
                                        Showing <?php echo $result->num_rows; ?> entries
                                    </p>
                                </div>
                                <!-- Add pagination controls if needed -->
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Footer -->
                
            </div>
            <?php include 'footer.php'; ?>
        </div>

        <a class="border rounded d-inline scroll-to-top" href="#page-top">
            <i class="fas fa-angle-up"></i>
        </a>
    </div>

    <!-- External Scripts -->
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/script.min.js"></script>
    
    <script>
    // Search functionality
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const searchValue = this.value.toLowerCase();
        const table = document.querySelector('#dataTable table');
        const rows = table.getElementsByTagName('tr');

        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            const cells = row.getElementsByTagName('td');
            let found = false;

            for (let j = 0; j < cells.length; j++) {
                const cellText = cells[j].textContent.toLowerCase();
                if (cellText.includes(searchValue)) {
                    found = true;
                    break;
                }
            }

            row.style.display = found ? '' : 'none';
        }
    });

    // View student function
    function viewStudent(referenceId) {
        // Redirect to student details page
        window.location.href = `view_student.php?ref=${referenceId}`;
    }

    // Delete student function
    function deleteStudent(referenceId) {
        if (confirm('Are you sure you want to delete this student?')) {
            // Send delete request to server
            fetch('delete_student.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ reference_id: referenceId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload the page to refresh the table
                    location.reload();
                } else {
                    alert('Error deleting student');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting student');
            });
        }
    }
    </script>
</body>
</html>
