// Admin Deletion Requests JavaScript

let currentRequestId = null;

// Make functions globally accessible
window.approveRequest = function(requestId) {
    currentRequestId = requestId;
    const modal = document.getElementById('approvalModal');
    if (modal) {
        modal.classList.add('show');
        document.getElementById('approvalNotes').value = '';
    }
}

window.closeApprovalModal = function() {
    const modal = document.getElementById('approvalModal');
    if (modal) {
        modal.classList.remove('show');
    }
    currentRequestId = null;
}

window.confirmApprove = function() {
    console.log('confirmApprove called, currentRequestId:', currentRequestId);
    
    if (!currentRequestId) {
        console.error('No request ID set');
        showToast('Error: No request selected', 'error');
        return;
    }
    
    const notes = document.getElementById('approvalNotes')?.value || '';
    const confirmBtn = document.querySelector('#approvalModal .btn-confirm');
    
    if (!confirmBtn) {
        console.error('Confirm button not found');
        return;
    }
    
    const originalText = confirmBtn.textContent;
    
    confirmBtn.disabled = true;
    confirmBtn.textContent = 'Processing...';
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                     document.querySelector('input[name="_token"]')?.value;
    
    if (!csrfToken) {
        console.error('CSRF token not found');
        showToast('Error: Security token missing. Please refresh the page.', 'error');
        confirmBtn.disabled = false;
        confirmBtn.textContent = originalText;
        return;
    }
    
    console.log('Sending approval request for ID:', currentRequestId);
    
    fetch(`/admin/deletion-requests/${currentRequestId}/approve`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            admin_notes: notes
        })
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers.get('content-type'));
        
        if (!response.ok) {
            // Try to get error message from response
            return response.text().then(text => {
                console.error('Error response:', text);
                try {
                    const json = JSON.parse(text);
                    throw new Error(json.message || 'Request failed');
                } catch (e) {
                    throw new Error(`Server error: ${response.status} ${response.statusText}`);
                }
            });
        }
        
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            showToast(data.message || 'Account deleted successfully', 'success');
            closeApprovalModal();
            // Refresh table without reloading page
            setTimeout(() => {
                refreshTable();
            }, 500);
        } else {
            showToast(data.message || 'Failed to approve deletion request', 'error');
            confirmBtn.disabled = false;
            confirmBtn.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast(error.message || 'An error occurred while approving the request', 'error');
        confirmBtn.disabled = false;
        confirmBtn.textContent = originalText;
    });
}

window.declineRequest = function(requestId) {
    currentRequestId = requestId;
    const modal = document.getElementById('declineModal');
    if (modal) {
        modal.classList.add('show');
        document.getElementById('declineNotes').value = '';
    }
}

window.closeDeclineModal = function() {
    const modal = document.getElementById('declineModal');
    if (modal) {
        modal.classList.remove('show');
    }
    currentRequestId = null;
}

window.confirmDecline = function() {
    if (!currentRequestId) return;
    
    const notes = document.getElementById('declineNotes').value;
    
    if (!notes || notes.trim() === '') {
        showToast('Please provide a reason for declining', 'error');
        return;
    }
    
    const confirmBtn = document.querySelector('#declineModal .btn-confirm');
    const originalText = confirmBtn.textContent;
    
    confirmBtn.disabled = true;
    confirmBtn.textContent = 'Processing...';
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                     document.querySelector('input[name="_token"]')?.value;
    
    fetch(`/admin/deletion-requests/${currentRequestId}/decline`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            admin_notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Deletion request declined successfully', 'success');
            closeDeclineModal();
            // Refresh table without reloading page
            refreshTable();
        } else {
            showToast(data.message || 'Failed to decline deletion request', 'error');
            confirmBtn.disabled = false;
            confirmBtn.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while declining the request', 'error');
        confirmBtn.disabled = false;
        confirmBtn.textContent = originalText;
    });
}

// Auto-filter functionality
let searchTimeout = null;

function autoFilter() {
    const form = document.querySelector('.filters-form');
    if (form) {
        form.submit();
    }
}

// Close modals when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const approvalModal = document.getElementById('approvalModal');
    const declineModal = document.getElementById('declineModal');
    
    if (approvalModal) {
        approvalModal.addEventListener('click', function(e) {
            if (e.target === approvalModal) {
                closeApprovalModal();
            }
        });
    }
    
    if (declineModal) {
        declineModal.addEventListener('click', function(e) {
            if (e.target === declineModal) {
                closeDeclineModal();
            }
        });
    }
    
    // Close modals with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeApprovalModal();
            closeDeclineModal();
        }
    });
    
    // Auto-filter on select change
    const statusSelect = document.getElementById('status');
    const roleSelect = document.getElementById('role');
    
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            autoFilter();
        });
    }
    
    if (roleSelect) {
        roleSelect.addEventListener('change', function() {
            autoFilter();
        });
    }
    
    // Auto-filter on search input with debounce
    const searchInput = document.getElementById('search');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            // Clear existing timeout
            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }
            
            // Set new timeout for debounce (500ms delay)
            searchTimeout = setTimeout(function() {
                autoFilter();
            }, 500);
        });
    }
});

// Refresh table without reloading page
function refreshTable() {
    // Get current filter values
    const statusFilter = document.getElementById('status')?.value || 'all';
    const roleFilter = document.getElementById('role')?.value || 'all';
    const searchQuery = document.getElementById('search')?.value || '';
    
    // Build query string
    const params = new URLSearchParams();
    if (statusFilter !== 'all') params.append('status', statusFilter);
    if (roleFilter !== 'all') params.append('role', roleFilter);
    if (searchQuery) params.append('search', searchQuery);
    
    const queryString = params.toString();
    const url = `/admin/deletion-requests${queryString ? '?' + queryString : ''}`;
    
    // Fetch updated data
    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        if (!html) {
            throw new Error('No content received');
        }
        
        // Create a temporary container to parse the HTML
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
        
        // Extract the table body
        const newTableBody = tempDiv.querySelector('#requestsTableBody');
        const newStats = tempDiv.querySelectorAll('.stat-number');
        const newTableHeader = tempDiv.querySelector('.table-header h2');
        
        if (newTableBody) {
            // Update table body
            const currentTableBody = document.getElementById('requestsTableBody');
            if (currentTableBody) {
                currentTableBody.innerHTML = newTableBody.innerHTML;
            }
        }
        
        // Update statistics
        if (newStats && newStats.length >= 4) {
            const currentStats = document.querySelectorAll('.stat-number');
            currentStats.forEach((stat, index) => {
                if (newStats[index]) {
                    stat.textContent = newStats[index].textContent;
                }
            });
        }
        
        // Update table header count
        if (newTableHeader) {
            const currentTableHeader = document.querySelector('.table-header h2');
            if (currentTableHeader) {
                currentTableHeader.textContent = newTableHeader.textContent;
            }
        }
    })
    .catch(error => {
        console.error('Error refreshing table:', error);
        // Fallback to full page reload if AJAX fails
        window.location.reload();
    });
}

// Toast notification function
function showToast(message, type = 'info') {
    // Remove existing toasts
    const existingToasts = document.querySelectorAll('.toast');
    existingToasts.forEach(toast => toast.remove());
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10001;
        animation: slideIn 0.3s ease-out;
    `;
    
    document.body.appendChild(toast);
    
    // Remove after 3 seconds
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 3000);
}

