// Order Confirmation Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Get URL parameters to populate order data
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('id');
    const quantity = urlParams.get('quantity') || 1;
    
    // Initialize the page
    initializeOrderConfirmation();
    
    // Setup event listeners
    setupEventListeners();
    
    // Load product data if ID is provided
    if (productId) {
        loadProductData(productId, quantity);
    }
});

function initializeOrderConfirmation() {
    // Get the actual product price from the page
    const currentPriceElement = document.getElementById('currentPrice');
    const quantityElement = document.getElementById('itemQuantity');
    
    if (currentPriceElement && quantityElement) {
        const unitPrice = parseFloat(currentPriceElement.textContent);
        const quantity = parseInt(quantityElement.textContent) || 1;
        updatePriceBreakdown(quantity, unitPrice);
    }
}

function setupEventListeners() {
    // Receive method buttons
    const pickupBtn = document.getElementById('pickupBtn');
    const deliveryBtn = document.getElementById('deliveryBtn');
    
    pickupBtn?.addEventListener('click', () => selectReceiveMethod('pickup'));
    deliveryBtn?.addEventListener('click', () => selectReceiveMethod('delivery'));
    
    // Proceed to payment button
    const proceedBtn = document.getElementById('proceedPaymentBtn');
    proceedBtn?.addEventListener('click', handleProceedToPayment);
    
    // Time input validation
    const startTimeSelect = document.getElementById('startTime');
    const endTimeSelect = document.getElementById('endTime');
    
    startTimeSelect?.addEventListener('change', () => validateTimeRange());
    endTimeSelect?.addEventListener('change', () => validateTimeRange());
}

function selectReceiveMethod(method) {
    const pickupBtn = document.getElementById('pickupBtn');
    const deliveryBtn = document.getElementById('deliveryBtn');
    
    // Remove active class from all buttons
    pickupBtn?.classList.remove('active');
    deliveryBtn?.classList.remove('active');
    
    // Add active class to selected button
    if (method === 'pickup') {
        pickupBtn?.classList.add('active');
    } else {
        deliveryBtn?.classList.add('active');
    }
    
    // Update UI based on selection
    updateReceiveMethodUI(method);
}

function updateReceiveMethodUI(method) {
    // You can add logic here to show/hide different sections based on method
    console.log(`Selected receive method: ${method}`);
}

function validateTimeRange() {
    const startTime = document.getElementById('startTime').value;
    const endTime = document.getElementById('endTime').value;
    
    if (startTime && endTime) {
        const start = new Date(`2000-01-01T${startTime}`);
        const end = new Date(`2000-01-01T${endTime}`);
        
        if (start >= end) {
            showNotification('End time must be after start time', 'error');
            document.getElementById('endTime').value = '';
        }
    }
}

function loadProductData(productId, quantity) {
    // The product data is already populated from the server-side Blade template
    // We just need to update the price breakdown with the correct quantity
    const currentPriceElement = document.getElementById('currentPrice');
    if (currentPriceElement) {
        const unitPrice = parseFloat(currentPriceElement.textContent);
        updatePriceBreakdown(quantity, unitPrice);
    }
}

function populateProductInfo(data, quantity) {
    // Product header
    document.getElementById('productName').textContent = data.name;
    document.getElementById('bakeryName').textContent = data.bakery;
    
    // Product image
    document.getElementById('productImage').src = data.image;
    document.getElementById('productImage').alt = data.name;
    
    // Pricing
    document.getElementById('currentPrice').textContent = data.currentPrice.toFixed(2);
    document.getElementById('originalPrice').textContent = data.originalPrice.toFixed(2);
    
    // Show/hide discount badge
    const discountBadge = document.getElementById('discountBadge');
    if (data.discount > 0) {
        discountBadge.textContent = `${data.discount}% off`;
        discountBadge.style.display = 'block';
    } else {
        discountBadge.style.display = 'none';
    }
    
    // Product details
    document.getElementById('location').textContent = data.location;
    document.getElementById('pickupOption').textContent = data.pickupOption;
    document.getElementById('expiryDate').textContent = `Expiry Date: ${data.expiryDate}`;
    document.getElementById('operatingHours').textContent = data.operatingHours;
    
    // Bakery contact details
    document.getElementById('bakeryContactName').textContent = data.bakeryContact;
    document.getElementById('bakeryAddress').textContent = data.bakeryAddress;
    document.getElementById('bakeryPhone').textContent = data.bakeryPhone;
    
    // Update price breakdown
    updatePriceBreakdown(quantity, data.currentPrice);
}

function updatePriceBreakdown(quantity, unitPrice) {
    const totalPrice = quantity * unitPrice;
    
    document.getElementById('itemQuantity').textContent = quantity;
    document.getElementById('itemPrice').textContent = `₱ ${unitPrice.toFixed(2)}`;
    document.getElementById('totalPrice').textContent = `₱ ${totalPrice.toFixed(2)}`;
}

function handleProceedToPayment() {
    // Validate form
    if (!validateForm()) {
        return;
    }
    
    // Get form data
    const orderData = collectOrderData();
    
    // In a real app, this would submit the order to the server
    console.log('Order data:', orderData);
    
    // Show success message
    showNotification('Redirecting to payment...', 'success');
    
    // Redirect to payment options page
    setTimeout(() => {
        window.location.href = '/consumer/payment-options';
    }, 1500);
}

function validateForm() {
    const phoneNumber = document.getElementById('phoneNumber').value.trim();
    const startTime = document.getElementById('startTime').value;
    const endTime = document.getElementById('endTime').value;
    
    if (!phoneNumber) {
        showNotification('Please enter your phone number', 'error');
        return false;
    }
    
    if (!startTime || !endTime) {
        showNotification('Please select both start and end times', 'error');
        return false;
    }
    
    // Validate phone number format (basic validation)
    const phoneRegex = /^[0-9]{10,11}$/;
    if (!phoneRegex.test(phoneNumber.replace(/\D/g, ''))) {
        showNotification('Please enter a valid phone number', 'error');
        return false;
    }
    
    return true;
}

function collectOrderData() {
    const receiveMethod = document.querySelector('.method-btn.active')?.dataset.method || 'pickup';
    const phoneNumber = document.getElementById('phoneNumber').value.trim();
    const startTime = document.getElementById('startTime').value;
    const endTime = document.getElementById('endTime').value;
    
    return {
        productId: new URLSearchParams(window.location.search).get('id'),
        quantity: parseInt(new URLSearchParams(window.location.search).get('quantity')) || 1,
        receiveMethod: receiveMethod,
        phoneNumber: phoneNumber,
        startTime: startTime,
        endTime: endTime,
        timestamp: new Date().toISOString()
    };
}

// Notification function (you can replace this with your existing notification system)
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    // Style the notification
    Object.assign(notification.style, {
        position: 'fixed',
        top: '20px',
        right: '20px',
        padding: '12px 20px',
        borderRadius: '8px',
        color: 'white',
        fontWeight: '600',
        zIndex: '10000',
        maxWidth: '300px',
        wordWrap: 'break-word'
    });
    
    // Set background color based on type
    const colors = {
        success: '#10b981',
        error: '#ef4444',
        info: '#3b82f6',
        warning: '#f59e0b'
    };
    notification.style.backgroundColor = colors[type] || colors.info;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 3000);
}

// Back button function
function goBack() {
    // Get the product ID from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('id');
    
    if (productId) {
        // Navigate back to the specific product detail page
        window.location.href = `/consumer/food-detail/${productId}`;
    } else {
        // Fallback to browser back or food listing page
        if (window.history.length > 1) {
            window.history.back();
        } else {
            window.location.href = '/consumer/food-listing';
        }
    }
}

// Initialize page when DOM is loaded
console.log('Order Confirmation page initialized successfully!');
