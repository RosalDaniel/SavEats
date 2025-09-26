// Enhanced Search and Filter Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    const clearBtn = document.getElementById('clearBtn');
    
    // Category filters
    const categoryPills = document.querySelectorAll('.category-pill');
    const mobileCategoryItems = document.querySelectorAll('.mobile-category-item');
    const categoriesToggle = document.getElementById('categoriesToggle');
    const categoriesGrid = document.getElementById('categoriesGrid');
    
    // Price dropdown
    const priceDropdown = document.getElementById('priceDropdown');
    const priceMenu = document.getElementById('priceMenu');
    const priceItems = document.querySelectorAll('.dropdown-item');
    
    // Active filters
    const activeFiltersContainer = document.getElementById('activeFilters');
    
    // Food grid
    const foodGrid = document.getElementById('foodGrid');
    const foodCards = document.querySelectorAll('.food-card');
    
    // State
    let activeFilters = {
        category: 'all',
        price: 'all',
        search: ''
    };

    // Search input handlers
    searchInput.addEventListener('input', (e) => {
        const value = e.target.value;
        clearBtn.classList.toggle('visible', value.length > 0);
        activeFilters.search = value;
        updateActiveFilters();
        filterFoodItems();
    });

    searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            performSearch();
        }
    });

    searchBtn.addEventListener('click', performSearch);
    
    clearBtn.addEventListener('click', () => {
        searchInput.value = '';
        clearBtn.classList.remove('visible');
        activeFilters.search = '';
        updateActiveFilters();
        filterFoodItems();
        searchInput.focus();
    });

    // Category filter handlers (Desktop)
    categoryPills.forEach(pill => {
        pill.addEventListener('click', () => {
            // Remove active class from all pills
            categoryPills.forEach(p => p.classList.remove('active'));
            // Add active class to clicked pill
            pill.classList.add('active');
            
            activeFilters.category = pill.dataset.category;
            updateActiveFilters();
            filterFoodItems();
        });
    });

    // Mobile categories toggle
    categoriesToggle.addEventListener('click', () => {
        categoriesToggle.classList.toggle('active');
        categoriesGrid.classList.toggle('open');
    });

    // Mobile category handlers
    mobileCategoryItems.forEach(item => {
        item.addEventListener('click', () => {
            // Remove active class from all mobile items
            mobileCategoryItems.forEach(i => i.classList.remove('active'));
            // Add active class to clicked item
            item.classList.add('active');
            
            // Also sync with desktop pills
            categoryPills.forEach(p => p.classList.remove('active'));
            const desktopPill = document.querySelector(`[data-category="${item.dataset.category}"]`);
            if (desktopPill) {
                desktopPill.classList.add('active');
            }
            
            activeFilters.category = item.dataset.category;
            updateActiveFilters();
            filterFoodItems();
            
            // Close mobile menu after selection
            categoriesToggle.classList.remove('active');
            categoriesGrid.classList.remove('open');
        });
    });

    // Price dropdown handlers
    priceDropdown.addEventListener('click', (e) => {
        e.stopPropagation();
        priceDropdown.classList.toggle('active');
        priceMenu.classList.toggle('open');
    });

    priceItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.stopPropagation();
            
            // Update dropdown text
            const selectedText = item.textContent;
            priceDropdown.querySelector('span').textContent = selectedText;
            
            // Close dropdown
            priceDropdown.classList.remove('active');
            priceMenu.classList.remove('open');
            
            activeFilters.price = item.dataset.price;
            updateActiveFilters();
            filterFoodItems();
        });
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', () => {
        priceDropdown.classList.remove('active');
        priceMenu.classList.remove('open');
    });

    // Update active filters display
    function updateActiveFilters() {
        const filters = [];
        
        if (activeFilters.search) {
            filters.push({
                type: 'search',
                text: `Search: "${activeFilters.search}"`,
                value: activeFilters.search
            });
        }
        
        if (activeFilters.category !== 'all') {
            const categoryName = document.querySelector(`[data-category="${activeFilters.category}"] span`).textContent;
            filters.push({
                type: 'category',
                text: `Category: ${categoryName}`,
                value: activeFilters.category
            });
        }
        
        if (activeFilters.price !== 'all') {
            const priceText = document.querySelector(`[data-price="${activeFilters.price}"]`).textContent;
            filters.push({
                type: 'price',
                text: `Price: ${priceText}`,
                value: activeFilters.price
            });
        }
        
        // Update display
        if (filters.length > 0) {
            activeFiltersContainer.innerHTML = filters.map(filter => `
                <div class="filter-tag" data-type="${filter.type}" data-value="${filter.value}">
                    <span>${filter.text}</span>
                    <button class="filter-tag-remove" onclick="removeFilter('${filter.type}', '${filter.value}')">×</button>
                </div>
            `).join('');
            activeFiltersContainer.style.display = 'flex';
        } else {
            activeFiltersContainer.style.display = 'none';
        }
    }

    // Remove filter
    function removeFilter(type, value) {
        if (type === 'search') {
            searchInput.value = '';
            clearBtn.classList.remove('visible');
            activeFilters.search = '';
        } else if (type === 'category') {
            document.querySelector('[data-category="all"]').classList.add('active');
            document.querySelector(`[data-category="${value}"]`).classList.remove('active');
            activeFilters.category = 'all';
        } else if (type === 'price') {
            priceDropdown.querySelector('span').textContent = 'Price Range';
            activeFilters.price = 'all';
        }
        
        updateActiveFilters();
        filterFoodItems();
    }

    // Perform search
    function performSearch() {
        searchBtn.classList.add('loading');
        
        // Simulate search delay
        setTimeout(() => {
            searchBtn.classList.remove('loading');
            filterFoodItems();
        }, 500);
    }

    // Filter food items based on active filters
    function filterFoodItems() {
        foodCards.forEach(card => {
            let shouldShow = true;
            
            // Category filter
            if (activeFilters.category !== 'all') {
                const cardCategory = card.dataset.category;
                if (cardCategory !== activeFilters.category) {
                    shouldShow = false;
                }
            }
            
            // Search filter
            if (activeFilters.search && shouldShow) {
                const foodName = card.querySelector('.food-name').textContent.toLowerCase();
                const searchTerm = activeFilters.search.toLowerCase();
                if (!foodName.includes(searchTerm)) {
                    shouldShow = false;
                }
            }
            
            // Price filter
            if (activeFilters.price !== 'all' && shouldShow) {
                const priceText = card.querySelector('.current-price').textContent;
                const price = parseFloat(priceText.replace('₱', '').replace(',', ''));
                
                switch (activeFilters.price) {
                    case '0-50':
                        if (price > 50) shouldShow = false;
                        break;
                    case '51-100':
                        if (price < 51 || price > 100) shouldShow = false;
                        break;
                    case '101+':
                        if (price < 101) shouldShow = false;
                        break;
                }
            }
            
            // Show/hide card with animation
            if (shouldShow) {
                card.style.display = 'block';
                card.style.animation = 'fadeInUp 0.3s ease forwards';
            } else {
                card.style.display = 'none';
            }
        });
        
        // Update grid layout
        updateGridLayout();
    }

    // Update grid layout after filtering
    function updateGridLayout() {
        const visibleCards = Array.from(foodCards).filter(card => card.style.display !== 'none');
        
        if (visibleCards.length === 0) {
            // Show no results message
            if (!document.querySelector('.no-results')) {
                const noResults = document.createElement('div');
                noResults.className = 'no-results';
                noResults.innerHTML = `
                    <div style="text-align: center; padding: 40px; color: #64748b;">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="currentColor" style="margin-bottom: 16px; opacity: 0.5;">
                            <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                        </svg>
                        <h3 style="margin-bottom: 8px; color: #1e293b;">No results found</h3>
                        <p>Try adjusting your search or filter criteria</p>
                    </div>
                `;
                foodGrid.appendChild(noResults);
            }
        } else {
            // Remove no results message
            const noResults = document.querySelector('.no-results');
            if (noResults) {
                noResults.remove();
            }
        }
    }

    // Initialize
    updateActiveFilters();
    
    // Make removeFilter globally available
    window.removeFilter = removeFilter;
});

// Additional utility functions
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#4a7c59' : type === 'error' ? '#e53e3e' : '#4a7c59'};
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        animation: slideInRight 0.3s ease;
    `;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Add CSS for notifications
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
