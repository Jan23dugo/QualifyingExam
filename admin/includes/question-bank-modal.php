<!-- Add these in the head section -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

<!-- Question Bank Modal -->
<div class="modal fade" id="qbModal" tabindex="-1" aria-labelledby="qbModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="qbModalLabel">Question Bank</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Auto Generate Button -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <button type="button" class="btn btn-primary w-100" id="autoGenerateBtn">
                            <i class="fas fa-magic"></i> Auto Generate Questions
                        </button>
                    </div>
                </div>

                <!-- Search and Manual Import Controls -->
                <div class="row mb-3">
                    <div class="col-md-8">
                        <input type="text" id="qbSearchQuestion" class="form-control" placeholder="Search questions...">
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