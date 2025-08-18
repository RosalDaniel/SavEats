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

menuToggle?.addEventListener('click', toggleMobileMenu);
overlay?.addEventListener('click', closeMobileMenu);

// Navigation functionality
const navLinks = document.querySelectorAll('.nav-link');

navLinks.forEach(link => {
    link.addEventListener('click', (e) => {
        e.preventDefault();

        // Remove active class from all links
        navLinks.forEach(l => l.classList.remove('active'));

        // Add active class to clicked link
        link.classList.add('active');

        // Close mobile menu if open
        if (window.innerWidth <= 768) {
            closeMobileMenu();
        }

        // Here you can add logic to show different content based on the selected nav item
        const page = link.getAttribute('data-page');
        handlePageNavigation(page);
    });
});