// Foodbank Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize animations and interactions
    initializeAnimations();
    initializeInteractions();
    initializeChart();
});

function initializeAnimations() {
    // Chart bar animation
    const bars = document.querySelectorAll('.bar');
    bars.forEach((bar, index) => {
        const originalHeight = bar.style.height;
        bar.style.height = '0';
        setTimeout(() => {
            bar.style.transition = 'height 0.6s ease';
            bar.style.height = originalHeight;
        }, 100 + (index * 100));
    });

    // Stats animation
    const statValues = document.querySelectorAll('.stat-value');
    statValues.forEach((stat, index) => {
        const finalValue = parseInt(stat.textContent);
        let currentValue = 0;
        const increment = finalValue / 30;
        
        const timer = setInterval(() => {
            currentValue += increment;
            if (currentValue >= finalValue) {
                stat.textContent = finalValue;
                clearInterval(timer);
            } else {
                stat.textContent = Math.floor(currentValue);
            }
        }, 30);
    });

    // Welcome section animation
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
}

function initializeInteractions() {
    // Button click handlers
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('btn-primary') && e.target.textContent === 'Rate') {
            e.preventDefault();
            showNotification('Rating submitted successfully!', 'success');
        }
        
        if (e.target.classList.contains('btn-secondary') && e.target.textContent === 'View Store') {
            e.preventDefault();
            showNotification('Opening store details...', 'info');
        }

        if (e.target.classList.contains('see-all-link')) {
            e.preventDefault();
            showNotification('Loading all donations...', 'info');
        }
    });

    // Bar hover tooltip
    const bars = document.querySelectorAll('.bar');
    bars.forEach(bar => {
        bar.addEventListener('mouseenter', (e) => {
            const value = e.target.getAttribute('data-value');
            const tooltip = document.createElement('div');
            tooltip.className = 'bar-tooltip';
            tooltip.textContent = `${value} items`;
            tooltip.style.cssText = `
                position: absolute;
                bottom: 100%;
                left: 50%;
                transform: translateX(-50%) translateY(-5px);
                background: #333;
                color: white;
                padding: 5px 10px;
                border-radius: 4px;
                font-size: 12px;
                white-space: nowrap;
                z-index: 10;
            `;
            e.target.style.position = 'relative';
            e.target.appendChild(tooltip);
        });

        bar.addEventListener('mouseleave', (e) => {
            const tooltip = e.target.querySelector('.bar-tooltip');
            if (tooltip) {
                tooltip.remove();
            }
        });
    });
}

function initializeChart() {
    // Chart initialization if needed
    console.log('Chart initialized');
}

// Notification system
function showNotification(message, type = 'info') {
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
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
    
    const backgrounds = {
        'success': '#4caf50',
        'error': '#f44336',
        'warning': '#ff9800',
        'info': '#2196f3'
    };
    notification.style.background = backgrounds[type] || backgrounds.info;
    
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 10);
    
    setTimeout(() => {
        notification.style.transform = 'translateX(400px)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    }, 3000);
}

// Keyboard navigation support
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        // Close any open modals or menus
        console.log('Escape key pressed');
    }
});

console.log('Food Bank Dashboard initialized successfully!');