@extends('layouts.establishment')

@section('title', 'Earnings | SavEats')

@section('header', 'Earnings')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Afacad:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/earnings.css') }}">
@endsection

@section('content')
<!-- Total Earnings Cards -->
<div class="earnings-summary-grid">
    <div class="earnings-card gross">
        <div class="earnings-label">Total Gross Earnings</div>
        <div class="earnings-amount">₱{{ number_format($totalGrossEarnings ?? 0, 2) }}</div>
        <div class="earnings-subtitle">Before platform fee</div>
    </div>
    <div class="earnings-card fee">
        <div class="earnings-label">Platform Fees (5%)</div>
        <div class="earnings-amount">₱{{ number_format($totalPlatformFees ?? 0, 2) }}</div>
        <div class="earnings-subtitle">SavEats commission</div>
    </div>
    <div class="earnings-card net">
        <div class="earnings-label">Net Earnings</div>
        <div class="earnings-amount">₱{{ number_format($totalNetEarnings ?? 0, 2) }}</div>
        <div class="earnings-subtitle">Your earnings</div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="earnings-container">
    <!-- Items Sold Section -->
    <div class="items-sold-section">
        <div class="items-sold-header">
            <h2 class="items-sold-title">Items Sold</h2>
            <div class="filter-controls">
                <button class="filter-btn" title="Filter">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3 6h18v2H3V6zm0 5h18v2H3v-2zm0 5h18v2H3v-2z"/>
                    </svg>
                </button>
                <button class="filter-btn" title="Sort">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M7 10l5 5 5-5H7z"/>
                    </svg>
                </button>
            </div>
        </div>
        
        <div class="search-container">
            <input type="text" class="search-input" placeholder="Search...">
        </div>
        
        <div class="date-container">
            <input type="date" class="date-input" placeholder="Select Date">
        </div>
        
        <!-- Orders Table -->
        <table class="orders-table">
            <thead class="table-header">
                <tr>
                    <th>Order ID</th>
                    <th>Item Sold</th>
                    <th>Gross Amount</th>
                    <th>Platform Fee (5%)</th>
                    <th>Net Earnings</th>
                    <th>Date</th>
                    <th>Mode of Payment</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($completedOrders) && count($completedOrders) > 0)
                    @foreach($completedOrders as $order)
                    <tr class="table-row">
                        <td class="order-id" data-label="Order ID">{{ $order['order_number'] ?? 'ID#' . $order['id'] }}</td>
                        <td class="item-name" data-label="Item">{{ $order['product_name'] }}<br><small>{{ $order['quantity'] }} pcs.</small></td>
                        <td class="amount gross-amount" data-label="Gross Amount">₱{{ number_format($order['total_price'], 2) }}</td>
                        <td class="amount fee-amount" data-label="Platform Fee">₱{{ number_format($order['platform_fee'] ?? ($order['total_price'] * 0.05), 2) }}</td>
                        <td class="amount net-amount" data-label="Net Earnings">₱{{ number_format($order['net_earnings'] ?? ($order['total_price'] * 0.95), 2) }}</td>
                        <td data-label="Date">{{ $order['completed_at'] ?? $order['created_at'] }}</td>
                        <td class="payment-mode" data-label="Payment">{{ $order['payment_method'] ?? 'N/A' }}</td>
                    </tr>
                    @endforeach
                @else
                    <tr class="table-row">
                        <td colspan="7" style="text-align: center; padding: 20px; color: #6c757d;">
                            No completed orders yet.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
        
        <!-- Pagination -->
        <div class="pagination">
            <div class="pagination-nav">
                <a href="#">← Previous</a>
                <a href="#">Next →</a>
            </div>
            <div class="pagination-links">
                <a href="#" class="pagination-link active">1</a>
                <span class="pagination-link">...</span>
                <a href="#" class="pagination-link">10</a>
            </div>
        </div>
    </div>
    
    <!-- Daily Earning Trends Section -->
    <div class="trends-section">
        <div class="trends-header">
            <div class="export-dropdown">
                <button class="export-btn" id="exportEarningsBtn">
                    Export into
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M7 10l5 5 5-5H7z"/>
                    </svg>
                </button>
                <div class="export-menu" id="exportEarningsMenu">
                    <a href="{{ route('establishment.earnings.export', 'csv') }}" class="export-option">Export as CSV</a>
                    <a href="{{ route('establishment.earnings.export', 'excel') }}" class="export-option">Export as Excel</a>
                    <a href="{{ route('establishment.earnings.export', 'pdf') }}" class="export-option">Export as PDF</a>
                </div>
            </div>
        </div>
        
        <div class="time-tabs">
            <button class="time-tab active">Daily</button>
            <button class="time-tab">Monthly</button>
            <button class="time-tab">Yearly</button>
        </div>
        
        <h3 class="chart-title">DAILY EARNING TRENDS</h3>
        
        <div class="chart-container">
            <canvas id="earningsChart"></canvas>
        </div>
        
        <div class="chart-legend">
            <div class="legend-color"></div>
            <div class="legend-text">Sales</div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Pass earnings data to JavaScript
    window.earningsData = {
        daily: @json($dailyEarnings ?? []),
        monthly: @json($monthlyEarnings ?? []),
        yearly: @json($yearlyEarnings ?? [])
    };
</script>
<script src="{{ asset('js/earnings.js') }}"></script>
@endsection
