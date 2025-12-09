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
        if (e.target.classList.contains('see-all-link')) {
            // Let the link work normally - it should navigate to donation history
        }
    });

    // Initialize modal close handlers
    const closeModalBtn = document.getElementById('closeDonationModal');
    const closeModalBtn2 = document.getElementById('closeDonationModalBtn');
    const modal = document.getElementById('donationDetailsModal');

    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', closeDonationModal);
    }

    if (closeModalBtn2) {
        closeModalBtn2.addEventListener('click', closeDonationModal);
    }

    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeDonationModal();
            }
        });
    }

    // Close modal on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal && modal.classList.contains('show')) {
            closeDonationModal();
        }
    });
}

// View donation details function
window.viewDonationDetails = function(id) {
    const donations = window.recentDonations || [];
    const donation = donations.find(d => d.id === id || String(d.id) === String(id));
    
    if (!donation) {
        showNotification('Donation not found', 'error');
        return;
    }

    const modal = document.getElementById('donationDetailsModal');
    const modalBody = document.getElementById('modalDonationBody');
    const modalNumber = document.getElementById('modalDonationNumber');

    if (!modal || !modalBody || !modalNumber) return;

    modalNumber.textContent = `Donation ${donation.donation_number || donation.id}`;

    // Escape HTML helper
    const escapeHtml = (text) => {
        if (!text) return 'N/A';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    };

    // Build timeline events
    const timelineEvents = [];
    timelineEvents.push({
        event: 'Donation Created',
        date: donation.created_at_display || donation.formatted_date,
        description: 'Donation record was created in the system'
    });

    if (donation.status === 'collected' && donation.collected_at_display && donation.collected_at_display !== 'N/A') {
        timelineEvents.push({
            event: 'Donation Collected',
            date: donation.collected_at_display,
            description: `Collected by ${donation.handler_name || 'Foodbank'}`
        });
    }

    if (donation.status === 'cancelled') {
        timelineEvents.push({
            event: 'Donation Cancelled',
            date: donation.collected_at_display !== 'N/A' ? donation.collected_at_display : donation.created_at_display,
            description: 'Donation was cancelled'
        });
    }

    if (donation.status === 'expired') {
        timelineEvents.push({
            event: 'Donation Expired',
            date: donation.expiry_date_display !== 'N/A' ? donation.expiry_date_display : donation.created_at_display,
            description: 'Donation has expired'
        });
    }

    modalBody.innerHTML = `
        <div class="donation-detail-content">
            <!-- Alerts Section -->
            ${donation.is_urgent || donation.is_nearing_expiry ? `
            <div class="alert-section">
                ${donation.is_urgent ? '<div class="alert alert-urgent">⚠️ Urgent: This donation requires immediate attention</div>' : ''}
                ${donation.is_nearing_expiry ? '<div class="alert alert-expiry">⏰ Expiring Soon: This item is nearing its expiry date</div>' : ''}
            </div>
            ` : ''}

            <!-- Basic Information -->
            <div class="detail-section">
                <h3>Basic Information</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Donation ID:</span>
                        <span class="detail-value">${escapeHtml(donation.donation_number || donation.id)}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Establishment:</span>
                        <span class="detail-value">${escapeHtml(donation.establishment_name)}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Item Name:</span>
                        <span class="detail-value">${escapeHtml(donation.item_name)}</span>
                    </div>
                    ${donation.category ? `
                    <div class="detail-item">
                        <span class="detail-label">Category:</span>
                        <span class="detail-value">${escapeHtml(donation.category)}</span>
                    </div>
                    ` : ''}
                    <div class="detail-item">
                        <span class="detail-label">Quantity:</span>
                        <span class="detail-value">${donation.quantity} ${donation.unit}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Status:</span>
                        <span class="detail-value"><span class="status-badge status-${donation.status}">${donation.status_display || escapeHtml(donation.status)}</span></span>
                    </div>
                </div>
            </div>

            <!-- Description -->
            ${donation.description ? `
            <div class="detail-section">
                <h3>Description</h3>
                <p class="detail-description">${escapeHtml(donation.description)}</p>
            </div>
            ` : ''}

            <!-- Schedule & Logistics -->
            ${donation.scheduled_date_display ? `
            <div class="detail-section">
                <h3>Schedule & Logistics</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Scheduled Date:</span>
                        <span class="detail-value">${escapeHtml(donation.scheduled_date_display)}</span>
                    </div>
                    ${donation.scheduled_time ? `
                    <div class="detail-item">
                        <span class="detail-label">Scheduled Time:</span>
                        <span class="detail-value">${escapeHtml(donation.scheduled_time)}</span>
                    </div>
                    ` : ''}
                    ${donation.pickup_method_display ? `
                    <div class="detail-item">
                        <span class="detail-label">Pickup Method:</span>
                        <span class="detail-value">${escapeHtml(donation.pickup_method_display)}</span>
                    </div>
                    ` : ''}
                    ${donation.expiry_date_display && donation.expiry_date_display !== 'N/A' ? `
                    <div class="detail-item">
                        <span class="detail-label">Expiry Date:</span>
                        <span class="detail-value">${escapeHtml(donation.expiry_date_display)}</span>
                    </div>
                    ` : ''}
                </div>
            </div>
            ` : ''}

            <!-- Collection Information -->
            ${donation.status === 'collected' && donation.collected_at_display ? `
            <div class="detail-section">
                <h3>Collection Information</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Collected At:</span>
                        <span class="detail-value">${escapeHtml(donation.collected_at_display)}</span>
                    </div>
                    ${donation.handler_name && donation.handler_name !== 'N/A' ? `
                    <div class="detail-item">
                        <span class="detail-label">Handler:</span>
                        <span class="detail-value">${escapeHtml(donation.handler_name)}</span>
                    </div>
                    ` : ''}
                </div>
            </div>
            ` : ''}

            <!-- Notes -->
            ${donation.establishment_notes || donation.foodbank_notes ? `
            <div class="detail-section">
                <h3>Notes</h3>
                ${donation.establishment_notes ? `
                <div class="note-item">
                    <span class="note-label">Establishment Notes:</span>
                    <p class="note-content">${escapeHtml(donation.establishment_notes)}</p>
                </div>
                ` : ''}
                ${donation.foodbank_notes ? `
                <div class="note-item">
                    <span class="note-label">Foodbank Notes:</span>
                    <p class="note-content">${escapeHtml(donation.foodbank_notes)}</p>
                </div>
                ` : ''}
            </div>
            ` : ''}

            <!-- Timeline -->
            <div class="detail-section">
                <h3>Donation Timeline</h3>
                <div class="timeline">
                    ${timelineEvents.map((event, index) => `
                        <div class="timeline-item ${index === timelineEvents.length - 1 ? 'active' : ''}">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <div class="timeline-event">${escapeHtml(event.event)}</div>
                                <div class="timeline-date">${escapeHtml(event.date)}</div>
                                <div class="timeline-description">${escapeHtml(event.description)}</div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        </div>
    `;

    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
};

// Close modal function
function closeDonationModal() {
    const modal = document.getElementById('donationDetailsModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

function initializeChart() {
    const ctx = document.getElementById('weeklyChart');
    if (!ctx) return;

    // Use real data from server, fallback to empty array if not available
    const weeklyDataFromServer = window.weeklyChartData || [];
    const labels = weeklyDataFromServer.map(d => d.label);
    const data = weeklyDataFromServer.map(d => d.value);

    // Calculate max value dynamically, with a minimum of 10 and rounded up to nearest 5
    const maxValue = Math.max(...data, 0);
    const chartMax = maxValue > 0 ? Math.ceil((maxValue + 2) / 5) * 5 : 10;
    const stepSize = chartMax <= 10 ? 2 : chartMax <= 20 ? 4 : Math.ceil(chartMax / 5);

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
                    max: chartMax,
                    ticks: {
                        stepSize: stepSize,
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