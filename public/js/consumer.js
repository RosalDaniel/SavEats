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

// Event listeners for mobile menu
if (menuToggle) {
    menuToggle.addEventListener('click', toggleMobileMenu);
}

if (overlay) {
    overlay.addEventListener('click', closeMobileMenu);
}

// Navigation functionality - Let Laravel handle routing
const navLinks = document.querySelectorAll('.nav-link');

navLinks.forEach(link => {
    link.addEventListener('click', (e) => {
        // Only prevent default for # links, let Laravel handle real navigation
        if (link.getAttribute('href') === '#') {
            e.preventDefault();
        }
        
        // Remove active class from all links
        navLinks.forEach(l => l.classList.remove('active'));
        
        // Add active class to clicked link
        link.classList.add('active');
        
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

// Show notification
function showNotification(message, type = 'info') {
    // Simple notification - in a real app, you might use a toast library
    alert(message);
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
        }, 300);
    }

    // Stats grid animation
    statCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'all 0.6s ease';
        
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 500 + (index * 100));
    });

    // Deal items animation
    const dealItems = document.querySelectorAll('.deal-item');
    dealItems.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateX(-30px)';
        item.style.transition = 'all 0.6s ease';
        
        setTimeout(() => {
            item.style.opacity = '1';
            item.style.transform = 'translateX(0)';
        }, 800 + (index * 200));
    });
});

// Simulate loading animation
function simulateLoading(element, duration) {
    const originalText = element.textContent;
    element.textContent = 'Loading...';
    element.disabled = true;
    element.style.opacity = '0.7';
    
    setTimeout(() => {
        element.textContent = originalText;
        element.disabled = false;
        element.style.opacity = '1';
    }, duration);
}

// Add smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Add keyboard navigation support
document.addEventListener('keydown', (e) => {
    // Close mobile menu with Escape key
    if (e.key === 'Escape') {
        closeMobileMenu();
    }
});

// Handle window resize with error handling
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

// Add touch support for mobile
let touchStartY = 0;
let touchEndY = 0;

document.addEventListener('touchstart', (e) => {
    touchStartY = e.changedTouches[0].screenY;
});

document.addEventListener('touchend', (e) => {
    touchEndY = e.changedTouches[0].screenY;
    handleSwipe();
});

function handleSwipe() {
    const swipeThreshold = 50;
    const diff = touchStartY - touchEndY;
    
    if (Math.abs(diff) > swipeThreshold) {
        if (diff > 0) {
            // Swipe up - close mobile menu
            closeMobileMenu();
        }
    }
}