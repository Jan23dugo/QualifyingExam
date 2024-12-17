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

    .section-block {
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 20px;
        background-color: #f9f9f9;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .title-block {
        margin-bottom: 15px;
    }

    .title-block input {
        font-size: 18px;
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

    .option-container input[type="radio"] {
        margin-right: 5px;
    }

    .plus-button {
        background-color: #007bff;
        color: white;
        border: none;
        padding: 10px 15px;
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

    .floating-toolbar:before {
        content: '';
        position: absolute;
        top: -6px;
        left: 10px;
        width: 0;
        height: 0;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-bottom: 5px solid #ddd;
    }

    .floating-toolbar:after {
        content: '';
        position: absolute;
        top: -5px;
        left: 10px;
        width: 0;
        height: 0;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-bottom: 5px solid #fff;
    }

    .toolbar-btn {
        padding: 4px 8px;
        margin: 0 2px;
        font-size: 14px;
    }

    .toolbar-separator {
        margin: 0 4px;
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
              <a href="create-exam.php?exam_id=<?php echo $exam_id; ?>" class="btn btn-secondary">Back to Exam Creation</a>
              <button class="active">Questions</button>
              <a href="preview_exam.php?exam_id=<?php echo $exam_id; ?>" class="btn btn-primary">Preview</a>
              <a href="exam_settings.php?exam_id=<?php echo $exam_id; ?>" class="btn btn-primary">Settings</a>
          </div>

          <div class="form-container">
          <?php include 'question_toolbar.php'; ?>
              <div class="form-scrollable">
                  <form id="questionForm" method="POST" action="save_question.php">
                      <input type="hidden" name="exam_id" value="<?php echo $exam_id; ?>">
                      <div id="sectionBlocks">
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

<script src="js/test2.js"></script>


<!-- Question Bank Modal -->
<div class="modal fade" id="questionBankModal" tabindex="-1" aria-labelledby="questionBankModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="questionBankModalLabel">Import Questions</h5>
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

<!-- Add this modal for programming test cases -->
<div class="modal fade" id="addTestCaseModal" tabindex="-1" aria-labelledby="addTestCaseModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addTestCaseModalLabel">Add Test Case</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Input</label>
          <textarea class="form-control" id="testCaseInput" rows="3"></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">Expected Output</label>
          <textarea class="form-control" id="testCaseOutput" rows="3"></textarea>
        </div>
        <div class="mb-3">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="testCaseHidden">
            <label class="form-check-label" for="testCaseHidden">
              Hidden Test Case
            </label>
          </div>
        </div>
        <div class="mb-3 hidden-description" style="display: none;">
          <label class="form-label">Description (shown to students for hidden test cases)</label>
          <input type="text" class="form-control" id="testCaseDescription" placeholder="Optional description">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveTestCase">Add Test Case</button>
      </div>
    </div>
  </div>
</div>

<!-- Add this script section at the end of the file -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize test case modal functionality
    const testCaseModal = document.getElementById('addTestCaseModal');
    const testCaseHidden = document.getElementById('testCaseHidden');
    const hiddenDescription = testCaseModal.querySelector('.hidden-description');

    // Show/hide description field when hidden checkbox changes
    testCaseHidden.addEventListener('change', function() {
        hiddenDescription.style.display = this.checked ? 'block' : 'none';
    });

    // Reset modal when it's hidden
    testCaseModal.addEventListener('hidden.bs.modal', function() {
        testCaseModal.querySelector('#testCaseInput').value = '';
        testCaseModal.querySelector('#testCaseOutput').value = '';
        testCaseHidden.checked = false;
        testCaseModal.querySelector('#testCaseDescription').value = '';
        hiddenDescription.style.display = 'none';
    });
});
</script>

</body>
</html>
