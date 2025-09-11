@extends('layouts.app')

@section('title', 'Login - SaveEats')

@section('styles')
    <link href="{{ asset('css/home.css') }}" rel="stylesheet">
    <link href="{{ asset('css/login.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="back-btn-container">
        <a href="{{ route('home') }}" class="back-btn" id="backToLanding">
            <span class="back-arrow">←</span>
            Back to Landing
        </a>
    </div>

    <main class="main-content">
        <div class="login-container">
            <div class="login-header">
                <div class="saveats-logo">
                    <img src="{{ asset('images/SavEats-Logo.png') }}" alt="SaveAts Logo" class="logo-image">
                </div>
                <h1 class="welcome-text">WELCOME BACK!</h1>
            </div>

            <div class="success-message" id="successMessage">
                Welcome back! Redirecting to your dashboard...
            </div>

            <form class="login-form" method="POST" action="{{ route('login.submit') }}" id="loginForm">
                @csrf
                
                @if ($errors->any())
                    <div class="error-message" style="margin-bottom: 1rem;">
                        @foreach ($errors->all() as $error)
                            {{ $error }}<br>
                        @endforeach
                    </div>
                @endif

                @if (session('success'))
                    <div class="success-message" style="margin-bottom: 1rem;">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="form-group">
                    <label for="login" class="form-label">Username or Email</label>
                    <input 
                        type="text" 
                        id="login" 
                        name="login" 
                        class="form-input" 
                        placeholder="Enter Username or Email"
                        required
                        value="{{ old('login') }}"
                        autocomplete="username"
                    >
                    @error('login')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input" 
                        placeholder="Enter Password"
                        required
                        autocomplete="current-password"
                    >
                    @error('password')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-options">
                    <div class="checkbox-container">
                        <input type="checkbox" id="remember" name="remember" class="checkbox">
                        <label for="remember" class="checkbox-label">Remember Me</label>
                    </div>
                    <a href="#" class="forgot-password">Forgot Password?</a>
                </div>

                <button type="submit" class="login-btn">
                    Login
                </button>
            </form>

            <div class="register-link">
                Don't have an account yet? <a href="{{ route('registration') }}" id="registerLink">Register Here</a>
            </div>
        </div>
    </main>
@endsection

@section('scripts')
    <script src="{{ asset('js/home.js') }}"></script>
    <script src="{{ asset('js/login.js') }}"></script>
@endsection
