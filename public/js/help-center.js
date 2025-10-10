// Help Center JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeHelpCenter();
});

function initializeHelpCenter() {
    // Initialize search functionality
    const searchInput = document.getElementById('helpSearch');
    if (searchInput) {
        searchInput.addEventListener('input', handleSearch);
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchHelp();
            }
        });
    }
    
    // Initialize FAQ functionality
    initializeFAQs();
    
    // Initialize category functionality
    initializeCategories();
}

// Search functionality
function handleSearch() {
    const searchTerm = document.getElementById('helpSearch').value.toLowerCase();
    const searchResults = document.querySelector('.search-results');
    
    if (searchTerm.length > 2) {
        performSearch(searchTerm);
    } else {
        hideSearchResults();
    }
}

function searchHelp() {
    const searchTerm = document.getElementById('helpSearch').value.toLowerCase();
    
    if (searchTerm.length > 0) {
        performSearch(searchTerm);
    } else {
        showNotification('Please enter a search term', 'info');
    }
}

function performSearch(searchTerm) {
    // Sample search data - in a real app, this would come from a database
    const helpData = [
        {
            title: 'How to add food listings',
            content: 'Learn how to create and manage your food listings on SavEats platform.',
            category: 'food-listing'
        },
        {
            title: 'Managing orders',
            content: 'Understand how to accept, process, and complete customer orders.',
            category: 'orders'
        },
        {
            title: 'Earnings tracking',
            content: 'Track your earnings, view reports, and understand payment processing.',
            category: 'earnings'
        },
        {
            title: 'Account settings',
            content: 'Update your business information, profile, and account preferences.',
            category: 'account'
        },
        {
            title: 'Getting started guide',
            content: 'Complete guide for new establishments joining SavEats platform.',
            category: 'getting-started'
        },
        {
            title: 'Payment methods',
            content: 'Supported payment methods and how to process payments.',
            category: 'orders'
        },
        {
            title: 'Profile management',
            content: 'How to update your business profile and contact information.',
            category: 'account'
        },
        {
            title: 'Troubleshooting common issues',
            content: 'Solutions for common problems and technical issues.',
            category: 'troubleshooting'
        }
    ];
    
    const results = helpData.filter(item => 
        item.title.toLowerCase().includes(searchTerm) || 
        item.content.toLowerCase().includes(searchTerm)
    );
    
    displaySearchResults(results, searchTerm);
}

function displaySearchResults(results, searchTerm) {
    const searchResults = document.querySelector('.search-results');
    
    if (!searchResults) {
        createSearchResultsContainer();
    }
    
    const container = document.querySelector('.search-results');
    
    if (results.length === 0) {
        container.innerHTML = `
            <div class="no-results">
                <h4>No results found</h4>
                <p>We couldn't find any help articles matching "${searchTerm}". Try different keywords or browse our categories below.</p>
            </div>
        `;
    } else {
        container.innerHTML = `
            <h3>Search Results for "${searchTerm}"</h3>
            ${results.map(result => `
                <div class="search-result-item">
                    <h4>${highlightSearchTerm(result.title, searchTerm)}</h4>
                    <p>${highlightSearchTerm(result.content, searchTerm)}</p>
                </div>
            `).join('')}
        `;
    }
    
    container.classList.add('active');
    container.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function createSearchResultsContainer() {
    const container = document.createElement('div');
    container.className = 'search-results';
    document.querySelector('.quick-help-section').insertAdjacentElement('afterend', container);
}

function highlightSearchTerm(text, searchTerm) {
    const regex = new RegExp(`(${searchTerm})`, 'gi');
    return text.replace(regex, '<mark>$1</mark>');
}

function hideSearchResults() {
    const searchResults = document.querySelector('.search-results');
    if (searchResults) {
        searchResults.classList.remove('active');
    }
}

// FAQ functionality
function initializeFAQs() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        question.addEventListener('click', () => toggleFAQ(item));
    });
}

function toggleFAQ(faqItem) {
    const isActive = faqItem.classList.contains('active');
    
    // Close all other FAQ items
    document.querySelectorAll('.faq-item').forEach(item => {
        item.classList.remove('active');
    });
    
    // Toggle current item
    if (!isActive) {
        faqItem.classList.add('active');
    }
}

// Category functionality
function initializeCategories() {
    const categories = document.querySelectorAll('.help-category');
    
    categories.forEach(category => {
        category.addEventListener('click', () => {
            const categoryType = category.getAttribute('onclick').match(/'([^']+)'/)[1];
            showCategory(categoryType);
        });
    });
}

function showCategory(categoryType) {
    // Hide search results if visible
    hideSearchResults();
    
    // Scroll to FAQ section
    const faqSection = document.querySelector('.faq-section');
    faqSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    
    // Filter FAQs based on category
    filterFAQsByCategory(categoryType);
    
    // Show notification
    const categoryNames = {
        'getting-started': 'Getting Started',
        'food-listing': 'Food Listing',
        'orders': 'Orders & Payments',
        'earnings': 'Earnings & Reports',
        'account': 'Account Settings',
        'troubleshooting': 'Troubleshooting'
    };
    
    showNotification(`Showing help for: ${categoryNames[categoryType] || categoryType}`, 'info');
}

function filterFAQsByCategory(categoryType) {
    const faqItems = document.querySelectorAll('.faq-item');
    
    // For demo purposes, we'll show all FAQs
    // In a real app, you would filter based on category
    faqItems.forEach(item => {
        item.style.display = 'block';
    });
    
    // Highlight relevant FAQs (demo)
    setTimeout(() => {
        faqItems.forEach((item, index) => {
            if (index < 3) { // Show first 3 FAQs as relevant
                item.style.borderLeft = '4px solid #2d5016';
            } else {
                item.style.borderLeft = '4px solid transparent';
            }
        });
    }, 100);
}

// Contact functionality
function startLiveChat() {
    // In a real app, this would open a live chat widget
    showNotification('Live chat feature coming soon! Please use email or phone support for now.', 'info');
    
    // Simulate opening chat
    setTimeout(() => {
        const chatWindow = document.createElement('div');
        chatWindow.className = 'chat-window';
        chatWindow.innerHTML = `
            <div class="chat-header">
                <h4>Live Chat Support</h4>
                <button onclick="closeChat()" class="close-chat">×</button>
            </div>
            <div class="chat-body">
                <div class="chat-message support">
                    <p>Hello! How can we help you today?</p>
                </div>
            </div>
            <div class="chat-input">
                <input type="text" placeholder="Type your message..." id="chatInput">
                <button onclick="sendMessage()">Send</button>
            </div>
        `;
        
        chatWindow.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 350px;
            height: 400px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            display: flex;
            flex-direction: column;
        `;
        
        document.body.appendChild(chatWindow);
    }, 500);
}

function closeChat() {
    const chatWindow = document.querySelector('.chat-window');
    if (chatWindow) {
        chatWindow.remove();
    }
}

function sendMessage() {
    const chatInput = document.getElementById('chatInput');
    const message = chatInput.value.trim();
    
    if (message) {
        const chatBody = document.querySelector('.chat-body');
        const messageDiv = document.createElement('div');
        messageDiv.className = 'chat-message user';
        messageDiv.innerHTML = `<p>${message}</p>`;
        chatBody.appendChild(messageDiv);
        
        chatInput.value = '';
        
        // Simulate support response
        setTimeout(() => {
            const responseDiv = document.createElement('div');
            responseDiv.className = 'chat-message support';
            responseDiv.innerHTML = '<p>Thank you for your message. Our support team will respond shortly.</p>';
            chatBody.appendChild(responseDiv);
            chatBody.scrollTop = chatBody.scrollHeight;
        }, 1000);
        
        chatBody.scrollTop = chatBody.scrollHeight;
    }
}

// Notification system
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        font-family: 'Afacad', sans-serif;
        font-weight: 500;
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + K to focus search
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = document.getElementById('helpSearch');
        if (searchInput) {
            searchInput.focus();
        }
    }
    
    // Escape to clear search
    if (e.key === 'Escape') {
        const searchInput = document.getElementById('helpSearch');
        if (searchInput) {
            searchInput.value = '';
            hideSearchResults();
        }
    }
});
