// Listing Management JavaScript

// Global variables
let selectedItems = new Set();
let currentEditingId = null;

// Mobile menu functionality - ensure it works on listing-management page
(function() {
    function initMobileMenu() {
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        
        if (!menuToggle || !sidebar || !overlay) {
            return false;
        }
        
        // Remove any existing click listeners by cloning
        const newToggle = menuToggle.cloneNode(true);
        menuToggle.parentNode.replaceChild(newToggle, menuToggle);
        
        // Add click handler
        newToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const isOpen = sidebar.classList.contains('mobile-visible');
            sidebar.classList.toggle('mobile-visible');
            overlay.classList.toggle('active');
            
            const mainContent = document.getElementById('mainContent');
            if (isOpen) {
                document.body.style.overflow = '';
                if (mainContent) mainContent.style.overflow = '';
            } else {
                document.body.style.overflow = 'hidden';
                if (mainContent) mainContent.style.overflow = 'hidden';
            }
        });
        
        // Overlay click handler
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('mobile-visible');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
            const mainContent = document.getElementById('mainContent');
            if (mainContent) mainContent.style.overflow = '';
        });
        
        return true;
    }
    
    // Try to initialize immediately if DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMobileMenu);
    } else {
        // DOM is already ready
        setTimeout(initMobileMenu, 100);
    }
})();

// Checkbox functionality
const selectAllCheckbox = document.getElementById('selectAll');
const itemCheckboxes = document.querySelectorAll('.item-checkbox');
const bulkActions = document.getElementById('bulkActions');
const selectedCount = document.getElementById('selectedCount');

function updateBulkActions() {
    const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
    selectedItems = new Set(Array.from(checkedBoxes).map(cb => cb.dataset.id));
    
    if (selectedItems.size > 0) {
        bulkActions.classList.add('show');
        selectedCount.textContent = selectedItems.size;
    } else {
        bulkActions.classList.remove('show');
    }

    // Update select all checkbox
    const totalCheckboxes = document.querySelectorAll('.item-checkbox');
    selectAllCheckbox.indeterminate = selectedItems.size > 0 && selectedItems.size < totalCheckboxes.length;
    selectAllCheckbox.checked = selectedItems.size === totalCheckboxes.length;
}

selectAllCheckbox?.addEventListener('change', (e) => {
    itemCheckboxes.forEach(checkbox => {
        checkbox.checked = e.target.checked;
    });
    updateBulkActions();
});

itemCheckboxes.forEach(checkbox => {
    checkbox.addEventListener('change', updateBulkActions);
});

// Search functionality
const searchInput = document.getElementById('searchInput');
searchInput?.addEventListener('input', (e) => {
    const searchTerm = e.target.value.toLowerCase();
    filterTable(searchTerm);
});

// Filter state
let currentStatusFilter = 'all';
let currentCategoryFilter = 'all';

function filterTable(searchTerm = '') {
    const rows = document.querySelectorAll('#itemsTableBody tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const itemName = row.querySelector('.item-name')?.textContent.toLowerCase() || '';
        const itemId = row.querySelector('.item-id')?.textContent.toLowerCase() || '';
        const category = row.getAttribute('data-category')?.toLowerCase() || '';
        const status = row.getAttribute('data-status')?.toLowerCase() || '';
        
        // Search filter
        const matchesSearch = !searchTerm || 
            itemName.includes(searchTerm) || 
            itemId.includes(searchTerm) ||
            category.includes(searchTerm);
        
        // Status filter
        const matchesStatus = currentStatusFilter === 'all' || status === currentStatusFilter;
        
        // Category filter
        const matchesCategory = currentCategoryFilter === 'all' || category === currentCategoryFilter;
        
        // Show/hide row based on all filters
        if (matchesSearch && matchesStatus && matchesCategory) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update pagination info
    const paginationInfo = document.querySelector('.pagination-info');
    if (paginationInfo) {
        const totalCount = rows.length;
        paginationInfo.textContent = 
            `Showing ${visibleCount} of ${totalCount} items${(searchTerm || currentStatusFilter !== 'all' || currentCategoryFilter !== 'all') ? ' (filtered)' : ''}`;
    }
}

// Actions menu toggle
document.addEventListener('click', (e) => {
    if (e.target.closest('.actions-btn')) {
        const btn = e.target.closest('.actions-btn');
        const id = btn.getAttribute('data-id');
        const menu = document.getElementById(`menu-${id}`);
        const row = btn.closest('tr');
        
        // Remove menu-open class from all rows
        document.querySelectorAll('.table tbody tr').forEach(r => {
            r.classList.remove('menu-open');
        });
        
        // Close all other menus
        document.querySelectorAll('.actions-menu').forEach(m => {
            if (m !== menu) m.classList.remove('show');
        });
        
        // Toggle current menu
        const isOpen = menu.classList.contains('show');
        menu.classList.toggle('show');
        
        // Add/remove menu-open class to row
        if (!isOpen) {
            row.classList.add('menu-open');
        } else {
            row.classList.remove('menu-open');
        }
    } else if (!e.target.closest('.actions-menu')) {
        // Close all menus and remove menu-open classes
        document.querySelectorAll('.actions-menu').forEach(m => m.classList.remove('show'));
        document.querySelectorAll('.table tbody tr').forEach(r => {
            r.classList.remove('menu-open');
        });
    }
});

// Quantity Controls
function increaseQuantity() {
    const quantityInput = document.getElementById('itemQuantity');
    const currentValue = parseInt(quantityInput.value) || 0;
    quantityInput.value = currentValue + 1;
    calculateDiscountedPrice();
}

function decreaseQuantity() {
    const quantityInput = document.getElementById('itemQuantity');
    const currentValue = parseInt(quantityInput.value) || 1;
    if (currentValue > 1) {
        quantityInput.value = currentValue - 1;
        calculateDiscountedPrice();
    }
}

// Discount Calculation
function calculateDiscountedPrice() {
    const originalPrice = parseFloat(document.getElementById('itemOriginalPrice').value) || 0;
    const discountPercentage = parseFloat(document.getElementById('itemDiscount').value) || 0;
    
    if (originalPrice > 0 && discountPercentage > 0) {
        const discountAmount = (originalPrice * discountPercentage) / 100;
        const discountedPrice = originalPrice - discountAmount;
        document.getElementById('itemDiscountedPrice').value = discountedPrice.toFixed(2);
    } else {
        document.getElementById('itemDiscountedPrice').value = '';
    }
}

// Add event listeners for discount calculation and image preview
document.addEventListener('DOMContentLoaded', function() {
    const originalPriceInput = document.getElementById('itemOriginalPrice');
    const discountSelect = document.getElementById('itemDiscount');
    const imageInput = document.getElementById('itemImage');
    const itemForm = document.getElementById('itemForm');
    
    if (originalPriceInput) {
        originalPriceInput.addEventListener('input', calculateDiscountedPrice);
    }
    
    if (discountSelect) {
        discountSelect.addEventListener('change', calculateDiscountedPrice);
    }
    
    // Image preview functionality
    if (imageInput) {
        imageInput.addEventListener('change', handleImagePreview);
    }
    
    // Prevent form submission on Enter key or accidental submits
    // Only allow submission via saveItem() function
    if (itemForm) {
        itemForm.addEventListener('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            // Form should only be submitted via saveItem() function
            return false;
        });
    }
    
    // Initialize filters on page load
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        filterTable(searchInput.value || '');
    } else {
        filterTable('');
    }
});

// Handle image preview when file is selected
function handleImagePreview(event) {
    const file = event.target.files[0];
    const uploadBox = document.querySelector('.image-upload-box');
    const uploadPlaceholder = document.querySelector('.upload-placeholder');
    
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            // Create or update image preview
            let previewImg = uploadBox.querySelector('.image-preview');
            if (!previewImg) {
                previewImg = document.createElement('img');
                previewImg.className = 'image-preview';
                previewImg.style.cssText = `
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                    border-radius: 10px;
                    position: absolute;
                    top: 0;
                    left: 0;
                `;
                uploadBox.appendChild(previewImg);
            }
            
            previewImg.src = e.target.result;
            
            // Add preview class and hide placeholder text
            uploadBox.classList.add('has-preview');
            if (uploadPlaceholder) {
                uploadPlaceholder.style.display = 'none';
            }
            
            // Add overlay with change button
            let overlay = uploadBox.querySelector('.image-overlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.className = 'image-overlay';
                overlay.style.cssText = `
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(0, 0, 0, 0.5);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border-radius: 10px;
                    opacity: 0;
                    transition: opacity 0.3s ease;
                `;
                
                const changeBtn = document.createElement('button');
                changeBtn.type = 'button'; // Prevent form submission
                changeBtn.textContent = 'Change Image';
                changeBtn.style.cssText = `
                    background: white;
                    border: none;
                    padding: 8px 16px;
                    border-radius: 6px;
                    cursor: pointer;
                    font-size: 14px;
                    font-weight: 500;
                    color: #333;
                `;
                
                overlay.appendChild(changeBtn);
                uploadBox.appendChild(overlay);
                
                // Show overlay on hover
                uploadBox.addEventListener('mouseenter', () => {
                    overlay.style.opacity = '1';
                });
                
                uploadBox.addEventListener('mouseleave', () => {
                    overlay.style.opacity = '0';
                });
                
                // Click to change image
                changeBtn.addEventListener('click', (e) => {
                    e.preventDefault(); // Prevent any default behavior
                    e.stopPropagation(); // Stop event bubbling
                    const imageInput = document.getElementById('itemImage');
                    if (imageInput) {
                        imageInput.click();
                    }
                });
            }
        };
        
        reader.readAsDataURL(file);
    } else {
        // Reset to placeholder if invalid file
        resetImagePreview();
    }
}

// Reset image preview to placeholder
function resetImagePreview() {
    const uploadBox = document.querySelector('.image-upload-box');
    const uploadPlaceholder = document.querySelector('.upload-placeholder');
    const previewImg = uploadBox.querySelector('.image-preview');
    const overlay = uploadBox.querySelector('.image-overlay');
    
    if (previewImg) {
        previewImg.remove();
    }
    
    if (overlay) {
        overlay.remove();
    }
    
    if (uploadPlaceholder) {
        uploadPlaceholder.style.display = 'flex';
    }
    
    // Remove preview class
    uploadBox.classList.remove('has-preview');
}

// Load existing image for editing
function loadExistingImage(imageUrl) {
    const uploadBox = document.querySelector('.image-upload-box');
    const uploadPlaceholder = document.querySelector('.upload-placeholder');
    
    if (!uploadBox) return;
    
    // Hide the placeholder
    if (uploadPlaceholder) {
        uploadPlaceholder.style.display = 'none';
    }
    
    // Create or update the preview image
    let previewImg = uploadBox.querySelector('.image-preview');
    if (!previewImg) {
        previewImg = document.createElement('img');
        previewImg.className = 'image-preview';
        previewImg.style.cssText = `
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
            position: absolute;
            top: 0;
            left: 0;
        `;
        uploadBox.appendChild(previewImg);
    }
    
    previewImg.src = imageUrl;
    previewImg.alt = 'Current food image';
    
    // Add overlay for hover effect
    let overlay = uploadBox.querySelector('.image-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'image-overlay';
        overlay.style.cssText = `
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            opacity: 0;
            transition: opacity 0.3s ease;
        `;
        
        const changeBtn = document.createElement('button');
        changeBtn.type = 'button'; // Prevent form submission
        changeBtn.textContent = 'Change Image';
        changeBtn.style.cssText = `
            background: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            color: #333;
        `;
        
        overlay.appendChild(changeBtn);
        uploadBox.appendChild(overlay);
        
        // Show overlay on hover
        uploadBox.addEventListener('mouseenter', () => {
            overlay.style.opacity = '1';
        });
        
        uploadBox.addEventListener('mouseleave', () => {
            overlay.style.opacity = '0';
        });
        
        // Click to change image
        changeBtn.addEventListener('click', (e) => {
            e.preventDefault(); // Prevent any default behavior
            e.stopPropagation(); // Stop event bubbling
            const imageInput = document.getElementById('itemImage');
            if (imageInput) {
                imageInput.click();
            }
        });
    }
    
    // Add preview class
    uploadBox.classList.add('has-preview');
}

// Modal functionality
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        
        // If opening add form (not editing), populate address with establishment address
        if (modalId === 'itemModal' && currentEditingId === null) {
            const addressField = document.getElementById('itemAddress');
            if (addressField && window.establishmentAddress) {
                addressField.value = window.establishmentAddress;
            }
        }
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
        
        if (modalId === 'itemModal') {
            const form = document.getElementById('itemForm');
            if (form) {
                form.reset();
            }
            currentEditingId = null;
            const modalTitle = document.getElementById('modalTitle');
            if (modalTitle) {
                modalTitle.textContent = 'Add New Food Item';
            }
            // Reset image preview when closing modal
            resetImagePreview();
        }
    }
}

// Item management functions
function editItem(id) {
    // Prevent any form submission
    event.preventDefault();
    event.stopPropagation();
    
    currentEditingId = id;
    const modalTitle = document.getElementById('modalTitle');
    if (modalTitle) {
        modalTitle.textContent = 'Edit Food Item';
    }
    
    // Find the row data from the table
    const row = document.querySelector(`tr[data-id="${id}"]`);
    if (!row) {
        showNotification('Item data not found', 'error');
        return;
    }
    
     // Extract data from the table row
     const nameField = document.getElementById('itemName');
     const descriptionField = document.getElementById('itemDescription');
     const categoryField = document.getElementById('itemCategory');
     const quantityField = document.getElementById('itemQuantity');
     const originalPriceField = document.getElementById('itemOriginalPrice');
     const discountField = document.getElementById('itemDiscount');
     const discountedPriceField = document.getElementById('itemDiscountedPrice');
     const expiryField = document.getElementById('itemExpiry');
     const addressField = document.getElementById('itemAddress');
     
     // Populate form fields
     if (nameField) nameField.value = row.dataset.name || '';
     if (descriptionField) descriptionField.value = row.dataset.description || '';
     if (categoryField) categoryField.value = row.dataset.category || '';
     if (quantityField) quantityField.value = row.dataset.quantity || '1';
     if (originalPriceField) originalPriceField.value = row.dataset.originalPrice || '';
     if (discountField) discountField.value = row.dataset.discountPercentage || '';
     if (discountedPriceField) discountedPriceField.value = row.dataset.discountedPrice || '';
     if (expiryField) expiryField.value = row.dataset.expiry || '';
     if (addressField) addressField.value = row.dataset.address || '';
    
    // Recalculate discounted price
    calculateDiscountedPrice();
    
    // Load existing image if available
    const existingImage = row.dataset.image;
    if (existingImage && existingImage.trim() !== '') {
        loadExistingImage(existingImage);
    } else {
        resetImagePreview();
    }
    
    // Show/hide disabled item notice
    const disabledNotice = document.getElementById('disabledItemNotice');
    const isDisabled = row.dataset.isDisabled === 'true' || row.dataset.dbStatus === 'inactive';
    if (disabledNotice) {
        if (isDisabled) {
            disabledNotice.style.display = 'flex';
        } else {
            disabledNotice.style.display = 'none';
        }
    }
    
    // Show modal
    showModal('itemModal');
    showNotification('Item details loaded successfully', 'success');
}

function viewItem(id) {
    viewDetails(id);
}

function deleteItem(id) {
    if (confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        fetch(`/establishment/food-listings/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Item deleted successfully!', 'success');
                // Reload the page to show updated data
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showNotification(data.message || 'Failed to delete item', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while deleting the item', 'error');
        });
    }
}

function saveItem() {
    const form = document.getElementById('itemForm');
    if (!form || !form.checkValidity()) {
        if (form) {
            form.reportValidity();
        }
        return;
    }

    if (!validateForm()) {
        showNotification('Please fill in all required fields correctly', 'error');
        return;
    }

    // Collect form data
    const formData = new FormData();
    formData.append('name', document.getElementById('itemName').value.trim());
    formData.append('description', document.getElementById('itemDescription').value.trim());
    formData.append('category', document.getElementById('itemCategory').value);
    formData.append('quantity', parseInt(document.getElementById('itemQuantity').value));
    formData.append('original_price', parseFloat(document.getElementById('itemOriginalPrice').value));
    formData.append('discount_percentage', parseFloat(document.getElementById('itemDiscount').value) || 0);
    formData.append('expiry_date', document.getElementById('itemExpiry').value);
    formData.append('address', document.getElementById('itemAddress').value.trim());
    
    // Add image if selected
    const imageFile = document.getElementById('itemImage').files[0];
    if (imageFile) {
        formData.append('image', imageFile);
    }

    const saveBtn = document.querySelector('.modal-footer .btn-primary');
    const originalText = saveBtn?.textContent;
    if (saveBtn) {
        saveBtn.innerHTML = '<div class="spinner"></div> Saving...';
        saveBtn.disabled = true;
    }

    // Determine API endpoint
    const isEdit = currentEditingId !== null;
    const url = isEdit 
        ? `/establishment/food-listings/${currentEditingId}` 
        : '/establishment/food-listings';
    const method = 'POST'; // Always use POST with method spoofing

    // Add CSRF token and method spoofing for PUT requests
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        formData.append('_token', csrfToken);
    } else {
        showNotification('CSRF token not found. Please refresh the page.', 'error');
        return;
    }
    
    // Add method spoofing for PUT requests
    if (isEdit) {
        formData.append('_method', 'PUT');
    }

    // Make API call
    fetch(url, {
        method: method,
        body: formData,
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        
        if (data.success) {
            const action = isEdit ? 'updated' : 'added';
            showNotification(`Item "${formData.get('name')}" ${action} successfully!`, 'success');
            closeModal('itemModal');
            
            // Reload the page to show updated data
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            // Show detailed validation errors
            if (data.errors) {
                let errorMessage = 'Validation errors:\n';
                for (const [field, errors] of Object.entries(data.errors)) {
                    errorMessage += `${field}: ${errors.join(', ')}\n`;
                }
                showNotification(errorMessage, 'error');
            } else {
                showNotification(data.message || data.error || 'Failed to save item', 'error');
            }
        }
    })
    .catch(error => {
        showNotification('An error occurred while saving the item', 'error');
    })
    .finally(() => {
        if (saveBtn) {
            saveBtn.textContent = originalText;
            saveBtn.disabled = false;
        }
    });
}

// Function to add new item to table (for demo purposes)
function addItemToTable(itemData) {
    const tableBody = document.getElementById('itemsTableBody');
    if (!tableBody) return;
    
    const newRow = document.createElement('tr');
    const newId = Date.now(); // Simple ID generation for demo
    
    newRow.innerHTML = `
        <td>
            <input type="checkbox" class="checkbox item-checkbox" data-id="${newId}">
        </td>
        <td>
            <div class="item-info">
                <div class="item-image">
                    ${itemData.image && !itemData.image.includes('placeholder') 
                        ? `<img src="${itemData.image}" alt="${itemData.name}" class="item-img">` 
                        : `<span class="item-initials">${itemData.name.substring(0, 2).toUpperCase()}</span>`
                    }
                </div>
                <div class="item-details">
                    <div class="item-name">${itemData.name}</div>
                    <div class="item-description">${itemData.description || 'No description'}</div>
                </div>
            </div>
        </td>
        <td>${itemData.quantity}</td>
        <td>₱${itemData.price.toFixed(2)}</td>
        <td>${new Date(itemData.expiry).toLocaleDateString('en-US', { 
            year: 'numeric', month: 'short', day: 'numeric' 
        })}</td>
        <td>
            <span class="status-badge active">active</span>
        </td>
        <td>
            <div style="position: relative;">
                <button class="actions-btn" data-id="${newId}" aria-label="Actions menu">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                    </svg>
                </button>
                <div class="actions-menu" id="menu-${newId}">
                    <button type="button" onclick="viewItem(${newId})">View Details</button>
                    <button type="button" onclick="editItem(${newId})">Edit</button>
                    <button type="button" class="delete" onclick="deleteItem(${newId})">Delete</button>
                </div>
            </div>
        </td>
    `;
    
    // Add event listener for new checkbox
    const newCheckbox = newRow.querySelector('.item-checkbox');
    newCheckbox?.addEventListener('change', updateBulkActions);
    
    // Insert at beginning of table
    tableBody.insertBefore(newRow, tableBody.firstChild);
}

// Bulk actions
document.getElementById('bulkEditBtn')?.addEventListener('click', () => {
    showNotification(`Editing ${selectedItems.size} selected items...`, 'info');
});

document.getElementById('bulkDeleteBtn')?.addEventListener('click', () => {
    if (confirm(`Are you sure you want to delete ${selectedItems.size} selected items? This action cannot be undone.`)) {
        showNotification(`${selectedItems.size} items deleted successfully!`, 'success');
        // Clear selections
        selectedItems.clear();
        document.querySelectorAll('.item-checkbox:checked').forEach(cb => cb.checked = false);
        updateBulkActions();
    }
});

// Add food button
document.getElementById('addFoodBtn')?.addEventListener('click', () => {
    // Reset editing state to ensure address is populated
    currentEditingId = null;
    const modalTitle = document.getElementById('modalTitle');
    if (modalTitle) {
        modalTitle.textContent = 'Add List Form';
    }
    
    // Hide disabled item notice when adding new item
    const disabledNotice = document.getElementById('disabledItemNotice');
    if (disabledNotice) {
        disabledNotice.style.display = 'none';
    }
    
    showModal('itemModal');
});

// Filter buttons
document.getElementById('filterBtn')?.addEventListener('click', () => {
    showNotification('Filter options coming soon...', 'info');
});

document.getElementById('sortBtn')?.addEventListener('click', () => {
    showNotification('Sort options coming soon...', 'info');
});

// Status filter dropdown
let statusDropdown = null;
document.getElementById('statusFilterBtn')?.addEventListener('click', (e) => {
    e.stopPropagation();
    
    // Close category dropdown if open
    if (categoryDropdown) {
        categoryDropdown.remove();
        categoryDropdown = null;
    }
    
    // Remove existing status dropdown if open
    if (statusDropdown) {
        statusDropdown.remove();
        statusDropdown = null;
        return;
    }
    
    const btn = e.target.closest('#statusFilterBtn');
    const rect = btn.getBoundingClientRect();
    
    statusDropdown = document.createElement('div');
    statusDropdown.className = 'filter-dropdown-menu';
    statusDropdown.style.position = 'fixed';
    statusDropdown.style.top = (rect.bottom + 5) + 'px';
    statusDropdown.style.left = rect.left + 'px';
    statusDropdown.style.zIndex = '1000';
    
    const statusOptions = [
        { value: 'all', label: 'All Statuses' },
        { value: 'active', label: 'Active' },
        { value: 'expiring', label: 'Expiring Soon' },
        { value: 'expired', label: 'Expired' },
        { value: 'sold', label: 'Sold' },
        { value: 'completed', label: 'Completed' },
        { value: 'pending', label: 'Pending' }
    ];
    
    statusOptions.forEach(option => {
        const item = document.createElement('button');
        item.type = 'button';
        item.className = 'filter-dropdown-item';
        if (currentStatusFilter === option.value) {
            item.classList.add('active');
        }
        item.textContent = option.label;
        item.addEventListener('click', () => {
            currentStatusFilter = option.value;
            btn.innerHTML = `
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M10 18h4v-2h-4v2zM3 6v2h18V6H3zm3 7h12v-2H6v2z"/>
                </svg>
                Status: ${option.label}
            `;
            statusDropdown.remove();
            statusDropdown = null;
            
            const searchInput = document.getElementById('searchInput');
            filterTable(searchInput?.value || '');
        });
        statusDropdown.appendChild(item);
    });
    
    document.body.appendChild(statusDropdown);
    
    // Close on outside click
    setTimeout(() => {
        document.addEventListener('click', function closeDropdown(e) {
            if (!statusDropdown?.contains(e.target) && !btn.contains(e.target)) {
                if (statusDropdown) {
                    statusDropdown.remove();
                    statusDropdown = null;
                }
                document.removeEventListener('click', closeDropdown);
            }
        }, { once: true });
    }, 0);
});

// Category filter dropdown
let categoryDropdown = null;
document.getElementById('categoryFilterBtn')?.addEventListener('click', (e) => {
    e.stopPropagation();
    
    // Close status dropdown if open
    if (statusDropdown) {
        statusDropdown.remove();
        statusDropdown = null;
    }
    
    // Remove existing category dropdown if open
    if (categoryDropdown) {
        categoryDropdown.remove();
        categoryDropdown = null;
        return;
    }
    
    const btn = e.target.closest('#categoryFilterBtn');
    const rect = btn.getBoundingClientRect();
    
    categoryDropdown = document.createElement('div');
    categoryDropdown.className = 'filter-dropdown-menu';
    categoryDropdown.style.position = 'fixed';
    categoryDropdown.style.top = (rect.bottom + 5) + 'px';
    categoryDropdown.style.left = rect.left + 'px';
    categoryDropdown.style.zIndex = '1000';
    
    const categoryOptions = [
        { value: 'all', label: 'All Categories' },
        { value: 'fruits-vegetables', label: 'Fruits & Vegetables' },
        { value: 'baked-goods', label: 'Baked Goods' },
        { value: 'cooked-meals', label: 'Cooked Meals' },
        { value: 'packaged-goods', label: 'Packaged Goods' },
        { value: 'beverages', label: 'Beverages' }
    ];
    
    categoryOptions.forEach(option => {
        const item = document.createElement('button');
        item.type = 'button';
        item.className = 'filter-dropdown-item';
        if (currentCategoryFilter === option.value) {
            item.classList.add('active');
        }
        item.textContent = option.label;
        item.addEventListener('click', () => {
            currentCategoryFilter = option.value;
            btn.innerHTML = `
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
                Category: ${option.label}
            `;
            categoryDropdown.remove();
            categoryDropdown = null;
            
            const searchInput = document.getElementById('searchInput');
            filterTable(searchInput?.value || '');
        });
        categoryDropdown.appendChild(item);
    });
    
    document.body.appendChild(categoryDropdown);
    
    // Close on outside click
    setTimeout(() => {
        document.addEventListener('click', function closeDropdown(e) {
            if (!categoryDropdown?.contains(e.target) && !btn.contains(e.target)) {
                if (categoryDropdown) {
                    categoryDropdown.remove();
                    categoryDropdown = null;
                }
                document.removeEventListener('click', closeDropdown);
            }
        }, { once: true });
    }, 0);
});

// Pagination functionality
const pageButtons = document.querySelectorAll('.page-btn');
pageButtons.forEach(button => {
    if (!button.disabled && !button.id) {
        button.addEventListener('click', () => {
            // Remove active class from all buttons
            pageButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            button.classList.add('active');
            showNotification(`Loading page ${button.textContent}...`, 'info');
        });
    }
});

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
        z-index: 10001;
        transform: translateX(400px);
        transition: transform 0.3s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        max-width: 300px;
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

// Notification bell functionality is now handled by notifications.js

// Form validation enhancement
function validateForm() {
    const requiredFields = ['itemName', 'itemQuantity', 'itemOriginalPrice', 'itemCategory', 'itemExpiry'];
    let isValid = true;
    
    requiredFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field && !field.value.trim()) {
            field.style.borderColor = '#f44336';
            isValid = false;
        } else if (field) {
            field.style.borderColor = '#ddd';
        }
    });
    
    // Validate price
    const priceField = document.getElementById('itemOriginalPrice');
    if (priceField && priceField.value && (isNaN(priceField.value) || parseFloat(priceField.value) <= 0)) {
        priceField.style.borderColor = '#f44336';
        isValid = false;
    }
    
    // Validate quantity
    const quantityField = document.getElementById('itemQuantity');
    if (quantityField && quantityField.value && (isNaN(quantityField.value) || parseInt(quantityField.value) <= 0)) {
        quantityField.style.borderColor = '#f44336';
        isValid = false;
    }
    
    return isValid;
}

// Keyboard shortcuts
document.addEventListener('keydown', (e) => {
    // ESC key to close modals and mobile menu
    if (e.key === 'Escape') {
        closeMobileMenu();
        document.querySelectorAll('.modal.show').forEach(modal => {
            const modalId = modal.id;
            closeModal(modalId);
        });
        document.querySelectorAll('.actions-menu.show').forEach(dropdown => {
            dropdown.classList.remove('show');
        });
    }
    
    // Ctrl/Cmd + N to add new item
    if ((e.ctrlKey || e.metaKey) && e.key === 'n' && !e.target.closest('.modal')) {
        e.preventDefault();
        showModal('itemModal');
    }
    
    // Ctrl/Cmd + F to focus search
    if ((e.ctrlKey || e.metaKey) && e.key === 'f' && !e.target.closest('.modal')) {
        e.preventDefault();
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.focus();
        }
    }
});

// Real-time search with debouncing (if searchInputElement is different from searchInput)
if (searchInput && !searchInput.hasAttribute('data-listener-added')) {
    let searchTimeout;
    searchInput.setAttribute('data-listener-added', 'true');
    searchInput.addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const searchTerm = e.target.value.toLowerCase();
            filterTable(searchTerm);
        }, 300);
    });
}

// Responsive handling
function handleResize() {
    try {
        if (window.innerWidth > 768) {
            // Check if closeMobileMenu function exists
            if (typeof closeMobileMenu === 'function') {
                closeMobileMenu();
            } else {
                // Try to close menu manually if function doesn't exist
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('overlay');
                if (sidebar) {
                    sidebar.classList.remove('mobile-visible');
                }
                if (overlay) {
                    overlay.classList.remove('active');
                }
                document.body.style.overflow = '';
                const mainContent = document.getElementById('mainContent');
                if (mainContent) {
                    mainContent.style.overflow = '';
                }
            }
        }
        
        // Close any open dropdowns on resize
        document.querySelectorAll('.actions-menu.show').forEach(menu => {
            menu.classList.remove('show');
        });
    } catch (error) {
        console.error('Resize handler error:', error);
        // Don't show error notification for resize errors
    }
}

// Debounce resize handler to prevent too many calls
let resizeTimeout;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(handleResize, 150);
});

// Initialize animations and functionality
document.addEventListener('DOMContentLoaded', () => {
    // Animate stats cards on load
    const statsCards = document.querySelectorAll('.stat-card');
    statsCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'all 0.6s ease';
        
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 200 + (index * 100));
    });

    // Animate table rows
    const tableRows = document.querySelectorAll('#itemsTableBody tr');
    tableRows.forEach((row, index) => {
        row.style.opacity = '0';
        row.style.transform = 'translateX(-20px)';
        row.style.transition = 'all 0.4s ease';
        
        setTimeout(() => {
            row.style.opacity = '1';
            row.style.transform = 'translateX(0)';
        }, 500 + (index * 50));
    });

    // Set minimum date for expiry field to today
    const today = new Date().toISOString().split('T')[0];
    const expiryField = document.getElementById('itemExpiry');
    if (expiryField) {
        expiryField.min = today;
    }
});

// Error boundary for production - only catch unhandled errors
window.addEventListener('error', (e) => {
    console.error('Application error:', e.error);
    
    // Don't show error for resize-related issues or expected errors
    const errorMessage = e.error?.message || e.message || '';
    const isResizeError = errorMessage.includes('resize') || 
                         errorMessage.includes('Resize') ||
                         e.filename?.includes('resize');
    
    // Don't show error for null reference errors that might be expected
    const isNullReference = errorMessage.includes('null') || 
                           errorMessage.includes('undefined') ||
                           errorMessage.includes('Cannot read');
    
    // Only show notification for unexpected errors
    if (!isResizeError && !isNullReference && e.error) {
        // Check if showNotification exists before calling
        if (typeof showNotification === 'function') {
            showNotification('An error occurred. Please refresh the page.', 'error');
        }
    }
});

// View Details Modal functionality
const viewDetailsModal = document.getElementById('viewDetailsModal');
const closeViewModal = document.getElementById('closeViewModal');

// Close modal functions
function closeViewDetailsModal() {
    viewDetailsModal.classList.remove('active');
    document.body.style.overflow = '';
}

// Event listeners for View Details modal
if (closeViewModal) {
    closeViewModal.addEventListener('click', closeViewDetailsModal);
}

// Close modal when clicking outside
if (viewDetailsModal) {
    viewDetailsModal.addEventListener('click', (e) => {
        if (e.target === viewDetailsModal) {
            closeViewDetailsModal();
        }
    });
}

// View details function
function viewDetails(itemId) {
    // Find the item data from the table
    const row = document.querySelector(`tr[data-id="${itemId}"]`);
    if (!row) return;

    // Extract data from the row
    const itemData = {
        id: row.dataset.id,
        name: row.dataset.name,
        description: row.dataset.description,
        category: row.dataset.category,
        quantity: row.dataset.quantity,
        originalPrice: parseFloat(row.dataset.originalPrice),
        discountPercentage: parseFloat(row.dataset.discountPercentage) || 0,
        discountedPrice: parseFloat(row.dataset.discountedPrice) || parseFloat(row.dataset.originalPrice),
        expiry: row.dataset.expiry,
        address: row.dataset.address,
        pickupAvailable: row.dataset.pickupAvailable === 'true',
        deliveryAvailable: row.dataset.deliveryAvailable === 'true',
        image: row.dataset.image
    };

    // Calculate current price
    const currentPrice = itemData.discountedPrice || itemData.originalPrice;
    const discount = itemData.discountPercentage;

    // Populate modal with data
    document.getElementById('viewProductTitle').textContent = itemData.name;
    document.getElementById('viewBakeryName').textContent = 'Sample Bakery'; // You can get this from establishment data
    document.getElementById('viewProductImage').src = itemData.image || 'https://via.placeholder.com/400x300/4a7c59/ffffff?text=' + encodeURIComponent(itemData.name.charAt(0));
    document.getElementById('viewCurrentPrice').textContent = `₱ ${currentPrice.toFixed(2)}`;
    document.getElementById('viewOriginalPrice').textContent = `₱ ${itemData.originalPrice.toFixed(2)}`;
    document.getElementById('viewLocation').textContent = itemData.address || 'Location not specified';
    document.getElementById('viewPickupOption').textContent = itemData.pickupAvailable ? 'Pick-Up Available' : 'Pick-Up Not Available';
    document.getElementById('viewExpiryDate').textContent = `Expiry Date: ${new Date(itemData.expiry).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}`;
    document.getElementById('viewOperatingHours').textContent = 'Mon - Sat | 7:00 am - 5:00 pm';
    document.getElementById('viewAvailability').textContent = `${itemData.quantity} pieces available`;
    document.getElementById('viewQuantityInput').max = itemData.quantity;

    // Show/hide discount badge
    const discountBadge = document.getElementById('viewDiscountBadge');
    if (discount > 0) {
        discountBadge.textContent = `${Math.round(discount)}% OFF`;
        discountBadge.style.display = 'inline-block';
    } else {
        discountBadge.style.display = 'none';
    }

    // Show modal
    viewDetailsModal.classList.add('active');
    document.body.style.overflow = 'hidden';
    
    // Show loading state for reviews immediately
    showReviewsLoading();
    
    // Fetch and load reviews
    loadReviews(itemId);
}

// Function to show loading state for reviews
function showReviewsLoading() {
    const reviewsList = document.getElementById('viewReviewsList');
    const ratingStars = document.getElementById('viewRatingStars');
    const ratingText = document.getElementById('viewRatingText');
    const noReviews = document.getElementById('viewNoReviews');
    const showMoreBtn = document.getElementById('viewShowMoreBtn');
    
    // Show loading state
    if (reviewsList) {
        reviewsList.innerHTML = '<div class="no-reviews"><p>Loading reviews...</p></div>';
    }
    if (noReviews) {
        noReviews.style.display = 'none';
    }
    if (showMoreBtn) {
        showMoreBtn.style.display = 'none';
    }
    if (ratingText) {
        ratingText.textContent = 'Loading...';
    }
    if (ratingStars) {
        ratingStars.innerHTML = '';
    }
}

// Function to load reviews for a food listing
async function loadReviews(foodListingId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                      document.querySelector('input[name="_token"]')?.value;
    
    try {
        // Add timeout to prevent hanging
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 5000); // 5 second timeout
        
        const response = await fetch(`/establishment/food-listings/${foodListingId}/reviews`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            signal: controller.signal
        });
        
        clearTimeout(timeoutId);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success !== false) {
            renderReviews(data.reviews || [], data.average_rating || 0, data.total_reviews || 0);
        } else {
            console.error('Error loading reviews:', data.error || data.message);
            renderReviews([], 0, 0);
        }
    } catch (error) {
        if (error.name === 'AbortError') {
            console.error('Request timeout while fetching reviews');
            renderReviews([], 0, 0, 'Request timeout. Please try again.');
        } else {
            console.error('Error fetching reviews:', error);
            renderReviews([], 0, 0, 'Failed to load reviews. Please try again.');
        }
    }
}

// Function to render reviews
function renderReviews(reviews, averageRating, totalReviews, errorMessage = null) {
    const reviewsList = document.getElementById('viewReviewsList');
    const noReviews = document.getElementById('viewNoReviews');
    const ratingStars = document.getElementById('viewRatingStars');
    const ratingText = document.getElementById('viewRatingText');
    const showMoreBtn = document.getElementById('viewShowMoreBtn');
    
    // Render rating stars immediately (optimized)
    if (ratingStars) {
        renderRatingStars(ratingStars, averageRating);
    }
    
    // Update rating text
    if (ratingText) {
        if (errorMessage) {
            ratingText.textContent = 'Error loading ratings';
        } else if (totalReviews > 0) {
            ratingText.textContent = `${averageRating} out of 5 (${totalReviews} reviews)`;
        } else {
            ratingText.textContent = 'No ratings yet';
        }
    }
    
    // Render reviews using innerHTML for better performance
    if (errorMessage) {
        if (reviewsList) {
            reviewsList.innerHTML = `<div class="no-reviews"><p>${errorMessage}</p></div>`;
        }
        if (noReviews) noReviews.style.display = 'none';
        if (showMoreBtn) showMoreBtn.style.display = 'none';
        return;
    }
    
    if (reviews.length === 0) {
        if (reviewsList) reviewsList.innerHTML = '';
        if (noReviews) noReviews.style.display = 'block';
        if (showMoreBtn) showMoreBtn.style.display = 'none';
    } else {
        // Build all HTML at once for maximum performance
        let reviewsHTML = '';
        reviews.forEach(review => {
            const userName = review.user_name || 'Anonymous';
            const nameParts = userName.split(' ');
            const initials = nameParts.length >= 2 
                ? (nameParts[0].charAt(0) + nameParts[1].charAt(0)).toUpperCase()
                : userName.charAt(0).toUpperCase();
            
            let starsHTML = '';
            for (let i = 1; i <= 5; i++) {
                const starClass = i <= review.rating ? 'star filled' : 'star empty';
                starsHTML += `<svg class="${starClass}" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>`;
            }
            
            reviewsHTML += `<div class="review-item" data-rating="${review.rating}">
                <div class="review-header">
                    ${review.avatar ? `<img src="${review.avatar}" alt="${userName}" class="review-avatar">` : `<div class="review-avatar-initials">${initials}</div>`}
                    <span class="reviewer-name">${userName}</span>
                    <div class="review-rating">${starsHTML}</div>
                </div>
                ${review.flagged 
                    ? `<p class="review-hidden-tag">This review is hidden.</p>` 
                    : `${review.comment ? `<p class="review-comment">${review.comment}</p>` : ''}
                       ${review.image_path ? `<div class="review-media"><img src="${review.image_path}" alt="Review image" class="review-image"></div>` : ''}
                       ${review.video_path ? `<div class="review-media"><video src="${review.video_path}" controls class="review-video"></video></div>` : ''}`
                }
            </div>`;
        });
        
        if (reviewsList) {
            reviewsList.innerHTML = reviewsHTML;
        }
        if (noReviews) noReviews.style.display = 'none';
        
        // Show "Show more" button if there are more reviews (for future pagination)
        if (showMoreBtn) {
            showMoreBtn.style.display = 'none'; // Hide for now, can be implemented later
        }
    }
}

// Function to render rating stars (optimized with innerHTML)
function renderRatingStars(container, rating) {
    if (!container) return;
    
    let starsHTML = '';
    for (let i = 1; i <= 5; i++) {
        let starClass = 'star empty';
        if (i <= Math.floor(rating)) {
            starClass = 'star filled';
        } else if (i - 0.5 <= rating) {
            starClass = 'star half';
        }
        
        starsHTML += `<svg class="${starClass}" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
        </svg>`;
    }
    
    container.innerHTML = starsHTML;
}

// Function to create a review element
function createReviewElement(review) {
    const reviewItem = document.createElement('div');
    reviewItem.className = 'review-item';
    reviewItem.setAttribute('data-rating', review.rating);
    
    // Get user initials
    const userName = review.user_name || 'Anonymous';
    const nameParts = userName.split(' ');
    const initials = nameParts.length >= 2 
        ? (nameParts[0].charAt(0) + nameParts[1].charAt(0)).toUpperCase()
        : userName.charAt(0).toUpperCase();
    
    let reviewHTML = `
        <div class="review-header">
            ${review.avatar ? `<img src="${review.avatar}" alt="${userName}" class="review-avatar">` : `<div class="review-avatar-initials">${initials}</div>`}
            <span class="reviewer-name">${userName}</span>
            <div class="review-rating">
    `;
    
    // Add stars
    for (let i = 1; i <= 5; i++) {
        const starClass = i <= review.rating ? 'star filled' : 'star empty';
        reviewHTML += `
            <svg class="${starClass}" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
        `;
    }
    
    reviewHTML += `
            </div>
        </div>
    `;
    
    // If flagged, show hidden message only; otherwise show content
    if (review.flagged) {
        reviewHTML += `<p class="review-hidden-tag">This review is hidden.</p>`;
    } else {
        // Add comment if exists
        if (review.comment) {
            reviewHTML += `<p class="review-comment">${review.comment}</p>`;
        }
        
        // Add image if exists
        if (review.image_path) {
            reviewHTML += `
                <div class="review-media">
                    <img src="${review.image_path}" alt="Review image" class="review-image">
                </div>
            `;
        }
        
        // Add video if exists
        if (review.video_path) {
            reviewHTML += `
                <div class="review-media">
                    <video src="${review.video_path}" controls class="review-video"></video>
                </div>
            `;
        }
    }
    
    reviewItem.innerHTML = reviewHTML;
    return reviewItem;
}

// Quantity controls for View Details modal
const viewDecreaseQty = document.getElementById('viewDecreaseQty');
const viewIncreaseQty = document.getElementById('viewIncreaseQty');
const viewQuantityInput = document.getElementById('viewQuantityInput');

if (viewDecreaseQty) {
    viewDecreaseQty.addEventListener('click', () => {
        const currentValue = parseInt(viewQuantityInput.value) || 1;
        if (currentValue > 1) {
            viewQuantityInput.value = currentValue - 1;
        }
    });
}

if (viewIncreaseQty) {
    viewIncreaseQty.addEventListener('click', () => {
        const currentValue = parseInt(viewQuantityInput.value) || 1;
        const maxValue = parseInt(viewQuantityInput.max) || 1;
        if (currentValue < maxValue) {
            viewQuantityInput.value = currentValue + 1;
        }
    });
}

// Review filter functionality - use event delegation for dynamically loaded content
document.addEventListener('click', (e) => {
    if (e.target.closest('.rating-filters .filter-btn')) {
        const btn = e.target.closest('.rating-filters .filter-btn');
        const filterBtns = document.querySelectorAll('.rating-filters .filter-btn');
        
        // Remove active class from all buttons
        filterBtns.forEach(b => b.classList.remove('active'));
        // Add active class to clicked button
        btn.classList.add('active');
        
        // Filter reviews based on rating
        const rating = btn.dataset.rating;
        filterReviews(rating);
    }
});

function filterReviews(rating) {
    const reviewItems = document.querySelectorAll('.review-item');
    
    reviewItems.forEach(item => {
        if (rating === 'all') {
            item.style.display = 'flex';
        } else {
            const itemRating = parseInt(item.dataset.rating) || 5; // Default to 5 stars
            if (itemRating.toString() === rating) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        }
    });
}

// Show more reviews functionality
const showMoreBtn = document.getElementById('viewShowMoreBtn');
if (showMoreBtn) {
    showMoreBtn.addEventListener('click', () => {
        // In a real app, this would load more reviews from the server
        showNotification('Loading more reviews...', 'info');
    });
}

// Buy Now function
function buyNow() {
    const quantity = document.getElementById('viewQuantityInput').value;
    const productName = document.getElementById('viewProductTitle').textContent;
    showNotification(`Order placed successfully! ${quantity} x ${productName}`, 'success');
    closeViewDetailsModal();
}

// Make functions globally available
window.viewDetails = viewDetails;
window.buyNow = buyNow;

console.log('Listing Management page initialized successfully!');
