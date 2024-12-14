<!-- Question Bank Modal -->
<div class="modal fade" id="questionBankModal" tabindex="-1" aria-labelledby="questionBankModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center">
                    <i class="fas fa-book info-icon me-2"></i>
                    <h5 class="modal-title" id="questionBankModalLabel">Import Questions</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Category Selection -->
                <div class="mb-3">
                    <label class="form-label">Select Category:</label>
                    <select class="form-control" id="categorySelect">
                        <option value="">All Categories</option>
                        <!-- Categories will be loaded dynamically -->
                    </select>
                </div>

                <!-- Import Options -->
                <div class="mb-4">
                    <h6>Import Options:</h6>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="importType" id="manualSelect" value="manual" checked>
                        <label class="form-check-label" for="manualSelect">
                            Manually select questions
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="importType" id="autoGenerate" value="auto">
                        <label class="form-check-label" for="autoGenerate">
                            Auto-generate questions
                        </label>
                    </div>
                </div>

                <!-- Manual Selection Section -->
                <div id="manualSelectSection">
                    <div class="mb-3">
                        <input type="text" id="questionSearch" class="form-control" placeholder="Search questions...">
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAll"></th>
                                    <th class="sortable" data-sort="question_text">Question <i class="fas fa-sort"></i></th>
                                    <th class="sortable" data-sort="question_type">Type <i class="fas fa-sort"></i></th>
                                    <th class="sortable" data-sort="points">Points <i class="fas fa-sort"></i></th>
                                </tr>
                            </thead>
                            <tbody id="questionBankList">
                                <!-- Questions will be loaded here dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Auto Generate Section -->
                <div id="autoGenerateSection" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label">Select Category:</label>
                        <select class="form-control" id="autoGenerateCategory">
                            <option value="">All Categories</option>
                            <!-- Categories will be loaded dynamically -->
                        </select>
                        <small class="text-muted" id="availableQuestionCount"></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Number of Questions:</label>
                        <input type="number" id="questionCount" class="form-control" min="1" value="5">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Question Types:</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="multiple_choice" id="typeMultipleChoice" checked>
                            <label class="form-check-label" for="typeMultipleChoice">
                                Multiple Choice (<span id="mcCount">0</span> available)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="true_false" id="typeTrueFalse">
                            <label class="form-check-label" for="typeTrueFalse">
                                True/False (<span id="tfCount">0</span> available)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="programming" id="typeProgramming">
                            <label class="form-check-label" for="typeProgramming">
                                Programming (<span id="progCount">0</span> available)
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="importSelectedQuestions">Import Questions</button>
            </div>
        </div>
    </div>
</div>