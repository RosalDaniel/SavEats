<!-- Rate Product Modal -->
<div id="rateModal" class="rate-modal">
    <div class="rate-modal-overlay"></div>
    <div class="rate-modal-container">
        <div class="rate-modal-header">
            <h2 class="rate-modal-title">Rate the Product</h2>
            <button class="rate-modal-close" onclick="closeRateModal()" aria-label="Close modal">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        
        <div class="rate-modal-content">
            <form id="rateForm" onsubmit="submitReview(event)">
                <input type="hidden" id="rateOrderId" name="order_id">
                
                <!-- Product Quality Rating -->
                <div class="rating-section">
                    <label class="rating-label">Product Quality</label>
                    <div class="star-rating" id="starRating">
                        <span class="star" data-rating="1">★</span>
                        <span class="star" data-rating="2">★</span>
                        <span class="star" data-rating="3">★</span>
                        <span class="star" data-rating="4">★</span>
                        <span class="star" data-rating="5">★</span>
                    </div>
                    <input type="hidden" id="ratingValue" name="rating" value="0" required>
                </div>
                
                <!-- Upload Buttons -->
                <div class="upload-buttons">
                    <button type="button" class="upload-btn upload-image-btn" onclick="triggerImageUpload()">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                            <polyline points="21 15 16 10 5 21"></polyline>
                        </svg>
                        Upload Image
                    </button>
                    <button type="button" class="upload-btn upload-video-btn" onclick="triggerVideoUpload()">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="23 7 16 12 23 17 23 7"></polygon>
                            <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
                        </svg>
                        Upload Video
                    </button>
                </div>
                <input type="file" id="imageUpload" name="image" accept="image/*" style="display: none;" onchange="handleImageUpload(event)">
                <input type="file" id="videoUpload" name="video" accept="video/*" style="display: none;" onchange="handleVideoUpload(event)">
                
                <!-- Uploaded Files Preview -->
                <div id="uploadPreview" class="upload-preview" style="display: none;">
                    <div id="imagePreview" class="preview-item"></div>
                    <div id="videoPreview" class="preview-item"></div>
                </div>
                
                <!-- Description Textarea -->
                <div class="description-section">
                    <textarea 
                        id="reviewDescription" 
                        name="description" 
                        class="review-textarea" 
                        placeholder="Write description"
                        rows="6"
                    ></textarea>
                </div>
                
                <!-- Submit Button -->
                <div class="rate-modal-actions">
                    <button type="submit" class="publish-btn">Publish Review</button>
                </div>
            </form>
        </div>
    </div>
</div>

