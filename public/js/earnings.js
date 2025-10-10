// Earnings Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Time tab functionality
    const timeTabs = document.querySelectorAll('.time-tab');
    timeTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            timeTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Here you would update the chart data based on the selected time period
            console.log('Selected time period:', this.textContent);
            updateChart(this.textContent.toLowerCase());
        });
    });
    
    // Export button functionality
    const exportBtn = document.querySelector('.export-btn');
    exportBtn.addEventListener('click', function() {
        // Here you would implement export functionality
        console.log('Export clicked');
        showExportOptions();
    });
    
    // Filter buttons functionality
    const filterBtns = document.querySelectorAll('.filter-btn');
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Here you would implement filter functionality
            console.log('Filter clicked');
            toggleFilterMenu(this);
        });
    });
    
    // Search functionality
    const searchInput = document.querySelector('.search-input');
    searchInput.addEventListener('input', function() {
        // Here you would implement search functionality
        console.log('Search:', this.value);
        filterTable(this.value);
    });
    
    // Date picker functionality
    const dateInput = document.querySelector('.date-input');
    dateInput.addEventListener('change', function() {
        // Here you would implement date filtering
        console.log('Date selected:', this.value);
        filterByDate(this.value);
    });
    
    // Initialize chart with default data
    initializeChart();
});

// Update chart based on selected time period
function updateChart(period) {
    const chartBars = document.querySelectorAll('.chart-bar');
    const chartTitle = document.querySelector('.chart-title');
    
    // Update chart title
    chartTitle.textContent = `${period.toUpperCase()} EARNING TRENDS`;
    
    // Here you would fetch new data based on the period
    // For now, we'll just log the period
    console.log(`Updating chart for ${period} period`);
    
    // Example: You could animate the bars or update their heights
    chartBars.forEach((bar, index) => {
        // Add animation class for visual feedback
        bar.style.transition = 'all 0.5s ease';
        // You would update the height based on new data
        // bar.style.height = newData[index] + '%';
    });
}

// Show export options dropdown
function showExportOptions() {
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
    `;
    
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
    // Implement CSV export
}

function exportToPDF() {
    console.log('Exporting to PDF');
    // Implement PDF export
}

function exportToExcel() {
    console.log('Exporting to Excel');
    // Implement Excel export
}

// Toggle filter menu
function toggleFilterMenu(button) {
    console.log('Toggle filter menu');
    // Implement filter menu toggle
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
    // Implement date filtering
    // You would filter the table rows based on the selected date
}

// Initialize chart with sample data
function initializeChart() {
    const chartBars = document.querySelectorAll('.chart-bar');
    
    // Add hover effects and tooltips
    chartBars.forEach((bar, index) => {
        bar.addEventListener('mouseenter', function() {
            showTooltip(this, index);
        });
        
        bar.addEventListener('mouseleave', function() {
            hideTooltip();
        });
    });
}

// Show tooltip on bar hover
function showTooltip(bar, index) {
    const tooltip = document.createElement('div');
    tooltip.className = 'chart-tooltip';
    tooltip.textContent = `Day ${index + 1}: ₱${Math.floor(Math.random() * 100)}`;
    
    tooltip.style.cssText = `
        position: absolute;
        background: #374151;
        color: white;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 12px;
        z-index: 1000;
        pointer-events: none;
    `;
    
    const rect = bar.getBoundingClientRect();
    tooltip.style.top = (rect.top - 40) + 'px';
    tooltip.style.left = (rect.left + rect.width / 2) + 'px';
    tooltip.style.transform = 'translateX(-50%)';
    
    document.body.appendChild(tooltip);
}

// Hide tooltip
function hideTooltip() {
    const tooltip = document.querySelector('.chart-tooltip');
    if (tooltip) {
        tooltip.remove();
    }
}

// Pagination functionality
function goToPage(page) {
    console.log('Going to page:', page);
    // Implement pagination
}

function goToPreviousPage() {
    console.log('Going to previous page');
    // Implement previous page
}

function goToNextPage() {
    console.log('Going to next page');
    // Implement next page
}
