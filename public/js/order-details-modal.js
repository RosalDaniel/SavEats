// Order Details Modal JavaScript

let currentOrderId = null;
let isConsumerView = false;

// Initialize event listeners when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Close button in modal header
    const closeBtn = document.getElementById('closeOrderDetailsModal');
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            closeOrderDetailsModal();
        });
    }
    
    // Close modal when clicking outside (on overlay)
    const modal = document.getElementById('orderDetailsModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            // Close if clicking on the overlay (not the modal content)
            if (e.target === modal) {
                closeOrderDetailsModal();
            }
        });
    }
    
    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('orderDetailsModal');
            if (modal && modal.style.display !== 'none') {
                closeOrderDetailsModal();
            }
        }
    });
});

/**
 * Open order details modal
 * @param {number} orderId - The order ID
 * @param {boolean} consumer - Whether this is consumer view (true) or establishment view (false)
 */
window.openOrderDetailsModal = function(orderId, consumer = false) {
    currentOrderId = orderId;
    isConsumerView = consumer;
    
    const modal = document.getElementById('orderDetailsModal');
    if (!modal) {
        console.error('Order details modal not found');
        return;
    }

    // Show loading state
    showLoadingState();

    // Show modal
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    // Fetch order details
    fetchOrderDetails(orderId, consumer);
}

/**
 * Close order details modal
 */
window.closeOrderDetailsModal = function() {
    const modal = document.getElementById('orderDetailsModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
    currentOrderId = null;
}

/**
 * Show loading state in modal
 */
function showLoadingState() {
    const loading = document.getElementById('orderDetailsLoading');
    const content = document.getElementById('orderDetailsContent');
    const footer = document.getElementById('orderDetailsModalFooter');
    
    if (loading) loading.style.display = 'block';
    if (content) content.style.display = 'none';
    if (footer) footer.style.display = 'none';
}

/**
 * Fetch order details from API
 */
function fetchOrderDetails(orderId, consumer) {
    const url = consumer 
        ? `/consumer/orders/${orderId}/details`
        : `/establishment/orders/${orderId}/details`;
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                     document.querySelector('input[name="_token"]')?.value;

    fetch(url, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success && data.order) {
            populateOrderDetails(data.order);
        } else {
            showError('Failed to load order details');
        }
    })
    .catch(error => {
        console.error('Error fetching order details:', error);
        showError('An error occurred while loading order details');
    });
}

/**
 * Populate modal with order details
 */
function populateOrderDetails(order) {
    const modalContent = document.getElementById('orderDetailsContent');
    const modalFooter = document.getElementById('orderDetailsModalFooter');
    const loading = document.getElementById('orderDetailsLoading');
    
    if (!modalContent) return;
    
    // Format dates - use the formatted date from backend directly, or format if needed
    const formatDate = (dateString) => {
        if (!dateString) return '-';
        // If already formatted (contains '|'), use it directly
        if (dateString.includes('|')) {
            return dateString;
        }
        // Otherwise, try to format it with timezone conversion
        try {
            const date = new Date(dateString);
            if (isNaN(date.getTime())) return dateString; // Return original if invalid
            // Convert to Asia/Manila timezone
            const options = {
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true,
                timeZone: 'Asia/Manila'
            };
            return date.toLocaleDateString('en-US', options);
        } catch (e) {
            return dateString; // Return original if parsing fails
        }
    };
    
    // Decode URL-encoded strings (remove %20, etc.)
    const decodeName = (name) => {
        if (!name) return '-';
        try {
            return decodeURIComponent(name.replace(/\+/g, ' '));
        } catch (e) {
            // Fallback: simple replace of %20
            return name.replace(/%20/g, ' ').replace(/\+/g, ' ');
        }
    };
    
    // Get status badge class
    const getStatusBadgeClass = (status) => {
        const statusMap = {
            'pending': 'status-pending',
            'pending_delivery_confirmation': 'status-pending',
            'accepted': 'status-accepted',
            'on_the_way': 'status-accepted',
            'completed': 'status-completed',
            'cancelled': 'status-cancelled'
        };
        return statusMap[status?.toLowerCase()] || 'status-pending';
    };
    
    // Build HTML
    let html = '<div class="order-details-content">';
    
    // ORDER INFORMATION Section
    html += '<div class="detail-section">';
    html += '<h3>ORDER INFORMATION</h3>';
    html += `<div class="detail-item"><div class="detail-label">Order ID</div><div class="detail-value">#${order.id} (${order.order_number || 'N/A'})</div></div>`;
    // Format status display
    const getStatusDisplay = (status) => {
        const statusMap = {
            'pending': 'PENDING',
            'pending_delivery_confirmation': 'PENDING DELIVERY CONFIRMATION',
            'accepted': 'ACCEPTED',
            'on_the_way': 'ON THE WAY',
            'completed': 'COMPLETED',
            'cancelled': 'CANCELLED'
        };
        return statusMap[status?.toLowerCase()] || status?.toUpperCase() || 'PENDING';
    };
    
    html += `<div class="detail-item"><div class="detail-label">Status</div><div class="detail-value"><span class="status-badge ${getStatusBadgeClass(order.status)}">${getStatusDisplay(order.status)}</span></div></div>`;
    html += `<div class="detail-item"><div class="detail-label">Placed On</div><div class="detail-value">${formatDate(order.created_at)}</div></div>`;
    if (order.accepted_at) {
        html += `<div class="detail-item"><div class="detail-label">Accepted On</div><div class="detail-value">${formatDate(order.accepted_at)}</div></div>`;
    }
    if (order.out_for_delivery_at) {
        html += `<div class="detail-item"><div class="detail-label">Out for Delivery On</div><div class="detail-value">${formatDate(order.out_for_delivery_at)}</div></div>`;
    }
    if (order.completed_at) {
        html += `<div class="detail-item"><div class="detail-label">Completed On</div><div class="detail-value">${formatDate(order.completed_at)}</div></div>`;
    }
    if (order.cancelled_at) {
        html += `<div class="detail-item"><div class="detail-label">Cancelled On</div><div class="detail-value">${formatDate(order.cancelled_at)}</div></div>`;
    }
    html += '</div>';
    
    // CUSTOMER INFORMATION Section
    html += '<div class="detail-section">';
    html += '<h3>CUSTOMER INFORMATION</h3>';
    const customerName = decodeName(order.customer_name);
    html += `<div class="detail-item"><div class="detail-label">Name</div><div class="detail-value">${escapeHtml(customerName)}</div></div>`;
    html += `<div class="detail-item"><div class="detail-label">Phone</div><div class="detail-value">${order.customer_phone || '-'}</div></div>`;
    html += `<div class="detail-item"><div class="detail-label">Email</div><div class="detail-value">${order.customer_email || '-'}</div></div>`;
    html += '</div>';
    
    // ORDER ITEMS Section
    html += '<div class="detail-section">';
    html += '<h3>ORDER ITEMS</h3>';
    html += '<div class="order-items-list">';
    
    const items = order.items || [];
    if (items.length === 0) {
        html += '<div class="order-item-row"><div class="item-info"><span class="item-name">No items found</span></div></div>';
    } else {
        items.forEach(item => {
            const unitPrice = parseFloat(item.unit_price || 0);
            const quantity = parseInt(item.quantity || 1);
            const totalPrice = parseFloat(item.total_price || unitPrice * quantity);
            
            html += '<div class="order-item-row">';
            html += `<div class="item-info"><span class="item-name">${escapeHtml(item.name || 'Unknown Item')}</span><span class="item-quantity">Qty: ${quantity}</span></div>`;
            html += `<div class="item-price"><span class="item-unit-price">₱ ${unitPrice.toFixed(2)}</span><span class="item-total-price">₱ ${totalPrice.toFixed(2)}</span></div>`;
            html += '</div>';
        });
    }
    
    html += '</div>';
    html += '</div>';
    
    // Summary of Charges Section
    html += '<div class="detail-section">';
    html += '<h3>Summary of Charges</h3>';
    const subtotal = parseFloat(order.subtotal || order.total || 0);
    const total = parseFloat(order.total || subtotal);
    
    html += `<div class="summary-row"><span class="summary-label">Subtotal</span><span class="summary-value">₱ ${subtotal.toFixed(2)}</span></div>`;
    html += `<div class="summary-row total-row"><span class="summary-label">Total</span><span class="summary-value">₱ ${total.toFixed(2)}</span></div>`;
    html += '</div>';
    
    // DELIVERY INFORMATION Section
    html += '<div class="detail-section">';
    html += '<h3>DELIVERY INFORMATION</h3>';
    html += `<div class="detail-item"><div class="detail-label">Method</div><div class="detail-value">${order.delivery_method || 'Pickup'}</div></div>`;
    
    if (order.delivery_method === 'Delivery' || order.delivery_method === 'delivery') {
        if (order.delivery_address) {
            html += `<div class="detail-item"><div class="detail-label">Delivery Address</div><div class="detail-value">${escapeHtml(order.delivery_address)}</div></div>`;
        }
    } else {
        if (order.pickup_start_time && order.pickup_end_time) {
            html += `<div class="detail-item"><div class="detail-label">Pickup Time</div><div class="detail-value">${order.pickup_start_time} - ${order.pickup_end_time}</div></div>`;
        }
    }
    
    html += '</div>';
    
    html += '</div>';
    
    // Set content
    modalContent.innerHTML = html;
    
    // Build footer buttons based on user type and order status
    let footerButtons = '';
    
    if (isConsumerView) {
        // Consumer view buttons
        if ((order.status === 'on_the_way' || order.status === 'pending_delivery_confirmation') && 
            (order.delivery_method === 'Delivery' || order.delivery_method === 'delivery')) {
            footerButtons = '<button class="btn btn-confirm-delivery" onclick="confirmDelivery(' + order.id + ')">Confirm Delivered</button>';
        }
    } else {
        // Establishment view buttons
        if ((order.status === 'pending_delivery_confirmation' || order.status === 'accepted') && 
            (order.delivery_method === 'Delivery' || order.delivery_method === 'delivery')) {
            footerButtons = '<button class="btn btn-out-for-delivery" onclick="markOutForDelivery(' + order.id + ')">Mark Out for Delivery</button>';
        } else if (order.status === 'accepted' && (order.delivery_method === 'Pickup' || order.delivery_method === 'pickup')) {
            footerButtons = '<button class="btn btn-confirm-pickup" onclick="confirmPickup(' + order.id + ')">Pickup Confirmed</button>';
        }
        
        // Request admin intervention button (for delivery orders that are on_the_way or pending_delivery_confirmation after 24 hours)
        if ((order.status === 'on_the_way' || order.status === 'pending_delivery_confirmation') && 
            (order.delivery_method === 'Delivery' || order.delivery_method === 'delivery') &&
            !order.admin_intervention_requested_at) {
            const checkTime = order.out_for_delivery_at || order.accepted_at;
            if (checkTime) {
                const hoursSince = Math.floor((new Date() - new Date(checkTime)) / (1000 * 60 * 60));
                if (hoursSince >= 24) {
                    footerButtons += '<button class="btn btn-request-intervention" onclick="requestAdminIntervention(' + order.id + ')">Request Admin Intervention</button>';
                }
            }
        }
    }
    
    footerButtons += '<button class="btn btn-secondary" onclick="closeOrderDetailsModal()">Close</button>';
    
    if (modalFooter) {
        modalFooter.innerHTML = footerButtons;
        modalFooter.style.display = 'flex';
    }
    
    // Hide loading and show content
    if (loading) loading.style.display = 'none';
    modalContent.style.display = 'block';
}

/**
 * Hide loading state
 */
function hideLoadingState() {
    // This function is kept for compatibility but the new structure handles loading differently
}

/**
 * Update order items list (kept for compatibility)
 */
function updateOrderItems(items) {
    // This function is now handled in populateOrderDetails
}


/**
 * Update element text content
 */
function updateElement(id, value) {
    const element = document.getElementById(id);
    if (element) {
        element.textContent = value || '-';
    }
}

/**
 * Format price
 */
function formatPrice(amount) {
    return `₱ ${parseFloat(amount).toFixed(2)}`;
}

/**
 * Format phone number
 */
function formatPhoneNumber(phone) {
    if (!phone) return '-';
    // Format: +63 | 910 - 025 - 1514
    const cleaned = phone.replace(/\D/g, '');
    if (cleaned.length >= 10) {
        const country = cleaned.substring(0, 2);
        const area = cleaned.substring(2, 5);
        const part1 = cleaned.substring(5, 8);
        const part2 = cleaned.substring(8);
        return `+${country} | ${area} - ${part1} - ${part2}`;
    }
    return phone;
}

/**
 * Get status text
 */
function getStatusText(status, effectiveStatus) {
    const statusMap = {
        'pending': 'Pending Order',
        'accepted': 'Accepted Order',
        'completed': 'Completed Order',
        'cancelled': 'Cancelled Order',
        'missed_pickup': 'Missed Pickup'
    };
    
    return statusMap[effectiveStatus] || statusMap[status] || 'Order';
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Show error message
 */
function showError(message) {
    const loading = document.getElementById('orderDetailsLoading');
    const content = document.getElementById('orderDetailsContent');
    
    if (loading) loading.style.display = 'none';
    if (content) {
        content.innerHTML = `
            <div style="padding: 60px 24px; text-align: center;">
                <p style="color: #dc2626; font-size: 16px; margin-bottom: 12px;">${escapeHtml(message)}</p>
                <button onclick="closeOrderDetailsModal()" style="padding: 8px 16px; background: #4a7c59; color: white; border: none; border-radius: 6px; cursor: pointer;">
                    Close
                </button>
            </div>
        `;
        content.style.display = 'block';
    }
}

// Confirm delivery function (consumer)
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
            closeOrderDetailsModal();
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

// Mark out for delivery function (establishment)
function markOutForDelivery(orderId) {
    if (!confirm('Mark this order as out for delivery?')) {
        return;
    }
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                     document.querySelector('input[name="_token"]')?.value;
    
    fetch(`/establishment/orders/${orderId}/out-for-delivery`, {
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
            alert(data.message || 'Order marked as out for delivery successfully!');
            closeOrderDetailsModal();
            // Refresh the page to show updated order status
            window.location.reload();
        } else {
            alert('Error: ' + (data.error || data.message || 'Failed to mark order as out for delivery'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while marking order as out for delivery');
    });
}

// Request admin intervention function (establishment)
function requestAdminIntervention(orderId) {
    const reason = prompt('Please provide a reason for requesting admin intervention:');
    if (!reason || reason.trim() === '') {
        alert('A reason is required to request admin intervention.');
        return;
    }
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                     document.querySelector('input[name="_token"]')?.value;
    
    fetch(`/establishment/orders/${orderId}/request-admin-intervention`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            reason: reason.trim()
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message || 'Admin intervention requested successfully!');
            closeOrderDetailsModal();
            // Refresh the page
            window.location.reload();
        } else {
            alert('Error: ' + (data.error || data.message || 'Failed to request admin intervention'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while requesting admin intervention');
    });
}

function confirmPickup(orderId) {
    // This would typically call an API endpoint to confirm pickup
    alert('Pickup confirmation functionality to be implemented');
}

