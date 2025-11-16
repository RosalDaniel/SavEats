// Admin Order Management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Modal elements
    const orderDetailsModal = document.getElementById('orderDetailsModal');
    const closeOrderDetailsModal = document.getElementById('closeOrderDetailsModal');
    const closeOrderDetailsModalBtn = document.getElementById('closeOrderDetailsModalBtn');
    
    const forceCancelModal = document.getElementById('forceCancelModal');
    const closeForceCancelModal = document.getElementById('closeForceCancelModal');
    const cancelForceCancelBtn = document.getElementById('cancelForceCancelBtn');
    const confirmForceCancelBtn = document.getElementById('confirmForceCancelBtn');
    const forceCancelForm = document.getElementById('forceCancelForm');
    
    const resolveDisputeModal = document.getElementById('resolveDisputeModal');
    const closeResolveDisputeModal = document.getElementById('closeResolveDisputeModal');
    const cancelResolveDisputeBtn = document.getElementById('cancelResolveDisputeBtn');
    const confirmResolveDisputeBtn = document.getElementById('confirmResolveDisputeBtn');
    const resolveDisputeForm = document.getElementById('resolveDisputeForm');
    
    // Close order details modal handlers
    if (closeOrderDetailsModal) {
        closeOrderDetailsModal.addEventListener('click', function() {
            closeOrderDetailsModalFunc();
        });
    }
    
    if (closeOrderDetailsModalBtn) {
        closeOrderDetailsModalBtn.addEventListener('click', function() {
            closeOrderDetailsModalFunc();
        });
    }
    
    if (orderDetailsModal) {
        orderDetailsModal.addEventListener('click', function(e) {
            if (e.target === orderDetailsModal) {
                closeOrderDetailsModalFunc();
            }
        });
    }
    
    // Close force cancel modal handlers
    if (closeForceCancelModal) {
        closeForceCancelModal.addEventListener('click', function() {
            closeForceCancelModalFunc();
        });
    }
    
    if (cancelForceCancelBtn) {
        cancelForceCancelBtn.addEventListener('click', function() {
            closeForceCancelModalFunc();
        });
    }
    
    if (forceCancelModal) {
        forceCancelModal.addEventListener('click', function(e) {
            if (e.target === forceCancelModal) {
                closeForceCancelModalFunc();
            }
        });
    }
    
    // Force cancel form submission
    if (confirmForceCancelBtn) {
        confirmForceCancelBtn.addEventListener('click', function() {
            if (forceCancelForm) {
                forceCancelForm.dispatchEvent(new Event('submit'));
            }
        });
    }
    
    if (forceCancelForm) {
        forceCancelForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitForceCancel();
        });
    }
    
    // Close resolve dispute modal handlers
    if (closeResolveDisputeModal) {
        closeResolveDisputeModal.addEventListener('click', function() {
            closeResolveDisputeModalFunc();
        });
    }
    
    if (cancelResolveDisputeBtn) {
        cancelResolveDisputeBtn.addEventListener('click', function() {
            closeResolveDisputeModalFunc();
        });
    }
    
    if (resolveDisputeModal) {
        resolveDisputeModal.addEventListener('click', function(e) {
            if (e.target === resolveDisputeModal) {
                closeResolveDisputeModalFunc();
            }
        });
    }
    
    // Resolve dispute form submission
    if (confirmResolveDisputeBtn) {
        confirmResolveDisputeBtn.addEventListener('click', function() {
            if (resolveDisputeForm) {
                resolveDisputeForm.dispatchEvent(new Event('submit'));
            }
        });
    }
    
    if (resolveDisputeForm) {
        resolveDisputeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitResolveDispute();
        });
    }
    
    // Automatic filtering
    initializeAutoFilter();
    
    // Apply initial filters if any are set
    applyFilters();
});

// Initialize automatic filtering
function initializeAutoFilter() {
    const searchInput = document.getElementById('search');
    const statusSelect = document.getElementById('status');
    const dateSelect = document.getElementById('date');
    
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                applyFilters();
            }, 300);
        });
    }
    
    if (statusSelect) {
        statusSelect.addEventListener('change', applyFilters);
    }
    
    if (dateSelect) {
        dateSelect.addEventListener('change', applyFilters);
    }
}

// Apply filters automatically
function applyFilters() {
    const searchInput = document.getElementById('search');
    const statusSelect = document.getElementById('status');
    const tableBody = document.getElementById('ordersTableBody');
    
    if (!tableBody) return;
    
    const searchValue = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const statusValue = statusSelect ? statusSelect.value : 'all';
    
    const rows = tableBody.querySelectorAll('tr');
    let visibleCount = 0;
    
    rows.forEach(function(row) {
        if (row.classList.contains('no-orders')) {
            row.style.display = 'none';
            return;
        }
        
        const searchText = row.getAttribute('data-search-text') || '';
        const status = row.getAttribute('data-status') || 'pending';
        
        // Check search filter
        const matchesSearch = !searchValue || searchText.includes(searchValue);
        
        // Check status filter
        const matchesStatus = statusValue === 'all' || status === statusValue;
        
        // Show/hide row based on filters
        if (matchesSearch && matchesStatus) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update count
    const tableHeader = document.getElementById('ordersCountHeader');
    if (tableHeader) {
        const totalRows = Array.from(rows).filter(row => !row.classList.contains('no-orders')).length;
        if (visibleCount === totalRows) {
            tableHeader.textContent = `All Orders (${visibleCount})`;
        } else {
            tableHeader.textContent = `All Orders (${visibleCount} of ${totalRows})`;
        }
    }
    
    // Show "no orders" message if no rows are visible
    const noOrdersRow = tableBody.querySelector('.no-orders');
    if (visibleCount === 0 && !noOrdersRow) {
        const newRow = document.createElement('tr');
        newRow.className = 'no-orders';
        newRow.innerHTML = '<td colspan="9" class="no-orders">No orders found matching the filters.</td>';
        tableBody.appendChild(newRow);
    } else if (visibleCount > 0 && noOrdersRow) {
        noOrdersRow.remove();
    }
}

// Clear filters
function clearFilters() {
    window.location.href = '/admin/orders';
}

// View order details
function viewOrderDetails(orderId, orderData) {
    const modal = document.getElementById('orderDetailsModal');
    const modalBody = document.getElementById('orderDetailsModalBody');
    
    if (!modal || !modalBody) return;
    
    let html = '<div class="order-details-grid">';
    
    // Order Information
    html += '<div class="detail-section">';
    html += '<h3>Order Information</h3>';
    html += `<div class="detail-item"><div class="detail-label">Order Number</div><div class="detail-value">${orderData.order_number}</div></div>`;
    html += `<div class="detail-item"><div class="detail-label">Status</div><div class="detail-value"><span class="status-badge status-${orderData.status}">${orderData.status}</span></div></div>`;
    html += `<div class="detail-item"><div class="detail-label">Delivery Method</div><div class="detail-value">${orderData.delivery_method || 'N/A'}</div></div>`;
    html += `<div class="detail-item"><div class="detail-label">Payment Method</div><div class="detail-value">${orderData.payment_method || 'N/A'}</div></div>`;
    html += `<div class="detail-item"><div class="detail-label">Created</div><div class="detail-value">${new Date(orderData.created_at).toLocaleString()}</div></div>`;
    if (orderData.accepted_at) {
        html += `<div class="detail-item"><div class="detail-label">Accepted</div><div class="detail-value">${new Date(orderData.accepted_at).toLocaleString()}</div></div>`;
    }
    if (orderData.completed_at) {
        html += `<div class="detail-item"><div class="detail-label">Completed</div><div class="detail-value">${new Date(orderData.completed_at).toLocaleString()}</div></div>`;
    }
    if (orderData.cancelled_at) {
        html += `<div class="detail-item"><div class="detail-label">Cancelled</div><div class="detail-value">${new Date(orderData.cancelled_at).toLocaleString()}</div></div>`;
        if (orderData.cancellation_reason) {
            html += `<div class="detail-item"><div class="detail-label">Cancellation Reason</div><div class="detail-value">${orderData.cancellation_reason}</div></div>`;
        }
    }
    html += '</div>';
    
    // Customer Information
    html += '<div class="detail-section">';
    html += '<h3>Customer Information</h3>';
    html += `<div class="detail-item"><div class="detail-label">Name</div><div class="detail-value">${orderData.customer_name || 'N/A'}</div></div>`;
    html += `<div class="detail-item"><div class="detail-label">Phone</div><div class="detail-value">${orderData.customer_phone || 'N/A'}</div></div>`;
    if (orderData.consumer) {
        html += `<div class="detail-item"><div class="detail-label">Consumer Account</div><div class="detail-value">${orderData.consumer.name} (${orderData.consumer.email})</div></div>`;
    }
    if (orderData.delivery_address) {
        html += `<div class="detail-item"><div class="detail-label">Delivery Address</div><div class="detail-value">${orderData.delivery_address}</div></div>`;
    }
    if (orderData.pickup_start_time && orderData.pickup_end_time) {
        html += `<div class="detail-item"><div class="detail-label">Pickup Time</div><div class="detail-value">${orderData.pickup_start_time} - ${orderData.pickup_end_time}</div></div>`;
    }
    html += '</div>';
    
    // Establishment Information
    if (orderData.establishment) {
        html += '<div class="detail-section">';
        html += '<h3>Establishment</h3>';
        html += `<div class="detail-item"><div class="detail-label">Name</div><div class="detail-value">${orderData.establishment.name}</div></div>`;
        html += `<div class="detail-item"><div class="detail-label">Email</div><div class="detail-value">${orderData.establishment.email}</div></div>`;
        if (orderData.establishment.phone) {
            html += `<div class="detail-item"><div class="detail-label">Phone</div><div class="detail-value">${orderData.establishment.phone}</div></div>`;
        }
        html += '</div>';
    }
    
    // Item Information
    if (orderData.food_listing) {
        html += '<div class="detail-section">';
        html += '<h3>Item Details</h3>';
        html += `<div class="detail-item"><div class="detail-label">Name</div><div class="detail-value">${orderData.food_listing.name}</div></div>`;
        html += `<div class="detail-item"><div class="detail-label">Category</div><div class="detail-value">${orderData.food_listing.category || 'N/A'}</div></div>`;
        html += `<div class="detail-item"><div class="detail-label">Quantity</div><div class="detail-value">${orderData.quantity}</div></div>`;
        html += `<div class="detail-item"><div class="detail-label">Unit Price</div><div class="detail-value">₱${parseFloat(orderData.unit_price).toFixed(2)}</div></div>`;
        html += `<div class="detail-item"><div class="detail-label">Total Price</div><div class="detail-value">₱${parseFloat(orderData.total_price).toFixed(2)}</div></div>`;
        html += '</div>';
    }
    
    html += '</div>';
    
    modalBody.innerHTML = html;
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

// Close order details modal
function closeOrderDetailsModalFunc() {
    const modal = document.getElementById('orderDetailsModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

// Force cancel
function forceCancel(orderId) {
    const modal = document.getElementById('forceCancelModal');
    const form = document.getElementById('forceCancelForm');
    
    if (!modal || !form) return;
    
    document.getElementById('cancelOrderId').value = orderId;
    form.reset();
    document.getElementById('cancelOrderId').value = orderId;
    
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

// Close force cancel modal
function closeForceCancelModalFunc() {
    const modal = document.getElementById('forceCancelModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
    
    const form = document.getElementById('forceCancelForm');
    if (form) {
        form.reset();
    }
}

// Submit force cancel
function submitForceCancel() {
    const orderId = document.getElementById('cancelOrderId').value;
    const reason = document.getElementById('cancelReason').value;
    
    if (!orderId || !reason.trim()) {
        showToast('Please provide a cancellation reason', 'error');
        return;
    }
    
    // Show loading state
    const confirmBtn = document.getElementById('confirmForceCancelBtn');
    const originalText = confirmBtn ? confirmBtn.textContent : 'Force Cancel Order';
    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.textContent = 'Cancelling...';
    }
    
    fetch(`/admin/orders/${orderId}/force-cancel`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ reason: reason })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Order cancelled successfully', 'success');
            closeForceCancelModalFunc();
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast(data.message || 'Failed to cancel order', 'error');
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.textContent = originalText;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while cancelling the order', 'error');
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.textContent = originalText;
        }
    });
}

// Resolve dispute
function resolveDispute(orderId) {
    const modal = document.getElementById('resolveDisputeModal');
    const form = document.getElementById('resolveDisputeForm');
    
    if (!modal || !form) return;
    
    document.getElementById('disputeOrderId').value = orderId;
    form.reset();
    document.getElementById('disputeOrderId').value = orderId;
    
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

// Close resolve dispute modal
function closeResolveDisputeModalFunc() {
    const modal = document.getElementById('resolveDisputeModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
    
    const form = document.getElementById('resolveDisputeForm');
    if (form) {
        form.reset();
    }
}

// Submit resolve dispute
function submitResolveDispute() {
    const orderId = document.getElementById('disputeOrderId').value;
    const resolution = document.getElementById('disputeResolution').value;
    const notes = document.getElementById('disputeNotes').value;
    
    if (!orderId || !resolution) {
        showToast('Please select a resolution', 'error');
        return;
    }
    
    // Show loading state
    const confirmBtn = document.getElementById('confirmResolveDisputeBtn');
    const originalText = confirmBtn ? confirmBtn.textContent : 'Resolve Dispute';
    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.textContent = 'Resolving...';
    }
    
    fetch(`/admin/orders/${orderId}/resolve-dispute`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ 
            resolution: resolution,
            notes: notes 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Dispute resolved successfully', 'success');
            closeResolveDisputeModalFunc();
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast(data.message || 'Failed to resolve dispute', 'error');
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.textContent = originalText;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while resolving the dispute', 'error');
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.textContent = originalText;
        }
    });
}

// Toast notification
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => toast.classList.add('show'), 10);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

