@extends('layouts.establishment')

@section('title', 'Establishment Dashboard - SavEats')

@section('header', 'Dashboard')

@section('content')
<div class="dashboard-stats">
    <div class="stat-card">
        <div class="stat-icon">
            <svg viewBox="0 0 24 24">
                <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/>
            </svg>
        </div>
        <div class="stat-content">
            <h3>Active Listings</h3>
            <p class="stat-number">12</p>
            <span class="stat-change positive">+3 this week</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <svg viewBox="0 0 24 24">
                <path d="M7 4V2C7 1.45 7.45 1 8 1h8c.55 0 1 .45 1 1v2h5v2h-2v13c0 1.1-.9 2-2 2H6c-1.1 0-2-.9-2-2V6H2V4h5zM9 3v1h6V3H9zM6 6v13h12V6H6z"/>
            </svg>
        </div>
        <div class="stat-content">
            <h3>Orders Received</h3>
            <p class="stat-number">28</p>
            <span class="stat-change positive">+8 today</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <svg viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm3.5 6L12 10.5 8.5 8 12 5.5 15.5 8zM12 17.5L8.5 15 12 12.5 15.5 15 12 17.5z"/>
            </svg>
        </div>
        <div class="stat-content">
            <h3>Donations Made</h3>
            <p class="stat-number">45kg</p>
            <span class="stat-change positive">To local food banks</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <svg viewBox="0 0 24 24">
                <path d="M16 6l2.29 2.29-4.88 4.88-4-4L2 16.59 3.41 18l6-6 4 4 6.3-6.29L22 12V6z"/>
            </svg>
        </div>
        <div class="stat-content">
            <h3>Revenue This Month</h3>
            <p class="stat-number">₱15,450</p>
            <span class="stat-change positive">+22% from last month</span>
        </div>
    </div>
</div>

<div class="dashboard-content">
    <div class="content-section">
        <h2>Recent Orders</h2>
        <div class="orders-list">
            <div class="order-item">
                <div class="order-info">
                    <h4>Mixed Vegetables Bundle</h4>
                    <p>Customer: Maria Santos</p>
                    <span class="order-amount">₱250</span>
                </div>
                <div class="order-status">
                    <span class="status-badge ready">Ready for Pickup</span>
                    <span class="order-time">2 hours ago</span>
                </div>
            </div>
            <div class="order-item">
                <div class="order-info">
                    <h4>Fresh Bread (5 loaves)</h4>
                    <p>Customer: John Cruz</p>
                    <span class="order-amount">₱150</span>
                </div>
                <div class="order-status">
                    <span class="status-badge completed">Completed</span>
                    <span class="order-time">5 hours ago</span>
                </div>
            </div>
        </div>
        <button class="btn-secondary">View All Orders</button>
    </div>

    <div class="content-section">
        <h2>Quick Actions</h2>
        <div class="quick-actions">
            <button class="action-card">
                <div class="action-icon">📦</div>
                <h4>Add New Listing</h4>
                <p>List surplus food items</p>
            </button>
            <button class="action-card">
                <div class="action-icon">🏪</div>
                <div>
                    <h4>Donate to Food Bank</h4>
                    <p>Schedule food donation</p>
                </div>
            </button>
            <button class="action-card">
                <div class="action-icon">📊</div>
                <div>
                    <h4>View Analytics</h4>
                    <p>Check performance metrics</p>
                </div>
            </button>
        </div>
    </div>

    <div class="content-section">
        <h2>Environmental Impact</h2>
        <div class="impact-grid">
            <div class="impact-item">
                <div class="impact-icon">🌱</div>
                <div class="impact-details">
                    <h4>Food Waste Reduced</h4>
                    <p class="impact-value">125kg</p>
                    <span class="impact-period">This month</span>
                </div>
            </div>
            <div class="impact-item">
                <div class="impact-icon">🌍</div>
                <div class="impact-details">
                    <h4>CO2 Emissions Saved</h4>
                    <p class="impact-value">89kg</p>
                    <span class="impact-period">Equivalent</span>
                </div>
            </div>
            <div class="impact-item">
                <div class="impact-icon">💰</div>
                <div class="impact-details">
                    <h4>Money Saved</h4>
                    <p class="impact-value">₱8,750</p>
                    <span class="impact-period">From reduced waste</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
