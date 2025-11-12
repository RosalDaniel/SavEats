// Mobile menu functionality
document.addEventListener('DOMContentLoaded', function() {
    const mobileToggle = document.getElementById('mobileToggle');
    const navMenu = document.getElementById('navMenu');

    // Check if elements exist before adding event listeners
    if (mobileToggle && navMenu) {
        mobileToggle.addEventListener('click', function() {
            const isActive = navMenu.classList.contains('active');
            navMenu.classList.toggle('active');
            
            const icon = mobileToggle.querySelector('span');
            if (!isActive) {
                icon.innerHTML = '✕';
                // Disable scrolling when menu is open
                document.body.style.overflow = 'hidden';
            } else {
                icon.innerHTML = '☰';
                // Enable scrolling when menu is closed
                document.body.style.overflow = '';
            }
        });
    }

    // Close mobile menu when clicking on a link
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            navMenu.classList.remove('active');
            const toggleSpan = mobileToggle.querySelector('span');
            if (toggleSpan) {
                toggleSpan.innerHTML = '☰';
            }
            // Enable scrolling when menu is closed
            document.body.style.overflow = '';
        });
    });

    // Close mobile menu when clicking outside
    if (navMenu && mobileToggle) {
        document.addEventListener('click', function(event) {
            const isClickInsideNav = navMenu.contains(event.target);
            const isClickOnToggle = mobileToggle.contains(event.target);
            
            if (!isClickInsideNav && !isClickOnToggle && navMenu.classList.contains('active')) {
                navMenu.classList.remove('active');
                const toggleSpan = mobileToggle.querySelector('span');
                if (toggleSpan) {
                    toggleSpan.innerHTML = '☰';
                }
                // Enable scrolling when menu is closed
                document.body.style.overflow = '';
            }
        });
    }

    // Smooth scrolling for anchor links
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

    // Header scroll effect
    let lastScrollTop = 0;
    const header = document.querySelector('.header');
    
    if (header) {
        window.addEventListener('scroll', function() {
            let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            if (scrollTop > lastScrollTop && scrollTop > 100) {
                header.style.transform = 'translateY(-100%)';
            } else {
                header.style.transform = 'translateY(0)';
            }
            lastScrollTop = scrollTop;
        });
    }

    // Intersection Observer for animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe elements for animation
    document.querySelectorAll('.step-card, .testimonial-card').forEach((el, index) => {
        el.style.animationDelay = `${index * 0.1}s`;
        observer.observe(el);
    });



    // Add floating animation to CTA buttons
    const ctaButtons = document.querySelectorAll('.cta-button, .btn-primary, .btn-secondary');
    ctaButtons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.animation = 'none';
            setTimeout(() => {
                this.style.animation = 'float 2s ease-in-out infinite';
            }, 10);
        });
    });
});
