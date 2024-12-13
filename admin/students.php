<?php
// Include database connection
require_once('../config/config.php');

// Get pagination parameters
$entries_per_page = isset($_GET['entries']) ? (int)$_GET['entries'] : 20; // Default to 20
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $entries_per_page;

// Function to safely escape column names
function escapeSortColumn($column) {
    return '`' . str_replace('`', '``', $column) . '`';
}

// Get the sort parameter from URL
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'reference_id';

// Define allowed sort columns and their corresponding SQL
$allowed_sorts = [
    'reference_id' => 'reference_id',
    'full_name' => 'full_name',
    'student_type' => 'student_type',
    'is_tech' => 'is_tech',
    'registration_year' => 'YEAR(registration_date)' // Using correct column name
];

// Validate and set the sort column
$sort_column = isset($allowed_sorts[$sort]) ? $allowed_sorts[$sort] : $allowed_sorts['reference_id'];

// Get the direction parameter from URL
$sort_direction = isset($_GET['direction']) && strtoupper($_GET['direction']) === 'DESC' ? 'DESC' : 'ASC';

// Count total records for pagination
$count_query = "SELECT COUNT(*) as total FROM students";
$count_result = $conn->query($count_query);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $entries_per_page);

// Modified query to include tech/non-tech and registration year
$query = "SELECT 
    reference_id, 
    CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) as full_name,
    email, 
    student_type,
    is_tech,
    YEAR(registration_date) as registration_year
FROM students 
ORDER BY " . $sort_column . " " . $sort_direction . "
LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $entries_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();

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

    <!-- Add these styles in the head section -->
    <style>
        /* Table container with fixed height and scroll */
        .table-container {
            max-height: calc(100vh - 450px); /* Reduced height */
            overflow-y: auto;
            border-radius: 4px;
        }

        /* Keep the header fixed while scrolling */
        .table-fixed {
            position: relative;
        }

        .table-fixed thead {
            position: sticky;
            top: 0;
            background-color: #fff;
            z-index: 1;
            border-top: 1px solid #dee2e6;
        }

        /* Add shadow to header when scrolling */
        .table-fixed thead::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            bottom: -1px;
            height: 1px;
            background-color: #dee2e6;
        }

        /* Ensure consistent column widths */
        .table-fixed th,
        .table-fixed td {
            white-space: nowrap;
            padding: 12px 15px;
        }

        .table-fixed th:nth-child(1),
        .table-fixed td:nth-child(1) { width: 15%; } /* Reference Number */
        
        .table-fixed th:nth-child(2),
        .table-fixed td:nth-child(2) { width: 20%; } /* Full Name */
        
        .table-fixed th:nth-child(3),
        .table-fixed td:nth-child(3) { width: 20%; } /* Email */
        
        .table-fixed th:nth-child(4),
        .table-fixed td:nth-child(4) { width: 10%; } /* Student Type */
        
        .table-fixed th:nth-child(5),
        .table-fixed td:nth-child(5) { width: 10%; } /* Tech/Non-Tech */
        
        .table-fixed th:nth-child(6),
        .table-fixed td:nth-child(6) { width: 10%; } /* Registration Year */
        
        .table-fixed th:nth-child(7),
        .table-fixed td:nth-child(7) { width: 15%; } /* Action */

        /* Common Modal Styles */
        .modal-dialog {
            max-width: 500px;
        }
        
        .modal .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .modal .modal-header {
            background-color: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
            border-radius: 12px 12px 0 0;
            padding: 1rem 1.5rem;
        }
        
        .modal .modal-title {
            color: #1f2937;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .modal .modal-body {
            padding: 1.5rem;
            color: #4b5563;
            font-size: 0.95rem;
        }
        
        .modal .modal-footer {
            border-top: 1px solid #e5e7eb;
            padding: 1rem 1.5rem;
            background-color: #f8fafc;
            border-radius: 0 0 12px 12px;
        }
        
        .modal .btn {
            padding: 0.5rem 1.25rem;
            font-weight: 500;
            border-radius: 6px;
            transition: all 0.2s ease;
        }
        
        .modal .btn-secondary {
            background-color: #f3f4f6;
            border-color: #e5e7eb;
            color: #4b5563;
        }
        
        .modal .btn-secondary:hover {
            background-color: #e5e7eb;
            border-color: #d1d5db;
            color: #374151;
        }
        
        .modal .btn-danger {
            background-color: #ef4444;
            border-color: #ef4444;
        }
        
        .modal .btn-danger:hover {
            background-color: #dc2626;
            border-color: #dc2626;
        }
        
        .modal .btn-primary {
            background-color: #6200ea;
            border-color: #6200ea;
        }
        
        .modal .btn-primary:hover {
            background-color: #5000c9;
            border-color: #5000c9;
        }
        
        /* Modal Icons */
        .modal .modal-body i {
            font-size: 24px;
            margin-right: 1rem;
        }
        
        .modal .warning-icon {
            color: #f59e0b;
        }
        
        .modal .delete-icon {
            color: #ef4444;
        }
        
        .modal .info-icon {
            color: #6200ea;
        }
    </style>
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
                                               href="?sort=reference_id&direction=<?php echo ($sort === 'reference_id' && $sort_direction === 'ASC') ? 'DESC' : 'ASC'; ?>&entries=<?php echo $entries_per_page; ?>">
                                               Reference Number <?php echo $sort === 'reference_id' ? ($sort_direction === 'ASC' ? '↑' : '↓') : ''; ?>
                                            </a>
                                            <a class="dropdown-item <?php echo $sort === 'full_name' ? 'active' : ''; ?>" 
                                               href="?sort=full_name&direction=<?php echo ($sort === 'full_name' && $sort_direction === 'ASC') ? 'DESC' : 'ASC'; ?>&entries=<?php echo $entries_per_page; ?>">
                                               Name <?php echo $sort === 'full_name' ? ($sort_direction === 'ASC' ? '↑' : '↓') : ''; ?>
                                            </a>
                                            <a class="dropdown-item <?php echo $sort === 'student_type' ? 'active' : ''; ?>" 
                                               href="?sort=student_type&direction=<?php echo ($sort === 'student_type' && $sort_direction === 'ASC') ? 'DESC' : 'ASC'; ?>&entries=<?php echo $entries_per_page; ?>">
                                               Student Type <?php echo $sort === 'student_type' ? ($sort_direction === 'ASC' ? '↑' : '↓') : ''; ?>
                                            </a>
                                            <a class="dropdown-item <?php echo $sort === 'is_tech' ? 'active' : ''; ?>" 
                                               href="?sort=is_tech&direction=<?php echo ($sort === 'is_tech' && $sort_direction === 'ASC') ? 'DESC' : 'ASC'; ?>&entries=<?php echo $entries_per_page; ?>">
                                               Tech/Non-Tech <?php echo $sort === 'is_tech' ? ($sort_direction === 'ASC' ? '↑' : '↓') : ''; ?>
                                            </a>
                                            <a class="dropdown-item <?php echo $sort === 'registration_year' ? 'active' : ''; ?>" 
                                               href="?sort=registration_year&direction=<?php echo ($sort === 'registration_year' && $sort_direction === 'ASC') ? 'DESC' : 'ASC'; ?>&entries=<?php echo $entries_per_page; ?>">
                                               Registration Year <?php echo $sort === 'registration_year' ? ($sort_direction === 'ASC' ? '↑' : '↓') : ''; ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <select class="form-select form-select-sm" id="entriesPerPage" onchange="changeEntries(this.value)">
                                        <option value="20" <?php echo $entries_per_page == 20 ? 'selected' : ''; ?>>20 entries</option>
                                        <option value="40" <?php echo $entries_per_page == 40 ? 'selected' : ''; ?>>40 entries</option>
                                    </select>
                                </div>
                            </div>

                            <div class="table-container">
                                <div class="table-responsive">
                                    <table class="table table-fixed my-0">
                                        <thead>
                                            <tr>
                                                <th>Reference Number</th>
                                                <th>Full Name</th>
                                                <th>Email</th>
                                                <th>Student Type</th>
                                                <th>Tech/Non-Tech</th>
                                                <th>Registration Year</th>
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
                                                    echo "<td>" . ($row['is_tech'] ? 'Tech' : 'Non-Tech') . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['registration_year']) . "</td>";
                                                    echo "<td>
                                                            <button class='btn btn-primary btn-sm' onclick='viewStudent(\"" . $row['reference_id'] . "\")'>View</button>
                                                            <button class='btn btn-danger btn-sm' onclick='deleteStudent(\"" . $row['reference_id'] . "\")'>Delete</button>
                                                          </td>";
                                                    echo "</tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='7' class='text-center'>No students found</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Pagination -->
                            <div class="row">
                                <div class="col-md-6 align-self-center">
                                    <p id="dataTable_info" class="dataTables_info" role="status" aria-live="polite">
                                        Showing <?php echo ($offset + 1); ?> to <?php echo min($offset + $entries_per_page, $total_records); ?> of <?php echo $total_records; ?> entries
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination justify-content-end">
                                            <?php if ($current_page > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?page=<?php echo ($current_page - 1); ?>&sort=<?php echo $sort; ?>&direction=<?php echo $sort_direction; ?>&entries=<?php echo $entries_per_page; ?>">Previous</a>
                                                </li>
                                            <?php endif; ?>
                                            
                                            <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                                                <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                                                    <a class="page-link" href="?page=<?php echo $i; ?>&sort=<?php echo $sort; ?>&direction=<?php echo $sort_direction; ?>&entries=<?php echo $entries_per_page; ?>"><?php echo $i; ?></a>
                                                </li>
                                            <?php endfor; ?>
                                            
                                            <?php if ($current_page < $total_pages): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?page=<?php echo ($current_page + 1); ?>&sort=<?php echo $sort; ?>&direction=<?php echo $sort_direction; ?>&entries=<?php echo $entries_per_page; ?>">Next</a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </nav>
                                </div>
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
    <div class="modal fade" id="studentModal" tabindex="-1" aria-labelledby="studentModalLabel">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <style>
                    /* Custom styles for student modal */
                    .modal-xl {
                        max-width: 70%; /* Makes modal width 90% of screen width */
                        margin: 1.75rem auto;
                    }
                    
                    /* Adjust content layout */
                    .student-info-section {
                        padding: 20px;
                    }
                    
                    /* Make document previews larger */
                    .document-preview img {
                        max-height: 100px; /* Further reduced height */
                        max-width: 100px;  /* Further reduced width */
                        object-fit: contain;
                        margin: 10px;
                    }
                    
                    /* Add container styles for documents */
                    .document-preview {
                        display: flex;
                        flex-direction: column;
                        justify-content: center;
                        align-items: center;
                        padding: 10px;
                        background: #f8f9fa;
                        border-radius: 4px;
                        margin-bottom: 10px;
                        width: fit-content;
                        margin: 10px auto;
                    }
                    
                    /* Style for the View Full Size button */
                    .document-preview .btn {
                        margin-top: 10px;
                        font-size: 12px;
                        padding: 4px 8px;
                    }
                    
                    /* Style for the document section */
                    .document-section {
                        max-width: 300px;  /* Limit the width of document section */
                        margin: 0 auto;    /* Center the section */
                    }
                </style>
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
                                    <div class="card-body p-0" style="height: 300px; display: flex; align-items: center; justify-content: center;">
                                        <img id="tor_preview" src="" alt="TOR" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                    </div>
                                </div>
                                <div class="text-center mt-2">
                                    <button class="btn btn-primary btn-sm" onclick="viewDocument('tor')">View Full Size</button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        School ID
                                    </div>
                                    <div class="card-body p-0" style="height: 300px; display: flex; align-items: center; justify-content: center;">
                                        <img id="school_id_preview" src="" alt="School ID" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                    </div>
                                </div>
                                <div class="text-center mt-2">
                                    <button class="btn btn-primary btn-sm" onclick="viewDocument('school_id')">View Full Size</button>
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
    <div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Document View</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="fullSizeDocument" src="" alt="Document" class="img-fluid">
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Student Modal -->
    <div class="modal fade" id="deleteStudentModal" tabindex="-1" aria-labelledby="deleteStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteStudentModalLabel">Delete Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-trash-alt delete-icon"></i>
                        <p class="mb-0">Are you sure you want to delete this student? This action cannot be undone.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteStudent">Delete</button>
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

    // Function to open the modal and set the student reference ID
    function deleteStudent(referenceId) {
        referenceIdToDelete = referenceId;

        // Show the modal
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteStudentModal'));
        deleteModal.show();
    }

    // Event listener for the "Confirm Delete" button in the modal
    document.getElementById('confirmDeleteStudent').addEventListener('click', function () {
        if (referenceIdToDelete) {
            // Send delete request to server
            fetch('delete_student.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ reference_id: referenceIdToDelete })
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
    });

    // View document in full size
    function viewDocument(type) {
        const preview = document.getElementById(`${type}_preview`);
        const fullSizeImg = document.getElementById('fullSizeDocument');
        fullSizeImg.src = preview.dataset.fullPath;
        
        const documentModal = new bootstrap.Modal(document.getElementById('documentModal'));
        documentModal.show();
    }

    function changeEntries(value) {
        window.location.href = `?entries=${value}&sort=<?php echo $sort; ?>&direction=<?php echo $sort_direction; ?>&page=1`;
    }

    // Add this to your existing JavaScript
    document.addEventListener('DOMContentLoaded', function() {
        // Handle modal focus management
        const studentModal = document.getElementById('studentModal');
        const documentModal = document.getElementById('documentModal');

        studentModal.addEventListener('shown.bs.modal', function() {
            studentModal.querySelector('.btn-close').focus();
        });

        documentModal.addEventListener('shown.bs.modal', function() {
            documentModal.querySelector('.btn-close').focus();
        });
    });
    </script>
</body>
</html>
