@extends('layouts.foodbank')

@section('title', 'Dashboard | SavEats')

@section('header', 'Dashboard')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Afacad:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/foodbank-dashboard.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
@endsection

@section('content')
<div class="foodbank-dashboard">
    <div class="welcome-section">
        <h2>Hi {{ $user->name ?? session('user_name', 'User') }}!</h2>
        <p>Ready to save food today?</p>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card active">
            <div class="stat-label">Active Requests</div>
            <div class="stat-value">{{ $activeRequests ?? 0 }}</div>
        </div>
        <div class="stat-card partnered">
            <div class="stat-label">Business Partnered</div>
            <div class="stat-value">{{ $businessPartnered ?? 0 }}</div>
        </div>
        <div class="stat-card distributed">
            <div class="stat-label">Donations Received</div>
            <div class="stat-value">{{ $donationsReceived ?? 0 }}</div>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="main-grid">
        <!-- Recent Donations Section -->
        <div class="donations-section">
            <div class="section-header">
                <h3 class="section-title">Recent Donations</h3>
                <a href="{{ route('foodbank.donation-history') }}" class="see-all-link">See All</a>
            </div>
            
            @if(isset($recentDonations) && $recentDonations->count() > 0)
                @foreach($recentDonations->take(2) as $donation)
                <div class="donation-item">
                    <div class="donation-header">
                        <div>
                            <div class="donation-name">{{ $donation['item_name'] }}</div>
                            <div class="donation-quantity">{{ $donation['quantity'] }} {{ $donation['unit'] }}</div>
                        </div>
                        <div class="donation-store">{{ $donation['establishment_name'] }}</div>
                    </div>
                    <div class="donation-time">
                        <svg viewBox="0 0 24 24">
                            <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/>
                        </svg>
                        {{ $donation['collected_at'] ? 'Collected' : 'Donated' }}: {{ $donation['formatted_date'] }}
                    </div>
                    <div class="donation-actions">
                        <button class="btn btn-primary" onclick="viewDonationDetails('{{ $donation['id'] }}')">View Details</button>
                    </div>
                </div>
                @endforeach
            @else
                <div class="donation-item">
                    <div class="donation-header">
                        <div>
                            <div class="donation-name">No donations yet</div>
                            <div class="donation-quantity">Start receiving donations to see them here</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Sidebar - Chart -->
        <div class="chart-section">
            <h3 class="section-title">WEEKLY FOOD RECEIVED</h3>
            
            <div class="chart-container">
                <canvas id="weeklyChart"></canvas>
            </div>

            <div class="chart-legend">
                <div class="legend-item">
                    <div class="legend-color"></div>
                    <span>Number of items received</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Donation Details Modal -->
<div class="modal-overlay" id="donationDetailsModal">
    <div class="modal modal-donation-details">
        <div class="modal-header">
            <h2 id="modalDonationNumber">Donation Details</h2>
            <button class="modal-close" id="closeDonationModal" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body" id="modalDonationBody">
            <!-- Content will be populated by JavaScript -->
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="closeDonationModalBtn">Close</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Pass chart data to JavaScript
    window.weeklyChartData = @json($weeklyData ?? []);
    // Pass recent donations data to JavaScript
    window.recentDonations = @json($recentDonations ?? []);
</script>
<script src="{{ asset('js/foodbank-dashboard.js') }}"></script>
@endsection