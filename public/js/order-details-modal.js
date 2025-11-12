// Order Details Modal JavaScript

let currentOrderId = null;
let isConsumerView = false;

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
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';

    // Set focus to modal for accessibility
    const closeButton = modal.querySelector('.order-details-modal-close');
    if (closeButton) {
        setTimeout(() => closeButton.focus(), 100);
    }

    // Fetch order details
    fetchOrderDetails(orderId, consumer);
}

/**
 * Close order details modal
 */
window.closeOrderDetailsModal = function() {
    const modal = document.getElementById('orderDetailsModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
    currentOrderId = null;
}

/**
 * Show loading state in modal
 */
function showLoadingState() {
    const content = document.querySelector('.order-details-modal-content');
    if (content) {
        // Hide all sections and show loading
        const sections = content.querySelectorAll('.order-status-header, .order-info-section, .order-items-section, .order-summary-section, .pickup-info-section, .order-note-section');
        sections.forEach(section => {
            section.style.display = 'none';
        });
        
        // Add loading overlay
        let loadingDiv = content.querySelector('.loading-overlay');
        if (!loadingDiv) {
            loadingDiv = document.createElement('div');
            loadingDiv.className = 'loading-overlay';
            loadingDiv.style.cssText = 'position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.9); z-index: 100;';
            loadingDiv.innerHTML = '<div class="loading-spinner"></div>';
            content.style.position = 'relative';
            content.appendChild(loadingDiv);
        } else {
            loadingDiv.style.display = 'flex';
        }
    }
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
    // Get status text
    const statusText = getStatusText(order.status, order.effective_status);
    
    // Update status header
    const statusTextEl = document.getElementById('orderStatusText');
    if (statusTextEl) {
        statusTextEl.textContent = statusText;
    }

    // Update order information
    updateElement('orderNumber', order.order_number || `ID#${order.id}`);
    updateElement('orderDateTime', order.created_at || '-');
    updateElement('paymentMethod', order.payment_method || '-');
    updateElement('customerName', order.customer_name || '-');
    updateElement('customerPhone', formatPhoneNumber(order.customer_phone) || '-');
    updateElement('customerEmail', order.customer_email || '-');

    // Update order items
    updateOrderItems(order.items || []);

    // Update summary
    updateElement('deliveryFee', formatPrice(order.delivery_fee || 0));
    updateElement('totalAmount', formatPrice(order.total || 0));

    // Update pickup information
    updateElement('deliveryMethod', order.delivery_method || '-');
    updateElement('storeName', order.store_name || '-');
    updateElement('storeAddress', order.store_address || '-');

    // Hide loading and show content
    hideLoadingState();
}

/**
 * Hide loading state
 */
function hideLoadingState() {
    const content = document.querySelector('.order-details-modal-content');
    if (content) {
        // Remove loading overlay
        const loadingDiv = content.querySelector('.loading-overlay');
        if (loadingDiv) {
            loadingDiv.remove();
        }
        
        // Show all sections
        const sections = content.querySelectorAll('.order-status-header, .order-info-section, .order-items-section, .order-summary-section, .pickup-info-section, .order-note-section');
        sections.forEach(section => {
            section.style.display = '';
        });
    }
}

/**
 * Update order items list
 */
function updateOrderItems(items) {
    const itemsList = document.getElementById('orderItemsList');
    if (!itemsList) return;

    if (items.length === 0) {
        itemsList.innerHTML = '<div class="order-item-row"><span class="item-col-name">No items found</span></div>';
        return;
    }

    itemsList.innerHTML = items.map(item => `
        <div class="order-item-row">
            <span class="item-col-name">${escapeHtml(item.name || 'Unknown Item')}</span>
            <span class="item-col-qty">${item.quantity || 0}</span>
            <span class="item-col-price">${formatPrice(item.total_price || item.unit_price * (item.quantity || 0))}</span>
        </div>
    `).join('');
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
    hideLoadingState();
    const content = document.querySelector('.order-details-modal-content');
    if (content) {
        // Hide all sections
        const sections = content.querySelectorAll('.order-status-header, .order-info-section, .order-items-section, .order-summary-section, .pickup-info-section, .order-note-section');
        sections.forEach(section => {
            section.style.display = 'none';
        });
        
        // Show error message
        let errorDiv = content.querySelector('.error-message');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.style.cssText = 'padding: 60px 24px; text-align: center;';
            content.appendChild(errorDiv);
        }
        errorDiv.innerHTML = `
            <p style="color: #dc2626; font-size: 16px; margin-bottom: 12px;">${escapeHtml(message)}</p>
            <button onclick="closeOrderDetailsModal()" style="padding: 8px 16px; background: #4a7c59; color: white; border: none; border-radius: 6px; cursor: pointer;">
                Close
            </button>
        `;
        errorDiv.style.display = 'block';
    }
}

// Close modal on overlay click and handle keyboard navigation
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('orderDetailsModal');
    if (modal) {
        const overlay = modal.querySelector('.order-details-modal-overlay');
        if (overlay) {
            overlay.addEventListener('click', window.closeOrderDetailsModal);
        }

        // Trap focus within modal when open
        const modalContainer = modal.querySelector('.order-details-modal-container');
        if (modalContainer) {
            const focusableElements = modalContainer.querySelectorAll(
                'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
            );
            const firstElement = focusableElements[0];
            const lastElement = focusableElements[focusableElements.length - 1];

            modalContainer.addEventListener('keydown', function(e) {
                if (e.key === 'Tab') {
                    if (e.shiftKey) {
                        if (document.activeElement === firstElement) {
                            e.preventDefault();
                            lastElement.focus();
                        }
                    } else {
                        if (document.activeElement === lastElement) {
                            e.preventDefault();
                            firstElement.focus();
                        }
                    }
                }
            });
        }

        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('show')) {
                window.closeOrderDetailsModal();
            }
        });
    }
});

