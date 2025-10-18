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
    let selectedPaymentMethod = 'cash'; // Default

    paymentMethods.forEach(method => {
        method.addEventListener('click', function() {
            // Remove active class from all methods
            paymentMethods.forEach(m => m.classList.remove('active'));
            // Add active class to clicked method
            this.classList.add('active');
            // Update selected payment method
            selectedPaymentMethod = this.getAttribute('data-method');
        });
    });

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

    // Get selected payment method
    const activePaymentMethod = document.querySelector('.payment-method.active');
    const selectedPaymentMethod = activePaymentMethod ? activePaymentMethod.getAttribute('data-method') : 'cash';

    // Get customer data from backend
    const customerName = window.customerName || 'Customer';
    const customerPhone = window.customerPhone || urlParams.get('phone');

    // Prepare order data
    const orderData = {
        food_listing_id: foodListingId,
        quantity: parseInt(quantity),
        delivery_method: deliveryMethod,
        payment_method: selectedPaymentMethod,
        customer_name: customerName,
        customer_phone: customerPhone,
        delivery_address: deliveryMethod === 'delivery' ? prompt('Please enter delivery address:') : null,
        pickup_start_time: startTime,
        pickup_end_time: endTime,
        _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                document.querySelector('input[name="_token"]')?.value
    };

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
            'X-CSRF-TOKEN': orderData._token
        },
        body: JSON.stringify(orderData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            alert('Order placed successfully! Order Number: ' + data.order_number);
            // Redirect to my orders page
            window.location.href = '/consumer/my-orders';
        } else {
            // Show error message
            alert('Failed to place order: ' + (data.message || 'Unknown error'));
            // Reset button
            placeOrderBtn.textContent = originalText;
            placeOrderBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error placing order:', error);
        alert('An error occurred while placing the order. Please try again.');
        // Reset button
        placeOrderBtn.textContent = originalText;
        placeOrderBtn.disabled = false;
    });
}

function goBack() {
    window.history.back();
}