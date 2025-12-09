@extends('layouts.admin')

@section('title', 'SavEats Company Earnings - Admin')

@section('header', 'SavEats Company Earnings')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Afacad:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/admin-earnings.css') }}">
@endsection

@section('content')
<!-- Earnings Summary Cards -->
<div class="earnings-summary-grid">
    <div class="earnings-card gross">
        <div class="earnings-label">Total Gross Revenue</div>
        <div class="earnings-amount">P{{ number_format($totalGrossRevenue ?? 0, 2) }}</div>
        <div class="earnings-subtitle">All completed transactions</div>
    </div>
    <div class="earnings-card fee">
        <div class="earnings-label">Total Platform Fees (5%)</div>
        <div class="earnings-amount">P{{ number_format($totalPlatformFees ?? 0, 2) }}</div>
        <div class="earnings-subtitle">SavEats earnings</div>
    </div>
    <div class="earnings-card net">
        <div class="earnings-label">Total Net to Establishments</div>
        <div class="earnings-amount">P{{ number_format($totalNetEarnings ?? 0, 2) }}</div>
        <div class="earnings-subtitle">Establishment earnings</div>
    </div>
</div>

<!-- Filters Section -->
<div class="filters-section">
    <div class="filters-header">
        <h3>Filters</h3>
        <a href="{{ route('admin.earnings') }}" class="btn-clear-filters">Clear All</a>
    </div>
    <form method="GET" action="{{ route('admin.earnings') }}" class="filters-grid">
        <div class="filter-group">
            <label for="establishmentFilter">Establishment</label>
            <select name="establishment_id" id="establishmentFilter" class="filter-select">
                <option value="">All Establishments</option>
                @foreach($establishments as $establishment)
                    <option value="{{ $establishment['id'] }}" {{ request('establishment_id') == $establishment['id'] ? 'selected' : '' }}>
                        {{ $establishment['name'] }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="filter-group">
            <label for="dateFromFilter">Date From</label>
            <input type="date" name="date_from" id="dateFromFilter" class="filter-input" value="{{ request('date_from') }}">
        </div>
        <div class="filter-group">
            <label for="dateToFilter">Date To</label>
            <input type="date" name="date_to" id="dateToFilter" class="filter-input" value="{{ request('date_to') }}">
        </div>
        <div class="filter-group filter-actions">
            <button type="submit" class="btn-filter">Apply Filters</button>
        </div>
    </form>
</div>

<!-- Export Section -->
<div class="export-section">
    <div class="export-header">
        <h3>Export</h3>
    </div>
    <div class="export-buttons">
        <a href="{{ route('admin.earnings.export', array_merge(['type' => 'csv'], request()->only(['establishment_id', 'date_from', 'date_to']))) }}" class="btn-export">
            <svg viewBox="0 0 24 24">
                <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
            </svg>
            Export as CSV
        </a>
        <a href="{{ route('admin.earnings.export', array_merge(['type' => 'excel'], request()->only(['establishment_id', 'date_from', 'date_to']))) }}" class="btn-export">
            <svg viewBox="0 0 24 24">
                <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
            </svg>
            Export as Excel
        </a>
        <a href="{{ route('admin.earnings.export', array_merge(['type' => 'pdf'], request()->only(['establishment_id', 'date_from', 'date_to']))) }}" class="btn-export">
            <svg viewBox="0 0 24 24">
                <path d="M20 2H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-8.5 7.5c0 .83-.67 1.5-1.5 1.5H9v2H7.5V7H10c.83 0 1.5.67 1.5 1.5v1zm5 2c0 .83-.67 1.5-1.5 1.5h-2.5V7H15c.83 0 1.5.67 1.5 1.5v3zm4-3H19v1h1.5V11H19v2h-1.5V7h3v1.5zM9 9.5h1v-1H9v1zm5 2h1v-1h-1v1zm5-2h1v-1h-1v1z"/>
            </svg>
            Export as PDF
        </a>
    </div>
</div>

<!-- Main Content Grid -->
<div class="earnings-container">
    <!-- Transactions Table -->
    <div class="transactions-section">
        <div class="section-header">
            <h3 class="section-title">Platform Fee Transactions</h3>
            <span class="result-count">
                Showing {{ $orders->firstItem() ?? 0 }} to {{ $orders->lastItem() ?? 0 }} of {{ $orders->total() }} Transaction{{ $orders->total() !== 1 ? 's' : '' }}
            </span>
        </div>
        
        <div class="table-container">
            <table class="transactions-table">
                <thead>
                    <tr>
                        <th>Order Number</th>
                        <th>Establishment</th>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Gross Amount</th>
                        <th>Platform Fee (5%)</th>
                        <th>Net to Establishment</th>
                        <th>Date Completed</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td>{{ $order['order_number'] }}</td>
                        <td>{{ $order['establishment_name'] }}</td>
                        <td>{{ $order['item_name'] }}</td>
                        <td>{{ $order['quantity'] }}</td>
                        <td class="gross-amount">P{{ number_format($order['total_price'], 2) }}</td>
                        <td class="fee-amount">P{{ number_format($order['platform_fee'], 2) }}</td>
                        <td class="net-amount">P{{ number_format($order['net_earnings'], 2) }}</td>
                        <td>{{ $order['completed_at_display'] }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="no-data">No transactions found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($orders->hasPages())
        <div class="pagination-container">
            <div class="pagination-info">
                Showing {{ $orders->firstItem() ?? 0 }} to {{ $orders->lastItem() ?? 0 }} of {{ $orders->total() }} results
            </div>
            <div class="pagination-links">
                {{ $orders->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
    
    <!-- Earnings Chart Section -->
    <div class="chart-section">
        <div class="chart-header">
            <h3 class="chart-title">PLATFORM FEES EARNINGS</h3>
            <div class="time-tabs">
                <button class="time-tab active" data-tab="daily">Daily</button>
                <button class="time-tab" data-tab="monthly">Monthly</button>
            </div>
        </div>
        
        <div class="chart-container">
            <canvas id="earningsChart"></canvas>
        </div>
        
        <div class="chart-legend">
            <div class="legend-item">
                <div class="legend-color"></div>
                <span>Platform Fees (5%)</span>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    window.earningsData = {
        daily: @json($dailyEarnings ?? []),
        monthly: @json($monthlyEarnings ?? [])
    };
</script>
<script src="{{ asset('js/admin-earnings.js') }}"></script>
@endsection

