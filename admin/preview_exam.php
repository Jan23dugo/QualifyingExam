<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/handlers/exam_data_handler.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Get exam ID from URL
    $exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : 0;

    if ($exam_id === 0) {
        throw new Exception("No exam ID provided");
    }

    // Get exam data using the handler
    $exam_data = getExamData($exam_id, $conn);
    $exam = $exam_data['exam'];
    $sections = $exam_data['sections'];

    // Initialize pagination variables
    $questions_per_page = 10;
    $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    
    // Calculate total questions
    $total_questions = 0;
    $all_questions = [];
    foreach ($sections as $section) {
        foreach ($section['questions'] as $question) {
            $all_questions[] = $question;
            $total_questions++;
        }
    }

    // Calculate total pages
    $total_pages = ceil($total_questions / $questions_per_page);
    
    // Ensure current page is within valid range
    $current_page = max(1, min($current_page, $total_pages));
    
    // Calculate start and end question indices for current page
    $start_question = ($current_page - 1) * $questions_per_page;
    $end_question = min($start_question + $questions_per_page, $total_questions);
    
    // Get current page questions
    $current_questions = array_slice($all_questions, $start_question, $questions_per_page);

    // Add this after fetching exam data
    $settings_stmt = $conn->prepare("SELECT * FROM exam_settings WHERE exam_id = ?");
    $settings_stmt->bind_param("i", $exam_id);
    $settings_stmt->execute();
    $exam_settings = $settings_stmt->get_result()->fetch_assoc();

    // Apply randomization to questions if enabled
    if ($exam_settings['randomize_questions']) {
        foreach ($sections as &$section) {
            shuffle($section['questions']);
        }
    }

    // Apply randomization to multiple choice options if enabled
    if ($exam_settings['randomize_options']) {
        foreach ($sections as &$section) {
            foreach ($section['questions'] as &$question) {
                if ($question['type'] === 'multiple_choice' && isset($question['options'])) {
                    shuffle($question['options']);
                }
            }
        }
    }

    // Set time limit from settings
    $duration = $exam_settings['time_limit'] ?? 0;

} catch (Exception $e) {
    error_log("Error in preview_exam.php: " . $e->getMessage());
    die("An error occurred while loading the exam: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/exam-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
    /* Main layout */
    .main-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0;
        display: block;
    }

    h1 {
        font-size: 24px;
        color: #333;
        margin-bottom: 30px;
    }

    /* Questions area */
    .question-box {
        background: white;
        border-radius: 8px;
        padding: 24px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        position: relative;
    }

    .question-header {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 20px;
        font-size: 16px;
        line-height: 1.5;
    }

    .question-number {
        font-weight: 500;
        min-width: 15px;
    }

    .bookmark-btn {
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
        position: absolute;
        top: 24px;
        right: 24px;
        transition: transform 0.2s ease;
    }

    .bookmark-btn:hover {
        transform: scale(1.1);
    }

    .bookmark-icon {
        font-size: 24px;
        color: #666;
        transition: color 0.3s ease;
    }

    .bookmark-icon.marked {
        color: #FFC107;
    }

    /* Options styling */
    .options {
        display: flex;
        flex-direction: column;
        gap: 16px;
        margin-left: 27px;
    }

    .option {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        font-size: 15px;
        color: #333;
    }

    .option input[type="radio"] {
        margin: 0;
        width: 16px;
        height: 16px;
        cursor: pointer;
    }

    /* Submit button */
    .submit-btn {
        display: block;
        width: 100%;
        background-color: #4CAF50;
        color: white;
        padding: 12px;
        border: none;
        border-radius: 4px;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        margin-top: auto;
    }

    .submit-btn:hover {
        background-color: #45a049;
    }

    /* Sidebar */
    .sidebar {
        padding: 20px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    /* Status section */
    .status-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin: 15px 0;
    }

    .status-number {
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: white;
        border: 1px solid #ddd;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    /* Not answered */
    .status-number {
        background: white;
        color: #333;
        border-color: #ddd;
    }

    /* Only answered */
    .status-number.answered {
        background: #4CAF50;
        color: white;
        border-color: #4CAF50;
    }

    /* Only marked for review */
    .status-number.marked:not(.answered) {
        background: #FFC107;
        color: black;
        border-color: #FFC107;
    }

    /* Both answered and marked for review */
    .status-number.answered.marked {
        background: #FF9800;
        color: white;
        border-color: #FF9800;
    }

    /* Timer section */
    .timer-section {
        margin-top: 30px;
    }

    .time {
        font-size: 36px;
        font-weight: bold;
        color: #333;
        margin: 10px 0;
    }

    .time-info {
        color: #666;
        font-size: 14px;
    }

    .estimated-time {
        color: #666;
        font-size: 14px;
        margin-top: 5px;
    }

    /* Section styling */
    .section-header {
        margin-bottom: 30px;
    }

    .section-header h2 {
        font-size: 24px;
        color: #333;
        margin-bottom: 10px;
    }

    .section-description {
        color: #666;
        font-size: 16px;
    }

    /* Question content styling */
    .question-content {
        flex: 1;
    }

    .marks {
        color: #666;
        font-size: 14px;
        margin-left: 10px;
    }

    /* True/False specific styling */
    .options.true-false {
        flex-direction: row;
        gap: 30px;
    }

    /* Programming question styling */
    .programming-content {
        margin-left: 27px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 4px;
    }

    .programming-content h4 {
        font-size: 16px;
        color: #333;
        margin: 15px 0 10px;
    }

    .test-cases {
        margin: 20px 0;
    }

    .test-case {
        background: white;
        padding: 15px;
        border-radius: 4px;
        margin: 10px 0;
        border: 1px solid #e0e0e0;
    }

    .test-input, .test-explanation {
        margin: 5px 0;
    }

    .test-input strong, .test-explanation strong {
        margin-right: 10px;
    }

    .constraints ul {
        margin: 0;
        padding-left: 20px;
        color: #666;
    }

    /* Mobile responsiveness */
    @media (max-width: 768px) {
        .options.true-false {
            flex-direction: column;
        }
    }

    /* Code Editor Styles */
    .code-editor-container {
        margin: 15px 0;
    }

    .code-editor {
        width: 100%;
        height: 200px;
        font-family: 'Courier New', monospace;
        font-size: 14px;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        background-color: #f8f9fa;
        resize: vertical;
    }

    /* Test Cases Styles */
    .test-cases {
        margin: 20px 0;
    }

    .test-case {
        background: white;
        padding: 15px;
        border-radius: 4px;
        margin: 10px 0;
        border: 1px solid #e0e0e0;
    }

    .test-input, .test-output, .test-explanation {
        margin: 8px 0;
        font-size: 14px;
    }

    .test-input strong, .test-output strong, .test-explanation strong {
        color: #555;
        margin-right: 10px;
    }

    /* Code Actions Styles */
    .code-actions {
        margin-top: 15px;
    }

    .run-code-btn {
        background-color: #4CAF50;
        color: white;
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
    }

    .run-code-btn:hover {
        background-color: #45a049;
    }

    .code-output {
        margin-top: 10px;
        padding: 10px;
        background-color: #f8f9fa;
        border: 1px solid #ddd;
        border-radius: 4px;
        min-height: 40px;
        display: none;
    }

    /* Status grid styles */
    .status-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin: 15px 0;
    }

    .status-number {
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: white;
        border: 1px solid #ddd;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .status-number.answered {
        background: #4CAF50;
        color: white;
        border-color: #4CAF50;
    }

    .status-number.marked {
        background: #FFC107;
        color: black;
        border-color: #FFC107;
    }

    .status-number.marked.answered {
        background: #FF9800;
        color: white;
        border-color: #FF9800;
    }

    /* Legend styles */
    .status-legend {
        margin-top: 15px;
    }

    .legend-item {
        display: flex;
        align-items: center;
        margin: 5px 0;
        font-size: 14px;
    }

    .dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-right: 8px;
    }

    .dot.answered {
        background: #4CAF50;
    }

    .dot.marked {
        background: #FFC107;
    }

    .dot.not-answered {
        background: white;
        border: 1px solid #ddd;
    }

    /* Bookmark icon styles */
    .bookmark-icon {
        cursor: pointer;
        font-size: 20px;
        color: #666;
        transition: color 0.3s ease;
    }

    .bookmark-icon.marked {
        color: #FFC107;
    }

    /* Enhanced back button container styles */
    .back-button-container {
        padding: 10px 40px;
        background: white;
        border-bottom: 1px solid #eee;
        position: sticky;
        top: 0;
        z-index: 1000;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    /* Enhanced back button styles */
    .back-button {
        display: inline-flex;
        align-items: center;
        padding: 10px 20px;
        background-color: #fff;
        color: #333;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 500;
        font-size: 15px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .back-button:hover {
        background-color: #f8f9fa;
        color: #000;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        border-color: #d0d0d0;
    }

    .back-button:active {
        transform: translateY(0);
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .back-button i {
        margin-right: 10px;
        font-size: 16px;
        transition: transform 0.3s ease;
    }

    .back-button:hover i {
        transform: translateX(-2px);
    }

    /* Optional: Add a subtle animation when the page loads */
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-10px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .back-button {
        animation: slideIn 0.3s ease-out;
    }

    /* Questions area styles */
    .questions-area {
        padding: 20px 40px;
        flex: 1;
    }

    /* Content wrapper styles */
    .content-wrapper {
        display: grid;
        grid-template-columns: 3fr 1fr;
        gap: 30px;
        padding: 0 40px;
    }

    /* Header styles */
    .header {
        padding: 15px 40px;
        background: white;
        border-bottom: 1px solid #eee;
        position: sticky;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        width: 100vw;
        display: flex;
        align-items: center;
        height: 70px;
        margin: 0;
    }

    /* Left side of header with back arrow and title */
    .header-left {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    /* Preview mode text */
    .preview-mode-text {
        font-size: 20px;
        font-weight: 400;
        color: #333;
        text-transform: lowercase;
    }

    /* Back arrow link */
    .back-link {
        display: flex;
        align-items: center;
        text-decoration: none;
        color: #000;
        padding: 8px;
        transition: all 0.2s ease;
    }

    .back-link:hover {
        color: #000;
    }

    .back-link i {
        font-size: 24px;
        color: #000;
    }

    /* Adjust main container for full-width header */
    .main-container {
        margin-top: 0;
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
    }

    .content-wrapper {
        padding-top: 20px;
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Section container styles */
    .section-container {
        background: white;
        border-radius: 8px;
        padding: 24px;
        margin-bottom: 30px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    /* Section header styles */
    .section-header {
        margin-bottom: 20px;
    }

    .section-header h2 {
        font-size: 24px;
        color: #333;
        margin-bottom: 10px;
        font-weight: 500;
    }

    .section-description {
        color: #666;
        font-size: 16px;
        line-height: 1.5;
    }

    /* Update content wrapper padding */
    .content-wrapper {
        padding: 20px 40px;
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Questions area might need adjustment */
    .questions-area {
        flex: 1;
    }
</style>
</head>
<body>
    <?php
    // Get exam duration from the database
    $exam_id = $_GET['exam_id'] ?? null;
    $duration = 0;
    
    if ($exam_id) {
        $stmt = $conn->prepare("SELECT duration FROM exams WHERE exam_id = ?");
        $stmt->bind_param("i", $exam_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $duration = $row['duration'];
        }
    }
    ?>

    <div class="main-container">
        <div class="header">
            <div class="header-left">
                <a href="test2.php?exam_id=<?php echo $exam_id; ?>" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <span class="preview-mode-text">preview mode</span>
            </div>
        </div>
        
        <div class="content-wrapper">
            <div class="questions-area">
                <?php foreach ($sections as $section_id => $section): ?>
                    <div class="section-container">
                        <div class="section-header">
                            <h2><?php echo htmlspecialchars($section['title']); ?></h2>
                            <?php if (!empty($section['description'])): ?>
                                <p class="section-description"><?php echo htmlspecialchars($section['description']); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Questions in this section -->
                        <?php foreach ($section['questions'] as $question): ?>
                            <div class="question-box">
                                <div class="question-header">
                                    <span class="question-number"><?php echo $question['number']; ?></span>
                                    <div class="question-content">
                                        <p><?php echo htmlspecialchars($question['question']); ?></p>
                                    </div>
                                    <button class="bookmark-btn" onclick="toggleBookmark(this, <?php echo $question['number']; ?>)">
                                        <span class="bookmark-icon">☆</span>
                                    </button>
                                </div>

                                <?php switch($question['type']): 
                                    case 'multiple_choice': ?>
                                        <div class="options">
                                            <?php foreach ($question['options'] as $option): ?>
                                                <label class="option">
                                                    <input type="radio" name="q<?php echo $question['question_id']; ?>">
                                                    <span><?php echo htmlspecialchars($option); ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php break;

                                    case 'true_false': ?>
                                        <div class="options true-false">
                                            <label class="option">
                                                <input type="radio" name="q<?php echo $question['question_id']; ?>" value="true">
                                                <span>True</span>
                                            </label>
                                            <label class="option">
                                                <input type="radio" name="q<?php echo $question['question_id']; ?>" value="false">
                                                <span>False</span>
                                            </label>
                                        </div>
                                        <?php break;

                                    case 'programming': ?>
                                        <div class="programming-content">
                                            <?php if ($question['programming_language']): ?>
                                                <div class="language-info">
                                                    <strong>Programming Language:</strong> 
                                                    <?php echo htmlspecialchars($question['programming_language']); ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <!-- Code Editor -->
                                            <div class="code-editor-container">
                                                <textarea id="code-editor-<?php echo $question['question_id']; ?>" class="code-editor">// Write your code here</textarea>
                                            </div>

                                            <!-- Test Cases -->
                                            <div class="test-cases">
                                                <h4>Test Cases:</h4>
                                                <?php 
                                                // Fetch test cases for this question
                                                $test_cases_stmt = $conn->prepare("SELECT * FROM test_cases WHERE question_id = ? ORDER BY test_case_id");
                                                $test_cases_stmt->bind_param("i", $question['question_id']);
                                                $test_cases_stmt->execute();
                                                $test_cases = $test_cases_stmt->get_result();
                                                
                                                while ($test_case = $test_cases->fetch_assoc()): 
                                                ?>
                                                    <div class="test-case">
                                                        <div class="test-input">
                                                            <strong>Input:</strong> <?php echo htmlspecialchars($test_case['test_input']); ?>
                                                        </div>
                                                        <div class="test-output">
                                                            <strong>Expected Output:</strong> <?php echo htmlspecialchars($test_case['expected_output']); ?>
                                                        </div>
                                                        <?php if ($test_case['description']): ?>
                                                            <div class="test-explanation">
                                                                <strong>Explanation:</strong> <?php echo htmlspecialchars($test_case['description']); ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endwhile; ?>
                                            </div>

                                            <!-- Run Code Button -->
                                            <div class="code-actions">
                                                <button class="run-code-btn" onclick="runCode(<?php echo $question['question_id']; ?>)">
                                                    Run Code
                                                </button>
                                                <div id="code-output-<?php echo $question['question_id']; ?>" class="code-output"></div>
                                            </div>
                                        </div>
                                        <?php break;
                                endswitch; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="sidebar">
                <div class="status-section">
                    <h3>Questions status</h3>
                    <div class="status-grid">
                        <?php 
                        $total_questions = 0;
                        foreach ($sections as $section) {
                            $total_questions += count($section['questions']);
                        }
                        for ($i = 1; $i <= $total_questions; $i++): 
                        ?>
                            <div class="status-number" data-question="<?php echo $i; ?>"><?php echo $i; ?></div>
                        <?php endfor; ?>
                    </div>
                    <div class="status-legend">
                        <div class="legend-item">
                            <span class="dot answered"></span>
                            <span>Answered</span>
                        </div>
                        <div class="legend-item">
                            <span class="dot marked"></span>
                            <span>Marked for review</span>
                        </div>
                        <div class="legend-item">
                            <span class="dot not-answered"></span>
                            <span>Not Answered</span>
                        </div>
                    </div>
                </div>

                <div class="timer-section">
                    <h3>Timer</h3>
                    <div class="time" id="time">
                        <?php echo sprintf("%02d:%02d", floor($duration), 0); ?>
                    </div>
                    <div class="time-info">Time left</div>
                    <div class="estimated-time">Estimated time: <?php echo $duration; ?>min</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Bookmark functionality
        document.querySelectorAll('.bookmark-icon').forEach(icon => {
            icon.addEventListener('click', function() {
                this.textContent = this.textContent === '☆' ? '★' : '☆';
            });
        });

        // Timer functionality
        function startTimer(duration, display) {
            if (!duration) {
                display.textContent = "No time limit";
                return;
            }
            
            let timer = duration * 60;
            let minutes, seconds;
            
            const countdown = setInterval(function () {
                minutes = parseInt(timer / 60, 10);
                seconds = parseInt(timer % 60, 10);

                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;

                display.textContent = minutes + ":" + seconds;

                if (--timer < 0) {
                    clearInterval(countdown);
                    alert("Time's up!");
                    // You can add auto-submit functionality here
                }
            }, 1000);
        }

        // Initialize timer when page loads
        window.onload = function () {
            const duration = <?php echo $duration; ?>;
            const display = document.querySelector('#time');
            startTimer(duration, display);
        };

        // Initialize code editors when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            // You can use a code editor library like CodeMirror or Ace
            // This is a simple example using textarea
            const editors = document.querySelectorAll('.code-editor');
            editors.forEach(editor => {
                // Add any editor initialization here
            });
        });

        // Function to run the code
        function runCode(questionId) {
            const code = document.getElementById(`code-editor-${questionId}`).value;
            const outputDiv = document.getElementById(`code-output-${questionId}`);
            
            // Show the output div
            outputDiv.style.display = 'block';
            outputDiv.innerHTML = 'Running code...';

            // Send code to server for execution
            fetch('run_code.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    question_id: questionId,
                    code: code
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    outputDiv.innerHTML = `
                        <strong>Output:</strong><br>
                        <pre>${data.output}</pre>
                        ${data.passed ? '<div class="success">All test cases passed!</div>' : '<div class="error">Some test cases failed.</div>'}
                    `;
                } else {
                    outputDiv.innerHTML = `<div class="error">Error: ${data.error}</div>`;
                }
            })
            .catch(error => {
                outputDiv.innerHTML = `<div class="error">Error running code: ${error.message}</div>`;
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Handle radio button changes
            document.querySelectorAll('input[type="radio"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    const questionBox = this.closest('.question-box');
                    const questionNumber = questionBox.querySelector('.question-number').textContent;
                    const statusNumber = document.querySelector(`.status-number[data-question="${questionNumber}"]`);
                    const bookmarkIcon = questionBox.querySelector('.bookmark-icon');
                    
                    if (statusNumber) {
                        statusNumber.classList.add('answered');
                        
                        // If it's marked for review, maintain both states
                        if (bookmarkIcon.textContent === '★') {
                            statusNumber.classList.add('marked');
                        }
                    }
                });
            });

            // Handle programming input changes
            document.querySelectorAll('.code-editor').forEach(editor => {
                editor.addEventListener('input', function() {
                    const questionBox = this.closest('.question-box');
                    const questionNumber = questionBox.querySelector('.question-number').textContent;
                    const statusNumber = document.querySelector(`.status-number[data-question="${questionNumber}"]`);
                    const bookmarkIcon = questionBox.querySelector('.bookmark-icon');
                    
                    if (this.value.trim() !== '') {
                        statusNumber.classList.add('answered');
                        if (bookmarkIcon.textContent === '★') {
                            statusNumber.classList.add('marked');
                        }
                    } else {
                        statusNumber.classList.remove('answered');
                        if (bookmarkIcon.textContent === '★') {
                            statusNumber.classList.add('marked');
                        } else {
                            statusNumber.classList.remove('marked');
                        }
                    }
                });
            });

            // Handle bookmark clicks
            document.querySelectorAll('.bookmark-icon').forEach(icon => {
                icon.addEventListener('click', function() {
                    const questionBox = this.closest('.question-box');
                    const questionNumber = questionBox.querySelector('.question-number').textContent;
                    this.textContent = this.textContent === '☆' ? '★' : '☆';
                    
                    const statusNumber = document.querySelector(`.status-number[data-question="${questionNumber}"]`);
                    if (this.textContent === '★') {
                        statusNumber.classList.toggle('marked');
                        this.classList.add('marked');
                    } else {
                        statusNumber.classList.remove('marked');
                        this.classList.remove('marked');
                    }
                });
            });

            // Function to update question status
            function updateQuestionStatus(questionNumber, status) {
                const statusNumber = document.querySelector(`.status-number[data-question="${questionNumber}"]`);
                
                if (status === 'answered') {
                    statusNumber.classList.add('answered');
                } else if (status === 'not-answered') {
                    statusNumber.classList.remove('answered');
                }
            }

            // Click on status number to navigate to question
            document.querySelectorAll('.status-number').forEach(number => {
                number.addEventListener('click', function() {
                    const questionNumber = this.dataset.question;
                    const questionElement = document.querySelector(`.question-number:contains('${questionNumber}')`).closest('.question-box');
                    questionElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                });
            });
        });

        // Helper function for jQuery-like contains selector
        Element.prototype.contains = function(text) {
            return this.textContent.trim() === text.trim();
        };

        // Add this new function for bookmark toggling
        function toggleBookmark(button, questionNumber) {
            const icon = button.querySelector('.bookmark-icon');
            const isMarked = icon.textContent === '★';
            
            // Toggle the star icon
            icon.textContent = isMarked ? '☆' : '★';
            icon.classList.toggle('marked');
            
            if (statusNumber) {
                // Toggle marked class
                statusNumber.classList.toggle('marked');
                
                // Check if question is answered
                const isAnswered = statusNumber.classList.contains('answered');
                
                // Update colors based on current state
                if (isAnswered) {
                    if (!isMarked) { // Marking for review
                        statusNumber.classList.remove('answered');
                        statusNumber.classList.add('answered', 'marked');
                    } else { // Unmarking from review
                        statusNumber.classList.remove('marked');
                        statusNumber.classList.add('answered');
                    }
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Existing event listeners...

            // Update the status update function to handle both states
            function updateQuestionStatus(questionNumber, status) {
                const statusNumber = document.querySelector(`.status-number[data-question="${questionNumber}"]`);
                if (!statusNumber) return;
                
                if (status === 'answered') {
                    statusNumber.classList.add('answered');
                    // If it's marked, add the combined state
                    if (statusNumber.classList.contains('marked')) {
                        statusNumber.classList.add('marked-answered');
                    }
                } else if (status === 'not-answered') {
                    statusNumber.classList.remove('answered', 'marked-answered');
                }
            }
        });
    </script>
</body>
</html> 