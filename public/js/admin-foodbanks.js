// Admin Foodbank Management JavaScript
document.addEventListener('DOMContentLoaded', function() {
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
    const tableBody = document.getElementById('foodbanksTableBody');
    
    if (!tableBody) return;
    
    const searchValue = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const statusValue = statusSelect ? statusSelect.value : 'all';
    const verifiedValue = verifiedSelect ? verifiedSelect.value : 'all';
    
    const rows = tableBody.querySelectorAll('tr');
    let visibleCount = 0;
    
    rows.forEach(function(row) {
        if (row.classList.contains('no-foodbanks')) {
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
    const tableHeader = document.getElementById('foodbanksCountHeader');
    if (tableHeader) {
        const totalRows = Array.from(rows).filter(row => !row.classList.contains('no-foodbanks')).length;
        if (visibleCount === totalRows) {
            tableHeader.textContent = `All Food Banks (${visibleCount})`;
        } else {
            tableHeader.textContent = `All Food Banks (${visibleCount} of ${totalRows})`;
        }
    }
    
    // Show "no foodbanks" message if no rows are visible
    const noFoodbanksRow = tableBody.querySelector('.no-foodbanks');
    if (visibleCount === 0 && !noFoodbanksRow) {
        const newRow = document.createElement('tr');
        newRow.className = 'no-foodbanks';
        newRow.innerHTML = '<td colspan="9" class="no-foodbanks">No food banks found matching the filters.</td>';
        tableBody.appendChild(newRow);
    } else if (visibleCount > 0 && noFoodbanksRow) {
        noFoodbanksRow.remove();
    }
}

// Clear filters
function clearFilters() {
    window.location.href = '/admin/foodbanks';
}

// View foodbank details
function viewFoodbankDetails(id) {
    window.location.href = `/admin/foodbanks/${id}`;
}

// Toggle verification
function toggleVerification(id, verified) {
    const action = verified ? 'verify' : 'unverify';
    const verificationStatus = verified ? 'verified' : 'unverified';
    if (!confirm(`Are you sure you want to ${action} this food bank?`)) {
        return;
    }
    
    fetch(`/admin/foodbanks/${id}/verification`, {
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
        ? 'Are you sure you want to activate this food bank account? The account will regain full access to the system.'
        : 'Are you sure you want to suspend this food bank account? The user will be immediately logged out and prevented from accessing the system.';
    
    if (!confirm(message)) {
        return;
    }
    
    fetch(`/admin/foodbanks/${id}/status`, {
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

// Delete foodbank
function deleteFoodbank(id) {
    if (!confirm('Are you sure you want to delete this food bank? This action cannot be undone and will cascade delete all related data.')) {
        return;
    }
    
    fetch(`/admin/foodbanks/${id}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Food bank deleted successfully', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast(data.message || 'Failed to delete food bank', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while deleting the food bank', 'error');
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

