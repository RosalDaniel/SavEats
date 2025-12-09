@extends('layouts.establishment')

@section('title', 'Order Management')

@section('header', 'My Orders')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Afacad&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/order-management.css') }}">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
@endsection

@section('content')
@if(!($isVerified ?? true))
<div style="padding: 16px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; color: #856404; font-size: 14px; margin-bottom: 20px;">
    Your account is not verified. Please wait for admin approval.
</div>
@endif
<div class="orders-container">
    <!-- Tab Navigation -->
    <div class="order-tabs">
        <button class="tab-button active" data-tab="pending">Pending</button>
        <button class="tab-button" data-tab="accepted">Accepted</button>
        <button class="tab-button" data-tab="missed-pickup">
            Missed Pickup @if(isset($orderCounts['missed_pickup']) && $orderCounts['missed_pickup'] > 0)
                <span style="background: white; color: #dc3545; padding: 2px 6px; border-radius: 10px; margin-left: 5px; font-size: 12px;">
                    {{ $orderCounts['missed_pickup'] }}
                </span>
            @endif
        </button>
        <button class="tab-button" data-tab="completed">Completed</button>
        <button class="tab-button" data-tab="cancelled">Cancelled</button>
    </div>

    <!-- Orders List -->
    <div class="orders-list" id="ordersList">
        <!-- Pending Orders -->
        <div class="tab-content" id="pending-orders">
            @if(isset($orders) && count($orders) > 0)
                @foreach($orders as $order)
                @if($order['status'] === 'pending' && !$order['is_missed_pickup'])
                <div class="order-card">
                    <div class="order-header">
                        <div class="product-info">
                            <h3 class="product-name">{{ $order['product_name'] }}</h3>
                            <p class="product-quantity">{{ $order['quantity'] }}</p>
                        </div>
                        <div class="order-price">₱ {{ number_format($order['price'], 2) }}</div>
                    </div>
                    
                    <div class="order-details">
                        <div class="detail-row">
                            <span class="detail-label">Order ID:</span>
                            <span class="detail-value">ID#{{ $order['id'] }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Status:</span>
                            <span class="detail-value">
                                @if($order['status'] === 'pending_delivery_confirmation')
                                    Pending Delivery Confirmation
                                @else
                                    Pending
                                @endif
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Customer Name:</span>
                            <span class="detail-value">{{ urldecode($order['customer_name'] ?? '') }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Delivery Method:</span>
                            <span class="detail-value">{{ $order['delivery_method'] }}</span>
                        </div>
                    </div>
                    
                    <div class="order-actions">
                        <button class="btn-accept" onclick="acceptOrder('{{ $order['id'] }}')" @if(!($isVerified ?? true)) disabled style="opacity: 0.5; cursor: not-allowed;" title="Your account is not verified. Please wait for admin approval." @endif>
                            Accept Order
                        </button>
                        <button class="btn-cancel" onclick="cancelOrder('{{ $order['id'] }}')" @if(!($isVerified ?? true)) disabled style="opacity: 0.5; cursor: not-allowed;" title="Your account is not verified. Please wait for admin approval." @endif>
                            Cancel Order
                        </button>
                        <button class="btn-view" onclick="viewOrderDetails('{{ $order['id'] }}')">
                            View Details
                        </button>
                    </div>
                </div>
                @endif
                @endforeach
            @else
                <div class="empty-state">
                    <h3>No Pending Orders</h3>
                    <p>You don't have any pending orders at the moment.</p>
                </div>
            @endif
        </div>

        <!-- Accepted Orders -->
        <div class="tab-content" id="accepted-orders" style="display: none;">
            @if(isset($orders) && count($orders) > 0)
                @foreach($orders as $order)
                @if(($order['status'] === 'accepted' || $order['status'] === 'pending_delivery_confirmation' || $order['status'] === 'on_the_way') && !$order['is_missed_pickup'])
                <div class="order-card accepted-order-card">
                    <div class="accepted-order-header">
                        <div class="accepted-product-info">
                            <h3 class="accepted-product-name">{{ $order['product_name'] }}</h3>
                            <p class="accepted-product-quantity">{{ $order['quantity'] }}</p>
                        </div>
                        <div class="accepted-store-name">{{ $establishment->name ?? 'Store' }}</div>
                    </div>
                    
                    <div class="accepted-order-details">
                        <div class="accepted-detail-row">
                            <span class="accepted-detail-label">Order ID:</span>
                            <span class="accepted-detail-value">ID#{{ $order['id'] }}</span>
                        </div>
                        <div class="accepted-detail-row">
                            <span class="accepted-detail-label">Status:</span>
                            <span class="accepted-detail-value">
                                @if($order['status'] === 'pending_delivery_confirmation')
                                    Pending Delivery Confirmation
                                @elseif($order['status'] === 'on_the_way')
                                    On The Way
                                @else
                                    Accepted
                                @endif
                            </span>
                        </div>
                        <div class="accepted-detail-row">
                            <span class="accepted-detail-label">Contact No.</span>
                            <span class="accepted-detail-value">{{ $order['customer_phone'] ?? 'N/A' }}</span>
                        </div>
                        <div class="accepted-detail-row">
                            <span class="accepted-detail-label">Delivery Method:</span>
                            <span class="accepted-detail-value">{{ $order['delivery_method'] }}</span>
                        </div>
                        @if($order['delivery_method'] === 'Pickup' && $order['pickup_date'])
                        <div class="accepted-detail-row">
                            <span class="accepted-detail-label">Pick-Up Date:</span>
                            <span class="accepted-detail-value">{{ $order['pickup_date'] }}</span>
                        </div>
                        @endif
                        @if($order['delivery_method'] === 'Pickup' && $order['pickup_time_range'])
                        <div class="accepted-detail-row">
                            <span class="accepted-detail-label">Pick-Up Time:</span>
                            <span class="accepted-detail-value">{{ $order['pickup_time_range'] }}</span>
                        </div>
                        @endif
                    </div>
                    
                    <div class="accepted-order-actions">
                        <button class="btn-view-details" onclick="viewOrderDetails('{{ $order['id'] }}')">
                            View Details
                        </button>
                        @if($order['delivery_method'] === 'Pickup' && $order['status'] === 'accepted')
                        <button class="btn-confirm-pickup" onclick="markComplete('{{ $order['id'] }}')">
                            Pick-Up Confirmed
                        </button>
                        @elseif($order['delivery_method'] === 'Delivery' && ($order['status'] === 'pending_delivery_confirmation' || $order['status'] === 'accepted'))
                        <button class="btn-out-for-delivery" onclick="markOutForDelivery('{{ $order['id'] }}')">
                            Mark Out for Delivery
                        </button>
                        @endif
                    </div>
                </div>
                @endif
                @endforeach
            @else
                <div class="empty-state">
                    <h3>No Accepted Orders</h3>
                    <p>You don't have any accepted orders at the moment.</p>
                </div>
            @endif
        </div>

        <!-- Missed Pickup Orders -->
        <div class="tab-content" id="missed-pickup-orders" style="display: none;">
            @if(isset($orders) && count($orders) > 0)
                @foreach($orders as $order)
                @if($order['is_missed_pickup'])
                <div class="order-card">
                    <div class="order-header">
                        <div class="product-info">
                            <h3 class="product-name">{{ $order['product_name'] }}</h3>
                            <p class="product-quantity">{{ $order['quantity'] }}</p>
                            <p style="color: #DD5D36; font-weight: bold; margin-top: 5px;">
                                ⚠️ Missed Pickup - End Time: {{ $order['pickup_end_time'] ?? 'N/A' }}
                            </p>
                        </div>
                        <div class="order-price">₱ {{ number_format($order['price'], 2) }}</div>
                    </div>
                    
                    <div class="order-details">
                        <div class="detail-row">
                            <span class="detail-label">Order ID:</span>
                            <span class="detail-value">ID#{{ $order['id'] }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Customer Name:</span>
                            <span class="detail-value">{{ urldecode($order['customer_name'] ?? '') }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Delivery Method:</span>
                            <span class="detail-value">{{ $order['delivery_method'] }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Pickup End Time:</span>
                            <span class="detail-value" style="color: #DD5D36; font-weight: bold;">
                                {{ $order['pickup_end_time'] ?? 'N/A' }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="order-actions">
                        <button class="btn-cancel" onclick="handleMissedPickup('{{ $order['id'] }}')" style="background-color: #DD5D36;">
                            Cancel & Refund
                        </button>
                        <button class="btn-view" onclick="viewOrderDetails('{{ $order['id'] }}')">
                            View Details
                        </button>
                    </div>
                </div>
                @endif
                @endforeach
            @else
                <div class="empty-state">
                    <h3>No Missed Pickup Orders</h3>
                    <p>All pickup orders have been collected on time.</p>
                </div>
            @endif
        </div>

        <!-- Completed Orders -->
        <div class="tab-content" id="completed-orders" style="display: none;">
            @if(isset($orders) && count($orders) > 0)
                @foreach($orders as $order)
                @if($order['status'] === 'completed')
                <div class="order-card">
                    <div class="order-header">
                        <div class="product-info">
                            <h3 class="product-name">{{ $order['product_name'] }}</h3>
                            <p class="product-quantity">{{ $order['quantity'] }}</p>
                        </div>
                        <div class="order-price">₱ {{ number_format($order['price'], 2) }}</div>
                    </div>
                    
                    <div class="order-details">
                        <div class="detail-row">
                            <span class="detail-label">Order ID:</span>
                            <span class="detail-value">ID#{{ $order['id'] }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Customer Name:</span>
                            <span class="detail-value">{{ urldecode($order['customer_name'] ?? '') }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Delivery Method:</span>
                            <span class="detail-value">{{ $order['delivery_method'] }}</span>
                        </div>
                    </div>
                    
                    <div class="order-actions">
                        <button class="btn-view" onclick="viewOrderDetails('{{ $order['id'] }}')">
                            View Details
                        </button>
                    </div>
                </div>
                @endif
                @endforeach
            @else
                <div class="empty-state">
                    <h3>No Completed Orders</h3>
                    <p>You don't have any completed orders yet.</p>
                </div>
            @endif
        </div>

        <!-- Cancelled Orders -->
        <div class="tab-content" id="cancelled-orders" style="display: none;">
            @if(isset($orders) && count($orders) > 0)
                @foreach($orders as $order)
                @if($order['status'] === 'cancelled')
                <div class="order-card">
                    <div class="order-header">
                        <div class="product-info">
                            <h3 class="product-name">{{ $order['product_name'] }}</h3>
                            <p class="product-quantity">{{ $order['quantity'] }}</p>
                        </div>
                        <div class="order-price">₱ {{ number_format($order['price'], 2) }}</div>
                    </div>
                    
                    <div class="order-details">
                        <div class="detail-row">
                            <span class="detail-label">Order ID:</span>
                            <span class="detail-value">ID#{{ $order['id'] }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Customer Name:</span>
                            <span class="detail-value">{{ urldecode($order['customer_name'] ?? '') }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Delivery Method:</span>
                            <span class="detail-value">{{ $order['delivery_method'] }}</span>
                        </div>
                    </div>
                    
                    <div class="order-actions">
                        <button class="btn-view" onclick="viewOrderDetails('{{ $order['id'] }}')">
                            View Details
                        </button>
                    </div>
                </div>
                @endif
                @endforeach
            @else
                <div class="empty-state">
                    <h3>No Cancelled Orders</h3>
                    <p>You don't have any cancelled orders.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Order Details Modal -->
<div class="modal-overlay" id="orderDetailsModal" style="display: none;">
    <div class="modal modal-order-details">
        <div class="modal-header">
            <h2 id="orderDetailsModalTitle">Order Details</h2>
            <button class="modal-close" id="closeOrderDetailsModal" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body" id="orderDetailsModalBody">
            <div id="orderDetailsLoading" style="text-align: center; padding: 40px;">
                <p>Loading order details...</p>
            </div>
            <div id="orderDetailsContent" style="display: none;">
                <!-- Content will be populated by JavaScript -->
            </div>
        </div>
        <div class="modal-footer" id="orderDetailsModalFooter" style="display: none;">
            <!-- Action buttons will be populated by JavaScript -->
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="{{ asset('js/order-management.js') }}"></script>
@endsection
