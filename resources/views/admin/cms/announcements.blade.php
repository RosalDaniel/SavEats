<!-- Filters and Actions -->
<div class="cms-filters">
    <div class="filter-group">
        <input type="text" class="search-input" placeholder="Search announcements..." id="announcementSearch">
        <select class="filter-select" id="announcementStatusFilter">
            <option value="all">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="archived">Archived</option>
        </select>
        <select class="filter-select" id="announcementAudienceFilter">
            <option value="all">All Audiences</option>
            <option value="consumer">Consumers</option>
            <option value="establishment">Establishments</option>
            <option value="foodbank">Food Banks</option>
        </select>
    </div>
    <button class="btn-primary" onclick="openAnnouncementModal()">
        <svg viewBox="0 0 24 24" fill="currentColor">
            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
        </svg>
        Add Announcement
    </button>
</div>

<!-- Announcements Table -->
<div class="cms-table-container">
    <table class="cms-table" id="announcementsTable">
        <thead>
            <tr>
                <th>Title</th>
                <th>Message</th>
                <th>Target Audience</th>
                <th>Status</th>
                <th>Published</th>
                <th>Expires</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="announcementsTableBody">
            <tr>
                <td colspan="8" class="loading">Loading announcements...</td>
            </tr>
        </tbody>
    </table>
    <div class="pagination-container" id="announcementsPagination"></div>
</div>

<!-- Announcement Modal -->
<div id="announcementModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="announcementModalTitle">Add Announcement</h2>
            <button class="modal-close" onclick="closeAnnouncementModal()">&times;</button>
        </div>
        <form id="announcementForm" onsubmit="saveAnnouncement(event)">
            <input type="hidden" id="announcementId">
            <div class="form-group">
                <label for="announcementTitle">Title *</label>
                <input type="text" id="announcementTitle" name="title" required>
            </div>
            <div class="form-group">
                <label for="announcementMessage">Message *</label>
                <textarea id="announcementMessage" name="message" rows="5" required></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="announcementAudience">Target Audience *</label>
                    <select id="announcementAudience" name="target_audience" required>
                        <option value="all">All Users</option>
                        <option value="consumer">Consumers</option>
                        <option value="establishment">Establishments</option>
                        <option value="foodbank">Food Banks</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="announcementStatus">Status *</label>
                    <select id="announcementStatus" name="status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="announcementPublishedAt">Published At</label>
                    <input type="datetime-local" id="announcementPublishedAt" name="published_at">
                </div>
                <div class="form-group">
                    <label for="announcementExpiresAt">Expires At</label>
                    <input type="datetime-local" id="announcementExpiresAt" name="expires_at">
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeAnnouncementModal()">Cancel</button>
                <button type="submit" class="btn-primary">Save Announcement</button>
            </div>
        </form>
    </div>
</div>

