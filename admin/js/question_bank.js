// Utility Functions
const safeObserve = (function() {
    let observer = null;
    
    return function(callback) {
        if (observer) {
            observer.disconnect();
        }
        
        observer = new MutationObserver((mutations) => {
            try {
                callback(mutations);
            } catch (error) {
                console.error('Mutation observer error:', error);
            }
        });
        
        return observer;
    };
})();

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

// Wait for QuillManager to be ready before initializing
window.addEventListener('QuillManagerReady', function() {
    console.log('Setting up MutationObserver for editors');
    
    // Initialize observer for editor instances
    const editorObserver = safeObserve((mutations) => {
        mutations.forEach((mutation) => {
            // Check added nodes
            if (mutation.addedNodes.length) {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) { // ELEMENT_NODE
                        // Check the node itself
                        if (node.classList && (
                            node.classList.contains('editor-container') ||
                            node.classList.contains('section-title-editor') ||
                            node.classList.contains('section-description-editor')
                        )) {
                            console.log('Found new editor container:', node);
                            if (!node.quillInstance && !window.editorInstances.has(node)) {
                                console.log('Initializing new editor');
                                QuillManager.initializeEditor(node);
                            }
                        }
                        
                        // Also check children of the added node
                        const newEditors = node.querySelectorAll('.editor-container, .section-title-editor, .section-description-editor');
                        newEditors.forEach(editor => {
                            console.log('Found new editor in children:', editor);
                            if (!editor.quillInstance && !window.editorInstances.has(editor)) {
                                console.log('Initializing new editor in children');
                                QuillManager.initializeEditor(editor);
                            }
                        });
                    }
                });
            }
        });
    });

    // Start observing with a more specific configuration
    console.log('Starting MutationObserver');
    const targetNode = document.getElementById('sectionBlocks');
    if (targetNode) {
        editorObserver.observe(targetNode, {
            childList: true,
            subtree: true,
            attributes: false,
            characterData: false
        });
        console.log('MutationObserver started on sectionBlocks');
    } else {
        console.error('Could not find sectionBlocks element');
    }
});

// Question Bank Functions
function loadCategories() {
    fetch('fetch_categories.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.categories) {
                const selects = [
                    document.getElementById('categorySelect'),
                    document.getElementById('autoGenerateCategory')
                ];
                
                selects.forEach(categorySelect => {
                    while (categorySelect.options.length > 1) {
                        categorySelect.remove(1);
                    }
                    data.categories.forEach(category => {
                        const option = document.createElement('option');
                        option.value = category;
                        option.textContent = category;
                        categorySelect.appendChild(option);
                    });
                });
            }
        })
        .catch(error => console.error('Error loading categories:', error));
}

function attachCheckboxListeners() {
    const checkboxes = document.querySelectorAll('#questionBankList input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectionCounter);
    });
}

function updateSelectionCounter() {
    const count = document.querySelectorAll('#questionBankList input[type="checkbox"]:checked').length;
    const counter = document.getElementById('selectionCounter');
    if (counter) {
        counter.textContent = `${count} question${count !== 1 ? 's' : ''} selected`;
    }
}

function loadQuestionBank(search = '') {
    const category = document.getElementById('categorySelect').value;
    const url = `fetch_question_bank.php?search=${encodeURIComponent(search)}&category=${encodeURIComponent(category)}`;
    
    const questionList = document.getElementById('questionBankList');
    questionList.innerHTML = `
        <tr>
            <td colspan="4" class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                Loading questions...
            </td>
        </tr>
    `;

    document.getElementById('questionBankModal').classList.add('loading');
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (!data.questions || data.questions.length === 0) {
                questionList.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center py-3">
                            <i class="fas fa-info-circle me-2"></i>
                            No questions found
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

            attachCheckboxListeners();
        })
        .catch(error => {
            console.error('Error:', error);
            questionList.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center text-danger py-3">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Error loading questions
                    </td>
                </tr>`;
        })
        .finally(() => {
            document.getElementById('questionBankModal').classList.remove('loading');
        });
}

function updateQuestionCounts(category = '') {
    fetch(`get_question_counts.php?category=${encodeURIComponent(category)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('mcCount').textContent = data.counts.multiple_choice || 0;
                document.getElementById('tfCount').textContent = data.counts.true_false || 0;
                document.getElementById('progCount').textContent = data.counts.programming || 0;
                
                const total = Object.values(data.counts).reduce((a, b) => a + b, 0);
                document.getElementById('availableQuestionCount').textContent = 
                    `Total available questions: ${total}`;
                
                const questionCountInput = document.getElementById('questionCount');
                if (questionCountInput) {
                    questionCountInput.max = total;
                    if (parseInt(questionCountInput.value) > total) {
                        questionCountInput.value = total;
                    }
                }
            }
        })
        .catch(error => console.error('Error getting question counts:', error));
}

function importSelectedQuestions() {
    const importType = document.querySelector('input[name="importType"]:checked').value;
    
    if (importType === 'manual') {
        importManuallySelectedQuestions();
    } else {
        importAutoGeneratedQuestions();
    }
}

function importManuallySelectedQuestions() {
    const selectedQuestions = document.querySelectorAll('#questionBankList input[type="checkbox"]:checked');
    if (selectedQuestions.length === 0) {
        alert('Please select at least one question');
        return;
    }
    importQuestionsToSection(Array.from(selectedQuestions).map(cb => JSON.parse(cb.dataset.question)));
}

function importAutoGeneratedQuestions() {
    const count = parseInt(document.getElementById('questionCount').value);
    const category = document.getElementById('autoGenerateCategory').value;
    
    const selectedTypes = Array.from(document.querySelectorAll('#autoGenerateSection input[type="checkbox"]:checked'))
        .map(cb => cb.value);
    
    if (selectedTypes.length === 0) {
        alert('Please select at least one question type');
        return;
    }

    // Fetch random questions from the question bank
    fetch(`fetch_random_questions.php?count=${count}&types=${selectedTypes.join(',')}&category=${encodeURIComponent(category)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.questions.length > 0) {
                importQuestionsToSection(data.questions);
            } else {
                alert('No questions found matching the criteria');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error fetching random questions');
        });
}

function importQuestionsToSection(questions) {
    const sections = document.querySelectorAll('.section-block');
    if (sections.length === 0) {
        alert('Please create a section first');
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
        alert('All selected questions have already been imported to this section');
        return;
    }

    // Create a document fragment to batch DOM updates
    const fragment = document.createDocumentFragment();

    newQuestions.forEach(questionData => {
        const questionIndex = questionContainer.children.length;
        const newQuestion = createQuestionElement(sectionId, questionIndex, questionData);
        fragment.appendChild(newQuestion);
    });

    // Append all questions at once
    questionContainer.appendChild(fragment);
    
    // Close the modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('questionBankModal'));
    if (modal) {
        modal.hide();
    }
}

// Function to create question element
function createQuestionElement(sectionId, questionIndex, questionData) {
    const newQuestion = document.createElement('div');
    newQuestion.className = 'question-block';
    newQuestion.dataset.questionId = questionData.id || '';
    newQuestion.style.cssText = 'margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 8px;';
    
    // Create the question content
    newQuestion.innerHTML = `
        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
            <div style="flex: 1; margin-right: 10px;">
                <div class="editor-container question-editor"></div>
            </div>
            <button type="button" class="delete-button btn btn-link text-danger" style="padding: 4px;">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>
    `;
    
    // Initialize the editor
    const editorContainer = newQuestion.querySelector('.question-editor');
    if (editorContainer) {
        const editor = QuillManager.initializeEditor(editorContainer);
        editor.root.dataset.placeholder = 'Input question text';
        
        // Set content if provided
        if (questionData.question_text) {
            editor.root.innerHTML = questionData.question_text;
        }
    }
    
    // Add delete handler
    const deleteBtn = newQuestion.querySelector('.delete-button');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', () => {
            if (confirm('Are you sure you want to delete this question?')) {
                // Clean up editor
                const editorContainer = newQuestion.querySelector('.editor-container');
                if (editorContainer && editorContainer.quillInstance) {
                    QuillManager.destroyEditor(editorContainer);
                }
                newQuestion.remove();
            }
        });
    }
    
    return newQuestion;
}

// Update cleanup code
window.addEventListener('beforeunload', () => {
    if (window.scrollHandler) {
        window.scrollHandler.destroy();
    }
    
    // Clean up all editor instances
    document.querySelectorAll('.editor-container').forEach(editor => {
        if (editor.quillInstance) {
            QuillManager.destroyEditor(editor);
        }
    });
    
    if (editorObserver) {
        editorObserver.disconnect();
    }
});

// Function to initialize Question Bank listeners
function initializeQuestionBankListeners() {
    const selectAll = document.getElementById('selectAll');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('#questionBankList input[type="checkbox"]');
            checkboxes.forEach(checkbox => checkbox.checked = this.checked);
            updateSelectionCounter();
        });
    }

    const questionSearch = document.getElementById('questionSearch');
    if (questionSearch) {
        questionSearch.addEventListener('input', debounce(function() {
            loadQuestionBank(this.value);
        }, 300));
    }

    const categorySelect = document.getElementById('categorySelect');
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            loadQuestionBank(document.getElementById('questionSearch').value);
        });
    }
}