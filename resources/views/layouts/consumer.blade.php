<!-- resources/views/layouts/consumer.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Consumer Dashboard')</title>
    <link href="https://fonts.googleapis.com/css2?family=Afacad&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/consumer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/notifications.css') }}">
    @yield('styles')
</head>
<body>
    <div class="dashboard-container">
        @include('components.sidebar.consumer')
        @include('components.overlay')

        <main class="main-content" id="mainContent">
            <header class="header">
                <div class="header-left">
                    <button class="menu-toggle" id="menuToggle">☰</button>
                    <h1>@yield('header', 'Consumer Dashboard')</h1>
                </div>
                <div class="header-actions">
                    <div class="notification-container">
                        <button class="notification-btn" id="notificationBtn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
                            </svg>
                            <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
                        </button>
                        <div class="notification-dropdown" id="notificationDropdown">
                            <div class="notification-dropdown-header">
                                <h3>Notifications</h3>
                                <button class="mark-all-read-btn" id="markAllReadBtn">Mark all as read</button>
                            </div>
                            <div class="notification-list" id="notificationList">
                                <div class="notification-loading">Loading notifications...</div>
                            </div>
                            <div class="notification-dropdown-footer">
                                <a href="#" class="view-all-link" id="viewAllNotifications">View all notifications</a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <div class="content">
                @yield('content')
            </div>
        </main>
    </div>

    <script src="{{ asset('js/consumer.js') }}"></script>
    <script src="{{ asset('js/notifications.js') }}"></script>
    @yield('scripts')
</body>
</html>
