// Handle page navigation (placeholder function)
function handlePageNavigation(page) {
    console.log(`Navigating to: ${page}`);

    // Update header title
    const headerTitle = document.querySelector('.header h1');
    const pageNames = {
        'dashboard': 'Dashboard',
        'food-listing': 'Food Listing',
        'announcements': 'Announcements',
        'orders': 'My Orders',
        'impact': 'My Impact',
        'settings': 'Settings',
        'help': 'Help Center',
        'logout': 'Logout'
    };

    if (headerTitle && pageNames[page]) {
        headerTitle.textContent = pageNames[page];
    }

    // Here you would typically load different content
    // For now, we'll just show a placeholder message
    if (page !== 'dashboard') {
        showPlaceholderContent(page);
    } else {
        showDashboardContent();
    }
}

// Show placeholder content for other pages
function showPlaceholderContent(page) {
    const content = document.querySelector('.content');
    const pageNames = {
        'food-listing': 'Food Listing',
        'announcements': 'Announcements',
        'orders': 'My Orders',
        'impact': 'My Impact',
        'settings': 'Settings',
        'help': 'Help Center',
        'logout': 'Logout'
    };

    content.innerHTML = `
        <div style="text-align: center; padding: 60px 20px;">
            <h2 style="color: #2d5016; margin-bottom: 20px; font-size: 28px;">${pageNames[page]}</h2>
            <p style="color: #666; font-size: 18px; margin-bottom: 30px;">This page is under construction.</p>
            <div style="background: #f8f9fa; border-radius: 12px; padding: 40px; border: 2px dashed #dee2e6;">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="#adb5bd" style="margin-bottom: 20px;">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
                <p style="color: #6c757d; margin: 0;">Content for ${pageNames[page]} will be added here.</p>
            </div>
        </div>
    `;
}

// Show dashboard content
function showDashboardContent() {
    location.reload(); // Simple way to restore dashboard content
}

// Button click handlers
document.addEventListener('click', (e) => {
    // Buy Now buttons
    if (e.target.classList.contains('btn-primary') && e.target.textContent === 'Buy Now') {
        e.preventDefault();
        showNotification('Item added to cart!', 'success');
    }

    // View Details buttons
    if (e.target.classList.contains('btn-secondary') && e.target.textContent === 'View Details') {
        e.preventDefault();
        showNotification('Opening product details...', 'info');
    }

    // Go to Food Listings button
    if (e.target.classList.contains('view-all-btn')) {
        e.preventDefault();
        // Simulate clicking on Food Listing nav item
        const foodListingNav = document.querySelector('[data-page="food-listing"]');
        foodListingNav.click();
    }

    // Go to Order History button
    if (e.target.classList.contains('order-action-btn')) {
        e.preventDefault();
        // Simulate clicking on My Orders nav item
        const ordersNav = document.querySelector('[data-page="orders"]');
        ordersNav.click();
    }
});

// Notification system
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }

    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        z-index: 10000;
        transform: translateX(400px);
        transition: transform 0.3s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    `;

    // Set background color based on type
    const backgrounds = {
        'success': '#4caf50',
        'error': '#f44336',
        'warning': '#ff9800',
        'info': '#2196f3'
    };
    notification.style.background = backgrounds[type] || backgrounds.info;

    notification.textContent = message;
    document.body.appendChild(notification);

    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 10);

    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(400px)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    }, 3000);
}

// Notification bell functionality
const notificationBtn = document.getElementById('notificationBtn');
notificationBtn?.addEventListener('click', () => {
    showNotification('No new notifications', 'info');
});

// Responsive handling
function handleResize() {
    if (window.innerWidth > 768) {
        closeMobileMenu();
        sidebar.classList.remove('mobile-hidden');
    }
}

window.addEventListener('resize', handleResize);

// Simulate loading states for buttons
function simulateLoading(button, duration = 1000) {
    const originalText = button.textContent;
    button.classList.add('loading');
    button.textContent = 'Loading...';
    button.disabled = true;

    setTimeout(() => {
        button.classList.remove('loading');
        button.textContent = originalText;
        button.disabled = false;
    }, duration);
}

// Add hover effects and interactions
document.addEventListener('DOMContentLoaded', () => {
    // Add click animations to stat cards
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach(card => {
        card.addEventListener('click', () => {
            card.style.transform = 'scale(0.98)';
            setTimeout(() => {
                card.style.transform = '';
            }, 150);
        });
    });

    // Add loading animation to deal buttons
    const dealButtons = document.querySelectorAll('.btn');
    dealButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            if (!e.defaultPrevented) {
                simulateLoading(button, 800);
            }
        });
    });

    // Welcome message animation
    const welcomeSection = document.querySelector('.welcome-section');
    if (welcomeSection) {
        welcomeSection.style.opacity = '0';
        welcomeSection.style.transform = 'translateY(-20px)';
        welcomeSection.style.transition = 'all 0.6s ease';

        setTimeout(() => {
            welcomeSection.style.opacity = '1';
            welcomeSection.style.transform = 'translateY(0)';
        }, 100);
    }

    // Animate stats cards on load
    const statsCards = document.querySelectorAll('.stat-card');
    statsCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'all 0.6s ease';

        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 200 + (index * 100));
    });
});

// Keyboard navigation support
document.addEventListener('keydown', (e) => {
    // ESC key to close mobile menu
    if (e.key === 'Escape') {
        closeMobileMenu();
    }
});

// Initialize app
console.log('Consumer Dashboard initialized successfully!');