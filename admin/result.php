<?php
require_once '../config/config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['user_id'])) {
    header('Location: loginAdmin.php');
    exit();
}

// Get filter parameters
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$exam_type = isset($_GET['exam_type']) ? $_GET['exam_type'] : 'all';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'exam_date';
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'DESC';

// Base query with joins to get all necessary information
$query = "SELECT 
    er.result_id,
    er.score,
    er.total_questions,
    er.created_at as submission_date,
    e.exam_id,
    e.exam_name,
    e.student_type as exam_type,
    e.schedule_date as exam_date,
    s.student_id,
    s.first_name,
    s.last_name,
    s.student_type,
    s.is_tech,
    COUNT(q.question_id) as total_exam_questions,
    YEAR(e.schedule_date) as exam_year
    FROM exam_results er
    JOIN exams e ON er.exam_id = e.exam_id
    JOIN students s ON er.student_id = s.student_id
    LEFT JOIN exam_sections es ON e.exam_id = es.exam_id
    LEFT JOIN questions q ON es.section_id = q.section_id
    WHERE 1=1";

// Apply filters
if ($year !== 'all') {
    $query .= " AND YEAR(e.schedule_date) = '$year'";
}
if ($exam_type !== 'all') {
    $query .= " AND e.student_type = '$exam_type'";
}

// Group by to avoid duplicate counts
$query .= " GROUP BY er.result_id";

// Add sorting
$query .= " ORDER BY $sort_by $sort_order";

$results = $conn->query($query);

if (!$results) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Exam Results - Brand</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">
    
    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i&display=swap">
    <link rel="stylesheet" href="assets/fonts/fontawesome-all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/styles.min.css">
    
    <style>
        /* Filter Section */
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
        }

        /* Table Section */
        .table-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 60px; /* Make space for pagination */
        }

        .table-responsive {
            height: 400px;
            overflow-y: auto;
            border: 1px solid #e3e6f0;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        /* Fixed pagination positioning */
        div.dataTables_wrapper div.dataTables_paginate {
            position: absolute;
            bottom: 20px;
            right: 20px;
        }

        div.dataTables_wrapper div.dataTables_info {
            position: absolute;
            bottom: 20px;
            left: 20px;
        }

        div.dataTables_wrapper div.dataTables_length, 
        div.dataTables_wrapper div.dataTables_filter {
            margin-bottom: 15px;
        }

        /* Pagination styling */
        div.dataTables_wrapper div.dataTables_paginate ul.pagination {
            margin: 0;
            white-space: nowrap;
            justify-content: flex-end;
        }

        .pagination .page-item .page-link {
            padding: 0.5rem 0.75rem;
            margin: 0 2px;
            border-radius: 4px;
        }

        .pagination .page-item.active .page-link {
            background-color: #4e73df;
            border-color: #4e73df;
        }

        /* Keep header fixed */
        .table thead th {
            position: sticky;
            top: 0;
            background: white;
            z-index: 1;
        }

        /* Status Badges */
        .badge-tech {
            background-color: #4e73df;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
        }

        .badge-non-tech {
            background-color: #1cc88a;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
        }

        .status-passed {
            background-color: #1cc88a;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
        }

        .status-failed {
            background-color: #e74a3b;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
        }

        /* DataTables Customization */
        .dataTables_wrapper .dataTables_length, 
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 1rem;
        }

        .dataTables_wrapper .dataTables_info {
            padding-top: 1rem;
        }

        .dataTables_wrapper .dataTables_paginate {
            padding-top: 1rem;
        }

        /* Action Buttons */
        .btn-view-details {
            padding: 5px 10px;
            font-size: 0.875rem;
        }

        /* Search bar styling */
        div.dataTables_wrapper div.dataTables_filter {
            width: 100%;
            margin-bottom: 15px;
            text-align: right;
        }

        div.dataTables_wrapper div.dataTables_filter input {
            width: 300px; /* Make search input wider */
            height: 38px; /* Match the height of other inputs */
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            border: 1px solid #d1d3e2;
            border-radius: 0.35rem;
            margin-left: 10px;
        }

        div.dataTables_wrapper div.dataTables_filter label {
            font-weight: 500;
            color: #4e73df;
            display: inline-flex;
            align-items: center;
        }

        /* Length (Show entries) styling to match */
        div.dataTables_wrapper div.dataTables_length {
            width: 100%;
            margin-bottom: 15px;
        }

        div.dataTables_wrapper div.dataTables_length select {
            width: 100px;
            height: 38px;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            border: 1px solid #d1d3e2;
            border-radius: 0.35rem;
            margin: 0 10px;
        }

        div.dataTables_wrapper div.dataTables_length label {
            font-weight: 500;
            color: #4e73df;
            display: inline-flex;
            align-items: center;
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand bg-white shadow mb-4 topbar static-top navbar-light">
                    <div class="container-fluid">
                        <button class="btn btn-link d-md-none rounded-circle me-3" id="sidebarToggleTop">
                            <i class="fas fa-bars"></i>
                        </button>
                    </div>
                </nav>

                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800">Exam Results</h1>
                    
                    <div class="filter-section">
                        <form method="GET" class="filter-form">
                            <div class="form-group">
                                <label class="form-label">Year:</label>
                                <select name="year" class="form-select">
                                    <option value="all">All Years</option>
                                    <?php
                                    $current_year = date('Y');
                                    for ($i = $current_year; $i >= $current_year - 5; $i--) {
                                        $selected = ($year == $i) ? 'selected' : '';
                                        echo "<option value='$i' $selected>$i</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Student Type:</label>
                                <select name="exam_type" class="form-select">
                                    <option value="all">All Types</option>
                                    <option value="tech" <?php echo ($exam_type == 'tech') ? 'selected' : ''; ?>>Technical</option>
                                    <option value="non-tech" <?php echo ($exam_type == 'non-tech') ? 'selected' : ''; ?>>Non-Technical</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Sort By:</label>
                                <select name="sort_by" class="form-select">
                                    <option value="exam_date" <?php echo ($sort_by == 'exam_date') ? 'selected' : ''; ?>>Exam Date</option>
                                    <option value="score" <?php echo ($sort_by == 'score') ? 'selected' : ''; ?>>Score</option>
                                    <option value="last_name" <?php echo ($sort_by == 'last_name') ? 'selected' : ''; ?>>Student Name</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Order:</label>
                                <select name="sort_order" class="form-select">
                                    <option value="DESC" <?php echo ($sort_order == 'DESC') ? 'selected' : ''; ?>>Descending</option>
                                    <option value="ASC" <?php echo ($sort_order == 'ASC') ? 'selected' : ''; ?>>Ascending</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                            </div>
                        </form>
                    </div>

                    <div class="download-buttons">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#downloadModal">
                            <i class="fas fa-download"></i> Download Results
                        </button>
                    </div>

                    <div class="table-card">
                        <div class="table-responsive">
                            <table id="resultsTable" class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Student Name</th>
                                        <th>Student Type</th>
                                        <th>Exam Name</th>
                                        <th>Date Taken</th>
                                        <th>Score</th>
                                        <th>Percentage</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $results->fetch_assoc()): 
                                        $percentage = ($row['score'] / $row['total_exam_questions']) * 100;
                                        $studentType = $row['is_tech'] ? 'tech' : 'non-tech';
                                    ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $studentType; ?>">
                                                    <?php echo ucfirst($studentType); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['exam_name']); ?></td>
                                            <td><?php echo date('Y-m-d', strtotime($row['exam_date'])); ?></td>
                                            <td><?php echo $row['score'] . '/' . $row['total_exam_questions']; ?></td>
                                            <td><?php echo round($percentage, 2) . '%'; ?></td>
                                            <td>
                                                <span class="status-badge <?php echo $percentage >= 75 ? 'status-passed' : 'status-failed'; ?>">
                                                    <?php echo $percentage >= 75 ? 'Passed' : 'Failed'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="view_result_details.php?result_id=<?php echo $row['result_id']; ?>" 
                                                   class="btn btn-info btn-view-details">
                                                    <i class="fas fa-eye"></i> View
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

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Scripts -->
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.20/jspdf.plugin.autotable.min.js"></script>
    <script src="assets/js/script.min.js"></script>

    <!-- Your existing JavaScript code -->
    <script>
        function downloadResults() {
            const type = document.getElementById('downloadType').value;
            const format = document.getElementById('fileFormat').value;
            
            // Get selected fields
            const fields = {
                studentName: document.getElementById('includeStudentName').checked,
                studentType: document.getElementById('includeStudentType').checked,
                examName: document.getElementById('includeExamName').checked,
                date: document.getElementById('includeDate').checked,
                score: document.getElementById('includeScore').checked,
                percentage: document.getElementById('includePercentage').checked,
                status: document.getElementById('includeStatus').checked
            };

            // Get headers and indexes based on selected fields
            const headers = [];
            const indexes = [];
            
            if (fields.studentName) { headers.push('Student Name'); indexes.push(0); }
            if (fields.studentType) { headers.push('Type'); indexes.push(1); }
            if (fields.examName) { headers.push('Exam Name'); indexes.push(2); }
            if (fields.date) { headers.push('Date'); indexes.push(3); }
            if (fields.score) { headers.push('Score'); indexes.push(4); }
            if (fields.percentage) { headers.push('Percentage'); indexes.push(5); }
            if (fields.status) { headers.push('Status'); indexes.push(6); }

            // Filter and format data
            let tableData = [];
            $('#resultsTable tbody tr').each(function() {
                const rowData = [];
                const studentType = $(this).find('td:eq(1)').text().trim().toLowerCase();
                
                if (type === 'all' || studentType.includes(type)) {
                    indexes.forEach(index => {
                        rowData.push($(this).find('td').eq(index).text().trim());
                    });
                    tableData.push(rowData);
                }
            });

            if (format === 'pdf') {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF();
                
                // Add header
                doc.setFontSize(16);
                doc.text('Exam Results Report', 14, 15);
                doc.setFontSize(12);
                doc.text(`Type: ${type.charAt(0).toUpperCase() + type.slice(1)}`, 14, 25);
                doc.text(`Generated on: ${new Date().toLocaleDateString()}`, 14, 35);

                // Create table in PDF
                doc.autoTable({
                    head: [headers],
                    body: tableData,
                    startY: 45,
                    styles: { fontSize: 8 },
                    headStyles: { fillColor: [41, 128, 185] }
                });

                // Save PDF
                doc.save(`exam_results_${type}_${new Date().toISOString().split('T')[0]}.pdf`);
            } else if (format === 'excel' || format === 'csv') {
                // Create CSV content
                let csvContent = "data:text/csv;charset=utf-8,";
                csvContent += headers.join(",") + "\n";
                tableData.forEach(row => {
                    csvContent += row.join(",") + "\n";
                });

                // Download file
                const encodedUri = encodeURI(csvContent);
                const link = document.createElement("a");
                link.setAttribute("href", encodedUri);
                link.setAttribute("download", `exam_results_${type}_${new Date().toISOString().split('T')[0]}.${format}`);
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }

            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('downloadModal')).hide();
        }

        $(document).ready(function() {
            $('#resultsTable').DataTable({
                "pageLength": 25,
                "order": [[3, "desc"]],
                "scrollCollapse": true,
                "paging": true,
                "info": true,
                "dom": "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
                      "<'row'<'col-sm-12'tr>>" +
                      "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                "language": {
                    "lengthMenu": "Show _MENU_ entries",
                    "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                    "paginate": {
                        "first": "First",
                        "last": "Last",
                        "next": "Next",
                        "previous": "Previous"
                    }
                }
            });
        });
    </script>
</body>
</html>

<!-- Download Settings Modal -->
<div class="modal fade" id="downloadModal" tabindex="-1" aria-labelledby="downloadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="downloadModalLabel">Download Settings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="downloadSettingsForm">
                    <div class="mb-3">
                        <label class="form-label">Student Type</label>
                        <select class="form-select" id="downloadType">
                            <option value="all">All Students</option>
                            <option value="tech">Technical Students Only</option>
                            <option value="non-tech">Non-Technical Students Only</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Include Fields</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="includeStudentName" checked>
                            <label class="form-check-label" for="includeStudentName">Student Name</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="includeStudentType" checked>
                            <label class="form-check-label" for="includeStudentType">Student Type</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="includeExamName" checked>
                            <label class="form-check-label" for="includeExamName">Exam Name</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="includeDate" checked>
                            <label class="form-check-label" for="includeDate">Date Taken</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="includeScore" checked>
                            <label class="form-check-label" for="includeScore">Score</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="includePercentage" checked>
                            <label class="form-check-label" for="includePercentage">Percentage</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="includeStatus" checked>
                            <label class="form-check-label" for="includeStatus">Status</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">File Format</label>
                        <select class="form-select" id="fileFormat">
                            <option value="pdf">PDF</option>
                            <option value="excel">Excel</option>
                            <option value="csv">CSV</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="downloadResults()">Download</button>
            </div>
        </div>
    </div>
</div>
