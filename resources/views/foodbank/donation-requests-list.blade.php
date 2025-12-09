@php
use Illuminate\Support\Facades\Storage;
@endphp

@extends('layouts.foodbank')

@section('title', 'Donation Requests List | SavEats')

@section('header', 'Donation Requests List')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/donation-request.css') }}">
@endsection

@section('content')
@if(!($isVerified ?? true))
<div style="display: flex; align-items: center; justify-content: center; min-height: 60vh; padding: 40px;">
    <div style="text-align: center; font-size: 18px; color: #856404; background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 30px 40px; max-width: 600px;">
        Your account is not verified. Please wait for admin approval.
    </div>
</div>
@else
<div class="donation-request-page">
    <!-- Donation Requests Section -->
    <div class="requests-section">
        <!-- Publish Button -->
        <button class="publish-btn" id="publishBtn" @if(!($isVerified ?? true)) disabled style="opacity: 0.5; cursor: not-allowed;" @endif>
            <svg viewBox="0 0 24 24">
                <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
            </svg>
            Publish Request
        </button>
        <div class="section-header">
            <h3 class="section-title">Donation Requests</h3>
        </div>

        <!-- Search Bar -->
        <div class="search-container">
            <input 
                type="text" 
                class="search-input" 
                id="searchInput" 
                placeholder="Search requests..."
                aria-label="Search donation requests"
            >
            <svg class="search-icon" viewBox="0 0 24 24">
                <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
            </svg>
        </div>

        <!-- Desktop Table View -->
        <table class="requests-table" id="requestsTable">
            <thead>
                <tr>
                    <th>Food Type</th>
                    <th>Quantity</th>
                    <th>Listing Matches</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <!-- Table rows will be dynamically inserted here -->
            </tbody>
        </table>

        <!-- Mobile Cards View -->
        <div class="mobile-cards" id="mobileCards">
            <!-- Mobile cards will be dynamically inserted here -->
        </div>

        <!-- Pagination -->
        <div class="pagination" id="pagination">
            <!-- Pagination will be dynamically inserted here -->
        </div>
    </div>
</div>

<!-- Publish Request Modal -->
<div class="modal-overlay" id="publishModal">
    <div class="modal modal-large">
        <div class="modal-header">
            <h2>Create Donation Request</h2>
            <button class="modal-close" id="closePublishModal" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="publishForm">
                <!-- Food Basic Information -->
                <div class="form-section">
                    <h3 class="form-section-title">Food Basic Information</h3>
                    
                    <div class="form-row-quantity">
                        <div class="form-group form-group-flex">
                            <input type="text" id="itemName" name="itemName" placeholder="Enter Item Name" required>
                        </div>
                        
                        <div class="quantity-controls">
                            <button type="button" class="quantity-btn" id="decrementBtn">−</button>
                            <input type="text" id="quantity" name="quantity" value="1" class="quantity-input">
                            <button type="button" class="quantity-btn" id="incrementBtn">+</button>
                        </div>
                    </div>

                    <div class="form-row-two">
                        <div class="form-group">
                            <select id="category" name="category" required>
                                <option value="">Select Item Category</option>
                                <option value="fresh-produce">Fresh Produce</option>
                                <option value="canned-goods">Canned Goods</option>
                                <option value="dairy">Dairy Products</option>
                                <option value="grains">Grains & Cereals</option>
                                <option value="protein">Protein (Meat/Fish)</option>
                                <option value="prepared">Prepared Meals</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <textarea id="description" name="description" placeholder="Write description" rows="4"></textarea>
                    </div>
                </div>

                <!-- Contact Details -->
                <div class="form-section">
                    <h3 class="form-section-title">Contact Details</h3>
                    
                    <div class="form-group">
                        <div class="input-wrapper">
                            <input type="text" id="contactName" name="contactName" class="icon-input" placeholder="Enter Name of Contact Person" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="phone-input-wrapper">
                            <div class="input-wrapper" style="flex: 1;">
                                <input type="tel" id="phoneNumber" name="phoneNumber" class="icon-input" placeholder="09123456789" pattern="0\d{10}" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-wrapper">
                            <input type="email" id="email" name="email" class="icon-input" placeholder="Enter Email Address" required>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="cancelPublish">Cancel</button>
            <button class="btn btn-primary" id="submitPublish">Publish Now</button>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal-overlay" id="previewModal">
    <div class="modal modal-preview">
        <div class="preview-container">
            <h1 class="preview-title">PREVIEW</h1>
            
            <!-- Foodbank Header -->
            <div class="preview-foodbank-header">
                <div class="foodbank-logo">
                    @php
                        $profileImage = $user->profile_image ?? session('user_profile_picture');
                    @endphp
                    @if($profileImage && Storage::disk('public')->exists($profileImage))
                        <img src="{{ Storage::url($profileImage) }}" alt="{{ $user->organization_name ?? 'Food Bank' }} Logo" class="logo-image">
                    @else
                        <div class="logo-text">
                            <span class="logo-text-top">{{ strtoupper(explode(' ', $user->organization_name ?? session('user_name', 'Food Bank'))[0] ?? 'CEBU') }}</span>
                            <span class="logo-text-bottom">FOOD BANK</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Preview Content -->
            <div class="preview-content">
                <!-- Item Details -->
                <div class="preview-section">
                    <div class="preview-row">
                        <span class="preview-label">Item Name</span>
                        <span class="preview-value" id="previewItemName">-</span>
                    </div>
                    <div class="preview-row">
                        <span class="preview-label">Quantity</span>
                        <span class="preview-value" id="previewQuantity">-</span>
                    </div>
                    <div class="preview-row">
                        <span class="preview-label">Item Category</span>
                        <span class="preview-value" id="previewCategory">-</span>
                    </div>
                    <div class="preview-row">
                        <span class="preview-label">Description</span>
                        <span class="preview-value" id="previewDescription">-</span>
                    </div>
                </div>

                <!-- Pickup Information -->
                <div class="preview-section">
                    <div class="preview-row">
                        <span class="preview-label">Pickup Method</span>
                        <span class="preview-value" id="previewDeliveryMethod">Pickup Only</span>
                    </div>
                    <div class="preview-row">
                        <span class="preview-label">Pickup Location</span>
                        <span class="preview-value" id="previewPickupLocation">Establishment's Registered Address</span>
                    </div>
                </div>

                <!-- Contact Details -->
                <div class="preview-section">
                    <div class="preview-row">
                        <span class="preview-label">Email Address</span>
                        <span class="preview-value" id="previewEmail">-</span>
                    </div>
                    <div class="preview-row">
                        <span class="preview-label">Name of Contact Person</span>
                        <span class="preview-value" id="previewContactName">-</span>
                    </div>
                    <div class="preview-row">
                        <span class="preview-label">Phone Number</span>
                        <span class="preview-value" id="previewPhoneNumber">-</span>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="preview-actions">
                <button class="btn btn-cancel" id="cancelPreview">Cancel</button>
                <button class="btn btn-confirm" id="confirmPreview">Confirm</button>
            </div>
        </div>
    </div>
</div>

<!-- View Details Modal -->
<div class="modal-overlay" id="viewDetailsModal">
    <div class="modal modal-large">
        <div class="modal-header">
            <h2>Donation Request Details</h2>
            <button class="modal-close" id="closeViewDetailsModal" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body">
            <div id="viewDetailsLoading" style="text-align: center; padding: 20px;">
                <p>Loading details...</p>
            </div>
            <div id="viewDetailsContent" style="display: none;">
                <!-- Content will be populated by JavaScript -->
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="closeViewDetailsBtn">Close</button>
        </div>
    </div>
</div>

<!-- Edit Request Modal -->
<div class="modal-overlay" id="editModal">
    <div class="modal modal-large">
        <div class="modal-header">
            <h2>Edit Donation Request</h2>
            <button class="modal-close" id="closeEditModal" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editForm">
                <input type="hidden" id="editRequestId">
                
                <!-- Food Basic Information -->
                <div class="form-section">
                    <h3 class="form-section-title">Food Basic Information</h3>
                    
                    <div class="form-row-quantity">
                        <div class="form-group form-group-flex">
                            <input type="text" id="editItemName" name="editItemName" placeholder="Enter Item Name" required>
                        </div>
                        
                        <div class="quantity-controls">
                            <button type="button" class="quantity-btn" id="editDecrementBtn">−</button>
                            <input type="text" id="editQuantity" name="editQuantity" value="1" class="quantity-input">
                            <button type="button" class="quantity-btn" id="editIncrementBtn">+</button>
                        </div>
                    </div>

                    <div class="form-row-two">
                        <div class="form-group">
                            <select id="editCategory" name="editCategory" required>
                                <option value="">Select Item Category</option>
                                <option value="fresh-produce">Fresh Produce</option>
                                <option value="canned-goods">Canned Goods</option>
                                <option value="dairy">Dairy Products</option>
                                <option value="grains">Grains & Cereals</option>
                                <option value="protein">Protein (Meat/Fish)</option>
                                <option value="prepared">Prepared Meals</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <textarea id="editDescription" name="editDescription" placeholder="Write description" rows="4"></textarea>
                    </div>
                </div>

                <!-- Contact Details -->
                <div class="form-section">
                    <h3 class="form-section-title">Contact Details</h3>
                    
                    <div class="form-group">
                        <div class="input-wrapper">
                            <input type="text" id="editContactName" name="editContactName" class="icon-input" placeholder="Enter Name of Contact Person" required>
                            <span class="input-icon">👤</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="phone-input-wrapper">
                            <div class="input-wrapper" style="flex: 1;">
                                <input type="tel" id="editPhoneNumber" name="editPhoneNumber" class="icon-input" placeholder="09123456789" pattern="0\d{10}" required>
                                <span class="input-icon">📞</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-wrapper">
                            <input type="email" id="editEmail" name="editEmail" class="icon-input" placeholder="Enter Email Address" required>
                            <span class="input-icon">✉️</span>
                        </div>
                    </div>
                </div>

                <!-- Status -->
                <div class="form-section">
                    <h3 class="form-section-title">Status</h3>
                    <div class="form-group">
                        <select id="editStatus">
                            <option value="pending">Pending</option>
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                            <option value="expired">Expired</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="cancelEdit">Cancel</button>
            <button class="btn btn-primary" id="submitEdit">
                <span id="editSubmitText">Save Changes</span>
                <span id="editSubmitLoading" style="display: none;">Saving...</span>
            </button>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
    // Foodbank's own donation requests (for the requests-section table)
    window.donationRequests = @json($foodbankDonationRequests ?? []);
</script>
<script src="{{ asset('js/donation-request.js') }}"></script>
@endsection

