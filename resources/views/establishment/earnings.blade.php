@extends('layouts.establishment')

@section('title', 'Earnings | SavEats')

@section('header', 'Earnings')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Afacad:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/earnings.css') }}">
@endsection

@section('content')
<!-- Total Earnings Card -->
<div class="total-earnings-card">
    <div class="total-earnings-label">Total Earnings</div>
    <div class="total-earnings-amount">₱500</div>
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
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Mode of</th>
                </tr>
            </thead>
            <tbody>
                <tr class="table-row">
                    <td class="order-id">ID#123</td>
                    <td class="item-name">Joy Bread<br><small>12 pcs.</small></td>
                    <td class="amount">₱25.00</td>
                    <td>May 1, 2025</td>
                    <td class="payment-mode">Credit Card</td>
                </tr>
                <tr class="table-row">
                    <td class="order-id">ID#123</td>
                    <td class="item-name">Joy Bread<br><small>12 pcs.</small></td>
                    <td class="amount">₱25.00</td>
                    <td>May 1, 2025</td>
                    <td class="payment-mode">E-Wallet</td>
                </tr>
                <tr class="table-row">
                    <td class="order-id">ID#123</td>
                    <td class="item-name">Joy Bread<br><small>12 pcs.</small></td>
                    <td class="amount">₱25.00</td>
                    <td>May 1, 2025</td>
                    <td class="payment-mode">Cash on Hand</td>
                </tr>
                <tr class="table-row">
                    <td class="order-id">ID#123</td>
                    <td class="item-name">Joy Bread<br><small>12 pcs.</small></td>
                    <td class="amount">₱25.00</td>
                    <td>May 1, 2025</td>
                    <td class="payment-mode">E-Wallet</td>
                </tr>
                <tr class="table-row">
                    <td class="order-id">ID#123</td>
                    <td class="item-name">Joy Bread<br><small>12 pcs.</small></td>
                    <td class="amount">₱25.00</td>
                    <td>May 1, 2025</td>
                    <td class="payment-mode">Credit Card</td>
                </tr>
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
            <button class="export-btn">
                Export into
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M7 10l5 5 5-5H7z"/>
                </svg>
            </button>
        </div>
        
        <div class="time-tabs">
            <button class="time-tab active">Daily</button>
            <button class="time-tab">Monthly</button>
            <button class="time-tab">Yearly</button>
        </div>
        
        <h3 class="chart-title">DAILY EARNING TRENDS</h3>
        
        <div class="chart-container">
            <div class="y-axis">
                <div class="y-label">100</div>
                <div class="y-label">80</div>
                <div class="y-label">60</div>
                <div class="y-label">40</div>
                <div class="y-label">20</div>
                <div class="y-label">0</div>
            </div>
            
            <div class="chart-bars">
                <div class="chart-bar" style="height: 95%;">
                    <div class="bar-label">M</div>
                </div>
                <div class="chart-bar" style="height: 65%;">
                    <div class="bar-label">T</div>
                </div>
                <div class="chart-bar" style="height: 45%;">
                    <div class="bar-label">W</div>
                </div>
                <div class="chart-bar" style="height: 28%;">
                    <div class="bar-label">TH</div>
                </div>
                <div class="chart-bar" style="height: 18%;">
                    <div class="bar-label">FRI</div>
                </div>
                <div class="chart-bar" style="height: 43%;">
                    <div class="bar-label">SAT</div>
                </div>
                <div class="chart-bar" style="height: 98%;">
                    <div class="bar-label">SUN</div>
                </div>
            </div>
        </div>
        
        <div class="chart-legend">
            <div class="legend-color"></div>
            <div class="legend-text">Sales</div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/earnings.js') }}"></script>
@endsection
