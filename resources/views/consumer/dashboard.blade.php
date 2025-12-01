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
            ['value' => '₱' . number_format($totalSavings ?? 0, 2), 'label' => 'Total Savings', 'type' => 'money'],
            ['value' => $ordersCount ?? 0, 'label' => 'Orders', 'type' => 'orders'],
            ['value' => ($foodRescued ?? 0) . ' pcs.', 'label' => 'Food Rescued', 'type' => 'food'],
            ['value' => $ratedOrdersCount ?? 0, 'label' => 'Rated Orders', 'type' => 'reviews'],
        ]
    ])

    <!-- Main Content Grid -->
    <div class="main-content-grid">
        <!-- Best Deals Section -->
        <div class="deals-section">
            <h3 class="section-title">BEST DEALS</h3>

            @if(isset($bestDeals) && $bestDeals->count() > 0)
                @foreach($bestDeals as $deal)
                <div class="deal-item">
                    <div class="deal-main">
                        <div class="deal-image">
                            @if($deal['image_url'])
                                <img src="{{ $deal['image_url'] }}" alt="{{ $deal['name'] }}">
                            @endif
                        </div>
                        <div class="deal-content">
                            <div class="deal-left">
                                <div class="deal-name">{{ $deal['name'] }}</div>
                                <div class="deal-quantity">{{ $deal['quantity'] }} pcs.</div>
                                <div class="discount-badge">{{ $deal['discount_percentage'] }}% off</div>
                            </div>
                            <div class="deal-right">
                                <div class="deal-price">₱ {{ number_format($deal['discounted_price'], 2) }}</div>
                                <div class="deal-original-price">₱ {{ number_format($deal['original_price'], 2) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="deal-actions">
                        <a href="{{ route('order.confirmation') }}?id={{ $deal['id'] }}&quantity=1" class="btn btn-primary">Buy Now</a>
                        <a href="{{ route('food.detail', $deal['id']) }}" class="btn btn-secondary">View Details</a>
                    </div>
                </div>
                @endforeach
            @else
                <div class="no-deals">
                    <p>No deals available at the moment. Check back later!</p>
                </div>
            @endif

            <a href="{{ route('food.listing') }}" class="view-all-btn">→ Go to Food Listings</a>
        </div>

        <!-- Sidebar Cards -->
        <div class="sidebar-cards">
            <!-- Upcoming Order -->
            <div class="sidebar-card">
                <h3 class="section-title">Upcoming Order</h3>
                @if($upcomingOrder && $upcomingOrder->foodListing)
                    <div class="order-item">
                        <div class="order-header">
                            <div class="order-name">{{ $upcomingOrder->foodListing->name }}</div>
                            <div class="order-price">₱ {{ number_format($upcomingOrder->total_price, 2) }}</div>
                        </div>
                        <div class="order-quantity">{{ $upcomingOrder->quantity }} pcs.</div>
                        <div class="order-details">
                            <strong>Order ID:</strong> {{ $upcomingOrder->order_number }}<br>
                            <strong>Store:</strong> {{ $upcomingOrder->establishment->business_name ?? 'N/A' }}<br>
                            <strong>Delivery Method:</strong> {{ ucfirst($upcomingOrder->delivery_method) }}<br>
                            <strong>Status:</strong> <span class="order-status status-{{ $upcomingOrder->status }}">{{ ucfirst($upcomingOrder->status) }}</span>
                        </div>
                    </div>
                    <a href="{{ route('my.orders') }}" class="order-action-btn">→ Go to Order History</a>
                @else
                    <div class="no-upcoming-order">
                        <p>No upcoming orders</p>
                        <a href="{{ route('food.listing') }}" class="order-action-btn">Browse Food Listings</a>
                    </div>
                @endif
            </div>

            <!-- Badges Collected -->
            <div class="sidebar-card">
                <h3 class="section-title">Badges Collected</h3>
                @if($badgeData)
                    <div class="badge-container">
                        <div class="badge-icon {{ $badgeData['status'] === 'completed' ? 'completed' : '' }}">
                            <svg viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </div>
                        <div class="badge-progress">{{ $badgeData['progress'] }}%</div>
                        <div class="badge-title">
                            @if($badgeData['current'])
                                {{ $badgeData['current']['name'] }}
                            @else
                                {{ $badgeData['next']['name'] }}
                            @endif
                        </div>
                        <div class="badge-subtitle">
                            @if($badgeData['status'] === 'completed')
                                {{ $badgeData['current']['description'] }}
                            @else
                                {{ $badgeData['meals_saved'] }} / {{ $badgeData['next_requirement'] }} meals saved
                            @endif
                        </div>
                        <div class="badge-status {{ $badgeData['status'] === 'completed' ? 'completed' : 'in-progress' }}">
                            {{ $badgeData['status'] === 'completed' ? 'Completed' : 'In Progress' }}
                        </div>
                    </div>
                @else
                    <div class="no-badge-data">
                        <p>Start ordering to earn badges!</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
