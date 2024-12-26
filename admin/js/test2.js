// At the top of test2.js, make handleQuestionTypeChange globally accessible
window.handleQuestionTypeChange = function(select, sectionId, questionIndex, existingData = null) {
    // Your existing handleQuestionTypeChange code...
};

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all modals
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modalElement => {
        new bootstrap.Modal(modalElement);
    });

    let sectionCounter = 1;
    const exam_id = new URLSearchParams(window.location.search).get('exam_id');

    // Initialize UI elements with null checks
    const showActionSidebarBtn = document.getElementById('showActionSidebar');
    const actionButtons = document.getElementById('actionButtons');
    const saveFormBtn = document.getElementById('save-form-btn');
    const addSectionBtn = document.getElementById('add-section-btn');
    const globalAddQuestionBtn = document.getElementById('global-add-question-btn');
    const importQuestionsBtn = document.getElementById('import-questions-btn');
    const searchQuestion = document.getElementById('searchQuestion');
    const importSelectedQuestionsBtn = document.getElementById('importSelectedQuestions');

    // Function to load existing sections and questions
    function loadSectionsAndQuestions(sections) {
        console.log('Starting to load sections and questions:', sections);
        const sectionBlocks = document.getElementById('sectionBlocks');
        if (!sectionBlocks) {
            console.error('sectionBlocks element not found');
            return;
        }
        sectionBlocks.innerHTML = ''; // Clear existing content
        
        sections.forEach(section => {
            console.log('Processing section:', section);
            const newSection = document.createElement('div');
            newSection.classList.add('section-block');
            newSection.setAttribute('data-section-id', section.section_id);
            
            newSection.innerHTML = `
                <div class="title-block">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="form-control editable-field section-field" 
                            contenteditable="true" 
                            data-placeholder="Untitled Section"
                            data-input-name="section_title[${section.section_id}]"
                            style="flex: 1; margin-right: 10px;">${section.title || ''}</div>
                        <input type="hidden" name="section_title[${section.section_id}]" value="${section.title || ''}">
                        <input type="hidden" name="section_id[${section.section_id}]" value="${section.section_id}">
                        <button type="button" class="delete-button btn btn-link text-danger" style="padding: 5px;">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="description-block">
                    <div class="form-control editable-field section-field" 
                        contenteditable="true" 
                        data-placeholder="Description (optional)"
                        data-input-name="section_description[${section.section_id}]">${section.description || ''}</div>
                    <input type="hidden" name="section_description[${section.section_id}]" value="${section.description || ''}">
                </div>
                <div id="question-container-${section.section_id}" class="question-block-container"></div>
            `;

            sectionBlocks.appendChild(newSection);

            // Add event listeners for contenteditable fields
            const editableFields = newSection.querySelectorAll('.editable-field');
            editableFields.forEach(field => {
                // Update hidden input when content changes
                field.addEventListener('input', function() {
                    const hiddenInput = this.nextElementSibling;
                    if (hiddenInput && hiddenInput.type === 'hidden') {
                        hiddenInput.value = this.innerHTML;
                    }
                });

                // Show toolbar on click
                field.addEventListener('click', function(e) {
                    e.stopPropagation();
                    currentField = this;
                    toolbar.classList.add('active');
                    positionToolbar(this);
                });
            });

            // Load questions for this section
            if (section.questions) {
                section.questions.forEach((question, qIndex) => {
                    const questionContainer = document.getElementById(`question-container-${section.section_id}`);
                    const newQuestion = createQuestionElement(section.section_id, qIndex, question);
                    questionContainer.appendChild(newQuestion);

                    // Load question options based on type
                    const questionTypeSelect = newQuestion.querySelector('.question-type-select');
                    questionTypeSelect.value = question.question_type;
                    handleQuestionTypeChange(questionTypeSelect, section.section_id, qIndex, question);
                });
            }
        });

        // Update sectionCounter to be higher than any existing section ID
        const maxSectionId = Math.max(...sections.map(s => parseInt(s.section_id)), 0);
        sectionCounter = maxSectionId + 1;

        attachEventListeners();
    }

    // Function to create question element with existing data
    function createQuestionElement(sectionId, questionIndex, questionData = null) {
        const newQuestion = document.createElement('div');
        newQuestion.classList.add('question-block');
        newQuestion.style.marginBottom = '20px';
        newQuestion.style.padding = '15px';
        newQuestion.style.border = '1px solid #ddd';
        newQuestion.style.borderRadius = '8px';

        if (questionData && questionData.question_id) {
            newQuestion.setAttribute('data-original-question-id', questionData.question_id);
        }

        newQuestion.innerHTML = `
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <div class="form-control editable-field question-field" 
                    contenteditable="true" 
                    data-placeholder="Enter your question here"
                    data-input-name="question_text[${sectionId}][${questionIndex}]"
                    style="flex: 1; margin-right: 10px; min-height: 100px; cursor: text;"
                >${questionData ? questionData.question_text : ''}</div>
                <div style="min-width: 200px;">
                    <select class="form-control question-type-select" name="question_type[${sectionId}][${questionIndex}]">
                        <option value="">Select Question Type</option>
                        <option value="multiple_choice">Multiple Choice</option>
                        <option value="true_false">True/False</option>
                        <option value="programming">Programming</option>
                    </select>
                </div>
                <button type="button" class="btn btn-link text-danger delete-question-btn" style="padding: 5px;">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
            <div class="question-options" style="margin-top: 10px;"></div>
            <div style="margin-top: 10px;">
                <input type="number" name="points[${sectionId}][${questionIndex}]" 
                    class="form-control" placeholder="Points" style="width: 100px;"
                    value="${questionData ? questionData.points || '' : ''}">
            </div>
        `;

        // Add click event listener specifically for the question field
        const questionField = newQuestion.querySelector('.question-field');
        questionField.addEventListener('click', function(e) {
            e.stopPropagation();
            currentField = this;
            toolbar.classList.add('active');
            positionToolbar(this);
        });

        if (questionData && questionData.question_type === 'multiple_choice' && questionData.options) {
            const optionsContainer = newQuestion.querySelector('.question-options');
            console.log('Question Data for Multiple Choice:', questionData);
            console.log('Options from database:', questionData.options);

            optionsContainer.innerHTML = `
                <div class="multiple-choice-options">
                    <!-- Options will be inserted here -->
                </div>
                <button type="button" class="btn btn-secondary add-option-btn" style="margin-bottom: 10px;">
                    Add Option
                </button>
            `;

            questionData.options.forEach((option, index) => {
                console.log('Processing option:', option);
                const optionHtml = `
                    <div class="option-container" style="margin-bottom: 10px;">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <input type="radio" name="correct_option[${sectionId}][${questionIndex}]" 
                                        ${parseInt(option.is_correct) === 1 ? 'checked' : ''}>
                                </div>
                            </div>
                            <input type="text" class="form-control" 
                                name="options[${sectionId}][${questionIndex}][]"
                                value="${option.choice_text}"
                                placeholder="Enter option text">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-danger delete-option-btn">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;

                const optionsDiv = optionsContainer.querySelector('.multiple-choice-options');
                optionsDiv.insertAdjacentHTML('beforeend', optionHtml);
            });

            // Add event listeners for delete buttons after adding all options
            optionsContainer.querySelectorAll('.delete-option-btn').forEach(button => {
                button.addEventListener('click', function() {
                    this.closest('.option-container').remove();
                });
            });

            // Add event listener for Add Option button
            const addOptionBtn = optionsContainer.querySelector('.add-option-btn');
            addOptionBtn.addEventListener('click', () => {
                addMultipleChoiceOption(optionsContainer, sectionId, questionIndex);
            });
        }

        // Make sure the question type is selected
        const questionTypeSelect = newQuestion.querySelector('.question-type-select');
        if (questionData && questionData.question_type) {
            questionTypeSelect.value = questionData.question_type;
        }

        return newQuestion;
    }

    // Fetch existing data when page loads
    if (exam_id) {
        console.log('Fetching exam data for exam_id:', exam_id);
        fetch(`save_question.php?exam_id=${exam_id}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('Received data:', data);
            if (data.sections) {
                console.log('Loading sections:', data.sections);
                loadSectionsAndQuestions(data.sections);
            } else {
                console.log('No sections found in response');
            }
        })
        .catch(error => {
            console.error('Error fetching exam data:', error);
            alert('Error loading exam data. Please check the console for details.');
        });
    }

    // Add Section functionality
    function addSection(sectionData = null) {
        const newSection = document.createElement('div');
        newSection.classList.add('section-block');
        newSection.setAttribute('data-section-id', 'new_' + sectionCounter);
        newSection.setAttribute('data-exam-id', exam_id);

        // Create section header with toggle and counter
        const sectionHeader = `
            <div class="title-block">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div class="form-control editable-field section-field" 
                        contenteditable="true" 
                        data-placeholder="Untitled Section"
                        data-input-name="section_title[${sectionCounter}]"></div>
                    <input type="hidden" name="section_title[${sectionCounter}]" value="">
                    <button type="button" class="delete-button btn btn-link text-danger" style="padding: 5px;">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>
        `;

        // Create section content
        const sectionContent = `
            <div class="description-block">
                <div class="form-control editable-field section-field" 
                    contenteditable="true" 
                    data-placeholder="Description (optional)"
                    data-input-name="section_description[${sectionCounter}]"></div>
                <input type="hidden" name="section_description[${sectionCounter}]" value="">
            </div>
            <div id="question-container-${sectionCounter}" class="question-block-container"></div>
        `;

        newSection.innerHTML = sectionHeader + sectionContent;

        const sectionBlocks = document.getElementById('sectionBlocks');
        if (sectionBlocks) {
            sectionBlocks.appendChild(newSection);

            // If we have section data, update the fields after adding to DOM
            if (sectionData) {
                const titleField = newSection.querySelector('[data-input-name^="section_title"]');
                const titleInput = newSection.querySelector('input[name^="section_title"]');
                const descField = newSection.querySelector('[data-input-name^="section_description"]');
                const descInput = newSection.querySelector('input[name^="section_description"]');

                if (titleField && titleInput && sectionData.title) {
                    titleField.innerHTML = sectionData.title;
                    titleInput.value = sectionData.title;
                }

                if (descField && descInput && sectionData.description) {
                    descField.innerHTML = sectionData.description;
                    descInput.value = sectionData.description;
                }
            }
        } else {
            console.error('sectionBlocks element not found');
            return;
        }

        // Add event listeners for contenteditable fields
        const editableFields = newSection.querySelectorAll('.editable-field');
        editableFields.forEach(field => {
            // Update hidden input when content changes
            field.addEventListener('input', function() {
                const hiddenInput = this.nextElementSibling;
                if (hiddenInput && hiddenInput.type === 'hidden') {
                    hiddenInput.value = this.innerHTML;
                }
            });

            // Show toolbar on click
            field.addEventListener('click', function(e) {
                e.stopPropagation();
                currentField = this;
                toolbar.classList.add('active');
                positionToolbar(this);
            });
        });

        // Add delete button functionality
        const deleteButton = newSection.querySelector('.delete-button');
        if (deleteButton) {
            deleteButton.addEventListener('click', function() {
                if (confirm('Are you sure you want to delete this section?')) {
                    newSection.remove();
                }
            });
        }

        attachEventListeners();
        sectionCounter++; // Increment counter after section is added
    }

    // Add Question functionality
    function addQuestionToSection(sectionId) {
        // Remove 'new_' prefix if it exists
        const cleanSectionId = sectionId.replace('new_', '');
        const questionContainer = document.getElementById(`question-container-${cleanSectionId}`);
        
        if (!questionContainer) {
            console.error(`Question container not found for section ${sectionId}`);
            return;
        }

        const questionIndex = questionContainer.children.length;

        const newQuestion = document.createElement('div');
        newQuestion.classList.add('question-block');
        newQuestion.style.marginBottom = '20px';
        newQuestion.style.padding = '15px';
        newQuestion.style.border = '1px solid #ddd';
        newQuestion.style.borderRadius = '8px';

        newQuestion.innerHTML = `
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <div class="form-control editable-field question-field" 
                    contenteditable="true" 
                    data-placeholder="Enter your question here"
                    data-input-name="question_text[${cleanSectionId}][${questionIndex}]"
                    style="flex: 1; margin-right: 10px; min-height: 100px; cursor: text;"
                ></div>
                <div style="min-width: 200px;">
                    <select class="form-control question-type-select" name="question_type[${cleanSectionId}][${questionIndex}]">
                        <option value="">Select Question Type</option>
                        <option value="multiple_choice">Multiple Choice</option>
                        <option value="true_false">True/False</option>
                        <option value="programming">Programming</option>
                    </select>
                </div>
                <button type="button" class="btn btn-link text-danger delete-question-btn" style="padding: 5px;">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
            <div class="question-options" style="margin-top: 10px;"></div>
            <div style="margin-top: 10px;">
                <input type="number" name="points[${cleanSectionId}][${questionIndex}]" 
                    class="form-control" placeholder="Points" style="width: 100px;">
            </div>
        `;

        questionContainer.appendChild(newQuestion);

        // Add event listener for question type selection
        const questionTypeSelect = newQuestion.querySelector('.question-type-select');
        questionTypeSelect.addEventListener('change', function() {
            handleQuestionTypeChange(this, cleanSectionId, questionIndex);
        });

        // Add event listener for delete question button
        const deleteQuestionBtn = newQuestion.querySelector('.delete-question-btn');
        deleteQuestionBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this question?')) {
                newQuestion.remove();
            }
        });

        // Add click event listener specifically for the question field
        const questionField = newQuestion.querySelector('.question-field');
        questionField.addEventListener('click', function(e) {
            e.stopPropagation();
            currentField = this;
            toolbar.classList.add('active');
            positionToolbar(this);
        });
    }

    // Handle question type change
    function handleQuestionTypeChange(select, sectionId, questionIndex, existingData = null) {
        const optionsContainer = select.closest('.question-block').querySelector('.question-options');
        optionsContainer.innerHTML = '';

        switch (select.value) {
            case 'true_false':
                optionsContainer.innerHTML = `
                    <div class="form-group">
                        <label>Correct Answer:</label>
                        <select class="form-control" name="correct_answer[${sectionId}][${questionIndex}]" style="width: 200px;">
                            <option value="true" ${existingData && existingData.correct_answer === 'true' ? 'selected' : ''}>True</option>
                            <option value="false" ${existingData && existingData.correct_answer === 'false' ? 'selected' : ''}>False</option>
                        </select>
                    </div>
                `;
                break;

            case 'multiple_choice':
                optionsContainer.innerHTML = `
                    <div class="multiple-choice-options">
                        <button type="button" class="btn btn-secondary add-option-btn" style="margin-bottom: 10px;">
                            Add Option
                        </button>
                    </div>
                `;

                const addOptionBtn = optionsContainer.querySelector('.add-option-btn');
                addOptionBtn.addEventListener('click', () => addMultipleChoiceOption(optionsContainer, sectionId, questionIndex));
                
                // Load existing options if available
                if (existingData && existingData.options) {
                    existingData.options.forEach((option, index) => {
                        const optionsDiv = optionsContainer.querySelector('.multiple-choice-options');
                        const optionDiv = document.createElement('div');
                        optionDiv.classList.add('option-container');
                        optionDiv.style.marginBottom = '10px';
                        optionDiv.innerHTML = `
                            <div class="input-group">
                                <input type="text" class="form-control" 
                                    name="options[${sectionId}][${questionIndex}][]" 
                                    value="${option.text}"
                                    placeholder="Option ${index + 1}">
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <input type="radio" name="correct_answer[${sectionId}][${questionIndex}]" 
                                            value="${index}" ${option.is_correct == 1 ? 'checked' : ''}>
                                        <label style="margin-left: 5px;">Correct</label>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-link text-danger remove-option-btn" style="padding: 5px;">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        `;
                        
                        // Insert before the Add Option button
                        const addButton = optionsContainer.querySelector('.add-option-btn');
                        optionsDiv.insertBefore(optionDiv, addButton);

                        // Add event listener for remove button
                        optionDiv.querySelector('.remove-option-btn').addEventListener('click', function() {
                            optionDiv.remove();
                        });
                    });
                } else {
                    // Add initial options for new questions
                    addMultipleChoiceOption(optionsContainer, sectionId, questionIndex);
                    addMultipleChoiceOption(optionsContainer, sectionId, questionIndex);
                }
                break;

            case 'programming':
                optionsContainer.innerHTML = `
                    <div class="programming-options">
                        <select class="form-control" name="programming_language[${sectionId}][${questionIndex}]" style="width: 200px; margin-bottom: 10px;">
                            <option value="python" ${existingData && existingData.programming_language && existingData.programming_language.language_name === 'python' ? 'selected' : ''}>Python</option>
                            <option value="java" ${existingData && existingData.programming_language && existingData.programming_language.language_name === 'java' ? 'selected' : ''}>Java</option>
                            <option value="c" ${existingData && existingData.programming_language && existingData.programming_language.language_name === 'c' ? 'selected' : ''}>C</option>
                        </select>
                        <div class="test-cases"></div>
                        <button type="button" class="btn btn-secondary add-test-case-btn" style="margin-top: 10px;">
                            Add Test Case
                        </button>
                    </div>
                `;
                const addTestCaseBtn = optionsContainer.querySelector('.add-test-case-btn');
                addTestCaseBtn.addEventListener('click', () => addTestCase(optionsContainer, sectionId, questionIndex));

                // Load existing test cases if available
                if (existingData && existingData.test_cases) {
                    existingData.test_cases.forEach(testCase => {
                        const testCasesDiv = optionsContainer.querySelector('.test-cases');
                        const testCaseDiv = document.createElement('div');
                        testCaseDiv.classList.add('test-case');
                        testCaseDiv.style.marginBottom = '10px';
                        testCaseDiv.innerHTML = `
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" 
                                    name="test_case_input[${sectionId}][${questionIndex}][]" 
                                    value="${testCase.test_input}" readonly>
                                <input type="text" class="form-control" 
                                    name="test_case_output[${sectionId}][${questionIndex}][]" 
                                    value="${testCase.expected_output}"
                                    placeholder="Expected Output">
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <input type="checkbox" 
                                            name="test_case_hidden[${sectionId}][${questionIndex}][]" 
                                            class="test-case-hidden"
                                            ${testCase.is_hidden ? 'checked' : ''}
                                            title="Hidden Test Case">
                                        <label class="ms-2 mb-0">Hidden</label>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-link text-danger remove-test-case-btn" style="padding: 5px;">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        `;
                        
                        testCasesDiv.appendChild(testCaseDiv);

                        // Add event listener for remove button
                        testCaseDiv.querySelector('.remove-test-case-btn').addEventListener('click', function() {
                            testCaseDiv.remove();
                        });
                    });
                } else {
                    // Add initial test case for new questions
                    addTestCase(optionsContainer, sectionId, questionIndex);
                }
                break;
        }
    }

    // Add multiple choice option
    function addMultipleChoiceOption(optionsContainer, sectionId, questionIndex) {
        const optionsDiv = optionsContainer.querySelector('.multiple-choice-options');
        const optionIndex = optionsDiv.querySelectorAll('.option-container').length;
        
        const optionDiv = document.createElement('div');
        optionDiv.classList.add('option-container');
        optionDiv.style.marginBottom = '10px';
        optionDiv.innerHTML = `
            <div class="input-group">
                <input type="text" class="form-control option-text" 
                    name="options[${sectionId}][${questionIndex}][]" 
                    value="Option ${optionIndex + 1}"
                    placeholder="Option ${optionIndex + 1}">
                <div class="input-group-append">
                    <div class="input-group-text">
                        <input type="radio" name="correct_answer[${sectionId}][${questionIndex}]" 
                            value="${optionIndex}">
                        <label style="margin-left: 5px;">Correct</label>
                    </div>
                </div>
                <button type="button" class="btn btn-link text-danger remove-option-btn" style="padding: 5px;">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        `;
        
        // Insert before the Add Option button
        const addButton = optionsContainer.querySelector('.add-option-btn');
        optionsDiv.insertBefore(optionDiv, addButton);

        // Add event listener for remove button
        optionDiv.querySelector('.remove-option-btn').addEventListener('click', function() {
            optionDiv.remove();
        });
    }

    // Update the addTestCase function to show the hidden test case option in the initial view
    function addTestCase(container, sectionId, questionIndex) {
        const testCasesDiv = container.querySelector('.test-cases');
        const testCaseDiv = document.createElement('div');
        testCaseDiv.classList.add('test-case');
        testCaseDiv.style.marginBottom = '10px';
        testCaseDiv.innerHTML = `
            <div class="input-group mb-2">
                <input type="text" class="form-control" 
                    name="test_case_input[${sectionId}][${questionIndex}][]" 
                    placeholder="Input">
                <input type="text" class="form-control" 
                    name="test_case_output[${sectionId}][${questionIndex}][]" 
                    placeholder="Expected Output">
                <div class="input-group-append">
                    <div class="input-group-text">
                        <input type="checkbox" 
                            name="test_case_hidden[${sectionId}][${questionIndex}][]" 
                            class="test-case-hidden"
                            title="Hidden Test Case">
                        <label class="ms-2 mb-0">Hidden</label>
                    </div>
                </div>
                <button type="button" class="btn btn-link text-danger remove-test-case-btn" style="padding: 5px;">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
            <div class="hidden-test-case-description" style="display: none;">
                <div class="alert alert-warning">
                    <small><i class="fas fa-info-circle"></i> This is a hidden test case. Students won't see the input/output.</small>
                </div>
                <input type="text" class="form-control" 
                    name="test_case_description[${sectionId}][${questionIndex}][]" 
                    placeholder="Description (optional, shown to students for hidden test cases)">
            </div>
        `;

        testCasesDiv.appendChild(testCaseDiv);

        // Add event listeners
        const hiddenCheckbox = testCaseDiv.querySelector('.test-case-hidden');
        const descriptionDiv = testCaseDiv.querySelector('.hidden-test-case-description');
        const inputGroup = testCaseDiv.querySelector('.input-group');
        
        if (hiddenCheckbox && descriptionDiv) {
            hiddenCheckbox.addEventListener('change', function() {
                const isNowHidden = this.checked;
                descriptionDiv.style.display = isNowHidden ? 'block' : 'none';
                inputGroup.classList.toggle('hidden-test-case', isNowHidden);
                
                // Update the eye icon and warning background
                const prependDiv = inputGroup.querySelector('.input-group-prepend');
                if (isNowHidden) {
                    if (!prependDiv) {
                        inputGroup.insertAdjacentHTML('afterbegin', 
                            '<div class="input-group-prepend"><span class="input-group-text bg-warning text-dark"><i class="fas fa-eye-slash"></i></span></div>'
                        );
                    }
                    inputGroup.querySelector('.input-group-text').classList.add('bg-warning');
                } else {
                    if (prependDiv) prependDiv.remove();
                    inputGroup.querySelector('.input-group-text').classList.remove('bg-warning');
                }
            });
        }

        testCaseDiv.querySelector('.remove-test-case-btn').addEventListener('click', function() {
            testCaseDiv.remove();
        });
    }

    // Update the handleProgrammingImport function to include the "Add Test Case" button
    function handleProgrammingImport(questionData, sectionId, questionIndex, questionElement) {
        const optionsContainer = questionElement.querySelector('.question-options');
        optionsContainer.innerHTML = `
            <div class="programming-options">
                <select class="form-control mb-3" name="programming_language[${sectionId}][${questionIndex}]">
                    <option value="python" ${questionData.programming_language === 'python' ? 'selected' : ''}>Python</option>
                    <option value="java" ${questionData.programming_language === 'java' ? 'selected' : ''}>Java</option>
                    <option value="c" ${questionData.programming_language === 'c' ? 'selected' : ''}>C</option>
                </select>
                <div class="test-cases mt-3">
                    ${questionData.test_cases ? questionData.test_cases.map(test => `
                        <div class="test-case mb-2">
                            <div class="input-group">
                                <input type="text" class="form-control" 
                                    name="test_case_input[${sectionId}][${questionIndex}][]" 
                                    value="${test.test_input}" readonly>
                                <input type="text" class="form-control" 
                                    name="test_case_output[${sectionId}][${questionIndex}][]" 
                                    value="${test.expected_output}" readonly>
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <input type="checkbox" 
                                            name="test_case_hidden[${sectionId}][${questionIndex}][]" 
                                            class="test-case-hidden"
                                            ${test.is_hidden ? 'checked' : ''}
                                            title="Hidden Test Case">
                                        <label class="ms-2 mb-0">Hidden</label>
                                    </div>
                                </div>
                            </div>
                            ${test.is_hidden ? `
                            <div class="hidden-test-case-description" style="display: block;">
                                <input type="text" class="form-control" 
                                    name="test_case_description[${sectionId}][${questionIndex}][]" 
                                    value="${test.description || ''}"
                                    placeholder="Description (optional, shown to students for hidden test cases)">
                            </div>
                            ` : ''}
                        </div>
                    `).join('') : ''}
                </div>
                <button type="button" class="btn btn-secondary add-test-case-btn mt-2" onclick="addTestCase(this.closest('.programming-options'), ${sectionId}, ${questionIndex})">
                    Add Test Case
                </button>
            </div>
        `;
    }

    // Event Listeners
    if (addSectionBtn) {
        addSectionBtn.addEventListener('click', function(e) {
            e.preventDefault();
            addSection();
            closeActionSidebar();
        });
    } else {
        console.error('Add Section button not found');
    }

    if (globalAddQuestionBtn) {
        globalAddQuestionBtn.addEventListener('click', () => {
            const sections = document.querySelectorAll('.section-block');
            if (sections.length === 0) {
                alert('Please add a section first before adding questions.');
                return;
            }
            
            const lastSection = sections[sections.length - 1];
            const sectionId = lastSection.getAttribute('data-section-id');
            if (sectionId) {
                addQuestionToSection(sectionId);
                closeActionSidebar();
            } else {
                console.error('Section ID not found');
            }
        });
    }

    if (importQuestionsBtn) {
        importQuestionsBtn.addEventListener('click', function() {
            try {
                const questionBankModal = document.getElementById('questionBankModal');
                if (!questionBankModal) {
                    console.error('Question bank modal not found');
                    return;
                }
                const modal = new bootstrap.Modal(questionBankModal, {
                    backdrop: 'static',
                    keyboard: false
                });
                loadCategories(); // Load categories first
                loadQuestionBank(); // Then load questions
                modal.show();
            } catch (error) {
                console.error('Error showing modal:', error);
            }
        });
    }

    if (searchQuestion) {
        searchQuestion.addEventListener('input', debounce(function() {
            loadQuestionBank(this.value);
        }, 300));
    }

    if (importSelectedQuestionsBtn) {
        importSelectedQuestionsBtn.addEventListener('click', importSelectedQuestions);
    }

    // Toggle action buttons
    if (showActionSidebarBtn && actionButtons) {
        showActionSidebarBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            showActionSidebarBtn.classList.toggle('active');
            actionButtons.classList.toggle('active');
        });
    }

    // Hide buttons when clicking outside
    document.addEventListener('click', (e) => {
        if (!actionButtons.contains(e.target) && 
            !showActionSidebarBtn.contains(e.target)) {
            actionButtons.classList.remove('active');
            showActionSidebarBtn.classList.remove('active');
        }
    });

    // Prevent clicks inside buttons from closing
    actionButtons.addEventListener('click', (e) => {
        e.stopPropagation();
    });

    // Button Click Handlers
    if (saveFormBtn) {
        saveFormBtn.addEventListener('click', () => {
            saveForm();
            closeActionSidebar();
        });
    }

    // Helper Functions
    function closeActionSidebar() {
        actionButtons.classList.remove('active');
        showActionSidebarBtn.classList.remove('active');
    }

    function saveForm() {
        const exam_id = new URLSearchParams(window.location.search).get('exam_id');
        console.log('Saving with exam_id:', exam_id);

        if (!exam_id) {
            alert('No exam ID found. Please make sure you are editing a valid exam.');
            return;
        }

        const sections = [];
        const sectionBlocks = document.querySelectorAll('.section-block');
        
        sectionBlocks.forEach((sectionBlock, sectionIndex) => {
            const sectionId = sectionBlock.getAttribute('data-section-id');
            const titleElement = sectionBlock.querySelector('[data-input-name^="section_title"]');
            const descriptionElement = sectionBlock.querySelector('[data-input-name^="section_description"]');
            
            const section = {
                section_id: sectionId.startsWith('new_') ? null : sectionId,
                exam_id: parseInt(exam_id),
                title: titleElement ? titleElement.innerHTML.trim() : '',
                description: descriptionElement ? descriptionElement.innerHTML.trim() : '',
                order: sectionIndex,
                questions: []
            };

            // Get questions for this section
            const questionBlocks = sectionBlock.querySelectorAll('.question-block');
            questionBlocks.forEach((questionBlock, questionIndex) => {
                const questionData = {
                    question_id: questionBlock.getAttribute('data-original-question-id') || null,
                    section_id: sectionId.startsWith('new_') ? null : sectionId,
                    question_text: questionBlock.querySelector('.question-field')?.innerHTML || '',
                    question_type: questionBlock.querySelector('.question-type-select')?.value || '',
                    points: parseInt(questionBlock.querySelector('input[name^="points"]')?.value) || 0,
                    order: questionIndex
                };

                // Handle different question types
                switch (questionData.question_type) {
                    case 'multiple_choice':
                        const options = [];
                        questionBlock.querySelectorAll('.option-container').forEach((optionContainer, optionIndex) => {
                            const optionInput = optionContainer.querySelector('input[type="text"]');
                            console.log('Option input element:', optionInput); // Debug log
                            const optionText = optionInput?.value;
                            const isCorrect = optionContainer.querySelector('input[type="radio"]')?.checked;
                            
                            if (optionText?.trim() !== '') {  // Only add non-empty options
                                options.push({
                                    text: optionText,         // This should match what save_question.php expects
                                    is_correct: isCorrect ? 1 : 0,
                                    order: optionIndex
                                });
                            }
                        });
                        console.log('Saving options:', options);
                        questionData.options = options;
                        break;

                    case 'true_false':
                        const correctAnswerSelect = questionBlock.querySelector('select[name^="correct_answer"]');
                        if (correctAnswerSelect) {
                            questionData.correct_answer = correctAnswerSelect.value;
                        }
                        break;

                    case 'programming':
                        const testCases = [];
                        const testCaseContainers = questionBlock.querySelectorAll('.test-case');
                        
                        console.log('Found test cases:', testCaseContainers.length); // Debug log
                        
                        testCaseContainers.forEach((testCase, testIndex) => {
                            // Safely get input and output values
                            const inputElement = testCase.querySelector('input[name^="test_case_input"]');
                            const outputElement = testCase.querySelector('input[name^="test_case_output"]');
                            const hiddenElement = testCase.querySelector('input[name^="test_case_hidden"]');
                            const descriptionElement = testCase.querySelector('input[name^="test_case_description"]');

                            console.log('Test case elements:', { // Debug log
                                input: inputElement?.value,
                                output: outputElement?.value,
                                hidden: hiddenElement?.checked,
                                description: descriptionElement?.value
                            });

                            if (inputElement && outputElement) {
                                const testCase = {
                                    test_input: inputElement.value,
                                    expected_output: outputElement.value,
                                    is_hidden: hiddenElement ? hiddenElement.checked : false,
                                    description: descriptionElement ? descriptionElement.value : '',
                                    order: testIndex
                                };
                                console.log('Adding test case:', testCase); // Debug log
                                testCases.push(testCase);
                            }
                        });
                        
                        // Add programming language
                        const languageSelect = questionBlock.querySelector('select[name^="programming_language"]');
                        questionData.programming_language = languageSelect ? languageSelect.value : 'python';
                        questionData.test_cases = testCases;
                        break;
                }

                section.questions.push(questionData);
            });

            sections.push(section);
        });

        // Log the options before sending to the server
        const questionsData = sections.map(section => {
            return {
                section_id: section.section_id,
                questions: section.questions.map(question => {
                    console.log('Question data before sending:', question);
                    return {
                        question_id: question.question_id,
                        question_text: question.question_text,
                        question_type: question.question_type,
                        options: question.options,
                        points: question.points,
                        order: question.order
                    };
                })
            };
        });
        console.log('Final data to send:', JSON.stringify({ exam_id, sections: questionsData }));

        const data = {
            exam_id: parseInt(exam_id),
            action: 'save_sections',
            sections: sections,
            deleted_questions: window.deletedQuestions || []
        };

        console.log('Sending data:', data);

        fetch('save_question.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            console.log('Response from server:', data); // Debug log
            if (data.success) {
                window.deletedQuestions = [];
                alert('Questions saved successfully!');
                window.location.reload();
            } else {
                alert('Error saving questions: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while saving.');
        });
    }

    function attachEventListeners() {
        // Add event listeners for dynamic elements
        document.querySelectorAll('.delete-question-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to delete this question?')) {
                    const questionBlock = this.closest('.question-block');
                    const questionId = questionBlock.getAttribute('data-original-question-id');
                    
                    if (questionId && questionId !== 'null' && questionId !== 'undefined') {
                        // Send delete request to server
                        fetch('delete_question.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                question_id: questionId
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                questionBlock.remove();
                                console.log('Question deleted successfully');
                            } else {
                                alert('Error deleting question: ' + (data.error || 'Unknown error'));
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while deleting the question.');
                        });
                    } else {
                        // If it's a new question that hasn't been saved yet
                        questionBlock.remove();
                    }
                }
            });
        });
    }

    // Add these event listeners after your existing ones
    document.getElementById('import-questions-btn').addEventListener('click', function() {
        try {
            const questionBankModal = document.getElementById('questionBankModal');
            if (!questionBankModal) {
                console.error('Question bank modal not found');
                return;
            }
            const modal = new bootstrap.Modal(questionBankModal, {
                backdrop: 'static',
                keyboard: false
            });
            loadCategories(); // Load categories first
            loadQuestionBank(); // Then load questions
            modal.show();
        } catch (error) {
            console.error('Error showing modal:', error);
        }
    });

    document.getElementById('searchQuestion').addEventListener('input', debounce(function() {
        loadQuestionBank(this.value);
    }, 300));

    document.getElementById('importSelectedQuestions').addEventListener('click', importSelectedQuestions);

    // Add these functions (keep only one copy)
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    function loadQuestionBank(search = '') {
        const categorySelect = document.getElementById('categorySelect');
        const questionList = document.getElementById('questionBankList');
        const questionBankModal = document.getElementById('questionBankModal');
        
        // Check if required elements exist
        if (!questionList || !questionBankModal) {
            console.error('Required elements not found:', {
                questionList: !!questionList,
                questionBankModal: !!questionBankModal
            });
            return;
        }

        const category = categorySelect ? categorySelect.value : '';
        const url = `fetch_question_bank.php?search=${encodeURIComponent(search)}&category=${encodeURIComponent(category)}`;
        
        // Show loading state
        questionList.innerHTML = `
            <tr>
                <td colspan="4" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    Loading questions...
                </td>
            </tr>
        `;

        // Add loading class to modal
        questionBankModal.classList.add('loading');
        
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (!data.questions || data.questions.length === 0) {
                    questionList.innerHTML = `
                        <tr>
                            <td colspan="4" class="text-center py-3">
                                <i class="fas fa-info-circle me-2"></i>
                                No questions found in this category
                            </td>
                        </tr>`;
                    return;
                }
                
                questionList.innerHTML = data.questions.map(question => `
                    <tr>
                        <td><input type="checkbox" value="${question.question_id}" data-question='${JSON.stringify(question)}'></td>
                        <td>${question.question_text}</td>
                        <td>${question.question_type}</td>
                        <td>${question.points || 0}</td>
                    </tr>
                `).join('');

                // Reattach event listeners for checkboxes
                attachCheckboxListeners();
            })
            .catch(error => {
                console.error('Error loading questions:', error);
                if (questionList) {
                    questionList.innerHTML = `
                        <tr>
                            <td colspan="4" class="text-center text-danger py-3">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                Error loading questions. Please try again.
                            </td>
                        </tr>`;
                }
            })
            .finally(() => {
                // Remove loading class if modal exists
                if (questionBankModal) {
                    questionBankModal.classList.remove('loading');
                }
            });
    }

    // Add null check for checkbox listeners
    function attachCheckboxListeners() {
        const checkboxes = document.querySelectorAll('#questionBankList input[type="checkbox"]');
        if (!checkboxes.length) {
            console.warn('No checkboxes found to attach listeners to');
            return;
        }
        
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectionCounter);
        });
    }

    function updateSelectionCounter() {
        const counter = document.getElementById('selectionCounter');
        if (!counter) {
            console.warn('Selection counter element not found');
            return;
        }
        
        const count = document.querySelectorAll('#questionBankList input[type="checkbox"]:checked').length;
        counter.textContent = `${count} question${count !== 1 ? 's' : ''} selected`;
    }

    function importSelectedQuestions() {
        const selectedQuestions = document.querySelectorAll('#questionBankList input[type="checkbox"]:checked');
        if (selectedQuestions.length === 0) {
            alert('Please select at least one question');
            return;
        }

        // Show importing progress
        const importBtn = document.getElementById('importSelectedQuestions');
        const originalText = importBtn.textContent;
        importBtn.disabled = true;
        importBtn.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            Importing...
        `;

        try {
            // Your existing import code here
            const sections = document.querySelectorAll('.section-block');
            if (sections.length === 0) {
                throw new Error('Please create a section first');
            }
            
            // ... rest of your import code ...

            bootstrap.Modal.getInstance(document.getElementById('questionBankModal')).hide();
        } catch (error) {
            alert(error.message);
        } finally {
            importBtn.disabled = false;
            importBtn.textContent = originalText;
        }
    }

    // Add these event listeners in your existing DOMContentLoaded function
    document.querySelectorAll('input[name="importType"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.getElementById('manualSelectSection').style.display = 
                this.value === 'manual' ? 'block' : 'none';
            document.getElementById('autoGenerateSection').style.display = 
                this.value === 'auto' ? 'block' : 'none';
        });
    });
}); // Close the DOMContentLoaded event listener