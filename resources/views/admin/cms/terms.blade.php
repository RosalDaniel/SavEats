<!-- Filters and Actions -->
<div class="cms-filters">
    <div class="filter-group">
        <input type="text" class="search-input" placeholder="Search terms..." id="termsSearch">
        <select class="filter-select" id="termsStatusFilter">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="draft">Draft</option>
        </select>
    </div>
    <button class="btn-primary" onclick="openTermsModal()">
        <svg viewBox="0 0 24 24" fill="currentColor">
            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
        </svg>
        Add Terms
    </button>
</div>

<!-- Terms Table -->
<div class="cms-table-container">
    <table class="cms-table" id="termsTable">
        <thead>
            <tr>
                <th>Version</th>
                <th>Status</th>
                <th>Published Date</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="termsTableBody">
            <tr>
                <td colspan="5" class="loading">Loading terms...</td>
            </tr>
        </tbody>
    </table>
    <div class="pagination-container" id="termsPagination"></div>
</div>

<!-- Terms Modal -->
<div id="termsModal" class="modal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h2 id="termsModalTitle">Add Terms & Conditions</h2>
            <button class="modal-close" onclick="closeTermsModal()">&times;</button>
        </div>
        <form id="termsForm" onsubmit="saveTerms(event)">
            <input type="hidden" id="termsId">
            <div class="form-row">
                <div class="form-group">
                    <label for="termsVersion">Version *</label>
                    <input type="text" id="termsVersion" name="version" required placeholder="e.g., 1.0, 2.0">
                </div>
                <div class="form-group">
                    <label for="termsStatus">Status *</label>
                    <select id="termsStatus" name="status" required>
                        <option value="draft">Draft</option>
                        <option value="active">Active</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="termsPublishedAt">Published Date</label>
                    <input type="datetime-local" id="termsPublishedAt" name="published_at">
                </div>
            </div>
            <div class="form-group">
                <label for="termsContent">Content *</label>
                <textarea id="termsContent" name="content" rows="20" required></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeTermsModal()">Cancel</button>
                <button type="submit" class="btn-primary">Save Terms</button>
            </div>
        </form>
    </div>
</div>

