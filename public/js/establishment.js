// Mobile menu functionality
const menuToggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');
const mainContent = document.getElementById('mainContent');

function toggleMobileMenu() {
    sidebar.classList.toggle('mobile-visible');
    overlay.classList.toggle('active');
    document.body.style.overflow = sidebar.classList.contains('mobile-visible') ? 'hidden' : '';
}

function closeMobileMenu() {
    sidebar.classList.remove('mobile-visible');
    overlay.classList.remove('active');
    document.body.style.overflow = '';
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
        const page = link.dataset.page;
        
        // Handle logout - this is now handled by the href route, but keeping for compatibility
        if (page === 'logout') {
            e.preventDefault();
            if (confirm('Are you sure you want to logout?')) {
                // Create a form to submit logout request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/logout';
                
                // Add CSRF token
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                form.appendChild(csrfToken);
                
                // Submit the form
                document.body.appendChild(form);
                form.submit();
            }
            return;
        }
        
        // Handle other navigation
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

// Dashboard functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize dashboard
    initializeDashboard();
});

function initializeDashboard() {
    // Add any dashboard-specific initialization here
    console.log('Establishment dashboard initialized');
}

// Notification functionality
const notificationBtn = document.getElementById('notificationBtn');
if (notificationBtn) {
    notificationBtn.addEventListener('click', function() {
        // Toggle notification dropdown or show notifications
        console.log('Notifications clicked');
        // Add notification functionality here
    });
}

// Responsive behavior
window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
        closeMobileMenu();
    }
});
