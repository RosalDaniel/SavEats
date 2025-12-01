@extends('layouts.foodbank')

@section('title', 'Donation Request | SavEats')

@section('header', 'Donation Request')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/donation-request.css') }}">
<link rel="stylesheet" href="{{ asset('css/order-management.css') }}">
@endsection

@section('content')
<div class="donation-request-page">
    <!-- Incoming Donation Requests from Establishments -->
    <div class="orders-container">
        <div class="section-header">
            <h3 class="section-title">Donation Requests from Establishments</h3>
        </div>
        
        <!-- Tabs -->
        <div class="order-tabs">
            <button class="tab-button active" data-tab="incoming">
                Incoming <span class="tab-count">({{ count($incomingRequests ?? []) }})</span>
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

        <!-- Incoming Tab Content -->
        <div class="tab-content active" id="incoming-tab">
            <div class="orders-list">
            @forelse($incomingRequests ?? [] as $request)
                <div class="order-card" data-id="{{ $request['id'] }}">
                    <div class="order-header">
                        <div class="product-info">
                            <h3 class="product-name">{{ $request['item_name'] }}</h3>
                            <p class="product-quantity">{{ $request['quantity'] }} {{ $request['unit'] }}</p>
                        </div>
                        <div class="order-price status-pending">Pending</div>
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
                            <span class="detail-label">From:</span>
                            <span class="detail-value">{{ $request['establishment_name'] }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Requested:</span>
                            <span class="detail-value">{{ $request['created_at_display'] }}</span>
                        </div>
                    </div>
                    
                    <div class="order-actions">
                        <button class="btn-view" onclick="viewDonationRequestDetails('{{ $request['id'] }}')">View Details</button>
                        <button class="btn-accept" onclick="acceptDonationRequest('{{ $request['id'] }}')">Accept</button>
                        <button class="btn-cancel" onclick="declineDonationRequest('{{ $request['id'] }}')">Decline</button>
                    </div>
                </div>
                @empty
                <div class="empty-state">
                    <h3>No Incoming Donation Requests</h3>
                    <p>You don't have any incoming donation requests at this time.</p>
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
                            <span class="detail-label">From:</span>
                            <span class="detail-value">{{ $request['establishment_name'] }}</span>
                        </div>
                        @if($request['status'] === 'accepted' && $request['accepted_at_display'])
                        <div class="detail-row">
                            <span class="detail-label">Accepted:</span>
                            <span class="detail-value">{{ $request['accepted_at_display'] }}</span>
                        </div>
                        @endif
                    </div>
                    
                    <div class="order-actions">
                        <button class="btn-view" onclick="viewDonationRequestDetails('{{ $request['id'] }}')">View Details</button>
                        @if($request['pickup_method'] === 'pickup')
                            <button class="btn-accept" onclick="confirmPickup('{{ $request['id'] }}')">Confirm Pickup</button>
                        @else
                            <button class="btn-accept" onclick="confirmDelivery('{{ $request['id'] }}')">Confirm Delivery</button>
                        @endif
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
                            <span class="detail-label">From:</span>
                            <span class="detail-value">{{ $request['establishment_name'] }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Requested:</span>
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
                            <span class="detail-label">From:</span>
                            <span class="detail-value">{{ $request['establishment_name'] }}</span>
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

<!-- Establishment Donation Details Modal -->
<div class="modal-overlay" id="establishmentDonationModal">
    <div class="modal modal-donation-details">
        <div class="modal-header">
            <h2 id="modalDonationNumber">Donation Details</h2>
            <button class="modal-close" id="closeEstablishmentDonationModal" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body" id="establishmentDonationModalBody">
            <!-- Content will be populated by JavaScript -->
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="closeEstablishmentDonationModalBtn">Close</button>
            <button class="btn btn-contact" id="modalContactEstablishmentBtn">Contact Establishment</button>
            <button class="btn btn-decline" id="modalDeclineBtn">Decline</button>
            <button class="btn btn-accept" id="modalAcceptBtn">Accept</button>
        </div>
    </div>
</div>

<!-- Contact Establishment Modal -->
<div class="modal-overlay" id="contactEstablishmentModal">
    <div class="modal modal-contact-establishment">
        <div class="modal-header">
            <h2 id="contactEstablishmentModalTitle">Contact Establishment</h2>
            <button class="modal-close" id="closeContactEstablishmentModal" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body" id="contactEstablishmentModalBody">
            <!-- Content will be populated by JavaScript -->
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="closeContactEstablishmentModalBtn">Close</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Establishment-initiated donation requests (for the tabs)
    window.incomingRequests = @json($incomingRequests ?? []);
    window.acceptedRequests = @json($acceptedRequests ?? []);
    window.declinedRequests = @json($declinedRequests ?? []);
    window.completedRequests = @json($completedRequests ?? []);
</script>
<script src="{{ asset('js/donation-request.js') }}"></script>
@endsection

