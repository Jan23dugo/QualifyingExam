<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Examination</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>

        .timer {
            max-width: 200px;
            margin: 20px auto;
            padding: 10px;
            background-color: #73343a;
            color: #f5f5f5;
            font-size: 1.2em;
            text-align: center;
            border-radius: 200px;
        }

        .instruction-section, .compiler-section {
            border: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 20px auto;
            border-radius: 8px;
            background-color: #f5f5f5;
            box-shadow: 0 2px 4px rgba(115,52,58, .7);
            display: none; /* Hide sections by default */
            max-width: 800px;
            margin: 20px auto;
        }

        .instruction-section {
            align-items: center;
            text-align: center;
        }

        .instruction-section li {
            align-items: left;
            text-align: left;
        }

        .instruction-section.active, .question-section.active, .compiler-section.active {
            display: block;
        }

        .question-section {
            border: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 20px auto;
            border-radius: 8px;
            background-color: #f5f5f5;
            box-shadow: 0 2px 4px rgba(115,52,58, .7);
            display: none; /* Hide sections by default */
            max-width: 800px;
            margin: 20px auto;
            max-height: 450px; /* Adjust based on the fixed timer height */
            overflow-y: auto;
            padding-top: 50px; /* Space for the fixed timer */

        }

        .navigation-buttons {
            text-align: center;
            margin-top: 15px;
        }

        button {
            background-color: #e5b168; /* Yellow-orange */
            color: #73343a;
            border: none;
            padding: 10px 18px;
            font-size: 1.1em;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        button:hover {
            background-color: #73343a; /* Lighter yellow-orange */
            transform: translateY(-3px);
            color: #e5b168;
        }

        .identification {
            padding: 10px;
            border-radius: 10px;
        }


    </style>
</head>
<body class="exam-body">

    <div class="navbar">
        <nav>
            <ul>
                <img src="assets/img/streamslogo.png" alt="STREAMS Logo" class="streamslogo">
            </ul>
        </nav>
    </div>

    <!-- Instruction Section -->
    <div id="instruction-section" class="instruction-section active">
        <h2>Instructions</h2>
        <p>Please carefully review all instructions and questions before and while answering the examination. You will be allotted a specific timeframe to complete the assessment.</p>
        <p>Once you start, a timer will begin counting down, and you will be able to navigate between question sections. After answering, you may review your answers before submitting.</p>
        <p>The examination will consists of two parts:<p> 
            <ul>
                <li><strong>Multiple Choices</strong></li>
                <li><strong>Programming</strong></li>
            </ul>
        <button onclick="startExam()">Start Exam</button>
    </div>

    <div id="timer" class="timer" style="display: none;">Time Remaining: 10:00</div>

    <div id="question-sections" style="display:none;">
        
        <!-- Question sections will be generated here -->
    </div>

    <!-- Compiler Section for Review -->
    <div class="compiler-section" id="compiler-section">
        <h3>Review Your Answers</h3>
        <div id="compiled-answers"></div>
        <div class="navigation-buttons">
            <button onclick="goToPreviousSection()">Back to Exam</button>
            <button onclick="confirmSubmit()">Submit Answers</button>
        </div>
    </div>

    <script>
        const questions = [
    // Multiple Choice Questions
    { 
        type: "multipleChoice",
        question: "Which of the following is not a programming language?", 
        options: { A: "A. Python", B: "B. Java", C: "C. HTML", D: "D. Ruby" } 
    },
    { 
        type: "multipleChoice",
        question: "Which component is the brain of the computer?", 
        options: { A: "A. Hard Drive", B: "B. RAM", C: "C. CPU", D: "D. GPU" } 
    },
    { 
        type: "multipleChoice",
        question: "What does 'HTTP' stand for?", 
        options: { A: "A. HyperText Transfer Protocol", B: "B. HyperText Transmission Protocol", C: "C. Hyperlink Text Transfer Protocol", D: "D. Hyperlink Transmission Protocol" } 
    },
    // True/False Questions
    { 
        type: "trueFalse",
        question: "The Internet and the World Wide Web are the same thing.",
        options: { A: "A. True", B: "B. False" }
    },
    { 
        type: "trueFalse",
        question: "A compiler translates code into machine language that the CPU can execute.",
        options: { A: "A. True", B: "B. False" }
    },
    // Identification Questions
    { 
        type: "identification",
        question: "Identify the device used to output audio from a computer to speakers or headphones."
    },
    { 
        type: "identification",
        question: "This type of memory is volatile and temporarily stores data for quick access."
    },
    { 
        type: "identification",
        question: "Identify the type of network that spans over a city and allows data transfer across a metropolitan area."
    }
];


const questionsPerSection = 5;
    let currentSectionIndex = 0;
    let answers = Array(questions.length).fill(null);  // Remove the duplicate declaration
    let timeLeft = 10 * 60; // Exam duration in seconds (10 minutes)
    let timerInterval;

    // Timer countdown
    function startTimer(duration) {
            let time = duration, minutes, seconds;
            timerInterval = setInterval(() => {
                minutes = parseInt(time / 60, 10);
                seconds = parseInt(time % 60, 10);
                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;
                document.getElementById('timer').textContent = `Time Remaining: ${minutes}:${seconds}`;
                if (--time < 0) clearInterval(timerInterval);
            }, 1000);
        }

    // Start exam function
    function startExam() {
        document.getElementById('instruction-section').classList.remove('active');
        document.getElementById('timer').style.display = 'block';
        document.getElementById('question-sections').style.display = 'block';
        startTimer(600);
        renderSections();  // Render sections only after the exam starts
    }

    // Render questions in sections
    function renderSections() {
        const questionSections = document.getElementById('question-sections');
        questionSections.innerHTML = '';  // Clear existing content

        const sectionCount = Math.ceil(questions.length / questionsPerSection);
        for (let i = 0; i < sectionCount; i++) {
            const section = document.createElement('div');
            section.className = 'question-section';
            if (i === currentSectionIndex) section.classList.add('active');

            const start = i * questionsPerSection;
            const end = Math.min(start + questionsPerSection, questions.length);

            for (let j = start; j < end; j++) {
                const q = questions[j];
                let questionHtml = `<div class="single-question"><h2>Question ${j + 1}</h2><p>${q.question}</p>`;

                if (q.type === "multipleChoice" || q.type === "trueFalse") {
                    questionHtml += `${Object.entries(q.options).map(
                        ([key, value]) => `
                            <input type="radio" name="question${j}" value="${key}" onclick="selectAnswer(${j}, '${key}')">
                            <label>${value}</label><br>
                        `
                    ).join('')}`;
                } else if (q.type === "identification") {
                    questionHtml += `<input class="identification" type="text" name="question${j}" oninput="selectAnswer(${j}, this.value)">`;
                }

                questionHtml += `</div>`;
                section.innerHTML += questionHtml;
            }

            // Navigation buttons
            section.innerHTML += `
                <div class="navigation-buttons">
                    <button onclick="goToPreviousSection()" ${i === 0 ? 'disabled' : ''}>Previous Section</button>
                    <button onclick="${i === sectionCount - 1 ? 'compileAnswers()' : 'goToNextSection()'}">${i === sectionCount - 1 ? 'Submit' : 'Next Section'}</button>
                </div>
            `;

            questionSections.appendChild(section);
        }
    }

        // Store selected answer
        function selectAnswer(questionIndex, answer) {
            answers[questionIndex] = answer;
        }

        // Show the next section
        function goToNextSection() {
            const sections = document.querySelectorAll('.question-section');
            if (!validateSectionAnswers()) return;
            if (currentSectionIndex < Math.ceil(questions.length / questionsPerSection) - 1) {
                currentSectionIndex++;
                updateSectionVisibility();
            } else {
                showCompilerSection();
            }
        }

        // Show the previous section
        function goToPreviousSection() {
            if (currentSectionIndex > 0) {
                currentSectionIndex--;
                updateSectionVisibility();
            } else {
                document.getElementById('compiler-section').classList.remove('active');
                document.getElementById('question-sections').style.display = 'block';
            }
        }

        // Ensure answers are selected for the current section
        function validateSectionAnswers() {
            const start = currentSectionIndex * questionsPerSection;
            const end = Math.min(start + questionsPerSection, questions.length);
            for (let i = start; i < end; i++) {
                if (answers[i] === null) {
                    alert(`Please answer Question ${i + 1} before moving on.`);
                    return false;
                }
            }
            return true;
        }

        // Update the visibility of sections and navigation buttons
        function updateSectionVisibility() {
            const sections = document.querySelectorAll('.question-section');
            sections.forEach((section, index) => {
                section.classList.toggle('active', index === currentSectionIndex);
            });
        }

        // Show the compiler section for review
        function showCompilerSection() {
            clearInterval(timerInterval);  // Stop timer when reviewing answers
            document.getElementById('question-sections').style.display = 'none';
            document.getElementById('compiler-section').classList.add('active');
            const compiledAnswers = document.getElementById('compiled-answers');
            compiledAnswers.innerHTML = '';

            questions.forEach((q, index) => {
                const selectedAnswer = answers[index] ? q.options[answers[index]] : "No answer selected";
                compiledAnswers.innerHTML += `
                    <p><strong>Question ${index + 1}:</strong> ${q.question}</p>
                    <p><strong>Answer:</strong> ${selectedAnswer}</p>
                    <hr>
                `;
            });
        }

        // Final submission confirmation
        function confirmSubmit() {
            alert("Your answers have been submitted!");
            window.location.href = 'results.php';
        }
    </script>    
</body>
</html>
