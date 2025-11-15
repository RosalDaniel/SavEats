@extends('layouts.establishment')

@section('title', 'Announcements | SavEats')

@section('header', 'Announcements')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Afacad:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/announcements.css') }}">
@endsection

@section('content')
<div class="announcements-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <h1 class="page-title">Get the Latest News</h1>
            <div class="header-badge">
                <span class="badge-number">3</span>
            </div>
        </div>
        <div class="header-actions">
            <button class="action-btn filter-btn" title="Filter">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M3 6h18v2H3V6zm0 5h18v2H3v-2zm0 5h18v2H3v-2z"/>
                </svg>
            </button>
            <button class="action-btn sort-btn" title="Sort">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M3 18h6v-2H3v2zM3 6v2h18V6H3zm0 7h12v-2H3v2z"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="search-container">
        <div class="search-input-wrapper">
            <input type="text" class="search-input" placeholder="Search...">
            <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
            </svg>
        </div>
    </div>

    <!-- Announcements Sections -->
    <div class="announcements-sections">
        <!-- Today Section -->
        <div class="announcement-section active">
            <div class="section-header" data-section="today">
                <div class="section-title">
                    <h3>Today</h3>
                    <div class="section-badge">
                        <span class="badge-number">3</span>
                    </div>
                </div>
                <svg class="section-arrow" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/>
                </svg>
            </div>
            <div class="section-content">
                <!-- System Update Notice -->
                <div class="announcement-item">
                    <div class="announcement-icon system">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                    <div class="announcement-content">
                        <h4 class="announcement-title">System update notice!</h4>
                        <p class="announcement-text">We're rolling out a new feature to make surplus listing even smoother. Stay tuned for updates!</p>
                        <div class="announcement-meta">
                            <span class="announcement-time">23 mins.</span>
                        </div>
                    </div>
                </div>

                <!-- Flash Sale Alert -->
                <div class="announcement-item">
                    <div class="announcement-icon sale">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M7 4V2C7 1.45 7.45 1 8 1H16C16.55 1 17 1.45 17 2V4H20C20.55 4 21 4.45 21 5S20.55 6 20 6H19V19C19 20.1 18.1 21 17 21H7C5.9 21 5 20.1 5 19V6H4C3.45 6 3 5.55 3 5S3.45 4 4 4H7ZM9 3V4H15V3H9ZM7 6V19H17V6H7Z"/>
                        </svg>
                    </div>
                    <div class="announcement-content">
                        <h4 class="announcement-title">Flash sale alert</h4>
                        <p class="announcement-text">Limited-time offer! List your surplus food items and reach more customers. Boost your sales today!</p>
                        <div class="announcement-meta">
                            <span class="announcement-time">23 mins.</span>
                        </div>
                        <div class="announcement-action">
                            <button class="action-button">
                                <span>Go to Listing Management</span>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Technical Issue Alert -->
                <div class="announcement-item">
                    <div class="announcement-icon technical">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M22.7 19l-9.1-9.1c.9-2.3.4-5-1.5-6.9-2-2-5-2.4-7.4-1.3L9 6 6 9 1.6 4.7C.4 7.1.9 10.1 2.9 12.1c1.9 1.9 4.6 2.4 6.9 1.5l9.1 9.1c.4.4 1 .4 1.4 0l2.3-2.3c.5-.4.5-1.1.1-1.4z"/>
                        </svg>
                    </div>
                    <div class="announcement-content">
                        <h4 class="announcement-title">Technical issue alert!</h4>
                        <p class="announcement-text">We're aware of a temporary issue affecting notifications. Our team is working on a fix—thank you for your patience!</p>
                        <div class="announcement-meta">
                            <span class="announcement-time">40 mins.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Yesterday Section -->
        <div class="announcement-section">
            <div class="section-header" data-section="yesterday">
                <div class="section-title">
                    <h3>Yesterday</h3>
                    <div class="section-badge">
                        <span class="badge-number">0</span>
                    </div>
                </div>
                <svg class="section-arrow" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/>
                </svg>
            </div>
            <div class="section-content" style="display: none;">
                <div class="empty-section">
                    <p>No announcements for yesterday.</p>
                </div>
            </div>
        </div>

        <!-- A Week Ago Section -->
        <div class="announcement-section">
            <div class="section-header" data-section="week">
                <div class="section-title">
                    <h3>A week ago</h3>
                    <div class="section-badge">
                        <span class="badge-number">0</span>
                    </div>
                </div>
                <svg class="section-arrow" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/>
                </svg>
            </div>
            <div class="section-content" style="display: none;">
                <div class="empty-section">
                    <p>No announcements for this week.</p>
                </div>
            </div>
        </div>

        <!-- A Month Ago Section -->
        <div class="announcement-section">
            <div class="section-header" data-section="month">
                <div class="section-title">
                    <h3>A month ago</h3>
                    <div class="section-badge">
                        <span class="badge-number">0</span>
                    </div>
                </div>
                <svg class="section-arrow" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/>
                </svg>
            </div>
            <div class="section-content" style="display: none;">
                <div class="empty-section">
                    <p>No announcements for this month.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/announcements.js') }}"></script>
<script>
    // Update action button to redirect to establishment listing management
    document.addEventListener('DOMContentLoaded', function() {
        const actionButtons = document.querySelectorAll('.action-button');
        actionButtons.forEach(button => {
            const span = button.querySelector('span');
            if (span && span.textContent.includes('Go to Listing Management')) {
                button.addEventListener('click', function() {
                    window.location.href = '{{ route("establishment.listing-management") }}';
                });
            }
        });
    });
</script>
@endsection

