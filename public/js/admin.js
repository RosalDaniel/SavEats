// Admin Dashboard JavaScript

document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.querySelector('.overlay');
    const body = document.body;
    
    // Menu toggle for mobile
    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', function() {
            const isVisible = sidebar.classList.contains('mobile-visible');
            
            if (isVisible) {
                sidebar.classList.remove('mobile-visible');
                if (overlay) {
                    overlay.classList.remove('active');
                }
                body.classList.remove('sidebar-open');
            } else {
                sidebar.classList.add('mobile-visible');
                if (overlay) {
                    overlay.classList.add('active');
                }
                body.classList.add('sidebar-open');
            }
        });
    }
    
    // Close sidebar when clicking overlay
    if (overlay) {
        overlay.addEventListener('click', function() {
            if (sidebar) {
                sidebar.classList.remove('mobile-visible');
            }
            overlay.classList.remove('active');
            body.classList.remove('sidebar-open');
        });
    }
    
    // Close sidebar on window resize if it's desktop size
    window.addEventListener('resize', function() {
        if (window.innerWidth > 1024) {
            if (sidebar) {
                sidebar.classList.remove('mobile-visible');
            }
            if (overlay) {
                overlay.classList.remove('active');
            }
            body.classList.remove('sidebar-open');
        }
    });
    
    // Close sidebar when clicking outside on mobile
    if (sidebar) {
        sidebar.addEventListener('click', function(e) {
            // Prevent closing when clicking inside sidebar
            e.stopPropagation();
        });
    }
    
    // Initialize nested menu expansion state
    initializeNestedMenus();
});

// Toggle submenu function
function toggleSubmenu(event, element) {
    event.preventDefault();
    const navItem = element.closest('.nav-item-has-children');
    const submenu = navItem.querySelector('.nav-submenu');
    
    if (navItem && submenu) {
        const isExpanded = navItem.classList.contains('expanded');
        
        if (isExpanded) {
            navItem.classList.remove('expanded');
            submenu.classList.remove('expanded');
        } else {
            navItem.classList.add('expanded');
            submenu.classList.add('expanded');
        }
    }
}

// Initialize nested menus - expand if any child is active
function initializeNestedMenus() {
    const navItemsWithChildren = document.querySelectorAll('.nav-item-has-children');
    
    navItemsWithChildren.forEach(navItem => {
        const submenu = navItem.querySelector('.nav-submenu');
        const activeChild = navItem.querySelector('.nav-link-child.active');
        
        if (activeChild && submenu) {
            navItem.classList.add('expanded');
            submenu.classList.add('expanded');
        }
    });
}

