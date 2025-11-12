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
    // Order details functionality removed
    alert('Order Details for Order ID: ' + orderId + '\n\nThis feature is not available in order management.');
}