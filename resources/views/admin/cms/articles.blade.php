<!-- Filters and Actions -->
<div class="cms-filters">
    <div class="filter-group">
        <input type="text" class="search-input" placeholder="Search articles..." id="articleSearch">
        <select class="filter-select" id="articleStatusFilter">
            <option value="">All Status</option>
            <option value="published">Published</option>
            <option value="draft">Draft</option>
            <option value="archived">Archived</option>
        </select>
        <select class="filter-select" id="articleCategoryFilter">
            <option value="">All Categories</option>
        </select>
    </div>
    <button class="btn-primary" onclick="openArticleModal()">
        <svg viewBox="0 0 24 24" fill="currentColor">
            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
        </svg>
        Add Article
    </button>
</div>

<!-- Articles Table -->
<div class="cms-table-container">
    <table class="cms-table" id="articlesTable">
        <thead>
            <tr>
                <th>Order</th>
                <th>Title</th>
                <th>Category</th>
                <th>Status</th>
                <th>Views</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="articlesTableBody">
            <tr>
                <td colspan="7" class="loading">Loading articles...</td>
            </tr>
        </tbody>
    </table>
    <div class="pagination-container" id="articlesPagination"></div>
</div>

<!-- Article Modal -->
<div id="articleModal" class="modal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h2 id="articleModalTitle">Add Article</h2>
            <button class="modal-close" onclick="closeArticleModal()">&times;</button>
        </div>
        <form id="articleForm" onsubmit="saveArticle(event)">
            <input type="hidden" id="articleId">
            <div class="form-group">
                <label for="articleTitle">Title *</label>
                <input type="text" id="articleTitle" name="title" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="articleCategory">Category</label>
                    <input type="text" id="articleCategory" name="category" placeholder="e.g., Getting Started">
                </div>
                <div class="form-group">
                    <label for="articleDisplayOrder">Display Order</label>
                    <input type="number" id="articleDisplayOrder" name="display_order" min="0" value="0">
                </div>
                <div class="form-group">
                    <label for="articleStatus">Status *</label>
                    <select id="articleStatus" name="status" required>
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="articleTags">Tags (comma-separated)</label>
                <input type="text" id="articleTags" name="tags" placeholder="help, guide, tutorial">
            </div>
            <div class="form-group">
                <label for="articleContent">Content *</label>
                <textarea id="articleContent" name="content" rows="15" required></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeArticleModal()">Cancel</button>
                <button type="submit" class="btn-primary">Save Article</button>
            </div>
        </form>
    </div>
</div>

