<?php 
session_start(); 

// Sample questions array (you would typically get this from a database)
$all_questions = [
    // Page 1 (1-30)
    1 => [
        'question' => 'Radiocarbon is produced in the atmosphere as a result of',
        'type' => 'multiple',
        'options' => [
            'collision between fast neutrons and nitrogen nuclei present in the atmosphere',
            'action of ultraviolet light from the sun on atmospheric oxygen',
            'lightning discharge in atmosphere'
        ]
    ],
    2 => [
        'question' => 'The absorption of ink by blotting paper involves',
        'type' => 'multiple',
        'options' => [
            'viscosity of ink',
            'capillary action phenomenon',
            'diffusion of ink through the blotting'
        ]
    ],
    2 => [
        'question' => 'The absorption of ink by blotting paper involves',
        'type' => 'multiple',
        'options' => [
            'viscosity of ink',
            'capillary action phenomenon',
            'diffusion of ink through the blotting'
        ]
    ],
    2 => [
        'question' => 'The absorption of ink by blotting paper involves',
        'type' => 'multiple',
        'options' => [
            'viscosity of ink',
            'capillary action phenomenon',
            'diffusion of ink through the blotting'
        ]
    ],
    2 => [
        'question' => 'The absorption of ink by blotting paper involves',
        'type' => 'multiple',
        'options' => [
            'viscosity of ink',
            'capillary action phenomenon',
            'diffusion of ink through the blotting'
        ]
    ],
    2 => [
        'question' => 'The absorption of ink by blotting paper involves',
        'type' => 'multiple',
        'options' => [
            'viscosity of ink',
            'capillary action phenomenon',
            'diffusion of ink through the blotting'
        ]
    ],
    2 => [
        'question' => 'The absorption of ink by blotting paper involves',
        'type' => 'multiple',
        'options' => [
            'viscosity of ink',
            'capillary action phenomenon',
            'diffusion of ink through the blotting'
        ]
    ],
    2 => [
        'question' => 'The absorption of ink by blotting paper involves',
        'type' => 'multiple',
        'options' => [
            'viscosity of ink',
            'capillary action phenomenon',
            'diffusion of ink through the blotting'
        ]
    ],
    2 => [
        'question' => 'The absorption of ink by blotting paper involves',
        'type' => 'multiple',
        'options' => [
            'viscosity of ink',
            'capillary action phenomenon',
            'diffusion of ink through the blotting'
        ]
    ],
    2 => [
        'question' => 'The absorption of ink by blotting paper involves',
        'type' => 'multiple',
        'options' => [
            'viscosity of ink',
            'capillary action phenomenon',
            'diffusion of ink through the blotting'
        ]
    ],
    2 => [
        'question' => 'The absorption of ink by blotting paper involves',
        'type' => 'multiple',
        'options' => [
            'viscosity of ink',
            'capillary action phenomenon',
            'diffusion of ink through the blotting'
        ]
    ],
    2 => [
        'question' => 'The absorption of ink by blotting paper involves',
        'type' => 'multiple',
        'options' => [
            'viscosity of ink',
            'capillary action phenomenon',
            'diffusion of ink through the blotting'
        ]
    ],
    2 => [
        'question' => 'The absorption of ink by blotting paper involves',
        'type' => 'multiple',
        'options' => [
            'viscosity of ink',
            'capillary action phenomenon',
            'diffusion of ink through the blotting'
        ]
    ],
    2 => [
        'question' => 'The absorption of ink by blotting paper involves',
        'type' => 'multiple',
        'options' => [
            'viscosity of ink',
            'capillary action phenomenon',
            'diffusion of ink through the blotting'
        ]
    ],
    2 => [
        'question' => 'The absorption of ink by blotting paper involves',
        'type' => 'multiple',
        'options' => [
            'viscosity of ink',
            'capillary action phenomenon',
            'diffusion of ink through the blotting'
        ]
    ],
    2 => [
        'question' => 'The absorption of ink by blotting paper involves',
        'type' => 'multiple',
        'options' => [
            'viscosity of ink',
            'capillary action phenomenon',
            'diffusion of ink through the blotting'
        ]
    ],
    2 => [
        'question' => 'The absorption of ink by blotting paper involves',
        'type' => 'multiple',
        'options' => [
            'viscosity of ink',
            'capillary action phenomenon',
            'diffusion of ink through the blotting'
        ]
    ],
    2 => [
        'question' => 'The absorption of ink by blotting paper involves',
        'type' => 'multiple',
        'options' => [
            'viscosity of ink',
            'capillary action phenomenon',
            'diffusion of ink through the blotting'
        ]
    ],
    2 => [
        'question' => 'The absorption of ink by blotting paper involves',
        'type' => 'multiple',
        'options' => [
            'viscosity of ink',
            'capillary action phenomenon',
            'diffusion of ink through the blotting'
        ]
    ],
    2 => [
        'question' => 'The absorption of ink by blotting paper involves',
        'type' => 'multiple',
        'options' => [
            'viscosity of ink',
            'capillary action phenomenon',
            'diffusion of ink through the blotting'
        ]
    ],
    2 => [
        'question' => 'The absorption of ink by blotting paper involves',
        'type' => 'multiple',
        'options' => [
            'viscosity of ink',
            'capillary action phenomenon',
            'diffusion of ink through the blotting'
        ]
    ],
    2 => [
        'question' => 'The absorption of ink by blotting paper involves',
        'type' => 'multiple',
        'options' => [
            'viscosity of ink',
            'capillary action phenomenon',
            'diffusion of ink through the blotting'
        ]
    ],
    2 => [
        'question' => 'The absorption of ink by blotting paper involves',
        'type' => 'multiple',
        'options' => [
            'viscosity of ink',
            'capillary action phenomenon',
            'diffusion of ink through the blotting'
        ]
    ],
    2 => [
        'question' => 'The absorption of ink by blotting paper involves',
        'type' => 'multiple',
        'options' => [
            'viscosity of ink',
            'capillary action phenomenon',
            'diffusion of ink through the blotting'
        ]
    ],
    2 => [
        'question' => 'The absorption of ink by blotting paper involves',
        'type' => 'multiple',
        'options' => [
            'viscosity of ink',
            'capillary action phenomenon',
            'diffusion of ink through the blotting'
        ]
    ],
    2 => [
        'question' => 'The absorption of ink by blotting paper involves',
        'type' => 'multiple',
        'options' => [
            'viscosity of ink',
            'capillary action phenomenon',
            'diffusion of ink through the blotting'
        ]
    ],
    2 => [
        'question' => 'The absorption of ink by blotting paper involves',
        'type' => 'multiple',
        'options' => [
            'viscosity of ink',
            'capillary action phenomenon',
            'diffusion of ink through the blotting'
        ]
    ],
    2 => [
        'question' => 'The absorption of ink by blotting paper involves',
        'type' => 'multiple',
        'options' => [
            'viscosity of ink',
            'capillary action phenomenon',
            'diffusion of ink through the blotting'
        ]
    ],
    2 => [
        'question' => 'The absorption of ink by blotting paper involves',
        'type' => 'multiple',
        'options' => [
            'viscosity of ink',
            'capillary action phenomenon',
            'diffusion of ink through the blotting'
        ]
    ],
    2 => [
        'question' => 'The absorption of ink by blotting paper involves',
        'type' => 'multiple',
        'options' => [
            'viscosity of ink',
            'capillary action phenomenon',
            'diffusion of ink through the blotting'
        ]
    ],
    2 => [
        'question' => 'The absorption of ink by blotting paper involves',
        'type' => 'multiple',
        'options' => [
            'viscosity of ink',
            'capillary action phenomenon',
            'diffusion of ink through the blotting'
        ]
    ],
    2 => [
        'question' => 'The absorption of ink by blotting paper involves',
        'type' => 'multiple',
        'options' => [
            'viscosity of ink',
            'capillary action phenomenon',
            'diffusion of ink through the blotting'
        ]
    ],
    3 => [
        'question' => 'Look at the pairs of shapes. Which shows a pair of sphere?',
        'type' => 'multiple',
        'options' => [
            'Circle pair',
            'Square pair',
            'Triangle pair'
        ]
    ],
    4 => [
        'question' => 'Write a Python function to find the factorial of a number.',
        'type' => 'programming',
        'language' => 'python',
        'initial_code' => "def factorial(n):\n    # Write your code here\n    pass\n\n# Test cases\nprint(factorial(5))",
        'test_cases' => [
            ['input' => '5', 'expected' => '120'],
            ['input' => '0', 'expected' => '1']
        ]
    ],
    5 => [
        'question' => 'What is the atomic number of Carbon?',
        'type' => 'multiple',
        'options' => [
            '5',
            '6',
            '7'
        ]
    ],
    6 => [
        'question' => 'Create a function that checks if a string is a palindrome.',
        'type' => 'programming',
        'language' => 'python',
        'initial_code' => "def is_palindrome(s):\n    # Write your code here\n    pass\n\n# Test cases\nprint(is_palindrome('radar'))\nprint(is_palindrome('hello'))",
        'test_cases' => [
            ['input' => 'radar', 'expected' => 'True'],
            ['input' => 'hello', 'expected' => 'False']
        ]
    ],
    // Add more questions...
];

// Get current page from query string or default to 1
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$questions_per_page = 30;
$total_pages = ceil(count($all_questions) / $questions_per_page);

// Get questions for current page
$start_question = (($current_page - 1) * $questions_per_page) + 1;
$end_question = min($start_question + $questions_per_page - 1, count($all_questions));

// Get current page questions
$current_questions = array_slice($all_questions, $start_question - 1, $questions_per_page, true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Questions & Answers</title>
    <link href="assets/css/exam-styles.css" rel="stylesheet">
</head>
<body>
    <div class="main-container">
        <h1 class="main-title">Questions & Answers</h1>
        
        <div class="content-wrapper">
            <!-- Left side - Questions -->
            <div class="questions-container">
                <form id="exam-form" method="GET">
                    <?php foreach($current_questions as $q_num => $question): ?>
                    <div class="question-container <?php echo $question['type']; ?>">
                        <div class="question-content">
                            <span class="question-number"><?php echo $q_num; ?></span>
                            <span class="question-text"><?php echo $question['question']; ?></span>
                            <span class="bookmark-icon">☆</span>
                        </div>
                        
                        <?php if($question['type'] === 'multiple'): ?>
                            <div class="options">
                                <?php foreach($question['options'] as $opt_num => $option): ?>
                                <div class="option">
                                    <input type="radio" name="q<?php echo $q_num; ?>" id="q<?php echo $q_num; ?>_<?php echo $opt_num; ?>">
                                    <label for="q<?php echo $q_num; ?>_<?php echo $opt_num; ?>"><?php echo $option; ?></label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php elseif($question['type'] === 'programming'): ?>
                            <div class="code-editor">
                                <div class="editor-header">
                                    <select class="language-select">
                                        <option value="python" <?php echo $question['language'] === 'python' ? 'selected' : ''; ?>>Python</option>
                                        <option value="java">Java</option>
                                        <option value="cpp">C++</option>
                                    </select>
                                    <button type="button" class="run-btn">Run Code</button>
                                </div>
                                <textarea class="code-input" rows="10"><?php echo $question['initial_code']; ?></textarea>
                                <div class="test-cases">
                                    <h4>Test Cases:</h4>
                                    <?php foreach($question['test_cases'] as $index => $test): ?>
                                        <div class="test-case">
                                            <div class="test-case-header">Test Case <?php echo $index + 1; ?></div>
                                            <div class="input">Input: <?php echo $test['input']; ?></div>
                                            <div class="expected">Expected Output: <?php echo $test['expected']; ?></div>
                                            <div class="actual-output" id="output-q<?php echo $q_num; ?>-test<?php echo $index; ?>"></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>

                    <div class="navigation-buttons">
                        <?php if($current_page > 1): ?>
                            <a href="?page=<?php echo $current_page - 1; ?>" class="btn-prev">Previous</a>
                        <?php endif; ?>
                        
                        <?php if($current_page < $total_pages): ?>
                            <a href="?page=<?php echo $current_page + 1; ?>" class="btn-next">Next</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Right side - Status and Timer -->
            <div class="sidebar">
                <div class="status-section">
                    <h3>Questions status</h3>
                    <div class="status-grid">
                        <?php 
                        // Show question numbers for current page
                        for($i = $start_question; $i <= $end_question; $i++): 
                        ?>
                            <div class="status-item <?php 
                                if(in_array($i, [1,2,3,5,6,16,21])) echo 'answered';
                                elseif(in_array($i, [7,19])) echo 'marked';
                                else echo 'not-answered';
                            ?>"><?php echo $i; ?></div>
                        <?php endfor; ?>
                    </div>
                    <div class="page-navigation">
                        <span>Page <?php echo $current_page; ?> of <?php echo $total_pages; ?></span>
                    </div>
                    <div class="status-legend">
                        <div class="legend-item"><span class="dot answered"></span> Answered</div>
                        <div class="legend-item"><span class="dot marked"></span> Marked for review</div>
                        <div class="legend-item"><span class="dot not-answered"></span> Not Answered</div>
                    </div>
                </div>

                <div class="timer-section">
                    <h3>Timer</h3>
                    <div class="time-display">
                        <div class="time" id="time">05:25</div>
                        <div class="time-label">Time left</div>
                    </div>
                    <div class="estimated-time">Estimated time : 30min</div>
                </div>

                <button type="button" class="btn-submit">Submit Exam</button>
            </div>
        </div>
    </div>

    <style>
    .navigation-buttons {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
    }

    .btn-prev, .btn-next {
        background: #4CAF50;
        color: white;
        padding: 10px 30px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        text-decoration: none;
        transition: background-color 0.3s ease;
    }

    .btn-prev:hover, .btn-next:hover {
        background: #45a049;
    }

    .page-navigation {
        text-align: center;
        margin: 10px 0;
        color: #666;
        font-size: 14px;
    }

    .code-editor {
        background: #f8f9fa;
        border-radius: 8px;
        overflow: hidden;
        margin-top: 15px;
    }

    .editor-header {
        background: #e9ecef;
        padding: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .language-select {
        padding: 5px 10px;
        border-radius: 4px;
        border: 1px solid #ced4da;
    }

    .code-input {
        width: 100%;
        min-height: 200px;
        padding: 15px;
        font-family: 'Consolas', monospace;
        font-size: 14px;
        line-height: 1.5;
        border: none;
        background: #fff;
        resize: vertical;
    }

    .test-cases {
        padding: 15px;
        background: #fff;
        border-top: 1px solid #dee2e6;
    }

    .test-case {
        margin-bottom: 15px;
        padding: 10px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
    }

    .test-case-header {
        font-weight: bold;
        margin-bottom: 5px;
    }

    .loading {
        color: #6c757d;
        font-style: italic;
    }

    .output-result {
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px dashed #dee2e6;
    }

    .status {
        display: inline-block;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 12px;
        font-weight: bold;
    }

    .status.success {
        background: #d4edda;
        color: #155724;
    }

    .status.error {
        background: #f8d7da;
        color: #721c24;
    }

    .actual {
        margin-top: 5px;
        font-family: monospace;
        font-size: 13px;
    }
    </style>

    <script>
        // Timer functionality
        function startTimer(duration, display) {
            let timer = duration, minutes, seconds;
            let countdown = setInterval(function () {
                minutes = parseInt(timer / 60, 10);
                seconds = parseInt(timer % 60, 10);

                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;

                display.textContent = minutes + ":" + seconds;

                if (--timer < 0) {
                    clearInterval(countdown);
                    document.getElementById('exam-form').submit();
                }
            }, 1000);
        }

        // Bookmark functionality
        document.querySelectorAll('.bookmark-icon').forEach(icon => {
            icon.addEventListener('click', function() {
                this.textContent = this.textContent === '☆' ? '★' : '☆';
                // Add logic to mark question for review
            });
        });

        // Code editor functionality
        document.querySelectorAll('.run-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const editorContainer = this.closest('.code-editor');
                const codeInput = editorContainer.querySelector('.code-input');
                const language = editorContainer.querySelector('.language-select').value;
                const testCases = editorContainer.querySelectorAll('.test-case');
                
                // Simulate code execution (in real implementation, this would call your backend)
                console.log(`Running ${language} code:`, codeInput.value);
                
                // Show loading state
                testCases.forEach(testCase => {
                    const output = testCase.querySelector('.actual-output');
                    output.innerHTML = '<div class="loading">Running test...</div>';
                    
                    // Simulate test execution
                    setTimeout(() => {
                        output.innerHTML = `
                            <div class="output-result">
                                <span class="status success">✓ Passed</span>
                                <div class="actual">Actual Output: ${testCase.querySelector('.expected').textContent.split(': ')[1]}</div>
                            </div>
                        `;
                    }, 1500);
                });
            });
        });

        // Initialize timer
        window.onload = function () {
            let fiveMinutes = 60 * 5 + 40,
                display = document.getElementById('time');
            startTimer(fiveMinutes, display);
        };
    </script>
</body>
</html> 