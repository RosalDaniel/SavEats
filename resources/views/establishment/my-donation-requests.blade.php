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

    // View donation request details (placeholder - implement as needed)
    window.viewDonationRequestDetails = function(requestId) {
        // TODO: Implement modal or page to view full donation request details
        alert('View details for request: ' + requestId);
    };
</script>
@endsection

