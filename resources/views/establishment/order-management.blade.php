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
        <button class="tab-button" data-tab="completed">Completed</button>
        <button class="tab-button" data-tab="cancelled">Cancelled</button>
    </div>

    <!-- Orders List -->
    <div class="orders-list" id="ordersList">
        <!-- Pending Orders -->
        <div class="tab-content" id="pending-orders">
            @if(isset($orders) && count($orders) > 0)
                @foreach($orders as $order)
                @if($order['status'] === 'pending')
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
                @if($order['status'] === 'accepted')
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
                        <button class="btn-accept" onclick="markComplete('{{ $order['id'] }}')">
                            Mark Complete
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
                    <h3>No Accepted Orders</h3>
                    <p>You don't have any accepted orders at the moment.</p>
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
