@extends('layouts.admin')

@section('title', 'Content Management System - Admin Dashboard')

@section('header', 'Content Management System')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin-cms.css') }}">
@endsection

@section('content')
<div class="cms-management-page">
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon banners">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Homepage Banners</h3>
                <p class="stat-number">{{ number_format($bannersCount) }}</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon articles">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Help Articles</h3>
                <p class="stat-number">{{ number_format($articlesCount) }}</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon terms">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M9 16h6v-6h-4v-4H9v10zm-2 2h10V8h-4V4H7v14zm-4 0h2V4H3v14z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Terms & Conditions</h3>
                <p class="stat-number">{{ number_format($termsCount) }}</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon privacy">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Privacy Policies</h3>
                <p class="stat-number">{{ number_format($privacyCount) }}</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon announcements">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Announcements</h3>
                <p class="stat-number">{{ number_format($announcementsCount) }}</p>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="cms-tabs">
        <button class="tab-btn active" data-tab="banners">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/>
            </svg>
            Homepage Banners
        </button>
        <button class="tab-btn" data-tab="articles">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
            </svg>
            Help Articles
        </button>
        <button class="tab-btn" data-tab="terms">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M9 16h6v-6h-4v-4H9v10zm-2 2h10V8h-4V4H7v14zm-4 0h2V4H3v14z"/>
            </svg>
            Terms & Conditions
        </button>
        <button class="tab-btn" data-tab="privacy">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/>
            </svg>
            Privacy Policy
        </button>
        <button class="tab-btn" data-tab="announcements">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
            </svg>
            Announcements
        </button>
    </div>

    <!-- Tab Content -->
    <div class="cms-content">
        <!-- Banners Tab -->
        <div class="tab-content active" id="banners-tab">
            @include('admin.cms.banners')
        </div>

        <!-- Articles Tab -->
        <div class="tab-content" id="articles-tab">
            @include('admin.cms.articles')
        </div>

        <!-- Terms Tab -->
        <div class="tab-content" id="terms-tab">
            @include('admin.cms.terms')
        </div>

        <!-- Privacy Tab -->
        <div class="tab-content" id="privacy-tab">
            @include('admin.cms.privacy')
        </div>

        <!-- Announcements Tab -->
        <div class="tab-content" id="announcements-tab">
            @include('admin.cms.announcements')
        </div>
    </div>
</div>

<!-- Modals will be included in each tab partial -->
@endsection

@section('scripts')
<script>
    // Define routes for JavaScript
    const CMS_ROUTES = {
        banners: {
            list: '{{ route('admin.cms.banners') }}',
            store: '{{ route('admin.cms.banners.store') }}',
            update: function(id) { return '{{ route('admin.cms.banners.update', ':id') }}'.replace(':id', id); },
            delete: function(id) { return '{{ route('admin.cms.banners.delete', ':id') }}'.replace(':id', id); }
        },
        articles: {
            list: '{{ route('admin.cms.articles') }}',
            store: '{{ route('admin.cms.articles.store') }}',
            update: function(id) { return '{{ route('admin.cms.articles.update', ':id') }}'.replace(':id', id); },
            delete: function(id) { return '{{ route('admin.cms.articles.delete', ':id') }}'.replace(':id', id); }
        },
        terms: {
            list: '{{ route('admin.cms.terms') }}',
            store: '{{ route('admin.cms.terms.store') }}',
            update: function(id) { return '{{ route('admin.cms.terms.update', ':id') }}'.replace(':id', id); },
            delete: function(id) { return '{{ route('admin.cms.terms.delete', ':id') }}'.replace(':id', id); }
        },
        privacy: {
            list: '{{ route('admin.cms.privacy') }}',
            store: '{{ route('admin.cms.privacy.store') }}',
            update: function(id) { return '{{ route('admin.cms.privacy.update', ':id') }}'.replace(':id', id); },
            delete: function(id) { return '{{ route('admin.cms.privacy.delete', ':id') }}'.replace(':id', id); }
        },
        announcements: {
            list: '{{ route('admin.cms.announcements') }}',
            store: '{{ route('admin.cms.announcements.store') }}',
            update: function(id) { return '{{ route('admin.cms.announcements.update', ':id') }}'.replace(':id', id); },
            delete: function(id) { return '{{ route('admin.cms.announcements.delete', ':id') }}'.replace(':id', id); }
        }
    };
</script>
<script src="{{ asset('js/admin-cms.js') }}"></script>
@endsection

