@extends('layouts.foodbank')

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
                <span class="badge-number">{{ count($announcements ?? []) }}</span>
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
            <input type="text" class="search-input" placeholder="Search..." id="announcementSearch">
            <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
            </svg>
        </div>
    </div>

    <!-- Announcements Sections -->
    <div class="announcements-sections">
        @php
            $isNewThreshold = now()->subDays(7);
        @endphp
        
        <!-- Today Section -->
        <div class="announcement-section {{ count($groupedAnnouncements['today'] ?? []) > 0 ? 'active' : '' }}">
            <div class="section-header" data-section="today">
                <div class="section-title">
                    <h3>Today</h3>
                    <div class="section-badge">
                        <span class="badge-number">{{ count($groupedAnnouncements['today'] ?? []) }}</span>
                    </div>
                </div>
                <svg class="section-arrow" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/>
                </svg>
            </div>
            <div class="section-content" style="{{ count($groupedAnnouncements['today'] ?? []) > 0 ? '' : 'display: none;' }}">
                @forelse($groupedAnnouncements['today'] ?? [] as $announcement)
                <div class="announcement-item" data-search-text="{{ strtolower($announcement->title . ' ' . $announcement->message) }}">
                    <div class="announcement-icon system">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                    <div class="announcement-content">
                        <div class="announcement-header">
                            <h4 class="announcement-title">{{ $announcement->title }}</h4>
                            @if($announcement->created_at >= $isNewThreshold)
                            <span class="new-badge">New</span>
                            @endif
                        </div>
                        <p class="announcement-text">{{ $announcement->message }}</p>
                        <div class="announcement-meta">
                            <span class="announcement-time">{{ $announcement->created_at->diffForHumans() }}</span>
                            <span class="announcement-date">{{ $announcement->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="empty-section">
                    <p>No announcements for today.</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Yesterday Section -->
        <div class="announcement-section">
            <div class="section-header" data-section="yesterday">
                <div class="section-title">
                    <h3>Yesterday</h3>
                    <div class="section-badge">
                        <span class="badge-number">{{ count($groupedAnnouncements['yesterday'] ?? []) }}</span>
                    </div>
                </div>
                <svg class="section-arrow" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/>
                </svg>
            </div>
            <div class="section-content" style="display: none;">
                @forelse($groupedAnnouncements['yesterday'] ?? [] as $announcement)
                <div class="announcement-item" data-search-text="{{ strtolower($announcement->title . ' ' . $announcement->message) }}">
                    <div class="announcement-icon system">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                    <div class="announcement-content">
                        <div class="announcement-header">
                            <h4 class="announcement-title">{{ $announcement->title }}</h4>
                            @if($announcement->created_at >= $isNewThreshold)
                            <span class="new-badge">New</span>
                            @endif
                        </div>
                        <p class="announcement-text">{{ $announcement->message }}</p>
                        <div class="announcement-meta">
                            <span class="announcement-time">{{ $announcement->created_at->diffForHumans() }}</span>
                            <span class="announcement-date">{{ $announcement->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="empty-section">
                    <p>No announcements for yesterday.</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- A Week Ago Section -->
        <div class="announcement-section">
            <div class="section-header" data-section="week">
                <div class="section-title">
                    <h3>A week ago</h3>
                    <div class="section-badge">
                        <span class="badge-number">{{ count($groupedAnnouncements['week'] ?? []) }}</span>
                    </div>
                </div>
                <svg class="section-arrow" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/>
                </svg>
            </div>
            <div class="section-content" style="display: none;">
                @forelse($groupedAnnouncements['week'] ?? [] as $announcement)
                <div class="announcement-item" data-search-text="{{ strtolower($announcement->title . ' ' . $announcement->message) }}">
                    <div class="announcement-icon system">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                    <div class="announcement-content">
                        <div class="announcement-header">
                            <h4 class="announcement-title">{{ $announcement->title }}</h4>
                            @if($announcement->created_at >= $isNewThreshold)
                            <span class="new-badge">New</span>
                            @endif
                        </div>
                        <p class="announcement-text">{{ $announcement->message }}</p>
                        <div class="announcement-meta">
                            <span class="announcement-time">{{ $announcement->created_at->diffForHumans() }}</span>
                            <span class="announcement-date">{{ $announcement->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="empty-section">
                    <p>No announcements for this week.</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- A Month Ago Section -->
        <div class="announcement-section">
            <div class="section-header" data-section="month">
                <div class="section-title">
                    <h3>A month ago</h3>
                    <div class="section-badge">
                        <span class="badge-number">{{ count($groupedAnnouncements['month'] ?? []) }}</span>
                    </div>
                </div>
                <svg class="section-arrow" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/>
                </svg>
            </div>
            <div class="section-content" style="display: none;">
                @forelse($groupedAnnouncements['month'] ?? [] as $announcement)
                <div class="announcement-item" data-search-text="{{ strtolower($announcement->title . ' ' . $announcement->message) }}">
                    <div class="announcement-icon system">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                    <div class="announcement-content">
                        <div class="announcement-header">
                            <h4 class="announcement-title">{{ $announcement->title }}</h4>
                            @if($announcement->created_at >= $isNewThreshold)
                            <span class="new-badge">New</span>
                            @endif
                        </div>
                        <p class="announcement-text">{{ $announcement->message }}</p>
                        <div class="announcement-meta">
                            <span class="announcement-time">{{ $announcement->created_at->diffForHumans() }}</span>
                            <span class="announcement-date">{{ $announcement->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="empty-section">
                    <p>No announcements for this month.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/announcements.js') }}"></script>
@endsection
