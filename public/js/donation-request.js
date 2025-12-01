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
        tableBody.innerHTML = pageRequests.map(request => {
            // Format status display
            let statusDisplay = request.status;
            let statusClass = request.status;
            if (request.status === 'accepted') {
                statusDisplay = 'Accepted';
                statusClass = 'accepted';
            } else if (request.status === 'pending_confirmation') {
                statusDisplay = 'Pending Confirmation';
                statusClass = 'pending';
            } else if (request.status === 'active' && request.donation_id && !request.fulfilled_at) {
                statusDisplay = 'Pending Confirmation';
                statusClass = 'pending';
            } else if (request.status === 'completed') {
                statusDisplay = 'Completed';
                statusClass = 'completed';
            } else if (request.status === 'pending') {
                statusDisplay = 'Pending';
                statusClass = 'pending';
            }
            
            // Build actions menu
            let actionsMenu = `
                <button onclick="viewRequest('${request.id}')">View Details</button>
            `;
            
            // Add confirm buttons if pending or active (not completed)
            if ((request.status === 'pending' || request.status === 'active' || request.status === 'accepted' || request.status === 'pending_confirmation') && request.delivery_option && request.status !== 'completed') {
                if (request.delivery_option === 'pickup') {
                    actionsMenu += `<button onclick="confirmFoodbankRequestPickup('${request.id}')" style="color: #22c55e;">Confirm Pickup</button>`;
                } else {
                    actionsMenu += `<button onclick="confirmFoodbankRequestDelivery('${request.id}')" style="color: #22c55e;">Confirm Delivery</button>`;
                }
            }
            
            // Only show edit/delete if not completed
            if (request.status !== 'completed') {
                actionsMenu += `
                    <button onclick="editRequest('${request.id}')">Edit</button>
                    <button class="delete" onclick="deleteRequest('${request.id}')">Delete</button>
                `;
            }
            
            return `
            <tr>
                <td>${request.foodType}</td>
                <td>${request.quantity}</td>
                <td>${request.matches}</td>
                <td><span class="status-badge ${statusClass}">${statusDisplay}</span></td>
                <td>
                    <div style="position: relative;">
                        <button class="actions-btn" data-id="${request.id}" aria-label="Actions menu">
                            <svg viewBox="0 0 24 24">
                                <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                            </svg>
                        </button>
                        <div class="actions-menu" id="menu-${request.id}">
                            ${actionsMenu}
                        </div>
                    </div>
                </td>
            </tr>
        `;
        }).join('');

        // Mobile cards
        mobileCards.innerHTML = pageRequests.map(request => {
            // Format status display
            let statusDisplay = request.status;
            let statusClass = request.status;
            if (request.status === 'accepted') {
                statusDisplay = 'Accepted';
                statusClass = 'accepted';
            } else if (request.status === 'pending_confirmation') {
                statusDisplay = 'Pending Confirmation';
                statusClass = 'pending';
            } else if (request.status === 'active' && request.donation_id && !request.fulfilled_at) {
                statusDisplay = 'Pending Confirmation';
                statusClass = 'pending';
            } else if (request.status === 'completed') {
                statusDisplay = 'Completed';
                statusClass = 'completed';
            } else if (request.status === 'pending') {
                statusDisplay = 'Pending';
                statusClass = 'pending';
            }
            
            // Build action buttons
            let actionButtons = `<button class="btn btn-primary" onclick="viewRequest('${request.id}')" style="flex: 1; margin-bottom: 10px;">View Details</button>`;
            
            if ((request.status === 'pending' || request.status === 'active' || request.status === 'accepted' || request.status === 'pending_confirmation') && request.delivery_option && request.status !== 'completed') {
                if (request.delivery_option === 'pickup') {
                    actionButtons += `<button class="btn btn-success" onclick="confirmFoodbankRequestPickup('${request.id}')" style="flex: 1; margin-bottom: 10px;">Confirm Pickup</button>`;
                } else {
                    actionButtons += `<button class="btn btn-success" onclick="confirmFoodbankRequestDelivery('${request.id}')" style="flex: 1; margin-bottom: 10px;">Confirm Delivery</button>`;
                }
            }
            
            if (request.status !== 'completed') {
                actionButtons += `
                    <button class="btn btn-primary" onclick="editRequest('${request.id}')" style="flex: 1; margin-bottom: 10px;">Edit</button>
                    <button class="btn btn-secondary" onclick="deleteRequest('${request.id}')" style="flex: 1;">Delete</button>
                `;
            }
            
            return `
            <div class="request-card">
                <div class="request-card-header">
                    <div class="request-card-title">${request.foodType}</div>
                    <span class="status-badge ${statusClass}">${statusDisplay}</span>
                </div>
                <div class="request-card-detail">
                    <strong>Quantity:</strong>
                    <span>${request.quantity}</span>
                </div>
                <div class="request-card-detail">
                    <strong>Matches:</strong>
                    <span>${request.matches}</span>
                </div>
                ${actionButtons ? `<div style="margin-top: 15px; display: flex; gap: 10px; flex-wrap: wrap;">${actionButtons}</div>` : ''}
            </div>
        `;
        }).join('');

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

    // Helper function to escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // View request - Fetch full details from API
    window.viewRequest = function(id) {
        const modal = document.getElementById('viewDetailsModal');
        const modalBody = document.getElementById('viewDetailsContent');
        const loading = document.getElementById('viewDetailsLoading');
        
        if (!modal || !modalBody) {
            showToast('Modal not found', 'error');
            return;
        }
        
        // Show loading state
        if (loading) loading.style.display = 'block';
        if (modalBody) modalBody.style.display = 'none';
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        fetch(`/foodbank/donation-request/${id}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(async response => {
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.message || `Server error: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.data) {
                const request = data.data;
                
                // Format status
                const statusMap = {
                    'pending': 'Pending',
                    'active': 'Active',
                    'completed': 'Completed',
                    'expired': 'Expired',
                    'accepted': 'Accepted',
                    'pending_confirmation': 'Pending Confirmation'
                };
                
                const html = `
                    <div class="donation-detail-content">
                        <div class="detail-section">
                            <h3>Basic Information</h3>
                            <div class="detail-grid">
                                <div class="detail-item">
                                    <span class="detail-label">Request ID:</span>
                                    <span class="detail-value">${escapeHtml(request.id)}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Item Name:</span>
                                    <span class="detail-value">${escapeHtml(request.item_name || 'N/A')}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Category:</span>
                                    <span class="detail-value">${escapeHtml(request.category || 'N/A')}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Quantity:</span>
                                    <span class="detail-value">${request.quantity || 0}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Status:</span>
                                    <span class="detail-value"><span class="status-badge status-${request.status}">${statusMap[request.status] || request.status}</span></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Listing Matches:</span>
                                    <span class="detail-value">${request.matches || 0}</span>
                                </div>
                                ${request.description ? `
                                <div class="detail-item full-width">
                                    <span class="detail-label">Description:</span>
                                    <span class="detail-value">${escapeHtml(request.description)}</span>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                        
                        <div class="detail-section">
                            <h3>Distribution & Availability</h3>
                            <div class="detail-grid">
                                <div class="detail-item">
                                    <span class="detail-label">Distribution Zone:</span>
                                    <span class="detail-value">${escapeHtml(request.distribution_zone_display || request.distribution_zone || 'N/A')}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Drop-off Date:</span>
                                    <span class="detail-value">${escapeHtml(request.dropoff_date_display || request.dropoff_date || 'N/A')}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Time Window:</span>
                                    <span class="detail-value">${escapeHtml(request.time_display || 'N/A')}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="detail-section">
                            <h3>Location & Logistics</h3>
                            <div class="detail-grid">
                                <div class="detail-item full-width">
                                    <span class="detail-label">Address:</span>
                                    <span class="detail-value">${escapeHtml(request.address || 'N/A')}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Delivery Option:</span>
                                    <span class="detail-value">${request.delivery_option ? request.delivery_option.charAt(0).toUpperCase() + request.delivery_option.slice(1) : 'N/A'}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="detail-section">
                            <h3>Contact Details</h3>
                            <div class="detail-grid">
                                <div class="detail-item">
                                    <span class="detail-label">Contact Name:</span>
                                    <span class="detail-value">${escapeHtml(request.contact_name || 'N/A')}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Phone Number:</span>
                                    <span class="detail-value">${escapeHtml(request.phone_number || 'N/A')}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Email:</span>
                                    <span class="detail-value">${escapeHtml(request.email || 'N/A')}</span>
                                </div>
                            </div>
                        </div>
                        
                        ${request.establishment_name ? `
                        <div class="detail-section">
                            <h3>Fulfillment</h3>
                            <div class="detail-grid">
                                <div class="detail-item">
                                    <span class="detail-label">Fulfilled By:</span>
                                    <span class="detail-value">${escapeHtml(request.establishment_name)}</span>
                                </div>
                                ${request.fulfilled_at_display ? `
                                <div class="detail-item">
                                    <span class="detail-label">Fulfilled At:</span>
                                    <span class="detail-value">${escapeHtml(request.fulfilled_at_display)}</span>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                        ` : ''}
                        
                        <div class="detail-section">
                            <h3>Timestamps</h3>
                            <div class="detail-grid">
                                <div class="detail-item">
                                    <span class="detail-label">Created:</span>
                                    <span class="detail-value">${escapeHtml(request.created_at_display || 'N/A')}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Last Updated:</span>
                                    <span class="detail-value">${escapeHtml(request.updated_at_display || 'N/A')}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                if (modalBody) {
                    modalBody.innerHTML = html;
                    modalBody.style.display = 'block';
                }
                if (loading) loading.style.display = 'none';
            } else {
                throw new Error('Invalid response data');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast(error.message || 'Failed to load request details. Please try again.', 'error');
            if (loading) loading.style.display = 'none';
            modal.classList.remove('show');
            document.body.style.overflow = '';
        });
    };

    // Edit request - Fetch full details and populate form
    window.editRequest = function(id) {
        const editModal = document.getElementById('editModal');
        if (!editModal) {
            showToast('Edit modal not found', 'error');
            return;
        }
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        // Show loading state
        const submitBtn = document.getElementById('submitEdit');
        if (submitBtn) submitBtn.disabled = true;
        
        fetch(`/foodbank/donation-request/${id}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(async response => {
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.message || `Server error: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.data) {
                const request = data.data;
                
                // Populate form fields
                const setValue = (id, value) => {
                    const el = document.getElementById(id);
                    if (el) el.value = value || '';
                };
                
                const setRadio = (name, value) => {
                    const radios = document.querySelectorAll(`input[name="${name}"]`);
                    radios.forEach(radio => {
                        if (radio.value === value) radio.checked = true;
                    });
                };
                
                setValue('editRequestId', request.id);
                setValue('editItemName', request.item_name);
                setValue('editQuantity', request.quantity);
                setValue('editCategory', request.category);
                setValue('editDescription', request.description);
                setValue('editDistributionZone', request.distribution_zone);
                setValue('editDropoffDate', request.dropoff_date);
                setValue('editStartTime', request.start_time);
                setValue('editEndTime', request.end_time);
                setValue('editAddress', request.address);
                setValue('editContactName', request.contact_name);
                setValue('editPhoneNumber', request.phone_number);
                setValue('editEmail', request.email);
                setValue('editStatus', request.status);
                
                setRadio('editTimeOption', request.time_option);
                setRadio('editDeliveryOption', request.delivery_option);
                
                // Toggle time inputs based on time option
                const timeInputs = document.getElementById('editTimeInputs');
                if (timeInputs && request.time_option === 'specific') {
                    timeInputs.style.display = 'flex';
                } else if (timeInputs) {
                    timeInputs.style.display = 'none';
                }
                
                // Set minimum date to today
                const dropoffDateInput = document.getElementById('editDropoffDate');
                if (dropoffDateInput) {
                    const today = new Date().toISOString().split('T')[0];
                    dropoffDateInput.setAttribute('min', today);
                }
                
                editModal.classList.add('show');
                document.body.style.overflow = 'hidden';
            } else {
                throw new Error('Invalid response data');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast(error.message || 'Failed to load request details. Please try again.', 'error');
        })
        .finally(() => {
            if (submitBtn) submitBtn.disabled = false;
        });
    };

    // Delete request - Call API to delete
    window.deleteRequest = function(id) {
        if (!confirm('Are you sure you want to delete this request? This action cannot be undone.')) {
            return;
        }
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        fetch(`/foodbank/donation-request/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(async response => {
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.message || `Server error: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Remove from local arrays
                requests = requests.filter(r => r.id !== id);
                filteredRequests = filteredRequests.filter(r => r.id !== id);
                
                // Recalculate current page if needed
                const totalPages = Math.ceil(filteredRequests.length / itemsPerPage);
                if (currentPage > totalPages && totalPages > 0) {
                    currentPage = totalPages;
                }
                
                renderTable();
                showToast(data.message || 'Request deleted successfully', 'success');
            } else {
                showToast(data.message || 'Failed to delete request.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast(error.message || 'Failed to delete request. Please try again.', 'error');
        });
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
                    
                    // Redirect to donation requests list page
                    window.location.href = '/foodbank/donation-requests-list';
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
            if (editModal) {
                editModal.classList.remove('show');
                document.body.style.overflow = '';
            }
        });
    }

    if (cancelEdit) {
        cancelEdit.addEventListener('click', () => {
            if (editModal) {
                editModal.classList.remove('show');
                document.body.style.overflow = '';
            }
        });
    }
    
    if (editModal) {
        editModal.addEventListener('click', (e) => {
            if (e.target === editModal) {
                editModal.classList.remove('show');
                document.body.style.overflow = '';
            }
        });
    }

    if (submitEdit) {
        submitEdit.addEventListener('click', () => {
            const editRequestId = document.getElementById('editRequestId');
            const editItemName = document.getElementById('editItemName');
            const editQuantity = document.getElementById('editQuantity');
            const editCategory = document.getElementById('editCategory');
            const editDescription = document.getElementById('editDescription');
            const editDistributionZone = document.getElementById('editDistributionZone');
            const editDropoffDate = document.getElementById('editDropoffDate');
            const editTimeOption = document.querySelector('input[name="editTimeOption"]:checked');
            const editStartTime = document.getElementById('editStartTime');
            const editEndTime = document.getElementById('editEndTime');
            const editAddress = document.getElementById('editAddress');
            const editDeliveryOption = document.querySelector('input[name="editDeliveryOption"]:checked');
            const editContactName = document.getElementById('editContactName');
            const editPhoneNumber = document.getElementById('editPhoneNumber');
            const editEmail = document.getElementById('editEmail');
            const editStatus = document.getElementById('editStatus');
            
            if (!editRequestId || !editItemName || !editQuantity || !editCategory || !editDistributionZone || 
                !editDropoffDate || !editAddress || !editContactName || !editPhoneNumber || !editEmail) {
                showToast('Please fill in all required fields', 'error');
                return;
            }
            
            const id = editRequestId.value;
            
            // Prepare form data
            const formData = {
                itemName: editItemName.value,
                quantity: parseInt(editQuantity.value),
                category: editCategory.value,
                description: editDescription.value || null,
                distributionZone: editDistributionZone.value,
                dropoffDate: editDropoffDate.value,
                timeOption: editTimeOption ? editTimeOption.value : 'allDay',
                startTime: editTimeOption && editTimeOption.value === 'specific' ? editStartTime.value : null,
                endTime: editTimeOption && editTimeOption.value === 'specific' ? editEndTime.value : null,
                address: editAddress.value,
                deliveryOption: editDeliveryOption ? editDeliveryOption.value : 'pickup',
                contactName: editContactName.value,
                phoneNumber: editPhoneNumber.value.replace('+63', ''),
                email: editEmail.value,
                status: editStatus.value
            };
            
            // Show loading state
            const submitText = document.getElementById('editSubmitText');
            const submitLoading = document.getElementById('editSubmitLoading');
            submitEdit.disabled = true;
            if (submitText) submitText.style.display = 'none';
            if (submitLoading) submitLoading.style.display = 'inline';
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            fetch(`/foodbank/donation-request/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(async response => {
                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || `Server error: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Update local arrays
                    const requestIndex = requests.findIndex(r => r.id === id);
                    if (requestIndex !== -1) {
                        requests[requestIndex].foodType = formData.itemName;
                        requests[requestIndex].quantity = formData.quantity;
                        requests[requestIndex].status = formData.status;
                        requests[requestIndex].category = formData.category;
                    }
                    
                    filteredRequests = [...requests];
                    renderTable();
                    
                    if (editModal) {
                        editModal.classList.remove('show');
                        document.body.style.overflow = '';
                    }
                    
                    showToast(data.message || 'Request updated successfully!', 'success');
                } else {
                    showToast(data.message || 'Failed to update request.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast(error.message || 'Failed to update request. Please try again.', 'error');
            })
            .finally(() => {
                submitEdit.disabled = false;
                if (submitText) submitText.style.display = 'inline';
                if (submitLoading) submitLoading.style.display = 'none';
            });
        });
    }
    
    // Edit form quantity controls
    const editIncrementBtn = document.getElementById('editIncrementBtn');
    const editDecrementBtn = document.getElementById('editDecrementBtn');
    const editQuantityInput = document.getElementById('editQuantity');
    
    if (editIncrementBtn && editQuantityInput) {
        editIncrementBtn.addEventListener('click', () => {
            const currentValue = parseInt(editQuantityInput.value) || 1;
            editQuantityInput.value = currentValue + 1;
        });
    }
    
    if (editDecrementBtn && editQuantityInput) {
        editDecrementBtn.addEventListener('click', () => {
            const currentValue = parseInt(editQuantityInput.value) || 1;
            if (currentValue > 1) {
                editQuantityInput.value = currentValue - 1;
            }
        });
    }
    
    // Edit form time option toggle
    const editTimeOptionRadios = document.querySelectorAll('input[name="editTimeOption"]');
    editTimeOptionRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            const timeInputs = document.getElementById('editTimeInputs');
            if (timeInputs) {
                timeInputs.style.display = radio.value === 'specific' ? 'flex' : 'none';
            }
        });
    });
    
    // View Details modal close handlers
    const viewDetailsModal = document.getElementById('viewDetailsModal');
    const closeViewDetailsModal = document.getElementById('closeViewDetailsModal');
    const closeViewDetailsBtn = document.getElementById('closeViewDetailsBtn');
    
    if (closeViewDetailsModal) {
        closeViewDetailsModal.addEventListener('click', () => {
            if (viewDetailsModal) {
                viewDetailsModal.classList.remove('show');
                document.body.style.overflow = '';
            }
        });
    }
    
    if (closeViewDetailsBtn) {
        closeViewDetailsBtn.addEventListener('click', () => {
            if (viewDetailsModal) {
                viewDetailsModal.classList.remove('show');
                document.body.style.overflow = '';
            }
        });
    }
    
    if (viewDetailsModal) {
        viewDetailsModal.addEventListener('click', (e) => {
            if (e.target === viewDetailsModal) {
                viewDetailsModal.classList.remove('show');
                document.body.style.overflow = '';
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
            // Notification functionality can be added here
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
    const contactBtn = document.getElementById('modalContactEstablishmentBtn');
    
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
    
    if (contactBtn && donation.establishment_id) {
        contactBtn.onclick = () => {
            closeModal('establishmentDonationModal');
            contactEstablishment(donation.establishment_id);
        };
    }

    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
};

window.acceptDonation = function(id) {
    if (!confirm('Are you sure you want to accept this donation?')) {
        return;
    }
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    fetch(`/foodbank/donation-request/accept/${id}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(async response => {
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || `Server error: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Donation accepted successfully!', 'success');
            
            // Close modal
            const modal = document.getElementById('establishmentDonationModal');
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
            }
            
            // Reload page immediately to show updated data (donation moved to Accepted tab)
            // Add hash to URL before reloading to switch to accepted tab
            window.location.hash = '#accepted';
            window.location.reload();
        } else {
            showToast(data.message || 'Failed to accept donation. Please try again.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast(error.message || 'Failed to accept donation. Please try again.', 'error');
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
            
            // Remove card from DOM (check both old and new class names)
            const card = document.querySelector(`.order-card[data-id="${id}"], .donation-request-card[data-id="${id}"], .establishment-donation-card[data-id="${id}"]`);
            if (card) {
                card.remove();
            }
            
            // Update tab count
            updateTabCounts();
            
            // Close modal
            const modal = document.getElementById('establishmentDonationModal');
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
            }
            
            // Reload page after 1.5 seconds to show declined request in declined tab
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showToast(data.message || 'Failed to decline donation. Please try again.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to decline donation. Please try again.', 'error');
    });
};

// Contact Establishment
window.contactEstablishment = function(establishmentId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    fetch(`/foodbank/establishment/contact/${establishmentId}`, {
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
            const establishment = data.data;
            const modal = document.getElementById('contactEstablishmentModal');
            const modalBody = document.getElementById('contactEstablishmentModalBody');
            const modalTitle = document.getElementById('contactEstablishmentModalTitle');
            
            if (!modal || !modalBody || !modalTitle) return;
            
            modalTitle.textContent = `Contact ${establishment.business_name || 'Establishment'}`;
            
            const escapeHtml = (text) => {
                if (!text) return 'N/A';
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            };
            
            const formatPhoneForLink = (phone) => {
                if (!phone || phone === 'Not provided') return null;
                const cleaned = phone.replace(/\D/g, '');
                return cleaned.length > 0 ? `tel:${cleaned}` : null;
            };
            
            const formatEmailForLink = (email) => {
                if (!email || email === 'Not provided') return null;
                return `mailto:${email}`;
            };
            
            const phoneLink = formatPhoneForLink(establishment.phone_no);
            const emailLink = formatEmailForLink(establishment.email);
            
            modalBody.innerHTML = `
                <div class="contact-establishment-content">
                    <div class="contact-section">
                        <h3>Contact Information</h3>
                        <div class="contact-grid">
                            ${establishment.owner_name && establishment.owner_name !== 'Not provided' ? `
                            <div class="contact-item">
                                <span class="contact-label">Owner Name:</span>
                                <span class="contact-value">${escapeHtml(establishment.owner_name)}</span>
                            </div>
                            ` : ''}
                            ${phoneLink ? `
                            <div class="contact-item">
                                <span class="contact-label">Phone Number:</span>
                                <a href="${phoneLink}" class="contact-link">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="vertical-align: middle; margin-right: 6px;">
                                        <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
                                    </svg>
                                    ${escapeHtml(establishment.phone_no)}
                                </a>
                            </div>
                            ` : establishment.phone_no && establishment.phone_no !== 'Not provided' ? `
                            <div class="contact-item">
                                <span class="contact-label">Phone Number:</span>
                                <span class="contact-value">${escapeHtml(establishment.phone_no)}</span>
                            </div>
                            ` : ''}
                            ${emailLink ? `
                            <div class="contact-item">
                                <span class="contact-label">Email:</span>
                                <a href="${emailLink}" class="contact-link">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="vertical-align: middle; margin-right: 6px;">
                                        <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                                    </svg>
                                    ${escapeHtml(establishment.email)}
                                </a>
                            </div>
                            ` : establishment.email ? `
                            <div class="contact-item">
                                <span class="contact-label">Email:</span>
                                <span class="contact-value">${escapeHtml(establishment.email)}</span>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                    
                    <div class="contact-section">
                        <h3>Business Information</h3>
                        <div class="contact-grid">
                            <div class="contact-item">
                                <span class="contact-label">Business Name:</span>
                                <span class="contact-value">${escapeHtml(establishment.business_name)}</span>
                            </div>
                            ${establishment.business_type && establishment.business_type !== 'Not provided' ? `
                            <div class="contact-item">
                                <span class="contact-label">Business Type:</span>
                                <span class="contact-value">${escapeHtml(establishment.business_type)}</span>
                            </div>
                            ` : ''}
                            ${establishment.address && establishment.address !== 'Not provided' ? `
                            <div class="contact-item">
                                <span class="contact-label">Address:</span>
                                <span class="contact-value">${escapeHtml(establishment.address)}</span>
                            </div>
                            ` : ''}
                            ${establishment.is_verified ? `
                            <div class="contact-item">
                                <span class="contact-label">Verification:</span>
                                <span class="contact-value verified-badge">✓ Verified</span>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;
            
            // Initialize modal close handlers
            const closeBtn = document.getElementById('closeContactEstablishmentModal');
            const closeBtn2 = document.getElementById('closeContactEstablishmentModalBtn');
            
            const closeModal = () => {
                if (modal) {
                    modal.classList.remove('show');
                    document.body.style.overflow = '';
                }
            };
            
            if (closeBtn) {
                closeBtn.onclick = closeModal;
            }
            
            if (closeBtn2) {
                closeBtn2.onclick = closeModal;
            }
            
            if (modal) {
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        closeModal();
                    }
                });
            }
            
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        } else {
            showToast(data.message || 'Failed to retrieve establishment details.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to retrieve establishment details. Please try again.', 'error');
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

// Tab functionality for donation requests (matching order management style)
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.order-tabs .tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Remove active class from all buttons and hide all contents
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => {
                content.classList.remove('active');
                content.style.display = 'none';
            });
            
            // Add active class to clicked button and show corresponding content
            this.classList.add('active');
            const targetContent = document.getElementById(targetTab + '-tab');
            if (targetContent) {
                targetContent.style.display = 'block';
                targetContent.classList.add('active');
            }
        });
    });
    
    // Initialize tab counts on page load
    updateTabCounts();
    
    // Check if URL hash indicates we should switch to accepted tab
    // Use setTimeout to ensure DOM is fully ready
    setTimeout(function() {
        const hash = window.location.hash;
        if (hash === '#accepted' || hash === 'accepted') {
            const acceptedButton = document.querySelector('.tab-button[data-tab="accepted"]');
            if (acceptedButton) {
                // Trigger click to switch to accepted tab
                acceptedButton.click();
                // Remove hash from URL after switching
                window.history.replaceState(null, null, window.location.pathname);
            }
        }
    }, 100);
});

// Accept donation request from establishment
window.acceptDonationRequest = function(requestId) {
    if (!confirm('Are you sure you want to accept this donation request?')) {
        return;
    }
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    fetch(`/foodbank/donation-request/accept-request/${requestId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(async response => {
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || `Server error: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Donation request accepted successfully! Please confirm pickup or delivery.', 'success');
            
            // Reload page immediately to show updated data (request moved to Accepted tab)
            // Add hash to URL before reloading to switch to accepted tab
            window.location.hash = '#accepted';
            window.location.reload();
        } else {
            showToast(data.message || 'Failed to accept donation request.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast(error.message || 'Failed to accept donation request. Please try again.', 'error');
    });
};

// Confirm pickup for accepted donation request
window.confirmPickup = function(requestId) {
    if (!confirm('Confirm that the pickup has been completed successfully?')) {
        return;
    }
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    fetch(`/foodbank/donation-request/confirm-pickup/${requestId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(async response => {
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || `Server error: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Pickup confirmed successfully!', 'success');
            
            // Reload page immediately to show updated data (request moved to Completed tab)
            window.location.hash = '#completed';
            window.location.reload();
        } else {
            showToast(data.message || 'Failed to confirm pickup.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast(error.message || 'Failed to confirm pickup. Please try again.', 'error');
    });
};

// Confirm delivery for accepted donation request
window.confirmDelivery = function(requestId) {
    if (!confirm('Confirm that the delivery has been completed successfully?')) {
        return;
    }
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    fetch(`/foodbank/donation-request/confirm-delivery/${requestId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(async response => {
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || `Server error: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Delivery confirmed successfully!', 'success');
            
            // Reload page immediately to show updated data (request moved to Completed tab)
            window.location.hash = '#completed';
            window.location.reload();
        } else {
            showToast(data.message || 'Failed to confirm delivery.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast(error.message || 'Failed to confirm delivery. Please try again.', 'error');
    });
};

// Decline donation request from establishment
window.declineDonationRequest = function(requestId) {
    if (!confirm('Are you sure you want to decline this donation request?')) {
        return;
    }
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    fetch(`/foodbank/donation-request/decline-request/${requestId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(async response => {
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || `Server error: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Donation request declined successfully.', 'success');
            
            // Reload page immediately to show updated data (request moved to Declined tab)
            window.location.hash = '#declined';
            window.location.reload();
        } else {
            showToast(data.message || 'Failed to decline donation request.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast(error.message || 'Failed to decline donation request. Please try again.', 'error');
    });
};

// Complete donation request
window.completeDonationRequest = function(requestId) {
    if (!confirm('Mark this donation request as completed?')) {
        return;
    }
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    fetch(`/foodbank/donation-request/complete-request/${requestId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(async response => {
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || `Server error: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Donation request marked as completed successfully.', 'success');
            
            // Reload page after 1.5 seconds to show updated data
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showToast(data.message || 'Failed to complete donation request.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast(error.message || 'Failed to complete donation request. Please try again.', 'error');
    });
};

// Confirm pickup for foodbank's own donation request
window.confirmFoodbankRequestPickup = function(requestId) {
    if (!confirm('Confirm that the pickup has been completed successfully?')) {
        return;
    }
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    fetch(`/foodbank/donation-request/confirm-foodbank-pickup/${requestId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(async response => {
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || `Server error: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Pickup confirmed successfully!', 'success');
            
            // Update local request status
            const requestIndex = requests.findIndex(r => r.id === requestId);
            if (requestIndex !== -1) {
                requests[requestIndex].status = 'completed';
                requests[requestIndex].fulfilled_at = data.data?.fulfilled_at || new Date().toISOString();
                requests[requestIndex].fulfilled_at_display = data.data?.fulfilled_at || new Date().toLocaleString();
            }
            
            // Refresh the table
            filteredRequests = [...requests];
            renderTable();
        } else {
            showToast(data.message || 'Failed to confirm pickup.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast(error.message || 'Failed to confirm pickup. Please try again.', 'error');
    });
};

// Confirm delivery for foodbank's own donation request
window.confirmFoodbankRequestDelivery = function(requestId) {
    if (!confirm('Confirm that the delivery has been completed successfully?')) {
        return;
    }
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    fetch(`/foodbank/donation-request/confirm-foodbank-delivery/${requestId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(async response => {
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || `Server error: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Delivery confirmed successfully!', 'success');
            
            // Update local request status
            const requestIndex = requests.findIndex(r => r.id === requestId);
            if (requestIndex !== -1) {
                requests[requestIndex].status = 'completed';
                requests[requestIndex].fulfilled_at = data.data?.fulfilled_at || new Date().toISOString();
                requests[requestIndex].fulfilled_at_display = data.data?.fulfilled_at || new Date().toLocaleString();
            }
            
            // Refresh the table
            filteredRequests = [...requests];
            renderTable();
        } else {
            showToast(data.message || 'Failed to confirm delivery.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast(error.message || 'Failed to confirm delivery. Please try again.', 'error');
    });
};

// View donation request details
window.viewDonationRequestDetails = function(requestId) {
    // Find the request in accepted, declined, or completed arrays
    const allRequests = [
        ...(window.acceptedRequests || []),
        ...(window.declinedRequests || []),
        ...(window.completedRequests || [])
    ];
    const request = allRequests.find(r => r.id === requestId);
    
    if (request) {
        // Show details modal or navigate to details page
        showToast(`Viewing details for: ${request.item_name}`, 'info');
        // You can implement a modal here similar to viewEstablishmentDonationDetails
    }
};

// Update tab counts
function updateTabCounts() {
    const incomingCount = (window.incomingRequests || []).length;
    const acceptedCount = (window.acceptedRequests || []).length;
    const declinedCount = (window.declinedRequests || []).length;
    const completedCount = (window.completedRequests || []).length;
    
    const incomingTab = document.querySelector('.tab-button[data-tab="incoming"] .tab-count');
    const acceptedTab = document.querySelector('.tab-button[data-tab="accepted"] .tab-count');
    const declinedTab = document.querySelector('.tab-button[data-tab="declined"] .tab-count');
    const completedTab = document.querySelector('.tab-button[data-tab="completed"] .tab-count');
    
    if (incomingTab) incomingTab.textContent = `(${incomingCount})`;
    if (acceptedTab) acceptedTab.textContent = `(${acceptedCount})`;
    if (declinedTab) declinedTab.textContent = `(${declinedCount})`;
    if (completedTab) completedTab.textContent = `(${completedCount})`;
}

