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
            <div class="stat-value">5</div>
        </div>
        <div class="stat-card partnered">
            <div class="stat-label">Business Partnered</div>
            <div class="stat-value">12</div>
        </div>
        <div class="stat-card distributed">
            <div class="stat-label">Meals Distributed</div>
            <div class="stat-value">320</div>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="main-grid">
        <!-- Recent Donations Section -->
        <div class="donations-section">
            <div class="section-header">
                <h3 class="section-title">Recent Donations</h3>
                <a href="#" class="see-all-link">See All</a>
            </div>
            
            <div class="donation-item">
                <div class="donation-header">
                    <div>
                        <div class="donation-name">Banana Bread</div>
                        <div class="donation-quantity">10 pcs.</div>
                    </div>
                    <div class="donation-store">Joy Grocery Store</div>
                </div>
                <div class="donation-time">
                    <svg viewBox="0 0 24 24">
                        <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/>
                    </svg>
                    Donated: Monday - 9:30 AM
                </div>
                <div class="donation-actions">
                    <button class="btn btn-primary">Rate</button>
                    <button class="btn btn-secondary">View Store</button>
                </div>
            </div>

            <div class="donation-item">
                <div class="donation-header">
                    <div>
                        <div class="donation-name">Banana Bread</div>
                        <div class="donation-quantity">10 pcs.</div>
                    </div>
                    <div class="donation-store">Joy Grocery Store</div>
                </div>
                <div class="donation-time">
                    <svg viewBox="0 0 24 24">
                        <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/>
                    </svg>
                    Donated: Monday - 9:30 AM
                </div>
                <div class="donation-actions">
                    <button class="btn btn-primary">Rate</button>
                    <button class="btn btn-secondary">View Store</button>
                </div>
            </div>

            <div class="donation-item">
                <div class="donation-header">
                    <div>
                        <div class="donation-name">Banana Bread</div>
                        <div class="donation-quantity">10 pcs.</div>
                    </div>
                    <div class="donation-store">Joy Grocery Store</div>
                </div>
                <div class="donation-time">
                    <svg viewBox="0 0 24 24">
                        <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/>
                    </svg>
                    Donated: Monday - 9:30 AM
                </div>
                <div class="donation-actions">
                    <button class="btn btn-primary">Rate</button>
                    <button class="btn btn-secondary">View Store</button>
                </div>
            </div>
        </div>

        <!-- Right Sidebar - Chart -->
        <div class="chart-section">
            <h3 class="section-title">WEEKLY FOOD RECEIVED</h3>
            
            <div class="chart-container">
                <div class="chart-bars">
                    <div class="chart-bar">
                        <div class="bar" style="height: 15%;" data-value="10"></div>
                        <div class="bar-label">M</div>
                    </div>
                    <div class="chart-bar">
                        <div class="bar" style="height: 55%;" data-value="55"></div>
                        <div class="bar-label">T</div>
                    </div>
                    <div class="chart-bar">
                        <div class="bar" style="height: 100%;" data-value="100"></div>
                        <div class="bar-label">W</div>
                    </div>
                    <div class="chart-bar">
                        <div class="bar" style="height: 20%;" data-value="20"></div>
                        <div class="bar-label">TH</div>
                    </div>
                    <div class="chart-bar">
                        <div class="bar" style="height: 45%;" data-value="45"></div>
                        <div class="bar-label">FRI</div>
                    </div>
                    <div class="chart-bar">
                        <div class="bar" style="height: 70%;" data-value="70"></div>
                        <div class="bar-label">SAT</div>
                    </div>
                    <div class="chart-bar">
                        <div class="bar" style="height: 95%;" data-value="95"></div>
                        <div class="bar-label">SUN</div>
                    </div>
                </div>
            </div>

            <div class="chart-legend">
                <div class="legend-dot"></div>
                <span>Number of items saved</span>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/foodbank-dashboard.js') }}"></script>
@endsection