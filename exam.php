<?php
session_start();
require_once 'config/config.php';

// Check if user is authorized and exam has been started
if (!isset($_SESSION['student_id']) || !isset($_SESSION['exam_id']) || !isset($_POST['start_exam'])) {
    header('Location: take_exam.php');
    exit;
}

// Fetch exam questions
$stmt = $conn->prepare("SELECT q.question_id, q.question_text, q.question_type, 
                              q.points, q.question_order,
                              mco.option_id, mco.choice_text, mco.is_correct,
                              tc.test_case_id, tc.test_input, tc.expected_output, 
                              tc.is_hidden, tc.description
                       FROM questions q 
                       LEFT JOIN multiple_choice_options mco ON q.question_id = mco.question_id
                       LEFT JOIN test_cases tc ON q.question_id = tc.question_id
                       WHERE q.exam_id = ?
                       ORDER BY q.question_order ASC");

if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}

$stmt->bind_param("i", $_SESSION['exam_id']);

if (!$stmt->execute()) {
    die("Query execution failed: " . $stmt->error);
}

$result = $stmt->get_result();
$questions = [];

// Process the results to group options and test cases with their questions
while ($row = $result->fetch_assoc()) {
    $question_id = $row['question_id'];
    
    if (!isset($questions[$question_id])) {
        $questions[$question_id] = [
            'question_id' => $row['question_id'],
            'question_text' => $row['question_text'],
            'question_type' => $row['question_type'],
            'points' => $row['points'],
            'question_order' => $row['question_order'],
            'options' => [],
            'test_cases' => []
        ];
    }
    
    // Add multiple choice options if they exist
    if ($row['option_id']) {
        $questions[$question_id]['options'][] = [
            'option_id' => $row['option_id'],
            'choice_text' => $row['choice_text'],
            'is_correct' => $row['is_correct']
        ];
    }
    
    // Add test cases if they exist and aren't hidden
    if ($row['test_case_id'] && !$row['is_hidden']) {
        $questions[$question_id]['test_cases'][] = [
            'test_case_id' => $row['test_case_id'],
            'test_input' => $row['test_data'],
            'expected_output' => $row['expected_output'],
            'description' => $row['description']
        ];
    }
}

// Convert to indexed array
$questions = array_values($questions);

// If no questions found
if (empty($questions)) {
    $_SESSION['error'] = "No questions found for this exam.";
    header('Location: start_exam.php');
    exit;
}

// Fetch exam duration
$stmt = $conn->prepare("SELECT duration FROM exams WHERE exam_id = ?");
$stmt->bind_param("i", $_SESSION['exam_id']);
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();

if (!$exam) {
    $_SESSION['error'] = "Exam details not found.";
    header('Location: take_exam.php');
    exit;
}

// Store exam start time in session if not already set
if (!isset($_SESSION['exam_start_time'])) {
    $_SESSION['exam_start_time'] = time();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam in Progress</title>
    <link rel="stylesheet" href="assets/css/exam.css">
</head>
<body>
    <div class="exam-container">
        <div class="questions-container">
            <!-- Exam header -->
            <div class="exam-header">
                <h1><?php echo htmlspecialchars($_SESSION['exam_name']); ?></h1>
                <div class="exam-timer">Time Remaining: <span id="timeLeft">Loading...</span></div>
            </div>

            <!-- Questions content -->
            <div class="questions-content">
                <?php 
                $questionsPerPage = 20;
                $totalQuestions = count($questions);
                $totalPages = ceil($totalQuestions / $questionsPerPage);
                ?>
                
                <div class="pagination-info">
                    Page <span id="currentPage">1</span> of <?php echo $totalPages; ?>
                    (Questions <?php echo $totalQuestions; ?>)
                </div>

                <div class="questions-wrapper">
                    <?php foreach ($questions as $index => $question): 
                        $pageNumber = floor($index / $questionsPerPage) + 1;
                    ?>
                        <div class="question-card" 
                             id="question-<?php echo $index + 1; ?>"
                             data-page="<?php echo $pageNumber; ?>"
                             style="display: <?php echo $pageNumber === 1 ? 'block' : 'none'; ?>">
                            <div class="question-header">
                                <div class="question-info">
                                    <span class="question-number">Question <?php echo $index + 1; ?></span>
                                    <span class="question-points"><?php echo $question['points']; ?> points</span>
                                </div>
                                <button type="button" 
                                        class="review-btn"
                                        data-question="<?php echo $index + 1; ?>"
                                        onclick="toggleReview(<?php echo $index + 1; ?>)">
                                    Mark for Review
                                </button>
                            </div>
                            
                            <div class="question-content">
                                <?php echo nl2br(htmlspecialchars($question['question_text'])); ?>
                            </div>

                            <?php if ($question['question_type'] === 'programming'): ?>
                                <div class="coding-area">
                                    <textarea name="answer[<?php echo $question['question_id']; ?>]" 
                                              class="code-editor" 
                                              rows="10"
                                              placeholder="Write your code here..."></textarea>
                                    
                                    <?php if (!empty($question['test_cases'])): ?>
                                        <div class="test-cases">
                                            <h4>Sample Test Cases:</h4>
                                            <?php foreach ($question['test_cases'] as $test_case): ?>
                                                <div class="test-case">
                                                    <p>Input: <?php echo htmlspecialchars($test_case['test_input']); ?></p>
                                                    <p>Expected Output: <?php echo htmlspecialchars($test_case['expected_output']); ?></p>
                                                    <?php if ($test_case['description']): ?>
                                                        <p>Explanation: <?php echo htmlspecialchars($test_case['description']); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php elseif ($question['question_type'] === 'multiple_choice'): ?>
                                <div class="options">
                                    <?php foreach ($question['options'] as $option): ?>
                                        <div class="option">
                                            <input type="radio" 
                                                   name="answer[<?php echo $question['question_id']; ?>]" 
                                                   value="<?php echo $option['option_id']; ?>"
                                                   id="option-<?php echo $option['option_id']; ?>">
                                            <label for="option-<?php echo $option['option_id']; ?>">
                                                <?php echo htmlspecialchars($option['choice_text']); ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <textarea name="answer[<?php echo $question['question_id']; ?>]" 
                                          class="answer-text" 
                                          rows="4"
                                          placeholder="Type your answer here..."></textarea>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($totalPages > 1): ?>
                    <div class="pagination-controls">
                        <button type="button" class="page-btn" id="prevPage" disabled>Previous Page</button>
                        <div class="page-numbers">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <button type="button" 
                                        class="page-number <?php echo $i === 1 ? 'active' : ''; ?>"
                                        data-page="<?php echo $i; ?>">
                                    <?php echo $i; ?>
                                </button>
                            <?php endfor; ?>
                        </div>
                        <button type="button" class="page-btn" id="nextPage">Next Page</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Question Status (now properly positioned) -->
        <div class="exam-status">
            <div class="status-header">
                <h3>Questions status</h3>
            </div>
            
            <div class="status-grid">
                <?php foreach ($questions as $index => $question): ?>
                    <div class="status-item" data-question="<?php echo $index + 1; ?>">
                        <?php echo $index + 1; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="page-info">
                Page <?php echo isset($currentPage) ? $currentPage : 1; ?> of <?php echo $totalPages; ?>
            </div>
            
            <div class="status-legend">
                <div class="legend-item">
                    <span class="status-dot answered"></span>
                    <span>Answered</span>
                </div>
                <div class="legend-item">
                    <span class="status-dot review"></span>
                    <span>Marked for review</span>
                </div>
                <div class="legend-item">
                    <span class="status-dot unanswered"></span>
                    <span>Not answered</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize exam duration from PHP
        const examDuration = <?php echo json_encode($exam['duration']); ?>;
        const startTime = <?php echo $_SESSION['exam_start_time']; ?>;
        const questionsPerPage = <?php echo $questionsPerPage; ?>;
        let currentPage = 1;
        
        // Timer functionality
        function updateTimer() {
            const timerElement = document.getElementById('timeLeft');
            if (!timerElement) return;

            const now = Math.floor(Date.now() / 1000);
            const timeElapsed = now - startTime;
            const timeLeft = (examDuration * 60) - timeElapsed;
            
            if (timeLeft <= 0) {
                document.getElementById('examForm')?.submit();
                return;
            }
            
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        }

        // Update timer every second
        setInterval(updateTimer, 1000);
        updateTimer();

        // Pagination functionality
        function showPage(pageNumber) {
            const currentPageElement = document.getElementById('currentPage');
            const prevPageButton = document.getElementById('prevPage');
            const nextPageButton = document.getElementById('nextPage');
            const questionCards = document.querySelectorAll('.question-card');
            const pageButtons = document.querySelectorAll('.page-number');
            const questionsWrapper = document.querySelector('.questions-wrapper');

            if (!currentPageElement || !questionCards.length) return;

            currentPage = pageNumber;
            currentPageElement.textContent = pageNumber;
            
            // Update buttons state
            if (prevPageButton) {
                prevPageButton.disabled = pageNumber === 1;
            }
            if (nextPageButton) {
                nextPageButton.disabled = pageNumber === <?php echo $totalPages; ?>;
            }
            
            // Update page numbers
            pageButtons.forEach(btn => {
                btn.classList.toggle('active', parseInt(btn.dataset.page) === pageNumber);
            });
            
            // Show questions for current page
            questionCards.forEach(card => {
                card.style.display = parseInt(card.dataset.page) === pageNumber ? 'block' : 'none';
            });
            
            // Scroll to top of questions wrapper
            if (questionsWrapper) {
                questionsWrapper.scrollTop = 0;
            }
        }

        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Event Listeners
            const prevPageButton = document.getElementById('prevPage');
            const nextPageButton = document.getElementById('nextPage');
            const pageButtons = document.querySelectorAll('.page-number');

            if (prevPageButton) {
                prevPageButton.addEventListener('click', () => {
                    if (currentPage > 1) showPage(currentPage - 1);
                });
            }

            if (nextPageButton) {
                nextPageButton.addEventListener('click', () => {
                    if (currentPage < <?php echo $totalPages; ?>) showPage(currentPage + 1);
                });
            }

            pageButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    showPage(parseInt(btn.dataset.page));
                });
            });

            // Initialize first page
            showPage(1);
        });

        // Confirmation before submitting
        function confirmSubmit() {
            return confirm('Are you sure you want to submit your exam? This action cannot be undone.');
        }

        // Initialize status tracking
        const questionStatus = new Map();

        // Initialize all questions as unanswered
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize status for all questions
            document.querySelectorAll('.status-item').forEach(item => {
                const questionNumber = item.getAttribute('data-question');
                questionStatus.set(questionNumber, {
                    answered: false,
                    review: false
                });
                updateStatusItemDisplay(questionNumber);
            });

            // Handle radio button changes (for multiple choice)
            document.querySelectorAll('input[type="radio"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    const questionCard = this.closest('.question-card');
                    if (questionCard) {
                        const questionNumber = questionCard.id.replace('question-', '');
                        updateQuestionStatus(questionNumber, true);
                    }
                });
            });

            // Handle textarea changes (for text/programming questions)
            document.querySelectorAll('textarea').forEach(textarea => {
                textarea.addEventListener('input', debounce(function() {
                    const questionCard = this.closest('.question-card');
                    if (questionCard) {
                        const questionNumber = questionCard.id.replace('question-', '');
                        updateQuestionStatus(questionNumber, this.value.trim() !== '');
                    }
                }, 500));
            });

            // Handle "Mark for Review" button clicks
            document.querySelectorAll('.review-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const questionNumber = this.getAttribute('data-question');
                    toggleReviewStatus(questionNumber);
                });
            });
        });

        // Debounce function to prevent too many updates
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func.apply(this, args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Update question status
        function updateQuestionStatus(questionNumber, isAnswered) {
            const status = questionStatus.get(questionNumber);
            if (status) {
                status.answered = isAnswered;
                updateStatusItemDisplay(questionNumber);
            }
        }

        // Toggle review status
        function toggleReviewStatus(questionNumber) {
            const status = questionStatus.get(questionNumber);
            const reviewBtn = document.querySelector(`.review-btn[data-question="${questionNumber}"]`);
            
            if (status) {
                status.review = !status.review;
                updateStatusItemDisplay(questionNumber);
                
                // Update review button
                if (reviewBtn) {
                    reviewBtn.classList.toggle('marked', status.review);
                    reviewBtn.textContent = status.review ? 'Remove Review Mark' : 'Mark for Review';
                }
            }
        }

        // Update the visual display of a status item
        function updateStatusItemDisplay(questionNumber) {
            const statusItem = document.querySelector(`.status-item[data-question="${questionNumber}"]`);
            const status = questionStatus.get(questionNumber);
            
            if (statusItem && status) {
                // Remove all possible status classes
                statusItem.classList.remove('unanswered', 'answered', 'review');
                
                // Add the appropriate class based on current status
                if (status.review) {
                    statusItem.classList.add('review');
                } else if (status.answered) {
                    statusItem.classList.add('answered');
                } else {
                    statusItem.classList.add('unanswered');
                }

                // Add visual indicator class
                statusItem.classList.add('status-indicator');
            }
        }

        // Add click navigation to status items
        document.querySelectorAll('.status-item').forEach(item => {
            item.addEventListener('click', () => {
                const questionNumber = parseInt(item.getAttribute('data-question'));
                const pageNumber = Math.ceil(questionNumber / questionsPerPage);
                showPage(pageNumber);
                
                // Scroll to the specific question after a short delay
                setTimeout(() => {
                    const questionCard = document.getElementById(`question-${questionNumber}`);
                    if (questionCard) {
                        questionCard.scrollIntoView({ behavior: 'smooth' });
                    }
                }, 100);
            });
        });
    </script>
</body>
</html> 