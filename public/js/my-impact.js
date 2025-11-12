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

    // Use real data from server, fallback to empty array if not available
    const monthlyDataFromServer = window.chartData?.monthly || [];
    const labels = monthlyDataFromServer.map(d => d.label);
    const data = monthlyDataFromServer.map(d => d.value);

    const monthlyData = {
        labels: labels.length > 0 ? labels : ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'],
        datasets: [{
            label: 'Number of items saved',
            data: data.length > 0 ? data : [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
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
    const serverData = window.chartData || {};
    
    switch(tabType) {
        case 'daily':
            const dailyDataFromServer = serverData.daily || [];
            const dailyLabels = dailyDataFromServer.map(d => d.label);
            const dailyValues = dailyDataFromServer.map(d => d.value);
            chartData = {
                labels: dailyLabels.length > 0 ? dailyLabels : ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'],
                datasets: [{
                    label: 'Number of items saved',
                    data: dailyValues.length > 0 ? dailyValues : [0, 0, 0, 0, 0, 0, 0],
                    backgroundColor: '#ffd700',
                    borderColor: '#ffd700',
                    borderWidth: 0,
                    borderRadius: 4,
                    borderSkipped: false,
                }]
            };
            break;
        case 'monthly':
            const monthlyDataFromServer = serverData.monthly || [];
            const monthlyLabels = monthlyDataFromServer.map(d => d.label);
            const monthlyValues = monthlyDataFromServer.map(d => d.value);
            chartData = {
                labels: monthlyLabels.length > 0 ? monthlyLabels : ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'],
                datasets: [{
                    label: 'Number of items saved',
                    data: monthlyValues.length > 0 ? monthlyValues : [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                    backgroundColor: '#ffd700',
                    borderColor: '#ffd700',
                    borderWidth: 0,
                    borderRadius: 4,
                    borderSkipped: false,
                }]
            };
            break;
        case 'yearly':
            const yearlyDataFromServer = serverData.yearly || [];
            const yearlyLabels = yearlyDataFromServer.map(d => d.label);
            const yearlyValues = yearlyDataFromServer.map(d => d.value);
            chartData = {
                labels: yearlyLabels.length > 0 ? yearlyLabels : [],
                datasets: [{
                    label: 'Number of items saved',
                    data: yearlyValues.length > 0 ? yearlyValues : [],
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
