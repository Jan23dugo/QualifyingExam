// Define functions in global scope
let currentPage = 1;
let questionBankModal;
let searchInput;
let categorySelect;
let questionsList;
let paginationContainer;
let selectAllCheckbox;
let importButton;

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

function initializeQuestionBank() {
    // Get modal elements using the correct IDs from your HTML
    const modalElement = document.getElementById('questionBankModal');
    const searchInput = document.getElementById('searchQuestion');
    const categorySelect = document.getElementById('categorySelect');
    const questionsList = document.getElementById('questionBankList');
    const paginationContainer = document.getElementById('questionsPagination');
    const importButton = document.getElementById('importSelectedQuestions');
    const selectAllCheckbox = document.getElementById('selectAll');

    // Remove any existing backdrop when initializing
    document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
    
    // Remove fade class from modal if it exists
    modalElement.classList.remove('fade');
    
    // Initialize Bootstrap modal with specific options
    const modal = new bootstrap.Modal(modalElement, {
        backdrop: false,
        keyboard: true,
        focus: true
    });

    // Add mutation observer to remove backdrop if it gets added
    const observer = new MutationObserver((mutations) => {
        document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

    // Add modal hidden event listener for cleanup
    modalElement.addEventListener('hidden.bs.modal', function () {
        // Force remove any remaining backdrop
        document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
        
        // Reset body styles
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');
    });

    // Debug logging
    console.log('Modal:', modalElement);
    console.log('Search:', searchInput);
    console.log('Category:', categorySelect);
    console.log('List:', questionsList);
    console.log('Pagination:', paginationContainer);

    // Initialize search functionality
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function() {
            loadQuestions(1);
        }, 300));
    } else {
        console.error('Search input not found');
    }

    // Initialize category filter
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            loadQuestions(1);
        });
    } else {
        console.error('Category select not found');
    }

    // Initialize import button with proper event handling
    if (importButton) {
        // Remove any existing event listeners
        importButton.replaceWith(importButton.cloneNode(true));
        
        // Get the fresh reference
        const newImportButton = document.getElementById('importSelectedQuestions');
        
        // Add the event listener
        newImportButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            importSelectedQuestions();
        });
    }

    // Initialize select all functionality
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = questionsList.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectionCounter();
        });
    }

    // Load initial questions
    loadQuestions(1);
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
}

// Function to render questions
function renderQuestions(questions) {
    const questionsList = document.getElementById('questionBankList');
    
    if (!questionsList) {
        console.error('Questions list element not found');
        return;
    }

    questionsList.innerHTML = '';

    if (questions.length === 0) {
        questionsList.innerHTML = '<tr><td colspan="4">No questions found</td></tr>';
        return;
    }

    questions.forEach(question => {
        const row = document.createElement('tr');
        const questionData = {
            question_id: question.question_id,
            question_text: question.question_text,
            question_type: question.question_type,
            category: question.category,
            options: question.options || question.choices || [],
            points: question.points || 0
        };

        row.innerHTML = `
            <td>
                <input type="checkbox" class="question-checkbox" 
                    value="${question.question_id}" 
                    data-question='${JSON.stringify(questionData)}'>
            </td>
            <td>${escapeHtml(question.question_text)}</td>
            <td>${escapeHtml(question.question_type)}</td>
            <td>${escapeHtml(question.category || 'N/A')}</td>
        `;
        questionsList.appendChild(row);
    });

    updateSelectionCounter();
}

// Function to import selected questions
function importSelectedQuestions() {
    const selectedCheckboxes = document.querySelectorAll('#questionBankList input[type="checkbox"]:checked');
    
    if (selectedCheckboxes.length === 0) {
        alert('Please select at least one question to import.');
        return;
    }

    // Get the section container
    const sectionId = document.querySelector('.section-block').getAttribute('data-section-id');
    const questionsContainer = document.querySelector(`#question-container-${sectionId}`);

    // Get existing question IDs
    const existingQuestionIds = new Set(
        Array.from(questionsContainer.querySelectorAll('input[name^="question_id"]'))
            .map(input => input.value)
    );

    let addedCount = 0;

    // Process each selected checkbox one at a time
    selectedCheckboxes.forEach(checkbox => {
        try {
            const questionData = JSON.parse(checkbox.dataset.question);
            
            // Skip if question already exists
            if (existingQuestionIds.has(questionData.question_id.toString())) {
                console.log('Skipping duplicate question:', questionData.question_id);
                return;
            }

            // Add the question
            const added = addQuestionToExam(questionData);
            if (added) {
                addedCount++;
                existingQuestionIds.add(questionData.question_id.toString());
            }
        } catch (e) {
            console.error('Error processing question:', e);
        }
    });

    // Show success message if any questions were added
    if (addedCount > 0) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'alert alert-success';
        messageDiv.style.position = 'fixed';
        messageDiv.style.top = '20px';
        messageDiv.style.right = '20px';
        messageDiv.style.zIndex = '9999';
        messageDiv.textContent = `${addedCount} question(s) imported successfully`;
        document.body.appendChild(messageDiv);

        setTimeout(() => messageDiv.remove(), 3000);
    }

    // Clear checkboxes
    selectedCheckboxes.forEach(checkbox => checkbox.checked = false);
    
    // Reset select all checkbox
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = false;
    }

    // Update selection counter
    updateSelectionCounter();
}

// Helper function to safely get text content
function getTextContent(element, defaultValue = '') {
    return element ? element.textContent.trim() : defaultValue;
}

function addQuestionToExam(question) {
    const sectionId = document.querySelector('.section-block').getAttribute('data-section-id');
    const questionsContainer = document.querySelector(`#question-container-${sectionId}`);
    const questionIndex = questionsContainer.children.length;

    // Create a new question block
    const questionBlock = document.createElement('div');
    questionBlock.classList.add('question-block');
    questionBlock.setAttribute('data-question-type', question.question_type);
    questionBlock.setAttribute('data-question-id', question.question_id);

    // Set up the question structure with toolbar
    questionBlock.innerHTML = `
        <input type="hidden" name="question_type[${sectionId}][${questionIndex}]" value="${question.question_type}">
        <input type="hidden" name="question_id[${sectionId}][${questionIndex}]" value="${question.question_id}">
        <div style="display: flex; justify-content: space-between; align-items: start;">
            <div style="flex: 1; margin-right: 10px;">
                <div class="toolbar" style="display: none; margin-bottom: 5px;">
                    <button type="button" class="toolbar-btn" data-command="bold">
                        <i class="fas fa-bold"></i>
                    </button>
                    <button type="button" class="toolbar-btn" data-command="italic">
                        <i class="fas fa-italic"></i>
                    </button>
                    <button type="button" class="toolbar-btn" data-command="underline">
                        <i class="fas fa-underline"></i>
                    </button>
                    <span class="toolbar-separator">|</span>
                    <button type="button" class="toolbar-btn" data-command="insertUnorderedList">
                        <i class="fas fa-list-ul"></i>
                    </button>
                    <button type="button" class="toolbar-btn" data-command="insertOrderedList">
                        <i class="fas fa-list-ol"></i>
                    </button>
                </div>
                <div class="form-control editable-field question-field" 
                    contenteditable="true" 
                    data-placeholder="Enter your question here"
                    data-input-name="question_text[${sectionId}][${questionIndex}]"
                >${question.question_text || ''}</div>
            </div>
            <div style="display: flex; align-items: start;">
                <div style="min-width: 150px; margin-right: 10px;">
                    <select class="form-control question-type-select" 
                        name="question_type[${sectionId}][${questionIndex}]" disabled>
                        <option value="multiple_choice" selected>Multiple Choice</option>
                    </select>
                </div>
                <button type="button" class="btn btn-link text-danger delete-question-btn" style="padding: 5px;">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </div>
        <div class="question-options" style="margin-top: 10px;">
            ${question.question_type === 'multiple_choice' ? createMultipleChoiceOptions(question, sectionId, questionIndex) : ''}
        </div>
        <div style="margin-top: 10px;">
            <input type="number" name="points[${sectionId}][${questionIndex}]" 
                class="form-control" placeholder="Points" style="width: 100px;"
                value="${question.points || 0}">
        </div>
    `;

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
    const deleteBtn = questionBlock.querySelector('.delete-question-btn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this question?')) {
                questionBlock.remove();
            }
        });
    }

    // Add event listeners for the options
    if (question.question_type === 'multiple_choice') {
        // Add event listener for the Add Option button
        addOptionEventListener(questionBlock);
        
        // Add event listeners for existing delete option buttons
        questionBlock.querySelectorAll('.delete-option-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.option-container').remove();
            });
        });
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
                <div class="input-group">
                    <div class="input-group-prepend">
                        <div class="input-group-text">
                            <input type="radio" 
                                name="correct_answer[${sectionId}][${questionIndex}]" 
                                value="${idx}"
                                ${isCorrect ? 'checked' : ''}>
                            <label>Correct</label>
                        </div>
                    </div>
                    <input type="text" 
                        class="form-control" 
                        name="options[${sectionId}][${questionIndex}][]" 
                        value="${optionText}"
                        placeholder="Option ${idx + 1}">
                    <button type="button" class="delete-option-btn">
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
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <input type="radio" 
                                    name="correct_answer[${sectionId}][${questionIndex}]" 
                                    value="${newOptionIndex}">
                                <label>Correct</label>
                            </div>
                        </div>
                        <input type="text" 
                            class="form-control" 
                            name="options[${sectionId}][${questionIndex}][]" 
                            placeholder="Option ${newOptionIndex + 1}">
                        <button type="button" class="delete-option-btn">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            `;
            
            // Insert the new option before the Add Option button
            addOptionBtn.insertAdjacentHTML('beforebegin', newOptionHtml);
            
            // Add event listener to the new delete button
            const newDeleteBtn = addOptionBtn.previousElementSibling.querySelector('.delete-option-btn');
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
        <div class="input-group">
            <div class="input-group-prepend">
                <div class="input-group-text">
                    <input type="radio" 
                        name="correct_option[${sectionId}][${questionIndex}]" 
                        value="">
                    <label>Correct</label>
                </div>
            </div>
            <input type="text" 
                class="form-control" 
                name="options[${sectionId}][${questionIndex}][]" 
                placeholder="New Option">
            <button type="button" class="delete-option">×</button>
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
    const selectedCount = document.querySelectorAll('#questionBankList input[type="checkbox"]:checked').length;
    const counterElement = document.getElementById('selectionCounter');
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