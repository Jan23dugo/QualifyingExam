<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Examination</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="exam-body">
    <div class="navbar">
        <img src="ccislogo.png" alt="Left Logo" class="ccislogo">
        <nav>
            <ul>
                <li><a href=index.html>HOME</a></li>
                <li><a href=aboutexam.html>ABOUT EXAM</a></li>
                <li><a href=eligibilitychecker.html>ELIGIBILITY CHECKER</a></li>
                <li><a href="contactus.html">CONTACT US</a></li>
                <li><a href=takeanexam.html>TAKE AN EXAM</a></li>
                <li><a href=register.html>REGISTER</a></li>
            </ul>
        </nav>
    </div>

    <div class="exam-interface">
        <div class="question-container">
            <h2 id="question-number">Question 1</h2>
            <p id="question-text">What is 2 + 2?</p>

            <div class="options-container" id="options">
            </div>

            
            <div class="buttons">
                <button class="previous-btn" onclick="goToPreviousQuestion()">Previous</button>
                <button class="next-btn" onclick="goToNextQuestion()">Next</button>
            </div>
        </div>

        <div class="submit" id="submit-dialog" style="display: none;">
            <h3>Submit Your Answers</h3>
            <p>Are you sure you want to submit your answers?</p>
                <button id="confirm-submit" onclick="confirmSubmit()">SUBMIT</button>
                <button id="review-answers" onclick="reviewAnswers()">REVIEW ANSWERS</button>
        </div>

        <div class="side-container">
            <div class="time-container">
                <p>Time</p>
                <p>00:00:00</p>
            </div>
            <div class="grid-container">
                <div class="grid">
                    <div class="question-box" id="box1" onclick="goToQuestion(0)"></div>
                    <div class="question-box" id="box2" onclick="goToQuestion(1)"></div>
                    <div class="question-box" id="box3" onclick="goToQuestion(2)"></div>
                    <div class="question-box" id="box4" onclick="goToQuestion(3)"></div>
                    <div class="question-box" id="box5" onclick="goToQuestion(4)"></div>
                </div>
            </div>
        </div>
        
    </div>
    <script>
        const questions = [
            {
                question: "What is 2 + 2?",
                options: {
                    A: "3",
                    B: "4",
                    C: "5",
                    D: "6"
                }
            },
            {
                question: "What is the capital of the Philippines?",
                options: {
                    A: "Cebu",
                    B: "Davao",
                    C: "Manila",
                    D: "Baguio"
                }
            },
            {
                question: "What color is the sky?",
                options: {
                    A: "Red",
                    B: "Green",
                    C: "Blue",
                    D: "Yellow"
                }
            },
            {
                question: "What color is the color of the ocean?",
                options: {
                    A: "Red",
                    B: "Green",
                    C: "Blue",
                    D: "Yellow"
                }
            },
            {
                question: "What is 2 + 12?",
                options: {
                    A: "13",
                    B: "14",
                    C: "15",
                    D: "16"
                }
            }
        ];

        let currentQuestion = 0;  // Start at question 0
        let answers = {};  // Store user answers

        // Load a question by its index
        function loadQuestion(questionIndex) {
            currentQuestion = questionIndex;

            // Update question number and text
            document.getElementById('question-number').innerText = `Question ${questionIndex + 1}`;
            document.getElementById('question-text').innerText = questions[questionIndex].question;

            // Load options dynamically
            let optionsHtml = '';
            for (const [key, value] of Object.entries(questions[questionIndex].options)) {
                optionsHtml += `
                    <input type="radio" name="question${questionIndex}" value="${key}" onclick="selectAnswer(${questionIndex}, '${key}')">
                    <label>${value}</label><br>
                `;
            }
            document.getElementById('options').innerHTML = optionsHtml;

            // Restore previously selected answer (if any)
            restoreAnswer(questionIndex);

            // Update button text for navigation
            const nextBtn = document.getElementById('next-btn');
            nextBtn.innerText = currentQuestion === questions.length - 1 ? "Submit" : "Next";
        }

        // Store the selected answer and update the navigation box color
        function selectAnswer(questionIndex, answer) {
            answers[questionIndex] = answer;

            // Change the corresponding question box color to indicate it's answered
            document.getElementById(`box${questionIndex + 1}`).classList.add('answered');
        }

        // Restore answer if the user has already answered the question
        function restoreAnswer(questionIndex) {
            const selectedAnswer = answers[questionIndex];
            if (selectedAnswer) {
                document.querySelector(`input[name="question${questionIndex}"][value="${selectedAnswer}"]`).checked = true;
            }
        }

        // Navigate to the next question or show submit dialog
        function goToNextQuestion() {
            if (currentQuestion < questions.length - 1) {
                loadQuestion(currentQuestion + 1);
            } else {
                // Show submit dialog instead of going to the next question
                showSubmitDialog();
            }
        }

        // Navigate to the previous question
        function goToPreviousQuestion() {
            if (currentQuestion > 0) {
                loadQuestion(currentQuestion - 1);
            }
        }

        // Jump to a specific question when a navigation box is clicked
        function goToQuestion(questionIndex) {
            loadQuestion(questionIndex);
        }

        // Show the submit dialog
        function showSubmitDialog() {
            const dialog = document.getElementById('submit-dialog');
            dialog.style.display = 'block';
        }

        // Handle submission confirmation
        function confirmSubmit() {
            window.location.href = 'results.html';
        }

        // Review answers, close the dialog
        function reviewAnswers() {
            const dialog = document.getElementById('submit-dialog');
            dialog.style.display = 'none';
        }

        // Initialize the quiz with the first question
        loadQuestion(currentQuestion);
    </script>    
    
</body>
</html>
