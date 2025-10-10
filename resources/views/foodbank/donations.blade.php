@extends('layouts.foodbank')

@section('title', 'Donations | SavEats')

@section('header', 'Donations Management')

@section('content')
<div class="donations-management">
    <div class="donations-header">
        <div class="header-actions">
            <button class="btn-primary">Request Pickup</button>
            <button class="btn-secondary">Export Report</button>
        </div>
        <div class="filter-controls">
            <select class="filter-select">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="in-transit">In Transit</option>
                <option value="received">Received</option>
                <option value="distributed">Distributed</option>
            </select>
            <input type="date" class="filter-date" placeholder="Filter by date">
        </div>
    </div>

    <div class="donations-grid">
        <div class="donation-card">
            <div class="donation-header">
                <h3>Fresh Vegetables</h3>
                <span class="status-badge received">Received</span>
            </div>
            <div class="donation-details">
                <div class="detail-item">
                    <span class="detail-label">From:</span>
                    <span class="detail-value">Green Market Grocery</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Weight:</span>
                    <span class="detail-value">25kg</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Received:</span>
                    <span class="detail-value">Jan 20, 2024</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Expiry:</span>
                    <span class="detail-value">Jan 25, 2024</span>
                </div>
            </div>
            <div class="donation-actions">
                <button class="btn-sm btn-primary">View Details</button>
                <button class="btn-sm btn-success">Mark Distributed</button>
            </div>
        </div>

        <div class="donation-card">
            <div class="donation-header">
                <h3>Bread & Pastries</h3>
                <span class="status-badge in-transit">In Transit</span>
            </div>
            <div class="donation-details">
                <div class="detail-item">
                    <span class="detail-label">From:</span>
                    <span class="detail-value">City Bakery</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Weight:</span>
                    <span class="detail-value">15kg</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Expected:</span>
                    <span class="detail-value">Today, 3:00 PM</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Expiry:</span>
                    <span class="detail-value">Jan 22, 2024</span>
                </div>
            </div>
            <div class="donation-actions">
                <button class="btn-sm btn-primary">Track Delivery</button>
                <button class="btn-sm btn-warning">Contact Donor</button>
            </div>
        </div>

        <div class="donation-card">
            <div class="donation-header">
                <h3>Canned Goods</h3>
                <span class="status-badge pending">Pending</span>
            </div>
            <div class="donation-details">
                <div class="detail-item">
                    <span class="detail-label">From:</span>
                    <span class="detail-value">Supermart Chain</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Weight:</span>
                    <span class="detail-value">50kg</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Scheduled:</span>
                    <span class="detail-value">Jan 22, 2024</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Expiry:</span>
                    <span class="detail-value">Dec 2024</span>
                </div>
            </div>
            <div class="donation-actions">
                <button class="btn-sm btn-primary">Schedule Pickup</button>
                <button class="btn-sm btn-secondary">View Details</button>
            </div>
        </div>
    </div>

    <div class="donations-summary">
        <h2>Monthly Summary</h2>
        <div class="summary-stats">
            <div class="summary-item">
                <h4>Total Donations</h4>
                <p class="summary-value">156</p>
                <span class="summary-change positive">+23 from last month</span>
            </div>
            <div class="summary-item">
                <h4>Weight Received</h4>
                <p class="summary-value">2.3T</p>
                <span class="summary-change positive">+15% from last month</span>
            </div>
            <div class="summary-item">
                <h4>Items Distributed</h4>
                <p class="summary-value">1.8T</p>
                <span class="summary-change positive">78% distribution rate</span>
            </div>
            <div class="summary-item">
                <h4>Active Donors</h4>
                <p class="summary-value">45</p>
                <span class="summary-change positive">+5 new donors</span>
            </div>
        </div>
    </div>
</div>
@endsection
