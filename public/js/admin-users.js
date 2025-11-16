// Admin User Management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Modal elements
    const editUserModal = document.getElementById('editUserModal');
    const closeEditUserModal = document.getElementById('closeEditUserModal');
    const cancelEditUserBtn = document.getElementById('cancelEditUserBtn');
    const saveUserBtn = document.getElementById('saveUserBtn');
    const editUserForm = document.getElementById('editUserForm');
    
    // Close modal handlers
    if (closeEditUserModal) {
        closeEditUserModal.addEventListener('click', closeEditModal);
    }
    
    if (cancelEditUserBtn) {
        cancelEditUserBtn.addEventListener('click', closeEditModal);
    }
    
    // Close modal when clicking overlay
    if (editUserModal) {
        editUserModal.addEventListener('click', function(e) {
            if (e.target === editUserModal) {
                closeEditModal();
            }
        });
    }
    
    // Form submission
    if (saveUserBtn) {
        saveUserBtn.addEventListener('click', function() {
            if (editUserForm) {
                editUserForm.dispatchEvent(new Event('submit'));
            }
        });
    }
    
    if (editUserForm) {
        editUserForm.addEventListener('submit', function(e) {
            e.preventDefault();
            saveUser();
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
    const roleSelect = document.getElementById('role');
    const statusSelect = document.getElementById('status');
    
    if (searchInput) {
        // Debounce search input
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                applyFilters();
            }, 300);
        });
    }
    
    if (roleSelect) {
        roleSelect.addEventListener('change', applyFilters);
    }
    
    if (statusSelect) {
        statusSelect.addEventListener('change', applyFilters);
    }
}

// Apply filters automatically
function applyFilters() {
    const searchInput = document.getElementById('search');
    const roleSelect = document.getElementById('role');
    const statusSelect = document.getElementById('status');
    const tableBody = document.getElementById('usersTableBody');
    
    if (!tableBody) return;
    
    const searchValue = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const roleValue = roleSelect ? roleSelect.value : 'all';
    const statusValue = statusSelect ? statusSelect.value : 'all';
    
    const rows = tableBody.querySelectorAll('tr');
    let visibleCount = 0;
    
    rows.forEach(function(row) {
        if (row.classList.contains('no-users')) {
            row.style.display = 'none';
            return;
        }
        
        const searchText = row.getAttribute('data-search-text') || '';
        const role = row.getAttribute('data-role') || '';
        const status = row.getAttribute('data-status') || 'active';
        
        // Check search filter
        const matchesSearch = !searchValue || searchText.includes(searchValue);
        
        // Check role filter
        const matchesRole = roleValue === 'all' || role === roleValue;
        
        // Check status filter
        const matchesStatus = statusValue === 'all' || status === statusValue;
        
        // Show/hide row based on filters
        if (matchesSearch && matchesRole && matchesStatus) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update user count
    const tableHeader = document.getElementById('usersCountHeader');
    if (tableHeader) {
        // Count total rows excluding "no-users" row
        const totalRows = Array.from(rows).filter(row => !row.classList.contains('no-users')).length;
        if (visibleCount === totalRows) {
            tableHeader.textContent = `All Users (${visibleCount})`;
        } else {
            tableHeader.textContent = `All Users (${visibleCount} of ${totalRows})`;
        }
    }
    
    // Show "no users" message if no rows are visible
    const noUsersRow = tableBody.querySelector('.no-users');
    if (visibleCount === 0 && !noUsersRow) {
        const newRow = document.createElement('tr');
        newRow.className = 'no-users';
        newRow.innerHTML = '<td colspan="7" class="no-users">No users found matching the filters.</td>';
        tableBody.appendChild(newRow);
    } else if (visibleCount > 0 && noUsersRow) {
        noUsersRow.remove();
    }
}

// Clear filters
function clearFilters() {
    window.location.href = '/admin/users';
}

// Edit user
function editUser(role, id) {
    // Find the user row
    const row = document.querySelector(`tr[data-user-id="${id}"][data-role="${role}"]`);
    if (!row) {
        showToast('User not found', 'error');
        return;
    }
    
    // Get user data from the row
    const userData = {
        role: role,
        id: id,
        name: row.querySelector('.user-name')?.textContent || '',
        email: row.cells[2]?.textContent || '',
        phone: row.cells[3]?.textContent || 'N/A',
    };
    
    // Set form values
    document.getElementById('editUserRole').value = role;
    document.getElementById('editUserId').value = id;
    
    // Show/hide fields based on role
    const nameGroup = document.getElementById('nameGroup');
    const emailGroup = document.getElementById('emailGroup');
    const phoneGroup = document.getElementById('phoneGroup');
    const businessNameGroup = document.getElementById('businessNameGroup');
    const organizationNameGroup = document.getElementById('organizationNameGroup');
    const fnameGroup = document.getElementById('fnameGroup');
    const lnameGroup = document.getElementById('lnameGroup');
    
    // Hide all role-specific fields
    businessNameGroup.style.display = 'none';
    organizationNameGroup.style.display = 'none';
    fnameGroup.style.display = 'none';
    lnameGroup.style.display = 'none';
    nameGroup.style.display = 'none';
    
    // Show appropriate fields based on role
    if (role === 'consumer') {
        const nameParts = userData.name.split(' ');
        fnameGroup.style.display = 'block';
        lnameGroup.style.display = 'block';
        document.getElementById('editFname').value = nameParts[0] || '';
        document.getElementById('editLname').value = nameParts.slice(1).join(' ') || '';
        document.getElementById('editUserEmail').value = userData.email;
        document.getElementById('editUserPhone').value = userData.phone !== 'N/A' ? userData.phone : '';
    } else if (role === 'establishment') {
        businessNameGroup.style.display = 'block';
        document.getElementById('editBusinessName').value = userData.name;
        document.getElementById('editUserEmail').value = userData.email;
        document.getElementById('editUserPhone').value = userData.phone !== 'N/A' ? userData.phone : '';
    } else if (role === 'foodbank') {
        organizationNameGroup.style.display = 'block';
        document.getElementById('editOrganizationName').value = userData.name;
        document.getElementById('editUserEmail').value = userData.email;
        document.getElementById('editUserPhone').value = userData.phone !== 'N/A' ? userData.phone : '';
    }
    
    // Show modal
    const modal = document.getElementById('editUserModal');
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

// Close edit modal
function closeEditModal() {
    const modal = document.getElementById('editUserModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
    
    // Reset form
    const form = document.getElementById('editUserForm');
    if (form) {
        form.reset();
    }
}

// Save user
function saveUser() {
    const role = document.getElementById('editUserRole').value;
    const id = document.getElementById('editUserId').value;
    
    if (!role || !id) {
        showToast('Invalid user data', 'error');
        return;
    }
    
    // Collect form data
    const formData = new FormData(document.getElementById('editUserForm'));
    const data = {};
    for (let [key, value] of formData.entries()) {
        if (value.trim() !== '') {
            data[key] = value.trim();
        }
    }
    
    // Remove role and id from data
    delete data.role;
    delete data.id;
    
    // Show loading state
    const saveBtn = document.getElementById('saveUserBtn');
    const originalText = saveBtn.textContent;
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving...';
    
    // Send request
    fetch(`/admin/users/${role}/${id}/info`, {
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
            showToast(data.message || 'User updated successfully', 'success');
            closeEditModal();
            // Reload page after a short delay
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast(data.message || 'Failed to update user', 'error');
            saveBtn.disabled = false;
            saveBtn.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while updating the user', 'error');
        saveBtn.disabled = false;
        saveBtn.textContent = originalText;
    });
}

// Update user status
function updateStatus(role, id, status) {
    if (!confirm(`Are you sure you want to ${status === 'active' ? 'activate' : 'suspend'} this user?`)) {
        return;
    }
    
    fetch(`/admin/users/${role}/${id}/status`, {
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
            showToast(data.message || 'User status updated successfully', 'success');
            // Reload page after a short delay
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast(data.message || 'Failed to update user status', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while updating user status', 'error');
    });
}

// Delete user
function deleteUser(role, id) {
    if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        return;
    }
    
    fetch(`/admin/users/${role}/${id}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'User deleted successfully', 'success');
            // Reload page after a short delay
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast(data.message || 'Failed to delete user', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while deleting the user', 'error');
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

