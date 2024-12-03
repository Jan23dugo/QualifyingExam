<?php
// Connect to the database
require_once '../config/config.php';

// Determine the folder being viewed
$folderId = isset($_GET['folder_id']) ? $_GET['folder_id'] : null;

// Get current folder name if in a folder
$folderName = '';
$parentFolderId = null;
if ($folderId) {
    $folder_stmt = $conn->prepare("SELECT folder_name, parent_folder_id FROM folders WHERE folder_id = ?");
    $folder_stmt->bind_param("i", $folderId);
    $folder_stmt->execute();
    $folder_result = $folder_stmt->get_result();
    if ($folder_row = $folder_result->fetch_assoc()) {
        $folderName = $folder_row['folder_name'];
        $parentFolderId = $folder_row['parent_folder_id'];
    }
}

// Prepare query based on folder
if ($folderId) {
    $stmt = $conn->prepare("SELECT * FROM exams WHERE folder_id = ?");
    $stmt->bind_param("i", $folderId);
} else {
    $stmt = $conn->prepare("SELECT * FROM exams WHERE folder_id IS NULL");
}

$stmt->execute();
$result = $stmt->get_result();
?>


<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Create Exam - Brand</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    
    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i&display=swap">
    <link rel="stylesheet" href="assets/fonts/fontawesome-all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/styles.min.css">
    
    <style>
        /* Custom styles for folder and table */
        .list-header {
            display: flex;
            padding: 10px 15px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 10px;
            font-weight: 600;
            color: #495057;
        }

        .list-header > div {
            padding: 0 15px;
        }

        .name-col {
            width: 40%;
            display: flex;
            align-items: center;
        }

        .type-col {
            width: 20%;
        }

        .date-col {
            width: 25%;
        }

        .actions-col {
            width: 15%;
            text-align: center;
        }

        .folder-item, .exam-item {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            margin-bottom: 8px;
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }

        .folder-icon, .exam-icon {
            margin-right: 10px;
            font-size: 20px;
        }

        .folder-icon {
            color: #ffc107;
        }

        .exam-icon {
            color: #0d6efd;
        }

        .folder-item:hover, .exam-item:hover {
            background-color: #e9ecef;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .back-button {
            cursor: pointer;
            color: #007bff;
            margin-bottom: 10px;
            display: inline-block;
        }

        .modal-custom {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #343a40;
            color: white;
            border-radius: 8px;
            padding: 20px;
            z-index: 1050;
            display: none;
        }

        .modal-custom input {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border: none;
            border-radius: 4px;
        }

        .modal-custom-buttons {
            text-align: right;
            margin-top: 15px;
        }

        .modal-custom-buttons button {
            margin-left: 10px;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .modal-custom-buttons .btn-cancel {
            background-color: #6c757d;
            color: white;
        }

        .modal-custom-buttons .btn-ok {
            background-color: #007bff;
            color: white;
        }

        /* Style the exam table to match folder aesthetics */
        .table {
            margin-top: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .table th {
            font-size: 16px;
            font-weight: 600;
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }

        .table td {
            font-size: 16px;
            vertical-align: middle;
            padding: 12px 16px;
        }

        /* Add container styling */
        .content-container {
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin: 20px;
        }

        .controls-wrapper {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .search-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-left: 15px;
        }

        .search-input {
            padding: 6px 12px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            width: 250px;
        }

        .sort-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-left: auto;
        }

        .sort-button {
            padding: 6px 12px;
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .sort-button:hover {
            background: #f8f9fa;
        }

        .sort-button.active {
            background: #e9ecef;
            border-color: #adb5bd;
        }

        /* Add styles for breadcrumb */
        .folder-breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 15px;
            padding: 8px 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }

        .folder-breadcrumb i {
            color: #6c757d;
            font-size: 14px;
        }

        .folder-breadcrumb span {
            color: #495057;
        }

        .folder-breadcrumb .current-folder {
            font-weight: 600;
            color: #0d6efd;
        }
    </style>
</head>
<body id="page-top">
    <div id="wrapper">
        <!-- Include Sidebar -->
        <?php include 'sidebar.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- Include Topbar -->
                <?php include 'topbar.php'; ?>

                <div class="content-container">
                    <!-- Breadcrumb Navigation -->
                    <div class="folder-breadcrumb">
                        <a href="create-exam.php" style="text-decoration: none; color: inherit;">
                            <i class="fas fa-home"></i>
                            <span>Home</span>
                        </a>
                        <?php if ($folderId): ?>
                            <i class="fas fa-chevron-right"></i>
                            <span class="current-folder"><?php echo htmlspecialchars($folderName); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="controls-wrapper">
                        <?php if ($folderId): ?>
                            <button class="btn btn-light" onclick="goBack(<?php echo $parentFolderId ? $parentFolderId : 'null'; ?>)">
                                <i class="fas fa-arrow-left"></i> Back
                            </button>
                            <span class="ms-3 fw-bold"><?php echo htmlspecialchars($folderName); ?></span>
                        <?php endif; ?>

                        <!-- Add Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                + Add
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <li><a class="dropdown-item" href="#" onclick="addAssessment()">Add Assessment</a></li>
                                <li><a class="dropdown-item" href="#" onclick="addFolder()">Add Folder</a></li>
                            </ul>
                        </div>

                        <div class="search-wrapper">
                            <input type="text" class="search-input" placeholder="Search..." id="searchInput">
                            <button class="btn btn-light" onclick="clearSearch()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <div class="sort-wrapper">
                            <span>Sort by:</span>
                            <button class="sort-button active" data-sort="name">
                                <i class="fas fa-sort-alpha-down"></i> Name
                            </button>
                            <button class="sort-button" data-sort="type">
                                <i class="fas fa-sort"></i> Type
                            </button>
                            <button class="sort-button" data-sort="date">
                                <i class="fas fa-sort"></i> Date
                            </button>
                        </div>
                    </div>

                    <!-- Folder List Section -->
                    <div class="folder-list" id="folderList">
                        <!-- List Header -->
                        <div class="list-header">
                            <div class="name-col">Name</div>
                            <div class="type-col">Type</div>
                            <div class="date-col">Date Modified</div>
                            <div class="actions-col">Actions</div>
                        </div>

                        <?php
                        // Fetch folders and exams from the database
                        include_once('../config/config.php');

                        $folder_sql = "SELECT * FROM folders WHERE " . 
                            ($folderId ? "parent_folder_id = " . intval($folderId) : "parent_folder_id IS NULL") . 
                            " ORDER BY folder_id DESC";
                        $folder_result = $conn->query($folder_sql);

                        if ($folder_result->num_rows > 0) {
                            while ($folder = $folder_result->fetch_assoc()) {
                                echo "<div class='folder-item' onclick='viewFolder({$folder['folder_id']})'>";
                                echo "<div class='name-col'>";
                                echo "<i class='fas fa-folder folder-icon'></i>";
                                echo $folder['folder_name'];
                                echo "</div>";
                                echo "<div class='type-col'>Folder</div>";
                                echo "<div class='date-col'>-</div>";
                                echo "<div class='actions-col'>
                                        <div class='dropdown'>
                                            <button class='btn btn-light btn-sm dropdown-toggle' type='button' data-bs-toggle='dropdown'>
                                                <i class='fas fa-bars'></i>
                                            </button>
                                            <ul class='dropdown-menu'>
                                                <li><a class='dropdown-item' href='#'><i class='fas fa-trash-alt me-2 text-danger'></i>Delete</a></li>
                                                <li><a class='dropdown-item' href='#'><i class='fas fa-edit me-2 text-primary'></i>Rename</a></li>
                                            </ul>
                                        </div>
                                     </div>";
                                echo "</div>";
                            }
                        }

                        // Display exams
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<div class='exam-item' onclick='openExam({$row['exam_id']})'>";
                                echo "<div class='name-col'>";
                                echo "<i class='fas fa-file-alt exam-icon'></i>";
                                echo $row['exam_name'];
                                echo "</div>";
                                echo "<div class='type-col'>Exam</div>";
                                echo "<div class='date-col'>" . $row['schedule_date'] . "</div>";
                                echo '<div class="actions-col">
                                        <div class="dropdown">
                                            <button class="btn btn-light dropdown-toggle" type="button" id="actionMenu' . $row['exam_id'] . '" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-bars"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item text-primary" href="delete_exam.php?exam_id=' . $row['exam_id'] . '">
                                                        <i class="fas fa-trash-alt me-2 text-danger"></i>Delete
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-primary" href="test2.php?exam_id=' . $row['exam_id'] . '">
                                                        <i class="fas fa-plus-circle me-2"></i>Add Questions
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="#" onclick="openMoveModal(' . $row['exam_id'] . ')">
                                                        <i class="fas fa-arrows-alt me-2 text-warning"></i>Move
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="#" onclick="openCopyModal(' . $row['exam_id'] . ')">
                                                        <i class="fas fa-copy me-2 text-info"></i>Copy
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>';
                                echo "</div>";
                            }
                        }
                        ?>
                    </div>
                </div>

                <div id="folderContent" style="display: none;">
                    <button class="btn btn-secondary" onclick="goBack()">Back</button>
                    <h4 id="currentFolderName"></h4>
                    <table class="table mt-3">
                        <thead>
                            <tr>
                                <th>Exam Name</th>
                                <th>Description</th>
                                <th>Duration</th>
                                <th>Schedule Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="folderExams">
                            <!-- Exams dynamically added here -->
                        </tbody>
                    </table>
                </div>

                <!-- Create Exam Modal -->
                <div class="modal fade" id="createExamModal" tabindex="-1" aria-labelledby="createExamModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="createExamModalLabel">Create Exam</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="createExamForm">
                                    <?php if ($folderId): ?>
                                        <input type="hidden" name="folder_id" value="<?php echo $folderId; ?>">
                                    <?php endif; ?>
                                    <div class="mb-3">
                                        <label for="examName" class="form-label">Exam Name:</label>
                                        <input type="text" class="form-control" id="examName" name="exam_name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description:</label>
                                        <textarea class="form-control" id="description" name="description"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="duration" class="form-label">Duration (in minutes):</label>
                                        <input type="number" class="form-control" id="duration" name="duration" value="90">
                                    </div>
                                    <div class="mb-3">
                                        <label for="scheduleDate" class="form-label">Schedule Date:</label>
                                        <input type="date" class="form-control" id="scheduleDate" name="schedule_date" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Student Type:</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="student_type" id="techStudents" value="tech">
                                            <label class="form-check-label" for="techStudents">Tech Students</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="student_type" id="nonTechStudents" value="non-tech">
                                            <label class="form-check-label" for="nonTechStudents">Non-Tech Students</label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="studentYear" class="form-label">Student Year:</label>
                                        <select class="form-control" id="studentYear" name="student_year">
                                            <option value="">Select Year (Optional)</option>
                                            <?php
                                            $currentYear = date('Y');
                                            for($i = 0; $i < 4; $i++) {
                                                $year = $currentYear - $i;
                                                echo "<option value='$year'>$year</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary" id="createExamBtn">Create</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Folder Content Wrapper -->
                <div id="folderContentWrapper"></div>

                <!-- Add Folder Modal -->
                <div class="modal fade" id="addFolderModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="add_folder.php" method="POST">
                                <?php if ($folderId): ?>
                                    <input type="hidden" name="parent_folder_id" value="<?php echo $folderId; ?>">
                                <?php endif; ?>
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <?php echo $folderId ? "Add Subfolder to " . htmlspecialchars($folderName) : "Add Folder"; ?>
                                    </h5>
                                    <button class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="folderName" class="form-label">Enter Folder Name:</label>
                                        <input type="text" id="folderNameInput" name="folder_name" class="form-control" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-primary" type="submit">Add Folder</button>
                                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Move Exam Modal -->
                <div class="modal fade" id="moveExamModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form id="moveExamForm">
                                <div class="modal-header">
                                    <h5 class="modal-title">Move Exam</h5>
                                    <button class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="exam_id" id="moveExamId">
                                    <label for="moveFolderId">Select Folder:</label>
                                    <select name="folder_id" id="moveFolderId" class="form-control">
                                        <option value="">No Folder</option>
                                        <?php
                                        $folder_list = $conn->query("SELECT * FROM folders ORDER BY folder_name ASC");
                                        while ($folder = $folder_list->fetch_assoc()): ?>
                                            <option value="<?= $folder['folder_id'] ?>"><?= htmlspecialchars($folder['folder_name']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-primary" type="submit">Move</button>
                                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Copy Exam Modal -->
                <div class="modal fade" id="copyExamModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form id="copyExamForm">
                                <div class="modal-header">
                                    <h5 class="modal-title">Copy Exam</h5>
                                    <button class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="exam_id" id="copyExamId">
                                    <label for="copyFolderId">Select Folder:</label>
                                    <select name="folder_id" id="copyFolderId" class="form-control">
                                        <option value="">No Folder</option>
                                        <?php
                                        $folder_list = $conn->query("SELECT * FROM folders ORDER BY folder_name ASC");
                                        while ($folder = $folder_list->fetch_assoc()): ?>
                                            <option value="<?= $folder['folder_id'] ?>"><?= htmlspecialchars($folder['folder_name']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-primary" type="submit">Copy</button>
                                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer -->
            <?php include 'footer.php'; ?>
        </div>

        <a class="border rounded d-inline scroll-to-top" href="#page-top">
            <i class="fas fa-angle-up"></i>
        </a>
    </div>

    <!-- Load jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Load Bootstrap JS -->
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>

    <script>
    let currentFolder = null;

    // Dropdown action handlers
    function addAssessment() {
        const modalElement = document.getElementById('createExamModal');
        const modal = new bootstrap.Modal(modalElement);  // Bootstrap 5 method to show modals
        modal.show();
    }

    function addFolder() {
        const modalElement = document.getElementById('addFolderModal');
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    }
    function closeModal() {
        const modalElement = document.getElementById('addFolderModal');
        const modal = bootstrap.Modal.getInstance(modalElement);
        modal.hide();
    }

    function confirmAddFolder() {
        const folderName = document.getElementById('folderName').value;
        if (folderName) {
            document.getElementById('loadingIndicator').style.display = 'block';
            fetch('../admin/process_create_folder.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ folder_name: folderName })
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('loadingIndicator').style.display = 'none';
                if (data.success) {
                    alert('Folder added successfully.');
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                document.getElementById('loadingIndicator').style.display = 'none';
                alert('Failed to add folder.');
            });
        }
    }


    function createFolderElement(folderName, folderId) {
        const folderItem = document.createElement('div');
        folderItem.className = 'folder-item';
        folderItem.setAttribute('onclick', `openFolder(${folderId})`);

        const folderIcon = document.createElement('i');
        folderIcon.className = 'fas fa-folder folder-icon';
        folderItem.appendChild(folderIcon);

        const folderNameText = document.createElement('div');
        folderNameText.innerText = folderName;
        folderItem.appendChild(folderNameText);

        return folderItem;
    }

    function goBackToFolderList() {
        // Restore the folder list view and hide folder content
        document.getElementById('folderList').style.display = 'flex';
        document.getElementById('folderContentWrapper').style.display = 'none';
        currentFolder = null; // Reset to root level when going back
    }

    function viewFolder(folderId) {
        window.location.href = `create-exam.php?folder_id=${folderId}`;
    }

    function openExam(examId) {
        window.location.href = `test2.php?exam_id=${examId}`;
    }

    // Prevent clicks on action buttons from triggering the folder/exam click
    document.querySelectorAll('.actions-col').forEach(el => {
        el.addEventListener('click', (e) => {
            e.stopPropagation();
        });
    });

    function openMoveModal(examId) {
        document.getElementById('moveExamId').value = examId;
        const modal = new bootstrap.Modal(document.getElementById('moveExamModal'));
        modal.show();
    }

    function openCopyModal(examId) {
        document.getElementById('copyExamId').value = examId;
        const modal = new bootstrap.Modal(document.getElementById('copyExamModal'));
        modal.show();
    }

    function openAddQuestionsModal(examId) {
        // Example: Show a modal for adding questions
        document.getElementById('addQuestionsExamId').value = examId; // Set the exam ID in a hidden input field
        const modal = new bootstrap.Modal(document.getElementById('addQuestionsModal'));
        modal.show();
    }

    // Handle form submissions for move and copy
    document.getElementById('moveExamForm').onsubmit = function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('move_exam.php', {
            method: 'POST',
            body: formData
        }).then(response => response.json()).then(data => {
            alert(data.message);
            location.reload();
        }).catch(error => console.error('Error:', error));
    };

    document.getElementById('copyExamForm').onsubmit = function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('copy_exam.php', {
            method: 'POST',
            body: formData
        }).then(response => response.json()).then(data => {
            alert(data.message);
            location.reload();
        }).catch(error => console.error('Error:', error));
    };


    function goBack(parentFolderId) {
        if (parentFolderId) {
            window.location.href = `create-exam.php?folder_id=${parentFolderId}`;
        } else {
            window.location.href = 'create-exam.php';
        }
    }

    document.getElementById('createExamBtn').addEventListener('click', function() {
        const form = document.getElementById('createExamForm');
        const formData = new FormData(form);

        // Validate student type selection
        if (!formData.get('student_type')) {
            alert('Please select a student type');
            return;
        }

        // Validate year selection
        if (!formData.get('student_year')) {
            alert('Please select a student year');
            return;
        }

        fetch('process_create_exam.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Exam created and automatically assigned to students');
                window.location.href = `test2.php?exam_id=${data.exam_id}`;
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error creating exam');
        });
    });

    // Search functionality
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const items = document.querySelectorAll('.folder-item, .exam-item');
        
        items.forEach(item => {
            const name = item.querySelector('.name-col').textContent.toLowerCase();
            const type = item.querySelector('.type-col').textContent.toLowerCase();
            if (name.includes(searchTerm) || type.includes(searchTerm)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });

    function clearSearch() {
        searchInput.value = '';
        searchInput.dispatchEvent(new Event('input'));
    }

    // Sorting functionality
    const sortButtons = document.querySelectorAll('.sort-button');
    sortButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Update active state
            sortButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            const sortBy = this.dataset.sort;
            const items = Array.from(document.querySelectorAll('.folder-item, .exam-item'));
            const container = document.getElementById('folderList');

            items.sort((a, b) => {
                const aValue = a.querySelector(`.${sortBy}-col`).textContent.toLowerCase();
                const bValue = b.querySelector(`.${sortBy}-col`).textContent.toLowerCase();

                if (sortBy === 'date') {
                    return new Date(bValue) - new Date(aValue);
                }
                return aValue.localeCompare(bValue);
            });

            // Preserve header
            const header = container.querySelector('.list-header');
            container.innerHTML = '';
            container.appendChild(header);
            items.forEach(item => container.appendChild(item));
        });
    });
    </script>

</body>
</html>
