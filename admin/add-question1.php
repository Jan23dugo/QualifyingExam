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
            let question = { type, text: questionText };

            if (type === "multiple-choice") {
                question.options = {
                    A: document.getElementById("optionA").value,
                    B: document.getElementById("optionB").value,
                    C: document.getElementById("optionC").value,
                    D: document.getElementById("optionD").value,
                };
                question.answer = document.getElementById("mcAnswer").value;
            } else if (type === "true-false") {
                question.answer = document.getElementById("tfAnswer").value;
            } else if (type === "matching") {
                question.term = document.getElementById("term").value;
                question.match = document.getElementById("match").value;
            } else if (type === "coding") {
                question.code = document.getElementById("codeAnswer").value;
            } else if (type === "identification") {
                question.answer = document.getElementById("identificationAnswer").value;
            }

            if (editingIndex !== null) {
                questions[editingIndex] = question;
                editingIndex = null;
            } else {
                questions.push(question);
            }

            renderQuestions();
            document.getElementById("form-container").style.display = "none";
        }

        function renderQuestions() {
            const questionList = document.getElementById("question-list");
            questionList.innerHTML = "";

            questions.forEach((question, index) => {
                const listItem = document.createElement("li");
                listItem.innerHTML = `
                    <strong>Question ${index + 1}:</strong> ${question.text} (${question.type})
                    <button onclick="editQuestion(${index})">Edit</button>
                    <button onclick="removeQuestion(${index})">Remove</button>
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

        // Render questions on the "Questions" tab (unchanged function)
        function renderQuestions() {
            const questionList = document.getElementById("question-list");
            questionList.innerHTML = "";

            questions.forEach((question, index) => {
                const listItem = document.createElement("li");
                listItem.innerHTML = `
                    <strong>Question ${index + 1}:</strong> ${question.text} (${question.type})
                    <button onclick="editQuestion(${index})">Edit</button>
                    <button onclick="removeQuestion(${index})">Remove</button>
                `;
                questionList.appendChild(listItem);
            });
        }

        function editQuestion(index) {
            editingIndex = index;
            showForm(questions[index].type);
        }

        function removeQuestion(index) {
            questions.splice(index, 1);
            renderQuestions();
        }
    </script>
</body>
</html>