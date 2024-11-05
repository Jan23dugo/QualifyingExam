<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Questions</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Additional styling for modals */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            width: 60%;
            max-width: 600px;
            text-align: center;
        }

        #question-list {
            margin: 20px;
            font-family: Arial, sans-serif;
        }

        #question-list div {
            padding: 10px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }

        #question-list button {
            padding: 5px 10px;
            margin-left: 5px;
            border: 1px solid #ccc;
            background: #fff;
            cursor: pointer;
            border-radius: 3px;
        }

        #question-list button:hover {
            background: #f0f0f0;
        }

        .question-details {
            margin: 10px 0;
            padding-left: 20px;
        }

        .question-details p {
            margin: 5px 0;
        }

        .question-actions {
            margin-top: 10px;
        }

        .question-actions button {
            margin-right: 10px;
            padding: 5px 10px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="logo">
            <img src="puplogo.png" alt="Logo">
        </div>
        <nav>
            <ul>
                <li><a href="#dashboard">Dashboard</a></li>
                <li><a href="#create-exam">Create Exam</a></li>
                <li><a href="#students">Students</a></li>
                <li><a href="#analytics">Analytics</a></li>
                <li><a href="#calendar">Calendar</a></li>
                <li><a href="#sign-out">Sign Out</a></li>
            </ul>
        </nav>
    </aside>
    <main class="main-content">
        <header>
            <div class="icons">
                <span class="settings-icon">&#9881;</span>
                <span class="user-icon">&#128100;</span>
            </div>
        </header>
        <div class="tab-container">
            <div class="tabs">
                <button class="tab active" onclick="showTab('questions')">Question</button>
                <button class="tab" onclick="showTab('preview')">Preview</button>
                <button class="tab" onclick="showTab('settings')">Settings</button>
                <button class="tab" onclick="showTab('result')">Result</button>
                <button class="tab" onclick="showTab('assign')">Assign Exam</button>
            </div>
        </div>
        
        <div id="questions-tab" class="tab-content">
            <ul id="question-list">
                <!-- List of added questions will appear here -->
            </ul>
            <button class="add-question-btn" onclick="showQuestionTypeOptions()">Add question</button>
        </div>
        
        <div id="preview-tab" class="tab-content" style="display: none;">
            <div class="instructions-container">
                <div class="instructions-content">
                    <h2>CCIS Qualifying Exam</h2>
                    <p>Instruction: Please carefully review all instructions before and while answering the examination.</p>
                    <p>Once you start, a timer will begin counting down, and you will be able to navigate between questions.</p>
                    <p>The examination consists of:</p>
                    <ul>
                        <li>Multiple Choice</li>
                        <li>Identification</li>
                        <li>True or False</li>
                        <li>Programming</li>
                    </ul>
                    <button onclick="startAttempt()">Start Attempt</button>
                </div>
            </div>
        </div>
    
            
            <!-- Modal for showing exam questions -->
        <div id="question-modal" class="modal">
            <div class="modal-content">
                <span class="close-btn" onclick="closeModal()">&times;</span>
                <div id="modal-questions-content">
                    <!-- Exam questions will be displayed here -->
                </div>
            </div>
        </div>

        <div id="form-container" style="display:none;">
            <!-- Dynamic form for adding questions will appear here -->
        </div>
    </main>

    <script>
        let questions = [];
        const currentExamId = getExamId(); // Store exam ID globally
        let editingIndex = null;

        function showTab(tab) {
            document.querySelectorAll('.tab-content').forEach(content => {
                content.style.display = 'none';
            });
            document.getElementById(`${tab}-tab`).style.display = 'block';

            if (tab === 'preview') {
                renderPreview();
            }
        }

        function showQuestionTypeOptions() {
            const formContainer = document.getElementById("form-container");
            formContainer.style.display = "block";
            formContainer.innerHTML = `
                <h4>Select Question Type</h4>
                <button onclick="showForm('multiple-choice')">Multiple Choice</button>
                <button onclick="showForm('true-false')">True or False</button>
                <button onclick="showForm('matching')">Matching</button>
                <button onclick="showForm('coding')">Coding</button>
                <button onclick="showForm('identification')">Identification</button>
            `;
        }

        function showForm(type) {
            const formContainer = document.getElementById("form-container");

            let formHTML = `<h4>${editingIndex !== null ? 'Edit' : 'Add'} ${type.charAt(0).toUpperCase() + type.slice(1)} Question</h4>`;
            formHTML += `
                <form id="questionForm" onsubmit="saveQuestion('${type}'); return false;">
                    <label>Question:</label>
                    <input type="text" id="questionText" required><br><br>
            `;

            if (type === "multiple-choice") {
                formHTML += `
                    <label>Option A:</label><input type="text" id="optionA" required><br>
                    <label>Option B:</label><input type="text" id="optionB" required><br>
                    <label>Option C:</label><input type="text" id="optionC" required><br>
                    <label>Option D:</label><input type="text" id="optionD" required><br>
                    <label>Answer:</label><input type="text" id="mcAnswer" required><br><br>
                `;
            } else if (type === "true-false") {
                formHTML += `
                    <label>Answer:</label>
                    <select id="tfAnswer">
                        <option value="True">True</option>
                        <option value="False">False</option>
                    </select><br><br>
                `;
            } else if (type === "matching") {
                formHTML += `
                    <label>Term:</label><input type="text" id="term" required><br>
                    <label>Match:</label><input type="text" id="match" required><br><br>
                `;
            } else if (type === "coding") {
                formHTML += `
                    <label>Code:</label><textarea id="codeAnswer" required></textarea><br><br>
                `;
            } else if (type === "identification") {
                formHTML += `
                    <label>Answer:</label><input type="text" id="identificationAnswer" required><br><br>
                `;
            }

            formHTML += `<button type="submit">${editingIndex !== null ? 'Save Changes' : 'Add Question'}</button></form>`;
            formContainer.innerHTML = formHTML;

            if (editingIndex !== null) {
                populateFormFields(type, questions[editingIndex]);
            }
        }

        function saveQuestion(type) {
            const questionText = document.getElementById("questionText").value;
            const newQuestion = {
                text: questionText,
                type: type,
                question_text: questionText,  // for backend compatibility
                question_type: type          // for backend compatibility
            };

            // Add type-specific data
            switch(type) {
                case 'multiple-choice':
                    newQuestion.options = {
                        A: document.getElementById("optionA").value,
                        B: document.getElementById("optionB").value,
                        C: document.getElementById("optionC").value,
                        D: document.getElementById("optionD").value
                    };
                    newQuestion.answer = document.getElementById("mcAnswer").value;
                    break;
                case 'true-false':
                    newQuestion.answer = document.getElementById("tfAnswer").value;
                    break;
                case 'matching':
                    newQuestion.term = document.getElementById("term").value;
                    newQuestion.match = document.getElementById("match").value;
                    break;
                case 'coding':
                    newQuestion.code = document.getElementById("codeAnswer").value;
                    break;
                case 'identification':
                    newQuestion.answer = document.getElementById("identificationAnswer").value;
                    break;
            }

            // Add to local array first
            if (editingIndex !== null) {
                questions[editingIndex] = newQuestion;
                editingIndex = null;
            } else {
                questions.push(newQuestion);
            }

            // Update the display immediately
            renderQuestions();

            // Then save to backend
            const formData = new FormData();
            formData.append('action', 'add_question');
            formData.append('exam_id', getExamId());
            formData.append('question_type', type);
            formData.append('question_text', questionText);

            // Add type-specific data to formData
            switch(type) {
                case 'multiple-choice':
                    formData.append('optionA', document.getElementById("optionA").value);
                    formData.append('optionB', document.getElementById("optionB").value);
                    formData.append('optionC', document.getElementById("optionC").value);
                    formData.append('optionD', document.getElementById("optionD").value);
                    formData.append('mcAnswer', document.getElementById("mcAnswer").value);
                    break;
                case 'true-false':
                    formData.append('tfAnswer', document.getElementById("tfAnswer").value);
                    break;
                case 'matching':
                    formData.append('term', document.getElementById("term").value);
                    formData.append('match', document.getElementById("match").value);
                    break;
                case 'coding':
                    formData.append('codeAnswer', document.getElementById("codeAnswer").value);
                    break;
                case 'identification':
                    formData.append('identificationAnswer', document.getElementById("identificationAnswer").value);
                    break;
            }

            fetch('add-questionsBack.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Update the question with the ID from the server
                    const lastIndex = questions.length - 1;
                    questions[lastIndex].question_id = data.data.question_id;
                    document.getElementById("form-container").style.display = "none";
                } else {
                    alert('Error: ' + data.message);
                    // Remove the question from the local array if save failed
                    questions.pop();
                    renderQuestions();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error saving question. Please try again.');
                // Remove the question from the local array if save failed
                questions.pop();
                renderQuestions();
            });
        }

        function renderQuestions() {
            const questionList = document.getElementById("question-list");
            questionList.innerHTML = "";

            questions.forEach((question, index) => {
                const listItem = document.createElement("div");
                listItem.style.marginBottom = "10px";
                listItem.innerHTML = `
                    Question ${index + 1}: ${question.text || question.question_text} (${question.type || question.question_type}) 
                    <button onclick="editQuestion(${index})" style="margin-left: 10px;">Edit</button>
                    <button onclick="removeQuestion(${index})" style="margin-left: 5px;">Remove</button>
                `;
                questionList.appendChild(listItem);
            });
        }

        // Show the question modal on "Start Attempt"
        function startAttempt() {
            renderModalQuestions(); // Populate the modal with questions
            document.getElementById("question-modal").style.display = "flex";
        }

        // Render questions in the modal
        function renderModalQuestions() {
            const modalContent = document.getElementById("modal-questions-content");
            modalContent.innerHTML = ""; // Clear any previous content

            questions.forEach((question, index) => {
                let questionHTML = `<p><strong>${index + 1}. ${question.text}</strong></p>`;

                if (question.type === "multiple-choice") {
                    questionHTML += `
                        <p>A) ${question.options.A}</p>
                        <p>B) ${question.options.B}</p>
                        <p>C) ${question.options.C}</p>
                        <p>D) ${question.options.D}</p>
                    `;
                } else if (question.type === "true-false") {
                    questionHTML += `<p>True or False</p>`;
                } else if (question.type === "matching") {
                    questionHTML += `<p>Match the following: ${question.term} - ${question.match}</p>`;
                } else if (question.type === "coding") {
                    questionHTML += `<pre>${question.code}</pre>`;
                } else if (question.type === "identification") {
                    questionHTML += `<p>Identification Answer</p>`;
                }

                modalContent.innerHTML += questionHTML + "<hr>";
            });
        }

        // Close the question modal
        function closeModal() {
            document.getElementById("question-modal").style.display = "none";
        }

        function editQuestion(index) {
            editingIndex = index;
            showForm(questions[index].type);
        }

        function removeQuestion(index) {
            const question = questions[index];
            if (!question || !question.question_id) {
                console.error('Invalid question');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'remove_question');
            formData.append('exam_id', getExamId());
            formData.append('question_id', question.question_id);

            fetch('add-questionsBack.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    questions.splice(index, 1);
                    renderQuestions();
                } else {
                    alert('Error removing question: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error removing question. Please try again.');
            });
        }

        // Get the exam_id from URL parameter
        function getExamId() {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get('exam_id');
        }

        // Function to load existing questions
        function loadQuestions() {
            const examId = getExamId();
            if (!examId) {
                console.error('No exam ID found');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'get_questions');
            formData.append('exam_id', examId);

            fetch('add-questionsBack.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' && data.data) {
                    questions = data.data.map(q => ({
                        question_id: q.question_id,
                        text: q.question_text,
                        type: q.question_type,
                        options: q.options || {},
                        answer: q.answer,
                        term: q.term,
                        match: q.match,
                        code: q.code
                    }));
                    renderQuestions();
                } else {
                    console.error('Error loading questions:', data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // Add this to load questions when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadQuestions();
        });

        // Add some CSS to style the questions list
        const styles = `
            #question-list {
                list-style-type: none;
                padding: 0;
            }

            #question-list li {
                background: #f5f5f5;
                margin: 10px 0;
                padding: 15px;
                border-radius: 4px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .edit-btn, .remove-btn {
                margin-left: 10px;
                padding: 5px 10px;
                border: none;
                border-radius: 3px;
                cursor: pointer;
            }

            .edit-btn {
                background: #4CAF50;
                color: white;
            }

            .remove-btn {
                background: #f44336;
                color: white;
            }
        `;

        // Add the styles to the document
        const styleSheet = document.createElement("style");
        styleSheet.innerText = styles;
        document.head.appendChild(styleSheet);

        // Load questions when the page loads
        window.addEventListener('load', function() {
            loadQuestions();
        });
    </script>
</body>
</html>
