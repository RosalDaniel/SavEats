// Establishment Donation History Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeExportDropdown();
    initializeFilters();
    initializeModal();
});

// Export Dropdown
function initializeExportDropdown() {
    const exportToggle = document.getElementById('exportToggle');
    const exportMenu = document.getElementById('exportMenu');

    if (!exportToggle || !exportMenu) return;

    exportToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        exportMenu.classList.toggle('show');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!exportToggle.contains(e.target) && !exportMenu.contains(e.target)) {
            exportMenu.classList.remove('show');
        }
    });
}

// Filters
function initializeFilters() {
    const clearFiltersBtn = document.getElementById('clearFilters');
    const filterForm = document.getElementById('filterForm');

    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            const inputs = filterForm.querySelectorAll('input, select');
            inputs.forEach(input => {
                if (input.type === 'text' || input.type === 'date') {
                    input.value = '';
                } else if (input.tagName === 'SELECT') {
                    input.selectedIndex = 0;
                }
            });
            filterForm.submit();
        });
    }
}

// Modal
function initializeModal() {
    const modal = document.getElementById('donationDetailsModal');
    const closeBtn = document.getElementById('closeDonationModal');
    const closeBtn2 = document.getElementById('closeDonationModalBtn');

    if (!modal) return;

    const closeModal = () => {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    };

    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }

    if (closeBtn2) {
        closeBtn2.addEventListener('click', closeModal);
    }

    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('show')) {
            closeModal();
        }
    });
}

// View Donation Details
window.viewDonationDetails = function(id) {
    const donations = window.donations || [];
    const donation = donations.find(d => d.id === id);

    if (!donation) {
        showToast('Donation not found', 'error');
        return;
    }

    const modal = document.getElementById('donationDetailsModal');
    const modalBody = document.getElementById('modalDonationBody');
    const modalNumber = document.getElementById('modalDonationNumber');

    if (!modal || !modalBody || !modalNumber) return;

    modalNumber.textContent = `Donation ${donation.donation_number || 'Details'}`;

    const escapeHtml = (text) => {
        if (!text) return 'N/A';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    };

    modalBody.innerHTML = `
        <div class="donation-detail-content">
            <div class="detail-section">
                <h3>Basic Information</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Donation Number:</span>
                        <span class="detail-value">${escapeHtml(donation.donation_number)}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Item Name:</span>
                        <span class="detail-value">${escapeHtml(donation.item_name)}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Category:</span>
                        <span class="detail-value">${escapeHtml(donation.category ? ucfirst(donation.category) : 'N/A')}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Quantity:</span>
                        <span class="detail-value">${donation.quantity} ${donation.unit}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Status:</span>
                        <span class="detail-value"><span class="status-badge status-${donation.status}">${donation.status_display}</span></span>
                    </div>
                </div>
            </div>

            ${donation.description ? `
            <div class="detail-section">
                <h3>Description</h3>
                <p class="detail-description">${escapeHtml(donation.description)}</p>
            </div>
            ` : ''}

            <div class="detail-section">
                <h3>Recipient Information</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Foodbank:</span>
                        <span class="detail-value">${escapeHtml(donation.foodbank_name)}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Date Donated:</span>
                        <span class="detail-value">${escapeHtml(donation.date_donated)}</span>
                    </div>
                </div>
            </div>

            <div class="detail-section">
                <h3>Schedule & Logistics</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Scheduled Date:</span>
                        <span class="detail-value">${escapeHtml(donation.scheduled_date)}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Scheduled Time:</span>
                        <span class="detail-value">${escapeHtml(donation.scheduled_time)}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Pickup Method:</span>
                        <span class="detail-value">${escapeHtml(donation.pickup_method)}</span>
                    </div>
                    ${donation.expiry_date && donation.expiry_date !== 'N/A' ? `
                    <div class="detail-item">
                        <span class="detail-label">Expiry Date:</span>
                        <span class="detail-value">${escapeHtml(donation.expiry_date)}</span>
                    </div>
                    ` : ''}
                    ${donation.collected_at && donation.collected_at !== 'N/A' ? `
                    <div class="detail-item">
                        <span class="detail-label">Collected At:</span>
                        <span class="detail-value">${escapeHtml(donation.collected_at)}</span>
                    </div>
                    ` : ''}
                </div>
            </div>
        </div>
    `;

    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
};

// Helper function
function ucfirst(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
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

// Add toast styles if not already in CSS
if (!document.querySelector('style[data-toast]')) {
    const style = document.createElement('style');
    style.setAttribute('data-toast', 'true');
    style.textContent = `
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 24px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            z-index: 10000;
            transform: translateX(400px);
            transition: transform 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            max-width: 350px;
        }
        .toast.show {
            transform: translateX(0);
        }
        .toast.success { background: #4caf50; }
        .toast.error { background: #f44336; }
        .toast.warning { background: #ff9800; }
        .toast.info { background: #2196f3; }
    `;
    document.head.appendChild(style);
}

