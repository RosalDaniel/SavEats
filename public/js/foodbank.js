// Foodbank Mobile Menu and General Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu functionality
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const mainContent = document.getElementById('mainContent');

    function toggleMobileMenu() {
        if (!sidebar || !overlay) return;
        
        sidebar.classList.toggle('mobile-visible');
        overlay.classList.toggle('active');
        const isOpen = sidebar.classList.contains('mobile-visible');
        
        // Disable scrolling on body and main content when menu is open
        if (isOpen) {
            document.body.style.overflow = 'hidden';
            if (mainContent) {
                mainContent.style.overflow = 'hidden';
            }
        } else {
            document.body.style.overflow = '';
            if (mainContent) {
                mainContent.style.overflow = '';
            }
        }
    }

    function closeMobileMenu() {
        if (!sidebar || !overlay) return;
        
        sidebar.classList.remove('mobile-visible');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
        if (mainContent) {
            mainContent.style.overflow = '';
        }
    }

    // Event listeners for mobile menu
    if (menuToggle) {
        menuToggle.addEventListener('click', toggleMobileMenu);
    }

    if (overlay) {
        overlay.addEventListener('click', closeMobileMenu);
    }

    // Navigation functionality
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            // Only prevent default for # links, let Laravel handle real navigation
            if (link.getAttribute('href') === '#') {
                e.preventDefault();
            }
            
            // Close mobile menu if open
            if (window.innerWidth <= 768) {
                closeMobileMenu();
            }
        });
    });

    // Notification functionality
    const notificationBtn = document.getElementById('notificationBtn');
    if (notificationBtn) {
        notificationBtn.addEventListener('click', () => {
            showNotification('No new notifications', 'info');
        });
    }

    // Handle window resize with error handling and debouncing
    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            try {
                if (window.innerWidth > 768) {
                    if (typeof closeMobileMenu === 'function') {
                        closeMobileMenu();
                    } else {
                        // Try to close menu manually if function doesn't exist
                        const sidebar = document.getElementById('sidebar');
                        const overlay = document.getElementById('overlay');
                        if (sidebar) {
                            sidebar.classList.remove('mobile-visible');
                        }
                        if (overlay) {
                            overlay.classList.remove('active');
                        }
                        document.body.style.overflow = '';
                        const mainContent = document.getElementById('mainContent');
                        if (mainContent) {
                            mainContent.style.overflow = '';
                        }
                    }
                }
            } catch (error) {
                console.error('Resize handler error:', error);
                // Don't show error notification for resize errors
            }
        }, 150);
    });

    // Keyboard navigation support
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeMobileMenu();
        }
    });
});

// Show notification
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
        font-family: 'Afacad', sans-serif;
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

