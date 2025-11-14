// Impact Reports Page JavaScript
let monthlyChart = null;
let donationChart = null;

document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    initializeTabs();
});

// Initialize the charts
function initializeCharts() {
    initializeMonthlyChart();
    initializeDonationChart();
}

// Initialize the monthly chart
function initializeMonthlyChart() {
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
            backgroundColor: '#ff6b35',
            borderColor: '#ff6b35',
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

    monthlyChart = new Chart(ctx, config);
}

// Initialize the donation pie chart
function initializeDonationChart() {
    const ctx = document.getElementById('donationChart');
    if (!ctx) return;

    const donationDataFromServer = window.donationData || [];
    
    if (donationDataFromServer.length === 0) {
        // Show empty state
        return;
    }

    const colors = ['#ffd700', '#ff6b35', '#84cc16', '#374151', '#ef4444'];
    const labels = donationDataFromServer.map(d => d.category.toUpperCase());
    const data = donationDataFromServer.map(d => d.quantity);
    const backgroundColors = donationDataFromServer.map((d, index) => colors[index % colors.length]);

    const pieData = {
        labels: labels,
        datasets: [{
            data: data,
            backgroundColor: backgroundColors,
            borderWidth: 0,
        }]
    };

    const config = {
        type: 'pie',
        data: pieData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(2) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    };

    donationChart = new Chart(ctx, config);
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
                    backgroundColor: '#ff6b35',
                    borderColor: '#ff6b35',
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
                    backgroundColor: '#ff6b35',
                    borderColor: '#ff6b35',
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
                    backgroundColor: '#ff6b35',
                    borderColor: '#ff6b35',
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
        const titleMap = {
            'daily': 'DAILY EARNING TRENDS',
            'monthly': 'MONTHLY EARNING TRENDS',
            'yearly': 'YEARLY EARNING TRENDS'
        };
        chartTitle.textContent = titleMap[tabType] || 'MONTHLY EARNING TRENDS';
    }

    // Destroy existing chart and create new one
    if (monthlyChart) {
        monthlyChart.destroy();
    }
    
    // Set scale based on tab type
    let yAxisMax, yAxisStepSize;
    switch(tabType) {
        case 'daily':
            yAxisMax = 500;
            yAxisStepSize = 100;
            break;
        case 'monthly':
            yAxisMax = 1000;
            yAxisStepSize = 200;
            break;
        case 'yearly':
            yAxisMax = 10000;
            yAxisStepSize = 2000;
            break;
        default:
            yAxisMax = 1000;
            yAxisStepSize = 200;
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

    monthlyChart = new Chart(ctx, config);
}

