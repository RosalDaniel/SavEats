@extends('layouts.admin')

@section('title', 'Establishment Management - Admin Dashboard')

@section('header', 'Establishment Management')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin-establishments.css') }}">
@endsection

@section('content')
<div class="establishments-management-page">
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon establishments">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V9h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V9h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10zm-2-8h-2v2h2v-2zm0 4h-2v2h2v-2z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Total Establishments</h3>
                <p class="stat-number">{{ number_format($stats['total'] ?? 0) }}</p>
                <div class="stat-breakdown">
                    <span>Verified: {{ number_format($stats['verified'] ?? 0) }}</span>
                    <span>Unverified: {{ number_format($stats['unverified'] ?? 0) }}</span>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon verified">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Verified</h3>
                <p class="stat-number">{{ number_format($stats['verified'] ?? 0) }}</p>
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
            <div class="stat-icon violations">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>With Violations</h3>
                <p class="stat-number">{{ number_format($stats['with_violations'] ?? 0) }}</p>
            </div>
        </div>
    </div>

    <!-- Combined Filters and Establishments Table Section -->
    <div class="combined-section">
        <div class="filters-header">
            <h2>Filters</h2>
            <button class="btn-clear-filters" onclick="clearFilters()">Clear All</button>
        </div>
        <div class="filters-grid">
            <div class="filter-group">
                <label for="search">Search</label>
                <input type="text" id="search" name="search" class="filter-input" 
                       placeholder="Search by name, email, or owner..." 
                       value="{{ $searchQuery ?? '' }}">
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
            
            <div class="filter-group">
                <label for="verified">Verification</label>
                <select id="verified" name="verified" class="filter-select">
                    <option value="all" {{ ($verifiedFilter ?? 'all') === 'all' ? 'selected' : '' }}>All</option>
                    <option value="verified" {{ ($verifiedFilter ?? 'all') === 'verified' ? 'selected' : '' }}>Verified</option>
                    <option value="unverified" {{ ($verifiedFilter ?? 'all') === 'unverified' ? 'selected' : '' }}>Unverified</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="violations">Violations</label>
                <select id="violations" name="violations" class="filter-select">
                    <option value="all" {{ ($violationsFilter ?? 'all') === 'all' ? 'selected' : '' }}>All</option>
                    <option value="has_violations" {{ ($violationsFilter ?? 'all') === 'has_violations' ? 'selected' : '' }}>Has Violations</option>
                    <option value="no_violations" {{ ($violationsFilter ?? 'all') === 'no_violations' ? 'selected' : '' }}>No Violations</option>
                </select>
            </div>
        </div>

        <div class="table-header">
            <h2 id="establishmentsCountHeader">All Establishments ({{ count($formattedEstablishments ?? []) }})</h2>
        </div>
        
        <div class="table-container">
            <table class="establishments-table">
                <thead>
                    <tr>
                        <th>Establishment</th>
                        <th>Owner</th>
                        <th>Status</th>
                        <th>Verified</th>
                        <th>Listings</th>
                        <th>Rating</th>
                        <th>Violations</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="establishmentsTableBody">
                    @forelse($formattedEstablishments ?? [] as $establishment)
                    <tr data-establishment-id="{{ $establishment['id'] }}" 
                        data-status="{{ $establishment['status'] ?? 'active' }}" 
                        data-verified="{{ $establishment['verified'] ? 'true' : 'false' }}"
                        data-violations-count="{{ $establishment['violations_count'] ?? 0 }}"
                        data-search-text="{{ strtolower($establishment['business_name'] . ' ' . $establishment['email'] . ' ' . $establishment['owner_name']) }}">
                        <td>
                            <div class="establishment-info">
                                <div class="establishment-avatar">
                                    @if(isset($establishment['profile_image']) && $establishment['profile_image'])
                                        <img src="{{ asset('storage/' . $establishment['profile_image']) }}" alt="{{ $establishment['business_name'] }}">
                                    @else
                                        <div class="avatar-placeholder">
                                            {{ strtoupper(substr($establishment['business_name'], 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="establishment-details">
                                    <div class="establishment-name">{{ $establishment['business_name'] }}</div>
                                    <div class="establishment-email">{{ $establishment['email'] }}</div>
                                    @if($establishment['business_type'])
                                    <div class="establishment-type">{{ $establishment['business_type'] }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>{{ $establishment['owner_name'] }}</td>
                        <td>
                            <span class="status-badge status-{{ $establishment['status'] ?? 'active' }}">
                                {{ ucfirst($establishment['status'] ?? 'active') }}
                            </span>
                        </td>
                        <td>
                            @if($establishment['verified'])
                            <span class="verified-badge verified-yes">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                </svg>
                                Verified
                            </span>
                            @else
                            <span class="verified-badge verified-no">Not Verified</span>
                            @endif
                        </td>
                        <td>
                            <div class="listings-info">
                                <span class="listings-active">{{ $establishment['active_listings'] }}</span>
                                <span class="listings-separator">/</span>
                                <span class="listings-total">{{ $establishment['total_listings'] }}</span>
                            </div>
                        </td>
                        <td>
                            @if($establishment['total_reviews'] > 0)
                            <div class="rating-info">
                                <div class="rating-stars">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= floor($establishment['avg_rating']))
                                            <span class="star filled">★</span>
                                        @elseif($i - 0.5 <= $establishment['avg_rating'])
                                            <span class="star half">★</span>
                                        @else
                                            <span class="star">★</span>
                                        @endif
                                    @endfor
                                </div>
                                <div class="rating-value">{{ $establishment['avg_rating'] }} ({{ $establishment['total_reviews'] }})</div>
                            </div>
                            @else
                            <span class="no-rating">No ratings</span>
                            @endif
                        </td>
                        <td>
                            @if($establishment['violations_count'] > 0)
                            <button class="btn-violations" onclick="viewViolations('{{ $establishment['id'] }}', {{ json_encode($establishment['violations']) }})">
                                {{ $establishment['violations_count'] }} violation(s)
                            </button>
                            @else
                            <span class="no-violations">None</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons">
                                @if(!$establishment['verified'])
                                <button class="btn-action btn-verify" onclick="toggleVerification('{{ $establishment['id'] }}', true)" title="Verify">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                    </svg>
                                </button>
                                @else
                                <button class="btn-action btn-unverify" onclick="toggleVerification('{{ $establishment['id'] }}', false)" title="Unverify">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                                    </svg>
                                </button>
                                @endif
                                @if(($establishment['status'] ?? 'active') === 'active')
                                <button class="btn-action btn-suspend" onclick="updateStatus('{{ $establishment['id'] }}', 'suspended')" title="Suspend">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                                    </svg>
                                </button>
                                @elseif(($establishment['status'] ?? 'active') === 'suspended')
                                <button class="btn-action btn-activate" onclick="updateStatus('{{ $establishment['id'] }}', 'active')" title="Activate">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                    </svg>
                                </button>
                                @endif
                                <button class="btn-action btn-violation" onclick="addViolation('{{ $establishment['id'] }}')" title="Add Violation">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                                    </svg>
                                </button>
                                <button class="btn-action btn-delete" onclick="deleteEstablishment('{{ $establishment['id'] }}')" title="Delete">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="no-establishments">No establishments found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Violations Modal -->
<div class="modal-overlay" id="violationsModal">
    <div class="modal modal-violations">
        <div class="modal-header">
            <h2 id="violationsModalTitle">Violations</h2>
            <button class="modal-close" id="closeViolationsModal" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body" id="violationsModalBody">
            <!-- Content will be populated by JavaScript -->
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="closeViolationsModalBtn">Close</button>
        </div>
    </div>
</div>

<!-- Add Violation Modal -->
<div class="modal-overlay" id="addViolationModal">
    <div class="modal modal-add-violation">
        <div class="modal-header">
            <h2>Add Violation</h2>
            <button class="modal-close" id="closeAddViolationModal" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="addViolationForm">
                <input type="hidden" id="violationEstablishmentId" name="establishment_id">
                
                <div class="form-group">
                    <label for="violationType">Violation Type</label>
                    <input type="text" id="violationType" name="violation_type" class="form-input" 
                           placeholder="e.g., Food Safety, Policy Violation" required>
                </div>
                
                <div class="form-group">
                    <label for="violationSeverity">Severity</label>
                    <select id="violationSeverity" name="severity" class="form-input" required>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="violationDescription">Description</label>
                    <textarea id="violationDescription" name="description" class="form-input" 
                              rows="4" placeholder="Describe the violation..." required></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="cancelAddViolationBtn">Cancel</button>
            <button class="btn btn-primary" id="saveViolationBtn">Add Violation</button>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/admin-establishments.js') }}"></script>
@endpush

@endsection

