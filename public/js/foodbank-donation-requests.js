// Foodbank Donation Requests Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching functionality
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    // Initialize: Show the active tab on page load
    tabContents.forEach(content => {
        if (content.classList.contains('active')) {
            content.style.display = 'block';
        } else {
            content.style.display = 'none';
        }
    });

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const targetTab = button.getAttribute('data-tab');

            // Remove active class from all buttons and contents
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => {
                content.classList.remove('active');
                content.style.display = 'none';
            });

            // Add active class to clicked button and corresponding content
            button.classList.add('active');
            const targetContent = document.getElementById(`${targetTab}-tab`);
            if (targetContent) {
                targetContent.classList.add('active');
                targetContent.style.display = 'block';
            }
        });
    });

    // Handle hash on page load (for redirects after actions)
    if (window.location.hash) {
        const hash = window.location.hash.substring(1);
        const tabButton = document.querySelector(`.tab-button[data-tab="${hash}"]`);
        if (tabButton) {
            tabButton.click();
            // Remove hash from URL
            window.history.replaceState(null, null, window.location.pathname);
        }
    }

    // Initialize modal close handlers
    const closeModalBtn = document.getElementById('closeDonationRequestModal');
    const closeModalBtn2 = document.getElementById('closeDonationRequestModalBtn');
    const modal = document.getElementById('donationRequestDetailsModal');

    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', closeDonationRequestModal);
    }

    if (closeModalBtn2) {
        closeModalBtn2.addEventListener('click', closeDonationRequestModal);
    }

    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeDonationRequestModal();
            }
        });
    }

    // Close modal on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal && modal.classList.contains('show')) {
            closeDonationRequestModal();
        }
    });
    
    // Update tab counts function
    if (typeof updateTabCounts !== 'function') {
        window.updateTabCounts = function() {
            const incomingCount = (window.incomingRequests || []).length;
            const acceptedCount = (window.acceptedRequests || []).length;
            const declinedCount = (window.declinedRequests || []).length;
            const completedCount = (window.completedRequests || []).length;
            
            const incomingTab = document.querySelector('.tab-button[data-tab="incoming"] .tab-count');
            const acceptedTab = document.querySelector('.tab-button[data-tab="accepted"] .tab-count');
            const declinedTab = document.querySelector('.tab-button[data-tab="declined"] .tab-count');
            const completedTab = document.querySelector('.tab-button[data-tab="completed"] .tab-count');
            
            if (incomingTab) incomingTab.textContent = `(${incomingCount})`;
            if (acceptedTab) acceptedTab.textContent = `(${acceptedCount})`;
            if (declinedTab) declinedTab.textContent = `(${declinedCount})`;
            if (completedTab) completedTab.textContent = `(${completedCount})`;
        };
    }
    
    // Initialize tab counts on page load
    updateTabCounts();
});

// Close donation request modal
function closeDonationRequestModal() {
    const modal = document.getElementById('donationRequestDetailsModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

// View donation request details - Enhanced version
window.viewDonationRequestDetails = function(requestId) {
    // Combine all request arrays to find the request
    const allRequests = [
        ...(window.incomingRequests || []),
        ...(window.acceptedRequests || []),
        ...(window.declinedRequests || []),
        ...(window.completedRequests || [])
    ];
    
    const request = allRequests.find(r => r.id === requestId || String(r.id) === String(requestId));
    
    if (!request) {
        if (typeof showToast !== 'undefined') {
            showToast('Donation request not found', 'error');
        } else {
            alert('Donation request not found');
        }
        return;
    }

    const modal = document.getElementById('donationRequestDetailsModal');
    const modalBody = document.getElementById('donationRequestModalBody');
    const modalTitle = document.getElementById('modalRequestTitle');
    const loading = document.getElementById('donationRequestLoading');

    if (!modal || !modalBody || !modalTitle) return;

    modalTitle.textContent = `Donation Request: ${request.item_name || 'Details'}`;

    // Show loading state
    if (loading) loading.style.display = 'block';
    modalBody.innerHTML = '';

    // Escape HTML helper
    const escapeHtml = (text) => {
        if (!text) return 'N/A';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    };

    // Format status display
    const statusMap = {
        'pending': 'Pending',
        'pending_confirmation': 'Pending Confirmation',
        'accepted': 'Accepted',
        'declined': 'Declined',
        'completed': 'Completed'
    };

    const statusDisplay = statusMap[request.status] || request.status || 'Unknown';
    const statusClass = request.status === 'pending_confirmation' ? 'pending' : request.status;

    // Build modal content
    const html = `
        <div class="donation-detail-content">
            <!-- Basic Information -->
            <div class="detail-section">
                <h3>Basic Information</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Request ID:</span>
                        <span class="detail-value">${escapeHtml(request.id)}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Item Name:</span>
                        <span class="detail-value">${escapeHtml(request.item_name || 'N/A')}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Category:</span>
                        <span class="detail-value">${escapeHtml(request.category ? ucfirst(request.category) : 'N/A')}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Quantity:</span>
                        <span class="detail-value">${request.quantity || 0} ${request.unit || 'pcs'}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Status:</span>
                        <span class="detail-value"><span class="status-badge status-${statusClass}">${statusDisplay}</span></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Establishment:</span>
                        <span class="detail-value">${escapeHtml(request.establishment_name || 'N/A')}</span>
                    </div>
                </div>
            </div>

            ${request.description ? `
            <div class="detail-section">
                <h3>Description</h3>
                <p class="detail-description">${escapeHtml(request.description)}</p>
            </div>
            ` : ''}

            <!-- Schedule & Logistics -->
            <div class="detail-section">
                <h3>Schedule & Logistics</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Scheduled Date:</span>
                        <span class="detail-value">${escapeHtml(request.scheduled_date_display || 'N/A')}</span>
                    </div>
                    ${request.scheduled_time_display && request.scheduled_time_display !== 'N/A' ? `
                    <div class="detail-item">
                        <span class="detail-label">Scheduled Time:</span>
                        <span class="detail-value">${escapeHtml(request.scheduled_time_display)}</span>
                    </div>
                    ` : ''}
                    <div class="detail-item">
                        <span class="detail-label">Pickup Method:</span>
                        <span class="detail-value">${escapeHtml(request.pickup_method_display || 'N/A')}</span>
                    </div>
                    ${request.expiry_date_display && request.expiry_date_display !== 'N/A' ? `
                    <div class="detail-item">
                        <span class="detail-label">Expiry Date:</span>
                        <span class="detail-value">${escapeHtml(request.expiry_date_display)}</span>
                    </div>
                    ` : ''}
                </div>
            </div>

            ${request.donation_number ? `
            <div class="detail-section">
                <h3>Donation Information</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Donation Number:</span>
                        <span class="detail-value">${escapeHtml(request.donation_number)}</span>
                    </div>
                </div>
            </div>
            ` : ''}

            <!-- Timestamps -->
            <div class="detail-section">
                <h3>Timestamps</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Requested:</span>
                        <span class="detail-value">${escapeHtml(request.created_at_display || 'N/A')}</span>
                    </div>
                    ${request.accepted_at_display ? `
                    <div class="detail-item">
                        <span class="detail-label">Accepted:</span>
                        <span class="detail-value">${escapeHtml(request.accepted_at_display)}</span>
                    </div>
                    ` : ''}
                    ${request.fulfilled_at_display ? `
                    <div class="detail-item">
                        <span class="detail-label">Completed:</span>
                        <span class="detail-value">${escapeHtml(request.fulfilled_at_display)}</span>
                    </div>
                    ` : ''}
                    <div class="detail-item">
                        <span class="detail-label">Last Updated:</span>
                        <span class="detail-value">${escapeHtml(request.updated_at_display || 'N/A')}</span>
                    </div>
                </div>
            </div>
        </div>
    `;

    modalBody.innerHTML = html;
    if (loading) loading.style.display = 'none';
    
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
};

// Helper function for ucfirst
function ucfirst(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

