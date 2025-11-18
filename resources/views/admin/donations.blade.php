@extends('layouts.admin')

@section('title', 'Donation Hub - Admin Dashboard')

@section('header', 'Donation Hub')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin-donations.css') }}">
@endsection

@section('content')
<div class="donations-management-page">
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon donations">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Total Donations</h3>
                <p class="stat-number">{{ number_format($stats['total_donations'] ?? 0) }}</p>
                <div class="stat-breakdown">
                    <span>Pending: {{ number_format($stats['pending_donations'] ?? 0) }}</span>
                    <span>Collected: {{ number_format($stats['collected_donations'] ?? 0) }}</span>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon requests">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Total Requests</h3>
                <p class="stat-number">{{ number_format($stats['total_requests'] ?? 0) }}</p>
                <div class="stat-breakdown">
                    <span>Active: {{ number_format($stats['active_requests'] ?? 0) }}</span>
                    <span>Completed: {{ number_format($stats['completed_requests'] ?? 0) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Combined Filters and Records Section -->
    <div class="combined-section">
        <div class="filters-header">
            <h2>Filters</h2>
            <div class="header-actions">
                <button class="btn-export" onclick="exportToCsv()" title="Export to CSV">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/>
                    </svg>
                    Export CSV
                </button>
                <button class="btn-clear-filters" onclick="clearFilters()">Clear All</button>
            </div>
        </div>
        <div class="filters-grid">
            <div class="filter-group">
                <label for="search">Search</label>
                <input type="text" id="search" name="search" class="filter-input" 
                       placeholder="Search by item, establishment, or food bank..." 
                       value="{{ $searchQuery ?? '' }}">
            </div>
            
            <div class="filter-group">
                <label for="type">Type</label>
                <select id="type" name="type" class="filter-select">
                    <option value="all" {{ ($typeFilter ?? 'all') === 'all' ? 'selected' : '' }}>All</option>
                    <option value="donations" {{ ($typeFilter ?? 'all') === 'donations' ? 'selected' : '' }}>Donations</option>
                    <option value="requests" {{ ($typeFilter ?? 'all') === 'requests' ? 'selected' : '' }}>Requests</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="filter-select">
                    <option value="all" {{ ($statusFilter ?? 'all') === 'all' ? 'selected' : '' }}>All Status</option>
                    <option value="pending_pickup" {{ ($statusFilter ?? 'all') === 'pending_pickup' ? 'selected' : '' }}>Pending Pickup</option>
                    <option value="ready_for_collection" {{ ($statusFilter ?? 'all') === 'ready_for_collection' ? 'selected' : '' }}>Ready for Collection</option>
                    <option value="collected" {{ ($statusFilter ?? 'all') === 'collected' ? 'selected' : '' }}>Collected</option>
                    <option value="cancelled" {{ ($statusFilter ?? 'all') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="pending" {{ ($statusFilter ?? 'all') === 'pending' ? 'selected' : '' }}>Pending (Request)</option>
                    <option value="active" {{ ($statusFilter ?? 'all') === 'active' ? 'selected' : '' }}>Active (Request)</option>
                    <option value="completed" {{ ($statusFilter ?? 'all') === 'completed' ? 'selected' : '' }}>Completed (Request)</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="date_from">Date From</label>
                <input type="date" id="date_from" name="date_from" class="filter-input" 
                       value="{{ $dateFrom ?? '' }}">
            </div>
            
            <div class="filter-group">
                <label for="date_to">Date To</label>
                <input type="date" id="date_to" name="date_to" class="filter-input" 
                       value="{{ $dateTo ?? '' }}">
            </div>
        </div>

        <div class="table-header">
            <h2 id="recordsCountHeader">All Records ({{ $total ?? 0 }})</h2>
        </div>
        
        <div class="table-container">
            <table class="donations-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>ID/Number</th>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Establishment</th>
                        <th>Food Bank</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="recordsTableBody">
                    @forelse($items ?? [] as $item)
                    <tr data-record-id="{{ $item['id'] }}" 
                        data-type="{{ $item['record_type'] ?? 'donation' }}"
                        data-status="{{ $item['status'] ?? '' }}"
                        data-search-text="{{ strtolower(($item['item_name'] ?? '') . ' ' . ($item['donation_number'] ?? '') . ' ' . ($item['establishment']['name'] ?? '') . ' ' . ($item['foodbank']['name'] ?? '')) }}">
                        <td>
                            <span class="type-badge type-{{ $item['record_type'] ?? 'donation' }}">
                                {{ ucfirst($item['record_type'] ?? 'donation') }}
                            </span>
                        </td>
                        <td>
                            @if(($item['record_type'] ?? 'donation') === 'donation')
                            <div class="record-id">{{ $item['donation_number'] ?? 'N/A' }}</div>
                            @else
                            <div class="record-id">{{ substr($item['id'], 0, 8) }}...</div>
                            @endif
                        </td>
                        <td>
                            <div class="item-info">
                                <div class="item-name">{{ $item['item_name'] ?? 'N/A' }}</div>
                                @if(isset($item['item_category']) || isset($item['category']))
                                <div class="item-category">{{ ucfirst($item['item_category'] ?? $item['category'] ?? '') }}</div>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="quantity-info">
                                <span class="quantity-value">{{ $item['quantity'] ?? 0 }}</span>
                                @if(isset($item['unit']))
                                <span class="quantity-unit">{{ $item['unit'] }}</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if(isset($item['establishment']) && $item['establishment'])
                            <div class="establishment-info">
                                <div class="establishment-name">{{ $item['establishment']['name'] }}</div>
                                <div class="establishment-email">{{ $item['establishment']['email'] }}</div>
                            </div>
                            @else
                            <span class="no-data">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if(isset($item['foodbank']) && $item['foodbank'])
                            <div class="foodbank-info">
                                <div class="foodbank-name">{{ $item['foodbank']['name'] }}</div>
                                <div class="foodbank-email">{{ $item['foodbank']['email'] }}</div>
                            </div>
                            @else
                            <span class="no-data">N/A</span>
                            @endif
                        </td>
                        <td>
                            <span class="status-badge status-{{ str_replace('_', '-', $item['status'] ?? 'pending') }}">
                                {{ ucfirst(str_replace('_', ' ', $item['status'] ?? 'pending')) }}
                            </span>
                        </td>
                        <td>
                            <div class="date-info">
                                @if(isset($item['created_at']) && $item['created_at'])
                                    @php
                                        $createdAt = \Carbon\Carbon::parse($item['created_at']);
                                    @endphp
                                    <div class="date-created">{{ $createdAt->format('M d, Y') }}</div>
                                    <div class="date-time">{{ $createdAt->format('h:i A') }}</div>
                                @else
                                    <div class="date-created">N/A</div>
                                @endif
                            </div>
                        </td>
                        <td>
                            <button class="btn-action btn-view" onclick="viewRecordDetails({{ json_encode($item) }})" title="View Details">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="no-records">No records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @php
            $totalPages = isset($total) && isset($perPage) && $perPage > 0 ? ceil($total / $perPage) : 1;
        @endphp
        @if(isset($total) && $total > ($perPage ?? 20) && $totalPages > 1)
        <div class="pagination-container">
            <div class="pagination-info">
                Showing {{ (($currentPage ?? 1) - 1) * ($perPage ?? 20) + 1 }} to {{ min(($currentPage ?? 1) * ($perPage ?? 20), $total) }} of {{ $total }} records
            </div>
            <div class="pagination">
                @if(($currentPage ?? 1) > 1)
                <a href="{{ request()->fullUrlWithQuery(['page' => ($currentPage ?? 1) - 1]) }}" class="pagination-link">Previous</a>
                @endif
                
                @for($i = max(1, ($currentPage ?? 1) - 2); $i <= min($totalPages, ($currentPage ?? 1) + 2); $i++)
                <a href="{{ request()->fullUrlWithQuery(['page' => $i]) }}" 
                   class="pagination-link {{ ($currentPage ?? 1) == $i ? 'active' : '' }}">{{ $i }}</a>
                @endfor
                
                @if(($currentPage ?? 1) < $totalPages)
                <a href="{{ request()->fullUrlWithQuery(['page' => ($currentPage ?? 1) + 1]) }}" class="pagination-link">Next</a>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Record Details Modal -->
<div class="modal-overlay" id="recordDetailsModal">
    <div class="modal modal-record-details">
        <div class="modal-header">
            <h2 id="recordDetailsModalTitle">Record Details</h2>
            <button class="modal-close" id="closeRecordDetailsModal" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body" id="recordDetailsModalBody">
            <!-- Content will be populated by JavaScript -->
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="closeRecordDetailsModalBtn">Close</button>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/admin-donations.js') }}"></script>
@endpush

@endsection

