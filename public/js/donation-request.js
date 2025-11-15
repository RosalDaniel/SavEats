// Donation Request Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize with data from Laravel or default empty array
    let requests = window.donationRequests || [
        { id: 1, foodType: 'Joy Bread', quantity: 12, matches: 2, status: 'pending' },
        { id: 2, foodType: 'Joy Bread', quantity: 12, matches: 1, status: 'active' },
        { id: 3, foodType: 'Joy Bread', quantity: 12, matches: 6, status: 'completed' },
        { id: 4, foodType: 'Joy Bread', quantity: 12, matches: 10, status: 'expired' },
        { id: 5, foodType: 'Vegetables', quantity: 25, matches: 3, status: 'active' },
        { id: 6, foodType: 'Canned Goods', quantity: 50, matches: 8, status: 'pending' },
        { id: 7, foodType: 'Fresh Fruits', quantity: 30, matches: 5, status: 'active' },
    ];

    let filteredRequests = [...requests];
    let currentPage = 1;
    const itemsPerPage = 4;

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

    // Make showToast globally accessible
    window.showToast = showToast;

    // Render table
    function renderTable() {
        const tableBody = document.getElementById('tableBody');
        const mobileCards = document.getElementById('mobileCards');
        
        if (!tableBody || !mobileCards) return;
        
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const pageRequests = filteredRequests.slice(startIndex, endIndex);

        // Desktop table
        tableBody.innerHTML = pageRequests.map(request => `
            <tr>
                <td>${request.foodType}</td>
                <td>${request.quantity}</td>
                <td>${request.matches}</td>
                <td><span class="status-badge ${request.status}">${request.status}</span></td>
                <td>
                    <div style="position: relative;">
                        <button class="actions-btn" data-id="${request.id}" aria-label="Actions menu">
                            <svg viewBox="0 0 24 24">
                                <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                            </svg>
                        </button>
                        <div class="actions-menu" id="menu-${request.id}">
                            <button onclick="viewRequest(${request.id})">View Details</button>
                            <button onclick="editRequest(${request.id})">Edit</button>
                            <button class="delete" onclick="deleteRequest(${request.id})">Delete</button>
                        </div>
                    </div>
                </td>
            </tr>
        `).join('');

        // Mobile cards
        mobileCards.innerHTML = pageRequests.map(request => `
            <div class="request-card">
                <div class="request-card-header">
                    <div class="request-card-title">${request.foodType}</div>
                    <span class="status-badge ${request.status}">${request.status}</span>
                </div>
                <div class="request-card-detail">
                    <strong>Quantity:</strong>
                    <span>${request.quantity}</span>
                </div>
                <div class="request-card-detail">
                    <strong>Matches:</strong>
                    <span>${request.matches}</span>
                </div>
                <div style="margin-top: 15px; display: flex; gap: 10px;">
                    <button class="btn btn-primary" onclick="editRequest(${request.id})" style="flex: 1;">Edit</button>
                    <button class="btn btn-secondary" onclick="deleteRequest(${request.id})" style="flex: 1;">Delete</button>
                </div>
            </div>
        `).join('');

        renderPagination();
        updateActiveRequestsCount();
    }

    // Render pagination
    function renderPagination() {
        const pagination = document.getElementById('pagination');
        if (!pagination) return;
        
        const totalPages = Math.ceil(filteredRequests.length / itemsPerPage);

        if (totalPages <= 1) {
            pagination.innerHTML = '';
            return;
        }

        let paginationHTML = `
            <button onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>
                Previous
            </button>
        `;

        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                paginationHTML += `
                    <button 
                        onclick="changePage(${i})" 
                        class="${i === currentPage ? 'active' : ''}"
                    >
                        ${i}
                    </button>
                `;
            } else if (i === currentPage - 2 || i === currentPage + 2) {
                paginationHTML += '<span>...</span>';
            }
        }

        paginationHTML += `
            <button onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>
                Next
            </button>
        `;

        pagination.innerHTML = paginationHTML;
    }

    // Change page
    window.changePage = function(page) {
        const totalPages = Math.ceil(filteredRequests.length / itemsPerPage);
        if (page < 1 || page > totalPages) return;
        currentPage = page;
        renderTable();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    // Search functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            filteredRequests = requests.filter(request => 
                request.foodType.toLowerCase().includes(searchTerm) ||
                request.status.toLowerCase().includes(searchTerm)
            );
            currentPage = 1;
            renderTable();
        });
    }

    // Actions menu toggle
    document.addEventListener('click', (e) => {
        if (e.target.closest('.actions-btn')) {
            const btn = e.target.closest('.actions-btn');
            const id = btn.getAttribute('data-id');
            const menu = document.getElementById(`menu-${id}`);
            
            // Close all other menus
            document.querySelectorAll('.actions-menu').forEach(m => {
                if (m !== menu) m.classList.remove('show');
            });
            
            if (menu) {
                menu.classList.toggle('show');
            }
        } else if (!e.target.closest('.actions-menu')) {
            document.querySelectorAll('.actions-menu').forEach(m => m.classList.remove('show'));
        }
    });

    // View request
    window.viewRequest = function(id) {
        const request = requests.find(r => r.id === id);
        if (request) {
            showToast(`Viewing details for: ${request.foodType}`, 'info');
        }
    };

    // Edit request
    window.editRequest = function(id) {
        const request = requests.find(r => r.id === id);
        if (request) {
            const editRequestId = document.getElementById('editRequestId');
            const editFoodType = document.getElementById('editFoodType');
            const editQuantity = document.getElementById('editQuantity');
            const editStatus = document.getElementById('editStatus');
            const editModal = document.getElementById('editModal');
            
            if (editRequestId) editRequestId.value = request.id;
            if (editFoodType) editFoodType.value = request.foodType;
            if (editQuantity) editQuantity.value = request.quantity;
            if (editStatus) editStatus.value = request.status;
            if (editModal) editModal.classList.add('show');
        }
    };

    // Delete request
    window.deleteRequest = function(id) {
        if (confirm('Are you sure you want to delete this request?')) {
            requests = requests.filter(r => r.id !== id);
            filteredRequests = filteredRequests.filter(r => r.id !== id);
            renderTable();
            showToast('Request deleted successfully', 'success');
        }
    };

    // Update active requests count
    function updateActiveRequestsCount() {
        const activeCount = requests.filter(r => r.status === 'active' || r.status === 'pending').length;
        const countElement = document.getElementById('activeRequestsCount');
        if (countElement) {
            countElement.textContent = activeCount;
        }
    }

    // Quantity controls
    let quantity = 1;
    const incrementBtn = document.getElementById('incrementBtn');
    const decrementBtn = document.getElementById('decrementBtn');
    const quantityInput = document.getElementById('quantity');

    if (incrementBtn && quantityInput) {
        incrementBtn.addEventListener('click', () => {
            quantity++;
            quantityInput.value = quantity;
        });
    }

    if (decrementBtn && quantityInput) {
        decrementBtn.addEventListener('click', () => {
            if (quantity > 1) {
                quantity--;
                quantityInput.value = quantity;
            }
        });
    }

    // Time option toggle
    function toggleTimeInputs() {
        const allDayChecked = document.getElementById('allDay');
        const anytimeChecked = document.getElementById('anytime');
        const timeInputs = document.getElementById('timeInputs');
        if (allDayChecked && anytimeChecked && timeInputs) {
            // Show time inputs when anytime is selected (for specific time entry)
            timeInputs.style.display = anytimeChecked.checked ? 'flex' : 'none';
        }
    }

    const timeOptionRadios = document.querySelectorAll('input[name="timeOption"]');
    timeOptionRadios.forEach(radio => {
        radio.addEventListener('change', toggleTimeInputs);
    });

    // Date input - set minimum date to today
    const dropoffDateInput = document.getElementById('dropoffDate');
    if (dropoffDateInput) {
        const today = new Date().toISOString().split('T')[0];
        dropoffDateInput.setAttribute('min', today);
    }

    // Publish modal
    const publishBtn = document.getElementById('publishBtn');
    const publishModal = document.getElementById('publishModal');
    const closePublishModal = document.getElementById('closePublishModal');
    const cancelPublish = document.getElementById('cancelPublish');
    const submitPublish = document.getElementById('submitPublish');

    if (publishBtn && publishModal) {
        publishBtn.addEventListener('click', () => {
            publishModal.classList.add('show');
            // Reset form when opening
            quantity = 1;
            if (quantityInput) quantityInput.value = 1;
            toggleTimeInputs();
        });
    }

    if (closePublishModal) {
        closePublishModal.addEventListener('click', () => {
            if (publishModal) publishModal.classList.remove('show');
        });
    }

    if (cancelPublish) {
        cancelPublish.addEventListener('click', () => {
            if (publishModal) publishModal.classList.remove('show');
        });
    }

    if (submitPublish) {
        submitPublish.addEventListener('click', () => {
            const publishForm = document.getElementById('publishForm');
            if (!publishForm) return;

            // Validate required fields
            const itemName = document.getElementById('itemName');
            const category = document.getElementById('category');
            const distributionZone = document.getElementById('distributionZone');
            const dropoffDate = document.getElementById('dropoffDate');
            const address = document.getElementById('address');
            const contactName = document.getElementById('contactName');
            const phoneNumber = document.getElementById('phoneNumber');
            const email = document.getElementById('email');

            if (!itemName || !itemName.value.trim()) {
                showToast('Please enter item name', 'warning');
                itemName?.focus();
                return;
            }

            if (!category || !category.value) {
                showToast('Please select a category', 'warning');
                category?.focus();
                return;
            }

            if (!distributionZone || !distributionZone.value) {
                showToast('Please select a distribution zone', 'warning');
                distributionZone?.focus();
                return;
            }

            if (!dropoffDate || !dropoffDate.value) {
                showToast('Please select a drop-off date', 'warning');
                dropoffDate?.focus();
                return;
            }

            if (!address || !address.value.trim()) {
                showToast('Please enter an address', 'warning');
                address?.focus();
                return;
            }

            if (!contactName || !contactName.value.trim()) {
                showToast('Please enter contact name', 'warning');
                contactName?.focus();
                return;
            }

            if (!phoneNumber || !phoneNumber.value.trim()) {
                showToast('Please enter phone number', 'warning');
                phoneNumber?.focus();
                return;
            }

            if (!email || !email.value.trim()) {
                showToast('Please enter email address', 'warning');
                email?.focus();
                return;
            }

            // Collect form data
            const timeOption = document.querySelector('input[name="timeOption"]:checked');
            const deliveryOption = document.querySelector('input[name="deliveryOption"]:checked');
            const startTime = document.getElementById('startTime');
            const endTime = document.getElementById('endTime');
            const description = document.getElementById('description');

            // Determine time option value
            let timeOptionValue = timeOption?.value || 'allDay';
            let startTimeValue = '';
            let endTimeValue = '';
            
            // If anytime is selected and time inputs have values, treat as specific time
            if (timeOptionValue === 'anytime') {
                const timeInputs = document.getElementById('timeInputs');
                if (timeInputs && timeInputs.style.display !== 'none' && startTime?.value && endTime?.value) {
                    timeOptionValue = 'specific';
                    startTimeValue = startTime.value;
                    endTimeValue = endTime.value;
                }
            }

            // Collect form data
            const formData = {
                itemName: itemName.value.trim(),
                quantity: parseInt(quantityInput?.value || 1),
                category: category.value,
                distributionZone: distributionZone.value,
                description: description?.value.trim() || '',
                dropoffDate: dropoffDate.value,
                timeOption: timeOptionValue,
                startTime: startTimeValue,
                endTime: endTimeValue,
                address: address.value.trim(),
                deliveryOption: deliveryOption?.value || 'pickup',
                contactName: contactName.value.trim(),
                phoneNumber: '+63' + phoneNumber.value.trim(),
                email: email.value.trim()
            };

            // Store form data for later submission
            window.pendingFormData = formData;

            // Show preview modal
            showPreviewModal(formData);
        });
    }

    // Preview modal functions
    function showPreviewModal(formData) {
        const previewModal = document.getElementById('previewModal');
        if (!previewModal) return;

        // Format category
        const categoryLabels = {
            'fresh-produce': 'Fresh Produce',
            'canned-goods': 'Canned Goods',
            'dairy': 'Dairy Products',
            'grains': 'Grains & Cereals',
            'protein': 'Protein (Meat/Fish)',
            'prepared': 'Prepared Meals',
            'other': 'Other'
        };

        // Format distribution zone
        const zoneLabels = {
            'zone-a': 'Zone A - North District',
            'zone-b': 'Zone B - South District',
            'zone-c': 'Zone C - East District',
            'zone-d': 'Zone D - West District',
            'zone-e': 'Zone E - Central District'
        };

        // Format date
        const dateObj = new Date(formData.dropoffDate);
        const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        const dayAvailable = dayNames[dateObj.getDay()];

        // Format time
        function formatTime(timeString) {
            if (!timeString) return 'All Day';
            const [hours, minutes] = timeString.split(':');
            const hour = parseInt(hours);
            const ampm = hour >= 12 ? 'pm' : 'am';
            const hour12 = hour % 12 || 12;
            return `${hour12}:${minutes} ${ampm}`;
        }

        // Format phone number
        function formatPhoneNumber(phone) {
            if (!phone) return '-';
            const cleaned = phone.replace(/\D/g, '');
            if (cleaned.length === 13) {
                return `+${cleaned.slice(0, 2)} | ${cleaned.slice(2, 5)} - ${cleaned.slice(5, 8)} - ${cleaned.slice(8)}`;
            }
            return phone;
        }

        // Populate preview fields
        document.getElementById('previewItemName').textContent = formData.itemName || '-';
        document.getElementById('previewQuantity').textContent = formData.quantity || '-';
        document.getElementById('previewCategory').textContent = categoryLabels[formData.category] || formData.category || '-';
        document.getElementById('previewDescription').textContent = formData.description || '-';
        document.getElementById('previewDistributionZone').textContent = zoneLabels[formData.distributionZone] || formData.distributionZone || '-';
        document.getElementById('previewDayAvailable').textContent = dayAvailable || '-';
        
        if (formData.timeOption === 'allDay') {
            document.getElementById('previewStartTime').textContent = 'All Day';
            document.getElementById('previewEndTime').textContent = 'All Day';
        } else if (formData.timeOption === 'anytime') {
            document.getElementById('previewStartTime').textContent = 'Anytime';
            document.getElementById('previewEndTime').textContent = 'Anytime';
        } else {
            document.getElementById('previewStartTime').textContent = formatTime(formData.startTime);
            document.getElementById('previewEndTime').textContent = formatTime(formData.endTime);
        }
        
        document.getElementById('previewAddress').textContent = formData.address || '-';
        document.getElementById('previewDeliveryMethod').textContent = formData.deliveryOption === 'pickup' ? 'Pick-up Only' : 'Delivery';
        document.getElementById('previewEmail').textContent = formData.email || '-';
        document.getElementById('previewContactName').textContent = formData.contactName || '-';
        document.getElementById('previewPhoneNumber').textContent = formatPhoneNumber(formData.phoneNumber);

        // Show modal
        previewModal.classList.add('show');
    }

    // Preview modal controls
    const previewModal = document.getElementById('previewModal');
    const cancelPreview = document.getElementById('cancelPreview');
    const confirmPreview = document.getElementById('confirmPreview');

    // Close preview modal when clicking outside
    if (previewModal) {
        previewModal.addEventListener('click', (e) => {
            if (e.target === previewModal) {
                previewModal.classList.remove('show');
            }
        });
    }

    if (cancelPreview) {
        cancelPreview.addEventListener('click', () => {
            if (previewModal) previewModal.classList.remove('show');
            window.pendingFormData = null;
        });
    }

    if (confirmPreview) {
        confirmPreview.addEventListener('click', () => {
            if (!window.pendingFormData) return;

            const formData = window.pendingFormData;

            // Send formData to backend API
            const publishForm = document.getElementById('publishForm');
            const searchInput = document.getElementById('searchInput');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            // Show loading state
            if (confirmPreview) {
                confirmPreview.disabled = true;
                confirmPreview.textContent = 'Publishing...';
            }

            fetch('/foodbank/donation-request', {
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
                    // Create new request object from server response
                    const newRequest = {
                        id: data.data.id,
                        foodType: data.data.foodType,
                        quantity: data.data.quantity,
                        matches: data.data.matches,
                        status: data.data.status
                    };

                    // Add new request to the beginning of the array
                    requests.unshift(newRequest);
                    
                    // Clear search filter to show all requests including the new one
                    if (searchInput) {
                        searchInput.value = '';
                    }
                    
                    // Update filtered requests to show all (no filter)
                    filteredRequests = [...requests];
                    currentPage = 1;
                    
                    // Render the table with the new request
                    renderTable();
                    
                    // Reset form
                    if (publishForm) publishForm.reset();
                    quantity = 1;
                    if (quantityInput) quantityInput.value = 1;
                    toggleTimeInputs();
                    
                    // Close modals
                    if (publishModal) publishModal.classList.remove('show');
                    if (previewModal) previewModal.classList.remove('show');
                    
                    // Clear pending form data
                    window.pendingFormData = null;
                    
                    // Show success message
                    showToast(data.message || 'Request published successfully!', 'success');
                    
                    // Scroll to the requests section to show the new request
                    const requestsSection = document.querySelector('.requests-section');
                    if (requestsSection) {
                        requestsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                } else {
                    showToast(data.message || 'Failed to publish request. Please try again.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Failed to publish request. Please try again.', 'error');
            })
            .finally(() => {
                // Reset button state
                if (confirmPreview) {
                    confirmPreview.disabled = false;
                    confirmPreview.textContent = 'Confirm';
                }
            });
        });
    }

    // Edit modal
    const editModal = document.getElementById('editModal');
    const closeEditModal = document.getElementById('closeEditModal');
    const cancelEdit = document.getElementById('cancelEdit');
    const submitEdit = document.getElementById('submitEdit');

    if (closeEditModal) {
        closeEditModal.addEventListener('click', () => {
            if (editModal) editModal.classList.remove('show');
        });
    }

    if (cancelEdit) {
        cancelEdit.addEventListener('click', () => {
            if (editModal) editModal.classList.remove('show');
        });
    }

    if (submitEdit) {
        submitEdit.addEventListener('click', () => {
            const editRequestId = document.getElementById('editRequestId');
            const editFoodType = document.getElementById('editFoodType');
            const editQuantity = document.getElementById('editQuantity');
            const editStatus = document.getElementById('editStatus');
            
            if (!editRequestId || !editFoodType || !editQuantity || !editStatus) return;
            
            const id = parseInt(editRequestId.value);
            const foodType = editFoodType.value;
            const quantity = editQuantity.value;
            const status = editStatus.value;

            const request = requests.find(r => r.id === id);
            if (request) {
                request.foodType = foodType;
                request.quantity = parseInt(quantity);
                request.status = status;
                
                filteredRequests = [...requests];
                renderTable();
                if (editModal) editModal.classList.remove('show');
                showToast('Request updated successfully!', 'success');
            }
        });
    }

    // Close modals on overlay click
    if (publishModal) {
        publishModal.addEventListener('click', (e) => {
            if (e.target === publishModal) {
                publishModal.classList.remove('show');
            }
        });
    }

    if (editModal) {
        editModal.addEventListener('click', (e) => {
            if (e.target === editModal) {
                editModal.classList.remove('show');
            }
        });
    }

    // Notification button
    const notificationBtn = document.getElementById('notificationBtn');
    if (notificationBtn) {
        notificationBtn.addEventListener('click', () => {
            showToast('No new notifications', 'info');
        });
    }

    // View toggle (list/grid)
    const viewButtons = document.querySelectorAll('.view-btn');
    viewButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            viewButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            showToast(`Switched to ${btn.getAttribute('data-view')} view`, 'info');
        });
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (publishModal) publishModal.classList.remove('show');
            if (editModal) editModal.classList.remove('show');
        }
    });

    // Initialize
    renderTable();

    console.log('Donation Request page initialized successfully!');
});

// Establishment Donation Functions
window.viewEstablishmentDonationDetails = function(id) {
    const donations = window.establishmentDonations || [];
    const donation = donations.find(d => d.id === id);
    
    if (!donation) {
        showToast('Donation not found', 'error');
        return;
    }

    const modal = document.getElementById('establishmentDonationModal');
    const modalBody = document.getElementById('establishmentDonationModalBody');
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

    modalBody.innerHTML = `
        <div class="donation-detail-content">
            ${donation.is_urgent || donation.is_nearing_expiry ? `
            <div class="alert-section">
                ${donation.is_urgent ? '<div class="alert alert-urgent">⚠️ Urgent: This donation requires immediate attention</div>' : ''}
                ${donation.is_nearing_expiry ? '<div class="alert alert-expiry">⏰ Expiring Soon: This item is nearing its expiry date</div>' : ''}
            </div>
            ` : ''}

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

            ${donation.description ? `
            <div class="detail-section">
                <h3>Description</h3>
                <p class="detail-description">${escapeHtml(donation.description)}</p>
            </div>
            ` : ''}

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

            ${donation.establishment_notes ? `
            <div class="detail-section">
                <h3>Notes from Establishment</h3>
                <div class="note-item">
                    <p class="note-content">${escapeHtml(donation.establishment_notes)}</p>
                </div>
            </div>
            ` : ''}
        </div>
    `;

    // Set up modal button actions
    const acceptBtn = document.getElementById('modalAcceptBtn');
    const declineBtn = document.getElementById('modalDeclineBtn');
    
    if (acceptBtn) {
        acceptBtn.onclick = () => {
            acceptDonation(id);
        };
    }
    
    if (declineBtn) {
        declineBtn.onclick = () => {
            declineDonation(id);
        };
    }

    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
};

window.acceptDonation = function(id) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    fetch(`/foodbank/donation-request/accept/${id}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Donation accepted successfully!', 'success');
            
            // Remove donation from list
            const donations = window.establishmentDonations || [];
            window.establishmentDonations = donations.filter(d => d.id !== id);
            
            // Remove card from DOM
            const card = document.querySelector(`.establishment-donation-card[data-id="${id}"]`);
            if (card) {
                card.remove();
            }
            
            // Update count
            const countEl = document.getElementById('establishmentDonationsCount');
            if (countEl) {
                const newCount = window.establishmentDonations.length;
                countEl.textContent = `${newCount} Offer${newCount !== 1 ? 's' : ''}`;
            }
            
            // Close modal
            const modal = document.getElementById('establishmentDonationModal');
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
            }
        } else {
            showToast(data.message || 'Failed to accept donation. Please try again.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to accept donation. Please try again.', 'error');
    });
};

window.declineDonation = function(id) {
    if (!confirm('Are you sure you want to decline this donation?')) {
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    fetch(`/foodbank/donation-request/decline/${id}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Donation declined successfully.', 'success');
            
            // Remove donation from list
            const donations = window.establishmentDonations || [];
            window.establishmentDonations = donations.filter(d => d.id !== id);
            
            // Remove card from DOM
            const card = document.querySelector(`.establishment-donation-card[data-id="${id}"]`);
            if (card) {
                card.remove();
            }
            
            // Update count
            const countEl = document.getElementById('establishmentDonationsCount');
            if (countEl) {
                const newCount = window.establishmentDonations.length;
                countEl.textContent = `${newCount} Offer${newCount !== 1 ? 's' : ''}`;
            }
            
            // Close modal
            const modal = document.getElementById('establishmentDonationModal');
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
            }
        } else {
            showToast(data.message || 'Failed to decline donation. Please try again.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to decline donation. Please try again.', 'error');
    });
};

// Close establishment donation modal
document.addEventListener('DOMContentLoaded', function() {
    const closeModalBtn = document.getElementById('closeEstablishmentDonationModal');
    const closeModalBtn2 = document.getElementById('closeEstablishmentDonationModalBtn');
    const modal = document.getElementById('establishmentDonationModal');

    const closeModal = () => {
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
    };

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

    // ESC key to close modal
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (modal && modal.classList.contains('show')) {
                closeModal();
            }
        }
    });
});

