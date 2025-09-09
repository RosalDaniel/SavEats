<!-- Navigation Bar -->
<nav class="navbar">
    <div class="navbar-brand">
        <img src="{{ asset('images/SavEats-Logo.png') }}" alt="SavEats Logo" class="logo">
        <span class="brand-text">SavEats</span>
    </div>
    
    <ul class="navbar-nav">
        <li class="nav-item">
            <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
        </li>
        <li class="nav-item">
            <a href="{{ route('about') }}" class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}">About</a>
        </li>
        @guest
            <li class="nav-item">
                <a href="{{ route('login') }}" class="nav-link {{ request()->routeIs('login') ? 'active' : '' }}">Login</a>
            </li>
            <li class="nav-item">
                <a href="{{ route('register') }}" class="nav-link {{ request()->routeIs('register') ? 'active' : '' }}">Register</a>
            </li>
        @else
            <li class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                    {{ Auth::user()->name }}
                </a>
                <div class="dropdown-menu">
                    <a href="{{ route('dashboard') }}" class="dropdown-item">Dashboard</a>
                    <a href="{{ route('profile') }}" class="dropdown-item">Profile</a>
                    <div class="dropdown-divider"></div>
                    <form action="{{ route('logout') }}" method="POST" class="dropdown-item">
                        @csrf
                        <button type="submit" class="btn-link">Logout</button>
                    </form>
                </div>
            </li>
        @endguest
    </ul>
</nav>
