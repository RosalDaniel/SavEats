@extends('layouts.app')

@section('title', 'Forgot Password - SaveEats')

@section('styles')
    <link href="{{ asset('css/home.css') }}" rel="stylesheet">
    <link href="{{ asset('css/login.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="back-btn-container">
        <a href="{{ route('login') }}" class="back-btn" id="backToLogin">
            <span class="back-arrow">←</span>
            Back to Login
        </a>
    </div>

    <main class="main-content">
        <div class="login-container">
            <div class="login-header">
                <div class="saveats-logo">
                    <img src="{{ asset('images/SavEats-Logo.png') }}" alt="SaveAts Logo" class="logo-image">
                </div>
                <h1 class="welcome-text">FORGOT PASSWORD?</h1>
                <p class="subtitle-text">Enter your email address to receive a password reset link</p>
            </div>

            @if (session('error'))
                <div class="error-message" style="margin-bottom: 1rem;">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('success'))
                <div class="success-message" style="margin-bottom: 1rem;">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('message'))
                <div class="info-message" style="margin-bottom: 1rem; background: #dbeafe; color: #1e40af; padding: 1rem; border-radius: 8px;">
                    {{ session('message') }}
                </div>
            @endif

            <form class="login-form" method="POST" action="{{ route('password-recovery.request') }}" id="forgotPasswordForm">
                @csrf
                
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        placeholder="Enter your email address"
                        value="{{ old('email') }}"
                        required
                        autocomplete="email"
                    >
                    @error('email')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="login-btn">
                    Send Reset Link
                </button>
            </form>

            <div class="register-link">
                Remember your password? <a href="{{ route('login') }}">Login Here</a>
            </div>
        </div>
    </main>
@endsection

@section('scripts')
@endsection

