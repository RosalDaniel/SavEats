// Admin Donation Hub JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Modal elements
    const recordDetailsModal = document.getElementById('recordDetailsModal');
    const closeRecordDetailsModal = document.getElementById('closeRecordDetailsModal');
    const closeRecordDetailsModalBtn = document.getElementById('closeRecordDetailsModalBtn');
    
    // Close modal handlers
    if (closeRecordDetailsModal) {
        closeRecordDetailsModal.addEventListener('click', function() {
            closeRecordDetailsModalFunc();
        });
    }
    
    if (closeRecordDetailsModalBtn) {
        closeRecordDetailsModalBtn.addEventListener('click', function() {
            closeRecordDetailsModalFunc();
        });
    }
    
    if (recordDetailsModal) {
        recordDetailsModal.addEventListener('click', function(e) {
            if (e.target === recordDetailsModal) {
                closeRecordDetailsModalFunc();
            }
        });
    }
    
    // Automatic filtering
    initializeAutoFilter();
    
    // Apply initial filters if any are set
    applyFilters();
});

// Initialize automatic filtering
function initializeAutoFilter() {
    const searchInput = document.getElementById('search');
    const typeSelect = document.getElementById('type');
    const statusSelect = document.getElementById('status');
    const dateFromInput = document.getElementById('date_from');
    const dateToInput = document.getElementById('date_to');
    
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                applyFilters();
            }, 300);
        });
    }
    
    if (typeSelect) {
        typeSelect.addEventListener('change', applyFilters);
    }
    
    if (statusSelect) {
        statusSelect.addEventListener('change', applyFilters);
    }
    
    if (dateFromInput) {
        dateFromInput.addEventListener('change', applyFilters);
    }
    
    if (dateToInput) {
        dateToInput.addEventListener('change', applyFilters);
    }
}

// Apply filters automatically
function applyFilters() {
    const searchInput = document.getElementById('search');
    const typeSelect = document.getElementById('type');
    const statusSelect = document.getElementById('status');
    const tableBody = document.getElementById('recordsTableBody');
    
    if (!tableBody) return;
    
    const searchValue = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const typeValue = typeSelect ? typeSelect.value : 'all';
    const statusValue = statusSelect ? statusSelect.value : 'all';
    
    const rows = tableBody.querySelectorAll('tr');
    let visibleCount = 0;
    
    rows.forEach(function(row) {
        if (row.classList.contains('no-records')) {
            row.style.display = 'none';
            return;
        }
        
        const searchText = row.getAttribute('data-search-text') || '';
        const type = row.getAttribute('data-type') || 'donation';
        const status = row.getAttribute('data-status') || '';
        
        // Check search filter
        const matchesSearch = !searchValue || searchText.includes(searchValue);
        
        // Check type filter
        const matchesType = typeValue === 'all' || type === typeValue;
        
        // Check status filter
        let matchesStatus = true;
        if (statusValue !== 'all') {
            // Handle status mapping
            const normalizedStatus = status.replace(/-/g, '_');
            const normalizedFilter = statusValue.replace(/-/g, '_');
            matchesStatus = normalizedStatus === normalizedFilter;
        }
        
        // Show/hide row based on filters
        if (matchesSearch && matchesType && matchesStatus) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update count
    const tableHeader = document.getElementById('recordsCountHeader');
    if (tableHeader) {
        const totalRows = Array.from(rows).filter(row => !row.classList.contains('no-records')).length;
        if (visibleCount === totalRows) {
            tableHeader.textContent = `All Records (${visibleCount})`;
        } else {
            tableHeader.textContent = `All Records (${visibleCount} of ${totalRows})`;
        }
    }
    
    // Show "no records" message if no rows are visible
    const noRecordsRow = tableBody.querySelector('.no-records');
    if (visibleCount === 0 && !noRecordsRow) {
        const newRow = document.createElement('tr');
        newRow.className = 'no-records';
        newRow.innerHTML = '<td colspan="9" class="no-records">No records found matching the filters.</td>';
        tableBody.appendChild(newRow);
    } else if (visibleCount > 0 && noRecordsRow) {
        noRecordsRow.remove();
    }
}

// Clear filters
function clearFilters() {
    window.location.href = '/admin/donations';
}

// Export to CSV
function exportToCsv() {
    const searchInput = document.getElementById('search');
    const typeSelect = document.getElementById('type');
    const statusSelect = document.getElementById('status');
    const dateFromInput = document.getElementById('date_from');
    const dateToInput = document.getElementById('date_to');
    
    const params = new URLSearchParams();
    
    if (searchInput && searchInput.value) {
        params.append('search', searchInput.value);
    }
    if (typeSelect && typeSelect.value !== 'all') {
        params.append('type', typeSelect.value);
    }
    if (statusSelect && statusSelect.value !== 'all') {
        params.append('status', statusSelect.value);
    }
    if (dateFromInput && dateFromInput.value) {
        params.append('date_from', dateFromInput.value);
    }
    if (dateToInput && dateToInput.value) {
        params.append('date_to', dateToInput.value);
    }
    
    const url = '/admin/donations/export/csv' + (params.toString() ? '?' + params.toString() : '');
    window.location.href = url;
}

// View record details
function viewRecordDetails(recordData) {
    const modal = document.getElementById('recordDetailsModal');
    const modalBody = document.getElementById('recordDetailsModalBody');
    const modalTitle = document.getElementById('recordDetailsModalTitle');
    
    if (!modal || !modalBody) return;
    
    const isDonation = recordData.record_type === 'donation';
    
    if (modalTitle) {
        modalTitle.textContent = isDonation ? 'Donation Details' : 'Donation Request Details';
    }
    
    let html = '<div class="record-details-grid">';
    
    // Basic Information
    html += '<div class="detail-section">';
    html += '<h3>Basic Information</h3>';
    if (isDonation) {
        html += `<div class="detail-item"><div class="detail-label">Donation Number</div><div class="detail-value">${recordData.donation_number || 'N/A'}</div></div>`;
    } else {
        html += `<div class="detail-item"><div class="detail-label">Request ID</div><div class="detail-value">${recordData.id || 'N/A'}</div></div>`;
    }
    html += `<div class="detail-item"><div class="detail-label">Item Name</div><div class="detail-value">${recordData.item_name || 'N/A'}</div></div>`;
    html += `<div class="detail-item"><div class="detail-label">Category</div><div class="detail-value">${recordData.item_category || recordData.category || 'N/A'}</div></div>`;
    html += `<div class="detail-item"><div class="detail-label">Quantity</div><div class="detail-value">${recordData.quantity || 0} ${recordData.unit || 'pcs'}</div></div>`;
    html += `<div class="detail-item"><div class="detail-label">Status</div><div class="detail-value"><span class="status-badge status-${(recordData.status || '').replace(/_/g, '-')}">${(recordData.status || '').replace(/_/g, ' ')}</span></div></div>`;
    if (recordData.description) {
        html += `<div class="detail-item"><div class="detail-label">Description</div><div class="detail-value">${recordData.description}</div></div>`;
    }
    html += '</div>';
    
    // Establishment Information (for donations)
    if (isDonation && recordData.establishment) {
        html += '<div class="detail-section">';
        html += '<h3>Establishment</h3>';
        html += `<div class="detail-item"><div class="detail-label">Name</div><div class="detail-value">${recordData.establishment.name || 'N/A'}</div></div>`;
        html += `<div class="detail-item"><div class="detail-label">Email</div><div class="detail-value">${recordData.establishment.email || 'N/A'}</div></div>`;
        if (recordData.establishment.phone) {
            html += `<div class="detail-item"><div class="detail-label">Phone</div><div class="detail-value">${recordData.establishment.phone}</div></div>`;
        }
        html += '</div>';
    }
    
    // Food Bank Information
    if (recordData.foodbank) {
        html += '<div class="detail-section">';
        html += '<h3>Food Bank</h3>';
        html += `<div class="detail-item"><div class="detail-label">Name</div><div class="detail-value">${recordData.foodbank.name || 'N/A'}</div></div>`;
        html += `<div class="detail-item"><div class="detail-label">Email</div><div class="detail-value">${recordData.foodbank.email || 'N/A'}</div></div>`;
        if (recordData.foodbank.phone) {
            html += `<div class="detail-item"><div class="detail-label">Phone</div><div class="detail-value">${recordData.foodbank.phone}</div></div>`;
        }
        html += '</div>';
    }
    
    // Donation-specific details
    if (isDonation) {
        html += '<div class="detail-section">';
        html += '<h3>Donation Details</h3>';
        if (recordData.pickup_method) {
            html += `<div class="detail-item"><div class="detail-label">Pickup Method</div><div class="detail-value">${recordData.pickup_method}</div></div>`;
        }
        if (recordData.scheduled_date) {
            html += `<div class="detail-item"><div class="detail-label">Scheduled Date</div><div class="detail-value">${new Date(recordData.scheduled_date).toLocaleDateString()}</div></div>`;
        }
        if (recordData.scheduled_time) {
            html += `<div class="detail-item"><div class="detail-label">Scheduled Time</div><div class="detail-value">${recordData.scheduled_time}</div></div>`;
        }
        if (recordData.collected_at) {
            html += `<div class="detail-item"><div class="detail-label">Collected At</div><div class="detail-value">${new Date(recordData.collected_at).toLocaleString()}</div></div>`;
        }
        if (recordData.expiry_date) {
            html += `<div class="detail-item"><div class="detail-label">Expiry Date</div><div class="detail-value">${new Date(recordData.expiry_date).toLocaleDateString()}</div></div>`;
        }
        if (recordData.is_urgent) {
            html += `<div class="detail-item"><div class="detail-label">Urgent</div><div class="detail-value">Yes</div></div>`;
        }
        if (recordData.donation_request) {
            html += `<div class="detail-item"><div class="detail-label">Related Request</div><div class="detail-value">${recordData.donation_request.item_name || 'N/A'}</div></div>`;
        }
        html += '</div>';
    } else {
        // Request-specific details
        html += '<div class="detail-section">';
        html += '<h3>Request Details</h3>';
        if (recordData.distribution_zone) {
            html += `<div class="detail-item"><div class="detail-label">Distribution Zone</div><div class="detail-value">${recordData.distribution_zone}</div></div>`;
        }
        if (recordData.dropoff_date) {
            html += `<div class="detail-item"><div class="detail-label">Dropoff Date</div><div class="detail-value">${new Date(recordData.dropoff_date).toLocaleDateString()}</div></div>`;
        }
        if (recordData.delivery_option) {
            html += `<div class="detail-item"><div class="detail-label">Delivery Option</div><div class="detail-value">${recordData.delivery_option}</div></div>`;
        }
        if (recordData.address) {
            html += `<div class="detail-item"><div class="detail-label">Address</div><div class="detail-value">${recordData.address}</div></div>`;
        }
        if (recordData.contact_name) {
            html += `<div class="detail-item"><div class="detail-label">Contact Name</div><div class="detail-value">${recordData.contact_name}</div></div>`;
        }
        if (recordData.phone_number) {
            html += `<div class="detail-item"><div class="detail-label">Phone</div><div class="detail-value">${recordData.phone_number}</div></div>`;
        }
        if (recordData.email) {
            html += `<div class="detail-item"><div class="detail-label">Email</div><div class="detail-value">${recordData.email}</div></div>`;
        }
        if (recordData.matches !== undefined) {
            html += `<div class="detail-item"><div class="detail-label">Matches</div><div class="detail-value">${recordData.matches}</div></div>`;
        }
        html += '</div>';
    }
    
    // Date Information
    html += '<div class="detail-section">';
    html += '<h3>Date Information</h3>';
    if (recordData.created_at) {
        html += `<div class="detail-item"><div class="detail-label">Created</div><div class="detail-value">${new Date(recordData.created_at).toLocaleString()}</div></div>`;
    }
    html += '</div>';
    
    html += '</div>';
    
    modalBody.innerHTML = html;
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

// Close record details modal
function closeRecordDetailsModalFunc() {
    const modal = document.getElementById('recordDetailsModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
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

