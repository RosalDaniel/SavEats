@extends('layouts.admin')

@section('title', 'Reports & Analytics - SavEats Admin')

@section('header', 'Reports & Analytics')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin-reports.css') }}">
@endsection

@section('content')
<div class="reports-dashboard">
    <div class="reports-filters">
        <div class="filter-group">
            <label>Date Range:</label>
            <select class="filter-select">
                <option value="7days">Last 7 days</option>
                <option value="30days">Last 30 days</option>
                <option value="90days">Last 90 days</option>
                <option value="custom">Custom Range</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Report Type:</label>
            <select class="filter-select">
                <option value="overview">Overview</option>
                <option value="users">User Analytics</option>
                <option value="food">Food Waste Reduction</option>
                <option value="financial">Financial Report</option>
            </select>
        </div>
        <button class="btn-primary">Generate Report</button>
    </div>

    <div class="reports-grid">
        <div class="report-card">
            <h3>Food Waste Reduction</h3>
            <div class="chart-placeholder">
                <div class="chart-bar" style="height: 60%"></div>
                <div class="chart-bar" style="height: 80%"></div>
                <div class="chart-bar" style="height: 45%"></div>
                <div class="chart-bar" style="height: 90%"></div>
                <div class="chart-bar" style="height: 75%"></div>
            </div>
            <p class="chart-description">2.5 tons of food waste prevented this month</p>
        </div>

        <div class="report-card">
            <h3>User Growth</h3>
            <div class="growth-chart">
                <div class="growth-line"></div>
            </div>
            <p class="chart-description">15% increase in active users</p>
        </div>

        <div class="report-card">
            <h3>Platform Usage</h3>
            <div class="usage-stats">
                <div class="usage-item">
                    <span class="usage-label">Daily Active Users</span>
                    <span class="usage-value">1,234</span>
                </div>
                <div class="usage-item">
                    <span class="usage-label">Food Listings</span>
                    <span class="usage-value">456</span>
                </div>
                <div class="usage-item">
                    <span class="usage-label">Successful Matches</span>
                    <span class="usage-value">89%</span>
                </div>
            </div>
        </div>
    </div>

    <div class="detailed-reports">
        <h2>Detailed Reports</h2>
        <div class="report-table">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Food Rescued (kg)</th>
                        <th>CO2 Saved (kg)</th>
                        <th>Money Saved (₱)</th>
                        <th>Active Users</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Jan 20, 2024</td>
                        <td>125.5</td>
                        <td>89.2</td>
                        <td>₱15,650</td>
                        <td>1,234</td>
                    </tr>
                    <!-- More data rows would be dynamically generated -->
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
