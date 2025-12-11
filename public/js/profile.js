// Profile Page JavaScript

let isEditing = false;
let currentEditingSection = null;
let originalData = {};

// Make functions globally accessible immediately (before DOMContentLoaded)
window.openContactModal = function() {
    // Wait for DOM if not ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            window.openContactModal();
        });
        return;
    }
    
    const modal = document.getElementById('contactModal');
    
    if (!modal) {
        return;
    }
    
    modal.classList.add('active');
    
    // Force display style to ensure visibility - use !important via setProperty
    modal.style.setProperty('display', 'flex', 'important');
    modal.style.setProperty('z-index', '10000', 'important');
    modal.style.setProperty('position', 'fixed', 'important');
    modal.style.setProperty('top', '0', 'important');
    modal.style.setProperty('left', '0', 'important');
    modal.style.setProperty('width', '100%', 'important');
    modal.style.setProperty('height', '100%', 'important');
    modal.style.setProperty('background-color', 'rgba(0, 0, 0, 0.5)', 'important');
    modal.style.setProperty('align-items', 'center', 'important');
    modal.style.setProperty('justify-content', 'center', 'important');
    modal.style.setProperty('visibility', 'visible', 'important');
    modal.style.setProperty('opacity', '1', 'important');
    
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
    
    // Focus on first input
    const firstInput = modal.querySelector('input');
    if (firstInput) {
        setTimeout(() => firstInput.focus(), 100);
    }
};

window.closeContactModal = function() {
    const modal = document.getElementById('contactModal');
    if (modal) {
        modal.classList.remove('active');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto'; // Restore scrolling
        // Reset form values to original
        resetContactModal();
    }
};

document.addEventListener('DOMContentLoaded', function() {
    initializeProfile();
    setupEventListeners();
});

function initializeProfile() {
    // Store original data for cancel functionality
    const firstNameEl = document.getElementById('firstName');
    const lastNameEl = document.getElementById('lastName');
    const middleNameEl = document.getElementById('middleName');
    const addressEl = document.getElementById('address');
    const phoneEl = document.getElementById('phone');
    const emailEl = document.getElementById('email');
    const usernameEl = document.getElementById('username');
    
    originalData = {
        firstName: firstNameEl ? firstNameEl.value : '',
        lastName: lastNameEl ? lastNameEl.value : '',
        middleName: middleNameEl ? middleNameEl.value : '',
        address: addressEl ? addressEl.value : '',
        phone: phoneEl ? phoneEl.value : '',
        email: emailEl ? emailEl.value : '',
        username: usernameEl ? usernameEl.value : ''
    };
    
    // Remove middleName if it doesn't exist (for consumers)
    if (!middleNameEl) {
        delete originalData.middleName;
    }
}

function setupEventListeners() {
    // Add input change listeners for validation
    const inputs = document.querySelectorAll('.form-group input');
    inputs.forEach(input => {
        input.addEventListener('input', validateField);
        input.addEventListener('blur', validateField);
    });

    // Add event listener for contact edit button (for foodbank and other roles)
    const editContactBtn = document.getElementById('editContactBtn');
    if (editContactBtn) {
        editContactBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (typeof window.openContactModal === 'function') {
                window.openContactModal();
            }
        });
    }

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
    } else {
        // Account modal doesn't exist (e.g., for consumers)
        // Remove account-related functions or make them no-ops
        window.openAccountModal = function() {};
        window.closeAccountModal = function() {};
        window.saveAccountInfo = function() {};
        window.validateAccountModal = function() { return true; };
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
            if (accountModal && accountModal.classList.contains('active') && typeof closeAccountModal === 'function') {
                closeAccountModal();
            }
            if (editProfileModal && editProfileModal.style.display === 'flex') {
                closeEditProfileModal();
            }
        }
    });
}

// Make editProfilePicture globally accessible
window.editProfilePicture = function() {
    openEditProfileModal();
};

function openEditProfileModal() {
    const modal = document.getElementById('editProfileModal');
    if (!modal) {
        console.error('Edit profile modal not found');
        return;
    }
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    // Reset the state
    selectedProfilePictureFile = null;
    const saveChangesBtn = document.getElementById('saveChangesBtn');
    if (saveChangesBtn) {
        saveChangesBtn.style.display = 'none';
        saveChangesBtn.disabled = false;
    }
    
    // Setup upload photo button click handler
    const uploadPhotoBtn = document.getElementById('uploadPhotoBtn');
    const profilePictureInput = document.getElementById('profilePictureInput');
    if (uploadPhotoBtn && profilePictureInput) {
        // Remove existing listeners to avoid duplicates
        const newUploadBtn = uploadPhotoBtn.cloneNode(true);
        uploadPhotoBtn.parentNode.replaceChild(newUploadBtn, uploadPhotoBtn);
        
        newUploadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (profilePictureInput) {
                profilePictureInput.click();
            } else {
                console.error('Profile picture input not found');
                showNotification('Error: File input not found', 'error');
            }
        });
    }
}

function closeEditProfileModal() {
    const modal = document.getElementById('editProfileModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Store the selected file globally
let selectedProfilePictureFile = null;

// Make handleProfilePictureChange globally accessible
window.handleProfilePictureChange = function(event) {
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
};

// Make saveProfilePicture globally accessible
window.saveProfilePicture = function() {
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
    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        showNotification('CSRF token not found. Please refresh the page and try again.', 'error');
        if (saveChangesBtn) {
            saveChangesBtn.disabled = false;
            saveChangesBtn.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                </svg>
                Save Changes
            `;
        }
        return;
    }
    formData.append('_token', csrfToken);

    fetch('/profile/update', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => {
        // Check if response is ok
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.message || `HTTP error! status: ${response.status}`);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Get the image URL from server response or use preview
            let imageUrl = data.data?.profile_image_url || data.profile_image_url;
            const previewImage = document.getElementById('previewImage');
            
            if (!imageUrl && previewImage) {
                // Fallback to preview image src (data URL)
                imageUrl = previewImage.src;
            }
            
            // Update the main profile image
            const profileImage = document.getElementById('profileImage');
            if (profileImage && imageUrl) {
                // If it's a data URL, use it directly; otherwise construct storage URL
                if (imageUrl.startsWith('data:')) {
                    profileImage.src = imageUrl;
                } else {
                    // Construct the full URL for the stored image
                    profileImage.src = imageUrl.startsWith('http') ? imageUrl : `/storage/${imageUrl}`;
                }
            } else if (!profileImage) {
                // Create image element if it doesn't exist
                const profilePicture = document.querySelector('.profile-picture');
                const placeholder = document.querySelector('.profile-placeholder');
                if (placeholder) {
                    placeholder.remove();
                }
                if (profilePicture) {
                    const imgUrl = imageUrl.startsWith('http') || imageUrl.startsWith('data:') ? imageUrl : `/storage/${imageUrl}`;
                    profilePicture.innerHTML = `<img src="${imgUrl}" alt="Profile Picture" id="profileImage">`;
                }
            }
            
            // Update sidebar avatar
            if (imageUrl) {
                const avatarUrl = imageUrl.startsWith('http') || imageUrl.startsWith('data:') ? imageUrl : `/storage/${imageUrl}`;
                updateSidebarAvatar(avatarUrl);
            }
            
            showNotification('Profile picture updated successfully!', 'success');
            closeEditProfileModal();
        } else {
            let errorMessage = data.message || 'Failed to update profile picture';
            if (data.errors) {
                errorMessage += ': ' + Object.values(data.errors).flat().join(', ');
            }
            showNotification(errorMessage, 'error');
        }
    })
    .catch(error => {
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
};

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

// Functions are now defined at the top of the file (above DOMContentLoaded)

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
                document.getElementById('lastName')
            );
            const middleNameEl = document.getElementById('middleName');
            if (middleNameEl) {
                inputs.push(middleNameEl);
            }
            break;
        case 'contact':
            inputs.push(
                document.getElementById('address'),
                document.getElementById('phone'),
                document.getElementById('email')
            );
            break;
        case 'account':
            const accountSection = document.querySelector('.profile-section:has(#username)');
            if (accountSection) {
                inputs.push(
                    document.getElementById('username'),
                    document.getElementById('password') ? document.getElementById('password') : null,
                    document.getElementById('passwordConfirmation') ? document.getElementById('passwordConfirmation') : null
                );
            }
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

    // Reset password fields (if they exist)
    const passwordEl = document.getElementById('password');
    const passwordConfirmationEl = document.getElementById('passwordConfirmation');
    if (passwordEl) passwordEl.value = '';
    if (passwordConfirmationEl) passwordConfirmationEl.value = '';

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

// Make function globally accessible
window.saveContactInfo = function() {
    // Validate modal form
    if (!validateContactModal()) {
        return;
    }

    const contactModal = document.getElementById('contactModal');
    const confirmBtn = contactModal ? contactModal.querySelector('.btn-confirm') : document.querySelector('#contactModal .btn-confirm');
    if (!confirmBtn) {
        return;
    }
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

    // Validate phone (format: 09123456789 - exactly 11 digits)
    const phone = document.getElementById('modalPhone');
    if (phone && phone.value) {
        const cleaned = phone.value.replace(/\D/g, '');
        if (cleaned.length < 11 || cleaned.length > 12) {
            phone.classList.add('error');
            showFieldError(phone, 'Please enter a valid phone number (11 digits, format: 09123456789)');
            isValid = false;
        } else if (!isValidPhone(phone.value)) {
            phone.classList.add('error');
            showFieldError(phone, 'Please enter a valid phone number (format: 09123456789)');
            isValid = false;
        }
    } else if (phone && !phone.value.trim()) {
        phone.classList.add('error');
        showFieldError(phone, 'Phone number is required');
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
    
    // Validate required fields (username only if account section exists)
    const requiredFields = ['firstName', 'lastName', 'address', 'phone', 'email'];
    const usernameEl = document.getElementById('username');
    if (usernameEl) {
        requiredFields.push('username');
    }
    
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

    // Validate phone (exactly 11 digits)
    const phone = document.getElementById('phone');
    if (phone && phone.value) {
        const cleaned = phone.value.replace(/\D/g, '');
        if (cleaned.length < 11 || cleaned.length > 12) {
            phone.classList.add('error');
            showFieldError(phone, 'Please enter a valid phone number (11 digits, format: 09123456789)');
            isValid = false;
        } else if (!isValidPhone(phone.value)) {
            phone.classList.add('error');
            showFieldError(phone, 'Please enter a valid phone number (format: 09123456789)');
            isValid = false;
        }
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
            if (input.value) {
                const cleaned = input.value.replace(/\D/g, '');
                if (cleaned.length < 11 || cleaned.length > 12) {
                    input.classList.add('error');
                    showFieldError(input, 'Please enter a valid phone number (11 digits, format: 09123456789)');
                } else if (!isValidPhone(input.value)) {
                    input.classList.add('error');
                    showFieldError(input, 'Please enter a valid phone number (format: 09123456789)');
                }
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
    // Format: 09123456789 (exactly 11 digits, starting with 0)
    const cleaned = phone.replace(/\D/g, '');
    if (cleaned.length < 11 || cleaned.length > 12) {
        return false;
    }
    const phoneRegex = /^0\d{10}$/;
    return phoneRegex.test(cleaned);
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

// Request account deletion
window.requestAccountDeletion = function() {
    if (!confirm('Are you sure you want to request account deletion? This action will be reviewed by an admin and cannot be undone once approved.')) {
        return;
    }
    
    // Optional: Ask for reason
    const reason = prompt('Please provide a reason for account deletion (optional):');
    
    // Show loading state
    const deleteBtn = document.querySelector('.btn-danger');
    if (deleteBtn) {
        const originalText = deleteBtn.textContent;
        deleteBtn.disabled = true;
        deleteBtn.textContent = 'Submitting Request...';
        
        fetch('/profile/request-deletion', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({
                reason: reason || null
            })
        })
        .then(async response => {
            const contentType = response.headers.get('content-type');
            let data;

            if (contentType && contentType.includes('application/json')) {
                data = await response.json();
            } else {
                throw new Error(`Server error (${response.status})`);
            }

            if (!response.ok) {
                throw new Error(data.message || `HTTP error! status: ${response.status}`);
            }
            return data;
        })
        .then(data => {
            if (data.success) {
                alert(data.message || 'Account deletion request submitted successfully!');
                // Optionally disable the button or show status
                if (deleteBtn) {
                    deleteBtn.textContent = 'Request Submitted';
                    deleteBtn.style.opacity = '0.6';
                }
            } else {
                alert('Failed to submit request: ' + (data.message || 'Unknown error'));
                deleteBtn.disabled = false;
                deleteBtn.textContent = originalText;
            }
        })
        .catch(error => {
            alert('An error occurred while submitting the request: ' + error.message);
            if (deleteBtn) {
                deleteBtn.disabled = false;
                deleteBtn.textContent = originalText;
            }
        });
    }
}
