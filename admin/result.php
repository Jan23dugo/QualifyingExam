<?php
require_once '../config/config.php';
session_start();

if (!isset($_SESSION['loggedin']) || !isset($_SESSION['user_id'])) {
    header('Location: loginAdmin.php');
    exit();
}

// Query to get exam results with the new date/time columns
$query = "SELECT 
    e.exam_id,
    e.exam_name,
    e.exam_date,
    e.exam_time,
    e.duration,
    e.status,
    COUNT(DISTINCT ea.student_id) as total_students,
    COUNT(DISTINCT er.student_id) as completed_exams,
    ROUND(AVG(er.score), 2) as average_score,
    MIN(er.score) as lowest_score,
    MAX(er.score) as highest_score
FROM 
    exams e
    LEFT JOIN exam_assignments ea ON e.exam_id = ea.exam_id
    LEFT JOIN exam_results er ON e.exam_id = er.exam_id
GROUP BY 
    e.exam_id, e.exam_name, e.exam_date, e.exam_time, e.duration, e.status
ORDER BY 
    e.exam_date DESC, e.exam_time DESC";

$result = $conn->query($query);

// Function to format the schedule display
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
                                        <th>Exam Name</th>
                                        <th>Schedule</th>
                                        <th>Duration</th>
                                        <th>Status</th>
                                        <th>Total Students</th>
                                        <th>Completed</th>
                                        <th>Average Score</th>
                                        <th>Score Range</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['exam_name']); ?></td>
                                            <td><?php echo formatSchedule($row['exam_date'], $row['exam_time']); ?></td>
                                            <td><?php echo $row['duration']; ?> mins</td>
                                            <td>
                                                <span class="badge bg-<?php echo $row['status'] === 'scheduled' ? 'success' : 'warning'; ?>">
                                                    <?php echo ucfirst($row['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $row['total_students']; ?></td>
                                            <td><?php echo $row['completed_exams']; ?></td>
                                            <td><?php echo $row['average_score'] ? $row['average_score'] . '%' : 'N/A'; ?></td>
                                            <td>
                                                <?php if ($row['lowest_score'] !== null && $row['highest_score'] !== null): ?>
                                                    <?php echo $row['lowest_score'] . '% - ' . $row['highest_score'] . '%'; ?>
                                                <?php else: ?>
                                                    N/A
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="view_exam_results.php?exam_id=<?php echo $row['exam_id']; ?>" 
                                                   class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i> View Details
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
