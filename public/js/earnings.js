// Earnings Page JavaScript
let earningsChart = null;

document.addEventListener('DOMContentLoaded', function() {
    // Time tab functionality
    const timeTabs = document.querySelectorAll('.time-tab');
    timeTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            timeTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            const period = this.textContent.toLowerCase();
            updateChart(period);
        });
    });
    
    // Export dropdown functionality
    const exportBtn = document.getElementById('exportEarningsBtn');
    const exportMenu = document.getElementById('exportEarningsMenu');
    if (exportBtn && exportMenu) {
        exportBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            exportMenu.classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!exportBtn.contains(e.target) && !exportMenu.contains(e.target)) {
                exportMenu.classList.remove('show');
            }
        });
    }
    
    // Filter buttons functionality
    const filterBtns = document.querySelectorAll('.filter-btn');
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            toggleFilterMenu(this);
        });
    });
    
    // Search functionality
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filterTable(this.value);
        });
    }
    
    // Date picker functionality
    const dateInput = document.querySelector('.date-input');
    if (dateInput) {
        dateInput.addEventListener('change', function() {
            filterByDate(this.value);
        });
    }
    
    // Initialize chart with default data (daily)
    initializeChart('daily');
});

// Initialize Chart.js chart
function initializeChart(period = 'daily') {
    const ctx = document.getElementById('earningsChart');
    if (!ctx) return;
    
    // Destroy existing chart if it exists
    if (earningsChart) {
        earningsChart.destroy();
    }
    
    // Get data for selected period
    const data = getChartData(period);
    
    // Set scale based on period
    let yAxisMax, yAxisStepSize;
    switch(period) {
        case 'daily':
            yAxisMax = 500;
            yAxisStepSize = 100;
            break;
        case 'monthly':
            yAxisMax = 10000;
            yAxisStepSize = 2000;
            break;
        case 'yearly':
            yAxisMax = 100000;
            yAxisStepSize = 20000;
            break;
        default:
            yAxisMax = 500;
            yAxisStepSize = 100;
    }
    
    const config = {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Sales',
                data: data.values,
                backgroundColor: '#ffd700',
                borderColor: '#ffd700',
                borderWidth: 0,
                borderRadius: 4,
                borderSkipped: false,
            }]
        },
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
                            return '₱' + context.parsed.y.toFixed(2);
                        }
                    }
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
                            return '₱' + value.toLocaleString();
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
    
    earningsChart = new Chart(ctx, config);
}

// Get chart data for selected period
function getChartData(period) {
    if (!window.earningsData) {
        return { labels: [], values: [] };
    }
    
    let data = [];
    let labels = [];
    
    switch(period) {
        case 'daily':
            data = window.earningsData.daily || [];
            labels = data.map(item => item.label);
            break;
        case 'monthly':
            data = window.earningsData.monthly || [];
            labels = data.map(item => item.label);
            break;
        case 'yearly':
            data = window.earningsData.yearly || [];
            labels = data.map(item => item.label);
            break;
        default:
            data = window.earningsData.daily || [];
            labels = data.map(item => item.label);
    }
    
    return {
        labels: labels,
        values: data.map(item => item.value || 0)
    };
}

// Update chart based on selected time period
function updateChart(period) {
    const chartTitle = document.querySelector('.chart-title');
    if (chartTitle) {
        chartTitle.textContent = `${period.toUpperCase()} EARNING TRENDS`;
    }
    
    // Reinitialize chart with new data
    initializeChart(period);
}


// Toggle filter menu
function toggleFilterMenu(button) {
    console.log('Toggle filter menu');
    // TODO: Implement filter menu toggle
}

// Filter table based on search input
function filterTable(searchTerm) {
    const tableRows = document.querySelectorAll('.table-row');
    const term = searchTerm.toLowerCase();
    
    tableRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(term)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Filter by date
function filterByDate(date) {
    console.log('Filtering by date:', date);
    // TODO: Implement date filtering
    // You would filter the table rows based on the selected date
}
