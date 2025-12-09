@extends('layouts.foodbank')

@section('title', 'Donation Requests | SavEats')

@section('header', 'Donation Requests')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/donation-request.css') }}">
<link rel="stylesheet" href="{{ asset('css/order-management.css') }}">
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
                            <h3 class="product-name">{{ $request['item_name'] ?? 'N/A' }}</h3>
                            <p class="product-quantity">{{ $request['quantity'] ?? 0 }} {{ $request['unit'] ?? 'pcs' }}</p>
                        </div>
                        <div class="order-price status-pending">
                            {{ $request['status'] === 'pending_confirmation' ? 'Pending Confirmation' : 'Pending' }}
                        </div>
                    </div>
                    
                    <div class="order-details">
                        <div class="detail-row">
                            <span class="detail-label">Category:</span>
                            <span class="detail-value">{{ ucfirst($request['category'] ?? 'other') }}</span>
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
                            <span class="detail-value">{{ $request['establishment_name'] ?? 'Unknown Establishment' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Requested:</span>
                            <span class="detail-value">{{ $request['created_at_display'] }}</span>
                        </div>
                    </div>
                    
                    <div class="order-actions">
                        <button class="btn-view" onclick="viewDonationRequestDetails('{{ $request['id'] }}')">View Details</button>
                        @if(in_array($request['status'], ['pending', 'pending_confirmation']))
                        <button class="btn-accept" onclick="acceptDonationRequest('{{ $request['id'] }}')" @if(!($isVerified ?? true)) disabled style="opacity: 0.5; cursor: not-allowed;" title="Your account is not verified. Please wait for admin approval." @endif>Accept</button>
                        <button class="btn-cancel" onclick="declineDonationRequest('{{ $request['id'] }}')" @if(!($isVerified ?? true)) disabled style="opacity: 0.5; cursor: not-allowed;" title="Your account is not verified. Please wait for admin approval." @endif>Decline</button>
                        @endif
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
                            <h3 class="product-name">{{ $request['item_name'] ?? 'N/A' }}</h3>
                            <p class="product-quantity">{{ $request['quantity'] ?? 0 }} {{ $request['unit'] ?? 'pcs' }}</p>
                        </div>
                        <div class="order-price status-accepted">Accepted</div>
                    </div>
                    
                    <div class="order-details">
                        <div class="detail-row">
                            <span class="detail-label">Category:</span>
                            <span class="detail-value">{{ ucfirst($request['category'] ?? 'other') }}</span>
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
                            <span class="detail-value">{{ $request['establishment_name'] ?? 'Unknown Establishment' }}</span>
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
                        <button class="btn-accept" onclick="confirmPickup('{{ $request['id'] }}')" @if(!($isVerified ?? true)) disabled style="opacity: 0.5; cursor: not-allowed;" title="Your account is not verified. Please wait for admin approval." @endif>Mark as Completed</button>
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
                            <h3 class="product-name">{{ $request['item_name'] ?? 'N/A' }}</h3>
                            <p class="product-quantity">{{ $request['quantity'] ?? 0 }} {{ $request['unit'] ?? 'pcs' }}</p>
                        </div>
                        <div class="order-price status-cancelled">Declined</div>
                    </div>
                    
                    <div class="order-details">
                        <div class="detail-row">
                            <span class="detail-label">Category:</span>
                            <span class="detail-value">{{ ucfirst($request['category'] ?? 'other') }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">From:</span>
                            <span class="detail-value">{{ $request['establishment_name'] ?? 'Unknown Establishment' }}</span>
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
                            <h3 class="product-name">{{ $request['item_name'] ?? 'N/A' }}</h3>
                            <p class="product-quantity">{{ $request['quantity'] ?? 0 }} {{ $request['unit'] ?? 'pcs' }}</p>
                        </div>
                        <div class="order-price status-completed">Completed</div>
                    </div>
                    
                    <div class="order-details">
                        <div class="detail-row">
                            <span class="detail-label">Category:</span>
                            <span class="detail-value">{{ ucfirst($request['category'] ?? 'other') }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">From:</span>
                            <span class="detail-value">{{ $request['establishment_name'] ?? 'Unknown Establishment' }}</span>
                        </div>
                        @if($request['donation_number'])
                        <div class="detail-row">
                            <span class="detail-label">Donation Number:</span>
                            <span class="detail-value">{{ $request['donation_number'] }}</span>
                        </div>
                        @endif
                        <div class="detail-row">
                            <span class="detail-label">Completed:</span>
                            <span class="detail-value">{{ $request['fulfilled_at_display'] ?? 'N/A' }}</span>
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
<div class="modal-overlay" id="donationRequestDetailsModal">
    <div class="modal modal-donation-details">
        <div class="modal-header">
            <h2 id="modalRequestTitle">Donation Request Details</h2>
            <button class="modal-close" id="closeDonationRequestModal" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body" id="donationRequestModalBody">
            <div id="donationRequestLoading" style="text-align: center; padding: 40px;">
                <p>Loading details...</p>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="closeDonationRequestModalBtn">Close</button>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
    // Pass data to JavaScript
    window.incomingRequests = @json($incomingRequests ?? []);
    window.acceptedRequests = @json($acceptedRequests ?? []);
    window.declinedRequests = @json($declinedRequests ?? []);
    window.completedRequests = @json($completedRequests ?? []);
</script>
<script src="{{ asset('js/donation-request.js') }}"></script>
<script src="{{ asset('js/foodbank-donation-requests.js') }}"></script>
@endsection

