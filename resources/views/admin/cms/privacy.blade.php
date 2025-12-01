<!-- Filters and Actions -->
<div class="cms-filters">
    <div class="filter-group">
        <input type="text" class="search-input" placeholder="Search privacy policies..." id="privacySearch">
        <select class="filter-select" id="privacyStatusFilter">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="draft">Draft</option>
        </select>
    </div>
    <button class="btn-primary" onclick="openPrivacyModal()">
        <svg viewBox="0 0 24 24" fill="currentColor">
            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
        </svg>
        Add Privacy Policy
    </button>
</div>

<!-- Privacy Table -->
<div class="cms-table-container">
    <table class="cms-table" id="privacyTable">
        <thead>
            <tr>
                <th>Version</th>
                <th>Status</th>
                <th>Last Updated</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="privacyTableBody">
            <tr>
                <td colspan="5" class="loading">Loading privacy policies...</td>
            </tr>
        </tbody>
    </table>
    <div class="pagination-container" id="privacyPagination"></div>
</div>

<!-- Privacy Modal -->
<div id="privacyModal" class="modal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h2 id="privacyModalTitle">Add Privacy Policy</h2>
            <button class="modal-close" onclick="closePrivacyModal()">&times;</button>
        </div>
        <form id="privacyForm" onsubmit="savePrivacy(event)">
            <input type="hidden" id="privacyId">
            <div class="form-row">
                <div class="form-group">
                    <label for="privacyVersion">Version *</label>
                    <input type="text" id="privacyVersion" name="version" required placeholder="e.g., 1.0, 2.0">
                </div>
                <div class="form-group">
                    <label for="privacyStatus">Status *</label>
                    <select id="privacyStatus" name="status" required>
                        <option value="draft">Draft</option>
                        <option value="active">Active</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="privacyPublishedAt">Published Date</label>
                    <input type="datetime-local" id="privacyPublishedAt" name="published_at">
                </div>
            </div>
            <div class="form-group">
                <label for="privacyContent">Content *</label>
                <textarea id="privacyContent" name="content" rows="20" required></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closePrivacyModal()">Cancel</button>
                <button type="submit" class="btn-primary">Save Privacy Policy</button>
            </div>
        </form>
    </div>
</div>

