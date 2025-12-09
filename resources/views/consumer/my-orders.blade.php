@extends('layouts.consumer')

@section('title', 'My Orders | SavEats')

@section('header', 'My Orders')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Afacad&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/food-listing.css') }}">
<link rel="stylesheet" href="{{ asset('css/order-details-modal.css') }}">
<link rel="stylesheet" href="{{ asset('css/order-management.css') }}">
<link rel="stylesheet" href="{{ asset('css/rate-modal.css') }}">
<style>
/* My Orders Specific Styles */
.orders-container {
    padding: 25px;
    margin-bottom: 30px;
}

/* Tab Navigation */
.order-tabs {
    display: flex;
    justify-content: center;
    border-bottom: 2px solid #e9ecef;
    margin-bottom: 25px;
    gap: 5%;
}

.tab-button {
    background: none;
    border: none;
    padding: 8px 16px;
    font-size: 14px;
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
    max-width: 500px;
    margin: 0 auto;
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

.btn-danger {
    background: #DD5D36;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    flex: 1;
    min-width: 120px;
}

.btn-danger:hover {
    background: #C02121;
    transform: translateY(-1px);
}

.btn-danger:disabled {
    background: #6c757d;
    cursor: not-allowed;
    transform: none;
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
    .orders-container {
        padding: 15px;
    }
    
    /* Tab Navigation Mobile */
    .order-tabs {
        gap: 3%;
        margin-bottom: 20px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    
    .order-tabs::-webkit-scrollbar {
        display: none;
    }
    
    .tab-button {
        padding: 8px 12px;
        font-size: 13px;
        white-space: nowrap;
        flex-shrink: 0;
    }
    
    .order-card {
        max-width: 100%;
        padding: 16px;
    }
    
    .order-header {
        flex-direction: row;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .product-info h3 {
        font-size: 18px;
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
        font-size: 20px;
        font-weight: 700;
        color: #2d5016;
        text-align: right;
    }
    
    .order-details {
        margin: 15px 0;
    }
    
    .detail-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 12px;
        font-size: 14px;
        flex-direction: row;
        gap: 0;
    }
    
    .detail-label {
        font-weight: 600;
        color: #495057;
        flex: 1;
        min-width: auto;
        margin-right: 0;
    }
    
    .detail-value {
        color: #6c757d;
        flex: 1;
        text-align: right;
        font-weight: 500;
    }
    
    .order-actions {
        display: flex;
        gap: 12px;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #e9ecef;
        flex-direction: row;
        flex-wrap: wrap;
    }
    
    .btn {
        flex: 1;
        min-width: 120px;
        padding: 10px 16px;
        font-size: 14px;
    }
    
    .btn-danger {
        padding: 10px 16px;
        font-size: 14px;
    }
    
    .view-receipt-btn,
    .buy-again-btn {
        width: 100%;
        padding: 12px 20px;
        font-size: 14px;
    }
}

/* Small Mobile Devices */
@media (max-width: 480px) {
    .orders-container {
        padding: 12px;
    }
    
    .order-tabs {
        gap: 2%;
        margin-bottom: 15px;
    }
    
    .tab-button {
        padding: 6px 10px;
        font-size: 12px;
    }
    
    .order-card {
        padding: 12px;
    }
    
    .product-info h3 {
        font-size: 16px;
    }
    
    .product-info .quantity {
        font-size: 13px;
    }
    
    .order-price {
        font-size: 18px;
    }
    
    .detail-row {
        font-size: 13px;
        margin-bottom: 10px;
    }
    
    .order-actions {
        flex-direction: column;
        gap: 8px;
    }
    
    .btn {
        width: 100%;
        min-width: auto;
        padding: 10px 14px;
        font-size: 13px;
    }
    
    .view-receipt-btn,
    .buy-again-btn {
        padding: 10px 16px;
        font-size: 13px;
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
                        <div class="detail-row">
                            <span class="detail-label">Status:</span>
                            <span class="detail-value status-{{ $order['status'] }}">
                                @if($order['status'] === 'pending_delivery_confirmation')
                                    Pending Delivery Confirmation
                                @elseif($order['status'] === 'on_the_way')
                                    On The Way
                                @else
                                    {{ ucfirst(str_replace('_', ' ', $order['status'])) }}
                                @endif
                            </span>
                        </div>
                    </div>
                    
                    <div class="order-actions">
                        <button class="btn btn-outline" onclick="viewReceipt('{{ $order['order_id'] }}')">
                            View Receipt
                        </button>
                        @if(strtolower($order['status']) === 'on_the_way' && strtolower($order['delivery_method']) === 'delivery')
                        <button class="btn btn-confirm-delivery" onclick="confirmDelivery({{ $order['order_id_raw'] }})">
                            Confirm Delivered
                        </button>
                        @endif
                        @if(!in_array(strtolower($order['status']), ['accepted', 'pending_delivery_confirmation', 'on_the_way', 'completed', 'cancelled']))
                        <button class="btn btn-danger" onclick="cancelOrder({{ $order['order_id_raw'] }})">
                            Cancel Order
                        </button>
                        @endif
                    </div>
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
                        @if(isset($order['has_rating']) && $order['has_rating'])
                            <button class="btn btn-primary" onclick="rateOrder('{{ $order['order_id'] }}', true)">
                                Edit Rating
                            </button>
                        @else
                            <button class="btn btn-primary" onclick="rateOrder('{{ $order['order_id'] }}', false)">
                                Rate Now
                            </button>
                        @endif
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
                            <span class="detail-value">{{ $order['cancelled_date'] ?? 'N/A' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Reason:</span>
                            <span class="detail-value">{{ $order['cancellation_reason'] ?? 'N/A' }}</span>
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

@include('components.order-details-modal')
@include('components.rate-modal')
@include('components.view-rating-modal')
@endsection

@section('scripts')
<script src="{{ asset('js/order-details-modal.js') }}"></script>
<script src="{{ asset('js/rate-modal.js') }}"></script>
<script src="{{ asset('js/view-rating-modal.js') }}"></script>
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
    // Extract numeric ID from orderId (e.g., "ID#123" -> "123")
    const numericId = orderId.toString().replace(/[^0-9]/g, '');
    if (numericId) {
        openOrderDetailsModal(numericId, true);
    } else {
        alert('Invalid order ID');
    }
}

// Rate order function
function rateOrder(orderId, isEdit = false) {
    // Extract numeric ID
    const numericId = orderId.toString().replace(/[^0-9]/g, '');
    openRateModal(numericId, isEdit);
}

// View rating function
function viewRating(orderId) {
    const numericId = orderId.toString().replace(/[^0-9]/g, '');
    openViewRatingModal(numericId);
}

// Buy again function
function buyAgain(orderId) {
    // In a real app, this would add the items back to cart or navigate to product page
    alert('Buy Again for Order ID: ' + orderId + '\n\nThis feature will be implemented in the next version.');
}

// Cancel order function
function cancelOrder(orderId) {
    if (!confirm('Are you sure you want to cancel this order? This action cannot be undone.')) {
        return;
    }
    
    // Show loading state - find the button that was clicked
    const cancelBtn = document.querySelector(`button[onclick*="cancelOrder(${orderId})"]`);
    if (!cancelBtn) {
        alert('Error: Could not find cancel button');
        return;
    }
    
    const originalText = cancelBtn.textContent;
    cancelBtn.disabled = true;
    cancelBtn.textContent = 'Cancelling...';
    
    fetch(`/consumer/orders/${orderId}/cancel`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
            reason: 'Cancelled by customer'
        })
    })
    .then(async response => {
        const contentType = response.headers.get('content-type');
        let data;

        if (contentType && contentType.includes('application/json')) {
            data = await response.json();
        } else {
            const text = await response.text();
            console.error('Server returned non-JSON:', text.substring(0, 200));
            throw new Error(`Server error (${response.status})`);
        }

        if (!response.ok) {
            throw new Error(data.message || `HTTP error! status: ${response.status}`);
        }
        return data;
    })
    .then(data => {
        if (data.success) {
            alert('Order cancelled successfully!');
            // Refresh the page to show updated order list
            window.location.reload();
        } else {
            alert('Failed to cancel order: ' + (data.message || 'Unknown error'));
            cancelBtn.disabled = false;
            cancelBtn.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error cancelling order:', error);
        alert('An error occurred while cancelling the order: ' + error.message);
        cancelBtn.disabled = false;
        cancelBtn.textContent = originalText;
    });
}

function reorder(orderId) {
    // In a real app, this would add the items back to cart
    alert('Items from Order ' + orderId + ' have been added to your cart.');
}

// Confirm delivery function
function confirmDelivery(orderId) {
    if (!confirm('Confirm that you have received your order?')) {
        return;
    }
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                     document.querySelector('input[name="_token"]')?.value;
    
    fetch(`/consumer/orders/${orderId}/confirm-delivery`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message || 'Delivery confirmed successfully!');
            // Refresh the page to show updated order status
            window.location.reload();
        } else {
            alert('Error: ' + (data.error || data.message || 'Failed to confirm delivery'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while confirming delivery');
    });
}
</script>
@endsection