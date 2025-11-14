@extends('layouts.establishment')

@section('title', 'Donation Hub | SavEats')

@section('header', 'Donation Hub')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Afacad:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/donation-hub.css') }}">
@endsection

@section('content')
<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stats-card orange">
        <h3>Total Donations</h3>
        <div class="value" id="totalDonations">{{ $totalDonations ?? 0 }}</div>
    </div>
    <div class="stats-card yellow">
        <h3>Partner Charities</h3>
        <div class="value" id="partnerCharities">{{ $partnerCharities ?? 0 }}</div>
    </div>
</div>

<!-- Donation History Section -->
<div class="history-section">
    <div class="section-header">
        <h3 class="section-title">Donation History</h3>
        <div class="header-actions">
            <button class="icon-btn" id="exportBtn" title="Export data">
                <svg viewBox="0 0 24 24">
                    <path d="M19 12v7H5v-7H3v7c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-7h-2zm-6 .67l2.59-2.58L17 11.5l-5 5-5-5 1.41-1.41L11 12.67V3h2z"/>
                </svg>
            </button>
            <button class="icon-btn" id="filterBtn" title="Filter">
                <svg viewBox="0 0 24 24">
                    <path d="M10 18h4v-2h-4v2zM3 6v2h18V6H3zm3 7h12v-2H6v2z"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="history-filters">
        <div class="search-container">
            <input 
                type="text" 
                class="search-input" 
                id="searchInput" 
                placeholder="Search..."
                aria-label="Search donation history"
            >
            <svg class="search-icon" viewBox="0 0 24 24">
                <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
            </svg>
        </div>
        <input type="date" class="date-select" id="dateSelect" aria-label="Select date">
    </div>

    <!-- Desktop Table -->
    <table class="history-table" id="historyTable">
        <thead>
            <tr>
                <th>Charity/Org</th>
                <th>Address</th>
                <th>Phone Number</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <!-- Rows will be inserted by JavaScript -->
        </tbody>
    </table>

    <!-- Mobile Cards -->
    <div class="mobile-history-cards" id="mobileCards">
        <!-- Cards will be inserted by JavaScript -->
    </div>

    <!-- Pagination -->
    <div class="pagination" id="pagination">
        <!-- Pagination will be inserted by JavaScript -->
    </div>
</div>

<!-- Donation Requests Section -->
<div class="requests-section">
    <div class="requests-header">
        <h3 class="section-title">Donation Requests</h3>
        <a href="#" class="see-all-link" id="seeAllLink">See All</a>
    </div>

    <div class="requests-grid" id="requestsGrid">
        <!-- Request cards will be inserted by JavaScript -->
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Pass data to JavaScript
    window.donationHistory = @json($donationHistory ?? []);
    window.donationRequests = @json($donationRequests ?? []);
</script>
<script src="{{ asset('js/donation-hub.js') }}"></script>
@endsection

