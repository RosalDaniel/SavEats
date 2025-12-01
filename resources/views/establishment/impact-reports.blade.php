@extends('layouts.establishment')

@section('title', 'Impact Reports | SavEats')

@section('header', 'Impact Reports')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Afacad:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/impact-reports.css') }}">
@endsection

@section('content')
<div class="impact-reports-container">
    <!-- Impact Summary Cards -->
    <div class="impact-summary">
        <div class="summary-card food-saved">
            <div class="card-content">
                <div class="card-label">Food Saved</div>
                <div class="card-value">{{ $foodSaved ?? 0 }}</div>
            </div>
        </div>
        <div class="summary-card cost-savings">
            <div class="card-content">
                <div class="card-label">Cost Savings</div>
                <div class="card-value">₱{{ number_format($costSavings ?? 0, 2) }}</div>
            </div>
        </div>
        <div class="summary-card food-donated">
            <div class="card-content">
                <div class="card-label">Food Donations Completed</div>
                <div class="card-value">{{ $foodDonated ?? 0 }}</div>
            </div>
        </div>
    </div>

    <!-- Chart and Donation Section -->
    <div class="charts-section">
        <!-- Monthly Earning Trends Chart -->
        <div class="chart-section">
            <div class="chart-header">
                <div class="chart-header-left">
                    <div class="export-dropdown">
                        <button class="export-btn" id="exportBtn">
                            Export info
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M8 11L3 6H6V1H10V6H13L8 11Z" fill="currentColor"/>
                            </svg>
                        </button>
                        <div class="export-menu" id="exportMenu">
                            <button class="export-option" data-format="pdf">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M14 2H6C4.9 2 4 2.9 4 4V20C4 21.1 4.9 22 6 22H18C19.1 22 20 21.1 20 20V8L14 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M14 2V8H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M16 13H8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M16 17H8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M10 9H8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Export as PDF
                            </button>
                            <button class="export-option" data-format="csv">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M14 2H6C4.9 2 4 2.9 4 4V20C4 21.1 4.9 22 6 22H18C19.1 22 20 21.1 20 20V8L14 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M14 2V8H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M16 13H8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M16 17H8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M10 9H8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Export as CSV
                            </button>
                        </div>
                    </div>
                    <div class="chart-tabs">
                        <button class="tab-btn" data-tab="daily">Daily</button>
                        <button class="tab-btn active" data-tab="monthly">Monthly</button>
                        <button class="tab-btn" data-tab="yearly">Yearly</button>
                    </div>
                </div>
                <h2 class="chart-title">MONTHLY EARNING TRENDS</h2>
            </div>
            <div class="chart-container">
                <canvas id="monthlyChart"></canvas>
            </div>
            <div class="chart-legend">
                <div class="legend-item">
                    <div class="legend-color"></div>
                    <span>Number of items saved</span>
                </div>
            </div>
        </div>

        <!-- Foodbanks Ranking of Donated Items Pie Chart -->
        <div class="donation-section">
            <h2 class="donation-title">FOODBANKS RANKING OF DONATED ITEMS</h2>
            <div class="pie-chart-container">
                <canvas id="donationChart"></canvas>
            </div>
            <div class="donation-legend">
                @if(!empty($topDonatedItems))
                    @foreach($topDonatedItems as $index => $item)
                        <div class="legend-item">
                            <div class="legend-dot" style="background-color: {{ ['#ffd700', '#ff6b35', '#84cc16', '#374151', '#ef4444'][$index % 5] }}"></div>
                            <span>{{ strtoupper($item['foodbank_name']) }}</span>
                        </div>
                    @endforeach
                @else
                    <div class="no-data">No donated items yet</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Pass chart data to JavaScript
    window.chartData = {
        daily: @json($dailyData ?? []),
        monthly: @json($monthlyData ?? []),
        yearly: @json($yearlyData ?? [])
    };
    window.donationData = @json($topDonatedItems ?? []);
</script>
<script src="{{ asset('js/impact-reports.js') }}"></script>
@endsection

