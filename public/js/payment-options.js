// Payment Options JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                     document.querySelector('input[name="_token"]')?.value;

    // Get URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const foodListingId = urlParams.get('id');
    const quantity = urlParams.get('quantity');
    const deliveryMethod = urlParams.get('method');
    const startTime = urlParams.get('startTime');
    const endTime = urlParams.get('endTime');
    
    // Get customer data from backend
    const customerPhone = window.customerPhone || urlParams.get('phone');

    // Payment method selection
    const paymentMethods = document.querySelectorAll('.payment-method');
    
    // Set default payment method (cash) as active
    const cashMethod = document.getElementById('cashMethod');
    if (cashMethod) {
        cashMethod.classList.add('active');
    }

    // Handle payment method selection
    paymentMethods.forEach(method => {
        method.addEventListener('click', function(e) {
            // Don't trigger if clicking inside the method content
            if (e.target.closest('.method-content')) {
                return;
            }
            
            // Remove active class from all methods
            paymentMethods.forEach(m => m.classList.remove('active'));
            // Add active class to clicked method
            this.classList.add('active');
            
            // Update checkbox state for cash method
            const cashCheckbox = document.getElementById('cashCheckbox');
            if (this.id === 'cashMethod' && cashCheckbox) {
                cashCheckbox.checked = true;
            } else if (cashCheckbox) {
                cashCheckbox.checked = false;
            }
        });
    });
    
    // Handle cash checkbox change
    const cashCheckbox = document.getElementById('cashCheckbox');
    if (cashCheckbox) {
        cashCheckbox.addEventListener('change', function() {
            if (this.checked) {
                // Set cash as active payment method
                paymentMethods.forEach(m => m.classList.remove('active'));
                cashMethod.classList.add('active');
            }
        });
    }

    // Place Order button functionality
    const placeOrderBtn = document.getElementById('placeOrderBtn');
    if (placeOrderBtn) {
        placeOrderBtn.addEventListener('click', function() {
            placeOrder();
        });
    }
});

function placeOrder() {
    // Get URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const foodListingId = urlParams.get('id');
    const quantity = urlParams.get('quantity');
    const deliveryMethod = urlParams.get('method');
    const startTime = urlParams.get('startTime');
    const endTime = urlParams.get('endTime');
    
    // Delivery data from URL
    const deliveryAddress = urlParams.get('deliveryAddress');
    const deliveryLat = urlParams.get('deliveryLat');
    const deliveryLng = urlParams.get('deliveryLng');
    const deliveryDistance = urlParams.get('deliveryDistance');
    const deliveryFee = urlParams.get('deliveryFee');
    const deliveryETA = urlParams.get('deliveryETA');
    const deliveryInstructions = urlParams.get('deliveryInstructions');
    const fullName = urlParams.get('fullName');

    // Get selected payment method
    const activePaymentMethod = document.querySelector('.payment-method.active');
    let selectedPaymentMethod = 'cash'; // Default
    
    if (activePaymentMethod) {
        selectedPaymentMethod = activePaymentMethod.getAttribute('data-method') || 'cash';
    }
    
    // Validate payment method - only cash is supported
    if (!selectedPaymentMethod || selectedPaymentMethod !== 'cash') {
        console.error('Invalid payment method:', selectedPaymentMethod);
        selectedPaymentMethod = 'cash'; // Only cash is supported
    }
    
    console.log('Selected payment method:', selectedPaymentMethod);

    // Get customer data from backend or URL
    const customerName = fullName || window.customerName || 'Customer';
    const customerPhone = window.customerPhone || urlParams.get('phone');

    // Validate required fields before sending
    if (!foodListingId) {
        alert('Error: Food item ID is missing');
        return;
    }
    if (!quantity || parseInt(quantity) < 1) {
        alert('Error: Invalid quantity');
        return;
    }
    if (!deliveryMethod || !['pickup', 'delivery'].includes(deliveryMethod)) {
        alert('Error: Invalid delivery method');
        return;
    }
    // Ensure customer name is valid (not empty and not just "Customer" placeholder)
    const trimmedName = customerName.trim();
    if (!trimmedName || trimmedName === '' || (trimmedName === 'Customer' && !window.customerName)) {
        alert('Error: Customer name is required. Please ensure you are logged in with a valid account.');
        console.error('Customer name validation failed:', { customerName, windowCustomerName: window.customerName });
        return;
    }
    if (!customerPhone || customerPhone.trim() === '') {
        alert('Error: Customer phone number is required');
        return;
    }

    // Prepare order data
    const orderData = {
        food_listing_id: foodListingId,
        quantity: parseInt(quantity),
        delivery_method: deliveryMethod,
        delivery_type: deliveryMethod,
        payment_method: selectedPaymentMethod,
        customer_name: customerName.trim(),
        customer_phone: customerPhone.trim(),
        pickup_start_time: deliveryMethod === 'pickup' && startTime ? decodeURIComponent(startTime) : null,
        pickup_end_time: deliveryMethod === 'pickup' && endTime ? decodeURIComponent(endTime) : null,
        _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                document.querySelector('input[name="_token"]')?.value
    };
    
    // Add delivery fields if delivery method
    if (deliveryMethod === 'delivery') {
        orderData.delivery_address = deliveryAddress ? decodeURIComponent(deliveryAddress) : null;
        orderData.delivery_lat = deliveryLat ? parseFloat(deliveryLat) : null;
        orderData.delivery_lng = deliveryLng ? parseFloat(deliveryLng) : null;
        orderData.delivery_distance = deliveryDistance ? parseFloat(deliveryDistance) : null;
        orderData.delivery_fee = deliveryFee ? parseFloat(deliveryFee) : null;
        orderData.delivery_eta = deliveryETA ? decodeURIComponent(deliveryETA) : null;
        orderData.delivery_instructions = deliveryInstructions ? decodeURIComponent(deliveryInstructions) : null;
    }

    // Log the data being sent for debugging
    console.log('Order data being sent:', orderData);

    // Show loading state
    const placeOrderBtn = document.getElementById('placeOrderBtn');
    const originalText = placeOrderBtn.textContent;
    placeOrderBtn.textContent = 'Placing Order...';
    placeOrderBtn.disabled = true;

    // Send order request
    fetch('/consumer/place-order', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': orderData._token,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(orderData)
    })
    .then(async response => {
        // Always try to parse as JSON first
        const contentType = response.headers.get('content-type');
        let data;
        
        if (contentType && contentType.includes('application/json')) {
            data = await response.json();
        } else {
            // If not JSON, try to parse anyway but log warning
            const text = await response.text();
            console.error('Server returned HTML instead of JSON:', text.substring(0, 200));
            throw new Error(`Server error (${response.status}). Please check the console for details.`);
        }
        
        // Check if response is ok
        if (!response.ok) {
            // If validation errors exist, format them nicely
            if (data.errors) {
                const errorList = Object.entries(data.errors)
                    .map(([field, messages]) => {
                        const fieldName = field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                        return `${fieldName}: ${Array.isArray(messages) ? messages.join(', ') : messages}`;
                    })
                    .join('\n');
                throw new Error('Validation failed:\n\n' + errorList);
            }
            throw new Error(data.message || `HTTP error! status: ${response.status}`);
        }
        
        return data;
    })
    .then(data => {
        if (data.success) {
            // Show success message
            alert('Order placed successfully! Order Number: ' + data.order_number);
            // Redirect to my orders page
            window.location.href = '/consumer/my-orders';
        } else {
            // This should not happen as errors are thrown above, but just in case
            alert('Failed to place order: ' + (data.message || 'Unknown error'));
            console.error('Order placement error:', data);
            // Reset button
            placeOrderBtn.textContent = originalText;
            placeOrderBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error placing order:', error);
        alert('An error occurred while placing the order: ' + error.message);
        // Reset button
        placeOrderBtn.textContent = originalText;
        placeOrderBtn.disabled = false;
    });
}

function toggleMethod(methodId) {
    const methodElement = document.getElementById(methodId);
    if (!methodElement) return;
    
    const methodContent = methodElement.querySelector('.method-content');
    const methodArrow = methodElement.querySelector('.method-arrow');
    
    if (methodContent && methodArrow) {
        // Toggle collapsed class
        methodContent.classList.toggle('collapsed');
        
        // Update arrow direction
        if (methodContent.classList.contains('collapsed')) {
            methodArrow.textContent = '▼';
        } else {
            methodArrow.textContent = '▲';
        }
    }
}

function goBack() {
    window.history.back();
}