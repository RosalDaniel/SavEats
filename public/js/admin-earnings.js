// Admin Earnings JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeChart();
    initializeTabs();
});

let earningsChart = null;

function initializeChart() {
    const ctx = document.getElementById('earningsChart');
    if (!ctx) return;

    const earningsData = window.earningsData || {};
    const dailyData = earningsData.daily || [];
    const monthlyData = earningsData.monthly || [];

    const labels = dailyData.map(d => d.label);
    const data = dailyData.map(d => d.value);

    const chartData = {
        labels: labels.length > 0 ? labels : [],
        datasets: [{
            label: 'Platform Fees (5%)',
            data: data.length > 0 ? data : [],
            backgroundColor: '#ef4444',
            borderColor: '#ef4444',
            borderWidth: 2,
            borderRadius: 4,
            borderSkipped: false,
        }]
    };

    const config = {
        type: 'bar',
        data: chartData,
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
                            return '₱' + context.parsed.y.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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
                    ticks: {
                        color: '#6b7280',
                        font: {
                            size: 12,
                            weight: '500'
                        },
                        callback: function(value) {
                            return '₱' + value.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                        }
                    },
                    grid: {
                        color: '#e5e7eb',
                        drawBorder: false
                    }
                }
            }
        }
    };

    earningsChart = new Chart(ctx, config);
}

function initializeTabs() {
    const tabs = document.querySelectorAll('.time-tab');
    const earningsData = window.earningsData || {};

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active class from all tabs
            tabs.forEach(t => t.classList.remove('active'));
            // Add active class to clicked tab
            this.classList.add('active');

            const tabType = this.getAttribute('data-tab');
            updateChart(tabType, earningsData);
        });
    });
}

function updateChart(tabType, earningsData) {
    if (!earningsChart) return;

    let labels, data;

    if (tabType === 'monthly') {
        labels = (earningsData.monthly || []).map(d => d.label);
        data = (earningsData.monthly || []).map(d => d.value);
    } else {
        labels = (earningsData.daily || []).map(d => d.label);
        data = (earningsData.daily || []).map(d => d.value);
    }

    earningsChart.data.labels = labels;
    earningsChart.data.datasets[0].data = data;
    earningsChart.update();
}

