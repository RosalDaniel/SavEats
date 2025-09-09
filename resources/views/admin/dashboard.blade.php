@extends('layouts.admin')

@section('title', 'Admin Dashboard - SavEats')

@section('header', 'Admin Dashboard')

@section('content')
<div class="dashboard-stats">
    <div class="stat-card">
        <div class="stat-icon">
            <svg viewBox="0 0 24 24">
                <path d="M16 7c0-2.76-2.24-5-5-5s-5 2.24-5 5 2.24 5 5 5 5-2.24 5-5zM12 14c-3.33 0-10 1.67-10 5v3h20v-3c0-3.33-6.67-5-10-5z"/>
            </svg>
        </div>
        <div class="stat-content">
            <h3>Total Users</h3>
            <p class="stat-number">1,234</p>
            <span class="stat-change positive">+12% from last month</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <svg viewBox="0 0 24 24">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
        </div>
        <div class="stat-content">
            <h3>Active Establishments</h3>
            <p class="stat-number">89</p>
            <span class="stat-change positive">+5% from last month</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <svg viewBox="0 0 24 24">
                <path d="M11 9H9V2H7v7H5V2H3v7c0 2.12 1.66 3.84 3.75 3.97V22h2.5v-9.03C11.34 12.84 13 11.12 13 9V2h-2v7zm5-3v8h2.5v8H21V2c-2.76 0-5 2.24-5 4z"/>
            </svg>
        </div>
        <div class="stat-content">
            <h3>Food Banks</h3>
            <p class="stat-number">23</p>
            <span class="stat-change positive">+2 new this month</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <svg viewBox="0 0 24 24">
                <path d="M16 6l2.29 2.29-4.88 4.88-4-4L2 16.59 3.41 18l6-6 4 4 6.3-6.29L22 12V6z"/>
            </svg>
        </div>
        <div class="stat-content">
            <h3>Food Waste Reduced</h3>
            <p class="stat-number">2.5T</p>
            <span class="stat-change positive">+15% from last month</span>
        </div>
    </div>
</div>

<div class="dashboard-content">
    <div class="content-section">
        <h2>Recent Activity</h2>
        <div class="activity-list">
            <div class="activity-item">
                <div class="activity-icon">📊</div>
                <div class="activity-content">
                    <h4>New establishment registered</h4>
                    <p>Green Grocers joined the platform</p>
                    <span class="activity-time">2 hours ago</span>
                </div>
            </div>
            <div class="activity-item">
                <div class="activity-icon">🏪</div>
                <div class="activity-content">
                    <h4>Food bank partnership</h4>
                    <p>City Food Bank started accepting donations</p>
                    <span class="activity-time">5 hours ago</span>
                </div>
            </div>
        </div>
    </div>

    <div class="content-section">
        <h2>System Overview</h2>
        <div class="overview-grid">
            <div class="overview-item">
                <h4>Platform Health</h4>
                <div class="health-indicator good">Excellent</div>
            </div>
            <div class="overview-item">
                <h4>User Engagement</h4>
                <div class="engagement-bar">
                    <div class="engagement-fill" style="width: 85%"></div>
                </div>
                <span>85% active users</span>
            </div>
        </div>
    </div>
</div>
@endsection
