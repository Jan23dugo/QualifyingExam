<?php 
// Get the exam_id from the query string
$exam_id = $_GET['exam_id'] ?? null;

// Ensure exam_id is passed
if (!$exam_id) {
    die("Exam ID is required to add questions.");
}

// Fetch existing questions for this exam_id
$questions = array();
include_once __DIR__ . '/../config/config.php'; // Includes the MySQLi connection ($conn)

// Then check for questions
$stmt = $conn->prepare("SELECT * FROM questions WHERE exam_id = ?");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$questions_result = $stmt->get_result();

while ($row = $questions_result->fetch_assoc()) {
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

// Check if both sections and questions are empty
$has_content = false; // Set to false by default to show empty state
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
  <script src="https://cdn.ckeditor.com/ckeditor5/40.1.0/classic/ckeditor.js"></script>

  
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
  
  <!-- Fonts -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i&display=swap">
  <link rel="stylesheet" href="assets/fonts/fontawesome-all.min.css">
  
  <!-- Custom CSS -->
  <link rel="stylesheet" href="assets/css/styles.min.css">
  <style>
    /* Custom Styling */
    .form-container {
      max-width: 1200px;
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

    .title-block, .description-block, .question-block {
      margin-bottom: 15px; 
    }

    .title-block {
      font-size: 24px;
      font-weight: bold;
      padding-bottom: 5px;
      border-bottom: 2px solid #6200ea;
    }

    .description-block {
      font-size: 14px;
      color: #757575;
      margin-top: 10px;
    }

    .form-scrollable {
      height: calc(100% - 60px);
      overflow-y: auto;
      padding: 20px;
      padding-bottom: 80px;
    }

    .tab-menu {
      margin-bottom: 20px;
      display: flex;
      justify-content: flex-start;
      gap: 10px; 
    }

    .tab-menu button {
      padding: 10px 20px;
      background-color: #6200ea;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    .tab-menu button.active {
      background-color: #3700b3;
    }

    .tab-menu button:hover {
      background-color: #3700b3;
    }
    .option-container {
    display: flex;
    align-items: center;
    margin-bottom: 5px;
  }

  .option-container input[type="text"] {
    flex-grow: 1;
    margin-right: 10px;
  }

  .option-container input[type="radio"] {
      margin-right: 5px;
    }

    .plus-button {
        position: absolute;
        bottom: 20px;
        left: 20px;
        width: 45px;
        height: 45px;
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

    .input-group .btn-link.text-danger {
        border: none;
        background: transparent;
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
    @media (max-width: 1400px) {
        .form-container {
            max-width: 95%;
            margin: 20px auto;
        }
    }

    .empty-state {
        background: #f8fafc;
        border: 2px dashed #e2e8f0;
        border-radius: 12px;
        margin: 20px;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 80%;
        max-width: 600px;
        padding: 40px;
        text-align: center;
    }
    
    .empty-state i {
        color: #94a3b8;
        display: block;
        margin-bottom: 1rem;
        font-size: 3rem;
    }
    
    .empty-state h4 {
        font-weight: 600;
        color: #475569;
        margin-bottom: 1rem;
    }
    
    .empty-state p {
        color: #64748b;
        font-size: 0.95rem;
        margin-bottom: 2rem;
    }
    
    .empty-state .btn {
        transition: all 0.3s ease;
        padding: 0.75rem 1.5rem;
    }
    
    .empty-state .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .form-scrollable {
        position: relative;
        min-height: 400px; /* Add minimum height */
    }

    /* Add or update these styles in your <style> section */
    .section-block {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        margin-bottom: 24px;
        border: 1px solid #e5e7eb;
        transition: box-shadow 0.3s ease;
    }

    .section-block:hover {
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .title-block {
        padding: 16px 20px;
        border-bottom: 1px solid #e5e7eb;
        background: #f8fafc;
        border-radius: 12px 12px 0 0;
    }

    .title-block input[type="text"] {
        border: none;
        background: transparent;
        font-size: 1.1rem;
        font-weight: 600;
        color: #1f2937;
        padding: 8px 12px;
        border-radius: 6px;
        transition: background 0.2s ease;
    }

    .title-block input[type="text"]:hover,
    .title-block input[type="text"]:focus {
        background: #fff;
        outline: none;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .description-block {
        padding: 12px 20px;
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
    }

    .description-block input[type="text"] {
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 8px 12px;
        font-size: 0.95rem;
        color: #6b7280;
        width: 100%;
        transition: all 0.2s ease;
    }

    .description-block input[type="text"]:focus {
        border-color: #6200ea;
        box-shadow: 0 0 0 2px rgba(98, 0, 234, 0.1);
        outline: none;
    }

    .question-block-container {
        padding: 20px;
    }

    .question-block {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        margin-bottom: 16px;
        padding: 16px;
        transition: all 0.2s ease;
    }

    .question-block:hover {
        border-color: #6200ea;
        box-shadow: 0 2px 4px rgba(98, 0, 234, 0.1);
    }

    .question-block textarea {
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 12px;
        font-size: 0.95rem;
        resize: vertical;
        min-height: 80px;
        transition: all 0.2s ease;
    }

    .question-block textarea:focus {
        border-color: #6200ea;
        box-shadow: 0 0 0 2px rgba(98, 0, 234, 0.1);
        outline: none;
    }

    .question-type-select {
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 8px 12px;
        font-size: 0.95rem;
        color: #4b5563;
        background-color: #f9fafb;
        transition: all 0.2s ease;
    }

    .question-type-select:focus {
        border-color: #6200ea;
        box-shadow: 0 0 0 2px rgba(98, 0, 234, 0.1);
        outline: none;
    }

    .delete-button, .delete-question-btn {
        color: #ef4444 !important;
        transition: all 0.2s ease;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
    }

    .delete-button:hover, .delete-question-btn:hover {
        background-color: #fee2e2;
        color: #dc2626 !important;
    }

    /* Style for points input */
    .question-block input[type="number"] {
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 8px 12px;
        font-size: 0.95rem;
        width: 100px;
        transition: all 0.2s ease;
    }

    .question-block input[type="number"]:focus {
        border-color: #6200ea;
        box-shadow: 0 0 0 2px rgba(98, 0, 234, 0.1);
        outline: none;
    }

    /* Multiple choice options styling */
    .option-container {
        margin-bottom: 8px;
    }

    .option-container .input-group {
        border-radius: 6px;
        overflow: hidden;
    }

    .option-container input[type="text"] {
        border: 1px solid #e5e7eb;
        padding: 8px 12px;
        font-size: 0.95rem;
        transition: all 0.2s ease;
    }

    .option-container .input-group-text {
        background-color: #f9fafb;
        border: 1px solid #e5e7eb;
        padding: 8px 12px;
    }

    .option-container input[type="radio"] {
        margin-right: 8px;
    }

    /* Programming question styling */
    .programming-options select {
        margin-bottom: 16px;
        width: 200px;
    }

    .test-case {
        margin-bottom: 12px;
    }

    .test-case .input-group input {
        border: 1px solid #e5e7eb;
        padding: 8px 12px;
        font-size: 0.95rem;
    }

    /* Add these button styles */
    .add-option-btn, .add-test-case-btn {
        background-color: #f3f4f6;
        color: #4b5563;
        border: 1px dashed #e5e7eb;
        padding: 8px 16px;
        border-radius: 6px;
        transition: all 0.2s ease;
    }

    .add-option-btn:hover, .add-test-case-btn:hover {
        background-color: #6200ea;
        color: #fff;
        border-style: solid;
    }

    /* Add this CSS to your existing <style> section */
    .toast {
        position: fixed;
        top: 20px;
        right: 20px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        padding: 16px;
        max-width: 300px;
        z-index: 999;
    }

    .toast.success {
        background-color: #d1e7dd;
        border-color: #badbcc;
    }

    .toast.error {
        background-color: #f8d7da;
        border-color: #f5c6cb;
    }

    .toast .toast-header {
        background-color: #6200ea;
        color: white;
        border-bottom: none;
    }

    .toast .toast-body {
        color: #333;
    }

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

    /* Update the TinyMCE styles in your existing <style> section */
    .tox-tinymce {
        visibility: visible !important;
        z-index: 1 !important;
    }

    .tox .tox-toolbar__primary {
        background: #f8f9fa !important;
        padding: 5px !important;
        border-bottom: 1px solid #dee2e6 !important;
    }

    .tox .tox-toolbar__group {
        border: none !important;
        padding: 3px 5px !important;
    }

    .tox .tox-tbtn {
        height: 34px !important;
        margin: 2px !important;
    }

    .question-editor, .section-editor, .section-description-editor {
        min-height: 150px;
        width: 100%;
    }

    /* Add or update these styles in your <style> section */
    .tox.tox-tinymce {
        visibility: visible !important;
        display: block !important;
        z-index: 100000 !important;
        margin-bottom: 10px;
    }

    .tox .tox-editor-container {
        background: white;
    }

    .tox .tox-toolbar__primary {
        background: #f8f9fa !important;
        padding: 5px !important;
        border-bottom: 1px solid #dee2e6 !important;
        justify-content: flex-start !important;
    }

    .tox .tox-toolbar__group {
        padding: 0 5px !important;
        border: none !important;
    }

    .tox .tox-tbtn {
        height: 34px !important;
        width: 34px !important;
        margin: 0 1px !important;
    }

    .tox .tox-tbtn:hover {
        background: #e9ecef !important;
    }

    .question-editor, .section-editor, .section-description-editor {
        min-height: 200px !important;
        width: 100% !important;
        display: block !important;
        visibility: visible !important;
    }

    /* Update these styles in your <style> section */
    .tox.tox-tinymce {
        border: 1px solid #dee2e6 !important;
        border-radius: 6px !important;
        margin-bottom: 10px !important;
    }

    .tox .tox-toolbar__primary {
        background: #f8f9fa !important;
        border-top: 1px solid #dee2e6 !important;
        padding: 8px !important;
    }

    .tox .tox-toolbar__group:not(:last-of-type) {
        border-right: 1px solid #dee2e6 !important;
        padding-right: 8px !important;
        margin-right: 8px !important;
    }

    .tox .tox-tbtn {
        height: 30px !important;
        width: 30px !important;
        margin: 0 2px !important;
        border-radius: 4px !important;
    }

    .tox .tox-tbtn:hover {
        background: #e9ecef !important;
    }

    .tox .tox-tbtn--enabled {
        background: #e9ecef !important;
    }

    .question-editor, .section-editor, .section-description-editor {
        min-height: 150px !important;
        width: 100% !important;
        margin-bottom: 0 !important;
        border-bottom-left-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    /* Style for the toolbar container */
    .tox .tox-toolbar-overlord {
        border-top: 1px solid #dee2e6;
    }

    /* Replace the TinyMCE styles with these CKEditor styles */
    .ck-editor__editable {
        min-height: 60px !important;
        max-height: none !important;
        transition: all 0.3s ease;
        height: auto !important;
        overflow: visible !important;
    }

    .ck.ck-editor {
        width: 100% !important;
        margin-bottom: 0 !important;
        border: 1px solid #dee2e6 !important;
        border-radius: 4px !important;
    }

    .ck.ck-editor__main > .ck-editor__editable {
        background-color: #fff !important;
        border: none !important;
        border-radius: 0 !important;
        padding: 8px !important;
        line-height: 1.5 !important;
    }

    .ck.ck-editor__main > .ck-editor__editable:focus {
        border-color: #80bdff !important;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25) !important;
    }

    .ck.ck-toolbar {
        border: none !important;
        border-bottom: 1px solid #dee2e6 !important;
        background: #f8f9fa !important;
        padding: 4px !important;
        position: static !important;
    }

    .ck.ck-toolbar.ck-toolbar_floating {
        border: 1px solid #dee2e6 !important;
        border-radius: 4px !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
    }

    .ck.ck-button {
        color: #495057 !important;
        cursor: pointer !important;
        font-size: 0.9rem !important;
        padding: 4px !important;
        min-height: unset !important;
        line-height: 1 !important;
    }

    .ck.ck-button:hover {
        background: #e9ecef !important;
    }

    .ck.ck-button.ck-on {
        background: #e9ecef !important;
        color: #212529 !important;
    }

    /* Add these new styles */
    .section-container {
        background: #fff;
        padding: 24px 24px 0 24px !important;
        margin-bottom: 8px !important;
        border: 1px solid #dadce0;
        border-radius: 8px;
    }

    .section-container:hover {
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .form-label.fw-bold {
        color: #495057;
        font-size: 0.95rem;
        margin-bottom: 4px !important;
    }

    .question-block-container {
        padding: 0;
        margin-top: 0 !important;
        min-height: 50px;
    }

    /* Add spacing between editors */
    .description-block {
        margin-top: 8px !important;
        opacity: 0.87;
        margin-bottom: 8px !important;
    }

    /* Update CKEditor styles for better placeholder visibility */
    .ck.ck-editor__main > .ck-editor__editable.ck-placeholder::before {
        color: #70757a !important;
        font-size: 16px !important;
        opacity: 0.8;
        padding: 8px 0 !important;
    }

    .ck.ck-toolbar__items {
        line-height: 1 !important;
    }

    /* Update the CKEditor styles */
    .ck-editor__editable {
        min-height: 60px !important;
        max-height: none !important;
        transition: all 0.3s ease;
        height: auto !important;
        overflow: visible !important;
    }

    .ck.ck-editor__main > .ck-editor__editable {
        background-color: transparent !important;
        border: none !important;
        border-bottom: 1px solid #dadce0 !important;
        border-radius: 0 !important;
        padding: 8px 0 !important;
        line-height: 1.2 !important;
        color: #333 !important;
        transition: border-bottom-color 0.2s ease;
    }

    .ck.ck-editor__main > .ck-editor__editable:focus {
        border-bottom-color: #1a73e8 !important;
        box-shadow: none !important;
    }

    /* Remove scrollbar styles */
    .ck.ck-editor__main > .ck-editor__editable::-webkit-scrollbar {
        display: none !important;
    }

    .ck.ck-editor__main > .ck-editor__editable {
        -ms-overflow-style: none !important;
        scrollbar-width: none !important;
    }

    /* Adjust content area */
    .ck.ck-editor__main {
        height: auto !important;
        min-height: unset !important;
    }

    .ck-editor__editable.ck-editor__editable_inline {
        height: auto !important;
        min-height: unset !important;
        overflow: visible !important;
    }

    /* Section title styling */
    .section-editor {
        font-size: 16px !important;
        font-weight: 500 !important;
        color: #202124 !important;
    }

    /* Description styling */
    .section-description-editor {
        font-size: 14px !important;
        color: #70757a !important;
    }

    /* Update the toolbar styling */
    .ck.ck-toolbar {
        border: none !important;
        background: transparent !important;
        padding: 4px 0 !important;
    }

    .ck-focused + .ck.ck-toolbar,
    .ck.ck-toolbar:hover {
        opacity: 1;
    }

    /* Delete button styling */
    .delete-button {
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    .section-container:hover .delete-button {
        opacity: 1;
    }

    /* Update placeholder style to also be vertically centered */
    .ck.ck-editor__main > .ck-editor__editable.ck-placeholder::before {
        color: #70757a !important;
        font-size: 16px !important;
        opacity: 0.8;
        padding: 8px 0 !important;
        position: absolute !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
    }

    /* Ensure the editor content is also vertically centered */
    .ck-editor__editable p {
        margin: 0 !important;
        padding: 0 !important;
    }

    /* Adjust the container height */
    .section-editor, .section-description-editor {
        min-height: 40px !important;
        height: auto !important;
        display: flex !important;
        align-items: center !important;
    }

    /* Style for option remove button */
    .remove-option {
        text-decoration: none;
        line-height: 1;
        opacity: 0.5;
        transition: opacity 0.2s ease;
    }

    .remove-option:hover {
        opacity: 1;
        text-decoration: none;
    }

    /* Style for option items */
    .option-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Add or update these styles */
    .question-editor-wrapper {
        border: 1px solid #dee2e6;
        border-radius: 4px;
        background: #fff;
    }

    .question-editor {
        min-height: 40px !important;
        padding: 8px !important;
    }

    .ck.ck-editor__main > .ck-editor__editable {
        border: none !important;
        box-shadow: none !important;
        padding: 8px !important;
    }

    .ck.ck-editor__main > .ck-editor__editable:focus {
        border: none !important;
        box-shadow: none !important;
    }

    .ck.ck-toolbar {
        border: none !important;
        border-bottom: 1px solid #dee2e6 !important;
        background: #f8f9fa !important;
    }

    .question-block {
        background: #fff;
        padding: 16px;
        border-radius: 4px;
        border: 1px solid #dee2e6;
    }

    .question-type-select {
        height: 40px !important;
    }
  </style>
</head>
<body>
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
              <a href="create-exam.php?exam_id=<?php echo $exam_id; ?>" class="btn btn-secondary">Back to Exam Creation</a>
              <button class="active">Questions</button>
              <a href="preview_exam.php?exam_id=<?php echo $exam_id; ?>" class="btn btn-primary">Preview</a>
              <a href="exam_settings.php?exam_id=<?php echo $exam_id; ?>" class="btn btn-primary">Settings</a>
          </div>

          <div class="form-container">
              <div class="form-scrollable">
                  <form id="questionForm" method="POST" action="save_question.php">
                      <input type="hidden" name="exam_id" value="<?php echo $exam_id; ?>">
                      <div id="sectionBlocks">
                          <?php if (!$has_content): ?>
                              <div class="empty-state text-center py-5">
                                  <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                  <h4 class="text-muted">No Questions Added Yet</h4>
                                  <p class="text-muted mb-4">Get started by adding your first question or section.</p>
                                  <div class="d-flex justify-content-center gap-2">
                                      <button type="button" class="btn btn-primary" onclick="addSection()">
                                          <i class="fas fa-plus-circle me-2"></i>Add Section
                                      </button>
                                      <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('import-questions-btn').click()">
                                          <i class="fas fa-file-import me-2"></i>Import Questions
                                      </button>
                                  </div>
                              </div>
                          <?php endif; ?>
                          <!-- Sections will be added here dynamically -->
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
      <?php include 'footer.php'; ?>
    </div>
  </div>

<!-- JavaScript to dynamically add questions, titles, and sections -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show/hide empty state based on content
    function toggleEmptyState() {
        const sectionBlocks = document.getElementById('sectionBlocks');
        const sections = sectionBlocks.querySelectorAll('.section-block');
        const hasContent = sections.length > 0; // Check if there are any sections
        
        // Remove existing empty state if it exists
        const existingEmptyState = sectionBlocks.querySelector('.empty-state');
        if (existingEmptyState) {
            existingEmptyState.remove();
        }
        
        if (!hasContent) {
            const emptyState = document.createElement('div');
            emptyState.className = 'empty-state text-center py-5';
            emptyState.innerHTML = `
                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No Questions Added Yet</h4>
                <p class="text-muted">Get started by adding your first question or section using the buttons below.</p>
            `;
            sectionBlocks.appendChild(emptyState);
        }
    }

    // Initialize all modals
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modalElement => {
        new bootstrap.Modal(modalElement);
    });

    let sectionCounter = 1;
    const exam_id = new URLSearchParams(window.location.search).get('exam_id');

    // Initialize UI elements
    const showActionSidebarBtn = document.getElementById('showActionSidebar');
    const actionButtons = document.getElementById('actionButtons');
    const saveFormBtn = document.getElementById('save-form-btn');
    const addSectionBtn = document.getElementById('add-section-btn');
    const globalAddQuestionBtn = document.getElementById('global-add-question-btn');

    // Function to load existing sections and questions
    function loadSectionsAndQuestions(sections) {
        const sectionBlocks = document.getElementById('sectionBlocks');
        sectionBlocks.innerHTML = ''; // Clear existing content
        
        if (sections && sections.length > 0) {
            sections.forEach(section => {
                const newSection = document.createElement('div');
                newSection.classList.add('section-block');
                newSection.setAttribute('data-section-id', section.section_id);
                
                newSection.innerHTML = `
                    <div class="section-container">
                        <div class="title-block">
                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                <div style="flex: 1; margin-right: 8px;">
                                    <div class="section-editor" 
                                        name="section_title[${section.section_id}]" 
                                        data-placeholder="Input section title"
                                        style="min-height: 20px !important;"></div>
                                </div>
                                <button type="button" class="delete-button btn btn-link text-danger" style="padding: 4px;">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                        <div class="description-block">
                            <div class="section-description-editor" 
                                name="section_description[${section.section_id}]" 
                                data-placeholder="Input description (optional)"
                                style="min-height: 20px !important;"></div>
                        </div>
                        <div id="question-container-${section.section_id}" class="question-block-container"></div>
                    </div>
                `;

                sectionBlocks.appendChild(newSection);

                // Load questions for this section
                if (section.questions) {
                    section.questions.forEach((question, qIndex) => {
                        const questionContainer = document.getElementById(`question-container-${section.section_id}`);
                        const newQuestion = createQuestionElement(section.section_id, qIndex, question);
                        questionContainer.appendChild(newQuestion);

                        // Load question options based on type
                        const questionTypeSelect = newQuestion.querySelector('.question-type-select');
                        questionTypeSelect.value = question.question_type;
                        handleQuestionTypeChange(questionTypeSelect, section.section_id, qIndex, question);
                    });
                }
            });
        }
        
        toggleEmptyState();

        // Update sectionCounter to be higher than any existing section ID
        const maxSectionId = Math.max(...sections.map(s => parseInt(s.section_id)), 0);
        sectionCounter = maxSectionId;

        attachEventListeners();
    }

    // Function to create question element with existing data
    function createQuestionElement(sectionId, questionIndex, questionData = null) {
        const newQuestion = document.createElement('div');
        newQuestion.classList.add('question-block');
        newQuestion.style.marginBottom = '16px';

        newQuestion.innerHTML = `
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <div style="flex: 1; margin-right: 10px;">
                    <div class="question-editor-wrapper">
                        <div class="question-editor" 
                            name="question_text[${sectionId}][${questionIndex}]" 
                            data-placeholder="Enter your question here"></div>
                    </div>
                </div>
                <div style="min-width: 200px;">
                    <select class="form-control question-type-select" name="question_type[${sectionId}][${questionIndex}]">
                        <option value="">Select Question Type</option>
                        <option value="multiple_choice">Multiple Choice</option>
                        <option value="true_false">True/False</option>
                        <option value="programming">Programming</option>
                    </select>
                </div>
                <button type="button" class="btn btn-link text-danger delete-question-btn" style="padding: 5px;">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
            <div class="question-options" style="margin-top: 10px;"></div>
            <div style="margin-top: 10px;">
                <input type="number" name="points[${sectionId}][${questionIndex}]" 
                    class="form-control" placeholder="Points" style="width: 100px;">
            </div>
        `;

        // Initialize CKEditor for the question
        setTimeout(() => {
            const editor = newQuestion.querySelector('.question-editor');
            if (editor) {
                ClassicEditor
                    .create(editor, {
                        toolbar: {
                            items: [
                                'bold', 'italic', 'underline',
                                '|', 'bulletedList', 'numberedList',
                                '|', 'alignment'
                            ],
                            shouldNotGroupWhenFull: true
                        },
                        toolbar: {
                            shouldNotGroupWhenFull: true,
                            viewportTopOffset: 0
                        },
                        language: 'en',
                        removePlugins: ['MediaEmbed', 'Table', 'BlockQuote', 'Heading'],
                        placeholder: 'Enter your question here',
                    })
                    .then(editor => {
                        // Keep toolbar visible inside the box
                        const toolbarElement = editor.ui.view.toolbar.element;
                        toolbarElement.style.position = 'static';
                        toolbarElement.style.top = 'auto';
                        toolbarElement.style.background = '#fff';
                        toolbarElement.style.borderBottom = '1px solid #dee2e6';
                        return editor;
                    })
                    .catch(error => {
                        console.error('Editor initialization error:', error);
                    });
            }
        }, 100);

        return newQuestion;
    }

    // Fetch existing data when page loads
    if (exam_id) {
        fetch(`save_question.php?exam_id=${exam_id}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.sections) {
                loadSectionsAndQuestions(data.sections);
            }
        })
        .catch(error => {
            console.error('Error fetching exam data:', error);
        });
    }

    // Add Section functionality
    function addSection() {
        sectionCounter++;
        const newSection = document.createElement('div');
        newSection.classList.add('section-block');
        newSection.setAttribute('data-section-id', sectionCounter);

        newSection.innerHTML = `
            <div class="section-container">
                <div class="title-block">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div style="flex: 1; margin-right: 8px;">
                            <div class="section-editor" 
                                name="section_title[${sectionCounter}]" 
                                data-placeholder="Input section title"
                                style="min-height: 20px !important;"></div>
                        </div>
                        <button type="button" class="delete-button btn btn-link text-danger" style="padding: 4px;">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="description-block">
                    <div class="section-description-editor" 
                        name="section_description[${sectionCounter}]" 
                        data-placeholder="Input description (optional)"
                        style="min-height: 20px !important;"></div>
                </div>
                <div id="question-container-${sectionCounter}" class="question-block-container"></div>
            </div>
        `;

        document.getElementById('sectionBlocks').appendChild(newSection);

        // Initialize editor for section title and description
        setTimeout(() => {
            const titleEditor = newSection.querySelector('.section-editor');
            const descEditor = newSection.querySelector('.section-description-editor');
            
            if (titleEditor) {
                initializeEditor(titleEditor);
            }
            if (descEditor) {
                initializeEditor(descEditor);
            }
        }, 100);

        attachEventListeners();
        toggleEmptyState();
        closeActionSidebar();
    }

    // Add Question functionality
    function addQuestionToSection(sectionId) {
        const questionContainer = document.getElementById(`question-container-${sectionId}`);
        const questionIndex = questionContainer.children.length;

        const newQuestion = document.createElement('div');
        newQuestion.classList.add('question-block');
        newQuestion.style.marginBottom = '20px';
        newQuestion.style.padding = '15px';
        newQuestion.style.border = '1px solid #ddd';
        newQuestion.style.borderRadius = '8px';

        newQuestion.innerHTML = `
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <div style="flex: 1; margin-right: 10px;">
                    <div class="question-editor" 
                        name="question_text[${sectionId}][${questionIndex}]" 
                        data-placeholder="Enter your question here"
                        style="border: 1px solid #dee2e6; border-radius: 4px; min-height: 100px;"></div>
                </div>
                <div style="min-width: 200px;">
                    <select class="form-control question-type-select" name="question_type[${sectionId}][${questionIndex}]">
                        <option value="">Select Question Type</option>
                        <option value="multiple_choice">Multiple Choice</option>
                        <option value="true_false">True/False</option>
                        <option value="programming">Programming</option>
                    </select>
                </div>
                <button type="button" class="btn btn-link text-danger delete-question-btn" style="padding: 5px;">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
            <div class="question-options" style="margin-top: 10px;"></div>
            <div style="margin-top: 10px;">
                <input type="number" name="points[${sectionId}][${questionIndex}]" 
                    class="form-control" placeholder="Points" style="width: 100px;">
            </div>
        `;

        questionContainer.appendChild(newQuestion);

        // Initialize CKEditor for the question
        const editor = newQuestion.querySelector('.question-editor');
        if (editor) {
            ClassicEditor
                .create(editor, {
                    toolbar: {
                        items: [
                            'bold', 'italic', 'underline',
                            '|', 'bulletedList', 'numberedList',
                            '|', 'alignment'
                        ],
                        shouldNotGroupWhenFull: true
                    },
                    toolbar: {
                        shouldNotGroupWhenFull: true,
                        viewportTopOffset: 0
                    },
                    language: 'en',
                    removePlugins: ['MediaEmbed', 'Table', 'BlockQuote', 'Heading'],
                    placeholder: 'Enter your question here',
                })
                .then(editor => {
                    // Keep toolbar visible inside the box
                    const toolbarElement = editor.ui.view.toolbar.element;
                    toolbarElement.style.position = 'static';
                    toolbarElement.style.top = 'auto';
                    toolbarElement.style.background = '#fff';
                    toolbarElement.style.borderBottom = '1px solid #dee2e6';
                    return editor;
                })
                .catch(error => {
                    console.error('Editor initialization error:', error);
                });
        }

        // Add event listener for question type selection
        const questionTypeSelect = newQuestion.querySelector('.question-type-select');
        questionTypeSelect.addEventListener('change', function() {
            handleQuestionTypeChange(this, sectionId, questionIndex);
        });

        // Add event listener for delete button
        const deleteQuestionBtn = newQuestion.querySelector('.delete-question-btn');
        deleteQuestionBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this question?')) {
                newQuestion.remove();
            }
        });
    }

    // Handle question type change
    function handleQuestionTypeChange(select, sectionId, questionIndex) {
        const optionsContainer = select.closest('.question-block').querySelector('.question-options');
        optionsContainer.innerHTML = '';

        switch(select.value) {
            case 'multiple_choice':
                optionsContainer.innerHTML = `
                    <div class="mb-3">
                        <div class="option-list">
                            <div class="option-item mb-2 d-flex align-items-center">
                                <input type="radio" name="correct[${sectionId}][${questionIndex}]" value="0" class="me-2">
                                <input type="text" class="form-control me-2" name="options[${sectionId}][${questionIndex}][]" placeholder="Option 1">
                                <button type="button" class="btn btn-link text-danger remove-option p-0 ms-1" style="font-size: 18px;">×</button>
                            </div>
                            <div class="option-item mb-2 d-flex align-items-center">
                                <input type="radio" name="correct[${sectionId}][${questionIndex}]" value="1" class="me-2">
                                <input type="text" class="form-control me-2" name="options[${sectionId}][${questionIndex}][]" placeholder="Option 2">
                                <button type="button" class="btn btn-link text-danger remove-option p-0 ms-1" style="font-size: 18px;">×</button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm mt-2 add-option">Add Option</button>
                    </div>
                `;
                break;
            case 'true_false':
                optionsContainer.innerHTML = `
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="correct[${sectionId}][${questionIndex}]" value="true">
                            <label class="form-check-label">True</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="correct[${sectionId}][${questionIndex}]" value="false">
                            <label class="form-check-label">False</label>
                        </div>
                    </div>
                `;
                break;
            case 'programming':
                optionsContainer.innerHTML = `
                    <div class="mb-3">
                        <div class="form-group mb-3">
                            <label>Programming Language</label>
                            <select class="form-control" name="programming_language[${sectionId}][${questionIndex}]">
                                <option value="python">Python</option>
                                <option value="java">Java</option>
                                <option value="cpp">C++</option>
                            </select>
                        </div>
                        
                        <div class="test-cases-container">
                            <h5>Test Cases</h5>
                            <div class="visible-test-cases">
                                <h6>Visible Test Cases</h6>
                                <div class="test-case mb-2">
                                    <input type="text" class="form-control mb-2" 
                                        name="test_input[${sectionId}][${questionIndex}][]" 
                                        placeholder="Input">
                                    <input type="text" class="form-control" 
                                        name="expected_output[${sectionId}][${questionIndex}][]" 
                                        placeholder="Expected Output">
                                </div>
                            </div>
                            
                            <div class="hidden-test-cases mt-3">
                                <h6>Hidden Test Cases (Optional)</h6>
                                <div class="test-case mb-2">
                                    <input type="text" class="form-control mb-2" 
                                        name="hidden_test_input[${sectionId}][${questionIndex}][]" 
                                        placeholder="Hidden Input">
                                    <input type="text" class="form-control" 
                                        name="hidden_expected_output[${sectionId}][${questionIndex}][]" 
                                        placeholder="Hidden Expected Output">
                                </div>
                            </div>
                            
                            <div class="mt-2">
                                <button type="button" class="btn btn-secondary btn-sm add-visible-test-case">
                                    Add Visible Test Case
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm add-hidden-test-case">
                                    Add Hidden Test Case
                                </button>
                            </div>
                        </div>
                    </div>
                `;

                // Add event listeners for adding test cases
                const addVisibleTestCase = optionsContainer.querySelector('.add-visible-test-case');
                const addHiddenTestCase = optionsContainer.querySelector('.add-hidden-test-case');
                const visibleTestCases = optionsContainer.querySelector('.visible-test-cases');
                const hiddenTestCases = optionsContainer.querySelector('.hidden-test-cases');

                addVisibleTestCase.addEventListener('click', () => {
                    const testCase = document.createElement('div');
                    testCase.className = 'test-case mb-2';
                    testCase.innerHTML = `
                        <input type="text" class="form-control mb-2" 
                            name="test_input[${sectionId}][${questionIndex}][]" 
                            placeholder="Input">
                        <input type="text" class="form-control" 
                            name="expected_output[${sectionId}][${questionIndex}][]" 
                            placeholder="Expected Output">
                        <button type="button" class="btn btn-link text-danger remove-test-case">×</button>
                    `;
                    visibleTestCases.appendChild(testCase);
                });

                addHiddenTestCase.addEventListener('click', () => {
                    const testCase = document.createElement('div');
                    testCase.className = 'test-case mb-2';
                    testCase.innerHTML = `
                        <input type="text" class="form-control mb-2" 
                            name="hidden_test_input[${sectionId}][${questionIndex}][]" 
                            placeholder="Hidden Input">
                        <input type="text" class="form-control" 
                            name="hidden_expected_output[${sectionId}][${questionIndex}][]" 
                            placeholder="Hidden Expected Output">
                        <button type="button" class="btn btn-link text-danger remove-test-case">×</button>
                    `;
                    hiddenTestCases.appendChild(testCase);
                });

                // Event delegation for removing test cases
                optionsContainer.addEventListener('click', (e) => {
                    if (e.target.classList.contains('remove-test-case')) {
                        e.target.closest('.test-case').remove();
                    }
                });
                break;
        }

        // Add event listener for "Add Option" button in multiple choice
        if (select.value === 'multiple_choice') {
            const addOptionBtn = optionsContainer.querySelector('.add-option');
            const optionList = optionsContainer.querySelector('.option-list');
            let optionCount = 2;

            addOptionBtn.addEventListener('click', () => {
                optionCount++;
                const newOption = document.createElement('div');
                newOption.className = 'option-item mb-2 d-flex align-items-center';
                newOption.innerHTML = `
                    <input type="radio" name="correct[${sectionId}][${questionIndex}]" value="${optionCount}" class="me-2">
                    <input type="text" class="form-control me-2" name="options[${sectionId}][${questionIndex}][]" placeholder="Option ${optionCount + 1}">
                    <button type="button" class="btn btn-link text-danger remove-option p-0 ms-1" style="font-size: 18px;">×</button>
                `;
                optionList.appendChild(newOption);
            });

            // Event delegation for remove option buttons
            optionList.addEventListener('click', (e) => {
                if (e.target.classList.contains('remove-option')) {
                    e.target.closest('.option-item').remove();
                }
            });
        }
    }

    // Event Listeners
    addSectionBtn.addEventListener('click', () => {
        addSection();
        closeActionSidebar();
    });

    function showWarningModal(message) {
        const warningModal = new bootstrap.Modal(document.getElementById('warningModal'));
        document.getElementById('warningMessage').textContent = message;
        warningModal.show();
    }

    globalAddQuestionBtn.addEventListener('click', () => {
        const sections = document.querySelectorAll('.section-block');
        if (sections.length === 0) {
            showWarningModal('Please add a section first before adding questions.');
            return;
        }
        
        const lastSection = sections[sections.length - 1];
        const sectionId = lastSection.getAttribute('data-section-id');
        addQuestionToSection(sectionId);
        closeActionSidebar();
    });

    // Toggle action buttons
    showActionSidebarBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        showActionSidebarBtn.classList.toggle('active');
        actionButtons.classList.toggle('active');
    });

    // Hide buttons when clicking outside
    document.addEventListener('click', (e) => {
        if (!actionButtons.contains(e.target) && 
            !showActionSidebarBtn.contains(e.target)) {
            actionButtons.classList.remove('active');
            showActionSidebarBtn.classList.remove('active');
        }
    });

    // Prevent clicks inside buttons from closing
    actionButtons.addEventListener('click', (e) => {
        e.stopPropagation();
    });

    // Button Click Handlers
    saveFormBtn.addEventListener('click', () => {
        saveForm();
        closeActionSidebar();
    });

    // Helper Functions
    function closeActionSidebar() {
        actionButtons.classList.remove('active');
        showActionSidebarBtn.classList.remove('active');
    }

    function saveForm() {
        // Get all editor instances and update their textarea values
        document.querySelectorAll('.ck-editor__editable').forEach(editable => {
            const editor = editable.ckeditorInstance;
            if (editor) {
                const textarea = editable.closest('.editor-wrapper').querySelector('textarea');
                textarea.value = editor.getData();
            }
        });

        const formData = new FormData(document.getElementById('questionForm'));
        
        fetch('save_question.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccessToast('Questions saved successfully!');
            } else {
                showErrorToast('Error saving questions: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showErrorToast('An error occurred while saving.');
        });
    }

    function attachEventListeners() {
        document.querySelectorAll('.delete-button').forEach(btn => {
            btn.addEventListener('click', function() {
                const section = this.closest('.section-block');
                const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
                
                // Remove any existing event listener from the confirm button
                const confirmBtn = document.getElementById('confirmDelete');
                const newConfirmBtn = confirmBtn.cloneNode(true);
                confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
                
                // Add new event listener
                newConfirmBtn.addEventListener('click', () => {
                    section.remove();
                    deleteModal.hide();
                    toggleEmptyState();
                });
                
                deleteModal.show();
            });
        });
    }

    // Add these event listeners after your existing ones
    document.getElementById('import-questions-btn').addEventListener('click', function() {
        try {
            const questionBankModal = document.getElementById('questionBankModal');
            if (!questionBankModal) {
                console.error('Question bank modal not found');
                return;
            }
            const modal = new bootstrap.Modal(questionBankModal, {
                backdrop: 'static',
                keyboard: false
            });
            loadCategories(); // Load categories first
            loadQuestionBank(); // Then load questions
            modal.show();
        } catch (error) {
            console.error('Error showing modal:', error);
        }
    });

    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('#questionBankList input[type="checkbox"]');
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    });

    document.getElementById('questionSearch').addEventListener('input', debounce(function() {
        loadQuestionBank(this.value);
    }, 300));

    document.getElementById('importSelectedQuestions').addEventListener('click', importSelectedQuestions);

    // Add these functions
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    function loadQuestionBank(search = '') {
        const category = document.getElementById('categorySelect').value;
        const url = `fetch_question_bank.php?search=${encodeURIComponent(search)}&category=${encodeURIComponent(category)}`;
        
        // Show loading state
        const questionList = document.getElementById('questionBankList');
        questionList.innerHTML = `
            <tr>
                <td colspan="4" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    Loading questions...
                </td>
            </tr>
        `;

        // Add loading class to modal
        document.getElementById('questionBankModal').classList.add('loading');
        
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (!data.questions || data.questions.length === 0) {
                    questionList.innerHTML = `
                        <tr>
                            <td colspan="4" class="text-center py-3">
                                <i class="fas fa-info-circle me-2"></i>
                                No questions found in this category
                            </td>
                        </tr>`;
                    return;
                }
                
                questionList.innerHTML = data.questions.map(question => `
                    <tr>
                        <td><input type="checkbox" value="${question.question_id}" data-question='${JSON.stringify(question)}'></td>
                        <td>${question.question_text}</td>
                        <td>${question.question_type}</td>
                        <td>${question.points || 0}</td>
                    </tr>
                `).join('');

                // Reattach event listeners for checkboxes
                attachCheckboxListeners();
            })
            .catch(error => {
                console.error('Error loading questions:', error);
                questionList.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center text-danger py-3">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            Error loading questions. Please try again.
                        </td>
                    </tr>`;
            })
            .finally(() => {
                // Remove loading class
                document.getElementById('questionBankModal').classList.remove('loading');
            });
    }

    // Add this function to handle checkbox events
    function attachCheckboxListeners() {
        const checkboxes = document.querySelectorAll('#questionBankList input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectionCounter);
        });
    }

    function importSelectedQuestions() {
        const selectedQuestions = document.querySelectorAll('#questionBankList input[type="checkbox"]:checked');
        if (selectedQuestions.length === 0) {
            alert('Please select at least one question');
            return;
        }

        // Show importing progress
        const importBtn = document.getElementById('importSelectedQuestions');
        const originalText = importBtn.textContent;
        importBtn.disabled = true;
        importBtn.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            Importing...
        `;

        try {
            // Your existing import code here
            const sections = document.querySelectorAll('.section-block');
            if (sections.length === 0) {
                throw new Error('Please create a section first');
            }
            
            // ... rest of your import code ...

            bootstrap.Modal.getInstance(document.getElementById('questionBankModal')).hide();
        } catch (error) {
            alert(error.message);
        } finally {
            importBtn.disabled = false;
            importBtn.textContent = originalText;
        }
    }

    // Add these event listeners in your existing DOMContentLoaded function
    document.querySelectorAll('input[name="importType"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.getElementById('manualSelectSection').style.display = 
                this.value === 'manual' ? 'block' : 'none';
            document.getElementById('autoGenerateSection').style.display = 
                this.value === 'auto' ? 'block' : 'none';
        });
    });

    // Modify the importSelectedQuestions function
    document.getElementById('importSelectedQuestions').addEventListener('click', function() {
        const importType = document.querySelector('input[name="importType"]:checked').value;
        
        if (importType === 'manual') {
            importManuallySelectedQuestions();
        } else {
            importAutoGeneratedQuestions();
        }
    });

    function importManuallySelectedQuestions() {
        const selectedQuestions = document.querySelectorAll('#questionBankList input[type="checkbox"]:checked');
        if (selectedQuestions.length === 0) {
            alert('Please select at least one question');
            return;
        }
        importQuestionsToSection(Array.from(selectedQuestions).map(cb => JSON.parse(cb.dataset.question)));
    }

    function importAutoGeneratedQuestions() {
        const count = parseInt(document.getElementById('questionCount').value);
        const category = document.getElementById('autoGenerateCategory').value;
        const selectedTypes = Array.from(document.querySelectorAll('#autoGenerateSection input[type="checkbox"]:checked'))
            .map(cb => cb.value);
        
        if (selectedTypes.length === 0) {
            alert('Please select at least one question type');
            return;
        }

        // Fetch random questions from the question bank
        fetch(`fetch_random_questions.php?count=${count}&types=${selectedTypes.join(',')}&category=${encodeURIComponent(category)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.questions.length > 0) {
                    importQuestionsToSection(data.questions);
                } else {
                    alert('No questions found matching the criteria');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error fetching random questions');
            });
    }

    function importQuestionsToSection(questions) {
        const sections = document.querySelectorAll('.section-block');
        if (sections.length === 0) {
            alert('Please create a section first');
            return;
        }
        
        const lastSection = sections[sections.length - 1];
        const sectionId = lastSection.getAttribute('data-section-id');
        const questionContainer = document.getElementById(`question-container-${sectionId}`);
        
        // Keep track of already imported questions
        const existingQuestionIds = Array.from(questionContainer.querySelectorAll('.question-block'))
            .map(block => block.getAttribute('data-original-question-id'))
            .filter(id => id); // Remove null/undefined values

        // Filter out already imported questions
        const newQuestions = questions.filter(question => 
            !existingQuestionIds.includes(question.question_id.toString())
        );

        if (newQuestions.length === 0) {
            alert('All selected questions have already been imported to this section');
            return;
        }

        newQuestions.forEach(questionData => {
            const questionIndex = questionContainer.children.length;
            const newQuestion = createQuestionElement(sectionId, questionIndex, questionData);
            questionContainer.appendChild(newQuestion);
            
            const questionTypeSelect = newQuestion.querySelector('.question-type-select');
            questionTypeSelect.value = questionData.question_type;
            
            switch(questionData.question_type) {
                case 'multiple_choice':
                    handleMultipleChoiceImport(questionData, sectionId, questionIndex, newQuestion);
                    break;
                case 'true_false':
                    handleTrueFalseImport(questionData, sectionId, questionIndex, newQuestion);
                    break;
                case 'programming':
                    handleProgrammingImport(questionData, sectionId, questionIndex, newQuestion);
                    break;
            }
        });
        
        bootstrap.Modal.getInstance(document.getElementById('questionBankModal')).hide();
    }

    // Add these helper functions
    function handleMultipleChoiceImport(questionData, sectionId, questionIndex, questionElement) {
        const optionsContainer = questionElement.querySelector('.question-options');
        optionsContainer.innerHTML = `
            <div class="multiple-choice-options">
                ${questionData.choices.map((choice, idx) => `
                    <div class="input-group mb-2">
                        <input type="text" class="form-control" 
                            name="options[${sectionId}][${questionIndex}][]" 
                            value="${choice.choice_text}" readonly>
                        <div class="input-group-text">
                            <input type="radio" name="correct_answer[${sectionId}][${questionIndex}]" 
                                value="${idx}" ${choice.is_correct == 1 ? 'checked' : ''}>
                            <label class="ms-2 mb-0">Correct</label>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }

    function handleTrueFalseImport(questionData, sectionId, questionIndex, questionElement) {
        const optionsContainer = questionElement.querySelector('.question-options');
        optionsContainer.innerHTML = `
            <div class="true-false-option">
                <select class="form-control" name="correct_answer[${sectionId}][${questionIndex}]">
                    <option value="true" ${questionData.correct_answer === 'true' ? 'selected' : ''}>True</option>
                    <option value="false" ${questionData.correct_answer === 'false' ? 'selected' : ''}>False</option>
                </select>
            </div>
        `;
    }

    function handleProgrammingImport(questionData, sectionId, questionIndex, questionElement) {
        const optionsContainer = questionElement.querySelector('.question-options');
        optionsContainer.innerHTML = `
            <div class="programming-options">
                <select class="form-control" name="programming_language[${sectionId}][${questionIndex}]">
                    <option value="python" ${questionData.programming_language === 'python' ? 'selected' : ''}>Python</option>
                    <option value="java" ${questionData.programming_language === 'java' ? 'selected' : ''}>Java</option>
                    <option value="c" ${questionData.programming_language === 'c' ? 'selected' : ''}>C</option>
                </select>
                <div class="test-cases mt-3">
                    ${questionData.test_cases ? questionData.test_cases.map(test => `
                        <div class="test-case mb-2">
                            <div class="input-group">
                                <span class="input-group-text">Input</span>
                                <input type="text" class="form-control" 
                                    name="test_case_input[${sectionId}][${questionIndex}][]" 
                                    value="${test.test_input}" readonly>
                                <span class="input-group-text">Output</span>
                                <input type="text" class="form-control" 
                                    name="test_case_output[${sectionId}][${questionIndex}][]" 
                                    value="${test.expected_output}" readonly>
                            </div>
                        </div>
                    `).join('') : ''}
                </div>
            </div>
        `;
    }

    // Add this to your existing JavaScript
    function loadCategories() {
        fetch('fetch_categories.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.categories) {
                    // Update both category selects
                    const selects = [
                        document.getElementById('categorySelect'),
                        document.getElementById('autoGenerateCategory')
                    ];
                    
                    selects.forEach(categorySelect => {
                        // Clear existing options except the first "All Categories" option
                        while (categorySelect.options.length > 1) {
                            categorySelect.remove(1);
                        }
                        data.categories.forEach(category => {
                            const option = document.createElement('option');
                            option.value = category;
                            option.textContent = category;
                            categorySelect.appendChild(option);
                        });
                    });
                }
            })
            .catch(error => console.error('Error loading categories:', error));
    }

    // Add this to your existing event listeners
    document.getElementById('categorySelect').addEventListener('change', function() {
        loadQuestionBank(document.getElementById('questionSearch').value);
    });

    // Add this function to update available question counts
    function updateQuestionCounts(category = '') {
        fetch(`get_question_counts.php?category=${encodeURIComponent(category)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('mcCount').textContent = data.counts.multiple_choice || 0;
                    document.getElementById('tfCount').textContent = data.counts.true_false || 0;
                    document.getElementById('progCount').textContent = data.counts.programming || 0;
                    
                    const total = Object.values(data.counts).reduce((a, b) => a + b, 0);
                    document.getElementById('availableQuestionCount').textContent = 
                        `Total available questions: ${total}`;
                    
                    // Update max value of questionCount input
                    document.getElementById('questionCount').max = total;
                    if (parseInt(document.getElementById('questionCount').value) > total) {
                        document.getElementById('questionCount').value = total;
                    }
                }
            })
            .catch(error => console.error('Error getting question counts:', error));
    }

    // Add event listener for category change
    document.getElementById('autoGenerateCategory').addEventListener('change', function() {
        updateQuestionCounts(this.value);
    });

    // Update counts when switching to auto-generate mode
    document.getElementById('autoGenerate').addEventListener('change', function() {
        if (this.checked) {
            updateQuestionCounts(document.getElementById('autoGenerateCategory').value);
        }
    });

    function getDifficultyColor(difficulty) {
        switch(difficulty?.toLowerCase()) {
            case 'easy': return 'success';
            case 'medium': return 'warning';
            case 'hard': return 'danger';
            default: return 'secondary';
        }
    }

    function previewQuestion(questionId) {
        const question = document.querySelector(`input[value="${questionId}"]`).dataset.question;
        const data = JSON.parse(question);
        
        const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
        document.getElementById('previewContent').innerHTML = generatePreviewHTML(data);
        previewModal.show();
    }

    function updateSelectionCounter() {
        const count = document.querySelectorAll('#questionBankList input[type="checkbox"]:checked').length;
        document.getElementById('selectionCounter').textContent = `${count} question${count !== 1 ? 's' : ''} selected`;
    }

    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'f' && document.getElementById('questionBankModal').classList.contains('show')) {
            e.preventDefault();
            document.getElementById('questionSearch').focus();
        }
        
        if (e.key === 'Escape' && document.getElementById('questionBankModal').classList.contains('show')) {
            bootstrap.Modal.getInstance(document.getElementById('questionBankModal')).hide();
        }
    });

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize TinyMCE for dynamic elements
    function initializeTinyMCE(selector) {
        return tinymce.init({
            selector: selector,
            height: 300,
            menubar: false,  // Hide the top menubar
            statusbar: false, // Hide the bottom status bar
            inline: false,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap',
                'preview', 'searchreplace', 'visualblocks', 'code',
                'insertdatetime', 'table', 'wordcount'
            ],
            toolbar: [
                'undo redo | styles | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify',
                'bullist numlist | outdent indent | forecolor backcolor | removeformat'
            ],
            toolbar_mode: 'wrap',
            toolbar_sticky: false,
            toolbar_location: 'bottom',  // Place toolbar at the bottom
            fixed_toolbar_container: '#toolbar',
            content_style: `
                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
                    font-size: 14px;
                    line-height: 1.5;
                    padding: 10px;
                    min-height: 150px;
                }
            `,
            setup: function(editor) {
                editor.on('init', function() {
                    editor.getContainer().style.display = 'block';
                });
            }
        });
    }

    // Replace the initializeTinyMCE function with this:
    function initializeEditor(element) {
        return ClassicEditor
            .create(element, {
                toolbar: {
                    items: [
                        'bold', 'italic', 'underline',
                        '|', 'bulletedList', 'numberedList',
                        '|', 'alignment',
                        '|', 'link'
                    ],
                    shouldNotGroupWhenFull: true
                },
                balloonToolbar: false,
                language: 'en',
                removePlugins: ['MediaEmbed', 'Table', 'BlockQuote', 'Heading'],
                placeholder: element.getAttribute('data-placeholder') || 'Enter text here...',
                ui: {
                    viewportOffset: { top: 10 }
                }
            })
            .then(editor => {
                // Hide toolbar by default
                const toolbarElement = editor.ui.view.toolbar.element;
                toolbarElement.style.display = 'none';

                // Show toolbar only when editor is focused
                editor.ui.focusTracker.on('change:isFocused', (evt, name, isFocused) => {
                    toolbarElement.style.display = isFocused ? 'flex' : 'none';
                });

                return editor;
            })
            .catch(error => {
                console.error('Editor initialization error:', error);
            });
    }
});
</script>

<!-- Question Bank Modal -->
<div class="modal fade" id="questionBankModal" tabindex="-1" aria-labelledby="questionBankModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center">
                    <i class="fas fa-book info-icon me-2"></i>
                    <h5 class="modal-title" id="questionBankModalLabel">Import Questions</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Category Selection -->
                <div class="mb-3">
                    <label class="form-label">Select Category:</label>
                    <select class="form-control" id="categorySelect">
                        <option value="">All Categories</option>
                        <!-- Categories will be loaded dynamically -->
                    </select>
                </div>

                <!-- Import Options -->
                <div class="mb-4">
                    <h6>Import Options:</h6>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="importType" id="manualSelect" value="manual" checked>
                        <label class="form-check-label" for="manualSelect">
                            Manually select questions
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="importType" id="autoGenerate" value="auto">
                        <label class="form-check-label" for="autoGenerate">
                            Auto-generate questions
                        </label>
                    </div>
                </div>

                <!-- Manual Selection Section -->
                <div id="manualSelectSection">
                    <div class="mb-3">
                        <input type="text" id="questionSearch" class="form-control" placeholder="Search questions...">
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAll"></th>
                                    <th class="sortable" data-sort="question_text">Question <i class="fas fa-sort"></i></th>
                                    <th class="sortable" data-sort="question_type">Type <i class="fas fa-sort"></i></th>
                                    <th class="sortable" data-sort="points">Points <i class="fas fa-sort"></i></th>
                                </tr>
                            </thead>
                            <tbody id="questionBankList">
                                <!-- Questions will be loaded here dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Auto Generate Section -->
                <div id="autoGenerateSection" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label">Select Category:</label>
                        <select class="form-control" id="autoGenerateCategory">
                            <option value="">All Categories</option>
                            <!-- Categories will be loaded dynamically -->
                        </select>
                        <small class="text-muted" id="availableQuestionCount"></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Number of Questions:</label>
                        <input type="number" id="questionCount" class="form-control" min="1" value="5">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Question Types:</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="multiple_choice" id="typeMultipleChoice" checked>
                            <label class="form-check-label" for="typeMultipleChoice">
                                Multiple Choice (<span id="mcCount">0</span> available)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="true_false" id="typeTrueFalse">
                            <label class="form-check-label" for="typeTrueFalse">
                                True/False (<span id="tfCount">0</span> available)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="programming" id="typeProgramming">
                            <label class="form-check-label" for="typeProgramming">
                                Programming (<span id="progCount">0</span> available)
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="importSelectedQuestions">Import Questions</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center">
                    <i class="fas fa-trash-alt delete-icon"></i>
                    <p class="mb-0">Are you sure you want to delete this section? This action cannot be undone.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Warning Modal -->
<div class="modal fade" id="warningModal" tabindex="-1" aria-labelledby="warningModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="warningModalLabel">Action Required</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle warning-icon"></i>
                    <p class="mb-0" id="warningMessage"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>
</body>
</html>
