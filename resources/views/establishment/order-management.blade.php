@extends('layouts.establishment')

@section('title', 'Order Management')

@section('header', 'My Orders')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Afacad&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/order-management.css') }}">
@endsection

@section('content')
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
                            <span class="detail-label">Customer Name:</span>
                            <span class="detail-value">{{ $order['customer_name'] }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Delivery Method:</span>
                            <span class="detail-value">{{ $order['delivery_method'] }}</span>
                        </div>
                    </div>
                    
                    <div class="order-actions">
                        <button class="btn-accept" onclick="acceptOrder('{{ $order['id'] }}')">
                            Accept Order
                        </button>
                        <button class="btn-cancel" onclick="cancelOrder('{{ $order['id'] }}')">
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
                @if($order['status'] === 'accepted' && !$order['is_missed_pickup'])
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
                        <button class="btn-confirm-{{ strtolower($order['delivery_method']) }}" onclick="markComplete('{{ $order['id'] }}')">
                            {{ $order['delivery_method'] === 'Pickup' ? 'Pick-Up Confirmed' : 'Delivery Confirmed' }}
                        </button>
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
                            <span class="detail-value">{{ $order['customer_name'] }}</span>
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
                            <span class="detail-value">{{ $order['customer_name'] }}</span>
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
                            <span class="detail-value">{{ $order['customer_name'] }}</span>
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

@endsection

@section('scripts')
<script src="{{ asset('js/order-management.js') }}"></script>
@endsection
