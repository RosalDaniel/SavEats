// Announcements Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeCollapsibleSections();
    initializeSearch();
    initializeActionButtons();
});

// Initialize collapsible sections
function initializeCollapsibleSections() {
    const sectionHeaders = document.querySelectorAll('.section-header');
    
    sectionHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const section = this.closest('.announcement-section');
            const content = section.querySelector('.section-content');
            const arrow = this.querySelector('.section-arrow');
            
            // Toggle active state
            const isActive = section.classList.contains('active');
            
            if (isActive) {
                // Close section
                section.classList.remove('active');
                content.style.display = 'none';
                arrow.style.transform = 'rotate(0deg)';
            } else {
                // Close all other sections first
                document.querySelectorAll('.announcement-section').forEach(otherSection => {
                    if (otherSection !== section) {
                        otherSection.classList.remove('active');
                        otherSection.querySelector('.section-content').style.display = 'none';
                        otherSection.querySelector('.section-arrow').style.transform = 'rotate(0deg)';
                    }
                });
                
                // Open current section
                section.classList.add('active');
                content.style.display = 'block';
                arrow.style.transform = 'rotate(180deg)';
            }
        });
    });
}

// Initialize search functionality
function initializeSearch() {
    const searchInput = document.querySelector('.search-input');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const announcementItems = document.querySelectorAll('.announcement-item');
            
            announcementItems.forEach(item => {
                const title = item.querySelector('.announcement-title').textContent.toLowerCase();
                const text = item.querySelector('.announcement-text').textContent.toLowerCase();
                
                if (title.includes(searchTerm) || text.includes(searchTerm)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
            
            // Show/hide sections based on visible items
            const sections = document.querySelectorAll('.announcement-section');
            sections.forEach(section => {
                const visibleItems = section.querySelectorAll('.announcement-item:not([style*="display: none"])');
                const sectionContent = section.querySelector('.section-content');
                
                if (visibleItems.length === 0 && searchTerm !== '') {
                    sectionContent.style.display = 'none';
                } else if (section.classList.contains('active')) {
                    sectionContent.style.display = 'block';
                }
            });
        });
    }
}

// Initialize action buttons
function initializeActionButtons() {
    // Filter button
    const filterBtn = document.querySelector('.filter-btn');
    if (filterBtn) {
        filterBtn.addEventListener('click', function() {
            showFilterOptions();
        });
    }
    
    // Sort button
    const sortBtn = document.querySelector('.sort-btn');
    if (sortBtn) {
        sortBtn.addEventListener('click', function() {
            showSortOptions();
        });
    }
    
    // Action buttons in announcements
    const actionButtons = document.querySelectorAll('.action-button');
    actionButtons.forEach(button => {
        button.addEventListener('click', function() {
            handleActionButtonClick(this);
        });
    });
}

// Show filter options
function showFilterOptions() {
    // Create filter dropdown
    const filterOptions = [
        { value: 'all', label: 'All Announcements' },
        { value: 'system', label: 'System Updates' },
        { value: 'sale', label: 'Sales & Offers' },
        { value: 'technical', label: 'Technical Issues' }
    ];
    
    showDropdown('Filter by:', filterOptions, (selectedValue) => {
        filterAnnouncements(selectedValue);
    });
}

// Show sort options
function showSortOptions() {
    const sortOptions = [
        { value: 'newest', label: 'Newest First' },
        { value: 'oldest', label: 'Oldest First' },
        { value: 'type', label: 'By Type' }
    ];
    
    showDropdown('Sort by:', sortOptions, (selectedValue) => {
        sortAnnouncements(selectedValue);
    });
}

// Show dropdown menu
function showDropdown(title, options, callback) {
    // Remove existing dropdown
    const existingDropdown = document.querySelector('.dropdown-menu');
    if (existingDropdown) {
        existingDropdown.remove();
    }
    
    // Create dropdown
    const dropdown = document.createElement('div');
    dropdown.className = 'dropdown-menu';
    dropdown.style.cssText = `
        position: absolute;
        top: 100%;
        right: 0;
        background: white;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        min-width: 200px;
        padding: 8px 0;
    `;
    
    // Add title
    const titleElement = document.createElement('div');
    titleElement.style.cssText = `
        padding: 8px 16px;
        font-weight: 600;
        color: #374151;
        border-bottom: 1px solid #f3f4f6;
        font-size: 14px;
    `;
    titleElement.textContent = title;
    dropdown.appendChild(titleElement);
    
    // Add options
    options.forEach(option => {
        const optionElement = document.createElement('div');
        optionElement.style.cssText = `
            padding: 8px 16px;
            cursor: pointer;
            transition: background-color 0.2s;
            font-size: 14px;
            color: #374151;
        `;
        optionElement.textContent = option.label;
        optionElement.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f9fafb';
        });
        optionElement.addEventListener('mouseleave', function() {
            this.style.backgroundColor = 'transparent';
        });
        optionElement.addEventListener('click', function() {
            callback(option.value);
            dropdown.remove();
        });
        dropdown.appendChild(optionElement);
    });
    
    // Position dropdown
    const button = event.target.closest('.action-btn');
    button.style.position = 'relative';
    button.appendChild(dropdown);
    
    // Close dropdown when clicking outside
    setTimeout(() => {
        document.addEventListener('click', function closeDropdown(e) {
            if (!dropdown.contains(e.target) && !button.contains(e.target)) {
                dropdown.remove();
                document.removeEventListener('click', closeDropdown);
            }
        });
    }, 0);
}

// Filter announcements by type
function filterAnnouncements(filterType) {
    const announcementItems = document.querySelectorAll('.announcement-item');
    
    announcementItems.forEach(item => {
        const icon = item.querySelector('.announcement-icon');
        let itemType = 'all';
        
        if (icon.classList.contains('system')) {
            itemType = 'system';
        } else if (icon.classList.contains('sale')) {
            itemType = 'sale';
        } else if (icon.classList.contains('technical')) {
            itemType = 'technical';
        }
        
        if (filterType === 'all' || itemType === filterType) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
    
    // Update section visibility
    updateSectionVisibility();
}

// Sort announcements
function sortAnnouncements(sortType) {
    const sections = document.querySelectorAll('.announcement-section');
    
    sections.forEach(section => {
        const content = section.querySelector('.section-content');
        const items = Array.from(content.querySelectorAll('.announcement-item'));
        
        if (items.length === 0) return;
        
        items.sort((a, b) => {
            switch (sortType) {
                case 'newest':
                    // Sort by time (assuming newer items come first in HTML)
                    return 0;
                case 'oldest':
                    // Reverse order
                    return 1;
                case 'type':
                    // Sort by icon type
                    const aIcon = a.querySelector('.announcement-icon');
                    const bIcon = b.querySelector('.announcement-icon');
                    const aType = getIconType(aIcon);
                    const bType = getIconType(bIcon);
                    return aType.localeCompare(bType);
                default:
                    return 0;
            }
        });
        
        // Re-append sorted items
        items.forEach(item => content.appendChild(item));
    });
}

// Get icon type for sorting
function getIconType(icon) {
    if (icon.classList.contains('system')) return 'system';
    if (icon.classList.contains('sale')) return 'sale';
    if (icon.classList.contains('technical')) return 'technical';
    return 'other';
}

// Update section visibility based on visible items
function updateSectionVisibility() {
    const sections = document.querySelectorAll('.announcement-section');
    
    sections.forEach(section => {
        const visibleItems = section.querySelectorAll('.announcement-item:not([style*="display: none"])');
        const sectionContent = section.querySelector('.section-content');
        
        if (visibleItems.length === 0) {
            sectionContent.style.display = 'none';
        } else if (section.classList.contains('active')) {
            sectionContent.style.display = 'block';
        }
    });
}

// Handle action button clicks
function handleActionButtonClick(button) {
    const buttonText = button.querySelector('span').textContent;
    
    if (buttonText.includes('Go to Food Listing')) {
        // Redirect to food listing page
        window.location.href = '/consumer/food-listing';
    } else {
        // Handle other action buttons
        console.log('Action button clicked:', buttonText);
    }
}

// Utility function to add new announcements dynamically
function addAnnouncement(section, announcementData) {
    const sectionElement = document.querySelector(`[data-section="${section}"]`).closest('.announcement-section');
    const content = sectionElement.querySelector('.section-content');
    
    const announcementItem = document.createElement('div');
    announcementItem.className = 'announcement-item';
    announcementItem.innerHTML = `
        <div class="announcement-icon ${announcementData.type}">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                ${getIconSVG(announcementData.type)}
            </svg>
        </div>
        <div class="announcement-content">
            <h4 class="announcement-title">${announcementData.title}</h4>
            <p class="announcement-text">${announcementData.text}</p>
            <div class="announcement-meta">
                <span class="announcement-time">${announcementData.time}</span>
            </div>
            ${announcementData.action ? `
                <div class="announcement-action">
                    <button class="action-button">
                        <span>${announcementData.action.text}</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/>
                        </svg>
                    </button>
                </div>
            ` : ''}
        </div>
    `;
    
    content.appendChild(announcementItem);
    
    // Update badge count
    const badge = sectionElement.querySelector('.badge-number');
    const currentCount = parseInt(badge.textContent);
    badge.textContent = currentCount + 1;
}

// Get appropriate SVG for icon type
function getIconSVG(type) {
    const icons = {
        system: '<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>',
        sale: '<path d="M7 4V2C7 1.45 7.45 1 8 1H16C16.55 1 17 1.45 17 2V4H20C20.55 4 21 4.45 21 5S20.55 6 20 6H19V19C19 20.1 18.1 21 17 21H7C5.9 21 5 20.1 5 19V6H4C3.45 6 3 5.55 3 5S3.45 4 4 4H7ZM9 3V4H15V3H9ZM7 6V19H17V6H7Z"/>',
        technical: '<path d="M22.7 19l-9.1-9.1c.9-2.3.4-5-1.5-6.9-2-2-5-2.4-7.4-1.3L9 6 6 9 1.6 4.7C.4 7.1.9 10.1 2.9 12.1c1.9 1.9 4.6 2.4 6.9 1.5l9.1 9.1c.4.4 1 .4 1.4 0l2.3-2.3c.5-.4.5-1.1.1-1.4z"/>'
    };
    
    return icons[type] || icons.system;
}
