// My Impact Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeChart();
    initializeTabs();
    initializeBadges();
});

// Initialize the monthly chart
function initializeChart() {
    const ctx = document.getElementById('monthlyChart');
    if (!ctx) return;

    const monthlyData = {
        labels: ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JULY', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'],
        datasets: [{
            label: 'Number of items saved',
            data: [1000, 800, 100, 300, 400, 900, 850, 500, 50, 200, 400, 900],
            backgroundColor: '#ffd700',
            borderColor: '#ffd700',
            borderWidth: 0,
            borderRadius: 4,
            borderSkipped: false,
        }]
    };

    const config = {
        type: 'bar',
        data: monthlyData,
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
                    max: 1000,
                    ticks: {
                        stepSize: 200,
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

// Initialize tab functionality
function initializeTabs() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all tabs
            tabButtons.forEach(btn => btn.classList.remove('active'));
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Handle tab content switching
            const tabType = this.getAttribute('data-tab');
            switchTab(tabType);
        });
    });
}

// Switch between different chart views
function switchTab(tabType) {
    const ctx = document.getElementById('monthlyChart');
    if (!ctx) return;

    let chartData;
    
    switch(tabType) {
        case 'daily':
            chartData = {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Number of items saved',
                    data: [15, 25, 10, 30, 20, 35, 18],
                    backgroundColor: '#ffd700',
                    borderColor: '#ffd700',
                    borderWidth: 0,
                    borderRadius: 4,
                    borderSkipped: false,
                }]
            };
            break;
        case 'monthly':
            chartData = {
                labels: ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JULY', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'],
                datasets: [{
                    label: 'Number of items saved',
                    data: [1000, 800, 100, 300, 400, 900, 850, 500, 50, 200, 400, 900],
                    backgroundColor: '#ffd700',
                    borderColor: '#ffd700',
                    borderWidth: 0,
                    borderRadius: 4,
                    borderSkipped: false,
                }]
            };
            break;
        case 'yearly':
            chartData = {
                labels: ['2021', '2022', '2023', '2024'],
                datasets: [{
                    label: 'Number of items saved',
                    data: [2500, 3200, 4100, 4800],
                    backgroundColor: '#ffd700',
                    borderColor: '#ffd700',
                    borderWidth: 0,
                    borderRadius: 4,
                    borderSkipped: false,
                }]
            };
            break;
    }

    // Update chart title
    const chartTitle = document.querySelector('.chart-title');
    if (chartTitle) {
        chartTitle.textContent = `${tabType.toUpperCase()} FOOD SAVED`;
    }

    // Destroy existing chart and create new one
    Chart.getChart(ctx)?.destroy();
    
    const config = {
        type: 'bar',
        data: chartData,
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
                    ticks: {
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

// Initialize badge interactions
function initializeBadges() {
    const badges = document.querySelectorAll('.badge');
    
    badges.forEach(badge => {
        badge.addEventListener('click', function() {
            // Add click animation
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
            
            // Handle badge click logic
            const badgeName = this.querySelector('.badge-name')?.textContent;
            if (badgeName) {
                console.log(`Clicked badge: ${badgeName}`);
                // Add any badge-specific functionality here
            }
        });
    });
}

// Utility function to update impact data
function updateImpactData(foodSaved, moneySaved) {
    const foodValue = document.querySelector('.summary-card.food-saved .card-value');
    const moneyValue = document.querySelector('.summary-card.money-saved .card-value');
    
    if (foodValue) {
        foodValue.textContent = foodSaved;
    }
    
    if (moneyValue) {
        moneyValue.textContent = `₱ ${moneySaved.toFixed(2)}`;
    }
}

// Utility function to update badge progress
function updateBadgeProgress(badgeName, percentage) {
    const badge = Array.from(document.querySelectorAll('.badge')).find(b => 
        b.querySelector('.badge-name')?.textContent === badgeName
    );
    
    if (badge) {
        const percentageElement = badge.querySelector('.badge-percentage');
        if (percentageElement) {
            percentageElement.textContent = `${percentage}%`;
        }
        
        // Update badge status based on percentage
        if (percentage >= 100) {
            badge.classList.remove('in-progress', 'locked');
            badge.classList.add('completed');
            
            // Add completed status if not exists
            if (!badge.querySelector('.badge-status.completed')) {
                const statusElement = document.createElement('div');
                statusElement.className = 'badge-status completed';
                statusElement.textContent = 'Completed';
                badge.querySelector('.badge-content').appendChild(statusElement);
            }
        } else if (percentage > 0) {
            badge.classList.remove('completed', 'locked');
            badge.classList.add('in-progress');
        } else {
            badge.classList.remove('completed', 'in-progress');
            badge.classList.add('locked');
        }
    }
}
