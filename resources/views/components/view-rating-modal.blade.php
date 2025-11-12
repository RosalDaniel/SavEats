<!-- View Rating Modal -->
<div id="viewRatingModal" class="rate-modal">
    <div class="rate-modal-overlay"></div>
    <div class="rate-modal-container">
        <div class="rate-modal-header">
            <h2 class="rate-modal-title">Your Rating</h2>
            <button class="rate-modal-close" onclick="closeViewRatingModal()" aria-label="Close modal">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        
        <div class="rate-modal-content">
            <div id="viewRatingContent">
                <!-- Loading state -->
                <div class="rating-loading" style="text-align: center; padding: 40px;">
                    <p>Loading rating...</p>
                </div>
            </div>
        </div>
    </div>
</div>

