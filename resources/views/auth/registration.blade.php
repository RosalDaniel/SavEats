@extends('layouts.app')

@section('title', 'Register - SaveEats')

@section('styles')
    <link href="{{ asset('css/home.css') }}" rel="stylesheet">
    <link href="{{ asset('css/registration.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
@endsection

@section('content')
    <div class="back-btn-container">
        <a href="{{ route('home') }}" class="back-btn" id="backToLanding">
            <span class="back-arrow">←</span>
            Back to Landing
        </a>
    </div>

    <main class="main-content">
        <div class="register-container">
            <div class="register-header">
                <img src="{{ asset('images/SavEats-Logo.png') }}" alt="SaveAts Logo" class="saveats-logo">
                <h1 class="welcome-text">JOIN THE MOVEMENT</h1>
                <p class="subtitle-text">Choose your role in fighting food waste</p>
            </div>

            <div class="step-indicator">
                <div class="step active" id="step1">1</div>
                <div class="step-connector" id="connector1"></div>
                <div class="step inactive" id="step2">2</div>
                <div class="step-connector" id="connector2"></div>
                <div class="step inactive" id="step3">3</div>
            </div>

            <div class="success-message" id="successMessage">
                Account created successfully! Welcome to SavEats!
            </div>

            <form class="register-form" id="registerForm" novalidate>
                <!-- Step 1: Account Type Selection -->
                <div class="form-step active" id="formStep1">
                    <h2 style="text-align: center; color: #347928; font-size: 1.3rem; margin-bottom: 1.5rem;">SELECT ACCOUNT TYPE</h2>
                    
                    <div class="account-types">
                        <label class="account-type" for="consumer">
                            <input type="radio" id="consumer" name="accountType" value="consumer" required>
                            <div class="account-type-icon">👤</div>
                            <div class="account-type-info">
                                <div class="account-type-title">Consumers</div>
                                <div class="account-type-desc">Save money while reducing food waste</div>
                            </div>
                        </label>

                        <label class="account-type" for="business">
                            <input type="radio" id="business" name="accountType" value="business" required>
                            <div class="account-type-icon">📦</div>
                            <div class="account-type-info">
                                <div class="account-type-title">Food Business</div>
                                <div class="account-type-desc">List surplus food and reach more customers</div>
                            </div>
                        </label>

                        <label class="account-type" for="foodbank">
                            <input type="radio" id="foodbank" name="accountType" value="foodbank" required>
                            <div class="account-type-icon">🏢</div>
                            <div class="account-type-info">
                                <div class="account-type-title">Food Bank</div>
                                <div class="account-type-desc">Connect with food donations and volunteers</div>
                            </div>
                        </label>
                    </div>

                    <div class="btn-group">
                        <button type="button" class="btn btn-primary" id="nextStep1">Continue</button>
                    </div>
                </div>

                <!-- Step 2: Basic Information -->
                <div class="form-step" id="formStep2">
                    <h2 style="text-align: center; color: #347928; font-size: 1.3rem; margin-bottom: 1.5rem;" id="step2Title">BASIC INFORMATION</h2>
                    
                    <div id="step2Content">
                        <!-- Dynamic content will be inserted here -->
                    </div>

                    <div class="btn-group">
                        <button type="button" class="btn btn-secondary" id="prevStep2">Back</button>
                        <button type="button" class="btn btn-primary" id="nextStep2">Continue</button>
                    </div>
                </div>

                <!-- Step 3: Account Setup -->
                <div class="form-step" id="formStep3">
                    <h2 style="text-align: center; color: #347928; font-size: 1.3rem; margin-bottom: 1.5rem;">ACCOUNT SETUP</h2>
                    
                    <div class="form-group full-width">
                        <label for="username" class="form-label required">Username</label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            class="form-input" 
                            placeholder="Choose a username"
                            required
                            autocomplete="username"
                            aria-describedby="username-error"
                        >
                        <div class="error-message" id="username-error" role="alert"></div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="password" class="form-label required">Password</label>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-input" 
                                placeholder="Create password"
                                required
                                autocomplete="new-password"
                                aria-describedby="password-error"
                            >
                            <div class="error-message" id="password-error" role="alert"></div>
                        </div>

                        <div class="form-group">
                            <label for="confirmPassword" class="form-label required">Confirm Password</label>
                            <input 
                                type="password" 
                                id="confirmPassword" 
                                name="confirmPassword" 
                                class="form-input" 
                                placeholder="Confirm password"
                                required
                                autocomplete="new-password"
                                aria-describedby="confirmPassword-error"
                            >
                            <div class="error-message" id="confirmPassword-error" role="alert"></div>
                        </div>
                    </div>

                    <!-- Address field - different for each account type -->
                    <div class="form-group full-width" id="addressFieldContainer">
                        <!-- For Establishments: Map-based address selection (required) -->
                        <div id="establishmentAddressContainer" style="display: none;">
                            <label for="location" class="form-label required">Business Address</label>
                            <div class="address-map-container">
                                <div id="addressMap" class="address-map"></div>
                                <div class="map-instructions">
                                    <p>Click on the map to pin your business location</p>
                                </div>
                                <input 
                                    type="text" 
                                    id="location" 
                                    name="location" 
                                    class="form-input" 
                                    placeholder="Address will be filled from map selection"
                                    required
                                    readonly
                                    autocomplete="address-line1"
                                    aria-describedby="location-error"
                                >
                                <input type="hidden" id="latitude" name="latitude">
                                <input type="hidden" id="longitude" name="longitude">
                            </div>
                            <div class="error-message" id="location-error" role="alert"></div>
                        </div>
                        
                        <!-- For Consumers and Foodbanks: Simple text input (optional) -->
                        <div id="simpleAddressContainer" style="display: none;">
                            <label for="simpleAddress" class="form-label">Address (Optional)</label>
                            <input 
                                type="text" 
                                id="simpleAddress" 
                                name="address" 
                                class="form-input" 
                                placeholder="Enter your address"
                                autocomplete="address-line1"
                            >
                        </div>
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox" id="terms" name="terms" class="checkbox" required>
                        <label for="terms" class="checkbox-label">
                            I agree to the <a href="{{ route('terms') }}" target="_blank">Terms of Service</a> and <a href="{{ route('privacy') }}" target="_blank">Privacy Policy</a>
                        </label>
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox" id="newsletter" name="newsletter" class="checkbox">
                        <label for="newsletter" class="checkbox-label">
                            I'd like to receive updates about SavEats and food waste reduction tips
                        </label>
                    </div>

                    <div class="btn-group">
                        <button type="button" class="btn btn-secondary" id="prevStep3">Back</button>
                        <button type="submit" class="btn btn-primary" id="registerBtn">Create Account</button>
                    </div>
                </div>
            </form>

            <div class="login-link">
                Already have an account? <a href="{{ route('login') }}" id="loginLink">Login Here</a>
            </div>
        </div>
    </main>
@endsection

@section('scripts')
    <script src="{{ asset('js/home.js') }}"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="{{ asset('js/registration.js') }}"></script>
@endsection
