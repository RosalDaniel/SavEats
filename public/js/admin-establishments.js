// Admin Establishment Management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Modal elements
    // Automatic filtering
    initializeAutoFilter();
    
    // Apply initial filters if any are set
    applyFilters();
});

// Initialize automatic filtering
function initializeAutoFilter() {
    const searchInput = document.getElementById('search');
    const statusSelect = document.getElementById('status');
    const verifiedSelect = document.getElementById('verified');
    
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                applyFilters();
            }, 300);
        });
    }
    
    if (statusSelect) {
        statusSelect.addEventListener('change', applyFilters);
    }
    
    if (verifiedSelect) {
        verifiedSelect.addEventListener('change', applyFilters);
    }
}

// Apply filters automatically
function applyFilters() {
    const searchInput = document.getElementById('search');
    const statusSelect = document.getElementById('status');
    const verifiedSelect = document.getElementById('verified');
    const tableBody = document.getElementById('establishmentsTableBody');
    
    if (!tableBody) return;
    
    const searchValue = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const statusValue = statusSelect ? statusSelect.value : 'all';
    const verifiedValue = verifiedSelect ? verifiedSelect.value : 'all';
    
    const rows = tableBody.querySelectorAll('tr');
    let visibleCount = 0;
    
    rows.forEach(function(row) {
        if (row.classList.contains('no-establishments')) {
            row.style.display = 'none';
            return;
        }
        
        const searchText = row.getAttribute('data-search-text') || '';
        const status = row.getAttribute('data-status') || 'active';
        const verified = row.getAttribute('data-verified') || 'false';
        
        // Check search filter
        const matchesSearch = !searchValue || searchText.includes(searchValue);
        
        // Check status filter
        const matchesStatus = statusValue === 'all' || status === statusValue;
        
        // Check verified filter
        const matchesVerified = verifiedValue === 'all' || 
            (verifiedValue === 'verified' && verified === 'true') ||
            (verifiedValue === 'unverified' && verified === 'false');
        
        // Show/hide row based on filters
        if (matchesSearch && matchesStatus && matchesVerified) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update count
    const tableHeader = document.getElementById('establishmentsCountHeader');
    if (tableHeader) {
        const totalRows = Array.from(rows).filter(row => !row.classList.contains('no-establishments')).length;
        if (visibleCount === totalRows) {
            tableHeader.textContent = `All Establishments (${visibleCount})`;
        } else {
            tableHeader.textContent = `All Establishments (${visibleCount} of ${totalRows})`;
        }
    }
    
    // Show "no establishments" message if no rows are visible
    const noEstablishmentsRow = tableBody.querySelector('.no-establishments');
    if (visibleCount === 0 && !noEstablishmentsRow) {
        const newRow = document.createElement('tr');
        newRow.className = 'no-establishments';
        newRow.innerHTML = '<td colspan="7" class="no-establishments">No establishments found matching the filters.</td>';
        tableBody.appendChild(newRow);
    } else if (visibleCount > 0 && noEstablishmentsRow) {
        noEstablishmentsRow.remove();
    }
}

// Clear filters
function clearFilters() {
    window.location.href = '/admin/establishments';
}

// Toggle verification
function toggleVerification(id, verified) {
    const action = verified ? 'verify' : 'unverify';
    const verificationStatus = verified ? 'verified' : 'unverified';
    if (!confirm(`Are you sure you want to ${action} this establishment?`)) {
        return;
    }
    
    fetch(`/admin/establishments/${id}/verification`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ verification_status: verificationStatus })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Verification status updated successfully', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast(data.message || 'Failed to update verification status', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while updating verification status', 'error');
    });
}

// Update status
function updateStatus(id, status) {
    const action = status === 'active' ? 'activate' : 'suspend';
    const actionText = status === 'active' ? 'activate' : 'suspend';
    const message = status === 'active' 
        ? 'Are you sure you want to activate this establishment account? The account will regain full access to the system.'
        : 'Are you sure you want to suspend this establishment account? The user will be immediately logged out and prevented from accessing the system.';
    
    if (!confirm(message)) {
        return;
    }
    
    fetch(`/admin/establishments/${id}/status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Status updated successfully', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast(data.message || 'Failed to update status', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while updating status', 'error');
    });
}

// Delete establishment
function deleteEstablishment(id) {
    if (!confirm('Are you sure you want to delete this establishment? This action cannot be undone.')) {
        return;
    }
    
    fetch(`/admin/establishments/${id}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Establishment deleted successfully', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast(data.message || 'Failed to delete establishment', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while deleting the establishment', 'error');
    });
}

// View establishment details
function viewEstablishmentDetails(id) {
    const modal = document.getElementById('establishmentDetailsModal');
    const modalBody = document.getElementById('establishmentDetailsModalBody');
    const loadingSpinner = document.getElementById('establishmentDetailsLoading');
    const contentDiv = document.getElementById('establishmentDetailsContent');
    
    if (!modal || !modalBody) return;
    
    // Show modal and loading state
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
    loadingSpinner.style.display = 'block';
    contentDiv.style.display = 'none';
    
    // Fetch establishment details
    fetch(`/admin/establishments/${id}/details`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        loadingSpinner.style.display = 'none';
        
        if (data.success && data.data) {
            const establishment = data.data;
            let html = '<div class="establishment-details-grid">';
            
            // Business Information
            html += '<div class="detail-section">';
            html += '<h3>Business Information</h3>';
            html += `<div class="detail-item"><div class="detail-label">Business Name</div><div class="detail-value">${establishment.business_name || 'N/A'}</div></div>`;
            html += `<div class="detail-item"><div class="detail-label">Business Type</div><div class="detail-value">${establishment.business_type || 'N/A'}</div></div>`;
            html += `<div class="detail-item"><div class="detail-label">Status</div><div class="detail-value"><span class="status-badge status-${establishment.status}">${establishment.status}</span></div></div>`;
            html += `<div class="detail-item"><div class="detail-label">Verification Status</div><div class="detail-value"><span class="verified-badge ${establishment.verified ? 'verified-yes' : 'verified-no'}">${establishment.verification_status}</span></div></div>`;
            html += `<div class="detail-item"><div class="detail-label">Registered</div><div class="detail-value">${establishment.registered_at}</div></div>`;
            html += '</div>';
            
            // Owner Information
            html += '<div class="detail-section">';
            html += '<h3>Owner Information</h3>';
            html += `<div class="detail-item"><div class="detail-label">Owner Name</div><div class="detail-value">${establishment.owner_name || 'N/A'}</div></div>`;
            html += `<div class="detail-item"><div class="detail-label">Email</div><div class="detail-value">${establishment.email || 'N/A'}</div></div>`;
            html += `<div class="detail-item"><div class="detail-label">Username</div><div class="detail-value">${establishment.username || 'N/A'}</div></div>`;
            html += `<div class="detail-item"><div class="detail-label">Phone</div><div class="detail-value">${establishment.phone_no || 'N/A'}</div></div>`;
            html += '</div>';
            
            // Location Information
            html += '<div class="detail-section">';
            html += '<h3>Location Information</h3>';
            html += `<div class="detail-item"><div class="detail-label">Address</div><div class="detail-value">${establishment.formatted_address || establishment.address || 'N/A'}</div></div>`;
            if (establishment.latitude && establishment.longitude) {
                html += `<div class="detail-item"><div class="detail-label">Coordinates</div><div class="detail-value">${establishment.latitude}, ${establishment.longitude}</div></div>`;
            }
            html += '</div>';
            
            // Business Statistics
            html += '<div class="detail-section">';
            html += '<h3>Business Statistics</h3>';
            html += `<div class="detail-item"><div class="detail-label">Active Listings</div><div class="detail-value">${establishment.active_listings}</div></div>`;
            html += `<div class="detail-item"><div class="detail-label">Total Listings</div><div class="detail-value">${establishment.total_listings}</div></div>`;
            html += `<div class="detail-item"><div class="detail-label">Total Orders</div><div class="detail-value">${establishment.total_orders}</div></div>`;
            html += `<div class="detail-item"><div class="detail-label">Average Rating</div><div class="detail-value">${establishment.avg_rating} (${establishment.total_reviews} reviews)</div></div>`;
            html += `<div class="detail-item"><div class="detail-label">Violations</div><div class="detail-value">${establishment.violations_count || 0}</div></div>`;
            html += '</div>';
            
            // BIR Certificate
            if (establishment.bir_file) {
                html += '<div class="detail-section">';
                html += '<h3>BIR Certificate</h3>';
                html += `<div class="detail-item"><div class="detail-label">Certificate File</div><div class="detail-value"><a href="${establishment.bir_file.startsWith('http') ? establishment.bir_file : '/storage/' + establishment.bir_file}" target="_blank" class="file-link">View Certificate</a></div></div>`;
                html += '</div>';
            }
            
            // Recent Orders
            if (establishment.recent_orders && establishment.recent_orders.length > 0) {
                html += '<div class="detail-section">';
                html += '<h3>Recent Orders (Last 5)</h3>';
                html += '<div class="recent-orders-list">';
                establishment.recent_orders.forEach(order => {
                    html += `<div class="order-item">`;
                    html += `<div class="order-number">${order.order_number}</div>`;
                    html += `<div class="order-info">${order.item_name} - Qty: ${order.quantity} - ₱${parseFloat(order.total_price).toFixed(2)}</div>`;
                    html += `<div class="order-status">Status: <span class="status-badge status-${order.status}">${order.status}</span></div>`;
                    html += `<div class="order-date">${order.created_at}</div>`;
                    html += `</div>`;
                });
                html += '</div>';
                html += '</div>';
            }
            
            html += '</div>';
            
            contentDiv.innerHTML = html;
            contentDiv.style.display = 'block';
        } else {
            contentDiv.innerHTML = '<div class="error-message">Failed to load establishment details. Please try again.</div>';
            contentDiv.style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Error fetching establishment details:', error);
        loadingSpinner.style.display = 'none';
        contentDiv.innerHTML = '<div class="error-message">An error occurred while loading establishment details. Please try again.</div>';
        contentDiv.style.display = 'block';
    });
}

// Close establishment details modal
function closeEstablishmentDetailsModal() {
    const modal = document.getElementById('establishmentDetailsModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

// Initialize modal close handlers
document.addEventListener('DOMContentLoaded', function() {
    const closeBtn = document.getElementById('closeEstablishmentDetailsModal');
    const closeBtnFooter = document.getElementById('closeEstablishmentDetailsModalBtn');
    const modal = document.getElementById('establishmentDetailsModal');
    
    if (closeBtn) {
        closeBtn.addEventListener('click', closeEstablishmentDetailsModal);
    }
    
    if (closeBtnFooter) {
        closeBtnFooter.addEventListener('click', closeEstablishmentDetailsModal);
    }
    
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeEstablishmentDetailsModal();
            }
        });
    }
    
    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal && modal.classList.contains('show')) {
            closeEstablishmentDetailsModal();
        }
    });
});

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

