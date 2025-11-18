// Admin CMS Management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tabs
    initializeTabs();
    
    // Load initial data for active tab
    const activeTab = document.querySelector('.tab-btn.active');
    if (activeTab) {
        const tabName = activeTab.getAttribute('data-tab');
        loadTabData(tabName);
    }
});

// Tab Management
function initializeTabs() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    tabButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const tabName = this.getAttribute('data-tab');
            switchTab(tabName);
        });
    });
}

function switchTab(tabName) {
    // Update tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
    
    // Update tab content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    document.getElementById(`${tabName}-tab`).classList.add('active');
    
    // Load data for the tab
    loadTabData(tabName);
}

function loadTabData(tabName) {
    switch(tabName) {
        case 'banners':
            loadBanners();
            break;
        case 'articles':
            loadArticles();
            break;
        case 'terms':
            loadTerms();
            break;
        case 'privacy':
            loadPrivacy();
            break;
    }
}

// ============================================================================
// BANNERS
// ============================================================================

let currentBannerPage = 1;

function loadBanners(page = 1) {
    currentBannerPage = page;
    const search = document.getElementById('bannerSearch')?.value || '';
    const status = document.getElementById('bannerStatusFilter')?.value || '';
    
    const params = new URLSearchParams({
        page: page,
        ...(search && { search }),
        ...(status && { status })
    });
    
    fetch(`${CMS_ROUTES.banners.list}?${params}`, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderBannersTable(data.data);
            renderPagination('bannersPagination', data.data, loadBanners);
        }
    })
    .catch(error => {
        console.error('Error loading banners:', error);
        showNotification('Error loading banners', 'error');
    });
}

function renderBannersTable(data) {
    const tbody = document.getElementById('bannersTableBody');
    if (!tbody) return;
    
    if (data.data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="loading">No banners found</td></tr>';
        return;
    }
    
    tbody.innerHTML = data.data.map(banner => `
        <tr>
            <td>${banner.display_order || 0}</td>
            <td><strong>${escapeHtml(banner.title)}</strong></td>
            <td>${banner.image_url ? `<img src="${escapeHtml(banner.image_url)}" class="image-preview" alt="Banner">` : '-'}</td>
            <td>${banner.link_url ? `<a href="${escapeHtml(banner.link_url)}" target="_blank" style="color: #667eea;">View Link</a>` : '-'}</td>
            <td><span class="status-badge ${banner.status}">${banner.status}</span></td>
            <td>${formatDateRange(banner.start_date, banner.end_date)}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-icon btn-edit" onclick="editBanner(${banner.id})" title="Edit">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                        </svg>
                    </button>
                    <button class="btn-icon btn-delete" onclick="deleteBanner(${banner.id})" title="Delete">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                        </svg>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function openBannerModal(id = null) {
    const modal = document.getElementById('bannerModal');
    const form = document.getElementById('bannerForm');
    const title = document.getElementById('bannerModalTitle');
    
    if (id) {
        title.textContent = 'Edit Banner';
        fetch(`${CMS_ROUTES.banners.list}?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.data) {
                    const banner = data.data.data.find(b => b.id === id) || data.data.data[0];
                    fillBannerForm(banner);
                }
            });
    } else {
        title.textContent = 'Add Banner';
        form.reset();
        document.getElementById('bannerId').value = '';
    }
    
    modal.classList.add('active');
}

function closeBannerModal() {
    document.getElementById('bannerModal').classList.remove('active');
    document.getElementById('bannerForm').reset();
    document.getElementById('bannerId').value = '';
}

function fillBannerForm(banner) {
    document.getElementById('bannerId').value = banner.id;
    document.getElementById('bannerTitle').value = banner.title || '';
    document.getElementById('bannerDescription').value = banner.description || '';
    document.getElementById('bannerImageUrl').value = banner.image_url || '';
    document.getElementById('bannerLinkUrl').value = banner.link_url || '';
    document.getElementById('bannerDisplayOrder').value = banner.display_order || 0;
    document.getElementById('bannerStatus').value = banner.status || 'active';
    document.getElementById('bannerStartDate').value = banner.start_date ? formatDateTimeLocal(banner.start_date) : '';
    document.getElementById('bannerEndDate').value = banner.end_date ? formatDateTimeLocal(banner.end_date) : '';
}

function saveBanner(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const id = document.getElementById('bannerId').value;
    const url = id 
        ? CMS_ROUTES.banners.update(id)
        : CMS_ROUTES.banners.store;
    const method = id ? 'POST' : 'POST';
    
    fetch(url, {
        method: method,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(Object.fromEntries(formData))
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            closeBannerModal();
            loadBanners(currentBannerPage);
        } else {
            showNotification(data.message || 'Error saving banner', 'error');
        }
    })
    .catch(error => {
        console.error('Error saving banner:', error);
        showNotification('Error saving banner', 'error');
    });
}

function editBanner(id) {
    fetch(`${CMS_ROUTES.banners.list}?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.data) {
                const banner = data.data.data.find(b => b.id === id) || data.data.data[0];
                fillBannerForm(banner);
                openBannerModal(id);
            }
        });
}

function deleteBanner(id) {
    if (!confirm('Are you sure you want to delete this banner?')) return;
    
    fetch(CMS_ROUTES.banners.delete(id), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            loadBanners(currentBannerPage);
        } else {
            showNotification(data.message || 'Error deleting banner', 'error');
        }
    })
    .catch(error => {
        console.error('Error deleting banner:', error);
        showNotification('Error deleting banner', 'error');
    });
}

// Initialize banner filters
document.addEventListener('DOMContentLoaded', function() {
    const bannerSearch = document.getElementById('bannerSearch');
    const bannerStatusFilter = document.getElementById('bannerStatusFilter');
    
    if (bannerSearch) {
        let searchTimeout;
        bannerSearch.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => loadBanners(1), 300);
        });
    }
    
    if (bannerStatusFilter) {
        bannerStatusFilter.addEventListener('change', () => loadBanners(1));
    }
});

// ============================================================================
// ARTICLES
// ============================================================================

let currentArticlePage = 1;

function loadArticles(page = 1) {
    currentArticlePage = page;
    const search = document.getElementById('articleSearch')?.value || '';
    const status = document.getElementById('articleStatusFilter')?.value || '';
    const category = document.getElementById('articleCategoryFilter')?.value || '';
    
    const params = new URLSearchParams({
        page: page,
        ...(search && { search }),
        ...(status && { status }),
        ...(category && { category })
    });
    
    fetch(`${CMS_ROUTES.articles.list}?${params}`, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderArticlesTable(data.data);
            renderPagination('articlesPagination', data.data, loadArticles);
        }
    })
    .catch(error => {
        console.error('Error loading articles:', error);
        showNotification('Error loading articles', 'error');
    });
}

function renderArticlesTable(data) {
    const tbody = document.getElementById('articlesTableBody');
    if (!tbody) return;
    
    if (data.data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="loading">No articles found</td></tr>';
        return;
    }
    
    tbody.innerHTML = data.data.map(article => `
        <tr>
            <td>${article.display_order || 0}</td>
            <td><strong>${escapeHtml(article.title)}</strong></td>
            <td>${article.category || '-'}</td>
            <td><span class="status-badge ${article.status}">${article.status}</span></td>
            <td>${article.view_count || 0}</td>
            <td>${formatDate(article.created_at)}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-icon btn-edit" onclick="editArticle(${article.id})" title="Edit">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                        </svg>
                    </button>
                    <button class="btn-icon btn-delete" onclick="deleteArticle(${article.id})" title="Delete">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                        </svg>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function openArticleModal(id = null) {
    const modal = document.getElementById('articleModal');
    const form = document.getElementById('articleForm');
    const title = document.getElementById('articleModalTitle');
    
    if (id) {
        title.textContent = 'Edit Article';
    } else {
        title.textContent = 'Add Article';
        form.reset();
        document.getElementById('articleId').value = '';
    }
    
    modal.classList.add('active');
}

function closeArticleModal() {
    document.getElementById('articleModal').classList.remove('active');
    document.getElementById('articleForm').reset();
    document.getElementById('articleId').value = '';
}

function saveArticle(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const id = document.getElementById('articleId').value;
    const url = id 
        ? CMS_ROUTES.articles.update(id)
        : CMS_ROUTES.articles.store;
    
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(Object.fromEntries(formData))
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            closeArticleModal();
            loadArticles(currentArticlePage);
        } else {
            showNotification(data.message || 'Error saving article', 'error');
        }
    })
    .catch(error => {
        console.error('Error saving article:', error);
        showNotification('Error saving article', 'error');
    });
}

function editArticle(id) {
    fetch(`${CMS_ROUTES.articles.list}?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.data) {
                const article = data.data.data.find(a => a.id === id) || data.data.data[0];
                document.getElementById('articleId').value = article.id;
                document.getElementById('articleTitle').value = article.title || '';
                document.getElementById('articleContent').value = article.content || '';
                document.getElementById('articleCategory').value = article.category || '';
                document.getElementById('articleTags').value = article.tags || '';
                document.getElementById('articleDisplayOrder').value = article.display_order || 0;
                document.getElementById('articleStatus').value = article.status || 'draft';
                openArticleModal(id);
            }
        });
}

function deleteArticle(id) {
    if (!confirm('Are you sure you want to delete this article?')) return;
    
    fetch(CMS_ROUTES.articles.delete(id), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            loadArticles(currentArticlePage);
        } else {
            showNotification(data.message || 'Error deleting article', 'error');
        }
    })
    .catch(error => {
        console.error('Error deleting article:', error);
        showNotification('Error deleting article', 'error');
    });
}

// Initialize article filters
document.addEventListener('DOMContentLoaded', function() {
    const articleSearch = document.getElementById('articleSearch');
    const articleStatusFilter = document.getElementById('articleStatusFilter');
    const articleCategoryFilter = document.getElementById('articleCategoryFilter');
    
    if (articleSearch) {
        let searchTimeout;
        articleSearch.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => loadArticles(1), 300);
        });
    }
    
    if (articleStatusFilter) {
        articleStatusFilter.addEventListener('change', () => loadArticles(1));
    }
    
    if (articleCategoryFilter) {
        articleCategoryFilter.addEventListener('change', () => loadArticles(1));
    }
});

// ============================================================================
// TERMS & CONDITIONS
// ============================================================================

let currentTermsPage = 1;

function loadTerms(page = 1) {
    currentTermsPage = page;
    const search = document.getElementById('termsSearch')?.value || '';
    const status = document.getElementById('termsStatusFilter')?.value || '';
    
    const params = new URLSearchParams({
        page: page,
        ...(search && { search }),
        ...(status && { status })
    });
    
    fetch(`${CMS_ROUTES.terms.list}?${params}`, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderTermsTable(data.data);
            renderPagination('termsPagination', data.data, loadTerms);
        }
    })
    .catch(error => {
        console.error('Error loading terms:', error);
        showNotification('Error loading terms', 'error');
    });
}

function renderTermsTable(data) {
    const tbody = document.getElementById('termsTableBody');
    if (!tbody) return;
    
    if (data.data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="loading">No terms found</td></tr>';
        return;
    }
    
    tbody.innerHTML = data.data.map(term => `
        <tr>
            <td><strong>${escapeHtml(term.version)}</strong></td>
            <td><span class="status-badge ${term.status}">${term.status}</span></td>
            <td>${term.published_at ? formatDate(term.published_at) : '-'}</td>
            <td>${formatDate(term.created_at)}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-icon btn-edit" onclick="editTerms(${term.id})" title="Edit">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                        </svg>
                    </button>
                    <button class="btn-icon btn-delete" onclick="deleteTerms(${term.id})" title="Delete">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                        </svg>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function openTermsModal(id = null) {
    const modal = document.getElementById('termsModal');
    const form = document.getElementById('termsForm');
    const title = document.getElementById('termsModalTitle');
    
    if (id) {
        title.textContent = 'Edit Terms & Conditions';
    } else {
        title.textContent = 'Add Terms & Conditions';
        form.reset();
        document.getElementById('termsId').value = '';
    }
    
    modal.classList.add('active');
}

function closeTermsModal() {
    document.getElementById('termsModal').classList.remove('active');
    document.getElementById('termsForm').reset();
    document.getElementById('termsId').value = '';
}

function saveTerms(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const id = document.getElementById('termsId').value;
    const url = id 
        ? CMS_ROUTES.terms.update(id)
        : CMS_ROUTES.terms.store;
    
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(Object.fromEntries(formData))
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            closeTermsModal();
            loadTerms(currentTermsPage);
        } else {
            showNotification(data.message || 'Error saving terms', 'error');
        }
    })
    .catch(error => {
        console.error('Error saving terms:', error);
        showNotification('Error saving terms', 'error');
    });
}

function editTerms(id) {
    fetch(`${CMS_ROUTES.terms.list}?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.data) {
                const term = data.data.data.find(t => t.id === id) || data.data.data[0];
                document.getElementById('termsId').value = term.id;
                document.getElementById('termsVersion').value = term.version || '';
                document.getElementById('termsContent').value = term.content || '';
                document.getElementById('termsStatus').value = term.status || 'draft';
                document.getElementById('termsPublishedAt').value = term.published_at ? formatDateTimeLocal(term.published_at) : '';
                openTermsModal(id);
            }
        });
}

function deleteTerms(id) {
    if (!confirm('Are you sure you want to delete this terms & conditions?')) return;
    
    fetch(CMS_ROUTES.terms.delete(id), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            loadTerms(currentTermsPage);
        } else {
            showNotification(data.message || 'Error deleting terms', 'error');
        }
    })
    .catch(error => {
        console.error('Error deleting terms:', error);
        showNotification('Error deleting terms', 'error');
    });
}

// Initialize terms filters
document.addEventListener('DOMContentLoaded', function() {
    const termsSearch = document.getElementById('termsSearch');
    const termsStatusFilter = document.getElementById('termsStatusFilter');
    
    if (termsSearch) {
        let searchTimeout;
        termsSearch.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => loadTerms(1), 300);
        });
    }
    
    if (termsStatusFilter) {
        termsStatusFilter.addEventListener('change', () => loadTerms(1));
    }
});

// ============================================================================
// PRIVACY POLICY
// ============================================================================

let currentPrivacyPage = 1;

function loadPrivacy(page = 1) {
    currentPrivacyPage = page;
    const search = document.getElementById('privacySearch')?.value || '';
    const status = document.getElementById('privacyStatusFilter')?.value || '';
    
    const params = new URLSearchParams({
        page: page,
        ...(search && { search }),
        ...(status && { status })
    });
    
    fetch(`${CMS_ROUTES.privacy.list}?${params}`, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderPrivacyTable(data.data);
            renderPagination('privacyPagination', data.data, loadPrivacy);
        }
    })
    .catch(error => {
        console.error('Error loading privacy policies:', error);
        showNotification('Error loading privacy policies', 'error');
    });
}

function renderPrivacyTable(data) {
    const tbody = document.getElementById('privacyTableBody');
    if (!tbody) return;
    
    if (data.data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="loading">No privacy policies found</td></tr>';
        return;
    }
    
    tbody.innerHTML = data.data.map(policy => `
        <tr>
            <td><strong>${escapeHtml(policy.version)}</strong></td>
            <td><span class="status-badge ${policy.status}">${policy.status}</span></td>
            <td>${policy.published_at ? formatDate(policy.published_at) : '-'}</td>
            <td>${formatDate(policy.created_at)}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-icon btn-edit" onclick="editPrivacy(${policy.id})" title="Edit">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                        </svg>
                    </button>
                    <button class="btn-icon btn-delete" onclick="deletePrivacy(${policy.id})" title="Delete">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                        </svg>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function openPrivacyModal(id = null) {
    const modal = document.getElementById('privacyModal');
    const form = document.getElementById('privacyForm');
    const title = document.getElementById('privacyModalTitle');
    
    if (id) {
        title.textContent = 'Edit Privacy Policy';
    } else {
        title.textContent = 'Add Privacy Policy';
        form.reset();
        document.getElementById('privacyId').value = '';
    }
    
    modal.classList.add('active');
}

function closePrivacyModal() {
    document.getElementById('privacyModal').classList.remove('active');
    document.getElementById('privacyForm').reset();
    document.getElementById('privacyId').value = '';
}

function savePrivacy(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const id = document.getElementById('privacyId').value;
    const url = id 
        ? CMS_ROUTES.privacy.update(id)
        : CMS_ROUTES.privacy.store;
    
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(Object.fromEntries(formData))
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            closePrivacyModal();
            loadPrivacy(currentPrivacyPage);
        } else {
            showNotification(data.message || 'Error saving privacy policy', 'error');
        }
    })
    .catch(error => {
        console.error('Error saving privacy policy:', error);
        showNotification('Error saving privacy policy', 'error');
    });
}

function editPrivacy(id) {
    fetch(`${CMS_ROUTES.privacy.list}?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.data) {
                const policy = data.data.data.find(p => p.id === id) || data.data.data[0];
                document.getElementById('privacyId').value = policy.id;
                document.getElementById('privacyVersion').value = policy.version || '';
                document.getElementById('privacyContent').value = policy.content || '';
                document.getElementById('privacyStatus').value = policy.status || 'draft';
                document.getElementById('privacyPublishedAt').value = policy.published_at ? formatDateTimeLocal(policy.published_at) : '';
                openPrivacyModal(id);
            }
        });
}

function deletePrivacy(id) {
    if (!confirm('Are you sure you want to delete this privacy policy?')) return;
    
    fetch(CMS_ROUTES.privacy.delete(id), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            loadPrivacy(currentPrivacyPage);
        } else {
            showNotification(data.message || 'Error deleting privacy policy', 'error');
        }
    })
    .catch(error => {
        console.error('Error deleting privacy policy:', error);
        showNotification('Error deleting privacy policy', 'error');
    });
}

// Initialize privacy filters
document.addEventListener('DOMContentLoaded', function() {
    const privacySearch = document.getElementById('privacySearch');
    const privacyStatusFilter = document.getElementById('privacyStatusFilter');
    
    if (privacySearch) {
        let searchTimeout;
        privacySearch.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => loadPrivacy(1), 300);
        });
    }
    
    if (privacyStatusFilter) {
        privacyStatusFilter.addEventListener('change', () => loadPrivacy(1));
    }
});

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function formatDateTimeLocal(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${year}-${month}-${day}T${hours}:${minutes}`;
}

function formatDateRange(startDate, endDate) {
    if (!startDate && !endDate) return '-';
    const start = startDate ? formatDate(startDate) : 'No start';
    const end = endDate ? formatDate(endDate) : 'No end';
    return `${start} - ${end}`;
}

function renderPagination(containerId, paginationData, loadFunction) {
    const container = document.getElementById(containerId);
    if (!container || !paginationData) return;
    
    const currentPage = paginationData.current_page || 1;
    const lastPage = paginationData.last_page || 1;
    
    if (lastPage <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = '<div class="pagination-info">';
    html += `Page ${currentPage} of ${lastPage} (${paginationData.total || 0} total)`;
    html += '</div>';
    html += '<div style="display: flex; gap: 0.5rem;">';
    
    // Previous button
    html += `<button class="pagination-btn" ${currentPage === 1 ? 'disabled' : ''} onclick="${loadFunction.name}(${currentPage - 1})">Previous</button>`;
    
    // Page numbers
    for (let i = 1; i <= lastPage; i++) {
        if (i === 1 || i === lastPage || (i >= currentPage - 2 && i <= currentPage + 2)) {
            html += `<button class="pagination-btn ${i === currentPage ? 'active' : ''}" onclick="${loadFunction.name}(${i})">${i}</button>`;
        } else if (i === currentPage - 3 || i === currentPage + 3) {
            html += '<span class="pagination-info">...</span>';
        }
    }
    
    // Next button
    html += `<button class="pagination-btn" ${currentPage === lastPage ? 'disabled' : ''} onclick="${loadFunction.name}(${currentPage + 1})">Next</button>`;
    html += '</div>';
    
    container.innerHTML = html;
}

function showNotification(message, type = 'info') {
    // Simple notification - you can enhance this with a toast library
    alert(message);
}

