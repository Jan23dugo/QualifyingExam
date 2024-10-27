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
            <button onclick="previewExam()">Preview</button>
            <button>Settings</button>
          </div>

          <form id="questionForm" method="POST" action="save_question.php">
            <input type="hidden" name="exam_id" value="<?= $exam_id ?>">
            <div class="form-container form-scrollable" id="formContainer">

              <div id="sectionBlocks">
                <div class="section-block">
                  <div class="title-block">
                    <input type="text" class="form-control" name="section_title[]" placeholder="Untitled Section" style="font-weight: bold;">
                    <button type="button" class="delete-button btn btn-danger" onclick="deleteSection(this)" style="margin-top: 10px;">Delete Section</button>
                  </div>
                  <div class="description-block">
                    <input type="text" class="form-control" name="section_description[]" placeholder="Description (optional)">
                  </div>
                  <div class="question-block-container" id="question-container-1"></div>
                  <button type="button" class="btn btn-primary mt-2" onclick="addQuestionToSection(1)">Add Question</button>
                </div>
              </div>

              <div class="add-buttons">
                <button type="button" onclick="addSection()">Add Section</button>
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
let sectionCounter = 1;

// Load existing form data
function loadFormData() {
    if (localStorage.getItem('formSections')) {
        const sectionBlocks = document.getElementById('sectionBlocks');
        sectionBlocks.innerHTML = localStorage.getItem('formSections');
        sectionCounter = document.querySelectorAll('.section-block').length;

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

// Save current form state to local storage
function saveFormData() {
    const sectionBlocks = document.getElementById('sectionBlocks');
    localStorage.setItem('formSections', sectionBlocks.innerHTML);
}

// Add a new section
function addSection() {
    sectionCounter++;
    const sectionBlocks = document.getElementById('sectionBlocks');

    const newSection = document.createElement('div');
    newSection.classList.add('section-block');
    newSection.setAttribute('data-section-id', sectionCounter);
    newSection.innerHTML = `
        <div class="title-block">
            <input type="text" class="form-control" name="section_title[]" placeholder="Untitled Section" style="font-weight: bold;">
            <button type="button" class="delete-button btn btn-danger" onclick="deleteSection(this)" style="margin-top: 10px;">Delete Section</button>
        </div>
        <div class="description-block">
            <input type="text" class="form-control" name="section_description[]" placeholder="Description (optional)">
        </div>
        <div id="question-container-${sectionCounter}" class="question-block-container"></div>
        <button type="button" class="btn btn-primary mt-2 add-question-button" data-section-id="${sectionCounter}" onclick="addQuestionToSection(${sectionCounter})">Add Question</button>
    `;
    sectionBlocks.appendChild(newSection);
    saveFormData(); // Save state
}

// Add a new question to a section
function addQuestionToSection(sectionId) {
    const questionContainer = document.getElementById(`question-container-${sectionId}`);
    if (questionContainer) {
        const newQuestion = document.createElement('div');
        newQuestion.classList.add('question-block');

        newQuestion.innerHTML = `
            <div style="display: flex; justify-content: space-between;">
              <textarea class="form-control" name="question_text[]" placeholder="Question" style="flex: 1;" rows="3"></textarea>
              <div class="question-type" style="margin-left: 10px;">
                <select class="form-control" name="question_type[]" onchange="handleQuestionTypeChange(this)">
                  <option value="">Select Question Type</option>
                  <option value="multiple_choice">Multiple Choice</option>
                  <option value="true_false">True/False</option>
                  <option value="programming">Programming</option>
                </select>
              </div>
              <button type="button" class="btn btn-danger" onclick="deleteQuestion(this)">Delete</button>
            </div>
            <div class="question-details" style="margin-top: 10px;"></div>
            <div style="margin-top: 10px;">
              <input type="number" name="points[]" class="form-control" placeholder="Points/Grades" style="width: 150px;">
            </div>
        `;
        questionContainer.appendChild(newQuestion);
        saveFormData(); // Save state
    } else {
        console.error(`Question container for section ${sectionId} not found!`);
    }
}

function handleQuestionTypeChange(selectElement) {
    const questionBlock = selectElement.closest('.question-block');
    const questionDetails = questionBlock.querySelector('.question-details');
    questionDetails.innerHTML = ''; // Clear previous details

    if (selectElement.value === 'multiple_choice') {
        questionDetails.innerHTML = getMultipleChoiceTemplate();
    } else if (selectElement.value === 'true_false') {
        questionDetails.innerHTML = getTrueFalseTemplate();
    } else if (selectElement.value === 'programming') {
        questionDetails.innerHTML = getProgrammingTemplate();
    }
}

function getMultipleChoiceTemplate() {
    return `
        <div>
          <input type="text" name="multiple_choice_options[]" class="form-control" placeholder="Option 1">
          <input type="text" name="multiple_choice_options[]" class="form-control" placeholder="Option 2" style="margin-top: 5px;">
          <button type="button" class="btn btn-link" onclick="addOption(this)">Add Option</button>
        </div>
    `;
}

function getTrueFalseTemplate() {
    return `
        <div>
          <select name="true_false_correct[]" class="form-control">
            <option value="">Select Correct Answer</option>
            <option value="true">True</option>
            <option value="false">False</option>
          </select>
        </div>
    `;
}

function getProgrammingTemplate() {
    return `
        <div>
          <label>Programming Language:</label>
          <select name="programming_language[]" class="form-control">
            <option value="c">C</option>
            <option value="java">Java</option>
            <option value="python">Python</option>
          </select>
          <br>
          <label>Test Cases:</label>
          <div class="test-cases">
            <div class="test-case">
              <input type="text" name="test_case_input[]" class="form-control" placeholder="Input">
              <input type="text" name="test_case_output[]" class="form-control" placeholder="Expected Output" style="margin-top: 5px;">
            </div>
          </div>
          <button type="button" class="btn btn-link" onclick="addTestCase(this)">Add Test Case</button>
        </div>
    `;
}

function addOption(button) {
    const optionsBlock = button.closest('.question-details');
    const optionCount = optionsBlock.querySelectorAll('input[type="text"]').length + 1;

    const newOption = document.createElement('input');
    newOption.type = 'text';
    newOption.name = `multiple_choice_options[]`;
    newOption.classList.add('form-control');
    newOption.placeholder = `Option ${optionCount}`;
    newOption.style.marginTop = '5px';
    optionsBlock.insertBefore(newOption, button);
}

function addTestCase(button) {
    const testCasesContainer = button.closest('.question-details').querySelector('.test-cases');
    const newTestCase = document.createElement('div');
    newTestCase.classList.add('test-case');
    newTestCase.innerHTML = `
        <input type="text" name="test_case_input[]" class="form-control" placeholder="Input">
        <input type="text" name="test_case_output[]" class="form-control" placeholder="Expected Output" style="margin-top: 5px;">
    `;
    testCasesContainer.appendChild(newTestCase);
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


// Handle form submission with AJAX
document.getElementById('questionForm').addEventListener('submit', function (event) {
    event.preventDefault(); // Prevent default form submission

    const form = event.target;
    const formData = new FormData(form);

    // Perform an AJAX request to save_question.php
    fetch('save_question.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json()) // Parse the JSON response
        .then(data => {
            if (data.success) {
                // Show the success message on the page
                const successMessage = document.getElementById('successMessage');
                successMessage.style.display = 'block';
                successMessage.textContent = data.message;

                // Optionally scroll to the top
                window.scrollTo(0, 0);

                // Hide the success message after a few seconds
                setTimeout(() => {
                    successMessage.style.display = 'none';
                }, 3000);

                // Clear saved form data
                clearFormData();
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
});

// Clear form data from local storage when saved successfully
function clearFormData() {
    localStorage.removeItem('formSections');
}

// Load form data when the page loads
window.addEventListener('load', loadFormData);

// Save form data to local storage whenever the user changes an input
document.getElementById('questionForm').addEventListener('input', saveFormData);
</script>


</body>
</html>