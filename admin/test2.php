<?php 
// Get the exam_id from the query string
$exam_id = $_GET['exam_id'] ?? null;

// Ensure exam_id is passed
if (!$exam_id) {
    die("Exam ID is required to add questions.");
}

// Fetch existing questions for this exam_id
$questions = [];
include_once __DIR__ . '/../config/config.php'; // Includes the MySQLi connection ($conn)
$stmt = $conn->prepare("SELECT * FROM questions WHERE exam_id = ?");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    // Fetch options if the question is multiple choice
    $question_id = $row['question_id'];
    if ($row['question_type'] == 'multiple_choice') {
        $options_stmt = $conn->prepare("SELECT * FROM multiple_choice_options WHERE question_id = ?");
        $options_stmt->bind_param("i", $question_id);
        $options_stmt->execute();
        $options_result = $options_stmt->get_result();
        $options = $options_result->fetch_all(MYSQLI_ASSOC);
        $row['options'] = $options;
    }
    // Fetch test cases if the question is programming
    if ($row['question_type'] == 'programming') {
        $test_stmt = $conn->prepare("SELECT * FROM test_cases WHERE question_id = ?");
        $test_stmt->bind_param("i", $question_id);
        $test_stmt->execute();
        $test_result = $test_stmt->get_result();
        $test_cases = $test_result->fetch_all(MYSQLI_ASSOC);
        $row['test_cases'] = $test_cases;
    }
    $questions[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
  <title>Create Exam - Brand</title>
  
  <!-- jQuery first, then Popper.js, then Bootstrap JS -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
  
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
  
  <!-- Fonts -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i&display=swap">
  <link rel="stylesheet" href="assets/fonts/fontawesome-all.min.css">
  
  
  <!-- Custom CSS -->
  <link rel="stylesheet" href="assets/css/styles.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Add these in the head section, after the other CSS links -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
  
  <style>

    .section-block:hover {
        box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
        border-color: #d0d0d0;
    }

    /* Title and description styling */
    .title-block {
        margin-bottom: 10px;
        position: relative;
    }

    .title-block input {
        font-size: 24px;
        font-weight: 600;
        border: none;
        padding: 8px 0;
        border-bottom: 2px solid #6200ea;
        color: #333;
        background: transparent;
        width: 100%;
        transition: all 0.3s ease;
    }

    .title-block input:hover {
        border-bottom-color: #3700b3;
    }

    .description-block {
        margin: 10px 0;
        padding: 8px 0;
        border-bottom: 1px solid #eee;
    }

    /* Question block styling */
    .question-block {
        background: #ffffff;
        border: 1px solid #e9ecef;
        padding: 15px;
        margin: 8px 0;
        border: 1px solid #eaeaea;
        transition: all 0.3s ease;
    }
    /* Question text input/area */
.question-block .question-field {
    font-size: 16px;  /* Adjust text size */
    line-height: 1.6;  /* Line height for better readability */
    min-height: 100px;  /* Minimum height for question text area */
    width: 100%;
    padding: 12px;
    margin-bottom: 15px;
}


    .question-block:hover {
        background: linear-gradient(to right, #ffffff, #fafafa);
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        border-color: #d8d8d8;
    }

    /* Form controls styling */
    .form-control {
        border: 1px solid #e2e2e2;
        border-radius: 6px;
        padding: 8px 10px;
        transition: all 0.3s ease;
        margin-bottom: 8px;
        background-color: #ffffff;
    }

    .form-control:focus {
        border-color: #6200ea;
        box-shadow: 0 0 0 3px rgba(98, 0, 234, 0.1);
        outline: none;
        background-color: #ffffff;
    }

    /* Button styling */
    .btn {
        padding: 8px 16px;
        border-radius: 6px;
        transition: all 0.3s ease;
        font-weight: 500;
    }

    .btn-primary {
        background: #6200ea;
        border: none;
    }

    .btn-primary:hover {
        background: #3700b3;
        transform: translateY(-1px);
    }

    .delete-button {
        opacity: 0.7;
        transition: all 0.3s ease;
    }

    .delete-button:hover {
        opacity: 1;
        transform: scale(1.1);
    }
/* Style for option delete button (X button) - make it more specific */
.input-group .delete-option-btn,
.option-container .delete-option-btn {
    background: transparent;
    color: #dc3545;
    border: none;
    width: 24px;
    height: 24px;
    padding: 0;
    font-size: 18px;
    line-height: 1;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    margin-left: 5px;
    cursor: pointer;
}

.input-group .delete-option-btn::before,
.option-container .delete-option-btn::before {
    content: '×';
    font-weight: bold;
}

.input-group .delete-option-btn:hover,
.option-container .delete-option-btn:hover {
    background-color: rgba(220, 53, 69, 0.1);
    transform: scale(1.1);
}

/* Hide any existing trash icons */
.delete-option-btn i,
.delete-option-btn .fas,
.delete-option-btn .fa-trash-alt {
    display: none !important;
}

    /* Test case styling */
    .test-case {
        background: linear-gradient(to right, #ffffff, #fafafa);
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }

    .test-case:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    /* Input group styling */
    .input-group {
        margin-bottom: 15px;
        border-radius: 6px;
        overflow: hidden;
    }

    .input-group-text {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        padding: 2px 8px;
    }

    /* Hidden test case styling */
    .hidden-test-case {
        background-color: rgba(255, 193, 7, 0.05);
        border: 1px solid rgba(255, 193, 7, 0.2);
    }

    .hidden-test-case-description {
        padding: 15px;
        border-radius: 6px;
        background: rgba(255, 193, 7, 0.05);
    }

    /* Action buttons container */
    .action-buttons {
        position: fixed;
        bottom: 30px;
        left: 30px;
        z-index: 1000;
        display: flex;
        gap: 10px;
    }

    .action-buttons button {
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 500;
        box-shadow: 0 3px 15px rgba(0, 0, 0, 0.15);
        background: linear-gradient(45deg, #6200ea, #7c4dff);
        color: white;
        border: none;
    }

    .action-buttons button:hover {
        background: linear-gradient(45deg, #5000d6, #6e3fff);
        transform: translateY(-2px);
    }

    /* Custom Styling */
    .form-container {
      max-width: 900px;
      margin: 20px auto;  
      padding: 20px;
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      height: 70vh;
      position: relative;
    }
    
    .add-buttons {
      margin: 20px 0;
      display: flex;
      justify-content: flex-end;  
      gap: 10px; 
    }
    
    .add-buttons button {
      background: #6200ea;
      color: white;
      border: none;
      padding: 12px 24px;
      border-radius: 8px;
      cursor: pointer;
      transition: background 0.3s ease;
    }
    
    .add-buttons button:hover {
      background: #3700b3;
    }

    .title-block {
        margin-bottom: 10px;
    }

    .title-block input {
        font-size: 10px;
        font-weight: bold;
        border-bottom: 2px solid #6200ea;
        color: #333;
    }

    .description-block {
        margin-bottom: 15px;
    }

    .description-block input {
        font-size: 14px;
        color: #757575;
        line-height: 1.5;
    }

    .description-block {
        border: none;
        background-color: transparent;
        box-shadow: none;
        padding: 0;
    }

    .form-scrollable {
      height: calc(100% - 60px);
      overflow-y: auto;
      padding: 20px;
      padding-bottom: 80px;
    }

    .tab-menu {
    display: flex;
    gap: 10px;
    border-bottom: 2px solid #eee;
    padding: 0;
}

.tab-menu button,
.tab-menu .btn {
    background: transparent;
    color: #666;
    padding: 15px 25px;
    border: none;
    position: relative;
    font-weight: 500;
    transition: all 0.3s ease;
}

.tab-menu button::after,
.tab-menu .btn::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 2px;
    background: #6200ea;
    transition: width 0.3s ease;
}

.tab-menu button.active::after,
.tab-menu .btn.active::after {
    width: 100%;
}

.tab-menu button.active,
.tab-menu .btn.active {
    color: #6200ea;
}

.tab-menu button:hover,
.tab-menu .btn:hover {
    transform: scale(1.05);
    color: #6200ea;
    background-color: white;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

    .option-container {
    display: flex;
    align-items: center;
    margin-bottom: 2px;
  }

  .option-container input[type="text"] {
    flex-grow: 1;
    margin-right: 10px;
  }

  .option-container input[type="radio"] {
      margin-right: 0px;
    }

    .plus-button {
        position: absolute;
        bottom: 20px;
        left: 20px;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #6200ea;
        color: white;
        border: none;
        font-size: 24px;
        cursor: pointer;
        z-index: 99;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        transition: transform 0.3s ease;
    }

    .plus-button:hover {
        background: #3700b3;
    }

    .plus-button.active {
        transform: rotate(45deg);
    }

    .action-buttons {
        position: absolute;
        bottom: 20px;
        left: 80px;
        display: flex;
        gap: 10px;
        opacity: 0;
        visibility: hidden;
        transform: translateX(-20px);
        transition: all 0.3s ease;
    }

    .action-buttons.active {
        opacity: 1;
        visibility: visible;
        transform: translateX(0);
    }

    .action-buttons button {
        padding: 8px 16px;
        border: none;
        border-radius: 5px;
        background: #6200ea;
        color: white;
        cursor: pointer;
        transition: background 0.3s ease;
        font-size: 14px;
        white-space: nowrap;
    }

    .action-buttons button:hover {
        background: #3700b3;
    }

    /* Add these styles to your existing <style> section */
    .btn-link.text-danger {
        text-decoration: none;
        transition: transform 0.2s ease;
    }

    .btn-link.text-danger:hover {
        transform: scale(1.1);
    }

    .fa-trash-alt {
        font-size: 1.1rem;
    }


    /* Add these styles to your existing CSS */
    .modal.loading .modal-content {
        opacity: 0.6;
        pointer-events: none;
    }
    
    .modal.loading .modal-footer button {
        cursor: not-allowed;
    }
    
    #questionBankList tr {
        transition: background-color 0.2s ease;
    }
    
    #questionBankList tr:hover {
        background-color: rgba(0,0,0,0.02);
    }

    /* Adjust the container width on smaller screens */
    @media (max-width: 768px) {
        .form-container {
            max-width: 90%;
            margin: 10px auto;
        }
    }

    .option-container input[type="radio"] {
        margin-right: 5px;
    }

    .plus-button {
        background-color: #007bff;
        color: white;
        border: none;
        padding: 2px 5px;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .plus-button:hover {
        background-color: #0056b3;
    }

    .question-block {
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 20px;
        background-color: #f9f9f9;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .form-control {
        margin-bottom: 10px;
    }

    .modal-header {
        background-color: #007bff;
        color: white;
    }

    .modal-footer .btn {
        background-color: #007bff;
        color: white;
    }

    /* Make text fields appear as rich text areas */
    .section-block input[type="text"], 
    .section-block textarea {
      min-height: 40px;
      padding: 8px 12px;
      line-height: 1.5;
      transition: border-color 0.2s ease;
    }

    .section-block input[type="text"]:focus,
    .section-block textarea:focus {
      outline: none;
      border-color: #6200ea;
      box-shadow: 0 0 0 2px rgba(98, 0, 234, 0.1);
    }

    /* Add these to your existing styles */
    .editable-field {
        min-height: 40px;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        outline: none;
        transition: border-color 0.2s ease;
    }

    .editable-field:empty:before {
        content: attr(data-placeholder);
        color: #999;
    }

    .editable-field:focus {
        border-color: #6200ea;
        box-shadow: 0 0 0 2px rgba(98, 0, 234, 0.1);
    }

    [data-readonly="true"] {
        background-color: #f8f9fa;
        cursor: not-allowed;
    }

    .floating-toolbar {
        position: absolute;
        display: none;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        padding: 5px;
        z-index: 1000;
        margin-top: 5px;
    }

    .floating-toolbar.active {
        display: flex !important;
        align-items: center;
    }

    .toolbar-btn {
        background: none;
        border: none;
        padding: 4px 8px;
        margin: 0 2px;
        font-size: 14px;
        cursor: pointer;
        color: #333;
        border-radius: 3px;
        transition: all 0.2s ease;
    }

    .toolbar-btn:hover {
        background: #f0f0f0;
    }

    .toolbar-btn.active {
        background: #e0e0e0;
        color: #000;
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
    }

    .test-case {
      background-color: #f8f9fa;
      border: 1px solid #dee2e6;
      border-radius: 4px;
      padding: 15px;
      margin-bottom: 10px;
    }

    .hidden-test-case-description {
      margin-top: 10px;
      padding-top: 10px;
      border-top: 1px dashed #dee2e6;
    }

    .test-case .input-group-text label { margin-bottom: 0; }

    /* Add these styles for hidden test cases */
    .hidden-test-case {
        background-color: rgba(255, 193, 7, 0.05);
        border: 1px solid rgba(255, 193, 7, 0.2);
    }

    .hidden-test-case input.form-control {
        background-color: rgba(255, 193, 7, 0.05);
    }

    .hidden-test-case .input-group-text {
        background-color: #ffc107;
        border-color: rgba(255, 193, 7, 0.5);
    }

    .hidden-test-case-description .alert {
        padding: 0.5rem;
        margin-bottom: 0.5rem;
    }

    /* Add these styles for test cases */
    .test-case {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 15px;
        margin-bottom: 10px;
    }

    .input-group-text {
        display: flex;
        align-items: center;
        padding: 0.375rem 0.75rem;
        background-color: #e9ecef;
        border: 1px solid #ced4da;
    }

    .input-group-text input[type="checkbox"] {
        margin-right: 5px;
    }

    .hidden-test-case {
        background-color: rgba(255, 193, 7, 0.05);
    }

    .hidden-test-case .input-group-text {
        background-color: #ffc107;
        border-color: rgba(255, 193, 7, 0.5);
    }

    .hidden-test-case-description {
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px dashed #dee2e6;
    }

    .hidden-test-case-description .alert {
        padding: 0.5rem;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }

    /* Add some depth to the page */
    body {
        background-color: #f0f2f5;
    }

    /* Make scrollbar more modern */
    .form-scrollable::-webkit-scrollbar {
        width: 8px;
    }

    .form-scrollable::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .form-scrollable::-webkit-scrollbar-thumb {
        background: #c5c5c5;
        border-radius: 4px;
    }

    .form-scrollable::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    .section-block {
        background: block;
        border: 0px solid #dee2e6;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .title-block {
        background: #fff;
        padding: 1px;
        border-radius: 6px;
        margin-bottom: 10px;
        border: 1px solid #e9ecef;
    }

    .description-block {
        background: #fff;
        padding: -10px;
        border-radius: 6px;
        margin-bottom: 20px;
        border: 1px solid #e9ecef;
    }

    .question-block {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 15px;
        
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .question-block:hover {
        border-color: #007bff;
        box-shadow: 0 2px 5px rgba(0,123,255,0.1);
    }

    .section-field[contenteditable="true"] {
        font-size: 1rem;
        font-weight: 500;
        color: #2c3e50;
        border: 1px solid transparent;
        transition: all 0.3s ease;
        padding: 6px;
    }

    .section-field[contenteditable="true"]:hover,
    .section-field[contenteditable="true"]:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        background: #fff;
    }

    .question-field {
        font-size: 1rem;
        color: #495057;
        min-height: 40px !important;
        height: auto;
    }

    .multiple-choice-options,
    .programming-options {
        margin-left: 0px;
        margin-top: 1px;
    }

    .option-container {
        background: hidden;
        border-radius: 4px;
        padding: -1px;
        margin-bottom: -5px;
    }

    .add-option-btn {
    background-color: transparent;
    color: #6200ea;
    padding: 6px 12px;
    border: none;
    font-size: 14px;
    margin: -40px 0 -5px -5px;
    text-decoration: underline;
    transition: all 0.3s ease;
}

.add-option-btn:hover {
    color: #ddddff;
    text-decoration: none;
}

    .add-test-case-btn {
        margin-left: 0px;
    }
    /* Visual separator between sections */
    .section-block:not(:last-child)::after {
        content: '';
        display: block;
        height: 2px;
        background: #e9ecef;
        margin: 30px 0;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        padding: 8px 12px;
        background: #fff;
        border-radius: 8px;
        margin-bottom: 10px;
    }

    .section-content {
        display: none;
        padding: 10px;
    }

    .section-content.show {
        display: block;
    }

    .section-header .toggle-icon {
        transition: transform 0.3s ease;
    }

    .section-header.active .toggle-icon {
        transform: rotate(180deg);
    }

    /* Question counter badge */
    .question-count {
        background: #e9ecef;
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 0.9rem;
        color: #495057;
    }

    /* Search and filter container */
    .search-filter-container {
        background: #fff;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    /* Pagination container */
    .questions-pagination {
        display: flex;
        justify-content: center;
        margin-top: 20px;
        gap: 10px;
    }

    .page-button {
        padding: 5px 10px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        background: #fff;
        cursor: pointer;
    }

    .page-button.active {
        background: #007bff;
        color: #fff;
        border-color: #007bff;
    }

    .form-scrollable {
        position: relative;
    }

    .section-block {
        position: relative;
    }

    .question-block {
        position: relative;
    }

    .editable-field {
        position: relative;
    }
  </style>

</head>
<body>
  <div class="floating-toolbar" id="floatingToolbar">
    <button type="button" class="toolbar-btn" data-command="bold">
      <i class="fas fa-bold"></i>
    </button>
    <button type="button" class="toolbar-btn" data-command="italic">
      <i class="fas fa-italic"></i>
    </button>
    <button type="button" class="toolbar-btn" data-command="underline">
      <i class="fas fa-underline"></i>
    </button>
    <span class="toolbar-separator">|</span>
    <button type="button" class="toolbar-btn" data-command="insertUnorderedList">
      <i class="fas fa-list-ul"></i>
    </button>
    <button type="button" class="toolbar-btn" data-command="insertOrderedList">
      <i class="fas fa-list-ol"></i>
    </button>
  </div>

  <div id="wrapper">
    <!-- Sidebar -->
    <nav class="sidebar sidebar-dark accordion" id="accordionSidebar">
      <?php include 'sidebar.php'; ?>
    </nav>
    
    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <nav class="navbar navbar-expand bg-white shadow mb-4 topbar">
          <div class="container-fluid">
            <button class="btn btn-link d-md-none rounded-circle me-3" id="sidebarToggleTop" type="button">
              <i class="fas fa-bars"></i>
            </button>
            <form class="d-none d-sm-inline-block me-auto ms-md-3 my-2 my-md-0 mw-100 navbar-search">
              <div class="input-group">
                <input class="bg-light form-control border-0 small" type="text" placeholder="Search for ...">
                <button class="btn btn-primary py-0" type="button" style="background: rgb(255,255,255);">
                  <i class="fas fa-search" style="font-size: 19px;color: var(--bs-secondary-color);"></i>
                </button>
              </div>
            </form>
            <ul class="navbar-nav flex-nowrap ms-auto">
              <li class="nav-item dropdown no-arrow mx-1">
                <a class="dropdown-toggle nav-link" aria-expanded="false" data-bs-toggle="dropdown" href="#" style="width: 60px;height: 60px;">
                  <i class="far fa-user-circle" style="font-size: 30px;color: var(--bs-navbar-disabled-color);backdrop-filter: brightness(99%);-webkit-backdrop-filter: brightness(99%);"></i>
                </a>
              </li>
            </ul>
          </div>
        </nav>

        <div class="container-fluid">
          <div id="successMessage" class="alert alert-success" style="display: none;"></div>

          <!-- Navigation tabs -->
          <div class="tab-menu">
              <a href="create-exam.php?exam_id=<?php echo $exam_id; ?>" class="btn">Back to Exam Creation</a>
              <button class="active">Questions</button>
              <a href="preview_exam.php?exam_id=<?php echo $exam_id; ?>" class="btn">Preview</a>
              <a href="exam_settings.php?exam_id=<?php echo $exam_id; ?>" class="btn">Settings</a>
          </div>

          <div class="form-container">
          <?php include 'question_toolbar.php'; ?>
              <div class="form-scrollable">
                  <form id="questionForm" method="POST" action="save_question.php">
                      <input type="hidden" name="exam_id" value="<?php echo $exam_id; ?>">
                      <div id="sectionBlocks">
                          <!-- Sections will be added here dynamically -->
                          <div class="section-block" data-section-id="<?php echo $section_id; ?>">
                              <!-- Section title and other content -->
                              <div class="questions-container">
                                  <!-- Questions will be added here -->
                              </div>
                          </div>
                      </div>
                  </form>
              </div>

              <button class="plus-button" id="showActionSidebar">+</button>
              <div class="action-buttons" id="actionButtons">
                  <button type="button" id="add-section-btn">Add Section</button>
                  <button type="button" id="global-add-question-btn">Add Question</button>
                  <button type="button" id="import-questions-btn">Import from Bank</button>
                  <button type="button" id="save-form-btn">Save</button>
              </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
    
    </div>
  </div>

<script src="js/test2.js"></script>
<?php include 'includes/question-bank-modal.php'; ?>
<script src="js/question-bank.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Function to show success message
function showSuccess(message) {
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: message,
        timer: 2000,
        showConfirmButton: false
    });
}

// Function to show error message
function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: message,
        confirmButtonText: 'OK'
    });
}

// Function to show warning message
function showWarning(message, confirmCallback = null) {
    Swal.fire({
        icon: 'warning',
        title: 'Warning',
        text: message,
        confirmButtonText: 'OK',
        showCancelButton: confirmCallback ? true : false,
    }).then((result) => {
        if (result.isConfirmed && confirmCallback) {
            confirmCallback();
        }
    });
}

// Add event listener for the add section button
document.getElementById('add-section-btn')?.addEventListener('click', function() {
    // Your existing add section logic here
    showSuccess('New section has been added successfully.');
});

// Add event listener for the save form button
document.getElementById('save-form-btn')?.addEventListener('click', function() {
    Swal.fire({
        title: 'Saving...',
        text: 'Please wait while we save your changes',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Your existing save logic here
    // After successful save:
    showSuccess('All changes have been saved successfully.');
    
    // If there's an error:
    // showError('Failed to save changes. Please try again.');
});
</script>
</body>
</html>