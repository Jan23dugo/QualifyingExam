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
    
    <!-- Add this modal HTML before the closing body tag -->
    <div class="modal fade" id="studentModal" tabindex="-1" aria-labelledby="studentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="studentModalLabel">Student Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <!-- Personal Information -->
                        <div class="row mb-3">
                            <h6 class="text-primary">Personal Information</h6>
                            <div class="col-md-4">
                                <p><strong>Reference ID:</strong> <span id="ref_id"></span></p>
                                <p><strong>First Name:</strong> <span id="first_name"></span></p>
                                <p><strong>Middle Name:</strong> <span id="middle_name"></span></p>
                                <p><strong>Last Name:</strong> <span id="last_name"></span></p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Gender:</strong> <span id="gender"></span></p>
                                <p><strong>Date of Birth:</strong> <span id="dob"></span></p>
                                <p><strong>Email:</strong> <span id="email"></span></p>
                                <p><strong>Contact:</strong> <span id="contact"></span></p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Address:</strong> <span id="address"></span></p>
                            </div>
                        </div>

                        <!-- Academic Information -->
                        <div class="row mb-3">
                            <h6 class="text-primary">Academic Information</h6>
                            <div class="col-md-6">
                                <p><strong>Student Type:</strong> <span id="student_type"></span></p>
                                <p><strong>Previous School:</strong> <span id="prev_school"></span></p>
                                <p><strong>Year Level:</strong> <span id="year_level"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Previous Program:</strong> <span id="prev_program"></span></p>
                                <p><strong>Desired Program:</strong> <span id="desired_program"></span></p>
                                <p><strong>Tech Student:</strong> <span id="is_tech"></span></p>
                            </div>
                        </div>

                        <!-- Documents -->
                        <div class="row mb-3">
                            <h6 class="text-primary">Uploaded Documents</h6>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        Transcript of Records
                                    </div>
                                    <div class="card-body">
                                        <img id="tor_preview" src="" alt="TOR" class="img-fluid mb-2" style="max-height: 300px;">
                                        <button class="btn btn-primary btn-sm" onclick="viewDocument('tor')">View Full Size</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        School ID
                                    </div>
                                    <div class="card-body">
                                        <img id="school_id_preview" src="" alt="School ID" class="img-fluid mb-2" style="max-height: 300px;">
                                        <button class="btn btn-primary btn-sm" onclick="viewDocument('school_id')">View Full Size</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Viewer Modal -->
    <div class="modal fade" id="documentModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="documentModalLabel">Document View</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="fullSizeDocument" src="" alt="Document" class="img-fluid">
                </div>
            </div>
        </div>
    </div>

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
    async function viewStudent(referenceId) {
        try {
            const response = await fetch(`get_student_details.php?ref=${referenceId}`);
            const student = await response.json();
            
            if (student) {
                // Populate modal with student details
                document.getElementById('ref_id').textContent = student.reference_id;
                document.getElementById('first_name').textContent = student.first_name;
                document.getElementById('middle_name').textContent = student.middle_name || 'N/A';
                document.getElementById('last_name').textContent = student.last_name;
                document.getElementById('gender').textContent = student.gender;
                document.getElementById('dob').textContent = student.dob;
                document.getElementById('email').textContent = student.email;
                document.getElementById('contact').textContent = student.contact_number;
                document.getElementById('address').textContent = student.street;
                document.getElementById('student_type').textContent = student.student_type;
                document.getElementById('prev_school').textContent = student.previous_school;
                document.getElementById('year_level').textContent = student.year_level;
                document.getElementById('prev_program').textContent = student.previous_program;
                document.getElementById('desired_program').textContent = student.desired_program;
                document.getElementById('is_tech').textContent = student.is_tech ? 'Yes' : 'No';

                // Set document previews
                document.getElementById('tor_preview').src = '../' + student.tor;
                document.getElementById('school_id_preview').src = '../' + student.school_id;

                // Store document paths for full view
                document.getElementById('tor_preview').dataset.fullPath = '../' + student.tor;
                document.getElementById('school_id_preview').dataset.fullPath = '../' + student.school_id;

                // Show the modal
                const studentModal = new bootstrap.Modal(document.getElementById('studentModal'));
                studentModal.show();
            } else {
                alert('Student details not found');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error loading student details');
        }
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

    // View document in full size
    function viewDocument(type) {
        const preview = document.getElementById(`${type}_preview`);
        const fullSizeImg = document.getElementById('fullSizeDocument');
        fullSizeImg.src = preview.dataset.fullPath;
        
        const documentModal = new bootstrap.Modal(document.getElementById('documentModal'));
        documentModal.show();
    }
    </script>
</body>
</html>
