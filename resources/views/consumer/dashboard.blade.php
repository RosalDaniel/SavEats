<!-- resources/views/consumer/dashboard.blade.php -->
 
@extends('layouts.consumer')

@section('title', 'SavEats')

@section('header', 'Dashboard')

@section('content')
    <div class="welcome-section">
        <h2>Hi {{ $user->name ?? session('user_name', 'User') }}!</h2>
        <p>Ready to save food today?</p>
    </div>

    @include('components.stat-grid', [
        'stats' => [
            ['value' => '₱1,250', 'label' => 'Total Savings', 'type' => 'money'],
            ['value' => '25', 'label' => 'Orders', 'type' => 'orders'],
            ['value' => '15kg', 'label' => 'Food Rescued', 'type' => 'food'],
            ['value' => '4.8⭐', 'label' => 'Rating', 'type' => 'reviews'],
        ]
    ])

    <!-- Flash Sale Banner -->
    <div class="flash-sale">
        <div class="flash-sale-text">Flash Sale: Everything 30% off until midnight!</div>
    </div>

    <!-- Main Content Grid -->
    <div class="main-content-grid">
        <!-- Best Deals Section -->
        <div class="deals-section">
            <h3 class="section-title">BEST DEALS</h3>

            <div class="deal-item">
                <div class="deal-image"></div>
                <div class="deal-info">
                    <div class="deal-name">Joy Bread</div>
                    <div class="deal-quantity">10 pcs.</div>
                    <div class="deal-price-row">
                        <div class="deal-price">₱ 25.00</div>
                        <div class="discount-badge">30% off</div>
                    </div>
                    <div class="deal-actions">
                        <button class="btn btn-primary">Buy Now</button>
                        <button class="btn btn-secondary">View Details</button>
                    </div>
                </div>
            </div>

            <div class="deal-item">
                <div class="deal-image"></div>
                <div class="deal-info">
                    <div class="deal-name">Joy Bread</div>
                    <div class="deal-quantity">10 pcs.</div>
                    <div class="deal-price-row">
                        <div class="deal-price">₱ 25.00</div>
                        <div class="discount-badge">30% off</div>
                    </div>
                    <div class="deal-actions">
                        <button class="btn btn-primary">Buy Now</button>
                        <button class="btn btn-secondary">View Details</button>
                    </div>
                </div>
            </div>

            <a href="{{ route('food.listing') }}" class="view-all-btn">→ Go to Food Listings</a>
        </div>

        <!-- Sidebar Cards -->
        <div class="sidebar-cards">
            <!-- Upcoming Order -->
            <div class="sidebar-card">
                <h3 class="section-title">Upcoming Order</h3>
                <div class="order-item">
                    <div class="order-header">
                        <div class="order-name">Banana Bread</div>
                        <div class="order-price">₱ 187.00</div>
                    </div>
                    <div class="order-quantity">10 pcs.</div>
                    <div class="order-details">
                        <strong>Order ID:</strong> ID#12323<br>
                        <strong>Store:</strong> Joy Share Grocery<br>
                        <strong>Delivery Method:</strong> Pick-Up
                    </div>
                </div>
                <button class="order-action-btn">→ Go to Order History</button>
            </div>

            <!-- Badges Collected -->
            <div class="sidebar-card">
                <h3 class="section-title">Badges Collected</h3>
                <div class="badge-container">
                    <div class="badge-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                    <div class="badge-progress">100%</div>
                    <div class="badge-title">Meal Rescuer</div>
                    <div class="badge-subtitle">Saved 5 meals</div>
                    <div class="badge-status">Completed</div>
                </div>
            </div>
        </div>
    </div>
@endsection
