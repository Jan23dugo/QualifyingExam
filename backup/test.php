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
    $questions[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Untitled Form</title>
  
  <!-- Stylesheets -->
  <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900&display=swap">
  <link rel="stylesheet" href="assets/fonts/fontawesome-all.min.css">
  <link rel="stylesheet" href="assets/css/styles.min.css">
  <style>
    .form-container {
      max-height: 70vh;
      overflow-y: auto;
    }
    .form-container {
      max-width: 800px;
      margin: 0 auto;
      padding: 20px;
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .add-buttons {
      margin: 20px 0;
      display: flex;
      justify-content: space-between;
    }
    .add-buttons button {
      background: #6200ea;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      cursor: pointer;
    }
    .add-buttons button:hover {
      background: #3700b3;
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
    .question-block {
      margin-top: 20px;
    }
    .question-type {
      margin-top: 10px;
    }
    .tab-menu {
      margin-bottom: 20px;
    }
    .tab-menu button {
      margin-right: 10px;
      padding: 10px 20px;
      background-color: #6200ea;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
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
        <!-- Topbar -->
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

<!-- Main Content -->
<div class="container-fluid">
    <div id="successMessage" class="alert alert-success" style="display: none;"></div>
    <div class="tab-menu">
        <a href="create-exam.php?exam_id=<?php echo $exam_id; ?>" class="btn btn-secondary" style="margin-right: 10px;">Back to Exam Creation</a>
        <button class="active">Questions</button>
        <button onclick="previewExam()">Preview</button>
        <button>Settings</button>
    </div>

    <!-- Form Container -->
    <form id="questionForm" method="POST" action="save_question.php">
      <input type="hidden" name="exam_id" value="<?= $exam_id ?>">
        <div class="form-container" id="formContainer">
            <div class="title-block">
                <input type="text" class="form-control" name="section_title" placeholder="Untitled Section" style="font-weight: bold;">
            </div>
            <div class="description-block">
                <input type="text" class="form-control" name="section_description" placeholder="Description (optional)">
            </div>

            <!-- Display previously added questions -->
            <div id="questionBlocks">
                <?php foreach ($questions as $index => $question): ?>
                <div class="question-block">
                    <div style="display: flex; justify-content: space-between;">
                        <textarea class="form-control" name="question_text[]" placeholder="Question" style="flex: 1;" rows="3"><?= htmlspecialchars($question['question_text']) ?></textarea>
                        <div class="question-type" style="margin-left: 10px;">
                            <select class="form-control" name="question_type[]">
                                <option value="multiple_choice" <?= $question['question_type'] == 'multiple_choice' ? 'selected' : '' ?>>Multiple Choice</option>
                                <option value="true_false" <?= $question['question_type'] == 'true_false' ? 'selected' : '' ?>>True/False</option>
                                <option value="programming" <?= $question['question_type'] == 'programming' ? 'selected' : '' ?>>Programming</option>
                            </select>
                        </div>
                    </div>
                    <div class="question-details" style="margin-top: 10px;">
                        <?php if ($question['question_type'] == 'multiple_choice' && isset($question['options'])): ?>
                            <?php foreach ($question['options'] as $option_index => $option): ?>
                                <input type="text" name="multiple_choice_options[<?= $index ?>][]" class="form-control" value="<?= htmlspecialchars($option['option_text']) ?>" style="margin-top: 5px;">
                                <input type="hidden" name="multiple_choice_correct[<?= $index ?>]" value="<?= $option['is_correct'] == 1 ? $option_index + 1 : '' ?>">
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div style="                    <div style="margin-top: 10px;">
                        <input type="number" name="points[]" class="form-control" placeholder="Points/Grades" style="width: 150px;" value="<?= htmlspecialchars($question['points']) ?>">
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Buttons for adding new elements -->
            <div class="add-buttons">
                <button type="button" onclick="addQuestion()">Add Question</button>
                <button type="button" onclick="addTitleDescription()">Add Title and Description</button>
                <button type="button" onclick="addSection()">Add Section</button>
            </div>
            <!-- Submit button -->
            <button type="submit" class="btn btn-primary">Save</button>
  
        </div>
    </form>
</div>

    </div>
          <!-- footer -->
          <?php include 'footer.php'; ?>
  </div>

  <!-- JavaScript to dynamically add questions, titles, and sections -->
  <script>
    let questionCounter = <?= count($questions) ?>;  // Set counter based on existing questions

    function addQuestion() {
      const questionBlocksContainer = document.getElementById('questionBlocks');
      const newQuestion = document.createElement('div');
      newQuestion.classList.add('question-block');

      // Increment the question counter for each new question
      questionCounter++;

      newQuestion.innerHTML = `
        <div style="display: flex; justify-content: space-between;">
          <textarea class="form-control" name="question_text[]" placeholder="Question" style="flex: 1;" rows="3"></textarea>
          <div class="question-type" style="margin-left: 10px;">
            <select class="form-control" name="question_type[]" onchange="handleQuestionTypeChange(this, ${questionCounter})">
              <option value="">Select Question Type</option>
              <option value="multiple_choice">Multiple Choice</option>
              <option value="true_false">True/False</option>
              <option value="programming">Programming</option>
            </select>
          </div>
        </div>
        <div class="question-details" style="margin-top: 10px;"></div>
        <div style="margin-top: 10px;">
          <input type="number" name="points[]" class="form-control" placeholder="Points/Grades" style="width: 150px;">
        </div>
      `;
      questionBlocksContainer.appendChild(newQuestion);
    }

    function handleQuestionTypeChange(selectElement, questionIndex) {
      const questionBlock = selectElement.closest('.question-block');
      const questionDetails = questionBlock.querySelector('.question-details');
      questionDetails.innerHTML = ''; // Clear previous details

      if (selectElement.value === 'multiple_choice') {
          questionDetails.innerHTML = getMultipleChoiceTemplate(questionIndex);
      } else if (selectElement.value === 'true_false') {
          questionDetails.innerHTML = getTrueFalseTemplate();
      } else if (selectElement.value === 'programming') {
          questionDetails.innerHTML = getProgrammingTemplate();
      }
    }

    function getMultipleChoiceTemplate(questionIndex) {
      return `
        <div>
          <input type="text" name="multiple_choice_options[${questionIndex}][]" class="form-control" placeholder="Option 1">
          <input type="text" name="multiple_choice_options[${questionIndex}][]" class="form-control" placeholder="Option 2" style="margin-top: 5px;">
          <input type="text" name="multiple_choice_options[${questionIndex}][]" class="form-control" placeholder="Option 3" style="margin-top: 5px;">
          <button type="button" class="btn btn-link" onclick="addOption(this, ${questionIndex})">Add Option</button>
          <br>
          <label>Correct Answer:</label>
          <select name="multiple_choice_correct[${questionIndex}]" class="form-control">
            <option value="">Select Correct Option</option>
            <option value="1">Option 1</option>
            <option value="2">Option 2</option>
            <option value="3">Option 3</option>
          </select>
        </div>
      `;
    }

    function addOption(button, questionIndex) {
      const optionsBlock = button.closest('.question-details');
      const optionCount = optionsBlock.querySelectorAll('input[type="text"]').length + 1;

      const newOption = document.createElement('input');
      newOption.type = 'text';
      newOption.name = `multiple_choice_options[${questionIndex}][]`;
      newOption.classList.add('form-control');
      newOption.placeholder = `Option ${optionCount}`;
      newOption.style.marginTop = '5px';
      optionsBlock.insertBefore(newOption, button);
    }

    function getTrueFalseTemplate() {
      return `
        <div>
          <label>True/False:</label>
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
              <input type="text" name="test_case_input[${questionCounter}][]" class="form-control" placeholder="Input">
              <input type="text" name="test_case_output[${questionCounter}][]" class="form-control" placeholder="Expected Output" style="margin-top: 5px;">
            </div>
          </div>
          <button type="button" class="btn btn-link" onclick="addTestCase(this, ${questionCounter})">Add Test Case</button>
        </div>
      `;
    }

    function addTestCase(button, questionIndex) {
      const testCasesContainer = button.closest('.question-details').querySelector('.test-cases');
      const newTestCase = document.createElement('div');
      newTestCase.classList.add('test-case');
      newTestCase.innerHTML = `
        <input type="text" name="test_case_input[${questionIndex}][]" class="form-control" placeholder="Input">
        <input type="text" name="test_case_output[${questionIndex}][]" class="form-control" placeholder="Expected Output" style="margin-top: 5px;">
      `;
      testCasesContainer.appendChild(newTestCase);
    }

    function addTitleDescription() {
      const formContainer = document.getElementById('formContainer');
      const newTitleDescription = document.createElement('div');
      newTitleDescription.innerHTML = `
        <div class="title-block">
          <input type="text" class="form-control" name="section_title[]" placeholder="Untitled Section" style="font-weight: bold;">
        </div>
        <div class="description-block">
          <input type="text" class="form-control" name="section_description[]" placeholder="Description (optional)">
        </div>
      `;
      formContainer.insertBefore(newTitleDescription, document.querySelector('.add-buttons'));
    }

    function addSection() {
      addTitleDescription();
      addQuestion();
    }
  </script>

<script>
document.getElementById('questionForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent the default form submission

    const form = event.target;
    const formData = new FormData(form);

    // Append the exam_id to the form data (assuming it exists in the URL)
    const examId = new URLSearchParams(window.location.search).get('exam_id');
    if (examId) {
        formData.append('exam_id', examId); // Append exam_id to the FormData object
    } else {
        console.error('Exam ID is missing.');
        return; // Stop form submission if exam_id is missing
    }

    fetch('save_question.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json()) // Expect JSON response from server
    .then(data => {
        if (data.success) {
            // Display success message
            const messageBox = document.getElementById('successMessage');
            messageBox.style.display = 'block';
            messageBox.textContent = data.message;

            // Optionally, scroll to the top to show the message clearly
            window.scrollTo(0, 0);

            // Hide the message after 3 seconds (3000 milliseconds)
            setTimeout(() => {
                messageBox.style.display = 'none';
            }, 3000);
        } else {
            console.error('Error:', data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
});
</script>

<script>
  function previewExam() {
    // Serialize form data into JSON format
    const formData = new FormData(document.getElementById('questionForm'));
    let data = {};
    formData.forEach((value, key) => {
      if (!data[key]) {
        data[key] = [];
      }
      data[key].push(value);
    });
    
    // Save the form data to localStorage (or pass via session/cookies, if necessary)
    localStorage.setItem('examPreviewData', JSON.stringify(data));
    
    // Redirect to preview page
    window.location.href = 'preview_exam.php';
  }
</script>

</body>
</html>

