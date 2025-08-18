<header class="header">
    <div class="nav-container">
        <div class="logo">
            <img src="{{ asset('images/SavEats-Logo.png') }}" alt="SaveAts Logo" class="logo-image">
        </div>
        <nav>
            <ul class="nav-menu" id="navMenu">
                <li><a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a></li>
                <li><a href="{{ route('home') }}#how-it-works" class="nav-link">How It Works</a></li>
                <li><a href="{{ route('home') }}#testimonials" class="nav-link">Testimonials</a></li>
                <li><a href="{{ route('about') }}" class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}">About Us</a></li>
                <li class="auth-buttons">
                    <a href="{{ route('login') }}" class="btn btn-login">Login</a>
                    <a href="{{ route('registration') }}" class="btn btn-register">Register</a>
                </li>
            </ul>
            <button class="mobile-toggle" id="mobileToggle">
                <span>☰</span>
            </button>
        </nav>
    </div>
</header>
