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
        // In a real app, this would make an API call to accept the order
        alert('Order ' + orderId + ' has been accepted.');
        // Refresh the page or update the UI
        location.reload();
    }
}

function cancelOrder(orderId) {
    if (confirm('Are you sure you want to cancel this order?')) {
        // In a real app, this would make an API call to cancel the order
        alert('Order ' + orderId + ' has been cancelled.');
        // Refresh the page or update the UI
        location.reload();
    }
}

function markComplete(orderId) {
    if (confirm('Are you sure you want to mark this order as complete?')) {
        // In a real app, this would make an API call to mark the order as complete
        alert('Order ' + orderId + ' has been marked as complete.');
        // Refresh the page or update the UI
        location.reload();
    }
}

function viewOrderDetails(orderId) {
    // In a real app, this would open a details modal or navigate to details page
    alert('Order Details for Order ID: ' + orderId + '\n\nThis feature will be implemented in the next version.');
}