@extends('layouts.admin')

@section('title', 'Food Bank Donations - Admin Dashboard')

@section('header', 'Food Bank Donations')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin-foodbanks.css') }}">
@endsection

@section('content')
<div class="donation-hub-page">
    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Requests</h3>
            <p class="stat-number">{{ number_format($stats['total_requests'] ?? 0) }}</p>
        </div>
        <div class="stat-card">
            <h3>Pending Requests</h3>
            <p class="stat-number">{{ number_format($stats['pending_requests'] ?? 0) }}</p>
        </div>
        <div class="stat-card">
            <h3>Accepted Requests</h3>
            <p class="stat-number">{{ number_format($stats['accepted_requests'] ?? 0) }}</p>
        </div>
        <div class="stat-card">
            <h3>Completed Requests</h3>
            <p class="stat-number">{{ number_format($stats['completed_requests'] ?? 0) }}</p>
        </div>
        <div class="stat-card">
            <h3>Total Donations</h3>
            <p class="stat-number">{{ number_format($stats['total_donations'] ?? 0) }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <form method="GET" action="{{ route('admin.foodbank-donation-hub') }}">
            <div class="filters-grid">
                <div class="filter-group">
                    <label for="foodbank">Food Bank</label>
                    <select id="foodbank" name="foodbank" class="filter-select">
                        <option value="all" {{ $foodbankFilter === 'all' ? 'selected' : '' }}>All Food Banks</option>
                        @foreach($foodbanks as $fb)
                        <option value="{{ $fb->foodbank_id }}" {{ $foodbankFilter === $fb->foodbank_id ? 'selected' : '' }}>
                            {{ $fb->organization_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="filter-select">
                        <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All Status</option>
                        <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="accepted" {{ $statusFilter === 'accepted' ? 'selected' : '' }}>Accepted</option>
                        <option value="completed" {{ $statusFilter === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="declined" {{ $statusFilter === 'declined' ? 'selected' : '' }}>Declined</option>
                    </select>
                </div>
                <div class="filter-group">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="{{ route('admin.foodbank-donation-hub') }}" class="btn btn-secondary">Clear</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Donation Requests -->
    <div class="section-card">
        <h2>Donation Requests</h2>
        @if($donationRequests->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>Food Bank</th>
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
                    <td>{{ $request->foodbank->organization_name ?? 'N/A' }}</td>
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

    <!-- Donations -->
    <div class="section-card">
        <h2>Donations</h2>
        @if($donations->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>Food Bank</th>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Status</th>
                    <th>Establishment</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($donations as $donation)
                <tr>
                    <td>{{ $donation->foodbank->organization_name ?? 'N/A' }}</td>
                    <td>{{ $donation->item_name }}</td>
                    <td>{{ $donation->quantity }} {{ $donation->unit ?? '' }}</td>
                    <td><span class="status-badge status-{{ $donation->status }}">{{ ucfirst($donation->status) }}</span></td>
                    <td>{{ $donation->establishment->business_name ?? 'N/A' }}</td>
                    <td>{{ $donation->created_at->format('M d, Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="no-data">No donations found.</p>
        @endif
    </div>
</div>

<style>
.donation-hub-page {
    padding: 2rem;
    max-width: 1600px;
    margin: 0 auto;
}

.filters-section {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.section-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.section-card h2 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    color: #1f2937;
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

.no-data {
    text-align: center;
    padding: 2rem;
    color: #6b7280;
}
</style>
@endsection

