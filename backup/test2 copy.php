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
      overflow-y: auto; 
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
      max-height: 70vh;
      overflow-y: auto;
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
            <button>Settings</button>
          </div>

          <form id="questionForm" method="POST" action="save_question.php">
          <input type="hidden" name="exam_id" value="<?php echo $exam_id; ?>">
            <div class="form-container form-scrollable" id="formContainer">

            <div id="sectionBlocks">
              <div class="section-block" data-section-id="1">
                <div class="title-block">
                  <input type="text" class="form-control" name="section_title[1]" placeholder="Untitled Section" style="font-weight: bold;">
                  <button type="button" class="delete-button btn btn-danger" onclick="deleteSection(this)" style="margin-top: 10px;">Delete Section</button>
                </div>
                <div class="description-block">
                  <input type="text" class="form-control" name="section_description[1]" placeholder="Description (optional)">
                </div>
                <div class="question-block-container" id="add-question-container"></div>
                <button type="button" class="btn btn-primary mt-2 add-question-button" data-section-id="1">Add Question</button>

              </div>
            </div>

              <div class="add-buttons">
              <button type="button" id="add-section-btn">Add Section</button>
                <button type="submit" class="btn btn-primary">Save</button>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Footer -->
      <?php include 'footer.php'; ?>
    </div>
  </div>

<!-- JavaScript to dynamically add questions, titles, and sections -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  let sectionCounter = 1; // Initialize section counter
    const exam_id = new URLSearchParams(window.location.search).get('exam_id');

    if (exam_id) {
        // Fetching exam data using POST method
        fetch('save_question.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                exam_id: exam_id
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
          console.log('Response data:', data); // Debugging line to see the data structure
            if (data.success) {
                loadSectionsAndQuestions(data.sections);
            } else {
                console.error('Error fetching questions:', data.error);
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error.message || error);
        });
    }

    document.getElementById('add-section-btn').addEventListener('click', addSection);
    // Attach event listener to the Add Section button
    attachEventListeners(); // Attach event listeners to existing buttons

    // Add a new section
    function addSection() {
        sectionCounter++;
        const newSection = document.createElement('div');
        newSection.classList.add('section-block');
        newSection.setAttribute('data-section-id', sectionCounter);

        newSection.innerHTML = `
            <div class="title-block">
                <input type="text" class="form-control" name="section_title[${sectionCounter}]" placeholder="Untitled Section" style="font-weight: bold;">
                <button type="button" class="delete-button btn btn-danger" style="margin-top: 10px;">Delete Section</button>
            </div>
            <div class="description-block">
                <input type="text" class="form-control" name="section_description[${sectionCounter}]" placeholder="Description (optional)">
            </div>
            <div id="question-container-${sectionCounter}" class="question-block-container"></div>
            <button type="button" class="btn btn-primary mt-2 add-question-button" data-section-id="${sectionCounter}">Add Question</button>
        `;

        sectionBlocks.appendChild(newSection);

        // Attach event listener to the new section's buttons
        newSection.querySelector('.delete-button').addEventListener('click', function() {
            deleteSection(this);
        });
        newSection.querySelector('.add-question-button').addEventListener('click', function() {
            const sectionId = this.getAttribute('data-section-id');
            addQuestionToSection(sectionId);
        });
    }

// Create a global add question button that is not tied to a specific section
function createGlobalAddQuestionButton() {
    const addQuestionContainer = document.getElementById('add-question-container');
    addQuestionContainer.innerHTML = `
        <button type="button" class="btn btn-primary mt-2" id="global-add-question-btn">Add Question</button>
        <select id="select-section" class="form-control" style="width: 200px; display: inline-block;">
            <option value="">Select Section</option>
            ${[...document.querySelectorAll('.section-block')].map(section => {
                const sectionId = section.getAttribute('data-section-id');
                return `<option value="${sectionId}">Section ${sectionId}</option>`;
            }).join('')}
        </select>
    `;

    // Attach event listeners for the global add question button
    document.getElementById('global-add-question-btn').addEventListener('click', function () {
        const sectionId = document.getElementById('select-section').value;
        if (sectionId) {
            addQuestionToSection(sectionId);
        } else {
            alert('Please select a section to add the question to.');
        }
    });
}

// Call createGlobalAddQuestionButton on page load
createGlobalAddQuestionButton();

    function loadSectionsAndQuestions(sections) {

if (!Array.isArray(sections) || sections.length === 0) {
  console.error('Sections data is missing or not an array:', sections);
  return;
}
  const sectionBlocks = document.getElementById('sectionBlocks');
  sectionBlocks.innerHTML = ''; // Clear existing content

  sections.forEach(section => {
      const sectionId = section.section_id;

      const newSection = document.createElement('div');
      newSection.classList.add('section-block');
      newSection.setAttribute('data-section-id', sectionId);
      newSection.innerHTML = `
          <div class="title-block">
              <input type="text" class="form-control" name="section_title[${sectionId}]" value="${section.section_title}" style="font-weight: bold;">
              <button type="button" class="delete-button btn btn-danger" style="margin-top: 10px;">Delete Section</button>
          </div>
          <div class="description-block">
              <input type="text" class="form-control" name="section_description[${sectionId}]" value="${section.section_description}" placeholder="Description (optional)">
          </div>
          <div id="question-container-${sectionId}" class="question-block-container"></div>
          <button type="button" class="btn btn-primary mt-2 add-question-button" data-section-id="${sectionId}">Add Question</button>
      `;

      sectionBlocks.appendChild(newSection);

      // Add questions for this section
      const questionContainer = newSection.querySelector(`#question-container-${sectionId}`);
      section.questions.forEach((question, qIndex) => {
          const newQuestion = document.createElement('div');
          newQuestion.classList.add('question-block');
          newQuestion.innerHTML = `
              <div style="display: flex; justify-content: space-between;">
                  <textarea class="form-control" name="question_text[${sectionId}][]" placeholder="Question" style="flex: 1;" rows="3">${question.question_text}</textarea>
                  <div class="question-type" style="margin-left: 10px;">
                      <select class="form-control" name="question_type[${sectionId}][]">
                          <option value="multiple_choice" ${question.question_type === 'multiple_choice' ? 'selected' : ''}>Multiple Choice</option>
                          <option value="true_false" ${question.question_type === 'true_false' ? 'selected' : ''}>True/False</option>
                          <option value="programming" ${question.question_type === 'programming' ? 'selected' : ''}>Programming</option>
                      </select>
                  </div>
                  <button type="button" class="btn btn-danger delete-question-btn">Delete</button>
              </div>
              <div class="question-details" style="margin-top: 10px;">
                  ${getQuestionDetailsHTML(question, sectionId, qIndex)}
              </div>
              <div style="margin-top: 10px;">
                  <input type="number" name="points[${sectionId}][]" class="form-control" value="${question.points}" placeholder="Points/Grades" style="width: 150px;">
              </div>
          `;

          questionContainer.appendChild(newQuestion);
      });
  });

  attachEventListeners(); // Attach event listeners to the loaded elements
}

    function getQuestionDetailsHTML(question, sectionId, qIndex) {
        let html = '';
        if (question.question_type === 'multiple_choice') {
            html = getMultipleChoiceTemplate(sectionId, qIndex, question.options);
        } else if (question.question_type === 'true_false') {
            html = getTrueFalseTemplate(sectionId, qIndex, question.correct_answer);
        } else if (question.question_type === 'programming') {
            html = getProgrammingTemplate(sectionId, qIndex, question.test_cases);
        }
        return html;
    }

// Add a new question to a section
function addQuestionToSection(sectionId) {
        const questionContainer = document.getElementById(`question-container-${sectionId}`);
        if (questionContainer) {
            const newQuestion = document.createElement('div');
            newQuestion.classList.add('question-block');

            newQuestion.innerHTML = `
                <div style="display: flex; justify-content: space-between;">
                    <textarea class="form-control" name="question_text[${sectionId}][]" placeholder="Question" style="flex: 1;" rows="3"></textarea>
                    <div class="question-type" style="margin-left: 10px;">
                        <select class="form-control" name="question_type[${sectionId}][]">
                            <option value="">Select Question Type</option>
                            <option value="multiple_choice">Multiple Choice</option>
                            <option value="true_false">True/False</option>
                            <option value="programming">Programming</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-danger delete-question-btn">Delete</button>
                </div>
                <div class="question-details" style="margin-top: 10px;"></div>
                <div style="margin-top: 10px;">
                    <input type="number" name="points[${sectionId}][]" class="form-control" placeholder="Points/Grades" style="width: 150px;">
                </div>
            `;

            questionContainer.appendChild(newQuestion);

            // Attach event listener to the new question's delete button
            newQuestion.querySelector('.delete-question-btn').addEventListener('click', function() {
                deleteQuestion(this);
            });
        } else {
            console.error(`Question container for section ${sectionId} not found!`);
        }
    }

function handleQuestionTypeChange(selectElement) {
    const questionBlock = selectElement.closest('.question-block');
    const sectionId = questionBlock.closest('.section-block').getAttribute('data-section-id');
    const questionIndex = Array.from(questionBlock.parentElement.children).indexOf(questionBlock);
    const questionDetails = questionBlock.querySelector('.question-details');

    if (!questionDetails) {
        console.error(`Question details element not found.`);
        return;
    }

    // Clear previous details
    questionDetails.innerHTML = '';

    if (selectElement.value === 'multiple_choice') {
        questionDetails.innerHTML = getMultipleChoiceTemplate(sectionId, questionIndex);
    } else if (selectElement.value === 'true_false') {
        questionDetails.innerHTML = getTrueFalseTemplate(sectionId, questionIndex);
    } else if (selectElement.value === 'programming') {
        questionDetails.innerHTML = getProgrammingTemplate(sectionId, questionIndex);
    }

    // Attach event listeners to the new elements
    attachEventListeners();
}

function getMultipleChoiceTemplate(sectionId, questionIndex, options = []) {
        let html = '<div class="multiple-choice-options">';
        options.forEach((option, index) => {
            html += `
                <div class="option-container">
                    <input type="text" name="multiple_choice_options[${sectionId}][${questionIndex}][]" class="form-control" value="${option.option_text}" placeholder="Option ${index + 1}">
                    <input type="radio" name="correct_answer[${sectionId}][${questionIndex}]" value="${index}" ${option.is_correct ? 'checked' : ''}> Correct
                    <button type="button" class="btn btn-sm btn-danger remove-option-btn">Remove</button>
                </div>
            `;
        });
        html += '</div><button type="button" class="btn btn-sm btn-primary add-option-btn">Add Option</button>';
        return html;
    }

    function getTrueFalseTemplate(sectionId, questionIndex, correctAnswer = '') {
        return `
            <div>
                <select name="true_false_correct[${sectionId}][${questionIndex}]" class="form-control">
                    <option value="">Select Correct Answer</option>
                    <option value="true" ${correctAnswer === 'true' ? 'selected' : ''}>True</option>
                    <option value="false" ${correctAnswer === 'false' ? 'selected' : ''}>False</option>
                </select>
            </div>
        `;
    }

    function getProgrammingTemplate(sectionId, questionIndex, testCases = []) {
        let html = `
            <div>
                <label>Programming Language:</label>
                <select name="programming_language[${sectionId}][${questionIndex}]" class="form-control">
                    <option value="c">C</option>
                    <option value="java">Java</option>
                    <option value="python">Python</option>
                </select>
                <br>
                <label>Test Cases:</label>
                <div class="test-cases">
        `;
        testCases.forEach((testCase, index) => {
            html += `
                <div class="test-case">
                    <input type="text" name="test_case_input[${sectionId}][${questionIndex}][]" class="form-control" value="${testCase.input}" placeholder="Input">
                    <input type="text" name="test_case_output[${sectionId}][${questionIndex}][]" class="form-control" value="${testCase.expected_output}" placeholder="Expected Output" style="margin-top: 5px;">
                    <button type="button" class="btn btn-sm btn-danger remove-test-case-btn">Remove</button>
                </div>
            `;
        });
        html += '</div><button type="button" class="btn btn-sm btn-primary add-test-case-btn">Add Test Case</button></div>';
        return html;
    }

function addOption(button) {
    const optionsContainer = button.previousElementSibling;
    const optionCount = optionsContainer.children.length;  // Keep track of the number of options

    const questionBlock = button.closest('.question-block');
    const sectionId = questionBlock.closest('.section-block').getAttribute('data-section-id');
    const questionIndex = Array.from(questionBlock.parentElement.children).indexOf(questionBlock);

    const newOptionContainer = document.createElement('div');
    newOptionContainer.classList.add('option-container');
    newOptionContainer.innerHTML = `
        <input type="text" name="multiple_choice_options[${sectionId}][${questionIndex}][]" class="form-control" placeholder="Option ${optionCount + 1}">
        <input type="radio" name="correct_answer[${sectionId}][${questionIndex}]" value="${optionCount}"> Correct
        <button type="button" class="btn btn-sm btn-danger remove-option-btn">Remove</button>
    `;
    optionsContainer.appendChild(newOptionContainer);
    updateCorrectAnswerValues(optionsContainer);  // Make sure radio button values are updated

    // Attach event listener to the new Remove Option button
    newOptionContainer.querySelector('.remove-option-btn').addEventListener('click', function() {
        removeOption(this);
    });
}

function removeOption(button) {
    const optionContainer = button.closest('.option-container');
    const optionsContainer = optionContainer.parentElement;
    optionContainer.remove();
    updateCorrectAnswerValues(optionsContainer);
}

function updateCorrectAnswerValues(optionsContainer) {
    const options = optionsContainer.querySelectorAll('.option-container');
    options.forEach((option, index) => {
        option.querySelector('input[type="radio"]').value = index;
    });
}

function addTestCase(button) {
    const testCasesContainer = button.previousElementSibling;

    const questionBlock = button.closest('.question-block');
    const sectionId = questionBlock.closest('.section-block').getAttribute('data-section-id');
    const questionIndex = Array.from(questionBlock.parentElement.children).indexOf(questionBlock);

    const newTestCase = document.createElement('div');
    newTestCase.classList.add('test-case');
    newTestCase.innerHTML = `
        <input type="text" name="test_case_input[${sectionId}][${questionIndex}][]" class="form-control" placeholder="Input">
        <input type="text" name="test_case_output[${sectionId}][${questionIndex}][]" class="form-control" placeholder="Expected Output" style="margin-top: 5px;">
        <button type="button" class="btn btn-sm btn-danger remove-test-case-btn">Remove</button>
    `;
    testCasesContainer.appendChild(newTestCase);

    // Attach event listener to the new Remove Test Case button
    newTestCase.querySelector('.remove-test-case-btn').addEventListener('click', function() {
        removeTestCase(this);
    });
}

function removeTestCase(button) {
    const testCase = button.closest('.test-case');
    testCase.remove();
}

// Handle deletion of a section
function deleteSection(button) {
    if (confirm('Are you sure you want to delete this section? All questions in this section will also be deleted.')) {
        const sectionBlock = button.closest('.section-block');
        sectionBlock.remove();
        saveFormData(); // Save state
    }
}

// Handle deletion of a question
function deleteQuestion(button) {
    const questionBlock = button.closest('.question-block');
    if (confirm('Are you sure you want to delete this question?')) {
        questionBlock.remove();
        saveFormData(); // Save state
    }
}

// Attach event listeners to dynamically created elements
function attachEventListeners() {
        // Add Question buttons
        document.querySelectorAll('.add-question-button').forEach(function(button) {
            button.removeEventListener('click', addQuestionHandler);
            button.addEventListener('click', addQuestionHandler);
        });

        // Delete Section buttons
        document.querySelectorAll('.delete-button').forEach(function(button) {
            button.removeEventListener('click', deleteSectionHandler);
            button.addEventListener('click', deleteSectionHandler);
        });

        // Delete Question buttons
        document.querySelectorAll('.delete-question-btn').forEach(function(button) {
            button.removeEventListener('click', deleteQuestionHandler);
            button.addEventListener('click', deleteQuestionHandler);
        });

        // Question Type selects
        document.querySelectorAll('.question-type select').forEach(function(select) {
            select.removeEventListener('change', questionTypeChangeHandler);
            select.addEventListener('change', questionTypeChangeHandler);
        });

        // Add Option buttons
        document.querySelectorAll('.add-option-btn').forEach(function(button) {
            button.removeEventListener('click', addOptionHandler);
            button.addEventListener('click', addOptionHandler);
        });

        // Remove Option buttons
        document.querySelectorAll('.remove-option-btn').forEach(function(button) {
            button.removeEventListener('click', removeOptionHandler);
            button.addEventListener('click', removeOptionHandler);
        });

        // Add Test Case buttons
        document.querySelectorAll('.add-test-case-btn').forEach(function(button) {
            button.removeEventListener('click', addTestCaseHandler);
            button.addEventListener('click', addTestCaseHandler);
        });

        // Remove Test Case buttons
        document.querySelectorAll('.remove-test-case-btn').forEach(function(button) {
            button.removeEventListener('click', removeTestCaseHandler);
            button.addEventListener('click', removeTestCaseHandler);
        });
    }

    // Handler functions for attaching events
    function addQuestionHandler() {
        const sectionId = this.getAttribute('data-section-id');
        addQuestionToSection(sectionId);
    }

    function deleteSectionHandler() {
        deleteSection(this);
    }

    function deleteQuestionHandler() {
        deleteQuestion(this);
    }

    function questionTypeChangeHandler() {
        handleQuestionTypeChange(this);
    }

    function addOptionHandler() {
        addOption(this);
    }

    function removeOptionHandler() {
        removeOption(this);
    }

    function addTestCaseHandler() {
        addTestCase(this);
    }

    function removeTestCaseHandler() {
        removeTestCase(this);
    }
// Optional: Load saved form data
function loadFormData() {
    const savedData = localStorage.getItem('formData');
    if (savedData) {
        const formData = JSON.parse(savedData);
        const sectionBlocks = document.getElementById('sectionBlocks');
        sectionBlocks.innerHTML = formData.sectionsHTML;
        sectionCounter = formData.sectionCounter;

        // Restore input values
        formData.inputs.forEach(input => {
            const element = document.querySelector(`[name="${input.name}"]`);
            if (element) {
                element.value = input.value;
            }
        });

        // Add event listeners for dynamic elements
        sectionBlocks.querySelectorAll('.delete-button').forEach(btn => {
            btn.addEventListener('click', function () {
                deleteSection(btn);
            });
        });
        sectionBlocks.querySelectorAll('.add-question-button').forEach(btn => {
            btn.addEventListener('click', function () {
                const sectionId = btn.getAttribute('data-section-id');
                addQuestionToSection(sectionId);
            });
        });
    }
}

// Optional: Save form data
function saveFormData() {
    const sectionBlocks = document.getElementById('sectionBlocks');
    const inputs = Array.from(document.querySelectorAll('input, textarea, select')).map(input => ({
        name: input.name,
        value: input.value
    }));

    const formData = {
        sectionsHTML: sectionBlocks.innerHTML,
        sectionCounter: sectionCounter,
        inputs: inputs
    };

    localStorage.setItem('formData', JSON.stringify(formData));
}

    // Helper function to build nested objects
    function setDeepValue(obj, path, value) {
        const keys = path.replace(/\]/g, '').split('[');
        let current = obj;
        for (let i = 0; i < keys.length; i++) {
            const key = keys[i];
            if (i === keys.length - 1) {
                if (current[key] === undefined) {
                    current[key] = value;
                } else if (Array.isArray(current[key])) {
                    current[key].push(value);
                } else {
                    current[key] = [current[key], value];
                }
            } else {
                if (!current[key]) current[key] = {};
                current = current[key];
            }
        }
    }


 // Add form submit handler
 document.getElementById('questionForm').addEventListener('submit', function (event) {
        event.preventDefault();

        const formData = new FormData();
        formData.append('exam_id', document.querySelector('input[name="exam_id"]').value);

        // Manually append the nested data to flatten it for easier handling by PHP
        const inputs = document.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            if (input.name && input.value) {
                formData.append(input.name, input.value);
            }
        });

        // Debugging: log all FormData key-value pairs
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }

        // Send the data using fetch()
        fetch('save_question.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(responseData => {
            if (responseData.success) {
                alert('Questions saved successfully!');
            } else {
                alert('Error saving questions: ' + responseData.error);
            }
        })
        .catch(error => {
            console.error('Error:', error.message || error);
            alert('An error occurred while saving. Please check the console for more details.');
        });
    });

}); // This is the correct closing brace for 'DOMContentLoaded'
</script>


</body>
</html>
