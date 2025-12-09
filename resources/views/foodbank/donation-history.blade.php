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
                <div class="export-dropdown">
                    <button class="btn-export" id="exportHistoryToggle">
                        <svg viewBox="0 0 24 24">
                            <path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/>
                        </svg>
                        Export Donation History
                        <svg class="dropdown-arrow" viewBox="0 0 24 24">
                            <path d="M7 10l5 5 5-5z"/>
                        </svg>
                    </button>
                    <div class="export-menu" id="exportHistoryMenu">
                        <a href="{{ route('foodbank.donation-history.export', array_merge(['type' => 'history'], request()->only(['status', 'category', 'establishment_id', 'date_from', 'date_to']))) }}" class="export-option">
                            <svg viewBox="0 0 24 24">
                                <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                            </svg>
                            Export as CSV
                        </a>
                        <a href="{{ route('foodbank.donation-history.export', array_merge(['type' => 'history', 'format' => 'excel'], request()->only(['status', 'category', 'establishment_id', 'date_from', 'date_to']))) }}" class="export-option">
                            <svg viewBox="0 0 24 24">
                                <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                            </svg>
                            Export as Excel
                        </a>
                        <a href="{{ route('foodbank.donation-history.export', array_merge(['type' => 'history', 'format' => 'pdf'], request()->only(['status', 'category', 'establishment_id', 'date_from', 'date_to']))) }}" class="export-option">
                            <svg viewBox="0 0 24 24">
                                <path d="M20 2H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-8.5 7.5c0 .83-.67 1.5-1.5 1.5H9v2H7.5V7H10c.83 0 1.5.67 1.5 1.5v1zm5 2c0 .83-.67 1.5-1.5 1.5h-2.5V7H15c.83 0 1.5.67 1.5 1.5v3zm4-3H19v1h1.5V11H19v2h-1.5V7h3v1.5zM9 9.5h1v-1H9v1zm5 2h1v-1h-1v1zm5-2h1v-1h-1v1z"/>
                            </svg>
                            Export as PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Combined Filters and Donations Section -->
    <div class="combined-section">
        <!-- Filters Section -->
        <div class="filters-section">
            <div class="filters-header">
                <h3>Filters</h3>
                <button type="button" class="btn-clear-filters" id="clearFilters">Clear All</button>
            </div>
            <form id="filterForm" method="GET" action="{{ route('foodbank.donation-history') }}" class="filters-grid">
                <div class="filter-group">
                    <label for="statusFilter">Status</label>
                    <select name="status" id="statusFilter" class="filter-select">
                        <option value="">All Statuses</option>
                        <optgroup label="Donation Statuses">
                            <option value="pending_pickup" {{ request('status') == 'pending_pickup' ? 'selected' : '' }}>Pending Pickup</option>
                            <option value="ready_for_collection" {{ request('status') == 'ready_for_collection' ? 'selected' : '' }}>Ready for Collection</option>
                            <option value="collected" {{ request('status') == 'collected' ? 'selected' : '' }}>Collected</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                        </optgroup>
                        <optgroup label="Request Statuses">
                            <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>Accepted</option>
                            <option value="declined" {{ request('status') == 'declined' ? 'selected' : '' }}>Declined</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        </optgroup>
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
            </form>
        </div>

        <!-- Donations Table -->
        <div class="donations-section">
        <div class="section-header">
            <h3 class="section-title">Donation Records</h3>
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
                    <tr class="donation-row {{ $donation['is_urgent'] ? 'urgent' : '' }} {{ $donation['is_nearing_expiry'] ? 'nearing-expiry' : '' }}" 
                        data-id="{{ $donation['id'] }}"
                        data-category="{{ strtolower($donation['category'] ?? '') }}"
                        data-status="{{ $donation['status'] }}"
                        data-establishment-id="{{ $donation['establishment_id'] ?? '' }}"
                        data-search-text="{{ strtolower($donation['item_name'] . ' ' . ($donation['establishment_name'] ?? '')) }}">
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
        
        <!-- Pagination -->
        @if($donations->hasPages())
        <div class="pagination-container">
            <div class="pagination-info">
                Showing {{ $donations->count() }} of {{ $donations->total() }} items
            </div>
            <div class="pagination-links">
                {{ $donations->appends(request()->query())->links('vendor.pagination.custom') }}
            </div>
        </div>
        @endif
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
    window.donations = @json($donations->items() ?? []);
    
    // Client-side filtering (no page refresh)
    document.addEventListener('DOMContentLoaded', function() {
        initializeAutoFilter();
        applyFilters();
    });

    // Initialize automatic filtering
    function initializeAutoFilter() {
        const statusSelect = document.getElementById('statusFilter');
        const categorySelect = document.getElementById('categoryFilter');
        const establishmentSelect = document.getElementById('establishmentFilter');
        
        if (statusSelect) {
            statusSelect.addEventListener('change', applyFilters);
        }
        
        if (categorySelect) {
            categorySelect.addEventListener('change', applyFilters);
        }
        
        if (establishmentSelect) {
            establishmentSelect.addEventListener('change', applyFilters);
        }
    }

    // Apply filters automatically
    function applyFilters() {
        const statusSelect = document.getElementById('statusFilter');
        const categorySelect = document.getElementById('categoryFilter');
        const establishmentSelect = document.getElementById('establishmentFilter');
        const tableBody = document.getElementById('donationsTableBody');
        
        if (!tableBody) return;
        
        const statusValue = statusSelect ? statusSelect.value : '';
        const categoryValue = categorySelect ? categorySelect.value : '';
        const establishmentValue = establishmentSelect ? establishmentSelect.value : '';
        
        const rows = tableBody.querySelectorAll('tr');
        let visibleCount = 0;
        
        rows.forEach(function(row) {
            if (row.classList.contains('no-data')) {
                row.style.display = 'none';
                return;
            }
            
            const status = row.getAttribute('data-status') || '';
            const category = row.getAttribute('data-category') || '';
            const establishmentId = row.getAttribute('data-establishment-id') || '';
            
            // Check status filter
            const matchesStatus = !statusValue || status === statusValue;
            
            // Check category filter
            const matchesCategory = !categoryValue || category === categoryValue.toLowerCase();
            
            // Check establishment filter
            const matchesEstablishment = !establishmentValue || establishmentId === establishmentValue;
            
            // Show/hide row based on filters
            if (matchesStatus && matchesCategory && matchesEstablishment) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Show "no donations" message if no rows are visible
        const noDataRow = tableBody.querySelector('.no-data');
        if (visibleCount === 0 && !noDataRow) {
            const newRow = document.createElement('tr');
            newRow.className = 'no-data';
            newRow.innerHTML = '<td colspan="8" class="no-data">No donations found matching the filters.</td>';
            tableBody.appendChild(newRow);
        } else if (visibleCount > 0 && noDataRow) {
            noDataRow.remove();
        }
    }
    
    // Export dropdown toggle
    (function() {
        const exportHistoryToggle = document.getElementById('exportHistoryToggle');
        const exportHistoryMenu = document.getElementById('exportHistoryMenu');
        
        if (exportHistoryToggle && exportHistoryMenu) {
            exportHistoryToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                exportHistoryMenu.classList.toggle('show');
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!exportHistoryToggle.contains(e.target) && !exportHistoryMenu.contains(e.target)) {
                    exportHistoryMenu.classList.remove('show');
                }
            });
        }
    })();
    
    // Keep existing form submission for date filters (they require server-side filtering)
    (function() {
        const filterForm = document.getElementById('filterForm');
        if (!filterForm) return;
        
        const dateInputs = filterForm.querySelectorAll('input[type="date"]');
        
        dateInputs.forEach(input => {
            input.addEventListener('change', function() {
                filterForm.submit();
            });
        });
        
        // Clear filters button
        const clearFiltersBtn = document.getElementById('clearFilters');
        if (clearFiltersBtn) {
            clearFiltersBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                // Reset all form fields
                const filterForm = document.getElementById('filterForm');
                if (filterForm) {
                    filterForm.reset();
                    // Reset select dropdowns to first option
                    const selects = filterForm.querySelectorAll('select');
                    selects.forEach(select => {
                        select.selectedIndex = 0;
                    });
                    // Clear date inputs
                    const dateInputs = filterForm.querySelectorAll('input[type="date"]');
                    dateInputs.forEach(input => {
                        input.value = '';
                    });
                }
                // Redirect to clear all query parameters
                window.location.href = '{{ route("foodbank.donation-history") }}';
            });
        }
    })();
</script>
<script src="{{ asset('js/donation-history.js') }}"></script>
@endsection

