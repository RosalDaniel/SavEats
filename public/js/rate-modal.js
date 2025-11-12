// Rate Modal JavaScript
let currentRating = 0;
let selectedImage = null;
let selectedVideo = null;

// Open rate modal
function openRateModal(orderId) {
    const modal = document.getElementById('rateModal');
    const orderIdInput = document.getElementById('rateOrderId');
    
    if (!modal || !orderIdInput) return;
    
    // Set order ID
    orderIdInput.value = orderId;
    
    // Reset form
    resetRateForm();
    
    // Show modal
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
    
    // Focus on close button for accessibility
    const closeButton = modal.querySelector('.rate-modal-close');
    if (closeButton) {
        setTimeout(() => closeButton.focus(), 100);
    }
    
    // Initialize star rating
    initializeStarRating();
}

// Close rate modal
function closeRateModal() {
    const modal = document.getElementById('rateModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
        resetRateForm();
    }
}

// Reset form
function resetRateForm() {
    currentRating = 0;
    selectedImage = null;
    selectedVideo = null;
    
    // Reset stars
    const stars = document.querySelectorAll('.star');
    stars.forEach(star => {
        star.classList.remove('active', 'filled');
    });
    
    // Reset rating input
    const ratingInput = document.getElementById('ratingValue');
    if (ratingInput) {
        ratingInput.value = 0;
    }
    
    // Reset file inputs
    const imageInput = document.getElementById('imageUpload');
    const videoInput = document.getElementById('videoUpload');
    if (imageInput) imageInput.value = '';
    if (videoInput) videoInput.value = '';
    
    // Clear previews
    const imagePreview = document.getElementById('imagePreview');
    const videoPreview = document.getElementById('videoPreview');
    const uploadPreview = document.getElementById('uploadPreview');
    if (imagePreview) imagePreview.innerHTML = '';
    if (videoPreview) videoPreview.innerHTML = '';
    if (uploadPreview) uploadPreview.style.display = 'none';
    
    // Reset textarea
    const textarea = document.getElementById('reviewDescription');
    if (textarea) textarea.value = '';
}

// Initialize star rating
function initializeStarRating() {
    const stars = document.querySelectorAll('.star');
    
    stars.forEach((star, index) => {
        star.addEventListener('click', function() {
            const rating = parseInt(this.getAttribute('data-rating'));
            setRating(rating);
        });
        
        star.addEventListener('mouseenter', function() {
            const rating = parseInt(this.getAttribute('data-rating'));
            highlightStars(rating);
        });
    });
    
    // Reset on mouse leave
    const starRating = document.getElementById('starRating');
    if (starRating) {
        starRating.addEventListener('mouseleave', function() {
            highlightStars(currentRating);
        });
    }
}

// Set rating
function setRating(rating) {
    currentRating = rating;
    const ratingInput = document.getElementById('ratingValue');
    if (ratingInput) {
        ratingInput.value = rating;
    }
    highlightStars(rating);
    fillStars(rating);
}

// Highlight stars on hover
function highlightStars(rating) {
    const stars = document.querySelectorAll('.star');
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.add('active');
        } else {
            star.classList.remove('active');
        }
    });
}

// Fill stars permanently
function fillStars(rating) {
    const stars = document.querySelectorAll('.star');
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.add('filled');
        } else {
            star.classList.remove('filled');
        }
    });
}

// Trigger image upload
function triggerImageUpload() {
    const imageInput = document.getElementById('imageUpload');
    if (imageInput) {
        imageInput.click();
    }
}

// Trigger video upload
function triggerVideoUpload() {
    const videoInput = document.getElementById('videoUpload');
    if (videoInput) {
        videoInput.click();
    }
}

// Handle image upload
function handleImageUpload(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    if (!file.type.startsWith('image/')) {
        alert('Please select an image file.');
        return;
    }
    
    selectedImage = file;
    
    // Show preview
    const reader = new FileReader();
    reader.onload = function(e) {
        const imagePreview = document.getElementById('imagePreview');
        const uploadPreview = document.getElementById('uploadPreview');
        
        if (imagePreview && uploadPreview) {
            imagePreview.innerHTML = `
                <img src="${e.target.result}" alt="Preview">
                <button type="button" class="remove-preview" onclick="removeImagePreview()" aria-label="Remove image">×</button>
            `;
            uploadPreview.style.display = 'flex';
        }
    };
    reader.readAsDataURL(file);
}

// Handle video upload
function handleVideoUpload(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    if (!file.type.startsWith('video/')) {
        alert('Please select a video file.');
        return;
    }
    
    selectedVideo = file;
    
    // Show preview
    const reader = new FileReader();
    reader.onload = function(e) {
        const videoPreview = document.getElementById('videoPreview');
        const uploadPreview = document.getElementById('uploadPreview');
        
        if (videoPreview && uploadPreview) {
            videoPreview.innerHTML = `
                <video src="${e.target.result}" controls></video>
                <button type="button" class="remove-preview" onclick="removeVideoPreview()" aria-label="Remove video">×</button>
            `;
            uploadPreview.style.display = 'flex';
        }
    };
    reader.readAsDataURL(file);
}

// Remove image preview
function removeImagePreview() {
    selectedImage = null;
    const imageInput = document.getElementById('imageUpload');
    const imagePreview = document.getElementById('imagePreview');
    const uploadPreview = document.getElementById('uploadPreview');
    
    if (imageInput) imageInput.value = '';
    if (imagePreview) imagePreview.innerHTML = '';
    
    // Hide preview container if no items
    if (uploadPreview) {
        const videoPreview = document.getElementById('videoPreview');
        if (!videoPreview || !videoPreview.innerHTML.trim()) {
            uploadPreview.style.display = 'none';
        }
    }
}

// Remove video preview
function removeVideoPreview() {
    selectedVideo = null;
    const videoInput = document.getElementById('videoUpload');
    const videoPreview = document.getElementById('videoPreview');
    const uploadPreview = document.getElementById('uploadPreview');
    
    if (videoInput) videoInput.value = '';
    if (videoPreview) videoPreview.innerHTML = '';
    
    // Hide preview container if no items
    if (uploadPreview) {
        const imagePreview = document.getElementById('imagePreview');
        if (!imagePreview || !imagePreview.innerHTML.trim()) {
            uploadPreview.style.display = 'none';
        }
    }
}

// Submit review
function submitReview(event) {
    event.preventDefault();
    
    const ratingInput = document.getElementById('ratingValue');
    const description = document.getElementById('reviewDescription');
    const orderIdInput = document.getElementById('rateOrderId');
    const publishBtn = event.target.querySelector('.publish-btn');
    
    if (!ratingInput || !description || !orderIdInput) return;
    
    const rating = parseInt(ratingInput.value);
    if (rating === 0 || rating < 1 || rating > 5) {
        alert('Please select a rating (1-5 stars).');
        return;
    }
    
    // Disable button during submission
    if (publishBtn) {
        publishBtn.disabled = true;
        publishBtn.textContent = 'Publishing...';
    }
    
    // Create form data
    const formData = new FormData();
    formData.append('order_id', orderIdInput.value);
    formData.append('rating', rating);
    formData.append('description', description.value.trim());
    formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');
    
    if (selectedImage) {
        formData.append('image', selectedImage);
    }
    if (selectedVideo) {
        formData.append('video', selectedVideo);
    }
    
    // Submit review via API
    fetch('/consumer/reviews', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(async response => {
        const contentType = response.headers.get('content-type');
        let data;

        if (contentType && contentType.includes('application/json')) {
            data = await response.json();
        } else {
            const text = await response.text();
            console.error('Server returned non-JSON:', text.substring(0, 200));
            throw new Error(`Server error (${response.status})`);
        }

        if (!response.ok) {
            if (data.errors) {
                const errorList = Object.entries(data.errors)
                    .map(([field, messages]) => {
                        const fieldName = field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                        return `${fieldName}: ${Array.isArray(messages) ? messages.join(', ') : messages}`;
                    })
                    .join('\n');
                throw new Error('Validation failed:\n\n' + errorList);
            }
            throw new Error(data.message || `HTTP error! status: ${response.status}`);
        }
        return data;
    })
    .then(data => {
        if (data.success) {
            // Update the button text to "View Rating"
            updateRateButtonToViewRating(orderIdInput.value);
            
            alert('Review published successfully!');
            closeRateModal();
        } else {
            alert('Failed to publish review: ' + (data.message || 'Unknown error'));
        }
        
        if (publishBtn) {
            publishBtn.disabled = false;
            publishBtn.textContent = 'Publish Review';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while publishing the review: ' + error.message);
        
        if (publishBtn) {
            publishBtn.disabled = false;
            publishBtn.textContent = 'Publish Review';
        }
    });
}

// Close modal on overlay click
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('rateModal');
    if (modal) {
        const overlay = modal.querySelector('.rate-modal-overlay');
        if (overlay) {
            overlay.addEventListener('click', closeRateModal);
        }
        
        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('show')) {
                closeRateModal();
            }
        });
    }
});

// Update rate button to "View Rating" after submission
function updateRateButtonToViewRating(orderId) {
    // Find all rate buttons for this order - try multiple formats
    const numericId = orderId.toString().replace(/[^0-9]/g, '');
    const orderIdFormats = [
        `ID#${numericId}`,
        `ID#${orderId}`,
        numericId,
        orderId.toString()
    ];
    
    // Try to find button by various onclick patterns
    let rateButton = null;
    for (const format of orderIdFormats) {
        rateButton = document.querySelector(`button[onclick*="rateOrder('${format}')"], button[onclick*="rateOrder('ID#${format}')"]`);
        if (rateButton) break;
    }
    
    // If not found by onclick, try finding by text content
    if (!rateButton) {
        const allButtons = document.querySelectorAll('.btn-primary, .btn');
        allButtons.forEach(btn => {
            if (btn.textContent.trim() === 'Rate Now') {
                // Check if it's in the same order card
                const orderCard = btn.closest('.order-card');
                if (orderCard) {
                    const orderIdElement = orderCard.querySelector('[class*="order-id"], [class*="detail-value"]');
                    if (orderIdElement && orderIdElement.textContent.includes(numericId)) {
                        rateButton = btn;
                    }
                }
            }
        });
    }
    
    if (rateButton) {
        rateButton.textContent = 'View Rating';
        rateButton.classList.remove('btn-primary');
        rateButton.classList.add('btn-outline');
        // Update onclick to open view rating modal
        rateButton.setAttribute('onclick', `viewRating(${numericId})`);
    } else {
        // If button not found, reload page to show updated state
        setTimeout(() => {
            location.reload();
        }, 1000);
    }
}

// Make functions globally accessible
window.openRateModal = openRateModal;
window.closeRateModal = closeRateModal;
window.triggerImageUpload = triggerImageUpload;
window.triggerVideoUpload = triggerVideoUpload;
window.removeImagePreview = removeImagePreview;
window.removeVideoPreview = removeVideoPreview;
window.submitReview = submitReview;
window.updateRateButtonToViewRating = updateRateButtonToViewRating;

