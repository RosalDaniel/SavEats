// Help Center JavaScript - Static FAQ System
document.addEventListener('DOMContentLoaded', function() {
    initializeHelpCenter();
});

function initializeHelpCenter() {
    // Initialize search functionality (static search through FAQs)
    const searchInput = document.getElementById('helpSearch');
    if (searchInput) {
        searchInput.addEventListener('input', handleSearch);
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchHelp();
            }
        });
    }
    
    // Initialize FAQ accordion functionality
    initializeFAQs();
}

// Search functionality - searches through static FAQ content
function handleSearch() {
    const searchTerm = document.getElementById('helpSearch').value.toLowerCase().trim();
    
    if (searchTerm.length > 2) {
        performSearch(searchTerm);
    } else {
        // Show all FAQs if search is cleared
        document.querySelectorAll('.faq-item').forEach(item => {
            item.style.display = 'block';
        });
    }
}

function searchHelp() {
    const searchTerm = document.getElementById('helpSearch').value.toLowerCase().trim();
    
    if (searchTerm.length > 0) {
        performSearch(searchTerm);
    } else {
        // Show all FAQs
        document.querySelectorAll('.faq-item').forEach(item => {
            item.style.display = 'block';
        });
    }
}

function performSearch(searchTerm) {
    const faqItems = document.querySelectorAll('.faq-item');
    let foundCount = 0;
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question h4')?.textContent.toLowerCase() || '';
        const answer = item.querySelector('.faq-answer')?.textContent.toLowerCase() || '';
        
        if (question.includes(searchTerm) || answer.includes(searchTerm)) {
            item.style.display = 'block';
            foundCount++;
            
            // Highlight matching text (optional enhancement)
            if (question.includes(searchTerm)) {
                item.style.borderLeft = '4px solid #2d5016';
            }
        } else {
            item.style.display = 'none';
        }
    });
    
    // Scroll to first result if found
    if (foundCount > 0) {
        const firstMatch = Array.from(faqItems).find(item => item.style.display !== 'none');
        if (firstMatch) {
            firstMatch.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
}

// FAQ Accordion functionality - only one open at a time
function initializeFAQs() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    // Ensure all FAQs start closed on mobile
    const isMobile = window.innerWidth <= 768;
    if (isMobile) {
        faqItems.forEach(item => {
            item.classList.remove('active');
        });
    }
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        const icon = item.querySelector('.faq-icon');
        
        if (question) {
            // Make the entire question area clickable
            question.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                toggleFAQ(item);
            });
            
            // Ensure icon is also clickable and provides visual feedback
            if (icon) {
                icon.style.cursor = 'pointer';
                icon.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation(); // Prevent double toggle
                    toggleFAQ(item);
                });
            }
        }
    });
    
    // Handle window resize to maintain mobile behavior
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            const isMobileNow = window.innerWidth <= 768;
            if (isMobileNow) {
                // On mobile, ensure all FAQs are closed by default
                faqItems.forEach(item => {
                    if (!item.classList.contains('active')) {
                        item.classList.remove('active');
                    }
                });
            }
        }, 250);
    });
}

function toggleFAQ(faqItem) {
    const isActive = faqItem.classList.contains('active');
    
    // Close all other FAQ items
    document.querySelectorAll('.faq-item').forEach(item => {
        if (item !== faqItem) {
            item.classList.remove('active');
        }
    });
    
    // Toggle current item
    if (!isActive) {
        faqItem.classList.add('active');
    } else {
        faqItem.classList.remove('active');
    }
}

// Scroll to FAQ and open it when tile is clicked
function scrollToFAQ(faqId) {
    const faqItem = document.getElementById(faqId);
    if (!faqItem) return;
    
    // Close all FAQs first
    document.querySelectorAll('.faq-item').forEach(item => {
        item.classList.remove('active');
    });
    
    // Scroll to the FAQ section first
    const faqSection = document.querySelector('.faq-section');
    if (faqSection) {
        faqSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    
    // Wait for scroll, then open the target FAQ
    setTimeout(() => {
        faqItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
        faqItem.classList.add('active');
    }, 300);
}

// Contact functionality
function startLiveChat() {
    // In a real app, this would open a live chat widget
    showNotification('Live chat feature coming soon! Please use email or phone support for now.', 'info');
    
    // Simulate opening chat
    setTimeout(() => {
        // Check if chat window already exists
        const existingChat = document.querySelector('.chat-window');
        if (existingChat) {
            existingChat.remove();
        }
        
        const chatWindow = document.createElement('div');
        chatWindow.className = 'chat-window';
        chatWindow.innerHTML = `
            <div class="chat-header">
                <h4>Live Chat Support</h4>
                <button onclick="closeChat()" class="close-chat" aria-label="Close chat">×</button>
            </div>
            <div class="chat-body">
                <div class="chat-message support">
                    <p>Hello! How can we help you today?</p>
                </div>
            </div>
            <div class="chat-input">
                <input type="text" placeholder="Type your message..." id="chatInput" aria-label="Chat message input">
                <button onclick="sendMessage()" aria-label="Send message">Send</button>
            </div>
        `;
        
        document.body.appendChild(chatWindow);
        
        // Focus on input after animation
        setTimeout(() => {
            const chatInput = document.getElementById('chatInput');
            if (chatInput) {
                chatInput.focus();
            }
        }, 350);
        
        // Allow Enter key to send message
        const chatInput = document.getElementById('chatInput');
        if (chatInput) {
            chatInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    sendMessage();
                }
            });
        }
    }, 500);
}

function closeChat() {
    const chatWindow = document.querySelector('.chat-window');
    if (chatWindow) {
        // Add closing animation
        chatWindow.style.animation = 'slideDown 0.3s ease-out';
        chatWindow.style.opacity = '0';
        chatWindow.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            chatWindow.remove();
        }, 300);
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
            document.querySelectorAll('.faq-item').forEach(item => {
                item.style.display = 'block';
                item.style.borderLeft = '';
            });
        }
    }
});
