<!-- Filters and Actions -->
<div class="cms-filters">
    <div class="filter-group">
        <input type="text" class="search-input" placeholder="Search banners..." id="bannerSearch">
        <select class="filter-select" id="bannerStatusFilter">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>
    <button class="btn-primary" onclick="openBannerModal()">
        <svg viewBox="0 0 24 24" fill="currentColor">
            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
        </svg>
        Add Banner
    </button>
</div>

<!-- Banners Table -->
<div class="cms-table-container">
    <table class="cms-table" id="bannersTable">
        <thead>
            <tr>
                <th>Order</th>
                <th>Title</th>
                <th>Image</th>
                <th>Link</th>
                <th>Status</th>
                <th>Date Range</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="bannersTableBody">
            <tr>
                <td colspan="7" class="loading">Loading banners...</td>
            </tr>
        </tbody>
    </table>
    <div class="pagination-container" id="bannersPagination"></div>
</div>

<!-- Banner Modal -->
<div id="bannerModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="bannerModalTitle">Add Banner</h2>
            <button class="modal-close" onclick="closeBannerModal()">&times;</button>
        </div>
        <form id="bannerForm" onsubmit="saveBanner(event)">
            <input type="hidden" id="bannerId">
            <div class="form-group">
                <label for="bannerTitle">Title *</label>
                <input type="text" id="bannerTitle" name="title" required>
            </div>
            <div class="form-group">
                <label for="bannerDescription">Description</label>
                <textarea id="bannerDescription" name="description" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label for="bannerImageUrl">Image URL</label>
                <input type="url" id="bannerImageUrl" name="image_url" placeholder="https://example.com/image.jpg">
            </div>
            <div class="form-group">
                <label for="bannerLinkUrl">Link URL</label>
                <input type="url" id="bannerLinkUrl" name="link_url" placeholder="https://example.com">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="bannerDisplayOrder">Display Order</label>
                    <input type="number" id="bannerDisplayOrder" name="display_order" min="0" value="0">
                </div>
                <div class="form-group">
                    <label for="bannerStatus">Status *</label>
                    <select id="bannerStatus" name="status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="bannerStartDate">Start Date</label>
                    <input type="datetime-local" id="bannerStartDate" name="start_date">
                </div>
                <div class="form-group">
                    <label for="bannerEndDate">End Date</label>
                    <input type="datetime-local" id="bannerEndDate" name="end_date">
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeBannerModal()">Cancel</button>
                <button type="submit" class="btn-primary">Save Banner</button>
            </div>
        </form>
    </div>
</div>

