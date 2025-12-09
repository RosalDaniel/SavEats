@extends('layouts.establishment')

@section('title', 'My Donation Requests | SavEats')

@section('header', 'My Donation Requests')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/donation-request.css') }}">
<link rel="stylesheet" href="{{ asset('css/order-management.css') }}">
@endsection

@section('content')
<div class="donation-request-page">
    <!-- My Donation Requests Section -->
    <div class="orders-container">
        <div class="section-header">
            <h3 class="section-title">My Donation Requests</h3>
        </div>
        
        <!-- Tabs -->
        <div class="order-tabs">
            <button class="tab-button active" data-tab="pending">
                Pending <span class="tab-count">({{ count($pendingRequests ?? []) }})</span>
            </button>
            <button class="tab-button" data-tab="accepted">
                Accepted <span class="tab-count">({{ count($acceptedRequests ?? []) }})</span>
            </button>
            <button class="tab-button" data-tab="declined">
                Declined <span class="tab-count">({{ count($declinedRequests ?? []) }})</span>
            </button>
            <button class="tab-button" data-tab="completed">
                Completed <span class="tab-count">({{ count($completedRequests ?? []) }})</span>
            </button>
        </div>

        <!-- Pending Tab Content -->
        <div class="tab-content active" id="pending-tab">
            <div class="orders-list">
            @forelse($pendingRequests ?? [] as $request)
                <div class="order-card" data-id="{{ $request['id'] }}">
                    <div class="order-header">
                        <div class="product-info">
                            <h3 class="product-name">{{ $request['item_name'] }}</h3>
                            <p class="product-quantity">{{ $request['quantity'] }} {{ $request['unit'] }}</p>
                        </div>
                        <div class="order-price status-pending">Pending Review</div>
                    </div>
                    
                    <div class="order-details">
                        <div class="detail-row">
                            <span class="detail-label">Category:</span>
                            <span class="detail-value">{{ ucfirst($request['category']) }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Method:</span>
                            <span class="detail-value">{{ $request['pickup_method_display'] }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Scheduled Date:</span>
                            <span class="detail-value">{{ $request['scheduled_date_display'] }}</span>
                        </div>
                        @if($request['scheduled_time_display'] !== 'N/A')
                        <div class="detail-row">
                            <span class="detail-label">Scheduled Time:</span>
                            <span class="detail-value">{{ $request['scheduled_time_display'] }}</span>
                        </div>
                        @endif
                        <div class="detail-row">
                            <span class="detail-label">Foodbank:</span>
                            <span class="detail-value">{{ $request['foodbank_name'] }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Submitted:</span>
                            <span class="detail-value">{{ $request['created_at_display'] }}</span>
                        </div>
                    </div>
                    
                    <div class="order-actions">
                        <button class="btn-view" onclick="viewDonationRequestDetails('{{ $request['id'] }}')">View Details</button>
                    </div>
                </div>
                @empty
                <div class="empty-state">
                    <h3>No Pending Donation Requests</h3>
                    <p>You don't have any pending donation requests at this time.</p>
                </div>
                @endforelse
            </div>
        </div>
        
        <!-- Accepted Tab Content -->
        <div class="tab-content" id="accepted-tab" style="display: none;">
            <div class="orders-list">
                @forelse($acceptedRequests ?? [] as $request)
                <div class="order-card" data-id="{{ $request['id'] }}">
                    <div class="order-header">
                        <div class="product-info">
                            <h3 class="product-name">{{ $request['item_name'] }}</h3>
                            <p class="product-quantity">{{ $request['quantity'] }} {{ $request['unit'] }}</p>
                        </div>
                        <div class="order-price status-accepted">Accepted</div>
                    </div>
                    
                    <div class="order-details">
                        <div class="detail-row">
                            <span class="detail-label">Category:</span>
                            <span class="detail-value">{{ ucfirst($request['category']) }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Method:</span>
                            <span class="detail-value">{{ $request['pickup_method_display'] }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Scheduled Date:</span>
                            <span class="detail-value">{{ $request['scheduled_date_display'] }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Foodbank:</span>
                            <span class="detail-value">{{ $request['foodbank_name'] }}</span>
                        </div>
                        @if($request['accepted_at_display'])
                        <div class="detail-row">
                            <span class="detail-label">Accepted:</span>
                            <span class="detail-value">{{ $request['accepted_at_display'] }}</span>
                        </div>
                        @endif
                    </div>
                    
                    <div class="order-actions">
                        <button class="btn-view" onclick="viewDonationRequestDetails('{{ $request['id'] }}')">View Details</button>
                    </div>
                </div>
                @empty
                <div class="empty-state">
                    <h3>No Accepted Donation Requests</h3>
                    <p>You don't have any accepted donation requests at this time.</p>
                </div>
                @endforelse
            </div>
        </div>
        
        <!-- Declined Tab Content -->
        <div class="tab-content" id="declined-tab" style="display: none;">
            <div class="orders-list">
                @forelse($declinedRequests ?? [] as $request)
                <div class="order-card" data-id="{{ $request['id'] }}">
                    <div class="order-header">
                        <div class="product-info">
                            <h3 class="product-name">{{ $request['item_name'] }}</h3>
                            <p class="product-quantity">{{ $request['quantity'] }} {{ $request['unit'] }}</p>
                        </div>
                        <div class="order-price status-cancelled">Declined</div>
                    </div>
                    
                    <div class="order-details">
                        <div class="detail-row">
                            <span class="detail-label">Category:</span>
                            <span class="detail-value">{{ ucfirst($request['category']) }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Foodbank:</span>
                            <span class="detail-value">{{ $request['foodbank_name'] }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Submitted:</span>
                            <span class="detail-value">{{ $request['created_at_display'] }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Declined:</span>
                            <span class="detail-value">{{ $request['updated_at_display'] }}</span>
                        </div>
                    </div>
                    
                    <div class="order-actions">
                        <button class="btn-view" onclick="viewDonationRequestDetails('{{ $request['id'] }}')">View Details</button>
                    </div>
                </div>
                @empty
                <div class="empty-state">
                    <h3>No Declined Donation Requests</h3>
                    <p>You don't have any declined donation requests.</p>
                </div>
                @endforelse
            </div>
        </div>
        
        <!-- Completed Tab Content -->
        <div class="tab-content" id="completed-tab" style="display: none;">
            <div class="orders-list">
            @forelse($completedRequests ?? [] as $request)
                <div class="order-card" data-id="{{ $request['id'] }}">
                    <div class="order-header">
                        <div class="product-info">
                            <h3 class="product-name">{{ $request['item_name'] }}</h3>
                            <p class="product-quantity">{{ $request['quantity'] }} {{ $request['unit'] }}</p>
                        </div>
                        <div class="order-price status-completed">Completed</div>
                    </div>
                    
                    <div class="order-details">
                        <div class="detail-row">
                            <span class="detail-label">Category:</span>
                            <span class="detail-value">{{ ucfirst($request['category']) }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Foodbank:</span>
                            <span class="detail-value">{{ $request['foodbank_name'] }}</span>
                        </div>
                        @if($request['donation_number'])
                        <div class="detail-row">
                            <span class="detail-label">Donation Number:</span>
                            <span class="detail-value">{{ $request['donation_number'] }}</span>
                        </div>
                        @endif
                        <div class="detail-row">
                            <span class="detail-label">Completed:</span>
                            <span class="detail-value">{{ $request['fulfilled_at_display'] }}</span>
                        </div>
                    </div>
                    
                    <div class="order-actions">
                        <button class="btn-view" onclick="viewDonationRequestDetails('{{ $request['id'] }}')">View Details</button>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <h3>No Completed Donation Requests</h3>
                    <p>You don't have any completed donation requests.</p>
                </div>
            @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Donation Request Details Modal -->
<div class="modal-overlay" id="donationRequestDetailsModal" style="display: none;">
    <div class="modal modal-large">
        <div class="modal-header">
            <h2>Donation Request Details</h2>
            <button class="modal-close" id="closeDonationRequestDetailsModal" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body">
            <div id="donationRequestDetailsLoading" style="text-align: center; padding: 20px;">
                <p>Loading details...</p>
            </div>
            <div id="donationRequestDetailsContent" style="display: none;">
                <!-- Content will be populated by JavaScript -->
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="closeDonationRequestDetailsBtn">Close</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Tab functionality
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
    });

    // View donation request details
    window.viewDonationRequestDetails = function(requestId) {
        const modal = document.getElementById('donationRequestDetailsModal');
        const loading = document.getElementById('donationRequestDetailsLoading');
        const content = document.getElementById('donationRequestDetailsContent');
        
        if (!modal || !loading || !content) {
            console.error('Modal elements not found');
            return;
        }
        
        // Show modal and loading state
        modal.style.display = 'flex';
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        loading.style.display = 'block';
        content.style.display = 'none';
        
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        // Fetch donation request details
        fetch(`/establishment/donation-request/${requestId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
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
                populateDonationRequestDetails(data.data);
                loading.style.display = 'none';
                content.style.display = 'block';
            } else {
                throw new Error(data.message || 'Failed to load donation request details');
            }
        })
        .catch(error => {
            console.error('Error fetching donation request details:', error);
            loading.innerHTML = `<p style="color: red;">Error: ${error.message}</p>`;
        });
    };
    
    // Populate donation request details in modal
    function populateDonationRequestDetails(request) {
        const content = document.getElementById('donationRequestDetailsContent');
        if (!content) return;
        
        // Helper function to escape HTML
        const escapeHtml = (text) => {
            if (!text || text === 'N/A') return 'N/A';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        };
        
        // Helper function to format status
        const formatStatus = (status) => {
            const statusMap = {
                'pending': 'Pending Review',
                'pending_confirmation': 'Pending Confirmation',
                'accepted': 'Accepted',
                'declined': 'Declined',
                'completed': 'Completed'
            };
            return statusMap[status?.toLowerCase()] || status?.toUpperCase() || 'Unknown';
        };
        
        // Helper function to get status class
        const getStatusClass = (status) => {
            const statusMap = {
                'pending': 'status-pending',
                'pending_confirmation': 'status-pending',
                'accepted': 'status-accepted',
                'declined': 'status-cancelled',
                'completed': 'status-completed'
            };
            return statusMap[status?.toLowerCase()] || 'status-pending';
        };
        
        let html = '<div class="donation-detail-content">';
        
        // Request Information Section
        html += '<div class="detail-section">';
        html += '<h3>Request Information</h3>';
        html += '<div class="detail-grid">';
        html += `<div class="detail-item"><div class="detail-label">Request ID</div><div class="detail-value">#${escapeHtml(request.id || request.donation_request_id || 'N/A')}</div></div>`;
        html += `<div class="detail-item"><div class="detail-label">Status</div><div class="detail-value"><span class="status-badge ${getStatusClass(request.status)}">${formatStatus(request.status)}</span></div></div>`;
        html += `<div class="detail-item"><div class="detail-label">Submitted</div><div class="detail-value">${escapeHtml(request.created_at_display || request.created_at || 'N/A')}</div></div>`;
        if (request.accepted_at_display) {
            html += `<div class="detail-item"><div class="detail-label">Accepted</div><div class="detail-value">${escapeHtml(request.accepted_at_display)}</div></div>`;
        }
        if (request.fulfilled_at_display) {
            html += `<div class="detail-item"><div class="detail-label">Completed</div><div class="detail-value">${escapeHtml(request.fulfilled_at_display)}</div></div>`;
        }
        if (request.updated_at_display && request.status === 'declined') {
            html += `<div class="detail-item"><div class="detail-label">Declined</div><div class="detail-value">${escapeHtml(request.updated_at_display)}</div></div>`;
        }
        html += '</div>';
        html += '</div>';
        
        // Item Details Section
        html += '<div class="detail-section">';
        html += '<h3>Item Details</h3>';
        html += '<div class="detail-grid">';
        html += `<div class="detail-item"><div class="detail-label">Item Name</div><div class="detail-value">${escapeHtml(request.item_name || 'N/A')}</div></div>`;
        html += `<div class="detail-item"><div class="detail-label">Category</div><div class="detail-value">${escapeHtml(request.category ? request.category.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'N/A')}</div></div>`;
        html += `<div class="detail-item"><div class="detail-label">Quantity</div><div class="detail-value">${escapeHtml(request.quantity || 'N/A')} ${escapeHtml(request.unit || 'pcs')}</div></div>`;
        if (request.description) {
            html += `<div class="detail-item full-width"><div class="detail-label">Description</div><div class="detail-value">${escapeHtml(request.description)}</div></div>`;
        }
        html += '</div>';
        html += '</div>';
        
        // Foodbank Information Section
        html += '<div class="detail-section">';
        html += '<h3>Foodbank Information</h3>';
        html += '<div class="detail-grid">';
        html += `<div class="detail-item"><div class="detail-label">Foodbank Name</div><div class="detail-value">${escapeHtml(request.foodbank_name || 'N/A')}</div></div>`;
        if (request.foodbank_email) {
            html += `<div class="detail-item"><div class="detail-label">Email</div><div class="detail-value">${escapeHtml(request.foodbank_email)}</div></div>`;
        }
        if (request.foodbank_phone) {
            html += `<div class="detail-item"><div class="detail-label">Phone</div><div class="detail-value">${escapeHtml(request.foodbank_phone)}</div></div>`;
        }
        html += '</div>';
        html += '</div>';
        
        // Pickup Information Section
        html += '<div class="detail-section">';
        html += '<h3>Pickup Information</h3>';
        html += '<div class="detail-grid">';
        html += `<div class="detail-item"><div class="detail-label">Pickup Method</div><div class="detail-value">${escapeHtml(request.pickup_method_display || 'Pickup')}</div></div>`;
        html += `<div class="detail-item"><div class="detail-label">Pickup Location</div><div class="detail-value">${escapeHtml(request.address || 'Establishment Address')}</div></div>`;
        if (request.scheduled_date_display && request.scheduled_date_display !== 'N/A') {
            html += `<div class="detail-item"><div class="detail-label">Scheduled Date</div><div class="detail-value">${escapeHtml(request.scheduled_date_display)}</div></div>`;
        }
        if (request.scheduled_time_display && request.scheduled_time_display !== 'N/A') {
            html += `<div class="detail-item"><div class="detail-label">Scheduled Time</div><div class="detail-value">${escapeHtml(request.scheduled_time_display)}</div></div>`;
        }
        html += '</div>';
        html += '</div>';
        
        // Establishment Notes Section
        if (request.establishment_notes) {
            html += '<div class="detail-section">';
            html += '<h3>Your Notes</h3>';
            html += `<div class="note-item"><p class="note-content">${escapeHtml(request.establishment_notes)}</p></div>`;
            html += '</div>';
        }
        
        // Donation Information (for completed requests)
        if (request.donation_number) {
            html += '<div class="detail-section">';
            html += '<h3>Donation Information</h3>';
            html += '<div class="detail-grid">';
            html += `<div class="detail-item"><div class="detail-label">Donation Number</div><div class="detail-value">${escapeHtml(request.donation_number)}</div></div>`;
            html += '</div>';
            html += '</div>';
        }
        
        html += '</div>';
        
        content.innerHTML = html;
    }
    
    // Modal close handlers
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('donationRequestDetailsModal');
        const closeBtn = document.getElementById('closeDonationRequestDetailsModal');
        const closeBtn2 = document.getElementById('closeDonationRequestDetailsBtn');
        
        function closeModal() {
            if (modal) {
                modal.classList.remove('show');
                modal.style.display = 'none';
                document.body.style.overflow = '';
                // Reset loading and content states
                const loading = document.getElementById('donationRequestDetailsLoading');
                const content = document.getElementById('donationRequestDetailsContent');
                if (loading) loading.style.display = 'block';
                if (content) {
                    content.style.display = 'none';
                    content.innerHTML = '';
                }
            }
        }
        
        if (closeBtn) {
            closeBtn.addEventListener('click', closeModal);
        }
        
        if (closeBtn2) {
            closeBtn2.addEventListener('click', closeModal);
        }
        
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeModal();
                }
            });
        }
        
        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal && modal.classList.contains('show')) {
                closeModal();
            }
        });
    });
</script>
@endsection

