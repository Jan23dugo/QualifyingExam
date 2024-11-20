<?php
session_start();
include_once 'config/config.php';

// IP Address restriction
$allowed_ips = ['your.lab.ip.address', '127.0.0.1', 'other.lab.ip.addresses']; // Add your lab IPs
$client_ip = $_SERVER['REMOTE_ADDR'];

if (!in_array($client_ip, $allowed_ips)) {
    die("Access denied. This exam can only be taken from designated computer laboratories.");
}

// Handle reference ID submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reference_id'])) {
    $reference_id = $_POST['reference_id'];
    
    // Fetch student details using reference_id
    $stmt = $conn->prepare("
        SELECT 
            student_id, 
            CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) as full_name,
            first_name,
            last_name 
        FROM students 
        WHERE reference_id = ?
    ");
    $stmt->bind_param("s", $reference_id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
    
    if ($student) {
        $_SESSION['student_id'] = $student['student_id'];
        $_SESSION['student_name'] = $student['full_name'];
        $_SESSION['reference_id'] = $reference_id;
    } else {
        $error_message = "Invalid reference ID. Please try again.";
    }
}

// If student is identified, show their assigned exams
if (isset($_SESSION['student_id'])) {
    $student_id = $_SESSION['student_id'];
    
    if (isset($_GET['exam_id'])) {
        // Show specific exam
        $exam_id = $_GET['exam_id'];
        
        // Check if exam is assigned to student
        $check_assignment = $conn->prepare("
            SELECT 
                ea.*,
                e.exam_name,
                e.duration,
                COALESCE(er.status, 'Pending') as status,
                er.start_time,
                er.result_id
            FROM exam_assignments ea 
            JOIN exams e ON ea.exam_id = e.exam_id
            LEFT JOIN exam_results er ON ea.exam_id = er.exam_id AND er.student_id = ea.student_id
            WHERE ea.exam_id = ? AND ea.student_id = ?
        ");
        $check_assignment->bind_param("ii", $exam_id, $student_id);
        $check_assignment->execute();
        $assignment = $check_assignment->get_result()->fetch_assoc();
        
        if (!$assignment) {
            die("You are not assigned to this exam.");
        }
        
        // Rest of your existing exam display code...
        // (Keep all the exam sections and questions fetching code)
        
    } else {
        // Show list of assigned exams
        $exams_query = "
            SELECT e.exam_id, e.exam_name, e.duration, er.status
            FROM exam_assignments ea
            JOIN exams e ON ea.exam_id = e.exam_id
            LEFT JOIN exam_results er ON ea.exam_id = er.exam_id AND er.student_id = ea.student_id
            WHERE ea.student_id = ?
            ORDER BY e.exam_name
        ";
        $stmt = $conn->prepare($exams_query);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $assigned_exams = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

// Handle exam start
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start_exam'])) {
    $conn->begin_transaction();
    
    try {
        // First check if there's already a result entry
        $check_result = $conn->prepare("SELECT result_id FROM exam_results WHERE exam_id = ? AND student_id = ?");
        $check_result->bind_param("ii", $exam_id, $student_id);
        $check_result->execute();
        $existing_result = $check_result->get_result()->fetch_assoc();
        
        if ($existing_result) {
            // Update existing result
            $update_stmt = $conn->prepare("
                UPDATE exam_results 
                SET status = 'In Progress', 
                    start_time = CURRENT_TIMESTAMP 
                WHERE result_id = ?
            ");
            $update_stmt->bind_param("i", $existing_result['result_id']);
            $update_stmt->execute();
        } else {
            // Create new result entry
            $insert_stmt = $conn->prepare("
                INSERT INTO exam_results (exam_id, student_id, status, start_time, total_points) 
                SELECT ?, ?, 'In Progress', CURRENT_TIMESTAMP, 
                    (SELECT COALESCE(SUM(points), 0) FROM questions WHERE exam_id = ?)
            ");
            $insert_stmt->bind_param("iii", $exam_id, $student_id, $exam_id);
            $insert_stmt->execute();
        }
        
        $conn->commit();
        // Reload page to show exam
        header("Location: take_exam.php?exam_id=" . $exam_id);
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        die("Error starting exam: " . $e->getMessage());
    }
}

// Create exam_results table if it doesn't exist
$create_results_table = "
CREATE TABLE IF NOT EXISTS exam_results (
    result_id INT PRIMARY KEY AUTO_INCREMENT,
    exam_id INT NOT NULL,
    student_id INT NOT NULL,
    score INT DEFAULT 0,
    total_points INT NOT NULL,
    start_time TIMESTAMP NULL,
    end_time TIMESTAMP NULL,
    completion_time TIME,
    status ENUM('Pending', 'In Progress', 'Completed', 'Failed') DEFAULT 'Pending',
    submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (exam_id) REFERENCES exams(exam_id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
)";
$conn->query($create_results_table);

// Create student_answers table if it doesn't exist
$create_answers_table = "
CREATE TABLE IF NOT EXISTS student_answers (
    answer_id INT PRIMARY KEY AUTO_INCREMENT,
    result_id INT NOT NULL,
    question_id INT NOT NULL,
    student_answer TEXT,
    is_correct BOOLEAN DEFAULT FALSE,
    points_earned INT DEFAULT 0,
    submission_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (result_id) REFERENCES exam_results(result_id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(question_id) ON DELETE CASCADE
)";
$conn->query($create_answers_table);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Exam</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/styles.min.css">
    <script src="https://cdn.jsdelivr.net/npm/monaco-editor@0.33.0/min/vs/loader.js"></script>
    <style>
        .reference-form {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .exam-list {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .exam-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .exam-container {
    max-width: 900px;
    margin: 20px auto;
    padding: 20px;
}

.section {
    background: white;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.question {
    background: #f8f9fa;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 15px;
}

.timer {
    position: fixed;
    top: 20px;
    right: 20px;
    background: #6200ea;
    color: white;
    padding: 10px 20px;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    z-index: 1000;
}

.programming-editor {
    font-family: monospace;
    width: 100%;
    min-height: 200px;
    margin-top: 10px;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.test-case {
    background: white;
    padding: 10px;
    margin: 5px 0;
    border-radius: 4px;
    border: 1px solid #dee2e6;
}
        /* Keep your existing styles */
        .programming-question {
            margin-bottom: 2rem;
        }
        
        .code-editor-container {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 1rem;
            background: #f8f9fa;
        }
        
        .code-editor {
            height: 400px;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .code-output {
            background: #1e1e1e;
            color: #fff;
            padding: 1rem;
            border-radius: 4px;
            max-height: 200px;
            overflow-y: auto;
        }
        
        .code-output .error {
            color: #ff6b6b;
        }
    </style>
</head>
<body>
    <?php if (!isset($_SESSION['student_id'])): ?>
        <!-- Reference ID Form -->
        <div class="reference-form">
            <h2 class="text-center mb-4">Enter Your Reference ID</h2>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <input type="text" class="form-control" name="reference_id" 
                           placeholder="Enter your reference ID" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 mt-3">Submit</button>
            </form>
        </div>
        
    <?php elseif (!isset($_GET['exam_id'])): ?>
        <!-- List of Assigned Exams -->
        <div class="exam-list">
            <h2 class="mb-4">Welcome, <?php echo isset($_SESSION['student_name']) ? htmlspecialchars($_SESSION['student_name']) : 'Student'; ?></h2>
            <p class="text-muted">Reference ID: <?php echo htmlspecialchars($_SESSION['reference_id']); ?></p>
            
            <h3 class="mb-3">Your Assigned Exams</h3>
            
            <?php if (empty($assigned_exams)): ?>
                <div class="alert alert-info">No exams are currently assigned to you.</div>
            <?php else: ?>
                <?php foreach ($assigned_exams as $exam): ?>
                    <div class="exam-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4><?php echo htmlspecialchars($exam['exam_name']); ?></h4>
                                <p class="text-muted mb-0">Duration: <?php echo $exam['duration']; ?> minutes</p>
                                <p class="mb-0">Status: <?php echo $exam['status'] ?? 'Not started'; ?></p>
                            </div>
                            <?php if (!$exam['status'] || $exam['status'] === 'Pending'): ?>
                                <a href="?exam_id=<?php echo $exam['exam_id']; ?>" 
                                   class="btn btn-primary">Take Exam</a>
                            <?php elseif ($exam['status'] === 'In Progress'): ?>
                                <a href="?exam_id=<?php echo $exam['exam_id']; ?>" 
                                   class="btn btn-warning">Continue Exam</a>
                            <?php else: ?>
                                <button class="btn btn-secondary" disabled>Completed</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <form method="POST" action="logout.php" class="mt-4">
                <button type="submit" class="btn btn-secondary">Exit</button>
            </form>
        </div>
        
    <?php else: ?>
        <div class="exam-container">
            <h1 class="text-center mb-4"><?php echo htmlspecialchars($assignment['exam_name']); ?></h1>
            
            <?php if ($assignment['status'] === 'Pending'): ?>
                <div class="section">
                    <h3>Exam Instructions</h3>
                    <p>Duration: <?php echo $assignment['duration']; ?> minutes</p>
                    <p>Please read all questions carefully before answering.</p>
                    <form method="POST">
                        <button type="submit" name="start_exam" class="btn btn-primary">Start Exam</button>
                    </form>
                </div>
                
            <?php elseif ($assignment['status'] === 'In Progress'): ?>
                <div class="timer" id="examTimer"></div>
                
                <?php
                // Fetch exam sections and questions
                $sections_query = "
                    SELECT 
                        es.section_id,
                        es.section_title,
                        es.section_description,
                        q.question_id,
                        q.question_text,
                        q.question_type,
                        q.points
                    FROM exam_sections es
                    LEFT JOIN questions q ON es.section_id = q.section_id
                    WHERE es.exam_id = ?
                    ORDER BY es.section_order, q.question_order
                ";
                
                $stmt = $conn->prepare($sections_query);
                $stmt->bind_param("i", $exam_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                $sections = [];
                while ($row = $result->fetch_assoc()) {
                    $section_id = $row['section_id'];
                    if (!isset($sections[$section_id])) {
                        $sections[$section_id] = [
                            'title' => $row['section_title'],
                            'description' => $row['section_description'],
                            'questions' => []
                        ];
                    }
                    
                    if ($row['question_id']) {
                        $question = [
                            'id' => $row['question_id'],
                            'text' => $row['question_text'],
                            'type' => $row['question_type'],
                            'points' => $row['points']
                        ];
                        
                        // Fetch options for multiple choice questions
                        if ($row['question_type'] === 'multiple_choice') {
                            $opt_stmt = $conn->prepare("SELECT * FROM multiple_choice_options WHERE question_id = ? ORDER BY option_order");
                            $opt_stmt->bind_param("i", $row['question_id']);
                            $opt_stmt->execute();
                            $question['options'] = $opt_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                        }
                        
                        // Fetch test cases for programming questions
                        if ($row['question_type'] === 'programming') {
                            $test_stmt = $conn->prepare("SELECT * FROM test_cases WHERE question_id = ? ORDER BY test_case_order");
                            $test_stmt->bind_param("i", $row['question_id']);
                            $test_stmt->execute();
                            $question['test_cases'] = $test_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                            
                            // Fetch programming language
                            $lang_stmt = $conn->prepare("SELECT language_name FROM programming_languages WHERE question_id = ?");
                            $lang_stmt->bind_param("i", $row['question_id']);
                            $lang_stmt->execute();
                            $lang_result = $lang_stmt->get_result()->fetch_assoc();
                            $question['programming_language'] = $lang_result['language_name'] ?? null;
                        }
                        
                        $sections[$section_id]['questions'][] = $question;
                    }
                }
                ?>
                
                <form id="examForm" method="POST" action="submit_exam.php">
                    <input type="hidden" name="exam_id" value="<?php echo $exam_id; ?>">
                    
                    <?php foreach ($sections as $section_id => $section): ?>
                        <div class="section">
                            <h3><?php echo htmlspecialchars($section['title']); ?></h3>
                            <?php if ($section['description']): ?>
                                <p class="text-muted"><?php echo htmlspecialchars($section['description']); ?></p>
                            <?php endif; ?>
                            
                            <?php foreach ($section['questions'] as $index => $question): ?>
                                <div class="question">
                                    <div class="d-flex justify-content-between">
                                        <div>Question <?php echo $index + 1; ?></div>
                                        <div class="text-muted"><?php echo $question['points']; ?> points</div>
                                    </div>
                                    <div class="mt-2 mb-3"><?php echo htmlspecialchars($question['text']); ?></div>
                                    
                                    <?php if ($question['type'] === 'multiple_choice'): ?>
                                        <?php foreach ($question['options'] as $option): ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" 
                                                       name="answers[<?php echo $question['id']; ?>]" 
                                                       value="<?php echo $option['option_id']; ?>"
                                                       id="opt_<?php echo $option['option_id']; ?>">
                                                <label class="form-check-label" for="opt_<?php echo $option['option_id']; ?>">
                                                    <?php echo htmlspecialchars($option['option_text']); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                        
                                    <?php elseif ($question['type'] === 'programming'): ?>
                                        <div class="programming-question">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="question-content">
                                                        <div class="mt-2 mb-3"><?php echo htmlspecialchars($question['text']); ?></div>
                                                        
                                                        <?php if (!empty($question['test_cases'])): ?>
                                                            <h5 class="mt-3">Test Cases:</h5>
                                                            <?php foreach ($question['test_cases'] as $test_case): ?>
                                                                <div class="test-case">
                                                                    <div><strong>Input:</strong> <?php echo htmlspecialchars($test_case['input_data']); ?></div>
                                                                    <div><strong>Expected Output:</strong> <?php echo htmlspecialchars($test_case['expected_output']); ?></div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="code-editor-container">
                                                        <div class="language-selector mb-2">
                                                            <label>Language:</label>
                                                            <select class="form-control" id="language-select-<?php echo $question['id']; ?>">
                                                                <option value="java">Java</option>
                                                                <option value="python">Python</option>
                                                                <option value="c">C</option>
                                                            </select>
                                                        </div>
                                                        <div id="code-editor-<?php echo $question['id']; ?>" class="code-editor"></div>
                                                        <div class="editor-actions mt-2">
                                                            <button type="button" class="btn btn-primary run-code" 
                                                                    data-question-id="<?php echo $question['id']; ?>">
                                                                Run Code
                                                            </button>
                                                        </div>
                                                        <div id="output-<?php echo $question['id']; ?>" class="code-output mt-2"></div>
                                                        <input type="hidden" name="answers[<?php echo $question['id']; ?>]" 
                                                               id="code-input-<?php echo $question['id']; ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="text-center mt-4 mb-5">
                        <button type="submit" class="btn btn-primary btn-lg">Submit Exam</button>
                    </div>
                </form>
                
                <script>
                    // Timer functionality
                    const startTime = new Date('<?php echo $assignment['start_time']; ?>').getTime();
                    const duration = <?php echo $assignment['duration']; ?> * 60 * 1000; // Convert minutes to milliseconds
                    const endTime = startTime + duration;
                    
                    function updateTimer() {
                        const now = new Date().getTime();
                        const timeLeft = endTime - now;
                        
                        if (timeLeft <= 0) {
                            document.getElementById('examTimer').innerHTML = "Time's Up!";
                            document.getElementById('examForm').submit();
                            return;
                        }
                        
                        const hours = Math.floor(timeLeft / (1000 * 60 * 60));
                        const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
                        const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);
                        
                        document.getElementById('examTimer').innerHTML = 
                            `Time Left: ${hours}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                    }
                    
                    setInterval(updateTimer, 1000);
                    updateTimer();
                </script>
                
            <?php else: ?>
                <div class="alert alert-info">
                    This exam has already been completed or is no longer available.
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script>
    class CodeEditor {
        constructor(language, containerId) {
            this.language = language;
            this.containerId = containerId;
            this.editor = null;
            this.init();
        }

        init() {
            require.config({ paths: { 'vs': 'https://cdn.jsdelivr.net/npm/monaco-editor@0.33.0/min/vs' }});
            require(['vs/editor/editor.main'], () => {
                this.editor = monaco.editor.create(document.getElementById(this.containerId), {
                    value: this.getDefaultCode(),
                    language: this.getMonacoLanguage(),
                    theme: 'vs-dark',
                    minimap: { enabled: false },
                    automaticLayout: true,
                    fontSize: 14,
                    scrollBeyondLastLine: false,
                    lineNumbers: true,
                    lineHeight: 21
                });

                // Update hidden input when code changes
                this.editor.onDidChangeModelContent(() => {
                    const inputId = this.containerId.replace('code-editor-', 'code-input-');
                    document.getElementById(inputId).value = this.editor.getValue();
                });
            });
        }

        getMonacoLanguage() {
            const languageMap = {
                'python': 'python',
                'java': 'java',
                'c': 'c'
            };
            return languageMap[this.language.toLowerCase()] || 'plaintext';
        }

        getDefaultCode() {
            const templates = {
                'java': 'public class Main {\n    public static void main(String[] args) {\n        // Write your code here\n        \n    }\n}',
                'python': '# Write your code here\n',
                'c': '#include <stdio.h>\n\nint main() {\n    // Write your code here\n    \n    return 0;\n}'
            };
            return templates[this.language.toLowerCase()] || '// Write your code here\n';
        }

        getCode() {
            return this.editor ? this.editor.getValue() : '';
        }

        setCode(code) {
            if (this.editor) {
                this.editor.setValue(code);
            }
        }
    }

    // Initialize code editors when the page loads
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize code editors for programming questions
        document.querySelectorAll('.programming-question').forEach(question => {
            const editorDiv = question.querySelector('.code-editor');
            const questionId = editorDiv.id.split('-')[2];
            const languageSelect = document.getElementById(`language-select-${questionId}`);
            
            const editor = new CodeEditor(languageSelect.value, `code-editor-${questionId}`);
            
            // Language change handler
            languageSelect.addEventListener('change', () => {
                editor.language = languageSelect.value;
                editor.init();
            });
            
            // Run code handler
            question.querySelector('.run-code').addEventListener('click', async () => {
                const code = editor.getCode();
                const language = languageSelect.value;
                const outputDiv = document.getElementById(`output-${questionId}`);
                const runButton = question.querySelector('.run-code');
                
                try {
                    runButton.disabled = true;
                    runButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Running...';
                    outputDiv.innerHTML = '<div class="text-muted">Executing code...</div>';
                    
                    const response = await fetch('api/execute_code.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ code, language })
                    });
                    
                    const result = await response.json();
                    
                    if (result.output) {
                        outputDiv.innerHTML = `
                            <div class="output-header">Output:</div>
                            <pre class="output-content">${result.output}</pre>
                            <div class="output-stats">
                                <span>Memory: ${result.memory} KB</span>
                                <span>CPU Time: ${result.cpuTime} sec</span>
                            </div>
                        `;
                    } else if (result.error) {
                        outputDiv.innerHTML = `<pre class="error">${result.error}</pre>`;
                    }
                } catch (error) {
                    outputDiv.innerHTML = `<pre class="error">Error: ${error.message}</pre>`;
                } finally {
                    runButton.disabled = false;
                    runButton.innerHTML = 'Run Code';
                }
            });
        });
    });
    </script>
</body>
</html>
