// Profile Page JavaScript

let isEditing = false;
let currentEditingSection = null;
let originalData = {};

document.addEventListener('DOMContentLoaded', function() {
    initializeProfile();
    setupEventListeners();
});

function initializeProfile() {
    // Store original data for cancel functionality
    originalData = {
        firstName: document.getElementById('firstName').value,
        lastName: document.getElementById('lastName').value,
        middleName: document.getElementById('middleName').value,
        address: document.getElementById('address').value,
        phone: document.getElementById('phone').value,
        email: document.getElementById('email').value,
        username: document.getElementById('username').value
    };
}

function setupEventListeners() {
    // Add input change listeners for validation
    const inputs = document.querySelectorAll('.form-group input');
    inputs.forEach(input => {
        input.addEventListener('input', validateField);
        input.addEventListener('blur', validateField);
    });

    // Add modal event listeners
    const contactModal = document.getElementById('contactModal');
    const accountModal = document.getElementById('accountModal');
    const editProfileModal = document.getElementById('editProfileModal');
    
    if (contactModal) {
        // Close modal when clicking outside
        contactModal.addEventListener('click', function(e) {
            if (e.target === contactModal) {
                closeContactModal();
            }
        });

        // Add validation listeners to modal inputs
        const modalInputs = contactModal.querySelectorAll('input');
        modalInputs.forEach(input => {
            input.addEventListener('input', function() {
                input.classList.remove('error');
                clearFieldError(input);
            });
        });
    }
    
    if (accountModal) {
        // Close modal when clicking outside
        accountModal.addEventListener('click', function(e) {
            if (e.target === accountModal) {
                closeAccountModal();
            }
        });

        // Add validation listeners to modal inputs
        const modalInputs = accountModal.querySelectorAll('input');
        modalInputs.forEach(input => {
            input.addEventListener('input', function() {
                input.classList.remove('error');
                clearFieldError(input);
            });
        });
    }
    
    if (editProfileModal) {
        // Close modal when clicking outside
        editProfileModal.addEventListener('click', function(e) {
            if (e.target === editProfileModal) {
                closeEditProfileModal();
            }
        });
    }

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (contactModal && contactModal.classList.contains('active')) {
                closeContactModal();
            }
            if (accountModal && accountModal.classList.contains('active')) {
                closeAccountModal();
            }
            if (editProfileModal && editProfileModal.style.display === 'flex') {
                closeEditProfileModal();
            }
        }
    });
}

function editProfilePicture() {
    openEditProfileModal();
}

function openEditProfileModal() {
    const modal = document.getElementById('editProfileModal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    // Reset the state
    selectedProfilePictureFile = null;
    const saveChangesBtn = document.getElementById('saveChangesBtn');
    if (saveChangesBtn) {
        saveChangesBtn.style.display = 'none';
        saveChangesBtn.disabled = false;
    }
}

function closeEditProfileModal() {
    const modal = document.getElementById('editProfileModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Store the selected file globally
let selectedProfilePictureFile = null;

function handleProfilePictureChange(event) {
    const file = event.target.files[0];
    if (!file) return;

    // Validate file type
    if (!file.type.startsWith('image/')) {
        showNotification('Please select a valid image file', 'error');
        return;
    }

    // Validate file size (2MB max)
    if (file.size > 2 * 1024 * 1024) {
        showNotification('Image size must be less than 2MB', 'error');
        return;
    }

    // Store the file for later upload
    selectedProfilePictureFile = file;

    // Preview the image
    const reader = new FileReader();
    reader.onload = function(e) {
        // Update the preview in the modal
        const previewImage = document.getElementById('previewImage');
        const profilePlaceholderPreview = document.querySelector('.profile-placeholder-preview');
        
        if (previewImage) {
            previewImage.src = e.target.result;
            previewImage.style.display = 'block';
        } else {
            // Create image element if it doesn't exist
            const profilePicturePreview = document.getElementById('profilePicturePreview');
            profilePicturePreview.innerHTML = `<img src="${e.target.result}" alt="Profile Picture" id="previewImage">`;
        }
        
        if (profilePlaceholderPreview) {
            profilePlaceholderPreview.style.display = 'none';
        }
        
        // Show the save changes button
        const saveChangesBtn = document.getElementById('saveChangesBtn');
        if (saveChangesBtn) {
            saveChangesBtn.style.display = 'flex';
        }
    };
    reader.readAsDataURL(file);
}

function saveProfilePicture() {
    if (!selectedProfilePictureFile) {
        showNotification('No image selected', 'error');
        return;
    }

    const saveChangesBtn = document.getElementById('saveChangesBtn');
    if (saveChangesBtn) {
        saveChangesBtn.disabled = true;
        saveChangesBtn.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
            </svg>
            Saving...
        `;
    }

    // Upload the file
    const formData = new FormData();
    formData.append('profile_picture', selectedProfilePictureFile);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

    fetch('/profile/update', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update the main profile image
            const profileImage = document.getElementById('profileImage');
            if (profileImage) {
                profileImage.src = document.getElementById('previewImage').src;
            } else {
                const profilePicture = document.querySelector('.profile-picture');
                profilePicture.innerHTML = `<img src="${document.getElementById('previewImage').src}" alt="Profile Picture" id="profileImage">`;
            }
            
            // Update sidebar avatar
            updateSidebarAvatar(document.getElementById('previewImage').src);
            
            showNotification('Profile picture updated successfully!', 'success');
            closeEditProfileModal();
        } else {
            showNotification(data.message || 'Failed to update profile picture', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while updating profile picture', 'error');
    })
    .finally(() => {
        // Reset button state
        if (saveChangesBtn) {
            saveChangesBtn.disabled = false;
            saveChangesBtn.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                </svg>
                Save Changes
            `;
        }
    });
}

function updateSidebarAvatar(imageSrc) {
    // Update sidebar avatar if it exists
    const sidebarAvatar = document.querySelector('.user-avatar');
    if (sidebarAvatar) {
        const existingImage = sidebarAvatar.querySelector('.avatar-image');
        if (existingImage) {
            existingImage.src = imageSrc;
        } else {
            // Create image element if it doesn't exist
            sidebarAvatar.innerHTML = `<img src="${imageSrc}" alt="Profile Picture" class="avatar-image">`;
        }
    }
}

function openContactModal() {
    const modal = document.getElementById('contactModal');
    if (modal) {
        modal.classList.add('active');
        // Focus on first input
        const firstInput = modal.querySelector('input');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
    }
}

function closeContactModal() {
    const modal = document.getElementById('contactModal');
    if (modal) {
        modal.classList.remove('active');
        // Reset form values to original
        resetContactModal();
    }
}

function resetContactModal() {
    // Reset modal inputs to current values
    document.getElementById('modalAddress').value = document.getElementById('address').value;
    document.getElementById('modalPhone').value = document.getElementById('phone').value;
    document.getElementById('modalEmail').value = document.getElementById('email').value;
}

function openAccountModal() {
    const modal = document.getElementById('accountModal');
    if (modal) {
        modal.classList.add('active');
        // Focus on first input
        const firstInput = modal.querySelector('input');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
    }
}

function closeAccountModal() {
    const modal = document.getElementById('accountModal');
    if (modal) {
        modal.classList.remove('active');
        // Reset form values
        resetAccountModal();
    }
}

function resetAccountModal() {
    // Clear password fields
    document.getElementById('modalPassword').value = '';
    document.getElementById('modalPasswordConfirmation').value = '';
}

function getSectionInputs(section) {
    const inputs = [];
    
    switch (section) {
        case 'personal':
            inputs.push(
                document.getElementById('firstName'),
                document.getElementById('lastName'),
                document.getElementById('middleName')
            );
            break;
        case 'contact':
            inputs.push(
                document.getElementById('address'),
                document.getElementById('phone'),
                document.getElementById('email')
            );
            break;
        case 'account':
            inputs.push(
                document.getElementById('username'),
                document.getElementById('password'),
                document.getElementById('passwordConfirmation')
            );
            break;
    }
    
    return inputs.filter(input => input !== null);
}

function showProfileActions() {
    const actions = document.getElementById('profileActions');
    if (actions) {
        actions.style.display = 'flex';
    }
}

function hideProfileActions() {
    const actions = document.getElementById('profileActions');
    if (actions) {
        actions.style.display = 'none';
    }
}

function cancelEdit() {
    // Restore original values
    Object.keys(originalData).forEach(key => {
        const input = document.getElementById(key);
        if (input) {
            input.value = originalData[key];
            input.setAttribute('readonly', 'readonly');
            input.classList.remove('editing');
        }
    });

    // Reset password fields
    document.getElementById('password').value = '';
    document.getElementById('passwordConfirmation').value = '';

    // Reset profile picture if changed
    const profilePictureInput = document.getElementById('profilePictureInput');
    if (profilePictureInput) {
        profilePictureInput.value = '';
    }

    // Reset state
    isEditing = false;
    currentEditingSection = null;
    hideProfileActions();
    
    showNotification('Changes cancelled', 'info');
}

function saveContactInfo() {
    // Validate modal form
    if (!validateContactModal()) {
        return;
    }

    const confirmBtn = document.querySelector('.btn-confirm');
    const originalText = confirmBtn.textContent;
    
    // Show loading state
    confirmBtn.innerHTML = '<div class="loading"></div> Saving...';
    confirmBtn.disabled = true;

    // Collect form data
    const formData = new FormData();
    formData.append('address', document.getElementById('modalAddress').value);
    formData.append('phone', document.getElementById('modalPhone').value);
    formData.append('email', document.getElementById('modalEmail').value);

    // Add CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        formData.append('_token', csrfToken);
    }

    // Make API call
    fetch('/profile/update', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Contact information updated successfully!', 'success');
            
            // Update main form values
            document.getElementById('address').value = document.getElementById('modalAddress').value;
            document.getElementById('phone').value = document.getElementById('modalPhone').value;
            document.getElementById('email').value = document.getElementById('modalEmail').value;

            // Update original data
            originalData.address = document.getElementById('modalAddress').value;
            originalData.phone = document.getElementById('modalPhone').value;
            originalData.email = document.getElementById('modalEmail').value;

            // Close modal
            closeContactModal();

        } else {
            showNotification(data.message || 'Failed to update contact information', 'error');
            
            // Show validation errors
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    const input = document.getElementById('modal' + field.charAt(0).toUpperCase() + field.slice(1));
                    if (input) {
                        input.classList.add('error');
                        showFieldError(input, data.errors[field][0]);
                    }
                });
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while updating contact information', 'error');
    })
    .finally(() => {
        // Reset button
        confirmBtn.textContent = originalText;
        confirmBtn.disabled = false;
    });
}

function validateContactModal() {
    let isValid = true;
    
    // Clear previous errors
    clearFieldErrors();
    
    // Validate required fields
    const requiredFields = ['modalAddress', 'modalPhone', 'modalEmail'];
    requiredFields.forEach(fieldId => {
        const input = document.getElementById(fieldId);
        if (input && !input.value.trim()) {
            input.classList.add('error');
            showFieldError(input, 'This field is required');
            isValid = false;
        }
    });

    // Validate email
    const email = document.getElementById('modalEmail');
    if (email && email.value && !isValidEmail(email.value)) {
        email.classList.add('error');
        showFieldError(email, 'Please enter a valid email address');
        isValid = false;
    }

    // Validate phone
    const phone = document.getElementById('modalPhone');
    if (phone && phone.value && !isValidPhone(phone.value)) {
        phone.classList.add('error');
        showFieldError(phone, 'Please enter a valid phone number');
        isValid = false;
    }

    return isValid;
}

function saveAccountInfo() {
    // Validate modal form
    if (!validateAccountModal()) {
        return;
    }

    const confirmBtn = document.querySelector('#accountModal .btn-confirm');
    const originalText = confirmBtn.textContent;
    
    // Show loading state
    confirmBtn.innerHTML = '<div class="loading"></div> Saving...';
    confirmBtn.disabled = true;

    // Collect form data
    const formData = new FormData();
    formData.append('password', document.getElementById('modalPassword').value);
    formData.append('password_confirmation', document.getElementById('modalPasswordConfirmation').value);

    // Add CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        formData.append('_token', csrfToken);
    }

    // Make API call
    fetch('/profile/update', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Password updated successfully!', 'success');
            
            // Clear password fields in main form
            document.getElementById('password').value = '';
            document.getElementById('passwordConfirmation').value = '';

            // Close modal
            closeAccountModal();

        } else {
            showNotification(data.message || 'Failed to update password', 'error');
            
            // Show validation errors
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    const input = document.getElementById('modal' + field.charAt(0).toUpperCase() + field.slice(1));
                    if (input) {
                        input.classList.add('error');
                        showFieldError(input, data.errors[field][0]);
                    }
                });
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while updating password', 'error');
    })
    .finally(() => {
        // Reset button
        confirmBtn.textContent = originalText;
        confirmBtn.disabled = false;
    });
}

function validateAccountModal() {
    let isValid = true;
    
    // Clear previous errors
    clearFieldErrors();
    
    // Validate password
    const password = document.getElementById('modalPassword');
    const passwordConfirmation = document.getElementById('modalPasswordConfirmation');
    
    if (!password.value.trim()) {
        password.classList.add('error');
        showFieldError(password, 'Password is required');
        isValid = false;
    } else if (password.value.length < 8) {
        password.classList.add('error');
        showFieldError(password, 'Password must be at least 8 characters');
        isValid = false;
    }
    
    if (!passwordConfirmation.value.trim()) {
        passwordConfirmation.classList.add('error');
        showFieldError(passwordConfirmation, 'Password confirmation is required');
        isValid = false;
    } else if (password.value !== passwordConfirmation.value) {
        passwordConfirmation.classList.add('error');
        showFieldError(passwordConfirmation, 'Passwords do not match');
        isValid = false;
    }

    return isValid;
}

function validateForm() {
    let isValid = true;
    
    // Clear previous errors
    clearFieldErrors();
    
    // Validate required fields
    const requiredFields = ['firstName', 'lastName', 'address', 'phone', 'email', 'username'];
    requiredFields.forEach(fieldId => {
        const input = document.getElementById(fieldId);
        if (input && !input.value.trim()) {
            input.classList.add('error');
            showFieldError(input, 'This field is required');
            isValid = false;
        }
    });

    // Validate email
    const email = document.getElementById('email');
    if (email && email.value && !isValidEmail(email.value)) {
        email.classList.add('error');
        showFieldError(email, 'Please enter a valid email address');
        isValid = false;
    }

    // Validate phone
    const phone = document.getElementById('phone');
    if (phone && phone.value && !isValidPhone(phone.value)) {
        phone.classList.add('error');
        showFieldError(phone, 'Please enter a valid phone number');
        isValid = false;
    }

    // Validate password if provided
    const password = document.getElementById('password');
    const passwordConfirmation = document.getElementById('passwordConfirmation');
    
    if (password && password.value) {
        if (password.value.length < 8) {
            password.classList.add('error');
            showFieldError(password, 'Password must be at least 8 characters');
            isValid = false;
        }
        
        if (password.value !== passwordConfirmation.value) {
            passwordConfirmation.classList.add('error');
            showFieldError(passwordConfirmation, 'Passwords do not match');
            isValid = false;
        }
    }

    return isValid;
}

function validateField(event) {
    const input = event.target;
    const fieldId = input.id;
    
    // Clear previous error
    input.classList.remove('error');
    clearFieldError(input);
    
    // Validate based on field type
    switch (fieldId) {
        case 'email':
            if (input.value && !isValidEmail(input.value)) {
                input.classList.add('error');
                showFieldError(input, 'Please enter a valid email address');
            }
            break;
        case 'phone':
            if (input.value && !isValidPhone(input.value)) {
                input.classList.add('error');
                showFieldError(input, 'Please enter a valid phone number');
            }
            break;
        case 'password':
            if (input.value && input.value.length < 8) {
                input.classList.add('error');
                showFieldError(input, 'Password must be at least 8 characters');
            }
            break;
        case 'passwordConfirmation':
            const password = document.getElementById('password');
            if (input.value && password.value && input.value !== password.value) {
                input.classList.add('error');
                showFieldError(input, 'Passwords do not match');
            }
            break;
    }
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function isValidPhone(phone) {
    const phoneRegex = /^[\+]?[0-9\s\-\(\)]{10,}$/;
    return phoneRegex.test(phone);
}

function showFieldError(input, message) {
    // Remove existing error message
    clearFieldError(input);
    
    // Create error message element
    const errorElement = document.createElement('div');
    errorElement.className = 'field-error';
    errorElement.textContent = message;
    errorElement.style.color = '#f44336';
    errorElement.style.fontSize = '12px';
    errorElement.style.marginTop = '5px';
    
    // Insert after input
    input.parentNode.insertBefore(errorElement, input.nextSibling);
}

function clearFieldError(input) {
    const errorElement = input.parentNode.querySelector('.field-error');
    if (errorElement) {
        errorElement.remove();
    }
}

function clearFieldErrors() {
    const errorElements = document.querySelectorAll('.field-error');
    errorElements.forEach(element => element.remove());
    
    const errorInputs = document.querySelectorAll('.error');
    errorInputs.forEach(input => input.classList.remove('error'));
}

function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Show notification
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    // Hide notification after 5 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 5000);
}

// BIR File Upload Functionality
function initializeBirFileUpload() {
    const birFileInput = document.getElementById('birFileUpload');
    if (!birFileInput) return;

    birFileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file type
            const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
            if (!allowedTypes.includes(file.type)) {
                showNotification('Please select a valid file type (PDF, JPG, or PNG)', 'error');
                return;
            }

            // Validate file size (2MB max)
            if (file.size > 2 * 1024 * 1024) {
                showNotification('File size must be less than 2MB', 'error');
                return;
            }

            // Upload the file
            uploadBirFile(file);
        }
    });
}

function uploadBirFile(file) {
    const formData = new FormData();
    formData.append('bir_file', file);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

    // Show loading state
    const fileDisplay = document.querySelector('.file-upload-display');
    const originalText = fileDisplay.querySelector('.file-upload-text').textContent;
    fileDisplay.querySelector('.file-upload-text').textContent = 'Uploading...';
    fileDisplay.style.opacity = '0.7';

    fetch('/profile/update', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('BIR file uploaded successfully!', 'success');
            // Update the display
            updateBirFileDisplay(file.name);
            // Update sidebar avatar if needed
            updateSidebarAvatar();
        } else {
            showNotification(data.message || 'Failed to upload BIR file', 'error');
            // Reset display
            fileDisplay.querySelector('.file-upload-text').textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error uploading BIR file:', error);
        showNotification('Error uploading BIR file', 'error');
        // Reset display
        fileDisplay.querySelector('.file-upload-text').textContent = originalText;
    })
    .finally(() => {
        // Reset loading state
        fileDisplay.style.opacity = '1';
    });
}

function updateBirFileDisplay(fileName) {
    const fileDisplay = document.querySelector('.file-upload-display');
    const fileText = fileDisplay.querySelector('.file-upload-text');
    
    // Update to uploaded state
    fileDisplay.classList.add('file-uploaded');
    
    // Create file info structure if it doesn't exist
    if (!fileDisplay.querySelector('.file-info')) {
        const fileInfo = document.createElement('div');
        fileInfo.className = 'file-info';
        fileInfo.innerHTML = `
            <svg class="file-icon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
            </svg>
            <span class="file-upload-text">${fileName}</span>
        `;
        fileDisplay.innerHTML = '';
        fileDisplay.appendChild(fileInfo);
        
        // Add chevron icon
        const chevron = document.createElement('svg');
        chevron.className = 'file-upload-icon';
        chevron.setAttribute('width', '16');
        chevron.setAttribute('height', '16');
        chevron.setAttribute('viewBox', '0 0 24 24');
        chevron.setAttribute('fill', 'currentColor');
        chevron.innerHTML = '<path d="M7 14l5-5 5 5z"/>';
        fileDisplay.appendChild(chevron);
    } else {
        fileText.textContent = fileName;
    }
}

// Initialize BIR file upload when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeBirFileUpload();
});
