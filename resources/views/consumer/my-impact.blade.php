@extends('layouts.consumer')

@section('title', 'My Impact | SavEats')

@section('header', 'My Impact')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Afacad:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/my-impact.css') }}">
@endsection

@section('content')
<div class="my-impact-container">
    <!-- Impact Summary Cards -->
    <div class="impact-summary">
        <div class="summary-card food-saved">
            <div class="card-content">
                <div class="card-label">Food Saved</div>
                <div class="card-value">{{ $foodSaved ?? 0 }}</div>
            </div>
        </div>
        <div class="summary-card money-saved">
            <div class="card-content">
                <div class="card-label">Money Saved</div>
                <div class="card-value">₱ {{ number_format($moneySaved ?? 0, 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Monthly Food Saved Chart -->
    <div class="chart-section">
        <div class="chart-header">
            <div class="chart-tabs">
                <button class="tab-btn" data-tab="daily">Daily</button>
                <button class="tab-btn active" data-tab="monthly">Monthly</button>
                <button class="tab-btn" data-tab="yearly">Yearly</button>
            </div>
            <h2 class="chart-title">MONTHLY FOOD SAVED</h2>
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
</script>
<script src="{{ asset('js/my-impact.js') }}"></script>
@endsection
