<!-- Question Bank Modal -->
<div class="modal fade" id="questionBankModal" tabindex="-1" aria-labelledby="questionBankModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="questionBankModalLabel">Question Bank</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Search and Filter Section -->
                <div class="search-filter-container mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <input type="text" id="searchQuestion" class="form-control" placeholder="Search questions...">
                        </div>
                        <div class="col-md-6">
                            <select id="categoryFilter" class="form-control">
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
                                <th><input type="checkbox" id="selectAll"></th>
                                <th>Question</th>
                                <th>Type</th>
                                <th>Category</th>
                            </tr>
                        </thead>
                        <tbody id="questionBankList">
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="questions-pagination" id="questionsPagination">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="importSelectedQuestions">Import Selected</button>
            </div>
        </div>
    </div>
</div> 