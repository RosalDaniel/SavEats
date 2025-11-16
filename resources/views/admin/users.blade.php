@extends('layouts.admin')

@section('title', 'User Management - Admin Dashboard')

@section('header', 'User Management')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin-users.css') }}">
@endsection

@section('content')
<div class="users-management-page">
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon users">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M16 7c0-2.21-1.79-4-4-4S8 4.79 8 7s1.79 4 4 4 4-1.79 4-4zm-4 6c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Total Users</h3>
                <p class="stat-number">{{ number_format($stats['total'] ?? 0) }}</p>
                <div class="stat-breakdown">
                    <span>Consumers: {{ number_format($stats['consumers'] ?? 0) }}</span>
                    <span>Establishments: {{ number_format($stats['establishments'] ?? 0) }}</span>
                    <span>Food Banks: {{ number_format($stats['foodbanks'] ?? 0) }}</span>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon active">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Active Users</h3>
                <p class="stat-number">{{ number_format($stats['active'] ?? 0) }}</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon suspended">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Suspended Users</h3>
                <p class="stat-number">{{ number_format($stats['suspended'] ?? 0) }}</p>
            </div>
        </div>
    </div>

    <!-- Combined Filters and Users Table Section -->
    <div class="combined-section">
        <div class="filters-header">
            <h2>Filters</h2>
            <button class="btn-clear-filters" onclick="clearFilters()">Clear All</button>
        </div>
        <div class="filters-grid">
            <div class="filter-group">
                <label for="search">Search</label>
                <input type="text" id="search" name="search" class="filter-input" 
                       placeholder="Search by name, email, or username..." 
                       value="{{ $searchQuery ?? '' }}">
            </div>
            
            <div class="filter-group">
                <label for="role">Role</label>
                <select id="role" name="role" class="filter-select">
                    <option value="all" {{ ($roleFilter ?? 'all') === 'all' ? 'selected' : '' }}>All Roles</option>
                    <option value="consumer" {{ ($roleFilter ?? 'all') === 'consumer' ? 'selected' : '' }}>Consumers</option>
                    <option value="establishment" {{ ($roleFilter ?? 'all') === 'establishment' ? 'selected' : '' }}>Establishments</option>
                    <option value="foodbank" {{ ($roleFilter ?? 'all') === 'foodbank' ? 'selected' : '' }}>Food Banks</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="filter-select">
                    <option value="all" {{ ($statusFilter ?? 'all') === 'all' ? 'selected' : '' }}>All Status</option>
                    <option value="active" {{ ($statusFilter ?? 'all') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ ($statusFilter ?? 'all') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="deleted" {{ ($statusFilter ?? 'all') === 'deleted' ? 'selected' : '' }}>Deleted</option>
                </select>
            </div>
        </div>

        <div class="table-header">
            <h2 id="usersCountHeader">All Users ({{ count($allUsers ?? []) }})</h2>
        </div>
        
        <div class="table-container">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    @forelse($allUsers ?? [] as $user)
                    <tr data-user-id="{{ $user['id'] }}" data-role="{{ $user['role'] }}" data-status="{{ $user['status'] ?? 'active' }}" data-search-text="{{ strtolower($user['name'] . ' ' . $user['email'] . ' ' . $user['username']) }}">
                        <td>
                            <div class="user-info">
                                <div class="user-avatar">
                                    @if(isset($user['profile_image']) && $user['profile_image'])
                                        <img src="{{ asset('storage/' . $user['profile_image']) }}" alt="{{ $user['name'] }}">
                                    @else
                                        <div class="avatar-placeholder">
                                            {{ strtoupper(substr($user['name'], 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="user-details">
                                    <div class="user-name">{{ $user['name'] }}</div>
                                    <div class="user-username">{{ '@' . $user['username'] }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="role-badge role-{{ $user['role'] }}">
                                {{ ucfirst($user['role']) }}
                            </span>
                        </td>
                        <td>{{ $user['email'] }}</td>
                        <td>{{ $user['phone'] ?? 'N/A' }}</td>
                        <td>
                            <span class="status-badge status-{{ $user['status'] ?? 'active' }}">
                                {{ ucfirst($user['status'] ?? 'active') }}
                            </span>
                        </td>
                        <td>{{ $user['registered_at']->format('M d, Y') }}</td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-action btn-edit" onclick="editUser('{{ $user['role'] }}', '{{ $user['id'] }}')" title="Edit">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                                    </svg>
                                </button>
                                @if(($user['status'] ?? 'active') === 'active')
                                <button class="btn-action btn-suspend" onclick="updateStatus('{{ $user['role'] }}', '{{ $user['id'] }}', 'suspended')" title="Suspend">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                                    </svg>
                                </button>
                                @elseif(($user['status'] ?? 'active') === 'suspended')
                                <button class="btn-action btn-activate" onclick="updateStatus('{{ $user['role'] }}', '{{ $user['id'] }}', 'active')" title="Activate">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                    </svg>
                                </button>
                                @endif
                                <button class="btn-action btn-delete" onclick="deleteUser('{{ $user['role'] }}', '{{ $user['id'] }}')" title="Delete">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="no-users">No users found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal-overlay" id="editUserModal">
    <div class="modal modal-edit-user">
        <div class="modal-header">
            <h2 id="editUserModalTitle">Edit User</h2>
            <button class="modal-close" id="closeEditUserModal" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editUserForm">
                <input type="hidden" id="editUserRole" name="role">
                <input type="hidden" id="editUserId" name="id">
                
                <div class="form-group" id="nameGroup">
                    <label for="editUserName">Name</label>
                    <input type="text" id="editUserName" name="name" class="form-input" required>
                </div>
                
                <div class="form-group" id="emailGroup">
                    <label for="editUserEmail">Email</label>
                    <input type="email" id="editUserEmail" name="email" class="form-input" required>
                </div>
                
                <div class="form-group" id="phoneGroup">
                    <label for="editUserPhone">Phone</label>
                    <input type="text" id="editUserPhone" name="phone_no" class="form-input">
                </div>
                
                <div class="form-group" id="businessNameGroup" style="display: none;">
                    <label for="editBusinessName">Business Name</label>
                    <input type="text" id="editBusinessName" name="business_name" class="form-input">
                </div>
                
                <div class="form-group" id="organizationNameGroup" style="display: none;">
                    <label for="editOrganizationName">Organization Name</label>
                    <input type="text" id="editOrganizationName" name="organization_name" class="form-input">
                </div>
                
                <div class="form-group" id="fnameGroup" style="display: none;">
                    <label for="editFname">First Name</label>
                    <input type="text" id="editFname" name="fname" class="form-input">
                </div>
                
                <div class="form-group" id="lnameGroup" style="display: none;">
                    <label for="editLname">Last Name</label>
                    <input type="text" id="editLname" name="lname" class="form-input">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="cancelEditUserBtn">Cancel</button>
            <button class="btn btn-primary" id="saveUserBtn">Save Changes</button>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/admin-users.js') }}"></script>
@endpush

@endsection
