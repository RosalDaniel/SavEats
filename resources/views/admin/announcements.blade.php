@extends('layouts.admin')

@section('title', 'Announcement Management - Admin Dashboard')

@section('header', 'Announcement Management')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin-announcements.css') }}">
@endsection

@section('content')
<div class="announcements-management-page">
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon announcements">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Total Announcements</h3>
                <p class="stat-number">{{ number_format($stats['total'] ?? 0) }}</p>
                <div class="stat-breakdown">
                    <span>Active: {{ number_format($stats['active'] ?? 0) }}</span>
                    <span>Inactive: {{ number_format($stats['inactive'] ?? 0) }}</span>
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
                <h3>Active</h3>
                <p class="stat-number">{{ number_format($stats['active'] ?? 0) }}</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon archived">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M20.54 5.23l-1.39-1.68C18.88 3.21 18.47 3 18 3H6c-.47 0-.88.21-1.16.55L3.46 5.23C3.17 5.57 3 6.02 3 6.5V19c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6.5c0-.48-.17-.93-.46-1.27zM12 17.5L6.5 12H10v-2h4v2h3.5L12 17.5zM5.12 5l.81-1h12l.94 1H5.12z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Archived</h3>
                <p class="stat-number">{{ number_format($stats['archived'] ?? 0) }}</p>
            </div>
        </div>
    </div>

    <!-- Combined Filters and Announcements Table Section -->
    <div class="combined-section">
        <div class="filters-header">
            <h2>Filters</h2>
            <div class="header-actions">
                <button class="btn-create" onclick="openCreateModal()">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                    </svg>
                    Create Announcement
                </button>
                <button class="btn-clear-filters" onclick="clearFilters()">Clear All</button>
            </div>
        </div>
        <div class="filters-grid">
            <div class="filter-group">
                <label for="search">Search</label>
                <input type="text" id="search" name="search" class="filter-input" 
                       placeholder="Search by title or message..." 
                       value="{{ $searchQuery ?? '' }}">
            </div>
            
            <div class="filter-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="filter-select">
                    <option value="all" {{ ($statusFilter ?? 'all') === 'all' ? 'selected' : '' }}>All Status</option>
                    <option value="active" {{ ($statusFilter ?? 'all') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ ($statusFilter ?? 'all') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="archived" {{ ($statusFilter ?? 'all') === 'archived' ? 'selected' : '' }}>Archived</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="audience">Target Audience</label>
                <select id="audience" name="audience" class="filter-select">
                    <option value="all" {{ ($audienceFilter ?? 'all') === 'all' ? 'selected' : '' }}>All Audiences</option>
                    <option value="consumer" {{ ($audienceFilter ?? 'all') === 'consumer' ? 'selected' : '' }}>Consumers</option>
                    <option value="establishment" {{ ($audienceFilter ?? 'all') === 'establishment' ? 'selected' : '' }}>Establishments</option>
                    <option value="foodbank" {{ ($audienceFilter ?? 'all') === 'foodbank' ? 'selected' : '' }}>Food Banks</option>
                </select>
            </div>
        </div>

        <div class="table-header">
            <h2 id="announcementsCountHeader">All Announcements ({{ count($announcements ?? []) }})</h2>
        </div>
        
        <div class="table-container">
            <table class="announcements-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Message</th>
                        <th>Target Audience</th>
                        <th>Status</th>
                        <th>Published</th>
                        <th>Expires</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="announcementsTableBody">
                    @forelse($announcements ?? [] as $announcement)
                    <tr data-announcement-id="{{ $announcement->id }}" 
                        data-status="{{ $announcement->status ?? 'active' }}"
                        data-audience="{{ $announcement->target_audience ?? 'all' }}"
                        data-search-text="{{ strtolower($announcement->title . ' ' . $announcement->message) }}">
                        <td>
                            <div class="announcement-title">{{ $announcement->title }}</div>
                        </td>
                        <td>
                            <div class="announcement-message">{{ Str::limit($announcement->message, 100) }}</div>
                        </td>
                        <td>
                            <span class="audience-badge audience-{{ $announcement->target_audience ?? 'all' }}">
                                {{ ucfirst($announcement->target_audience ?? 'all') }}
                            </span>
                        </td>
                        <td>
                            <span class="status-badge status-{{ $announcement->status ?? 'active' }}">
                                {{ ucfirst($announcement->status ?? 'active') }}
                            </span>
                        </td>
                        <td>
                            @if($announcement->published_at)
                            <div class="date-info">
                                <div class="date-created">{{ $announcement->published_at->format('M d, Y') }}</div>
                                <div class="date-time">{{ $announcement->published_at->format('h:i A') }}</div>
                            </div>
                            @else
                            <span class="no-date">Not published</span>
                            @endif
                        </td>
                        <td>
                            @if($announcement->expires_at)
                            <div class="date-info">
                                <div class="date-created">{{ $announcement->expires_at->format('M d, Y') }}</div>
                                <div class="date-time">{{ $announcement->expires_at->format('h:i A') }}</div>
                            </div>
                            @else
                            <span class="no-date">No expiry</span>
                            @endif
                        </td>
                        <td>
                            <div class="date-info">
                                <div class="date-created">{{ $announcement->created_at->format('M d, Y') }}</div>
                                <div class="date-time">{{ $announcement->created_at->format('h:i A') }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-action btn-edit" onclick="editAnnouncement({{ $announcement->id }}, {{ json_encode($announcement) }})" title="Edit">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                                    </svg>
                                </button>
                                <button class="btn-action btn-delete" onclick="deleteAnnouncement({{ $announcement->id }})" title="Delete">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="no-announcements">No announcements found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create/Edit Announcement Modal -->
<div class="modal-overlay" id="announcementModal">
    <div class="modal modal-announcement">
        <div class="modal-header">
            <h2 id="announcementModalTitle">Create Announcement</h2>
            <button class="modal-close" id="closeAnnouncementModal" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="announcementForm">
                <input type="hidden" id="announcementId" name="id">
                
                <div class="form-group">
                    <label for="announcementTitle">Title <span class="required">*</span></label>
                    <input type="text" id="announcementTitle" name="title" class="form-input" 
                           placeholder="Enter announcement title..." required>
                </div>
                
                <div class="form-group">
                    <label for="announcementMessage">Message <span class="required">*</span></label>
                    <textarea id="announcementMessage" name="message" class="form-input" 
                              rows="5" placeholder="Enter announcement message..." required></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="announcementAudience">Target Audience <span class="required">*</span></label>
                        <select id="announcementAudience" name="target_audience" class="form-input" required>
                            <option value="all">All Users</option>
                            <option value="consumer">Consumers</option>
                            <option value="establishment">Establishments</option>
                            <option value="foodbank">Food Banks</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="announcementStatus">Status <span class="required">*</span></label>
                        <select id="announcementStatus" name="status" class="form-input" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="announcementPublishedAt">Published At</label>
                        <input type="datetime-local" id="announcementPublishedAt" name="published_at" class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label for="announcementExpiresAt">Expires At</label>
                        <input type="datetime-local" id="announcementExpiresAt" name="expires_at" class="form-input">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="cancelAnnouncementBtn">Cancel</button>
            <button class="btn btn-primary" id="saveAnnouncementBtn">Save Announcement</button>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/admin-announcements.js') }}"></script>
@endpush

@endsection

