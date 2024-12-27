<!-- Question Bank Modal -->
<div class="modal fade" id="qbModal" tabindex="-1" aria-labelledby="qbModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="qbModalLabel">Question Bank</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Add search and category filter container -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <input type="text" 
                            id="qbSearchQuestion" 
                            class="form-control" 
                            placeholder="Search questions...">
                    </div>
                    <div class="col-md-6">
                        <select id="qbCategorySelect" class="form-control">
                            <option value="">All Categories</option>
                            <!-- Categories will be loaded dynamically -->
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th width="40px">
                                    <input type="checkbox" id="qbSelectAll">
                                </th>
                                <th>Question</th>
                                <th>Type</th>
                                <th>Category</th>
                            </tr>
                        </thead>
                        <tbody id="qbQuestionsList">
                            <!-- Questions will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <span id="qbSelectionCounter">0 questions selected</span>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="qbImportSelectedBtn">Import Selected</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Add styles for the search and category container */
.row.mb-3 {
    margin: 0 -10px 1rem;
}

.col-md-6 {
    padding: 0 10px;
}

#qbSearchQuestion,
#qbCategorySelect {
    width: 100%;
    padding: 0.375rem 0.75rem;
    font-size: 1rem;
    line-height: 1.5;
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

#qbSearchQuestion:focus,
#qbCategorySelect:focus {
    border-color: #86b7fe;
    outline: 0;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

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