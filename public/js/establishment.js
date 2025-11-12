// Establishment Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu functionality
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const mainContent = document.getElementById('mainContent');

    function toggleMobileMenu() {
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
        sidebar.classList.remove('mobile-visible');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
        if (mainContent) {
            mainContent.style.overflow = '';
        }
    }

    menuToggle?.addEventListener('click', toggleMobileMenu);
    overlay?.addEventListener('click', closeMobileMenu);

    // Navigation functionality
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            // Allow logout and external links to work normally
            const href = link.getAttribute('href');
            if (href === '/logout' || 
                href.includes('logout') ||
                href.startsWith('http') ||
                link.textContent.toLowerCase().includes('logout')) {
                // Don't prevent default for logout or external links
                return;
            }
            
            e.preventDefault();
            
            // Remove active class from all links
            navLinks.forEach(l => l.classList.remove('active'));
            
            // Add active class to clicked link
            link.classList.add('active');
            
            // Close mobile menu if open
            if (window.innerWidth <= 768) {
                closeMobileMenu();
            }
            
            // Handle page navigation
            const page = link.getAttribute('data-page');
            handlePageNavigation(page);
        });
    });

    // Handle page navigation
    function handlePageNavigation(page) {
        console.log(`Navigating to: ${page}`);
        
        // Update header title
        const headerTitle = document.querySelector('.header h1');
        const pageNames = {
            'dashboard': 'Dashboard',
            'listing-management': 'Listing Management',
            'order-management': 'Order Management',
            'announcements': 'Announcements',
            'earnings': 'Earnings',
            'donation-hub': 'Donation Hub',
            'impact-reports': 'Impact Reports',
            'settings': 'Settings',
            'help': 'Help Center',
            'logout': 'Logout'
        };
        
        if (headerTitle && pageNames[page]) {
            headerTitle.textContent = pageNames[page];
        }
        
        // Show different content based on page
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
            'listing-management': 'Listing Management',
            'order-management': 'Order Management',
            'announcements': 'Announcements',
            'earnings': 'Earnings',
            'donation-hub': 'Donation Hub',
            'impact-reports': 'Impact Reports',
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
        location.reload(); // Restore dashboard content
    }

    // Button click handlers
    document.addEventListener('click', (e) => {
        // Donate buttons
        if (e.target.classList.contains('btn-donate')) {
            e.preventDefault();
            showNotification('Item marked for donation!', 'success');
        }
        
        // View Listing buttons
        if (e.target.classList.contains('btn-view')) {
            e.preventDefault();
            showNotification('Opening listing details...', 'info');
        }
        
        // Go to Order Management button
        if (e.target.classList.contains('order-action-btn') || e.target.closest('.order-action-btn')) {
            // Let the link work normally - no need to prevent default or find navigation element
            // The link already points to the order management route
            return;
        }

        // See All buttons
        if (e.target.classList.contains('see-all-btn')) {
            e.preventDefault();
            showNotification('Loading all items...', 'info');
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

    // Simulate loading states
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

    // Animate inventory bars on load
    function animateInventoryBars() {
        const bars = document.querySelectorAll('.bar-fill');
        bars.forEach((bar, index) => {
            const targetWidth = bar.style.width;
            bar.style.width = '0%';
            setTimeout(() => {
                bar.style.width = targetWidth;
            }, 500 + (index * 200));
        });
    }

    // Initialize dashboard
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

    // Animate welcome section
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

    // Animate inventory bars after a delay
    setTimeout(animateInventoryBars, 1000);

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

    // Add loading animation to buttons
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('click', (e) => {
            if (!e.defaultPrevented) {
                simulateLoading(button, 800);
            }
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
    console.log('Establishment Dashboard initialized successfully!');
});