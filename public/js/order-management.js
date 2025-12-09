// Order Management JavaScript

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

// Order action functions
function acceptOrder(orderId) {
    if (confirm('Are you sure you want to accept this order?')) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                         document.querySelector('input[name="_token"]')?.value;
        
        fetch(`/establishment/orders/${orderId}/accept`, {
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
                alert('Order accepted successfully!');
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Failed to accept order'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while accepting the order');
        });
    }
}

function cancelOrder(orderId) {
    if (confirm('Are you sure you want to cancel this order? The quantity will be restored to the food listing.')) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                         document.querySelector('input[name="_token"]')?.value;
        
        fetch(`/establishment/orders/${orderId}/cancel`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                reason: 'Cancelled by establishment'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Order cancelled and quantity restored successfully!');
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Failed to cancel order'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while cancelling the order');
        });
    }
}

function markComplete(orderId) {
    if (confirm('Are you sure you want to mark this order as complete?')) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                         document.querySelector('input[name="_token"]')?.value;
        
        fetch(`/establishment/orders/${orderId}/complete`, {
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
                alert('Order marked as complete successfully!');
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Failed to mark order as complete'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while marking the order as complete');
        });
    }
}

function handleMissedPickup(orderId) {
    if (confirm('This order has missed its pickup time. Do you want to cancel and refund this order? The quantity will be restored to the food listing.')) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                         document.querySelector('input[name="_token"]')?.value;
        
        fetch(`/establishment/orders/${orderId}/cancel`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                reason: 'Missed pickup - Cancelled and refunded by establishment'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Order cancelled and refunded. Quantity has been restored to the food listing.');
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Failed to cancel order'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while cancelling the order');
        });
    }
}

function viewOrderDetails(orderId) {
    const modal = document.getElementById('orderDetailsModal');
    const modalBody = document.getElementById('orderDetailsModalBody');
    const modalContent = document.getElementById('orderDetailsContent');
    const modalFooter = document.getElementById('orderDetailsModalFooter');
    const loading = document.getElementById('orderDetailsLoading');
    const closeBtn = document.getElementById('closeOrderDetailsModal');
    
    // Show modal and loading state
    modal.style.display = 'flex';
    loading.style.display = 'block';
    modalContent.style.display = 'none';
    modalFooter.style.display = 'none';
    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                     document.querySelector('input[name="_token"]')?.value;
    
    // Fetch order details
    fetch(`/establishment/orders/${orderId}/details`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.order) {
            const order = data.order;
            
            // Build order details HTML
            let html = `
                <div class="order-details-content">
                    <!-- Order Info Section -->
                    <div class="detail-section">
                        <h3>Order Information</h3>
                        <div class="detail-item">
                            <span class="detail-label">Order ID:</span>
                            <span class="detail-value">#${order.id} (${order.order_number || 'N/A'})</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Status:</span>
                            <span class="detail-value status-badge status-${order.status}">${order.status.charAt(0).toUpperCase() + order.status.slice(1)}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Placed On:</span>
                            <span class="detail-value">${order.created_at}</span>
                        </div>
                        ${order.accepted_at ? `
                        <div class="detail-item">
                            <span class="detail-label">Accepted On:</span>
                            <span class="detail-value">${order.accepted_at}</span>
                        </div>
                        ` : ''}
                        ${order.completed_at ? `
                        <div class="detail-item">
                            <span class="detail-label">Completed On:</span>
                            <span class="detail-value">${order.completed_at}</span>
                        </div>
                        ` : ''}
                        ${order.cancelled_at ? `
                        <div class="detail-item">
                            <span class="detail-label">Cancelled On:</span>
                            <span class="detail-value">${order.cancelled_at}</span>
                        </div>
                        ` : ''}
                    </div>
                    
                    <!-- Customer Info Section -->
                    <div class="detail-section">
                        <h3>Customer Information</h3>
                        <div class="detail-item">
                            <span class="detail-label">Name:</span>
                            <span class="detail-value">${order.customer_name}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Phone:</span>
                            <span class="detail-value">${order.customer_phone}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value">${order.customer_email}</span>
                        </div>
                    </div>
                    
                    <!-- Items Section -->
                    <div class="detail-section">
                        <h3>Order Items</h3>
                        <div class="order-items-list">
            `;
            
            order.items.forEach(item => {
                html += `
                    <div class="order-item-row">
                        <div class="item-info">
                            <span class="item-name">${item.name}</span>
                            <span class="item-quantity">Qty: ${item.quantity}</span>
                        </div>
                        <div class="item-price">
                            <span class="item-unit-price">₱ ${parseFloat(item.unit_price).toFixed(2)}</span>
                            <span class="item-total-price">₱ ${parseFloat(item.total_price).toFixed(2)}</span>
                        </div>
                    </div>
                `;
            });
            
            html += `
                        </div>
                        <div class="order-summary">
                            <div class="summary-row">
                                <span class="summary-label">Subtotal:</span>
                                <span class="summary-value">₱ ${parseFloat(order.subtotal).toFixed(2)}</span>
                            </div>
                            ${order.delivery_method === 'Delivery' ? `
                            <div class="summary-row">
                                <span class="summary-label">Delivery Fee:</span>
                                <span class="summary-value">₱ ${parseFloat(order.delivery_fee_amount || 0).toFixed(2)}</span>
                            </div>
                            ` : ''}
                            <div class="summary-row total-row">
                                <span class="summary-label">Total:</span>
                                <span class="summary-value">₱ ${parseFloat(order.total).toFixed(2)}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Delivery/Pickup Info Section -->
                    <div class="detail-section">
                        <h3>${order.delivery_method === 'Delivery' ? 'Delivery' : 'Pickup'} Information</h3>
                        <div class="detail-item">
                            <span class="detail-label">Method:</span>
                            <span class="detail-value">${order.delivery_method}</span>
                        </div>
            `;
            
            if (order.delivery_method === 'Delivery') {
                html += `
                        <div class="detail-item">
                            <span class="detail-label">Delivery Address:</span>
                            <span class="detail-value">${order.delivery_address}</span>
                        </div>
                        ${order.delivery_lat && order.delivery_lng ? `
                        <div class="detail-item full-width">
                            <span class="detail-label">Delivery Location:</span>
                            <div id="deliveryMapContainer" style="width: 100%; height: 300px; margin-top: 12px; border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb;"></div>
                        </div>
                        ` : ''}
                        <div class="detail-item">
                            <span class="detail-label">Distance:</span>
                            <span class="detail-value">${order.delivery_distance}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Delivery Fee:</span>
                            <span class="detail-value">₱ ${parseFloat(order.delivery_fee_amount || 0).toFixed(2)}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Estimated Time:</span>
                            <span class="detail-value">${order.delivery_eta}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Delivery Instructions:</span>
                            <span class="detail-value">${order.delivery_instructions}</span>
                        </div>
                `;
            } else {
                html += `
                        <div class="detail-item">
                            <span class="detail-label">Pickup Date:</span>
                            <span class="detail-value">${order.pickup_date || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Pickup Time:</span>
                            <span class="detail-value">${order.pickup_time_range || order.pickup_end_time || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Store Address:</span>
                            <span class="detail-value">${order.store_address}</span>
                        </div>
                `;
            }
            
            html += `
                    </div>
                    
                    <!-- Payment Info Section -->
                    <div class="detail-section">
                        <h3>Payment Information</h3>
                        <div class="detail-item">
                            <span class="detail-label">Payment Method:</span>
                            <span class="detail-value">${order.payment_method}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Payment Status:</span>
                            <span class="detail-value">${order.payment_status}</span>
                        </div>
                    </div>
                </div>
            `;
            
            // Set content
            modalContent.innerHTML = html;
            
            // Initialize map for delivery orders if coordinates are available
            if (order.delivery_method === 'Delivery' && order.delivery_lat && order.delivery_lng) {
                // Wait a bit for the DOM to update
                setTimeout(() => {
                    initializeDeliveryMap(order.delivery_lat, order.delivery_lng, order.delivery_address);
                }, 100);
            }
            
            // Build action buttons based on order status
            let actionButtons = '';
            if (order.status === 'pending' && !order.is_missed_pickup) {
                actionButtons = `
                    <button class="btn btn-accept" onclick="acceptOrderFromModal('${order.id}')">Accept Order</button>
                    <button class="btn btn-cancel" onclick="cancelOrderFromModal('${order.id}')">Cancel Order</button>
                `;
            } else if ((order.status === 'pending_delivery_confirmation' || order.status === 'accepted') && 
                       (order.delivery_method === 'Delivery' || order.delivery_method === 'delivery') && 
                       !order.is_missed_pickup) {
                // For delivery orders, show "Mark Out for Delivery" button
                actionButtons = `
                    <button class="btn btn-out-for-delivery" onclick="markOutForDeliveryFromModal('${order.id}')">Mark Out for Delivery</button>
                `;
            } else if (order.status === 'accepted' && 
                       (order.delivery_method === 'Pickup' || order.delivery_method === 'pickup') && 
                       !order.is_missed_pickup) {
                // For pickup orders, show "Pick-Up Confirmed" button
                actionButtons = `
                    <button class="btn btn-confirm-pickup" onclick="markCompleteFromModal('${order.id}')">Pick-Up Confirmed</button>
                `;
            } else if (order.status === 'on_the_way' && 
                       (order.delivery_method === 'Delivery' || order.delivery_method === 'delivery') && 
                       !order.is_missed_pickup) {
                // For orders out for delivery, show "Request Admin Intervention" if 24+ hours have passed
                const checkTime = order.out_for_delivery_at || order.accepted_at;
                if (checkTime) {
                    const hoursSince = Math.floor((new Date() - new Date(checkTime)) / (1000 * 60 * 60));
                    if (hoursSince >= 24 && !order.admin_intervention_requested_at) {
                        actionButtons = `
                            <button class="btn btn-request-intervention" onclick="requestAdminInterventionFromModal('${order.id}')">Request Admin Intervention</button>
                        `;
                    }
                }
            } else if (order.is_missed_pickup) {
                actionButtons = `
                    <button class="btn btn-cancel" onclick="handleMissedPickupFromModal('${order.id}')" style="background-color: #DD5D36;">
                        Cancel & Refund
                    </button>
                `;
            }
            
            if (actionButtons) {
                modalFooter.innerHTML = actionButtons;
                modalFooter.style.display = 'flex';
            } else {
                modalFooter.style.display = 'none';
            }
            
            // Show content
            loading.style.display = 'none';
            modalContent.style.display = 'block';
        } else {
            loading.innerHTML = '<p style="color: #dc3545;">Error loading order details. Please try again.</p>';
        }
    })
    .catch(error => {
        console.error('Error fetching order details:', error);
        loading.innerHTML = '<p style="color: #dc3545;">An error occurred while loading order details.</p>';
    });
    
    // Close modal handlers
    closeBtn.onclick = function() {
        closeOrderDetailsModal();
    };
    
    modal.onclick = function(event) {
        if (event.target === modal) {
            closeOrderDetailsModal();
        }
    };
}

// Helper functions for modal action buttons
function acceptOrderFromModal(orderId) {
    document.getElementById('orderDetailsModal').style.display = 'none';
    acceptOrder(orderId);
}

function cancelOrderFromModal(orderId) {
    document.getElementById('orderDetailsModal').style.display = 'none';
    cancelOrder(orderId);
}

function markCompleteFromModal(orderId) {
    document.getElementById('orderDetailsModal').style.display = 'none';
    markComplete(orderId);
}

function handleMissedPickupFromModal(orderId) {
    document.getElementById('orderDetailsModal').style.display = 'none';
    handleMissedPickup(orderId);
}

function markOutForDeliveryFromModal(orderId) {
    document.getElementById('orderDetailsModal').style.display = 'none';
    markOutForDelivery(orderId);
}

function requestAdminInterventionFromModal(orderId) {
    document.getElementById('orderDetailsModal').style.display = 'none';
    requestAdminIntervention(orderId);
}

// Mark order as out for delivery
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
            location.reload();
        } else {
            alert('Error: ' + (data.error || data.message || 'Failed to mark order as out for delivery'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while marking order as out for delivery');
    });
}

// Request admin intervention
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
            location.reload();
        } else {
            alert('Error: ' + (data.error || data.message || 'Failed to request admin intervention'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while requesting admin intervention');
    });
}

// Global variable to store the delivery map instance
let deliveryMapInstance = null;

// Initialize delivery map in modal
function initializeDeliveryMap(lat, lng, address) {
    const mapContainer = document.getElementById('deliveryMapContainer');
    if (!mapContainer) {
        console.error('Delivery map container not found');
        return;
    }
    
    // Destroy existing map if it exists
    if (deliveryMapInstance) {
        deliveryMapInstance.remove();
        deliveryMapInstance = null;
    }
    
    // Validate coordinates
    if (!lat || !lng || isNaN(parseFloat(lat)) || isNaN(parseFloat(lng))) {
        console.error('Invalid coordinates:', lat, lng);
        mapContainer.innerHTML = '<p style="padding: 20px; text-align: center; color: #6b7280;">Map location not available</p>';
        return;
    }
    
    const deliveryLat = parseFloat(lat);
    const deliveryLng = parseFloat(lng);
    
    try {
        // Initialize map centered on delivery location
        deliveryMapInstance = L.map('deliveryMapContainer', {
            zoomControl: true
        }).setView([deliveryLat, deliveryLng], 15);
        
        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(deliveryMapInstance);
        
        // Add marker at delivery location
        const deliveryMarker = L.marker([deliveryLat, deliveryLng], {
            draggable: false
        }).addTo(deliveryMapInstance);
        
        // Bind popup with address
        const popupContent = address ? `<b>Delivery Location</b><br>${address}` : '<b>Delivery Location</b>';
        deliveryMarker.bindPopup(popupContent).openPopup();
        
        // Fit map to show marker with some padding
        deliveryMapInstance.fitBounds([[deliveryLat, deliveryLng]], {
            padding: [20, 20],
            maxZoom: 17
        });
        
        // Invalidate size to ensure proper rendering in modal
        setTimeout(() => {
            if (deliveryMapInstance) {
                deliveryMapInstance.invalidateSize();
            }
        }, 200);
        
    } catch (error) {
        console.error('Error initializing delivery map:', error);
        mapContainer.innerHTML = '<p style="padding: 20px; text-align: center; color: #dc3545;">Error loading map</p>';
    }
}

// Clean up map when modal closes
function closeOrderDetailsModal() {
    const modal = document.getElementById('orderDetailsModal');
    if (modal) {
        modal.style.display = 'none';
    }
    
    // Destroy map instance when modal closes
    if (deliveryMapInstance) {
        deliveryMapInstance.remove();
        deliveryMapInstance = null;
    }
}