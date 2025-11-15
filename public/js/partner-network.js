// Partner Network Page JavaScript

(function() {
    'use strict';

    // Escape HTML helper function
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Initialize partners data from window object or use default
    let partners = window.partners || [
        { id: 1, name: 'Joy Grocery Store', type: 'grocery', location: '31 Luna Street, Cebu City', rating: 4.8, donations: 45, impact: 120 },
        { id: 2, name: 'Sunrise Bakery', type: 'bakery', location: '12 Osmeña Blvd, Cebu City', rating: 4.6, donations: 38, impact: 95 },
        { id: 3, name: 'Green Valley Farm', type: 'farm', location: 'Talamban, Cebu City', rating: 4.9, donations: 52, impact: 156 },
        { id: 4, name: 'Metro Supermarket', type: 'grocery', location: 'Ayala Center, Cebu City', rating: 4.7, donations: 61, impact: 183 },
        { id: 5, name: 'Golden Bread House', type: 'bakery', location: 'Colon Street, Cebu City', rating: 4.5, donations: 29, impact: 87 },
        { id: 6, name: 'Fresh Harvest Cafe', type: 'restaurant', location: 'IT Park, Cebu City', rating: 4.8, donations: 33, impact: 99 },
        { id: 7, name: 'City Market', type: 'grocery', location: 'Carbon Market, Cebu City', rating: 4.4, donations: 41, impact: 123 },
        { id: 8, name: 'Artisan Bakeshop', type: 'bakery', location: 'Banilad, Cebu City', rating: 4.7, donations: 35, impact: 105 },
        { id: 9, name: 'Organic Roots Farm', type: 'farm', location: 'Busay, Cebu City', rating: 4.9, donations: 48, impact: 144 },
        { id: 10, name: 'Daily Groceries', type: 'grocery', location: 'Mabolo, Cebu City', rating: 4.6, donations: 37, impact: 111 },
        { id: 11, name: 'The Bread Corner', type: 'bakery', location: 'Mandaue City', rating: 4.8, donations: 42, impact: 126 },
        { id: 12, name: 'Seaside Restaurant', type: 'restaurant', location: 'SRP, Cebu City', rating: 4.5, donations: 28, impact: 84 }
    ];

    let filteredPartners = [...partners];

    // Render partners grid
    function renderPartners() {
        const grid = document.getElementById('partnersGrid');
        const resultCount = document.getElementById('resultCount');
        
        if (!grid || !resultCount) return;
        
        resultCount.textContent = `${filteredPartners.length} Partner${filteredPartners.length !== 1 ? 's' : ''}`;
        
        if (filteredPartners.length === 0) {
            grid.innerHTML = '<div style="text-align:center;padding:60px 20px;color:#999;">No partners found matching your criteria.</div>';
            return;
        }
        
        grid.innerHTML = filteredPartners.map(p => {
            const stars = Array(5).fill().map((_, i) => 
                `<svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>`
            ).join('');
            
            // Escape HTML and handle UUID strings
            const partnerId = typeof p.id === 'string' ? `'${p.id}'` : p.id;
            
            return `
                <div class="partner-card" onclick="window.showPartnerDetails('${p.id}')">
                    <div class="partner-image">
                        <svg viewBox="0 0 24 24">
                            <path d="M18 6h-2c0-2.21-1.79-4-4-4S8 3.79 8 6H6c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-6-2c1.1 0 2 .9 2 2h-4c0-1.1.9-2 2-2zm6 16H6V8h2v2c0 .55.45 1 1 1s1-.45 1-1V8h4v2c0 .55.45 1 1 1s1-.45 1-1V8h2v12z"/>
                        </svg>
                        <span class="partner-type-badge ${p.type}">${p.type}</span>
                    </div>
                    <div class="partner-content">
                        <div class="partner-name">${escapeHtml(p.name)}</div>
                        <div class="partner-location">
                            <svg viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                            ${escapeHtml(p.location)}
                        </div>
                        <div class="partner-rating">
                            <div class="stars">${stars}</div>
                            <span class="rating-value">${p.rating}</span>
                        </div>
                        <div class="partner-stats">
                            <div class="stat-item">
                                <span class="stat-value">${p.donations}</span>
                                <span class="stat-label">Donations</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-value">${p.impact}</span>
                                <span class="stat-label">Meals</span>
                            </div>
                        </div>
                        <div class="partner-actions">
                            <button class="btn btn-primary" onclick="event.stopPropagation(); window.showPartnerDetails('${p.id}')">View Details</button>
                            <button class="btn btn-secondary" onclick="event.stopPropagation(); window.contactPartner('${p.id}')">Contact</button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    // Show partner details modal
    window.showPartnerDetails = function(id) {
        const partner = partners.find(p => p.id === id || p.id === String(id));
        if (!partner) {
            console.error('Partner not found with id:', id);
            showToast('Partner not found', 'error');
            return;
        }
        
        const modalTitle = document.getElementById('modalTitle');
        const modalBody = document.getElementById('modalBody');
        const detailModal = document.getElementById('detailModal');
        
        if (!modalTitle || !modalBody || !detailModal) return;
        
        modalTitle.textContent = partner.name;
        
        modalBody.innerHTML = `
            <div class="partner-detail-image">
                <svg viewBox="0 0 24 24">
                    <path d="M18 6h-2c0-2.21-1.79-4-4-4S8 3.79 8 6H6c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-6-2c1.1 0 2 .9 2 2h-4c0-1.1.9-2 2-2zm6 16H6V8h2v2c0 .55.45 1 1 1s1-.45 1-1V8h4v2c0 .55.45 1 1 1s1-.45 1-1V8h2v12z"/>
                </svg>
            </div>
            <div class="detail-section">
                <h3>Business Information</h3>
                <div class="detail-row"><span class="detail-label">Business Name:</span><span class="detail-value">${escapeHtml(partner.name)}</span></div>
                <div class="detail-row"><span class="detail-label">Type:</span><span class="detail-value">${partner.type ? partner.type.charAt(0).toUpperCase() + partner.type.slice(1) : 'N/A'}</span></div>
                <div class="detail-row"><span class="detail-label">Location:</span><span class="detail-value">${escapeHtml(partner.location)}</span></div>
                <div class="detail-row"><span class="detail-label">Owner:</span><span class="detail-value">${escapeHtml(partner.owner || 'N/A')}</span></div>
                <div class="detail-row"><span class="detail-label">Email:</span><span class="detail-value">${escapeHtml(partner.email || 'N/A')}</span></div>
                <div class="detail-row"><span class="detail-label">Phone:</span><span class="detail-value">${escapeHtml(partner.phone || 'N/A')}</span></div>
                <div class="detail-row"><span class="detail-label">Rating:</span><span class="detail-value">${partner.rating} ⭐</span></div>
            </div>
            <div class="detail-section">
                <h3>Partnership Statistics</h3>
                <div class="detail-row"><span class="detail-label">Total Donations:</span><span class="detail-value">${partner.donations || 0}</span></div>
                <div class="detail-row"><span class="detail-label">Meals Provided:</span><span class="detail-value">${partner.impact || 0}</span></div>
                <div class="detail-row"><span class="detail-label">Partnership Since:</span><span class="detail-value">${partner.registered_at || 'N/A'}</span></div>
            </div>
        `;
        
        // Store partner ID for contact button
        detailModal.dataset.partnerId = id;
        detailModal.classList.add('show');
    };

    // Contact partner
    window.contactPartner = function(id) {
        const partner = partners.find(p => p.id === id || p.id === String(id));
        if (partner) {
            showToast(`Contact request sent to ${partner.name}`, 'success');
        } else {
            showToast('Partner not found', 'error');
        }
    };

    // Filter partners
    function filterPartners() {
        const searchInput = document.getElementById('searchInput');
        const typeFilter = document.getElementById('typeFilter');
        const sortFilter = document.getElementById('sortFilter');
        
        if (!searchInput || !typeFilter || !sortFilter) return;
        
        const search = searchInput.value.toLowerCase();
        const type = typeFilter.value;
        const sort = sortFilter.value;
        
        filteredPartners = partners.filter(p => {
            const matchSearch = p.name.toLowerCase().includes(search) || p.location.toLowerCase().includes(search);
            const matchType = type === 'all' || p.type === type;
            return matchSearch && matchType;
        });
        
        if (sort === 'name') {
            filteredPartners.sort((a, b) => a.name.localeCompare(b.name));
        } else if (sort === 'rating') {
            filteredPartners.sort((a, b) => b.rating - a.rating);
        } else if (sort === 'donations') {
            filteredPartners.sort((a, b) => b.donations - a.donations);
        }
        
        renderPartners();
    }

    // Show toast notification
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

    // Update stats
    function updateStats() {
        const totalPartners = document.getElementById('totalPartners');
        
        // Use stats from backend if available, otherwise calculate from partners
        if (window.stats) {
            if (totalPartners) {
                totalPartners.textContent = window.stats.totalPartners || partners.length;
            }
        } else {
            // Fallback to calculating from partners
            if (totalPartners) {
                totalPartners.textContent = partners.length;
            }
        }
    }

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Event listeners
        const searchInput = document.getElementById('searchInput');
        const typeFilter = document.getElementById('typeFilter');
        const sortFilter = document.getElementById('sortFilter');
        const closeModal = document.getElementById('closeModal');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const detailModal = document.getElementById('detailModal');
        const addPartnerBtn = document.getElementById('addPartnerBtn');
        const exportBtn = document.getElementById('exportBtn');
        const contactPartnerBtn = document.getElementById('contactPartnerBtn');

        if (searchInput) {
            searchInput.addEventListener('input', filterPartners);
        }

        if (typeFilter) {
            typeFilter.addEventListener('change', filterPartners);
        }

        if (sortFilter) {
            sortFilter.addEventListener('change', filterPartners);
        }

        if (closeModal) {
            closeModal.addEventListener('click', () => {
                if (detailModal) detailModal.classList.remove('show');
            });
        }

        if (closeModalBtn) {
            closeModalBtn.addEventListener('click', () => {
                if (detailModal) detailModal.classList.remove('show');
            });
        }

        if (detailModal) {
            detailModal.addEventListener('click', (e) => {
                if (e.target === detailModal) {
                    detailModal.classList.remove('show');
                }
            });
        }

        if (addPartnerBtn) {
            addPartnerBtn.addEventListener('click', () => {
                showToast('Add Partner feature coming soon!', 'info');
            });
        }

        if (exportBtn) {
            exportBtn.addEventListener('click', () => {
                showToast('Export feature coming soon!', 'info');
            });
        }

        if (contactPartnerBtn) {
            contactPartnerBtn.addEventListener('click', () => {
                // Get partner ID from modal (you might need to store this)
                showToast('Contact feature coming soon!', 'info');
            });
        }

        // Initialize
        updateStats();
        renderPartners();
        
        console.log('Partner Network page initialized successfully!');
    });
})();

