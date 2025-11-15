// Foodbank Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize animations and interactions
    initializeAnimations();
    initializeInteractions();
    initializeChart();
});

function initializeAnimations() {
    // Stats animation
    const statValues = document.querySelectorAll('.stat-value');
    statValues.forEach((stat, index) => {
        const finalValue = parseInt(stat.textContent);
        let currentValue = 0;
        const increment = finalValue / 30;
        
        const timer = setInterval(() => {
            currentValue += increment;
            if (currentValue >= finalValue) {
                stat.textContent = finalValue;
                clearInterval(timer);
            } else {
                stat.textContent = Math.floor(currentValue);
            }
        }, 30);
    });

    // Welcome section animation
    const welcomeSection = document.querySelector('.welcome-section');
    if (welcomeSection) {
        welcomeSection.style.opacity = '0';
        welcomeSection.style.transform = 'translateY(-20px)';
        welcomeSection.style.transition = 'all 0.6s ease';
        
        setTimeout(() => {
            welcomeSection.style.opacity = '1';
            welcomeSection.style.transform = 'translateY(0)';
        }, 100);
    }
}

function initializeInteractions() {
    // Button click handlers
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('btn-primary') && e.target.textContent === 'Rate') {
            e.preventDefault();
            showNotification('Rating submitted successfully!', 'success');
        }
        
        if (e.target.classList.contains('btn-secondary') && e.target.textContent === 'View Store') {
            e.preventDefault();
            showNotification('Opening store details...', 'info');
        }

        if (e.target.classList.contains('see-all-link')) {
            e.preventDefault();
            showNotification('Loading all donations...', 'info');
        }
    });

}

function initializeChart() {
    const ctx = document.getElementById('weeklyChart');
    if (!ctx) return;

    // Use real data from server, fallback to empty array if not available
    const weeklyDataFromServer = window.weeklyChartData || [];
    const labels = weeklyDataFromServer.map(d => d.label);
    const data = weeklyDataFromServer.map(d => d.value);

    const weeklyData = {
        labels: labels.length > 0 ? labels : ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'],
        datasets: [{
            label: 'Number of items received',
            data: data.length > 0 ? data : [0, 0, 0, 0, 0, 0, 0],
            backgroundColor: '#ffd700',
            borderColor: '#ffd700',
            borderWidth: 0,
            borderRadius: 4,
            borderSkipped: false,
        }]
    };

    const config = {
        type: 'bar',
        data: weeklyData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#6b7280',
                        font: {
                            size: 12,
                            weight: '500'
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    max: 20, // Weekly: 0-20 items (matching consumer's daily scale)
                    ticks: {
                        stepSize: 4,
                        color: '#6b7280',
                        font: {
                            size: 12,
                            weight: '500'
                        },
                        callback: function(value) {
                            return value;
                        }
                    },
                    grid: {
                        color: '#e5e7eb',
                        drawBorder: false
                    }
                }
            },
            elements: {
                bar: {
                    borderRadius: 4
                }
            }
        }
    };

    new Chart(ctx, config);
}

// Notification system
function showNotification(message, type = 'info') {
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        z-index: 10000;
        transform: translateX(400px);
        transition: transform 0.3s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    `;
    
    const backgrounds = {
        'success': '#4caf50',
        'error': '#f44336',
        'warning': '#ff9800',
        'info': '#2196f3'
    };
    notification.style.background = backgrounds[type] || backgrounds.info;
    
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 10);
    
    setTimeout(() => {
        notification.style.transform = 'translateX(400px)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    }, 3000);
}

// Keyboard navigation support
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        // Close any open modals or menus
        console.log('Escape key pressed');
    }
});

console.log('Food Bank Dashboard initialized successfully!');