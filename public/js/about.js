// About page specific functionality
document.addEventListener('DOMContentLoaded', function() {
    // Add intersection observer for animations
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

    // Observe team members and mission statement
    document.querySelectorAll('.team-member, .mission-statement').forEach(el => {
        observer.observe(el);
    });

    // Add staggered animation delays for team members
    document.querySelectorAll('.team-member').forEach((member, index) => {
        member.style.animationDelay = `${index * 0.1}s`;
    });
});
