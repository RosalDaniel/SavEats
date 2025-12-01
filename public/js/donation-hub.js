// Donation Hub Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeModals();
    initializeRequestToDonateForm();
    initializeRequestToDonateFoodbankForm();
    initializeFilters();
});

// Initialize Filters
function initializeFilters() {
    // Donation Requests Filters
    const requestSearchInput = document.getElementById('requestSearchInput');
    const requestStatusFilter = document.getElementById('requestStatusFilter');
    const requestCategoryFilter = document.getElementById('requestCategoryFilter');
    const clearRequestFilters = document.getElementById('clearRequestFilters');

    // Food Banks Filters
    const foodbankSearchInput = document.getElementById('foodbankSearchInput');
    const clearFoodbankFilters = document.getElementById('clearFoodbankFilters');

    // Donation Requests filter handlers
    if (requestSearchInput) {
        requestSearchInput.addEventListener('input', filterDonationRequests);
    }
    if (requestStatusFilter) {
        requestStatusFilter.addEventListener('change', filterDonationRequests);
    }
    if (requestCategoryFilter) {
        requestCategoryFilter.addEventListener('change', filterDonationRequests);
    }
    if (clearRequestFilters) {
        clearRequestFilters.addEventListener('click', () => {
            if (requestSearchInput) requestSearchInput.value = '';
            if (requestStatusFilter) requestStatusFilter.value = '';
            if (requestCategoryFilter) requestCategoryFilter.value = '';
            filterDonationRequests();
        });
    }

    // Food Banks filter handlers
    if (foodbankSearchInput) {
        foodbankSearchInput.addEventListener('input', filterFoodbanks);
    }
    if (clearFoodbankFilters) {
        clearFoodbankFilters.addEventListener('click', () => {
            if (foodbankSearchInput) foodbankSearchInput.value = '';
            filterFoodbanks();
        });
    }
}

// Filter Donation Requests
function filterDonationRequests() {
    const requests = window.donationRequests || [];
    const requestsGrid = document.getElementById('requestsGrid');
    const requestsCount = document.getElementById('requestsCount');
    
    if (!requestsGrid) return;

    const searchInput = document.getElementById('requestSearchInput');
    const statusFilter = document.getElementById('requestStatusFilter');
    const categoryFilter = document.getElementById('requestCategoryFilter');

    const searchTerm = (searchInput?.value || '').toLowerCase().trim();
    const statusValue = statusFilter?.value || '';
    const categoryValue = categoryFilter?.value || '';

    const filtered = requests.filter(request => {
        // Search filter
        const matchesSearch = !searchTerm || 
            (request.foodbank_name || '').toLowerCase().includes(searchTerm) ||
            (request.item_name || '').toLowerCase().includes(searchTerm);

        // Status filter
        const matchesStatus = !statusValue || 
            (request.status || '').toLowerCase() === statusValue.toLowerCase();

        // Category filter
        const matchesCategory = !categoryValue || 
            (request.category || '').toLowerCase() === categoryValue.toLowerCase();

        return matchesSearch && matchesStatus && matchesCategory;
    });

    // Render filtered requests
    renderDonationRequests(filtered);
    
    // Update count
    if (requestsCount) {
        requestsCount.textContent = `${filtered.length} Request${filtered.length !== 1 ? 's' : ''}`;
    }
}

// Render Donation Requests
function renderDonationRequests(requests) {
    const requestsGrid = document.getElementById('requestsGrid');
    if (!requestsGrid) return;

    if (requests.length === 0) {
        requestsGrid.innerHTML = '<div class="no-requests"><p>No donation requests found matching your filters.</p></div>';
        return;
    }

    requestsGrid.innerHTML = requests.map(request => {
        const logoLabel = request.foodbank_name ? 
            ucwords((request.foodbank_name || '').toLowerCase().substring(0, 6)) : 'FOOD';
        
        return `
            <div class="request-card" data-id="${escapeHtml(request.id)}">
                <div class="request-card-header">
                    <div class="request-logo-circle">
                        <div class="logo-wheat-top">🌾</div>
                        <div class="logo-bread">🍞</div>
                        <div class="logo-label">${escapeHtml(logoLabel)}</div>
                    </div>
                </div>
                <div class="request-card-body">
                    <h4 class="request-foodbank-name">${escapeHtml(request.foodbank_name || 'Food Bank')}</h4>
                    <p class="request-item-name">${escapeHtml(request.item_name || 'N/A')}</p>
                    <p class="request-quantity">${request.quantity || 0} pcs. • ${escapeHtml(ucfirst(request.category || 'N/A'))}</p>
                </div>
                <div class="request-card-actions">
                    <button class="btn-view-details-outline" onclick="viewRequestDetails('${escapeHtml(request.id)}')">View Details</button>
                    <button class="btn-donate-now" onclick="donateNow('${escapeHtml(request.id)}')">Donate Now</button>
                </div>
            </div>
        `;
    }).join('');
}

// Filter Foodbanks
function filterFoodbanks() {
    const foodbanks = window.foodbanks || [];
    const foodbanksGrid = document.getElementById('foodbanksGrid');
    const foodbanksCount = document.getElementById('foodbanksCount');
    
    if (!foodbanksGrid) return;

    const searchInput = document.getElementById('foodbankSearchInput');
    const searchTerm = (searchInput?.value || '').toLowerCase().trim();

    const filtered = foodbanks.filter(foodbank => {
        const matchesSearch = !searchTerm || 
            (foodbank.organization_name || '').toLowerCase().includes(searchTerm) ||
            (foodbank.address || '').toLowerCase().includes(searchTerm);

        return matchesSearch;
    });

    // Render filtered foodbanks
    renderFoodbanks(filtered);
    
    // Update count
    if (foodbanksCount) {
        foodbanksCount.textContent = `${filtered.length} Food Bank${filtered.length !== 1 ? 's' : ''}`;
    }
}

// Render Foodbanks
function renderFoodbanks(foodbanks) {
    const foodbanksGrid = document.getElementById('foodbanksGrid');
    if (!foodbanksGrid) return;

    if (foodbanks.length === 0) {
        foodbanksGrid.innerHTML = '<div class="no-foodbanks"><p>No food banks found matching your filters.</p></div>';
        return;
    }

    foodbanksGrid.innerHTML = foodbanks.map(foodbank => {
        const logoLabel = foodbank.organization_name ? 
            ucwords((foodbank.organization_name || '').toLowerCase().substring(0, 6)) : 'FOOD';
        
        return `
            <div class="foodbank-card" data-id="${escapeHtml(foodbank.id)}">
                <div class="foodbank-card-header">
                    <div class="foodbank-logo-circle">
                        <div class="logo-wheat-top">🌾</div>
                        <div class="logo-bread">🍞</div>
                        <div class="logo-label">${escapeHtml(logoLabel)}</div>
                    </div>
                </div>
                <div class="foodbank-card-body">
                    <h4 class="foodbank-name">${escapeHtml(foodbank.organization_name || 'Food Bank')}</h4>
                    <p class="foodbank-address">${escapeHtml(foodbank.address || 'Not provided')}</p>
                </div>
                <div class="foodbank-card-actions">
                    <button class="btn-view-details-outline" onclick="viewFoodbankDetails('${escapeHtml(foodbank.id)}')">View Details</button>
                    <button class="btn-contact" onclick="contactFoodbank('${escapeHtml(foodbank.id)}')">Contact</button>
                    <button class="btn-request-donate" onclick="requestToDonate('${escapeHtml(foodbank.id)}')">Request to Donate</button>
                </div>
            </div>
        `;
    }).join('');
}

// Helper functions
function ucwords(str) {
    if (!str) return '';
    return str.toLowerCase().replace(/\b\w/g, l => l.toUpperCase());
}

function ucfirst(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
}

// Initialize modals
function initializeModals() {
    // Request Details Modal
    const requestDetailsModal = document.getElementById('requestDetailsModal');
    const closeRequestDetailsModal = document.getElementById('closeRequestDetailsModal');
    
    if (closeRequestDetailsModal) {
        closeRequestDetailsModal.addEventListener('click', () => closeModal('requestDetailsModal'));
    }
    
    if (requestDetailsModal) {
        requestDetailsModal.addEventListener('click', (e) => {
            if (e.target === requestDetailsModal) {
                closeModal('requestDetailsModal');
            }
        });
    }

    // Foodbank Details Modal
    const foodbankDetailsModal = document.getElementById('foodbankDetailsModal');
    const closeFoodbankDetailsModal = document.getElementById('closeFoodbankDetailsModal');
    const closeFoodbankDetailsModalBtn = document.getElementById('closeFoodbankDetailsModalBtn');
    
    if (closeFoodbankDetailsModal) {
        closeFoodbankDetailsModal.addEventListener('click', () => closeModal('foodbankDetailsModal'));
    }
    
    if (closeFoodbankDetailsModalBtn) {
        closeFoodbankDetailsModalBtn.addEventListener('click', () => closeModal('foodbankDetailsModal'));
    }
    
    if (foodbankDetailsModal) {
        foodbankDetailsModal.addEventListener('click', (e) => {
            if (e.target === foodbankDetailsModal) {
                closeModal('foodbankDetailsModal');
            }
        });
    }

    // Request to Donate Modal
    const requestToDonateModal = document.getElementById('requestToDonateModal');
    const closeRequestToDonateModal = document.getElementById('closeRequestToDonateModal');
    
    const resetDonationForm = () => {
        const form = document.getElementById('requestToDonateForm');
        if (form) {
            const foodbankId = document.getElementById('requestDonateFoodbankId')?.value;
            form.reset();
            if (foodbankId) {
                document.getElementById('requestDonateFoodbankId').value = foodbankId;
            }
            // Clear the fulfill request ID
            window.currentFulfillRequestId = null;
        }
    };
    
    if (closeRequestToDonateModal) {
        closeRequestToDonateModal.addEventListener('click', () => {
            closeModal('requestToDonateModal');
            resetDonationForm();
        });
    }
    
    if (requestToDonateModal) {
        requestToDonateModal.addEventListener('click', (e) => {
            if (e.target === requestToDonateModal) {
                closeModal('requestToDonateModal');
                resetDonationForm();
            }
        });
    }

    // Contact Foodbank Modal
    const contactFoodbankModal = document.getElementById('contactFoodbankModal');
    const closeContactFoodbankModal = document.getElementById('closeContactFoodbankModal');
    const closeContactFoodbankModalBtn = document.getElementById('closeContactFoodbankModalBtn');
    
    if (closeContactFoodbankModal) {
        closeContactFoodbankModal.addEventListener('click', () => closeModal('contactFoodbankModal'));
    }
    
    if (closeContactFoodbankModalBtn) {
        closeContactFoodbankModalBtn.addEventListener('click', () => closeModal('contactFoodbankModal'));
    }
    
    if (contactFoodbankModal) {
        contactFoodbankModal.addEventListener('click', (e) => {
            if (e.target === contactFoodbankModal) {
                closeModal('contactFoodbankModal');
            }
        });
    }

    // ESC key to close modals
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeModal('requestDetailsModal');
            closeModal('foodbankDetailsModal');
            closeModal('requestToDonateModal');
            closeModal('contactFoodbankModal');
        }
    });
}

// Close modal function
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

// View Request Details
window.viewRequestDetails = function(id) {
    const donationRequests = window.donationRequests || [];
    const request = donationRequests.find(r => r.id === id || String(r.id) === String(id));
    
    if (!request) {
        showToast('Request not found', 'error');
        return;
    }

    // Format phone number
    function formatPhoneNumber(phone) {
        if (!phone) return 'N/A';
        const cleaned = phone.replace(/\D/g, '');
        if (cleaned.length === 13) {
            return `+${cleaned.slice(0, 2)} | ${cleaned.slice(2, 5)} - ${cleaned.slice(5, 8)} - ${cleaned.slice(8)}`;
        }
        return phone;
    }

    // Format date available
    function formatDateAvailable() {
        const dateDisplay = request.dropoff_date_display || request.dropoff_date;
        if (request.time_option === 'allDay') {
            return `${dateDisplay} - All Day`;
        } else if (request.time_option === 'anytime') {
            return `${dateDisplay} - Anytime`;
        } else if (request.time_display && request.time_display !== 'N/A') {
            return `${dateDisplay} - ${request.time_display}`;
        }
        return dateDisplay;
    }

    // Format distribution zones
    function formatDistributionZones() {
        if (!request.distribution_zone) return 'N/A';
        const zoneLabels = {
            'zone-a': 'Zone A - North District',
            'zone-b': 'Zone B - South District',
            'zone-c': 'Zone C - East District',
            'zone-d': 'Zone D - West District',
            'zone-e': 'Zone E - Central District'
        };
        return zoneLabels[request.distribution_zone] || request.distribution_zone;
    }

    // Get foodbank name and extract first word for logo
    const foodbankName = request.foodbank_name || 'Food Bank';
    const logoTop = foodbankName.split(' ')[0].toUpperCase().substring(0, 4);

    // Get modal elements
    const modal = document.getElementById('requestDetailsModal');
    if (!modal) {
        showToast('Modal not found', 'error');
        return;
    }

    // Populate modal
    const setElementText = (id, text) => {
        const el = document.getElementById(id);
        if (el) el.textContent = text;
    };

    setElementText('modalFoodbankName', foodbankName);
    setElementText('modalLogoTop', logoTop);
    setElementText('modalItemName', request.item_name || 'N/A');
    setElementText('modalItemQuantity', `${request.quantity || 0} pcs.`);
    setElementText('modalDescription', request.description || 'No description provided.');
    setElementText('modalPhone', formatPhoneNumber(request.phone_number));
    setElementText('modalAddress', request.address || 'N/A');
    setElementText('modalEmail', request.email || 'N/A');
    setElementText('modalDateAvailable', formatDateAvailable());
    setElementText('modalDeliveryOption', request.delivery_option_display || 'N/A');
    setElementText('modalDistributionZones', formatDistributionZones());

    // Store current request ID for contact function
    window.currentViewRequestId = id;

    // Set donate now button action
    const donateBtn = document.getElementById('modalDonateNowBtn');
    if (donateBtn) {
        donateBtn.onclick = () => window.donateNow(id);
    }

    // Show modal
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
};

// Donate Now - Fulfill a donation request
window.donateNow = function(requestId) {
    const donationRequests = window.donationRequests || [];
    const request = donationRequests.find(r => r.id === requestId || String(r.id) === String(requestId));
    
    if (!request) {
        showToast('Request not found', 'error');
        return;
    }

    // Store the request ID for later use
    window.currentFulfillRequestId = requestId;
    
    // Prepare item details for pre-filling
    const itemDetails = {
        item_name: request.item_name || '',
        category: request.category || '',
        quantity: request.quantity || 1,
        description: request.description || ''
    };
    
    // Open request to donate modal with pre-filled foodbank ID and item details
    requestToDonate(request.foodbank_id, itemDetails);
};

// Contact Foodbank from Request Details
window.contactFoodbankFromRequest = function() {
    const donationRequests = window.donationRequests || [];
    const requestDetailsModal = document.getElementById('requestDetailsModal');
    
    if (!requestDetailsModal || !requestDetailsModal.classList.contains('show')) {
        return;
    }
    
    // Get the current request ID from the modal
    const currentRequestId = window.currentViewRequestId;
    if (!currentRequestId) {
        showToast('Request not found', 'error');
        return;
    }
    
    const request = donationRequests.find(r => r.id === currentRequestId || String(r.id) === String(currentRequestId));
    if (!request) {
        showToast('Request not found', 'error');
        return;
    }
    
    // Close request details modal and open contact modal
    closeModal('requestDetailsModal');
    contactFoodbank(request.foodbank_id);
};

// View Foodbank Details
window.viewFoodbankDetails = function(id) {
    const foodbanks = window.foodbanks || [];
    const foodbank = foodbanks.find(f => f.id === id || String(f.id) === String(id));
    
    if (!foodbank) {
        showToast('Foodbank not found', 'error');
        return;
    }

    const modal = document.getElementById('foodbankDetailsModal');
    const modalBody = document.getElementById('foodbankDetailsModalBody');
    const modalName = document.getElementById('modalFoodbankDetailsName');

    if (!modal || !modalBody || !modalName) return;

    modalName.textContent = foodbank.organization_name;

    const escapeHtml = (text) => {
        if (!text) return 'N/A';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    };

    modalBody.innerHTML = `
        <div class="foodbank-detail-content">
            <div class="detail-section">
                <h3>Contact Information</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Organization Name:</span>
                        <span class="detail-value">${escapeHtml(foodbank.organization_name)}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Contact Person:</span>
                        <span class="detail-value">${escapeHtml(foodbank.contact_person)}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value">${escapeHtml(foodbank.email)}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Phone Number:</span>
                        <span class="detail-value">${escapeHtml(foodbank.phone_no)}</span>
                    </div>
                </div>
            </div>

            <div class="detail-section">
                <h3>Location</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Address:</span>
                        <span class="detail-value">${escapeHtml(foodbank.address)}</span>
                    </div>
                </div>
            </div>

            <div class="detail-section">
                <h3>Registration</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Registration Number:</span>
                        <span class="detail-value">${escapeHtml(foodbank.registration_number)}</span>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Set request to donate button action
    const requestBtn = document.getElementById('requestDonateFromDetailsBtn');
    if (requestBtn) {
        requestBtn.onclick = () => {
            closeModal('foodbankDetailsModal');
            requestToDonate(id);
        };
    }

    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
};

// Contact Foodbank
window.contactFoodbank = function(id) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    fetch(`/establishment/foodbank/contact/${id}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const foodbank = data.data;
    const modal = document.getElementById('contactFoodbankModal');
    const modalBody = document.getElementById('contactFoodbankModalBody');
    const modalTitle = document.getElementById('contactFoodbankModalTitle');

    if (!modal || !modalBody || !modalTitle) return;

    modalTitle.textContent = `Contact ${foodbank.organization_name || 'Food Bank'}`;

    const escapeHtml = (text) => {
        if (!text) return 'N/A';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    };

    // Format phone number for clickable link
    const formatPhoneForLink = (phone) => {
        if (!phone || phone === 'Not provided') return null;
        const cleaned = phone.replace(/\D/g, '');
        return cleaned.length > 0 ? `tel:${cleaned}` : null;
    };

    // Format email for clickable link
    const formatEmailForLink = (email) => {
        if (!email || email === 'Not provided') return null;
        return `mailto:${email}`;
    };

    const phoneLink = formatPhoneForLink(foodbank.phone_no);
    const emailLink = formatEmailForLink(foodbank.email);

    modalBody.innerHTML = `
        <div class="contact-foodbank-content">
            <div class="contact-section">
                <h3>Contact Information</h3>
                <div class="contact-grid">
                    ${foodbank.contact_person && foodbank.contact_person !== 'Not provided' ? `
                    <div class="contact-item">
                        <span class="contact-label">Contact Person:</span>
                        <span class="contact-value">${escapeHtml(foodbank.contact_person)}</span>
                    </div>
                    ` : ''}
                    ${phoneLink ? `
                    <div class="contact-item">
                        <span class="contact-label">Phone Number:</span>
                        <a href="${phoneLink}" class="contact-link">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="vertical-align: middle; margin-right: 6px;">
                                <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
                            </svg>
                            ${escapeHtml(foodbank.phone_no)}
                        </a>
                    </div>
                    ` : foodbank.phone_no && foodbank.phone_no !== 'Not provided' ? `
                    <div class="contact-item">
                        <span class="contact-label">Phone Number:</span>
                        <span class="contact-value">${escapeHtml(foodbank.phone_no)}</span>
                    </div>
                    ` : ''}
                    ${emailLink ? `
                    <div class="contact-item">
                        <span class="contact-label">Email:</span>
                        <a href="${emailLink}" class="contact-link">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="vertical-align: middle; margin-right: 6px;">
                                <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                            </svg>
                            ${escapeHtml(foodbank.email)}
                        </a>
                    </div>
                    ` : foodbank.email ? `
                    <div class="contact-item">
                        <span class="contact-label">Email:</span>
                        <span class="contact-value">${escapeHtml(foodbank.email)}</span>
                    </div>
                    ` : ''}
                    ${foodbank.address && foodbank.address !== 'Not provided' ? `
                    <div class="contact-item">
                        <span class="contact-label">Address:</span>
                        <span class="contact-value">${escapeHtml(foodbank.address)}</span>
                    </div>
                    ` : ''}
                </div>
            </div>
                    ${foodbank.registration_number && foodbank.registration_number !== 'Not provided' ? `
                    <div class="contact-section">
                        <h3>Registration</h3>
                        <div class="contact-grid">
                            <div class="contact-item">
                                <span class="contact-label">Registration Number:</span>
                                <span class="contact-value">${escapeHtml(foodbank.registration_number)}</span>
                            </div>
                            ${foodbank.is_verified ? `
                            <div class="contact-item">
                                <span class="contact-label">Verification:</span>
                                <span class="contact-value verified-badge">✓ Verified</span>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                    ` : ''}
        </div>
    `;

    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
        } else {
            showToast(data.message || 'Failed to retrieve foodbank details.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to retrieve foodbank details. Please try again.', 'error');
    });
};

// Request to Donate
window.requestToDonate = function(foodbankId, itemDetails = null) {
    console.log('requestToDonate called with:', { foodbankId, itemDetails });
    const foodbanks = window.foodbanks || [];
    const foodbank = foodbanks.find(f => f.id === foodbankId || String(f.id) === String(foodbankId));
    
    if (!foodbank) {
        showToast('Foodbank not found', 'error');
        return;
    }

    const modal = document.getElementById('requestToDonateModal');
    const modalTitle = document.getElementById('requestDonateModalTitle');
    const foodbankIdInput = document.getElementById('requestDonateFoodbankId');

    if (!modal || !modalTitle || !foodbankIdInput) {
        console.error('Modal elements not found:', { modal: !!modal, modalTitle: !!modalTitle, foodbankIdInput: !!foodbankIdInput });
        return;
    }

    modalTitle.textContent = `Request to Donate to ${foodbank.organization_name}`;
    foodbankIdInput.value = foodbank.id;
    console.log('Foodbank ID set to:', foodbank.id);

    // Get display elements and hidden inputs
    const displayItemName = document.getElementById('displayItemName');
    const displayQuantity = document.getElementById('displayQuantity');
    const displayCategory = document.getElementById('displayCategory');
    const displayDescription = document.getElementById('displayDescription');
    const itemNameInput = document.getElementById('donateItemName');
    const quantityInput = document.getElementById('donateQuantity');
    const categoryInput = document.getElementById('donateCategory');
    const descriptionInput = document.getElementById('donateDescription');

    // Populate display and hidden fields if item details are provided
    if (itemDetails) {
        // Populate display elements
        if (displayItemName && itemDetails.item_name) {
            displayItemName.textContent = itemDetails.item_name;
        } else if (displayItemName) {
            displayItemName.textContent = '-';
        }
        
        if (displayQuantity && itemDetails.quantity) {
            displayQuantity.textContent = `${itemDetails.quantity} pcs.`;
        } else if (displayQuantity) {
            displayQuantity.textContent = '-';
        }
        
        if (displayCategory && itemDetails.category) {
            // Format category name (e.g., "fruits-vegetables" -> "Fruits & Vegetables")
            const categoryMap = {
                'fruits-vegetables': 'Fruits & Vegetables',
                'baked-goods': 'Baked Goods',
                'cooked-meals': 'Cooked Meals',
                'packaged-goods': 'Packaged Goods',
                'beverages': 'Beverages',
                'dairy': 'Dairy',
                'meat-seafood': 'Meat & Seafood',
                'other': 'Other'
            };
            const formattedCategory = categoryMap[itemDetails.category] || 
                itemDetails.category.charAt(0).toUpperCase() + itemDetails.category.slice(1).replace('-', ' ');
            displayCategory.textContent = formattedCategory;
        } else if (displayCategory) {
            displayCategory.textContent = '-';
        }
        
        if (displayDescription && itemDetails.description) {
            displayDescription.textContent = itemDetails.description;
        } else if (displayDescription) {
            displayDescription.textContent = '-';
        }
        
        // Populate hidden inputs for form submission
        if (itemNameInput) itemNameInput.value = itemDetails.item_name || '';
        if (quantityInput) quantityInput.value = itemDetails.quantity || '';
        if (categoryInput) categoryInput.value = itemDetails.category || '';
        if (descriptionInput) descriptionInput.value = itemDetails.description || '';
    } else {
        // Clear display elements when no item details (when clicking from foodbank list)
        if (displayItemName) displayItemName.textContent = '-';
        if (displayQuantity) displayQuantity.textContent = '-';
        if (displayCategory) displayCategory.textContent = '-';
        if (displayDescription) displayDescription.textContent = '-';
        
        // Clear hidden inputs
        if (itemNameInput) itemNameInput.value = '';
        if (quantityInput) quantityInput.value = '';
        if (categoryInput) categoryInput.value = '';
        if (descriptionInput) descriptionInput.value = '';
    }

    // Set minimum date to today
    const scheduledDateInput = document.getElementById('donateScheduledDate');
    if (scheduledDateInput) {
        scheduledDateInput.min = new Date().toISOString().split('T')[0];
    }

    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
    
    // Re-initialize form event listeners after modal is shown
    setTimeout(() => {
        console.log('Re-initializing form after modal open');
        initializeRequestToDonateForm();
    }, 50);
};

// Initialize Request to Donate Form
function initializeRequestToDonateForm() {
    const form = document.getElementById('requestToDonateForm');
    
    if (!form) {
        console.error('Donation form not found!');
        return;
    }

    // Remove any existing submit listeners by cloning
    const formClone = form.cloneNode(true);
    form.parentNode.replaceChild(formClone, form);
    const freshForm = document.getElementById('requestToDonateForm');
    const freshCancelBtn = freshForm ? freshForm.querySelector('#cancelRequestDonate') : null;
    const freshSubmitBtn = freshForm ? freshForm.querySelector('button[type="submit"]') : null;

    console.log('Initializing donation form:', { form: !!freshForm, cancelBtn: !!freshCancelBtn, submitBtn: !!freshSubmitBtn });

    // Set up cancel button AFTER cloning
    if (freshCancelBtn) {
        freshCancelBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            closeModal('requestToDonateModal');
            // Reset form but preserve foodbank_id
            if (freshForm) {
                const foodbankId = document.getElementById('requestDonateFoodbankId')?.value;
                freshForm.reset();
                if (foodbankId) {
                    document.getElementById('requestDonateFoodbankId').value = foodbankId;
                }
                
                // Clear display elements
                const displayItemName = document.getElementById('displayItemName');
                const displayQuantity = document.getElementById('displayQuantity');
                const displayCategory = document.getElementById('displayCategory');
                const displayDescription = document.getElementById('displayDescription');
                
                if (displayItemName) displayItemName.textContent = '-';
                if (displayQuantity) displayQuantity.textContent = '-';
                if (displayCategory) displayCategory.textContent = '-';
                if (displayDescription) displayDescription.textContent = '-';
                
                // Clear the fulfill request ID
                window.currentFulfillRequestId = null;
            }
            return false;
        });
    }
    
    if (!freshForm) {
        console.error('Could not find form after clone');
        return;
    }
    
    // Add submit event listener to form
    freshForm.addEventListener('submit', function(e) {
        console.log('Form submit event triggered');
        e.preventDefault();
        e.stopPropagation();
        submitDonationRequest();
        return false;
    });
    
    // Add click listener to submit button as primary handler (more reliable)
    if (freshSubmitBtn) {
        // Remove any existing listeners first by cloning
        const newSubmitBtn = freshSubmitBtn.cloneNode(true);
        freshSubmitBtn.parentNode.replaceChild(newSubmitBtn, freshSubmitBtn);
        const finalSubmitBtn = freshForm.querySelector('button[type="submit"]');
        
        if (finalSubmitBtn) {
            finalSubmitBtn.addEventListener('click', function(e) {
                console.log('Submit button clicked');
                e.preventDefault();
                e.stopPropagation();
                
                // Validate form first
                if (freshForm.checkValidity()) {
                    submitDonationRequest();
                } else {
                    console.log('Form validation failed');
                    freshForm.reportValidity();
                }
                return false;
            });
            console.log('Submit button listener attached');
        } else {
            console.error('Could not find submit button after clone');
        }
    } else {
        console.error('Submit button not found');
    }
    
    console.log('Form event listeners attached successfully', { form: !!freshForm, cancelBtn: !!freshCancelBtn, submitBtn: !!freshSubmitBtn });
}

// Submit Donation Request
function submitDonationRequest() {
    console.log('submitDonationRequest called');
    const form = document.getElementById('requestToDonateForm');
    if (!form) {
        console.error('Form not found in submitDonationRequest');
        showToast('Form not found. Please refresh the page.', 'error');
        return;
    }

    console.log('Form found, getting form data');
    const formData = new FormData(form);
    
    // Include disabled fields (read-only fields from donation request)
    // Disabled fields are not included in FormData by default
    const disabledFields = form.querySelectorAll('select[disabled], input[readonly].read-only-field, textarea[readonly].read-only-field');
    disabledFields.forEach(field => {
        if (field.name && field.value) {
            formData.append(field.name, field.value);
        }
    });
    
    const data = Object.fromEntries(formData);
    
    console.log('Form data:', data);
    
    // Validate foodbank_id is set
    if (!data.foodbank_id) {
        console.error('foodbank_id is missing');
        showToast('Please select a foodbank first.', 'error');
        return;
    }
    
    // Validate required fields
    if (!data.item_name || !data.quantity || !data.category || !data.scheduled_date || !data.pickup_method) {
        showToast('Please fill in all required fields', 'error');
        return;
    }
    
    // Clean up scheduled_time - remove if empty
    if (!data.scheduled_time || data.scheduled_time.trim() === '') {
        delete data.scheduled_time;
    }
    
    // Clean up expiry_date - remove if empty
    if (!data.expiry_date || data.expiry_date.trim() === '') {
        delete data.expiry_date;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!csrfToken) {
        console.error('CSRF token not found');
        showToast('Security token missing. Please refresh the page.', 'error');
        return;
    }
    
    // Check if we're fulfilling a donation request
    const fulfillRequestId = window.currentFulfillRequestId;
    let url = '/establishment/donation-request';
    let method = 'POST';
    
    if (fulfillRequestId) {
        // Fulfill existing donation request
        url = `/establishment/donation-request/fulfill/${fulfillRequestId}`;
    }
    
    // Show loading state (disable submit button)
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalSubmitText = submitBtn ? submitBtn.textContent : '';
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';
    }

    // Log data being sent for debugging
    console.log('Submitting donation request:', data);
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(async response => {
        // Check if response is ok (status 200-299)
        if (!response.ok) {
            // Try to parse error response as JSON
            let errorMessage = `Server error: ${response.status}`;
            try {
                const errorData = await response.json();
                console.error('Error response:', errorData);
                errorMessage = errorData.message || errorData.error || errorMessage;
                
                // Handle validation errors
                if (errorData.errors) {
                    const validationErrors = Object.values(errorData.errors).flat().join(', ');
                    errorMessage = validationErrors || errorMessage;
                }
            } catch (e) {
                console.error('Error parsing response:', e);
                // If response is not JSON, use status text
                errorMessage = response.statusText || errorMessage;
            }
            throw new Error(errorMessage);
        }
        return response.json();
    })
    .then(result => {
        if (result.success) {
            showToast(result.message || 'Donation request submitted successfully!', 'success');
            closeModal('requestToDonateModal');
            // Reset form completely
            if (form) {
                form.reset();
            }
            // Clear the fulfill request ID
            window.currentFulfillRequestId = null;
            // Optionally reload the page or update the UI
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showToast(result.message || 'Failed to submit donation request. Please try again.', 'error');
            // Re-enable submit button on error
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalSubmitText;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const errorMessage = error.message || 'Failed to submit donation request. Please try again.';
        showToast(errorMessage, 'error');
        // Re-enable submit button on error
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = originalSubmitText;
        }
    });
}

// Toast notification
function showToast(message, type = 'info') {
    // Remove any existing toasts first
    const existingToasts = document.querySelectorAll('.toast');
    existingToasts.forEach(toast => toast.remove());
    
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    // Force a reflow to ensure the element is in the DOM
    toast.offsetHeight;
    
    setTimeout(() => toast.classList.add('show'), 10);
    
    // Show for 4 seconds for better visibility
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
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

// ============================================================================
// Request to Donate to Food Bank Modal (Fully Editable - for Foodbanks Section)
// ============================================================================

// Open Request to Donate Foodbank Modal
window.openRequestToDonateFoodbankModal = function(foodbankId) {
    const foodbanks = window.foodbanks || [];
    const foodbank = foodbanks.find(f => f.id === foodbankId || String(f.id) === String(foodbankId));
    
    if (!foodbank) {
        showToast('Foodbank not found', 'error');
        return;
    }

    const modal = document.getElementById('requestToDonateFoodbankModal');
    const modalTitle = document.getElementById('requestDonateFoodbankModalTitle');
    const foodbankIdInput = document.getElementById('requestDonateFoodbankFoodbankId');

    if (!modal || !modalTitle || !foodbankIdInput) {
        console.error('Modal elements not found');
        return;
    }

    modalTitle.textContent = `Request to Donate to ${foodbank.organization_name}`;
    foodbankIdInput.value = foodbank.id;

    // Set minimum date to today for date inputs
    const today = new Date().toISOString().split('T')[0];
    const expiryDateInput = document.getElementById('foodbankDonateExpiryDate');
    const scheduledDateInput = document.getElementById('foodbankDonateScheduledDate');
    
    if (expiryDateInput) {
        expiryDateInput.min = today;
    }
    if (scheduledDateInput) {
        scheduledDateInput.min = today;
    }

    // Reset form
    const form = document.getElementById('requestToDonateFoodbankForm');
    if (form) {
        form.reset();
        // Restore foodbank_id after reset
        foodbankIdInput.value = foodbank.id;
        // Set default quantity to 1
        const quantityInput = document.getElementById('foodbankDonateQuantity');
        if (quantityInput) {
            quantityInput.value = 1;
        }
    }

    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
};

// Initialize Request to Donate Foodbank Form
function initializeRequestToDonateFoodbankForm() {
    const modal = document.getElementById('requestToDonateFoodbankModal');
    if (!modal) return;

    // Close button
    const closeBtn = document.getElementById('closeRequestToDonateFoodbankModal');
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            closeModal('requestToDonateFoodbankModal');
        });
    }

    // Close on overlay click
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal('requestToDonateFoodbankModal');
        }
    });

    // Cancel button
    const cancelBtn = document.getElementById('cancelRequestDonateFoodbank');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            closeModal('requestToDonateFoodbankModal');
        });
    }

    // Quantity controls
    const quantityInput = document.getElementById('foodbankDonateQuantity');
    const incrementBtn = document.getElementById('foodbankDonateIncrementBtn');
    const decrementBtn = document.getElementById('foodbankDonateDecrementBtn');

    if (incrementBtn && quantityInput) {
        incrementBtn.addEventListener('click', () => {
            const currentValue = parseInt(quantityInput.value) || 1;
            quantityInput.value = currentValue + 1;
        });
    }

    if (decrementBtn && quantityInput) {
        decrementBtn.addEventListener('click', () => {
            const currentValue = parseInt(quantityInput.value) || 1;
            if (currentValue > 1) {
                quantityInput.value = currentValue - 1;
            }
        });
    }

    // Form submission
    const form = document.getElementById('requestToDonateFoodbankForm');
    if (form) {
        form.addEventListener('submit', handleRequestToDonateFoodbankSubmit);
    }
}

// Handle form submission for Request to Donate Foodbank
function handleRequestToDonateFoodbankSubmit(e) {
    e.preventDefault();
    e.stopPropagation();

    const form = document.getElementById('requestToDonateFoodbankForm');
    if (!form) return;

    const submitBtn = document.getElementById('submitRequestDonateFoodbank');
    const originalSubmitText = submitBtn ? submitBtn.textContent : 'Submit Request';

    // Get form data
    const formData = {
        foodbank_id: document.getElementById('requestDonateFoodbankFoodbankId')?.value,
        item_name: document.getElementById('foodbankDonateItemName')?.value,
        category: document.getElementById('foodbankDonateCategory')?.value,
        quantity: parseInt(document.getElementById('foodbankDonateQuantity')?.value) || 1,
        unit: document.getElementById('foodbankDonateUnit')?.value || 'pcs',
        description: document.getElementById('foodbankDonateDescription')?.value || null,
        expiry_date: document.getElementById('foodbankDonateExpiryDate')?.value || null,
        scheduled_date: document.getElementById('foodbankDonateScheduledDate')?.value,
        scheduled_time: document.getElementById('foodbankDonateScheduledTime')?.value || null,
        pickup_method: document.getElementById('foodbankDonatePickupMethod')?.value,
        establishment_notes: document.getElementById('foodbankDonateNotes')?.value || null,
    };

    // Validate required fields
    if (!formData.foodbank_id) {
        showToast('Foodbank ID is required', 'error');
        return;
    }
    if (!formData.item_name || !formData.item_name.trim()) {
        showToast('Item name is required', 'error');
        return;
    }
    if (!formData.category) {
        showToast('Category is required', 'error');
        return;
    }
    if (!formData.quantity || formData.quantity < 1) {
        showToast('Quantity must be at least 1', 'error');
        return;
    }
    if (!formData.scheduled_date) {
        showToast('Scheduled date is required', 'error');
        return;
    }
    if (!formData.pickup_method) {
        showToast('Pickup method is required', 'error');
        return;
    }

    // Show loading state
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';
    }

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                      document.querySelector('input[name="_token"]')?.value;

    // Submit to API
    fetch('/establishment/donation-request', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Donation request submitted successfully!', 'success');
            closeModal('requestToDonateFoodbankModal');
            
            // Reset form
            if (form) {
                form.reset();
                const quantityInput = document.getElementById('foodbankDonateQuantity');
                if (quantityInput) {
                    quantityInput.value = 1;
                }
            }
            
            // Optionally refresh the page or update UI
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showToast(data.message || 'Failed to submit donation request', 'error');
            
            // Handle validation errors
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    const errorMessage = data.errors[field][0];
                    showToast(`${field}: ${errorMessage}`, 'error');
                });
            }
        }
    })
    .catch(error => {
        console.error('Error submitting donation request:', error);
        showToast('An error occurred. Please try again.', 'error');
    })
    .finally(() => {
        // Reset button state
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = originalSubmitText;
        }
    });
}
