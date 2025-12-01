@extends('layouts.admin')

@section('title', 'Food Bank Details - Admin Dashboard')

@section('header', 'Food Bank Details')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin-foodbanks.css') }}">
@endsection

@section('content')
<div class="foodbank-details-page">
    <div class="details-header">
        <a href="{{ route('admin.foodbanks') }}" class="btn-back">← Back to Food Banks</a>
        <h1>{{ $foodbank->organization_name }}</h1>
    </div>

    <!-- Foodbank Information -->
    <div class="details-section">
        <h2>Food Bank Information</h2>
        <div class="info-grid">
            <div class="info-item">
                <label>Organization Name:</label>
                <span>{{ $foodbank->organization_name }}</span>
            </div>
            <div class="info-item">
                <label>Contact Person:</label>
                <span>{{ $foodbank->contact_person }}</span>
            </div>
            <div class="info-item">
                <label>Email:</label>
                <span>{{ $foodbank->email }}</span>
            </div>
            <div class="info-item">
                <label>Phone:</label>
                <span>{{ $foodbank->phone_no ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <label>Address:</label>
                <span>{{ $foodbank->address ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <label>Registration Number:</label>
                <span>{{ $foodbank->registration_number ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <label>Status:</label>
                <span class="status-badge status-{{ $foodbank->status ?? 'active' }}">{{ ucfirst($foodbank->status ?? 'active') }}</span>
            </div>
            <div class="info-item">
                <label>Verified:</label>
                @if($foodbank->verified)
                <span class="verified-badge verified-yes">Verified</span>
                @else
                <span class="verified-badge verified-no">Not Verified</span>
                @endif
            </div>
            <div class="info-item">
                <label>Date Registered:</label>
                <span>{{ $foodbank->created_at->format('M d, Y') }}</span>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="stats-section">
        <h2>Activity Statistics</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Requests</h3>
                <p class="stat-number">{{ $foodbank->total_requests_count ?? 0 }}</p>
            </div>
            <div class="stat-card">
                <h3>Pending Requests</h3>
                <p class="stat-number">{{ $foodbank->pending_requests_count ?? 0 }}</p>
            </div>
            <div class="stat-card">
                <h3>Accepted Requests</h3>
                <p class="stat-number">{{ $foodbank->accepted_requests_count ?? 0 }}</p>
            </div>
            <div class="stat-card">
                <h3>Completed Requests</h3>
                <p class="stat-number">{{ $foodbank->completed_requests_count ?? 0 }}</p>
            </div>
            <div class="stat-card">
                <h3>Total Donations</h3>
                <p class="stat-number">{{ $foodbank->total_donations_count ?? 0 }}</p>
            </div>
        </div>
    </div>

    <!-- Donation Requests -->
    <div class="details-section">
        <h2>Recent Donation Requests</h2>
        @if($donationRequests->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Status</th>
                    <th>Establishment</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($donationRequests as $request)
                <tr>
                    <td>{{ $request->item_name }}</td>
                    <td>{{ $request->quantity }} {{ $request->unit ?? '' }}</td>
                    <td><span class="status-badge status-{{ $request->status }}">{{ ucfirst($request->status) }}</span></td>
                    <td>{{ $request->establishment->business_name ?? 'N/A' }}</td>
                    <td>{{ $request->created_at->format('M d, Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="no-data">No donation requests found.</p>
        @endif
    </div>

    <!-- System Logs -->
    <div class="details-section">
        <h2>System Logs</h2>
        @if($systemLogs->count() > 0)
        <div class="logs-list">
            @foreach($systemLogs as $log)
            <div class="log-item">
                <div class="log-header">
                    <span class="log-action">{{ $log->action ?? 'N/A' }}</span>
                    <span class="log-date">{{ $log->created_at->format('M d, Y H:i') }}</span>
                </div>
                <div class="log-description">{{ $log->description }}</div>
            </div>
            @endforeach
        </div>
        @else
        <p class="no-data">No system logs found.</p>
        @endif
    </div>
</div>

<style>
.foodbank-details-page {
    padding: 2rem;
    max-width: 1400px;
    margin: 0 auto;
}

.details-header {
    margin-bottom: 2rem;
}

.btn-back {
    display: inline-block;
    margin-bottom: 1rem;
    color: #4a7c59;
    text-decoration: none;
}

.details-section {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.details-section h2 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    color: #1f2937;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1rem;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.info-item label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #6b7280;
}

.info-item span {
    font-size: 0.875rem;
    color: #1f2937;
}

.stats-section {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th,
.data-table td {
    padding: 0.75rem;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}

.data-table th {
    background: #f9fafb;
    font-weight: 600;
    font-size: 0.875rem;
    color: #6b7280;
}

.logs-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.log-item {
    padding: 1rem;
    background: #f9fafb;
    border-radius: 8px;
    border-left: 4px solid #4a7c59;
}

.log-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.log-action {
    font-weight: 600;
    color: #1f2937;
}

.log-date {
    font-size: 0.75rem;
    color: #6b7280;
}

.log-description {
    font-size: 0.875rem;
    color: #6b7280;
}

.no-data {
    text-align: center;
    padding: 2rem;
    color: #6b7280;
}
</style>
@endsection

