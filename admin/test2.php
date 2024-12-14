<?php 
// Get the exam_id from the query string
$exam_id = $_GET['exam_id'] ?? null;

// Ensure exam_id is passed
if (!$exam_id) {
    die("Exam ID is required to add questions.");
}

include_once __DIR__ . '/../config/config.php';

// Fetch existing sections and questions
$sections = array();
$stmt = $conn->prepare("
    SELECT * FROM sections 
    WHERE exam_id = ? 
    ORDER BY section_order
");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$sections_result = $stmt->get_result();

while ($section = $sections_result->fetch_assoc()) {
    // Fetch questions for this section
    $section['questions'] = array();
    $stmt = $conn->prepare("
        SELECT * FROM questions 
        WHERE section_id = ? 
        ORDER BY question_order
    ");
    $stmt->bind_param("i", $section['section_id']);
    $stmt->execute();
    $questions_result = $stmt->get_result();

    while ($question = $questions_result->fetch_assoc()) {
        // Fetch additional data based on question type
        switch ($question['question_type']) {
            case 'multiple_choice':
                $stmt = $conn->prepare("SELECT * FROM multiple_choice_options WHERE question_id = ?");
                $stmt->bind_param("i", $question['question_id']);
                $stmt->execute();
                $question['options'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                break;

            case 'programming':
                $stmt = $conn->prepare("SELECT * FROM test_cases WHERE question_id = ?");
                $stmt->bind_param("i", $question['question_id']);
                $stmt->execute();
                $question['test_cases'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                break;
        }
        $section['questions'][] = $question;
    }
    $sections[] = $section;
}

// Check if there's any content
$has_content = !empty($sections);

// Set page-specific head content
$additionalHead = '
    <!-- Load CKEditor first -->
    <script src="https://cdn.ckeditor.com/ckeditor5/40.1.0/classic/ckeditor.js"></script>
    <style>
    .ck-editor__editable {
        min-height: 100px;
    }
    .ck.ck-editor {
        width: 100%;
    }
    </style>

    <!-- Load other libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
';

$pageTitle = 'Create Exam - Questions';
include 'includes/header.php';
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
    /* Custom Styling */
    .empty-state {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        width: 80%;
        max-width: 600px;
        padding: 40px;
        background: #f8fafc;
        border: 2px dashed #e2e8f0;
        border-radius: 12px;
        z-index: 1;
    }

    .form-container {
        max-width: 1200px;
        margin: 20px auto;  
        padding: 20px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        height: 70vh;
        position: relative;
        overflow: hidden;
    }

    .form-scrollable {
        height: calc(100% - 60px);
        overflow-y: auto;
        padding: 20px;
        padding-bottom: 100px;
        position: relative;
    }

    #sectionBlocks {
        position: relative;
        min-height: 100%;
        width: 100%;
        display: block;
        padding-top: 20px;
    }

    .section-block {
        display: block !important;
        margin-bottom: 20px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        position: relative;
        width: 100%;
        min-height: 200px;
        visibility: visible !important;
        opacity: 1 !important;
    }

    .section-container {
        padding: 20px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        margin-bottom: 20px;
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }

    .form-container {
        max-width: 1200px;
        margin: 20px auto;  
        padding: 20px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        height: auto;
        min-height: 500px;
        position: relative;
        overflow: visible;
    }
    
    .form-scrollable {
        height: auto;
        overflow-y: auto;
        padding: 20px;
        padding-bottom: 100px;
        position: relative;
        display: block;
    }

    .editor-container {
        background: #fff;
        border-radius: 4px;
        margin-bottom: 10px;
        min-height: 50px;
        border: 1px solid #ddd;
        padding: 10px;
        display: block !important;
        position: static;
        width: 100%;
        opacity: 1;
        visibility: visible;
    }

    .ck.ck-editor {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        margin-bottom: 15px;
    }

    .ck-editor__editable {
        min-height: 100px !important;
        border: 1px solid #ddd !important;
        padding: 10px !important;
        background: #fff !important;
        display: block !important;
    }

    .empty-state {
        position: relative;
        top: auto;
        left: auto;
        transform: none;
        margin: 20px auto;
    }

    .title-block, 
    .description-block {
        position: relative;
        z-index: 1;
    }

    .title-block {
        margin-bottom: 15px;
        background: #fff;
        padding: 10px;
        border-radius: 8px 8px 0 0;
    }

    .description-block {
        background: #fff;
        padding: 10px;
        margin-bottom: 15px;
    }

    .editor-container[contenteditable="true"]:empty:before {
        content: attr(data-placeholder);
        color: #999;
        font-style: italic;
    }

    .form-container {
        max-width: 1200px;
        margin: 20px auto;  
        padding: 20px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        min-height: 70vh;
        position: relative;
        overflow: visible;
    }
    
    .form-scrollable {
        min-height: calc(100vh - 200px);
        overflow-y: auto;
        padding: 20px;
        padding-bottom: 80px;
        position: relative;
    }

    /* Remove any display:none that might be getting added */
    .ck-editor__editable,
    .ck.ck-editor__main,
    .ck.ck-editor__editable {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }

    /* Ensure CKEditor toolbar is visible */
    .ck.ck-toolbar {
        visibility: visible !important;
        opacity: 1 !important;
        z-index: 100;
    }

    #sectionBlocks {
        position: relative;
        width: 100%;
        display: block;
    }

    /* Style for the delete button to ensure it's visible */
    .delete-button {
        position: relative;
        z-index: 10;
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

    /* Quill Editor Styles */
    .ql-editor {
        min-height: 100px;
        font-size: 14px;
        line-height: 1.5;
        padding: 12px 15px;
    }

    .ql-container {
        border-bottom-left-radius: 4px;
        border-bottom-right-radius: 4px;
        background: #fff;
    }

    .ql-toolbar {
        border-top-left-radius: 4px;
        border-top-right-radius: 4px;
        background: #f8f9fa;
        border: 1px solid #ced4da;
    }

    .ql-toolbar .ql-stroke {
        stroke: #495057;
    }

    .ql-toolbar .ql-fill {
        fill: #495057;
    }

    .ql-toolbar .ql-picker {
        color: #495057;
    }

    .question-editor-wrapper .ql-container {
        height: auto;
    }

    /* Toolbar dropdown styles */
    .ql-snow .ql-picker.ql-size .ql-picker-label::before,
    .ql-snow .ql-picker.ql-size .ql-picker-item::before {
        content: 'Normal';
    }

    .ql-snow .ql-picker.ql-size .ql-picker-label[data-value=small]::before,
    .ql-snow .ql-picker.ql-size .ql-picker-item[data-value=small]::before {
        content: 'Small';
    }

    .ql-snow .ql-picker.ql-size .ql-picker-label[data-value=large]::before,
    .ql-snow .ql-picker.ql-size .ql-picker-item[data-value=large]::before {
        content: 'Large';
    }

    .ql-snow .ql-picker.ql-size .ql-picker-label[data-value=huge]::before,
    .ql-snow .ql-picker.ql-size .ql-picker-item[data-value=huge]::before {
        content: 'Huge';
    }

    /* Scroll Animation Styles */
    .scroll-animate {
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 0.5s ease, transform 0.5s ease;
    }

    .scroll-animate.visible {
        opacity: 1;
        transform: translateY(0);
    }

    .question-block {
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 0.5s ease, transform 0.5s ease;
    }

    .question-block.visible {
        opacity: 1;
        transform: translateY(0);
    }

    .section-block {
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 0.5s ease, transform 0.5s ease;
    }

    .section-block.visible {
        opacity: 1;
        transform: translateY(0);
    }

    .editor-container {
        min-height: 100px;
        border: 1px solid #ccc;
        border-radius: 4px;
        margin-bottom: 10px;
    }

    .section-title-editor {
        min-height: 50px !important;
    }

    .section-description-editor {
        min-height: 80px !important;
    }

    /* Override any potential conflicting styles */
    .scroll-animate {
        opacity: 1 !important;
        transform: none !important;
        visibility: visible !important;
        display: block !important;
    }

    /* Ensure editor containers are visible */
    .editor-container {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        min-height: 50px;
        margin-bottom: 10px;
    }

    /* Fix form container scrolling */
    .form-scrollable {
        min-height: 400px;
        overflow-y: auto;
        padding: 20px;
        padding-bottom: 100px;
        display: block !important;
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
                              </div>
                          <?php else: ?>
                              <?php foreach ($sections as $section): ?>
                                  <div class="section-block scroll-animate" data-section-id="<?php echo $section['section_id']; ?>">
                                      <div class="section-container">
                                          <div class="title-block">
                                              <div style="display: flex; justify-content: space-between; align-items: start;">
                                                  <div style="flex: 1; margin-right: 8px;">
                                                      <div class="section-title-editor" 
                                                          contenteditable="true"
                                                          data-placeholder="Input section title"
                                                          style="display: block !important; visibility: visible !important;">
                                                      </div>
                                                  </div>
                                                  <button type="button" class="delete-button btn btn-link text-danger" style="padding: 4px;">
                                                      <i class="fas fa-trash-alt"></i>
                                                  </button>
                                              </div>
                                          </div>
                                          <div class="description-block">
                                              <div class="section-description-editor" 
                                                  data-placeholder="Input description (optional)"
                                                  style="display: block !important; visibility: visible !important;">
                                              </div>
                                          </div>
                                          <div id="question-container-<?php echo $section['section_id']; ?>" class="question-block-container">
                                              <?php foreach ($section['questions'] as $question): ?>
                                                  <div class="question-block" 
                                                      data-question-id="<?php echo $question['question_id']; ?>" 
                                                      style="margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 8px;">
                                                      <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                                          <div style="flex: 1; margin-right: 10px;">
                                                              <div class="question-editor" 
                                                                  name="question_text[<?php echo $section['section_id']; ?>][]" 
                                                                  data-placeholder="Enter your question here"
                                                                  style="border: 1px solid #dee2e6; border-radius: 4px; min-height: 100px;">
                                                                  <?php echo $question['question_text']; ?>
                                                              </div>
                                                          </div>
                                                          <div style="min-width: 200px;">
                                                              <select class="form-control question-type-select">
                                                                  <option value="">Select Question Type</option>
                                                                  <option value="multiple_choice" <?php echo $question['question_type'] === 'multiple_choice' ? 'selected' : ''; ?>>Multiple Choice</option>
                                                                  <option value="true_false" <?php echo $question['question_type'] === 'true_false' ? 'selected' : ''; ?>>True/False</option>
                                                                  <option value="programming" <?php echo $question['question_type'] === 'programming' ? 'selected' : ''; ?>>Programming</option>
                                                              </select>
                                                          </div>
                                                          <button type="button" class="btn btn-link text-danger delete-question-btn" style="padding: 5px;">
                                                              <i class="fas fa-trash-alt"></i>
                                                          </button>
                                                      </div>
                                                      <div class="question-options" style="margin-top: 10px;">
                                                          <?php if ($question['question_type'] === 'multiple_choice' && !empty($question['options'])): ?>
                                                              <?php foreach ($question['options'] as $option): ?>
                                                                  <div class="option-item mb-2 d-flex align-items-center">
                                                                      <input type="radio" 
                                                                          name="correct_option_<?php echo $question['question_id']; ?>" 
                                                                          value="<?php echo $option['option_id']; ?>" 
                                                                          <?php echo $option['is_correct'] ? 'checked' : ''; ?> 
                                                                          class="me-2">
                                                                      <input type="text" 
                                                                          class="form-control me-2" 
                                                                          name="option_text_<?php echo $option['option_id']; ?>" 
                                                                          value="<?php echo htmlspecialchars($option['choice_text']); ?>">
                                                                      <button type="button" class="btn btn-link text-danger remove-option p-0 ms-1" style="font-size: 18px;">×</button>
                                                                  </div>
                                                              <?php endforeach; ?>
                                                              <button type="button" class="btn btn-secondary btn-sm mt-2 add-option">Add Option</button>
                                                          <?php elseif ($question['question_type'] === 'true_false'): ?>
                                                              <div class="mb-3">
                                                                  <div class="form-check">
                                                                      <input class="form-check-input" type="radio" 
                                                                          name="correct_<?php echo $question['question_id']; ?>" 
                                                                          value="true" 
                                                                          <?php echo $question['correct_answer'] === 'true' ? 'checked' : ''; ?>>
                                                                      <label class="form-check-label">True</label>
                                                                  </div>
                                                                  <div class="form-check">
                                                                      <input class="form-check-input" type="radio" 
                                                                          name="correct_<?php echo $question['question_id']; ?>" 
                                                                          value="false" 
                                                                          <?php echo $question['correct_answer'] === 'false' ? 'checked' : ''; ?>>
                                                                      <label class="form-check-label">False</label>
                                                                  </div>
                                                              </div>
                                                          <?php elseif ($question['question_type'] === 'programming' && !empty($question['test_cases'])): ?>
                                                              <div class="test-cases mb-3">
                                                                  <?php foreach ($question['test_cases'] as $testCase): ?>
                                                                      <div class="test-case mb-2">
                                                                          <div class="input-group">
                                                                              <input type="text" class="form-control" 
                                                                                  placeholder="Test Input" 
                                                                                  value="<?php echo htmlspecialchars($testCase['test_input']); ?>">
                                                                              <input type="text" class="form-control" 
                                                                                  placeholder="Expected Output" 
                                                                                  value="<?php echo htmlspecialchars($testCase['expected_output']); ?>">
                                                                              <button type="button" class="btn btn-link text-danger remove-test-case">×</button>
                                                                          </div>
                                                                      </div>
                                                                  <?php endforeach; ?>
                                                                  <button type="button" class="btn btn-secondary btn-sm mt-2 add-test-case">Add Test Case</button>
                                                              </div>
                                                          <?php endif; ?>
                                                      </div>
                                                      <div style="margin-top: 10px;">
                                                          <input type="number" class="form-control" placeholder="Points" style="width: 100px;" value="<?php echo $question['points']; ?>">
                                                      </div>
                                                  </div>
                                              <?php endforeach; ?>
                                          </div>
                                      </div>
                                  </div>
                              <?php endforeach; ?>
                          <?php endif; ?>
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


<!-- Initialize global variables -->
<script>
window.editorInstances = new Map();
</script>

<!-- Load custom scripts -->
<script src="js/editor-management.js"></script>
<script src="js/scroll.js"></script>
<script src="js/question_bank.js"></script>


<!-- Add this right before closing body tag -->
<script>
// Initialize EditorManager
const EditorManager = {
    editors: new Map(),
    
    initializeEditor: async function(container, placeholder = '') {
        if (!container) return null;
        
        // Wait for CKEditor to be available
        const initializeEditor = () => {
            try {
                return ClassicEditor
                    .create(container, {
                        placeholder: placeholder,
                        toolbar: ['bold', 'italic', 'link'],
                        removePlugins: ['CKFinderUploadAdapter', 'CKFinder', 'EasyImage', 'Image', 'ImageCaption', 'ImageStyle', 'ImageToolbar', 'ImageUpload'],
                        height: '200px'
                    })
                    .then(editor => {
                        this.editors.set(container, editor);
                        return editor;
                    })
                    .catch(error => {
                        console.error('Editor initialization error:', error);
                        return null;
                    });
            } catch (error) {
                console.error('Error initializing CKEditor:', error);
                return null;
            }
        };

        // Check if CKEditor is available
        if (typeof ClassicEditor !== 'undefined') {
            return initializeEditor();
        } else {
            // If CKEditor is not available, wait for it to load
            const checkEditor = setInterval(() => {
                if (typeof ClassicEditor !== 'undefined') {
                    clearInterval(checkEditor);
                    return initializeEditor();
                }
            }, 50);

            setTimeout(() => {
                clearInterval(checkEditor);
                console.error('CKEditor failed to load after maximum attempts');
            }, 2000);
        }
    }
};

// Update the ready check
const waitForEditor = setInterval(() => {
    if (typeof ClassicEditor !== 'undefined') {
        clearInterval(waitForEditor);
        window.dispatchEvent(new Event('EditorManagerReady'));
    }
}, 50);

// Initialize action buttons immediately
const actionButtons = document.querySelector('.action-buttons');
const showActionSidebarBtn = document.querySelector('.plus-button');

// Initialize action button events
if (showActionSidebarBtn) {
    showActionSidebarBtn.addEventListener('click', () => {
        console.log('+ button clicked');
        actionButtons.classList.toggle('active');
        showActionSidebarBtn.classList.toggle('active');
    });
}

// Hide buttons when clicking outside
document.addEventListener('click', (e) => {
    if (!actionButtons.contains(e.target) && 
        !showActionSidebarBtn.contains(e.target)) {
        actionButtons.classList.remove('active');
        showActionSidebarBtn.classList.remove('active');
    }
});

// Prevent clicks inside buttons from closing
if (actionButtons) {
    actionButtons.addEventListener('click', (e) => {
        e.stopPropagation();
    });
}

// Initialize everything when the DOM is ready and EditorManager is available
let isEditorManagerReady = false;
let isDOMReady = false;

function closeActionSidebar() {
    actionButtons.classList.remove('active');
    showActionSidebarBtn.classList.remove('active');
}

// Add a flag to prevent multiple initializations
let isInitialized = false;

function initializeApp() {
    // Prevent multiple initializations
    if (isInitialized) {
        console.log('App already initialized');
        return;
    }

    console.log('Attempting to initialize app...');
    console.log('isEditorManagerReady:', isEditorManagerReady);
    console.log('isDOMReady:', isDOMReady);
    
    if (!isEditorManagerReady || !isDOMReady) {
        console.log('Not ready to initialize yet');
        return;
    }

    console.log('Initializing app...');

    // Set initialization flag
    isInitialized = true;

    // Function to create new section
    function createNewSection() {
        console.log('Creating new section...');
        const sectionBlocks = document.getElementById('sectionBlocks');
        const emptyState = document.querySelector('.empty-state');
        
        // Remove empty state if it exists
        if (emptyState) {
            emptyState.remove();
        }

        // Create section container
        const section = document.createElement('div');
        section.className = 'section-block';
        section.style.cssText = 'display: block !important; visibility: visible !important; opacity: 1 !important;';
        
        // Add section HTML
        section.innerHTML = `
            <div class="section-container" style="display: block !important; visibility: visible !important;">
                <div class="title-block">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div style="flex: 1; margin-right: 8px;">
                            <div class="section-title-editor" 
                                contenteditable="true"
                                data-placeholder="Input section title"
                                style="display: block !important; visibility: visible !important;">
                            </div>
                        </div>
                        <button type="button" class="delete-button btn btn-link text-danger" style="padding: 4px;">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="description-block">
                    <div class="section-description-editor" 
                        data-placeholder="Input description (optional)"
                        style="display: block !important; visibility: visible !important;">
                    </div>
                </div>
                <div class="question-block-container"></div>
            </div>
        `;

        // Add to DOM
        sectionBlocks.appendChild(section);
        
        // Force reflow
        section.offsetHeight;
        
        // Remove any animation classes that might interfere
        section.classList.remove('scroll-animate');
        
        console.log('Section added to DOM');

        // Initialize CKEditor for title and description
        const titleContainer = section.querySelector('.section-title-editor');
        const descriptionContainer = section.querySelector('.section-description-editor');

        if (titleContainer) {
            ClassicEditor
                .create(titleContainer, {
                    placeholder: 'Input section title',
                    toolbar: {
                        items: ['bold', 'italic', 'link']
                    },
                    autoParagraph: false,
                    heading: {
                        options: []
                    },
                    removePlugins: ['CKFinderUploadAdapter', 'CKFinder', 'EasyImage', 'Image', 'ImageCaption', 'ImageStyle', 'ImageToolbar', 'ImageUpload'],
                })
                .then(editor => {
                    EditorManager.editors.set(titleContainer, editor);
                    titleContainer.removeAttribute('contenteditable');
                    titleContainer.classList.remove('editor-container');
                })
                .catch(error => console.error('Title editor error:', error));
        }

        if (descriptionContainer) {
            ClassicEditor
                .create(descriptionContainer, {
                    placeholder: 'Input description (optional)',
                    toolbar: {
                        items: ['bold', 'italic', 'link', 'bulletedList', 'numberedList']
                    },
                    autoParagraph: false,
                    heading: {
                        options: []
                    },
                    removePlugins: ['CKFinderUploadAdapter', 'CKFinder', 'EasyImage', 'Image', 'ImageCaption', 'ImageStyle', 'ImageToolbar', 'ImageUpload'],
                })
                .then(editor => {
                    EditorManager.editors.set(descriptionContainer, editor);
                    descriptionContainer.removeAttribute('contenteditable');
                    descriptionContainer.classList.remove('editor-container');
                })
                .catch(error => console.error('Description editor error:', error));
        }

        // Add delete handler
        const deleteBtn = section.querySelector('.delete-button');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Find the closest section-block parent
                const sectionBlock = this.closest('.section-block');
                if (sectionBlock) {
                    // Destroy any editors in this section
                    const editors = sectionBlock.querySelectorAll('.editor-container');
                    editors.forEach(editor => {
                        const editorInstance = EditorManager.editors.get(editor);
                        if (editorInstance) {
                            editorInstance.destroy();
                            EditorManager.editors.delete(editor);
                        }
                    });

                    // Remove the section
                    sectionBlock.remove();

                    // Check if we need to show empty state
                    if (sectionBlocks.children.length === 0) {
                        sectionBlocks.innerHTML = `
                            <div class="empty-state text-center py-5">
                                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                <h4 class="text-muted">No Questions Added Yet</h4>
                                <p class="text-muted mb-4">Get started by adding your first question or section.</p>
                            </div>
                        `;
                    }
                }
            });
        }
    }

    const saveFormBtn = document.getElementById('save-form-btn');
    const addSectionBtn = document.getElementById('add-section-btn');
    const importQuestionsBtn = document.getElementById('import-questions-btn');

    // Save button handler
    if (saveFormBtn) {
        saveFormBtn.addEventListener('click', () => {
            saveForm();
            closeActionSidebar();
        });
    }

    // Add section button handler
    if (addSectionBtn) {
        console.log('Setting up add section button handler');
        addSectionBtn.addEventListener('click', () => {
            console.log('Add section button clicked');
            createNewSection();
            closeActionSidebar();
        });
    } else {
        console.error('Add section button not found!');
    }

    // Import questions button handler
    if (importQuestionsBtn) {
        importQuestionsBtn.addEventListener('click', () => {
            const modal = new bootstrap.Modal(document.getElementById('questionBankModal'));
            modal.show();
            closeActionSidebar();
        });
    }

    // Initialize Question Bank Modal Event Listeners
    initializeQuestionBankListeners();
    console.log('App initialization complete');
}

// Listen for EditorManager ready event
window.addEventListener('EditorManagerReady', function() {
    console.log('EditorManager ready event received');
    isEditorManagerReady = true;
    if (isDOMReady) {
        initializeApp();
    }
});

// Listen for DOM ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM ready event received');
    isDOMReady = true;
    if (isEditorManagerReady) {
        initializeApp();
    }
});
</script>

</body>
</html>