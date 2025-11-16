// Admin Announcement Management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Modal elements
    const announcementModal = document.getElementById('announcementModal');
    const closeAnnouncementModal = document.getElementById('closeAnnouncementModal');
    const cancelAnnouncementBtn = document.getElementById('cancelAnnouncementBtn');
    const saveAnnouncementBtn = document.getElementById('saveAnnouncementBtn');
    const announcementForm = document.getElementById('announcementForm');
    
    // Close modal handlers
    if (closeAnnouncementModal) {
        closeAnnouncementModal.addEventListener('click', function() {
            closeAnnouncementModalFunc();
        });
    }
    
    if (cancelAnnouncementBtn) {
        cancelAnnouncementBtn.addEventListener('click', function() {
            closeAnnouncementModalFunc();
        });
    }
    
    if (announcementModal) {
        announcementModal.addEventListener('click', function(e) {
            if (e.target === announcementModal) {
                closeAnnouncementModalFunc();
            }
        });
    }
    
    // Form submission
    if (saveAnnouncementBtn && announcementForm) {
        saveAnnouncementBtn.addEventListener('click', function() {
            saveAnnouncement();
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
    const audienceSelect = document.getElementById('audience');
    
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
    
    if (audienceSelect) {
        audienceSelect.addEventListener('change', applyFilters);
    }
}

// Apply filters automatically
function applyFilters() {
    const searchInput = document.getElementById('search');
    const statusSelect = document.getElementById('status');
    const audienceSelect = document.getElementById('audience');
    const tableBody = document.getElementById('announcementsTableBody');
    
    if (!tableBody) return;
    
    const searchValue = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const statusValue = statusSelect ? statusSelect.value : 'all';
    const audienceValue = audienceSelect ? audienceSelect.value : 'all';
    
    const rows = tableBody.querySelectorAll('tr');
    let visibleCount = 0;
    
    rows.forEach(function(row) {
        if (row.classList.contains('no-announcements')) {
            row.style.display = 'none';
            return;
        }
        
        const searchText = row.getAttribute('data-search-text') || '';
        const status = row.getAttribute('data-status') || '';
        const audience = row.getAttribute('data-audience') || '';
        
        // Check search filter
        const matchesSearch = !searchValue || searchText.includes(searchValue);
        
        // Check status filter
        const matchesStatus = statusValue === 'all' || status === statusValue;
        
        // Check audience filter
        const matchesAudience = audienceValue === 'all' || audience === audienceValue;
        
        // Show/hide row based on filters
        if (matchesSearch && matchesStatus && matchesAudience) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update count
    const tableHeader = document.getElementById('announcementsCountHeader');
    if (tableHeader) {
        const totalRows = Array.from(rows).filter(row => !row.classList.contains('no-announcements')).length;
        if (visibleCount === totalRows) {
            tableHeader.textContent = `All Announcements (${visibleCount})`;
        } else {
            tableHeader.textContent = `All Announcements (${visibleCount} of ${totalRows})`;
        }
    }
    
    // Show "no announcements" message if no rows are visible
    const noAnnouncementsRow = tableBody.querySelector('.no-announcements');
    if (visibleCount === 0 && !noAnnouncementsRow) {
        const newRow = document.createElement('tr');
        newRow.className = 'no-announcements';
        newRow.innerHTML = '<td colspan="8" class="no-announcements">No announcements found matching the filters.</td>';
        tableBody.appendChild(newRow);
    } else if (visibleCount > 0 && noAnnouncementsRow) {
        noAnnouncementsRow.remove();
    }
}

// Clear filters
function clearFilters() {
    window.location.href = '/admin/announcements';
}

// Open create modal
function openCreateModal() {
    const modal = document.getElementById('announcementModal');
    const modalTitle = document.getElementById('announcementModalTitle');
    const form = document.getElementById('announcementForm');
    
    if (!modal || !form) return;
    
    if (modalTitle) {
        modalTitle.textContent = 'Create Announcement';
    }
    
    // Reset form
    form.reset();
    document.getElementById('announcementId').value = '';
    
    // Set default published_at to now
    const now = new Date();
    const localDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    const publishedAtInput = document.getElementById('announcementPublishedAt');
    if (publishedAtInput) {
        publishedAtInput.value = localDateTime;
    }
    
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

// Edit announcement
function editAnnouncement(id, announcementData) {
    const modal = document.getElementById('announcementModal');
    const modalTitle = document.getElementById('announcementModalTitle');
    const form = document.getElementById('announcementForm');
    
    if (!modal || !form) return;
    
    if (modalTitle) {
        modalTitle.textContent = 'Edit Announcement';
    }
    
    // Populate form
    document.getElementById('announcementId').value = id;
    document.getElementById('announcementTitle').value = announcementData.title || '';
    document.getElementById('announcementMessage').value = announcementData.message || '';
    document.getElementById('announcementAudience').value = announcementData.target_audience || 'all';
    document.getElementById('announcementStatus').value = announcementData.status || 'active';
    
    // Format dates for datetime-local input
    if (announcementData.published_at) {
        const publishedDate = new Date(announcementData.published_at);
        const localPublishedDateTime = new Date(publishedDate.getTime() - publishedDate.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
        document.getElementById('announcementPublishedAt').value = localPublishedDateTime;
    } else {
        document.getElementById('announcementPublishedAt').value = '';
    }
    
    if (announcementData.expires_at) {
        const expiresDate = new Date(announcementData.expires_at);
        const localExpiresDateTime = new Date(expiresDate.getTime() - expiresDate.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
        document.getElementById('announcementExpiresAt').value = localExpiresDateTime;
    } else {
        document.getElementById('announcementExpiresAt').value = '';
    }
    
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

// Save announcement
function saveAnnouncement() {
    const form = document.getElementById('announcementForm');
    if (!form) return;
    
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    const announcementId = formData.get('id');
    const isEdit = announcementId && announcementId !== '';
    
    const data = {
        title: formData.get('title'),
        message: formData.get('message'),
        target_audience: formData.get('target_audience'),
        status: formData.get('status'),
        published_at: formData.get('published_at') || null,
        expires_at: formData.get('expires_at') || null,
    };
    
    // Validate expires_at is after published_at
    if (data.expires_at && data.published_at) {
        const publishedDate = new Date(data.published_at);
        const expiresDate = new Date(data.expires_at);
        if (expiresDate <= publishedDate) {
            showToast('Expires date must be after published date.', 'error');
            return;
        }
    }
    
    const saveBtn = document.getElementById('saveAnnouncementBtn');
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving...';
    }
    
    const url = isEdit ? `/admin/announcements/${announcementId}` : '/admin/announcements';
    const method = isEdit ? 'POST' : 'POST';
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast(data.message || 'Failed to save announcement.', 'error');
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save Announcement';
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while saving the announcement.', 'error');
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save Announcement';
        }
    });
}

// Delete announcement
function deleteAnnouncement(id) {
    if (!confirm('Are you sure you want to delete this announcement? This action cannot be undone.')) {
        return;
    }
    
    fetch(`/admin/announcements/${id}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast(data.message || 'Failed to delete announcement.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while deleting the announcement.', 'error');
    });
}

// Close announcement modal
function closeAnnouncementModalFunc() {
    const modal = document.getElementById('announcementModal');
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

