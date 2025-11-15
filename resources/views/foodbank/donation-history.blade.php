@extends('layouts.foodbank')

@section('title', 'Donation History | SavEats')

@section('header', 'Donation History')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/donation-history.css') }}">
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
            <h3>Establishment Participation</h3>
            <div class="value" id="establishmentParticipation">{{ $stats['establishment_participation'] ?? 0 }}</div>
        </div>
    </div>

    <!-- Export Actions -->
    <div class="export-section">
        <div class="export-header">
            <h3>Export Reports</h3>
        </div>
        <div class="export-buttons">
            <div class="export-left-group">
                <a href="{{ route('foodbank.donation-history.export', array_merge(['type' => 'history'], request()->only(['status', 'category', 'establishment_id', 'date_from', 'date_to']))) }}" class="btn-export" id="exportHistoryBtn">
                    <svg viewBox="0 0 24 24">
                        <path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/>
                    </svg>
                    Export Donation History
                </a>
                <a href="{{ route('foodbank.donation-history.export', ['type' => 'category']) }}" class="btn-export" id="exportCategoryBtn">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                    Export Category Breakdown
                </a>
            </div>
            <div class="export-monthly-group">
                <select id="monthSelector" class="month-selector">
                    @for($i = 0; $i < 12; $i++)
                        @php
                            $date = now()->subMonths($i);
                            $monthValue = $date->format('Y-m');
                            $monthLabel = $date->format('F Y');
                        @endphp
                        <option value="{{ $monthValue }}" {{ $i === 0 ? 'selected' : '' }}>{{ $monthLabel }}</option>
                    @endfor
                </select>
                <a href="#" class="btn-export" id="exportMonthlyBtn" data-base-url="{{ route('foodbank.donation-history.export', ['type' => 'monthly']) }}">
                    <svg viewBox="0 0 24 24">
                        <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/>
                    </svg>
                    Export Monthly Report
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
            <form id="filterForm" method="GET" action="{{ route('foodbank.donation-history') }}" class="filters-grid">
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
                    <label for="categoryFilter">Category</label>
                    <select name="category" id="categoryFilter" class="filter-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>{{ ucfirst($category) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label for="establishmentFilter">Establishment</label>
                    <select name="establishment_id" id="establishmentFilter" class="filter-select">
                        <option value="">All Establishments</option>
                        @foreach($establishments as $establishment)
                            <option value="{{ $establishment['id'] }}" {{ request('establishment_id') == $establishment['id'] ? 'selected' : '' }}>{{ $establishment['name'] }}</option>
                        @endforeach
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
            <h3 class="section-title">Donation Records</h3>
            <span id="resultCount">{{ count($donations) }} Donation{{ count($donations) !== 1 ? 's' : '' }}</span>
        </div>

        <div class="table-container">
            <table class="donations-table" id="donationsTable">
                <thead>
                    <tr>
                        <th>Donation ID</th>
                        <th>Establishment</th>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Scheduled Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="donationsTableBody">
                    @forelse($donations as $donation)
                    <tr class="donation-row {{ $donation['is_urgent'] ? 'urgent' : '' }} {{ $donation['is_nearing_expiry'] ? 'nearing-expiry' : '' }}" data-id="{{ $donation['id'] }}">
                        <td>
                            <div class="donation-id">{{ $donation['donation_number'] }}</div>
                            @if($donation['is_urgent'])
                                <span class="badge badge-urgent">Urgent</span>
                            @endif
                            @if($donation['is_nearing_expiry'])
                                <span class="badge badge-expiry">Expiring Soon</span>
                            @endif
                        </td>
                        <td>{{ $donation['establishment_name'] }}</td>
                        <td>{{ $donation['item_name'] }}</td>
                        <td><span class="category-badge">{{ ucfirst($donation['category']) }}</span></td>
                        <td>{{ $donation['quantity'] }} {{ $donation['unit'] }}</td>
                        <td>
                            <span class="status-badge status-{{ $donation['status'] }}">{{ $donation['status_display'] }}</span>
                        </td>
                        <td>{{ $donation['scheduled_date_display'] }}</td>
                        <td>
                            <button class="btn-view-details" onclick="viewDonationDetails('{{ $donation['id'] }}')">View Details</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="no-data">No donations found matching your criteria.</td>
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
    window.donations = @json($donations ?? []);
</script>
<script src="{{ asset('js/donation-history.js') }}"></script>
@endsection

