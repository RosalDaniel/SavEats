// Payment Options Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initializePaymentOptions();
    setupEventListeners();
    loadOrderData();
});

function initializePaymentOptions() {
    // Set initial state - Cash method expanded by default
    const cashMethod = document.getElementById('cashMethod');
    const cardMethod = document.getElementById('cardMethod');
    const ewalletMethod = document.getElementById('ewalletMethod');
    
    if (cashMethod) {
        const content = cashMethod.querySelector('.method-content');
        const arrow = cashMethod.querySelector('.method-arrow');
        content.classList.remove('collapsed');
        arrow.textContent = '▲';
    }
    
    if (cardMethod) {
        const content = cardMethod.querySelector('.method-content');
        const arrow = cardMethod.querySelector('.method-arrow');
        content.classList.remove('collapsed');
        arrow.textContent = '▲';
    }
    
    if (ewalletMethod) {
        const content = ewalletMethod.querySelector('.method-content');
        const arrow = ewalletMethod.querySelector('.method-arrow');
        content.classList.add('collapsed');
        arrow.textContent = '▼';
    }
}

function loadOrderData() {
    // Get URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const receiveMethod = urlParams.get('method') || 'pickup';
    const phoneNumber = urlParams.get('phone') || '';
    const startTime = urlParams.get('startTime') || '';
    const endTime = urlParams.get('endTime') || '';
    
    // Pre-populate phone number if available
    if (phoneNumber) {
        const phoneInput = document.getElementById('phoneNumber');
        if (phoneInput) {
            phoneInput.value = phoneNumber;
        }
    }
    
    // Set receive method visual state
    updateReceiveMethodDisplay(receiveMethod);
}

function updateReceiveMethodDisplay(method) {
    // This function can be used to show which receive method was selected
    // For now, we'll just log it or add visual indicators if needed
    console.log('Receive method:', method);
}

function setupEventListeners() {
    // Place order button
    const placeOrderBtn = document.getElementById('placeOrderBtn');
    if (placeOrderBtn) {
        placeOrderBtn.addEventListener('click', handlePlaceOrder);
    }
    
    // Card number formatting
    const cardNumberInput = document.getElementById('cardNumber');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', formatCardNumber);
    }
    
    // Expiry date formatting
    const expiryDateInput = document.getElementById('expiryDate');
    if (expiryDateInput) {
        expiryDateInput.addEventListener('input', formatExpiryDate);
    }
    
    // Security code formatting
    const securityCodeInput = document.getElementById('securityCode');
    if (securityCodeInput) {
        securityCodeInput.addEventListener('input', formatSecurityCode);
    }
    
    // File upload preview
    const foodImageInput = document.getElementById('foodImage');
    if (foodImageInput) {
        foodImageInput.addEventListener('change', handleImageUpload);
    }
    
    // Bank logo selection
    const bankLogos = document.querySelectorAll('.bank-logo');
    bankLogos.forEach(logo => {
        logo.addEventListener('click', selectBank);
    });
}

function toggleMethod(methodId) {
    const method = document.getElementById(methodId);
    if (!method) return;
    
    const content = method.querySelector('.method-content');
    const arrow = method.querySelector('.method-arrow');
    
    if (content.classList.contains('collapsed')) {
        // Expand
        content.classList.remove('collapsed');
        arrow.textContent = '▲';
    } else {
        // Collapse
        content.classList.add('collapsed');
        arrow.textContent = '▼';
    }
}

function formatCardNumber(event) {
    let value = event.target.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
    let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
    
    if (formattedValue.length > 19) {
        formattedValue = formattedValue.substring(0, 19);
    }
    
    event.target.value = formattedValue;
}

function formatExpiryDate(event) {
    let value = event.target.value.replace(/\D/g, '');
    
    if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
    }
    
    event.target.value = value;
}

function formatSecurityCode(event) {
    let value = event.target.value.replace(/\D/g, '');
    
    if (value.length > 4) {
        value = value.substring(0, 4);
    }
    
    event.target.value = value;
}

function selectBank(event) {
    // Remove selection from all banks
    const bankLogos = document.querySelectorAll('.bank-logo');
    bankLogos.forEach(logo => {
        logo.classList.remove('selected');
    });
    
    // Add selection to clicked bank
    event.currentTarget.classList.add('selected');
}

function handleImageUpload(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // You can add image preview functionality here
            console.log('Image uploaded:', file.name);
        };
        reader.readAsDataURL(file);
    }
}

function handlePlaceOrder() {
    // Validate form
    if (!validatePaymentForm()) {
        return;
    }
    
    // Get order data from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const orderData = {
        productId: urlParams.get('id'),
        quantity: urlParams.get('quantity'),
        receiveMethod: urlParams.get('method'),
        phoneNumber: urlParams.get('phone'),
        startTime: urlParams.get('startTime'),
        endTime: urlParams.get('endTime'),
        paymentMethod: getSelectedPaymentMethod(),
        cardDetails: getCardDetails(),
        timestamp: new Date().toISOString()
    };
    
    // Show loading state
    const placeOrderBtn = document.getElementById('placeOrderBtn');
    const originalText = placeOrderBtn.textContent;
    placeOrderBtn.textContent = 'Processing...';
    placeOrderBtn.disabled = true;
    
    // Simulate processing
    setTimeout(() => {
        // Reset button
        placeOrderBtn.textContent = originalText;
        placeOrderBtn.disabled = false;
        
        // Show success message
        showNotification('Order placed successfully!', 'success');
        
        // Log order data for debugging
        console.log('Order placed:', orderData);
        
        // Redirect to confirmation page or dashboard
        setTimeout(() => {
            window.location.href = '/consumer/dashboard';
        }, 2000);
    }, 2000);
}

function getSelectedPaymentMethod() {
    const cashCheckbox = document.getElementById('cashCheckbox');
    if (cashCheckbox && cashCheckbox.checked) {
        return 'cash';
    }
    
    const cardNumber = document.getElementById('cardNumber');
    if (cardNumber && cardNumber.value.trim()) {
        return 'card';
    }
    
    return 'unknown';
}

function getCardDetails() {
    const cardNumber = document.getElementById('cardNumber');
    const expiryDate = document.getElementById('expiryDate');
    const securityCode = document.getElementById('securityCode');
    
    if (cardNumber && cardNumber.value.trim()) {
        return {
            number: cardNumber.value.trim(),
            expiry: expiryDate ? expiryDate.value.trim() : '',
            cvv: securityCode ? securityCode.value.trim() : ''
        };
    }
    
    return null;
}

function validatePaymentForm() {
    // Check if at least one payment method is selected
    const cashCheckbox = document.getElementById('cashCheckbox');
    const cardNumber = document.getElementById('cardNumber');
    const expiryDate = document.getElementById('expiryDate');
    const securityCode = document.getElementById('securityCode');
    
    let isValid = true;
    let errorMessage = '';
    
    // Check cash payment
    if (cashCheckbox && cashCheckbox.checked) {
        return true; // Cash payment is valid
    }
    
    // Check card payment
    if (cardNumber && cardNumber.value.trim()) {
        if (!validateCardNumber(cardNumber.value)) {
            errorMessage = 'Please enter a valid card number';
            isValid = false;
        } else if (!expiryDate.value.trim()) {
            errorMessage = 'Please enter expiry date';
            isValid = false;
        } else if (!validateExpiryDate(expiryDate.value)) {
            errorMessage = 'Please enter a valid expiry date (MM/YY)';
            isValid = false;
        } else if (!securityCode.value.trim()) {
            errorMessage = 'Please enter security code';
            isValid = false;
        } else if (securityCode.value.length < 3) {
            errorMessage = 'Security code must be at least 3 digits';
            isValid = false;
        }
    } else {
        errorMessage = 'Please select a payment method';
        isValid = false;
    }
    
    if (!isValid) {
        showNotification(errorMessage, 'error');
    }
    
    return isValid;
}

function validateCardNumber(cardNumber) {
    // Remove spaces and check if it's a valid card number
    const cleanNumber = cardNumber.replace(/\s/g, '');
    return /^\d{13,19}$/.test(cleanNumber);
}

function validateExpiryDate(expiryDate) {
    const regex = /^(0[1-9]|1[0-2])\/\d{2}$/;
    if (!regex.test(expiryDate)) {
        return false;
    }
    
    const [month, year] = expiryDate.split('/');
    const currentDate = new Date();
    const currentYear = currentDate.getFullYear() % 100;
    const currentMonth = currentDate.getMonth() + 1;
    
    const expYear = parseInt(year);
    const expMonth = parseInt(month);
    
    if (expYear < currentYear || (expYear === currentYear && expMonth < currentMonth)) {
        return false;
    }
    
    return true;
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    // Style the notification
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        z-index: 1000;
        animation: slideIn 0.3s ease;
    `;
    
    // Set background color based on type
    if (type === 'success') {
        notification.style.backgroundColor = '#4a7c59';
    } else if (type === 'error') {
        notification.style.backgroundColor = '#dc2626';
    } else {
        notification.style.backgroundColor = '#2563eb';
    }
    
    // Add to page
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

function goBack() {
    window.history.back();
}

// Add CSS for notifications
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .bank-logo.selected {
        border-color: #4a7c59 !important;
        background: #f0f9ff;
        transform: translateY(-2px);
    }
`;
document.head.appendChild(style);
