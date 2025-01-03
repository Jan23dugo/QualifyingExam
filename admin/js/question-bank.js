// Define functions in global scope
let currentPage = 1;
let questionBankModal;
let searchInput;
let categorySelect = null;
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
    // Check if SweetAlert2 is loaded
    if (!checkSweetAlert()) {
        console.error('SweetAlert2 is required but not loaded!');
        return;
    }

    // Initialize question bank modal
    const qbModal = document.getElementById('qbModal');
    if (qbModal) {
        const importBtn = document.getElementById('qbImportSelectedBtn');
        if (importBtn) {
            importBtn.addEventListener('click', function() {
                console.log('Import button clicked');
                importSelectedQuestions();
            });
        } else {
            console.error('Import button not found in modal');
        }
    } else {
        console.error('Question bank modal not found');
    }

    // Add click handler for auto generate button
    document.getElementById('autoGenerateBtn')?.addEventListener('click', showAutoGenerateModal);

    // Load categories when modal opens
    $('#qbModal').on('show.bs.modal', loadCategories);
    
    // Auto generate button click handler
    document.getElementById('autoGenerateBtn').addEventListener('click', handleAutoGenerate);
});

async function loadQuestionBank(search = '', category = '') {
    try {
        const questionsList = document.getElementById('qbQuestionsList');
        if (!questionsList) {
            console.error('Questions list container not found');
            return;
        }

        questionsList.innerHTML = '<tr><td colspan="4" class="text-center">Loading...</td></tr>';

        // Get the category value
        const categorySelect = document.getElementById('qbCategorySelect');
        const categoryValue = category || (categorySelect ? categorySelect.value : '');

        const response = await fetch(`fetch_question_bank.php?${new URLSearchParams({
            search: search || '',
            category: categoryValue || ''
        })}`);

        if (!response.ok) throw new Error('Network response was not ok');
        
        const data = await response.json();
        console.log('Received data:', data);

        if (data.success) {
            // Update categories if they're included in the response
            if (data.categories && Array.isArray(data.categories)) {
                updateCategorySelect(data.categories);
            }

            // Render questions
            if (Array.isArray(data.questions)) {
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

                // Add event listeners to checkboxes
                questionsList.querySelectorAll('.question-checkbox').forEach(checkbox => {
                    checkbox.addEventListener('change', updateSelectionCounter);
                });
            }
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

// Add function to update category select
function updateCategorySelect(categories) {
    const categorySelect = document.getElementById('qbCategorySelect');
    if (categorySelect) {
        const currentValue = categorySelect.value;
        categorySelect.innerHTML = '<option value="">All Categories</option>';
        categories.forEach(category => {
            const option = document.createElement('option');
            option.value = category;
            option.textContent = category;
            categorySelect.appendChild(option);
        });
        categorySelect.value = currentValue; // Maintain selected value
    }
}

// Make sure initializeQuestionBank is called when needed
function initializeQuestionBank() {
    // Add event listeners for search
    const searchInput = document.getElementById('qbSearchQuestion');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function() {
            const categorySelect = document.getElementById('qbCategorySelect');
            loadQuestionBank(this.value, categorySelect ? categorySelect.value : '');
        }, 300));
    }

    // Add event listener for category select
    const categorySelect = document.getElementById('qbCategorySelect');
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            const searchInput = document.getElementById('qbSearchQuestion');
            loadQuestionBank(searchInput ? searchInput.value : '', this.value);
        });
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
function loadCategories() {
    console.log('Loading categories...');
    
    fetch('handlers/get_categories.php')
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Categories data:', data);
            
            const select = document.getElementById('qbCategorySelect');
            if (!select) {
                console.error('Category select element not found');
                return;
            }
            
            select.innerHTML = ''; // Clear existing options
            
            if (!data.success) {
                console.error('Error loading categories:', data.message);
                throw new Error(data.message);
            }
            
            // Get categories array from the response data
            const categories = data.categories || [];
            
            if (categories.length === 0) {
                // Add a placeholder option if no categories are found
                const option = document.createElement('option');
                option.value = "";
                option.textContent = "No categories available";
                option.disabled = true;
                select.appendChild(option);
                return;
            }
            
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category;
                option.textContent = category;
                select.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading categories:', error);
            showError('Failed to load categories: ' + error.message);
            
            // Add a placeholder option in case of error
            const select = document.getElementById('qbCategorySelect');
            if (select) {
                select.innerHTML = '<option value="" disabled>Error loading categories</option>';
            }
        });
}

// Call this function when the page loads
document.addEventListener('DOMContentLoaded', loadCategories);

// Add event listener for modal show event
document.addEventListener('DOMContentLoaded', function() {
    const qbModal = document.getElementById('qbModal');
    if (qbModal) {
        const modal = new bootstrap.Modal(qbModal, {
            backdrop: 'static',
            keyboard: true // Allow keyboard navigation
        });

        // Handle focus management
        qbModal.addEventListener('shown.bs.modal', function () {
            // Set focus to the first focusable element
            const firstFocusable = qbModal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            if (firstFocusable) {
                firstFocusable.focus();
            }
        });

        // Handle proper cleanup
        qbModal.addEventListener('hidden.bs.modal', function () {
            // Reset focus to the trigger element if needed
            const trigger = document.querySelector('[data-bs-target="#qbModal"]');
            if (trigger) {
                trigger.focus();
            }
        });
    }
});

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
    try {
        console.log('Starting import of selected questions');
        
        // First check if there's a section to add questions to
        const sectionBlock = document.querySelector('.section-block');
        if (!sectionBlock) {
            console.log('No section found, showing warning...');
            Swal.fire({
                icon: 'warning',
                title: 'No Section Found',
                text: 'Please create a section first before importing questions.',
                confirmButtonText: 'Create Section',
                showCancelButton: true,
                cancelButtonText: 'Cancel',
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-secondary'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const addSectionBtn = document.getElementById('add-section-btn');
                    if (addSectionBtn) {
                        addSectionBtn.click();
                    } else {
                        console.error('Add section button not found');
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Could not create section. Please try again.'
                        });
                    }
                }
            });
            return;
        }

        const sectionId = sectionBlock.getAttribute('data-section-id');
        if (!sectionId) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Section',
                text: 'The selected section appears to be invalid. Please try creating a new section.',
                confirmButtonText: 'OK'
            });
            return;
        }

        // Get checked questions
        const checkedBoxes = document.querySelectorAll('#qbQuestionsList input[type="checkbox"]:checked');
        
        if (checkedBoxes.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Questions Selected',
                text: 'Please select at least one question to import.',
                confirmButtonText: 'OK'
            });
            return;
        }

        // Show loading state
        Swal.fire({
            title: 'Importing Questions',
            text: 'Please wait while we import your selected questions...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Process selected questions
        const selectedQuestions = Array.from(checkedBoxes)
            .map(checkbox => {
                try {
                    const questionData = checkbox.getAttribute('data-question');
                    return questionData ? JSON.parse(questionData) : null;
                } catch (e) {
                    console.error('Error parsing question data:', e);
                    return null;
                }
            })
            .filter(q => q !== null);

        // Add questions to exam with section ID
        selectedQuestions.forEach(question => {
            if (question) {
                try {
                    addQuestionToExam(question, sectionId);
                } catch (e) {
                    console.error('Error adding question to exam:', e, question);
                }
            }
        });

        // Close the modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('qbModal'));
        if (modal) {
            modal.hide();
        }

        // Show success message
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: `${selectedQuestions.length} questions have been imported successfully.`,
            timer: 2000,
            showConfirmButton: false
        });

    } catch (error) {
        console.error('Error in importSelectedQuestions:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while importing questions. Please try again.',
            confirmButtonText: 'OK'
        });
    }
}

// Helper function to safely get text content
function getTextContent(element, defaultValue = '') {
    return element ? element.textContent.trim() : defaultValue;
}

function addQuestionToExam(question) {
    console.log('Adding question:', question);
    console.log('Question type:', question.question_type);
    console.log('Test cases:', question.test_cases);

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
                        <option value="multiple_choice" ${question.question_type === 'multiple_choice' ? 'selected' : ''}>Multiple Choice</option>
                        <option value="true_false" ${question.question_type === 'true_false' ? 'selected' : ''}>True/False</option>
                        <option value="programming" ${question.question_type === 'programming' ? 'selected' : ''}>Programming</option>
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
    if (question.question_type === 'true_false') {
        const optionsContainer = questionBlock.querySelector('.question-options');
        optionsContainer.innerHTML = `
            <div class="true-false-options" style="margin-top: 15px;">
                <div class="form-group">
                    <label>Correct Answer:</label>
                    <select class="form-control" name="correct_answer[${sectionId}][${questionIndex}]" style="width: 200px;">
                        <option value="true" ${question.correct_answer === 'true' ? 'selected' : ''}>True</option>
                        <option value="false" ${question.correct_answer === 'false' ? 'selected' : ''}>False</option>
                    </select>
                </div>
            </div>
        `;
    } else if (question.question_type === 'programming') {
        const optionsContainer = questionBlock.querySelector('.question-options');
        
        optionsContainer.innerHTML = `
            <div class="programming-options" style="margin-top: 15px;">
                <select class="form-control mb-3" name="programming_language[${sectionId}][${questionIndex}]" style="
                    width: 200px;
                    padding: 8px;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    background-color: #f8f9fa;
                ">
                    <option value="python" ${question.programming_language === 'python' ? 'selected' : ''}>Python</option>
                    <option value="java" ${question.programming_language === 'java' ? 'selected' : ''}>Java</option>
                    <option value="c" ${question.programming_language === 'c' ? 'selected' : ''}>C</option>
                </select>
                <div class="test-cases">
                    ${question.test_cases ? question.test_cases.map((test, index) => `
                        <div class="test-case mb-2" style="
                            background-color: ${test.is_hidden ? '#fff8e6' : '#ffffff'};
                            border: 1px solid #e9ecef;
                            border-radius: 8px;
                            padding: 15px;
                            margin-bottom: 15px;
                        ">
                            <div class="input-group" style="margin-bottom: 10px;">
                                <input type="text" class="form-control" 
                                    name="test_case_input[${sectionId}][${questionIndex}][]" 
                                    value="${test.test_input || ''}" 
                                    readonly
                                    style="
                                        border: 1px solid #ddd;
                                        border-radius: 4px 0 0 4px;
                                        padding: 8px 12px;
                                    "
                                    placeholder="Test input">
                                <input type="text" class="form-control" 
                                    name="test_case_output[${sectionId}][${questionIndex}][]" 
                                    value="${test.expected_output || ''}" 
                                    readonly
                                    style="
                                        border: 1px solid #ddd;
                                        border-left: none;
                                        border-right: none;
                                        padding: 8px 12px;
                                    "
                                    placeholder="Expected output">
                                <div class="input-group-append" style="display: flex;">
                                    <div class="input-group-text" style="
                                        background-color: #f8f9fa;
                                        border: 1px solid #ddd;
                                        border-left: none;
                                        padding: 8px 12px;
                                    ">
                                        <input type="checkbox" 
                                            name="test_case_hidden[${sectionId}][${questionIndex}][]" 
                                            ${test.is_hidden ? 'checked' : ''}
                                            disabled>
                                        <label class="ms-2 mb-0">Hidden</label>
                                    </div>
                                </div>
                            </div>
                            ${test.is_hidden ? `
                                <div class="alert alert-warning" style="
                                    margin-bottom: 10px;
                                    padding: 8px;
                                    font-size: 0.9em;
                                    background-color: #fff3cd;
                                    border: 1px solid #ffeeba;
                                    border-radius: 4px;
                                ">
                                    <i class="fas fa-info-circle"></i> This is a hidden test case. Students won't see the input/output.
                                </div>
                                <textarea class="form-control" 
                                    name="test_case_description[${sectionId}][${questionIndex}][]" 
                                    placeholder="Description (optional, shown to students for hidden test cases)"
                                    readonly
                                    style="
                                        border: 1px solid #ddd;
                                        border-radius: 4px;
                                        padding: 8px 12px;
                                        min-height: 60px;
                                    ">${test.description || ''}</textarea>
                            ` : ''}
                        </div>
                    `).join('') : ''}
                </div>
                <button type="button" class="btn btn-secondary add-test-case-btn mt-2" style="
                    padding: 8px 16px;
                    background-color: #6c757d;
                    border: none;
                    border-radius: 4px;
                    color: white;
                    cursor: pointer;
                ">
                    <i class="fas fa-plus"></i> Add Test Case
                </button>
            </div>
        `;

        // Keep the existing add test case functionality but update the new test case HTML
        const addTestCaseBtn = optionsContainer.querySelector('.add-test-case-btn');
        if (addTestCaseBtn) {
            addTestCaseBtn.addEventListener('click', () => {
                const testCasesContainer = optionsContainer.querySelector('.test-cases');
                const newTestCase = document.createElement('div');
                newTestCase.className = 'test-case mb-2';
                newTestCase.style.cssText = `
                    background-color: #ffffff;
                    border: 1px solid #e9ecef;
                    border-radius: 8px;
                    padding: 15px;
                    margin-bottom: 15px;
                `;
                
                // Rest of your add test case code...
                // (Keep the existing functionality, just update the styling)
            });
        }
    } else if (question.question_type === 'multiple_choice') {
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
            <button type="button" class="qb-delete-option">
                ×
            </button>
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
    // Clear question list
    const questionsList = document.getElementById('qbQuestionsList');
    if (questionsList) {
        questionsList.innerHTML = '';
    }

    // Reset search input
    const searchInput = document.getElementById('qbSearchQuestion');
    if (searchInput) {
        searchInput.value = '';
    }

    // Reset select all checkbox
    const selectAllCheckbox = document.getElementById('qbSelectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = false;
    }

    // Reset selection counter
    const counterElement = document.getElementById('qbSelectionCounter');
    if (counterElement) {
        counterElement.textContent = '0 questions selected';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const qbModal = document.getElementById('qbModal');
    if (qbModal) {
        qbModal.addEventListener('hide.bs.modal', function() {
            // Ensure backdrop is removed when modal starts hiding
            setTimeout(() => {
                document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('padding-right');
                document.body.style.removeProperty('overflow');
            }, 200); // Small delay to ensure modal hide animation completes
        });
    }
});

// Add this function to check if SweetAlert2 is loaded
function checkSweetAlert() {
    if (typeof Swal === 'undefined') {
        console.error('SweetAlert2 is not loaded!');
        return false;
    }
    console.log('SweetAlert2 is loaded and ready');
    return true;
}

// Add event listener to check SweetAlert2 when document loads
document.addEventListener('DOMContentLoaded', function() {
    checkSweetAlert();
});

// Add this function to handle the auto-generation of questions
async function generateQuestions() {
    try {
        // Get form data
        const form = document.getElementById('autoGenerateForm');
        const formData = new FormData(form);
        
        // Validate form data
        const numQuestions = parseInt(formData.get('num_questions'));
        const questionTypes = formData.getAll('question_types[]');
        const categories = formData.getAll('categories[]');
        
        if (!numQuestions || numQuestions <= 0) {
            throw new Error('Please enter a valid number of questions');
        }
        
        if (questionTypes.length === 0) {
            throw new Error('Please select at least one question type');
        }
        
        if (categories.length === 0) {
            throw new Error('Please select at least one category');
        }

        // Show loading state
        Swal.fire({
            title: 'Generating Questions',
            text: 'Please wait...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Send request to generate questions
        const response = await fetch('handlers/generate_questions.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message || 'Failed to generate questions');
        }

        // Close the auto-generate modal
        const autoGenModal = bootstrap.Modal.getInstance(document.getElementById('autoGenerateModal'));
        if (autoGenModal) {
            autoGenModal.hide();
        }

        // Get the current section
        const currentSection = document.querySelector('.section-block');
        if (!currentSection) {
            throw new Error('No section found to add questions to');
        }

        const sectionId = currentSection.getAttribute('data-section-id');
        console.log('Current section ID:', sectionId);
        console.log('Current section:', currentSection);

        // Add each generated question to the section
        if (data.questions && data.questions.length > 0) {
            console.log('Questions to add:', data.questions);
            data.questions.forEach(question => {
                console.log('Adding question:', question);
                if (typeof window.addQuestionToExam === 'function') {
                    window.addQuestionToExam(question, sectionId);
                } else {
                    console.error('addQuestionToExam function not found');
                }
            });
        } else {
            console.log('No questions received from server');
        }

        // Show success message
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: `Generated ${data.count} questions successfully`,
            timer: 2000,
            showConfirmButton: false
        });

    } catch (error) {
        console.error('Error generating questions:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Failed to generate questions'
        });
    }
}

// Add this function to view questions in a category
async function viewCategoryQuestions(category) {
    try {
        const response = await fetch(`handlers/fetch_questions.php?category=${encodeURIComponent(category)}`);
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message || 'Failed to load questions');
        }

        // Create HTML for questions preview
        const questionsHtml = data.questions.map(q => `
            <div class="question-preview mb-3">
                <div class="d-flex justify-content-between">
                    <span class="badge bg-primary">${q.question_type}</span>
                    <small class="text-muted">Points: ${q.points}</small>
                </div>
                <div class="question-text mt-2">${q.question_text}</div>
                ${renderQuestionDetails(q)}
            </div>
        `).join('');

        // Show modal with questions
        Swal.fire({
            title: `Questions in ${category}`,
            html: `
                <div class="questions-preview" style="max-height: 400px; overflow-y: auto;">
                    ${questionsHtml}
                </div>
            `,
            width: '800px',
            showCloseButton: true,
            showConfirmButton: false
        });

    } catch (error) {
        console.error('Error viewing category questions:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Failed to load category questions'
        });
    }
}

// Add this helper function to render question details
function renderQuestionDetails(question) {
    switch (question.question_type) {
        case 'multiple_choice':
            return `
                <div class="options-list mt-2">
                    ${question.choices.map(choice => `
                        <div class="option ${choice.is_correct ? 'text-success' : ''}">
                            ${choice.is_correct ? '✓' : '○'} ${choice.choice_text}
                        </div>
                    `).join('')}
                </div>
            `;
        case 'programming':
            return `
                <div class="programming-details mt-2">
                    <div>Language: ${question.programming_language}</div>
                    ${question.test_cases ? `
                        <div class="test-cases mt-1">
                            <small>Test Cases: ${question.test_cases.length}</small>
                        </div>
                    ` : ''}
                </div>
            `;
        default:
            return '';
    }
}

// Add event listener for auto generate button
document.addEventListener('DOMContentLoaded', function() {
    const autoGenerateBtn = document.getElementById('autoGenerateBtn');
    if (autoGenerateBtn) {
        autoGenerateBtn.addEventListener('click', showAutoGenerateModal);
    }
});

// Function to show auto generate modal
function showAutoGenerateModal() {
    // First fetch categories
    fetch('handlers/get_categories.php')
        .then(response => response.json())
        .then(data => {
            const categories = data.categories || [];
            
            Swal.fire({
                title: 'Auto Generate Questions',
                html: `
                    <div class="container">
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label">Number of Questions</label>
                                <input type="number" id="numQuestions" class="form-control" min="1" value="5">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label">Categories</label>
                                <select id="autoGenCategories" class="form-control" multiple>
                                    ${categories.map(category => `
                                        <option value="${category}">${category}</option>
                                    `).join('')}
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label">Question Types</label>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="question_types[]" value="multiple_choice" id="type_mc" checked>
                                    <label class="form-check-label" for="type_mc">Multiple Choice</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="question_types[]" value="true_false" id="type_tf">
                                    <label class="form-check-label" for="type_tf">True/False</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="question_types[]" value="programming" id="type_prog">
                                    <label class="form-check-label" for="type_prog">Programming</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div id="questionCountInfo" class="text-muted small">
                                    Available questions: <span id="mcCount">0</span> Multiple Choice, 
                                    <span id="tfCount">0</span> True/False, 
                                    <span id="progCount">0</span> Programming
                                </div>
                            </div>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Generate',
                didOpen: () => {
                    // Initialize question counts
                    updateQuestionCounts();
                    
                    // Add event listeners for checkboxes and category select
                    document.querySelectorAll('input[name="question_types[]"]').forEach(checkbox => {
                        checkbox.addEventListener('change', updateQuestionCounts);
                    });
                    
                    const categorySelect = document.getElementById('autoGenCategories');
                    if (categorySelect) {
                        categorySelect.addEventListener('change', updateQuestionCounts);
                    }
                },
                preConfirm: () => {
                    const numQuestions = document.getElementById('numQuestions').value;
                    const selectedCategories = Array.from(document.getElementById('autoGenCategories').selectedOptions)
                        .map(option => option.value);
                    const selectedTypes = Array.from(document.querySelectorAll('input[name="question_types[]"]:checked'))
                        .map(cb => cb.value);

                    // Validation
                    if (!numQuestions || numQuestions < 1) {
                        Swal.showValidationMessage('Please enter a valid number of questions');
                        return false;
                    }

                    if (selectedCategories.length === 0) {
                        Swal.showValidationMessage('Please select at least one category');
                        return false;
                    }

                    if (selectedTypes.length === 0) {
                        Swal.showValidationMessage('Please select at least one question type');
                        return false;
                    }

                    // Get the exam_id from the URL
                    const urlParams = new URLSearchParams(window.location.search);
                    const examId = urlParams.get('exam_id');

                    // Prepare form data
                    const formData = new FormData();
                    formData.append('num_questions', numQuestions);
                    formData.append('categories', JSON.stringify(selectedCategories));
                    formData.append('question_types', JSON.stringify(selectedTypes));
                    if (examId) formData.append('exam_id', examId);

                    // Return the fetch promise
                    return fetch('handlers/auto_generate_questions.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            throw new Error(data.message || 'Failed to generate questions');
                        }
                        return data;
                    });
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    const data = result.value;
                    if (data.questions && data.questions.length > 0) {
                        importQuestionsToSection(data.questions);
                        showSuccess(`Successfully generated and imported ${data.questions.length} questions`);
                    } else {
                        showWarning('No questions were generated matching your criteria');
                    }
                }
            }).catch(error => {
                console.error('Error:', error);
                showError('An error occurred while generating questions');
            });
        })
        .catch(error => {
            console.error('Error loading categories:', error);
            showError('Failed to load categories');
        });
}

// Add the importQuestionsToSection function
function importQuestionsToSection(questions) {
    const sections = document.querySelectorAll('.section-block');
    if (sections.length === 0) {
        showError('Please create a section first');
        return;
    }
    
    const lastSection = sections[sections.length - 1];
    const sectionId = lastSection.getAttribute('data-section-id');
    const questionContainer = document.getElementById(`question-container-${sectionId}`);
    
    // Keep track of already imported questions
    const existingQuestionIds = Array.from(questionContainer.querySelectorAll('.question-block'))
        .map(block => block.getAttribute('data-original-question-id'))
        .filter(id => id);

    // Filter out already imported questions
    const newQuestions = questions.filter(question => 
        !existingQuestionIds.includes(question.question_id.toString())
    );

    if (newQuestions.length === 0) {
        showWarning('All selected questions have already been imported to this section');
        return;
    }

    newQuestions.forEach(questionData => {
        addQuestionToExam(questionData);
    });

    // Close the modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('qbModal'));
    if (modal) {
        modal.hide();
    }
}

// Add function to update question counts
function updateQuestionCounts() {
    const categorySelect = document.getElementById('autoGenCategories');
    const category = categorySelect ? categorySelect.value : '';
    const types = Array.from(document.querySelectorAll('input[name="question_types[]"]:checked'))
        .map(cb => cb.value);
        
    fetch(`handlers/get_question_counts.php?${new URLSearchParams({
        category: category,
        types: types.join(',')
    })}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('mcCount').textContent = data.counts.multiple_choice || 0;
            document.getElementById('tfCount').textContent = data.counts.true_false || 0;
            document.getElementById('progCount').textContent = data.counts.programming || 0;
        }
    })
    .catch(error => console.error('Error getting question counts:', error));
}

// Add event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Update counts when question types or category changes
    document.querySelectorAll('input[name="question_types[]"]').forEach(checkbox => {
        checkbox.addEventListener('change', updateQuestionCounts);
    });
    
    const categorySelect = document.getElementById('qbCategorySelect');
    if (categorySelect) {
        categorySelect.addEventListener('change', updateQuestionCounts);
    }
    
    // Initial count update
    updateQuestionCounts();
}); 