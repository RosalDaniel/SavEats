// Admin Settings JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeForms();
    initializeFileUploads();
    initializeConditionalFields();
});

// Initialize all forms
function initializeForms() {
    const forms = document.querySelectorAll('.settings-form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const group = form.closest('.settings-panel').dataset.group;
            saveSettings(group, form);
        });
    });
}

// Initialize file uploads
function initializeFileUploads() {
    // Logo upload
    const logoInput = document.getElementById('platform_logo');
    if (logoInput) {
        logoInput.addEventListener('change', function(e) {
            handleFilePreview(e.target, '.current-logo', '.file-upload-display');
        });
    }

    // Favicon upload
    const faviconInput = document.getElementById('favicon');
    if (faviconInput) {
        faviconInput.addEventListener('change', function(e) {
            handleFilePreview(e.target, '.current-favicon', '.file-upload-display');
        });
    }
}

// Handle file preview
function handleFilePreview(input, previewSelector, containerSelector) {
    const file = input.files[0];
    if (!file) return;

    const container = input.closest('.file-upload-wrapper').querySelector(containerSelector);
    if (!container) return;

    // Validate file size
    const maxSize = input.id === 'favicon' ? 512 * 1024 : 2 * 1024 * 1024; // 512KB for favicon, 2MB for logo
    if (file.size > maxSize) {
        showToast('File size exceeds maximum allowed size', 'error');
        input.value = '';
        return;
    }

    // Validate file type
    const validTypes = input.id === 'favicon' 
        ? ['image/ico', 'image/png', 'image/jpeg', 'image/jpg']
        : ['image/png', 'image/jpeg', 'image/jpg', 'image/gif', 'image/svg+xml'];
    
    if (!validTypes.includes(file.type)) {
        showToast('Invalid file type. Please select an image file.', 'error');
        input.value = '';
        return;
    }

    // Show preview
    const reader = new FileReader();
    reader.onload = function(e) {
        const existingImg = container.querySelector(previewSelector);
        const noFile = container.querySelector('.no-file');
        
        if (existingImg) {
            existingImg.src = e.target.result;
        } else {
            if (noFile) noFile.remove();
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = previewSelector.substring(1);
            img.alt = 'Preview';
            container.appendChild(img);
        }
    };
    reader.readAsDataURL(file);
}

// Initialize conditional fields
function initializeConditionalFields() {
    // SMS settings toggle
    const smsEnabled = document.getElementById('sms_enabled');
    const smsSettings = document.getElementById('sms-settings');
    
    if (smsEnabled && smsSettings) {
        smsEnabled.addEventListener('change', function() {
            smsSettings.style.display = this.checked ? 'block' : 'none';
        });
    }

    // Auto cleanup logs toggle
    const autoCleanup = document.getElementById('auto_cleanup_logs');
    const cleanupDays = document.getElementById('cleanup-days-group');
    
    if (autoCleanup && cleanupDays) {
        autoCleanup.addEventListener('change', function() {
            cleanupDays.style.display = this.checked ? 'block' : 'none';
        });
    }
}

// Save settings
function saveSettings(group, form) {
    const formData = new FormData(form);
    formData.append('group', group);

    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';
    form.classList.add('loading');

    fetch('/admin/settings/update', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Settings saved successfully!', 'success');
            
            // If logo or favicon was uploaded, refresh the preview
            if (formData.has('platform_logo') || formData.has('favicon')) {
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }
        } else {
            showToast(data.error || 'Failed to save settings', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while saving settings', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
        form.classList.remove('loading');
    });
}

// Reset settings
function resetSettings(group) {
    if (!confirm(`Are you sure you want to reset all ${group} settings to their default values? This action cannot be undone.`)) {
        return;
    }

    const form = document.querySelector(`[data-group="${group}"] .settings-form`);
    if (!form) return;

    const submitBtn = form.querySelector('button[type="submit"]');
    const resetBtn = form.querySelector('button[onclick*="resetSettings"]');
    
    if (resetBtn) {
        resetBtn.disabled = true;
        resetBtn.textContent = 'Resetting...';
    }

    fetch('/admin/settings/reset', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ group: group })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Settings reset to defaults successfully!', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showToast(data.error || 'Failed to reset settings', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while resetting settings', 'error');
    })
    .finally(() => {
        if (resetBtn) {
            resetBtn.disabled = false;
            resetBtn.textContent = 'Reset to Defaults';
        }
    });
}

// Show toast notification
function showToast(message, type = 'info') {
    // Remove existing toasts
    const existingToasts = document.querySelectorAll('.toast');
    existingToasts.forEach(toast => toast.remove());

    // Create toast
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    // Icon based on type
    const icons = {
        success: '✓',
        error: '✕',
        warning: '⚠',
        info: 'ℹ'
    };
    
    toast.innerHTML = `
        <span style="font-weight: bold; font-size: 1.2rem;">${icons[type] || icons.info}</span>
        <span>${message}</span>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

// Add slideOut animation
const style = document.createElement('style');
style.textContent = `
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

