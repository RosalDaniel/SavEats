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
        const timeInputs = document.getElementById('timeInputs');
        if (allDayChecked && timeInputs) {
            timeInputs.style.display = allDayChecked.checked ? 'none' : 'flex';
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

            const formData = {
                itemName: itemName.value.trim(),
                quantity: parseInt(quantityInput?.value || 1),
                category: category.value,
                distributionZone: distributionZone.value,
                description: description?.value.trim() || '',
                dropoffDate: dropoffDate.value,
                timeOption: timeOption?.value || 'allDay',
                startTime: startTime?.value || '',
                endTime: endTime?.value || '',
                address: address.value.trim(),
                deliveryOption: deliveryOption?.value || 'pickup',
                contactName: contactName.value.trim(),
                phoneNumber: '+63' + phoneNumber.value.trim(),
                email: email.value.trim()
            };

            // Create new request object for display
            const newRequest = {
                id: requests.length > 0 ? Math.max(...requests.map(r => r.id)) + 1 : 1,
                foodType: formData.itemName,
                quantity: formData.quantity,
                matches: 0,
                status: 'pending',
                // Store additional data for future use
                category: formData.category,
                distributionZone: formData.distributionZone,
                dropoffDate: formData.dropoffDate,
                address: formData.address,
                contactName: formData.contactName,
                phoneNumber: formData.phoneNumber,
                email: formData.email
            };

            // TODO: Send formData to backend API
            // fetch('/foodbank/donation-request', {
            //     method: 'POST',
            //     headers: {
            //         'Content-Type': 'application/json',
            //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            //     },
            //     body: JSON.stringify(formData)
            // })
            // .then(response => response.json())
            // .then(data => {
            //     if (data.success) {
            //         requests.unshift(newRequest);
            //         filteredRequests = [...requests];
            //         currentPage = 1;
            //         renderTable();
            //         publishForm.reset();
            //         quantity = 1;
            //         if (quantityInput) quantityInput.value = 1;
            //         publishModal.classList.remove('show');
            //         showToast('Request published successfully!', 'success');
            //     }
            // })
            // .catch(error => {
            //     console.error('Error:', error);
            //     showToast('Failed to publish request. Please try again.', 'error');
            // });

            // For now, add to local array
            requests.unshift(newRequest);
            filteredRequests = [...requests];
            currentPage = 1;
            renderTable();
            
            publishForm.reset();
            quantity = 1;
            if (quantityInput) quantityInput.value = 1;
            toggleTimeInputs();
            if (publishModal) publishModal.classList.remove('show');
            showToast('Request published successfully!', 'success');
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

