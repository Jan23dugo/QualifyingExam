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

// Helper function to check if the exam is scheduled
function isExamScheduled($exam) {
    return $exam['status'] === 'scheduled' && !empty($exam['exam_date']) && !empty($exam['exam_time']);
}

// Format schedule date and time
function formatScheduleDateTime($schedule_date) {
    if (empty($schedule_date)) return ['date' => '', 'time' => ''];
    $date = date('Y-m-d', strtotime($schedule_date));
    $time = date('h:i A', strtotime($schedule_date));
    return ['date' => $date, 'time' => $time];
}

// Get exam status
function getExamStatus($exam) {
    if ($exam['status'] === 'unscheduled') {
        return ['class' => 'warning', 'text' => 'Not Scheduled'];
    }

    $examDateTime = $exam['exam_date'] . ' ' . $exam['exam_time'];
    $now = new DateTime();
    $startTime = new DateTime($examDateTime);
    $endTime = clone $startTime;
    $endTime->add(new DateInterval('PT' . $exam['duration'] . 'M'));

    if ($now < $startTime) {
        return ['class' => 'info', 'text' => 'Upcoming'];
    } elseif ($now > $endTime) {
        return ['class' => 'success', 'text' => 'Completed'];
    } else {
        return ['class' => 'warning', 'text' => 'In Progress'];
    }
}
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
    
    <!-- Load jQuery first -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Load Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

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
            width: 45%;
            display: flex;
            align-items: center;
        }

        .type-col {
            width: 15%;
        }

        .date-col {
            width: 25%;
            line-height: 1.4;
        }

        .time-col {
            width: 15%;
            line-height: 1.4;
        }

        .date-col br {
            margin: 2px 0;
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
            width: 100%;
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
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transform: none;
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

        /* Update these styles to fix the dropdown behavior */
        .folder-item, .exam-item {
            position: relative;
            background: #fff;
            z-index: 1;
        }

        .actions-col {
            position: relative;
            z-index: 1;
        }

        .actions-col .dropdown {
            position: relative;
        }

        .actions-col .dropdown-menu {
            z-index: 9999;
            margin-top: 2px;
            min-width: 160px;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            border: 1px solid rgba(0,0,0,0.1);
        }

        .actions-col .dropdown.show .dropdown-menu {
            display: block;
        }

        .folder-item, .exam-item {
            position: relative;
            z-index: 1;
        }

        .folder-item:hover, .exam-item:hover {
            background-color: #e9ecef;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            z-index: 2;
        }

        .actions-col .dropdown.show {
            z-index: 9999;
        }

        /* Ensure the dropdown toggle button has proper z-index */
        .actions-col .dropdown-toggle {
            position: relative;
            z-index: 4;
        }

        /* Ensure containers don't clip the dropdown */
        .content-container,
        #content-wrapper,
        #content {
            overflow: visible !important;
        }

        /* Update the styles for the action button and dropdown */
        .actions-col .dropdown-toggle {
            padding: 4px 8px;  /* Reduce padding */
            font-size: 14px;   /* Reduce font size */
            line-height: 1;    /* Adjust line height */
        }

        .actions-col .dropdown-toggle .fas {
            font-size: 12px;   /* Reduce icon size */
        }

        .actions-col .dropdown-menu {
            min-width: 120px;  /* Reduce minimum width */
            font-size: 14px;   /* Match font size */
            padding: 4px 0;    /* Reduce padding */
        }

        .actions-col .dropdown-menu .dropdown-item {
            padding: 6px 12px; /* Adjust item padding */
            font-size: 13px;   /* Slightly smaller font for items */
        }

        /* Make the button more compact */
        .actions-col .btn-light.btn-sm {
            padding: 4px 8px;
            font-size: 14px;
        }

        /* Adjust the icon size in the button */
        .actions-col .btn-light .fas {
            font-size: 12px;
        }

        .dropdown-item.text-danger:hover {
            background-color: #dc3545;
            color: white !important;
        }
        
        .dropdown-item.text-danger:hover i {
            color: white !important;
        }

        .dropdown-toggle {
            padding: 6px 12px;
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }

        .dropdown-toggle:hover {
            background-color: #f8f9fa;
            border-color: #c1c9d0;
        }

        .dropdown-menu {
            padding: 0.5rem 0;
            border: 1px solid rgba(0,0,0,.15);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15);
            border-radius: 4px;
        }

        .dropdown-item {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        .dropdown-item i {
            width: 16px;
            margin-right: 0.5rem;
        }

        .dropdown-item.text-danger:hover {
            background-color: #fee2e2;
            color: #dc3545 !important;
        }

        .modern-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin: 20px 0;
        }

        .modern-table th {
            background: #f8f9fa;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            color: #2c3e50;
            border-bottom: 2px solid #eee;
        }

        .modern-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            color: #444;
        }

        .modern-table tr:hover {
            background-color: #f8f9fa;
        }

        .modern-table tr:last-child td {
            border-bottom: none;
        }

        .btn-modern {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .btn-primary-modern {
            background: #4361ee;
            color: white;
        }

        .btn-primary-modern:hover {
            background: #3451db;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(67, 97, 238, 0.3);
        }

        .btn-danger-modern {
            background: #ef4444;
            color: white;
        }

        .btn-danger-modern:hover {
            background: #dc2626;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
        }

        .exam-actions {
            display: flex;
            gap: 8px;
        }

        .form-container {
            background: white;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
        }

        .form-title {
            font-size: 1.5rem;
            color: #2c3e50;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .modern-dropdown-menu {
            padding: 0.5rem 0;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            background: white;
            margin-top: 5px;
        }

        .modern-dropdown-menu .dropdown-item {
            padding: 0.7rem 1rem;
            color: #2c3e50;
            font-size: 14px;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
        }

        .modern-dropdown-menu .dropdown-item:hover {
            background-color: #f8f9fa;
            color: #4361ee;
        }

        .modern-dropdown-menu .dropdown-item i {
            width: 20px;
            text-align: center;
            margin-right: 8px;
        }

        /* Update existing button styles */
        .btn-modern.dropdown-toggle::after {
            margin-left: 8px;
            vertical-align: middle;
        }

        .actions-col .dropdown-toggle {
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
            color: #2c3e50;
            background: white;
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
        }

        .actions-col .dropdown-toggle:hover {
            background: #f8f9fa;
            border-color: #cbd5e1;
        }

        .actions-col .dropdown-menu {
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: 1px solid rgba(0,0,0,0.1);
            padding: 0.5rem 0;
        }

        /* Update the dropdown styles to be consistent */
        .dropdown-toggle.btn-link {
            background: none;
            border: none;
            padding: 8px;
            color: #6c757d;
            box-shadow: none !important;
        }

        .dropdown-toggle.btn-link:hover {
            color: #4361ee;
            background: none;
        }

        .dropdown-toggle.btn-link::after {
            display: none;
        }

        .dropdown-toggle .fas.fa-ellipsis-v {
            font-size: 16px;
        }

        .dropdown-menu .dropdown-item i {
            width: 20px;
            margin-right: 8px;
            font-size: 14px;
        }

        .toast-container {
            z-index: 9999;
        }

        .toast {
            opacity: 1 !important;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 10px;
        }

        .toast.bg-success {
            background-color: #10b981 !important;
        }

        .toast.bg-danger {
            background-color: #ef4444 !important;
        }

        .toast .toast-body {
            padding: 12px 16px;
            font-size: 14px;
            font-weight: 500;
        }

        /* Modal size and layout adjustments */
        #createExamModal .modal-dialog {
            max-width: 800px;
            margin: 1.75rem auto;
        }

        #createExamModal .modal-content {
            border-radius: 8px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }

        #createExamModal .modal-header {
            padding: 1.25rem 1.5rem;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        #createExamModal .modal-body {
            padding: 1.5rem;
        }

        #createExamModal .modal-footer {
            padding: 1.25rem 1.5rem;
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }

        /* Form field improvements */
        #createExamModal .form-control {
            padding: 0.625rem 1rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 6px;
        }

        #createExamModal textarea.form-control {
            min-height: 120px;
        }

        #createExamModal .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #2c3e50;
        }

        /* Section styling */
        #createExamModal .section {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }

        #createExamModal .section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        #createExamModal .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        /* Radio buttons and checkboxes */
        #createExamModal .form-check {
            margin-bottom: 0.5rem;
            padding-left: 1.75rem;
        }

        #createExamModal .form-check-input {
            margin-top: 0.3rem;
        }

        #createExamModal .form-check-label {
            font-size: 0.95rem;
        }

        /* Help text */
        #createExamModal .form-text {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 0.25rem;
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
                            <i class="fas fa-book"></i>
                            <span>Exam Library</span>
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
                            <button class="btn-modern btn-primary-modern dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-plus"></i> Add
                            </button>
                            <ul class="dropdown-menu modern-dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <li>
                                    <a class="dropdown-item" href="#" onclick="addAssessment()">
                                        <i class="fas fa-file-alt me-2"></i> Add Exam
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#" onclick="addFolder()">
                                        <i class="fas fa-folder me-2"></i> Add Folder
                                    </a>
                                </li>
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
                            <div class="date-col">Date</div>
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
                                        <div class='dropdown' onclick='event.stopPropagation();'>
                                            <button class='btn btn-link dropdown-toggle' type='button' data-bs-toggle='dropdown' aria-expanded='false'>
                                                <i class='fas fa-ellipsis-v'></i>
                                            </button>
                                            <div class='dropdown-menu dropdown-menu-end'>
                                                <a class='dropdown-item' href='#' onclick='event.stopPropagation(); editFolder({$folder['folder_id']}, \"{$folder['folder_name']}\")'>
                                                    <i class='fas fa-edit'></i> Edit
                                                </a>
                                                <a class='dropdown-item text-danger' href='javascript:void(0);' onclick='event.stopPropagation(); deleteFolder({$folder['folder_id']});'>
                                                    <i class='fas fa-trash'></i> Delete
                                                </a>
                                            </div>
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
                                if (isExamScheduled($row)) {
                                    $status = getExamStatus($row);
                                    echo "<div class='date-col'>Date: " . date('Y-m-d', strtotime($row['exam_date'])) . 
                                         "<br>Time: " . date('h:i A', strtotime($row['exam_time'])) . 
                                         "<br><span class='badge bg-" . $status['class'] . "'>" . $status['text'] . "</span></div>";
                                } else {
                                    echo "<div class='date-col'>Not Scheduled</div>";
                                }
                                echo '<div class="actions-col">
                                        <div class="dropdown" onclick="event.stopPropagation();">
                                            <button class="btn btn-link dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item" href="#" onclick="event.stopPropagation(); editExam(' . $row['exam_id'] . ', \'' . htmlspecialchars($row['exam_name']) . '\', \'' . htmlspecialchars($row['description']) . '\', ' . $row['duration'] . ', \'' . $row['exam_date'] . '\', \'' . $row['exam_time'] . '\')">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a></li>
                                                <li><a class="dropdown-item text-danger" href="#" onclick="event.stopPropagation(); deleteExam(' . $row['exam_id'] . ')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </a></li>
                                                <li><a class="dropdown-item" href="test2.php?exam_id=' . $row['exam_id'] . '" onclick="event.stopPropagation();">
                                                    <i class="fas fa-question-circle"></i> Manage Questions
                                                </a></li>
                                                <li><a class="dropdown-item" href="#" onclick="event.stopPropagation(); openMoveModal(' . $row['exam_id'] . ')">
                                                    <i class="fas fa-arrows-alt"></i> Move
                                                </a></li>
                                                <li><a class="dropdown-item" href="#" onclick="event.stopPropagation(); openCopyModal(' . $row['exam_id'] . ')">
                                                    <i class="fas fa-copy"></i> Copy
                                                </a></li>
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
                    <div class="modal-dialog modal-lg">
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
                                    
                                    <!-- Exam Details Section -->
                                    <div class="section">
                                        <h6 class="section-title">Exam Details</h6>
                                        <div class="mb-3">
                                            <label for="examName" class="form-label">Exam Name</label>
                                            <input type="text" class="form-control" id="examName" name="exam_name" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="description" class="form-label">Description</label>
                                            <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="duration" class="form-label">Duration (in minutes)</label>
                                            <input type="number" class="form-control" id="duration" name="duration" value="90" min="1">
                                        </div>
                                    </div>

                                    <!-- Schedule Section -->
                                    <div class="section">
                                        <h6 class="section-title">Schedule</h6>
                                        <div class="mb-3">
                                            <label for="scheduleDate" class="form-label">Schedule Date (Optional)</label>
                                            <input type="date" class="form-control" id="scheduleDate" name="schedule_date">
                                            <div class="form-text">Leave empty to create an unscheduled exam</div>
                                        </div>
                                    </div>

                                    <!-- Student Type Section -->
                                    <div class="section">
                                        <h6 class="section-title">Student Type</h6>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="student_type" id="techStudents" value="tech" required>
                                            <label class="form-check-label" for="techStudents">Tech Students</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="student_type" id="nonTechStudents" value="non-tech">
                                            <label class="form-check-label" for="nonTechStudents">Non-Tech Students</label>
                                        </div>
                                    </div>

                                    <!-- Student Year Section -->
                                    <div class="section">
                                        <h6 class="section-title">Student Year</h6>
                                        <div class="mb-3">
                                            <select class="form-select" id="studentYear" name="student_year">
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
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" id="createExamBtn">Create Exam</button>
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

                <!-- Replace both editExamModal and manageScheduleModal with this new unified modal -->
                <div class="modal fade" id="editExamModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Exam</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form id="editExamForm">
                                    <input type="hidden" id="editExamId" name="exam_id">
                                    
                                    <!-- Exam Details Section -->
                                    <div class="mb-4">
                                        <h6 class="mb-3">Exam Details</h6>
                                        <div class="mb-3">
                                            <label for="editExamName" class="form-label">Exam Name:</label>
                                            <input type="text" class="form-control" id="editExamName" name="exam_name" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="editDescription" class="form-label">Description:</label>
                                            <textarea class="form-control" id="editDescription" name="description" rows="3"></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="editDuration" class="form-label">Duration (minutes):</label>
                                            <input type="number" class="form-control" id="editDuration" name="duration" required>
                                        </div>
                                    </div>

                                    <!-- Schedule Section -->
                                    <div class="mb-4">
                                        <h6 class="mb-3">Exam Schedule</h6>
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="scheduleEnabled" onchange="toggleScheduleFields()">
                                                <label class="form-check-label" for="scheduleEnabled">Enable Schedule</label>
                                            </div>
                                        </div>
                                        <div id="scheduleFields" style="display: none;">
                                            <div class="mb-3">
                                                <label class="form-label">Date:</label>
                                                <input type="date" class="form-control" id="scheduleDate" name="exam_date">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Time:</label>
                                                <input type="time" class="form-control" id="scheduleTime" name="exam_time">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Student Type Section -->
                                    <div class="mb-3">
                                        <label id="studentTypeLabel" class="form-label">Student Type:</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="student_type" id="techStudents" value="tech">
                                            <label id="techStudentsLabel" class="form-check-label" for="techStudents">Tech Students</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="student_type" id="nonTechStudents" value="non-tech">
                                            <label id="nonTechStudentsLabel" class="form-check-label" for="nonTechStudents">Non-Tech Students</label>
                                        </div>
                                    </div>

                                    <!-- Student Year Section -->
                                    <div class="mb-3">
                                        <label id="studentYearLabel" for="studentYear" class="form-label">Student Year:</label>
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
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" onclick="saveExamChanges()">Save Changes</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delete Folder Confirmation Modal -->
                <div class="modal fade" id="deleteFolderModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Delete Folder</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-exclamation-triangle text-warning me-3" style="font-size: 24px;"></i>
                                    <p class="mb-0">Are you sure you want to delete this folder? All exams inside will be moved to the root level.</p>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-danger" id="confirmDeleteFolder">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delete Exam Confirmation Modal -->
                <div class="modal fade" id="deleteExamModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Delete Exam</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-exclamation-triangle text-warning me-3" style="font-size: 24px;"></i>
                                    <p class="mb-0">Are you sure you want to delete this exam? This action cannot be undone.</p>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-danger" id="confirmDeleteExam">Delete</button>
                            </div>
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

    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <!-- Success Toast -->
        <div class="toast align-items-center text-white bg-success border-0" id="successToast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-check-circle me-2"></i>
                    <span id="successToastMessage"></span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
        
        <!-- Error Toast -->
        <div class="toast align-items-center text-white bg-danger border-0" id="errorToast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <span id="errorToastMessage"></span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script>
    // First, define the utility functions
    function showSuccessToast(message) {
        const toast = document.getElementById('successToast');
        const toastMessage = document.getElementById('successToastMessage');
        toastMessage.textContent = message;
        
        const bsToast = new bootstrap.Toast(toast, {
            autohide: true,
            delay: 3000
        });
        bsToast.show();
    }

    function showErrorToast(message) {
        const toast = document.getElementById('errorToast');
        const toastMessage = document.getElementById('errorToastMessage');
        toastMessage.textContent = message;
        
        const bsToast = new bootstrap.Toast(toast, {
            autohide: true,
            delay: 5000
        });
        bsToast.show();
    }

    function setButtonLoading(button, isLoading) {
        if (isLoading) {
            button.disabled = true;
            const originalText = button.innerHTML;
            button.setAttribute('data-original-text', originalText);
            button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
        } else {
            button.disabled = false;
            const originalText = button.getAttribute('data-original-text');
            button.innerHTML = originalText;
        }
    }

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
        
        // Reset modal title
        modalElement.querySelector('.modal-title').textContent = 'Add Folder';
        
        // Clear the folder name input
        const folderNameInput = document.getElementById('folderNameInput');
        folderNameInput.value = '';
        
        // Update the form action
        const form = modalElement.querySelector('form');
        form.action = 'handlers/add_folder.php';
        
        // Remove any existing folder ID input
        const existingFolderIdInput = form.querySelector('input[name="folder_id"]');
        if (existingFolderIdInput) {
            existingFolderIdInput.remove();
        }
        
        // Update submit button text
        const submitButton = form.querySelector('button[type="submit"]');
        submitButton.textContent = 'Add Folder';
        
        // Reset form submission handler
        form.onsubmit = function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            
            fetch('handlers/add_folder.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    modal.hide();
                    showSuccessToast('Folder created successfully');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    throw new Error(data.message || 'Failed to create folder');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorToast(error.message || 'Error creating folder');
            });
        };
        
        modal.show();
    }

    function editFolder(folderId, folderName) {
        // Get the modal element
        const modalElement = document.getElementById('addFolderModal');
        const modal = new bootstrap.Modal(modalElement);
        
        // Update modal title
        modalElement.querySelector('.modal-title').textContent = 'Edit Folder';
        
        // Set the folder name in the input
        const folderNameInput = document.getElementById('folderNameInput');
        folderNameInput.value = folderName;
        
        // Update the form action and add folder ID
        const form = modalElement.querySelector('form');
        form.action = 'handlers/update_folder.php';
        
        // Add or update hidden folder ID input
        let folderIdInput = form.querySelector('input[name="folder_id"]');
        if (!folderIdInput) {
            folderIdInput = document.createElement('input');
            folderIdInput.type = 'hidden';
            folderIdInput.name = 'folder_id';
            form.appendChild(folderIdInput);
        }
        folderIdInput.value = folderId;
        
        // Update submit button text
        const submitButton = form.querySelector('button[type="submit"]');
        submitButton.textContent = 'Save Changes';
        
        // Show the modal
        modal.show();
        
        // Update form submission handler
        form.onsubmit = function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            
            fetch('handlers/update_folder.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    modal.hide();
                    showSuccessToast('Folder updated successfully');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    throw new Error(data.message || 'Failed to update folder');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorToast(error.message || 'Error updating folder');
            });
        };
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
        
        // Add student type validation
        const studentType = formData.get('student_type');
        if (!studentType) {
            alert('Please select a student type');
            return;
        }
        
        // Show loading indicator
        const btn = this;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Creating...';
        btn.disabled = true;

        fetch('handlers/save_exam.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('createExamModal'));
                modal.hide();
                location.reload();
            } else {
                throw new Error(data.message || 'Error creating exam');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert(error.message || 'Error creating exam. Please try again.');
        })
        .finally(() => {
            // Restore button state
            btn.innerHTML = originalText;
            btn.disabled = false;
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

    document.addEventListener('DOMContentLoaded', function() {
        // Handle dropdown toggles
        document.querySelectorAll('.actions-col .dropdown-toggle').forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                e.preventDefault();
                
                const dropdown = this.closest('.dropdown');
                const menu = dropdown.querySelector('.dropdown-menu');
                
                // Close all other dropdowns
                document.querySelectorAll('.actions-col .dropdown.show').forEach(otherDropdown => {
                    if (otherDropdown !== dropdown) {
                        otherDropdown.classList.remove('show');
                        otherDropdown.querySelector('.dropdown-menu').style.display = 'none';
                    }
                });
                
                // Toggle current dropdown
                dropdown.classList.toggle('show');
                menu.style.display = dropdown.classList.contains('show') ? 'block' : 'none';
            });
        });
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.actions-col .dropdown')) {
                document.querySelectorAll('.actions-col .dropdown.show').forEach(dropdown => {
                    dropdown.classList.remove('show');
                    dropdown.querySelector('.dropdown-menu').style.display = 'none';
                });
            }
        });
        
        // Prevent folder/exam click when clicking dropdown items
        document.querySelectorAll('.actions-col .dropdown-menu').forEach(menu => {
            menu.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });

        // Add sidebar toggle functionality
        const sidebarToggle = document.body.querySelector('#sidebarToggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function(e) {
                e.preventDefault();
                document.body.classList.toggle('sidebar-toggled');
                document.querySelector('.sidebar').classList.toggle('toggled');
                
                // Optional: Save the state in localStorage
                const isToggled = document.querySelector('.sidebar').classList.contains('toggled');
                localStorage.setItem('sidebarToggled', isToggled);
            });
        }

        // Optional: Restore sidebar state from localStorage on page load
        const sidebarState = localStorage.getItem('sidebarToggled');
        if (sidebarState === 'true') {
            document.body.classList.add('sidebar-toggled');
            document.querySelector('.sidebar').classList.add('toggled');
        }

        // Add responsive behavior
        const mediaQuery = window.matchMedia('(max-width: 768px)');
        function handleScreenChange(e) {
            if (e.matches && !document.querySelector('.sidebar').classList.contains('toggled')) {
                document.body.classList.add('sidebar-toggled');
                document.querySelector('.sidebar').classList.add('toggled');
            }
        }
        mediaQuery.addListener(handleScreenChange);
        handleScreenChange(mediaQuery);

        // Also add top sidebar toggle functionality
        const sidebarToggleTop = document.body.querySelector('#sidebarToggleTop');
        if (sidebarToggleTop) {
            sidebarToggleTop.addEventListener('click', function(e) {
                e.preventDefault();
                document.body.classList.toggle('sidebar-toggled');
                document.querySelector('.sidebar').classList.toggle('toggled');
            });
        }
    });

    // Add these functions to handle exam editing
    function editExam(examId, examName, description, duration, examDate, examTime) {
        // Populate exam details
        document.getElementById('editExamId').value = examId;
        document.getElementById('editExamName').value = examName;
        document.getElementById('editDescription').value = description;
        document.getElementById('editDuration').value = duration;
        
        // Handle schedule
        const scheduleEnabled = document.getElementById('scheduleEnabled');
        const scheduleFields = document.getElementById('scheduleFields');
        const dateInput = document.getElementById('scheduleDate');
        const timeInput = document.getElementById('scheduleTime');
        
        // Check if exam has a schedule
        const hasSchedule = examDate && examTime && examDate !== 'null' && examTime !== 'null';
        
        // Set schedule enabled state based on existing schedule
        scheduleEnabled.checked = hasSchedule;
        scheduleFields.style.display = hasSchedule ? 'block' : 'none';
        
        // Store and set the date and time values if they exist
        if (examDate && examDate !== 'null') {
            dateInput.value = examDate;
            dateInput.setAttribute('data-original-date', examDate);
        }
        if (examTime && examTime !== 'null') {
            timeInput.value = examTime;
            timeInput.setAttribute('data-original-time', examTime);
        }
        
        const modal = new bootstrap.Modal(document.getElementById('editExamModal'));
        modal.show();
    }

    function saveExamChanges() {
        const form = document.getElementById('editExamForm');
        const formData = new FormData(form);
        const scheduleEnabled = document.getElementById('scheduleEnabled').checked;
        const dateInput = document.getElementById('scheduleDate');
        const timeInput = document.getElementById('scheduleTime');
        
        // Preserve schedule if enabled and has values
        if (scheduleEnabled) {
            // Only update if the fields have values
            if (dateInput.value && timeInput.value) {
                formData.set('exam_date', dateInput.value);
                formData.set('exam_time', timeInput.value);
            } else {
                // Keep existing values if no new values are provided
                const existingDate = dateInput.getAttribute('data-original-date');
                const existingTime = timeInput.getAttribute('data-original-time');
                if (existingDate && existingTime) {
                    formData.set('exam_date', existingDate);
                    formData.set('exam_time', existingTime);
                }
            }
        } else {
            // Clear schedule if disabled
            formData.set('exam_date', '');
            formData.set('exam_time', '');
        }
        
        formData.append('enabled', scheduleEnabled);

        // Show loading state on the save button
        const saveButton = document.querySelector('[onclick="saveExamChanges()"]');
        setButtonLoading(saveButton, true);

        fetch('handlers/update_exam.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('editExamModal'));
                modal.hide();
                showSuccessToast('Exam updated successfully!');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                throw new Error(data.message || 'Failed to update exam');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showErrorToast(error.message || 'Error updating exam');
        })
        .finally(() => {
            setButtonLoading(saveButton, false);
        });
    }

    function manageSchedule(examId, examName, scheduleDate) {
        document.getElementById('scheduleExamId').value = examId;
        document.getElementById('scheduleExamName').value = examName;
        
        const scheduleEnabled = document.getElementById('scheduleEnabled');
        const scheduleFields = document.getElementById('scheduleFields');
        const dateInput = document.getElementById('scheduleDate');
        const timeInput = document.getElementById('scheduleTime');
        
        if (scheduleDate && scheduleDate !== 'null') {
            const dateObj = new Date(scheduleDate);
            scheduleEnabled.checked = true;
            scheduleFields.style.display = 'block';
            
            // Format date as YYYY-MM-DD
            dateInput.value = dateObj.toISOString().split('T')[0];
            
            // Format time as HH:MM
            const hours = String(dateObj.getHours()).padStart(2, '0');
            const minutes = String(dateObj.getMinutes()).padStart(2, '0');
            timeInput.value = `${hours}:${minutes}`;
        } else {
            scheduleEnabled.checked = false;
            scheduleFields.style.display = 'none';
            dateInput.value = '';
            timeInput.value = '';
        }
        
        const modal = new bootstrap.Modal(document.getElementById('manageScheduleModal'));
        modal.show();
    }

    function toggleScheduleFields() {
        const enabled = document.getElementById('scheduleEnabled').checked;
        const fields = document.getElementById('scheduleFields');
        const dateInput = document.getElementById('scheduleDate');
        const timeInput = document.getElementById('scheduleTime');
        
        fields.style.display = enabled ? 'block' : 'none';
        
        if (enabled) {
            // Set default values if empty
            if (!dateInput.value) {
                const today = new Date().toISOString().split('T')[0];
                dateInput.value = today;
            }
            if (!timeInput.value) {
                timeInput.value = '08:00';
            }
        }
    }

    function updateSchedule() {
        const form = document.getElementById('manageScheduleForm');
        const formData = new FormData(form);
        formData.append('enabled', document.getElementById('scheduleEnabled').checked);
        
        fetch('handlers/update_schedule.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                $('#manageScheduleModal').modal('hide');
                alert('Schedule updated successfully!');
                location.reload();
            } else {
                alert('Error updating schedule: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating schedule');
        });
    }

    function deleteFolder(folderId) {
        if (!folderId) {
            console.error('No folder ID provided');
            return;
        }

        // Store the folder ID for use in confirmation
        const confirmBtn = document.getElementById('confirmDeleteFolder');
        confirmBtn.setAttribute('data-folder-id', folderId);

        // Show the modal
        const modal = new bootstrap.Modal(document.getElementById('deleteFolderModal'));
        modal.show();
    }

    // Add this function to handle exam deletion
    function deleteExam(examId) {
        if (!examId) {
            console.error('No exam ID provided');
            return;
        }

        // Store the exam ID for use in confirmation
        const confirmBtn = document.getElementById('confirmDeleteExam');
        confirmBtn.setAttribute('data-exam-id', examId);

        // Show the modal
        const modal = new bootstrap.Modal(document.getElementById('deleteExamModal'));
        modal.show();
    }

    // Add these event listeners in your document.addEventListener('DOMContentLoaded', function() {...})
    document.addEventListener('DOMContentLoaded', function() {
        // ... (existing code) ...

        // Handle folder deletion confirmation
        document.getElementById('confirmDeleteFolder').addEventListener('click', function() {
            const folderId = this.getAttribute('data-folder-id');
            const modal = bootstrap.Modal.getInstance(document.getElementById('deleteFolderModal'));
            
            // Show loading state
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...';

            fetch('handlers/delete_folder.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `folder_id=${folderId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    modal.hide();
                    showSuccessToast('Folder deleted successfully');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    throw new Error(data.message || 'Failed to delete folder');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorToast('Error deleting folder: ' + error.message);
            })
            .finally(() => {
                // Reset button state
                this.disabled = false;
                this.innerHTML = 'Delete';
            });
        });

        // Handle exam deletion confirmation
        document.getElementById('confirmDeleteExam').addEventListener('click', function() {
            const examId = this.getAttribute('data-exam-id');
            const modal = bootstrap.Modal.getInstance(document.getElementById('deleteExamModal'));
            
            // Show loading state
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...';

            fetch('handlers/delete_exam.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `exam_id=${examId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    modal.hide();
                    showSuccessToast('Exam deleted successfully');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    throw new Error(data.message || 'Failed to delete exam');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorToast('Error deleting exam: ' + error.message);
            })
            .finally(() => {
                // Reset button state
                this.disabled = false;
                this.innerHTML = 'Delete';
            });
        });
    });
    </script>

</body>
</html>