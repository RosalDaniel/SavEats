@extends('layouts.establishment')

@section('title', 'SavEats')
@section('header', 'Dashboard')

@section('content')
<div class="welcome-section">
    <h2>Hi {{ $user->name ?? session('user_name', 'User') }}!</h2>
    <p>Ready to save food today?</p>
</div>

<!-- Stats Grid -->
@php
    // Safely get dashboardStats with fallback
    // Ensure all required keys exist with default values
    $stats = isset($dashboardStats) && is_array($dashboardStats) ? $dashboardStats : [];
    $stats = array_merge([
        'active_listings' => 0,
        'today_earnings' => 0,
        'food_donated' => 0,
        'food_saved' => 0,
    ], $stats);
    
    // Ensure all values are properly set (handle null/undefined)
    $stats['active_listings'] = (int) ($stats['active_listings'] ?? 0);
    $stats['today_earnings'] = (float) ($stats['today_earnings'] ?? 0);
    $stats['food_donated'] = (int) ($stats['food_donated'] ?? 0);
    $stats['food_saved'] = (int) ($stats['food_saved'] ?? 0);
@endphp
<div class="stats-grid">
    <div class="stat-card active-listings">
        <div class="stat-label">Active Listings</div>
        <div class="stat-value">{{ $stats['active_listings'] ?? 0 }}</div>
    </div>
    <div class="stat-card earnings">
        <div class="stat-label">Today's Earnings</div>
        <div class="stat-value">₱ {{ number_format($stats['today_earnings'] ?? 0, 2) }}</div>
    </div>
    <div class="stat-card food-donated">
        <div class="stat-label">Food Donated</div>
        <div class="stat-value">{{ $stats['food_donated'] ?? 0 }}</div>
    </div>
    <div class="stat-card food-saved">
        <div class="stat-label">Food Saved</div>
        <div class="stat-value">{{ $stats['food_saved'] ?? 0 }} pcs.</div>
    </div>
</div>

<!-- Expiring Soon Section (Mobile Top) -->
<div class="section-card expiring-soon-section mobile-top-section">
    <div class="section-header">
        <h3 class="section-title">Expiring Soon</h3>
        <a href="{{ route('establishment.listing-management') }}" class="see-all-btn">See All</a>
    </div>
    
    @if(isset($expiringItems) && count($expiringItems) > 0)
        @foreach($expiringItems as $item)
        <div class="expiring-item">
            <div class="expiring-info">
                <h4>{{ $item['name'] }}</h4>
                <p>{{ $item['quantity'] }} pcs.</p>
                <div class="expiring-time">{{ $item['expiry_time'] }}</div>
            </div>
            <div class="expiring-actions">
                <button class="btn btn-donate" onclick="donateItem({{ $item['id'] }})">Donate</button>
                <button class="btn btn-view" onclick="viewListing({{ $item['id'] }})">View Listing</button>
            </div>
        </div>
        @endforeach
    @else
        <div class="expiring-item">
            <div class="expiring-info">
                <p>No items expiring soon.</p>
            </div>
        </div>
    @endif
</div>

<!-- Main Grid -->
<div class="main-grid">
    <!-- Left Column -->
    <div class="left-column">
        <!-- Expiring Soon Section (Desktop) -->
        <div class="section-card expiring-soon-section desktop-only">
            <div class="section-header">
                <h3 class="section-title">Expiring Soon</h3>
                <a href="{{ route('establishment.listing-management') }}" class="see-all-btn">See All</a>
            </div>
            
            @if(isset($expiringItems) && count($expiringItems) > 0)
                @foreach($expiringItems as $item)
                <div class="expiring-item">
                    <div class="expiring-info">
                        <h4>{{ $item['name'] }}</h4>
                        <p>{{ $item['quantity'] }} pcs.</p>
                        <div class="expiring-time">{{ $item['expiry_time'] }}</div>
                    </div>
                    <div class="expiring-actions">
                        <button class="btn btn-donate" onclick="donateItem({{ $item['id'] }})">Donate</button>
                        <button class="btn btn-view" onclick="viewListing({{ $item['id'] }})">View Listing</button>
                    </div>
                </div>
                @endforeach
            @else
                <div class="expiring-item">
                    <div class="expiring-info">
                        <p>No items expiring soon.</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Inventory Health Section -->
        <div class="section-card inventory-health-section">
            <h3 class="section-title">Inventory Health</h3>
            
            @php
                // Safely get inventoryHealth with fallback
                $inventory = isset($inventoryHealth) && is_array($inventoryHealth) ? $inventoryHealth : [];
                $inventory = array_merge([
                    'fresh_stock_percent' => 0,
                    'expiring_stock_percent' => 0,
                    'expired_stock_percent' => 0,
                ], $inventory);
            @endphp
            
            <div class="inventory-chart">
                <div class="chart-bar">
                    <div class="bar-label">Fresh Stock</div>
                    <div class="bar-container">
                        <div class="bar-fill fresh" style="width: {{ $inventory['fresh_stock_percent'] }}%"></div>
                    </div>
                    <div class="bar-percentage">{{ $inventory['fresh_stock_percent'] }}%</div>
                </div>
                
                <div class="chart-bar">
                    <div class="bar-label">Expiring Stock</div>
                    <div class="bar-container">
                        <div class="bar-fill expiring" style="width: {{ $inventory['expiring_stock_percent'] }}%"></div>
                    </div>
                    <div class="bar-percentage">{{ $inventory['expiring_stock_percent'] }}%</div>
                </div>
                
                <div class="chart-bar">
                    <div class="bar-label">Expired Stock</div>
                    <div class="bar-container">
                        <div class="bar-fill expired" style="width: {{ $inventory['expired_stock_percent'] }}%"></div>
                    </div>
                    <div class="bar-percentage">{{ $inventory['expired_stock_percent'] }}%</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Sidebar -->
    <div class="right-sidebar mobile-sidebar">
        <!-- Pending Order -->
        <div class="sidebar-card">
            <h3 class="section-title">Pending Order</h3>
            @if(isset($pendingOrderData) && $pendingOrderData)
            <div class="pending-order">
                <div class="order-header">
                    <div class="order-item-info">
                        <div class="order-item-name">{{ $pendingOrderData['product_name'] }}</div>
                        <div class="order-item-quantity">{{ $pendingOrderData['quantity'] }} pcs.</div>
                    </div>
                    <div class="order-item-price">₱ {{ number_format($pendingOrderData['total_price'], 2) }}</div>
                </div>
                <div class="order-details">
                    <div class="order-detail-row">
                        <span class="order-detail-label">Order ID:</span>
                        <span class="order-detail-value">{{ $pendingOrderData['order_number'] }}</span>
                    </div>
                    <div class="order-detail-row">
                        <span class="order-detail-label">Customer Name:</span>
                        <span class="order-detail-value">{{ urldecode($pendingOrderData['customer_name'] ?? '') }}</span>
                    </div>
                    <div class="order-detail-row">
                        <span class="order-detail-label">Delivery Method:</span>
                        <span class="order-detail-value">{{ $pendingOrderData['delivery_method'] }}</span>
                    </div>
                </div>
            </div>
            <a href="{{ route('establishment.order-management') }}" class="order-action-btn">→ Go to Order Management</a>
            @else
            <div class="pending-order">
                <div class="order-details" style="text-align: center; padding: 20px; color: #6c757d;">
                    <p>No pending orders at the moment.</p>
                </div>
            </div>
            <a href="{{ route('establishment.order-management') }}" class="order-action-btn">→ Go to Order Management</a>
            @endif
        </div>

        <!-- Reviews and Ratings -->
        <div class="sidebar-card">
            <h3 class="section-title">Reviews and Ratings</h3>
            @php
                $reviewsData = $reviewsData ?? [
                    'average_rating' => 0,
                    'total_reviews' => 0,
                    'positive_reviews' => 0,
                    'negative_reviews' => 0,
                    'reviews_this_month' => 0,
                    'positive_percentage' => 0,
                    'rating_text' => 'No reviews yet. Start selling to get reviews!',
                ];
                $avgRating = $reviewsData['average_rating'] ?? 0;
                $fullStars = floor($avgRating);
                $hasHalfStar = ($avgRating - $fullStars) >= 0.5;
            @endphp
            <div class="rating-summary">
                <div class="rating-score" id="ratingScore">{{ $avgRating > 0 ? number_format($avgRating, 1) : '0.0' }}/5</div>
                <div class="rating-stars" id="ratingStars">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= $fullStars)
                            <span class="star filled">★</span>
                        @elseif($i == $fullStars + 1 && $hasHalfStar)
                            <span class="star half">★</span>
                        @else
                            <span class="star empty">★</span>
                        @endif
                    @endfor
                </div>
                <div class="rating-text" id="ratingText">{{ $reviewsData['rating_text'] ?? 'No reviews yet.' }}</div>
            </div>
            
            <div class="review-breakdown">
                <div class="review-item">
                    <svg class="review-icon positive" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    <span>Positive Reviews</span>
                </div>
                <div class="review-count" id="positiveReviewsCount">{{ $reviewsData['positive_reviews'] ?? 0 }}</div>
            </div>
            
            <div class="review-breakdown">
                <div class="review-item">
                    <svg class="review-icon negative" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2l-3.09 6.26L2 9.27l5 4.87-1.18 6.88L12 17.77l6.18 3.25L17 14.14l5-4.87-6.91-1.01L12 2z"/>
                    </svg>
                    <span>Negative Reviews</span>
                </div>
                <div class="review-count" id="negativeReviewsCount">{{ $reviewsData['negative_reviews'] ?? 0 }}</div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function donateItem(itemId) {
    // Redirect to listing management with the item selected for donation
    window.location.href = '{{ route("establishment.listing-management") }}?donate=' + itemId;
}

function viewListing(itemId) {
    // Redirect to listing management and scroll to the item
    window.location.href = '{{ route("establishment.listing-management") }}?view=' + itemId;
}

// Real-time reviews and ratings updates
(function() {
    const ratingScore = document.getElementById('ratingScore');
    const ratingStars = document.getElementById('ratingStars');
    const ratingText = document.getElementById('ratingText');
    const positiveReviewsCount = document.getElementById('positiveReviewsCount');
    const negativeReviewsCount = document.getElementById('negativeReviewsCount');
    
    function updateRatings(data) {
        if (!data) return;
        
        // Update rating score
        if (ratingScore) {
            const avgRating = data.average_rating || 0;
            ratingScore.textContent = avgRating > 0 ? parseFloat(avgRating).toFixed(1) + '/5' : '0.0/5';
        }
        
        // Update stars
        if (ratingStars) {
            const avgRating = data.average_rating || 0;
            const fullStars = Math.floor(avgRating);
            const hasHalfStar = (avgRating - fullStars) >= 0.5;
            
            let starsHTML = '';
            for (let i = 1; i <= 5; i++) {
                if (i <= fullStars) {
                    starsHTML += '<span class="star filled">★</span>';
                } else if (i === fullStars + 1 && hasHalfStar) {
                    starsHTML += '<span class="star half">★</span>';
                } else {
                    starsHTML += '<span class="star empty">★</span>';
                }
            }
            ratingStars.innerHTML = starsHTML;
        }
        
        // Update rating text
        if (ratingText) {
            ratingText.textContent = data.rating_text || 'No reviews yet.';
        }
        
        // Update counts
        if (positiveReviewsCount) {
            positiveReviewsCount.textContent = data.positive_reviews || 0;
        }
        if (negativeReviewsCount) {
            negativeReviewsCount.textContent = data.negative_reviews || 0;
        }
    }
    
    function fetchRatings() {
        fetch('{{ route("establishment.dashboard.ratings") }}', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateRatings(data.data);
            }
        })
        .catch(error => {
            console.error('Error fetching ratings:', error);
        });
    }
    
    // Fetch ratings on page load
    fetchRatings();
    
    // Auto-refresh every 30 seconds
    setInterval(fetchRatings, 30000);
    
    // Also refresh when the page becomes visible (user switches back to tab)
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            fetchRatings();
        }
    });
})();
</script>
@endsection