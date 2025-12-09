@extends('layouts.admin')

@section('title', 'Deletion Requests - Admin Dashboard')

@section('header', 'Deletion Requests')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin-users.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin-deletion-requests.css') }}">
@endsection

@section('content')
<div class="deletion-requests-page">
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon users">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Total Requests</h3>
                <p class="stat-number">{{ number_format($stats['total'] ?? 0) }}</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon active">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Pending</h3>
                <p class="stat-number">{{ number_format($stats['pending'] ?? 0) }}</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon suspended">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Approved</h3>
                <p class="stat-number">{{ number_format($stats['approved'] ?? 0) }}</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon suspended">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Rejected</h3>
                <p class="stat-number">{{ number_format($stats['rejected'] ?? 0) }}</p>
            </div>
        </div>
    </div>

    <!-- Combined Filters and Requests Table Section -->
    <div class="combined-section">
        <div class="filters-header">
            <h2>Filters</h2>
            <a href="{{ route('admin.deletion-requests') }}" class="btn-clear-filters">Clear All</a>
        </div>
        <form method="GET" action="{{ route('admin.deletion-requests') }}" class="filters-form">
            <div class="filters-grid">
                <div class="filter-group">
                    <label for="search">Search</label>
                    <input type="text" id="search" name="search" class="filter-input" 
                           placeholder="Search by reason or notes..." 
                           value="{{ $searchQuery ?? '' }}">
                </div>
                
                <div class="filter-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="filter-select">
                        <option value="all" {{ ($statusFilter ?? 'all') === 'all' ? 'selected' : '' }}>All Status</option>
                        <option value="pending" {{ ($statusFilter ?? 'all') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ ($statusFilter ?? 'all') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ ($statusFilter ?? 'all') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="role">Role</label>
                    <select id="role" name="role" class="filter-select">
                        <option value="all" {{ ($roleFilter ?? 'all') === 'all' ? 'selected' : '' }}>All Roles</option>
                        <option value="consumer" {{ ($roleFilter ?? 'all') === 'consumer' ? 'selected' : '' }}>Consumer</option>
                        <option value="establishment" {{ ($roleFilter ?? 'all') === 'establishment' ? 'selected' : '' }}>Establishment</option>
                        <option value="foodbank" {{ ($roleFilter ?? 'all') === 'foodbank' ? 'selected' : '' }}>Foodbank</option>
                    </select>
                </div>
            </div>
        </form>

        <div class="table-header">
            <h2>Deletion Requests ({{ count($requests ?? []) }})</h2>
        </div>
        
        <div class="table-container">
            <table class="users-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Reason</th>
                    <th>Submitted</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="requestsTableBody">
                @forelse($requests ?? [] as $request)
                <tr data-request-id="{{ $request['id'] }}" data-status="{{ $request['status'] }}">
                    <td>
                        <div class="user-info">
                            <div class="user-details">
                                <div class="user-name">{{ $request['user_name'] }}</div>
                                <div class="user-username">{{ $request['user_email'] }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="role-badge {{ $request['user_type'] }}">{{ ucfirst($request['user_type']) }}</span>
                    </td>
                    <td>
                        <span class="reason-text" title="{{ $request['reason'] ?? 'No reason provided' }}">
                            {{ $request['reason'] ?? 'No reason provided' }}
                        </span>
                    </td>
                    <td style="white-space: nowrap;">{{ $request['created_at']->format('M d, Y | g:i A') }}</td>
                    <td>
                        <span class="status-badge {{ $request['status'] }}">{{ ucfirst($request['status']) }}</span>
                    </td>
                    <td style="white-space: nowrap;">
                        @if($request['status'] === 'pending')
                        <div class="action-buttons">
                            <button class="btn-approve" onclick="approveRequest({{ $request['id'] }})">Approve</button>
                            <button class="btn-decline" onclick="declineRequest({{ $request['id'] }})">Decline</button>
                        </div>
                        @else
                        <span style="color: #6b7280; font-size: 0.8125rem;">
                            {{ $request['approved_at'] ? 'Processed ' . $request['approved_at']->diffForHumans() : 'N/A' }}
                        </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="no-requests">No deletion requests found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div class="modal-overlay" id="approvalModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirm Account Deletion</h3>
        </div>
        <div class="modal-body">
            <p style="margin-bottom: 15px; color: #dc2626; font-weight: 600; font-size: 1rem;">
                Are you sure you want to permanently delete this account? This action cannot be undone.
            </p>
            <p style="margin-bottom: 15px; color: #6b7280; font-size: 0.875rem;">
                This will permanently delete the user account and all related data including orders, listings, reviews, donation records, logs, notifications, and all foreign-key-linked records.
            </p>
            <label for="approvalNotes" style="display: block; margin-bottom: 8px; font-weight: 600;">Admin Notes (Optional):</label>
            <textarea id="approvalNotes" placeholder="Add any notes about this approval..."></textarea>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="closeApprovalModal()">Cancel</button>
            <button class="btn-confirm" onclick="confirmApprove()" style="background: #236816;">Confirm Deletion</button>
        </div>
    </div>
</div>

<!-- Decline Modal -->
<div class="modal-overlay" id="declineModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Decline Deletion Request</h3>
        </div>
        <div class="modal-body">
            <p style="margin-bottom: 15px; font-size: 1rem; font-weight: 500;">
                Decline this deletion request?
            </p>
            <p style="margin-bottom: 15px; color: #6b7280; font-size: 0.875rem;">
                The user's account will remain active. Please provide a reason for declining this request.
            </p>
            <label for="declineNotes" style="display: block; margin-bottom: 8px; font-weight: 600;">Admin Notes:</label>
            <textarea id="declineNotes" placeholder="Reason for declining..."></textarea>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="closeDeclineModal()">Cancel</button>
            <button class="btn-confirm" onclick="confirmDecline()" style="background: #DD5D36;">Confirm Decline</button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="{{ asset('js/admin-deletion-requests.js') }}"></script>
@endsection

