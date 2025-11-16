// Admin Food Listings Management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Automatic filtering
    initializeAutoFilter();
    
    // Apply initial filters if any are set
    applyFilters();
});

// Initialize automatic filtering
function initializeAutoFilter() {
    const searchInput = document.getElementById('search');
    const categorySelect = document.getElementById('category');
    const statusSelect = document.getElementById('status');
    const expirySelect = document.getElementById('expiry');
    
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                applyFilters();
            }, 300);
        });
    }
    
    if (categorySelect) {
        categorySelect.addEventListener('change', applyFilters);
    }
    
    if (statusSelect) {
        statusSelect.addEventListener('change', applyFilters);
    }
    
    if (expirySelect) {
        expirySelect.addEventListener('change', applyFilters);
    }
}

// Apply filters automatically
function applyFilters() {
    const searchInput = document.getElementById('search');
    const categorySelect = document.getElementById('category');
    const statusSelect = document.getElementById('status');
    const expirySelect = document.getElementById('expiry');
    const tableBody = document.getElementById('listingsTableBody');
    
    if (!tableBody) return;
    
    const searchValue = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const categoryValue = categorySelect ? categorySelect.value : 'all';
    const statusValue = statusSelect ? statusSelect.value : 'all';
    const expiryValue = expirySelect ? expirySelect.value : 'all';
    
    const rows = tableBody.querySelectorAll('tr');
    let visibleCount = 0;
    
    rows.forEach(function(row) {
        if (row.classList.contains('no-listings')) {
            row.style.display = 'none';
            return;
        }
        
        const searchText = row.getAttribute('data-search-text') || '';
        const status = row.getAttribute('data-status') || 'active';
        const category = row.getAttribute('data-category') || '';
        const isExpired = row.getAttribute('data-is-expired') === 'true';
        
        // Check search filter
        const matchesSearch = !searchValue || searchText.includes(searchValue);
        
        // Check category filter
        const matchesCategory = categoryValue === 'all' || category === categoryValue.toLowerCase();
        
        // Check status filter
        const matchesStatus = statusValue === 'all' || status === statusValue;
        
        // Check expiry filter
        let matchesExpiry = true;
        if (expiryValue !== 'all') {
            if (expiryValue === 'expired') {
                matchesExpiry = isExpired;
            } else if (expiryValue === 'expiring_soon') {
                // Check if expiring within 7 days (this would need to be calculated from the expiry date)
                // For now, we'll use a simple check based on the expired status
                matchesExpiry = !isExpired; // Simplified - would need actual days calculation
            } else if (expiryValue === 'active') {
                matchesExpiry = !isExpired;
            }
        }
        
        // Show/hide row based on filters
        if (matchesSearch && matchesCategory && matchesStatus && matchesExpiry) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update count
    const tableHeader = document.getElementById('listingsCountHeader');
    if (tableHeader) {
        const totalRows = Array.from(rows).filter(row => !row.classList.contains('no-listings')).length;
        if (visibleCount === totalRows) {
            tableHeader.textContent = `All Food Listings (${visibleCount})`;
        } else {
            tableHeader.textContent = `All Food Listings (${visibleCount} of ${totalRows})`;
        }
    }
    
    // Show "no listings" message if no rows are visible
    const noListingsRow = tableBody.querySelector('.no-listings');
    if (visibleCount === 0 && !noListingsRow) {
        const newRow = document.createElement('tr');
        newRow.className = 'no-listings';
        newRow.innerHTML = '<td colspan="8" class="no-listings">No food listings found matching the filters.</td>';
        tableBody.appendChild(newRow);
    } else if (visibleCount > 0 && noListingsRow) {
        noListingsRow.remove();
    }
}

// Clear filters
function clearFilters() {
    window.location.href = '/admin/food-listings';
}

// Update status
function updateStatus(id, status) {
    const action = status === 'active' ? 'activate' : 'disable';
    if (!confirm(`Are you sure you want to ${action} this food listing?`)) {
        return;
    }
    
    fetch(`/admin/food-listings/${id}/status`, {
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

// Delete listing
function deleteListing(id) {
    if (!confirm('Are you sure you want to delete this food listing? This action cannot be undone.')) {
        return;
    }
    
    fetch(`/admin/food-listings/${id}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Food listing deleted successfully', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast(data.message || 'Failed to delete food listing', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while deleting the food listing', 'error');
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

