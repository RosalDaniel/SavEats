// Foodbank Impact Reports Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeChart();
    initializeTabs();
    initializeContributorsList();
    initializeActionButtons();
});

let foodSavedChart = null;

// Initialize the bar chart (matching consumer's my-impact)
function initializeChart() {
    const ctx = document.getElementById('foodSavedChart');
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
                    max: 400, // Monthly: 0-400 items
                    ticks: {
                        stepSize: 80,
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

    foodSavedChart = new Chart(ctx, config);
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

// Switch between different chart views (matching consumer's my-impact)
function switchTab(tabType) {
    const ctx = document.getElementById('foodSavedChart');
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
    if (foodSavedChart) {
        foodSavedChart.destroy();
    }
    
    // Set scale based on tab type (matching consumer's my-impact)
    let yAxisMax, yAxisStepSize;
    switch(tabType) {
        case 'daily':
            yAxisMax = 20;
            yAxisStepSize = 4;
            break;
        case 'monthly':
            yAxisMax = 400;
            yAxisStepSize = 80;
            break;
        case 'yearly':
            yAxisMax = 5000;
            yAxisStepSize = 1000;
            break;
        default:
            yAxisMax = 400;
            yAxisStepSize = 80;
    }
    
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
                    max: yAxisMax,
                    ticks: {
                        stepSize: yAxisStepSize,
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

    foodSavedChart = new Chart(ctx, config);
}

// Initialize Top Establishment Contributors List
function initializeContributorsList() {
    const contributorsList = document.getElementById('contributorsList');
    
    if (!contributorsList) return;
    
    const contributorsData = window.topContributorsData || [];
    
    if (contributorsData.length === 0) {
        contributorsList.innerHTML = '<div class="no-contributors">No establishment contributors yet</div>';
        return;
    }
    
    contributorsList.innerHTML = contributorsData.map(contributor => {
        const rankIcon = getRankIcon(contributor.rank);
        return `
            <div class="contributor-item">
                <div class="contributor-rank">
                    <span class="rank-number">${rankIcon}</span>
                </div>
                <div class="contributor-info">
                    <div class="contributor-name">${escapeHtml(contributor.establishment_name)}</div>
                    <div class="contributor-stats">
                        <span class="contributor-quantity">${contributor.completed_requests.toLocaleString()} ${contributor.completed_requests === 1 ? 'request' : 'requests'}</span>
                        <span class="contributor-percentage">${contributor.percentage}%</span>
                    </div>
                    <div class="contributor-bar-container">
                        <div class="contributor-bar" style="width: ${contributor.percentage}%; background: ${contributor.color}"></div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

function getRankIcon(rank) {
    const icons = {
        1: '🥇',
        2: '🥈',
        3: '🥉'
    };
    return icons[rank] || `#${rank}`;
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Initialize action buttons
function initializeActionButtons() {
    const exportBtn = document.getElementById('exportBtn');

    if (exportBtn) {
        exportBtn.addEventListener('click', () => {
            showToast('Exporting comprehensive impact report...', 'success');
        });
    }
}

// Download report
window.downloadReport = function(name) {
    showToast(`Downloading: ${name}`, 'success');
};

// Toast notification
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => toast.classList.add('show'), 10);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Add toast styles if not already in CSS
if (!document.querySelector('style[data-toast]')) {
    const style = document.createElement('style');
    style.setAttribute('data-toast', 'true');
    style.textContent = `
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 24px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            z-index: 10000;
            transform: translateX(400px);
            transition: transform 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            max-width: 350px;
        }
        .toast.show {
            transform: translateX(0);
        }
        .toast.success { background: #4caf50; }
        .toast.error { background: #f44336; }
        .toast.warning { background: #ff9800; }
        .toast.info { background: #2196f3; }
    `;
    document.head.appendChild(style);
}

