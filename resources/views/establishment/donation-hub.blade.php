@extends('layouts.establishment')

@section('title', 'Donation Hub | SavEats')

@section('header', 'Donation Hub')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Afacad:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/donation-hub.css') }}">
@endsection

@section('content')
@if(!($isVerified ?? true))
<div style="padding: 16px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; color: #856404; font-size: 14px; margin-bottom: 20px;">
    Your account is not verified. Please wait for admin approval.
</div>
@endif
<!-- Donation Requests Section -->
<div class="donation-requests-section">
    <div class="section-header">
        <h3 class="section-title">Donation Requests</h3>
        <span id="requestsCount">{{ count($donationRequests ?? []) }} Request{{ count($donationRequests ?? []) !== 1 ? 's' : '' }}</span>
    </div>

    <!-- Filters for Donation Requests -->
    <div class="filters-section">
        <div class="filters-header">
            <h4>Filters</h4>
            <button class="btn-clear-filters" id="clearRequestFilters">Clear All</button>
        </div>
        <div class="filters-grid">
            <div class="filter-group">
                <label for="requestSearchInput">Search</label>
                <input type="text" id="requestSearchInput" class="filter-input" placeholder="Search by foodbank, item name...">
            </div>
            <div class="filter-group">
                <label for="requestStatusFilter">Status</label>
                <select id="requestStatusFilter" class="filter-select">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="active">Active</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="requestCategoryFilter">Category</label>
                <select id="requestCategoryFilter" class="filter-select">
                    <option value="">All Categories</option>
                    <option value="fruits-vegetables">Fruits & Vegetables</option>
                    <option value="baked-goods">Baked Goods</option>
                    <option value="cooked-meals">Cooked Meals</option>
                    <option value="packaged-goods">Packaged Goods</option>
                    <option value="beverages">Beverages</option>
                    <option value="dairy">Dairy</option>
                    <option value="meat-seafood">Meat & Seafood</option>
                    <option value="other">Other</option>
                </select>
            </div>
        </div>
    </div>

    <div class="requests-grid" id="requestsGrid">
        @forelse($donationRequests ?? [] as $request)
        <div class="request-card" data-id="{{ $request['id'] }}">
            <div class="request-card-header">
                <div class="request-logo-circle">
                    @if(isset($request['foodbank_profile_image']) && $request['foodbank_profile_image'])
                        <img src="{{ asset('storage/' . $request['foodbank_profile_image']) }}" alt="{{ $request['foodbank_name'] }}" class="foodbank-card-profile-image">
                    @else
                        <div class="foodbank-card-profile-placeholder">{{ strtoupper(substr($request['foodbank_name'], 0, 1)) }}</div>
                    @endif
                </div>
            </div>
            <div class="request-card-body">
                <h4 class="request-foodbank-name">{{ $request['foodbank_name'] }}</h4>
                <p class="request-item-name">{{ $request['item_name'] }}</p>
                <p class="request-quantity">{{ $request['quantity'] }} pcs. • {{ ucfirst($request['category']) }}</p>
            </div>
            <div class="request-card-actions">
                <button class="btn-view-details-outline" onclick="viewRequestDetails('{{ $request['id'] }}')">View Details</button>
                <button class="btn-donate-now" onclick="donateNow('{{ $request['id'] }}')">Donate Now</button>
            </div>
        </div>
        @empty
        <div class="no-requests">
            <p>No donation requests available at this time.</p>
        </div>
        @endforelse
    </div>
</div>

<!-- Food Banks Accounts Section -->
<div class="foodbanks-section">
    <div class="section-header">
        <h3 class="section-title">Food Banks Accounts</h3>
        <span id="foodbanksCount">{{ count($foodbanks ?? []) }} Food Bank{{ count($foodbanks ?? []) !== 1 ? 's' : '' }}</span>
    </div>

    <!-- Filters for Food Banks -->
    <div class="filters-section">
        <div class="filters-header">
            <h4>Filters</h4>
            <button class="btn-clear-filters" id="clearFoodbankFilters">Clear All</button>
        </div>
        <div class="filters-grid">
            <div class="filter-group">
                <label for="foodbankSearchInput">Search</label>
                <input type="text" id="foodbankSearchInput" class="filter-input" placeholder="Search by organization name, address...">
            </div>
        </div>
    </div>

    <div class="foodbanks-grid" id="foodbanksGrid">
        @forelse($foodbanks ?? [] as $foodbank)
        <div class="foodbank-card" data-id="{{ $foodbank['id'] }}">
            <div class="foodbank-card-header">
                <div class="foodbank-logo-circle">
                    @if(isset($foodbank['profile_image']) && $foodbank['profile_image'])
                        <img src="{{ asset('storage/' . $foodbank['profile_image']) }}" alt="{{ $foodbank['organization_name'] }}" class="foodbank-card-profile-image">
                    @else
                        <div class="foodbank-card-profile-placeholder">{{ strtoupper(substr($foodbank['organization_name'], 0, 1)) }}</div>
                    @endif
                </div>
            </div>
            <div class="foodbank-card-body">
                <h4 class="foodbank-name">{{ $foodbank['organization_name'] }}</h4>
                <p class="foodbank-address">{{ $foodbank['address'] }}</p>
            </div>
            <div class="foodbank-card-actions">
                <button class="btn-view-details-outline" onclick="viewFoodbankDetails('{{ $foodbank['id'] }}')">View Details</button>
                <button class="btn-contact" onclick="contactFoodbank('{{ $foodbank['id'] }}')">Contact</button>
                <button class="btn-request-donate" onclick="openRequestToDonateFoodbankModal('{{ $foodbank['id'] }}')" @if(!($isVerified ?? true)) disabled style="opacity: 0.5; cursor: not-allowed;" @endif>Request to Donate</button>
            </div>
        </div>
        @empty
        <div class="no-foodbanks">
            <p>No food banks registered yet.</p>
        </div>
        @endforelse
    </div>
</div>

<!-- Donation Request Details Modal -->
<div class="modal-overlay" id="requestDetailsModal">
    <div class="modal modal-request-details">
        <div class="request-details-container">
            <!-- Foodbank Header -->
            <div class="request-details-header">
                <h2 class="request-foodbank-name" id="modalFoodbankName">Food Bank</h2>
                <div class="request-foodbank-logo">
                    <img id="modalFoodbankProfileImage" class="foodbank-profile-image" src="" alt="Food Bank" style="display: none;">
                    <div class="foodbank-profile-placeholder" id="modalFoodbankProfilePlaceholder" style="display: none;"></div>
                </div>
            </div>

            <!-- Item Details -->
            <div class="request-item-section">
                <div class="request-item-name" id="modalItemName">-</div>
                <div class="request-item-quantity" id="modalItemQuantity">-</div>
            </div>

            <!-- Description -->
            <div class="request-detail-section">
                <div class="request-detail-label">Description</div>
                <div class="request-detail-value" id="modalDescription">-</div>
            </div>

            <!-- Contact Information -->
            <div class="request-detail-section">
                <div class="request-contact-item">
                    <span class="contact-icon">📞</span>
                    <span class="contact-text" id="modalPhone">-</span>
                </div>
                <div class="request-contact-item">
                    <span class="contact-icon">📍</span>
                    <span class="contact-text" id="modalAddress">-</span>
                </div>
                <div class="request-contact-item">
                    <span class="contact-icon">✉️</span>
                    <span class="contact-text" id="modalEmail">-</span>
                </div>
            </div>

            <!-- Logistical Details -->
            <div class="request-detail-section">
                <div class="request-logistic-item">
                    <span class="logistic-icon">🕐</span>
                    <div class="logistic-content">
                        <div class="logistic-text" id="modalDateAvailable">-</div>
                        <div class="logistic-label">Date Available</div>
                    </div>
                </div>
                <div class="request-logistic-item">
                    <span class="logistic-icon">📦</span>
                    <div class="logistic-content">
                        <div class="logistic-text">Pickup Only</div>
                    </div>
                </div>
                <div class="request-logistic-item">
                    <span class="logistic-icon">🚚</span>
                    <div class="logistic-content">
                        <div class="logistic-text" id="modalDistributionZones">-</div>
                        <div class="logistic-label">Distribution Zones</div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="request-details-actions">
                @if(!($isVerified ?? true))
                    <div style="padding: 12px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 6px; color: #856404; font-size: 14px; margin-bottom: 12px;">
                        Your account is not verified. Please wait for admin approval.
                    </div>
                @endif
                <button class="btn-donate-now" id="modalDonateNowBtn" @if(!($isVerified ?? true)) disabled style="opacity: 0.5; cursor: not-allowed;" @endif>Donate Now</button>
            </div>

            <!-- Close Button -->
            <button class="modal-close" id="closeRequestDetailsModal" aria-label="Close modal">&times;</button>
        </div>
    </div>
</div>

<!-- Foodbank Details Modal -->
<div class="modal-overlay" id="foodbankDetailsModal">
    <div class="modal modal-foodbank-details">
        <div class="modal-header">
            <h2 id="modalFoodbankDetailsName">Food Bank Details</h2>
            <button class="modal-close" id="closeFoodbankDetailsModal" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body" id="foodbankDetailsModalBody">
            <!-- Content will be populated by JavaScript -->
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="closeFoodbankDetailsModalBtn">Close</button>
            @if(!($isVerified ?? true))
                <div style="padding: 12px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 6px; color: #856404; font-size: 14px; margin-bottom: 12px; width: 100%;">
                    Your account is not verified. Please wait for admin approval.
                </div>
            @endif
            <button class="btn btn-primary" id="requestDonateFromDetailsBtn" @if(!($isVerified ?? true)) disabled style="opacity: 0.5; cursor: not-allowed;" @endif>Request to Donate</button>
        </div>
    </div>
</div>

<!-- Contact Foodbank Modal -->
<div class="modal-overlay" id="contactFoodbankModal">
    <div class="modal modal-contact-foodbank">
        <div class="modal-header">
            <h2 id="contactFoodbankModalTitle">Contact Food Bank</h2>
            <button class="modal-close" id="closeContactFoodbankModal" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body" id="contactFoodbankModalBody">
            <!-- Content will be populated by JavaScript -->
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="closeContactFoodbankModalBtn">Close</button>
        </div>
    </div>
</div>

<!-- Request to Donate Modal (for fulfilling existing requests) -->
<div class="modal-overlay" id="requestToDonateModal">
    <div class="modal modal-request-donate">
        <div class="modal-header">
            <h2 id="requestDonateModalTitle">Request to Donate</h2>
            <button class="modal-close" id="closeRequestToDonateModal" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="requestToDonateForm">
                <input type="hidden" id="requestDonateFoodbankId" name="foodbank_id">
                <input type="hidden" id="donateItemName" name="item_name">
                <input type="hidden" id="donateQuantity" name="quantity">
                <input type="hidden" id="donateCategory" name="category">
                <input type="hidden" id="donateDescription" name="description">
                
                <!-- Item Details (Read-only) -->
                <div class="request-item-section">
                    <div class="request-item-name" id="displayItemName">-</div>
                    <div class="request-item-quantity" id="displayQuantity">-</div>
                </div>

                <!-- Category (Read-only) -->
                <div class="request-detail-section">
                    <div class="request-detail-label">Category</div>
                    <div class="request-detail-value" id="displayCategory">-</div>
                </div>

                <!-- Description (Read-only) -->
                <div class="request-detail-section">
                    <div class="request-detail-label">Description</div>
                    <div class="request-detail-value" id="displayDescription">-</div>
                </div>

                <!-- Pickup Location Section -->
                <div class="form-section">
                    <h3 class="form-section-title">Pickup Location</h3>
                    
                    <div class="form-group">
                        <label for="donateEstablishmentAddress">Establishment Address</label>
                        <input type="text" id="donateEstablishmentAddress" class="form-input" value="{{ $establishment->address ?? 'Address not provided' }}" data-default-value="{{ $establishment->address ?? 'Address not provided' }}" readonly style="background-color: #f5f5f5; cursor: not-allowed;">
                    </div>
                </div>

                <!-- Pickup Notes Section -->
                <div class="form-section">
                    <h3 class="form-section-title">Pickup Notes</h3>
                    
                    <div class="form-group">
                        <label for="donateNotes">Pickup Notes</label>
                        <textarea id="donateNotes" name="establishment_notes" class="form-input" rows="3" placeholder="Additional notes for pickup (optional)"></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="cancelRequestDonate">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Request to Donate to Food Bank Modal (Fully Editable - for Foodbanks Section) -->
<div class="modal-overlay" id="requestToDonateFoodbankModal">
    <div class="modal modal-request-donate">
        <div class="modal-header">
            <h2 id="requestDonateFoodbankModalTitle">Request to Donate</h2>
            <button class="modal-close" id="closeRequestToDonateFoodbankModal" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="requestToDonateFoodbankForm">
                <input type="hidden" id="requestDonateFoodbankFoodbankId" name="foodbank_id">
                
                <!-- Food Basic Information Section -->
                <div class="form-section">
                    <h3 class="form-section-title">Food Basic Information</h3>
                    
                    <div class="form-group">
                        <label for="foodbankDonateItemName">Item Name *</label>
                        <input type="text" id="foodbankDonateItemName" name="item_name" class="form-input" placeholder="Enter item name" required>
                    </div>

                    <div class="form-row-two">
                        <div class="form-group">
                            <label for="foodbankDonateCategory">Category *</label>
                            <select id="foodbankDonateCategory" name="category" class="form-input" required>
                                <option value="">Select Category</option>
                                <option value="fruits-vegetables">Fruits & Vegetables</option>
                                <option value="baked-goods">Baked Goods</option>
                                <option value="cooked-meals">Cooked Meals</option>
                                <option value="packaged-goods">Packaged Goods</option>
                                <option value="beverages">Beverages</option>
                                <option value="dairy">Dairy</option>
                                <option value="meat-seafood">Meat & Seafood</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="foodbankDonateQuantity">Quantity *</label>
                            <div class="quantity-controls">
                                <button type="button" class="quantity-btn" id="foodbankDonateDecrementBtn">−</button>
                                <input type="number" id="foodbankDonateQuantity" name="quantity" value="1" min="1" class="quantity-input" required>
                                <button type="button" class="quantity-btn" id="foodbankDonateIncrementBtn">+</button>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="foodbankDonateUnit">Unit</label>
                        <select id="foodbankDonateUnit" name="unit" class="form-input">
                            <option value="pcs">Pieces (pcs)</option>
                            <option value="kg">Kilograms (kg)</option>
                            <option value="g">Grams (g)</option>
                            <option value="lbs">Pounds (lbs)</option>
                            <option value="oz">Ounces (oz)</option>
                            <option value="l">Liters (l)</option>
                            <option value="ml">Milliliters (ml)</option>
                            <option value="boxes">Boxes</option>
                            <option value="packages">Packages</option>
                            <option value="containers">Containers</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="foodbankDonateDescription">Description</label>
                        <textarea id="foodbankDonateDescription" name="description" class="form-input" rows="3" placeholder="Enter item description (optional)"></textarea>
                    </div>
                </div>

                <!-- Pickup Location Section -->
                <div class="form-section">
                    <h3 class="form-section-title">Pickup Location</h3>
                    
                    <div class="form-group">
                        <label for="foodbankDonateEstablishmentAddress">Establishment Address</label>
                        <input type="text" id="foodbankDonateEstablishmentAddress" class="form-input" value="{{ $establishment->address ?? 'Address not provided' }}" data-default-value="{{ $establishment->address ?? 'Address not provided' }}" readonly style="background-color: #f5f5f5; cursor: not-allowed;">
                    </div>
                </div>

                <!-- Pickup Notes Section -->
                <div class="form-section">
                    <h3 class="form-section-title">Pickup Notes</h3>
                    
                    <div class="form-group">
                        <label for="foodbankDonateNotes">Pickup Notes</label>
                        <textarea id="foodbankDonateNotes" name="establishment_notes" class="form-input" rows="3" placeholder="Additional notes for pickup (optional)"></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="cancelRequestDonateFoodbank">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitRequestDonateFoodbank">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    window.donationRequests = @json($donationRequests ?? []);
    window.foodbanks = @json($foodbanks ?? []);
</script>
<script src="{{ asset('js/donation-hub.js') }}"></script>
@endsection
