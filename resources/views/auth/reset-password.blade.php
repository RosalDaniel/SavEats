@extends('layouts.app')

@section('title', 'Reset Password - SaveEats')

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
                <h1 class="welcome-text">RESET PASSWORD</h1>
                <p class="subtitle-text">Enter your new password below</p>
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

            <form class="login-form" method="POST" action="{{ route('password-recovery.reset') }}" id="resetPasswordForm">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="form-group">
                    <label for="password" class="form-label">New Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input" 
                        placeholder="Enter new password"
                        required
                        minlength="8"
                        autocomplete="new-password"
                    >
                    <small style="color: #6b7280; font-size: 0.75rem; margin-top: 0.25rem; display: block;">
                        Password must be at least 8 characters long
                    </small>
                    @error('password')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Confirm New Password</label>
                    <input 
                        type="password" 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        class="form-input" 
                        placeholder="Confirm new password"
                        required
                        minlength="8"
                        autocomplete="new-password"
                    >
                    @error('password_confirmation')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="login-btn">
                    Reset Password
                </button>
            </form>

            <div class="register-link">
                Remember your password? <a href="{{ route('login') }}">Login Here</a>
            </div>
        </div>
    </main>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('resetPasswordForm');
            const password = document.getElementById('password');
            const passwordConfirmation = document.getElementById('password_confirmation');

            function validatePassword() {
                if (password.value !== passwordConfirmation.value) {
                    passwordConfirmation.setCustomValidity('Passwords do not match');
                } else {
                    passwordConfirmation.setCustomValidity('');
                }
            }

            password.addEventListener('input', validatePassword);
            passwordConfirmation.addEventListener('input', validatePassword);
        });
    </script>
@endsection

