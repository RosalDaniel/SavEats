@extends('layouts.admin')

@section('title', 'Food Bank Management - Admin Dashboard')

@section('header', 'Food Bank Management')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin-foodbanks.css') }}">
@endsection

@section('content')
<div class="foodbanks-management-page">
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon foodbanks">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Total Food Banks</h3>
                <p class="stat-number">{{ number_format($stats['total'] ?? 0) }}</p>
                <div class="stat-breakdown">
                    <span>Verified: {{ number_format($stats['verified'] ?? 0) }}</span>
                    <span>Unverified: {{ number_format($stats['unverified'] ?? 0) }}</span>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon verified">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Verified</h3>
                <p class="stat-number">{{ number_format($stats['verified'] ?? 0) }}</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon active">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Active</h3>
                <p class="stat-number">{{ number_format($stats['active'] ?? 0) }}</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon suspended">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Suspended</h3>
                <p class="stat-number">{{ number_format($stats['suspended'] ?? 0) }}</p>
            </div>
        </div>
    </div>

    <!-- Combined Filters and Foodbanks Table Section -->
    <div class="combined-section">
        <div class="filters-header">
            <h2>Filters</h2>
            <button class="btn-clear-filters" onclick="clearFilters()">Clear All</button>
        </div>
        <div class="filters-grid">
            <div class="filter-group">
                <label for="search">Search</label>
                <input type="text" id="search" name="search" class="filter-input" 
                       placeholder="Search by name, email, or contact person..." 
                       value="{{ $searchQuery ?? '' }}">
            </div>
            
            <div class="filter-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="filter-select">
                    <option value="all" {{ ($statusFilter ?? 'all') === 'all' ? 'selected' : '' }}>All Status</option>
                    <option value="active" {{ ($statusFilter ?? 'all') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ ($statusFilter ?? 'all') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="verified">Verification</label>
                <select id="verified" name="verified" class="filter-select">
                    <option value="all" {{ ($verifiedFilter ?? 'all') === 'all' ? 'selected' : '' }}>All</option>
                    <option value="verified" {{ ($verifiedFilter ?? 'all') === 'verified' ? 'selected' : '' }}>Verified</option>
                    <option value="unverified" {{ ($verifiedFilter ?? 'all') === 'unverified' ? 'selected' : '' }}>Unverified</option>
                </select>
            </div>
        </div>

        <div class="table-header">
            <h2 id="foodbanksCountHeader">All Food Banks ({{ count($formattedFoodbanks ?? []) }})</h2>
        </div>
        
        <div class="table-container">
            <table class="foodbanks-table">
                <thead>
                    <tr>
                        <th>Food Bank</th>
                        <th>Contact Person</th>
                        <th>Contact Number</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Verified</th>
                        <th>Donation Requests</th>
                        <th>Date Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="foodbanksTableBody">
                    @forelse($formattedFoodbanks ?? [] as $foodbank)
                    <tr data-foodbank-id="{{ $foodbank['id'] }}" 
                        data-status="{{ $foodbank['status'] ?? 'active' }}" 
                        data-verified="{{ $foodbank['verified'] ? 'true' : 'false' }}"
                        data-search-text="{{ strtolower($foodbank['organization_name'] . ' ' . $foodbank['email'] . ' ' . $foodbank['contact_person']) }}">
                        <td>
                            <div class="foodbank-info">
                                <div class="foodbank-avatar">
                                    @if(isset($foodbank['profile_image']) && $foodbank['profile_image'])
                                        <img src="{{ asset('storage/' . $foodbank['profile_image']) }}" alt="{{ $foodbank['organization_name'] }}">
                                    @else
                                        <div class="avatar-placeholder">
                                            {{ strtoupper(substr($foodbank['organization_name'], 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="foodbank-details">
                                    <div class="foodbank-name">{{ $foodbank['organization_name'] }}</div>
                                    <div class="foodbank-email">{{ $foodbank['email'] }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $foodbank['contact_person'] }}</td>
                        <td>{{ $foodbank['phone_no'] ?? 'N/A' }}</td>
                        <td>{{ $foodbank['address'] ?? 'N/A' }}</td>
                        <td>
                            <span class="status-badge status-{{ $foodbank['status'] ?? 'active' }}">
                                {{ ucfirst($foodbank['status'] ?? 'active') }}
                            </span>
                        </td>
                        <td>
                            @if($foodbank['verified'])
                            <span class="verified-badge verified-yes">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                </svg>
                                Verified
                            </span>
                            @else
                            <span class="verified-badge verified-no">Not Verified</span>
                            @endif
                        </td>
                        <td>
                            <div class="requests-info">
                                <span class="requests-count">{{ $foodbank['total_requests'] }}</span>
                            </div>
                        </td>
                        <td>{{ $foodbank['registered_at']->format('M d, Y') }}</td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-action btn-view" onclick="viewFoodbankDetails('{{ $foodbank['id'] }}')" title="View Details">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                                    </svg>
                                </button>
                                @if(!$foodbank['verified'])
                                <button class="btn-action btn-verify" onclick="toggleVerification('{{ $foodbank['id'] }}', true)" title="Verify">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                    </svg>
                                </button>
                                @else
                                <button class="btn-action btn-unverify" onclick="toggleVerification('{{ $foodbank['id'] }}', false)" title="Unverify">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                                    </svg>
                                </button>
                                @endif
                                @if(($foodbank['status'] ?? 'active') === 'active')
                                <button class="btn-action btn-suspend" onclick="updateStatus('{{ $foodbank['id'] }}', 'suspended')" title="Suspend">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                                    </svg>
                                </button>
                                @elseif(($foodbank['status'] ?? 'active') === 'suspended')
                                <button class="btn-action btn-activate" onclick="updateStatus('{{ $foodbank['id'] }}', 'active')" title="Activate">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                    </svg>
                                </button>
                                @endif
                                <button class="btn-action btn-delete" onclick="deleteFoodbank('{{ $foodbank['id'] }}')" title="Delete">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="no-foodbanks">No food banks found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/admin-foodbanks.js') }}"></script>
@endpush

@endsection

