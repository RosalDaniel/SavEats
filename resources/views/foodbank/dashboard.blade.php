@extends('layouts.foodbank')

@section('title', 'Foodbank Dashboard - SavEats')

@section('header', 'Foodbank Dashboard')

@section('content')
<div class="dashboard-stats">
    <div class="stat-card">
        <div class="stat-icon">
            <svg viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm3.5 6L12 10.5 8.5 8 12 5.5 15.5 8zM12 17.5L8.5 15 12 12.5 15.5 15 12 17.5z"/>
            </svg>
        </div>
        <div class="stat-content">
            <h3>Donations Received</h3>
            <p class="stat-number">156</p>
            <span class="stat-change positive">+23 this week</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <svg viewBox="0 0 24 24">
                <path d="M7 4V2C7 1.45 7.45 1 8 1h8c.55 0 1 .45 1 1v2h5v2h-2v13c0 1.1-.9 2-2 2H6c-1.1 0-2-.9-2-2V6H2V4h5zM9 3v1h6V3H9zM6 6v13h12V6H6z"/>
            </svg>
        </div>
        <div class="stat-content">
            <h3>Current Inventory</h3>
            <p class="stat-number">2.3T</p>
            <span class="stat-change neutral">Available for distribution</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <svg viewBox="0 0 24 24">
                <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zM8.9 6c0-1.71 1.39-3.1 3.1-3.1s3.1 1.39 3.1 3.1v2H8.9V6zM16 16h-3v3h-2v-3H8v-2h3v-3h2v3h3v2z"/>
            </svg>
        </div>
        <div class="stat-content">
            <h3>Food Distributed</h3>
            <p class="stat-number">1.8T</p>
            <span class="stat-change positive">+12% from last month</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <svg viewBox="0 0 24 24">
                <path d="M16 4c0-1.11.89-2 2-2s2 .89 2 2-.89 2-2 2-2-.89-2-2zM4 18v-6h3v2h2v-2h3v6H4zM12.5 11.5c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5.67 1.5 1.5 1.5 1.5-.67 1.5-1.5z"/>
            </svg>
        </div>
        <div class="stat-content">
            <h3>Active Volunteers</h3>
            <p class="stat-number">45</p>
            <span class="stat-change positive">+5 new volunteers</span>
        </div>
    </div>
</div>

<div class="dashboard-content">
    <div class="content-section">
        <h2>Recent Donations</h2>
        <div class="donations-list">
            <div class="donation-item">
                <div class="donation-info">
                    <h4>Fresh Vegetables</h4>
                    <p>From: Green Market Grocery</p>
                    <span class="donation-amount">25kg</span>
                </div>
                <div class="donation-status">
                    <span class="status-badge received">Received</span>
                    <span class="donation-time">2 hours ago</span>
                </div>
            </div>
            <div class="donation-item">
                <div class="donation-info">
                    <h4>Bread & Pastries</h4>
                    <p>From: City Bakery</p>
                    <span class="donation-amount">15kg</span>
                </div>
                <div class="donation-status">
                    <span class="status-badge pending">In Transit</span>
                    <span class="donation-time">Expected in 1 hour</span>
                </div>
            </div>
        </div>
    </div>

    <div class="content-section">
        <h2>Distribution Schedule</h2>
        <div class="schedule-calendar">
            <div class="schedule-item">
                <div class="schedule-time">10:00 AM</div>
                <div class="schedule-details">
                    <h4>Community Distribution</h4>
                    <p>Barangay San Antonio</p>
                    <span class="schedule-volunteers">12 volunteers assigned</span>
                </div>
            </div>
            <div class="schedule-item">
                <div class="schedule-time">2:00 PM</div>
                <div class="schedule-details">
                    <h4>School Feeding Program</h4>
                    <p>Elementary School ABC</p>
                    <span class="schedule-volunteers">8 volunteers assigned</span>
                </div>
            </div>
        </div>
    </div>

    <div class="content-section">
        <h2>Inventory Alerts</h2>
        <div class="alerts-list">
            <div class="alert-item warning">
                <div class="alert-icon">⚠️</div>
                <div class="alert-content">
                    <h4>Low Stock Alert</h4>
                    <p>Canned goods inventory is running low</p>
                </div>
            </div>
            <div class="alert-item info">
                <div class="alert-icon">ℹ️</div>
                <div class="alert-content">
                    <h4>Expiry Notice</h4>
                    <p>5 items expiring within 3 days</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
