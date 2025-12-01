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
                    <div class="error-message" role="alert" aria-live="polite">
                        <strong>⚠️ Login Failed</strong><br>
                        @foreach ($errors->all() as $error)
                            {!! $error !!}
                            @if (!$loop->last)<br>@endif
                        @endforeach
                    </div>
                @endif

                @if (session('success'))
                    <div class="success-message" style="margin-bottom: 1rem; display: block;">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('info'))
                    <div class="info-message" style="margin-bottom: 1rem; background: #dbeafe; color: #1e40af; padding: 1rem; border-radius: 8px;">
                        {{ session('info') }}
                    </div>
                @endif

                <div class="form-group @error('login') has-error @enderror">
                    <label for="login" class="form-label">Username or Email</label>
                    <input 
                        type="text" 
                        id="login" 
                        name="login" 
                        class="form-input @error('login') error @enderror" 
                        placeholder="Enter Username or Email"
                        required
                        value="{{ old('login') }}"
                        autocomplete="username"
                        aria-invalid="@error('login') true @else false @enderror"
                        aria-describedby="@error('login') login-error @enderror"
                    >
                </div>

                <div class="form-group @error('password') has-error @enderror">
                    <label for="password" class="form-label">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input @error('password') error @enderror" 
                        placeholder="Enter Password"
                        required
                        autocomplete="current-password"
                        aria-invalid="@error('password') true @else false @enderror"
                        aria-describedby="@error('password') password-error @enderror"
                    >
                    @error('password')
                        <div class="error-message" id="password-error" role="alert">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-options">
                    <a href="{{ route('password-recovery.forgot') }}" class="forgot-password">Forgot Password?</a>
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
