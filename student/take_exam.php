<!-- Add this in the head section -->
<head>
    <!-- ... existing head content ... -->
    <script src="https://cdn.jsdelivr.net/npm/monaco-editor@0.33.0/min/vs/loader.js"></script>
    <link rel="stylesheet" href="assets/css/code-editor.css">
</head>

<!-- Add this where you display programming questions -->
<?php if ($question['type'] === 'programming'): ?>
    <div class="programming-question">
        <div class="row">
            <div class="col-md-6">
                <div class="question-content">
                    <h4>Question <?php echo $questionNumber; ?></h4>
                    <?php echo htmlspecialchars($question['question_text']); ?>
                    
                    <?php if (!empty($question['sample_input'])): ?>
                        <div class="sample-case">
                            <h5>Sample Input:</h5>
                            <pre><?php echo htmlspecialchars($question['sample_input']); ?></pre>
                            <h5>Sample Output:</h5>
                            <pre><?php echo htmlspecialchars($question['sample_output']); ?></pre>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="code-editor-container">
                    <div class="language-selector mb-2">
                        <label>Language:</label>
                        <select class="form-control" id="language-select-<?php echo $question['question_id']; ?>">
                            <option value="java">Java</option>
                            <option value="python">Python</option>
                            <option value="c">C</option>
                        </select>
                    </div>
                    <div id="code-editor-<?php echo $question['question_id']; ?>" class="code-editor"></div>
                    <div class="editor-actions mt-2">
                        <button class="btn btn-primary run-code" data-question-id="<?php echo $question['question_id']; ?>">
                            Run Code
                        </button>
                        <button class="btn btn-success submit-code" data-question-id="<?php echo $question['question_id']; ?>">
                            Submit
                        </button>
                    </div>
                    <div id="output-<?php echo $question['question_id']; ?>" class="code-output mt-2"></div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Add this at the end of the file -->
<script src="assets/js/code-editor.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize code editors for programming questions
    document.querySelectorAll('.programming-question').forEach(question => {
        const questionId = question.querySelector('.code-editor').id.split('-')[2];
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
            
            try {
                const response = await fetch('/api/execute_code.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ code, language, testCases: [{ input: '', output: '' }] })
                });
                
                const result = await response.json();
                outputDiv.innerHTML = `
                    <h5>Output:</h5>
                    <pre>${result.results[0].actualOutput || ''}</pre>
                    ${result.results[0].error ? `<pre class="error">${result.results[0].error}</pre>` : ''}
                `;
            } catch (error) {
                outputDiv.innerHTML = `<pre class="error">Error: ${error.message}</pre>`;
            }
        });
    });
});
</script> 