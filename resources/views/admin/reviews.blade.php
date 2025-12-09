@extends('layouts.admin')

@section('title', 'Review Management - Admin Dashboard')

@section('header', 'Review Management')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin-reviews.css') }}">
@endsection

@section('content')
<div class="reviews-management-page">
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon total">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Total Reviews</h3>
                <p class="stat-number">{{ number_format($stats['total_reviews'] ?? 0) }}</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon flagged">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Flagged Reviews</h3>
                <p class="stat-number">{{ number_format($stats['flagged_reviews'] ?? 0) }}</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon average">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Average Rating</h3>
                <p class="stat-number">{{ number_format($stats['average_rating'] ?? 0, 1) }}/5</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon today">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Reviews Today</h3>
                <p class="stat-number">{{ number_format($stats['reviews_today'] ?? 0) }}</p>
            </div>
        </div>
    </div>

    <!-- Combined Filters and Reviews Section -->
    <div class="combined-section">
        <div class="filters-header">
            <h2>Filters</h2>
            <button class="btn-clear-filters" onclick="clearFilters()">Clear All</button>
        </div>
        <div class="filters-grid">
            <div class="filter-group">
                <label for="search">Search</label>
                <input type="text" id="search" name="search" class="filter-input" 
                       placeholder="Search by review, reviewer, or establishment..." 
                       value="{{ $searchQuery ?? '' }}">
            </div>
            
            <div class="filter-group">
                <label for="rating">Rating</label>
                <select id="rating" name="rating" class="filter-select">
                    <option value="all" {{ ($ratingFilter ?? 'all') === 'all' ? 'selected' : '' }}>All Ratings</option>
                    <option value="5" {{ ($ratingFilter ?? 'all') === '5' ? 'selected' : '' }}>5 Stars</option>
                    <option value="4" {{ ($ratingFilter ?? 'all') === '4' ? 'selected' : '' }}>4 Stars</option>
                    <option value="3" {{ ($ratingFilter ?? 'all') === '3' ? 'selected' : '' }}>3 Stars</option>
                    <option value="2" {{ ($ratingFilter ?? 'all') === '2' ? 'selected' : '' }}>2 Stars</option>
                    <option value="1" {{ ($ratingFilter ?? 'all') === '1' ? 'selected' : '' }}>1 Star</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="flagged">Flagged Status</label>
                <select id="flagged" name="flagged" class="filter-select">
                    <option value="all" {{ ($flaggedFilter ?? 'all') === 'all' ? 'selected' : '' }}>All Reviews</option>
                    <option value="yes" {{ ($flaggedFilter ?? 'all') === 'yes' ? 'selected' : '' }}>Flagged</option>
                    <option value="no" {{ ($flaggedFilter ?? 'all') === 'no' ? 'selected' : '' }}>Not Flagged</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="date_from">Date From</label>
                <input type="date" id="date_from" name="date_from" class="filter-input" 
                       value="{{ $dateFrom ?? '' }}">
            </div>
            
            <div class="filter-group">
                <label for="date_to">Date To</label>
                <input type="date" id="date_to" name="date_to" class="filter-input" 
                       value="{{ $dateTo ?? '' }}">
            </div>
        </div>

        <div class="table-header">
            <h2>All Reviews ({{ $reviews->total() }})</h2>
        </div>
        
        <div class="table-container">
            <table class="reviews-table">
                <thead>
                    <tr>
                        <th>Reviewer</th>
                        <th>Establishment</th>
                        <th>Item</th>
                        <th>Rating</th>
                        <th>Review</th>
                        <th>Media</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $review)
                    <tr data-review-id="{{ $review->id }}" class="{{ $review->flagged ? 'flagged-row' : '' }}">
                        <td>
                            <div class="reviewer-info">
                                <div class="reviewer-name">
                                    {{ $review->consumer ? ($review->consumer->fname . ' ' . $review->consumer->lname) : 'N/A' }}
                                </div>
                                <div class="reviewer-email">{{ $review->consumer->email ?? 'N/A' }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="establishment-info">
                                <div class="establishment-name">{{ $review->establishment->business_name ?? 'N/A' }}</div>
                                <div class="establishment-email">{{ $review->establishment->email ?? 'N/A' }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="item-name">{{ $review->foodListing->name ?? 'N/A' }}</div>
                        </td>
                        <td>
                            <div class="rating-display">
                                @for($i = 1; $i <= 5; $i++)
                                    <span class="star {{ $i <= $review->rating ? 'filled' : '' }}">★</span>
                                @endfor
                                <span class="rating-value">({{ $review->rating }})</span>
                            </div>
                        </td>
                        <td>
                            <div class="review-text">
                                {{ $review->description ? \Illuminate\Support\Str::limit($review->description, 100) : 'No description' }}
                            </div>
                        </td>
                        <td>
                            <div class="media-preview">
                                @if($review->image_path)
                                    <span class="media-badge image">📷 Image</span>
                                @endif
                                @if($review->video_path)
                                    <span class="media-badge video">🎥 Video</span>
                                @endif
                                @if(!$review->image_path && !$review->video_path)
                                    <span class="no-media">None</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($review->flagged)
                                <span class="status-badge flagged">Flagged</span>
                                @if($review->flagged_at)
                                    <div class="flagged-date">{{ \Carbon\Carbon::parse($review->flagged_at)->format('M d, Y') }}</div>
                                @endif
                            @else
                                <span class="status-badge active">Active</span>
                            @endif
                        </td>
                        <td>
                            <div class="date-info">
                                <div class="date-created">{{ $review->created_at->format('M d, Y') }}</div>
                                <div class="date-time">{{ $review->created_at->format('h:i A') }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-action btn-view" 
                                        onclick="viewReviewDetails({{ $review->id }})" 
                                        title="View Details">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                                    </svg>
                                </button>
                                <button class="btn-action btn-flag" 
                                        onclick="toggleFlag({{ $review->id }}, {{ $review->flagged ? 'true' : 'false' }})" 
                                        title="{{ $review->flagged ? 'Unflag Review' : 'Flag Review' }}">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        @if($review->flagged)
                                        <path d="M14.59 8L12 10.59 9.41 8 8 9.41 10.59 12 8 14.59 9.41 16 12 13.41 14.59 16 16 14.59 13.41 12 16 9.41 14.59 8zM12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
                                        @else
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                        @endif
                                    </svg>
                                </button>
                                <button class="btn-action btn-delete" 
                                        onclick="deleteReview({{ $review->id }})" 
                                        title="Delete Review">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="no-records">No reviews found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="pagination-container">
            {{ $reviews->links() }}
        </div>
    </div>
</div>

<!-- Review Details Modal -->
<div class="modal-overlay" id="reviewDetailsModal">
    <div class="modal-content review-details-modal">
        <div class="modal-header">
            <h3 class="modal-title">Review Details</h3>
            <button type="button" class="close-modal" id="closeReviewDetailsModal">&times;</button>
        </div>
        <div class="modal-body" id="reviewDetailsContent">
            <div class="loading-spinner">
                <p>Loading review details...</p>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" id="closeReviewDetailsBtn">Close</button>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Confirm Delete</h3>
            <button type="button" class="close-modal" id="closeDeleteModal">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete this review? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" id="cancelDeleteBtn">Cancel</button>
            <button type="button" class="btn-danger" id="confirmDeleteBtn">Delete</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const REVIEW_ROUTES = {
        details: '{{ route('admin.reviews.details', ':id') }}',
        flag: '{{ route('admin.reviews.flag', ':id') }}',
        delete: '{{ route('admin.reviews.delete', ':id') }}'
    };

    function clearFilters() {
        document.getElementById('search').value = '';
        document.getElementById('rating').value = 'all';
        document.getElementById('flagged').value = 'all';
        document.getElementById('date_from').value = '';
        document.getElementById('date_to').value = '';
        applyFilters();
    }

    function applyFilters() {
        const params = new URLSearchParams();
        const search = document.getElementById('search').value;
        const rating = document.getElementById('rating').value;
        const flagged = document.getElementById('flagged').value;
        const dateFrom = document.getElementById('date_from').value;
        const dateTo = document.getElementById('date_to').value;

        if (search) params.append('search', search);
        if (rating !== 'all') params.append('rating', rating);
        if (flagged !== 'all') params.append('flagged', flagged);
        if (dateFrom) params.append('date_from', dateFrom);
        if (dateTo) params.append('date_to', dateTo);

        window.location.href = '{{ route('admin.reviews') }}?' + params.toString();
    }

    // Add event listeners for filter changes
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('search').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                applyFilters();
            }
        });
        document.getElementById('rating').addEventListener('change', applyFilters);
        document.getElementById('flagged').addEventListener('change', applyFilters);
        document.getElementById('date_from').addEventListener('change', applyFilters);
        document.getElementById('date_to').addEventListener('change', applyFilters);
    });

    let reviewToDelete = null;

    function deleteReview(id) {
        reviewToDelete = id;
        document.getElementById('deleteModal').style.display = 'flex';
    }

    function toggleFlag(id, currentFlagged) {
        const url = REVIEW_ROUTES.flag.replace(':id', id);
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to update flag status.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the flag status.');
        });
    }

    // Modal handlers
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (reviewToDelete) {
            const url = REVIEW_ROUTES.delete.replace(':id', reviewToDelete);
            
            fetch(url, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to delete review.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the review.');
            });
        }
        document.getElementById('deleteModal').style.display = 'none';
        reviewToDelete = null;
    });

    document.getElementById('cancelDeleteBtn').addEventListener('click', function() {
        document.getElementById('deleteModal').style.display = 'none';
        reviewToDelete = null;
    });

    document.getElementById('closeDeleteModal').addEventListener('click', function() {
        document.getElementById('deleteModal').style.display = 'none';
        reviewToDelete = null;
    });

    // Review Details Modal Functions
    function viewReviewDetails(id) {
        const modal = document.getElementById('reviewDetailsModal');
        const content = document.getElementById('reviewDetailsContent');
        const url = REVIEW_ROUTES.details.replace(':id', id);
        
        // Show modal with loading state
        modal.style.display = 'flex';
        content.innerHTML = '<div class="loading-spinner"><p>Loading review details...</p></div>';
        
        fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                displayReviewDetails(data.review);
            } else {
                const errorMsg = data.message || data.error || 'Failed to load review details. Please try again.';
                content.innerHTML = `<div class="error-message"><p>${errorMsg}</p></div>`;
                console.error('API Error:', data);
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            content.innerHTML = '<div class="error-message"><p>An error occurred while loading review details. Please check the console for details.</p></div>';
        });
    }

    function displayReviewDetails(review) {
        const content = document.getElementById('reviewDetailsContent');
        
        // Generate stars HTML
        let starsHTML = '';
        for (let i = 1; i <= 5; i++) {
            starsHTML += `<span class="star ${i <= review.rating ? 'filled' : ''}">★</span>`;
        }
        
        // Format review HTML
        const reviewHTML = `
            <div class="review-details-container">
                <div class="review-details-section">
                    <h4>Review Information</h4>
                    <div class="detail-row">
                        <span class="detail-label">Review ID:</span>
                        <span class="detail-value">#${review.id}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Rating:</span>
                        <span class="detail-value">
                            <div class="rating-display">${starsHTML} <span class="rating-value">(${review.rating}/5)</span></div>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Status:</span>
                        <span class="detail-value">
                            <span class="status-badge ${review.flagged ? 'flagged' : 'active'}">${review.flagged ? 'Flagged' : 'Active'}</span>
                        </span>
                    </div>
                    ${review.flagged && review.flagged_at ? `
                    <div class="detail-row">
                        <span class="detail-label">Flagged At:</span>
                        <span class="detail-value">${review.flagged_at}</span>
                    </div>
                    ` : ''}
                    <div class="detail-row">
                        <span class="detail-label">Created:</span>
                        <span class="detail-value">${review.created_at}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Last Updated:</span>
                        <span class="detail-value">${review.updated_at}</span>
                    </div>
                </div>

                <div class="review-details-section">
                    <h4>Review Content</h4>
                    ${review.description ? `
                    <div class="detail-row full-width">
                        <span class="detail-label">Comment:</span>
                        <div class="detail-value review-description">${review.description}</div>
                    </div>
                    ` : '<div class="detail-row"><span class="detail-value">No comment provided.</span></div>'}
                    ${review.image_path ? `
                    <div class="detail-row full-width">
                        <span class="detail-label">Image:</span>
                        <div class="detail-value">
                            <img src="${review.image_path}" alt="Review image" class="review-detail-image" />
                        </div>
                    </div>
                    ` : ''}
                    ${review.video_path ? `
                    <div class="detail-row full-width">
                        <span class="detail-label">Video:</span>
                        <div class="detail-value">
                            <video src="${review.video_path}" controls class="review-detail-video"></video>
                        </div>
                    </div>
                    ` : ''}
                </div>

                <div class="review-details-section">
                    <h4>Reviewer Information</h4>
                    ${review.consumer ? `
                    <div class="detail-row">
                        <span class="detail-label">Name:</span>
                        <span class="detail-value">${review.consumer.name || 'N/A'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value">${review.consumer.email || 'N/A'}</span>
                    </div>
                    ${review.consumer.phone_no ? `
                    <div class="detail-row">
                        <span class="detail-label">Phone:</span>
                        <span class="detail-value">${review.consumer.phone_no}</span>
                    </div>
                    ` : ''}
                    ${review.consumer.address ? `
                    <div class="detail-row full-width">
                        <span class="detail-label">Address:</span>
                        <span class="detail-value">${review.consumer.address}</span>
                    </div>
                    ` : ''}
                    <div class="detail-row">
                        <span class="detail-label">Member Since:</span>
                        <span class="detail-value">${review.consumer.member_since}</span>
                    </div>
                    ` : '<div class="detail-row"><span class="detail-value">Reviewer information not available.</span></div>'}
                </div>

                <div class="review-details-section">
                    <h4>Establishment Information</h4>
                    ${review.establishment ? `
                    <div class="detail-row">
                        <span class="detail-label">Business Name:</span>
                        <span class="detail-value">${review.establishment.name || 'N/A'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value">${review.establishment.email || 'N/A'}</span>
                    </div>
                    ${review.establishment.phone_no ? `
                    <div class="detail-row">
                        <span class="detail-label">Phone:</span>
                        <span class="detail-value">${review.establishment.phone_no}</span>
                    </div>
                    ` : ''}
                    ${review.establishment.address ? `
                    <div class="detail-row full-width">
                        <span class="detail-label">Address:</span>
                        <span class="detail-value">${review.establishment.address}</span>
                    </div>
                    ` : ''}
                    <div class="detail-row">
                        <span class="detail-label">Member Since:</span>
                        <span class="detail-value">${review.establishment.member_since}</span>
                    </div>
                    ` : '<div class="detail-row"><span class="detail-value">Establishment information not available.</span></div>'}
                </div>

                ${review.food_listing ? `
                <div class="review-details-section">
                    <h4>Food Item Information</h4>
                    <div class="detail-row">
                        <span class="detail-label">Item Name:</span>
                        <span class="detail-value">${review.food_listing.name || 'N/A'}</span>
                    </div>
                    ${review.food_listing.description ? `
                    <div class="detail-row full-width">
                        <span class="detail-label">Description:</span>
                        <div class="detail-value">${review.food_listing.description}</div>
                    </div>
                    ` : ''}
                    <div class="detail-row">
                        <span class="detail-label">Original Price:</span>
                        <span class="detail-value">₱${parseFloat(review.food_listing.original_price || 0).toFixed(2)}</span>
                    </div>
                    ${review.food_listing.discount_percentage ? `
                    <div class="detail-row">
                        <span class="detail-label">Discount:</span>
                        <span class="detail-value">${review.food_listing.discount_percentage}%</span>
                    </div>
                    ` : ''}
                    ${review.food_listing.image_url ? `
                    <div class="detail-row full-width">
                        <span class="detail-label">Item Image:</span>
                        <div class="detail-value">
                            <img src="${review.food_listing.image_url}" alt="Food item" class="review-detail-image" />
                        </div>
                    </div>
                    ` : ''}
                </div>
                ` : ''}

                ${review.order ? `
                <div class="review-details-section">
                    <h4>Order Information</h4>
                    <div class="detail-row">
                        <span class="detail-label">Order Number:</span>
                        <span class="detail-value">${review.order.order_number || 'N/A'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Order Status:</span>
                        <span class="detail-value">${review.order.status || 'N/A'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Total Price:</span>
                        <span class="detail-value">₱${parseFloat(review.order.total_price || 0).toFixed(2)}</span>
                    </div>
                    ${review.order.delivery_method ? `
                    <div class="detail-row">
                        <span class="detail-label">Delivery Method:</span>
                        <span class="detail-value">${review.order.delivery_method}</span>
                    </div>
                    ` : ''}
                    <div class="detail-row">
                        <span class="detail-label">Order Date:</span>
                        <span class="detail-value">${review.order.created_at}</span>
                    </div>
                </div>
                ` : ''}
            </div>
        `;
        
        content.innerHTML = reviewHTML;
    }

    // Close review details modal
    document.getElementById('closeReviewDetailsModal').addEventListener('click', function() {
        document.getElementById('reviewDetailsModal').style.display = 'none';
    });

    document.getElementById('closeReviewDetailsBtn').addEventListener('click', function() {
        document.getElementById('reviewDetailsModal').style.display = 'none';
    });

    // Close modal when clicking outside
    document.getElementById('reviewDetailsModal').addEventListener('click', function(e) {
        if (e.target === this) {
            this.style.display = 'none';
        }
    });
</script>
@endsection

