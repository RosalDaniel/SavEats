@extends('layouts.admin')

@section('title', 'Order Management - Admin Dashboard')

@section('header', 'Order Management')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin-orders.css') }}">
@endsection

@section('content')
<div class="orders-management-page">
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon orders">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M7 18c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12L8.1 13h7.45c.75 0 1.41-.41 1.75-1.03L21.7 4H5.21l-.94-2H1zm16 16c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Total Orders</h3>
                <p class="stat-number">{{ number_format($stats['total'] ?? 0) }}</p>
                <div class="stat-breakdown">
                    <span>Today: {{ number_format($stats['today'] ?? 0) }}</span>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon pending">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Pending</h3>
                <p class="stat-number">{{ number_format($stats['pending'] ?? 0) }}</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon completed">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Completed</h3>
                <p class="stat-number">{{ number_format($stats['completed'] ?? 0) }}</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon cancelled">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm5 13.59L15.59 17 12 13.41 8.41 17 7 15.59 10.59 12 7 8.41 8.41 7 12 10.59 15.59 7 17 8.41 13.41 12 17 15.59z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Cancelled</h3>
                <p class="stat-number">{{ number_format($stats['cancelled'] ?? 0) }}</p>
            </div>
        </div>
    </div>

    <!-- Combined Filters and Orders Table Section -->
    <div class="combined-section">
        <div class="filters-header">
            <h2>Filters</h2>
            <button class="btn-clear-filters" onclick="clearFilters()">Clear All</button>
        </div>
        <div class="filters-grid">
            <div class="filter-group">
                <label for="search">Search</label>
                <input type="text" id="search" name="search" class="filter-input" 
                       placeholder="Search by order number, customer, or establishment..." 
                       value="{{ $searchQuery ?? '' }}">
            </div>
            
            <div class="filter-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="filter-select">
                    <option value="all" {{ ($statusFilter ?? 'all') === 'all' ? 'selected' : '' }}>All Status</option>
                    <option value="pending" {{ ($statusFilter ?? 'all') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="accepted" {{ ($statusFilter ?? 'all') === 'accepted' ? 'selected' : '' }}>Accepted</option>
                    <option value="completed" {{ ($statusFilter ?? 'all') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ ($statusFilter ?? 'all') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="date">Date Range</label>
                <select id="date" name="date" class="filter-select">
                    <option value="all" {{ ($dateFilter ?? 'all') === 'all' ? 'selected' : '' }}>All Time</option>
                    <option value="today" {{ ($dateFilter ?? 'all') === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="week" {{ ($dateFilter ?? 'all') === 'week' ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="month" {{ ($dateFilter ?? 'all') === 'month' ? 'selected' : '' }}>Last 30 Days</option>
                </select>
            </div>
        </div>

        <div class="table-header">
            <h2 id="ordersCountHeader">All Orders ({{ count($formattedOrders ?? []) }})</h2>
        </div>
        
        <div class="table-container">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Establishment</th>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="ordersTableBody">
                    @forelse($formattedOrders ?? [] as $order)
                    <tr data-order-id="{{ $order['id'] }}" 
                        data-status="{{ $order['status'] ?? 'pending' }}"
                        data-search-text="{{ strtolower($order['order_number'] . ' ' . ($order['customer_name'] ?? '') . ' ' . ($order['consumer']['name'] ?? '') . ' ' . ($order['establishment']['name'] ?? '')) }}">
                        <td>
                            <div class="order-number">{{ $order['order_number'] }}</div>
                            <div class="order-method">
                                <span class="method-badge method-{{ strtolower($order['delivery_method'] ?? 'pickup') }}">
                                    {{ ucfirst($order['delivery_method'] ?? 'Pickup') }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <div class="customer-info">
                                <div class="customer-name">{{ $order['customer_name'] }}</div>
                                @if($order['consumer'])
                                <div class="consumer-email">{{ $order['consumer']['email'] }}</div>
                                @endif
                                <div class="customer-phone">{{ $order['customer_phone'] }}</div>
                            </div>
                        </td>
                        <td>
                            @if($order['establishment'])
                            <div class="establishment-info">
                                <div class="establishment-name">{{ $order['establishment']['name'] }}</div>
                                <div class="establishment-email">{{ $order['establishment']['email'] }}</div>
                            </div>
                            @else
                            <span class="no-establishment">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($order['food_listing'])
                            <div class="listing-info">
                                <div class="listing-name">{{ $order['food_listing']['name'] }}</div>
                                <div class="listing-category">{{ ucfirst($order['food_listing']['category'] ?? 'N/A') }}</div>
                            </div>
                            @else
                            <span class="no-listing">N/A</span>
                            @endif
                        </td>
                        <td>{{ $order['quantity'] }}</td>
                        <td>
                            <div class="price-info">
                                <div class="price-total">₱{{ number_format($order['total_price'], 2) }}</div>
                                <div class="payment-method">{{ ucfirst($order['payment_method'] ?? 'N/A') }}</div>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge status-{{ $order['status'] ?? 'pending' }}">
                                {{ ucfirst($order['status'] ?? 'pending') }}
                            </span>
                            @if($order['effective_status'] === 'missed_pickup')
                            <div class="missed-badge">Missed Pickup</div>
                            @endif
                        </td>
                        <td>
                            <div class="date-info">
                                <div class="date-created">{{ \Carbon\Carbon::parse($order['created_at'])->setTimezone('Asia/Manila')->format('M d, Y') }}</div>
                                <div class="date-time">{{ \Carbon\Carbon::parse($order['created_at'])->setTimezone('Asia/Manila')->format('h:i A') }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-action btn-view" onclick="viewOrderDetails({{ $order['id'] }}, {{ json_encode($order) }})" title="View Details">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                                    </svg>
                                </button>
                                @if(($order['status'] ?? 'pending') !== 'cancelled' && ($order['status'] ?? 'pending') !== 'completed')
                                <button class="btn-action btn-cancel" onclick="forceCancel({{ $order['id'] }})" title="Force Cancel">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm5 13.59L15.59 17 12 13.41 8.41 17 7 15.59 10.59 12 7 8.41 8.41 7 12 10.59 15.59 7 17 8.41 13.41 12 17 15.59z"/>
                                    </svg>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="no-orders">No orders found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Order Details Modal -->
<div class="modal-overlay" id="orderDetailsModal">
    <div class="modal modal-order-details">
        <div class="modal-header">
            <h2 id="orderDetailsModalTitle">Order Details</h2>
            <button class="modal-close" id="closeOrderDetailsModal" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body" id="orderDetailsModalBody">
            <!-- Content will be populated by JavaScript -->
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="closeOrderDetailsModalBtn">Close</button>
        </div>
    </div>
</div>

<!-- Force Cancel Modal -->
<div class="modal-overlay" id="forceCancelModal">
    <div class="modal modal-force-cancel">
        <div class="modal-header">
            <h2>Force Cancel Order</h2>
            <button class="modal-close" id="closeForceCancelModal" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="forceCancelForm">
                <input type="hidden" id="cancelOrderId" name="order_id">
                
                <div class="form-group">
                    <label for="cancelReason">Cancellation Reason (Optional)</label>
                    <textarea id="cancelReason" name="reason" class="form-input" 
                              rows="4" placeholder="Enter the reason for force cancelling this order (optional)..."></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="cancelForceCancelBtn">Cancel</button>
            <button class="btn btn-danger" id="confirmForceCancelBtn">Force Cancel Order</button>
        </div>
    </div>
</div>


@push('scripts')
<script src="{{ asset('js/admin-orders.js') }}"></script>
@endpush

@endsection

