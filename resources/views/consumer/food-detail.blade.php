@extends('layouts.consumer')

@section('title', $foodItem['name'] . ' - SavEats')

@section('header', 'Product Details')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/food-detail.css') }}">
@endsection

@section('content')
<div class="product-detail-container">
    <!-- Product Header Section - Outside main container -->
    

    <!-- Main Product Container - Single unified container -->
    <div class="main-product-container">
    <div class="product-header">
        <h1 class="product-name">{{ $foodItem['name'] }}</h1>
        <p class="store-name">{{ $foodItem['store'] }}</p>
    </div>
        <!-- Product Image Section -->
        <div class="product-image">
            <img src="{{ $foodItem['image'] }}" alt="{{ $foodItem['name'] }}" />
        </div>

        <!-- Pricing and Discount Section -->
        <div class="pricing-section">
            <div class="price-info">
                <span class="current-price">₱{{ number_format($foodItem['price'], 2) }}</span>
                @if($foodItem['discount'] > 0)
                    <span class="discount-tag">{{ $foodItem['discount'] }}% OFF</span>
                @endif
                <span class="original-price">₱{{ number_format($foodItem['original_price'], 2) }}</span>
            </div>
        </div>

        <!-- Product Details Section -->
        <div class="product-details">
            <div class="detail-item">
                <svg class="detail-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                </svg>
                <span>{{ $foodItem['location'] }}</span>
            </div>
            
            <div class="detail-row">
                <div class="detail-item">
                    <svg class="detail-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    <span>{{ $foodItem['pickup_available'] ? 'Pick-Up Available' : 'Delivery Only' }}</span>
                </div>
                <div class="operating-hours">{{ $foodItem['operating_hours'] }}</div>
            </div>
            
            <div class="detail-item">
                <svg class="detail-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm.5 15h-1v-6h1v6zm0-8h-1V7h1v2z"/>
                </svg>
                <span>Expiry Date: {{ $foodItem['expiry_formatted'] }}</span>
            </div>
            
            <div class="detail-item">
                <svg class="detail-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
                <span>Stock: {{ $foodItem['quantity'] }}</span>
            </div>
        </div>

        <!-- Quantity Selector and Call to Action - Inside the main container -->
        <div class="action-section">
            <!-- Quantity Selector -->
            <div class="quantity-selector">
                <div class="quantity-controls">
                    <button class="quantity-btn decrease" onclick="decreaseQuantity()">-</button>
                    <input type="number" id="quantityInput" class="quantity-input" value="1" min="1" max="{{ $foodItem['quantity'] }}">
                    <button class="quantity-btn increase" onclick="increaseQuantity()">+</button>
                </div>
                <div class="availability-info">
                    <span>{{ $foodItem['quantity'] }} pieces available</span>
                </div>
            </div>
            
            <!-- Buy Now Button -->
            <button class="buy-now-btn" onclick="buyNow({{ $foodItem['id'] }})">
                <svg class="btn-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M7 18c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12L8.1 13h7.45c.75 0 1.41-.41 1.75-1.03L21.7 4H5.21l-.94-2H1zm16 16c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                </svg>
                Buy Now
            </button>
        </div>
    </div>

    <!-- Customer Reviews Section -->
    <div class="reviews-section">
        <div class="reviews-header">
            <div class="rating-summary">
                <div class="stars">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= floor($foodItem['rating']))
                            <svg class="star filled" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        @elseif($i - 0.5 <= $foodItem['rating'])
                            <svg class="star half" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        @else
                            <svg class="star empty" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        @endif
                    @endfor
                </div>
                <span class="rating-text">{{ $foodItem['rating'] }} out of 5</span>
            </div>
            
            <div class="rating-filters">
                <button class="filter-btn active" data-rating="all">All</button>
                <button class="filter-btn" data-rating="5">5 Stars</button>
                <button class="filter-btn" data-rating="4">4 Stars</button>
                <button class="filter-btn" data-rating="3">3 Stars</button>
                <button class="filter-btn" data-rating="2">2 Stars</button>
                <button class="filter-btn" data-rating="1">1 Star</button>
            </div>
        </div>

        <div class="reviews-list">
            @foreach($reviews as $review)
            <div class="review-item" data-rating="{{ $review['rating'] }}">
                <div class="review-header">
                    @if($review['avatar'])
                        <img src="{{ $review['avatar'] }}" alt="{{ $review['user_name'] }}" class="review-avatar">
                    @else
                        <div class="review-avatar-initials">
                            {{ strtoupper(substr($review['user_name'], 0, 1)) }}{{ strtoupper(substr(explode(' ', $review['user_name'])[1] ?? '', 0, 1)) }}
                        </div>
                    @endif
                    <span class="reviewer-name">{{ $review['user_name'] }}</span>
                    <div class="review-rating">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= $review['rating'])
                                <svg class="star filled" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            @else
                                <svg class="star empty" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            @endif
                        @endfor
                    </div>
                </div>
                <p class="review-comment">{{ $review['comment'] }}</p>
            </div>
            @endforeach
        </div>

        <div class="show-more-section">
            <button class="show-more-btn" onclick="showMoreReviews()">
                Show more ({{ $foodItem['total_reviews'] - count($reviews) }})
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Product detail page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    setupRatingFilters();
});

// Setup rating filter functionality
function setupRatingFilters() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all buttons
            filterButtons.forEach(b => b.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            const rating = this.dataset.rating;
            filterReviews(rating);
        });
    });
}

// Filter reviews based on rating
function filterReviews(rating) {
    const reviews = document.querySelectorAll('.review-item');
    
    reviews.forEach(review => {
        if (rating === 'all' || review.dataset.rating === rating) {
            review.style.display = 'block';
        } else {
            review.style.display = 'none';
        }
    });
}

// Quantity control functions
function increaseQuantity() {
    const quantityInput = document.getElementById('quantityInput');
    const currentValue = parseInt(quantityInput.value) || 1;
    const maxValue = parseInt(quantityInput.max) || 1;
    
    if (currentValue < maxValue) {
        quantityInput.value = currentValue + 1;
    }
}

function decreaseQuantity() {
    const quantityInput = document.getElementById('quantityInput');
    const currentValue = parseInt(quantityInput.value) || 1;
    
    if (currentValue > 1) {
        quantityInput.value = currentValue - 1;
    }
}

// Buy now function
function buyNow(foodId) {
    const quantity = document.getElementById('quantityInput').value;
    // In a real app, this would make an API call to create an order
    showNotification(`Order placed successfully! Quantity: ${quantity}`, 'success');
}

// Show more reviews function
function showMoreReviews() {
    // In a real app, this would load more reviews from the server
    showNotification('Loading more reviews...', 'info');
}

// Notification function
function showNotification(message, type = 'info') {
    // Simple notification - in a real app, you might use a toast library
    alert(message);
}
</script>
@endsection
