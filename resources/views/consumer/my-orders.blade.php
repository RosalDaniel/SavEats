@extends('layouts.consumer')

@section('title', 'My Orders - SavEats')

@section('header', 'My Orders')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/food-listing.css') }}">
<style>
/* My Orders Specific Styles */
.orders-container {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    margin-bottom: 30px;
}

/* Tab Navigation */
.order-tabs {
    display: flex;
    justify-content: center;
    border-bottom: 2px solid #e9ecef;
    margin-bottom: 25px;
    gap: 0;
}

.tab-button {
    background: none;
    border: none;
    padding: 12px 24px;
    font-size: 16px;
    font-weight: 600;
    color: #6c757d;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all 0.3s ease;
    position: relative;
}

.tab-button.active {
    color: #2d5016;
    border-bottom-color: #2d5016;
}

.tab-button:hover {
    color: #2d5016;
}

/* Order Cards */
.orders-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.order-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
}

.order-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.product-info h3 {
    font-size: 20px;
    font-weight: 700;
    color: #ff8c00;
    margin: 0 0 5px 0;
}

.product-info .quantity {
    font-size: 14px;
    color: #6c757d;
    margin: 0;
}

.order-price {
    font-size: 24px;
    font-weight: 700;
    color: #2d5016;
    margin: 0;
}

.order-details {
    margin: 15px 0;
}

.detail-row {
    display: flex;
    margin-bottom: 8px;
    font-size: 14px;
}

.detail-label {
    font-weight: 600;
    color: #495057;
    min-width: 120px;
    margin-right: 10px;
}

.detail-value {
    color: #6c757d;
    flex: 1;
}

/* Order Actions */
.order-actions {
    display: flex;
    gap: 12px;
    margin-top: 15px;
}

.btn {
    flex: 1;
    padding: 12px 20px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
    text-align: center;
}

.btn-outline {
    background: white;
    color: #2d5016;
    border: 2px solid #2d5016;
}

.btn-outline:hover {
    background: #f8f9fa;
    transform: translateY(-1px);
}

.btn-primary {
    background: #2d5016;
    color: white;
}

.btn-primary:hover {
    background: #1e3a0f;
    transform: translateY(-1px);
}

/* Status Styling */
.status-completed {
    color: #28a745 !important;
    font-weight: 600;
}

/* Buy Again Button */
.buy-again-btn {
    width: 100%;
    background: #2d5016;
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 15px;
}

.buy-again-btn:hover {
    background: #1e3a0f;
    transform: translateY(-1px);
}

/* Legacy button for other tabs */
.view-receipt-btn {
    width: 100%;
    background: #2d5016;
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 15px;
}

.view-receipt-btn:hover {
    background: #1e3a0f;
    transform: translateY(-1px);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
}

.empty-state h3 {
    font-size: 24px;
    margin-bottom: 10px;
    color: #495057;
}

.empty-state p {
    font-size: 16px;
    margin-bottom: 20px;
}

/* Responsive */
@media (max-width: 768px) {
    .order-header {
        flex-direction: column;
        gap: 10px;
    }
    
    .order-price {
        font-size: 20px;
    }
    
    .detail-row {
        flex-direction: column;
        gap: 2px;
    }
    
    .detail-label {
        min-width: auto;
        margin-right: 0;
    }
    
    .order-actions {
        flex-direction: column;
        gap: 8px;
    }
    
    .btn {
        width: 100%;
    }
}
</style>
@endsection

@section('content')
<div class="orders-container">
    <!-- Tab Navigation -->
    <div class="order-tabs">
        <button class="tab-button active" data-tab="upcoming">Upcoming</button>
        <button class="tab-button" data-tab="completed">Completed</button>
        <button class="tab-button" data-tab="cancelled">Cancelled</button>
    </div>

    <!-- Orders List -->
    <div class="orders-list" id="ordersList">
        <!-- Upcoming Orders -->
        <div class="tab-content" id="upcoming-orders">
            @if(isset($userOrders['upcoming']) && count($userOrders['upcoming']) > 0)
                @foreach($userOrders['upcoming'] as $order)
                <div class="order-card">
                    <div class="order-header">
                        <div class="product-info">
                            <h3>{{ $order['product_name'] }}</h3>
                            <p class="quantity">{{ $order['quantity'] }}</p>
                        </div>
                        <div class="order-price">₱ {{ number_format($order['price'], 2) }}</div>
                    </div>
                    
                    <div class="order-details">
                        <div class="detail-row">
                            <span class="detail-label">Order ID:</span>
                            <span class="detail-value">{{ $order['order_id'] }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Store:</span>
                            <span class="detail-value">{{ $order['store_name'] }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Store Time Range:</span>
                            <span class="detail-value">{{ $order['store_hours'] }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Delivery Method:</span>
                            <span class="detail-value">{{ $order['delivery_method'] }}</span>
                        </div>
                    </div>
                    
                    <button class="view-receipt-btn" onclick="viewReceipt('{{ $order['order_id'] }}')">
                        View Receipt
                    </button>
                </div>
                @endforeach
            @else
                <div class="empty-state">
                    <h3>No Upcoming Orders</h3>
                    <p>You don't have any upcoming orders at the moment.</p>
                </div>
            @endif
        </div>

        <!-- Completed Orders -->
        <div class="tab-content" id="completed-orders" style="display: none;">
            @if(isset($userOrders['completed']) && count($userOrders['completed']) > 0)
                @foreach($userOrders['completed'] as $order)
                <div class="order-card">
                    <div class="order-header">
                        <div class="product-info">
                            <h3>{{ $order['product_name'] }}</h3>
                            <p class="quantity">{{ $order['quantity'] }}</p>
                        </div>
                        <div class="order-price">₱ {{ number_format($order['price'], 2) }}</div>
                    </div>
                    
                    <div class="order-details">
                        <div class="detail-row">
                            <span class="detail-label">Order ID:</span>
                            <span class="detail-value">{{ $order['order_id'] }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Store:</span>
                            <span class="detail-value">{{ $order['store_name'] }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Store Time Range:</span>
                            <span class="detail-value">{{ $order['store_hours'] }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Delivery Method:</span>
                            <span class="detail-value">{{ $order['delivery_method'] }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Status:</span>
                            <span class="detail-value status-completed">Successfully Picked Up</span>
                        </div>
                    </div>
                    
                    <div class="order-actions">
                        <button class="btn btn-outline" onclick="viewReceipt('{{ $order['order_id'] }}')">
                            View Receipt
                        </button>
                        <button class="btn btn-primary" onclick="rateOrder('{{ $order['order_id'] }}')">
                            Rate Now
                        </button>
                    </div>
                </div>
                @endforeach
            @else
                <div class="empty-state">
                    <h3>No Completed Orders</h3>
                    <p>You haven't completed any orders yet.</p>
                </div>
            @endif
        </div>

        <!-- Cancelled Orders -->
        <div class="tab-content" id="cancelled-orders" style="display: none;">
            @if(isset($userOrders['cancelled']) && count($userOrders['cancelled']) > 0)
                @foreach($userOrders['cancelled'] as $order)
                <div class="order-card">
                    <div class="order-header">
                        <div class="product-info">
                            <h3>{{ $order['product_name'] }}</h3>
                            <p class="quantity">{{ $order['quantity'] }}</p>
                        </div>
                        <div class="order-price">₱ {{ number_format($order['price'], 2) }}</div>
                    </div>
                    
                    <div class="order-details">
                        <div class="detail-row">
                            <span class="detail-label">Order ID:</span>
                            <span class="detail-value">{{ $order['order_id'] }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Store:</span>
                            <span class="detail-value">{{ $order['store_name'] }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Cancelled:</span>
                            <span class="detail-value">{{ $order['cancelled_date'] }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Reason:</span>
                            <span class="detail-value">{{ $order['cancellation_reason'] }}</span>
                        </div>
                    </div>
                    
                    <button class="buy-again-btn" onclick="buyAgain('{{ $order['order_id'] }}')">
                        Buy Again
                    </button>
                </div>
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
<script>
// Tab functionality
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Remove active class from all buttons
            tabButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            
            // Hide all tab contents
            tabContents.forEach(content => {
                content.style.display = 'none';
            });
            
            // Show target tab content
            const targetContent = document.getElementById(targetTab + '-orders');
            if (targetContent) {
                targetContent.style.display = 'block';
            }
        });
    });
});

// View receipt function
function viewReceipt(orderId) {
    // In a real app, this would open a receipt modal or navigate to receipt page
    alert('Receipt for Order ID: ' + orderId + '\n\nThis feature will be implemented in the next version.');
}

// Rate order function
function rateOrder(orderId) {
    // In a real app, this would open a rating modal or navigate to rating page
    alert('Rate Order ID: ' + orderId + '\n\nThis feature will be implemented in the next version.');
}

// Buy again function
function buyAgain(orderId) {
    // In a real app, this would add the items back to cart or navigate to product page
    alert('Buy Again for Order ID: ' + orderId + '\n\nThis feature will be implemented in the next version.');
}

// Order actions
function cancelOrder(orderId) {
    if (confirm('Are you sure you want to cancel this order?')) {
        // In a real app, this would make an API call to cancel the order
        alert('Order ' + orderId + ' has been cancelled.');
        // Refresh the page or update the UI
        location.reload();
    }
}

function reorder(orderId) {
    // In a real app, this would add the items back to cart
    alert('Items from Order ' + orderId + ' have been added to your cart.');
}
</script>
@endsection