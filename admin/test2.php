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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Exam</title>
  
  <!-- Stylesheets -->
  <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900&display=swap">
  <link rel="stylesheet" href="assets/fonts/fontawesome-all.min.css">
  <link rel="stylesheet" href="assets/css/styles.min.css">
  <style>
    /* Custom Styling */
    .form-container {
      max-width: 800px;
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
      padding-bottom: 60px;
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

          <div class="tab-menu">
            <a href="create-exam.php?exam_id=<?php echo $exam_id; ?>" class="btn btn-secondary">Back to Exam Creation</a>
            <button class="active">Questions</button>
            <a href="preview_exam.php?exam_id=<?php echo $exam_id; ?>" class="btn btn-primary">Preview</a>
            <a href="exam_settings.php?exam_id=<?php echo $exam_id; ?>" class="btn btn-primary">Settings</a>
            <a href="assign_exam.php?exam_id=<?php echo $exam_id; ?>" class="btn btn-primary">Assign</a>
            <a href="exam_results.php?exam_id=<?php echo $exam_id; ?>" class="btn btn-primary">Results</a>
          </div>

          <div class="form-container">
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
        
        sections.forEach(section => {
            const newSection = document.createElement('div');
            newSection.classList.add('section-block');
            newSection.setAttribute('data-section-id', section.section_id);
            
            newSection.innerHTML = `
                <div class="title-block">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <input type="text" class="form-control" name="section_title[${section.section_id}]" 
                            value="${section.section_title}" style="flex: 1; margin-right: 10px;">
                        <input type="hidden" name="section_id[${section.section_id}]" value="${section.section_id}">
                        <button type="button" class="delete-button btn btn-link text-danger" style="padding: 5px;">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="description-block">
                    <input type="text" class="form-control" name="section_description[${section.section_id}]" 
                        value="${section.section_description || ''}" placeholder="Description (optional)">
                </div>
                <div id="question-container-${section.section_id}" class="question-block-container"></div>
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

        // Update sectionCounter to be higher than any existing section ID
        const maxSectionId = Math.max(...sections.map(s => parseInt(s.section_id)), 0);
        sectionCounter = maxSectionId;

        attachEventListeners();
    }

    // Function to create question element with existing data
    function createQuestionElement(sectionId, questionIndex, questionData = null) {
        const newQuestion = document.createElement('div');
        newQuestion.classList.add('question-block');
        newQuestion.style.marginBottom = '20px';
        newQuestion.style.padding = '15px';
        newQuestion.style.border = '1px solid #ddd';
        newQuestion.style.borderRadius = '8px';

        newQuestion.innerHTML = `
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <textarea class="form-control" name="question_text[${sectionId}][${questionIndex}]" 
                    placeholder="Enter your question here" style="flex: 1; margin-right: 10px;" rows="3"
                    >${questionData ? questionData.question_text : ''}</textarea>
                <input type="hidden" name="question_id[${sectionId}][${questionIndex}]" 
                    value="${questionData ? questionData.question_id : ''}">
                <div style="min-width: 200px;">
                    <select class="form-control question-type-select" name="question_type[${sectionId}][${questionIndex}]">
                        <option value="">Select Question Type</option>
                        <option value="multiple_choice" ${questionData && questionData.question_type === 'multiple_choice' ? 'selected' : ''}>Multiple Choice</option>
                        <option value="true_false" ${questionData && questionData.question_type === 'true_false' ? 'selected' : ''}>True/False</option>
                        <option value="programming" ${questionData && questionData.question_type === 'programming' ? 'selected' : ''}>Programming</option>
                    </select>
                </div>
                <button type="button" class="btn btn-link text-danger delete-question-btn" style="padding: 5px;">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
            <div class="question-options" style="margin-top: 10px;"></div>
            <div style="margin-top: 10px;">
                <input type="number" name="points[${sectionId}][${questionIndex}]" 
                    class="form-control" placeholder="Points" style="width: 100px;"
                    value="${questionData ? questionData.points : ''}">
            </div>
        `;

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
            <div class="title-block">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <input type="text" class="form-control" name="section_title[${sectionCounter}]" placeholder="Untitled Section" style="flex: 1; margin-right: 10px;">
                    <button type="button" class="delete-button btn btn-link text-danger" style="padding: 5px;">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>
            <div class="description-block">
                <input type="text" class="form-control" name="section_description[${sectionCounter}]" placeholder="Description (optional)">
            </div>
            <div id="question-container-${sectionCounter}" class="question-block-container"></div>
        `;

        document.getElementById('sectionBlocks').appendChild(newSection);
        attachEventListeners();
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
                <textarea class="form-control" name="question_text[${sectionId}][${questionIndex}]" 
                    placeholder="Enter your question here" style="flex: 1; margin-right: 10px;" rows="3"></textarea>
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

        // Add event listener for question type selection
        const questionTypeSelect = newQuestion.querySelector('.question-type-select');
        questionTypeSelect.addEventListener('change', function() {
            handleQuestionTypeChange(this, sectionId, questionIndex);
        });

        // Add event listener for delete question button
        const deleteQuestionBtn = newQuestion.querySelector('.delete-question-btn');
        deleteQuestionBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this question?')) {
                newQuestion.remove();
            }
        });
    }

    // Handle question type change
    function handleQuestionTypeChange(select, sectionId, questionIndex, existingData = null) {
        const optionsContainer = select.closest('.question-block').querySelector('.question-options');
        optionsContainer.innerHTML = '';

        switch (select.value) {
            case 'multiple_choice':
                optionsContainer.innerHTML = `
                    <div class="multiple-choice-options">
                        <button type="button" class="btn btn-secondary add-option-btn" style="margin-bottom: 10px;">
                            Add Option
                        </button>
                    </div>
                `;
                const addOptionBtn = optionsContainer.querySelector('.add-option-btn');
                addOptionBtn.addEventListener('click', () => addMultipleChoiceOption(optionsContainer, sectionId, questionIndex));

                // Load existing options if available
                if (existingData && existingData.options) {
                    existingData.options.forEach((option, index) => {
                        const optionsDiv = optionsContainer.querySelector('.multiple-choice-options');
                        const optionDiv = document.createElement('div');
                        optionDiv.classList.add('option-container');
                        optionDiv.style.marginBottom = '10px';
                        optionDiv.innerHTML = `
                            <div class="input-group">
                                <input type="text" class="form-control" 
                                    name="options[${sectionId}][${questionIndex}][]" 
                                    value="${option.option_text}"
                                    placeholder="Option ${index + 1}">
                                <input type="hidden" name="option_id[${sectionId}][${questionIndex}][]" 
                                    value="${option.option_id}">
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <input type="radio" name="correct_answer[${sectionId}][${questionIndex}]" 
                                            value="${index}" ${option.is_correct == 1 ? 'checked' : ''}>
                                        <label style="margin-left: 5px;">Correct</label>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-link text-danger remove-option-btn" style="padding: 5px;">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        `;
                        
                        // Insert before the Add Option button
                        const addButton = optionsContainer.querySelector('.add-option-btn');
                        optionsDiv.insertBefore(optionDiv, addButton);

                        // Add event listener for remove button
                        optionDiv.querySelector('.remove-option-btn').addEventListener('click', function() {
                            optionDiv.remove();
                        });
                    });
                } else {
                    // Add initial options for new questions
                    addMultipleChoiceOption(optionsContainer, sectionId, questionIndex);
                    addMultipleChoiceOption(optionsContainer, sectionId, questionIndex);
                }
                break;

            case 'true_false':
                optionsContainer.innerHTML = `
                    <div class="true-false-option">
                        <select class="form-control" name="correct_answer[${sectionId}][${questionIndex}]" style="width: 200px;">
                            <option value="">Select Correct Answer</option>
                            <option value="true" ${existingData && existingData.correct_answer === 'true' ? 'selected' : ''}>True</option>
                            <option value="false" ${existingData && existingData.correct_answer === 'false' ? 'selected' : ''}>False</option>
                        </select>
                    </div>
                `;
                break;

            case 'programming':
                optionsContainer.innerHTML = `
                    <div class="programming-options">
                        <select class="form-control" name="programming_language[${sectionId}][${questionIndex}]" style="width: 200px; margin-bottom: 10px;">
                            <option value="python" ${existingData && existingData.programming_language && existingData.programming_language.language_name === 'python' ? 'selected' : ''}>Python</option>
                            <option value="java" ${existingData && existingData.programming_language && existingData.programming_language.language_name === 'java' ? 'selected' : ''}>Java</option>
                            <option value="c" ${existingData && existingData.programming_language && existingData.programming_language.language_name === 'c' ? 'selected' : ''}>C</option>
                        </select>
                        <div class="test-cases"></div>
                        <button type="button" class="btn btn-secondary add-test-case-btn" style="margin-top: 10px;">
                            Add Test Case
                        </button>
                    </div>
                `;
                const addTestCaseBtn = optionsContainer.querySelector('.add-test-case-btn');
                addTestCaseBtn.addEventListener('click', () => addTestCase(optionsContainer, sectionId, questionIndex));

                // Load existing test cases if available
                if (existingData && existingData.test_cases) {
                    existingData.test_cases.forEach(testCase => {
                        const testCasesDiv = optionsContainer.querySelector('.test-cases');
                        const testCaseDiv = document.createElement('div');
                        testCaseDiv.classList.add('test-case');
                        testCaseDiv.style.marginBottom = '10px';
                        testCaseDiv.innerHTML = `
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" 
                                    name="test_case_input[${sectionId}][${questionIndex}][]" 
                                    value="${testCase.input_data}"
                                    placeholder="Input">
                                <input type="hidden" name="test_case_id[${sectionId}][${questionIndex}][]" 
                                    value="${testCase.test_case_id}">
                                <input type="text" class="form-control" 
                                    name="test_case_output[${sectionId}][${questionIndex}][]" 
                                    value="${testCase.expected_output}"
                                    placeholder="Expected Output">
                                <button type="button" class="btn btn-link text-danger remove-test-case-btn" style="padding: 5px;">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        `;
                        testCasesDiv.appendChild(testCaseDiv);

                        // Add event listener for remove button
                        testCaseDiv.querySelector('.remove-test-case-btn').addEventListener('click', function() {
                            testCaseDiv.remove();
                        });
                    });
                } else {
                    // Add initial test case for new questions
                    addTestCase(optionsContainer, sectionId, questionIndex);
                }
                break;
        }
    }

    // Add multiple choice option
    function addMultipleChoiceOption(container, sectionId, questionIndex) {
        const optionsDiv = container.querySelector('.multiple-choice-options');
        const optionCount = optionsDiv.querySelectorAll('.option-container').length;
        
        const optionDiv = document.createElement('div');
        optionDiv.classList.add('option-container');
        optionDiv.style.marginBottom = '10px';
        optionDiv.innerHTML = `
            <div class="input-group">
                <input type="text" class="form-control" 
                    name="options[${sectionId}][${questionIndex}][]" 
                    placeholder="Option ${optionCount + 1}">
                <div class="input-group-append">
                    <div class="input-group-text">
                        <input type="radio" name="correct_answer[${sectionId}][${questionIndex}]" 
                            value="${optionCount}">
                        <label style="margin-left: 5px;">Correct</label>
                    </div>
                </div>
                <button type="button" class="btn btn-link text-danger remove-option-btn" style="padding: 5px;">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        `;

        // Insert before the Add Option button
        const addButton = container.querySelector('.add-option-btn');
        optionsDiv.insertBefore(optionDiv, addButton);

        // Add event listener for remove button
        optionDiv.querySelector('.remove-option-btn').addEventListener('click', function() {
            optionDiv.remove();
        });
    }

    // Add test case
    function addTestCase(container, sectionId, questionIndex) {
        const testCasesDiv = container.querySelector('.test-cases');
        const testCaseCount = testCasesDiv.children.length;
        
        const testCaseDiv = document.createElement('div');
        testCaseDiv.classList.add('test-case');
        testCaseDiv.style.marginBottom = '10px';
        testCaseDiv.innerHTML = `
            <div class="input-group mb-2">
                <input type="text" class="form-control" 
                    name="test_case_input[${sectionId}][${questionIndex}][]" 
                    placeholder="Input">
                <input type="text" class="form-control" 
                    name="test_case_output[${sectionId}][${questionIndex}][]" 
                    placeholder="Expected Output">
                <button type="button" class="btn btn-link text-danger remove-test-case-btn" style="padding: 5px;">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        `;

        testCasesDiv.appendChild(testCaseDiv);

        // Add event listener for remove button
        testCaseDiv.querySelector('.remove-test-case-btn').addEventListener('click', function() {
            testCaseDiv.remove();
        });
    }

    // Event Listeners
    addSectionBtn.addEventListener('click', () => {
        addSection();
        closeActionSidebar();
    });

    globalAddQuestionBtn.addEventListener('click', () => {
        const sections = document.querySelectorAll('.section-block');
        if (sections.length === 0) {
            alert('Please add a section first before adding questions.');
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
        const formData = new FormData(document.getElementById('questionForm'));
        
        fetch('save_question.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Questions saved successfully!');
            } else {
                alert('Error saving questions: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while saving.');
        });
    }

    function attachEventListeners() {
        // Add event listeners for dynamic elements
        document.querySelectorAll('.delete-button').forEach(btn => {
            btn.addEventListener('click', function() {
                if (confirm('Are you sure you want to delete this section?')) {
                    this.closest('.section-block').remove();
                }
            });
        });
    }
});
</script>


</body>
</html>
