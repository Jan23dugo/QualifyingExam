<?php
// Connect to the database
require_once '../config/config.php';

// Determine the folder being viewed
$folderId = isset($_GET['folder_id']) ? $_GET['folder_id'] : null;

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
        .folder-list {
            margin-top: 20px;
            display: flex;
            flex-wrap: wrap;
        }

        .folder-item {
            width: 150px;
            margin: 10px;
            text-align: center;
            cursor: pointer;
        }

        .folder-icon {
            font-size: 60px;
            color: #f0ad4e;
        }

        .folder-content {
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-top: 10px;
            background-color: #ffffff;
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

                <!-- Exam Creation Section -->
                <div class="add-dropdown-wrapper" id="addDropdown">
                    <!-- + Add Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            + Add
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <li><a class="dropdown-item" href="#" onclick="addAssessment()">Add Assessment</a></li>
                            <li><a class="dropdown-item" href="#" onclick="addFolder()">Add Folder</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Folder List Section -->
                <div class="folder-list" id="folderList">
                    <!-- Folders and Exams will be added here dynamically -->
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Exam Name</th>
                                    <th>Description</th>
                                    <th>Duration</th>
                                    <th>Schedule Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Display exams
                                if ($result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . $row['exam_name'] . "</td>";
                                        echo "<td>" . $row['description'] . "</td>";
                                        echo "<td>" . $row['duration'] . " minutes</td>";
                                        echo "<td>" . $row['schedule_date'] . "</td>";
                                        echo '
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-light dropdown-toggle" type="button" id="actionMenu' . $row['exam_id'] . '" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fas fa-bars"></i> <!-- Hamburger icon -->
                                                    </button>
                                                    <ul class="dropdown-menu" aria-labelledby="actionMenu' . $row['exam_id'] . '">
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
                                            </td>
                                            ';

                                        echo "</tr>";
                                        
                                    }
                                } else {
                                    echo "<tr><td colspan='6'>No exams created yet. Click '+ Add' to create a new exam.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>

                    <div class="folder-list" id="folderList">
                        <?php
                        // Fetch folders and exams from the database
                        include_once('../config/config.php');

                        $folder_sql = "SELECT * FROM folders ORDER BY folder_id DESC";
                        $folder_result = $conn->query($folder_sql);

                        if ($folder_result->num_rows > 0) {
                            while ($folder = $folder_result->fetch_assoc()) {
                                echo "<div class='folder-item' onclick='viewFolder({$folder['folder_id']})'>";
                                echo "<i class='fas fa-folder folder-icon'></i>";
                                echo "<p>{$folder['folder_name']}</p>";
                                echo "</div>";
                            }
                        } else {
                            echo "<p>No folders available. Click '+ Add Folder' to create one.</p>";
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
                                    <div class="mb-3">
                                        <label for="folder" class="form-label">Folder:</label>
                                        <select name="folder_id" class="form-control">
                                            <option value="0">No Folder</option>
                                            <?php
                                            $folder_list = $conn->query("SELECT * FROM folders ORDER BY folder_name ASC");
                                            while ($folder = $folder_list->fetch_assoc()): ?>
                                                <option value="<?= $folder['folder_id'] ?>"><?= htmlspecialchars($folder['folder_name']) ?></option>
                                            <?php endwhile; ?>
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
                                <div class="modal-header">
                                    <h5 class="modal-title">Add Folder</h5>
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
        document.getElementById('folderList').style.display = 'none';
        document.getElementById('folderContent').style.display = 'block';
        document.getElementById('currentFolderName').innerText = `Folder ${folderId}`;

            // Fetch exams for the folder using AJAX
            fetch(`fetch_exams.php?folder_id=${folderId}`)
                .then(response => response.json())
                .then(data => {
                    const examsTableBody = document.getElementById('folderExams');
                    if (data.exams && data.exams.length > 0) {
                        examsTableBody.innerHTML = data.exams.map(exam => `
                            <tr>
                                <td>${exam.exam_name || 'N/A'}</td>
                                <td>${exam.description || 'N/A'}</td>
                                <td>${exam.duration || 'N/A'}</td>
                                <td>${exam.schedule_date || 'N/A'}</td>
                                <td>
                                    <button class="btn btn-danger" onclick="deleteExam(${exam.exam_id})">Delete</button>
                                </td>
                            </tr>
                        `).join('');
                    } else {
                        examsTableBody.innerHTML = `<tr><td colspan="5" class="text-center">No exams found in this folder.</td></tr>`;
                    }
                })
            }

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


        function goBack() {
            document.getElementById('folderList').style.display = 'flex';
            document.getElementById('folderContent').style.display = 'none';
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
    </script>

</body>
</html>
