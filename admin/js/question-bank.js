// Define functions in global scope
let currentPage = 1;
let questionBankModal;
let searchInput;
const categorySelect = document.getElementById('qbCategorySelect');
const questionsList = document.getElementById('qbQuestionsList');
const paginationContainer = document.getElementById('qbPagination');
const selectAllCheckbox = document.getElementById('qbSelectAll');
const importButton = document.getElementById('qbImportSelectedBtn');

// Add the debounce function at the top of the file
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

// Initialize everything when the DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeQuestionBank();
});

async function loadQuestionBank(search = '') {
    try {
        const questionsList = document.getElementById('qbQuestionsList');
        if (!questionsList) {
            console.error('Questions list container not found');
            return;
        }

        questionsList.innerHTML = '<tr><td colspan="4" class="text-center">Loading...</td></tr>';

        const response = await fetch(`fetch_question_bank.php${search ? `?search=${encodeURIComponent(search)}` : ''}`);
        if (!response.ok) throw new Error('Network response was not ok');
        
        const data = await response.json();
        console.log('Received data:', data);

        if (data.success && Array.isArray(data.questions)) {
            if (data.questions.length === 0) {
                questionsList.innerHTML = '<tr><td colspan="4" class="text-center">No questions found</td></tr>';
                return;
            }

            questionsList.innerHTML = data.questions.map(question => `
                <tr>
                    <td style="width: 40px;">
                        <input type="checkbox" 
                            class="question-checkbox" 
                            value="${question.question_id}"
                            data-question='${JSON.stringify(question)}'>
                    </td>
                    <td>${question.question_text}</td>
                    <td>${question.question_type}</td>
                    <td>${question.category || 'Uncategorized'}</td>
                </tr>
            `).join('');

            questionsList.querySelectorAll('.question-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectionCounter);
            });
        } else {
            throw new Error(data.error || 'Failed to load questions');
        }
    } catch (error) {
        console.error('Error loading questions:', error);
        questionsList.innerHTML = `
            <tr>
                <td colspan="4" class="text-center text-danger">
                    Error loading questions. Please try again.
                </td>
            </tr>
        `;
    }
}

// Make sure initializeQuestionBank is called when needed
function initializeQuestionBank() {
    // Add event listeners for search
    const searchInput = document.getElementById('qbSearchQuestion');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function() {
            loadQuestionBank(this.value);
        }, 300));
    }

    // Add event listener for select all checkbox
    const selectAllCheckbox = document.getElementById('qbSelectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('#qbQuestionsList input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectionCounter();
        });
    }

    // Add event listener for import button
    const importBtn = document.getElementById('qbImportSelectedBtn');
    if (importBtn) {
        importBtn.addEventListener('click', importSelectedQuestions);
    }
}

// Function to load categories
async function loadCategories() {
    try {
        console.log('Fetching categories...');
        // Use absolute path
        const response = await fetch('../admin/fetch_categories.php');
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Categories response:', data);

        if (!categorySelect) {
            console.error('Category select element not found. Element ID:', 'categorySelect');
            // Log the current state of the DOM
            console.log('Current modal content:', document.getElementById('questionBankModal')?.innerHTML);
            return;
        }

        if (data.success && Array.isArray(data.categories)) {
            console.log('Loading categories:', data.categories);
            categorySelect.innerHTML = '<option value="">All Categories</option>';
            data.categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category;
                option.textContent = category;
                categorySelect.appendChild(option);
            });
            
            // Load questions after categories are loaded
            loadQuestions(1);
        } else {
            console.error('Failed to load categories:', data.error || 'Unknown error');
            // Show error in the select element
            categorySelect.innerHTML = '<option value="">Error loading categories</option>';
        }
    } catch (error) {
        console.error('Error loading categories:', error);
        if (categorySelect) {
            categorySelect.innerHTML = '<option value="">Error loading categories</option>';
        }
    }
}

// Function to load questions
async function loadQuestions(page = 1) {
    try {
        const searchInput = document.getElementById('qbSearchQuestion');
        const categorySelect = document.getElementById('qbCategorySelect');
        const questionsList = document.getElementById('qbQuestionsList');

        if (!questionsList) {
            console.error('Questions list container not found');
            return;
        }

        // Show loading state
        questionsList.innerHTML = '<tr><td colspan="4" class="text-center">Loading...</td></tr>';

        const searchTerm = searchInput ? searchInput.value : '';
        const category = categorySelect ? categorySelect.value : '';

        const response = await fetch(`fetch_question_bank.php?page=${page}&search=${encodeURIComponent(searchTerm)}&category=${encodeURIComponent(category)}`);
        const data = await response.json();

        if (data.success) {
            renderQuestions(data.questions);
        } else {
            questionsList.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error loading questions</td></tr>';
        }
    } catch (error) {
        console.error('Error loading questions:', error);
        const questionsList = document.getElementById('qbQuestionsList');
        if (questionsList) {
            questionsList.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error loading questions</td></tr>';
        }
    }
}

// Function to render questions
function renderQuestions(questions) {
    const questionsList = document.getElementById('questionBankList');
    if (!questionsList) return;

    questionsList.innerHTML = questions.map(question => {
        // Prepare the question data to be stored
        const questionData = {
            question_id: question.question_id,
            question_text: question.question_text,
            question_type: question.question_type,
            category: question.category,
            options: question.options || [], // This will now match the database structure
            points: question.points || 0,
            // Include any programming-specific data if needed
            test_cases: question.test_cases || []
        };

        // Debug log
        console.log('Processing question:', questionData);

        return `
            <tr>
                <td>
                    <input type="checkbox" 
                        class="question-checkbox" 
                        value="${question.question_id}"
                        data-question='${JSON.stringify(questionData)}'>
                </td>
                <td>
                    ${escapeHtml(question.question_text)}
                    ${question.question_type === 'multiple_choice' && question.options ? `
                        <div class="options-preview" style="
                            margin-top: 5px;
                            font-size: 0.9em;
                            color: #666;
                        ">
                            ${question.options.map(option => `
                                <div class="option-preview" style="
                                    padding: 2px 5px;
                                    ${option.is_correct ? 'color: #28a745; font-weight: bold;' : ''}
                                ">
                                    ${option.is_correct ? '✓ ' : ''}${escapeHtml(option.option_text)}
                                </div>
                            `).join('')}
                        </div>
                    ` : ''}
                </td>
                <td>${question.question_type}</td>
                <td>${question.category || 'Uncategorized'}</td>
            </tr>
        `;
    }).join('');

    // Add event listener for checkboxes
    questionsList.querySelectorAll('.question-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectionCounter);
    });
}

// Function to import selected questions
function importSelectedQuestions() {
    const selectedQuestions = Array.from(
        document.querySelectorAll('#qbQuestionsList input[type="checkbox"]:checked')
    ).map(checkbox => JSON.parse(checkbox.getAttribute('data-question')));

    if (selectedQuestions.length === 0) {
        alert('Please select at least one question to import.');
        return;
    }

    selectedQuestions.forEach(question => {
        console.log('Importing question:', question);
        // Add logic to add the question to the exam
        addQuestionToExam(question);
    });

    // Ensure the modal is closed properly
    const modalElement = document.getElementById('qbModal');
    const modal = bootstrap.Modal.getInstance(modalElement);
    if (modal) {
        modal.hide();
    }

    // Remove any remaining modal backdrop
    document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());

    // Reset modal state
    resetModalState();
}

// Helper function to safely get text content
function getTextContent(element, defaultValue = '') {
    return element ? element.textContent.trim() : defaultValue;
}

function addQuestionToExam(question) {
    console.log('Adding question:', question);
    console.log('Question type:', question.question_type);
    console.log('Options:', question.options);

    const sectionId = document.querySelector('.section-block').getAttribute('data-section-id');
    const questionsContainer = document.querySelector(`#question-container-${sectionId}`);
    const questionIndex = questionsContainer.children.length;

    const questionBlock = document.createElement('div');
    questionBlock.classList.add('qb-question-block');
    questionBlock.setAttribute('data-question-type', question.question_type);
    questionBlock.setAttribute('data-question-id', question.question_id);

    // Apply styles directly to the question block
    Object.assign(questionBlock.style, {
        backgroundColor: '#ffffff',
        border: '1px solid #e9ecef',
        borderRadius: '8px',
        padding: '15px',
        marginBottom: '20px',
        boxShadow: '0 2px 5px rgba(0, 0, 0, 0.1)',
        transition: 'all 0.3s ease'
    });

    // Set up the question structure with toolbar
    questionBlock.innerHTML = `
        <input type="hidden" name="question_type[${sectionId}][${questionIndex}]" value="${question.question_type}">
        <input type="hidden" name="question_id[${sectionId}][${questionIndex}]" value="${question.question_id}">
        <div style="display: flex; justify-content: space-between; align-items: start;">
            <div style="flex: 1; margin-right: 10px;">
                <div class="toolbar" style="
                    display: none;
                    margin-bottom: 5px;
                    background: #fff;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    padding: 5px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    z-index: 1000;
                ">
                    <button type="button" class="toolbar-btn" data-command="bold" style="
                        background: none;
                        border: 1px solid transparent;
                        padding: 4px 8px;
                        margin: 0 2px;
                        cursor: pointer;
                        border-radius: 3px;
                        transition: all 0.2s ease;
                    ">
                        <i class="fas fa-bold"></i>
                    </button>
                    <button type="button" class="toolbar-btn" data-command="italic" style="
                        background: none;
                        border: 1px solid transparent;
                        padding: 4px 8px;
                        margin: 0 2px;
                        cursor: pointer;
                        border-radius: 3px;
                        transition: all 0.2s ease;
                    ">
                        <i class="fas fa-italic"></i>
                    </button>
                    <button type="button" class="toolbar-btn" data-command="underline" style="
                        background: none;
                        border: 1px solid transparent;
                        padding: 4px 8px;
                        margin: 0 2px;
                        cursor: pointer;
                        border-radius: 3px;
                        transition: all 0.2s ease;
                    ">
                        <i class="fas fa-underline"></i>
                    </button>
                    <span style="margin: 0 5px; color: #ddd;">|</span>
                    <button type="button" class="toolbar-btn" data-command="insertUnorderedList" style="
                        background: none;
                        border: 1px solid transparent;
                        padding: 4px 8px;
                        margin: 0 2px;
                        cursor: pointer;
                        border-radius: 3px;
                        transition: all 0.2s ease;
                    ">
                        <i class="fas fa-list-ul"></i>
                    </button>
                    <button type="button" class="toolbar-btn" data-command="insertOrderedList" style="
                        background: none;
                        border: 1px solid transparent;
                        padding: 4px 8px;
                        margin: 0 2px;
                        cursor: pointer;
                        border-radius: 3px;
                        transition: all 0.2s ease;
                    ">
                        <i class="fas fa-list-ol"></i>
                    </button>
                </div>
                <div class="form-control editable-field question-field" 
                    contenteditable="true" 
                    data-placeholder="Enter your question here"
                    data-input-name="question_text[${sectionId}][${questionIndex}]"
                    style="
                        flex: 1;
                        min-height: 100px;
                        padding: 12px;
                        border: 1px solid #ddd;
                        border-radius: 4px;
                        margin-bottom: 10px;
                        cursor: text;
                        transition: border-color 0.2s ease;
                        line-height: 1.5;
                    "
                >${question.question_text || ''}</div>
            </div>
            <div style="display: flex; align-items: start;">
                <div style="min-width: 150px; margin-right: 10px;">
                    <select class="form-control question-type-select" 
                        name="question_type[${sectionId}][${questionIndex}]" 
                        disabled
                        style="
                            width: 100%;
                            padding: 8px;
                            border: 1px solid #ddd;
                            border-radius: 4px;
                            background-color: #f8f9fa;
                        ">
                        <option value="multiple_choice" selected>Multiple Choice</option>
                    </select>
                </div>
                <button type="button" class="btn btn-link text-danger delete-question-btn" style="
                    padding: 5px;
                    background: none;
                    border: none;
                    color: #dc3545;
                    cursor: pointer;
                    transition: transform 0.2s ease;
                ">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </div>
        <div class="question-options" style="margin-top: 10px;"></div>
        <div style="margin-top: 10px;">
            <input type="number" 
                name="points[${sectionId}][${questionIndex}]" 
                class="form-control" 
                placeholder="Points" 
                style="
                    width: 100px;
                    padding: 8px;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    transition: border-color 0.2s ease;
                "
                value="${question.points || 0}">
        </div>
    `;

    // Add hover effects for toolbar buttons
    questionBlock.querySelectorAll('.toolbar-btn').forEach(button => {
        button.addEventListener('mouseover', () => {
            button.style.backgroundColor = '#f0f0f0';
            button.style.borderColor = '#ddd';
        });

        button.addEventListener('mouseout', () => {
            button.style.backgroundColor = 'transparent';
            button.style.borderColor = 'transparent';
        });
    });

    // Add hover effect for delete button
    const deleteQuestionBtn = questionBlock.querySelector('.delete-question-btn');
    deleteQuestionBtn.addEventListener('mouseover', () => {
        deleteQuestionBtn.style.transform = 'scale(1.1)';
    });
    deleteQuestionBtn.addEventListener('mouseout', () => {
        deleteQuestionBtn.style.transform = 'scale(1)';
    });

    // Add toolbar functionality
    const questionField = questionBlock.querySelector('.question-field');
    const toolbar = questionBlock.querySelector('.toolbar');

    questionField.addEventListener('focus', function() {
        toolbar.style.display = 'block';
    });

    questionField.addEventListener('blur', function(e) {
        // Don't hide if clicking toolbar
        if (!e.relatedTarget || !e.relatedTarget.closest('.toolbar')) {
            toolbar.style.display = 'none';
        }
    });

    // Add toolbar button functionality
    questionBlock.querySelectorAll('.toolbar-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const command = this.getAttribute('data-command');
            document.execCommand(command, false, null);
            questionField.focus();
        });
    });

    // Add delete question functionality
    if (deleteQuestionBtn) {
        deleteQuestionBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this question?')) {
                questionBlock.remove();
            }
        });
    }

    // Update the options handling code
    if (question.question_type === 'multiple_choice') {
        const optionsContainer = questionBlock.querySelector('.question-options');
        
        if (question.choices && Array.isArray(question.choices)) {
            optionsContainer.innerHTML = `
                <div class="multiple-choice-options" style="margin-top: 15px;">
                    ${question.choices.map((option, index) => `
                        <div class="option-container" style="margin-bottom: 10px;">
                            <div class="input-group">
                                <input type="text" 
                                    class="form-control" 
                                    name="options[${sectionId}][${questionIndex}][]" 
                                    value="${option.choice_text || ''}"
                                    readonly>
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <input type="radio" 
                                            name="correct_option[${sectionId}][${questionIndex}]" 
                                            value="${index}"
                                            ${option.is_correct ? 'checked' : ''}>
                                        <label style="margin-left: 5px;">Correct</label>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-link text-danger remove-option-btn">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    `).join('')}
                    <button type="button" class="btn btn-secondary add-option-btn">
                        Add Option
                    </button>
                </div>
            `;

            // Add event listeners for the option buttons
            optionsContainer.querySelectorAll('.remove-option-btn').forEach(button => {
                button.addEventListener('click', function() {
                    this.closest('.option-container').remove();
                });
            });

            const addOptionBtn = optionsContainer.querySelector('.add-option-btn');
            if (addOptionBtn) {
                addOptionBtn.addEventListener('click', () => {
                    addMultipleChoiceOption(optionsContainer, sectionId, questionIndex);
                });
            }
        }
    }

    questionsContainer.appendChild(questionBlock);
    return questionBlock;
}

function createMultipleChoiceOptions(question, sectionId, questionIndex) {
    // Make sure we handle all possible option formats
    let options = [];
    if (question.options && Array.isArray(question.options)) {
        options = question.options;
    } else if (question.choices && Array.isArray(question.choices)) {
        options = question.choices;
    }

    // If no options, create at least one empty option
    if (options.length === 0) {
        options = [{ option_text: '', is_correct: false }];
    }

    const optionsHtml = options.map((option, idx) => {
        // Handle different property names for option text
        const optionText = option.option_text || option.text || option.choice_text || '';
        const isCorrect = option.is_correct === true || option.is_correct === 1;
        
        return `
            <div class="option-container">
                <div class="qb-input-group">
                    <div class="qb-input-group-prepend">
                        <div class="qb-input-group-text">
                            <input type="radio" 
                                name="qb_correct_answer[${sectionId}][${questionIndex}]" 
                                value="${idx}"
                                ${isCorrect ? 'checked' : ''}>
                            <label>Correct</label>
                        </div>
                    </div>
                    <input type="text" 
                        class="qb-form-control" 
                        name="qb_options[${sectionId}][${questionIndex}][]" 
                        value="${optionText}"
                        placeholder="Option ${idx + 1}">
                    <button type="button" class="qb-delete-option-btn">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>
        `;
    }).join('');

    return `
        <div class="multiple-choice-options">
            ${optionsHtml}
            <button type="button" class="add-option-btn">+ Add Option</button>
        </div>
    `;
}

// Add this function to handle adding new options
function addOptionEventListener(questionBlock) {
    const addOptionBtn = questionBlock.querySelector('.add-option-btn');
    const optionsContainer = questionBlock.querySelector('.multiple-choice-options');
    
    if (addOptionBtn && optionsContainer) {
        addOptionBtn.addEventListener('click', () => {
            const newOptionIndex = optionsContainer.querySelectorAll('.option-container').length;
            const sectionId = questionBlock.closest('.section-block').getAttribute('data-section-id');
            const questionIndex = Array.from(questionBlock.parentNode.children).indexOf(questionBlock);
            
            const newOptionHtml = `
                <div class="option-container">
                    <div class="qb-input-group">
                        <div class="qb-input-group-prepend">
                            <div class="qb-input-group-text">
                                <input type="radio" 
                                    name="qb_correct_option[${sectionId}][${questionIndex}]" 
                                    value="${newOptionIndex}">
                                <label>Correct</label>
                            </div>
                        </div>
                        <input type="text" 
                            class="qb-form-control" 
                            name="qb_options[${sectionId}][${questionIndex}][]" 
                            placeholder="Enter option text"
                            style="
                                border: 1px solid #dee2e6;
                                border-radius: 4px 0 0 4px;
                                border-right: none;
                            ">
                        <button type="button" class="qb-delete-option-btn" style="
                            border: 1px solid #dee2e6;
                            border-left: none;
                            border-radius: 0 4px 4px 0;
                            padding: 0.375rem 0.75rem;
                        ">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            `;
            
            // Insert the new option before the Add Option button
            addOptionBtn.insertAdjacentHTML('beforebegin', newOptionHtml);
            
            // Add event listener to the new delete button
            const newDeleteBtn = addOptionBtn.previousElementSibling.querySelector('.qb-delete-option-btn');
            if (newDeleteBtn) {
                newDeleteBtn.addEventListener('click', function() {
                    this.closest('.option-container').remove();
                });
            }
        });
    }
}

function createNewOptionHtml(questionId, sectionId, questionIndex) {
    const optionContainer = document.createElement('div');
    optionContainer.className = 'option-container';
    optionContainer.innerHTML = `
        <div class="qb-input-group">
            <div class="qb-input-group-prepend">
                <div class="qb-input-group-text">
                    <input type="radio" 
                        name="qb_correct_option[${sectionId}][${questionIndex}]" 
                        value="">
                    <label>Correct</label>
                </div>
            </div>
            <input type="text" 
                class="qb-form-control" 
                name="qb_options[${sectionId}][${questionIndex}][]" 
                placeholder="New Option">
            <button type="button" class="qb-delete-option">×</button>
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

// Function to render pagination
function renderPagination(totalPages, currentPage) {
    const paginationContainer = document.getElementById('questionsPagination');
    
    if (!paginationContainer) {
        console.error('Pagination container (questionsPagination) not found');
        return;
    }

    // Clear existing pagination
    paginationContainer.innerHTML = '';

    // Create pagination HTML
    let paginationHtml = '<ul class="pagination justify-content-center">';

    // Previous button
    paginationHtml += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
        </li>
    `;

    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        paginationHtml += `
            <li class="page-item ${currentPage === i ? 'active' : ''}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
            </li>
        `;
    }

    // Next button
    paginationHtml += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
        </li>
    `;

    paginationHtml += '</ul>';
    paginationContainer.innerHTML = paginationHtml;

    // Add click handlers for pagination
    paginationContainer.querySelectorAll('.page-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const page = parseInt(this.getAttribute('data-page'));
            if (!isNaN(page) && page > 0 && page <= totalPages) {
                loadQuestions(page);
            }
        });
    });
}

// Helper function to escape HTML
function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Add selection counter update function
function updateSelectionCounter() {
    const selectedCount = document.querySelectorAll('#qbQuestionsList input[type="checkbox"]:checked').length;
    const counterElement = document.getElementById('qbSelectionCounter');
    if (counterElement) {
        counterElement.textContent = `${selectedCount} questions selected`;
    }
}

// Make loadQuestions globally accessible
window.loadQuestions = async function(page = 1) {
    try {
        const searchInput = document.getElementById('searchQuestion');
        const categorySelect = document.getElementById('categorySelect');
        const questionsList = document.getElementById('questionBankList');

        if (!questionsList) {
            console.error('Questions list container not found');
            return;
        }

        const searchTerm = searchInput ? encodeURIComponent(searchInput.value || '') : '';
        const category = categorySelect ? encodeURIComponent(categorySelect.value || '') : '';
        
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
            questionsList.innerHTML = '<tr><td colspan="4">Error loading questions</td></tr>';
        }
    } catch (error) {
        console.error('Error loading questions:', error);
        const questionsList = document.getElementById('questionBankList');
        if (questionsList) {
            questionsList.innerHTML = '<tr><td colspan="4">Error loading questions</td></tr>';
        }
    }
};

// Add these new helper functions
function cleanupModalArtifacts() {
    const modalElement = document.getElementById('questionBankModal');
    
    // Force remove backdrop
    document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
    
    const modal = bootstrap.Modal.getInstance(modalElement);
    if (modal) {
        modal.hide();
        
        // Reset modal state immediately
        modalElement.classList.remove('show');
        modalElement.style.display = 'none';
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');
    }
    
    // Reset modal state
    resetModalState();
}

function resetModalState() {
    if (questionsList) {
        questionsList.innerHTML = '';
    }
    if (categorySelect) {
        categorySelect.innerHTML = '<option value="">All Categories</option>';
    }
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = false;
    }
    if (searchInput) {
        searchInput.value = '';
    }
} 