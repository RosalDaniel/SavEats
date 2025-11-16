@extends('layouts.admin')

@section('title', 'Food Listings Management - Admin Dashboard')

@section('header', 'Food Listings Management')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin-food-listings.css') }}">
@endsection

@section('content')
<div class="food-listings-management-page">
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon listings">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M8.1 13.34l2.83-2.83L3.91 3.5c-1.56 1.56-1.56 4.09 0 5.66l4.19 4.18zm6.78-1.81c1.53.71 3.68.21 5.27-1.38 1.91-1.91 2.28-4.65.81-6.12-1.46-1.46-4.2-1.1-6.12.81-1.59 1.59-2.09 3.74-1.38 5.27L3.7 19.87l1.41 1.41L12 14.41l6.88 6.88 1.41-1.41L13.41 13l1.47-1.47z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Total Listings</h3>
                <p class="stat-number">{{ number_format($stats['total'] ?? 0) }}</p>
                <div class="stat-breakdown">
                    <span>Active: {{ number_format($stats['active'] ?? 0) }}</span>
                    <span>Inactive: {{ number_format($stats['inactive'] ?? 0) }}</span>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon active">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Active Listings</h3>
                <p class="stat-number">{{ number_format($stats['active'] ?? 0) }}</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon expired">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Expired</h3>
                <p class="stat-number">{{ number_format($stats['expired'] ?? 0) }}</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon expiring">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Expiring Soon</h3>
                <p class="stat-number">{{ number_format($stats['expiring_soon'] ?? 0) }}</p>
            </div>
        </div>
    </div>

    <!-- Combined Filters and Listings Table Section -->
    <div class="combined-section">
        <div class="filters-header">
            <h2>Filters</h2>
            <button class="btn-clear-filters" onclick="clearFilters()">Clear All</button>
        </div>
        <div class="filters-grid">
            <div class="filter-group">
                <label for="search">Search</label>
                <input type="text" id="search" name="search" class="filter-input" 
                       placeholder="Search by name, description, or establishment..." 
                       value="{{ $searchQuery ?? '' }}">
            </div>
            
            <div class="filter-group">
                <label for="category">Category</label>
                <select id="category" name="category" class="filter-select">
                    <option value="all" {{ ($categoryFilter ?? 'all') === 'all' ? 'selected' : '' }}>All Categories</option>
                    @foreach($categories ?? [] as $category)
                    <option value="{{ $category }}" {{ ($categoryFilter ?? 'all') === $category ? 'selected' : '' }}>
                        {{ ucfirst($category) }}
                    </option>
                    @endforeach
                </select>
            </div>
            
            <div class="filter-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="filter-select">
                    <option value="all" {{ ($statusFilter ?? 'all') === 'all' ? 'selected' : '' }}>All Status</option>
                    <option value="active" {{ ($statusFilter ?? 'all') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ ($statusFilter ?? 'all') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="expired" {{ ($statusFilter ?? 'all') === 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="expiry">Expiry Date</label>
                <select id="expiry" name="expiry" class="filter-select">
                    <option value="all" {{ ($expiryFilter ?? 'all') === 'all' ? 'selected' : '' }}>All</option>
                    <option value="active" {{ ($expiryFilter ?? 'all') === 'active' ? 'selected' : '' }}>Active (Not Expired)</option>
                    <option value="expiring_soon" {{ ($expiryFilter ?? 'all') === 'expiring_soon' ? 'selected' : '' }}>Expiring Soon (7 days)</option>
                    <option value="expired" {{ ($expiryFilter ?? 'all') === 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
            </div>
        </div>

        <div class="table-header">
            <h2 id="listingsCountHeader">All Food Listings ({{ count($formattedListings ?? []) }})</h2>
        </div>
        
        <div class="table-container">
            <table class="listings-table">
                <thead>
                    <tr>
                        <th>Listing</th>
                        <th>Establishment</th>
                        <th>Category</th>
                        <th>Stock</th>
                        <th>Price</th>
                        <th>Expiry Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="listingsTableBody">
                    @forelse($formattedListings ?? [] as $listing)
                    <tr data-listing-id="{{ $listing['id'] }}" 
                        data-status="{{ $listing['status'] ?? 'active' }}" 
                        data-category="{{ strtolower($listing['category'] ?? '') }}"
                        data-is-expired="{{ $listing['is_expired'] ? 'true' : 'false' }}"
                        data-search-text="{{ strtolower($listing['name'] . ' ' . ($listing['description'] ?? '') . ' ' . ($listing['establishment']['name'] ?? '')) }}">
                        <td>
                            <div class="listing-info">
                                <div class="listing-image">
                                    @if(isset($listing['image_path']) && $listing['image_path'])
                                        <img src="{{ asset('storage/' . $listing['image_path']) }}" alt="{{ $listing['name'] }}">
                                    @else
                                        <div class="image-placeholder">
                                            {{ strtoupper(substr($listing['name'], 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="listing-details">
                                    <div class="listing-name">{{ $listing['name'] }}</div>
                                    @if($listing['description'])
                                    <div class="listing-description">{{ Str::limit($listing['description'], 50) }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($listing['establishment'])
                            <div class="establishment-info">
                                <div class="establishment-name">{{ $listing['establishment']['name'] }}</div>
                                <div class="establishment-email">{{ $listing['establishment']['email'] }}</div>
                            </div>
                            @else
                            <span class="no-establishment">N/A</span>
                            @endif
                        </td>
                        <td>
                            <span class="category-badge">{{ ucfirst($listing['category'] ?? 'N/A') }}</span>
                        </td>
                        <td>
                            <div class="stock-info">
                                <div class="stock-available">Available: <strong>{{ $listing['available_stock'] }}</strong></div>
                                <div class="stock-total">Total: {{ $listing['quantity'] }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="price-info">
                                @if($listing['discounted_price'])
                                <div class="price-original">₱{{ number_format($listing['original_price'], 2) }}</div>
                                <div class="price-discounted">₱{{ number_format($listing['discounted_price'], 2) }}</div>
                                <div class="price-discount">{{ $listing['discount_percentage'] }}% off</div>
                                @else
                                <div class="price-normal">₱{{ number_format($listing['original_price'], 2) }}</div>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="expiry-info">
                                <div class="expiry-date">{{ $listing['expiry_date']->format('M d, Y') }}</div>
                                @if($listing['is_expired'])
                                <div class="expiry-badge expired">Expired</div>
                                @elseif($listing['days_until_expiry'] <= 7)
                                <div class="expiry-badge expiring">Expires in {{ $listing['days_until_expiry'] }} days</div>
                                @else
                                <div class="expiry-badge active">{{ $listing['days_until_expiry'] }} days left</div>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="status-badge status-{{ $listing['status'] ?? 'active' }}">
                                {{ ucfirst($listing['status'] ?? 'active') }}
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                @if(($listing['status'] ?? 'active') === 'active')
                                <button class="btn-action btn-disable" onclick="updateStatus({{ $listing['id'] }}, 'inactive')" title="Disable">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                                    </svg>
                                </button>
                                @else
                                <button class="btn-action btn-activate" onclick="updateStatus({{ $listing['id'] }}, 'active')" title="Activate">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                    </svg>
                                </button>
                                @endif
                                <button class="btn-action btn-delete" onclick="deleteListing({{ $listing['id'] }})" title="Delete">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="no-listings">No food listings found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/admin-food-listings.js') }}"></script>
@endpush

@endsection

