@extends('layouts.establishment')

@section('title', 'SavEats')
@section('header', 'Dashboard')

@section('content')
<div class="welcome-section">
    <h2>Hi {{ $user->name ?? session('user_name', 'User') }}!</h2>
    <p>Ready to save food today?</p>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card active-listings">
        <div class="stat-label">Active Listings</div>
        <div class="stat-value">{{ $dashboardStats['active_listings'] ?? 0 }}</div>
    </div>
    <div class="stat-card earnings">
        <div class="stat-label">Today's Earnings</div>
        <div class="stat-value">₱ {{ number_format($dashboardStats['today_earnings'] ?? 0, 2) }}</div>
    </div>
    <div class="stat-card food-donated">
        <div class="stat-label">Food Donated</div>
        <div class="stat-value">{{ $dashboardStats['food_donated'] ?? 0 }}</div>
    </div>
    <div class="stat-card food-saved">
        <div class="stat-label">Food Saved</div>
        <div class="stat-value">{{ $dashboardStats['food_saved'] ?? 0 }} pcs.</div>
    </div>
</div>

<!-- Flash Sale Banner -->
<div class="flash-sale">
    <div class="flash-sale-text">🎉 Flash Sale: Everything 30% off until midnight! 🎉</div>
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
            
            <div class="inventory-chart">
                <div class="chart-bar">
                    <div class="bar-label">Fresh Stock</div>
                    <div class="bar-container">
                        <div class="bar-fill fresh" style="width: {{ $inventoryHealth['fresh_stock_percent'] ?? 0 }}%"></div>
                    </div>
                    <div class="bar-percentage">{{ $inventoryHealth['fresh_stock_percent'] ?? 0 }}%</div>
                </div>
                
                <div class="chart-bar">
                    <div class="bar-label">Expiring Stock</div>
                    <div class="bar-container">
                        <div class="bar-fill expiring" style="width: {{ $inventoryHealth['expiring_stock_percent'] ?? 0 }}%"></div>
                    </div>
                    <div class="bar-percentage">{{ $inventoryHealth['expiring_stock_percent'] ?? 0 }}%</div>
                </div>
                
                <div class="chart-bar">
                    <div class="bar-label">Expired Stock</div>
                    <div class="bar-container">
                        <div class="bar-fill expired" style="width: {{ $inventoryHealth['expired_stock_percent'] ?? 0 }}%"></div>
                    </div>
                    <div class="bar-percentage">{{ $inventoryHealth['expired_stock_percent'] ?? 0 }}%</div>
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
                        <span class="order-detail-value">{{ $pendingOrderData['customer_name'] }}</span>
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
            <div class="rating-summary">
                <div class="rating-score">4.6/5</div>
                <div class="rating-stars">
                    <span class="star">★</span>
                    <span class="star">★</span>
                    <span class="star">★</span>
                    <span class="star">★</span>
                    <span class="star empty">★</span>
                </div>
                <div class="rating-text">You've received +2 reviews this month - 99% positive!</div>
            </div>
            
            <div class="review-breakdown">
                <div class="review-item">
                    <svg class="review-icon positive" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                    </svg>
                    <span>Positive Reviews</span>
                </div>
                <div class="review-count">38</div>
            </div>
            
            <div class="review-breakdown">
                <div class="review-item">
                    <svg class="review-icon negative" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                    </svg>
                    <span>Negative Reviews</span>
                </div>
                <div class="review-count">4</div>
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
</script>
@endsection