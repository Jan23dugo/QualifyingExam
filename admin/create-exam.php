<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Create Exam - Brand</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i&amp;display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans&amp;display=swap">
    <link rel="stylesheet" href="assets/fonts/fontawesome-all.min.css">
    <link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="assets/fonts/fontawesome5-overrides.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css">
    <link rel="stylesheet" href="https://cdn.quilljs.com/1.0.0/quill.snow.css">
    <link rel="stylesheet" href="assets/css/styles.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .time-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            width: 70%;
        }

        .time-input {
            flex: 1;
            padding-right: 40px;
            text-align: center;
            cursor: pointer;
        }

        .arrow-buttons {
            position: absolute;
            right: 10px;
            display: flex;
            flex-direction: column;
        }

        .arrow-buttons button {
            background: none;
            border: none;
            cursor: pointer;
        }

        .highlight {
            background-color: #d1ecf1;
            border-radius: 4px;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .add-dropdown-wrapper {
            margin-bottom: 20px;
            position: relative;
            top: 10px;
            left: 10px;
        }

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
                    <!-- Folders will be added here dynamically -->
                </div>

                <!-- Create Exam Modal -->
                <div class="modal fade" role="dialog" tabindex="-1" id="createExamModal">
                    <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form action="../admin/process_create_exam.php" method="POST">
                            <div class="modal-header">
                        <h4 class="modal-title">Create Exam</h4>
                            <button class="btn-close" type="button" aria-label="Close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" style="height: auto;padding: 36px;margin: 9px;">
                        <div class="input-group">
                            <span class="input-group-text">Exam Name:</span>
                            <input class="form-control" type="text" name="exam_name" required>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text">Description:</span>
                        <input class="form-control" type="text" name="description">
                    </div>
                    <div class="input-group">
                        <span class="input-group-text">Duration (in minutes):</span>
                        <input class="form-control" type="text" name="duration" placeholder="eg. 90 minutes" required>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text">Schedule Date:</span>
                        <input class="form-control" type="date" name="schedule_date" required>
                    </div>
                </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">Create</button>
                    <button class="btn btn-light" type="button" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>


                <!-- Custom Folder Modal -->
                <div class="modal-custom" id="folderModal">
                    <div>Enter folder name:</div>
                    <input type="text" id="folderNameInput">
                    <div class="modal-custom-buttons">
                        <button class="btn-cancel" onclick="closeModal()">Cancel</button>
                        <button class="btn-ok" onclick="confirmAddFolder()">OK</button>
                    </div>
                </div>
                <!-- Footer -->
                <?php include 'footer.php'; ?>
            </div>
        </div>
        <a class="border rounded d-inline scroll-to-top" href="#page-top">
            <i class="fas fa-angle-up"></i>
        </a>
        
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="https://cdn.quilljs.com/1.0.0/quill.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
    <script src="assets/js/script.min.js"></script>
    <script>
    let currentFolder = null;

    // Dropdown action handlers
    function addAssessment() {
        // Show Create Exam Modal when "Add Assessment" is clicked
        $('#createExamModal').modal('show');
    }

    function addFolder() {
        const modal = document.getElementById('folderModal');
        if (modal) {
            modal.style.display = 'block';
        }
    }

    function closeModal() {
        const modal = document.getElementById('folderModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    function confirmAddFolder() {
        const folderName = document.getElementById('folderNameInput').value;
        if (folderName) {
            const folderItem = createFolderElement(folderName);
            const targetFolder = currentFolder || document.getElementById('folderList');
            targetFolder.appendChild(folderItem);
        }
        closeModal();
    }

    function createFolderElement(folderName) {
        const folderItem = document.createElement('div');
        folderItem.className = 'folder-item';

        const folderIcon = document.createElement('i');
        folderIcon.className = 'fas fa-folder folder-icon';
        folderItem.appendChild(folderIcon);

        const folderNameText = document.createElement('div');
        folderNameText.innerText = folderName;
        folderItem.appendChild(folderNameText);

        folderItem.setAttribute('onclick', 'openFolder(this)');
        return folderItem;
    }

    function openFolder(folderElement) {
        currentFolder = folderElement;

        // Hide the folder list and display folder content
        document.getElementById('folderList').style.display = 'none';
        document.getElementById('addDropdown').style.display = 'block';

        const contentWrapper = document.getElementById('folderContentWrapper');
        contentWrapper.innerHTML = '';

        const backButton = document.createElement('div');
        backButton.className = 'back-button';
        backButton.innerHTML = '&larr; Back';
        backButton.onclick = function() {
            goBackToFolderList();
        };

        contentWrapper.appendChild(backButton);

        const folderContent = document.createElement('div');
        folderContent.className = 'folder-content';
        folderContent.innerHTML = `<h5>Contents of ${folderElement.querySelector('div').innerText}</h5>`;

        // Add functionality to create subfolders within the current folder
        const subFolderList = document.createElement('div');
        subFolderList.className = 'folder-list';
        subFolderList.id = 'subFolderList';

        folderContent.appendChild(subFolderList);
        contentWrapper.appendChild(folderContent);

        contentWrapper.style.display = 'block'; // Show the folder content

        // Update the global currentFolder to the new folderContent so subfolders are added here
        currentFolder = subFolderList;
    }

    function goBackToFolderList() {
        // Restore the folder list view and hide folder content
        document.getElementById('folderList').style.display = 'flex';
        document.getElementById('folderContentWrapper').style.display = 'none';
        currentFolder = null; // Reset to root level when going back
    }
</script>



</body>
</html>
