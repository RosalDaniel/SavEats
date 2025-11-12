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
    
    // Export button functionality
    const exportBtn = document.querySelector('.export-btn');
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            showExportOptions();
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
    
    // Calculate max value for y-axis (round up to nearest 100)
    const maxValue = Math.max(...data.values, 0);
    const yAxisMax = maxValue > 0 ? Math.ceil(maxValue / 100) * 100 : 100;
    
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
                        stepSize: yAxisMax / 5,
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

// Show export options dropdown
function showExportOptions() {
    // Remove existing menu if any
    const existingMenu = document.querySelector('.export-menu');
    if (existingMenu) {
        existingMenu.remove();
        return;
    }
    
    // Create a simple dropdown menu
    const exportMenu = document.createElement('div');
    exportMenu.className = 'export-menu';
    exportMenu.innerHTML = `
        <div class="export-option" onclick="exportToCSV()">Export to CSV</div>
        <div class="export-option" onclick="exportToPDF()">Export to PDF</div>
        <div class="export-option" onclick="exportToExcel()">Export to Excel</div>
    `;
    
    // Style the menu
    exportMenu.style.cssText = `
        position: absolute;
        background: white;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        min-width: 150px;
        padding: 4px 0;
    `;
    
    // Style export options
    const options = exportMenu.querySelectorAll('.export-option');
    options.forEach(option => {
        option.style.cssText = `
            padding: 8px 16px;
            cursor: pointer;
            font-size: 14px;
            color: #374151;
        `;
        option.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f3f4f6';
        });
        option.addEventListener('mouseleave', function() {
            this.style.backgroundColor = 'transparent';
        });
    });
    
    // Position the menu
    const exportBtn = document.querySelector('.export-btn');
    const rect = exportBtn.getBoundingClientRect();
    exportMenu.style.top = (rect.bottom + 5) + 'px';
    exportMenu.style.left = rect.left + 'px';
    
    // Add to document
    document.body.appendChild(exportMenu);
    
    // Remove menu when clicking outside
    document.addEventListener('click', function removeMenu(e) {
        if (!exportMenu.contains(e.target) && !exportBtn.contains(e.target)) {
            exportMenu.remove();
            document.removeEventListener('click', removeMenu);
        }
    });
}

// Export functions
function exportToCSV() {
    console.log('Exporting to CSV');
    // TODO: Implement CSV export
    alert('CSV export functionality coming soon!');
}

function exportToPDF() {
    console.log('Exporting to PDF');
    // TODO: Implement PDF export
    alert('PDF export functionality coming soon!');
}

function exportToExcel() {
    console.log('Exporting to Excel');
    // TODO: Implement Excel export
    alert('Excel export functionality coming soon!');
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
