<?php
// Get the exam_id from the query string
$exam_id = $_GET['exam_id'] ?? null;

// Ensure exam_id is passed
if (!$exam_id) {
    die("Exam ID is required to preview the exam.");
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
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Preview Exam</title>
  
  <!-- Stylesheets -->
  <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900&display=swap">
  <link rel="stylesheet" href="assets/fonts/fontawesome-all.min.css">
  <link rel="stylesheet" href="assets/css/styles.min.css">
  <style>
    /* Custom Styling for Preview */
    .form-container {
      max-width: 800px;
      margin: 20px auto;
      padding: 20px;
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      height: auto;
      overflow-y: auto;
    }
    
    .question-block {
      margin-bottom: 20px;
    }

    .question-title {
      font-weight: bold;
      font-size: 18px;
      margin-bottom: 10px;
    }

    .option {
      margin-bottom: 8px;
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
          <div class="tab-menu">
            <a href="create-exam.php?exam_id=<?php echo $exam_id; ?>" class="btn btn-secondary">Back to Exam Creation</a>
            <button class="active">Preview Exam</button>
          </div>

          <div class="form-container">
            <?php if (empty($questions)): ?>
              <p>No questions available for this exam.</p>
            <?php else: ?>
              <?php foreach ($questions as $question): ?>
                <div class="question-block">
                  <div class="question-title">
                    <?php echo htmlspecialchars($question['question_text']); ?>
                  </div>
                  <?php if ($question['question_type'] == 'multiple_choice'): ?>
                    <?php foreach ($question['options'] as $option): ?>
                      <div class="option">
                        <input type="radio" disabled> <?php echo htmlspecialchars($option['option_text']); ?>
                      </div>
                    <?php endforeach; ?>
                  <?php elseif ($question['question_type'] == 'true_false'): ?>
                    <div class="option">
                      <input type="radio" disabled> True
                    </div>
                    <div class="option">
                      <input type="radio" disabled> False
                    </div>
                  <?php elseif ($question['question_type'] == 'programming'): ?>
                    <div class="option">
                      <p><strong>Programming Language:</strong> <?php echo htmlspecialchars($question['programming_language']); ?></p>
                      <p><strong>Test Cases:</strong></p>
                      <ul>
                        <?php foreach ($question['test_cases'] as $test_case): ?>
                          <li>Input: <?php echo htmlspecialchars($test_case['input']); ?>, Expected Output: <?php echo htmlspecialchars($test_case['expected_output']); ?></li>
                        <?php endforeach; ?>
                      </ul>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <!-- Footer -->
      <?php include 'footer.php'; ?>
    </div>
  </div>

  <!-- Bootstrap and JavaScript -->
  <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
