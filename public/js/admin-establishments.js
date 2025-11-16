// Admin Establishment Management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Modal elements
    const violationsModal = document.getElementById('violationsModal');
    const closeViolationsModal = document.getElementById('closeViolationsModal');
    const closeViolationsModalBtn = document.getElementById('closeViolationsModalBtn');
    
    const addViolationModal = document.getElementById('addViolationModal');
    const closeAddViolationModal = document.getElementById('closeAddViolationModal');
    const cancelAddViolationBtn = document.getElementById('cancelAddViolationBtn');
    const saveViolationBtn = document.getElementById('saveViolationBtn');
    const addViolationForm = document.getElementById('addViolationForm');
    
    // Close violations modal handlers
    if (closeViolationsModal) {
        closeViolationsModal.addEventListener('click', closeViolationsModal);
    }
    
    if (closeViolationsModalBtn) {
        closeViolationsModalBtn.addEventListener('click', closeViolationsModal);
    }
    
    if (violationsModal) {
        violationsModal.addEventListener('click', function(e) {
            if (e.target === violationsModal) {
                closeViolationsModal();
            }
        });
    }
    
    // Close add violation modal handlers
    if (closeAddViolationModal) {
        closeAddViolationModal.addEventListener('click', closeAddViolationModal);
    }
    
    if (cancelAddViolationBtn) {
        cancelAddViolationBtn.addEventListener('click', closeAddViolationModal);
    }
    
    if (addViolationModal) {
        addViolationModal.addEventListener('click', function(e) {
            if (e.target === addViolationModal) {
                closeAddViolationModal();
            }
        });
    }
    
    // Form submission
    if (saveViolationBtn) {
        saveViolationBtn.addEventListener('click', function() {
            if (addViolationForm) {
                addViolationForm.dispatchEvent(new Event('submit'));
            }
        });
    }
    
    if (addViolationForm) {
        addViolationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            saveViolation();
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
    const statusSelect = document.getElementById('status');
    const verifiedSelect = document.getElementById('verified');
    const violationsSelect = document.getElementById('violations');
    
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
    
    if (violationsSelect) {
        violationsSelect.addEventListener('change', applyFilters);
    }
}

// Apply filters automatically
function applyFilters() {
    const searchInput = document.getElementById('search');
    const statusSelect = document.getElementById('status');
    const verifiedSelect = document.getElementById('verified');
    const violationsSelect = document.getElementById('violations');
    const tableBody = document.getElementById('establishmentsTableBody');
    
    if (!tableBody) return;
    
    const searchValue = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const statusValue = statusSelect ? statusSelect.value : 'all';
    const verifiedValue = verifiedSelect ? verifiedSelect.value : 'all';
    const violationsValue = violationsSelect ? violationsSelect.value : 'all';
    
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
        const violationsCount = parseInt(row.getAttribute('data-violations-count') || 0);
        
        // Check search filter
        const matchesSearch = !searchValue || searchText.includes(searchValue);
        
        // Check status filter
        const matchesStatus = statusValue === 'all' || status === statusValue;
        
        // Check verified filter
        const matchesVerified = verifiedValue === 'all' || 
            (verifiedValue === 'verified' && verified === 'true') ||
            (verifiedValue === 'unverified' && verified === 'false');
        
        // Check violations filter
        const matchesViolations = violationsValue === 'all' ||
            (violationsValue === 'has_violations' && violationsCount > 0) ||
            (violationsValue === 'no_violations' && violationsCount === 0);
        
        // Show/hide row based on filters
        if (matchesSearch && matchesStatus && matchesVerified && matchesViolations) {
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
        newRow.innerHTML = '<td colspan="8" class="no-establishments">No establishments found matching the filters.</td>';
        tableBody.appendChild(newRow);
    } else if (visibleCount > 0 && noEstablishmentsRow) {
        noEstablishmentsRow.remove();
    }
}

// Clear filters
function clearFilters() {
    window.location.href = '/admin/establishments';
}

// View violations
function viewViolations(establishmentId, violations) {
    const modal = document.getElementById('violationsModal');
    const modalBody = document.getElementById('violationsModalBody');
    
    if (!modal || !modalBody) return;
    
    if (!violations || violations.length === 0) {
        modalBody.innerHTML = '<p>No violations recorded for this establishment.</p>';
    } else {
        let html = '<div class="violations-list">';
        violations.forEach(function(violation) {
            const severity = violation.severity || 'low';
            html += `
                <div class="violation-item severity-${severity}">
                    <div class="violation-header">
                        <span class="violation-type">${violation.type || 'Unknown Violation'}</span>
                        <span class="violation-severity ${severity}">${severity}</span>
                    </div>
                    <div class="violation-description">${violation.description || 'No description provided.'}</div>
                    <div class="violation-meta">
                        <span>Date: ${violation.date || 'N/A'}</span>
                        <span>Admin: ${violation.admin || 'System'}</span>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        modalBody.innerHTML = html;
    }
    
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

// Close violations modal
function closeViolationsModal() {
    const modal = document.getElementById('violationsModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

// Add violation
function addViolation(establishmentId) {
    const modal = document.getElementById('addViolationModal');
    const form = document.getElementById('addViolationForm');
    
    if (!modal || !form) return;
    
    document.getElementById('violationEstablishmentId').value = establishmentId;
    form.reset();
    document.getElementById('violationEstablishmentId').value = establishmentId;
    
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

// Close add violation modal
function closeAddViolationModal() {
    const modal = document.getElementById('addViolationModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
    
    const form = document.getElementById('addViolationForm');
    if (form) {
        form.reset();
    }
}

// Save violation
function saveViolation() {
    const establishmentId = document.getElementById('violationEstablishmentId').value;
    
    if (!establishmentId) {
        showToast('Invalid establishment', 'error');
        return;
    }
    
    const formData = new FormData(document.getElementById('addViolationForm'));
    const data = {
        violation_type: formData.get('violation_type'),
        description: formData.get('description'),
        severity: formData.get('severity')
    };
    
    // Show loading state
    const saveBtn = document.getElementById('saveViolationBtn');
    const originalText = saveBtn ? saveBtn.textContent : 'Add Violation';
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.textContent = 'Adding...';
    }
    
    // Send request
    fetch(`/admin/establishments/${establishmentId}/violation`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Violation added successfully', 'success');
            closeAddViolationModal();
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast(data.message || 'Failed to add violation', 'error');
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.textContent = originalText;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while adding the violation', 'error');
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.textContent = originalText;
        }
    });
}

// Toggle verification
function toggleVerification(id, verified) {
    const action = verified ? 'verify' : 'unverify';
    if (!confirm(`Are you sure you want to ${action} this establishment?`)) {
        return;
    }
    
    fetch(`/admin/establishments/${id}/verification`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ verified: verified })
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
    if (!confirm(`Are you sure you want to ${action} this establishment?`)) {
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

