// Settings Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeSettings();
});

function initializeSettings() {
    setupTabNavigation();
    setupToggleSwitches();
    setupRangeSliders();
    setupFormValidation();
}

// Tab Navigation
function setupTabNavigation() {
    const navItems = document.querySelectorAll('.nav-item');
    const sections = document.querySelectorAll('.settings-section');
    
    navItems.forEach(item => {
        item.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Remove active class from all nav items and sections
            navItems.forEach(nav => nav.classList.remove('active'));
            sections.forEach(section => section.classList.remove('active'));
            
            // Add active class to clicked nav item and corresponding section
            this.classList.add('active');
            const targetSection = document.getElementById(targetTab);
            if (targetSection) {
                targetSection.classList.add('active');
            }
        });
    });
}

// Toggle Switches
function setupToggleSwitches() {
    const toggleSwitches = document.querySelectorAll('.toggle-switch input');
    
    toggleSwitches.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const setting = this.closest('.toggle-item').querySelector('h4').textContent;
            const isEnabled = this.checked;
            
            console.log(`${setting}: ${isEnabled ? 'Enabled' : 'Disabled'}`);
            
            // Here you would typically save the setting to the server
            saveSetting(setting, isEnabled);
        });
    });
}

// Range Sliders
function setupRangeSliders() {
    const rangeSliders = document.querySelectorAll('.preference-range');
    
    rangeSliders.forEach(slider => {
        const valueDisplay = slider.nextElementSibling;
        
        slider.addEventListener('input', function() {
            valueDisplay.textContent = `${this.value} km`;
        });
    });
}

// Form Validation
function setupFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            validateForm(this);
        });
    });
}

// Save Setting Function
function saveSetting(setting, value) {
    // Simulate API call
    console.log(`Saving setting: ${setting} = ${value}`);
    
    // Show notification
    showNotification(`${setting} ${value ? 'enabled' : 'disabled'}`, 'success');
}

// Profile Functions
function editProfile() {
    // Redirect to profile page
    window.location.href = '/profile';
}

function changePassword() {
    const modal = document.getElementById('passwordModal');
    modal.classList.add('active');
}

function closePasswordModal() {
    const modal = document.getElementById('passwordModal');
    modal.classList.remove('active');
    
    // Clear form and errors
    const form = document.getElementById('passwordForm');
    form.reset();
    
    // Clear all field errors
    const inputs = form.querySelectorAll('input');
    inputs.forEach(input => {
        clearFieldError(input);
    });
}

// Add event listeners to clear errors when user types
document.addEventListener('DOMContentLoaded', function() {
    const passwordInputs = ['currentPassword', 'newPassword', 'confirmPassword'];
    
    passwordInputs.forEach(inputId => {
        const input = document.getElementById(inputId);
        if (input) {
            input.addEventListener('input', function() {
                clearFieldError(this);
            });
        }
    });
});

function savePassword() {
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    // Clear previous errors
    clearFieldError(document.getElementById('currentPassword'));
    clearFieldError(document.getElementById('newPassword'));
    clearFieldError(document.getElementById('confirmPassword'));
    
    // Validate passwords
    if (!currentPassword) {
        showFieldError(document.getElementById('currentPassword'), 'Current password is required');
        return;
    }
    
    if (!newPassword) {
        showFieldError(document.getElementById('newPassword'), 'New password is required');
        return;
    }
    
    if (newPassword.length < 8) {
        showFieldError(document.getElementById('newPassword'), 'Password must be at least 8 characters long');
        return;
    }
    
    if (newPassword !== confirmPassword) {
        showFieldError(document.getElementById('confirmPassword'), 'New passwords do not match');
        return;
    }
    
    // Show loading state
    const saveBtn = document.querySelector('.btn-confirm');
    const originalText = saveBtn.textContent;
    saveBtn.textContent = 'Saving...';
    saveBtn.disabled = true;
    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                      document.querySelector('input[name="_token"]')?.value;
    
    // Make API call
    fetch('/profile/change-password', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            current_password: currentPassword,
            new_password: newPassword,
            confirm_password: confirmPassword
        })
    })
    .then(response => response.json())
    .then(data => {
        // Reset button
        saveBtn.textContent = originalText;
        saveBtn.disabled = false;
        
        if (data.success) {
            showNotification(data.message || 'Password changed successfully', 'success');
            closePasswordModal();
            
            // Clear form
            document.getElementById('passwordForm').reset();
        } else {
            // Handle validation errors
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    let inputId;
                    // Map backend field names to frontend input IDs
                    if (field === 'current_password') {
                        inputId = 'currentPassword';
                    } else if (field === 'new_password') {
                        inputId = 'newPassword';
                    } else if (field === 'confirm_password') {
                        inputId = 'confirmPassword';
                    }
                    
                    const input = document.getElementById(inputId);
                    if (input && data.errors[field] && data.errors[field].length > 0) {
                        showFieldError(input, data.errors[field][0]);
                    }
                });
            } else {
                showNotification(data.message || 'Failed to change password', 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error changing password:', error);
        saveBtn.textContent = originalText;
        saveBtn.disabled = false;
        showNotification('An error occurred. Please try again.', 'error');
    });
}

// Form Validation
function validateForm(form) {
    const inputs = form.querySelectorAll('input[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            showFieldError(input, 'This field is required');
            isValid = false;
        } else {
            clearFieldError(input);
        }
    });
    
    return isValid;
}

function showFieldError(input, message) {
    clearFieldError(input);
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.textContent = message;
    errorDiv.style.color = '#ef4444';
    errorDiv.style.fontSize = '12px';
    errorDiv.style.marginTop = '4px';
    
    input.parentNode.appendChild(errorDiv);
    input.style.borderColor = '#ef4444';
}

function clearFieldError(input) {
    const existingError = input.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
    input.style.borderColor = '#d1d5db';
}

// Notification System
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    const colors = {
        success: '#10b981',
        error: '#ef4444',
        info: '#3b82f6',
        warning: '#f59e0b'
    };
    
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${colors[type] || colors.info};
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 1001;
        font-family: 'Afacad', sans-serif;
        font-weight: 500;
        max-width: 300px;
        animation: slideIn 0.3s ease;
    `;
    
    // Add animation keyframes
    if (!document.querySelector('#notification-styles')) {
        const style = document.createElement('style');
        style.id = 'notification-styles';
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    }
    
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    }, 3000);
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Escape key to close modals
    if (e.key === 'Escape') {
        const activeModal = document.querySelector('.modal-overlay.active');
        if (activeModal) {
            activeModal.classList.remove('active');
        }
    }
});

// Click outside modal to close
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
    }
});

// Auto-save preferences
function autoSavePreferences() {
    const preferences = {
        theme: document.querySelector('select[data-preference="theme"]')?.value,
        language: document.querySelector('select[data-preference="language"]')?.value,
        currency: document.querySelector('select[data-preference="currency"]')?.value,
        sortBy: document.querySelector('select[data-preference="sortBy"]')?.value,
        maxDistance: document.querySelector('.preference-range')?.value
    };
    
    // Save to localStorage for now
    localStorage.setItem('savEatsPreferences', JSON.stringify(preferences));
}

// Load saved preferences
function loadPreferences() {
    const saved = localStorage.getItem('savEatsPreferences');
    if (saved) {
        const preferences = JSON.parse(saved);
        
        // Apply saved preferences
        Object.keys(preferences).forEach(key => {
            const element = document.querySelector(`[data-preference="${key}"]`);
            if (element) {
                element.value = preferences[key];
            }
        });
    }
}

// Initialize preferences on load
document.addEventListener('DOMContentLoaded', function() {
    loadPreferences();
    
    // Set up auto-save
    const preferenceElements = document.querySelectorAll('[data-preference]');
    preferenceElements.forEach(element => {
        element.addEventListener('change', autoSavePreferences);
    });
});
