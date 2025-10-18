@extends('layouts.establishment')

@section('title', 'Listing Management | SavEats')
@section('header', 'Listing Management')

@section('styles')
<link href="{{ asset('css/listing-management.css') }}" rel="stylesheet">
@endsection

@section('content')
    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ $stats['total_items'] }}</div>
            <div class="stat-label">Total Items</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['active_listings'] }}</div>
            <div class="stat-label">Active Listings</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['expiring_soon'] }}</div>
            <div class="stat-label">Expiring Soon</div>
        </div>
        <div class="stat-card unsold">
            <div class="stat-value">{{ $stats['unsold_items'] }}</div>
            <div class="stat-label">Unsold Items</div>
        </div>
    </div>

    <!-- Main Panel -->
    <div class="main-panel">
                    <!-- Panel Header -->
                    <div class="panel-header">
                        <h2 class="panel-title">Food Listings</h2>
                        <div class="header-actions-panel">
                            <button class="btn btn-primary" id="addFoodBtn">
                                <svg class="btn-icon" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                                </svg>
                                List Food
                            </button>
                            <button class="btn btn-secondary" id="filterBtn">
                                <svg class="btn-icon" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M10 18h4v-2h-4v2zM3 6v2h18V6H3zm3 7h12v-2H6v2z"/>
                                </svg>
                                Filter
                            </button>
                            <button class="btn btn-secondary" id="sortBtn">
                                <svg class="btn-icon" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M3 18h6v-2H3v2zM3 6v2h18V6H3zm0 7h12v-2H3v2z"/>
                                </svg>
                                Sort
                            </button>
                        </div>
                    </div>

                    <!-- Bulk Actions Bar -->
                    <div class="bulk-actions" id="bulkActions">
                        <div class="bulk-info">
                            <span id="selectedCount">0</span> items selected
                        </div>
                        <button class="btn btn-secondary" id="bulkEditBtn">Edit Selected</button>
                        <button class="btn btn-secondary" id="bulkDonateBtn">Mark for Donation</button>
                        <button class="btn btn-secondary" id="bulkDeleteBtn" style="color: #dc3545; border-color: #dc3545;">Delete Selected</button>
                    </div>

                    <!-- Search and Filter Bar -->
                    <div class="search-filter-bar">
                        <div class="search-box">
                            <input type="text" class="search-input" placeholder="Search items by name, ID, or category..." id="searchInput">
                            <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M15.5 14h-.79l-.28-.27A6.5 6.5 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                            </svg>
                        </div>
                        <div class="filter-dropdown">
                            <button class="filter-btn" id="statusFilterBtn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M10 18h4v-2h-4v2zM3 6v2h18V6H3zm3 7h12v-2H6v2z"/>
                                </svg>
                                Status: All
                            </button>
                        </div>
                        <div class="filter-dropdown">
                            <button class="filter-btn" id="categoryFilterBtn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                                Category: All
                            </button>
                        </div>
                    </div>

        <!-- Table Container -->
        <div class="table-container">
            <table class="table">
                 <thead>
                     <tr>
                         <th>
                             <input type="checkbox" class="checkbox" id="selectAll">
                         </th>
                         <th>Item Name</th>
                         <th>Quantity</th>
                         <th>Price</th>
                         <th>Expiry Date</th>
                         <th>Status</th>
                         <th>Actions</th>
                     </tr>
                 </thead>
                <tbody id="itemsTableBody">
                     @foreach($foodItems as $item)
                     <tr data-id="{{ $item['id'] }}" 
                         data-name="{{ $item['name'] }}"
                         data-description="{{ $item['description'] ?? '' }}"
                         data-category="{{ $item['category'] }}"
                         data-quantity="{{ $item['quantity'] }}"
                         data-original-price="{{ $item['price'] }}"
                         data-discount-percentage="{{ $item['discount'] ?? 0 }}"
                         data-discounted-price="{{ $item['discounted_price'] ?? '' }}"
                         data-expiry="{{ $item['expiry'] }}"
                         data-address="{{ $item['address'] ?? '' }}"
                         data-pickup-available="{{ $item['pickup_available'] ? 'true' : 'false' }}"
                         data-delivery-available="{{ $item['delivery_available'] ? 'true' : 'false' }}"
                         data-image="{{ $item['image'] ?? '' }}">
                         <td>
                             <input type="checkbox" class="checkbox item-checkbox" data-id="{{ $item['id'] }}">
                         </td>
                         <td>
                             <div class="item-info">
                                 <div class="item-image">
                                     @if(isset($item['image']) && $item['image'] && !str_contains($item['image'], 'placeholder'))
                                         <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="item-img">
                                     @else
                                         <span class="item-initials">{{ substr($item['name'], 0, 2) }}</span>
                                     @endif
                                 </div>
                                 <div class="item-details">
                                     <div class="item-name">{{ $item['name'] }}</div>
                                     <div class="item-id">ID#{{ $item['id'] }}</div>
                                 </div>
                             </div>
                         </td>
                         <td>{{ $item['quantity'] }}</td>
                         <td>₱{{ number_format($item['price'], 2) }}</td>
                         <td>{{ \Carbon\Carbon::parse($item['expiry'])->format('M d, Y') }}</td>
                         <td>
                             <span class="status-badge {{ $item['status'] }}">{{ strtoupper($item['status']) }}</span>
                         </td>
                         <td>
                             <div class="action-dropdown">
                                 <button class="action-btn menu-btn" onclick="toggleDropdown(this)" title="More Actions">
                                     <svg viewBox="0 0 24 24" width="20" height="20">
                                         <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                                     </svg>
                                 </button>
                                 <div class="dropdown-menu">
                                     <button class="dropdown-item" type="button" onclick="editItem({{ $item['id'] }})">Edit</button>
                                     <button class="dropdown-item" type="button" onclick="viewItem({{ $item['id'] }})">View Details</button>
                                     <button class="dropdown-item" type="button" onclick="duplicateItem({{ $item['id'] }})">Duplicate</button>
                                     <button class="dropdown-item" type="button" onclick="donateItem({{ $item['id'] }})">Mark for Donation</button>
                                     <button class="dropdown-item danger" type="button" onclick="deleteItem({{ $item['id'] }})">Delete</button>
                                 </div>
                             </div>
                         </td>
                     </tr>
                     @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination">
            <div class="pagination-info">
                Showing 1-{{ count($foodItems) }} of {{ count($foodItems) }} items
            </div>
            <div class="pagination-controls">
                <button class="page-btn" id="prevBtn" disabled>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
                    </svg>
                </button>
                <button class="page-btn active">1</button>
                <button class="page-btn">2</button>
                <button class="page-btn">3</button>
                <span style="padding: 0 10px; color: #999;">...</span>
                <button class="page-btn" id="nextBtn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Item Modal -->
<div class="modal" id="itemModal">
    <div class="modal-content add-list-modal">
        <div class="modal-header">
            <div class="header-left">
                <button class="back-btn" onclick="closeModal('itemModal')">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
                    </svg>
                </button>
                <h3 id="modalTitle">Add List Form</h3>
            </div>
            <div class="establishment-info">
                <div class="establishment-name">{{ session('user_name', 'Establishment User') }}</div>
                <div class="establishment-subtitle">Establishment</div>
            </div>
        </div>
        
        <form id="itemForm" class="modal-body">
            <!-- Image Upload Section -->
            <div class="image-upload-section">
                <div class="image-upload-box">
                    <div class="upload-placeholder">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                        </svg>
                        <p>Upload Food Image</p>
                    </div>
                    <input type="file" id="itemImage" name="image" accept="image/*" class="image-input">
                </div>
            </div>

            <!-- Food Basic Information Section -->
            <div class="form-section">
                <h4 class="section-title">Food Basic Information</h4>
                <div class="form-group">
                    <div class="input-with-icon">
                        <input type="text" id="itemName" name="name" placeholder="Enter Item Name" required>
                        <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                    </div>
                </div>
                <div class="form-group">
                    <div class="input-with-icon">
                        <select id="itemCategory" name="category" required>
                            <option value="">Select Item Category</option>
                            <option value="fruits-vegetables">Fruits & Vegetables</option>
                            <option value="baked-goods">Baked Goods</option>
                            <option value="cooked-meals">Cooked Meals</option>
                            <option value="packaged-goods">Packaged Goods</option>
                            <option value="beverages">Beverages</option>
                        </select>
                        <svg class="input-icon dropdown-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M7 10l5 5 5-5z"/>
                        </svg>
                    </div>
                </div>
                <div class="form-group">
                    <textarea id="itemDescription" name="description" placeholder="Write description" rows="3"></textarea>
                </div>
            </div>

            <!-- Quality & Pricing Section -->
            <div class="form-section">
                <h4 class="section-title">Quality & Pricing</h4>
                <div class="form-group">
                    <label>Quantity</label>
                    <div class="quantity-controls">
                        <button type="button" class="quantity-btn minus" onclick="decreaseQuantity()">-</button>
                        <input type="number" id="itemQuantity" name="quantity" placeholder="Quantity" required min="1" value="1">
                        <button type="button" class="quantity-btn plus" onclick="increaseQuantity()">+</button>
                    </div>
                </div>
                <div class="form-group">
                    <div class="input-with-icon">
                        <input type="number" id="itemOriginalPrice" name="original_price" placeholder="Enter Original Price" min="0" step="0.01">
                        <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                    </div>
                </div>
                <div class="form-group">
                    <div class="input-with-icon">
                        <select id="itemDiscount" name="discount">
                            <option value="">Discount Percentage</option>
                            <option value="10">10%</option>
                            <option value="20">20%</option>
                            <option value="30">30%</option>
                            <option value="40">40%</option>
                            <option value="50">50%</option>
                        </select>
                        <svg class="input-icon dropdown-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M7 10l5 5 5-5z"/>
                        </svg>
                    </div>
                </div>
                <div class="form-group">
                    <div class="input-with-icon">
                        <input type="text" id="itemDiscountedPrice" name="discounted_price" placeholder="Automated Discounted Price" readonly>
                        <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Expiry & Time Section -->
            <div class="form-section">
                <h4 class="section-title">Expiry & Time</h4>
                <div class="form-group">
                    <div class="input-with-icon">
                        <input type="date" id="itemExpiry" name="expiry" required>
                        <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Location & Logistics Section -->
            <div class="form-section">
                <h4 class="section-title">Location & Logistics</h4>
                <div class="form-group">
                    <div class="input-with-icon">
                        <input type="text" id="itemAddress" name="address" placeholder="Enter Address">
                        <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                        </svg>
                    </div>
                </div>
                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="itemPickup" name="pickup" value="1">
                        <span class="checkmark"></span>
                        Pickup
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" id="itemDelivery" name="delivery" value="1">
                        <span class="checkmark"></span>
                        Delivery
                    </label>
                </div>
            </div>
        </form>
        
        <div class="modal-footer">
            <button type="button" class="btn btn-primary publish-btn" onclick="saveItem()">
                Publish Now
            </button>
        </div>
    </div>
</div>

<!-- Overlay -->
<div class="overlay" id="overlay"></div>

<!-- View Details Modal -->
<div class="view-details-modal" id="viewDetailsModal">
    <div class="modal-content">
        <!-- Modal Header -->
        <div class="modal-header">
            <button class="close-btn" id="closeViewModal">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                </svg>
            </button>
            <div class="product-title-section">
                <h2 class="product-title" id="viewProductTitle">Joy Bread</h2>
                <p class="bakery-name" id="viewBakeryName">Henry Happy Bakery</p>
            </div>
            <button class="more-options-btn" id="viewMoreOptions">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                </svg>
            </button>
        </div>

        <!-- Product Image -->
        <div class="product-image-section">
            <img id="viewProductImage" src="" alt="Product Image" class="product-image">
        </div>

        <!-- Pricing Section -->
        <div class="pricing-section">
            <div class="price-info">
                <span class="current-price" id="viewCurrentPrice">₱ 25.00</span>
                <div class="discount-badge" id="viewDiscountBadge">50% off</div>
            </div>
            <span class="original-price" id="viewOriginalPrice">₱ 50.00</span>
        </div>

        <!-- Product Details -->
        <div class="product-details-section">
            <div class="detail-item">
                <svg class="detail-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                </svg>
                <span id="viewLocation">Sunny Side St. 1234</span>
            </div>
            <div class="detail-item">
                <svg class="detail-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
                <span id="viewPickupOption">Pick-Up Only</span>
            </div>
            <div class="detail-row">
                <div class="detail-item">
                    <svg class="detail-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                    <span id="viewExpiryDate">Expiry Date: June 27, 2025</span>
                </div>
                <div class="operating-hours" id="viewOperatingHours">Mon - Sat | 7:00 am - 5:00 pm</div>
            </div>
        </div>

        <!-- Quantity Selector -->
        <div class="quantity-section">
            <div class="quantity-controls">
                <button class="quantity-btn decrease" id="viewDecreaseQty">-</button>
                <input type="number" id="viewQuantityInput" class="quantity-input" value="1" min="1">
                <button class="quantity-btn increase" id="viewIncreaseQty">+</button>
            </div>
            <div class="availability-info">
                <span id="viewAvailability">10 pieces available</span>
            </div>
        </div>

        <!-- Reviews Section -->
        <div class="reviews-section">
            <div class="rating-summary">
                <div class="stars">
                    <svg class="star filled" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    <svg class="star filled" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    <svg class="star filled" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    <svg class="star filled" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    <svg class="star half" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                </div>
                <span class="rating-text" id="viewRatingText">4.6 out 5</span>
            </div>
            
            <div class="rating-filters">
                <button class="filter-btn active" data-rating="all">All</button>
                <button class="filter-btn" data-rating="5">5 Stars</button>
                <button class="filter-btn" data-rating="4">4 Stars</button>
                <button class="filter-btn" data-rating="3">3 Stars</button>
                <button class="filter-btn" data-rating="2">2 Stars</button>
                <button class="filter-btn" data-rating="1">1 Star</button>
            </div>

            <div class="reviews-list" id="viewReviewsList">
                <div class="review-item">
                    <div class="review-avatar">
                        <div class="review-avatar-initials">JD</div>
                    </div>
                    <div class="review-content">
                        <div class="review-header">
                            <span class="reviewer-name">John Doe</span>
                            <div class="review-stars">
                                <svg class="star filled" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                                <svg class="star filled" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                                <svg class="star filled" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                                <svg class="star filled" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                                <svg class="star filled" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            </div>
                        </div>
                        <p class="review-text">Supporting line text lorem ipsum dolor sit amet, consectetur.</p>
                    </div>
                </div>
                <div class="review-item">
                    <div class="review-avatar">
                        <div class="review-avatar-initials">JD</div>
                    </div>
                    <div class="review-content">
                        <div class="review-header">
                            <span class="reviewer-name">John Doe</span>
                            <div class="review-stars">
                                <svg class="star filled" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                                <svg class="star filled" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                                <svg class="star filled" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                                <svg class="star filled" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                                <svg class="star filled" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            </div>
                        </div>
                        <p class="review-text">Supporting line text lorem ipsum dolor sit amet, consectetur.</p>
                    </div>
                </div>
            </div>

            <button class="show-more-btn" id="viewShowMoreBtn">Show more (20)</button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="{{ asset('js/listing-management.js') }}"></script>
@endsection
