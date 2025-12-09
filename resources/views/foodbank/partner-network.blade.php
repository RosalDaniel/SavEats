@extends('layouts.foodbank')

@section('title', 'Partner Network | SavEats')

@section('header', 'Partner Network')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/partner-network.css') }}">
@endsection

@section('content')
<div class="partner-network-page">
    <div class="stats-grid">
        <div class="stats-card">
            <h3>Business Partnered</h3>
            <div class="value" id="totalPartners">{{ $totalPartners ?? 0 }}</div>
        </div>
    </div>

    <div class="partners-section">
        <div class="section-header">
            <h3 class="section-title">Food Businesses</h3>
            <span id="resultCount">0 Partners</span>
        </div>

        <div class="filters-container">
            <div class="search-container">
                <input 
                    type="text" 
                    class="search-input" 
                    id="searchInput" 
                    placeholder="Search partners..." 
                    aria-label="Search partners"
                >
                <svg class="search-icon" viewBox="0 0 24 24">
                    <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                </svg>
            </div>
            <select class="filter-select" id="typeFilter">
                <option value="all">All Types</option>
                <option value="bakery">Bakery</option>
                <option value="grocery">Grocery Store</option>
                <option value="restaurant">Restaurant</option>
                <option value="farm">Farm</option>
            </select>
            <select class="filter-select" id="sortFilter">
                <option value="name">Sort by Name</option>
                <option value="rating">Sort by Rating</option>
                <option value="donations">Sort by Donations</option>
            </select>
        </div>

        <div class="partners-grid" id="partnersGrid">
            <!-- Partner cards will be dynamically inserted here -->
        </div>
    </div>
</div>

<!-- Partner Details Modal -->
<div class="modal-overlay" id="detailModal">
    <div class="modal">
        <div class="modal-header">
            <h2 id="modalTitle">Partner Details</h2>
            <button class="modal-close" id="closeModal" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- Modal content will be dynamically inserted here -->
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="closeModalBtn">Close</button>
            <button class="btn btn-primary" id="contactPartnerBtn">Contact Partner</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    window.partners = @json($partners ?? []);
    window.stats = {
        totalPartners: {{ $totalPartners ?? 0 }}
    };
</script>
<script src="{{ asset('js/partner-network.js') }}"></script>
@endsection

