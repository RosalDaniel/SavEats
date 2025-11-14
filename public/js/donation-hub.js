// Donation Hub Page JavaScript
let filteredHistory = [];
let currentPage = 1;
const itemsPerPage = 4;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize data
    filteredHistory = window.donationHistory || [];
    
    // Initialize page
    renderHistoryTable();
    renderRequests();
    
    // Setup event listeners
    setupEventListeners();
});

// Setup event listeners
function setupEventListeners() {
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            const donationHistory = window.donationHistory || [];
            filteredHistory = donationHistory.filter(item => 
                (item.charity || '').toLowerCase().includes(searchTerm) ||
                (item.address || '').toLowerCase().includes(searchTerm) ||
                (item.status || '').toLowerCase().includes(searchTerm)
            );
            currentPage = 1;
            renderHistoryTable();
        });
    }

    // Date filter
    const dateSelect = document.getElementById('dateSelect');
    if (dateSelect) {
        dateSelect.addEventListener('change', (e) => {
            if (e.target.value) {
                showToast('Date filter applied', 'success');
            }
        });
    }

    // Button handlers
    const exportBtn = document.getElementById('exportBtn');
    if (exportBtn) {
        exportBtn.addEventListener('click', () => {
            showToast('Exporting data...', 'success');
        });
    }

    const filterBtn = document.getElementById('filterBtn');
    if (filterBtn) {
        filterBtn.addEventListener('click', () => {
            showToast('Opening filter options', 'info');
        });
    }

    const seeAllLink = document.getElementById('seeAllLink');
    if (seeAllLink) {
        seeAllLink.addEventListener('click', (e) => {
            e.preventDefault();
            showToast('Loading all donation requests...', 'info');
        });
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            // Close any open modals or menus
        }
    });
}

// Toast notification
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => toast.classList.add('show'), 10);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Render donation history table
function renderHistoryTable() {
    const tableBody = document.getElementById('tableBody');
    const mobileCards = document.getElementById('mobileCards');
    
    if (!tableBody || !mobileCards) return;
    
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const pageHistory = filteredHistory.slice(startIndex, endIndex);

    // Desktop table
    if (pageHistory.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 40px; color: #999;">No donation history found</td></tr>';
    } else {
        tableBody.innerHTML = pageHistory.map(item => `
            <tr>
                <td>${escapeHtml(item.charity || 'N/A')}</td>
                <td>${escapeHtml(item.address || 'N/A')}</td>
                <td>${escapeHtml(item.phone || 'N/A')}</td>
                <td><span class="status-badge ${item.status || 'pending'}">${(item.status || 'pending').toLowerCase()}</span></td>
                <td>
                    <button class="actions-btn" onclick="viewDetails(${item.id || 0})" aria-label="More actions">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="#666">
                            <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                        </svg>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    // Mobile cards
    if (pageHistory.length === 0) {
        mobileCards.innerHTML = '<div class="history-card" style="text-align: center; padding: 40px; color: #999;">No donation history found</div>';
    } else {
        mobileCards.innerHTML = pageHistory.map(item => `
            <div class="history-card">
                <div class="history-card-header">
                    <div>
                        <div class="history-card-title">${escapeHtml(item.charity || 'N/A')}</div>
                    </div>
                    <span class="status-badge ${item.status || 'pending'}">${(item.status || 'pending').toLowerCase()}</span>
                </div>
                <div class="history-card-detail">
                    <strong>Address:</strong>
                    <span>${escapeHtml(item.address || 'N/A')}</span>
                </div>
                <div class="history-card-detail">
                    <strong>Phone:</strong>
                    <span>${escapeHtml(item.phone || 'N/A')}</span>
                </div>
            </div>
        `).join('');
    }

    renderPagination();
}

// Render pagination
function renderPagination() {
    const pagination = document.getElementById('pagination');
    if (!pagination) return;
    
    const totalPages = Math.ceil(filteredHistory.length / itemsPerPage);

    if (totalPages <= 1) {
        pagination.innerHTML = '';
        return;
    }

    let paginationHTML = `
        <button onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>
            Previous
        </button>
    `;

    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
            paginationHTML += `
                <button 
                    onclick="changePage(${i})" 
                    class="${i === currentPage ? 'active' : ''}"
                >
                    ${i}
                </button>
            `;
        } else if (i === currentPage - 2 || i === currentPage + 2) {
            paginationHTML += '<span>...</span>';
        }
    }

    paginationHTML += `
        <button onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>
            Next
        </button>
    `;

    pagination.innerHTML = paginationHTML;
}

// Change page
function changePage(page) {
    const totalPages = Math.ceil(filteredHistory.length / itemsPerPage);
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    renderHistoryTable();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Render donation requests
function renderRequests() {
    const requestsGrid = document.getElementById('requestsGrid');
    if (!requestsGrid) return;
    
    const donationRequests = window.donationRequests || [];
    
    if (donationRequests.length === 0) {
        requestsGrid.innerHTML = '<div style="text-align: center; padding: 40px; color: #999; grid-column: 1 / -1;">No donation requests available</div>';
        return;
    }
    
    requestsGrid.innerHTML = donationRequests.map(request => `
        <div class="request-card">
            <div class="request-logo">
                <svg viewBox="0 0 200 80">
                    <text x="100" y="30" font-family="Georgia, serif" font-size="24" font-weight="bold" fill="#4a7c59" text-anchor="middle">FOOD BANK</text>
                    <path d="M85 40 L90 35 L95 40 L100 35 L105 40 L110 35 L115 40" fill="none" stroke="#7AB267" stroke-width="2"/>
                </svg>
            </div>
            <div class="request-info">
                <h3>${escapeHtml(request.charity || 'N/A')}</h3>
                <p>${escapeHtml(request.address || 'N/A')}</p>
            </div>
            <div class="request-details">
                <div class="detail-item">
                    <div class="detail-label">${escapeHtml(request.item || 'Item')}</div>
                    <div class="detail-value">${escapeHtml(request.quantity || '0 pcs.')}</div>
                </div>
            </div>
            <div class="request-actions">
                <button class="btn btn-primary" onclick="donateNow(${request.id || 0})">Donate Now</button>
                <button class="btn btn-secondary" onclick="viewRequestDetails(${request.id || 0})">View Details</button>
            </div>
        </div>
    `).join('');
}

// Action handlers
function viewDetails(id) {
    showToast('Viewing donation details', 'info');
    // Add navigation or modal logic here
}

function donateNow(id) {
    showToast('Redirecting to donation form...', 'success');
    // Add navigation logic here
}

function viewRequestDetails(id) {
    showToast('Viewing request details', 'info');
    // Add navigation or modal logic here
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

