@extends('layouts.establishment')

@section('title', 'Donation History | SavEats')

@section('header', 'Donation History')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/establishment-donation-history.css') }}">
@endsection

@section('content')
<div class="donation-history-page">
    <!-- Summary Statistics -->
    <div class="stats-grid">
        <div class="stats-card">
            <h3>Total Donations</h3>
            <div class="value" id="totalDonations">{{ $stats['total_donations'] ?? 0 }}</div>
        </div>
        <div class="stats-card">
            <h3>Total Quantity</h3>
            <div class="value" id="totalQuantity">{{ number_format($stats['total_quantity'] ?? 0) }}</div>
        </div>
        <div class="stats-card">
            <h3>Foodbanks Served</h3>
            <div class="value" id="foodbanksServed">{{ $stats['foodbanks_served'] ?? 0 }}</div>
        </div>
    </div>

    <!-- Export Section -->
    <div class="export-section">
        <div class="export-header">
            <h3>Export</h3>
        </div>
        <div class="export-dropdown">
            <button class="btn-export-toggle" id="exportToggle">
                <svg viewBox="0 0 24 24">
                    <path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/>
                </svg>
                Export
                <svg class="dropdown-arrow" viewBox="0 0 24 24">
                    <path d="M7 10l5 5 5-5z"/>
                </svg>
            </button>
            <div class="export-menu" id="exportMenu">
                <a href="{{ route('establishment.donation-history.export', array_merge(['type' => 'csv'], request()->only(['status', 'date_from', 'date_to', 'search']))) }}" class="export-option">
                    <svg viewBox="0 0 24 24">
                        <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                    </svg>
                    Export as CSV
                </a>
                <a href="{{ route('establishment.donation-history.export', array_merge(['type' => 'excel'], request()->only(['status', 'date_from', 'date_to', 'search']))) }}" class="export-option">
                    <svg viewBox="0 0 24 24">
                        <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                    </svg>
                    Export as Excel
                </a>
                <a href="{{ route('establishment.donation-history.export', array_merge(['type' => 'pdf'], request()->only(['status', 'date_from', 'date_to', 'search']))) }}" class="export-option">
                    <svg viewBox="0 0 24 24">
                        <path d="M20 2H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-8.5 7.5c0 .83-.67 1.5-1.5 1.5H9v2H7.5V7H10c.83 0 1.5.67 1.5 1.5v1zm5 2c0 .83-.67 1.5-1.5 1.5h-2.5V7H15c.83 0 1.5.67 1.5 1.5v3zm4-3H19v1h1.5V11H19v2h-1.5V7h3v1.5zM9 9.5h1v-1H9v1zm5 2h1v-1h-1v1zm5-2h1v-1h-1v1z"/>
                    </svg>
                    Export as PDF
                </a>
            </div>
        </div>
    </div>

    <!-- Combined Filters and Donations Section -->
    <div class="combined-section">
        <!-- Filters Section -->
        <div class="filters-section">
            <div class="filters-header">
                <h3>Filters</h3>
                <button class="btn-clear-filters" id="clearFilters">Clear All</button>
            </div>
            <form id="filterForm" method="GET" action="{{ route('establishment.donation-history') }}" class="filters-grid">
                <div class="filter-group">
                    <label for="searchInput">Search</label>
                    <input type="text" name="search" id="searchInput" class="filter-input" placeholder="Search by item name, foodbank..." value="{{ request('search') }}">
                </div>
                <div class="filter-group">
                    <label for="statusFilter">Status</label>
                    <select name="status" id="statusFilter" class="filter-select">
                        <option value="">All Statuses</option>
                        <option value="pending_pickup" {{ request('status') == 'pending_pickup' ? 'selected' : '' }}>Pending Pickup</option>
                        <option value="ready_for_collection" {{ request('status') == 'ready_for_collection' ? 'selected' : '' }}>Ready for Collection</option>
                        <option value="collected" {{ request('status') == 'collected' ? 'selected' : '' }}>Collected</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="dateFromFilter">Date From</label>
                    <input type="date" name="date_from" id="dateFromFilter" class="filter-input" value="{{ request('date_from') }}">
                </div>
                <div class="filter-group">
                    <label for="dateToFilter">Date To</label>
                    <input type="date" name="date_to" id="dateToFilter" class="filter-input" value="{{ request('date_to') }}">
                </div>
                <div class="filter-group filter-actions">
                    <button type="submit" class="btn-filter">Apply Filters</button>
                </div>
            </form>
        </div>

        <!-- Donations Table -->
        <div class="donations-section">
        <div class="section-header">
            <h3 class="section-title">Donation History</h3>
            <span id="resultCount">{{ count($formattedDonations ?? []) }} Donation{{ count($formattedDonations ?? []) !== 1 ? 's' : '' }}</span>
        </div>

        <div class="table-container">
            <table class="donations-table" id="donationsTable">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Quantity</th>
                        <th>Date Donated</th>
                        <th>Recipient (Foodbank)</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="donationsTableBody">
                    @forelse($formattedDonations as $donation)
                    <tr class="donation-row" data-id="{{ $donation['id'] }}">
                        <td>
                            <div class="item-name">{{ $donation['item_name'] }}</div>
                            @if($donation['category'])
                                <span class="category-badge">{{ ucfirst($donation['category']) }}</span>
                            @endif
                        </td>
                        <td>{{ $donation['quantity'] }} {{ $donation['unit'] }}</td>
                        <td>{{ $donation['date_donated'] }}</td>
                        <td>{{ $donation['foodbank_name'] }}</td>
                        <td>
                            <span class="status-badge status-{{ $donation['status'] }}">{{ $donation['status_display'] }}</span>
                        </td>
                        <td>
                            <button class="btn-view-details" onclick="viewDonationDetails('{{ $donation['id'] }}')">View Details</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="no-data">No donations found matching your criteria.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        </div>
    </div>
</div>

<!-- Donation Details Modal -->
<div class="modal-overlay" id="donationDetailsModal">
    <div class="modal modal-donation-details">
        <div class="modal-header">
            <h2 id="modalDonationNumber">Donation Details</h2>
            <button class="modal-close" id="closeDonationModal" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body" id="modalDonationBody">
            <!-- Content will be populated by JavaScript -->
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="closeDonationModalBtn">Close</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    window.donations = @json($formattedDonations ?? []);
</script>
<script src="{{ asset('js/establishment-donation-history.js') }}"></script>
@endsection

