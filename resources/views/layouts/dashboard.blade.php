<!-- resources/views/layouts/dashboard.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard')</title>
    <link href="https://fonts.googleapis.com/css2?family=Afacad&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('js/app.js') }}">

    {{-- Role-based CSS --}}
    @php $role = Auth::user()->role; @endphp
    @if ($role === 'consumer')
        <link rel="stylesheet" href="{{ asset('css/consumer.css') }}">
    @elseif ($role === 'establishment')
        <link rel="stylesheet" href="{{ asset('css/establishment.css') }}">
    @elseif ($role === 'foodbank')
        <link rel="stylesheet" href="{{ asset('css/foodbank.css') }}">
    @endif

</head>
<body>
    <div class="dashboard-container">
        <!-- Dynamically include sidebar based on user role -->
        <!-- @include('components.sidebar.' . Auth::user()->role) -->
        @include('components.sidebar.consumer') <!-- consumer role, for testing-->
        @include('components.overlay')

        <main class="main-content" id="mainContent">
            <header class="header">
                <div class="header-left">
                    <button class="menu-toggle" id="menuToggle">☰</button>
                    <h1>@yield('header', 'Dashboard')</h1>
                </div>
                <div class="header-actions">
                    <button class="notification-btn" id="notificationBtn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
                        </svg>
                    </button>
                </div>
            </header>

            <div class="content">
                @yield('content')
            </div>
        </main>
    </div>

    {{-- Role-based JS --}}
    @if ($role === 'consumer')
        <script src="{{ asset('js/consumer.js') }}"></script>
    @elseif ($role === 'establishment')
        <script src="{{ asset('js/establishment.js') }}"></script>
    @elseif ($role === 'foodbank')
        <script src="{{ asset('js/foodbank.js') }}"></script>
    @endif

</body>
</html>
