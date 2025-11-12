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
            <canvas id="monthlyChart" width="800" height="400"></canvas>
        </div>
        <div class="chart-legend">
            <div class="legend-item">
                <div class="legend-color"></div>
                <span>Number of items saved</span>
            </div>
        </div>
    </div>

    <!-- Badge Progress Section -->
    <div class="badge-section">
        <h2 class="badge-title">Badge Progress</h2>
        <div class="badges-container">
            <div class="badge completed">
                <div class="badge-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M8.1,13.34L3.91,9.16C3.5,8.75 2.87,8.75 2.46,9.16C2.05,9.57 2.05,10.2 2.46,10.61L7.39,15.54C7.8,15.95 8.43,15.95 8.84,15.54L21.54,2.84C21.95,2.43 21.95,1.8 21.54,1.39C21.13,0.98 20.5,0.98 20.09,1.39L8.1,13.34Z"/>
                    </svg>
                </div>
                <div class="badge-content">
                    <div class="badge-percentage">100%</div>
                    <div class="badge-name">Meal Rescuer</div>
                    <div class="badge-description">Saved 5 meals</div>
                    <div class="badge-status completed">Completed</div>
                </div>
            </div>

            <div class="badge completed">
                <div class="badge-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4M12,6A6,6 0 0,0 6,12A6,6 0 0,0 12,18A6,6 0 0,0 18,12A6,6 0 0,0 12,6M12,8A4,4 0 0,1 16,12A4,4 0 0,1 12,16A4,4 0 0,1 8,12A4,4 0 0,1 12,8Z"/>
                    </svg>
                </div>
                <div class="badge-content">
                    <div class="badge-percentage">90%</div>
                    <div class="badge-name">Food Hero</div>
                    <div class="badge-description">Saved 10 meals</div>
                    <div class="badge-status completed">Completed</div>
                </div>
            </div>

            <div class="badge in-progress">
                <div class="badge-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4M12,6A6,6 0 0,0 6,12A6,6 0 0,0 12,18A6,6 0 0,0 18,12A6,6 0 0,0 12,6M12,8A4,4 0 0,1 16,12A4,4 0 0,1 8,12A4,4 0 0,1 12,8Z"/>
                    </svg>
                </div>
                <div class="badge-content">
                    <div class="badge-percentage">50%</div>
                    <div class="badge-name">Eco Starter</div>
                    <div class="badge-description">Saved 20 meals</div>
                </div>
            </div>

            <div class="badge locked">
                <div class="badge-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12,17A2,2 0 0,0 14,15C14,13.89 13.1,13 12,13A2,2 0 0,0 10,15A2,2 0 0,0 12,17M18,8A2,2 0 0,1 20,10V20A2,2 0 0,1 18,22H6A2,2 0 0,1 4,20V10C4,8.89 4.9,8 6,8H7V6A5,5 0 0,1 12,1A5,5 0 0,1 17,6V8H18M12,3A3,3 0 0,0 9,6V8H15V6A3,3 0 0,0 12,3Z"/>
                    </svg>
                </div>
                <div class="badge-content">
                    <div class="badge-percentage">5%</div>
                    <div class="badge-name">Super Saver</div>
                    <div class="badge-description">Saved 30 meals</div>
                </div>
            </div>

            <div class="badge locked">
                <div class="badge-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17,8C8,10 5.9,16.17 3.82,21.34L5.71,22L6.66,19.7C7.14,19.87 7.64,20 8,20C19,20 22,3 22,3C21,5 14,5.25 9,6.25C4,7.25 2,11.5 2,13.5C2,15.5 3.75,17.25 6,17.25C7.5,17.25 9,16.5 9,16.5C9,16.5 8.5,18 7,18C5.5,18 4,16.5 4,15C4,13.5 5.5,12 7,12C8.5,12 10,13.5 10,15C10,16.5 8.5,18 7,18Z"/>
                    </svg>
                </div>
                <div class="badge-content">
                    <div class="badge-percentage">0%</div>
                    <div class="badge-name">Sustainability Champ</div>
                    <div class="badge-description">Saved 40 meals</div>
                </div>
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
