// View Rating Modal JavaScript

// Open view rating modal
function openViewRatingModal(orderId) {
    const modal = document.getElementById('viewRatingModal');
    const content = document.getElementById('viewRatingContent');
    
    if (!modal || !content) return;
    
    // Show loading state
    content.innerHTML = '<div class="rating-loading" style="text-align: center; padding: 40px;"><p>Loading rating...</p></div>';
    
    // Show modal
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
    
    // Focus on close button for accessibility
    const closeButton = modal.querySelector('.rate-modal-close');
    if (closeButton) {
        setTimeout(() => closeButton.focus(), 100);
    }
    
    // Fetch rating data
    fetch(`/consumer/orders/${orderId}/review`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
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
            throw new Error(data.message || `HTTP error! status: ${response.status}`);
        }
        return data;
    })
    .then(data => {
        if (data.success && data.review) {
            displayRating(data.review);
        } else {
            content.innerHTML = '<div class="rating-error" style="text-align: center; padding: 40px; color: #dc3545;"><p>No rating found for this order.</p></div>';
        }
    })
    .catch(error => {
        console.error('Error fetching rating:', error);
        content.innerHTML = `<div class="rating-error" style="text-align: center; padding: 40px; color: #dc3545;"><p>Error loading rating: ${error.message}</p></div>`;
    });
}

// Display rating in modal
function displayRating(review) {
    const content = document.getElementById('viewRatingContent');
    if (!content) return;
    
    let html = `
        <div class="view-rating-content">
            <!-- Rating Stars -->
            <div class="rating-section">
                <label class="rating-label">Product Quality</label>
                <div class="star-rating-display">
                    ${generateStarDisplay(review.rating)}
                </div>
            </div>
    `;
    
    // Description
    if (review.description) {
        html += `
            <div class="description-section">
                <label class="description-label">Your Review</label>
                <div class="review-description-display">
                    ${escapeHtml(review.description)}
                </div>
            </div>
        `;
    }
    
    // Image
    if (review.image_path) {
        html += `
            <div class="review-media-section">
                <label class="media-label">Image</label>
                <div class="review-image-display">
                    <img src="${review.image_path}" alt="Review image" style="max-width: 100%; border-radius: 8px;">
                </div>
            </div>
        `;
    }
    
    // Video
    if (review.video_path) {
        html += `
            <div class="review-media-section">
                <label class="media-label">Video</label>
                <div class="review-video-display">
                    <video src="${review.video_path}" controls style="max-width: 100%; border-radius: 8px;"></video>
                </div>
            </div>
        `;
    }
    
    // Date
    html += `
            <div class="rating-date-section">
                <p style="color: #6c757d; font-size: 14px; margin: 0;">Reviewed on ${review.created_at}</p>
            </div>
        </div>
    `;
    
    content.innerHTML = html;
}

// Generate star display
function generateStarDisplay(rating) {
    let html = '';
    for (let i = 1; i <= 5; i++) {
        if (i <= rating) {
            html += '<span class="star filled">★</span>';
        } else {
            html += '<span class="star">★</span>';
        }
    }
    return html;
}

// Escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Close view rating modal
function closeViewRatingModal() {
    const modal = document.getElementById('viewRatingModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

// Close modal on overlay click
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('viewRatingModal');
    if (modal) {
        const overlay = modal.querySelector('.rate-modal-overlay');
        if (overlay) {
            overlay.addEventListener('click', closeViewRatingModal);
        }
        
        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('show')) {
                closeViewRatingModal();
            }
        });
    }
});

// Make functions globally accessible
window.openViewRatingModal = openViewRatingModal;
window.closeViewRatingModal = closeViewRatingModal;

