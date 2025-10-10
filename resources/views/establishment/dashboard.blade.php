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
        <div class="stat-value">500</div>
    </div>
    <div class="stat-card earnings">
        <div class="stat-label">Today's Earnings</div>
        <div class="stat-value">₱ 500.00</div>
    </div>
    <div class="stat-card food-donated">
        <div class="stat-label">Food Donated</div>
        <div class="stat-value">500</div>
    </div>
    <div class="stat-card food-saved">
        <div class="stat-label">Food Saved</div>
        <div class="stat-value">500 pcs.</div>
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
        <a href="#" class="see-all-btn">See All</a>
    </div>
    
    <div class="expiring-item">
        <div class="expiring-info">
            <h4>Banana Bread</h4>
            <p>10 pcs.</p>
            <div class="expiring-time">Today, 6pm</div>
        </div>
        <div class="expiring-actions">
            <button class="btn btn-donate">Donate</button>
            <button class="btn btn-view">View Listing</button>
        </div>
    </div>

    <div class="expiring-item">
        <div class="expiring-info">
            <h4>Banana Bread</h4>
            <p>10 pcs.</p>
            <div class="expiring-time">Today, 6pm</div>
        </div>
        <div class="expiring-actions">
            <button class="btn btn-donate">Donate</button>
            <button class="btn btn-view">View Listing</button>
        </div>
    </div>
</div>

<!-- Main Grid -->
<div class="main-grid">
    <!-- Left Column -->
    <div class="left-column">
        <!-- Expiring Soon Section (Desktop) -->
        <div class="section-card expiring-soon-section desktop-only">
            <div class="section-header">
                <h3 class="section-title">Expiring Soon</h3>
                <a href="#" class="see-all-btn">See All</a>
            </div>
            
            <div class="expiring-item">
                <div class="expiring-info">
                    <h4>Banana Bread</h4>
                    <p>10 pcs.</p>
                    <div class="expiring-time">Today, 6pm</div>
                </div>
                <div class="expiring-actions">
                    <button class="btn btn-donate">Donate</button>
                    <button class="btn btn-view">View Listing</button>
                </div>
            </div>

            <div class="expiring-item">
                <div class="expiring-info">
                    <h4>Banana Bread</h4>
                    <p>10 pcs.</p>
                    <div class="expiring-time">Today, 6pm</div>
                </div>
                <div class="expiring-actions">
                    <button class="btn btn-donate">Donate</button>
                    <button class="btn btn-view">View Listing</button>
                </div>
            </div>
        </div>

        <!-- Inventory Health Section -->
        <div class="section-card inventory-health-section">
            <h3 class="section-title">Inventory Health</h3>
            
            <div class="inventory-chart">
                <div class="chart-bar">
                    <div class="bar-label">Fresh Stock</div>
                    <div class="bar-container">
                        <div class="bar-fill fresh" style="width: 85%"></div>
                    </div>
                    <div class="bar-percentage">85%</div>
                </div>
                
                <div class="chart-bar">
                    <div class="bar-label">Expiring Stock</div>
                    <div class="bar-container">
                        <div class="bar-fill expiring" style="width: 25%"></div>
                    </div>
                    <div class="bar-percentage">25%</div>
                </div>
                
                <div class="chart-bar">
                    <div class="bar-label">Expired Stock</div>
                    <div class="bar-container">
                        <div class="bar-fill expired" style="width: 5%"></div>
                    </div>
                    <div class="bar-percentage">5%</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Sidebar -->
    <div class="right-sidebar mobile-sidebar">
        <!-- Pending Order -->
        <div class="sidebar-card">
            <h3 class="section-title">Pending Order</h3>
            <div class="pending-order">
                <div class="order-header">
                    <div class="order-name">Banana Bread</div>
                    <div class="order-price">₱ 187.00</div>
                </div>
                <div class="order-quantity">10 pcs.</div>
                <div class="order-details">
                    <strong>Order ID:</strong> ID#12323<br>
                    <strong>Customer Name:</strong> Marianne Joy Napisa<br>
                    <strong>Delivery Method:</strong> Pick-Up
                </div>
            </div>
            <button class="order-action-btn">→ Go to Order Management</button>
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