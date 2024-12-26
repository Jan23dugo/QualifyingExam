<!-- Question Bank Modal -->
<div class="modal fade" id="questionBankModal" tabindex="-1" aria-labelledby="questionBankModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="questionBankModalLabel">Question Bank</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Import Type Selection -->
                <div class="import-type-container mb-3">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="importType" id="manualSelect" value="manual" checked>
                        <label class="form-check-label" for="manualSelect">Manual Selection</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="importType" id="autoGenerate" value="auto">
                        <label class="form-check-label" for="autoGenerate">Auto Generate</label>
                    </div>
                </div>

                <!-- Manual Selection Section -->
                <div id="manualSelectSection">
                    <!-- Search and Filter Section -->
                    <div class="search-filter-container mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <input type="text" id="searchQuestion" class="form-control" placeholder="Search questions...">
                            </div>
                            <div class="col-md-6">
                                <select id="categorySelect" class="form-control">
                                    <option value="">All Categories</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Questions List -->
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAll">
                                        <span id="selectionCounter" class="ms-2 text-muted small">0 questions selected</span>
                                    </th>
                                    <th>Question</th>
                                    <th>Type</th>
                                    <th>Category</th>
                                </tr>
                            </thead>
                            <tbody id="questionBankList">
                                <!-- Questions will be dynamically added here -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Preview Template (Hidden by default) -->
                    <template id="multipleChoicePreviewTemplate">
                        <div class="question-preview">
                            <div class="question-text mb-3"></div>
                            <div class="options-container">
                                <!-- Options will be added here -->
                            </div>
                            <div class="option-container" style="margin-bottom: 10px;">
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <input type="radio" name="correct_option">
                                    </div>
                                    <input type="text" class="form-control" placeholder="Option text">
                                    <button type="button" class="btn btn-link text-danger delete-option-btn" style="padding: 5px;">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-secondary add-option-btn" style="margin-bottom: 10px;">
                                Add Option
                            </button>
                        </div>
                    </template>

                    <!-- Pagination -->
                    <div class="questions-pagination" id="questionsPagination">
                        <!-- Pagination will be dynamically added here -->
                    </div>
                </div>

                <!-- Auto Generate Section -->
                <div id="autoGenerateSection" style="display: none;">
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="autoGenerateCategory" class="form-label">Select Category</label>
                                <select id="autoGenerateCategory" class="form-control">
                                    <option value="">All Categories</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="questionCount" class="form-label">Number of Questions</label>
                                <input type="number" id="questionCount" class="form-control" min="1" value="5">
                                <small id="availableQuestionCount" class="form-text text-muted">Total available questions: 0</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Question Types</label>
                                <div class="question-types-container">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="multiple_choice" id="mcType" checked>
                                        <label class="form-check-label" for="mcType">
                                            Multiple Choice (<span id="mcCount">0</span> available)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="true_false" id="tfType" checked>
                                        <label class="form-check-label" for="tfType">
                                            True/False (<span id="tfCount">0</span> available)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="programming" id="progType" checked>
                                        <label class="form-check-label" for="progType">
                                            Programming (<span id="progCount">0</span> available)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="importSelectedQuestions">
                    <i class="fas fa-check me-1"></i>Import Selected
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Imported Multiple Choice Styles */
.imported-mc-options {
    margin-top: 10px;
    margin-bottom: 15px;
}

.imported-option-item {
    margin-bottom: 10px;
    background-color: #f8f9fa;
    border-radius: 4px;
}

.imported-option-item .input-group {
    display: flex;
    align-items: stretch;
    background-color: #f8f9fa;
    border-radius: 4px;
}

.imported-option-item .input-group-prepend .input-group-text {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-right: none;
    padding: 0.375rem 0.75rem;
    display: flex;
    align-items: center;
    min-width: 100px;
}

.imported-option-item .input-group-text input[type="radio"] {
    margin-right: 8px;
}

.imported-option-item .input-group-text label {
    margin: 0;
    font-weight: normal;
    color: #495057;
}

.imported-option-item .form-control {
    border: 1px solid #dee2e6;
    border-radius: 0;
    border-left: none;
    border-right: none;
}

.imported-remove-btn {
    background: transparent;
    color: #dc3545;
    border: 1px solid #dee2e6;
    border-left: none;
    width: 40px;
    padding: 0;
    font-size: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.imported-remove-btn:hover {
    background-color: #fee2e2;
    color: #dc2626;
}

.imported-add-option {
    color: #4f46e5;
    background: transparent;
    border: none;
    padding: 8px 0;
    font-size: 14px;
    cursor: pointer;
    text-decoration: none;
}

.imported-add-option:hover {
    color: #4338ca;
    text-decoration: underline;
}

/* Question block styles */
.question-block {
    background-color: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.question-text {
    margin-bottom: 15px;
}

.points-input {
    margin-top: 15px;
}
</style> 