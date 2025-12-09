// Notification System JavaScript
let notificationPollInterval = null;
let unreadCount = 0;

document.addEventListener('DOMContentLoaded', function() {
    initializeNotifications();
});

function initializeNotifications() {
    const notificationBtn = document.getElementById('notificationBtn');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const markAllReadBtn = document.getElementById('markAllReadBtn');
    const viewAllLink = document.getElementById('viewAllNotifications');
    
    if (!notificationBtn || !notificationDropdown) {
        return;
    }
    
    // Load initial unread count
    loadUnreadCount();
    
    // Load notifications
    loadNotifications();
    
    // Toggle dropdown
    notificationBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        toggleNotificationDropdown();
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!notificationDropdown.contains(e.target) && !notificationBtn.contains(e.target)) {
            closeNotificationDropdown();
        }
    });
    
    // Mark all as read
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            markAllAsRead();
        });
    }
    
    // View all notifications link
    if (viewAllLink) {
        viewAllLink.addEventListener('click', function(e) {
            e.preventDefault();
            // Could navigate to a full notifications page
            // For now, just show all notifications in dropdown
            loadNotifications('all');
        });
    }
    
    // Poll for new notifications every 30 seconds
    notificationPollInterval = setInterval(function() {
        loadUnreadCount();
    }, 30000);
}

function toggleNotificationDropdown() {
    const dropdown = document.getElementById('notificationDropdown');
    if (dropdown) {
        dropdown.classList.toggle('active');
        if (dropdown.classList.contains('active')) {
            loadNotifications();
        }
    }
}

function closeNotificationDropdown() {
    const dropdown = document.getElementById('notificationDropdown');
    if (dropdown) {
        dropdown.classList.remove('active');
    }
}

function loadUnreadCount() {
    fetch('/notifications/unread-count')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                unreadCount = data.count;
                updateNotificationBadge(unreadCount);
            }
        })
        .catch(error => {
            console.error('Error loading unread count:', error);
        });
}

function updateNotificationBadge(count) {
    const badge = document.getElementById('notificationBadge');
    if (badge) {
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }
}

function loadNotifications(filter = 'unread') {
    const notificationList = document.getElementById('notificationList');
    if (!notificationList) return;
    
    notificationList.innerHTML = '<div class="notification-loading">Loading notifications...</div>';
    
    const url = filter === 'all' 
        ? '/notifications?per_page=10'
        : '/notifications?filter=unread&per_page=10';
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const notifications = Array.isArray(data.data) 
                    ? data.data 
                    : (data.data.data || []);
                renderNotifications(notifications);
            } else {
                notificationList.innerHTML = '<div class="no-notifications">No notifications</div>';
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            notificationList.innerHTML = '<div class="notification-error">Error loading notifications</div>';
        });
}

function renderNotifications(notifications) {
    const notificationList = document.getElementById('notificationList');
    if (!notificationList) return;
    
    if (notifications.length === 0) {
        notificationList.innerHTML = '<div class="no-notifications">No notifications</div>';
        return;
    }
    
    notificationList.innerHTML = notifications.map(notification => {
        const timeAgo = getTimeAgo(notification.created_at);
        const readClass = notification.is_read ? 'read' : 'unread';
        const icon = getNotificationIcon(notification.type);
        
        return `
            <div class="notification-item ${readClass}" data-id="${notification.id}" onclick="handleNotificationClick(${notification.id}, '${notification.type}')">
                <div class="notification-icon">${icon}</div>
                <div class="notification-content">
                    <div class="notification-title">${escapeHtml(notification.title)}</div>
                    <div class="notification-message">${escapeHtml(notification.message)}</div>
                    <div class="notification-time">${timeAgo}</div>
                </div>
                ${!notification.is_read ? '<div class="notification-unread-dot"></div>' : ''}
            </div>
        `;
    }).join('');
}

function getNotificationIcon(type) {
    const icons = {
        'order_placed': '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M7 18c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12L8.1 13h7.45c.75 0 1.41-.41 1.75-1.03L21.7 4H5.21l-.94-2H1zm16 16c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>',
        'order_accepted': '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>',
        'order_cancelled': '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 11H7v-2h10v2z"/></svg>',
        'order_completed': '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>',
        'donation_offered': '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>',
        'donation_approved': '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>',
        'donation_collected': '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>',
        'donation_requested': '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>',
        'food_listing_deleted': '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>',
        'food_listing_disabled': '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>',
        'food_listing_enabled': '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>',
    };
    
    return icons[type] || '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>';
}

function getTimeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);
    
    if (diffInSeconds < 60) {
        return 'Just now';
    } else if (diffInSeconds < 3600) {
        const minutes = Math.floor(diffInSeconds / 60);
        return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
    } else if (diffInSeconds < 86400) {
        const hours = Math.floor(diffInSeconds / 3600);
        return `${hours} hour${hours > 1 ? 's' : ''} ago`;
    } else if (diffInSeconds < 604800) {
        const days = Math.floor(diffInSeconds / 86400);
        return `${days} day${days > 1 ? 's' : ''} ago`;
    } else {
        return date.toLocaleDateString();
    }
}

function handleNotificationClick(notificationId, type) {
    // Mark as read
    markAsRead(notificationId);
    
    // Navigate based on notification type
    if (type.startsWith('order_')) {
        // Navigate to order details
        const userType = getCurrentUserType();
        if (userType === 'consumer') {
            window.location.href = '/consumer/my-orders';
        } else if (userType === 'establishment') {
            window.location.href = '/establishment/order-management';
        }
    } else if (type.startsWith('donation_')) {
        // Navigate to donation page
        const userType = getCurrentUserType();
        if (userType === 'establishment') {
            window.location.href = '/establishment/donation-hub';
        } else if (userType === 'foodbank') {
            window.location.href = '/foodbank/donation-request';
        }
    }
}

function markAsRead(notificationId) {
    fetch(`/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI
            const notificationItem = document.querySelector(`.notification-item[data-id="${notificationId}"]`);
            if (notificationItem) {
                notificationItem.classList.remove('unread');
                notificationItem.classList.add('read');
                const unreadDot = notificationItem.querySelector('.notification-unread-dot');
                if (unreadDot) {
                    unreadDot.remove();
                }
            }
            // Update count
            loadUnreadCount();
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    });
}

function markAllAsRead() {
    fetch('/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload notifications
            loadNotifications();
            // Update count
            loadUnreadCount();
        }
    })
    .catch(error => {
        console.error('Error marking all as read:', error);
    });
}

function getCurrentUserType() {
    // Try to get from URL or session
    const path = window.location.pathname;
    if (path.includes('/consumer/')) return 'consumer';
    if (path.includes('/establishment/')) return 'establishment';
    if (path.includes('/foodbank/')) return 'foodbank';
    return 'consumer'; // default
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (notificationPollInterval) {
        clearInterval(notificationPollInterval);
    }
});

