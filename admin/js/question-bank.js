// Define functions in global scope
let currentPage = 1;
let questionBankModal;
let searchInput;
let categoryFilter;
let questionsList;
let paginationContainer;
let selectAllCheckbox;
let importButton;

// Initialize everything when the DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeQuestionBank();
});

function initializeQuestionBank() {
    // Initialize modal functionality
    questionBankModal = document.getElementById('questionBankModal');
    if (!questionBankModal) {
        console.error('Question bank modal not found');
        return;
    }

    searchInput = document.getElementById('searchQuestion');
    categoryFilter = document.getElementById('categoryFilter');
    questionsList = document.getElementById('questionBankList');
    paginationContainer = document.getElementById('questionsPagination');
    selectAllCheckbox = document.getElementById('selectAll');
    importButton = document.getElementById('importSelectedQuestions');

    // Initialize the import questions button
    const importQuestionsBtn = document.getElementById('import-questions-btn');
    if (importQuestionsBtn) {
        importQuestionsBtn.addEventListener('click', function() {
            const modal = new bootstrap.Modal(questionBankModal);
            modal.show();
            // Load categories first, then questions will be loaded after categories
            loadCategories();
        });
    }

    // Add event listeners only if elements exist
    if (searchInput) {
        searchInput.addEventListener('input', debounce(() => loadQuestions(1), 300));
    }
    if (categoryFilter) {
        categoryFilter.addEventListener('change', () => loadQuestions(1));
    }
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', toggleAllQuestions);
    }
    if (importButton) {
        importButton.addEventListener('click', importSelectedQuestions);
    }
}

// Function to load categories
async function loadCategories() {
    try {
        const response = await fetch('fetch_categories.php');
        const data = await response.json();
        
        if (!categoryFilter) {
            console.error('Category filter element not found');
            return;
        }

        if (data.success && Array.isArray(data.categories)) {
            categoryFilter.innerHTML = '<option value="">All Categories</option>';
            data.categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category;
                option.textContent = category;
                categoryFilter.appendChild(option);
            });
            
            // Load questions after categories are loaded
            loadQuestions(1);
        } else {
            console.error('Failed to load categories:', data.error || 'Unknown error');
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

// Function to load questions
async function loadQuestions(page = 1) {
    try {
        if (!searchInput || !categoryFilter) {
            console.error('Search input or category filter not found');
            return;
        }

        const searchTerm = encodeURIComponent(searchInput.value || '');
        const category = encodeURIComponent(categoryFilter.value || '');
        
        console.log('Loading questions for page:', page);
        
        const response = await fetch(`fetch_questions.php?page=${page}&search=${searchTerm}&category=${category}`);
        const data = await response.json();

        if (data.success && Array.isArray(data.questions)) {
            renderQuestions(data.questions);
            if (data.totalPages) {
                renderPagination(data.totalPages, page);
            }
        } else {
            console.error('Failed to load questions:', data.error || 'Unknown error');
        }
    } catch (error) {
        console.error('Error loading questions:', error);
    }
}

// Function to render questions
function renderQuestions(questions) {
    if (!questionsList) {
        console.error('Questions list element not found');
        return;
    }

    questionsList.innerHTML = questions.map(question => `
        <tr>
            <td>
                <input type="checkbox" class="question-checkbox" 
                       value="${question.question_id}" 
                       data-question='${JSON.stringify(question)}'>
            </td>
            <td>${escapeHtml(question.question_text)}</td>
            <td>${escapeHtml(question.question_type)}</td>
            <td>${escapeHtml(question.category || 'N/A')}</td>
        </tr>
    `).join('');
}

// Function to import selected questions
function importSelectedQuestions() {
    const selectedCheckboxes = document.querySelectorAll('.question-checkbox:checked');
    if (selectedCheckboxes.length === 0) {
        alert('Please select at least one question to import.');
        return;
    }

    try {
        const selectedQuestions = Array.from(selectedCheckboxes).map(checkbox => {
            const questionData = JSON.parse(checkbox.dataset.question);
            return questionData;
        });

        // Add questions to the exam
        selectedQuestions.forEach(question => {
            addQuestionToExam(question);
        });

        // Close the modal
        const modal = bootstrap.Modal.getInstance(questionBankModal);
        if (modal) {
            modal.hide();
        }
    } catch (error) {
        console.error('Error importing questions:', error);
        alert('Failed to import questions. Please try again.');
    }
}

// Keep your existing helper functions (escapeHtml, debounce, etc.)
// and the question creation functions (createQuestionHtml, etc.)
// as they are...

function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return unsafe
        .toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

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

function toggleAllQuestions(event) {
    const checkboxes = document.querySelectorAll('.question-checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = event.target.checked);
}

function addQuestionToExam(question) {
    console.log('Adding question to exam:', question);
    
    // Get or create a section block
    let sectionBlock = document.querySelector('.section-block');
    if (!sectionBlock) {
        const sectionBlocks = document.getElementById('sectionBlocks');
        if (!sectionBlocks) {
            console.error('Section blocks container not found');
            return;
        }
        
        // Create new section using the structure from test2.js
        sectionBlocks.insertAdjacentHTML('beforeend', `
            <div class="section-block">
                <div class="title-block">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="form-control editable-field section-field" 
                            contenteditable="true" 
                            data-placeholder="Untitled Section"
                            data-input-name="section_title[]"
                            style="flex: 1; margin-right: 10px;">Imported Questions</div>
                        <input type="hidden" name="section_title[]" value="Imported Questions">
                        <button type="button" class="delete-button btn btn-link text-danger" style="padding: 5px;">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="description-block">
                    <div class="form-control editable-field section-field" 
                        contenteditable="true" 
                        data-placeholder="Description (optional)"
                        data-input-name="section_description[]"></div>
                    <input type="hidden" name="section_description[]" value="">
                </div>
                <div id="question-container-${Date.now()}" class="question-block-container"></div>
            </div>
        `);
        sectionBlock = sectionBlocks.lastElementChild;
    }

    // Get the questions container using the correct class name
    const questionsContainer = sectionBlock.querySelector('.question-block-container');
    if (!questionsContainer) {
        console.error('Questions container not found');
        return;
    }

    // Generate a unique section ID and question index
    const sectionId = Date.now(); // Use timestamp as temporary ID
    const questionIndex = questionsContainer.children.length;

    // Create the question block using the structure from test2.js
    const questionBlock = document.createElement('div');
    questionBlock.className = 'question-block';
    questionBlock.style.marginBottom = '20px';
    questionBlock.style.padding = '15px';
    questionBlock.style.border = '1px solid #ddd';
    questionBlock.style.borderRadius = '8px';

    questionBlock.innerHTML = `
        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
            <div class="form-control editable-field question-field" 
                contenteditable="true" 
                data-placeholder="Enter your question here"
                data-input-name="question_text[${sectionId}][${questionIndex}]"
                style="flex: 1; margin-right: 10px; min-height: 100px; cursor: text;"
            >${escapeHtml(question.question_text)}</div>
            <div style="min-width: 200px;">
                <select class="form-control question-type-select" name="question_type[${sectionId}][${questionIndex}]">
                    <option value="multiple_choice" ${question.question_type === 'multiple_choice' ? 'selected' : ''}>Multiple Choice</option>
                    <option value="true_false" ${question.question_type === 'true_false' ? 'selected' : ''}>True/False</option>
                    <option value="programming" ${question.question_type === 'programming' ? 'selected' : ''}>Programming</option>
                </select>
            </div>
            <button type="button" class="btn btn-link text-danger delete-question-btn" style="padding: 5px;">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>
        <div class="question-options" style="margin-top: 10px;"></div>
        <div style="margin-top: 10px;">
            <input type="number" name="points[${sectionId}][${questionIndex}]" 
                class="form-control" placeholder="Points" style="width: 100px;">
        </div>
    `;

    // Add the question block to the container
    questionsContainer.appendChild(questionBlock);

    // Initialize the question based on its type
    const questionData = {
        question_text: question.question_text,
        question_type: question.question_type,
        category: question.category,
        correct_answer: question.correct_answer,
        choices: question.choices,
        programming_details: question.programming_details,
        test_cases: question.test_cases
    };

    // Call the appropriate handler from test2.js
    if (typeof window.handleQuestionTypeChange === 'function') {
        const select = questionBlock.querySelector('.question-type-select');
        window.handleQuestionTypeChange(select, sectionId, questionIndex, questionData);
    } else {
        console.error('handleQuestionTypeChange function not found');
    }

    // Add delete functionality
    const deleteBtn = questionBlock.querySelector('.delete-question-btn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this question?')) {
                questionBlock.remove();
            }
        });
    }
}

function createNewSection() {
    const sectionBlocks = document.getElementById('sectionBlocks');
    const sectionHtml = `
        <div class="section-block">
            <div class="section-header">
                <input type="text" class="section-title" placeholder="Section Title" value="Imported Questions">
                <textarea class="section-description" placeholder="Section Description"></textarea>
            </div>
            <div class="questions-container">
            </div>
        </div>
    `;
    sectionBlocks.insertAdjacentHTML('beforeend', sectionHtml);
    return sectionBlocks.lastElementChild;
}

function createQuestionHtml(question) {
    let questionHtml = `
        <div class="question-block" data-question-type="${question.question_type}">
            <input type="hidden" name="question_type[]" value="${question.question_type}">
            <input type="hidden" name="question_id[]" value="${question.question_id}">
            <div class="question-header">
                <textarea class="question-text" name="question_text[]" rows="3">${escapeHtml(question.question_text)}</textarea>
            </div>
    `;

    // Add type-specific HTML
    switch (question.question_type) {
        case 'multiple_choice':
            questionHtml += createMultipleChoiceHtml(question);
            break;
        case 'true_false':
            questionHtml += createTrueFalseHtml(question);
            break;
        case 'programming':
            questionHtml += createProgrammingHtml(question);
            break;
    }

    questionHtml += `
            <div class="question-footer">
                <button type="button" class="btn btn-danger btn-sm delete-question">Delete</button>
            </div>
        </div>
    `;

    return questionHtml;
}

function createMultipleChoiceHtml(question) {
    let html = '<div class="multiple-choice-options">';
    if (question.choices && question.choices.length > 0) {
        question.choices.forEach((choice, index) => {
            html += `
                <div class="option-container">
                    <div class="input-group mb-2">
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <input type="radio" name="correct_answer_${question.question_id}[]" 
                                       value="${index}" ${choice.is_correct ? 'checked' : ''}>
                            </div>
                        </div>
                        <input type="text" class="form-control" name="options_${question.question_id}[]" 
                               value="${escapeHtml(choice.text)}">
                        <button type="button" class="btn btn-danger delete-option">×</button>
                    </div>
                </div>
            `;
        });
    }
    html += `
        <button type="button" class="btn btn-link add-option-btn">+ Add Option</button>
    </div>`;
    return html;
}

function createTrueFalseHtml(question) {
    return `
        <div class="true-false-options">
            <div class="form-check">
                <input type="radio" class="form-check-input" name="correct_answer_${question.question_id}" 
                       value="true" ${question.correct_answer === 'true' ? 'checked' : ''}>
                <label class="form-check-label">True</label>
            </div>
            <div class="form-check">
                <input type="radio" class="form-check-input" name="correct_answer_${question.question_id}" 
                       value="false" ${question.correct_answer === 'false' ? 'checked' : ''}>
                <label class="form-check-label">False</label>
            </div>
        </div>
    `;
}

function createProgrammingHtml(question) {
    let html = `
        <div class="programming-question">
            <textarea class="form-control mb-2" name="problem_description[]" 
                      placeholder="Problem Description" rows="4">${escapeHtml(question.programming_details?.problem_description || '')}</textarea>
            <select class="form-control mb-2" name="programming_language[]">
                <option value="python" ${question.programming_details?.programming_language === 'python' ? 'selected' : ''}>Python</option>
                <option value="java" ${question.programming_details?.programming_language === 'java' ? 'selected' : ''}>Java</option>
                <option value="cpp" ${question.programming_details?.programming_language === 'cpp' ? 'selected' : ''}>C++</option>
            </select>
    `;

    if (question.test_cases && question.test_cases.length > 0) {
        question.test_cases.forEach(testCase => {
            html += `
                <div class="test-case">
                    <input type="text" class="form-control mb-2" name="test_input[]" 
                           placeholder="Test Input" value="${escapeHtml(testCase.input)}">
                    <input type="text" class="form-control mb-2" name="expected_output[]" 
                           placeholder="Expected Output" value="${escapeHtml(testCase.expected_output)}">
                    <textarea class="form-control mb-2" name="explanation[]" 
                              placeholder="Explanation">${escapeHtml(testCase.explanation || '')}</textarea>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="is_hidden[]" 
                               value="1" ${testCase.is_hidden ? 'checked' : ''}>
                        <label class="form-check-label">Hidden Test Case</label>
                    </div>
                </div>
            `;
        });
    }

    html += `
            <button type="button" class="btn btn-link add-test-case-btn">+ Add Test Case</button>
        </div>
    `;
    return html;
}

function initializeQuestionHandlers(questionElement, question) {
    // Add delete handler
    const deleteBtn = questionElement.querySelector('.delete-question');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this question?')) {
                questionElement.remove();
            }
        });
    }

    // Add option handlers for multiple choice
    if (question.question_type === 'multiple_choice') {
        const addOptionBtn = questionElement.querySelector('.add-option-btn');
        if (addOptionBtn) {
            addOptionBtn.addEventListener('click', function() {
                const optionsContainer = questionElement.querySelector('.multiple-choice-options');
                const newOption = createNewOptionHtml(question.question_id);
                optionsContainer.insertBefore(newOption, addOptionBtn);
            });
        }

        // Add delete option handlers
        const deleteOptionBtns = questionElement.querySelectorAll('.delete-option');
        deleteOptionBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                btn.closest('.option-container').remove();
            });
        });
    }

    // Add test case handlers for programming questions
    if (question.question_type === 'programming') {
        const addTestCaseBtn = questionElement.querySelector('.add-test-case-btn');
        if (addTestCaseBtn) {
            addTestCaseBtn.addEventListener('click', function() {
                const newTestCase = createNewTestCaseHtml();
                addTestCaseBtn.insertAdjacentHTML('beforebegin', newTestCase);
            });
        }
    }
}

function createNewOptionHtml(questionId) {
    const optionContainer = document.createElement('div');
    optionContainer.className = 'option-container';
    optionContainer.innerHTML = `
        <div class="input-group mb-2">
            <div class="input-group-prepend">
                <div class="input-group-text">
                    <input type="radio" name="correct_answer_${questionId}[]" value="">
                </div>
            </div>
            <input type="text" class="form-control" name="options_${questionId}[]" value="">
            <button type="button" class="btn btn-danger delete-option">×</button>
        </div>
    `;
    return optionContainer;
}

function createNewTestCaseHtml() {
    return `
        <div class="test-case">
            <input type="text" class="form-control mb-2" name="test_input[]" placeholder="Test Input">
            <input type="text" class="form-control mb-2" name="expected_output[]" placeholder="Expected Output">
            <textarea class="form-control mb-2" name="explanation[]" placeholder="Explanation"></textarea>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="is_hidden[]" value="1">
                <label class="form-check-label">Hidden Test Case</label>
            </div>
        </div>
    `;
}

// Add this function after renderQuestions function
function renderPagination(totalPages, page = 1) {
    if (!paginationContainer) {
        console.error('Pagination container not found');
        return;
    }

    currentPage = page;

    let paginationHtml = '';
    for (let i = 1; i <= totalPages; i++) {
        paginationHtml += `
            <button type="button" 
                    class="page-button ${i === currentPage ? 'active' : ''}"
                    onclick="window.loadQuestions(${i})">
                ${i}
            </button>
        `;
    }
    paginationContainer.innerHTML = paginationHtml;
}

// Make loadQuestions globally accessible
window.loadQuestions = async function(page = 1) {
    try {
        if (!searchInput || !categoryFilter) {
            console.error('Search input or category filter not found');
            return;
        }

        const searchTerm = encodeURIComponent(searchInput.value || '');
        const category = encodeURIComponent(categoryFilter.value || '');
        
        console.log('Loading questions for page:', page);
        
        const response = await fetch(`fetch_questions.php?page=${page}&search=${searchTerm}&category=${category}`);
        const data = await response.json();

        if (data.success && Array.isArray(data.questions)) {
            renderQuestions(data.questions);
            if (data.totalPages) {
                renderPagination(data.totalPages, page);
            }
        } else {
            console.error('Failed to load questions:', data.error || 'Unknown error');
        }
    } catch (error) {
        console.error('Error loading questions:', error);
    }
}; 