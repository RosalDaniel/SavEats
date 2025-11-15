// Donation History Page JavaScript

(function() {
    'use strict';

    // Initialize donations data from window object
    const donations = window.donations || [];

    // View donation details
    window.viewDonationDetails = function(id) {
        const donation = donations.find(d => d.id === id);
        if (!donation) {
            showToast('Donation not found', 'error');
            return;
        }

        const modal = document.getElementById('donationDetailsModal');
        const modalBody = document.getElementById('modalDonationBody');
        const modalNumber = document.getElementById('modalDonationNumber');

        if (!modal || !modalBody || !modalNumber) return;

        modalNumber.textContent = `Donation ${donation.donation_number}`;

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
            date: donation.created_at_display,
            description: 'Donation record was created in the system'
        });

        if (donation.status === 'collected' && donation.collected_at_display !== 'N/A') {
            timelineEvents.push({
                event: 'Donation Collected',
                date: donation.collected_at_display,
                description: `Collected by ${donation.handler_name}`
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
                            <span class="detail-value">${escapeHtml(donation.donation_number)}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Establishment:</span>
                            <span class="detail-value">${escapeHtml(donation.establishment_name)}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Item Name:</span>
                            <span class="detail-value">${escapeHtml(donation.item_name)}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Category:</span>
                            <span class="detail-value">${escapeHtml(donation.category)}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Quantity:</span>
                            <span class="detail-value">${donation.quantity} ${donation.unit}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Status:</span>
                            <span class="detail-value"><span class="status-badge status-${donation.status}">${donation.status_display}</span></span>
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
                <div class="detail-section">
                    <h3>Schedule & Logistics</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Scheduled Date:</span>
                            <span class="detail-value">${escapeHtml(donation.scheduled_date_display)}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Scheduled Time:</span>
                            <span class="detail-value">${escapeHtml(donation.scheduled_time)}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Pickup Method:</span>
                            <span class="detail-value">${escapeHtml(donation.pickup_method_display)}</span>
                        </div>
                        ${donation.expiry_date ? `
                        <div class="detail-item">
                            <span class="detail-label">Expiry Date:</span>
                            <span class="detail-value">${escapeHtml(donation.expiry_date_display)}</span>
                        </div>
                        ` : ''}
                    </div>
                </div>

                <!-- Collection Information -->
                ${donation.status === 'collected' ? `
                <div class="detail-section">
                    <h3>Collection Information</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Collected At:</span>
                            <span class="detail-value">${escapeHtml(donation.collected_at_display)}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Handler:</span>
                            <span class="detail-value">${escapeHtml(donation.handler_name)}</span>
                        </div>
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
    const closeModal = () => {
        const modal = document.getElementById('donationDetailsModal');
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
    };

    // Show toast notification
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

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Close modal buttons
        const closeModalBtn = document.getElementById('closeDonationModal');
        const closeModalBtn2 = document.getElementById('closeDonationModalBtn');
        const modal = document.getElementById('donationDetailsModal');

        if (closeModalBtn) {
            closeModalBtn.addEventListener('click', closeModal);
        }

        if (closeModalBtn2) {
            closeModalBtn2.addEventListener('click', closeModal);
        }

        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    closeModal();
                }
            });
        }

        // Clear filters button
        const clearFiltersBtn = document.getElementById('clearFilters');
        if (clearFiltersBtn) {
            clearFiltersBtn.addEventListener('click', () => {
                window.location.href = '{{ route("foodbank.donation-history") }}';
            });
        }

        // ESC key to close modal
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeModal();
            }
        });

        // Monthly export button with month selector
        const exportMonthlyBtn = document.getElementById('exportMonthlyBtn');
        const monthSelector = document.getElementById('monthSelector');
        
        if (exportMonthlyBtn && monthSelector) {
            // Update href when month changes
            monthSelector.addEventListener('change', function() {
                const baseUrl = exportMonthlyBtn.getAttribute('data-base-url');
                exportMonthlyBtn.href = baseUrl + '&month=' + this.value;
            });
            
            // Set initial href
            const baseUrl = exportMonthlyBtn.getAttribute('data-base-url');
            exportMonthlyBtn.href = baseUrl + '&month=' + monthSelector.value;
        }
    });
})();

